<?php
require 'db.php';
header('Content-Type: application/json');

// Fetch only items categorized as 'Add-ons'
$sql = "SELECT code, name, price FROM menu WHERE stock > 0 AND category = 'Add-ons' ORDER BY name ASC";
$result = $conn->query($sql);
$addons = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($addons);
?>