<?php
// DBConn.php - Database connection file
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ClothingStore";
 
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>