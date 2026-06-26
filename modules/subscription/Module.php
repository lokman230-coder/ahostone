<?php
class AhostModule_subscription {
    public static function install(PDO $db): bool {
        $db->exec("CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            plan_id INT NOT NULL,
            status ENUM('active','cancelled','expired','paused') DEFAULT 'active',
            billing_cycle ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
            next_billing_date DATE,
            last_billing_date DATE,
            auto_renew TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }
    public static function processRenewals(): int {
        $subs = db()->query("SELECT * FROM subscriptions WHERE auto_renew=1 AND next_billing_date <= CURDATE() AND status='active'")->fetchAll();
        foreach($subs as $s) {
            $invoice = self::createRenewalInvoice($s);
            if($invoice) db()->prepare("UPDATE subscriptions SET last_billing_date=NOW(), next_billing_date=? WHERE id=?")
                ->execute([date('Y-m-d', strtotime('+1 month')), $s['id']]);
        }
        return count($subs);
    }
    private static function createRenewalInvoice($sub): ?int {
        // Create invoice and charge payment
        return null;
    }
    public static function cancel($id): bool {
        try { db()->prepare("UPDATE subscriptions SET auto_renew=0, status='cancelled' WHERE id=?")->execute([$id]); return true; } catch(Throwable $e) { return false; }
    }
}
