<?php
session_start();
header('Content-Type: application/json');

$cart = $_SESSION['cart'] ?? [];
$total_items = 0;
$total_price = 0;

foreach ($cart as $item) {
    $total_items++;
    $item_price = floatval($item['price']);
    foreach ($item['addons'] as $addon) {
        $item_price += floatval($addon['price']);
    }
    $total_price += $item_price;
}

echo json_encode([
    'total_items' => $total_items,
    'total_price' => $total_price
]);
?>