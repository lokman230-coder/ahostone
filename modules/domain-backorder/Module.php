<?php
/**
 * Domain Backorder
 * 到期 domain yakalama servisi
 */
class AhostModule_domain_backorder {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        return true;
    }
    
    public static function getSettings(): array {
        return [];
    }
}
