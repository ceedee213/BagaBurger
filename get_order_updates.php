<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

// Security: Ensure a user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_ids_to_check = json_decode(file_get_contents('php://input'), true);

// If there are no order IDs to check, do nothing.
if (empty($order_ids_to_check) || !is_array($order_ids_to_check)) {
    echo json_encode([]);
    exit;
}

// Create placeholders for the IN clause (e.g., ?,?,?)
$placeholders = implode(',', array_fill(0, count($order_ids_to_check), '?'));
$types = str_repeat('i', count($order_ids_to_check)); // 'i' for integer

$sql = "SELECT id, status FROM orders WHERE user_id = ? AND id IN ($placeholders)";
$stmt = $conn->prepare($sql);

// Bind the user_id and then the list of order IDs
$stmt->bind_param("i" . $types, $user_id, ...$order_ids_to_check);
$stmt->execute();
$result = $stmt->get_result();

$statuses = [];
while ($row = $result->fetch_assoc()) {
    $statuses[$row['id']] = $row['status'];
}

echo json_encode($statuses);
?>