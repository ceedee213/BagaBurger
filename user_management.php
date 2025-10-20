<?php
session_start();
require 'db.php';

// Security check: Only allow 'owner' to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    die("Access Denied. You do not have permission to view this page.");
}

$feedback = '';

// --- HANDLE DEACTIVATE request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_user'])) {
    $user_id_to_deactivate = intval($_POST['user_id']);
    
    if ($user_id_to_deactivate === $_SESSION['user_id']) {
        $feedback = "<p class='feedback error'>Error: You cannot deactivate your own account.</p>";
    } else {
        $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id_to_deactivate);
        if ($stmt->execute()) {
            $feedback = "<p class='feedback success'>Success: User account has been deactivated.</p>";
        } else {
            $feedback = "<p class='feedback error'>Error: Could not deactivate user account.</p>";
        }
        $stmt->close();
    }
}

// --- Handle ADD USER request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!in_array($role, ['user', 'admin'])) {
        $feedback = "<p class='feedback error'>Error: Invalid role selected.</p>";
    } else {
        $check_user = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_user->bind_param("ss", $username, $email);
        $check_user->execute();
        $check_user->store_result();

        if ($check_user->num_rows > 0) {
            $feedback = "<p class='feedback error'>Error: Username or email is already taken.</p>";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Accounts created by the owner are activated immediately.
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_active, is_verified) VALUES (?, ?, ?, ?, 1, 1)");
            $stmt->bind_param("ssss", $username, $email, $password_hash, $role);
            if ($stmt->execute()) {
                $feedback = "<p class='feedback success'>Success: New account created and activated successfully.</p>";
            } else {
                $feedback = "<p class='feedback error'>Error: An unexpected error occurred.</p>";
            }
            $stmt->close();
        }
        $check_user->close();
    }
}

// Fetch all users to display
$users = [];
$result = $conn->query("SELECT id, username, email, role, is_active FROM users ORDER BY role, username ASC");
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Management - Owner</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .user-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .user-card { background: rgba(0,0,0,0.4); border-radius: 12px; overflow: hidden; }
        .user-card-header { padding: 15px; background: rgba(0,0,0,0.2); border-left: 5px solid; }
        .user-card-header h3 { margin: 0; }
        .user-card-body { padding: 20px; }
        .user-card-body p { margin: 0 0 15px 0; font-size: 1.1em; }
        .user-card-actions { margin-top: 20px; text-align: center; }
        .btn-deactivate { background: #ffc107; color: black; padding: 8px 15px; border-radius: 6px; border: none; cursor: pointer; font-family: inherit; font-size: 1em; font-weight: bold; }
        .feedback { padding: 15px; border-radius: 8px; margin-bottom: 20px; color: white; }
        .feedback.success { background: #28a745; }
        .feedback.error { background: #dc3545; }
        /* Role and Status Styles */
        .role-owner { border-left-color: gold; }
        .role-admin { border-left-color: #dc3545; }
        .role-user { border-left-color: #007bff; }
        .status-badge { display: inline-block; padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9em; }
        .status-active { background: #28a745; color: white; }
        .status-inactive { background: #6c757d; color: white; }
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #2c2c2c; margin: 10% auto; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px; color: white; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #555; padding-bottom: 15px; margin-bottom: 20px; }
        .modal-header h2 { margin: 0; }
        .close-btn { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .form-group select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #555; background: #333; color: white; }
    </style>
</head>
<body>
<header>
  <nav>
    <div class="logo"><a href="owner.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
    <button class="nav-toggle" aria-label="toggle navigation">
        <span class="hamburger"></span>
    </button>
    <ul>
      <li><a href="owner.php">Dashboard</a></li>
      <li><a href="MenuManagementOwner.php">Menu Management</a></li>
      <li><a href="OrderList.php">Order List</a></li>
      <li><a href="user_management.php" class="active">User Management</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
</header>
<main>
    <section class="glass-section">
        <div class="page-header">
            <h1>👥 User Account Management</h1>
            <button class="btn-primary" onclick="openModal('addUserModal')">(+) Add New Account</button>
        </div>
        
        <?= $feedback ?>

        <div class="user-grid">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-card-header role-<?= strtolower(htmlspecialchars($user['role'])) ?>">
                        <h3>👤 <?= htmlspecialchars($user['username']) ?> <small>(<?= htmlspecialchars(ucfirst($user['role'])) ?>)</small></h3>
                    </div>
                    <div class="user-card-body">
                        <p>✉️ <?= htmlspecialchars($user['email']) ?></p>
                        <div style="text-align:center;">
                            <?php if ($user['is_active']): ?>
                                <span class="status-badge status-active">Active</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Inactive</span>
                            <?php endif; ?>
                        </div>
                        <div class="user-card-actions">
                            <?php if ($_SESSION['user_id'] !== $user['id'] && $user['role'] !== 'owner' && $user['is_active']): ?>
                                <form action="user_management.php" method="POST" onsubmit="return confirm('Are you sure you want to deactivate this account?');">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="deactivate_user" class="btn-deactivate">Deactivate</button>
                                </form>
                            <?php elseif ($_SESSION['user_id'] === $user['id']): ?>
                                <p style="font-size: 0.9em; color: #ccc;">(This is you)</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Account</h2>
            <span class="close-btn" onclick="closeModal('addUserModal')">&times;</span>
        </div>
        <form action="user_management.php" method="POST">
            <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn-primary">Add Account</button>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) { document.getElementById(modalId).style.display = 'block'; }
    function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>

<script src="responsive.js"></script>

</body>
</html>