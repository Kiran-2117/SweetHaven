<?php

session_start();
require_once '../database/db_connect.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_products.php");
    exit();
}

// ---- Collect + sanitize the shared fields ----
$product_name       = mysqli_real_escape_string($conn, trim($_POST['product_name'] ?? ''));
$category            = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
$description         = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
$short_description   = trim($_POST['short_description'] ?? '');
$has_collections      = !empty($_POST['collections']);

if ($short_description !== '') {
    $description = mysqli_real_escape_string($conn, $short_description . "\n\n" . ($_POST['description'] ?? ''));
}

if ($product_name === '') {
    header("Location: add_products.php?status=error&msg=" . urlencode("Product name is required."));
    exit();
}

// A product needs a Category OR at least one Collection/Style — not both required.
if ($category === '' && !$has_collections) {
    header("Location: add_products.php?status=error&msg=" . urlencode("Please select a Category, or tick at least one Collection/Style."));
    exit();
}

$is_wallpaper = ($category === 'wallpaper');
$upload_dir   = $is_wallpaper ? '../uploads/wallpapers/' : '../uploads/products/';
$image_source = trim($_POST['image_source'] ?? 'upload');

// ================== IMAGE HANDLING ==================
// $image_db_path is what gets stored in the DB.
// For uploads it must be ROOT-relative (e.g. 'uploads/products/xxx.jpg') since
// your storefront pages (decor.php, product.php, wallpaper.php) live at the
// project root, not inside /admin/ where this script runs.
// For URL images, we just store the URL as-is.

$image_db_path = '';

if ($image_source === 'url') {

    $image_url = trim($_POST['image_url'] ?? '');

    if ($image_url === '' || !filter_var($image_url, FILTER_VALIDATE_URL)) {
        header("Location: add_products.php?status=error&msg=" . urlencode("Please provide a valid image URL."));
        exit();
    }

    $image_db_path = mysqli_real_escape_string($conn, $image_url);

} else {

    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        header("Location: add_products.php?status=error&msg=" . urlencode("Please upload a product image."));
        exit();
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $file_type     = $_FILES['product_image']['type'];
    $file_size     = $_FILES['product_image']['size'];
    $file_tmp      = $_FILES['product_image']['tmp_name'];
    $file_ext      = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_type, $allowed_types)) {
        header("Location: add_products.php?status=error&msg=" . urlencode("Only JPG, PNG, or WEBP images are allowed."));
        exit();
    }

    if ($file_size > 5 * 1024 * 1024) {
        header("Location: add_products.php?status=error&msg=" . urlencode("Image must be under 5MB."));
        exit();
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $image_name = uniqid('prod_', true) . '.' . $file_ext;
    $filesystem_path = $upload_dir . $image_name; // physical save path (relative to /admin/)

    if (!move_uploaded_file($file_tmp, $filesystem_path)) {
        header("Location: add_products.php?status=error&msg=" . urlencode("Image upload failed. Please try again."));
        exit();
    }

    // Root-relative path for the DB / storefront pages
    $root_relative_path = ($is_wallpaper ? 'uploads/wallpapers/' : 'uploads/products/') . $image_name;
    $image_db_path = mysqli_real_escape_string($conn, $root_relative_path);
}

// Helper: delete an uploaded file if the DB insert fails later (no-op for URL images)
function cleanup_upload($image_source, $upload_dir, $image_db_path) {
    if ($image_source !== 'url' && $image_db_path !== '') {
        $filename = basename($image_db_path);
        $path = $upload_dir . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

// Helper: save the ticked Collection/Style checkboxes for the product/wallpaper
// we just inserted. $product_type must be 'product' or 'wallpaper'.
function save_product_collections($conn, $new_id, $product_type) {
    if (!empty($_POST['collections'])) {
        foreach ($_POST['collections'] as $collection_id) {
            $collection_id = (int) $collection_id;
            mysqli_query($conn, "
                INSERT INTO product_collections (collection_id, product_id, product_type)
                VALUES ($collection_id, $new_id, '$product_type')
            ");
        }
    }
}

//insert in to wallpapers
if ($is_wallpaper) {

    $price     = mysqli_real_escape_string($conn, trim($_POST['wp_price'] ?? ''));
    $color     = mysqli_real_escape_string($conn, trim($_POST['color'] ?? ''));
    $color_hex = mysqli_real_escape_string($conn, trim($_POST['color_hex'] ?? ''));

    if ($price === '' || $color === '' || $color_hex === '') {
        cleanup_upload($image_source, $upload_dir, $image_db_path);
        header("Location: add_products.php?status=error&msg=" . urlencode("Price, color name, and color hex are required for wallpapers."));
        exit();
    }


    $sql = "INSERT INTO wallpapers (name, color, color_hex, price, image)
            VALUES ('$product_name', '$color', '$color_hex', '$price', '$image_db_path')";

    if (mysqli_query($conn, $sql)) {
        // NEW: save which styles this wallpaper belongs to
        $new_id = mysqli_insert_id($conn);
        save_product_collections($conn, $new_id, 'wallpaper');

        header("Location: add_products.php?status=success");
        exit();
    } else {
        cleanup_upload($image_source, $upload_dir, $image_db_path);
        header("Location: add_products.php?status=error&msg=" . urlencode("Database error: " . mysqli_error($conn)));
        exit();
    }


} else {

    $categories_id_sql = ($category !== '') ? "'" . (int) $category . "'" : "NULL";
    $price             = mysqli_real_escape_string($conn, trim($_POST['price'] ?? ''));
    $stock             = mysqli_real_escape_string($conn, trim($_POST['stock'] ?? '0'));
    $linked_wallpaper  = trim($_POST['wallpaper_id'] ?? '');

    if ($price === '') {
        cleanup_upload($image_source, $upload_dir, $image_db_path);
        header("Location: add_products.php?status=error&msg=" . urlencode("Price is required."));
        exit();
    }

    $wallpaper_id_sql = ($linked_wallpaper !== '') ? "'" . (int) $linked_wallpaper . "'" : "NULL";

    $sql = "INSERT INTO products (categories_id, name, price, image, description, stock, created_at, wallpaper_id)
            VALUES ($categories_id_sql, '$product_name', '$price', '$image_db_path', '$description', '$stock', NOW(), $wallpaper_id_sql)";

    if (mysqli_query($conn, $sql)) {
        // NEW: save which styles this product belongs to
        $new_id = mysqli_insert_id($conn);
        save_product_collections($conn, $new_id, 'product');

        header("Location: add_products.php?status=success");
        exit();
    } else {
        cleanup_upload($image_source, $upload_dir, $image_db_path);
        header("Location: add_products.php?status=error&msg=" . urlencode("Database error: " . mysqli_error($conn)));
        exit();
    }
}
?>