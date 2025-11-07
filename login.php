<?php
session_start();
require 'db.php'; 

$error = '';
$success = '';
$inactive_email = null;

if (isset($_GET['resent']) && $_GET['resent'] == 1) {
    $success = "A new verification link has been sent to your email address.";
}
if (isset($_GET['error'])) {
    $error = "Could not resend verification email. Please try again.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username']);
    $password        = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, email, password, role, is_active FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if ($user['is_active'] == 0) {
            $error = "Your account is not active. Please check your email to verify it.";
            $inactive_email = $user['email'];
        } 
        elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'owner') {
                header("Location: owner.php");
            } else if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
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
  <link rel="icon" type="image/png" href="images.png">
  <link rel="stylesheet" href="style.css" />
  <style>
    /* This style is for the resend button and can remain here or be moved to style.css */
    .btn-resend {
      background: none;
      border: none;
      color: #f1c40f; /* Matched to the new yellow color */
      text-decoration: underline;
      cursor: pointer;
      padding: 0;
      font-family: inherit;
      font-size: 1em;
    }
  </style>
</head>
<body class="login-body">

  <main class="glass-login">
    <img src="images.png" alt="Baga Burger Logo" class="login-logo" />
    <h2>Welcome Back!</h2>
    <p class="subtitle">Please login to continue</p>

    <?php if ($error): ?>
      <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
      <p class="success-message"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if ($inactive_email): ?>
      <form action="resend_verification.php" method="POST" class="resend-form">
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

      <button type="submit" name="login" class="btn-primary">User Login</button>
    </form>

    <p class="account-link">
      Don't have an account? 
      <a href="signup.php">Sign up</a>
    </p>

    <p class="forgot-password-link">
      <a href="forgot_password.php">Forgot Password?</a>
    </p>
  </main>
  
</body>
</html>