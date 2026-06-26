<?php
/**
 * Webhook Manager
 * API webhook'ları yönetimi
 */
class AhostModule_webhook_manager {
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
