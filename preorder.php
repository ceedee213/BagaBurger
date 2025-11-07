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
    <title>How to Order - Baga Burger</title>
    <link rel="icon" type="image/png" href="images.png">
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        .how-to-order-section {
            text-align: left;
        }
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        .step-icon {
            font-size: 2.5em;
            color: gold;
            margin-right: 25px;
            width: 60px;
            text-align: center;
        }
        .step-content h3 {
            margin: 0 0 5px 0;
            color: white; /* Changed from white */
        }
        .step-content p {
            margin: 0;
            color: white; /* Changed from #ddd to a dark grey */
        }
        .center-text {
            text-align: center;
        }
        /* New styles to make title text black */
        .how-to-order-section > h1,
        .how-to-order-section > p {
            color: white;
        }
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
            <li><a href="preorder.php" class="active">How to Order</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="feedback.php">Feedback</a></li>
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
    <section class="glass-section how-to-order-section">
        <h1 class="center-text">How To Order Online</h1>
        <p class="center-text" style="margin-bottom: 40px;">Follow these simple steps to get your delicious Baga Burger!</p>

        <div class="step">
            <div class="step-icon"><i class="fas fa-bars"></i></div>
            <div class="step-content">
                <h3>1. Browse the Menu</h3>
                <p>Explore our full selection of burgers and sandwiches. Click "Add to Order" for any item you like.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-icon"><i class="fas fa-plus-circle"></i></div>
            <div class="step-content">
                <h3>2. Customize Your Items</h3>
                <p>A popup will appear allowing you to add extras like cheese, bacon, or an egg to make your burger perfect.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="step-content">
                <h3>3. View Your Cart</h3>
                <p>Click the "View Cart" button to review, edit quantities, or remove items before checking out.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-icon"><i class="fas fa-qrcode"></i></div>
            <div class="step-content">
                <h3>4. Proceed to Payment</h3>
                <p>Once you're happy with your order, proceed to checkout, pay via GCash, and submit your reference number to finalize.</p>
            </div>
        </div>

        <div class="center-text" style="margin-top: 40px;">
            <a href="menu.php" class="btn-primary">Start Ordering Now!</a>
        </div>
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
    // Mobile Navigation Logic
    const openNav = () => document.getElementById("mobile-overlay").style.height = "100%";
    const closeNav = () => document.getElementById("mobile-overlay").style.height = "0%";
    document.querySelector('.menu-toggle').addEventListener('click', openNav);
    document.querySelector('.closebtn').addEventListener('click', closeNav);
});
</script>

</body>
</html>