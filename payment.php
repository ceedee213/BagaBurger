<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if an order ID is provided in the URL
if (!isset($_GET['order_id'])) {
    die("No order specified.");
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch the order details to ensure it belongs to the logged-in user
$stmt = $conn->prepare("SELECT total_amount, payment_method, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found or you do not have permission to view it.");
}

$order = $result->fetch_assoc();
$stmt->close();

// Prevent users from submitting a reference number if the order is not pending payment
if ($order['status'] !== 'Pending Payment') {
    // Redirect them to their orders page if payment is already being processed or completed
    header("Location: my_orders.php?already_paid=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Complete Your Payment</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .payment-card {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            color: white;
        }
        .payment-card h2 { color: gold; }
        .payment-card .total { font-size: 1.5em; font-weight: bold; margin: 10px 0 20px; }
        .qr-code { max-width: 200px; margin: 15px auto; border: 5px solid white; border-radius: 8px; }
        .payment-details p { margin: 5px 0; }
        .payment-details strong { color: #ffcc00; }
        hr { border-color: rgba(255,255,255,0.3); margin: 25px 0; }
        .form-group input { text-align: center; }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
            <ul>
                <li><a href="my_orders.php">Back to My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="glass-section">
            <div class="payment-card">
                <h2>✅ Order Placed! Please Complete Your Payment</h2>
                <p class="total">Total Amount: ₱<?= number_format($order['total_amount'], 2) ?></p>
                <hr>

                <?php if ($order['payment_method'] === 'gcash'): ?>
                    <h3>Scan to Pay with GCash</h3>
                    <img src="gcash_qr.png" alt="GCash QR Code" class="qr-code">
                    <div class="payment-details">
                        <p>Or send manually to:</p>
                        <p>Account Name: <strong>CL**K DU*E S.</strong></p>
                        <p>Account Number: <strong>0926-683-3266</strong></p>
                    </div>
                <?php elseif ($order['payment_method'] === 'paymaya'): ?>
                    <h3>Scan to Pay with PayMaya</h3>
                    <img src="paymaya_qr.png" alt="PayMaya QR Code" class="qr-code">
                    <div class="payment-details">
                        <p>Or send manually to:</p>
                        <p>Account Name: <strong>CL**K DU*E S.</strong></p>
                        <p>Account Number: <strong>0926-683-3266</strong></p>
                    </div>
                <?php endif; ?>

                <hr>
                
                <h3>Step 2: Submit Your Payment Reference</h3>
                <p>After paying, enter the reference number from the receipt below.</p>
                
                <form action="my_orders.php" method="POST" style="max-width:400px; margin:auto;">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <div class="form-group">
                        <input type="text" name="reference_number" placeholder="Enter Reference Number Here" required>
                    </div>
                    <button type="submit" name="submit_reference" class="btn-primary">I Have Paid, Submit for Confirmation</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>