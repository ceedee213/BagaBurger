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

// Updated logic to check for both statuses
if ($order['status'] !== 'Pending Payment' && $order['status'] !== 'Wrong Reference #') {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Complete Your Payment</title>
    <link rel="icon" type="image/png" href="images.png">
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
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
        
        /* --- NEW & IMPROVED BUTTON STYLES --- */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px; /* Space between buttons */
            margin-top: 20px;
        }
        .button-group .btn-primary,
        .button-group .btn-cancel {
            padding: 12px 25px;
            font-size: 1em;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .btn-cancel { 
            background: #dc3545; 
            color: white; 
        }
        .btn-cancel:hover { background: #c82333; }

        /* On mobile, stack the buttons for better usability */
        @media (max-width: 500px) {
            .button-group {
                flex-direction: column;
                align-items: center;
            }
            .button-group .btn-primary,
            .button-group .btn-cancel {
                width: 100%;
                max-width: 300px; /* Limit button width */
            }
        }
    </style>
</head>
<body class="full-content-page">

<header>
    </header>

<div id="mobile-overlay" class="overlay">
    </div>

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

            <form action="my_orders.php" method="POST" onsubmit="return validateReference()">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <div class="form-group" style="width: 100%; margin-bottom: 10px;">
                    <input type="text" id="reference_number" name="reference_number" placeholder="Enter Reference Number Here" required>
                </div>

                <div class="button-group">
                    <button type="submit" name="submit_reference" class="btn-primary">Submit for Confirmation</button>
                    <a href="cancel_order.php?order_id=<?= $order_id ?>" class="btn-cancel" onclick="return confirm('Are you sure you want to cancel this order? It will be permanently removed.');">Cancel Order</a>
                </div>
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

    document.addEventListener("DOMContentLoaded", () => {
        const openNav = () => document.getElementById("mobile-overlay").style.height = "100%";
        const closeNav = () => document.getElementById("mobile-overlay").style.height = "0%";
        document.querySelector('.menu-toggle').addEventListener('click', openNav);
        document.querySelector('.closebtn').addEventListener('click', closeNav);
    });
</script>

</body>
</html>