<?php
/**
 * Workflow Automation
 * IFTTT tarzı otomasyon kuralları
 */
class AhostModule_workflow_automation {
    public static function install(PDO $db): bool {
        $db->exec("CREATE TABLE IF NOT EXISTS automations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            trigger_type VARCHAR(100) NOT NULL,
            trigger_config JSON,
            actions JSON,
            is_active TINYINT(1) DEFAULT 1,
            run_count INT DEFAULT 0,
            last_run DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }
    
    public static function run(int $automationId): bool {
        $automation = db()->prepare("SELECT * FROM automations WHERE id=?")->execute([$automationId])->fetch();
        if(!$automation || !$automation["is_active"]) return false;
        
        $trigger = json_decode($automation["trigger_config"], true);
        $actions = json_decode($automation["actions"], true);
        
        if(self::checkTrigger($trigger)) {
            foreach($actions as $action) {
                self::executeAction($action);
            }
            db()->prepare("UPDATE automations SET run_count=run_count+1, last_run=NOW() WHERE id=?")
                ->execute([$automationId]);
        }
        
        return true;
    }
    
    public static function getTriggers(): array {
        return [
            ["id"=>"order_completed","name"=>"Sipariş Tamamlandı","icon"=>"🛒"],
            ["id"=>"payment_received","name"=>"Ödeme Alındı","icon"=>"💰"],
            ["id"=>"hosting_expiring","name"=>"Hosting Bitiyor","icon"=>"⏰"],
            ["id"=>"ticket_created","name"=>"Destek Talebi","icon"=>"🎫"],
            ["id"=>"domain_expiring","name"=>"Domain Bitiyor","icon"=>"🌐"],
            ["id"=>"user_registered","name"=>"Yeni Kayıt","icon"=>"👤"]
        ];
    }
    
    public static function getActions(): array {
        return [
            ["id"=>"send_email","name"=>"E-posta Gönder","icon"=>"📧"],
            ["id"=>"send_sms","name"=>"SMS Gönder","icon"=>"📱"],
            ["id"=>"create_invoice","name"=>"Fatura Oluştur","icon"=>"📄"],
            ["id"=>"suspend_service","name"=>"Hizmeti Askıya Al","icon"=>"⛔"],
            ["id"=>"webhook","name"=>"Webhook Tetikle","icon"=>"🔗"],
            ["id"=>"slack_notify","name"=>"Slack Bildirimi","icon"=>"💬"]
        ];
    }
    
    private static function checkTrigger(array $trigger): bool {
        return true;
    }
    
    private static function executeAction(array $action): bool {
        return true;
    }
}
