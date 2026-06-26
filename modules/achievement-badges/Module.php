<?php
/**
 * Başarı Rozetleri
 * Rozet ödülleri
 */
class AhostModule_achievement_badges {
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
