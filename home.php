<?php include "header.php"; 

function getFeaturedProducts($conn = null) {
    
    if ($conn instanceof mysqli) {
        // Wrapped in try/catch because your PHP's mysqli driver throws an
        // exception on a bad query instead of just returning false -- without
        // this, one wrong column name here fatal-errors the whole homepage
        // instead of quietly falling back to the sample data below.
        try {
            // NOTE: aliased to match what the rest of this function expects
            // (id, name, image...) even though the real columns in your
            // products table are product_id, product_name, image_path.
            $sql = "SELECT product_id AS id, product_name AS name, image_path AS image, price
                    FROM products
                    WHERE status = 'Active'
                    ORDER BY product_id DESC
                    LIMIT 6";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $products = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Your products table doesn't have color_name/color_hex/rating/
                    // review_count/badge/product_type/is_featured yet, so we fill in
                    // sensible defaults here rather than pretending the DB has them.
                    $row['color_name']   = $row['color_name']   ?? '';
                    $row['color_hex']    = $row['color_hex']    ?? '#997E67';
                    $row['rating']       = $row['rating']       ?? 0;
                    $row['review_count'] = $row['review_count'] ?? 0;
                    $row['badge']        = $row['badge']        ?? null;
                    $row['product_type'] = $row['product_type'] ?? 'decor';
                    $products[] = $row;
                }
                return $products;
            }
        } catch (mysqli_sql_exception $e) {
            // Query didn't match the real schema -- fall through to sample data below
            // instead of crashing the page. Log it so it's easy to find later.
            error_log('getFeaturedProducts DB query failed: ' . $e->getMessage());
        }
    }

$sampleFile = __DIR__ . 'database/sample-featured-data.php';
    if (file_exists($sampleFile)) {
       include $sampleFile;
       if (!empty($sampleFeaturedProducts)) {
     return $sampleFeaturedProducts;
        }
    }

return [
        [
            'id' => 1, 'name' => 'Peacock Floral Wallpaper',
            'image' => 'https://i.pinimg.com/736x/8b/07/2a/8b072a3f96b92dfe5203094f7cd3f938.jpg',
            'price' => 7000 , 'color_name' => 'Green', 'color_hex' => '#4E6B4E',
            'rating' => 4.8, 'review_count' => 32, 'badge' => 'Bestseller', 'product_type' => 'wallpaper',
        ],
        [
            'id' => 2, 'name' => 'Hexagon Floating Shelves',
            'image' => 'hexagon.jpg',
            'price' => 12500, 'color_name' => 'Black', 'color_hex' => '#2C1A0E',
            'rating' => 4.6, 'review_count' => 18, 'badge' => null, 'product_type' => 'decor',
        ],
        [
            'id' => 3, 'name' => 'Jute Pendant Lamp',
            'image' => 'jute.jpg',
            'price' => 11000, 'color_name' => 'Natural', 'color_hex' => '#997E67',
            'rating' => 4.7, 'review_count' => 24, 'badge' => 'Bestseller', 'product_type' => 'decor',
        ],
        [
        'id'           => 4, 'name'  => 'Wooden Wall Art Panel',
        'image'        => 'https://i.pinimg.com/1200x/00/79/1a/00791a9f30f4495f732df6634c65db86.jpg',
        'price'        => 27000, 'color_name'   => 'Walnut', 'color_hex'    => '#664930',
        'rating'       => 4.5, 'review_count' => 11,
        'badge'        => null, 'product_type' => 'decor',
    ],
    [
        'id'           => 5,
        'name'         => 'Bohemian Cushion Set',
        'image'        => 'https://i.pinimg.com/1200x/aa/44/05/aa4405d40a79c528682b8f660bacefc6.jpg',
        'price'        => 8500,
        'color_name'   => 'Terracotta',
        'color_hex'    => '#B44446',
        'rating'       => 4.4,
        'review_count' => 15,
        'badge'        => null,
        'product_type' => 'decor',
    ],
    [
        'id'           => 6,
        'name'         => 'Hand-Thrown Ceramic Vase',
        'image'        => 'https://i.pinimg.com/736x/eb/70/2a/eb702ab9a0d2e1a8aeaf1178444d318c.jpg',
        'price'        => 8500,
        'color_name'   => 'Blue & White',
        'color_hex'    => '#3B5A87',
        'rating'       => 4.9,
        'review_count' => 27,
        'badge'        => 'Bestseller',
        'product_type' => 'decor',
    ]
    ];
}
 
/** Render a 5-star rating (full / half / empty) as Font Awesome icons */
function renderStars($rating) {
    $rating = (float) $rating;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star"></i>';
        }
    }
    return $html;
}
 
$featuredProducts = getFeaturedProducts($conn ?? null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Sweet Haven HomePage</title>
</head>
<body>
<div class="tst" id="tst"></div>
    
<section class="cero">
    <video class="cero-video" autoplay muted loop playsinline preload="auto">
        <source src="greenwallpaper.mp4" type="video/mp4">
    </video>
<div class="cero-tint"></div>
<div class="cero-overlay"></div>

<div class="cero-content">
    <h1 class="cero-title">
        A room that <br>feels like a <br><em>slow exhale.</em> 
    </h1>
<p class="cero-sub">Hand-picked wallpaper, lighting, wall arts- crafted in the workshop of Kathmandu, designed for spaces to hold meanings.</p>
<div class="cero-btns">
    <a href="#shop" class="btn-primary">View Collections</a>
    <a href="#dna" class="btn-outline">Choose Your Style</a>
</div>
</div>
</section>

<div class="styles">
    <div class="style-track">
        <div class="style-item">Classic Style <span class="s-dot"></span></div>
        <div class="style-item">Bohemian Style <span class="s-dot"></span></div>
        <div class="style-item">Vintage Style <span class="s-dot"></span></div>
        <div class="style-item">Minimalist Style <span class="s-dot"></span></div>
        <div class="style-item">Floral Style <span class="s-dot"></span></div>
        <div class="style-item">Rustic Style <span class="s-dot"></span></div>
        <div class="style-item">Classic Style <span class="s-dot"></span></div>
        <div class="style-item">Bohemian Style <span class="s-dot"></span></div>
        <div class="style-item">Vintage Style <span class="s-dot"></span></div>
        <div class="style-item">Minimalist Style <span class="s-dot"></span></div>
        <div class="style-item">Floral Style <span class="s-dot"></span></div>
        <div class="style-item">Rustic Style <span class="s-dot"></span></div>
    </div>
</div>

<section class="our-story" id="our-story">
    <div class="story-left">
        <img src="sweet-haven-logo-2.png" alt="Sweet Haven">
    </div>
    <div class="story-right">
        <span class="story-tag">OUR STORY</span>
        <h2>Crafting Spaces <span>You'll Love</span> </h2>
        <p>At Sweet Haven, we believe a home is more than just a place—it's a reflection of the people who live in it. Inspired by timeless elegance, vintage charm, and botanical beauty, we curate home décor that transforms everyday spaces into warm, inviting havens.</p>
        <p>From handcrafted accents to statement wallpapers and thoughtfully selected décor pieces, every collection is chosen to bring comfort, character, and lasting style to your home. Our mission is simple: to help you create spaces that feel beautiful, personal, and truly yours.</p>
    
    <div class="story-divider"></div> 
        <h3>Where Beauty Meets Comfort </h3>
    </div>
</section>

<section class="dna-section">
    <p class="discover">Discover Your Style</p>
    <h2 class="section-title">What's Your Design <em>DNA?</em></h2>
    <p class="section-sub"> Choose a sytle and curate your perfect collections. </p>

    <div class="dna-grid">
        <div class="dna-card" data-style="cozy" onclick="selectDNA(this)">
            <span class="dna-icon">🕯️</span><div class="dna-name">Cozy</div>
            <div class="dna-desc">Warm textures, candlelight tones, and layered comfort for souls.</div>
        </div>

        <div class="dna-card" data-style="bold" onclick="selectDNA(this)">
            <span class="dna-icon">⚡</span><div class="dna-name">Bold</div>
            <div class="dna-desc">Statement prints, vivid palettes, and pieces that refuse to go unnoticed.</div>
        </div>

        <div class="dna-card" data-style="serene" onclick="selectDNA(this)">
            <span class="dna-icon">🌿</span><div class="dna-name">Serene</div>
            <div class="dna-desc">Organic forms, and calm that you feel the moment you enter.</div>
        </div>
        
        <div class="dna-card" data-style="luxe" onclick="selectDNA(this)">
            <span class="dna-icon">✨</span><div class="dna-name">Luxe</div>
            <div class="dna-desc">Gilded edges, velvet drapes, and the quiet confidence of a shiny room.</div>
        </div>

        <div class="dna-card" data-style="rustic" onclick="selectDNA(this)">
            <span class="dna-icon">🪵</span><div class="dna-name">Rustic</div>
            <div class="dna-desc">Rough timber, hand-thrown clay, and earthy tones rooted in honest materials.</div>
        </div>
    </div>
</section>

<section id="featured" class="shop">
    <div class="section-head">
    <p class="season">Trending this season</p>
    <h2 class="season-featured">Featured <em>Pieces</em></h2>
    <p class="season-sub">Handpick your style - each piece is one of a kind.</p>
    <div class="products-grid" id="productsGrid"></div>
    </div>

    <div class="feat-grid" id="productGrid">
     <?php if (!empty($featuredProducts)): ?>
        <?php foreach ($featuredProducts as $product): ?>
           <?php
           $pid        = (int) ($product['id'] ?? 0);
           $name       = htmlspecialchars($product['name'] ?? '');
           $image      = htmlspecialchars($product['image'] ?? '');
           $price      = number_format((float) ($product['price'] ?? 0));
           $colorName  = htmlspecialchars($product['color_name'] ?? '');
           $colorHex   = htmlspecialchars($product['color_hex'] ?? '#997E67');
           $rating     = (float) ($product['rating'] ?? 0);
           $reviewCnt  = (int) ($product['review_count'] ?? 0);
           $badge      = $product['badge'] ?? null;
           $productType= htmlspecialchars($product['product_type'] ?? 'decor');
           // "decor" and "product" both mean the same source table (products);
           // only "wallpaper" needs the different item-type for the cart.
           $cartItemType = $productType === 'wallpaper' ? 'wallpaper' : 'product';
           $detailLink = $productType === 'wallpaper'
                    ? "wallpaper_details.php?id={$pid}"
                    : "product.php?id={$pid}";
       ?>
 <div class="feat-card">
     <a href="<?php echo $detailLink; ?>" class="feat-img-link">
       <div class="feat-img">
           <?php if ($badge): ?>
               <span class="feat-badge"><?php echo htmlspecialchars($badge); ?></span>
           <?php endif; ?>
           <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
         </div>
     </a>
     <div class="feat-info">
       <a href="<?php echo $detailLink; ?>" class="feat-name"><?php echo $name; ?></a>
 
       <div class="feat-meta">
           <span class="feat-color">
               <span class="feat-dot" style="background: <?php echo $colorHex; ?>;"></span>
               <?php echo $colorName; ?>
           </span>
           <span class="feat-rating">
               <span class="feat-stars"><?php echo renderStars($rating); ?></span>
               <?php echo number_format($rating, 1); ?>
           </span>
       </div>
 
       <div class="feat-price-row">
           <span class="feat-price">Rs.<?php echo $price; ?></span>
           <button
               class="feat-cart-btn add-to-cart-btn"
               type="button"
               data-product-id="<?php echo $pid; ?>"
               data-item-type="<?php echo $cartItemType; ?>"
           >
               <i class="fa-solid fa-bag-shopping"></i> Add to Cart
        </button>
       </div>
       </div>
    </div>
   <?php endforeach; ?>
    <?php else: ?>
        <p class="feat-empty">New pieces are on their way — check back soon.</p>
    <?php endif; ?>
    </div>
</section>


<div class="wrap">
    <div class="eye-unique">UNIQUE FEATURES</div>
    <h2>Build Your Room <em>Palette</em></h2>
    <p class="sub">Describe a mood or scene - we'll generate a color palette as per the customization. </p>

    <div class="mood-row">
        <input type="text" name="Mood Palette" id="moodInput" placeholder="e.g. morning glory ">
        <button id="generatebtn">Generate Palette</button>
    </div>
<?php include "mood-palette-popup.php"; ?>
    
    <p class="helper">or try one of these</p>
    <div class="chips">
        <span class="chip" data-mood="midnight elegance">midnight elegance</span>
        <span class="chip" data-mood="coastal breeze">coastal breeze</span>
        <span class="chip" data-mood="festival glow">festival glow</span>
        <span class="chip" data-mood="rustic autumn">rustic autumn</span>
        <span class="chip" data-mood="spring garden">spring garden</span>
        <span class="chip" data-mood="romantic sunset">romantic sunset</span>
        <span class="chip" data-mood="cozy winter">cozy winter</span>
    </div>
</div>

<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <span class="footer-brand-name">Sweet Haven</span>
            <p>Handcrafted home decor from the workshop of Nepal - designed for spaces that hold meanings.</p>
            <div class="socials">
                <a class="soc-link" href="#">IG</a>
                <a class="soc-link" href="#">FB</a>
                <a class="soc-link" href="#">YT</a>
                <a class="soc-link" href="#">PT</a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Shop</h4>
            <a href="#">Wallpapers</a>
            <a href="#">Decor Items</a>
            <a href="#">Collections</a>
            <a href="#">New Arrivals</a>
        </div>
        <div class="footer-col">
            <h4>Explore</h4>
            <a href="#">Design DNA</a>
            <a href="#">Palette Builder</a>
            <a href="#">Our Story</a>
            <a href="#">Decor Journal</a>
        </div>
        <div class="footer-col">
            <h4>Help</h4>
            <a href="#">Contact Us</a>
            <a href="#">My Account</a>
            <a href="#">Admin</a>

        <div class="contact-info" style="margin-top: 1.5rem;">
            <div>Lalitpur,Nepal</div>
        <div><a href="#">hello@sweethaven.com</a></div>
        <div><a href="#">+977 9837491728</a></div>
    </div>
    </div>
    </div>
    
    <div class="footer-bottom">
        <p>© 2026 Sweet Haven Home Decor. All rights reserved.</p>
        <p>Made with care in Nepal.</p>
    </div>
</footer>


<?php include "mood-palette-popup.php"; ?>


</body>
</html>