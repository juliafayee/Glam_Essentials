<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "glam_essentials";

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// In includes/config.php
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/draftGlamEssentials';

    // // In config.php
    // if (session_status() === PHP_SESSION_NONE) {
    //     session_start();
    // }
?>
