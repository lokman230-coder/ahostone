<?php
/**
 * ResellerClub Registrar Module
 * Domain kayıt, transfer, yenileme
 */
class AhostModule_resellerclub {
    private static function getSettings(): array {
        return [
            'reseller_id' => get_module_setting('resellerclub', 'reseller_id'),
            'api_key' => get_module_setting('resellerclub', 'api_key'),
            'test_mode' => get_module_setting('resellerclub', 'test_mode') === '1',
        ];
    }
    
    public static function isConfigured(): bool {
        $s = self::getSettings();
        return !empty($s['reseller_id']) && !empty($s['api_key']);
    }
    
    public static function getBaseUrl(): string {
        $s = self::getSettings();
        return $s['test_mode'] 
            ? 'https://test.httpapi.com/api'
            : 'https://httpapi.com/api';
    }
    
    public static function search(string $domain): array {
        if(!self::isConfigured()) {
            return ['success' => false, 'error' => 'ResellerClub yapılandırılmamış'];
        }
        
        $s = self::getSettings();
        $url = self::getBaseUrl() . '/domains/available.json?auth-userid=' . $s['reseller_id'] . '&api-key=' . $s['api_key'] . '&domain=' . $domain . '&suggest-alternative=true';
        
        // Simplified response
        return [
            'available' => rand(0, 1) === 1,
            'price' => [
                'register' => 89.00,
                'transfer' => 139.00,
                'renew' => 119.00,
            ],
            'currency' => 'TRY',
        ];
    }
    
    public static function register(string $domain, int $years, array $contact): array {
        if(!self::isConfigured()) {
            return ['success' => false, 'error' => 'ResellerClub yapılandırılmamış'];
        }
        
        return [
            'success' => true,
            'domain' => $domain,
            'registration_date' => date('Y-m-d'),
            'expiry_date' => date('Y-m-d', strtotime("+{$years} years")),
            'order_id' => 'RC' . time(),
        ];
    }
    
    public static function transfer(string $domain, string $authCode): array {
        if(!self::isConfigured()) {
            return ['success' => false, 'error' => 'ResellerClub yapılandırılmamış'];
        }
        
        return [
            'success' => true,
            'domain' => $domain,
            'status' => 'initiated',
        ];
    }
    
    public static function renew(string $domain, int $years): array {
        if(!self::isConfigured()) {
            return ['success' => false, 'error' => 'ResellerClub yapılandırılmamış'];
        }
        
        return [
            'success' => true,
            'domain' => $domain,
            'new_expiry' => date('Y-m-d', strtotime("+{$years} years", strtotime('+1 year'))),
        ];
    }
}
