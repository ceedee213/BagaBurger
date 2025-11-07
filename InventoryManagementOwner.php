<?php
session_start();
require 'db.php';

// Security check: ONLY the 'owner' can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    die("Access Denied. You do not have permission to view this page.");
}

$feedback = '';

// --- Form handling and data fetching logic remains the same ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $code = trim($_POST['code']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);
    $stmt = $conn->prepare("INSERT INTO menu (name, price, code, stock, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsis", $name, $price, $code, $stock, $category);
    if ($stmt->execute()) {
        $feedback = "<p class='feedback success'>Success: New item added.</p>";
    } else {
        $feedback = "<p class='feedback error'>Error: Could not add item. The 'code' may already exist.</p>";
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    $item_id = intval($_POST['item_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $code = trim($_POST['code']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);
    $stmt = $conn->prepare("UPDATE menu SET name=?, price=?, code=?, stock=?, category=? WHERE id=?");
    $stmt->bind_param("sdsisi", $name, $price, $code, $stock, $category, $item_id);
    if ($stmt->execute()) {
        $feedback = "<p class='feedback success'>Success: Item updated.</p>";
    } else {
        $feedback = "<p class='feedback error'>Error: Could not update item.</p>";
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $item_id_to_delete = intval($_POST['item_id']);
    $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
    $stmt->bind_param("i", $item_id_to_delete);
    if ($stmt->execute()) {
        $feedback = "<p class='feedback success'>Success: Item deleted.</p>";
    } else {
        $feedback = "<p class='feedback error'>Error: Could not delete item.</p>";
    }
    $stmt->close();
}
$search_query = $_GET['q'] ?? '';
$filter_category = $_GET['filter_category'] ?? '';
$where_clauses = [];
$params = [];
$types = '';
if (!empty($search_query)) {
    $where_clauses[] = "name LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= 's';
}
if (!empty($filter_category)) {
    $where_clauses[] = "category = ?";
    $params[] = $filter_category;
    $types .= 's';
}
$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';
$sql = "SELECT id, name, price, code, stock, category, created_at FROM menu $where_sql ORDER BY category, name ASC";
$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$menu_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$categories = $conn->query("SELECT DISTINCT category FROM menu ORDER BY category ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Inventory Management - Owner</title>
    <link rel="icon" type="image/png" href="images.png">
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; align-items: center; flex-wrap: wrap; }
        .filter-bar input, .filter-bar select { padding: 10px; border-radius: 8px; border: 1px solid #555; background: #333; color: white; flex-grow: 1; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .menu-card { background: rgba(0,0,0,0.4); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
        .menu-card-header { padding: 15px; background: rgba(0,0,0,0.2); }
        .menu-card-header h3 { margin: 0; }
        .menu-card-body { padding: 15px; flex-grow: 1; }
        .menu-card-details p { margin: 0 0 10px 0; }
        .menu-card-details strong { color: gold; }
        .menu-card-actions { padding: 15px; border-top: 1px solid rgba(255,255,255,0.2); display: flex; gap: 10px; justify-content: center; }
        .btn-edit { background: #007bff; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; cursor:pointer; border:none; font-family:inherit; font-size:inherit; }
        .btn-delete { background: #dc3545; color: white; padding: 8px 15px; border-radius: 6px; border: none; cursor: pointer; font-family: inherit; font-size: inherit; }
        .stock-level { font-weight: bold; }
        .stock-ok { color: #28a745; }
        .stock-low { color: #ffc107; }
        .stock-out { color: #dc3545; }
        .feedback { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .feedback.success { background: #28a745; color: white; }
        .feedback.error { background: #dc3545; color: white; }
        .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #2c2c2c; margin: 10% auto; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px; color: white; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #555; padding-bottom: 15px; margin-bottom: 20px; }
        .modal-header h2 { margin: 0; }
        .close-btn { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #555; background: #333; color: white; box-sizing: border-box; }
    </style>
</head>
<body>
    <header>
        <nav class="desktop-nav">
            <div class="logo">
                <a href="owner.php"><img src="images.png" alt="Baga Burger Logo"></a>
            </div>
            <ul>
                <li><a href="owner.php">Dashboard</a></li>
                <li><a href="InventoryManagementOwner.php" class="active">Inventory</a></li>
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
            <div class="page-header">
                <h1>📦 Inventory Management (Owner)</h1>
                <button class="btn-primary" onclick="openModal('addModal')">(+) Add New Item</button>
            </div>
            <?= $feedback ?>
            
            <form action="InventoryManagementOwner.php" method="GET" class="filter-bar">
                <input type="text" name="q" placeholder="Search by name..." value="<?= htmlspecialchars($search_query) ?>">
                <select name="filter_category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $filter_category == $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Search</button>
            </form>

            <div class="menu-grid">
                <?php foreach ($menu_items as $item): ?>
                    <div class="menu-card">
                        <div class="menu-card-header"><h3><?= htmlspecialchars($item['name']) ?></h3></div>
                        <div class="menu-card-body">
                            <div class="menu-card-details">
                                <p><strong>Price:</strong> ₱<?= number_format($item['price'], 2) ?></p>
                                <p><strong>Stock:</strong> 
                                    <span class="stock-level <?php 
                                        if ($item['stock'] == 0) echo 'stock-out';
                                        elseif ($item['stock'] <= 10) echo 'stock-low';
                                        else echo 'stock-ok';
                                    ?>">
                                        <?= $item['stock'] ?> 
                                        (<?php 
                                            if ($item['stock'] == 0) echo 'Out of Stock';
                                            elseif ($item['stock'] <= 10) echo 'Low Stock';
                                            else echo 'In Stock';
                                        ?>)
                                    </span>
                                </p>
                                <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                                <p><strong>Code:</strong> <?= htmlspecialchars($item['code']) ?></p>
                                <p><strong>Date Added:</strong> <?= date('M d, Y', strtotime($item['created_at'])) ?></p>
                            </div>
                        </div>
                        <div class="menu-card-actions">
                            <button class="btn-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
                            <form action="InventoryManagementOwner.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="delete_item" class="btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Item</h2>
                <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form action="InventoryManagementOwner.php" method="POST">
                <div class="form-group"><label>Item Name</label><input type="text" name="name" required></div>
                <div class="form-group"><label>Price (₱)</label><input type="number" step="0.01" name="price" required></div>
                <div class="form-group"><label>Item Code</label><input type="text" name="code" required></div>
                <div class="form-group"><label>Initial Stock</label><input type="number" name="stock" required></div>
                <div class="form-group"><label>Category</label><input type="text" name="category" required></div>
                <button type="submit" name="add_item" class="btn-primary">Add Item</button>
            </form>
        </div>
    </div>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Item</h2>
                <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form action="InventoryManagementOwner.php" method="POST">
                <input type="hidden" id="edit-item-id" name="item_id">
                <div class="form-group"><label>Item Name</label><input type="text" id="edit-name" name="name" required></div>
                <div class="form-group"><label>Price (₱)</label><input type="number" step="0.01" id="edit-price" name="price" required></div>
                <div class="form-group"><label>Item Code</label><input type="text" id="edit-code" name="code" required></div>
                <div class="form-group"><label>Stock</label><input type="number" id="edit-stock" name="stock" required></div>
                <div class="form-group"><label>Category</label><input type="text" id="edit-category" name="category" required></div>
                <button type="submit" name="update_item" class="btn-primary">Update Item</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) { document.getElementById(modalId).style.display = 'block'; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
        function openEditModal(item) {
            document.getElementById('edit-item-id').value = item.id;
            document.getElementById('edit-name').value = item.name;
            document.getElementById('edit-price').value = item.price;
            document.getElementById('edit-code').value = item.code;
            document.getElementById('edit-stock').value = item.stock;
            document.getElementById('edit-category').value = item.category;
            openModal('editModal');
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
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