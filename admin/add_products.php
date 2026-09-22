<?php

session_start();

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_email'])) {

}

require_once '../database/db_connect.php';

// ---- Fetch categories for the dropdown ----
$categories = [];
$cat_result = mysqli_query($conn, "SELECT categories_id, name FROM categories ORDER BY name ASC");
if ($cat_result) {
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $row;
    }
}

// ---- Fetch wallpapers for the optional "link to wallpaper" dropdown ----
$wallpapers = [];
$wp_result = mysqli_query($conn, "SELECT id, name FROM wallpapers ORDER BY name ASC");
if ($wp_result) {
    while ($row = mysqli_fetch_assoc($wp_result)) {
        $wallpapers[] = $row;
    }
}

// ---- Fetch collections/styles for the checkbox list (NEW) ----
$collections = [];
$col_result = mysqli_query($conn, "SELECT collection_id, name FROM collections ORDER BY collection_id ASC");
if ($col_result) {
    while ($row = mysqli_fetch_assoc($col_result)) {
        $collections[] = $row;
    }
}

// ---- Show a success/error message after redirect from process-add-product.php ----
$message = '';
$message_type = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = 'Product added successfully.';
        $message_type = 'success';
    } elseif ($_GET['status'] === 'error') {
        $message = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Something went wrong. Please try again.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Product - Sweet Haven Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="add_product.css">
</head>
<body>

<div class="admin-layout">

    <?php
    $current_page = "add_product"; // tells admin_sidebar.php which link to highlight
    include "admin_sidebar.php";
    ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="page-title">Add New Product</div>
        <div class="breadcrumb">Dashboard &gt; Add New Product</div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form id="addProductForm" action="process-add-product.php" method="POST" enctype="multipart/form-data">
        <div class="form-layout">

            <div class="form-card">

    <div class="form-row cols-2">
        <div class="field">
            <label for="product_name">Product Name *</label>
            <input type="text" id="product_name" name="product_name" placeholder="Enter product name" required>
        </div>
        <div class="field">
            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="">Select category</option>
                <option value="wallpaper">Wallpaper</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['categories_id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- ===== COLLECTION / STYLE (NEW) ===== -->
    <div class="form-row">
        <div class="field">
            <label>Collection / Style</label>
            <p style="font-size:13px; color:#8a7a6d; margin: 4px 0 10px;">
                Tick a style if this product doesn't need a Category — pick a Category, a Style, or both.
            </p>
            <div class="collection-checkboxes">
                <?php foreach ($collections as $col): ?>
                    <label class="checkbox-pill">
                        <input type="checkbox" name="collections[]" value="<?php echo $col['collection_id']; ?>">
                        <?php echo htmlspecialchars($col['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

 <!-- ===== DECOR-PRODUCT FIELDS (goes into "products" table) ===== -->
 <div class="form-row cols-3" id="decorFields">
     <div class="field">
         <label for="price">Price (Rs.) *</label>
         <input type="number" step="0.01" id="price" name="price" placeholder="e.g. 2500.00" required>
     </div>
     <div class="field">
         <label for="stock">Stock Quantity *</label>
         <input type="number" id="stock" name="stock" placeholder="e.g. 20">
     </div>
     <div class="field">
         <label for="wallpaper_id">Link Wallpaper (optional)</label>
         <select id="wallpaper_id" name="wallpaper_id">
             <option value="">None</option>
             <?php foreach ($wallpapers as $wp): ?>
                 <option value="<?php echo $wp['id']; ?>">
                     <?php echo htmlspecialchars($wp['name']); ?>
                 </option>
             <?php endforeach; ?>
         </select>
     </div>
 </div>

 <!-- ===== WALLPAPER FIELDS (goes into "wallpapers" table) ===== -->
 <div class="form-row cols-4" id="wallpaperFields">
     <div class="field">
         <label for="wp_price">Price (Rs.) *</label>
         <input type="number" step="0.01" id="wp_price" name="wp_price" placeholder="e.g. 1800.00">
     </div>
     <div class="field">
         <label for="color">Color Name *</label>
         <input type="text" id="color" name="color" placeholder="e.g. Sage Green">
     </div>
     <div class="field">
         <label for="color_hex">Color Hex *</label>
         <input type="color" id="color_hex" name="color_hex" value="#8a9a6b">
     </div>
     <div class="field">
         <label for="badge">Badge</label>
         <select id="badge" name="badge">
             <option value="">None</option>
             <option value="New">New</option>
             <option value="Bestseller">Bestseller</option>
             <option value="Sale">Sale</option>
         </select>
     </div>
 </div>

 <div class="form-row">
     <div class="field">
         <label for="short_description">Short Description (optional)</label>
         <input type="text" id="short_description" name="short_description" placeholder="A short description about the product">
     </div>
 </div>

 <div class="form-row" id="fullDescriptionRow">
     <div class="field">
         <label for="description">Full Description *</label>
         <textarea id="description" name="description" placeholder="Write detailed description about the product..." required></textarea>
     </div>
 </div>

 <div class="form-row cols-2">
     <div class="field">
         <label>Product Image *</label>

         <div class="image-source-toggle" style="margin-bottom:10px;">
             <label class="radio-row" style="display:inline-block; margin-right:16px;">
                 <input type="radio" name="image_source" value="upload" id="sourceUpload" checked> Upload File
             </label>
             <label class="radio-row" style="display:inline-block;">
                 <input type="radio" name="image_source" value="url" id="sourceUrl"> Image URL (Pinterest, etc.)
             </label>
         </div>

         <div class="upload-box" id="uploadBox">
             <div>&#9729;</div>
             <div class="upload-title">Click to upload or drag and drop</div>
             <div class="upload-hint">JPG, PNG, WEBP (Max. 5MB)</div>
         </div>
         <input type="file" id="product_image" name="product_image" accept=".jpg,.jpeg,.png,.webp" style="display:none;">

         <div class="field" id="imageUrlField" style="display:none; margin-top:10px;">
             <input type="url" id="image_url" name="image_url" placeholder="Paste image URL here">
         </div>
     </div>
     <div class="field">
         <label>Image Preview</label>
         <div class="image-preview" id="imagePreview">
             <span class="placeholder">No image selected</span>
         </div>
     </div>
 </div>

       <div class="form-actions">
           <button type="button" class="btn btn-cancel" onclick="window.location.href='products.php'">Cancel</button>
           <button type="submit" class="btn btn-submit">+ Add Product</button>
       </div>

     </div>

 <div class="side-panel">
     <div class="side-card">
         <h3>Product Status</h3>
         <label class="radio-row"><input type="radio" name="status" value="Active" checked> Active</label>
         <label class="radio-row"><input type="radio" name="status" value="Inactive"> Inactive</label>
     </div>
     <div class="side-card">
         <h3>Product Details</h3>
         <div class="tip">Add high quality images for better customer experience.</div>
         <div class="tip">Use accurate price and stock information.</div>
         <div class="tip">Choosing "Wallpaper" as the category automatically switches the form to wallpaper fields, and the item will appear on the wallpaper page instead of the decor category page.</div>
         <div class="tip">Tick a Collection/Style so the product also shows up on the Collections page.</div>
     </div>
 </div>

        </div>
        </form>

    </main>
</div>

<style>
/* NEW styles for the collection checkboxes — add these to add_product.css,
   or leave them here inline, either works. */
.collection-checkboxes {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.checkbox-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1px solid #e6d9c3;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 400;
    cursor: pointer;
    background: #fff;
}
.checkbox-pill:has(input:checked) {
    background: #5c1a2e;
    color: #fff;
    border-color: #5c1a2e;
}
</style>

<script>

document.addEventListener('DOMContentLoaded', function () {

  var categorySelect = document.getElementById('category');
  var decorFields = document.getElementById('decorFields');
  var wallpaperFields = document.getElementById('wallpaperFields');


  function toggleFieldsByCategory() {
    var isWallpaper = categorySelect.value === 'wallpaper';

    decorFields.style.display = isWallpaper ? 'none' : 'grid';
    wallpaperFields.style.display = isWallpaper ? 'grid' : 'none';

    //decor field
    document.getElementById('price').required = !isWallpaper;
    document.getElementById('stock').required = !isWallpaper;

    //wallpaper field
    document.getElementById('wp_price').required = isWallpaper;
    document.getElementById('color').required = isWallpaper;
    document.getElementById('color_hex').required = isWallpaper;

    document.getElementById('description').required = !isWallpaper;
  }

  categorySelect.addEventListener('change', toggleFieldsByCategory);
  toggleFieldsByCategory(); 

//image upload 
  var uploadBox = document.getElementById('uploadBox');
  var fileInput = document.getElementById('product_image');
  var previewBox = document.getElementById('imagePreview');

  uploadBox.addEventListener('click', function () {
    fileInput.click();
  });

  // Drag and drop support
  uploadBox.addEventListener('dragover', function (e) {
    e.preventDefault();
    uploadBox.style.background = '#f5ecdd';
  });

  uploadBox.addEventListener('dragleave', function () {
    uploadBox.style.background = '';
  });

  uploadBox.addEventListener('drop', function (e) {
    e.preventDefault();
    uploadBox.style.background = '';
    if (e.dataTransfer.files.length) {
      fileInput.files = e.dataTransfer.files;
      showPreview(fileInput.files[0]);
    }

  });

  fileInput.addEventListener('change', function () {
    if (fileInput.files.length) {
      showPreview(fileInput.files[0]);
    }
  });

  function showPreview(file) {
    var allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (allowed.indexOf(file.type) === -1) {
      alert('Please upload a JPG, PNG, or WEBP image.');
      fileInput.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert('Image must be under 5MB.');
      fileInput.value = '';
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
      previewBox.innerHTML = '<img src="' + e.target.result + '" alt="Product preview">';
    };
    reader.readAsDataURL(file);
  }

  // ---------- IMAGE SOURCE TOGGLE (upload vs URL) ----------
  var sourceUpload = document.getElementById('sourceUpload');
  var sourceUrl = document.getElementById('sourceUrl');
  var imageUrlField = document.getElementById('imageUrlField');
  var imageUrlInput = document.getElementById('image_url');

  function toggleImageSource() {
    var useUrl = sourceUrl.checked;
    uploadBox.style.display = useUrl ? 'none' : 'block';
    imageUrlField.style.display = useUrl ? 'block' : 'none';

    if (useUrl) {
      fileInput.value = '';
      previewBox.innerHTML = '<span class="placeholder">No image selected</span>';
    } else {
      imageUrlInput.value = '';
      previewBox.innerHTML = '<span class="placeholder">No image selected</span>';
    }
  }

  sourceUpload.addEventListener('change', toggleImageSource);
  sourceUrl.addEventListener('change', toggleImageSource);

  // Live preview when pasting a URL
  imageUrlInput.addEventListener('input', function () {
    if (imageUrlInput.value.trim()) {
      previewBox.innerHTML = '<img src="' + imageUrlInput.value.trim() + '" alt="Product preview" onerror="this.parentNode.innerHTML=\'<span class=&quot;placeholder&quot;>Couldn&#39;t load that URL</span>\'">';
    } else {
      previewBox.innerHTML = '<span class="placeholder">No image selected</span>';
    }
  });

  //basic form validation
  var form = document.getElementById('addProductForm');
  form.addEventListener('submit', function (e) {
    if (sourceUpload.checked && !fileInput.files.length) {
      e.preventDefault();
      alert('Please select a product image.');
      return;
    }
    if (sourceUrl.checked && !imageUrlInput.value.trim()) {
      e.preventDefault();
      alert('Please paste an image URL.');
      return;
    }

    // NEW: require a Category OR at least one Collection/Style
    var categoryPicked = categorySelect.value !== '';
    var anyStyleChecked = document.querySelectorAll('input[name="collections[]"]:checked').length > 0;
    if (!categoryPicked && !anyStyleChecked) {
      e.preventDefault();
      alert('Please select a Category, or tick at least one Collection/Style.');
      return;
    }
  });

});
</script>
</body>
</html>