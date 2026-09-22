<?php
require_once __DIR__."/header.php";
include 'database/db_connect.php';


$colorList = $colorList ?? [];
$allwallpapers = $allwallpapers ?? [];
$wallpapers = $wallpapers ?? [];
$selectedColor = $selectedColor ?? '';
$backToHere = $backToHere ?? 'wallpaper.php';
?>

<?php
$allwallpapers= [];

if(isset($conn) && $conn) {
  $result= mysqli_query($conn, "SELECT * FROM wallpapers ORDER BY created_at DESC");

  if($result && mysqli_num_rows($result) > 0){
    while($rows= mysqli_fetch_assoc($result)){
      $allwallpapers[] =[
        'id' => (int) $rows['id'],
        'name' => $rows['name'],
        'color' => $rows['color'],
        'colorHex' => $rows['color_hex'],
        'price' => (float)$rows['price'],
        'rating' => (float)$rows['rating'],
        'image' => $rows['image'],
        'badge' => $rows['badge'],
      ];
    }
  }
}
if (empty($allwallpapers)){
  $sampleData = @include __DIR__ . '/sample-data.php';
    if (is_array($sampleData) && !empty($sampleData)) {
        $allwallpapers = $sampleData;
    }
  
}

$colorList = [
  ['name' => 'Red', 'hex' => '#862f31'],
  ['name' => 'Blue', 'hex' => '#83b4cb'],
  ['name' => 'Yellow', 'hex' => '#e8e668'],
  ['name' => 'Lavender', 'hex' => '#C9B7DA'],
  ['name' => 'Green', 'hex' => '#5b9c6b'],
  ['name' => 'Pink', 'hex' => '#ffaaaa'],
  ['name' => 'Orange', 'hex' => '#D97A4B'],
  ['name' => 'Beige', 'hex' => '#CCBEB1'],
  ['name' => 'Gold', 'hex' => '#fad460'],

];

$selectedColor= isset($_GET['color'])? trim($_GET['color']): '';

$wallpapers = $allwallpapers;

if($selectedColor !==''){
  $wallpapers= array_filter($allwallpapers, function ($w) use ($selectedColor){
    return strtolower($w['color']) == strtolower($selectedColor);
  });
  $wallpapers = array_values($wallpapers);
}


$backToHere= 'wallpaper.php'. ($selectedColor !== ''? '?color='. urlencode($selectedColor): '');
?> 


<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
     <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
     <link rel="stylesheet" href="./wallpaper.css">
     <title>Wallpaper</title>
 </head>
<body>
  <section class="wp-cero">
     <div class="wp-cero__media" style="background-image:url('https://i.pinimg.com/1200x/ec/d4/54/ecd45496b6780857452edeb06ea91702.jpg');">
     </div>
 <div class="wp-cero__scrim"></div>
<div class="wp-cero__content">
  <h1 class="wall-title">Wallapaper</h1>
  <p class="wall-subtitle">Patterns woven with warmth, for walls that feels like home.</p>
</div>
</section>

<section class="wall-split">
  <aside class="wall-colors" id="wallColors">
    <h2 class="color-list">Colors</h2>
    <ul class="wp-colors__list">

<?php foreach ($colorList as $c):?>
  <?php
$count = count(array_filter($allwallpapers, fn($w) => $w['color'] === $c['name']));
  $isActive= $selectedColor === $c['name'];
  ?>

<li>
  <a href="wallpaper.php?color=<?php echo urlencode($c['name']); ?>"
     class="wp-color-items <?php echo $isActive? 'is-active': '';?>">

      <span class="wp-color-item__dot" style="background:<?php echo htmlspecialchars($c['hex']); ?>"></span>
      <?php echo htmlspecialchars($c['name']);?>

       <!-- <span class="wp-color-item__count"><?php echo $count; ?></span> -->
     </a>
     </li>

     <?php endforeach; ?>
     </ul>
     <a href="wallpaper.php" class="wp-colors__clears">Show All </a>
     </aside>


 <div class="wall-products">
  


   <div class="wp-products__scroll">
     <?php if (empty($wallpapers)): ?>

      <div class="wp-empty">
        <i class="fa-regular fa-image"></i>
        <h3>No wallapapers in this color yet.</h3>
        <p>Try another color, or view the full collection.</p>
        <a href="wallpaper.php" class="wp-colors__clear">Show All</a>
      </div>

       <?php else:?>
         <?php foreach ($wallpapers as $w): ?>
       <?php
       $fullStars = (int) round($w['rating']);
       $starString = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
       $badgeLabel = $w['badge'] === 'new' ? 'New' : ($w['badge'] === 'bestseller' ? 'Bestseller' : '');
           ?>


 <article class="wp-card">
  <a href="wallpaper_details.php?id=<?php echo $w['id']; ?>" class="product-link">
     <div class="wp-card__media">
       <?php if ($badgeLabel !== ''): ?>
         <span class="wp-card__badge<?php echo $w['badge'] === 'new' ? ' is-new' : ''; ?>"><?php echo $badgeLabel; ?></span>
      <?php endif; ?>

        
      <img src="<?php echo htmlspecialchars($w['image']); ?>" alt="<?php echo htmlspecialchars($w['name']); ?> wallpaper" loading="lazy">
 
   </div>

   <div class="wp-card__body">
       <h3 class="wp-card__name"><?php echo htmlspecialchars($w['name']); ?></h3>
       </a>
       <div class="wp-card__meta">
           <span class="wp-card__color">
             <span class="wp-card__color-dot" style="background:<?php echo htmlspecialchars($w['colorHex']); ?>"></span>
        <?php echo htmlspecialchars($w['color']); ?>
           </span>
     <span class="wp-card__rating"><?php echo $starString; ?> <span><?php echo number_format($w['rating'], 1); ?></span></span>
   </div>


    <div class="wp-card__footer">
        <span class="wp-card__price">Rs.<?php echo number_format($w['price'], 2); ?></span>
 
               
  <form method="post" action="cart.php" class="wp-card__form">
       <input type="hidden" name="id" value="<?php echo $w['id']; ?>">
       <input type="hidden" name="name" value="<?php echo htmlspecialchars($w['name']); ?>">
       <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($backToHere); ?>">
      
       <button type="button" class="wp-card__add add-to-cart-btn"
        data-id="<?php echo $w['id']; ?>"
        data-type="wallpaper">
            <i class="fa-solid fa-bag-shopping"></i> Add to Cart
          </button>
        </form>
      </div>
    </div>
  </article>

  <?php endforeach; ?>
  <?php endif;?>
 
     </div>
   </div>
 
 </section>
 

 <?php if (!empty($_GET['added']) && !empty($_GET['name'])): ?>
   <div class="wp-flash">
     <i class="fa-solid fa-circle-check"></i>
     <?php echo htmlspecialchars($_GET['name']); ?> added to your cart.
     <a href="<?php echo htmlspecialchars($backToHere); ?>" class="wp-flash__close" aria-label="Dismiss">&times;</a>
   </div>
 <?php endif; ?>


 </div>


    

 <?php
require_once __DIR__."/footer.php";
?>

</body>
</html>

