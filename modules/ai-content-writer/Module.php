<?php
/**
 * AI Content Writer
 * OpenAI ile içerik üretimi
 */
class AhostModule_ai_content_writer {
    public static function write(string $type, array $data): string {
        $prompts = [
            "blog_intro" => "Write an engaging blog introduction about: {$data['topic']}",
            "product_desc" => "Write a compelling product description for: {$data['product']}",
            "seo_content" => "Write SEO optimized content about: {$data['topic']} with keywords: {$data['keywords']}",
            "social_post" => "Write an engaging social media post about: {$data['topic']}",
            "email_template" => "Write a professional email template for: {$data['purpose']}"
        ];
        
        $prompt = $prompts[$type] ?? "Write content about: " . json_encode($data);
        
        return self::callGpt($prompt, $data["tone"] ?? "professional");
    }
    
    private static function callGpt(string $prompt, string $tone): string {
        // OpenAI GPT API implementation
        return "Generated content...";
    }
    
    public static function getTypes(): array {
        return [
            ["id"=>"blog_intro","name"=>"Blog Girişi","icon"=>"📝"],
            ["id"=>"product_desc","name"=>"Ürün Açıklaması","icon"=>"🏷️"],
            ["id"=>"seo_content","name"=>"SEO İçeriği","icon"=>"🔍"],
            ["id"=>"social_post","name"=>"Sosyal Medya","icon"=>"📱"],
            ["id"=>"email_template","name"=>"E-posta Şablonu","icon"=>"📧"]
        ];
    }
}
