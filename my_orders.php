<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Security check for pending payments
if (isset($_SESSION['pending_order_id'])) {
    if (!isset($_POST['submit_reference'])) {
        header("Location: payment.php?order_id=" . $_SESSION['pending_order_id']);
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reference'])) {
    $order_id = intval($_POST['order_id']);
    $reference = trim($_POST['reference_number']);

    if (!empty($reference) && preg_match('/^\d{9,13}$/', $reference)) {
        $stmt = $conn->prepare("UPDATE orders SET payment_reference = ? WHERE id = ? AND user_id = ? AND status IN ('Pending Payment', 'Wrong Reference #')");
        $stmt->bind_param("sii", $reference, $order_id, $user_id);
        
        if ($stmt->execute()) {
            $status_stmt = $conn->prepare("UPDATE orders SET status = 'For Confirmation' WHERE id = ?");
            $status_stmt->bind_param("i", $order_id);
            $status_stmt->execute();
            $status_stmt->close();
            $message = "✅ Success! Your reference number has been submitted for confirmation.";
            
            unset($_SESSION['pending_order_id']);
        }
        $stmt->close();
    } else {
        header("Location: payment.php?order_id=" . $order_id . "&error=invalid_ref");
        exit;
    }
}

$sql = "
    SELECT 
        o.id AS order_id, o.created_at, o.status, o.total_amount,
        oi.item_description, oi.quantity
    FROM orders AS o
    JOIN order_items AS oi ON o.id = oi.order_id
    WHERE o.user_id = ? ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'created_at' => date('F j, Y, g:i a', strtotime($row['created_at'])),
            'status' => $row['status'],
            'total_amount' => $row['total_amount'],
            'items' => []
        ];
    }
    
    $item_desc = $row['item_description'];
    if (isset($orders[$order_id]['items'][$item_desc])) {
        $orders[$order_id]['items'][$item_desc]['quantity'] += $row['quantity'];
    } else {
        $orders[$order_id]['items'][$item_desc] = [
            'name' => $item_desc, 
            'quantity' => $row['quantity']
        ];
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Orders - Baga Burger</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        .order-card { background: rgba(0, 0, 0, 0.4); border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
        .order-header { background: rgba(0,0,0,0.2); padding: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;}
        .order-header h3 { margin: 0; color: gold; font-size: 1.2em; }
        .order-header .total { font-size: 1.2em; font-weight: bold; }
        .order-body { padding: 20px; }
        .order-details { font-size: 0.9em; color: #ddd; margin-bottom: 15px; }
        .order-items h4 { margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 5px; }
        .order-items ul { list-style: none; padding: 0; margin: 0; }
        .order-items li { display: flex; justify-content: space-between; padding: 5px 0; }
        .order-footer { background: rgba(0,0,0,0.2); padding: 15px; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9em; }
        .status-Pending-Payment { background-color: #ffc107; color: black; }
        .status-For-Confirmation { background-color: #6f42c1; color: white; }
        .status-Wrong-Reference-\# { background-color: #dc3545; color: white; }
        .status-Preparing { background-color: #17a2b8; color: white; }
        .status-Ready-for-Pickup { background-color: #28a745; color: white; }
        .status-Completed { background-color: #007bff; color: white; }
        .status-Cancelled { background-color: #6c757d; color: white; }
        
        .payment-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .payment-form p { width: 100%; margin-top: 0; }
        .payment-form p.error-notice { color: #ffc107; font-weight: bold; }
        .payment-form input { flex-grow: 1; padding: 10px; border-radius: 5px; border: 1px solid #ccc; min-width: 150px; }
        .payment-form button { flex-shrink: 0; padding: 10px 15px; border-radius: 5px; border: none; background: gold; color: black; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <header>
      <nav>
        <div class="logo"><a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
        <button class="nav-toggle" aria-label="toggle navigation">
            <span class="hamburger"></span>
        </button>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="preorder.php">Pre-order Now</a></li>
            <li><a href="my_orders.php" class="active">My Orders</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')">Logout</a></li>
        </ul>
      </nav>
    </header>
    <main>
        <section class="glass-section">
            <h1>My Order History</h1>
            <?php if ($message): ?><p style="color: lightgreen; text-align: center; font-weight: bold;"><?= $message ?></p><?php endif; ?>
            
            <?php if (empty($orders)): ?>
                <p>You have not placed any orders yet. <a href="menu.php" style="color:gold;">Order Now!</a></p>
            <?php else: ?>
                <?php foreach ($orders as $order_id => $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h3>Order #<?= $order_id ?></h3>
                                <span class="status-badge status-<?= str_replace([' ', '#'], ['-', '\#'], $order['status']) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </div>
                            <span class="total">₱<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                        <div class="order-body">
                            <p class="order-details">Placed on: <?= $order['created_at'] ?></p>
                            
                            <div class="order-items">
                                <h4>Items Ordered</h4>
                                <ul>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li>
                                            <span><?= htmlspecialchars($item['quantity']) ?>x <?= htmlspecialchars($item['name']) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <?php if ($order['status'] == 'Pending Payment' || $order['status'] == 'Wrong Reference #'): ?>
                            <div class="order-footer">
                                <form class="payment-form" action="my_orders.php" method="POST">
                                    <?php if ($order['status'] == 'Wrong Reference #'): ?>
                                        <p class="error-notice">Your last reference was incorrect. Please submit the correct one.</p>
                                    <?php else: ?>
                                        <p>Please submit your payment reference number below.</p>
                                    <?php endif; ?>
                                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                    <input type="text" name="reference_number" placeholder="Enter new reference number" required>
                                    <button type="submit" name="submit_reference">Submit</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
    
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="images.png" alt="Baga Burger Logo">
                </div>
                <p class="franchise-info">Interested in Franchising?<br>
                    <a href="mailto:contact@bagaburgerph.com">contact@bagaburgerph.com</a>
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
    
    <script src="responsive.js?v=<?php echo filemtime('responsive.js'); ?>"></script>
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