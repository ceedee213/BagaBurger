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

                $stmt = $conn->prepare("INSERT INTO users (username, email, contact_number, password, verification_token) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $error = "Database prepare error: " . $conn->error;
                } else {
                    $stmt->bind_param("sssss", $username, $email, $contact_number, $passwordHash, $token);

                    if ($stmt->execute()) {
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'clarksulit5@gmail.com';
                            $mail->Password   = 'icrx kxbn sccg dfec';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                            $mail->Port       = 465;

                            $mail->setFrom('no-reply@bagaburger.com', 'Baga Burger');
                            $mail->addAddress($email, $username);

                            $verification_link = "https://bagaburger.shop/verify.php?token=$token";
                            
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
  <style>
    .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
    .modal-content { background: #222; color: white; padding: 20px 30px; border-radius: 10px; width: 80%; max-width: 600px; text-align: left; overflow-y: auto; max-height: 80vh; }
    .modal-content h3 { margin-top: 0; color: #ffcc00; }
    .modal-content h4 { color: #ffcc00; margin-top: 20px; }
    .modal-content ul { padding-left: 20px; }
    .close-btn { float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: #ffcc00; }
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

<!-- MODIFIED: Terms & Conditions Modal with Full Privacy Policy -->
<div id="termsModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeTerms">&times;</span>
    <h3>Privacy Policy for Baga Burger Verdant</h3>
    <p><strong>Effective Date:</strong> October 16, 2025</p>
    <p>This Privacy Policy describes how Baga Burger Verdant ("we," "us," or "our") collects, uses, and protects the personal information you provide on our website, https://bagaburger.shop/. We are committed to protecting your privacy in accordance with the Republic Act No. 10173, the Data Privacy Act of 2012.</p>
    
    <h4>1. Information We Collect</h4>
    <p>We may collect the following types of personal information when you use our website, place an order, or sign up for our newsletter:</p>
    <ul>
        <li><strong>Personal Identification Information:</strong> Name, delivery address, email address, and mobile number.</li>
        <li><strong>Order Information:</strong> Details of the products you purchase, order history, and special instructions.</li>
        <li><strong>Payment Information:</strong> We use a secure third-party payment processor (GCash, Maya,). We do not store your full payment details on our servers. The information you provide is encrypted and processed by our payment partners.</li>
        <li><strong>Technical Information:</strong> IP address, browser type, device information, and cookies, which help us improve your browsing experience.</li>
    </ul>

    <h4>2. How We Use Your Information</h4>
    <p>We use the information we collect for the following legitimate purposes:</p>
    <ul>
        <li><strong>To Process Your Orders:</strong> To confirm, prepare, and deliver your food orders.</li>
        <li><strong>To Communicate with You:</strong> To send order confirmations, delivery updates, and respond to your inquiries.</li>
        <li><strong>To Improve Our Services:</strong> To analyze website traffic and customer preferences to enhance our menu and website functionality.</li>
        <li><strong>For Marketing Purposes:</strong> To send you promotional materials, but only if you have given your explicit consent. You can opt-out at any time.</li>
    </ul>

    <h4>3. Data Sharing and Disclosure</h4>
    <p>We do not sell, trade, or rent your personal information. We may share your information with trusted third parties only when necessary to provide our services, such as delivery partners and payment processors. These partners are obligated to keep your information confidential.</p>

    <h4>4. Data Security</h4>
    <p>We implement appropriate technical and organizational security measures to protect your personal data. Our website uses Secure Sockets Layer (SSL) technology to encrypt data.</p>

    <h4>5. Your Rights as a Data Subject</h4>
    <p>In accordance with the Data Privacy Act of 2012, you have the right to be informed, access, rectify, or erase your data, object to processing, and file a complaint with the National Privacy Commission (NPC). To exercise any of these rights, please contact us.</p>

    <h4>6. Use of Cookies</h4>
    <p>Our website uses "cookies" to enhance your experience, like remembering items in your cart. You can disable cookies in your browser, but this may affect website functionality.</p>

    <h4>7. Changes to This Privacy Policy</h4>
    <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>

    <h4>8. Contact Us</h4>
    <p>If you have any questions or concerns, please contact us at:</p>
    <p>
        <strong>Baga Burger Verdant</strong><br>
        <strong>Email:</strong> clarksulit5@gmail.com<br>
        <strong>Phone:</strong> 0912-123-123<br>
        <strong>Address:</strong> Verdant Ave. Pamplona Tres, Las Piñas.
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