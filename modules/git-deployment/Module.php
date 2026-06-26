<?php
/**
 * Git Deployment
 * Git repo'dan otomatik deploy
 */
class AhostModule_git_deployment {
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
