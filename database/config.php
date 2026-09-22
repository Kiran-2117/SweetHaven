<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sweet_haven');
define('DB_USER', root);
define('DB_PASS', '');

if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(empty($_SESSION['sh_session_id'])) {
    $_SESSION['sh_session_id']= bin2hex(random_bytes(16));
}

$conn= @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if($conn){
    mysqli_set_charset($conn, 'utf8mb4');
}

?>