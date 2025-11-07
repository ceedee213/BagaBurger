<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function getItemDetails($conn, $codes) {
    if (empty($codes)) return [];
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $types = str_repeat('s', count($codes));
    $stmt = $conn->prepare("SELECT code, name, price FROM menu WHERE code IN ($placeholders)");
    $stmt->bind_param($types, ...$codes);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = [];
    while ($row = $result->fetch_assoc()) {
        $details[$row['code']] = $row;
    }
    return $details;
}

switch ($action) {
    case 'submit_item': // Handles both adding and editing
        $item_code = $_POST['item_code'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 1);
        $addons_codes = $_POST['addons'] ?? [];
        $original_ids_json = $_POST['original_ids'] ?? '[]';
        $original_ids = json_decode($original_ids_json, true);

        // If this is an edit, remove the old items first
        if (!empty($original_ids)) {
            foreach ($original_ids as $id) {
                unset($_SESSION['cart'][$id]);
            }
        }

        // Add the new or updated items
        $main_item_details = getItemDetails($conn, [$item_code]);
        $addons_details = getItemDetails($conn, $addons_codes);

        if (!empty($main_item_details[$item_code])) {
            for ($i = 0; $i < $quantity; $i++) {
                $unique_id = uniqid('item_');
                $_SESSION['cart'][$unique_id] = [
                    'code' => $item_code,
                    'name' => $main_item_details[$item_code]['name'],
                    'price' => $main_item_details[$item_code]['price'],
                    'addons' => array_values($addons_details)
                ];
            }
        }
        echo json_encode(['success' => true]);
        break;

    case 'delete_item':
        $item_ids_json = $_POST['ids'] ?? '[]';
        $item_ids = json_decode($item_ids_json, true);
        foreach ($item_ids as $id) {
            unset($_SESSION['cart'][$id]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        break;

    case 'view':
    default:
        $groupedCart = [];
        $totalPrice = 0;

        foreach ($_SESSION['cart'] as $id => $item) {
            $item_price = floatval($item['price']);
            $addon_names = [];
            $addon_codes = [];
            if (!empty($item['addons'])) {
                foreach ($item['addons'] as $addon) {
                    $item_price += floatval($addon['price']);
                    $addon_names[] = $addon['name'];
                    $addon_codes[] = $addon['code'];
                }
            }
            sort($addon_names); // Ensure consistent ordering
            $description = $item['name'] . (!empty($addon_names) ? ' (w/ ' . implode(', ', $addon_names) . ')' : '');

            if (isset($groupedCart[$description])) {
                $groupedCart[$description]['quantity']++;
                $groupedCart[$description]['ids'][] = $id;
            } else {
                $groupedCart[$description] = [
                    'name' => $item['name'],
                    'description' => $description,
                    'price' => $item_price,
                    'quantity' => 1,
                    'image_code' => $item['code'],
                    'main_item_code' => $item['code'],
                    'addon_codes' => $addon_codes,
                    'ids' => [$id]
                ];
            }
            $totalPrice += $item_price;
        }
        echo json_encode(['items' => array_values($groupedCart), 'totalPrice' => $totalPrice]);
        break;
}
?>