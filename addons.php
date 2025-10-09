<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the main items selected from the menu page
$main_items = $_POST['items'] ?? [];

// Filter out items that were not selected
$selected_main_items = array_filter($main_items, function($qty) {
    return intval($qty) > 0;
});

if (empty($selected_main_items)) {
    // Redirect back if no main items were chosen
    header("Location: menu.php");
    exit;
}

// This SQL query specifically fetches the items you requested (bacon, cheese, etc.)
// as long as their category is set to 'Add-ons' in your database.
$sql = "SELECT code, name, price, stock, category FROM menu WHERE stock > 0 AND category = 'Add-ons' ORDER BY name ASC";
$result = $conn->query($sql);
$addons = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Baga Burger - Choose Add-ons</title>
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
    .quantity-selector { display: flex; justify-content: center; align-items: center; gap: 15px; }
    .quantity-selector button { background: gold; color: black; border: none; width: 30px; height: 30px; border-radius: 50%; font-size: 1.2em; font-weight: bold; cursor: pointer; }
    .quantity-selector .quantity { font-size: 1.2em; font-weight: bold; }
    .btn-place-order { background: #28a745; color: white; padding: 12px 25px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; font-size: 1em; text-transform: uppercase; }
  </style>
</head>
<body>
<header>
  <nav>
    <div class="logo"><a href="index.php"><img src="images.png" alt="Baga Burger Logo"></a></div>
    <ul>
      <li><a href="menu.php">Back to Menu</a></li>
      <li><a href="my_orders.php">My Orders</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
</header>
<main>
  <section class="glass-section menu-container">
    <h1>Want Some Add-ons?</h1>
    <form action="process_order.php" method="POST" id="addons-form">
      <?php
      // This part carries over the main items (burgers, drinks) you selected on the previous page.
      foreach ($selected_main_items as $code => $qty) {
          echo '<input type="hidden" name="items[' . htmlspecialchars($code) . ']" value="' . intval($qty) . '">';
      }
      ?>

      <?php if (empty($addons)): ?>
        <p>There are no add-ons available at the moment.</p>
      <?php else: ?>
        <h2 class="category-title">Extras</h2>
        <div class="menu-grid">
          <?php
          // This loop creates the visual list of your add-ons (bacon, cheese, etc.) for the user to choose from.
          foreach ($addons as $item):
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
                <button type="button" class="btn-minus" disabled>-</button>
                <span class="quantity">0</span>
                <button type="button" class="btn-plus">+</button>
                <input type="hidden" name="items[<?= $item['code'] ?>]" value="0" class="quantity-input">
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </form>
    <div style="text-align:center; margin-top: 30px;">
        <button type="submit" form="addons-form" class="btn-place-order">Review Final Order</button>
    </div>
  </section>
</main>
<script>
// This Javascript handles the '+' and '-' buttons for the add-ons.
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.menu-item-card');
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
            }
        });
    });
});
</script>
</body>
</html>

