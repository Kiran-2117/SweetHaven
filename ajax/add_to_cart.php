<?php

session_start();
header('Content-Type: application/json');

// ---- 1) Must be logged in ----
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success'  => false,
        'reason'   => 'not_logged_in',
        'redirect' => 'login.php'
    ]);
    exit;
}

require_once '../database/db_connect.php';

$user_id    = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$item_type  = (isset($_POST['item_type']) && $_POST['item_type'] === 'wallpaper') ? 'wallpaper' : 'product';
$quantity   = isset($_POST['quantity']) ? max(1, (int) $_POST['quantity']) : 1;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'reason' => 'invalid_product']);
    exit;
}

try {

    if ($item_type === 'wallpaper') {
        $lookupStmt = mysqli_prepare($conn, "SELECT id, name, price FROM wallpapers WHERE id = ?");
    } else {
        $lookupStmt = mysqli_prepare($conn, "SELECT product_id, name, price FROM products WHERE product_id = ?");
    }
    mysqli_stmt_bind_param($lookupStmt, "i", $product_id);
    mysqli_stmt_execute($lookupStmt);
    $item = mysqli_fetch_assoc(mysqli_stmt_get_result($lookupStmt));

    if (!$item) {
        echo json_encode(['success' => false, 'reason' => 'item_not_found']);
        exit;
    }

    $price = $item['price'];
    $name  = $item['name'];

    $cart_id = null;

    $findCartStmt = mysqli_prepare($conn, "SELECT cart_id FROM cart WHERE id = ? AND status = 'active' LIMIT 1");
    mysqli_stmt_bind_param($findCartStmt, "i", $user_id);
    mysqli_stmt_execute($findCartStmt);
    $cartRow = mysqli_fetch_assoc(mysqli_stmt_get_result($findCartStmt));

    if ($cartRow) {
        $cart_id = $cartRow['cart_id'];
    } else {
        $createCartStmt = mysqli_prepare($conn, "INSERT INTO cart (id, description, status) VALUES (?, 'Shopping Cart', 'active')");
        mysqli_stmt_bind_param($createCartStmt, "i", $user_id);
        mysqli_stmt_execute($createCartStmt);
        $cart_id = mysqli_insert_id($conn);
    }


    $existingStmt = mysqli_prepare($conn, "SELECT cart_items_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND item_type = ?");
    mysqli_stmt_bind_param($existingStmt, "iis", $cart_id, $product_id, $item_type);
    mysqli_stmt_execute($existingStmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($existingStmt));

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        $updateStmt = mysqli_prepare($conn, "UPDATE cart_items SET quantity = ? WHERE cart_items_id = ?");
        mysqli_stmt_bind_param($updateStmt, "ii", $newQty, $existing['cart_items_id']);
        mysqli_stmt_execute($updateStmt);
        $cart_items_id = $existing['cart_items_id'];
    } else {
        $insertStmt = mysqli_prepare($conn, "INSERT INTO cart_items (cart_id, product_id, item_type, price, quantity, description) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insertStmt, "iisdis", $cart_id, $product_id, $item_type, $price, $quantity, $name);
        mysqli_stmt_execute($insertStmt);
        $cart_items_id = mysqli_insert_id($conn);
    }

    
    $countStmt = mysqli_prepare($conn, "SELECT SUM(quantity) AS total FROM cart_items WHERE cart_id = ?");
    mysqli_stmt_bind_param($countStmt, "i", $cart_id);
    mysqli_stmt_execute($countStmt);
    $countRow = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
    $newCount = $countRow['total'] ? (int) $countRow['total'] : 0;

    echo json_encode([
        'success'       => true,
        'cart_count'    => $newCount,
        'item_name'     => $name,
        'cart_items_id' => (int) $cart_items_id
    ]);

} catch (Throwable $e) {
    
    error_log('add_to_cart.php failed: ' . $e->getMessage());
    http_response_code(200); 
    echo json_encode([
        'success' => false,
        'reason'  => 'server_error',
        'message' => $e->getMessage() 
    ]);
}