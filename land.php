<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="land.css">
    <title>Sweet Haven HomePage</title>
</head>
<body>
<div class="tst" id="tst"></div>
    <header id="header">
        <a href="#" class="logo">
            <img src="sweet-haven-logo-2.png" alt="Sweet Haven Logo">
            <span class="logo-text">Sweet Haven</span>
        </a>
    <nav>
        <a href="#" class="active">Home</a>
        <a href="wallpaper.php">Wallpaper</a>
        <a href="#shop">Shop</a>
        <a href="#decor">Decor</a>
        <a href="#collections">Collections</a>
        <a href="#about">About</a>
    </nav>
    <div class="header-right">
        <div class="search-box">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#997E67" stroke-width="2">
                <circle cx="11" cy="11" r="8"><path d="M21 21l-4.35-4.35"/> </circle>
            </svg>
    <input type="text" name="" id="" placeholder="Search decor...">
    </div>

    <button class="icon-btn" title="Account">
        <i class="fa-thin fa-circle-user" style="color: rgb(99, 230, 190);"></i>
    </button>

    <button class="icon-btn" title="cart" style="position: relative;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <span class="cart-badge" id="cartCount">0</span>
    </button>

    
</div>
</header>

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

    <div class="pro-grid">
      <div class="pro-card">
       <div class="pro-img">
        <img src="https://i.pinimg.com/736x/8b/07/2a/8b072a3f96b92dfe5203094f7cd3f938.jpg" alt="Peacock Floral Wallapaper">
       </div>
    <div class="pro-info">
       <h3>Peacock Floral Wallpaper</h3>
       <div class="price">Rs. 7,000</div>
    <button class="btn">Add To Cart</button>
   </div>
 </div>

 <div class="pro-card">
       <div class="pro-img">
        <img src="hexagon.jpg" alt="Hexagon Floating Shelves">
       </div>
    <div class="pro-info">
       <h3>Hexagon Floating Shelves</h3>
       <div class="price">Rs. 12,500</div>
    <button class="btn">Add To Cart</button>
   </div>
 </div>

 <div class="pro-card">
       <div class="pro-img">
        <img src="jute.jpg" alt="Jute Pendent Lamp">
       </div>
    <div class="pro-info">
       <h3>Jute Pendent Lamp</h3>
       <div class="price">Rs. 11,000</div>
    <button class="btn">Add To Cart</button>
   </div>
 </div>


 <div class="pro-card">
       <div class="pro-img">
        <img src="https://i.pinimg.com/1200x/00/79/1a/00791a9f30f4495f732df6634c65db86.jpg" alt="Wooden Wall Art Panel">
       </div>
    <div class="pro-info">
       <h3>Peacock Floral Wallpaper</h3>
       <div class="price">Rs. 27,000</div>
    <button class="btn"> To Cart</button>
   </div>
 </div>

  <div class="pro-card">
       <div class="pro-img">
        <img src="https://i.pinimg.com/1200x/aa/44/05/aa4405d40a79c528682b8f660bacefc6.jpg" alt="Bohemian Cushion Set">
       </div>
    <div class="pro-info">
       <h3>Bohemian Cushion Set</h3>
       <div class="price">Rs. 8,500</div>
    <button class="btn">Add To Cart</button>
   </div>
 </div>

 <div class="pro-card">
       <div class="pro-img">
        <img src="https://i.pinimg.com/736x/eb/70/2a/eb702ab9a0d2e1a8aeaf1178444d318c.jpg" alt="Hand-Thrown Ceramic Vase">
       </div>
    <div class="pro-info">
       <h3>Hand-Thrown Ceramic Vase</h3>
       <div class="price">Rs. 8,500</div>
    <button class="btn">Add To Cart</button>
   </div>
 </div>

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

    <p class="helper">or try one of these</p>
    <div class="chips">
        <span class="chip" data-mood="midnight elegance">midnight elegance</span>
        <span class="chip" data-mood="coastal breeze">coastal breeze</span>
        <span class="chip" data-mood="festival glow">festival glow</span>
        <span class="chip" data-mood="rustic autumn">rustic autumn</span>
        <span class="chip" data-mood="spring garden">spring garden</span>
        <span class="chip" data-mood="romantic sunset">romantic sunset</span>
        <span class="chip" data-mood=">cozy winter">cozy winter</span>
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


<script>

</script>


</body>
</html>

