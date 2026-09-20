<?php
require_once __DIR__ . '/core/Cart.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/Inventory.php';

$cart = new Cart();
$inventory = new Inventory($db);
$products = $inventory->getProducts();

$pageTitle = 'Home';
$activeCategory = '';

require __DIR__ . '/views/header.php';
?>

<div class="hero">
    <h1>AS-IS WAREHOUSE CLEARANCE</h1>
    <p>Deeply discounted secondary-market computer hardware. All items are sold strictly as-is, where-is, with no returns or warranties.</p>
</div>

<div class="store-container">
    <div class="grid">
        <?php require __DIR__ . '/views/add_item.php'; ?>
        
        <?php foreach ($products as $p): ?>
            <?php require __DIR__ . '/views/product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/views/footer.php'; ?>