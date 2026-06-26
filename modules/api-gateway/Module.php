<?php
/**
 * Ahost One API Gateway Module
 * REST API ve Webhook sistemi
 */
class AhostModule_api_gateway {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $db->exec($sql);
        return true;
    }
    
    public static function uninstall(PDO $db): bool {
        $db->exec("DROP TABLE IF EXISTS api_keys");
        $db->exec("DROP TABLE IF EXISTS api_logs");
        $db->exec("DROP TABLE IF EXISTS webhooks");
        $db->exec("DROP TABLE IF EXISTS webhook_logs");
        return true;
    }
    
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
}
