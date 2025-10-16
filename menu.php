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
  <link rel="stylesheet" href="style.css">
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
    .cart-summary { font-size: 1.2em; font-weight: bold; }
    .cart-summary span { color: gold; }
    .btn-place-order { background: #28a745; color: white; padding: 12px 25px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; font-size: 1em; text-transform: uppercase; }
    .btn-place-order:disabled { background: #6c757d; cursor: not-allowed; }
    .btn-add-item { background: gold; color: black; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; }
    .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); justify-content: center; align-items: center; }
    .modal-content { background: #333; color: white; padding: 25px; border-radius: 15px; width: 90%; max-width: 500px; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #555; padding-bottom: 15px; margin-bottom: 20px; }
    .modal-header h2 { margin: 0; color: gold; }
    .close-btn { font-size: 28px; font-weight: bold; cursor: pointer; }
    .addons-list { list-style: none; padding: 0; margin: 0 0 20px 0; }
    .addons-list li { display: flex; align-items: center; padding: 8px 0; } /* Flex for alignment */
    .addons-list img { width: 40px; height: 40px; border-radius: 5px; margin-right: 15px; object-fit: cover; } /* Image style */
    .addons-list label { font-size: 1.1em; flex-grow: 1; } /* Label takes remaining space */
    .modal-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
    .quantity-selector { display: flex; justify-content: center; align-items: center; gap: 15px; }
    .quantity-selector button { background: gold; color: black; border: none; width: 30px; height: 30px; border-radius: 50%; font-size: 1.2em; font-weight: bold; cursor: pointer; }
    .quantity-selector .quantity { font-size: 1.2em; font-weight: bold; }
  </style>
</head>
<body>
<header>
  <nav>
    <div class="logo"><a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About Us</a></li>
      <li><a href="preorder.php" class="active">Pre-order Now</a></li>
      <li><a href="my_orders.php">My Orders</a></li>
      <li><a href="contact.php">Contact Us</a></li>
      <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')">Logout</a></li>
    </ul>
  </nav>
</header>
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
          <div class="menu-item-card" 
               data-code="<?= htmlspecialchars($item['code']) ?>" 
               data-name="<?= htmlspecialchars($item['name']) ?>"
               data-price="<?= $item['price'] ?>">
            <img src="product_images/<?= htmlspecialchars($item['code']) ?>.jpg" alt="<?= htmlspecialchars($item['name']) ?>"
                 onerror="this.onerror=null;this.src='images.png';">
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
            <span class="close-btn">&times;</span>
        </div>
        <form id="modal-form">
            <input type="hidden" id="modal-item-code" name="item_code">
            <h4>Extras:</h4>
            <ul id="modal-addons-list" class="addons-list"></ul>
            <div class="modal-footer">
                <div class="quantity-selector">
                    <button type="button" id="modal-minus-btn">-</button>
                    <span id="modal-quantity" class="quantity">1</span>
                    <button type="button" id="modal-plus-btn">+</button>
                </div>
                <button type="submit" class="btn-primary">Add to Cart</button>
            </div>
        </form>
    </div>
</div>

<div class="floating-cart">
    <div class="cart-summary">
        🛒 Items: <span id="total-items">0</span> | Total: <span>₱</span><span id="total-price">0.00</span>
    </div>
    <a href="process_order.php" class="btn-place-order" id="place-order-btn">Review Order</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('customization-modal');
    const closeBtn = modal.querySelector('.close-btn');
    const addonList = document.getElementById('modal-addons-list');
    let availableAddons = [];

    fetch('get_addons.php')
        .then(response => response.json())
        .then(data => {
            availableAddons = data;
        });

    document.querySelectorAll('.btn-add-item').forEach(button => {
        button.addEventListener('click', event => {
            const card = event.target.closest('.menu-item-card');
            document.getElementById('modal-item-name').textContent = card.dataset.name;
            document.getElementById('modal-item-code').value = card.dataset.code;
            document.getElementById('modal-quantity').textContent = 1;

            addonList.innerHTML = '';
            availableAddons.forEach(addon => {
                const li = document.createElement('li');
                // --- MODIFIED: Added the <img> tag here ---
                li.innerHTML = `
                    <img src="product_images/${addon.code}.jpg" alt="${addon.name}" onerror="this.style.display='none'">
                    <label>
                        <input type="checkbox" name="addons[]" value="${addon.code}">
                        ${addon.name} (+₱${parseFloat(addon.price).toFixed(2)})
                    </label>`;
                addonList.appendChild(li);
            });
            modal.style.display = 'flex';
        });
    });

    closeBtn.onclick = () => { modal.style.display = 'none'; };
    window.onclick = event => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    const qtyElem = document.getElementById('modal-quantity');
    document.getElementById('modal-plus-btn').onclick = () => {
        qtyElem.textContent = parseInt(qtyElem.textContent) + 1;
    };
    document.getElementById('modal-minus-btn').onclick = () => {
        let qty = parseInt(qtyElem.textContent);
        if (qty > 1) {
            qtyElem.textContent = qty - 1;
        }
    };
    
    document.getElementById('modal-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append('quantity', document.getElementById('modal-quantity').textContent);

        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        }).then(() => {
            updateCartSummary();
            modal.style.display = 'none';
        });
    });

    function updateCartSummary() {
        fetch('get_cart_summary.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-items').textContent = data.total_items;
                document.getElementById('total-price').textContent = data.total_price.toFixed(2);
                document.getElementById('place-order-btn').style.pointerEvents = data.total_items > 0 ? 'auto' : 'none';
                document.getElementById('place-order-btn').style.opacity = data.total_items > 0 ? '1' : '0.5';
            });
    }

    updateCartSummary();
});
</script>
</body>
</html>