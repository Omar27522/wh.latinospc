<?php
// views/product_card.php
// Modular product card
// Automatically delegates to the Tender edit card if a Tender is currently active!

require_once __DIR__ . '/../core/Tender.php';

if (Tender::isTenderMode()) {
    require __DIR__ . '/tender/product_card_tender.php';
    return;
}
?>
<!-- Customer (Shopper) Product Card -->
<article class='card'>
    <?php if (!empty($p['last_update'])): ?>
        <span class="last-updated">Last update: <?= htmlspecialchars($p['last_update']) ?></span>
    <?php endif; ?>
    
    <figure class='img-wrapper'>
        <picture>
            <source media='(max-width: 768px)' srcset='<?= htmlspecialchars($p['thumb']) ?>'>
            <img src='<?= htmlspecialchars($p['image']) ?>' alt='<?= htmlspecialchars($p['title']) ?>' class='card-img' width='600' height='600' loading='lazy' onerror="this.onerror=null; this.src='images/placeholder.svg';">
        </picture>
    </figure>
    <header>
        <h2 class='card-title'><?= htmlspecialchars($p['title']) ?></h2>
    </header>
    <p class='card-desc'><?= htmlspecialchars($p['description']) ?></p>
    <footer class='card-footer'>
        <div class='price'><span>$</span><?= number_format((float)$p['price'], 2) ?></div>
        <form method='POST' action='cart.php' style='margin:0;'>
            <input type='hidden' name='action' value='add'>
            <input type='hidden' name='product_id' value='<?= htmlspecialchars($p['id']) ?>'>
            <button type='submit' class='buy-btn'>Acquire</button>
        </form>
    </footer>
</article>
