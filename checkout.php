<?php
session_start();
require_once __DIR__ . '/database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$requestedIds = [];
if (isset($_GET['cart_items_id'])) {
    $requestedIds = array_map('intval', (array) $_GET['cart_items_id']);
    $requestedIds = array_values(array_filter($requestedIds, fn($id) => $id > 0));
}

// ---- Pull profile info to pre-fill the shipping fields ----
$userStmt = mysqli_prepare($conn, "SELECT full_name, phone, shipping_address, billing_address FROM users WHERE id = ?");
mysqli_stmt_bind_param($userStmt, "i", $user_id);
mysqli_stmt_execute($userStmt);
$profile = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt)) ?: [];

$prefillName    = $profile['full_name'] ?? '';
$prefillPhone   = $profile['phone'] ?? '';
$prefillAddress = !empty($profile['shipping_address']) ? $profile['shipping_address'] : ($profile['billing_address'] ?? '');
$hasSavedAddress = trim($prefillAddress) !== '';

// ---- Find the cart and the items being checked out ----
$cart_id = null;
$cartStmt = mysqli_prepare($conn, "SELECT cart_id FROM cart WHERE id = ? AND status = 'active' LIMIT 1");
mysqli_stmt_bind_param($cartStmt, "i", $user_id);
mysqli_stmt_execute($cartStmt);
if ($cartRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cartStmt))) {
    $cart_id = $cartRow['cart_id'];
}

$checkoutItems = [];
$grandTotal = 0;

if ($cart_id) {
    if (!empty($requestedIds)) {
        $placeholders = implode(',', array_fill(0, count($requestedIds), '?'));
        $sql = "SELECT ci.cart_items_id, ci.product_id, ci.item_type, ci.price, ci.quantity,
                       COALESCE(p.name, w.name) AS item_name,
                       COALESCE(p.image, w.image) AS item_image
                FROM cart_items ci
                LEFT JOIN products   p ON ci.item_type = 'product'   AND ci.product_id = p.product_id
                LEFT JOIN wallpapers w ON ci.item_type = 'wallpaper' AND ci.product_id = w.id
                WHERE ci.cart_id = ? AND ci.cart_items_id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        $types = 'i' . str_repeat('i', count($requestedIds));
        $params = array_merge([$cart_id], $requestedIds);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    } else {
        $sql = "SELECT ci.cart_items_id, ci.product_id, ci.item_type, ci.price, ci.quantity,
                       COALESCE(p.name, w.name) AS item_name,
                       COALESCE(p.image, w.image) AS item_image
                FROM cart_items ci
                LEFT JOIN products   p ON ci.item_type = 'product'   AND ci.product_id = p.product_id
                LEFT JOIN wallpapers w ON ci.item_type = 'wallpaper' AND ci.product_id = w.id
                WHERE ci.cart_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cart_id);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $grandTotal += $row['subtotal'];
        $checkoutItems[] = $row;
    }
}

include __DIR__ . '/header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    :root{ --sand:#CCBEB1; --clay:#997E67; --bark:#664930; --wine:#64242F; --coral:#B44446; --stone:#DFD9D8; --dark:#2C1A0E; --white:#FDFAF7; }
    .checkout-page{ max-width: 1100px; margin: 0 auto; padding: 130px 2rem 5rem; font-family: 'Jost', sans-serif; color: var(--dark); }
    .checkout-page h1{ font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; font-weight: 600; color: var(--wine); margin-bottom: 2rem; }
    .checkout-layout{ display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem; align-items: start; }
    .section-card{ background: var(--white); border: 1px solid var(--stone); border-radius: 1rem; padding: 1.8rem; margin-bottom: 1.5rem; }
    .section-card h3{ font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: var(--wine); margin-bottom: 1.2rem; }
    .form-group{ margin-bottom: 1rem; }
    .form-group label{ display: block; font-size: 0.8rem; color: var(--bark); margin-bottom: 0.35rem; }
    .form-group input, .form-group textarea{ width: 100%; padding: 0.65rem 0.9rem; border: 1px solid var(--stone); border-radius: 0.6rem; font-family: 'Jost', sans-serif; font-size: 0.9rem; color: var(--dark); }
    .prefill-note{ background: #FFF7ED; border: 1px solid #FFDBBB; color: var(--bark); font-size: 0.78rem; padding: 0.6rem 0.9rem; border-radius: 0.6rem; margin-bottom: 1rem; }
    .prefill-note a{ color: var(--wine); font-weight: 600; }
    .checkout-item{ display: grid; grid-template-columns: 64px 1fr auto; align-items: center; gap: 1rem; padding: 0.9rem 0; border-bottom: 1px solid var(--stone); }
    .checkout-item img{ width: 64px; height: 64px; object-fit: cover; border-radius: 0.6rem; background: var(--sand); }
    .checkout-item .name{ font-weight: 500; font-size: 0.95rem; }
    .checkout-item .qty{ font-size: 0.8rem; color: var(--clay); }
    .checkout-item .line-total{ font-weight: 600; color: var(--wine); white-space: nowrap; }
    .payment-option{ display: flex; align-items: center; gap: 0.9rem; border: 1px solid var(--stone); border-radius: 0.8rem; padding: 0.9rem 1rem; margin-bottom: 0.8rem; cursor: pointer; }
    .payment-option:has(input:checked){ border-color: var(--wine); background: #FDF3EE; }
    .payment-option input{ accent-color: var(--wine); width: 18px; height: 18px; }
    .payment-option .option-icon{ font-size: 1.2rem; color: var(--wine); width: 24px; text-align: center; }
    .payment-option .option-label{ font-weight: 500; font-size: 0.92rem; }
    .payment-option .option-sub{ font-size: 0.75rem; color: var(--clay); }
    #orderSummary{ background: var(--wine); color: var(--sand); border-radius: 1rem; padding: 1.8rem; position: sticky; top: 100px; }
    #orderSummary h3{ font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; color: var(--white); margin-bottom: 1rem; }
    .summary-row{ display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.7rem; }
    .summary-row.total{ color: var(--white); font-weight: 600; font-size: 1.1rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 0.8rem; margin-top: 0.8rem; }
    .place-order-btn{ display: block; width: 100%; margin-top: 1.2rem; background: var(--sand); color: var(--wine); border: none; padding: 0.9rem; border-radius: 2rem; font-family: 'Jost', sans-serif; font-weight: 600; font-size: 0.9rem; cursor: pointer; }
    .error-note{ color: var(--coral); background: #FFF0F0; border: 1px solid var(--coral); font-size: 0.82rem; padding: 0.7rem 1rem; border-radius: 0.6rem; margin-bottom: 1.5rem; }
    .empty-checkout{ text-align: center; padding: 4rem 1rem; background: var(--white); border: 1px dashed var(--sand); border-radius: 1rem; color: var(--clay); }
    @media (max-width: 800px){ .checkout-layout{ grid-template-columns: 1fr; } }
</style>

<div class="checkout-page">
    <h1>Checkout</h1>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_fields'): ?>
        <div class="error-note">Please fill in all shipping details before placing your order.</div>
    <?php endif; ?>

    <?php if (empty($checkoutItems)): ?>
        <div class="empty-checkout">
            <p>There's nothing to check out.</p>
            <a href="cart.php" class="continue-shopping-btn">Back to Cart</a>
        </div>
    <?php else: ?>
        <!--
            ONE form, top to bottom: shipping fields, payment choice, and the
            hidden list of which cart items are being ordered. Clicking
            "Place Order" submits everything at once to place_order.php.
        -->
        <form method="POST" action="place_order.php">
            <?php foreach ($checkoutItems as $item): ?>
                <input type="hidden" name="cart_items_id[]" value="<?= $item['cart_items_id'] ?>">
            <?php endforeach; ?>

            <div class="checkout-layout">
                <div>
                    <div class="section-card">
                        <h3>Shipping Details</h3>
                        <?php if ($hasSavedAddress): ?>
                            <div class="prefill-note">Pulled from your <a href="profile.php">profile</a> &mdash; feel free to edit for this order only.</div>
                        <?php else: ?>
                            <div class="prefill-note">No saved address yet on your <a href="profile.php">profile</a> &mdash; just fill it in below.</div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="shipName">Full name</label>
                            <input type="text" id="shipName" name="shipping_name" value="<?= htmlspecialchars($prefillName) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="shipPhone">Phone</label>
                            <input type="tel" id="shipPhone" name="shipping_phone" value="<?= htmlspecialchars($prefillPhone) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="shipAddress">Delivery address</label>
                            <textarea id="shipAddress" name="shipping_address" rows="3" placeholder="Street, city, landmark" required><?= htmlspecialchars($prefillAddress) ?></textarea>
                        </div>
                    </div>

                    <div class="section-card">
                        <h3>Payment Method</h3>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <span class="option-icon"><i class="fa-solid fa-money-bill-wave"></i></span>
                            <span><div class="option-label">Cash on Delivery</div><div class="option-sub">Pay when your order arrives</div></span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="esewa">
                            <span class="option-icon"><i class="fa-solid fa-wallet"></i></span>
                            <span><div class="option-label">eSewa</div><div class="option-sub">Pay with your eSewa wallet</div></span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="card">
                            <span class="option-icon"><i class="fa-regular fa-credit-card"></i></span>
                            <span><div class="option-label">Credit / Debit Card</div><div class="option-sub">Visa, Mastercard</div></span>
                        </label>
                    </div>

                    <div class="section-card">
                        <h3>Order Items</h3>
                        <?php foreach ($checkoutItems as $item): ?>
                            <div class="checkout-item">
                                <img src="<?= $item['item_image'] ? htmlspecialchars($item['item_image']) : 'assets/images/placeholder.png' ?>" alt="">
                                <div>
                                    <div class="name"><?= htmlspecialchars($item['item_name'] ?? 'Item') ?></div>
                                    <div class="qty">Qty <?= $item['quantity'] ?> &times; Rs. <?= number_format($item['price'], 2) ?></div>
                                </div>
                                <div class="line-total">Rs. <?= number_format($item['subtotal'], 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="orderSummary">
                    <h3>Order Total</h3>
                    <div class="summary-row"><span>Items (<?= count($checkoutItems) ?>)</span><span>Rs. <?= number_format($grandTotal, 2) ?></span></div>
                    <div class="summary-row total"><span>Total</span><span>Rs. <?= number_format($grandTotal, 2) ?></span></div>
                    <button type="submit" class="place-order-btn">Place Order</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>