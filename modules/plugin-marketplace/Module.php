<?php
/**
 * Plugin Marketplace Pro
 * Ucretli modul/tumleşik satisı - ZIP yukleme, lisans, guncelleme
 */
namespace Ahost\Modules\PluginMarketplace;

class PluginMarketplaceModule {
    private $commission_rate = 0.15;

    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }

    public static function install(\PDO $db): bool {
        $db->query("
            CREATE TABLE IF NOT EXISTS `plugin_marketplace_products` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `seller_id` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) UNIQUE,
                `description` TEXT,
                `short_description` VARCHAR(500),
                `price_basic` DECIMAL(10,2) DEFAULT 0,
                `price_pro` DECIMAL(10,2) DEFAULT 0,
                `currency` VARCHAR(10) DEFAULT 'TRY',
                `category` VARCHAR(100),
                `type` VARCHAR(50) DEFAULT 'module',
                `file_path` VARCHAR(500),
                `demo_url` VARCHAR(500),
                `version` VARCHAR(20) DEFAULT '1.0.0',
                `license_type` ENUM('single_domain', 'open_source') DEFAULT 'single_domain',
                `support_months` INT DEFAULT 6,
                `is_active` TINYINT(1) DEFAULT 1,
                `is_featured` TINYINT(1) DEFAULT 0,
                `downloads` INT DEFAULT 0,
                `rating` DECIMAL(3,2) DEFAULT 0,
                `tags` JSON,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_seller (seller_id),
                INDEX idx_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `plugin_marketplace_licenses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `buyer_id` INT NOT NULL,
                `license_key` VARCHAR(64) UNIQUE NOT NULL,
                `license_type` ENUM('single_domain', 'open_source') DEFAULT 'single_domain',
                `package` ENUM('basic', 'pro') DEFAULT 'basic',
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
            CREATE TABLE IF NOT EXISTS `plugin_marketplace_orders` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `seller_id` INT NOT NULL,
                `buyer_id` INT NOT NULL,
                `package` ENUM('basic', 'pro') DEFAULT 'basic',
                `amount` DECIMAL(10,2) NOT NULL,
                `commission` DECIMAL(10,2) NOT NULL,
                `seller_earnings` DECIMAL(10,2) NOT NULL,
                `currency` VARCHAR(10) DEFAULT 'TRY',
                `status` ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
                `transaction_id` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `plugin_marketplace_updates` (
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

    public static function uninstall(\PDO $db): bool {
        return true;
    }

    public static function enable(\PDO $db): bool {
        return true;
    }

    public static function disable(\PDO $db): bool {
        return true;
    }

    public static function validateLicense($license_key, $domain = null) {
        $db = db();

        $stmt = $db->prepare("
            SELECT pml.*, pmp.name as product_name, pmp.license_type
            FROM plugin_marketplace_licenses pml
            JOIN plugin_marketplace_products pmp ON pmp.id = pml.product_id
            WHERE pml.license_key = ?
        ");
        $stmt->execute([$license_key]);
        $license = $stmt->fetch();

        if (!$license) return ['valid' => false, 'error' => 'Lisans bulunamadi'];
        if (!$license['is_active']) return ['valid' => false, 'error' => 'Lisans aktif degil'];

        if ($license['valid_until'] && strtotime($license['valid_until']) < time()) {
            return ['valid' => false, 'error' => 'Lisans suresi dolmus'];
        }

        return [
            'valid' => true,
            'product_name' => $license['product_name'],
            'license_type' => $license['license_type'],
            'package' => $license['package'],
            'valid_until' => $license['valid_until']
        ];
    }

    public static function checkLicenseHash($product_id, $hash) {
        $expected = hash('sha256', $product_id . 'ahost_secret_key');
        return $expected === $hash;
    }
}
