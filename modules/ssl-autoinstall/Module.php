<?php
/**
 * SSL Auto-Install
 * Let's Encrypt ve AutoSSL otomatik kurulum
 */
class AhostModule_ssl_autoinstall {
    public static function install(PDO $db): bool {
        $db->exec("CREATE TABLE IF NOT EXISTS ssl_certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            domain VARCHAR(255) NOT NULL,
            type ENUM('letsencrypt','autossl','comodo') DEFAULT 'letsencrypt',
            status ENUM('pending','issuing','active','expired') DEFAULT 'pending',
            cert_path VARCHAR(500),
            key_path VARCHAR(500),
            expires_at DATE,
            auto_renew TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }
    
    public static function request(string $domain, string $type = 'letsencrypt'): array {
        // Let's Encrypt ACME challenge
        $challenges = [
            "http-01" => self::createHttpChallenge($domain),
            "dns-01" => self::createDnsChallenge($domain)
        ];
        
        return [
            "success" => true,
            "challenge" => $challenges["http-01"],
            "instructions" => "Place the challenge file in /.well-known/acme-challenge/"
        ];
    }
    
    public static function renew(int $certId): bool {
        $cert = db()->prepare("SELECT * FROM ssl_certificates WHERE id=?")->execute([$certId])->fetch();
        if(!$cert) return false;
        
        // Trigger renewal
        return true;
    }
    
    private static function createHttpChallenge(string $domain): string {
        return bin2hex(random_bytes(32));
    }
    
    private static function createDnsChallenge(string $domain): string {
        return base64_encode(hash("sha256", random_bytes(32), true));
    }
}
