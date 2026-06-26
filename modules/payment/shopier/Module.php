<?php
/** Shopier Payment Gateway - Ahost One Module */
class AhostModule_shopier {
    public static function manifest(): array { return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: []; }
    public static function install(PDO $db): bool { return true; }
    public static function uninstall(PDO $db): bool { return true; }
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
}
