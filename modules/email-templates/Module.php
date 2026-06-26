<?php
/**
 * Ahost One Email Templates Module
 * E-posta şablon sistemi
 */
class AhostModule_email_templates {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $db->exec($sql);
        self::insertDefaultTemplates($db);
        return true;
    }
    
    public static function uninstall(PDO $db): bool {
        $db->exec("DROP TABLE IF EXISTS email_templates");
        return true;
    }
    
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
    
    private static function insertDefaultTemplates(PDO $db) {
        $templates = [
            ['welcome', 'Hoş Geldiniz', 'customer', 'Merhaba {customer_name}, Ahost One\'a hoş geldiniz!', 'welcome.html'],
            ['order_created', 'Sipariş Oluşturuldu', 'order', 'Siparişiniz oluşturuldu.', 'order_created.html'],
            ['order_completed', 'Sipariş Tamamlandı', 'order', 'Siparişiniz tamamlandı.', 'order_completed.html'],
            ['invoice_created', 'Fatura Oluşturuldu', 'invoice', 'Yeni fatura oluşturuldu.', 'invoice_created.html'],
            ['invoice_paid', 'Fatura Ödendi', 'invoice', 'Faturanız ödendi.', 'invoice_paid.html'],
            ['domain_registered', 'Domain Kaydedildi', 'domain', 'Domaininiz kaydedildi.', 'domain_registered.html'],
            ['domain_expiring', 'Domain Bitiş Uyarısı', 'domain', 'Domain süreniz doluyor.', 'domain_expiring.html'],
            ['support_ticket', 'Destek Talebi', 'support', 'Destek talebiniz alındı.', 'support_ticket.html'],
            ['support_reply', 'Destek Yanıtı', 'support', 'Destek talebinize yanıt verildi.', 'support_reply.html'],
            ['password_reset', 'Şifre Sıfırlama', 'system', 'Şifrenizi sıfırlayın.', 'password_reset.html'],
            ['affiliate_signup', 'Affiliate Kaydı', 'affiliate', 'Affiliate programına katıldınız.', 'affiliate_signup.html'],
            ['affiliate_commission', 'Komisyon Kazandınız', 'affiliate', 'Yeni komisyon kazandınız.', 'affiliate_commission.html'],
        ];
        
        foreach($templates as $t) {
            try {
                $db->prepare("INSERT IGNORE INTO email_templates (slug, name, type, subject, content) VALUES (?,?,?,?,?)")
                    ->execute([$t[0], $t[1], $t[2], $t[3], self::getDefaultContent($t[4])]);
            } catch(Throwable $e) {}
        }
    }
    
    private static function getDefaultContent($file): string {
        $path = __DIR__ . '/templates/' . $file;
        if(file_exists($path)) {
            return file_get_contents($path);
        }
        return '<h1>Merhaba {customer_name}</h1><p>{message}</p>';
    }
}
