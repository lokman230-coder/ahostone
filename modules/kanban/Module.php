<?php
/**
 * Ahost One Kanban Board Module
 * Proje ve görev yönetimi
 */
class AhostModule_kanban {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $db->exec($sql);
        return true;
    }
    
    public static function uninstall(PDO $db): bool {
        $db->exec("DROP TABLE IF EXISTS kanban_cards");
        $db->exec("DROP TABLE IF EXISTS kanban_columns");
        $db->exec("DROP TABLE IF EXISTS kanban_boards");
        return true;
    }
    
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
}
