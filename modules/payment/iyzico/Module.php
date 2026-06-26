<?php
/**
 * Iyzico Payment Gateway Module
 * Türkiye'nin en popüler ödeme altyapısı
 */
class AhostModule_iyzico {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function getSettings(): array {
        return [
            'api_key' => get_module_setting('iyzico', 'api_key'),
            'secret_key' => get_module_setting('iyzico', 'secret_key'),
            'base_url' => get_module_setting('iyzico', 'base_url') ?: 'https://sandbox-api.iyzipay.com',
        ];
    }
    
    public static function isConfigured(): bool {
        $s = self::getSettings();
        return !empty($s['api_key']) && !empty($s['secret_key']);
    }
    
    public static function createPaymentForm(float $amount, string $orderId, string $customerName, string $customerEmail, string $callbackUrl): string {
        if(!self::isConfigured()) {
            return '<div class="error">Iyzico yapılandırılmamış.</div>';
        }
        
        $s = self::getSettings();
        
        $form = '<form id="iyzico-form" method="POST" action="' . $s['base_url'] . '/payment/auth/FormInitialize" target="_blank">';
        $form .= '<input type="hidden" name="locale" value="tr">';
        $form .= '<input type="hidden" name="conversationId" value="' . e($orderId) . '">';
        $form .= '<input type="hidden" name="pricingPlanType" value="PRIMARY_BASED">';
        $form .= '<input type="hidden" name="paymentGroup" value="PRODUCT">';
        $form .= '<input type="hidden" name="currency" value="TRY">';
        $form .= '<input type="hidden" name="channelCode" value="WEB">';
        
        // First payment item
        $form .= '<input type="hidden" name="paymentItems[0][itemId]" value="' . e($orderId) . '">';
        $form .= '<input type="hidden" name="paymentItems[0][name]" value="Sipariş #' . e($orderId) . '">';
        $form .= '<input type="hidden" name="paymentItems[0][category1]" value="Hosting">';
        $form .= '<input type="hidden" name="paymentItems[0][category2]" value="Sipariş">';
        $form .= '<input type="hidden" name="paymentItems[0][price]" value="' . number_format($amount, 2, '.', '') . '">';
        
        // Customer
        $form .= '<input type="hidden" name="customer[name]" value="' . e($customerName) . '">';
        $form .= '<input type="hidden" name="customer[surname]" value=".">';
        $form .= '<input type="hidden" name="customer[email]" value="' . e($customerEmail) . '">';
        $form .= '<input type="hidden" name="customer[gsmNumber]" value="+905550000000">';
        $form .= '<input type="hidden" name="customer[identityNumber]" value="11111111111">';
        
        // Billing address
        $form .= '<input type="hidden" name="billingAddress[contactName]" value="' . e($customerName) . '">';
        $form .= '<input type="hidden" name="billingAddress[city]" value="Istanbul">';
        $form .= '<input type="hidden" name="billingAddress[country]" value="Turkey">';
        $form .= '<input type="hidden" name="billingAddress[address]" value="Billing Address">';
        $form .= '<input type="hidden" name="billingAddress[zipCode]" value="34000">';
        
        return $form;
    }
    
    public static function verifyCallback(array $post): array {
        $s = self::getSettings();
        
        $response = [
            'success' => false,
            'order_id' => $post['conversationId'] ?? '',
            'payment_id' => $post['paymentId'] ?? '',
            'status' => $post['status'] ?? '',
            'error_message' => $post['errorMessage'] ?? '',
        ];
        
        if($post['status'] === 'success' && !empty($post['paymentId'])) {
            $response['success'] = true;
        }
        
        return $response;
    }
    
    public static function refund(string $paymentId, float $amount): array {
        // Implement refund logic
        return ['success' => false, 'message' => 'Refund not implemented'];
    }
}
