<?php include "header.php"; 

function getFeaturedProducts($conn = null) {
    
    if ($conn instanceof mysqli) {
    
        try {
          
            // NOTE: product_type, color_name, color_hex, rating, review_count and
            // badge are all used further down this file, so they must be
            // selected here too - otherwise every product silently defaults
            // to 'decor' and links to the wrong detail page.
            $sql = "SELECT product_id AS id, product_name AS name, image_path AS image, price,
                           product_type, color_name, color_hex, rating, review_count, badge
                    FROM products
                    WHERE status = 'Active'
                    ORDER BY product_id DESC
                    LIMIT 6";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $products = [];
                while ($row = mysqli_fetch_assoc($result)) {
                
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

    <!-- ===================================================
         NEW: styles for the Build Your Room Palette output.
         Feel free to move this block into home.css later.
    ==================================================== -->
    <style>
    #paletteMessage{
        font-size:0.9rem;
        color:var(--bark, #664930);
        margin-top:24px;
        min-height:20px;
    }
    #paletteResult{
        display:flex;
        justify-content:center;
        flex-wrap:wrap;
        gap:16px;
        margin-top:10px;
    }
    .mood-swatch{
        width:120px;
        height:150px;
        border-radius:14px;
        display:flex;
        flex-direction:column;
        justify-content:flex-end;
        align-items:center;
        padding-bottom:10px;
        box-shadow:0 6px 16px rgba(44,26,14,0.15);
        color:#fff;
        font-size:0.75rem;
        font-weight:600;
        text-shadow:0 1px 3px rgba(0,0,0,0.4);
        cursor:pointer;

        /* start hidden/small so the "pop" animation can play */
        opacity:0;
        transform:scale(0.4);
        animation:moodPop 0.5s cubic-bezier(.34,1.56,.64,1) forwards;
    }
    @keyframes moodPop{
        to{
            opacity:1;
            transform:scale(1);
        }
    }
    .mood-swatch .copied-tag{
        position:absolute;
        margin-top:-34px;
        background:rgba(0,0,0,.65);
        color:#fff;
        font-size:9px;
        padding:2px 6px;
        border-radius:5px;
        opacity:0;
        transition:opacity .2s ease;
        pointer-events:none;
    }
    .mood-swatch .copied-tag.show{
        opacity:1;
    }

    /* Design DNA cards now navigate to style_products.php on click -
       just a hover cue is enough here, no "selected" state needed. */
    .dna-card{
        cursor:pointer;
        transition:transform .15s ease, box-shadow .15s ease;
    }
    .dna-card:hover{
        transform:translateY(-4px);
        box-shadow:0 10px 24px rgba(0,0,0,.2);
    }
    </style>
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
    <a href="decor.php" class="btn-primary">View Collections</a>
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

<section class="dna-section" id="dna">
    <p class="discover">Discover Your Style</p>
    <h2 class="section-title">What's Your Design <em>DNA?</em></h2>
    <p class="section-sub"> Choose a sytle and curate your perfect collections. </p>

    <div class="dna-grid">
        <div class="dna-card" data-style="cozy" onclick="location.href='style_products.php?style=cozy'">
            <span class="dna-icon">🕯️</span><div class="dna-name">Cozy</div>
            <div class="dna-desc">Warm textures, candlelight tones, and layered comfort for souls.</div>
        </div>

        <div class="dna-card" data-style="bold" onclick="location.href='style_products.php?style=bold'">
            <span class="dna-icon">⚡</span><div class="dna-name">Bold</div>
            <div class="dna-desc">Statement prints, vivid palettes, and pieces that refuse to go unnoticed.</div>
        </div>

        <div class="dna-card" data-style="serene" onclick="location.href='style_products.php?style=serene'">
            <span class="dna-icon">🌿</span><div class="dna-name">Serene</div>
            <div class="dna-desc">Organic forms, and calm that you feel the moment you enter.</div>
        </div>
        
        <div class="dna-card" data-style="luxe" onclick="location.href='style_products.php?style=luxe'">
            <span class="dna-icon">✨</span><div class="dna-name">Luxe</div>
            <div class="dna-desc">Gilded edges, velvet drapes, and the quiet confidence of a shiny room.</div>
        </div>

        <div class="dna-card" data-style="rustic" onclick="location.href='style_products.php?style=rustic'">
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

    <!-- REMOVED: <?php /* include "mood-palette-popup.php"; */ ?>
         That file called generate_palette.php on the server, which
         doesn't exist yet. Replaced with a simple inline result box
         below, filled entirely by JavaScript — no server call needed. -->
    <p id="paletteMessage"></p>
    <div id="paletteResult"></div>

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

<!-- ===================================================
     NEW: the mood → palette generator, matching the real
     IDs/classes already in this page (#moodInput,
     #generatebtn, .chip[data-mood]). No server call,
     no popup file needed anymore.
==================================================== -->
<script>
/* STEP 1: mood -> 5 hex colors. Add a new line to add a new mood. */
const moodPalettes = {
  "midnight elegance": ["#0B1D3A", "#1F2A44", "#3E4C6D", "#C9A66B", "#EDE6D6"],
  "coastal breeze":    ["#DCEEF2", "#A8D0DB", "#5B8A9A", "#F4EBDD", "#E8B98A"],
  "festival glow":     ["#FF6B35", "#F7C548", "#C1121F", "#780116", "#FDF0D5"],
  "rustic autumn":     ["#7A3B22", "#B5651D", "#D9A441", "#4A2E1F", "#EDD6A3"],
  "spring garden":     ["#A8D5BA", "#6FAE8C", "#F6E27F", "#F7A6C1", "#FFFDF7"],
  "romantic sunset":   ["#FF9F80", "#FF6F91", "#D65DB1", "#845EC2", "#FFF1E6"],
  "cozy winter":       ["#EAF0F4", "#C9D6DF", "#52616B", "#1E2022", "#F0F5F9"],
  "calm":              ["#DDEFE4", "#A9CBB7", "#6F9C8D", "#3E6259", "#F4F9F6"],
  "energetic":         ["#FF3D3D", "#FF9F1C", "#FFD400", "#2EC4B6", "#011627"],
  "luxury":            ["#1A1A1A", "#3B2F2F", "#C9A227", "#7A6C5D", "#F5F0E6"]
};

/* STEP 2: simple keyword backup if the mood typed isn't in the list above */
const keywordFallbacks = {
  "dark":  ["#0B1D3A", "#1F2A44", "#3E4C6D", "#C9A66B", "#EDE6D6"],
  "night": ["#0B1D3A", "#1F2A44", "#3E4C6D", "#C9A66B", "#EDE6D6"],
  "bright":["#FF6B35", "#F7C548", "#C1121F", "#780116", "#FDF0D5"],
  "light": ["#DCEEF2", "#A8D0DB", "#5B8A9A", "#F4EBDD", "#E8B98A"],
  "warm":  ["#7A3B22", "#B5651D", "#D9A441", "#4A2E1F", "#EDD6A3"],
  "cool":  ["#EAF0F4", "#C9D6DF", "#52616B", "#1E2022", "#F0F5F9"],
  "green": ["#A8D5BA", "#6FAE8C", "#F6E27F", "#F7A6C1", "#FFFDF7"],
  "pink":  ["#FF9F80", "#FF6F91", "#D65DB1", "#845EC2", "#FFF1E6"]
};

/* if nothing matches at all, fall back to the site's own brand colors */
const defaultPalette = ["#FFDBBB", "#CCBEB1", "#997E67", "#664930", "#64242F"];

/* STEP 3: pick a palette for whatever the user typed */
function getPaletteForMood(rawText) {
  const mood = rawText.trim().toLowerCase();

  if (moodPalettes[mood]) {
    return { colors: moodPalettes[mood], message: `Palette for "${mood}"` };
  }
  for (const word in keywordFallbacks) {
    if (mood.includes(word)) {
      return { colors: keywordFallbacks[word], message: `Closest match: "${word}" mood` };
    }
  }
  return { colors: defaultPalette, message: `No exact match yet — here's a versatile palette` };
}

/* STEP 4: draw the swatches, each popping in a little after the last one */
function renderPalette(mood) {
  const messageBox = document.getElementById("paletteMessage");
  const resultBox  = document.getElementById("paletteResult");
  const result = getPaletteForMood(mood);

  messageBox.textContent = result.message;
  resultBox.innerHTML = "";

  result.colors.forEach(function (hex, index) {
    const swatch = document.createElement("div");
    swatch.className = "mood-swatch";
    swatch.style.backgroundColor = hex;
    swatch.style.animationDelay = (index * 0.1) + "s";
    swatch.textContent = hex;

    // bonus: click a swatch to copy its hex code
    swatch.addEventListener("click", function () {
      navigator.clipboard.writeText(hex.toUpperCase());
      swatch.textContent = "Copied!";
      setTimeout(function () { swatch.textContent = hex; }, 800);
    });

    resultBox.appendChild(swatch);
  });
}

/* STEP 5: hook up the real elements from this page */
document.addEventListener("DOMContentLoaded", function () {
  const moodInput   = document.getElementById("moodInput");
  const generateBtn = document.getElementById("generatebtn");
  const chips       = document.querySelectorAll(".chip");

  if (!moodInput || !generateBtn) return;

  generateBtn.addEventListener("click", function () {
    const val = moodInput.value.trim();
    if (!val) { moodInput.focus(); return; }
    renderPalette(val);
  });

  moodInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") generateBtn.click();
  });

  chips.forEach(function (chip) {
    chip.addEventListener("click", function () {
      moodInput.value = chip.dataset.mood;
      renderPalette(chip.dataset.mood);
    });
  });
});

/* ===================================================
   Add to Cart - for the buttons in the Featured Pieces
   grid (class="add-to-cart-btn", already in the HTML).
==================================================== */
/* ===================================================
   Add to Cart - using event delegation (one listener on
   document) so it also works for any Add to Cart button
   added to the page later, not just ones present at load.
==================================================== */
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".add-to-cart-btn");
  if (!btn) return;

  const productId = btn.dataset.productId;
  const itemType  = btn.dataset.itemType;
  const originalHTML = btn.innerHTML;

  btn.disabled = true;
  btn.innerHTML = "Adding...";

  fetch("add_to_cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "product_id=" + encodeURIComponent(productId)
        + "&item_type="  + encodeURIComponent(itemType)
        + "&quantity=1"
        + "&ajax=1"
  })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.success) {
        const badge = document.getElementById("cartCount"); // lives in header.php
        if (badge) badge.textContent = data.cart_count;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
      } else {
        alert(data.message || "Could not add to cart.");
        btn.innerHTML = originalHTML;
      }
    })
    .catch(function () {
      alert("Something went wrong. Please try again.");
      btn.innerHTML = originalHTML;
    })
    .finally(function () {
      setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
      }, 1200);
    });
});
</script>

</body>
</html>