<?php
session_start();
require 'db.php';

// Security check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: login.php");
    exit;
}

$is_owner = ($_SESSION['role'] === 'owner');
$allowed_statuses_for_update = ['Pending Payment', 'For Confirmation', 'Preparing', 'Ready for Pickup', 'Completed', 'Cancelled', 'Wrong Reference #'];
if ($is_owner) {
    $allowed_statuses_for_update[] = 'Archived';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id_to_update = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    if (in_array($new_status, $allowed_statuses_for_update)) {
        if ($new_status === 'Completed') {
            // Your stock deduction logic would go here
        }
        
        $status_update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $status_update_stmt->bind_param("si", $new_status, $order_id_to_update);
        $status_update_stmt->execute();
        $status_update_stmt->close();
    }
    
    $q = urlencode($_POST['q'] ?? '');
    $filter_status = urlencode($_POST['filter_status'] ?? '');
    header("Location: OrderList.php?q=$q&filter_status=$filter_status");
    exit;
}

$search_query = $_GET['q'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$where_clauses = [];
$params = [];
$types = '';

if (!empty($search_query)) {
    $where_clauses[] = "o.id LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= 's';
}
if (!empty($filter_status)) {
    $where_clauses[] = "o.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if (!$is_owner) {
    $where_clauses[] = "o.status != 'Archived'";
}
$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';

$sql = "
    SELECT
        o.id AS order_id, u.username, o.status, o.created_at, o.payment_reference, o.total_amount,
        GROUP_CONCAT(oi.quantity, 'x ', oi.item_description SEPARATOR '<br>') AS items_list
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    $where_sql
    GROUP BY o.id
    ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stats = ['For Confirmation' => 0, 'Preparing' => 0];
$stats_result = $conn->query("SELECT status, COUNT(id) as count FROM orders WHERE status IN ('For Confirmation', 'Preparing') GROUP BY status");
if ($stats_result) {
    while($row = $stats_result->fetch_assoc()) {
        $stats[$row['status']] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Order List</title>
    <link rel="icon" type="image/png" href="images.png">
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        .order-dashboard { padding: 20px; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; align-items: center; flex-wrap: wrap; }
        .filter-bar input, .filter-bar select { padding: 10px; border-radius: 8px; border: 1px solid #555; background: #333; color: white; flex-grow: 1;}
        .stats-bar { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;}
        .stat-box { background: rgba(0,0,0,0.3); padding: 15px 20px; border-radius: 10px; text-align: center; flex-grow: 1; min-width: 180px; }
        .stat-box h3 { margin: 0 0 5px 0; color: #aaa; font-size: 1em; }
        .stat-box p { margin: 0; font-size: 2em; font-weight: bold; color: gold; }
        .order-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        .order-card { background: rgba(0,0,0,0.4); border-radius: 12px; border-left: 5px solid; overflow: hidden; }
        .order-card-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: rgba(0,0,0,0.2); flex-wrap: wrap; gap: 10px; }
        .order-card-header h3 { margin: 0; font-size: 1.2em; }
        .order-card-body { padding: 15px; }
        .order-card-details p { margin: 0 0 10px 0; color: #ddd; }
        .order-card-details p strong { color: white; }
        .order-card-items { margin: 15px 0; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2); }
        .order-card-actions { margin-top: 15px; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9em; color: white; }
        .status-For-Confirmation { border-left-color: #6f42c1; } .badge-For-Confirmation { background-color: #6f42c1; }
        .status-Preparing { border-left-color: #17a2b8; } .badge-Preparing { background-color: #17a2b8; }
        .status-Ready-for-Pickup { border-left-color: #28a745; } .badge-Ready-for-Pickup { background-color: #28a745; }
        .status-Completed { border-left-color: #007bff; } .badge-Completed { background-color: #007bff; }
        .status-Wrong-Reference-\# { border-left-color: #dc3545; } .badge-Wrong-Reference-\# { background-color: #dc3545; }
        .status-Cancelled { border-left-color: #6c757d; } .badge-Cancelled { background-color: #6c757d; }
        .status-Pending-Payment { border-left-color: #ffc107; } .badge-Pending-Payment { background-color: #ffc107; color: black; }
        .status-Archived { border-left-color: #343a40; } .badge-Archived { background-color: #343a40; }
    </style>
</head>
<body>
    <header>
        <nav class="desktop-nav">
            <div class="logo">
                <a href="<?= $is_owner ? 'owner.php' : 'admin.php' ?>"><img src="images.png" alt="Baga Burger Logo"></a>
            </div>
            <ul>
                <li><a href="<?= $is_owner ? 'owner.php' : 'admin.php' ?>">Dashboard</a></li>
                <li><a href="<?= $is_owner ? 'InventoryManagementOwner.php' : 'InventoryManagementAdmin.php' ?>">Inventory</a></li>
                <li><a href="OrderList.php" class="active">Order List</a></li>
                <?php if ($is_owner): ?>
                    <li><a href="user_management.php">Users</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <div class="mobile-header">
            <div class="logo">
                <a href="<?= $is_owner ? 'owner.php' : 'admin.php' ?>"><img src="images.png" alt="Baga Burger Logo"></a>
            </div>
            <button class="menu-toggle" aria-label="Open Menu"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <div id="mobile-overlay" class="overlay">
      <a href="javascript:void(0)" class="closebtn" aria-label="Close Menu">&times;</a>
      <div class="overlay-content">
        <a href="<?= $is_owner ? 'owner.php' : 'admin.php' ?>" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="<?= $is_owner ? 'InventoryManagementOwner.php' : 'InventoryManagementAdmin.php' ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventory</a>
        <a href="OrderList.php" class="nav-link"><i class="fas fa-clipboard-list"></i> Order List</a>
        <?php if ($is_owner): ?>
            <a href="user_management.php" class="nav-link"><i class="fas fa-users-cog"></i> User Management</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>

    <main>
        <section class="glass-section">
            <div class="order-dashboard">
                <h1>Order List</h1>

                <form action="OrderList.php" method="GET" class="filter-bar">
                    <input type="text" name="q" placeholder="Search by Order ID..." value="<?= htmlspecialchars($search_query) ?>">
                    <select name="filter_status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="For Confirmation" <?= $filter_status == 'For Confirmation' ? 'selected' : '' ?>>For Confirmation</option>
                        <option value="Preparing" <?= $filter_status == 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                        <option value="Ready for Pickup" <?= $filter_status == 'Ready for Pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                        <option value="Wrong Reference #" <?= $filter_status == 'Wrong Reference #' ? 'selected' : '' ?>>Wrong Reference #</option>
                        <option value="Completed" <?= $filter_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <?php if ($is_owner): ?>
                            <option value="Archived" <?= $filter_status == 'Archived' ? 'selected' : '' ?>>Archived</option>
                        <?php endif; ?>
                    </select>
                    <button type="submit" class="btn-primary">Search</button>
                </form>

                <div class="stats-bar">
                    <div class="stat-box"><h3>Pending Confirmation</h3><p><?= $stats['For Confirmation'] ?></p></div>
                    <div class="stat-box"><h3>Currently Preparing</h3><p><?= $stats['Preparing'] ?></p></div>
                </div>

                <div class="order-grid">
                    <?php if (empty($orders)): ?>
                        <p>No orders found matching your criteria.</p>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card status-<?= str_replace([' ', '#'], ['-', '\#'], $order['status']) ?>">
                                <div class="order-card-header">
                                    <h3>Order #<?= $order['order_id'] ?></h3>
                                    <span class="status-badge badge-<?= str_replace([' ', '#'], ['-', '\#'], $order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span>
                                </div>
                                <div class="order-card-body">
                                    <div class="order-card-details">
                                        <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?></p>
                                        <p><strong>Total:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
                                        <p><strong>Reference #:</strong> <?= htmlspecialchars($order['payment_reference'] ?? 'N/A') ?></p>
                                        <p><strong>Placed:</strong> <?= date('M d, Y, g:i A', strtotime($order['created_at'])) ?></p>
                                    </div>
                                    <div class="order-card-items"><?= $order['items_list'] ?></div>
                                    <div class="order-card-actions">
                                        <form action="OrderList.php" method="POST" style="display:flex; gap:10px; flex-wrap: wrap;">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <input type="hidden" name="q" value="<?= htmlspecialchars($search_query) ?>">
                                            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($filter_status) ?>">
                                            <select name="status" style="flex-grow: 1; min-width: 150px; padding: 8px;">
                                                <option value="For Confirmation" <?= $order['status'] == 'For Confirmation' ? 'selected' : '' ?>>For Confirmation</option>
                                                <option value="Wrong Reference #" <?= $order['status'] == 'Wrong Reference #' ? 'selected' : '' ?>>Wrong Reference #</option>
                                                <option value="Preparing" <?= $order['status'] == 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                                                <option value="Ready for Pickup" <?= $order['status'] == 'Ready for Pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                                                <option value="Completed" <?= $order['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                <?php if ($is_owner): ?>
                                                    <option value="Archived" <?= $order['status'] == 'Archived' ? 'selected' : '' ?>>Archived</option>
                                                <?php endif; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-primary" style="padding: 8px 15px;">Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
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