<?php
session_start();
require 'db.php';

function format_status_for_class($status) {
    return str_replace([' ', '#'], ['-', ''], $status);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['pending_order_id']) && !isset($_POST['submit_reference'])) {
    header("Location: payment.php?order_id=" . $_SESSION['pending_order_id']);
    exit;
}

$message = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "✅ Success! Your reference number has been submitted for confirmation.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reference'])) {
    $order_id = intval($_POST['order_id']);
    $reference = trim($_POST['reference_number']);

    if (!empty($reference) && preg_match('/^\d{9,13}$/', $reference)) {
        $stmt = $conn->prepare("UPDATE orders SET payment_reference = ? WHERE id = ? AND user_id = ? AND status IN ('Pending Payment', 'Wrong Reference #')");
        $stmt->bind_param("sii", $reference, $order_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $status_stmt = $conn->prepare("UPDATE orders SET status = 'For Confirmation' WHERE id = ?");
            $status_stmt->bind_param("i", $order_id);
            $status_stmt->execute();
            $status_stmt->close();
            
            unset($_SESSION['pending_order_id']);
            header("Location: my_orders.php?success=1");
            exit;
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
        GROUP_CONCAT(oi.quantity, 'x ', oi.item_description SEPARATOR '<br>') AS items_list
    FROM orders AS o
    JOIN order_items AS oi ON o.id = oi.order_id
    WHERE o.user_id = ? 
    GROUP BY o.id
    ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
if($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[$row['order_id']] = [
            'created_at' => date('F j, Y, g:i a', strtotime($row['created_at'])),
            'status' => $row['status'],
            'total_amount' => $row['total_amount'],
            'items_list' => $row['items_list']
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
    <link rel="icon" type="image/png" href="images.png">
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        .order-card { background: rgba(0, 0, 0, 0.4); border-radius: 12px; margin-bottom: 20px; overflow: hidden; transition: box-shadow 0.3s ease; }
        .order-card.highlight { box-shadow: 0 0 15px 5px gold; }
        .order-header { background: rgba(0,0,0,0.2); padding: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;}
        .order-header h3 { margin: 0; color: gold; font-size: 1.2em; }
        .order-header .total { font-size: 1.2em; font-weight: bold; }
        .order-body { padding: 20px; }
        .order-details { font-size: 0.9em; color: #ddd; margin-bottom: 15px; }
        .order-items h4 { margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 5px; }
        .order-footer { background: rgba(0,0,0,0.2); padding: 15px; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9em; }
        .status-Pending-Payment { background-color: #ffc107; color: black; }
        .status-For-Confirmation { background-color: #6f42c1; color: white; }
        .status-Wrong-Reference { background-color: #dc3545; color: white; }
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
    <!-- 1. Desktop Navigation -->
    <nav class="desktop-nav">
        <div class="logo">
            <a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="preorder.php">How to Order</a></li>
            <li><a href="my_orders.php" class="active">My Orders</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="feedback.php">Feedback</a></li>
            <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')">Logout</a></li>
        </ul>
    </nav>

    <!-- 2. Mobile Navigation Header -->
    <div class="mobile-header">
        <div class="logo">
            <a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <button class="menu-toggle" aria-label="Open Menu"><i class="fas fa-bars"></i></button>
    </div>
</header>

<!-- 3. Mobile Overlay Menu -->
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
    <section class="glass-section">
        <h1>My Order History</h1>
        <?php if ($message): ?><p style="color: lightgreen; text-align: center; font-weight: bold;"><?= htmlspecialchars($message) ?></p><?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <p>You have not placed any orders yet. <a href="menu.php" style="color:gold;">Order Now!</a></p>
        <?php else: ?>
            <?php foreach ($orders as $order_id => $order): ?>
                <div class="order-card" id="order-card-<?= $order_id ?>" data-order-id="<?= $order_id ?>">
                    <div class="order-header">
                        <div>
                            <h3>Order #<?= $order_id ?></h3>
                            <span class="status-badge status-<?= format_status_for_class($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </div>
                        <span class="total">₱<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                    <div class="order-body">
                        <p class="order-details">Placed on: <?= $order['created_at'] ?></p>
                        <div class="order-items">
                            <h4>Items Ordered</h4>
                            <p><?= $order['items_list'] ?></p>
                        </div>
                    </div>

                    <?php if ($order['status'] == 'Pending Payment' || $order['status'] == 'Wrong Reference #'): ?>
                        <div class="order-footer" id="footer-for-<?= $order_id ?>">
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

    // Real-time Order Polling Logic
    const orderCards = document.querySelectorAll('.order-card');
    let orderIdsToMonitor = [];
    const finalStatuses = ['Completed', 'Cancelled', 'Archived'];

    orderCards.forEach(card => {
        const statusBadge = card.querySelector('.status-badge');
        if (statusBadge && !finalStatuses.includes(statusBadge.textContent.trim())) {
            orderIdsToMonitor.push(card.dataset.orderId);
        }
    });

    const fetchOrderUpdates = async () => {
        if (orderIdsToMonitor.length === 0) {
            clearInterval(pollingInterval);
            return;
        }
        try {
            const response = await fetch('get_order_updates.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderIdsToMonitor)
            });
            const newStatuses = await response.json();
            for (const orderId in newStatuses) {
                const newStatus = newStatuses[orderId];
                const card = document.getElementById(`order-card-${orderId}`);
                if (!card) continue;
                const statusBadge = card.querySelector('.status-badge');
                const currentStatus = statusBadge.textContent.trim();
                if (currentStatus !== newStatus) {
                    if (newStatus === 'Wrong Reference #') {
                        window.location.reload();
                        return; 
                    }
                    statusBadge.textContent = newStatus;
                    const newClass = 'status-' + newStatus.replace('#', '').replace(/ /g, '-');
                    statusBadge.className = 'status-badge ' + newClass;
                    const paymentFooter = document.getElementById(`footer-for-${orderId}`);
                    if (paymentFooter) {
                        paymentFooter.style.display = 'none';
                    }
                    card.classList.add('highlight');
                    setTimeout(() => card.classList.remove('highlight'), 2000);
                    if (finalStatuses.includes(newStatus)) {
                        orderIdsToMonitor = orderIdsToMonitor.filter(id => id !== orderId);
                    }
                }
            }
        } catch (error) {
            console.error("Error fetching order updates:", error);
        }
    };

    if (orderIdsToMonitor.length > 0) {
        var pollingInterval = setInterval(fetchOrderUpdates, 5000);
    }
});
</script>
</body>
</html>