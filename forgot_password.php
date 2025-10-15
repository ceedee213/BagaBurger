<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // From Composer installation

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Security: Always show a generic success message to prevent attackers
    // from discovering which emails are registered on your site.
    $message = "If an account with that email exists, a password reset link has been sent.";

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token); // Store a hashed version for security
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Link is valid for 1 hour

        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $token_hash, $expires_at, $user['id']);
        $update_stmt->execute();
        
        $mail = new PHPMailer(true);
        try {
            // Your existing email configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'clarksulit5@gmail.com';
            $mail->Password   = 'icrx kxbn sccg dfec';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('no-reply@bagaburger.com', 'Baga Burger Password Reset');
            $mail->addAddress($email);

            // Use your live domain name
            $reset_link = "https://bagaburger.shop/reset_password.php?token=" . $token;
            
            $mail->isHTML(true);
            $mail->Subject = 'Your Baga Burger Password Reset Link';
            $mail->Body    = "<h2>Password Reset Request</h2><p>Click the link below to reset your password. This link is valid for one hour.</p><h3><a href='$reset_link'>Reset My Password</a></h3>";
            $mail->AltBody = "Copy and paste this link to reset your password: " . $reset_link;

            $mail->send();
        } catch (Exception $e) {
            // Silently fail if email doesn't send; the generic message protects user privacy.
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body class="login-body">
    <main class="glass-login">
        <img src="images.png" alt="Baga Burger Logo" class="login-logo" />
        <h2>Forgot Your Password?</h2>
        <p class="login-subtitle">Enter your email to receive a reset link.</p>

        <?php if ($message): ?>
            <p style="color: lightgreen;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required />
            </div>
            <button type="submit" class="btn-primary">Send Reset Link</button>
        </form>
        <p style="margin-top: 15px;">
            <a href="login.php" style="color: gold; text-decoration: none;">Back to Login</a>
        </p>
    </main>
</body>
</html>