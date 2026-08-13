<?php
include 'database/db_connect.php';

if (isset($_POST['submit_review'])) {

    $productId     = (int) $_POST['product_id'];
    $reviewerName  = mysqli_real_escape_string($conn, $_POST['reviewer_name']);
    $rating        = (int) $_POST['rating'];
    $comment       = mysqli_real_escape_string($conn, $_POST['comment']);

    $insertSql = "INSERT INTO reviews (product_type, product_id, reviewer_name, rating, comment)
                  VALUES ('decor', '$productId', '$reviewerName', '$rating', '$comment')";
    mysqli_query($conn, $insertSql);

    header("Location: product.php?id=$productId#reviews");
    exit;
}

require_once __DIR__."/header.php";


$productId = isset($_GET['id']) ? $_GET['id'] : "";
$productId = mysqli_real_escape_string($conn, $productId);

$product = null;
$category = null;

if ($productId != "") {
    $sql = "SELECT * FROM products WHERE product_id = '$productId'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("SQL ERROR (product lookup): " . mysqli_error($conn));
    }

    $product = mysqli_fetch_assoc($result);

    if ($product) {
        $catId = $product['categories_id'];
        $catSql = "SELECT * FROM categories WHERE categories_id = '$catId'";
        $catResult = mysqli_query($conn, $catSql);
        $category = mysqli_fetch_assoc($catResult);
    }
}


$avgRating = 0;
$totalReviews = 0;
$reviewsResult = null;

if ($product) {
    $avgSql = "SELECT AVG(rating) AS avgRating, COUNT(*) AS totalReviews
               FROM reviews WHERE product_type = 'decor' AND product_id = '$productId'";
    $avgResult = mysqli_query($conn, $avgSql);
    $avgRow = mysqli_fetch_assoc($avgResult);
    $avgRating = $avgRow['avgRating'] ? round($avgRow['avgRating'], 1) : 0;
    $totalReviews = (int) $avgRow['totalReviews'];

    $reviewsSql = "SELECT * FROM reviews WHERE product_type = 'decor' AND product_id = '$productId'
                   ORDER BY created_at DESC";
    $reviewsResult = mysqli_query($conn, $reviewsSql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/product.css">
    <title><?php echo $product ? $product['name'] : 'Product Not Found'; ?></title>
</head>
<body>
    <div class="container">
        <?php if(!$product) { ?>
    <div class="empty-state">
        <h2>Product not found</h2>
        <p>This product may have been removed. Please go back and pick another.</p>
        <a class="back-link" href="decor.php">&larr; Back to categories</a>
    </div>

    <?php } else { ?>
        <?php if($category) { ?>
        <div class="breadcrumb-plain">
            <a href="decor.php">Shop by Category</a> /
            <a href="category_products.php?category=<?php echo urlencode($category['slug']); ?>"><?php echo $category['name']; ?></a> /
            <span><?php echo $product['name'];?></span>
        </div>
    <?php } ?>

    <div class="product-detail">
        <div class="product-detail-image">
        <img src="<?php echo $product['image']; ?>" 
        alt="<?php echo $product['name']; ?>"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

        <div class="img-fallback"><?php echo $product['name']; ?></div>
        </div>

    <div class="product-detail-info">
        <span class="eyebrow"><?php echo $category ? htmlspecialchars($category['name']) : 'Sweet Haven Décor'; ?></span>
        <h1><?php echo $product['name']; ?></h1>

        <div class="detail-meta-row">
            <span class="rating-stars">
                <?php echo str_repeat('★', round($avgRating)) . str_repeat('☆', 5 - round($avgRating)); ?>

            <?php echo $avgRating > 0 ? $avgRating : 'No ratings yet'; ?>
            </span>

            <?php if ($totalReviews > 0) { ?>
            <span class="review-count-inline">(<?php echo $totalReviews; ?> review<?php echo $totalReviews != 1 ? 's' : ''; ?>)</span>
        <?php } ?>
        </div>

    <div class="product-detail-price">
        <?php echo number_format($product['price'], 2); ?>
    </div>

    <span class="stock-tag">
        <?php echo $product['stock'] > 0 ? $product['stock'] . " in stock" : "Out of stock"; ?>
    </span>
<br>
<span class="craft-badge"><span class="dot"></span> Handcrafted by Kathmandu artisans</span>


    <?php if($product['description']!= ""){?>
    <p class="product-detail-desc"><?php echo $product['description']; ?></p>
<?php } ?>

<div class="qty-row">
    <label for="qty">Quantity</label>
    <input type="number" name="qty" id="qty" value="1" min="1" max="<?php echo (int)$product['stock']; ?>">
</div>

<div class="product-detail-actions">
    <form action="cart.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        <input type="hidden" name="qty" id="qtyMirror1" value="1">
        <button type="submit" class="btn btn-secondary btn-large"><i class="fa-solid fa-bag-shopping"></i>  Add to Cart</button>
    </form>

    <form action="checkout.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        <input type="hidden" name="qty" id="qtyMirror2" value="1">

    <button type="submit" class="btn btn-primary btn-large">Buy Now</button>
    </form>
</div>
    </div>
    </div>

    <div class="reviews-section" id="reviews">
        <h2>Ratings &amp; Reviews</h2>
        <div class="reviews-summary">
            <span class="avg-rating-big"><?php echo $avgRating > 0 ? $avgRating : '0'; ?>&#9733;</span>
            <span class="review-count"><?php echo $totalReviews; ?> review<?php echo $totalReviews != 1 ? 's' : ''; ?></span>
        </div>


    <div class="review-list">
        <?php if ($totalReviews == 0) { ?>
        <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
       <?php } else { ?>
    <?php while ($review = mysqli_fetch_assoc($reviewsResult)) { ?>


<div class="review-card">
<div class="review-stars">
<?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
</div>

<div class="review-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></div>
    <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
    <span class="review-date"><?php echo date("M j, Y", strtotime($review['created_at'])); ?></span>
</div>
    <?php } ?>
     <?php  } ?>
    </div>

    <div class="add-review-form">
        <h3>Write a Review</h3>
        <form method="POST" action="product.php?id=<?php echo $productId; ?>#reviews">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            
            <label for="reviewer_name">Your Name</label>
               <input type="text" id="reviewer_name" name="reviewer_name" required>

    <label for="rating">Rating</label>
        <select id="rating" name="rating" required>
            <option value="5">★★★★★ - Excellent</option>
            <option value="4">★★★★☆ - Good</option>
            <option value="3">★★★☆☆ - Average</option>
            <option value="2">★★☆☆☆ - Poor</option>
            <option value="1">★☆☆☆☆ - Bad</option>
        </select>

    <label for="comment">Your Review</label>
       <textarea id="comment" name="comment" rows="4" required></textarea>

    <button type="submit" name="submit_review" class="btn btn-primary btn-large">Submit Review</button>
        </form>
    </div>
    </div>
    <?php } ?>
    </div>


<script>
    const qtyInput = document.getElementById('qty');
    if (qtyInput) {
        qtyInput.addEventListener('change', function() {
            document.getElementById('qtyMirror1').value = qtyInput.value;
            document.getElementById('qtyMirror2').value = qtyInput.value;
        });
    }
</script>

<?php
require_once __DIR__."/footer.php";
?>
</body>
</html>