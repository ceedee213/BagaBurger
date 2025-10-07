<?php
require 'db.php';
$message = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Find the user with the given token
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Token is valid, update the user account
        $stmt->close();
        $update_stmt = $conn->prepare("UPDATE users SET is_active = 1, is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $update_stmt->bind_param("s", $token);
        if ($update_stmt->execute()) {
            $message = "✅ Your email has been successfully verified! You can now log in.";
        } else {
            $message = "❌ Error updating your account. Please try again later.";
        }
        $update_stmt->close();
    } else {
        $message = "❌ Invalid or expired verification link.";
    }
} else {
    $message = "❌ No verification token provided.";
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Email Verification</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body class="login-body">
    <main class="glass-login">
        <img src="images.png" alt="Baga Burger Logo" class="login-logo" />
        <h2>Account Verification</h2>
        <p style="color: white; font-weight: bold;"><?= $message ?></p>
        <?php if (strpos($message, '✅') !== false): ?>
            <a href="login.php" class="btn-primary" style="text-decoration: none; margin-top: 15px;">Go to Login</a>
        <?php endif; ?>
    </main>
</body>
</html>