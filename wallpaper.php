<?php
require_once __DIR__."/header.php";

// Prevent undefined variable errors
$colorList = $colorList ?? [];
$allwallpapers = $allwallpapers ?? [];
$wallapers = $wallapers ?? [];
$selectedColor = $selectedColor ?? '';
$backToHere = $backToHere ?? 'wallpaper.php';
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
    <link rel="stylesheet" href="wallpaper.css">
    <title>Wallpaper</title>
</head>
<body>
  <section class="wall-cero">
    <div class="wall-cero-media">
    <img src="https://i.pinimg.com/1200x/fe/5a/bf/fe5abf7a3cd2aa8a4848c3dd2ad19455.jpg" alt="">
    </div>
<div class="wall-tint"></div>
<div class="wall-overlay">
  <h1 class="wall-title">Wallapaper</h1>
  <p class="wp-subtitle">Patterns woven with warmth, for walls that feels like home.</p>
</div>
</section>

<section class="wall-split">
  <aside class="wall-colors" id="wallColors">
    <ul class="wp-colors__list">
    <h2 class="color-list">Colors</h2>
    <?php if (!empty($colorList)): ?>
    <?php foreach($colorList as $c): ?>
      <?php
      $count = count(array_filter($allwallpapers ?? [], function($w) use ($c) {
        return $w['color'] === $c['name'];
      }));
      $isActive= ($selectedColor === $c['name']);
      ?>

    <li>
      <a href="wallpaper.php?color=<?php echo urlencode($c['name']); ?>"
      class="wp-color-item <?php echo $isActive? 'is-active': '';?>">

      <span class="wp-color-item__dot" style="background:<?php echo htmlspecialchars($c['hex']); ?>"></span>
      <?php echo htmlspecialchars($c['name']);?>

      <span class="wp-color-item__count"><?php echo $count; ?></span>
    </a>
    </li>

    <?php endforeach; ?>
    <?php endif; ?>
    </ul>
    <a href="wallpaper.php" class="wp-colors__clears">Show All </a>
    </aside>


<div class="wp-products">
  <div class="wp-products__bar">
    <span><?php echo count($wallapers ?? []); ?> </span> &nbsp;wallapaper
    <?php if ($selectedColor !== ''): ?>
      in <?php echo htmlspecialchars($selectedColor); ?>
      <?php endif; ?>
  </div>

  <div class="wp-products__scroll">
    <?php if (empty($wallapers ?? [])): ?>

      <div class="wp-empty">
        <i class="fa-regular fa-image"></i>
        <h3>No wallapapers in this color yet.</h3>
        <p>Try another color, or view the full collection.</p>
        <a href="wallpaper.php" class="wp-colors__clear">Show All</a>
      </div>

      <?php else:?>
        <?php foreach (($wallapers ?? []) as $w): ?>
      <?php
      $fullStars = (int) round($w['rating']);
      $starString = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
      $badgeLabel = $w['badge'] === 'new' ? 'New' : ($w['badge'] === 'bestseller' ? 'Bestseller' : '');
          ?>


<article class="wp-card">
    <div class="wp-card__media">
      <?php if ($badgeLabel !== ''): ?>
        <span class="wp-card__badge<?php echo $w['badge'] === 'new' ? ' is-new' : ''; ?>"><?php echo $badgeLabel; ?></span>
     <?php endif; ?>

     <img src="<?php echo htmlspecialchars($w['image']); ?>" alt="<?php echo htmlspecialchars($w['name']); ?> wallpaper" loading="lazy">
 
    
    <a href="#qv-<?php echo $w['id']; ?>" class="wp-card__quick">
      <i class="fa-regular fa-eye"></i> Quick View
 </a>
  </div>

  <div class="wp-card__body">
      <span class="wp-card__category"><?php echo htmlspecialchars($w['category']); ?></span>
      <h3 class="wp-card__name"><?php echo htmlspecialchars($w['name']); ?></h3>
      <div class="wp-card__meta">
          <span class="wp-card__color">
            <span class="wp-card__color-dot" style="background:<?php echo htmlspecialchars($w['colorHex']); ?>"></span>
       <?php echo htmlspecialchars($w['color']); ?>
          </span>
    <span class="wp-card__rating"><?php echo $starString; ?> <span><?php echo number_format($w['rating'], 1); ?></span></span>
  </div>


   <div class="wp-card__footer">
       <span class="wp-card__price">$<?php echo number_format($w['price'], 2); ?></span>
 
               
 <form method="post" action="cart.php" class="wp-card__form">
      <input type="hidden" name="id" value="<?php echo $w['id']; ?>">
      <input type="hidden" name="name" value="<?php echo htmlspecialchars($w['name']); ?>">
      <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($backToHere); ?>">
      <button type="submit" class="wp-card__add">
           <i class="fa-solid fa-bag-shopping"></i> Add to Cart
         </button>
       </form>
     </div>
   </div>
 </article>
 
          <!-- Quick View popup for this product (pure CSS, see .wp-modal:target) -->
  <div class="wp-modal" id="qv-<?php echo $w['id']; ?>">

    <a href="#" class="wp-modal__scrim" aria-label="Close"></a>

    <div class="wp-modal__panel" role="dialog" aria-label="<?php echo htmlspecialchars($w['name']); ?> details">
      <a href="#" class="wp-modal__close" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>

      <div class="wp-modal__media">
        <img src="<?php echo htmlspecialchars($w['image']); ?>" alt="<?php echo htmlspecialchars($w['name']); ?>">
      </div>


  <div class="wp-modal__body">
<span class="wp-modal__category"><?php echo htmlspecialchars($w['category']); ?></span>
    <h3><?php echo htmlspecialchars($w['name']); ?></h3>

   <div class="wp-modal__rating"><?php echo $starString; ?> <span><?php echo number_format($w['rating'], 1); ?> rating</span></div>

    <div class="wp-modal__price">$<?php echo number_format($w['price'], 2); ?></div>

    <div class="wp-modal__swatch" style="--swatch-color: <?php echo htmlspecialchars($w['colorHex']); ?>"><?php echo htmlspecialchars($w['color']); ?></div>
    <p class="wp-modal__desc">A hand-finished, fade-resistant wallpaper from the Sweet Haven collection — printed on premium non-woven stock for a smooth, seamless hang.</p>

      <form method="post" action="cart.php" class="wp-modal__actions">
        <input type="hidden" name="id" value="<?php echo $w['id']; ?>">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($w['name']); ?>">
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($backToHere); ?>">
        <button type="submit" class="wp-btn wp-btn--solid">
                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
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
