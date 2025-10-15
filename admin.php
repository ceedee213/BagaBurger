<?php
session_start();
require 'db.php';

// Security check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: login.php");
    exit;
}

// Handle feedback messages from the reset script
$feedback = '';
if (isset($_GET['reset'])) {
    if ($_GET['reset'] === 'success') {
        $feedback = "<p class='feedback success'>✅ Sales totals for the shift have been successfully reset.</p>";
    } else {
        $feedback = "<p class='feedback error'>❌ An error occurred. Could not reset sales data.</p>";
    }
}

// Determine current shift based on Philippine Time (GMT+8)
$timezone = new DateTimeZone('Asia/Manila');
$current_hour = (new DateTime('now', $timezone))->format('G');
$current_shift = '';
if ($current_hour >= 6 && $current_hour < 18) {
    $current_shift = 'morning';
} else {
    $current_shift = 'night';
}


// --- 1. Fetch Main Statistics ---
$total_orders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) AS count FROM menu")->fetch_assoc()['count'] ?? 0;
// Grand Total Revenue now includes ALL completed and archived orders
$revenue_sql = "
    SELECT SUM(oi.quantity * oi.price_at_purchase) AS total_revenue
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status = 'Completed' OR o.status = 'Archived'";
$total_revenue = $conn->query($revenue_sql)->fetch_assoc()['total_revenue'] ?? 0;

// Morning Shift Sales (counts only 'Completed' orders)
$morning_shift_sql = "
    SELECT SUM(oi.quantity * oi.price_at_purchase) AS total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status = 'Completed' AND (HOUR(o.created_at) >= 6 AND HOUR(o.created_at) < 18)";
$morning_shift_revenue = $conn->query($morning_shift_sql)->fetch_assoc()['total'] ?? 0;

// Night Shift Sales (counts only 'Completed' orders)
$night_shift_sql = "
    SELECT SUM(oi.quantity * oi.price_at_purchase) AS total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status = 'Completed' AND (HOUR(o.created_at) >= 18 OR HOUR(o.created_at) < 6)";
$night_shift_revenue = $conn->query($night_shift_sql)->fetch_assoc()['total'] ?? 0;

// --- 2. Fetch Recent Orders (Last 5) ---
$recent_orders_sql = "
    SELECT o.id, u.username, o.status, o.total_amount
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_sql);

// --- 3. Fetch Low Stock Items (10 or less) ---
$low_stock_sql = "SELECT name, stock FROM menu WHERE stock <= 10 AND stock > 0 ORDER BY stock ASC";
$low_stock_result = $conn->query($low_stock_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .dashboard-grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
    .stat-card { background: rgba(0, 0, 0, 0.4); padding: 20px; border-radius: 12px; color: white; text-align: center; }
    .stat-card .icon { font-size: 2.5em; margin-bottom: 10px; }
    .stat-card h3 { margin: 0 0 5px 0; color: #aaa; font-size: 1em; }
    .stat-card p { margin: 0; font-size: 1.8em; font-weight: bold; }
    .info-card { background: rgba(0,0,0,0.4); padding: 25px; border-radius: 12px; color: white; }
    @media (min-width: 1024px) { .info-card.full-width { grid-column: 1 / 3; } }
    .info-card h3 { margin-top: 0; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 10px; }
    .info-card ul { list-style: none; padding: 0; margin: 0; }
    .info-card li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .info-card a.card-link { display: inline-block; margin-top: 15px; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; text-decoration: none; color: white; font-weight: bold; }
    .status-badge { padding: 3px 10px; border-radius: 12px; font-size: 0.8em; color: white; }
    .badge-For-Confirmation { background-color: #6f42c1; }
    .badge-Preparing { background-color: #17a2b8; }
    .badge-Completed { background-color: #007bff; }
    .btn-reset { background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; display: block; width: 100%; margin-bottom: 30px; font-size: 1em; }
    .btn-reset:hover { background-color: #c82333; }
    .feedback { padding: 15px; border-radius: 8px; margin-bottom: 20px; color: white; text-align: center; font-weight: bold; }
    .feedback.success { background: #28a745; }
    .feedback.error { background: #dc3545; }
  </style>
</head>
<body>
 <header>
  <nav>
    <div class="logo"><a href="admin.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
    <ul>
      <li><a href="admin.php" class="active">Dashboard</a></li>
      <li><a href="MenuManagementAdmin.php">Menu Management</a></li>
      <li><a href="OrderList.php">Order List</a></li>
      <?php if ($_SESSION['role'] === 'owner'): ?>
        <li><a href="user_management.php">Manage Accounts</a></li>
      <?php endif; ?>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
</header>
<main>
  <section class="glass-section">
    <h1>⭐ Admin Dashboard</h1>
    <p style="margin-top:-10px; margin-bottom:30px;">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    
    <?= $feedback ?>
    
    <?php if ($current_shift === 'morning'): ?>
        <form action="reset_sales.php" method="POST" onsubmit="return confirm('Are you sure you want to reset the MORNING SHIFT sales? This will archive all completed morning orders.');">
            <input type="hidden" name="shift" value="morning">
            <button type="submit" name="reset_sales" class="btn-reset">Reset Morning Shift Sales</button>
        </form>
    <?php elseif ($current_shift === 'night'): ?>
        <form action="reset_sales.php" method="POST" onsubmit="return confirm('Are you sure you want to reset the NIGHT SHIFT sales? This will archive all completed night orders.');">
            <input type="hidden" name="shift" value="night">
            <button type="submit" name="reset_sales" class="btn-reset">Reset Night Shift Sales</button>
        </form>
    <?php endif; ?>
    
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="icon">🍔</div>
            <h3>Total Products</h3>
            <p><?= $total_products ?></p>
        </div>
        <div class="stat-card">
            <div class="icon">📦</div>
            <h3>Total Orders</h3>
            <p><?= $total_orders ?></p>
        </div>
        <div class="stat-card">
            <div class="icon">☀️</div>
            <h3>Morning Shift Sales</h3>
            <p>₱<?= number_format($morning_shift_revenue, 2) ?></p>
        </div>
        <div class="stat-card">
            <div class="icon">🌙</div>
            <h3>Night Shift Sales</h3>
            <p>₱<?= number_format($night_shift_revenue, 2) ?></p>
        </div>
    </div>
    
    <div style="margin-top:40px;"></div>

    <div class="dashboard-grid">
        <?php if ($low_stock_result && $low_stock_result->num_rows > 0): ?>
            <div class="info-card">
                <h3>🔔 Low Stock Alert</h3>
                <ul>
                    <?php while ($item = $low_stock_result->fetch_assoc()): ?>
                        <li><span><?= htmlspecialchars($item['name']) ?></span><span class="stock-level stock-low">Stock: <?= $item['stock'] ?></span></li>
                    <?php endwhile; ?>
                </ul>
                <a href="MenuManagementAdmin.php" class="card-link">Go to Menu Management →</a>
            </div>
        <?php endif; ?>

        <?php if ($recent_orders_result && $recent_orders_result->num_rows > 0): ?>
            <div class="info-card <?php echo ($low_stock_result->num_rows == 0) ? 'full-width' : ''; ?>">
                <h3>🕒 Recent Orders</h3>
                <ul>
                    <?php while ($order = $recent_orders_result->fetch_assoc()): ?>
                        <li>
                            <span>Order #<?= $order['id'] ?> (<?= htmlspecialchars($order['username']) ?>) - ₱<?= number_format($order['total_amount'], 2) ?></span>
                            <span class="status-badge badge-<?= str_replace(' ', '-', $order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <a href="OrderList.php" class="card-link">Go to Order List →</a>
            </div>
        <?php endif; ?>
    </div>
  </section>
</main>
</body>
</html>