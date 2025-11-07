<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];
$confirm = $_POST['confirm'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;

if (empty($cart)) {
    header("Location: menu.php?error=emptycart");
    exit;
}

// Process the cart for display and total calculation
$total_price = 0;
$detailed_order = [];
foreach ($cart as $unique_id => $item) {
    $item_price = floatval($item['price']);
    $addon_names = [];
    if(!empty($item['addons'])) {
        foreach ($item['addons'] as $addon) {
            $item_price += floatval($addon['price']);
            $addon_names[] = $addon['name'];
        }
    }
    
    $description = $item['name'];
    if (!empty($addon_names)) {
        $description .= ' (w/ ' . implode(', ', $addon_names) . ')';
    }

    if (isset($detailed_order[$description])) {
        $detailed_order[$description]['quantity']++;
        $detailed_order[$description]['subtotal'] += $item_price;
    } else {
        $detailed_order[$description] = [
            'quantity' => 1,
            'price' => $item_price,
            'subtotal' => $item_price,
            'name' => $description
        ];
    }
    $total_price += $item_price;
}

if ($confirm === '1' && $payment_method) {
    $conn->begin_transaction();
    try {
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, status, created_at) VALUES (?, ?, ?, 'Pending Payment', NOW())");
        $order_stmt->bind_param("ids", $user_id, $total_price, $payment_method);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();
        
        // Corrected logic to insert grouped items
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, item_description, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        foreach ($detailed_order as $desc => $order_item) {
            $item_price_per_unit = $order_item['subtotal'] / $order_item['quantity'];
            $item_stmt->bind_param("isid", $order_id, $desc, $order_item['quantity'], $item_price_per_unit);
            $item_stmt->execute();
        }
        $item_stmt->close();

        $conn->commit();
        unset($_SESSION['cart']); 
        $_SESSION['pending_order_id'] = $order_id;

        header("Location: payment.php?order_id=" . $order_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("<p style='color:red; font-weight:bold;'>❌ Error placing order: " . htmlspecialchars($e->getMessage()) . "</p>");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Confirm Your Order</title>
  <link rel="icon" type="image/png" href="images.png">
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    /* --- ADD THESE STYLES --- */
    .form-actions {
        display: flex;
        justify-content: center;
        gap: 15px; /* Adds space between buttons */
        margin-top: 20px;
        flex-wrap: wrap; /* Allows buttons to stack on small screens */
    }
    .btn-cancel {
        display: inline-block;
        padding: 12px 25px;
        background-color: #6c757d;
        color: white;
        font-weight: bold;
        text-decoration: none;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 1em; /* Matches btn-primary */
        text-align: center;
    }
    .btn-cancel:hover { background-color: #5a6268; }
    .table-container {
        overflow-x: auto;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .order-summary-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left;
        min-width: 500px;
    }
    .order-summary-table th, .order-summary-table td { 
        padding: 12px; 
        border-bottom: 1px solid rgba(255, 255, 255, 0.2); 
    }
    .order-summary-table th { color: gold; }
    .order-summary-table .price-col { text-align: right; }
    .total-row strong { font-size: 1.2em; color: gold; }
    .payment-options { text-align:left; margin: 20px 0; }
  </style>
</head>
<body class="full-content-page">

<header>
    <nav class="desktop-nav">
        <div class="logo">
            <a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <ul>
            <li><a href="menu.php">Back to Menu</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
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
    <a href="menu.php" class="nav-link"><i class="fas fa-arrow-left"></i> Back to Menu</a>
    <a href="my_orders.php" class="nav-link"><i class="fas fa-history"></i> My Orders</a>
    <a href="logout.php" class="nav-link" onclick="return confirm('Are you sure you want to log out?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<main>
  <section class="glass-section">
    <h1>Please Review Your Final Order</h1>
    
    <div class="table-container">
        <table class="order-summary-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th class="price-col">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detailed_order as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td class="price-col">₱<?= number_format($item['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2"><strong>Total Amount to Pay</strong></td>
                    <td class="price-col"><strong>₱<?= number_format($total_price, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <hr style="border-color: rgba(255,255,255,0.3);">

    <form action="process_order.php" method="POST">
      <input type="hidden" name="confirm" value="1" />
      <div class="payment-options">
        <h3 style="margin-bottom: 15px;">Choose Your Payment Method:</h3>
        <label style="display:block; padding:10px; background:rgba(0,0,0,0.1); border-radius:5px; margin-bottom:10px; cursor: pointer;">
          <input type="radio" name="payment_method" value="gcash" required> GCash
        </label>
        <label style="display:block; padding:10px; background:rgba(0,0,0,0.1); border-radius:5px; cursor: pointer;">
          <input type="radio" name="payment_method" value="paymaya" required> PayMaya
        </label>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn-primary">Confirm & Proceed to Payment</button>
        <a href="menu.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </section>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const openNav = () => document.getElementById("mobile-overlay").style.height = "100%";
    const closeNav = () => document.getElementById("mobile-overlay").style.height = "0%";
    document.querySelector('.menu-toggle').addEventListener('click', openNav);
    document.querySelector('.closebtn').addEventListener('click', closeNav);
});
</script>

</body>
</html>