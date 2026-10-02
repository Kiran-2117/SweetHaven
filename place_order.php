<?php

session_start();
require_once __DIR__ . '/database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$name    = trim($_POST['shipping_name'] ?? '');
$phone   = trim($_POST['shipping_phone'] ?? '');
$address = trim($_POST['shipping_address'] ?? '');

$paymentMethod = $_POST['payment_method'] ?? 'cod';
if (!in_array($paymentMethod, ['cod', 'esewa', 'card'], true)) {
    $paymentMethod = 'cod';
}

$ids = isset($_POST['cart_items_id']) ? (array) $_POST['cart_items_id'] : [];
$ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));

if (!$name || !$phone || !$address || empty($ids)) {
    header('Location: checkout.php?error=missing_fields');
    exit;
}

// ---- Find the cart and pull the items being ordered ----
$findCartStmt = mysqli_prepare($conn, "SELECT cart_id FROM cart WHERE id = ? AND status = 'active' LIMIT 1");
mysqli_stmt_bind_param($findCartStmt, "i", $user_id);
mysqli_stmt_execute($findCartStmt);
$cartRow = mysqli_fetch_assoc(mysqli_stmt_get_result($findCartStmt));

if (!$cartRow) {
    header('Location: cart.php');
    exit;
}
$cart_id = (int) $cartRow['cart_id'];

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$itemsStmt = mysqli_prepare($conn, "SELECT cart_items_id, product_id, item_type, price, quantity
                                     FROM cart_items WHERE cart_id = ? AND cart_items_id IN ($placeholders)");
$types = 'i' . str_repeat('i', count($ids));
$params = array_merge([$cart_id], $ids);
mysqli_stmt_bind_param($itemsStmt, $types, ...$params);
mysqli_stmt_execute($itemsStmt);
$orderItems = mysqli_fetch_all(mysqli_stmt_get_result($itemsStmt), MYSQLI_ASSOC);

if (empty($orderItems)) {
    header('Location: cart.php');
    exit;
}

$totalAmount = 0;
foreach ($orderItems as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

$fullAddress = "Name: $name\nPhone: $phone\nAddress: $address";

// A simple, readable delivery tracking code -- e.g. DEL-3F9A2B7C
$deliveryCode = 'DEL-' . strtoupper(bin2hex(random_bytes(4)));

// ---- Write everything as one transaction: if any step fails, nothing saves ----
mysqli_begin_transaction($conn);

try {

    // 0) Deduct stock FIRST. The "AND stock >= qty" condition makes this atomic:
    //    if two people race for the last items, only one UPDATE succeeds.
    //    CHANGE: wallpapers are now deducted too (from the wallpapers table).
    $stockProduct = mysqli_prepare($conn, "UPDATE products SET stock = stock - ?
                                            WHERE product_id = ? AND stock >= ?");
    $stockWall    = mysqli_prepare($conn, "UPDATE wallpapers SET stock = stock - ?
                                            WHERE id = ? AND stock >= ?");
    $nameProduct  = mysqli_prepare($conn, "SELECT name, stock FROM products WHERE product_id = ?");
    $nameWall     = mysqli_prepare($conn, "SELECT name, stock FROM wallpapers WHERE id = ?");

    foreach ($orderItems as $item) {
        $isWall = ($item['item_type'] === 'wallpaper');
        $pid = (int) $item['product_id'];
        $qty = (int) $item['quantity'];

        // Pick the right table for this item
        $stockStmt = $isWall ? $stockWall : $stockProduct;
        $lookup    = $isWall ? $nameWall  : $nameProduct;

        mysqli_stmt_bind_param($stockStmt, "iii", $qty, $pid, $qty);
        mysqli_stmt_execute($stockStmt);

        if (mysqli_stmt_affected_rows($stockStmt) < 1) {
            // Not enough stock: find out what to tell the customer
            mysqli_stmt_bind_param($lookup, "i", $pid);
            mysqli_stmt_execute($lookup);
            $p = mysqli_fetch_assoc(mysqli_stmt_get_result($lookup));
            $pname = $p['name'] ?? 'An item';
            $left  = (int) ($p['stock'] ?? 0);

            throw new RuntimeException(
                $left > 0
                    ? "Sorry, only $left of \"$pname\" left in stock. Please reduce the quantity in your cart."
                    : "Sorry, \"$pname\" just went out of stock."
            );
        }
    }

    // 1) The order itself
    $orderStmt = mysqli_prepare($conn, "INSERT INTO orders (id, total_amount, status, shipping_address, created_at)
                                         VALUES (?, ?, 'pending', ?, NOW())");
    mysqli_stmt_bind_param($orderStmt, "ids", $user_id, $totalAmount, $fullAddress);
    mysqli_stmt_execute($orderStmt);
    $order_id = mysqli_insert_id($conn);

    // 2) One row per item ordered
    $itemInsertStmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, item_type, quantity, price_each)
                                              VALUES (?, ?, ?, ?, ?)");
    foreach ($orderItems as $item) {
        mysqli_stmt_bind_param(
            $itemInsertStmt, "iisid",
            $order_id, $item['product_id'], $item['item_type'], $item['quantity'], $item['price']
        );
        mysqli_stmt_execute($itemInsertStmt);
    }

    // 3) The payment record, linked to this order
    $paymentStmt = mysqli_prepare($conn, "INSERT INTO payment (id, order_id, amount, payment_method, date, status)
                                           VALUES (?, ?, ?, ?, CURDATE(), 'pending')");
    mysqli_stmt_bind_param($paymentStmt, "iids", $user_id, $order_id, $totalAmount, $paymentMethod);
    mysqli_stmt_execute($paymentStmt);

    // 4) A delivery record, so the confirmation page can show a tracking code
    $deliveryStmt = mysqli_prepare($conn, "INSERT INTO delivery (id, order_id, deliver_code, date)
                                            VALUES (?, ?, ?, CURDATE())");
    mysqli_stmt_bind_param($deliveryStmt, "iis", $user_id, $order_id, $deliveryCode);
    mysqli_stmt_execute($deliveryStmt);

    // 5) Clear those items out of the cart
    $orderedIds = array_column($orderItems, 'cart_items_id');
    $delPlaceholders = implode(',', array_fill(0, count($orderedIds), '?'));
    $delStmt = mysqli_prepare($conn, "DELETE FROM cart_items WHERE cart_id = ? AND cart_items_id IN ($delPlaceholders)");
    $delTypes = 'i' . str_repeat('i', count($orderedIds));
    $delParams = array_merge([$cart_id], $orderedIds);
    mysqli_stmt_bind_param($delStmt, $delTypes, ...$delParams);
    mysqli_stmt_execute($delStmt);

    mysqli_commit($conn);

} catch (Throwable $e) {
    // Undo everything, including the stock deduction
    mysqli_rollback($conn);

    $_SESSION['checkout_error'] = ($e instanceof RuntimeException)
        ? $e->getMessage()
        : 'Something went wrong while placing your order. Please try again.';

    header('Location: checkout.php?' . http_build_query(['cart_items_id' => $ids]));
    exit;
}

header('Location: order_confirmation.php?order_id=' . $order_id);
exit;