<?php
/**
 * Freelance Gig Platform
 * Freelance web tasarımcı platformu
 */
class AhostModule_freelance_gig {
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
