<?php
/**
 * NEXUS-7 Store UI Component Library
 * Modular components mirroring the serverWarehouse architecture.
 */

class UI {
    /**
     * Renders a premium futuristic product card
     */
    public static function product_card($id, $image, $title, $description, $price, $color = 'blue', $category = '') {
        $priceStyle = '';
        $btnClass = '';
        $btnStyle = '';
        
        $is_auth = (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true);
        $preview = $_SESSION['live_preview'] ?? false;

        if ($is_auth && !$preview) {
            $parts = explode(' ', $title, 2);
            $brand = $parts[0] ?? '';
            $model = $parts[1] ?? '';
            
            // Format description for the editor
            $edit_desc = $description;
            $decoded = json_decode($description, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $edit_desc = json_encode($decoded, JSON_PRETTY_PRINT);
            }

            return "
            <div class='card' style='border: 2px solid var(--primary-color); position:relative;'>
                <form method='POST' action='admin.php' enctype='multipart/form-data' style='margin:0; display:flex; flex-direction:column; height:100%;'>
                    <input type='hidden' name='action' value='edit'>
                    <input type='hidden' name='id' value='" . htmlspecialchars($id) . "'>
                    
                    <div class='img-wrapper' style='position:relative;'>
                        <img src='" . htmlspecialchars($image) . "' class='card-img' loading='lazy' onerror=\"this.onerror=null; this.src='images/placeholder.svg';\">
                        <label style='position:absolute; bottom:5px; left:50%; transform:translateX(-50%); background:var(--primary-color); color:#fff; padding:2px 10px; border-radius:12px; font-size:0.8rem; cursor:pointer; opacity:0.8; transition:opacity 0.2s;' onmouseover='this.style.opacity=1' onmouseout='this.style.opacity=0.8'>
                            Change Photo
                            <input type='file' name='photo' accept='image/*' style='display:none;'>
                        </label>
                    </div>

                    <div style='display:flex; gap:0.25rem; margin-top:1rem; margin-bottom:0.5rem;'>
                        <input type='text' name='brand' value='" . htmlspecialchars($brand) . "' style='width:50%; background:transparent; border:1px dashed transparent; color:var(--primary-dark); font-size:1.4rem; font-weight:700; padding:0; outline:none; transition:border 0.2s;' onfocus=\"this.style.border='1px dashed var(--primary-color)'\" onblur=\"this.style.border='1px dashed transparent'\">
                        <input type='text' name='model' value='" . htmlspecialchars($model) . "' style='width:50%; background:transparent; border:1px dashed transparent; color:var(--primary-dark); font-size:1.4rem; font-weight:700; padding:0; outline:none; transition:border 0.2s;' onfocus=\"this.style.border='1px dashed var(--primary-color)'\" onblur=\"this.style.border='1px dashed transparent'\">
                    </div>

                    <textarea name='specs_json' style='width:100%; background:transparent; border:1px dashed transparent; color:var(--text-color); font-size:0.95rem; line-height:1.6; padding:0; outline:none; flex-grow:1; margin-bottom:1rem; transition:border 0.2s;' rows='4' onfocus=\"this.style.border='1px dashed var(--primary-color)'\" onblur=\"this.style.border='1px dashed transparent'\">" . htmlspecialchars($edit_desc) . "</textarea>
                    
                    <select name='sector' style='width:100%; background:#fff; border:1px solid rgba(0,0,0,0.1); color:var(--text-color); padding:0.5rem; border-radius:4px; margin-bottom:1rem; outline:none;'>
                        <option value='Laptops' " . (stripos($category, 'laptop') !== false ? 'selected' : '') . ">Category: Laptops</option>
                        <option value='Desktops' " . (stripos($category, 'desktop') !== false ? 'selected' : '') . ">Category: Desktops</option>
                        <option value='Servers' " . (stripos($category, 'server') !== false ? 'selected' : '') . ">Category: Servers</option>
                        <option value='Parts' " . (stripos($category, 'part') !== false ? 'selected' : '') . ">Category: Parts</option>
                    </select>

                    <div class='card-footer'>
                        <div class='price' {$priceStyle}>
                            <span>$</span>
                            <input type='number' name='price' step='0.01' value='" . htmlspecialchars($price) . "' style='width:100px; background:transparent; border:none; border-bottom:1px dashed var(--primary-color); color:var(--primary-color); font-family:inherit; font-weight:bold; font-size:inherit; outline:none; text-align:left;'> 
                        </div>
                        <button type='submit' class='buy-btn {$btnClass}' {$btnStyle}>Update</button>
                    </div>
                </form>

                <form method='POST' action='admin.php' style='position:absolute; top:10px; right:10px;' onsubmit='return confirm(\"Delete this item permanently?\")'>
                    <input type='hidden' name='action' value='delete'>
                    <input type='hidden' name='id' value='" . htmlspecialchars($id) . "'>
                    <button type='submit' style='background:rgba(255,0,60,0.8); color:#fff; border:none; width:30px; height:30px; border-radius:50%; cursor:pointer; font-weight:bold; box-shadow:0 0 10px rgba(255,0,60,0.5);'>X</button>
                </form>
            </div>";
        }

        return "
        <div class='card'>
            <div class='img-wrapper'>
                <picture>
                    <source media='(max-width: 768px)' srcset='images/thumb_" . basename($image) . "'>
                    <img src='" . htmlspecialchars($image) . "' alt='" . htmlspecialchars($title) . "' class='card-img' width='600' height='600' loading='lazy' onerror=\"this.onerror=null; this.src='images/placeholder.svg';\">
                </picture>
            </div>
            <h2 class='card-title'>" . htmlspecialchars($title) . "</h2>
            <p class='card-desc'>" . htmlspecialchars($description) . "</p>
            <div class='card-footer'>
                <div class='price' {$priceStyle}><span>$</span>" . htmlspecialchars($price) . "</div>
                <form method='POST' action='cart.php' style='margin:0;'>
                    <input type='hidden' name='action' value='add'>
                    <input type='hidden' name='product_id' value='" . htmlspecialchars($id) . "'>
                    <button type='submit' class='buy-btn {$btnClass}' {$btnStyle}>Acquire</button>
                </form>
            </div>
        </div>";
    }

    /**
     * Renders the site header navigation
     */
    public static function header_nav($cartCount = 0) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle live preview toggle
        if (isset($_GET['preview'])) {
            $_SESSION['live_preview'] = ($_GET['preview'] == '1');
            header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
            exit;
        }

        $is_auth = (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true);
        $preview = $_SESSION['live_preview'] ?? false;
        
        $adminLinks = "";
        if ($is_auth) {
            if ($preview) {
                $adminLinks = "<a href='?preview=0' style='color:var(--secondary-color);'>[ Exit Preview ]</a>";
            } else {
                $adminLinks = "<a href='?preview=1' style='color:var(--primary-color);'>[ Live Preview ]</a>";
            }
        }

        return "
        <script>
            // Instant theme load to prevent flash
            (function() {
                var currentTheme = localStorage.getItem('theme') || 'light';
                document.documentElement.setAttribute('data-theme', currentTheme);
            })();
            function toggleTheme() {
                var current = document.documentElement.getAttribute('data-theme');
                var next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
            }
        </script>
        <header>
            <a href='index.php' style='text-decoration:none;'><div class='logo'>LAtinosPC<span><hr style=\" display:block;\">Used Laptops</span></div></a>
            <nav class='nav-links'>
                $adminLinks
                <a href='category.php?cat=computers'>Computers</a>
                <a href='category.php?cat=servers'>Servers</a>
                <a href='category.php?cat=parts'>Parts</a>
                <a href='cart.php'>Cart ({$cartCount})</a>
                <a href='javascript:void(0);' onclick='toggleTheme()' style='color:var(--primary-color);'>◑</a>
            </nav>
        </header>";
    }
}