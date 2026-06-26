<?php
/**
 * E-Invoice Module
 * Türkiye uyumlu e-fatura ve proforma
 */
class AhostModule_e_invoice {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $db->exec($sql);
        return true;
    }
    
    public static function uninstall(PDO $db): bool {
        $db->exec("DROP TABLE IF EXISTS invoices");
        $db->exec("DROP TABLE IF EXISTS invoice_items");
        $db->exec("DROP TABLE IF EXISTS proforma_invoices");
        return true;
    }
    
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
}
