<?php
require 'db.php';

// Use PHPMailer classes, same as in your signup.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Find the user who is not yet verified
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? AND is_active = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Generate a new, secure token
        $token = bin2hex(random_bytes(32));

        // Update the user's record with the new token
        $update_stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
        $update_stmt->bind_param("si", $token, $user['id']);
        
        if ($update_stmt->execute()) {
            // --- Re-send the verification email (logic copied from signup.php) ---
            $mail = new PHPMailer(true);
            try {
                // SMTP Configuration from your signup.php
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'clarksulit5@gmail.com'; // Your Gmail address
                $mail->Password   = 'icrx kxbn sccg dfec';   // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('no-reply@bagaburger.com', 'Baga Burger');
                $mail->addAddress($email, $user['username']);

                // Construct the verification link
                $verification_link = "https://bagaburger.shop/verify.php?token=$token";
                
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Baga Burger Account';
                $mail->Body    = "<h2>Welcome to Baga Burger!</h2><p>Please click the link below to verify your email and activate your account:</p><h3><a href='$verification_link'>Verify My Account</a></h3>";
                $mail->AltBody = "Copy and paste this link to verify your account: $verification_link";

                $mail->send();
                
                // Redirect back to login with a success message
                header("Location: login.php?resent=1");
                exit;

            } catch (Exception $e) {
                // If mail fails, redirect with a generic error
                header("Location: login.php?error=mailfail");
                exit;
            }
        }
    }
}

// If the user wasn't found or something else went wrong, redirect back
header("Location: login.php?error=notfound");
exit;