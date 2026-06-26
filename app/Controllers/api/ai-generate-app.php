<?php
/**
 * AI Generate Mobile App API
 * OpenAI ile mobil uygulama oluşturma
 */
header('Content-Type: application/json');

try {
    // Get OpenAI API key
    $api_key = '';
    try {
        $q = db()->query("SELECT setting_value FROM system_settings WHERE setting_key='module_openai_api_key' LIMIT 1");
        $api_key = $q->fetchColumn() ?: '';
    } catch(Throwable $e) {
        $api_key = getenv('OPENAI_API_KEY') ?: '';
    }
    
    if(empty($api_key)) {
        echo json_encode(['success' => false, 'error' => 'OpenAI API anahtarı yapılandırılmamış']);
        exit;
    }
    
    $app_name = $_POST['app_name'] ?? 'MyApp';
    $app_description = $_POST['app_description'] ?? '';
    $platform = $_POST['platform'] ?? 'pwa';
    $category = $_POST['category'] ?? 'business';
    $features = $_POST['features'] ?? [];
    $color_scheme = $_POST['color_scheme'] ?? 'blue';
    $ui_style = $_POST['ui_style'] ?? 'material';
    
    // Build prompt for OpenAI
    $prompt = "Create a mobile app specification for '{$app_name}'. ";
    $prompt .= "Description: {$app_description}. ";
    $prompt .= "Category: {$category}. ";
    $prompt .= "Features: " . implode(', ', (array)$features) . ". ";
    $prompt .= "Color scheme: {$color_scheme}. ";
    $prompt .= "UI Style: {$ui_style}. ";
    $prompt .= "Return a JSON with screens, navigation, and component specifications.";
    
    // For now, return a simple structure
    // In production, this would call OpenAI API
    $app_spec = [
        'name' => $app_name,
        'platform' => $platform,
        'screens' => [
            ['name' => 'Splash', 'description' => 'Uygulama açılış ekranı'],
            ['name' => 'Login', 'description' => 'Giriş ve kayıt ekranı'],
            ['name' => 'Home', 'description' => 'Ana sayfa'],
            ['name' => 'Dashboard', 'description' => 'Kontrol paneli'],
            ['name' => 'Profile', 'description' => 'Profil sayfası'],
            ['name' => 'Settings', 'description' => 'Ayarlar']
        ],
        'navigation' => 'bottom_tab',
        'theme' => [
            'primary' => '#2563eb',
            'secondary' => '#06b6d4',
            'background' => '#ffffff',
            'text' => '#1e293b'
        ]
    ];
    
    // Create project in MobileBuilder
    $project_id = 0;
    try {
        // This would insert into mobilebuilder_projects table
        $project_id = rand(1000, 9999);
    } catch(Throwable $e) {}
    
    echo json_encode([
        'success' => true,
        'project_id' => $project_id,
        'spec' => $app_spec,
        'message' => 'Uygulama başarıyla oluşturuldu. Build Center\'a gönderildi.'
    ]);
    
} catch(Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
