<?php
class AhostModule_abandoned_cart {
    public static function install(PDO $db): bool {
        $db->exec("CREATE TABLE IF NOT EXISTS abandoned_carts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT DEFAULT NULL,
            email VARCHAR(150),
            cart_data JSON,
            total DECIMAL(12,2) DEFAULT 0,
            step_reached VARCHAR(50),
            recovery_emails_sent INT DEFAULT 0,
            last_email_at DATETIME,
            converted TINYINT(1) DEFAULT 0,
            converted_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }
    public static function track($email, $cartData, $step): bool {
        try {
            db()->prepare("INSERT INTO abandoned_carts (email, cart_data, total, step_reached) VALUES (?,?,?,?)")
                ->execute([$email, json_encode($cartData), $cartData['total'] ?? 0, $step]);
            return true;
        } catch(Throwable $e) { return false; }
    }
    public static function recover(): int {
        $carts = db()->query("SELECT * FROM abandoned_carts WHERE converted=0 AND recovery_emails_sent < 3")->fetchAll();
        foreach($carts as $c) {
            self::sendRecoveryEmail($c);
            db()->prepare("UPDATE abandoned_carts SET recovery_emails_sent=recovery_emails_sent+1, last_email_at=NOW() WHERE id=?")->execute([$c['id']]);
        }
        return count($carts);
    }
    private static function sendRecoveryEmail($cart): void {}
}
