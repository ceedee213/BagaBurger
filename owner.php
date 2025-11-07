<?php
session_start();
require 'db.php';

// EXCLUSIVE: Allow ONLY the 'owner' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') { 
    header("Location: login.php");
    exit;
}

// --- Fetch Main Statistics ---
$total_orders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) AS count FROM menu")->fetch_assoc()['count'] ?? 0;

$revenue_sql = "
    SELECT SUM(oi.quantity * oi.price_at_purchase) AS total_revenue
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status = 'Completed' OR o.status = 'Archived'";
$total_revenue = $conn->query($revenue_sql)->fetch_assoc()['total_revenue'] ?? 0;

$morning_shift_sql = "
    SELECT SUM(oi.quantity * oi.price_at_purchase) AS total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status = 'Completed' AND (HOUR(o.created_at) >= 6 AND HOUR(o.created_at) < 18)";
$morning_shift_revenue = $conn->query($morning_shift_sql)->fetch_assoc()['total'] ?? 0;

$night_shift_sql = "
    SELECT SUM(oi.quantity * oi.price_at_purchase) AS total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status = 'Completed' AND (HOUR(o.created_at) >= 18 OR HOUR(o.created_at) < 6)";
$night_shift_revenue = $conn->query($night_shift_sql)->fetch_assoc()['total'] ?? 0;

// Fetch Recent Orders (Last 5)
$recent_orders_sql = "
    SELECT o.id, u.username, o.status, o.total_amount
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_sql);

// Fetch Low Stock Items
$low_stock_sql = "SELECT name, stock FROM menu WHERE stock <= 10 AND stock > 0 ORDER BY stock ASC";
$low_stock_result = $conn->query($low_stock_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>👑 Owner Dashboard</title>
  <link rel="icon" type="image/png" href="images.png">
  <link rel="stylesheet" href="style.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
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
    .info-card li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); flex-wrap: wrap; gap: 5px;}
    .info-card a.card-link { display: inline-block; margin-top: 15px; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; text-decoration: none; color: white; font-weight: bold; }
  </style>
</head>
<body>

<header>
    <nav class="desktop-nav">
        <div class="logo">
            <a href="owner.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <ul>
            <li><a href="owner.php" class="active">Dashboard</a></li>
            <li><a href="InventoryManagementOwner.php">Inventory</a></li>
            <li><a href="OrderList.php">Order List</a></li>
            <li><a href="user_management.php">Users</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="mobile-header">
        <div class="logo">
            <a href="owner.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <button class="menu-toggle" aria-label="Open Menu"><i class="fas fa-bars"></i></button>
    </div>
</header>

<div id="mobile-overlay" class="overlay">
  <a href="javascript:void(0)" class="closebtn" aria-label="Close Menu">&times;</a>
  <div class="overlay-content">
    <a href="owner.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="InventoryManagementOwner.php" class="nav-link"><i class="fas fa-boxes"></i> Inventory</a>
    <a href="OrderList.php" class="nav-link"><i class="fas fa-clipboard-list"></i> Order List</a>
    <a href="user_management.php" class="nav-link"><i class="fas fa-users-cog"></i> User Management</a>
    <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<main>
  <section class="glass-section">
    <h1>👑 Owner Dashboard</h1>
    <p style="margin-top:-10px; margin-bottom:30px;">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    
    <div class="dashboard-grid">
        <div class="stat-card"><div class="icon">🍔</div><h3>Total Products</h3><p><?= $total_products ?></p></div>
        <div class="stat-card"><div class="icon">📦</div><h3>Total Orders</h3><p><?= $total_orders ?></p></div>
        <div class="stat-card"><div class="icon">💰</div><h3>Lifetime Revenue</h3><p>₱<?= number_format($total_revenue, 2) ?></p></div>
        <div class="stat-card"><div class="icon">☀️</div><h3>Morning Sales</h3><p>₱<?= number_format($morning_shift_revenue, 2) ?></p></div>
        <div class="stat-card"><div class="icon">🌙</div><h3>Night Sales</h3><p>₱<?= number_format($night_shift_revenue, 2) ?></p></div>
    </div>
    
    <div style="margin-top:40px;"></div>

    <div class="dashboard-grid">
        <?php if ($low_stock_result && $low_stock_result->num_rows > 0): ?>
            <div class="info-card">
                <h3>🔔 Low Stock Alert</h3>
                <ul>
                    <?php while ($item = $low_stock_result->fetch_assoc()): ?>
                        <li><span><?= htmlspecialchars($item['name']) ?></span><span style="color: #ffc107; font-weight: bold;">Stock: <?= $item['stock'] ?></span></li>
                    <?php endwhile; ?>
                </ul>
                <a href="InventoryManagementOwner.php" class="card-link">Go to Inventory →</a>
            </div>
        <?php endif; ?>

        <?php if ($recent_orders_result && $recent_orders_result->num_rows > 0): ?>
            <div class="info-card <?php echo ($low_stock_result->num_rows == 0) ? 'full-width' : ''; ?>">
                <h3>🕒 Recent Orders</h3>
                <ul>
                    <?php while ($order = $recent_orders_result->fetch_assoc()): ?>
                        <li><span>Order #<?= $order['id'] ?> (<?= htmlspecialchars($order['username']) ?>)</span><span><?= htmlspecialchars($order['status']) ?></span></li>
                    <?php endwhile; ?>
                </ul>
                <a href="OrderList.php" class="card-link">Go to Order List →</a>
            </div>
        <?php endif; ?>
    </div>
  </section>
</main>
<footer class="site-footer">
    </footer>

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