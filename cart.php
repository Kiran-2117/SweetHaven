<?php
session_start();
require_once __DIR__ . '/database/db_connect.php';

// Cart page requires login -- bounce anonymous visitors straight to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ---- Find this user's active cart ----
$cart_id = null;
$cartStmt = mysqli_prepare($conn, "SELECT cart_id FROM cart WHERE id = ? AND status = 'active' LIMIT 1");
mysqli_stmt_bind_param($cartStmt, "i", $user_id);
mysqli_stmt_execute($cartStmt);
$cartResult = mysqli_stmt_get_result($cartStmt);
if ($cartRow = mysqli_fetch_assoc($cartResult)) {
    $cart_id = $cartRow['cart_id'];
}

// ---- Fetch its items, pulling the name + image from whichever source table applies ----
$cartItems  = [];
$grandTotal = 0;

if ($cart_id) {
    $itemsStmt = mysqli_prepare($conn, "SELECT
                    ci.cart_items_id, ci.product_id, ci.item_type, ci.price, ci.quantity, ci.description,
                    COALESCE(p.name, w.name) AS item_name,
                    COALESCE(p.image, w.image) AS item_image
                 FROM cart_items ci
                 LEFT JOIN products   p ON ci.item_type = 'product'   AND ci.product_id = p.product_id
                 LEFT JOIN wallpapers w ON ci.item_type = 'wallpaper' AND ci.product_id = w.id
                 WHERE ci.cart_id = ?
                 ORDER BY ci.cart_items_id DESC");
    mysqli_stmt_bind_param($itemsStmt, "i", $cart_id);
    mysqli_stmt_execute($itemsStmt);
    $itemsResult = mysqli_stmt_get_result($itemsStmt);

    while ($row = mysqli_fetch_assoc($itemsResult)) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $grandTotal += $row['subtotal'];
        $cartItems[] = $row;
    }
}

include __DIR__ . '/header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root{
        --blush:#FFDBBB;
        --sand:#CCBEB1;
        --clay:#997E67;
        --bark:#664930;
        --wine:#64242F;
        --coral:#B44446;
        --rose:#FC8F8F;
        --stone:#DFD9D8;
        --dark:#2C1A0E;
        --white:#FDFAF7;
    }

    .cart-page{
        max-width: 1100px;
        margin: 0 auto;
        padding: 130px 2rem 5rem;
        font-family: 'Jost', sans-serif;
        color: var(--dark);
    }

    .cart-page h1{
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.4rem;
        font-weight: 600;
        color: var(--wine);
        margin-bottom: 0.3rem;
    }

    .cart-page .subhead{
        color: var(--clay);
        font-size: 0.9rem;
        margin-bottom: 2.5rem;
    }

    .cart-layout{
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 2.5rem;
        align-items: start;
    }

    .cart-toolbar{
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--stone);
        margin-bottom: 1rem;
    }

    .select-all-label{
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.85rem;
        color: var(--bark);
        cursor: pointer;
    }

    #deleteSelectedBtn{
        background: none;
        border: 1px solid var(--coral);
        color: var(--coral);
        padding: 0.45rem 1rem;
        border-radius: 2rem;
        font-family: 'Jost', sans-serif;
        font-size: 0.8rem;
        letter-spacing: 0.03rem;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    #deleteSelectedBtn:hover:not(:disabled){
        background: var(--coral);
        color: var(--white);
    }
    #deleteSelectedBtn:disabled{
        opacity: 0.4;
        cursor: not-allowed;
    }

    .cart-item-row{
        display: grid;
        grid-template-columns: auto 90px 1fr auto auto;
        align-items: center;
        gap: 1.2rem;
        background: var(--white);
        border: 1px solid var(--stone);
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .cart-item-checkbox{
        width: 18px; height: 18px;
        accent-color: var(--wine);
        cursor: pointer;
    }

    .cart-item-row img{
        width: 90px; height: 90px;
        object-fit: cover;
        border-radius: 0.7rem;
        background: var(--sand);
    }

    .item-info .item-name{
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark);
    }
    .item-info .item-type-tag{
        display: inline-block;
        margin-top: 0.3rem;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05rem;
        color: var(--clay);
        background: var(--blush);
        padding: 0.15rem 0.6rem;
        border-radius: 1rem;
    }
    .item-info .item-price-row{
        margin-top: 0.5rem;
        font-size: 0.85rem;
        color: var(--bark);
    }

    .item-subtotal{
        font-family: 'Cormorant Garamond', serif;
        font-weight: 600;
        font-size: 1.15rem;
        color: var(--wine);
        white-space: nowrap;
    }

    .single-delete-btn{
        background: none;
        border: none;
        color: var(--clay);
        font-size: 1.1rem;
        cursor: pointer;
        transition: color 0.2s;
        padding: 0.4rem;
    }
    .single-delete-btn:hover{
        color: var(--coral);
    }

    .empty-cart{
        text-align: center;
        padding: 4rem 1rem;
        background: var(--white);
        border: 1px dashed var(--sand);
        border-radius: 1rem;
        color: var(--clay);
    }
    .continue-shopping-btn{
        display: inline-block;
        margin-top: 1rem;
        background: var(--wine);
        color: var(--white);
        text-decoration: none;
        padding: 0.7rem 1.8rem;
        border-radius: 2rem;
        font-size: 0.85rem;
    }

    #cartSummary{
        background: var(--wine);
        color: var(--blush);
        border-radius: 1rem;
        padding: 1.8rem;
        position: sticky;
        top: 100px;
    }
    #cartSummary h3{
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        margin-bottom: 1rem;
        color: var(--white);
    }
    .summary-row{
        display: flex;
        justify-content: space-between;
        font-size: 0.88rem;
        margin-bottom: 0.7rem;
        color: var(--sand);
    }
    #selectedTotal{
        color: var(--white);
        font-weight: 600;
    }
    .checkout-btn{
        display: block;
        width: 100%;
        margin-top: 1.2rem;
        background: var(--blush);
        color: var(--wine);
        border: none;
        padding: 0.85rem;
        border-radius: 2rem;
        font-family: 'Jost', sans-serif;
        font-weight: 600;
        font-size: 0.9rem;
        letter-spacing: 0.03rem;
        cursor: pointer;
    }
    .summary-note{
        margin-top: 0.8rem;
        font-size: 0.72rem;
        color: var(--sand);
    }

    @media (max-width: 800px){
        .cart-layout{ grid-template-columns: 1fr; }
        .cart-item-row{ grid-template-columns: auto 70px 1fr; }
        .item-subtotal, .single-delete-btn{ grid-column: span 3; justify-self: end; }
    }
</style>

<div class="cart-page">
    <h1>Your Cart</h1>
    <div class="subhead"><?= count($cartItems) ?> item<?= count($cartItems) === 1 ? '' : 's' ?> in your Sweet Haven cart</div>

    <div class="cart-layout">

        <div class="cart-items-col">
            <?php if (!empty($cartItems)): ?>
                <div class="cart-toolbar">
                    <label class="select-all-label">
                        <input type="checkbox" id="selectAllItems">
                        Select all
                    </label>
                    <button id="deleteSelectedBtn" disabled>Remove Selected</button>
                </div>
            <?php endif; ?>

            <div id="cartItemsList">
                <?php if (empty($cartItems)): ?>
                    <div class="empty-cart">
                        <p>Your cart is empty.</p>
                        <a href="decor.php" class="continue-shopping-btn">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item-row" id="cart-row-<?= $item['cart_items_id'] ?>">
                            <input
                                type="checkbox"
                                class="cart-item-checkbox"
                                value="<?= $item['cart_items_id'] ?>"
                                data-price="<?= htmlspecialchars($item['price']) ?>"
                                data-quantity="<?= htmlspecialchars($item['quantity']) ?>"
                            >

                            <img
                                src="<?= $item['item_image'] ? htmlspecialchars($item['item_image']) : 'assets/images/placeholder.png' ?>"
                                alt="<?= htmlspecialchars($item['item_name'] ?? 'Product') ?>"
                            >

                            <div class="item-info">
                                <div class="item-name"><?= htmlspecialchars($item['item_name'] ?? 'Item #' . $item['product_id']) ?></div>
                                <span class="item-type-tag"><?= $item['item_type'] === 'wallpaper' ? 'Wallpaper' : 'Decor' ?></span>
                                <div class="item-price-row">
                                    Rs. <?= number_format($item['price'], 2) ?> &times; <?= $item['quantity'] ?>
                                </div>
                            </div>

                            <div class="item-subtotal">Rs. <?= number_format($item['subtotal'], 2) ?></div>

                            <button class="single-delete-btn" data-id="<?= $item['cart_items_id'] ?>" title="Remove item">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="cartSummary" style="<?= empty($cartItems) ? 'display:none;' : '' ?>">
            <h3>Order Summary</h3>
            <div class="summary-row">
                <span>Cart total</span>
                <span>Rs. <?= number_format($grandTotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Selected total</span>
                <span id="selectedTotal">Rs. 0.00</span>
            </div>
            <button class="checkout-btn" id="checkoutBtn">Proceed to Checkout</button>
            <div class="summary-note">Tick items on the left to see their combined total here.</div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/cart.js"></script>

</body>
</html>