<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$items = $_POST['items'] ?? []; // This now receives main items AND add-ons together
$confirm = $_POST['confirm'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;

// Filter only positive quantities
$ordered_items = array_filter($items, function($qty) {
    return intval($qty) > 0;
});

if (empty($ordered_items)) {
    die("<p>No items were selected. <a href='menu.php'>Back to Menu</a></p>");
}

// Fetch menu info by code to get full details like name and price
$item_codes = array_keys($ordered_items);
$placeholders = implode(',', array_fill(0, count($item_codes), '?'));
$sql = "SELECT id, code, name, price, stock FROM menu WHERE code IN ($placeholders)";
$stmt = $conn->prepare($sql);
$types = str_repeat('s', count($item_codes));
$stmt->bind_param($types, ...$item_codes);
$stmt->execute();
$result = $stmt->get_result();
$menu_data = [];
while ($row = $result->fetch_assoc()) {
    $menu_data[$row['code']] = $row;
}
$stmt->close();

$total_price = 0;
$detailed_order = [];
foreach ($ordered_items as $code => $qty) {
    if (isset($menu_data[$code])) {
        $price = floatval($menu_data[$code]['price']);
        $subtotal = $qty * $price;
        $total_price += $subtotal;
        $detailed_order[] = [
            'name' => $menu_data[$code]['name'],
            'quantity' => $qty,
            'price' => $price,
            'subtotal' => $subtotal
        ];
    }
}

// This block runs AFTER the user confirms their order and selects a payment method.
// It creates the order in the database.
if ($confirm === '1' && $payment_method) {
    $conn->begin_transaction();
    try {
        // Step 1: Create the main order record with 'Pending Payment' status
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, status, created_at) VALUES (?, ?, ?, 'Pending Payment', NOW())");
        $order_stmt->bind_param("ids", $user_id, $total_price, $payment_method);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();

        // Step 2: Insert the ordered items (without touching stock)
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        foreach ($ordered_items as $code => $qty) {
            $menu_id = $menu_data[$code]['id'];
            $price_at_purchase = $menu_data[$code]['price'];
            $item_stmt->bind_param("iiid", $order_id, $menu_id, $qty, $price_at_purchase);
            $item_stmt->execute();
        }
        $item_stmt->close();

        // If everything is successful, commit the transaction
        $conn->commit();

        // Redirect to the new payment page.
        header("Location: payment.php?order_id=" . $order_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color:red; font-weight:bold;'>❌ Error placing order: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><a href='menu.php'>Back to Menu</a></p>";
        exit;
    }
}

// This HTML block runs FIRST, when the user comes from the addons page.
// It now shows a detailed order summary (with add-ons) and then asks them to confirm and choose a payment method.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Confirm Your Order</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* Add some styles for the new order summary table */
    .order-summary-table {
        width: 100%;
        margin-top: 20px;
        margin-bottom: 20px;
        border-collapse: collapse;
        text-align: left;
    }
    .order-summary-table th, .order-summary-table td {
        padding: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }
    .order-summary-table th {
        color: gold;
    }
    .order-summary-table .price-col {
        text-align: right;
    }
    .total-row strong {
        font-size: 1.2em;
        color: gold;
    }
  </style>
</head>
<body class="login-body">
  <main class="glass-login" style="color:white; text-align:center; max-width: 600px;">
    <h1>Please Review Your Final Order</h1>

    <table class="order-summary-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th class="price-col">Price</th>
                <th class="price-col">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detailed_order as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td class="price-col">₱<?= number_format($item['price'], 2) ?></td>
                    <td class="price-col">₱<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3"><strong>Total Amount to Pay</strong></td>
                <td class="price-col"><strong>₱<?= number_format($total_price, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
    <hr style="border-color: rgba(255,255,255,0.3);">

    <form action="process_order.php" method="POST">
      <?php
      // Pass the order items again as hidden inputs
      foreach ($ordered_items as $code => $qty) {
          echo '<input type="hidden" name="items[' . htmlspecialchars($code) . ']" value="' . intval($qty) . '">';
      }
      ?>
      <input type="hidden" name="confirm" value="1" />

      <div style="text-align:left; margin: 20px 0;">
        <h3 style="margin-bottom: 15px;">Choose Your Payment Method:</h3>
        <label style="display:block; padding:10px; background:rgba(0,0,0,0.2); border-radius:5px; margin-bottom:10px;">
          <input type="radio" name="payment_method" value="gcash" required> GCash
        </label>
        <label style="display:block; padding:10px; background:rgba(0,0,0,0.2); border-radius:5px;">
          <input type="radio" name="payment_method" value="paymaya" required> PayMaya
        </label>
      </div>

      <button type="submit" class="btn-primary">Confirm & Proceed to Payment</button>
      <a href="menu.php" class="btn-secondary" style="background:grey; color:white; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-left:10px;">Cancel</a>
    </form>
  </main>
</body>
</html>

