<?php
/**
 * AI Generate Site API
 * OpenAI ile site tasarımı oluşturma
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
    
    $site_type = $_POST['site_type'] ?? 'landing';
    $site_name = $_POST['site_name'] ?? 'Yeni Site';
    $tagline = $_POST['tagline'] ?? '';
    $services = $_POST['services'] ?? '';
    $color_scheme = $_POST['color_scheme'] ?? 'modern';
    $custom_prompt = $_POST['custom_prompt'] ?? '';
    
    // Build prompt for OpenAI
    $prompt = "Create a professional website design JSON for a {$site_type} website called '{$site_name}'. ";
    $prompt .= "Tagline: {$tagline}. ";
    $prompt .= "Services: {$services}. ";
    $prompt .= "Color scheme: {$color_scheme}. ";
    if($custom_prompt) {
        $prompt .= "Additional requirements: {$custom_prompt}";
    }
    $prompt .= " Return only a JSON array of sections with type, content, and style properties. Do not include markdown code blocks.";
    
    // Call OpenAI API
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional web designer. Return only valid JSON.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000
        ])
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if($http_code !== 200) {
        // Fallback: return sample design
        $design_json = json_encode([
            ['type' => 'hero', 'title' => $site_name, 'text' => $tagline, 'button' => 'Başla'],
            ['type' => 'features', 'title' => 'Özelliklerimiz', 'items' => array_map(fn($s) => ['title' => trim($s)], explode(',', $services))],
            ['type' => 'cta', 'title' => 'Hemen Başlayın', 'text' => 'Bizimle iletişime geçin']
        ]);
    } else {
        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        // Clean JSON from markdown if present
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $design_json = trim($content);
    }
    
    // Create page in SiteBuilder
    $page_id = 0;
    try {
        $stmt = db()->prepare("INSERT INTO sitebuilder_pages (project_id, title, slug, builder_json, status) VALUES (?, ?, ?, ?, 'draft')");
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $site_name));
        $stmt->execute([1, $site_name, $slug, $design_json]);
        $page_id = db()->lastInsertId();
    } catch(Throwable $e) {
        // Table might not exist yet
    }
    
    echo json_encode([
        'success' => true,
        'page_id' => $page_id,
        'design' => json_decode($design_json, true),
        'message' => 'Tasarım başarıyla oluşturuldu'
    ]);
    
} catch(Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
