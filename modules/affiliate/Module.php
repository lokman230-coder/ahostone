<?php
/**
 * Ahost One Affiliate System
 * Ortaklık programı - komisyon, referral link, ödeme
 */
class AhostModule_affiliate {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $db->exec($sql);
        return true;
    }
    
    public static function uninstall(PDO $db): bool {
        $db->exec("DROP TABLE IF EXISTS affiliate_payments");
        $db->exec("DROP TABLE IF EXISTS affiliate_commissions");
        $db->exec("DROP TABLE IF EXISTS affiliate_referrals");
        $db->exec("DROP TABLE IF EXISTS affiliate_affiliates");
        return true;
    }
    
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
}
