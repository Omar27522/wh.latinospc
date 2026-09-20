<?php
// views/add_item.php
$is_auth = (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true);
$preview = $_SESSION['live_preview'] ?? false;
$activeCategory = $activeCategory ?? ''; // Passed from controller

if ($is_auth && !$preview):
?>
<div class='card' style='border: 2px dashed var(--primary-color); display:flex; flex-direction:column; justify-content:center; align-items:center; background:var(--bannerAndFooter-bg);'>
    <form method='POST' action='admin_action.php' enctype='multipart/form-data' style='width:100%; height:100%; display:flex; flex-direction:column; padding:1rem;'>
        <input type='hidden' name='action' value='add'>
        <button type="button" onclick="openWarehouseModal()" class="wh-quick-open-btn" style="width:100%; margin-bottom: 0.85rem; padding: 0.65rem; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff; border: none; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,102,255,0.2); transition: transform 0.2s;">
            <span>⚡ Post from Warehouse Stock</span>
        </button>
        <div style="display: flex; align-items: center; width: 100%; margin-bottom: 0.75rem;">
            <div style="flex-grow: 1; height: 1px; background: rgba(0,0,0,0.1);"></div>
            <span style="padding: 0 0.5rem; font-size: 0.72rem; color: var(--light-text); text-transform: uppercase; font-weight: 600;">or manual custom item</span>
            <div style="flex-grow: 1; height: 1px; background: rgba(0,0,0,0.1);"></div>
        </div>
        <input type='text' name='brand' placeholder='Brand' style='margin-bottom:0.5rem; background:#fff; border:1px solid rgba(0,0,0,0.1); color:var(--text-color); padding:0.5rem; border-radius:4px;' required>
        <input type='text' name='model' placeholder='Model' style='margin-bottom:0.5rem; background:#fff; border:1px solid rgba(0,0,0,0.1); color:var(--text-color); padding:0.5rem; border-radius:4px;' required>
        <textarea name='specs_json' placeholder='Specs / Description' style='flex-grow:1; margin-bottom:0.5rem; background:#fff; border:1px solid rgba(0,0,0,0.1); color:var(--text-color); padding:0.5rem; border-radius:4px;' required></textarea>
        <select name='sector' style='margin-bottom:0.5rem; background:#fff; border:1px solid rgba(0,0,0,0.1); color:var(--text-color); padding:0.5rem; border-radius:4px;'>
            <option value='Laptops' <?= (stripos($activeCategory, 'computer') !== false ? 'selected' : '') ?>>Laptops</option>
            <option value='Desktops' <?= (stripos($activeCategory, 'computer') !== false ? 'selected' : '') ?>>Desktops</option>
            <option value='Servers' <?= (stripos($activeCategory, 'server') !== false ? 'selected' : '') ?>>Servers</option>
            <option value='Parts' <?= (stripos($activeCategory, 'part') !== false ? 'selected' : '') ?>>Parts</option>
        </select>
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <input type='number' name='price' step='0.01' placeholder='Price' style='flex-grow: 1; background:#fff; border:1px solid rgba(0,0,0,0.1); color:var(--primary-color); font-weight:bold; padding:0.5rem; border-radius:4px;' required>
            <input type='number' name='quantity' min='0' step='1' placeholder='QTY' value='1' title='Initial Quantity' style='width: 80px; background:#fff; border:1px solid rgba(0,0,0,0.1); color:var(--text-color); font-weight:bold; padding:0.5rem; border-radius:4px;' required>
        </div>
        <input type='file' name='photo' accept='image/*' style='margin-bottom:0.5rem; color:var(--text-color); font-family:Inter;' onchange='previewAddPhoto(this)'>
        <div id='add-photo-preview' style='margin-bottom:0.5rem;'></div>
        <button type='submit' class='buy-btn'>+ CREATE</button>
    </form>
    <script>
    function previewAddPhoto(input) {
        const preview = document.getElementById('add-photo-preview');
        if (!preview) return;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px; padding: 4px 8px; background: rgba(0, 102, 255, 0.08); border: 1px dashed var(--primary-color); border-radius: 4px;">
                        <img src="${e.target.result}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 4px;" alt="Preview">
                        <span style="font-size: 0.72rem; color: var(--text-color); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${file.name}</span>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    }
    </script>
</div>
<?php endif; ?>
