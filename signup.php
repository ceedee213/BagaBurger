<?php
session_start();
require 'db.php';  // Make sure this file creates $conn (not $mysqli)

// --- ADD THESE LINES for PHPMailer ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // From Composer installation

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $contact_number = trim($_POST['cpnumber']);
    $password       = $_POST['password'];
    $confirm        = $_POST['confirm'];
    $agree          = isset($_POST['agree']);

    if (!$agree) {
        $error = "You must agree to the Terms & Conditions before signing up.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
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
                $token = bin2hex(random_bytes(32)); // Generate a secure token

                // --- MODIFIED: Added verification_token to the query ---
                $stmt = $conn->prepare("INSERT INTO users (username, email, contact_number, password, verification_token) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $error = "Database prepare error: " . $conn->error;
                } else {
                    $stmt->bind_param("sssss", $username, $email, $contact_number, $passwordHash, $token);

                    if ($stmt->execute()) {
                        // --- NEW: Send the verification email ---
                        $mail = new PHPMailer(true);
                        try {
                            // --- CONFIGURE YOUR EMAIL SETTINGS HERE ---
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com'; // Your SMTP server (e.g., smtp.gmail.com for Gmail)
                            $mail->SMTPAuth   = true;
                            // CONFIGURE YOUR EMAIL SETTINGS HERE
                            $mail->Username   = 'clarksulit5@gmail.com'; // Your real Gmail address
                            $mail->Password   = 'icrx kxbn sccg dfec';   // The 16-character App Password you generated
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                            $mail->Port       = 465;

                            $mail->setFrom('no-reply@bagaburger.com', 'Baga Burger');
                            $mail->addAddress($email, $username);

                            // IMPORTANT: Change this URL to your actual domain and project path
                            $verification_link = "https://bagaburger.shop/verify.php?token=$token";
                            
                            $mail->isHTML(true);
                            $mail->Subject = 'Verify Your Baga Burger Account';
                            $mail->Body    = "<h2>Welcome to Baga Burger!</h2><p>Please click the link below to verify your email and activate your account:</p><h3><a href='$verification_link'>Verify My Account</a></h3>";
                            $mail->AltBody = "Copy and paste this link to verify your account: $verification_link";

                            $mail->send();
                            // --- MODIFIED: Updated success message ---
                            $success = "Account created! Please check your email to activate your account.";

                        } catch (Exception $e) {
                            // This will show the real error message from the mail server
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
  <style>
    /* Basic modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: #222;
      color: white;
      padding: 20px;
      border-radius: 10px;
      width: 80%;
      max-width: 600px;
      text-align: left;
      overflow-y: auto;
      max-height: 80vh;
    }
    .modal-content h3 {
      margin-top: 0;
      color: #ffcc00;
    }
    .close-btn {
      float: right;
      font-size: 18px;
      cursor: pointer;
      color: #ffcc00;
    }
  </style>
</head>
<body class="login-body">

<main class="login-page glass-login">
  <img src="images.png" alt="Baga Burger Logo" class="login-logo" />
  <h2>Account Created!</h2>

  <?php if ($error): ?>
    <p style="color: yellow;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if ($success): ?>
    <p style="color: white;"><?= $success ?></p>
    <!-- --- MODIFICATION: ADDED THIS BUTTON --- -->
    <a href="login.php" class="btn-primary" style="text-decoration: none; margin-top: 15px;">Go to Login</a>
  <?php else: ?>
    <form method="POST" action="signup.php">
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
      </div>

      <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <input id="confirm" type="password" name="confirm" required />
      </div>

      <!-- Terms & Conditions with Cybersecurity Notice -->
      <div class="form-group" style="text-align:left; font-size:14px; color:white; margin-top:10px;">
        <p>
          By creating an account, you acknowledge and agree to our 
          <a href="#" id="openTerms" style="color:#ffcc00; text-decoration:underline;">Terms & Conditions</a>.
        </p>
        <label for="agree">
          <input type="checkbox" id="agree" name="agree" required /> I have read and agree to the Terms & Conditions
        </label>
      </div>

      <button type="submit" class="btn-primary">Sign Up</button>
    </form>

    <p class="account-link">
      Do you have an account? 
      <a href="login.php">Login</a>
    </p>
  <?php endif; ?>
</main>

<!-- Terms & Conditions Modal -->
<div id="termsModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeTerms">&times;</span>
    <h3>Terms & Conditions</h3>
    <p>
      By creating an account with Baga Burger, you agree to follow our rules for responsible usage and 
      the protection of your personal data. You must ensure that your login details are private and 
      your password is strong and not shared with others. Misuse of our system or attempts to breach 
      security are strictly prohibited.
    </p>
    <p style="color:#ffcc00; font-size:13px;">
      🔒 Cybersecurity Notice: By agreeing, you confirm that you will use a secure password, protect 
      your login details, and comply with our data protection standards. This helps safeguard your 
      account from misuse and strengthens overall system security.
    </p>
  </div>
</div>

<script>
  const modal = document.getElementById("termsModal");
  const openBtn = document.getElementById("openTerms");
  const closeBtn = document.getElementById("closeTerms");

  openBtn.onclick = function(e) {
    e.preventDefault();
    modal.style.display = "flex";
  };
  closeBtn.onclick = function() {
    modal.style.display = "none";
  };
  window.onclick = function(e) {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  };
</script>
</body>
</html>