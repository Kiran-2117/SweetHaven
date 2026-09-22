<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'reason' => 'not_logged_in']);
    exit;
}

require_once '../database/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$ids = isset($_POST['cart_items_id']) ? (array) $_POST['cart_items_id'] : [];
$ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));

if (empty($ids)) {
    echo json_encode(['success' => false, 'reason' => 'no_items_selected']);
    exit;
}

$findCartStmt = mysqli_prepare($conn, "SELECT cart_id FROM cart WHERE id = ? AND status = 'active' LIMIT 1");
mysqli_stmt_bind_param($findCartStmt, "i", $user_id);
mysqli_stmt_execute($findCartStmt);
$cartRow = mysqli_fetch_assoc(mysqli_stmt_get_result($findCartStmt));

if (!$cartRow) {
    echo json_encode(['success' => false, 'reason' => 'no_cart']);
    exit;
}
$cart_id = (int) $cartRow['cart_id'];

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "DELETE FROM cart_items WHERE cart_id = ? AND cart_items_id IN ($placeholders)";
$stmt = mysqli_prepare($conn, $sql);
$bindTypes = 'i' . str_repeat('i', count($ids));
$bindParams = array_merge([$cart_id], $ids);
mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindParams);
mysqli_stmt_execute($stmt);


$countStmt = mysqli_prepare($conn, "SELECT SUM(quantity) AS total FROM cart_items WHERE cart_id = ?");
mysqli_stmt_bind_param($countStmt, "i", $cart_id);
mysqli_stmt_execute($countStmt);
$countRow = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
$newCount = $countRow['total'] ? (int) $countRow['total'] : 0;

echo json_encode(['success' => true, 'cart_count' => $newCount]);