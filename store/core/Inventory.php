<?php
// core/Inventory.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/StoreImageProcessor.php';

class Inventory {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get products, optionally filtered by category
     */
    /**
     * Get products, optionally filtered by category (only returns posted products)
     */
    public function getProducts($category = null) {
        $sql = "
            SELECT 
                i.id,
                i.sector as category,
                i.brand || ' ' || i.model as title,
                i.specs_json as description,
                i.brand,
                i.model,
                i.price,
                i.quantity,
                i.location_code,
                i.user_owner,
                i.is_posted,
                i.updated_at,
                i.created_at,
                p.optimized_path as image,
                p.thumbnail_path as thumb
            FROM inventory i
            LEFT JOIN location_photos p ON i.location_code = p.location_code
            WHERE (i.is_posted = 1 OR i.user_owner = 'STORE')
        ";

        $params = [];
        if ($category) {
            $sql .= " AND i.sector LIKE ?";
            $params[] = '%' . $category . '%';
        }

        $sql .= " GROUP BY i.id ORDER BY i.updated_at DESC, i.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->formatProducts($items);
    }

    /**
     * Get available warehouse products ready for posting to the store
     */
    public function getWarehouseAvailableProducts($sector = null, $search = null) {
        $sql = "
            SELECT 
                i.id,
                i.sector as category,
                i.brand || ' ' || i.model as title,
                i.specs_json as description,
                i.brand,
                i.model,
                i.price,
                i.quantity,
                i.location_code,
                i.user_owner,
                i.is_posted,
                i.updated_at,
                i.created_at,
                p.optimized_path as image,
                p.thumbnail_path as thumb
            FROM inventory i
            LEFT JOIN location_photos p ON i.location_code = p.location_code
            WHERE (i.is_posted = 0 OR i.is_posted IS NULL)
              AND i.quantity > 0
        ";

        $params = [];
        if (!empty($sector) && strtolower($sector) !== 'all') {
            $sql .= " AND i.sector LIKE ?";
            $params[] = '%' . $sector . '%';
        }
        if (!empty($search)) {
            $sql .= " AND (i.brand LIKE ? OR i.model LIKE ? OR i.specs_json LIKE ? OR i.location_code LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " GROUP BY i.id ORDER BY i.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->formatProducts($items);
    }

    /**
     * Get count of warehouse products available to be posted
     */
    public function getWarehouseStockCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM inventory WHERE (is_posted = 0 OR is_posted IS NULL) AND quantity > 0");
        return (int)($stmt->fetchColumn() ?: 0);
    }

    /**
     * Post a warehouse product to the store storefront with full customization options
     */
    public function postFromWarehouse($id, $data = [], $file = null) {
        $fields = ["is_posted = 1", "updated_at = CURRENT_TIMESTAMP"];
        $params = [];

        if (isset($data['price']) && $data['price'] !== '') {
            $fields[] = "price = ?";
            $params[] = (float)$data['price'];
        }
        if (isset($data['quantity']) && $data['quantity'] !== '') {
            $fields[] = "quantity = ?";
            $params[] = (int)$data['quantity'];
        }
        if (isset($data['brand']) && $data['brand'] !== '') {
            $fields[] = "brand = ?";
            $params[] = trim($data['brand']);
        }
        if (isset($data['model']) && $data['model'] !== '') {
            $fields[] = "model = ?";
            $params[] = trim($data['model']);
        }
        if (isset($data['specs_json']) && $data['specs_json'] !== '') {
            $fields[] = "specs_json = ?";
            $params[] = $data['specs_json'];
        }
        if (isset($data['sector']) && $data['sector'] !== '') {
            $fields[] = "sector = ?";
            $params[] = trim($data['sector']);
        }

        $params[] = (int)$id;
        $sql = "UPDATE inventory SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute($params);

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $stmt = $this->db->prepare("SELECT location_code, sector FROM inventory WHERE id = ?");
            $stmt->execute([(int)$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item) {
                // Ensure posted item has an isolated store location code so physical warehouse shelf photos are untouched
                $storeLoc = 'STORE-WH-' . (int)$id;
                $updateLoc = $this->db->prepare("UPDATE inventory SET location_code = ? WHERE id = ?");
                $updateLoc->execute([$storeLoc, (int)$id]);

                $this->handlePhotoUpload($storeLoc, $item['sector'], $file);
            }
        }

        return $res;
    }

    /**
     * Unpost a product from the storefront (leaves warehouse inventory intact)
     */
    public function unpostProduct($id) {
        $stmt = $this->db->prepare("UPDATE inventory SET is_posted = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Resolve image path safely with store-local preference, warehouse fallback, and placeholder
     */
    public static function resolveImagePath($path, $type = 'image') {
        if (empty($path)) {
            return 'images/placeholder.svg';
        }

        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');

        // 1. Check if file is directly in store/ directory (e.g. images/store/... or images/...)
        if (file_exists(__DIR__ . '/../' . $cleanPath)) {
            return $cleanPath;
        }

        // 2. Check if file is in store/images/store/ by basename
        $storeBase = basename($cleanPath);
        if (file_exists(__DIR__ . '/../images/store/' . $storeBase)) {
            return 'images/store/' . $storeBase;
        }

        // 3. Fallback: check marketing asset bucket for warehouse-synced photography
        if (file_exists(__DIR__ . '/../../serverWarehouse/marketing/' . $cleanPath)) {
            return '../serverWarehouse/marketing/' . $cleanPath;
        }

        // 4. Fallback: check orders location_photos
        if (file_exists(__DIR__ . '/../../serverWarehouse/orders/' . $cleanPath)) {
            return '../serverWarehouse/orders/' . $cleanPath;
        }

        // 5. Fallback placeholder
        return 'images/placeholder.svg';
    }

    /**
     * Format the raw DB results into nice array
     */
    private function formatProducts($items) {
        $products = [];
        foreach ($items as $item) {
            $imagePath = self::resolveImagePath($item['image'] ?? '');
            $thumbPath = self::resolveImagePath(!empty($item['thumb']) ? $item['thumb'] : ($item['image'] ?? ''));

            // Raw specs for editing
            $rawSpecs = $item['description'] ?: 'As-is warehouse item.';
            $displaySpecs = $rawSpecs;

            // Formatted specs for display
            $decoded = json_decode($rawSpecs, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rawSpecs = json_encode($decoded, JSON_PRETTY_PRINT);
                $nice_desc = [];
                foreach ($decoded as $k => $v) {
                    if (!empty($v) && is_string($v)) $nice_desc[] = ucfirst($k) . ": " . $v;
                }
                if (!empty($nice_desc)) {
                    $displaySpecs = implode(" | ", $nice_desc);
                }
            }
            
            $dateStr = $item['updated_at'] ?: ($item['created_at'] ?? null);
            $lastUpdate = '';
            if ($dateStr) {
                $time = strtotime($dateStr);
                $year = date('Y', $time);
                $currentYear = date('Y');
                $lastUpdate = ($year == $currentYear) ? date('n/j', $time) : date('n/j/Y', $time);
            }

            $products[$item['id']] = [
                'id' => $item['id'],
                'brand' => $item['brand'] ?? '',
                'model' => $item['model'] ?? '',
                'title' => $item['title'] ?: 'Unknown Product',
                'description' => $displaySpecs,
                'raw_specs' => $rawSpecs,
                'price' => (float)$item['price'],
                'quantity' => (int)($item['quantity'] ?? 0),
                'location_code' => $item['location_code'] ?? '',
                'user_owner' => $item['user_owner'] ?? '',
                'is_warehouse' => ($item['user_owner'] ?? '') !== 'STORE',
                'is_posted' => (int)($item['is_posted'] ?? 0),
                'image' => $imagePath,
                'thumb' => $thumbPath,
                'category' => $item['category'] ?? '',
                'last_update' => $lastUpdate
            ];
        }
        return $products;
    }

    /**
     * Add a new product (manual store item)
     */
    public function add($data, $file = null) {
        $brand = $data['brand'] ?? '';
        $model = $data['model'] ?? '';
        $specs = $data['specs_json'] ?? '';
        $sector = $data['sector'] ?? 'Laptops';
        $price = (float)($data['price'] ?? 0);
        $qty = (int)($data['quantity'] ?? 1);

        $loc = 'STORE-FRONT-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $stmt = $this->db->prepare("INSERT INTO inventory (user_owner, sector, location_code, brand, model, specs_json, quantity, price, is_posted) VALUES ('STORE', ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$sector, $loc, $brand, $model, $specs, $qty, $price]);

        $this->handlePhotoUpload($loc, $sector, $file);
    }

    /**
     * Update an existing product
     */
    public function update($id, $data, $file = null) {
        $brand = $data['brand'] ?? '';
        $model = $data['model'] ?? '';
        $specs = $data['specs_json'] ?? '';
        $sector = $data['sector'] ?? 'Laptops';
        $price = (float)($data['price'] ?? 0);
        $qty = isset($data['quantity']) ? (int)$data['quantity'] : null;

        $stmt = $this->db->prepare("SELECT location_code FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
        $loc = $stmt->fetchColumn();
        
        if (!$loc) {
            $loc = 'STORE-FRONT-' . strtoupper(substr(md5(uniqid()), 0, 6));
        }

        if ($qty !== null) {
            $stmt = $this->db->prepare("UPDATE inventory SET sector = ?, location_code = ?, brand = ?, model = ?, specs_json = ?, price = ?, quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$sector, $loc, $brand, $model, $specs, $price, $qty, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE inventory SET sector = ?, location_code = ?, brand = ?, model = ?, specs_json = ?, price = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$sector, $loc, $brand, $model, $specs, $price, $id]);
        }

        $this->handlePhotoUpload($loc, $sector, $file);
    }

    /**
     * Deduct quantity after purchase
     */
    public function reduceQuantity($id, $amount) {
        $stmt = $this->db->prepare("UPDATE inventory SET quantity = MAX(0, quantity - ?) WHERE id = ?");
        $stmt->execute([(int)$amount, $id]);
    }

    /**
     * Delete a product
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Handle the photo upload logic using StoreImageProcessor in store/images/store/
     */
    private function handlePhotoUpload($loc, $sector, $file) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $result = StoreImageProcessor::processUpload($file);
            if (!empty($result['success'])) {
                $rawPath = $result['raw'];
                $optPath = $result['opt'];
                $thumbPath = $result['thumb'];

                $stmt = $this->db->prepare("SELECT id FROM location_photos WHERE location_code = ?");
                $stmt->execute([$loc]);
                $photo_id = $stmt->fetchColumn();

                if ($photo_id) {
                    $stmt = $this->db->prepare("UPDATE location_photos SET original_filename = ?, optimized_path = ?, thumbnail_path = ?, archive_path = ?, archive_driver = 'store_local', uploaded_by = 'STORE', sector = ? WHERE id = ?");
                    $stmt->execute([$file['name'], $optPath, $thumbPath, $rawPath, $sector, $photo_id]);
                } else {
                    $stmt = $this->db->prepare("INSERT INTO location_photos (location_code, original_filename, archive_driver, archive_path, optimized_path, thumbnail_path, uploaded_by, sector) VALUES (?, ?, 'store_local', ?, ?, ?, 'STORE', ?)");
                    $stmt->execute([$loc, $file['name'], $rawPath, $optPath, $thumbPath, $sector]);
                }
            }
        }
    }
}
