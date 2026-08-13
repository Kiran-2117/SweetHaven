<?php
require_once __DIR__."/header.php";
include 'database/db_connect.php';

$categorySlug= isset($_GET['category'])? $_GET['category']: "";

$categorySlug= mysqli_real_escape_string($conn, $categorySlug);

$currentCategory = null;
$products= null;

if($categorySlug!= ""){
    $catSql= "SELECT * FROM categories WHERE slug= '$categorySlug'";

    $catResult= mysqli_query($conn, $catSql);

    if(!$catResult) {
        die("SQL ERROR (category lookup): " . mysqli_error($conn));
    }

    $currentCategory= mysqli_fetch_assoc($catResult);

    if($currentCategory){
        $catId= $currentCategory['categories_id'];
        $prodSql= "SELECT * FROM products WHERE categories_id= '$catId' ORDER BY product_id DESC";
        $products= mysqli_query($conn, $prodSql);

        if (!$products) {
            die("SQL ERROR (products lookup): " . mysqli_error($conn));
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/decor.css">     
<title>Categroy products</title>
</head>
<body>
    <div class="page-header">
    <div class="brand">Sweet Haven</div>
 
    <?php if ($currentCategory) {    ?>

    <h1><?php echo $currentCategory['name']; ?></h1>
    <?php if ($currentCategory['description'] != "") { ?>
        <p><?php echo $currentCategory['description']; ?></p>
    <?php } ?>

    <div class="breadcrumb">
        <a href="decor.php">Shop by Category</a>
        <span>- <?php echo $currentCategory['name']; ?></span>
    </div>


    <?php } else { ?>

        <h1>Category Not Found</h1>
        <p>The category you're looking for doesn't exist</p>
    <?php } ?>

   

<div class="container">
 
    <a class="back-link" href="decor.php">&larr; Back to all categories</a>
 
    <?php if (!$currentCategory) { ?>
 
    <div class="empty-state">
        <h2>Hmm, we couldn't find that category</h2>
        <p>Please go back and choose a category from the list.</p>
    </div>
 
    <?php } else if (mysqli_num_rows($products) == 0) { ?>
 
        <div class="empty-state">
            <h2>No products yet</h2>
            <p>We're adding new <?php echo $currentCategory['name']; ?> soon. Check back later!</p>
        </div>
 
    <?php } else { ?>
 
 
<div class="product-grid">
    <?php while ($product = mysqli_fetch_assoc($products)) { ?>
    <div class="product-card" data-price="<?php echo $product['price']; ?>">

        <a href="product.php?id=<?php echo $product['product_id']; ?>" class="product-link">
            <div class="product-image-wrap">
                <img
                    src="<?php echo $product['image']; ?>"
                    alt="<?php echo $product['name']; ?>"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="img-fallback"><?php echo $product['name']; ?></div>
            </div>
            <div class="product-info">
                <h3><?php echo $product['name']; ?></h3>
            </div>
        </a>

        <div class="product-bottom">
            <div class="product-price"><?php echo number_format($product['price'], 2); ?></div>
            <form action="cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <button type="submit" class="btn-cart-pill">
                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                </button>
            </form>
        </div>

    </div>
   <?php } ?>
    </div>
 
   <?php } ?>
 
</div>
</div>
 </div>
<?php require_once __DIR__."/footer.php";
?>

</body>
</html>