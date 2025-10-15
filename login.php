<?php
session_start();
require 'db.php'; // connect to MySQL

$error = '';
$success = '';
$inactive_email = null; // Variable to hold the email for the resend link

// --- NEW: Check for feedback messages from resend_verification.php ---
if (isset($_GET['resent']) && $_GET['resent'] == 1) {
    $success = "A new verification link has been sent to your email address.";
}
if (isset($_GET['error'])) {
    $error = "Could not resend verification email. Please try again.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username']);
    $password        = $_POST['password'];

    // Find the user without checking if they are active
    $stmt = $conn->prepare("SELECT id, username, email, password, role, is_active FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Check if the account is active
        if ($user['is_active'] == 0) {
            // --- MODIFICATION: Prepare to show the resend link ---
            $error = "Your account is not active. Please check your email to verify it.";
            $inactive_email = $user['email']; // Pass the user's email to the form
        } 
        // If active, now check the password
        elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'owner') {
                header("Location: owner.php");
                exit;
            } else if ($user['role'] === 'admin') {
                header("Location: admin.php");
                exit;
            } else {
                header("Location: index.php");
                exit;
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Baga Burger - Login</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .btn-group {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 10px;
    }
    .btn-primary {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      font-size: 14px;
      background: gold;
      color: black;
    }
    /* --- NEW STYLE for the resend button --- */
    .btn-resend {
        background: none;
        border: none;
        color: gold;
        text-decoration: underline;
        cursor: pointer;
        padding: 0;
        font-family: inherit;
        font-size: 1em;
    }
  </style>
</head>
<body class="login-body">

  <main class="login-page glass-login">
    <img src="images.png" alt="Baga Burger Logo" class="login-logo" />
    <h2>Welcome Back!</h2>
    <p class="login-subtitle">Please login to continue</p>

    <?php if ($error): ?>
      <p style="color: yellow;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
      <p style="color: lightgreen;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if ($inactive_email): ?>
        <form action="resend_verification.php" method="POST" style="margin-top:-10px; margin-bottom:15px;">
            <input type="hidden" name="email" value="<?= htmlspecialchars($inactive_email) ?>">
            <button type="submit" class="btn-resend">Resend verification email?</button>
        </form>
    <?php endif; ?>


    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="username">Username or Email</label>
        <input type="text" id="username" name="username" required />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
      </div>

      <div class="btn-group">
        <button type="submit" name="login" class="btn-primary">User Login</button>
      </div>
    </form>

    <p style="margin-top: 15px; font-size: 14px; color: white;">
  Don't have an account? 
  <a href="signup.php" style="color: gold; font-weight: bold; text-decoration: none;">Sign up</a>
  </p>

  <p style="margin-top: 10px; font-size: 14px;">
      <a href="forgot_password.php" style="color: gold; text-decoration: none;">Forgot Password?</a>
  </p>

  </main>
</body>
</html>