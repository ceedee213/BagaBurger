<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    die("No order specified.");
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT total_amount, payment_method, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found or you do not have permission to view it.");
}

$order = $result->fetch_assoc();
$stmt->close();

if ($order['status'] !== 'Pending Payment') {
    unset($_SESSION['pending_order_id']);
    header("Location: my_orders.php?already_paid=1");
    exit;
}

$error_message = '';
if (isset($_GET['error']) && $_GET['error'] === 'invalid_ref') {
    $error_message = 'Invalid Reference Number. Please enter a valid 9 to 13-digit number.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Complete Your Payment</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .payment-card { background: rgba(0, 0, 0, 0.4); border-radius: 12px; padding: 30px; text-align: center; color: white; }
        .payment-card h2 { color: gold; }
        .payment-card .total { font-size: 1.5em; font-weight: bold; margin: 10px 0 20px; }
        .qr-code { max-width: 200px; margin: 15px auto; border: 5px solid white; border-radius: 8px; }
        .payment-details p { margin: 5px 0; }
        .payment-details strong { color: #ffcc00; }
        hr { border-color: rgba(255,255,255,0.3); margin: 25px 0; }
        .form-group input { text-align: center; }
        .error-notice { color: #ffc107; font-weight: bold; margin-bottom: 15px; }
        
        .btn-cancel { 
            background: #dc3545; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 8px; 
            margin-left: 10px;
            font-weight: bold;
            font-size: 14px;
            border: none;
            /* ✅ ADDED: Prevents text from wrapping */
            white-space: nowrap;
        }
        .btn-cancel:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
            <ul>
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
                <?php endif; ?>

                <hr>
                
                <h3>Step 2: Submit Your Payment Reference</h3>
                <p>After paying, enter the reference number from the receipt below.</p>
                
                <?php if ($error_message): ?>
                    <p class="error-notice"><?= $error_message ?></p>
                <?php endif; ?>

                <form action="my_orders.php" method="POST" style="max-width:450px; margin:auto; display: flex; flex-wrap: wrap; justify-content: center;" onsubmit="return validateReference()">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <div class="form-group" style="width: 100%; margin-bottom: 20px;">
                        <input type="text" id="reference_number" name="reference_number" placeholder="Enter Reference Number Here" required>
                    </div>
                    <button type="submit" name="submit_reference" class="btn-primary">Submit for Confirmation</button>
                    <a href="menu.php" class="btn-cancel">Cancel & Go Back to Menu</a>
                </form>
            </div>
        </section>
    </main>

    <script>
        function validateReference() {
            const refInput = document.getElementById('reference_number');
            const refValue = refInput.value.trim();
            const isValid = /^\d{9,13}$/.test(refValue);
            if (!isValid) {
                alert('Invalid Reference Number!\nPlease enter a valid 9 to 13-digit number.');
                refInput.focus();
                return false;
            }
            return true;
        }
    </script>
</body>
</html>