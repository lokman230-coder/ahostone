<?php
namespace Ahost\Modules\SeoAnalyzer;
use Ahost\System\Module;

class SeoAnalyzerModule extends Module {
    protected $name = 'seo-analyzer';
    protected $version = '1.0.0';
    protected $description = 'SEO Analiz Araçları';
    
    public function install() {
        $this->db()->query("CREATE TABLE IF NOT EXISTS `seo_analyses` (`id` INT AUTO_INCREMENT PRIMARY KEY, `url` VARCHAR(500) NOT NULL, `score` INT DEFAULT 0, `meta_score` INT DEFAULT 0, `content_score` INT DEFAULT 0, `speed_score` INT DEFAULT 0, `mobile_score` INT DEFAULT 0, `issues` TEXT, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db()->query("CREATE TABLE IF NOT EXISTS `seo_keywords` (`id` INT AUTO_INCREMENT PRIMARY KEY, `keyword` VARCHAR(255) NOT NULL, `volume` INT DEFAULT 0, `difficulty` INT DEFAULT 0, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }
    
    public function uninstall() {
        $this->db()->query("DROP TABLE IF EXISTS `seo_analyses`");
        $this->db()->query("DROP TABLE IF EXISTS `seo_keywords`");
        return true;
    }
    
    public function analyzeUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $html = curl_exec($ch);
        curl_close($ch);
        if (!$html) return false;
        $score = 100; $issues = [];
        preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $title);
        if (empty($title[1]) || strlen($title[1]) < 10) { $score -= 20; $issues[] = 'Baslik eksik'; }
        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $desc);
        if (empty($desc[1])) { $score -= 15; $issues[] = 'Description yok'; }
        return ['score' => max(0, $score), 'issues' => $issues, 'url' => $url];
    }
}
