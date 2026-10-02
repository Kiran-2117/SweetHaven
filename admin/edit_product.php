<?php
session_start();

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_email'])) {
    header('Location: login.php');
    exit;
}

require_once '../database/db_connect.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function img_src($p) {
    if (!$p) return '';
    return preg_match('#^https?://#i', $p) ? $p : '../' . ltrim($p, '/');
}

$type = ($_REQUEST['type'] ?? 'product') === 'wallpaper' ? 'wallpaper' : 'product';
$id   = (int) ($_REQUEST['id'] ?? 0);

// ---- Load the item being edited ----
if ($type === 'wallpaper') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM wallpapers WHERE id = ?");
} else {
    $stmt = mysqli_prepare($conn, "SELECT product_id, categories_id, name, price, image, description, stock
                                   FROM products WHERE product_id = ?");
}
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$item) {
    header('Location: products.php?status=notfound');
    exit;
}

// Categories for the dropdown (decor products only)
$categories = [];
$cr = mysqli_query($conn, "SELECT categories_id, name FROM categories ORDER BY name ASC");
while ($cr && $row = mysqli_fetch_assoc($cr)) { $categories[] = $row; }

// Which optional wallpaper columns exist in your table? (color, color_hex, badge, stock)
// Value = true if the column allows NULL.
$wpCols = [];
if ($type === 'wallpaper') {
    $cr2 = mysqli_query($conn, "SHOW COLUMNS FROM wallpapers");
    while ($cr2 && $c = mysqli_fetch_assoc($cr2)) { $wpCols[$c['Field']] = ($c['Null'] === 'YES'); }
}
$badgeOptions = ['New', 'Bestseller', 'Sale'];

// CHANGE: does this item have a stock value that can be edited?
$hasStock = ($type === 'product') || isset($wpCols['stock']);

$errors = [];

// ---- Save ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('Invalid request.');
    }

    $name  = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? '';
    $desc  = trim($_POST['description'] ?? '');
    $stock = $_POST['stock'] ?? '0';
    $catId = ($_POST['category'] ?? '') !== '' ? (int) $_POST['category'] : null;

    $color    = trim($_POST['color'] ?? '');
    $colorHex = trim($_POST['color_hex'] ?? '');
    $badge    = $_POST['badge'] ?? '';
    if ($type === 'wallpaper') {
        if ($colorHex !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $colorHex)) $errors[] = 'Pick a valid colour.';
        if (!in_array($badge, array_merge([''], $badgeOptions), true))         $errors[] = 'Invalid badge.';
    }

    if ($name === '')                      $errors[] = 'Name is required.';
    if (!is_numeric($price) || $price < 0) $errors[] = 'Enter a valid price.';
    // CHANGE: stock is checked for wallpapers too
    if ($hasStock && (!ctype_digit((string)$stock))) $errors[] = 'Stock must be a whole number (0 or more).';

    // ---- Image: keep current unless a new file / URL is given ----
    $image = $item['image'];
    $urlIn = trim($_POST['image_url'] ?? '');

    if (!empty($_FILES['image_file']['name'])) {
        $f   = $_FILES['image_file'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if ($f['error'] !== UPLOAD_ERR_OK)                                $errors[] = 'Image upload failed.';
        elseif (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true))    $errors[] = 'Image must be JPG, PNG or WEBP.';
        elseif ($f['size'] > 5 * 1024 * 1024)                             $errors[] = 'Image must be under 5MB.';
        elseif (!@getimagesize($f['tmp_name']))                           $errors[] = 'That file is not a valid image.';
        else {
            $dir = __DIR__ . '/../assets/images/uploads/';
            if (!is_dir($dir)) { mkdir($dir, 0777, true); }
            $fname = 'prod_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $dir . $fname)) {
                $image = 'assets/images/uploads/' . $fname;
            } else {
                $errors[] = 'Could not save the uploaded image.';
            }
        }
    } elseif ($urlIn !== '') {
        if (filter_var($urlIn, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $urlIn)) {
            $image = $urlIn;
        } else {
            $errors[] = 'Image URL is not valid.';
        }
    }

    if (!$errors) {
        if ($type === 'wallpaper') {
            $p = (float) $price;
            $cols = ['name' => [$name, 's'], 'price' => [$p, 'd'], 'image' => [$image, 's']];
            if (isset($wpCols['color']))     $cols['color']     = [$color, 's'];
            if (isset($wpCols['color_hex'])) $cols['color_hex'] = [$colorHex, 's'];
            if (isset($wpCols['badge']))     $cols['badge']     = [($badge === '' && $wpCols['badge']) ? null : $badge, 's'];
            // CHANGE: save the wallpaper's stock
            if (isset($wpCols['stock']))     $cols['stock']     = [(int) $stock, 'i'];

            $setSql = implode(', ', array_map(fn($c) => "$c = ?", array_keys($cols)));
            $u = mysqli_prepare($conn, "UPDATE wallpapers SET $setSql WHERE id = ?");
            $btypes = implode('', array_column($cols, 1)) . 'i';
            $bvals  = array_column($cols, 0);
            $bvals[] = $id;
            mysqli_stmt_bind_param($u, $btypes, ...$bvals);
        } else {
            $u = mysqli_prepare($conn, "UPDATE products
                                        SET name = ?, categories_id = ?, price = ?, stock = ?, description = ?, image = ?
                                        WHERE product_id = ?");
            $p = (float) $price;
            $s = (int) $stock;
            mysqli_stmt_bind_param($u, "sidissi", $name, $catId, $p, $s, $desc, $image, $id);
        }

        if (mysqli_stmt_execute($u)) {
            header('Location: products.php?status=updated');
            exit;
        }
        $errors[] = 'Database error: could not save changes.';
    }

    // Keep what the admin typed so the form doesn't reset on error
    $item['name']  = $name;
    $item['price'] = $price;
    if ($type === 'wallpaper') {
        $item['color']     = $color;
        $item['color_hex'] = $colorHex;
        $item['badge']     = $badge;
    }
    if ($hasStock) {
        $item['stock'] = $stock;
    }
    if ($type === 'product') {
        $item['description']   = $desc;
        $item['categories_id'] = $catId;
    }
}

$current_page = "products";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product - Sweet Haven Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    :root{
        --cream:#FBF5EE; --wine:#5c1a2e; --wine-dark:#45121f; --gold:#C9A25F;
        --blush:#f3e4dc; --bark:#4a3728; --coral:#B44446;
        --font-display:'Cormorant Garamond',serif; --font-body:'Jost',sans-serif;
    }
    .main-content{ padding:32px 36px; min-width:0; }
    .page-title{ font-family:var(--font-display); font-size:2.2rem; font-weight:600; color:var(--wine); }
    .breadcrumb{ font-size:.85rem; color:#8a7a6d; margin:.2rem 0 1.4rem; }
    .form-layout{ display:grid; grid-template-columns:1fr 300px; gap:1.6rem; align-items:start; }
    .form-card, .side-card{ background:#fff; border:1px solid #eadfd2; border-radius:14px; padding:1.6rem; }
    .field{ margin-bottom:1.1rem; }
    .field label{ display:block; font-size:.82rem; color:var(--bark); margin-bottom:.35rem; font-weight:500; }
    .field input[type=text], .field input[type=number], .field input[type=url], .field select, .field textarea{
        width:100%; padding:.65rem .85rem; border:1px solid #e0d2c3; border-radius:.6rem; font-family:var(--font-body); font-size:.92rem; box-sizing:border-box; }
    .field textarea{ min-height:130px; resize:vertical; }
    .row2{ display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .hint{ font-size:.75rem; color:#8a7a6d; margin-top:.3rem; }
    .preview{ width:100%; aspect-ratio:1/1; border-radius:12px; background:#e6dbd0; overflow:hidden; margin-bottom:.8rem; }
    .preview img{ width:100%; height:100%; object-fit:cover; }
    .actions{ display:flex; gap:.8rem; margin-top:1.2rem; }
    .btn{ padding:.7rem 1.5rem; border-radius:999px; font-family:var(--font-body); font-weight:600; font-size:.9rem; cursor:pointer; border:none; text-decoration:none; display:inline-block; }
    .btn-save{ background:var(--wine); color:#fff; }
    .btn-save:hover{ background:var(--coral); }
    .btn-cancel{ background:#fff; color:var(--wine); border:1.5px solid var(--wine); }
    .alert-error{ background:#FFF0F0; border:1px solid var(--coral); color:var(--coral); padding:.8rem 1.1rem; border-radius:.6rem; margin-bottom:1.3rem; font-size:.9rem; }
    .alert-error ul{ margin:.3rem 0 0 1.1rem; padding:0; }
    .swatches{ display:flex; flex-wrap:wrap; gap:.5rem; margin:.4rem 0 .8rem; }
    .swatch{ display:flex; align-items:center; gap:.4rem; padding:.3rem .8rem .3rem .4rem; border:1px solid #e0d2c3; background:#fff; border-radius:999px; cursor:pointer; font-family:var(--font-body); font-size:.82rem; color:var(--bark); }
    .swatch:hover{ border-color:var(--wine); }
    .swatch.sel{ background:var(--wine); color:#fff; border-color:var(--wine); }
    .swatch .dot{ width:18px; height:18px; border-radius:50%; border:1px solid rgba(0,0,0,.15); display:inline-block; }
    .color-row{ display:flex; gap:.8rem; align-items:center; }
    .color-row input[type=color]{ width:52px; height:42px; padding:2px; border:1px solid #e0d2c3; border-radius:.6rem; background:#fff; cursor:pointer; }
    @media (max-width:900px){ .admin-layout{ grid-template-columns:1fr; } .form-layout{ grid-template-columns:1fr; } }
</style>
</head>
<body>

<div class="admin-layout">

    <?php include "admin_sidebar.php"; ?>

    <main class="main-content">
        <div class="page-title">Edit <?php echo $type === 'wallpaper' ? 'Wallpaper' : 'Product'; ?></div>
        <div class="breadcrumb"><a href="products.php">Products</a> &gt; Edit</div>

        <?php if ($errors): ?>
            <div class="alert-error">Please fix the following:
                <ul><?php foreach ($errors as $e) echo '<li>' . h($e) . '</li>'; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="form-layout">
                <div class="form-card">

                    <div class="field">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo h($item['name']); ?>" required>
                    </div>

                    <div class="row2">
                        <div class="field">
                            <label for="price">Price (Rs.) *</label>
                            <input type="number" step="0.01" min="0" id="price" name="price" value="<?php echo h($item['price']); ?>" required>
                        </div>

                        <!-- CHANGE: stock box now appears for wallpapers too -->
                        <?php if ($hasStock): ?>
                        <div class="field">
                            <label for="stock">Stock Quantity *</label>
                            <input type="number" min="0" id="stock" name="stock" value="<?php echo h($item['stock'] ?? 0); ?>" required>
                            <div class="hint">Set to 0 to mark as out of stock; raise it to restock.</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($type === 'product'): ?>
                    <div class="field">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">None</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo (int)$c['categories_id']; ?>"
                                    <?php echo ((int)$item['categories_id'] === (int)$c['categories_id']) ? 'selected' : ''; ?>>
                                    <?php echo h($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo h($item['description']); ?></textarea>
                    </div>
                    <?php endif; ?>

                    <?php if ($type === 'wallpaper' && (isset($wpCols['color']) || isset($wpCols['color_hex']) || isset($wpCols['badge']))): ?>
                    <div class="field">
                        <label>Colour</label>
                        <div class="swatches" id="swatches"></div>
                        <div class="color-row">
                            <?php if (isset($wpCols['color_hex'])): ?>
                                <input type="color" id="colorPicker" name="color_hex"
                                       value="<?php echo h(preg_match('/^#[0-9a-fA-F]{6}$/', $item['color_hex'] ?? '') ? $item['color_hex'] : '#cccccc'); ?>">
                            <?php endif; ?>
                            <?php if (isset($wpCols['color'])): ?>
                                <input type="text" id="colorName" name="color" placeholder="Colour name, e.g. Sage Green"
                                       value="<?php echo h($item['color'] ?? ''); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="hint">Pick a preset above or choose any colour and type its name. This is what customers see next to the wallpaper.</div>
                    </div>
                    <?php endif; ?>

                    <?php if ($type === 'wallpaper' && isset($wpCols['badge'])): ?>
                    <div class="field">
                        <label for="badge">Badge</label>
                        <select id="badge" name="badge">
                            <option value="">None</option>
                            <?php foreach ($badgeOptions as $b): ?>
                                <option value="<?php echo $b; ?>" <?php echo (($item['badge'] ?? '') === $b) ? 'selected' : ''; ?>><?php echo $b; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="field">
                        <label>Replace image (optional)</label>
                        <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp">
                        <div class="hint">JPG, PNG or WEBP, max 5MB. Or paste a URL instead:</div>
                        <input type="url" name="image_url" placeholder="https://..." style="margin-top:.5rem;">
                        <div class="hint">Leave both empty to keep the current image.</div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-save">Save Changes</button>
                        <a href="products.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>

                <div class="side-card">
                    <label style="font-size:.82rem;font-weight:500;display:block;margin-bottom:.5rem;">Current image</label>
                    <div class="preview">
                        <img src="<?php echo h(img_src($item['image'])); ?>" alt="" onerror="this.style.visibility='hidden'">
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
// Preset colour chips: click one to fill both the colour picker and the name
(function () {
    var box = document.getElementById('swatches');
    if (!box) return;
    var picker = document.getElementById('colorPicker');
    var nameIn = document.getElementById('colorName');
    var presets = [
        ['Gold','#E8C468'], ['Pink','#F4B6B6'], ['Red','#8B2E2E'], ['Green','#8A9A6B'],
        ['Blue','#7FA6C9'], ['Beige','#DCCBB0'], ['Cream','#F3EBD8'], ['Grey','#A7A7A7'],
        ['Brown','#7A5539'], ['Black','#222222'], ['White','#FFFFFF']
    ];
    presets.forEach(function (p) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'swatch';
        b.dataset.name = p[0];
        b.innerHTML = '<span class="dot" style="background:' + p[1] + '"></span>' + p[0];
        b.addEventListener('click', function () {
            if (picker) picker.value = p[1];
            if (nameIn) nameIn.value = p[0];
            mark();
        });
        box.appendChild(b);
    });
    function mark() {
        var cur = nameIn ? nameIn.value.trim().toLowerCase() : '';
        box.querySelectorAll('.swatch').forEach(function (b) {
            b.classList.toggle('sel', b.dataset.name.toLowerCase() === cur);
        });
    }
    if (nameIn) nameIn.addEventListener('input', mark);
    mark();
})();
</script>
</body>
</html>