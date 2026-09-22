<?php
require_once __DIR__."/header.php";
include 'database/db_connect.php';


$current_slug = isset($_GET['style']) ? $_GET['style'] : 'classic';
$current_slug = mysqli_real_escape_string($conn, $current_slug);

// ---------------------------------------------------------
// 2. Get all the styles (for the tab bar)
// ---------------------------------------------------------
$styles = [];
$styles_result = mysqli_query($conn, "SELECT collection_id, name, slug, description FROM collections ORDER BY collection_id ASC");
if ($styles_result) {
    while ($row = mysqli_fetch_assoc($styles_result)) {
        $styles[] = $row;
    }
}

// Find the full info for the style currently selected
$active_style = null;
foreach ($styles as $s) {
    if ($s['slug'] === $current_slug) {
        $active_style = $s;
        break;
    }
}

// ---------------------------------------------------------
// 3. Get all products + wallpapers that belong to this style
// ---------------------------------------------------------
$items = [];
if ($active_style) {
    $collection_id = (int) $active_style['collection_id'];

    $sql = "
        SELECT p.product_id AS item_id, p.name, p.price, p.image, 'product' AS item_type
        FROM products p
        JOIN product_collections pc ON pc.product_id = p.product_id AND pc.product_type = 'product'
        WHERE pc.collection_id = $collection_id

        UNION ALL

        SELECT w.id AS item_id, w.name, w.price, w.image, 'wallpaper' AS item_type
        FROM wallpapers w
        JOIN product_collections pc ON pc.product_id = w.id AND pc.product_type = 'wallpaper'
        WHERE pc.collection_id = $collection_id
    ";

    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Collections | Sweet Haven</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Same stylesheet as category_products.php -- this is what keeps every
     product card on the site (decor, wallpapers, collections) identical.
     Do not add a competing <style> block with its own .product-card rules
     here again, or the two pages will drift apart visually like before. -->
<link rel="stylesheet" href="assets/css/decor.css">

<style>
    /* Only page-specific bits that decor.css doesn't already cover. */
    .style-tabs {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 20px 20px 0;
    }
    .style-tabs a {
        text-decoration: none;
        padding: 10px 22px;
        border: 1px solid var(--gold);
        border-radius: 25px;
        color: var(--wine);
        font-weight: 500;
        background: #fff;
        transition: 0.2s;
    }
    .style-tabs a.active,
    .style-tabs a:hover {
        background: var(--wine);
        color: #fff;
        border-color: var(--wine);
    }
    .style-description {
        text-align: center;
        color: var(--clay);
        font-style: italic;
        margin: 20px 0 0;
    }
</style>
</head>
<body>

<div class="page-header">
    <div class="brand">Sweet Haven</div>
    <h1>Shop by Style</h1>
    <p>Find pieces that match your favorite aesthetic</p>
</div>

<div class="style-tabs">
    <?php foreach ($styles as $s): ?>
        <a href="collections.php?style=<?php echo htmlspecialchars($s['slug']); ?>"
           class="<?php echo ($s['slug'] === $current_slug) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($s['name']); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($active_style && $active_style['description']): ?>
    <p class="style-description"><?php echo htmlspecialchars($active_style['description']); ?></p>
<?php endif; ?>

<div class="container">

    <?php if (count($items) > 0): ?>
        <div class="product-grid">
            <?php foreach ($items as $item): ?>
                <?php
                    // send to the right detail page depending on type
                    $link = ($item['item_type'] === 'wallpaper')
                        ? "wallpaper_details.php?id=" . $item['item_id']
                        : "product.php?id=" . $item['item_id'];
                ?>
                <div class="product-card" data-price="<?php echo $item['price']; ?>">

                    <a href="<?php echo $link; ?>" class="product-link">
                        <div class="product-image-wrap">
                            <img
                                src="<?php echo htmlspecialchars($item['image']); ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="img-fallback"><?php echo htmlspecialchars($item['name']); ?></div>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        </div>
                    </a>

                    <div class="product-bottom">
                        <div class="product-price"><?php echo number_format($item['price'], 2); ?></div>
                        <button type="button" class="btn-cart-pill add-to-cart-btn"
                            data-id="<?php echo $item['item_id']; ?>"
                            data-type="<?php echo $item['item_type']; ?>">
                            <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No products added to this style yet</h2>
            <p>Go to the Admin Panel &rarr; Add Product to assign items to this collection.</p>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__."/footer.php"; ?>

</body>
</html>