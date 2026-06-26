<?php
/**
 * Ahost One - Marketplace Pro Module v2
 * ZIP Upload | License | Updates | Commission
 */

namespace Ahost\Modules\Marketplace;

class MarketplaceModule {
    private $commission_rate = 0.15;
    
    public function install() {
        $db = db();
        
        $db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_products` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `seller_id` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) UNIQUE,
                `description` TEXT,
                `short_description` VARCHAR(500),
                `price` DECIMAL(10,2) DEFAULT 0,
                `currency` VARCHAR(10) DEFAULT 'TRY',
                `category` VARCHAR(100),
                `type` ENUM('script','template','plugin','module','other') DEFAULT 'script',
                `file_path` VARCHAR(500),
                `demo_url` VARCHAR(500),
                `version` VARCHAR(20) DEFAULT '1.0.0',
                `license_type` ENUM('single','multi','extended') DEFAULT 'single',
                `license_domains` INT DEFAULT 1,
                `support_months` INT DEFAULT 6,
                `is_active` TINYINT(1) DEFAULT 1,
                `is_featured` TINYINT(1) DEFAULT 0,
                `downloads` INT DEFAULT 0,
                `rating` DECIMAL(3,2) DEFAULT 0,
                `tags` JSON,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_licenses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `buyer_id` INT NOT NULL,
                `license_key` VARCHAR(64) UNIQUE NOT NULL,
                `domain` VARCHAR(255),
                `authorized_domains` JSON,
                `valid_until` DATE,
                `is_active` TINYINT(1) DEFAULT 1,
                `purchase_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY idx_key (license_key),
                KEY idx_domain (domain)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_orders` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `seller_id` INT NOT NULL,
                `buyer_id` INT NOT NULL,
                `amount` DECIMAL(10,2) NOT NULL,
                `commission` DECIMAL(10,2) NOT NULL,
                `seller_earnings` DECIMAL(10,2) NOT NULL,
                `currency` VARCHAR(10) DEFAULT 'TRY',
                `status` ENUM('pending','completed','refunded') DEFAULT 'pending',
                `transaction_id` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_updates` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `version` VARCHAR(20) NOT NULL,
                `changelog` TEXT,
                `file_path` VARCHAR(500),
                `is_critical` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_product (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        return true;
    }
    
    /**
     * ZIP Yükleme
     */
    public function uploadProduct($seller_id, $data, $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Dosya yüklenemedi'];
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return ['error' => 'Sadece ZIP dosyası kabul edilir'];
        }
        
        if ($file['size'] > 100 * 1024 * 1024) {
            return ['error' => "Dosya 100MB'ı aşamaz"];
        }
        
        // Validate ZIP
        $zip = new \ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            return ['error' => 'Geçersiz ZIP dosyası'];
        }
        $zip->close();
        
        // Save file
        $upload_dir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../../storage/marketplace';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $filename = uniqid('product_') . '.zip';
        $filepath = $upload_dir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['error' => 'Dosya kaydedilemedi'];
        }
        
        // Insert to DB
        $db = db();
        $slug = $this->makeSlug($data['name']);
        
        $stmt = $db->prepare("
            INSERT INTO marketplace_products 
            (seller_id, name, slug, description, short_description, price, category, type, file_path, version)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $seller_id, $data['name'], $slug, $data['description'] ?? '',
            $data['short_description'] ?? '', $data['price'] ?? 0, $data['category'] ?? 'other',
            $data['type'] ?? 'script', $filepath, $data['version'] ?? '1.0.0'
        ]);
        
        return ['success' => true, 'product_id' => $db->lastInsertId(), 'slug' => $slug];
    }
    
    /**
     * Lisans Doğrulama
     */
    public function validateLicense($license_key, $domain = null) {
        $db = db();
        
        $stmt = $db->prepare("
            SELECT ml.*, mp.name as product_name, mp.license_type, mp.license_domains
            FROM marketplace_licenses ml
            JOIN marketplace_products mp ON mp.id = ml.product_id
            WHERE ml.license_key = ?
        ");
        $stmt->execute([$license_key]);
        $license = $stmt->fetch();
        
        if (!$license) return ['valid' => false, 'error' => 'Lisans bulunamadı'];
        if (!$license['is_active']) return ['valid' => false, 'error' => 'Lisans aktif değil'];
        
        if ($license['valid_until'] && strtotime($license['valid_until']) < time()) {
            return ['valid' => false, 'error' => 'Lisans süresi dolmuş'];
        }
        
        // Domain kontrolü
        if ($domain) {
            $authorized = json_decode($license['authorized_domains'] ?? '[]', true);
            $all_domains = array_merge([$license['domain'] ?? []], $authorized);
            
            if (!in_array($domain, $all_domains)) {
                if (count($all_domains) >= $license['license_domains']) {
                    return ['valid' => false, 'error' => 'Maksimum domain sayısına ulaşıldı (' . $license['license_domains'] . ')'];
                }
                $authorized[] = $domain;
                $stmt = $db->prepare("UPDATE marketplace_licenses SET authorized_domains = ? WHERE id = ?");
                $stmt->execute([json_encode($authorized), $license['id']]);
            }
        }
        
        return [
            'valid' => true,
            'product_name' => $license['product_name'],
            'license_type' => $license['license_type'],
            'valid_until' => $license['valid_until'],
            'domains_used' => count(json_decode($license['authorized_domains'] ?? '[]', true)) + 1
        ];
    }
    
    /**
     * Güncelleme Kontrolü
     */
    public function checkUpdate($product_id, $current_version) {
        $db = db();
        
        $stmt = $db->prepare("
            SELECT * FROM marketplace_updates 
            WHERE product_id = ? AND version > ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$product_id, $current_version]);
        $update = $stmt->fetch();
        
        if (!$update) return ['has_update' => false];
        
        return [
            'has_update' => true,
            'new_version' => $update['version'],
            'changelog' => $update['changelog'],
            'is_critical' => (bool)$update['is_critical'],
            'file_path' => $update['file_path']
        ];
    }
    
    /**
     * Güncelleme Yayınla
     */
    public function publishUpdate($product_id, $seller_id, $version, $changelog, $file = null) {
        $db = db();
        
        // Verify ownership
        $stmt = $db->prepare("SELECT id FROM marketplace_products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $seller_id]);
        if (!$stmt->fetch()) return ['error' => 'Yetkiniz yok'];
        
        $file_path = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $dir = __DIR__ . '/../../storage/updates';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $file_path = $dir . '/' . uniqid('update_') . '.zip';
            move_uploaded_file($file['tmp_name'], $file_path);
        }
        
        $stmt = $db->prepare("INSERT INTO marketplace_updates (product_id, version, changelog, file_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $version, $changelog, $file_path]);
        
        // Update product version
        $stmt = $db->prepare("UPDATE marketplace_products SET version = ? WHERE id = ?");
        $stmt->execute([$version, $product_id]);
        
        return ['success' => true];
    }
    
    /**
     * Satış ve Komisyon
     */
    public function processSale($product_id, $buyer_id, $amount) {
        $db = db();
        
        $stmt = $db->prepare("SELECT * FROM marketplace_products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if (!$product) return ['error' => 'Ürün bulunamadı'];
        
        $commission = $amount * $this->commission_rate;
        $seller_earnings = $amount - $commission;
        $transaction_id = 'MP_' . time() . '_' . substr(uniqid(), -6);
        
        $stmt = $db->prepare("
            INSERT INTO marketplace_orders 
            (product_id, seller_id, buyer_id, amount, commission, seller_earnings, status, transaction_id)
            VALUES (?, ?, ?, ?, ?, ?, 'completed', ?)
        ");
        $stmt->execute([
            $product_id, $product['seller_id'], $buyer_id,
            $amount, $commission, $seller_earnings, $transaction_id
        ]);
        
        // Update downloads
        $stmt = $db->prepare("UPDATE marketplace_products SET downloads = downloads + 1 WHERE id = ?");
        $stmt->execute([$product_id]);
        
        // Create license
        $license_key = strtoupper(bin2hex(random_bytes(16)));
        $valid_until = date('Y-m-d', strtotime('+' . $product['support_months'] . ' months'));
        
        $stmt = $db->prepare("
            INSERT INTO marketplace_licenses (product_id, buyer_id, license_key, valid_until)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$product_id, $buyer_id, $license_key, $valid_until]);
        
        return [
            'success' => true,
            'transaction_id' => $transaction_id,
            'license_key' => $license_key,
            'commission' => $commission,
            'seller_earnings' => $seller_earnings
        ];
    }
    
    /**
     * Satıcı Kazançları
     */
    public function getSellerStats($seller_id, $period = 'month') {
        $db = db();
        
        $date_filter = match($period) {
            'today' => "DATE(created_at) = CURDATE()",
            'week' => "YEARWEEK(created_at) = YEARWEEK(NOW())",
            'month' => "MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())",
            default => "1=1"
        };
        
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as sales,
                SUM(amount) as revenue,
                SUM(commission) as commission_total,
                SUM(seller_earnings) as earnings
            FROM marketplace_orders
            WHERE seller_id = ? AND status = 'completed' AND $date_filter
        ");
        $stmt->execute([$seller_id]);
        return $stmt->fetch();
    }
    
    private function makeSlug($name) {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
        return trim($slug, '-');
    }
}
