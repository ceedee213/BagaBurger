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
  <title>Baga Burger - Contact Us</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>
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
    <section class="glass-section">
      <h1>Contact Us</h1>
      <p>If you have any questions, feedback, or special requests, feel free to reach out!</p>

      <?php if (isset($_GET['sent']) && $_GET['sent'] == 1): ?>
        <p style="color: lightgreen; font-weight: bold; text-align: center;">
          ✅ Thank you! Your message has been sent successfully.
        </p>
      <?php endif; ?>

      <form action="save_contact.php" method="POST" style="max-width:500px; margin:auto; text-align:left;">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" placeholder="Enter your name" required />
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required />
        </div>
        <div class="form-group">
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn-primary">Send Message</button>
      </form>

      <div style="margin-top: 30px;">
      <h2>Our Location</h2>
      <p>Verdant Ave. Pamplona Tres, Las Piñas. </p>
    </div>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Baga Burger</p>
  </footer>
</body>
</html>
