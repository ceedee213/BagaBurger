<?php
session_start();
require 'db.php';

// Security: Ensure a user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = trim($_POST['comment']);

    // Validate the rating
    if ($rating < 1 || $rating > 5) {
        header("Location: index.php?feedback=error");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO website_feedback (user_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $rating, $comment);

    if ($stmt->execute()) {
        // Redirect on success to prevent form resubmission
        header("Location: index.php?feedback=success");
    } else {
        header("Location: index.php?feedback=error");
    }
    $stmt->close();
    exit;
} else {
    // Redirect if accessed directly
    header("Location: index.php");
    exit;
}
?>