<?php

mysqli_report(MYSQLI_REPORT_OFF);

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "sweet_haven";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_admin_login() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: admin_login.php");
        exit;
    }
}