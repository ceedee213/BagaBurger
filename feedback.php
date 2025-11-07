<?php
session_start();
require 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$feedback_message = '';
if (isset($_GET['feedback'])) {
    if ($_GET['feedback'] === 'success') {
        $feedback_message = "<p class='feedback-notice success'>✅ Thank you for your feedback!</p>";
    } else {
        $feedback_message = "<p class='feedback-notice error'>❌ There was an error submitting your feedback. Please try again.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Feedback - Baga Burger</title>
    <link rel="icon" type="image/png" href="images.png">
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        .feedback-section { padding: 50px 40px; }
        .feedback-form { max-width: 600px; margin: 30px auto 0; text-align: left; }
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; margin-bottom: 20px; }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label { font-size: 2.5em; color: #444; cursor: pointer; transition: color 0.2s; padding: 0 5px; }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating:not(:checked) > label:hover,
        .star-rating:not(:checked) > label:hover ~ label { color: gold; }
        .feedback-form textarea { width: 100%; height: 120px; padding: 10px; border-radius: 8px; border: none; background-color: rgba(255, 255, 255, 0.85); font-size: 1em; box-sizing: border-box; margin-bottom: 20px; }
        .feedback-notice { text-align: center; font-weight: bold; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .feedback-notice.success { background: #28a745; color: white; }
        .feedback-notice.error { background: #dc3545; color: white; }
    </style>
</head>
<body class="full-content-page">

<header>
    <nav class="desktop-nav">
        <div class="logo">
            <a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="preorder.php">How to Order</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="feedback.php" class="active">Feedback</a></li>
            <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')">Logout</a></li>
        </ul>
    </nav>
    <div class="mobile-header">
        <div class="logo">
            <a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <button class="menu-toggle" aria-label="Open Menu"><i class="fas fa-bars"></i></button>
    </div>
</header>

<div id="mobile-overlay" class="overlay">
  <a href="javascript:void(0)" class="closebtn" aria-label="Close Menu">&times;</a>
  <div class="overlay-content">
    <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
    <a href="about.php" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
    <a href="preorder.php" class="nav-link"><i class="fas fa-shopping-cart"></i> How to Order</a>
    <a href="my_orders.php" class="nav-link"><i class="fas fa-history"></i> My Orders</a>
    <a href="contact.php" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
    <a href="feedback.php" class="nav-link"><i class="fas fa-star"></i> Feedback</a>
    <a href="logout.php" class="nav-link" onclick="return confirm('Are you sure you want to log out?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<main>
    <section class="glass-section feedback-section" id="feedback-section">
        <h2>Rate Your Website Experience</h2>
        <p>Your feedback helps us improve our service. Please let us know how we did!</p>
        
        <?= $feedback_message ?>

        <form action="submit_feedback.php" method="POST" class="feedback-form">
            <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
            </div>
            <div class="form-group">
                <label for="comment" style="margin-bottom: 10px;">Additional Comments (Optional):</label>
                <textarea id="comment" name="comment" placeholder="Tell us more..."></textarea>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn-primary">Submit Feedback</button>
            </div>
        </form>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <div class="footer-logo">
                <img src="images.png" alt="Baga Burger Logo">
            </div>
            <p class="franchise-info"><br>
                <a href="mailto:contact@bagaburgerph.com">bagaburger.shop</a>
            </p>
            <div class="social-links">
                <a href="https://www.facebook.com/profile.php?id=61556516257914" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
            </div>
        </div>
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Our Menu</a></li>
                <li><a href="about.php">About Us</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Address:</h4>
            <p>12B Verdant Ave. Verdant Acres Subd. Pamplona Tres, Las Piñas, Philippines</p>
            <h4>Hours:</h4>
            <p>Open 24-Ever (24 Hours)</p>
            <p style="margin-top: 20px;">CALL US</p>
            <p class="contact-number">(+63) 939 986 6058</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Baga Burger. All Rights Reserved.</p>
    </div>
</footer>
 
<script>
document.addEventListener("DOMContentLoaded", () => {
    const openNav = () => document.getElementById("mobile-overlay").style.height = "100%";
    const closeNav = () => document.getElementById("mobile-overlay").style.height = "0%";
    document.querySelector('.menu-toggle').addEventListener('click', openNav);
    document.querySelector('.closebtn').addEventListener('click', closeNav);

    if (window.location.search.includes('feedback=')) {
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 3000);
    }
});
</script>

</body>
</html>