<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- ADDED LOGIC: Load the cart from the session ---
// This checks if a cart already exists in the session and loads it.
// If not, it creates an empty cart.
$cart = $_SESSION['cart'] ?? [];

// This part of your code is unchanged
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
    .menu-container { padding-bottom: 120px; /* Space for the floating cart */ }
    .category-title {
        color: gold;
        text-align: left;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        padding-bottom: 10px;
        margin-top: 40px;
        margin-bottom: 20px;
    }
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    .menu-item-card {
        background: rgba(0,0,0,0.4);
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        text-align: center;
    }
    .menu-item-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background-color: #333;
    }
    .menu-item-info {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .menu-item-info h3 { margin: 0 0 5px 0; }
    .menu-item-info .price { color: gold; font-weight: bold; font-size: 1.2em; margin-bottom: 15px; }
    .quantity-selector {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
    }
    .quantity-selector button {
        background: gold;
        color: black;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        font-size: 1.2em;
        font-weight: bold;
        cursor: pointer;
    }
    .quantity-selector .quantity { font-size: 1.2em; font-weight: bold; }
    /* Floating Cart Styles */
    .floating-cart {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(10px);
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 -4px 15px rgba(0,0,0,0.3);
        box-sizing: border-box;
    }
    .cart-summary { font-size: 1.2em; font-weight: bold; }
    .cart-summary span { color: gold; }
    .btn-place-order {
        background: #28a745;
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        font-size: 1em;
        text-transform: uppercase;
    }
    .btn-place-order:disabled { background: #6c757d; cursor: not-allowed; }
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
    <form action="addons.php" method="POST" id="menu-form">
      <?php if (empty($categorized_menu)): ?>
        <p>Our menu is currently empty. Please check back later!</p>
      <?php else: ?>
        <?php foreach ($categorized_menu as $category => $items): ?>
          <h2 class="category-title"><?= htmlspecialchars($category) ?></h2>
          <div class="menu-grid">
            <?php foreach ($items as $item): ?>
            <?php
                // --- ADDED LOGIC: Check session for existing quantity ---
                // This sets the quantity for the item based on what's in the session cart.
                $quantity = isset($cart[$item['code']]) ? intval($cart[$item['code']]) : 0;
                $minus_disabled = $quantity === 0 ? 'disabled' : '';
            ?>
            <div class="menu-item-card" data-code="<?= $item['code'] ?>" data-price="<?= $item['price'] ?>" data-stock="<?= $item['stock'] ?>">
              <img src="product_images/<?= htmlspecialchars($item['code']) ?>.jpg" alt="<?= htmlspecialchars($item['name']) ?>"
                   onerror="this.onerror=null;this.src='images.png';">
              <div class="menu-item-info">
                <div>
                  <h3><?= htmlspecialchars($item['name']) ?></h3>
                  <p class="price">₱<?= number_format($item['price'], 2) ?></p>
                </div>
                <div class="quantity-selector">
                  <button type="button" class="btn-minus" <?= $minus_disabled ?>>-</button>
                  <span class="quantity"><?= $quantity ?></span>
                  <button type="button" class="btn-plus">+</button>
                  <input type="hidden" name="items[<?= $item['code'] ?>]" value="<?= $quantity ?>" class="quantity-input">
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </form>
  </section>
</main>

<div class="floating-cart">
    <div class="cart-summary">
        🛒 Items: <span id="total-items">0</span> | Total: <span>₱</span><span id="total-price">0.00</span>
    </div>
    <button type="submit" form="menu-form" class="btn-place-order" id="place-order-btn" disabled>Proceed to Add-ons</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.menu-item-card');
    const totalItemsElem = document.getElementById('total-items');
    const totalPriceElem = document.getElementById('total-price');
    const placeOrderBtn = document.getElementById('place-order-btn');

    function updateCartSummary() {
        let totalItems = 0;
        let totalPrice = 0;

        cards.forEach(card => {
            const quantity = parseInt(card.querySelector('.quantity-input').value);
            const price = parseFloat(card.dataset.price);

            totalItems += quantity;
            totalPrice += quantity * price;
        });

        totalItemsElem.textContent = totalItems;
        totalPriceElem.textContent = totalPrice.toFixed(2);

        placeOrderBtn.disabled = totalItems <= 0;
    }

    cards.forEach(card => {
        const plusBtn = card.querySelector('.btn-plus');
        const minusBtn = card.querySelector('.btn-minus');
        const quantityElem = card.querySelector('.quantity');
        const quantityInput = card.querySelector('.quantity-input');
        const maxStock = parseInt(card.dataset.stock);

        plusBtn.addEventListener('click', () => {
            let currentQty = parseInt(quantityInput.value);
            if (currentQty < maxStock) {
                currentQty++;
                quantityInput.value = currentQty;
                quantityElem.textContent = currentQty;
                minusBtn.disabled = false;
                if (currentQty >= maxStock) {
                    plusBtn.disabled = true;
                }
                updateCartSummary();
            }
        });

        minusBtn.addEventListener('click', () => {
            let currentQty = parseInt(quantityInput.value);
            if (currentQty > 0) {
                currentQty--;
                quantityInput.value = currentQty;
                quantityElem.textContent = currentQty;
                plusBtn.disabled = false;
                if (currentQty === 0) {
                    minusBtn.disabled = true;
                }
                updateCartSummary();
            }
        });
    });

    // --- ADDED LOGIC: Update the cart summary on page load ---
    // This makes sure the floating cart shows the correct totals
    // based on the items loaded from the session.
    updateCartSummary();
});
</script>
</body>
</html>