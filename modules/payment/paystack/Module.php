<?php
/**
 * Paystack Payment Gateway Module
 * Afrika'nın lider ödeme altyapısı
 */
class AhostModule_paystack {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function getSettings(): array {
        return [
            'public_key' => get_module_setting('paystack', 'public_key'),
            'secret_key' => get_module_setting('paystack', 'secret_key'),
            'merchant_email' => get_module_setting('paystack', 'merchant_email'),
            'test_mode' => get_module_setting('paystack', 'test_mode') === '1',
        ];
    }
    
    public static function isConfigured(): bool {
        $s = self::getSettings();
        return !empty($s['public_key']) && !empty($s['secret_key']);
    }
    
    public static function getBaseUrl(): string {
        $s = self::getSettings();
        return $s['test_mode'] ? 'https://api.paystack.co' : 'https://api.paystack.co';
    }
    
    public static function initializePayment(float $amount, string $orderId, string $email, string $callbackUrl): array {
        if(!self::isConfigured()) {
            return ['success' => false, 'error' => 'Paystack yapılandırılmamış'];
        }
        
        $s = self::getSettings();
        
        // Convert to kobo (Paystack uses kobo)
        $amount_kobo = (int)($amount * 100);
        
        $data = [
            'email' => $email,
            'amount' => $amount_kobo,
            'reference' => 'ORD_' . $orderId . '_' . time(),
            'callback_url' => $callbackUrl,
            'currency' => 'NGN',
            'metadata' => [
                'order_id' => $orderId,
            ],
        ];
        
        $ch = curl_init(self::getBaseUrl() . '/transaction/initialize');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $s['secret_key'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if($http_code === 200 && !empty($result['data']['authorization_url'])) {
            return [
                'success' => true,
                'authorization_url' => $result['data']['authorization_url'],
                'reference' => $result['data']['reference'],
            ];
        }
        
        return [
            'success' => false,
            'error' => $result['message'] ?? 'Ödeme başlatılamadı',
        ];
    }
    
    public static function verifyPayment(string $reference): array {
        $s = self::getSettings();
        
        $ch = curl_init(self::getBaseUrl() . '/transaction/verify/' . $reference);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $s['secret_key'],
            ],
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if($http_code === 200 && $result['data']['status'] === 'success') {
            return [
                'success' => true,
                'amount' => $result['data']['amount'] / 100,
                'reference' => $result['data']['reference'],
                'customer_email' => $result['data']['customer']['email'],
            ];
        }
        
        return [
            'success' => false,
            'error' => $result['message'] ?? 'Ödeme doğrulanamadı',
        ];
    }
    
    public static function getPaymentForm(float $amount, string $orderId, string $email, string $callbackUrl): string {
        $payment = self::initializePayment($amount, $orderId, $email, $callbackUrl);
        
        if($payment['success']) {
            return '<a href="' . e($payment['authorization_url']) . '" class="btn-paystack">Paystack ile Öde</a>';
        }
        
        return '<div class="error">' . e($payment['error']) . '</div>';
    }
}
