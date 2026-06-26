<?php
/**
 * Ahost One Live Chat Module
 * Canlı destek sohbet sistemi - WhatsApp, AI chatbot
 */
class AhostModule_live_chat {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $db->exec($sql);
        return true;
    }
    
    public static function uninstall(PDO $db): bool {
        $db->exec("DROP TABLE IF EXISTS chat_messages");
        $db->exec("DROP TABLE IF EXISTS chat_conversations");
        $db->exec("DROP TABLE IF EXISTS chat_departments");
        $db->exec("DROP TABLE IF EXISTS chat_agents");
        return true;
    }
    
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
}
