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
    <title>Baga Burger - About Us</title>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  
    <style>
        .about .glass-section h1,
        .about .glass-section h2,
        .about .glass-section p {
            color: #000000ff !important;
        }
        .about .glass-section strong {
            color: #000000ff !important;
        }
        .about .glass-section em {
            color: #000000ff !important;
        }
    </style>

</head>
<body class="about">
<header>
    <nav>
        <div class="logo">
            <a href="index.php">
                <img src="images.png" alt="Baga Burger Logo" />
            </a>
        </div>
        <button class="nav-toggle" aria-label="toggle navigation">
            <span class="hamburger"></span>
        </button>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php" class="active">About Us</a></li>
            <li><a href="preorder.php">Pre-order Now</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <section class="glass-section">
        <h1>About Baga Burger</h1>
        <p>
            Welcome to <strong>Baga Burger</strong>, your home for the juiciest, most flavorful burgers in town. 
            We are passionate about using only the freshest ingredients and time-tested recipes to craft burgers that satisfy your cravings.
        </p>
        <p>
            Established in 2020, <strong>Baga Burger</strong> has grown from a small neighborhood joint to a beloved local favorite. 
            Our commitment to quality and customer satisfaction drives us every day.
        </p>
        <p>
            Our menu features a wide variety of burgers, sides, and drinks to cater to all tastes, including vegetarian options and custom add-ons.
        </p>
        <p>
            <em>Come visit us and experience the Baga Burger difference!</em>
        </p>
        <div style="margin-top: 30px;">
            <h2>Our Location</h2>
            <p>Verdant Ave. Pamplona Tres, Las Piñas.</p>
        </div>
        <div class="about-video">
            <video controls>
                <source src="video1.mp4" type="video/mp4" />
                Your browser does not support the video tag.
            </video>
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

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="popup-overlay">
        <div class="popup-box">
            <h3>✅ Update Successful!</h3>
            <p>Your information has been updated.</p>
            <button class="popup-btn" onclick="closePopup()">OK</button>
        </div>
    </div>
    <script>
        function closePopup() {
            document.querySelector('.popup-overlay').style.display = 'none';
            window.history.replaceState({}, document.title, "about.php");
        }
    </script>
<?php endif; ?>

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