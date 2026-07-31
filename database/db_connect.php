
<?php

$host = "localhost";
$username = "root";      // default XAMPP/WAMP username
$password = "";          // default XAMPP/WAMP password (empty)
$database = "sweet_haven";    // the database we created in create_table.sql
 
// Create connection
$conn = mysqli_connect($host, $username, $password, $database);
 
// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
 