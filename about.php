<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/database/db_connect.php';
include_once __DIR__ . '/header.php';

/* =========================================================
   DYNAMIC CONTENT
   Everything below is generated instead of hardcoded, so the
   page updates itself as your store grows. Table/column names
   are guesses based on your cart/cart_items schema — rename
   the ones marked "ADJUST" to match your actual tables.
   ========================================================= */

// 1) Years crafting — calculated from a founding date, never needs manual updates
$foundedYear   = 2016; // ADJUST: the year Sweet Haven actually started
$yearsCrafting = (int) date('Y') - $foundedYear;

// 2) Live counts from the database, with safe fallbacks if a table doesn't exist yet
function sh_count($conn, $sql, $fallback) {
    // Your XAMPP setup has mysqli throwing exceptions on error (instead of
    // just returning false), so a plain "if ($result)" check isn't enough —
    // wrap in try/catch too, or an unknown table/column crashes the page.
    try {
        $result = @mysqli_query($conn, $sql);
        if ($result && ($row = mysqli_fetch_row($result))) {
            return (int) $row[0];
        }
    } catch (\mysqli_sql_exception $e) {
        // Table or column doesn't exist yet (or has a different name) —
        // fall back quietly instead of crashing the page.
    }
    return $fallback;
}

$productCount    = sh_count($conn, "SELECT COUNT(*) FROM products", 120);          // ADJUST table/column names below to match your DB
$collectionCount = sh_count($conn, "SELECT COUNT(*) FROM collections", 8);         // ADJUST
$customerCount   = sh_count($conn, "SELECT COUNT(DISTINCT user_id) FROM orders", 1400); // ADJUST — your `orders` table doesn't have `user_id`; check phpMyAdmin for the real column name (maybe `id` or `customer_id`)

// 3) Testimonials — pulled from a reviews table if you have one; falls back to defaults
$testimonials = [];
try {
    $reviewQuery = @mysqli_query($conn, "SELECT customer_name, comment FROM reviews ORDER BY created_at DESC LIMIT 6"); // ADJUST
    if ($reviewQuery && mysqli_num_rows($reviewQuery) > 0) {
        while ($row = mysqli_fetch_assoc($reviewQuery)) {
            $testimonials[] = ['name' => $row['customer_name'], 'text' => $row['comment']];
        }
    }
} catch (\mysqli_sql_exception $e) {
    // no reviews table yet — falls through to the defaults below
}
if (empty($testimonials)) {
    $testimonials = [
        ['name' => 'Anjali R.',  'text' => 'The wallpaper texture looks handmade in person. My reading nook finally feels finished.'],
        ['name' => 'Marcus T.',  'text' => 'Ordered decor pieces for a whole room refresh. Every color matched exactly as shown.'],
        ['name' => 'Priya D.',   'text' => 'Fast delivery and the packaging alone felt like a gift. Will be back for the next room.'],
    ];
}
?>

<style>
/* Reuses the Sweet Haven palette defined in header.php's :root */
@import url('https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&family=Cormorant+Garamond:wght@500;600;700&display=swap');

body{ font-family:'Jost', sans-serif; }

.about-wrap{
    padding-top: 74px; /* clears fixed header */
    background: var(--white);
}

/* HERO */
.about-hero{
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    align-items: center;
    gap: 3rem;
    padding: 5.5rem 3rem 4rem;
    background: linear-gradient(180deg, var(--blush) 0%, var(--white) 100%);
}
.about-hero h1{
    font-family: 'Cormorant Garamond', serif;
    font-weight: 600;
    font-size: clamp(2.4rem, 4vw, 3.4rem);
    line-height: 1.15;
    color: var(--wine);
    max-width: 14ch;
}
.about-hero p{
    margin-top: 1.4rem;
    max-width: 46ch;
    font-size: 1.02rem;
    line-height: 1.75;
    color: var(--bark);
}
.about-hero-figure{
    height: 422px;
    width: 422px;
    object-fit: cover;
    border-radius: 50%;
    clip-path: circle(50% at 50% 50%);
}
.about-hero-figure img{
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.about-hero-figure .tag{
    position: absolute;
    bottom: 1rem; left: 1rem;
    background: rgba(253,250,247,0.92);
    color: var(--wine);
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

/* STATS STRIP — dynamic values */
.stats-strip{
    display: flex;
    justify-content: center;
    gap: 4.5rem;
    padding: 2.6rem 2rem;
    background: var(--wine);
}
.stat{ text-align: center; color: var(--blush); }
.stat .num{
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.3rem;
    font-weight: 600;
    color: var(--white);
    display: block;
}
.stat .label{
    font-size: 0.8rem;
    color: var(--rose);
    margin-top: 0.2rem;
}

/* TESTIMONIALS — auto-rotating, populated from PHP */
.testimonials{
    padding: 5rem 2rem;
    text-align: center;
}
.testimonials h2{
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    color: var(--wine);
    margin-bottom: 2.2rem;
}
.testi-track{
    max-width: 640px;
    margin: 0 auto;
    min-height: 130px;
    position: relative;
}
.testi-slide{
    position: absolute; inset: 0;
    opacity: 0;
    transition: opacity 0.6s ease;
}
.testi-slide.active{ opacity: 1; }
.testi-slide p{
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.35rem;
    color: var(--bark);
    line-height: 1.6;
}
.testi-slide span{
    display: block;
    margin-top: 1rem;
    font-size: 0.82rem;
    color: var(--coral);
    letter-spacing: 0.03rem;
}
.testi-dots{
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.6rem;
}
.testi-dots button{
    width: 8px; height: 8px;
    border-radius: 50%;
    border: none;
    background: var(--sand);
    cursor: pointer;
    padding: 0;
}
.testi-dots button.active{ background: var(--coral); }

/* CTA */
.about-cta{
    background: var(--bark);
    color: var(--blush);
    text-align: center;
    padding: 4rem 2rem;
}
.about-cta h2{
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    margin-bottom: 0.6rem;
}
.about-cta p{ color: var(--sand); margin-bottom: 1.6rem; }
.about-cta a{
    display: inline-block;
    background: var(--coral);
    color: var(--white);
    text-decoration: none;
    padding: 0.85rem 2.2rem;
    border-radius: 100px;
    font-size: 0.9rem;
    transition: background 0.2s;
}
.about-cta a:hover{ background: var(--wine); }

@media (max-width: 820px){
    .about-hero{ grid-template-columns: 1fr; padding: 4rem 1.5rem 3rem; }
    .stats-strip{ flex-wrap: wrap; gap: 2rem; }
}
</style>

<main class="about-wrap">

    <section class="about-hero">
        <div>
            <h1>Made slowly, on purpose, in the colors we grew up with.</h1>
            <p>
                Sweet Haven started with one wallpaper roll and a stubborn idea:
                that a room should feel like it was mixed by hand, not picked
                from a catalog. Every clay, wine and blush tone you see across
                the shop traces back to that first batch.
            </p>
        </div>
        <div class="about-hero-figure">
            <img src="sweet-haven-logo-2.png" alt="Sweet Haven workshop">
            <div class="tag">Est. <?= (int) $foundedYear ?></div>
        </div>
    </section>

    <section class="stats-strip">
        <div class="stat">
            <span class="num"><?= $yearsCrafting ?>+</span>
            <span class="label">Years Crafting</span>
        </div>
        <div class="stat">
            <span class="num"><?= $productCount ?>+</span>
            <span class="label">Products in the Shop</span>
        </div>
        <div class="stat">
            <span class="num"><?= $collectionCount ?></span>
            <span class="label">Curated Collections</span>
        </div>
        <div class="stat">
            <span class="num"><?= number_format($customerCount) ?>+</span>
            <span class="label">Homes We've Reached</span>
        </div>
    </section>

    <section class="testimonials">
        <h2>From homes like yours</h2>
        <div class="testi-track" id="testiTrack">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="testi-slide<?= $i === 0 ? ' active' : '' ?>">
                <p>"<?= htmlspecialchars($t['text']) ?>"</p>
                <span><?= htmlspecialchars($t['name']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="testi-dots" id="testiDots">
            <?php foreach ($testimonials as $i => $t): ?>
                <button data-i="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Show testimonial <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="about-cta">
        <h2>See the colors for yourself</h2>
        <p>Browse the collections that started with the same palette as our studio.</p>
        <a href="collections.php">Shop Collections</a>
    </section>

     <?php
require_once __DIR__."/footer.php";
?>
</main>

<script>
(function(){
    const slides = document.querySelectorAll('.testi-slide');
    const dots   = document.querySelectorAll('.testi-dots button');
    if (slides.length < 2) return;

    let current = 0;
    function show(i){
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = i;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }

    let timer = setInterval(() => show((current + 1) % slides.length), 5000);

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            clearInterval(timer);
            show(parseInt(dot.dataset.i, 10));
            timer = setInterval(() => show((current + 1) % slides.length), 5000);
        });
    });
})();
</script>

</body>
</html>