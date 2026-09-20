<?php
// views/warehouse_modal.php
// Only rendered for authenticated administrators
if (!isset($inventory)) {
    require_once __DIR__ . '/../core/Inventory.php';
    require_once __DIR__ . '/../core/db.php';
    $inventory = new Inventory($db);
}

$whStockCount = $inventory->getWarehouseStockCount();
// Fetch initial batch of 30 items for instant display
$initialItems = $inventory->getWarehouseAvailableProducts(null, null);
$initialBatch = array_slice($initialItems, 0, 40);
?>
<!-- Warehouse Inventory Drawer / Modal -->
<div id="warehouseModal" class="wh-modal" style="display: none;">
    <div class="wh-modal-backdrop" onclick="closeWarehouseModal()"></div>
    <div class="wh-modal-dialog">
        <div class="wh-modal-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--primary-dark); margin: 0;">
                    📦 Warehouse Inventory
                </h2>
                <span class="wh-counter-badge" id="whModalBadge"><?= $whStockCount ?> Units Available</span>
            </div>
            <button type="button" class="wh-close-btn" onclick="closeWarehouseModal()" aria-label="Close">&times;</button>
        </div>

        <div class="wh-modal-subbar">
            <p style="font-size: 0.85rem; color: var(--light-text); margin: 0 0 0.75rem 0;">
                Real warehouse inventory loaded from <strong>warehouse.db</strong>. You have full control: adjust the retail price, quantity, description, or title before posting to the public catalog.
            </p>
            
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <div class="wh-search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="opacity: 0.6;">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                    <input type="text" id="whSearchInput" placeholder="Search 1,200+ models, brands, specs, or shelf..." oninput="debounceWhSearch()">
                </div>
                
                <div class="wh-sector-pills" id="whSectorPills">
                    <button type="button" class="wh-pill active" onclick="setWhSector('all', this)">All</button>
                    <button type="button" class="wh-pill" onclick="setWhSector('laptops', this)">Laptops</button>
                    <button type="button" class="wh-pill" onclick="setWhSector('desktops', this)">Desktops</button>
                    <button type="button" class="wh-pill" onclick="setWhSector('gaming', this)">Gaming</button>
                    <button type="button" class="wh-pill" onclick="setWhSector('servers', this)">Servers</button>
                    <button type="button" class="wh-pill" onclick="setWhSector('parts', this)">Parts</button>
                </div>
            </div>
        </div>

        <div class="wh-modal-body" id="whItemsContainer">
            <div class="wh-grid" id="whGrid">
                <?php foreach ($initialBatch as $item): ?>
                    <?= renderWarehouseCardHtml($item) ?>
                <?php endforeach; ?>
            </div>
            
            <div class="wh-empty-state" id="whEmptyMsg" style="<?= empty($initialBatch) ? 'display:block;' : 'display:none;' ?>">
                <p style="font-size: 1.1rem; font-weight: 600;">No warehouse items found.</p>
                <p style="font-size: 0.85rem; color: var(--light-text);">Try adjusting your search query or category filter.</p>
            </div>
        </div>
    </div>
</div>

<?php
function renderWarehouseCardHtml($item) {
    $id = htmlspecialchars($item['id']);
    $title = htmlspecialchars($item['title']);
    $brand = htmlspecialchars($item['brand']);
    $model = htmlspecialchars($item['model']);
    $category = htmlspecialchars($item['category']);
    $shelf = htmlspecialchars($item['location_code'] ?: 'SHELF');
    $qty = (int)$item['quantity'];
    $price = htmlspecialchars($item['price']);
    $thumb = htmlspecialchars($item['thumb']);
    $specs = htmlspecialchars($item['description']);
    $rawSpecs = htmlspecialchars($item['raw_specs']);

    ob_start();
    ?>
    <div class="wh-card" id="wh-card-<?= $id ?>">
        <div class="wh-card-top">
            <img src="<?= $thumb ?>" class="wh-card-img" onerror="this.onerror=null; this.src='images/placeholder.svg';" alt="<?= $title ?>">
            <div class="wh-card-meta">
                <div style="display: flex; gap: 0.35rem; align-items: center; margin-bottom: 4px; flex-wrap: wrap;">
                    <span class="wh-loc-badge" title="Warehouse Physical Shelf">📍 <?= $shelf ?></span>
                    <span class="wh-sec-badge"><?= $category ?></span>
                    <span class="wh-qty-badge">Stock: <?= $qty ?></span>
                </div>
                <h4 class="wh-card-title"><?= $title ?></h4>
                <p class="wh-card-specs"><?= $specs ?></p>
            </div>
        </div>

        <!-- Quick Post & Customize Row -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-top: 4px;">
            <button type="button" class="wh-customize-toggle-btn" onclick="toggleWhCustomize(<?= $id ?>)">
                ⚙️ Customize Details
            </button>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.8rem; color: var(--light-text); font-weight: 600;">$</span>
                <input type="number" id="quick-price-<?= $id ?>" step="0.01" value="<?= $price ?>" title="Store Selling Price" style="width: 80px; padding: 0.3rem; border: 1px solid rgba(0,0,0,0.15); border-radius: 4px; font-weight: bold; font-family: inherit; color: var(--primary-color);">
                <button type="button" class="wh-post-btn" id="btn-quick-post-<?= $id ?>" onclick="submitQuickPost(<?= $id ?>)" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">
                    🚀 Quick Post
                </button>
            </div>
        </div>

        <!-- Expandable Full Decision / Customization Drawer -->
        <form method="POST" action="admin_action.php" enctype="multipart/form-data" id="wh-config-form-<?= $id ?>" class="wh-config-form" style="display: none;" onsubmit="handleWarehouseFullPost(event, <?= $id ?>)">
            <input type="hidden" name="action" value="post_warehouse">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="wh-config-inner">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--primary-color); margin-bottom: 0.5rem;">
                    Decide Storefront Attributes
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <label class="wh-field-label">Brand</label>
                        <input type="text" name="brand" value="<?= $brand ?>" class="wh-field-input" required>
                    </div>
                    <div style="flex: 1;">
                        <label class="wh-field-label">Model</label>
                        <input type="text" name="model" value="<?= $model ?>" class="wh-field-input" required>
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <label class="wh-field-label">Store Retail Price ($)</label>
                        <input type="number" name="price" step="0.01" value="<?= $price ?>" class="wh-field-input" style="color: var(--primary-color); font-weight: bold;" required>
                    </div>
                    <div style="flex: 1;">
                        <label class="wh-field-label">Units to Post (Max <?= $qty ?>)</label>
                        <input type="number" name="quantity" min="1" max="<?= $qty ?>" value="<?= $qty ?>" class="wh-field-input" required>
                    </div>
                    <div style="flex: 1;">
                        <label class="wh-field-label">Category</label>
                        <select name="sector" class="wh-field-input">
                            <option value="Laptops" <?= (stripos($category, 'laptop') !== false ? 'selected' : '') ?>>Laptops</option>
                            <option value="Desktops" <?= (stripos($category, 'desktop') !== false ? 'selected' : '') ?>>Desktops</option>
                            <option value="Gaming" <?= (stripos($category, 'gaming') !== false ? 'selected' : '') ?>>Gaming</option>
                            <option value="Servers" <?= (stripos($category, 'server') !== false ? 'selected' : '') ?>>Servers</option>
                            <option value="Parts" <?= (stripos($category, 'part') !== false ? 'selected' : '') ?>>Parts</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 0.5rem;">
                    <label class="wh-field-label">Public Description & Tested Specs</label>
                    <textarea name="specs_json" rows="3" class="wh-field-input" style="resize: vertical;"><?= $rawSpecs ?></textarea>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <label class="wh-field-label">Upload Custom Store Photo (Optional)</label>
                    <input type="file" name="photo" accept="image/*" style="font-size: 0.75rem;" onchange="previewModalCustomPhoto(this, '<?= $id ?>')">
                    <div id="wh-photo-preview-<?= $id ?>"></div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="wh-btn-secondary" onclick="toggleWhCustomize(<?= $id ?>)">Cancel</button>
                    <button type="submit" class="wh-post-btn" id="btn-custom-post-<?= $id ?>">
                        ✓ Publish Custom Listing
                    </button>
                </div>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
?>

<script>
let currentWhSector = 'all';
let whSearchTimer = null;

function openWarehouseModal() {
    const modal = document.getElementById('warehouseModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeWarehouseModal(shouldReload = false) {
    const modal = document.getElementById('warehouseModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    if (shouldReload) {
        window.location.reload();
    }
}

function setWhSector(sector, btn) {
    currentWhSector = sector.toLowerCase();
    const pills = document.querySelectorAll('#whSectorPills .wh-pill');
    pills.forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    triggerWhFetch();
}

function debounceWhSearch() {
    clearTimeout(whSearchTimer);
    whSearchTimer = setTimeout(triggerWhFetch, 300);
}

async function triggerWhFetch() {
    const searchVal = (document.getElementById('whSearchInput').value || '').trim();
    const container = document.getElementById('whGrid');
    const emptyMsg = document.getElementById('whEmptyMsg');

    container.innerHTML = '<div style="text-align:center; padding: 2rem; color: var(--light-text); font-size: 0.9rem;">⏳ Loading warehouse inventory...</div>';
    if (emptyMsg) emptyMsg.style.display = 'none';

    try {
        const url = `admin_action.php?action=get_warehouse_items&sector=${encodeURIComponent(currentWhSector)}&search=${encodeURIComponent(searchVal)}`;
        const res = await fetch(url);
        const data = await res.json();

        if (data.success && data.items.length > 0) {
            container.innerHTML = '';
            data.items.slice(0, 50).forEach(item => {
                container.innerHTML += createClientCardHtml(item);
            });
            if (emptyMsg) emptyMsg.style.display = 'none';
        } else {
            container.innerHTML = '';
            if (emptyMsg) emptyMsg.style.display = 'block';
        }
    } catch (e) {
        container.innerHTML = '<div style="text-align:center; padding: 2rem; color: #ff3b30;">Failed to load items.</div>';
    }
}

function createClientCardHtml(item) {
    const id = item.id;
    const title = escapeHtml(item.title);
    const brand = escapeHtml(item.brand || '');
    const model = escapeHtml(item.model || '');
    const category = escapeHtml(item.category || '');
    const shelf = escapeHtml(item.location_code || 'SHELF');
    const qty = parseInt(item.quantity || 0);
    const price = parseFloat(item.price || 0).toFixed(2);
    const thumb = escapeHtml(item.thumb || 'images/placeholder.svg');
    const specs = escapeHtml(item.description || '');
    const rawSpecs = escapeHtml(item.raw_specs || '');

    return `
    <div class="wh-card" id="wh-card-${id}">
        <div class="wh-card-top">
            <img src="${thumb}" class="wh-card-img" onerror="this.onerror=null; this.src='images/placeholder.svg';" alt="${title}">
            <div class="wh-card-meta">
                <div style="display: flex; gap: 0.35rem; align-items: center; margin-bottom: 4px; flex-wrap: wrap;">
                    <span class="wh-loc-badge" title="Warehouse Physical Shelf">📍 ${shelf}</span>
                    <span class="wh-sec-badge">${category}</span>
                    <span class="wh-qty-badge">Stock: ${qty}</span>
                </div>
                <h4 class="wh-card-title">${title}</h4>
                <p class="wh-card-specs">${specs}</p>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-top: 4px;">
            <button type="button" class="wh-customize-toggle-btn" onclick="toggleWhCustomize(${id})">
                ⚙️ Customize Details
            </button>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.8rem; color: var(--light-text); font-weight: 600;">$</span>
                <input type="number" id="quick-price-${id}" step="0.01" value="${price}" title="Store Selling Price" style="width: 80px; padding: 0.3rem; border: 1px solid rgba(0,0,0,0.15); border-radius: 4px; font-weight: bold; font-family: inherit; color: var(--primary-color);">
                <button type="button" class="wh-post-btn" id="btn-quick-post-${id}" onclick="submitQuickPost(${id})" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">
                    🚀 Quick Post
                </button>
            </div>
        </div>

        <form method="POST" action="admin_action.php" enctype="multipart/form-data" id="wh-config-form-${id}" class="wh-config-form" style="display: none;" onsubmit="handleWarehouseFullPost(event, ${id})">
            <input type="hidden" name="action" value="post_warehouse">
            <input type="hidden" name="id" value="${id}">

            <div class="wh-config-inner">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--primary-color); margin-bottom: 0.5rem;">
                    Decide Storefront Attributes
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <label class="wh-field-label">Brand</label>
                        <input type="text" name="brand" value="${brand}" class="wh-field-input" required>
                    </div>
                    <div style="flex: 1;">
                        <label class="wh-field-label">Model</label>
                        <input type="text" name="model" value="${model}" class="wh-field-input" required>
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <label class="wh-field-label">Store Retail Price ($)</label>
                        <input type="number" name="price" step="0.01" value="${price}" class="wh-field-input" style="color: var(--primary-color); font-weight: bold;" required>
                    </div>
                    <div style="flex: 1;">
                        <label class="wh-field-label">Units to Post (Max ${qty})</label>
                        <input type="number" name="quantity" min="1" max="${qty}" value="${qty}" class="wh-field-input" required>
                    </div>
                    <div style="flex: 1;">
                        <label class="wh-field-label">Category</label>
                        <select name="sector" class="wh-field-input">
                            <option value="Laptops" ${category.toLowerCase().includes('laptop') ? 'selected' : ''}>Laptops</option>
                            <option value="Desktops" ${category.toLowerCase().includes('desktop') ? 'selected' : ''}>Desktops</option>
                            <option value="Gaming" ${category.toLowerCase().includes('gaming') ? 'selected' : ''}>Gaming</option>
                            <option value="Servers" ${category.toLowerCase().includes('server') ? 'selected' : ''}>Servers</option>
                            <option value="Parts" ${category.toLowerCase().includes('part') ? 'selected' : ''}>Parts</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 0.5rem;">
                    <label class="wh-field-label">Public Description & Tested Specs</label>
                    <textarea name="specs_json" rows="3" class="wh-field-input" style="resize: vertical;">${rawSpecs}</textarea>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <label class="wh-field-label">Upload Custom Store Photo (Optional)</label>
                    <input type="file" name="photo" accept="image/*" style="font-size: 0.75rem;" onchange="previewModalCustomPhoto(this, '${id}')">
                    <div id="wh-photo-preview-${id}"></div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="wh-btn-secondary" onclick="toggleWhCustomize(${id})">Cancel</button>
                    <button type="submit" class="wh-post-btn" id="btn-custom-post-${id}">
                        ✓ Publish Custom Listing
                    </button>
                </div>
            </div>
        </form>
    </div>`;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function previewModalCustomPhoto(input, id) {
    const preview = document.getElementById('wh-photo-preview-' + id);
    if (!preview) return;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px; padding: 6px 10px; background: rgba(16, 185, 129, 0.08); border: 1px dashed #10b981; border-radius: 6px;">
                    <img src="${e.target.result}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" alt="Preview">
                    <div style="font-size: 0.75rem; color: var(--text-color); overflow: hidden;">
                        <strong style="color: #10b981;">✓ Custom Photo Ready</strong><br>
                        <span style="opacity: 0.8; font-size: 0.7rem; text-overflow: ellipsis; white-space: nowrap; display: block;">${file.name} (${Math.round(file.size/1024)} KB)</span>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}

function toggleWhCustomize(id) {
    const form = document.getElementById('wh-config-form-' + id);
    if (form) {
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }
}

async function submitQuickPost(id) {
    const btn = document.getElementById('btn-quick-post-' + id);
    const priceInput = document.getElementById('quick-price-' + id);
    const card = document.getElementById('wh-card-' + id);
    const price = priceInput ? priceInput.value : '';

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Posting...';
    }

    const formData = new FormData();
    formData.append('action', 'post_warehouse');
    formData.append('id', id);
    formData.append('price', price);

    sendWhPostRequest(formData, btn, card, '🚀 Quick Post');
}

async function handleWarehouseFullPost(e, id) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btn-custom-post-' + id);
    const card = document.getElementById('wh-card-' + id);

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Publishing...';
    }

    const formData = new FormData(form);
    sendWhPostRequest(formData, btn, card, '✓ Publish Custom Listing');
}

async function sendWhPostRequest(formData, btn, card, origText) {
    try {
        const response = await fetch('admin_action.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();
        if (result.success) {
            if (btn) {
                btn.style.background = '#10b981';
                btn.innerHTML = '✓ Live!';
            }
            if (card) {
                card.style.transition = 'all 0.4s ease';
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0.3';
                setTimeout(() => {
                    card.remove();
                    window.location.reload();
                }, 400);
            }
        } else {
            alert('Failed to post: ' + (result.message || 'Unknown error'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        }
    } catch (err) {
        alert('Network error posting product.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    }
}
</script>
