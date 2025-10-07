<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// This part for handling the submission is updated
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reference'])) {
    $order_id = intval($_POST['order_id']);
    $reference = trim($_POST['reference_number']);

    if (!empty($reference)) {
        // --- CHANGE IS HERE ---
        // This query now ONLY updates the reference number. 
        // The status remains 'Pending Payment' for the admin to review.
        $stmt = $conn->prepare("UPDATE orders SET payment_reference = ? WHERE id = ? AND user_id = ? AND status IN ('Pending Payment', 'Wrong Reference #')");
        $stmt->bind_param("sii", $reference, $order_id, $user_id);
        
        if ($stmt->execute()) {
             // Now we manually update the status to 'For Confirmation'
            $status_stmt = $conn->prepare("UPDATE orders SET status = 'For Confirmation' WHERE id = ?");
            $status_stmt->bind_param("i", $order_id);
            $status_stmt->execute();
            $status_stmt->close();
            $message = "✅ Success! Your reference number has been submitted for confirmation.";
        }
        $stmt->close();
    }
}

// Fetch all orders and their items for the current user
$sql = "
    SELECT 
        o.id AS order_id, o.created_at, o.status, o.total_amount,
        oi.quantity, m.name AS product_name
    FROM orders AS o
    JOIN order_items AS oi ON o.id = oi.order_id
    JOIN menu AS m ON oi.menu_id = m.id
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
    $orders[$order_id]['items'][] = ['name' => $row['product_name'], 'quantity' => $row['quantity']];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Orders - Baga Burger</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .order-card { background: rgba(0, 0, 0, 0.4); border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
        .order-header { background: rgba(0,0,0,0.2); padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .order-header h3 { margin: 0; color: gold; font-size: 1.2em; }
        .order-header .total { font-size: 1.2em; font-weight: bold; }
        .order-body { padding: 20px; }
        .order-details { font-size: 0.9em; color: #ddd; margin-bottom: 15px; }
        .order-items h4 { margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 5px; }
        .order-items ul { list-style: none; padding: 0; margin: 0; }
        .order-items li { display: flex; justify-content: space-between; padding: 5px 0; }
        .order-footer { background: rgba(0,0,0,0.2); padding: 15px; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9em; }
        /* Status Colors */
        .status-Pending-Payment { background-color: #ffc107; color: black; }
        .status-For-Confirmation { background-color: #6f42c1; color: white; }
        .status-Wrong-Reference-\# { background-color: #dc3545; color: white; }
        .status-Preparing { background-color: #17a2b8; color: white; }
        .status-Ready-for-Pickup { background-color: #28a745; color: white; }
        .status-Completed { background-color: #007bff; color: white; }
        .status-Cancelled { background-color: #6c757d; color: white; }
        /* Payment Form */
        .payment-form p { margin-top: 0; }
        .payment-form p.error-notice { color: #ffc107; font-weight: bold; }
        .payment-form input { width: calc(100% - 120px); padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        .payment-form button { padding: 10px 15px; border-radius: 5px; border: none; background: gold; color: black; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <header>
      <nav>
        <div class="logo"><a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
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
                                <div class="payment-form">
                                    <?php if ($order['status'] == 'Wrong Reference #'): ?>
                                        <p class="error-notice">Your last reference was incorrect. Please submit the correct one.</p>
                                    <?php else: ?>
                                        <p>Please submit your payment reference number below.</p>
                                    <?php endif; ?>
                                    <form action="my_orders.php" method="POST" style="display:flex; gap:10px;">
                                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                        <input type="text" name="reference_number" placeholder="Enter new reference number" required>
                                        <button type="submit" name="submit_reference">Submit</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>