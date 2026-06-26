<?php
/**
 * Dunning Module
 * Otomatik ödeme hatırlatmaları ve tahsilat yönetimi
 */
class AhostModule_dunning {
    public static function install(PDO $db): bool {
        $db->exec("
            CREATE TABLE IF NOT EXISTS dunning_rules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                days_after_due INT NOT NULL,
                action ENUM('email','sms','suspend','terminate') NOT NULL,
                template VARCHAR(100),
                fee DECIMAL(10,2) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $db->exec("
            CREATE TABLE IF NOT EXISTS dunning_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_id INT NOT NULL,
                customer_id INT NOT NULL,
                rule_id INT DEFAULT NULL,
                action VARCHAR(50) NOT NULL,
                status ENUM('pending','sent','failed') DEFAULT 'pending',
                sent_at DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Insert default rules
        $rules = [
            ['Gecikme Bildirimi', 3, 'email', 'late_payment_1'],
            ['2. Hatırlatma', 7, 'email', 'late_payment_2'],
            ['Son Uyarı', 14, 'sms', 'late_payment_3'],
            ['Hizmet Askıya Alma', 21, 'suspend', null],
            ['Hizmet İptal', 30, 'terminate', null],
        ];
        
        foreach($rules as $r) {
            try {
                $db->prepare("INSERT IGNORE INTO dunning_rules (name, days_after_due, action, template) VALUES (?,?,?,?)")
                    ->execute($r);
            } catch(Throwable $e) {}
        }
        
        return true;
    }
    
    public static function processOverdueInvoices(): int {
        $processed = 0;
        
        // Get overdue invoices
        $overdue = db()->query("
            SELECT i.*, c.id as customer_id, c.name as customer_name, c.email
            FROM invoices i
            JOIN customers c ON c.id=i.customer_id
            WHERE i.status='unpaid' AND i.due_date < CURDATE()
            AND i.id NOT IN (SELECT invoice_id FROM dunning_logs WHERE action IN ('suspend','terminate'))
        ")->fetchAll();
        
        foreach($overdue as $inv) {
            $days_overdue = (strtotime(date('Y-m-d')) - strtotime($inv['due_date'])) / 86400;
            
            // Get applicable rule
            $rule = db()->prepare("
                SELECT * FROM dunning_rules 
                WHERE days_after_due <= ? AND is_active=1 
                ORDER BY days_after_due DESC LIMIT 1
            ")->execute([$days_overdue])->fetch();
            
            if($rule) {
                self::executeRule($inv, $rule);
                $processed++;
            }
        }
        
        return $processed;
    }
    
    private static function executeRule(array $invoice, array $rule): bool {
        try {
            // Log the action
            db()->prepare("INSERT INTO dunning_logs (invoice_id, customer_id, rule_id, action) VALUES (?,?,?,?)")
                ->execute([$invoice['id'], $invoice['customer_id'], $rule['id'], $rule['action']]);
            
            switch($rule['action']) {
                case 'email':
                    self::sendEmailReminder($invoice, $rule);
                    break;
                case 'sms':
                    self::sendSmsReminder($invoice, $rule);
                    break;
                case 'suspend':
                    self::suspendService($invoice);
                    break;
                case 'terminate':
                    self::terminateService($invoice);
                    break;
            }
            
            return true;
        } catch(Throwable $e) {
            return false;
        }
    }
    
    private static function sendEmailReminder(array $invoice, array $rule): void {
        // Send email using email templates
        $template = $rule['template'] ?? 'late_payment_reminder';
        $subject = 'Ödeme Hatırlatması - Fatura #' . $invoice['invoice_number'];
        
        // In production, would use mailer
        // mailer()->send($invoice['email'], $subject, $template, ['invoice' => $invoice]);
    }
    
    private static function sendSmsReminder(array $invoice, array $rule): void {
        // Send SMS using SMS module
        // In production, would use SMS provider
    }
    
    private static function suspendService(array $invoice): void {
        // Suspend hosting services
        db()->prepare("UPDATE hosting SET status='suspended' WHERE invoice_id=?")
            ->execute([$invoice['id']]);
    }
    
    private static function terminateService(array $invoice): void {
        // Terminate services
        db()->prepare("UPDATE hosting SET status='terminated' WHERE invoice_id=?")
            ->execute([$invoice['id']]);
    }
    
    public static function getStats(): array {
        return [
            'pending' => db()->query("SELECT COUNT(*) FROM dunning_logs WHERE status='pending'")->fetchColumn(),
            'sent' => db()->query("SELECT COUNT(*) FROM dunning_logs WHERE status='sent'")->fetchColumn(),
            'suspended' => db()->query("SELECT COUNT(*) FROM hosting WHERE status='suspended'")->fetchColumn(),
        ];
    }
}
