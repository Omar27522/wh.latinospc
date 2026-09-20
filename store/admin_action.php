<?php
// admin_action.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/Inventory.php';

// Authenticate via warehouse user accounts
$is_auth = (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true);

if (isset($_GET['logout'])) {
    unset($_SESSION['authenticated']);
    header('Location: ../serverWarehouse/orders/core/login.php');
    exit;
}

if (!$is_auth) {
    header('Location: ../serverWarehouse/orders/core/login.php?return_url=/store/');
    exit;
}

$inventory = new Inventory($db);

// Handle AJAX get available warehouse items
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_warehouse_items') {
    header('Content-Type: application/json');
    $sector = $_GET['sector'] ?? null;
    $search = $_GET['search'] ?? null;
    $items = $inventory->getWarehouseAvailableProducts($sector, $search);
    echo json_encode(['success' => true, 'items' => array_values($items)]);
    exit;
}

// Handle CRUD and Warehouse Posting operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    $file = isset($_FILES['photo']) ? $_FILES['photo'] : null;

    if ($action === 'add') {
        $inventory->add($_POST, $file);
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $inventory->update($id, $_POST, $file);
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $inventory->delete($id);
    } elseif ($action === 'post_warehouse') {
        $id = (int)$_POST['id'];
        $inventory->postFromWarehouse($id, $_POST, $file);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Product posted to store!']);
            exit;
        }
    } elseif ($action === 'unpost') {
        $id = (int)$_POST['id'];
        $inventory->unpostProduct($id);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Product unposted from store.']);
            exit;
        }
    }
    
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header('Location: ' . $referer);
    exit;
}

// If accessed directly via GET, redirect to store
header('Location: index.php');
exit;
