<?php
// views/product_card.php
// $p is passed in containing product data
$is_auth = (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true);
$preview = $_SESSION['live_preview'] ?? false;
$editMode = $is_auth && !$preview;

if ($editMode):
?>
<article class='card edit-mode-card'>
    <form method='POST' action='admin_action.php' enctype='multipart/form-data' class="edit-form">
        <input type='hidden' name='action' value='edit'>
        <input type='hidden' name='id' value='<?= htmlspecialchars($p['id']) ?>'>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.25rem;">
            <?php if (!empty($p['location_code']) && !empty($p['is_warehouse'])): ?>
                <span class="card-origin-badge" title="Imported from Warehouse Physical Shelf">📦 Shelf: <?= htmlspecialchars($p['location_code']) ?></span>
            <?php else: ?>
                <span class="card-origin-badge custom" title="Custom store item">🏪 Store Custom</span>
            <?php endif; ?>
            <?php if ($p['last_update']): ?>
                <span class="last-updated" style="position: static; font-size: 0.65rem;">Updated: <?= htmlspecialchars($p['last_update']) ?></span>
            <?php endif; ?>
        </div>

        <figure class='img-wrapper' style='position:relative; margin-bottom: 1rem;'>
            <img src='<?= htmlspecialchars($p['image']) ?>' class='card-img' loading='lazy' onerror="this.onerror=null; this.src='images/placeholder.svg';">
            <figcaption>
                <label class="edit-photo-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 4px; vertical-align: text-bottom;">
                      <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                      <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/>
                    </svg>
                    Update Photo
                    <input type='file' name='photo' accept='image/*' style='display:none;' onchange='previewImage(this)'>
                </label>
            </figcaption>
        </figure>

        <div class="edit-inputs-group">
            <input type='text' class="edit-input title-input" name='brand' value='<?= htmlspecialchars($p['brand']) ?>' placeholder="Brand" required>
            <input type='text' class="edit-input title-input" name='model' value='<?= htmlspecialchars($p['model']) ?>' placeholder="Model" required>
        </div>

        <textarea class="edit-textarea" name='specs_json' rows='4' placeholder="Description / Specs" required><?= htmlspecialchars($p['raw_specs']) ?></textarea>
        
        <select class="edit-select" name='sector'>
            <option value='Laptops' <?= (stripos($p['category'], 'laptop') !== false ? 'selected' : '') ?>>Category: Laptops</option>
            <option value='Desktops' <?= (stripos($p['category'], 'desktop') !== false ? 'selected' : '') ?>>Category: Desktops</option>
            <option value='Servers' <?= (stripos($p['category'], 'server') !== false ? 'selected' : '') ?>>Category: Servers</option>
            <option value='Parts' <?= (stripos($p['category'], 'part') !== false ? 'selected' : '') ?>>Category: Parts</option>
        </select>

        <footer class='card-footer' style="margin-top: auto; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <div class='price edit-price-wrapper' style="flex-grow: 1; width: auto;">
                <span style="padding-left: 0.5rem; color: var(--light-text);">$</span>
                <input type='number' class="edit-input price-input" name='price' step='0.01' value='<?= htmlspecialchars($p['price']) ?>' required> 
            </div>
            <div class='price edit-price-wrapper' style="width: 100px;" title="Quantity in Stock">
                <span style="padding-left: 0.5rem; color: var(--light-text); font-size: 0.85rem; font-weight: bold;">QTY</span>
                <input type='number' class="edit-input price-input" name='quantity' min="0" step='1' value='<?= htmlspecialchars($p['quantity'] ?? 0) ?>' style="padding-left: 0.25rem; font-size: 1.1rem;" required> 
            </div>
            <button type='submit' class='buy-btn update-btn'>Save</button>
        </footer>
    </form>

    <div class="card-admin-floating-actions" style="position: absolute; top: 10px; right: 10px; display: flex; gap: 6px; z-index: 10;">
        <?php if (!empty($p['is_warehouse'])): ?>
        <form method='POST' action='admin_action.php' class="unpost-form" onsubmit='return confirm("Unpost this product from the storefront? It will remain stored in warehouse inventory.")' style="margin: 0;">
            <input type='hidden' name='action' value='unpost'>
            <input type='hidden' name='id' value='<?= htmlspecialchars($p['id']) ?>'>
            <button type='submit' class="unpost-btn" title="Unpost from Store (Keep in Warehouse)">
                Unpost
            </button>
        </form>
        <?php endif; ?>

        <form method='POST' action='admin_action.php' class="delete-form" onsubmit='return confirm("Delete this item permanently?")' style="margin: 0;">
            <input type='hidden' name='action' value='delete'>
            <input type='hidden' name='id' value='<?= htmlspecialchars($p['id']) ?>'>
            <button type='submit' class="delete-btn" title="Delete Product Permanently">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                </svg>
            </button>
        </form>
    </div>
</article>
<?php else: ?>
<article class='card'>
    <?php if ($p['last_update']): ?>
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
        <div class='price'><span>$</span><?= htmlspecialchars($p['price']) ?></div>
        <form method='POST' action='cart.php' style='margin:0;'>
            <input type='hidden' name='action' value='add'>
            <input type='hidden' name='product_id' value='<?= htmlspecialchars($p['id']) ?>'>
            <button type='submit' class='buy-btn'>Acquire</button>
        </form>
    </footer>
</article>
<?php endif; ?>
