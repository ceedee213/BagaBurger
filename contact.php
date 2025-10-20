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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
</head>
<body>
<header>
    <nav>
        <div class="logo">
            <a href="index.php">
                <img src="images.png" alt="Baga Burger Logo">
            </a>
        </div>
        <button class="nav-toggle" aria-label="toggle navigation">
            <span class="hamburger"></span>
        </button>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="preorder.php">Pre-order Now</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
            <li><a href="contact.php" class="active">Contact Us</a></li>
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

<script src="responsive.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    const footerSections = document.querySelectorAll(".footer-section");
    footerSections.forEach((section) => {
        observer.observe(section);
    });
});
</script>

</body>
</html>