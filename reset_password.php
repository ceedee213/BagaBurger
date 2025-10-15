<?php
session_start();
require 'db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$is_token_valid = false;

if (!empty($token)) {
    $token_hash = hash('sha256', $token);
    
    // Check if the token is in the database and has not expired
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $is_token_valid = true;
    } else {
        $error = "This password reset link is invalid or has expired.";
    }
} else {
    $error = "No reset token provided.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_token_valid) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $posted_token = $_POST['token'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $token_hash = hash('sha256', $posted_token);

        // Update the password and clear the reset token so it can't be reused
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $password_hash, $token_hash);
        
        if ($stmt->execute()) {
            $success = "Your password has been reset successfully!";
            $is_token_valid = false; // Hide the form after success
        } else {
            $error = "An error occurred. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body class="login-body">
    <main class="glass-login">
        <img src="images.png" alt="Baga Burger Logo" class="login-logo" />
        <h2>Reset Your Password</h2>

        <?php if ($error): ?>
            <p style="color: yellow;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p style="color: lightgreen;"><?= htmlspecialchars($success) ?></p>
            <a href="login.php" class="btn-primary" style="text-decoration: none; margin-top: 15px;">Go to Login</a>
        <?php endif; ?>

        <?php if ($is_token_valid): ?>
            <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required />
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm New Password</label>
                    <input type="password" id="confirm" name="confirm" required />
                </div>
                <button type="submit" class="btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>