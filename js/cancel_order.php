<?php
session_start();
require 'db.php';

// Security check: ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if an order ID was provided to cancel
if (!isset($_GET['order_id'])) {
    header("Location: menu.php"); // Redirect to menu if no ID is given
    exit;
}

$order_id_to_cancel = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Begin a transaction to ensure both tables are updated correctly
$conn->begin_transaction();

try {
    // Security check: Delete only if the order belongs to the current user and is pending
    $sql_check = "SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'Pending Payment'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $order_id_to_cancel, $user_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 1) {
        // First, delete the associated items from the order_items table
        $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt_items->bind_param("i", $order_id_to_cancel);
        $stmt_items->execute();
        $stmt_items->close();

        // Second, delete the main order from the orders table
        $stmt_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt_order->bind_param("i", $order_id_to_cancel);
        $stmt_order->execute();
        $stmt_order->close();
    }
    
    // Commit the transaction
    $conn->commit();

} catch (Exception $e) {
    // If anything fails, roll back the changes to prevent partial deletion
    $conn->rollback();
}

// Crucial step: Remove the session lock so the user can browse freely
unset($_SESSION['pending_order_id']);

// Redirect the user back to the menu
header("Location: menu.php");
exit;
?>