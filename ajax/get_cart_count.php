<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['cart_count' => 0]);
    exit;
}

require_once '../database/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$sql = "SELECT SUM(ci.quantity) AS total
        FROM cart c
        JOIN cart_items ci ON ci.cart_id = c.cart_id
        WHERE c.id = $user_id AND c.status = 'active'";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$count = $row['total'] ? (int) $row['total'] : 0;

echo json_encode(['cart_count' => $count]);