<?php
// views/header.php
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
$cartCount = isset($cart) ? $cart->getCount() : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NEXUS-7 Advanced Tech Store.">
    <title><?= $pageTitle ?? 'Warehouse Inventory' ?> | NEXUS-7</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/store.css">
    <link rel="stylesheet" href="assets/css/components.css">
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

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var card = input.closest('.card');
                if (card) {
                    var img = card.querySelector('.card-img');
                    if (img) img.src = e.target.result;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</head>

<body>
    <header>
        <a href='index.php' style='text-decoration:none;'>
            <div class='logo'>NEXUS<span>-7</span></div>
        </a>
        <nav class='nav-links'>
            <?php if ($is_auth): ?>
            <?php if ($preview): ?>
            <a href='?preview=0' style='color:var(--secondary-color);'>[ Exit Preview ]</a>
            <?php else: ?>
            <button type='button' class='nav-wh-btn' onclick='openWarehouseModal()' title='Browse and post items from warehouse inventory'>
                📦 Warehouse Stock <span class='wh-nav-badge' id='whNavBadge'><?= isset($inventory) ? $inventory->getWarehouseStockCount() : '' ?></span>
            </button>
            <a href='?preview=1' style='color:var(--primary-color);align:left;display:box;' title='Customer Live Preview'>[LV]</a>
            <?php endif; ?>
            <?php endif; ?>
            <a href='javascript:void(0);' onclick='toggleTheme()' style='color:var(--primary-color);'>◑</a>
            <a href='category.php?cat=desktops'>Desktops</a>
            <a href='category.php?cat=laptops'>Laptops</a>
            <a href='category.php?cat=servers'>Servers</a>
            <a href='category.php?cat=parts'>Parts</a>
            <a href='cart.php'>Cart (<?= $cartCount ?>)</a>
        </nav>
    </header>
    <?php if ($is_auth && !$preview) { require __DIR__ . '/warehouse_modal.php'; } ?>