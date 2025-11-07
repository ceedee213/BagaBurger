<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT code, name, price, stock, category FROM menu WHERE stock > 0 AND category != 'Add-ons' ORDER BY category, name ASC";
$result = $conn->query($sql);
$categorized_menu = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $category = $row['category'];
        if (!isset($categorized_menu[$category])) {
            $categorized_menu[$category] = [];
        }
        $categorized_menu[$category][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Baga Burger - Menu</title>
  <link rel="icon" type="image/png" href="images.png">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    .menu-container { padding-bottom: 120px; }
    .category-title { color: gold; text-align: left; border-bottom: 2px solid rgba(255, 255, 255, 0.3); padding-bottom: 10px; margin-top: 40px; margin-bottom: 20px; }
    .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .menu-item-card { background: rgba(0,0,0,0.4); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; text-align: center; }
    .menu-item-card img { width: 100%; height: 180px; object-fit: cover; background-color: #333; }
    .menu-item-info { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .menu-item-info h3 { margin: 0 0 5px 0; }
    .menu-item-info .price { color: gold; font-weight: bold; font-size: 1.2em; margin-bottom: 15px; }
    .floating-cart { position: fixed; bottom: 0; left: 0; width: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 15px rgba(0,0,0,0.3); box-sizing: border-box; }
    .cart-summary { font-size: 1.2em; font-weight: bold; color: white; }
    .cart-summary span { color: gold; }
    .btn-view-cart { background: #007bff; color: white; padding: 12px 25px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; font-size: 1em; text-transform: uppercase; }
    .btn-view-cart:disabled { background: #6c757d; cursor: not-allowed; }
    .btn-add-item { background: gold; color: black; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; }
    .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); justify-content: center; align-items: center; }
    .modal-content { background: #333; color: white; padding: 25px; border-radius: 15px; width: 90%; max-width: 600px; max-height: 85vh; display: flex; flex-direction: column; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #555; padding-bottom: 15px; margin-bottom: 20px; }
    .modal-header h2 { margin: 0; color: gold; }
    .close-btn { font-size: 28px; font-weight: bold; cursor: pointer; }
    .modal-body { overflow-y: auto; flex-grow: 1; }
    .modal-footer { border-top: 1px solid #555; padding-top: 20px; margin-top: 20px; text-align: right; }
    .addons-list { list-style: none; padding: 0; margin: 0 0 20px 0; }
    .addons-list li { display: flex; align-items: center; padding: 8px 0; }
    .addons-list img { width: 40px; height: 40px; border-radius: 5px; margin-right: 15px; object-fit: cover; }
    .addons-list label { font-size: 1.1em; flex-grow: 1; }
    .cart-item-list { list-style: none; padding: 0; margin: 0; }
    .cart-item { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #444; }
    .cart-item img { width: 60px; height: 60px; border-radius: 8px; margin-right: 15px; object-fit: cover; }
    .cart-item-details { flex-grow: 1; }
    .cart-item-details .item-name { display: block; font-weight: bold; margin-bottom: 5px; }
    .cart-item-details .item-price { color: #ccc; font-size: 0.9em; }
    .cart-item-actions { display: flex; align-items: center; gap: 10px; }
    .btn-edit-item { background: #17a2b8; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; font-size: 1em; cursor: pointer; }
    .btn-delete-item { background: #dc3545; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; font-size: 1.2em; cursor: pointer; font-weight: bold; }
    .empty-cart-message { text-align: center; padding: 40px 0; }
    .cart-total { font-size: 1.3em; font-weight: bold; }
    .cart-total span { color: gold; }
    .quantity-selector { display: flex; align-items: center; gap: 15px; }
    .quantity-selector button { background: gold; color: black; border: none; width: 30px; height: 30px; border-radius: 50%; font-size: 1.2em; font-weight: bold; cursor: pointer; }
    .quantity-selector .quantity { font-size: 1.2em; font-weight: bold; }
  </style>
</head>
<body>

<header>
    <nav class="desktop-nav">
        <div class="logo">
            <a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a>
        </div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="preorder.php" class="active">How to Order</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="feedback.php">Feedback</a></li>
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
    <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
    <a href="about.php" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
    <a href="preorder.php" class="nav-link"><i class="fas fa-shopping-cart"></i> How to Order</a>
    <a href="my_orders.php" class="nav-link"><i class="fas fa-history"></i> My Orders</a>
    <a href="contact.php" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
    <a href="feedback.php" class="nav-link"><i class="fas fa-star"></i> Feedback</a>
    <a href="logout.php" class="nav-link" onclick="return confirm('Are you sure you want to log out?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<main>
  <section class="glass-section menu-container">
    <h1>Our Menu</h1>
    <?php if (empty($categorized_menu)): ?>
      <p>Our menu is currently empty. Please check back later!</p>
    <?php else: ?>
      <?php foreach ($categorized_menu as $category => $items): ?>
        <h2 class="category-title"><?= htmlspecialchars($category) ?></h2>
        <div class="menu-grid">
          <?php foreach ($items as $item): ?>
          <div class="menu-item-card" data-code="<?= htmlspecialchars($item['code']) ?>" data-name="<?= htmlspecialchars($item['name']) ?>">
            <img src="product_images/<?= htmlspecialchars($item['code']) ?>.jpg" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.onerror=null;this.src='images.png';">
            <div class="menu-item-info">
              <div>
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p class="price">₱<?= number_format($item['price'], 2) ?></p>
              </div>
              <button type="button" class="btn-add-item">Add to Order</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<div id="customization-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-item-name">Customize Item</h2>
            <span class="close-btn" data-modal="customization-modal">&times;</span>
        </div>
        <form id="modal-form">
            <div class="modal-body">
                <input type="hidden" name="action" value="submit_item">
                <input type="hidden" id="modal-item-code" name="item_code">
                <input type="hidden" id="modal-original-ids" name="original_ids">
                <h4>Extras:</h4>
                <ul id="modal-addons-list" class="addons-list"></ul>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="quantity-selector">
                    <button type="button" id="modal-minus-btn">-</button>
                    <span id="modal-quantity" class="quantity">1</span>
                    <button type="button" id="modal-plus-btn">+</button>
                </div>
                <button type="submit" class="btn-primary" id="modal-submit-btn">Add to Cart</button>
            </div>
        </form>
    </div>
</div>

<div id="view-cart-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Your Order</h2>
            <span class="close-btn" data-modal="view-cart-modal">&times;</span>
        </div>
        <div class="modal-body" id="cart-modal-body"></div>
        <div class="modal-footer" id="cart-modal-footer-content"></div>
    </div>
</div>

<div class="floating-cart">
    <div class="cart-summary">
        🛒 Items: <span id="total-items">0</span> | Total: <span>₱</span><span id="total-price">0.00</span>
    </div>
    <button type="button" class="btn-view-cart" id="view-cart-btn">View Cart</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // This is your existing, complex cart JavaScript. It remains unchanged.
    let availableAddons = [];
    fetch('get_addons.php').then(res => res.json()).then(data => availableAddons = data);
    const openModal = id => document.getElementById(id).style.display = 'flex';
    const closeModal = id => document.getElementById(id).style.display = 'none';
    document.querySelectorAll('.close-btn').forEach(btn => btn.onclick = () => closeModal(btn.dataset.modal));
    window.onclick = e => { if (e.target.classList.contains('modal')) closeModal(e.target.id); };
    document.querySelectorAll('.btn-add-item').forEach(button => {
        button.addEventListener('click', () => {
            const card = button.closest('.menu-item-card');
            prepareCustomizationModal('add', { name: card.dataset.name, main_item_code: card.dataset.code });
        });
    });
    function prepareCustomizationModal(mode, itemData) {
        const form = document.getElementById('modal-form');
        document.getElementById('modal-item-name').textContent = (mode === 'edit' ? `Editing: ` : '') + itemData.name;
        document.getElementById('modal-item-code').value = itemData.main_item_code;
        document.getElementById('modal-quantity').textContent = itemData.quantity || 1;
        document.getElementById('modal-submit-btn').textContent = mode === 'edit' ? 'Update Item' : 'Add to Cart';
        document.getElementById('modal-original-ids').value = mode === 'edit' ? JSON.stringify(itemData.ids) : '[]';
        const addonList = document.getElementById('modal-addons-list');
        addonList.innerHTML = '';
        availableAddons.forEach(addon => {
            const isChecked = (mode === 'edit' && itemData.addon_codes.includes(addon.code)) ? 'checked' : '';
            addonList.innerHTML += `
                <li>
                    <img src="product_images/${addon.code}.jpg" alt="${addon.name}" onerror="this.style.display='none'">
                    <label>
                        <input type="checkbox" name="addons[]" value="${addon.code}" ${isChecked}>
                        ${addon.name} (+₱${parseFloat(addon.price).toFixed(2)})
                    </label>
                </li>`;
        });
        openModal('customization-modal');
    }
    document.getElementById('modal-plus-btn').onclick = () => updateModalQuantity(1);
    document.getElementById('modal-minus-btn').onclick = () => updateModalQuantity(-1);
    function updateModalQuantity(change) {
        const qtyEl = document.getElementById('modal-quantity');
        let qty = parseInt(qtyEl.textContent) + change;
        if (qty >= 1) qtyEl.textContent = qty;
    }
    document.getElementById('modal-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append('quantity', document.getElementById('modal-quantity').textContent);
        await fetch('cart_actions.php', { method: 'POST', body: formData });
        closeModal('customization-modal');
        updateCartSummary();
        const isCartOpen = document.getElementById('view-cart-modal').style.display === 'flex';
        if (isCartOpen) {
            renderCartModal();
        }
    });
    document.getElementById('view-cart-btn').addEventListener('click', renderCartModal);
    async function renderCartModal() {
        const response = await fetch('cart_actions.php?action=view');
        const cartData = await response.json();
        const cartBody = document.getElementById('cart-modal-body');
        const cartFooter = document.getElementById('cart-modal-footer-content');
        if (cartData.items && cartData.items.length > 0) {
            cartBody.innerHTML = '<ul class="cart-item-list">' + cartData.items.map(item => `
                <li class="cart-item" data-item='${JSON.stringify(item)}'>
                    <img src="product_images/${item.image_code}.jpg" alt="${item.description}" onerror="this.onerror=null;this.src='images.png';">
                    <div class="cart-item-details">
                        <span class="item-name">${item.description}</span>
                        <span class="item-price">${item.quantity} x ₱${parseFloat(item.price).toFixed(2)}</span>
                    </div>
                    <div class="cart-item-actions">
                        <button type="button" class="btn-edit-item" title="Edit Item"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn-delete-item" title="Remove Item">&times;</button>
                    </div>
                </li>`).join('') + '</ul>';
            cartFooter.innerHTML = `
                <div class="cart-total">Total: <span>₱${cartData.totalPrice.toFixed(2)}</span></div>
                <div style="margin-top: 15px;">
                     <button type="button" class="btn-secondary" id="clear-cart-btn" style="background:#6c757d; color:white; padding:10px 15px; border-radius:8px; border:none; cursor:pointer;">Clear All</button>
                     <a href="process_order.php" class="btn-primary" style="text-decoration:none;">Proceed to Checkout</a>
                </div>`;
            cartBody.querySelectorAll('.btn-edit-item').forEach(btn => btn.addEventListener('click', handleEditItem));
            cartBody.querySelectorAll('.btn-delete-item').forEach(btn => btn.addEventListener('click', handleDeleteItem));
            document.getElementById('clear-cart-btn').addEventListener('click', handleClearCart);
        } else {
            cartBody.innerHTML = '<p class="empty-cart-message">Your cart is empty.</p>';
            cartFooter.innerHTML = '<a href="#" onclick="closeModal(\'view-cart-modal\'); return false;" class="btn-primary">Continue Shopping</a>';
        }
        openModal('view-cart-modal');
    }
    function handleEditItem(event) {
        const itemData = JSON.parse(event.target.closest('.cart-item').dataset.item);
        closeModal('view-cart-modal');
        prepareCustomizationModal('edit', itemData);
    }
    async function handleDeleteItem(event) {
        const itemData = JSON.parse(event.target.closest('.cart-item').dataset.item);
        if (confirm(`Are you sure you want to remove all "${itemData.description}" from your cart?`)) {
            const formData = new FormData();
            formData.append('action', 'delete_item');
            formData.append('ids', JSON.stringify(itemData.ids));
            await fetch('cart_actions.php', { method: 'POST', body: formData });
            await renderCartModal();
            await updateCartSummary();
        }
    }
    async function handleClearCart() {
        if (confirm('Are you sure you want to clear your entire cart?')) {
            const formData = new FormData();
            formData.append('action', 'clear');
            await fetch('cart_actions.php', { method: 'POST', body: formData });
            await renderCartModal();
            await updateCartSummary();
        }
    }
    function updateCartSummary() {
        fetch('get_cart_summary.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-items').textContent = data.total_items;
                document.getElementById('total-price').textContent = data.total_price.toFixed(2);
                document.getElementById('view-cart-btn').disabled = data.total_items <= 0;
            });
    }
    updateCartSummary();
}); 
</script>

<script>
// This new script handles the hybrid navigation
document.addEventListener("DOMContentLoaded", () => {
    const openNav = () => document.getElementById("mobile-overlay").style.height = "100%";
    const closeNav = () => document.getElementById("mobile-overlay").style.height = "0%";
    document.querySelector('.menu-toggle').addEventListener('click', openNav);
    document.querySelector('.closebtn').addEventListener('click', closeNav);
});
</script>

</body>
</html>