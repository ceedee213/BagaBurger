<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $item_code = $_POST['item_code'];
    $quantity = intval($_POST['quantity']);
    $addons_codes = $_POST['addons'] ?? [];

    // Fetch main item details
    $stmt = $conn->prepare("SELECT name, price FROM menu WHERE code = ?");
    $stmt->bind_param("s", $item_code);
    $stmt->execute();
    $main_item = $stmt->get_result()->fetch_assoc();

    // Fetch addons details
    $addons_details = [];
    if (!empty($addons_codes)) {
        $placeholders = implode(',', array_fill(0, count($addons_codes), '?'));
        $stmt = $conn->prepare("SELECT code, name, price FROM menu WHERE code IN ($placeholders)");
        $stmt->bind_param(str_repeat('s', count($addons_codes)), ...$addons_codes);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $addons_details[$row['code']] = $row;
        }
    }

    // Add the customized item(s) to the cart
    for ($i = 0; $i < $quantity; $i++) {
        $unique_id = uniqid('item_'); // Create a unique ID for each individual burger
        $_SESSION['cart'][$unique_id] = [
            'code' => $item_code,
            'name' => $main_item['name'],
            'price' => $main_item['price'],
            'addons' => $addons_details
        ];
    }
}
?>