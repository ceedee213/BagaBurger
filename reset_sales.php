<?php
session_start();
require 'db.php';

// Security check to ensure only an admin or owner can run this script.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    die("Access Denied. You do not have permission to perform this action.");
}

// Check which shift's reset button was pressed.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shift_to_reset = $_POST['shift'] ?? null;
    $sql_time_condition = '';

    // Determine the time window for the SQL query based on the shift.
    if ($shift_to_reset === 'morning') {
        // Morning shift is from 6:00 AM to 5:59 PM.
        $sql_time_condition = "AND (HOUR(created_at) >= 6 AND HOUR(created_at) < 18)";
    } elseif ($shift_to_reset === 'night') {
        // Night shift is from 6:00 PM to 5:59 AM of the next day.
        $sql_time_condition = "AND (HOUR(created_at) >= 18 OR HOUR(created_at) < 6)";
    } else {
        // If no valid shift is provided, do nothing and redirect with an error.
        header("Location: admin.php?reset=error");
        exit;
    }

    // This query now includes a time condition to only archive 'Completed' orders from the specified shift.
    $stmt = $conn->prepare("UPDATE orders SET status = 'Archived' WHERE status = 'Completed' " . $sql_time_condition);
    
    if ($stmt->execute()) {
        // If successful, redirect back to the admin page with a success message.
        header("Location: admin.php?reset=success");
        exit;
    } else {
        // If there's a database error, redirect back with an error message.
        header("Location: admin.php?reset=error");
        exit;
    }
    $stmt->close();
} else {
    // If someone tries to access this page directly, just send them back to the dashboard.
    header("Location: admin.php");
    exit;
}
?>