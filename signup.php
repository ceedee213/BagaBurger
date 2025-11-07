<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $contact_number = trim($_POST['cpnumber']);
    $password       = $_POST['password'];
    $confirm        = $_POST['confirm'];
    $agree          = isset($_POST['agree']);

    // --- NEW: Server-side password strength validation ---
    $hasNumber   = preg_match('/[0-9]/', $password);
    $hasSpecial  = preg_match('/[^A-Za-z0-9]/', $password);
    $hasMinLen   = (strlen($password) >= 8);

    if (!$agree) {
        $error = "You must agree to the Terms & Conditions before signing up.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!$hasMinLen || !$hasNumber || !$hasSpecial) {
        $error = "Password is not strong enough. Please meet all requirements.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$check) {
            $error = "Database prepare error: " . $conn->error;
        } else {
            $check->bind_param("ss", $username, $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Username or Email already taken.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));

                $stmt = $conn->prepare("INSERT INTO users (username, email, contact_number, password, verification_token) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $error = "Database prepare error: " . $conn->error;
                } else {
                    $stmt->bind_param("sssss", $username, $email, $contact_number, $passwordHash, $token);

                    if ($stmt->execute()) {
                        $mail = new PHPMailer(true);
                        try {
                            // SMTP Configuration...
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'clarksulit5@gmail.com';
                            $mail->Password   = 'icrx kxbn sccg dfec';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                            $mail->Port       = 465;

                            $mail->setFrom('no-reply@bagaburger.com', 'Baga Burger');
                            $mail->addAddress($email, $username);

                            $verification_link = "http://localhost/baga-burger/verify.php?token=$token";
                            
                            $mail->isHTML(true);
                            $mail->Subject = 'Verify Your Baga Burger Account';
                            $mail->Body    = "<h2>Welcome to Baga Burger!</h2><p>Please click the link below to verify your email and activate your account:</p><h3><a href='$verification_link'>Verify My Account</a></h3>";
                            $mail->AltBody = "Copy and paste this link to verify your account: $verification_link";

                            $mail->send();
                            $success = "Account created! Please check your email to activate your account.";

                        } catch (Exception $e) {
                            $error = "Mailer Error: " . $mail->ErrorInfo;
                        }
                    } else {
                        $error = "Database error: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Baga Burger - Sign Up</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
    .modal-content { background: #222; color: white; padding: 20px 30px; border-radius: 10px; width: 80%; max-width: 600px; text-align: left; overflow-y: auto; max-height: 80vh; }
    .modal-content h3 { margin-top: 0; color: #ffcc00; }
    .modal-content h4 { color: #ffcc00; margin-top: 20px; }
    .modal-content ul { padding-left: 20px; }
    .close-btn { float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: #ffcc00; }
    
    /* --- NEW PASSWORD STRENGTH STYLES --- */
    .password-strength-meter {
        height: 10px;
        background-color: #444;
        border-radius: 5px;
        margin-top: 5px;
        overflow: hidden;
    }
    .strength-bar {
        height: 100%;
        width: 0%;
        transition: width 0.3s, background-color 0.3s;
    }
    .strength-bar.weak { background-color: #dc3545; }
    .strength-bar.medium { background-color: #ffc107; }
    .strength-bar.strong { background-color: #28a745; }

    #password-criteria {
        list-style: none;
        padding: 0;
        margin-top: 10px;
        font-size: 0.9em;
    }
    #password-criteria li {
        margin-bottom: 5px;
        transition: color 0.3s;
    }
    #password-criteria li.invalid { color: #ff8a8a; }
    #password-criteria li.valid { color: #8aff8a; }
    #password-criteria li i {
        margin-right: 8px;
        width: 15px; /* Ensures alignment */
    }
  </style>
</head>
<body class="login-body">

<main class="login-page glass-login">
  <img src="images.png" alt="Baga Burger Logo" class="login-logo" />
  <h2>Create Your Account</h2>

  <?php if ($error): ?>
    <p style="color: yellow;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if ($success): ?>
    <p style="color: white;"><?= $success ?></p>
    <a href="login.php" class="btn-primary" style="text-decoration: none; margin-top: 15px;">Go to Login</a>
  <?php else: ?>
    <form method="POST" action="signup.php" id="signup-form">
      <div class="form-group">
        <label for="username">Username</label>
        <input id="username" type="text" name="username" required />
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email" required />
      </div>

      <div class="form-group">
        <label for="cpnumber">Contact Number</label>
        <input id="cpnumber" type="text" name="cpnumber" required pattern="[0-9]{11}" placeholder="e.g. 09123456789" />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required />
        <div class="password-strength-meter">
            <div id="strength-bar" class="strength-bar"></div>
        </div>
        <ul id="password-criteria">
            <li id="length" class="invalid"><i class="fas fa-times-circle"></i> At least 8 characters</li>
            <li id="number" class="invalid"><i class="fas fa-times-circle"></i> At least one number</li>
            <li id="special" class="invalid"><i class="fas fa-times-circle"></i> At least one special character</li>
        </ul>
      </div>

      <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <input id="confirm" type="password" name="confirm" required />
      </div>

      <div class="form-group" style="text-align:left; font-size:14px; color:white; margin-top:10px;">
        <p>By creating an account, you agree to our <a href="#" id="openTerms" style="color:#ffcc00; text-decoration:underline;">Terms & Conditions</a>.</p>
        <label for="agree"><input type="checkbox" id="agree" name="agree" required /> I have read and agree</label>
      </div>

      <button type="submit" id="signup-btn" class="btn-primary" disabled>Sign Up</button>
    </form>

    <p class="account-link">Do you have an account? <a href="login.php">Login</a></p>
  <?php endif; ?>
</main>

<div id="termsModal" class="modal">
  <!-- Your terms and conditions modal content -->
</div>

<script>
  // Your existing modal script here...

  // --- NEW PASSWORD STRENGTH CHECKER SCRIPT ---
  document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strength-bar');
    const criteriaList = {
        length: document.getElementById('length'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };
    const signupBtn = document.getElementById('signup-btn');

    passwordInput.addEventListener('input', () => {
        const password = passwordInput.value;
        let strength = 0;
        
        // Check length
        const hasMinLen = password.length >= 8;
        updateCriterion('length', hasMinLen);
        if (hasMinLen) strength++;

        // Check for number
        const hasNumber = /[0-9]/.test(password);
        updateCriterion('number', hasNumber);
        if (hasNumber) strength++;

        // Check for special character
        const hasSpecial = /[^A-Za-z0-9]/.test(password);
        updateCriterion('special', hasSpecial);
        if (hasSpecial) strength++;

        // Update strength bar
        const strengthLevels = { 0: 'weak', 1: 'weak', 2: 'medium', 3: 'strong' };
        strengthBar.className = `strength-bar ${strengthLevels[strength]}`;
        strengthBar.style.width = `${(strength / 3) * 100}%`;

        // Enable/disable signup button
        signupBtn.disabled = strength < 3;
    });

    function updateCriterion(key, isValid) {
        const element = criteriaList[key];
        const icon = element.querySelector('i');
        if (isValid) {
            element.classList.remove('invalid');
            element.classList.add('valid');
            icon.className = 'fas fa-check-circle';
        } else {
            element.classList.remove('valid');
            element.classList.add('invalid');
            icon.className = 'fas fa-times-circle';
        }
    }
  });
</script>
</body>
</html>