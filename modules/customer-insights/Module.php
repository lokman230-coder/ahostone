<?php
/**
 * Customer Insights
 * Müşteri davranış analizi
 */
class AhostModule_customer_insights {
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
