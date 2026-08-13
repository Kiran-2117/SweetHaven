<?php
include 'database/db_connect.php';

if (isset($_POST['submit_review'])) {

    $wallpaperId  = (int) $_POST['product_id'];
    $reviewerName = mysqli_real_escape_string($conn, $_POST['reviewer_name']);
    $rating       = (int) $_POST['rating'];
    $comment      = mysqli_real_escape_string($conn, $_POST['comment']);

    $insertSql = "INSERT INTO reviews (product_type, product_id, reviewer_name, rating, comment)
                  VALUES ('wallpaper', '$wallpaperId', '$reviewerName', '$rating', '$comment')";
    mysqli_query($conn, $insertSql);

    header("Location: wallpaper_details.php?id=$wallpaperId#reviews");
    exit;
}

require_once __DIR__."/header.php";


$wallpaperId = isset($_GET['id']) ? $_GET['id'] : "";
$wallpaperId = mysqli_real_escape_string($conn, $wallpaperId);


echo "<p style='background:yellow;padding:10px;'>DEBUG: Looking for wallpaper id = '" . htmlspecialchars($wallpaperId) . "'</p>";


$wallpaper = null;

if ($wallpaperId != "") {
    $sql = "SELECT * FROM wallpapers WHERE id = '$wallpaperId'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("SQL ERROR (wallpaper lookup): " . mysqli_error($conn));
    }

    echo "<p style='background:yellow;padding:10px;'>DEBUG: Query found " . mysqli_num_rows($result) . " matching row(s).</p>";


    $wallpaper = mysqli_fetch_assoc($result);
}
if (!$wallpaper && $wallpaperId != "") {
    $sampleFile = __DIR__ . 'database/sample-featured-data.php';
    if (file_exists($sampleFile)) {
        include $sampleFile;
        foreach ($sampleFeaturedProducts as $sampleItem) {
            if ($sampleItem['product_type'] === 'wallpaper' && (int) $sampleItem['id'] === (int) $wallpaperId) {
                $wallpaper = [
                    'id'          => $sampleItem['id'],
                    'name'        => $sampleItem['name'],
                    'image'       => $sampleItem['image'],
                    'price'       => $sampleItem['price'],
                    'color'       => $sampleItem['color_name'],
                    'color_hex'   => $sampleItem['color_hex'],
                    'description' => $sampleItem['description'] ?? '',
                ];
                break;
            }
        }
    }
}

$avgRating = 0;
$totalReviews = 0;
$reviewsResult = null;

if ($wallpaper) {
    $avgSql = "SELECT AVG(rating) AS avgRating, COUNT(*) AS totalReviews
               FROM reviews WHERE product_type = 'wallpaper' AND product_id = '$wallpaperId'";
    $avgResult = mysqli_query($conn, $avgSql);
    $avgRow = mysqli_fetch_assoc($avgResult);
    $avgRating = $avgRow['avgRating'] ? round($avgRow['avgRating'], 1) : 0;
    $totalReviews = (int) $avgRow['totalReviews'];

    $reviewsSql = "SELECT * FROM reviews WHERE product_type = 'wallpaper' AND product_id = '$wallpaperId'
                   ORDER BY created_at DESC";
    $reviewsResult = mysqli_query($conn, $reviewsSql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/product.css">
    <title><?php echo $wallpaper ? $wallpaper['name'] : 'Wallpaper Not Found'; ?></title>
</head>
<body>

<div class="container">

 <?php if (!$wallpaper) { ?>

 <div class="empty-state">
     <h2>Wallpaper not found</h2>
     <p>This wallpaper may have been removed. Please go back and pick another.</p>
     <a class="back-link" href="wallpaper.php">&larr; Back to wallpapers</a>
 </div>

    <?php } else { ?>

 <div class="breadcrumb-plain">
     <a href="wallpaper.php">Wallpapers</a> /
     <span><?php echo $wallpaper['name']; ?></span>
 </div>

   <div class="product-detail">

<div class="product-detail-image">
    <img
        src="<?php echo $wallpaper['image']; ?>"
        alt="<?php echo $wallpaper['name']; ?>"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
    <div class="img-fallback"><?php echo $wallpaper['name']; ?></div>
</div>

<div class="product-detail-info">

<h1><?php echo $wallpaper['name']; ?></h1>
<span class="eyebrow">Wallpaper Collection</span>
<div class="detail-meta-row">
    <span class="color-dot" style="background:<?php echo htmlspecialchars($wallpaper['color_hex']); ?>"></span>
    <?php echo $wallpaper['color']; ?>

    <span class="rating-stars">
        <?php echo str_repeat('★', round($avgRating)) . str_repeat('☆', 5 - round($avgRating)); ?>
        <?php echo $avgRating > 0 ? $avgRating : 'No ratings yet'; ?>
    </span>
    <?php if ($totalReviews > 0) { ?>
        <span class="review-count-inline">(<?php echo $totalReviews; ?> review<?php echo $totalReviews != 1 ? 's' : ''; ?>)</span>
    <?php } ?>
</div>

    <div class="product-detail-price"><?php echo number_format($wallpaper['price'], 2); ?></div>

    <span class="craft-badge"><span class="dot"></span> Hand-printed by Kathmandu artisans</span>

    <?php if (isset($wallpaper['description']) && $wallpaper['description'] != "") { ?>
        <p class="product-detail-desc"><?php echo $wallpaper['description']; ?></p>
    <?php } ?>

    <div class="qty-row">
        <label for="qty">Quantity:</label>
        <input type="number" id="qty" name="qty" value="1" min="1">
    </div>

    <div class="product-detail-actions">
        <form action="cart.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $wallpaper['id']; ?>">
            <input type="hidden" name="name" value="<?php echo htmlspecialchars($wallpaper['name']); ?>">
            <input type="hidden" name="redirect" value="wallpaper_details.php?id=<?php echo $wallpaper['id']; ?>">
            <button type="submit" class="btn btn-secondary btn-large"><i class="fa-solid fa-bag-shopping"></i>  Add to Cart</button>
        </form>

          <form action="checkout.php" method="POST">
              <input type="hidden" name="id" value="<?php echo $wallpaper['id']; ?>">
              <button type="submit" class="btn btn-primary btn-large">Buy Now</button>
          </form>
      </div>
 </div>
        </div>
         <div class="stitch-divider"></div>

        
    <div class="reviews-section" id="reviews">

     <h2>Ratings &amp; Reviews</h2>

    <div class="reviews-summary">
        <span class="avg-rating-big"><?php echo $avgRating > 0 ? $avgRating : '0'; ?> &#9733;</span>
        <span class="review-count"><?php echo $totalReviews; ?> review<?php echo $totalReviews != 1 ? 's' : ''; ?></span>
    </div>

<div class="review-list">
    <?php if ($totalReviews == 0) { ?>
        <p class="no-reviews">No reviews yet. Be the first to review this wallpaper!</p>
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
    <?php } ?>
</div>

    <div class="add-review-form">
        <h3>Write a Review</h3>
        <form method="POST" action="wallpaper_details.php?id=<?php echo $wallpaperId; ?>#reviews">
            <input type="hidden" name="product_id" value="<?php echo $wallpaperId; ?>">

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

<?php require_once __DIR__."/footer.php"; ?>

</body>
</html>