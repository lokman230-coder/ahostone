<?php
/**
 * Reseller Module
 * Çoklu bayi sistemi
 */
class AhostModule_reseller {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $db->exec("
            CREATE TABLE IF NOT EXISTS resellers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                company_name VARCHAR(200),
                commission_rate DECIMAL(5,2) DEFAULT 10.00,
                max_customers INT DEFAULT 100,
                current_customers INT DEFAULT 0,
                balance DECIMAL(12,2) DEFAULT 0.00,
                status ENUM('active','suspended','pending') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        return true;
    }
    
    public static function getReseller(int $userId): ?array {
        return db()->prepare("SELECT * FROM resellers WHERE user_id=?")->execute([$userId])->fetch() ?: null;
    }
    
    public static function createReseller(int $userId, float $commission = 10): bool {
        try {
            db()->prepare("INSERT INTO resellers (user_id, commission_rate) VALUES (?,?)")->execute([$userId, $commission]);
            return true;
        } catch(Throwable $e) { return false; }
    }
    
    public static function isReseller(int $userId): bool {
        return self::getReseller($userId) !== null;
    }
}
