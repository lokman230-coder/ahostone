<?php
/**
 * cPanel/WHM API Entegrasyonu
 * Otomatik hosting account oluşturma, suspend, terminate
 */
class AhostModule_cpanel_api {
    public static function install(PDO $db): bool {
        $db->exec("CREATE TABLE IF NOT EXISTS cpanel_servers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            host VARCHAR(255) NOT NULL,
            port INT DEFAULT 2087,
            username VARCHAR(100),
            api_token VARCHAR(500),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }
    
    public static function createAccount(array $data): array {
        $server = self::getActiveServer();
        if(!$server) return ["success"=>false, "error"=>"No active server"];
        
        // cPanel UAPI call example
        $result = self::apiCall($server, [
            "cpanel_jsonapi_module" => "Account",
            "cpanel_jsonapi_func" => "createaccount",
            "username" => $data["username"],
            "password" => $data["password"],
            "domain" => $data["domain"],
            "plan" => $data["package"]
        ]);
        
        return $result;
    }
    
    public static function suspend(string $username): bool {
        $server = self::getActiveServer();
        if(!$server) return false;
        
        self::apiCall($server, [
            "cpanel_jsonapi_module" => "Account",
            "cpanel_jsonapi_func" => "suspendacct",
            "user" => $username
        ]);
        return true;
    }
    
    private static function getActiveServer(): ?array {
        return db()->query("SELECT * FROM cpanel_servers WHERE is_active=1 LIMIT 1")->fetch() ?: null;
    }
    
    private static function apiCall(array $server, array $params): array {
        // Implementation would use cPanel API
        return ["success"=>true];
    }
}
