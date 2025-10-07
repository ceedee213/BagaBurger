<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pre-order Now</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="home">
<header>
  <nav>
    <div class="logo">
      <a href="index.php">
        <img src="images.png" alt="Baga Burger Logo">
      </a>
    </div>
    <ul>
      <li><a href="index.php" class="active">Home</a></li>
      <li><a href="about.php">About Us</a></li>
      <li><a href="preorder.php">Pre-order Now</a></li>
      <li><a href="my_orders.php">My Orders</a></li>
      <li><a href="contact.php">Contact Us</a></li>
      <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')">Logout</a></li>
    </ul>
  </nav>
</header>
<main>
  <section class="hero">
    <div class="hero-content">
      <h1>Welcome to Baga Burger</h1>
      <p>Dobol Burger Forever!</p>
      <a href="menu.php" class="btn-primary">Pre-order Now</a>
    </div>
  </section>
</main>

  <footer>
    <p>&copy; 2025 Baga Burger</p>
  </footer>
</body>
</html>
