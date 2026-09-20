<?php
require_once __DIR__ . '/core/Cart.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/Inventory.php';

$category = $_GET['cat'] ?? 'computers';
$cart = new Cart();
$inventory = new Inventory($db);
$products = $inventory->getProducts($category);

$pageTitle = ucfirst($category);
$activeCategory = $category;

require __DIR__ . '/views/header.php';
?>

<div class="hero" style="padding: 3rem 1rem;">
    <h1 style="font-size: 2.5rem;"><?= htmlspecialchars(strtoupper($category)) ?></h1>
    <p>Available inventory in this category.</p>
</div>

<div class="store-container">
    <div class="grid">
        <?php require __DIR__ . '/views/add_item.php'; ?>
        
        <?php if (empty($products)): ?>
            <p style="text-align: center; grid-column: 1/-1; color: var(--light-text);">No items found in this category.</p>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <?php require __DIR__ . '/views/product_card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/views/footer.php'; ?>
