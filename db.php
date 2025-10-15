<?php
// Set the default timezone for all date/time functions to Philippine Time
date_default_timezone_set('Asia/Manila');

$host = "localhost";
$user = "root";
$pass = "";
$db = "baga_burger";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>