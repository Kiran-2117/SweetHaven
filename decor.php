<?php
require_once __DIR__."/header.php";
include 'database/db_connect.php';

$sql= "SELECT * FROM categories ORDER BY categories_id ASC";
$result= mysqli_query($conn, $sql);

if (!$result) {
    die(mysqli_error($conn));
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
<title>Shop by Category</title>
</head>
<body>
    
    <div class="page-header">
        <div class="brand">Sweet Haven</div>
        <h1>Shop by Category</h1>
        <p>Handpicked decor pieces, sorted just the way you like</p>


    <div class="container">
        <?php if(mysqli_num_rows($result)==0) { ?>
            <div class="empty-state">
                <h2>No categories yet</h2>
                <p>Add rows to the categories table to see them here.</p>
            </div>

        <?php } else { ?>
            <div class="category-grid">
                <?php 
                $i= 0;
                while($cat= mysqli_fetch_assoc($result)){
                    $i++;
                    $number= $i < 10 ?"0" . $i: $i;
                    ?>
                    
        <a class="category-card" href="category_products.php?category=<?php echo urlencode($cat['slug']);?>">

        <img src="<?php echo $cat['image']; ?>" alt="<?php echo $cat['name']; ?>"
        onerror= "this.style.display='none'; this.nextElementSibling.style.display='flex';">

        <div class="img-fallback"><?php echo $cat['name']; ?></div>
        <div class="category_overlay">
            <span class="category-tag">Category <?php echo $number; ?></span>

            <h3><?php echo $cat['name']; ?></h3>
            <?php if (!empty($cat['description'])) { ?>
        <p><?php echo htmlspecialchars($cat['description']); ?></p>
    <?php } ?>
        </div>
</a>
          <?php } ?>
            </div>
       <?php } ?>
    </div>
</div>

    <?php 
    require_once __DIR__."/footer.php";
    ?>

</body> 
</html>