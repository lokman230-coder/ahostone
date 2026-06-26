<?php
namespace Ahost\Modules\Builder\SiteBuilder;

class SiteBuilderController
{
    private $db;
    private $userId;
    
    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->userId = $_SESSION['user_id'] ?? 0;
    }
    
    /**
     * Ana panel
     */
    public function index()
    {
        $templates = $this->getTemplates();
        $projects = $this->getUserProjects();
        $blocks = $this->getBlocks();
        
        return [
            'templates' => $templates,
            'projects' => $projects,
            'blocks' => $blocks
        ];
    }
    
    /**
     * Şablonları getir
     */
    public function getTemplates()
    {
        return [
            ['id' => 'hosting', 'name' => 'Hosting Firması', 'category' => 'business', 'preview' => 'hosting.jpg'],
            ['id' => 'corporate', 'name' => 'Kurumsal Şirket', 'category' => 'business', 'preview' => 'corporate.jpg'],
            ['id' => 'software', 'name' => 'Yazılım Firması', 'category' => 'business', 'preview' => 'software.jpg'],
            ['id' => 'agency', 'name' => 'Ajans', 'category' => 'business', 'preview' => 'agency.jpg'],
            ['id' => 'radio', 'name' => 'Radyo', 'category' => 'media', 'preview' => 'radio.jpg'],
            ['id' => 'news', 'name' => 'Haber', 'category' => 'media', 'preview' => 'news.jpg'],
            ['id' => 'ecommerce', 'name' => 'E-Ticaret Ön Site', 'category' => 'ecommerce', 'preview' => 'ecommerce.jpg'],
            ['id' => 'landing', 'name' => 'Landing Page', 'category' => 'marketing', 'preview' => 'landing.jpg'],
            ['id' => 'portfolio', 'name' => 'Portfolyo', 'category' => 'personal', 'preview' => 'portfolio.jpg'],
            ['id' => 'blog', 'name' => 'Blog', 'category' => 'personal', 'preview' => 'blog.jpg'],
            ['id' => 'restaurant', 'name' => 'Restoran', 'category' => 'business', 'preview' => 'restaurant.jpg'],
            ['id' => 'realestate', 'name' => 'Emlak', 'category' => 'business', 'preview' => 'realestate.jpg']
        ];
    }
    
    /**
     * Blokları getir
     */
    public function getBlocks()
    {
        return [
            ['id' => 'hero', 'name' => 'Hero Section', 'icon' => 'fa-image', 'category' => 'layout'],
            ['id' => 'text', 'name' => 'Metin Bloğu', 'icon' => 'fa-font', 'category' => 'content'],
            ['id' => 'image', 'name' => 'Görsel', 'icon' => 'fa-photo', 'category' => 'media'],
            ['id' => 'video', 'name' => 'Video', 'icon' => 'fa-video', 'category' => 'media'],
            ['id' => 'button', 'name' => 'Buton', 'icon' => 'fa-hand-pointer', 'category' => 'interactive'],
            ['id' => 'features', 'name' => 'Özellikler', 'icon' => 'fa-star', 'category' => 'content'],
            ['id' => 'pricing', 'name' => 'Fiyatlandırma', 'icon' => 'fa-tags', 'category' => 'business'],
            ['id' => 'testimonials', 'name' => 'Referanslar', 'icon' => 'fa-comments', 'category' => 'social'],
            ['id' => 'faq', 'name' => 'SSS', 'icon' => 'fa-question-circle', 'category' => 'content'],
            ['id' => 'cta', 'name' => 'CTA', 'icon' => 'fa-bullhorn', 'category' => 'interactive'],
            ['id' => 'form', 'name' => 'Form', 'icon' => 'fa-edit', 'category' => 'interactive'],
            ['id' => 'map', 'name' => 'Harita', 'icon' => 'fa-map-marker-alt', 'category' => 'media'],
            ['id' => 'counter', 'name' => 'Sayaç', 'icon' => 'fa-calculator', 'category' => 'interactive'],
            ['id' => 'gallery', 'name' => 'Galeri', 'icon' => 'fa-images', 'category' => 'media'],
            ['id' => 'team', 'name' => 'Ekip', 'icon' => 'fa-users', 'category' => 'content'],
            ['id' => 'blog-grid', 'name' => 'Blog Grid', 'icon' => 'fa-newspaper', 'category' => 'content'],
            ['id' => 'separator', 'name' => 'Ayırıcı', 'icon' => 'fa-minus', 'category' => 'layout'],
            ['id' => 'spacer', 'name' => 'Boşluk', 'icon' => 'fa-arrows-alt-v', 'category' => 'layout']
        ];
    }
    
    /**
     * Kullanıcı projelerini getir
     */
    public function getUserProjects()
    {
        if (!$this->userId) return [];
        
        $stmt = $this->db->prepare("
            SELECT * FROM site_builder_projects 
            WHERE user_id = ? 
            ORDER BY updated_at DESC
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Proje oluştur
     */
    public function createProject($data)
    {
        $name = $data['name'] ?? 'Yeni Site';
        $template = $data['template'] ?? 'corporate';
        
        $stmt = $this->db->prepare("
            INSERT INTO site_builder_projects 
            (user_id, name, template, settings, pages, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $this->userId,
            $name,
            $template,
            json_encode($this->getDefaultSettings($template)),
            json_encode($this->getDefaultPages($template))
        ]);
        
        return [
            'success' => true,
            'project_id' => $this->db->lastInsertId()
        ];
    }
    
    /**
     * Proje ayarlarını güncelle
     */
    public function updateSettings($projectId, $settings)
    {
        $stmt = $this->db->prepare("
            UPDATE site_builder_projects 
            SET settings = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([json_encode($settings), $projectId, $this->userId]);
        return ['success' => $stmt->rowCount() > 0];
    }
    
    /**
     * Sayfa güncelle
     */
    public function updatePage($projectId, $pageKey, $pageData)
    {
        $project = $this->getProject($projectId);
        if (!$project) return ['success' => false];
        
        $pages = json_decode($project['pages'] ?? '{}', true);
        $pages[$pageKey] = $pageData;
        
        $stmt = $this->db->prepare("
            UPDATE site_builder_projects 
            SET pages = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([json_encode($pages), $projectId, $this->userId]);
        return ['success' => true];
    }
    
    /**
     * Header güncelle
     */
    public function updateHeader($projectId, $headerData)
    {
        $project = $this->getProject($projectId);
        if (!$project) return ['success' => false];
        
        $settings = json_decode($project['settings'] ?? '{}', true);
        $settings['header'] = $headerData;
        
        $stmt = $this->db->prepare("
            UPDATE site_builder_projects 
            SET settings = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([json_encode($settings), $projectId, $this->userId]);
        return ['success' => true];
    }
    
    /**
     * Footer güncelle
     */
    public function updateFooter($projectId, $footerData)
    {
        $project = $this->getProject($projectId);
        if (!$project) return ['success' => false];
        
        $settings = json_decode($project['settings'] ?? '{}', true);
        $settings['footer'] = $footerData;
        
        $stmt = $this->db->prepare("
            UPDATE site_builder_projects 
            SET settings = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([json_encode($settings), $projectId, $this->userId]);
        return ['success' => true];
    }
    
    /**
     * Menü güncelle
     */
    public function updateMenu($projectId, $menuData)
    {
        $project = $this->getProject($projectId);
        if (!$project) return ['success' => false];
        
        $settings = json_decode($project['settings'] ?? '{}', true);
        $settings['menu'] = $menuData;
        
        $stmt = $this->db->prepare("
            UPDATE site_builder_projects 
            SET settings = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([json_encode($settings), $projectId, $this->userId]);
        return ['success' => true];
    }
    
    /**
     * ZIP export
     */
    public function exportZip($projectId)
    {
        $project = $this->getProject($projectId);
        if (!$project) {
            return ['success' => false, 'error' => 'Proje bulunamadı'];
        }
        
        $settings = json_decode($project['settings'] ?? '{}', true);
        $licenseType = $settings['license_type'] ?? 'single_domain';
        
        // ZIP oluştur (simülasyon)
        $files = $this->generateSiteFiles($project);
        
        $this->saveExportHistory($projectId, 'zip', count($files));
        
        return [
            'success' => true,
            'message' => 'ZIP dosyası hazır',
            'files_count' => count($files),
            'license_type' => $licenseType,
            'license_hash' => $this->generateLicenseHash($project, $licenseType)
        ];
    }
    
    /**
     * Lisans kontrolü
     */
    public function checkLicense($projectId)
    {
        $project = $this->getProject($projectId);
        if (!$project) {
            return ['valid' => false, 'error' => 'Proje bulunamadı'];
        }
        
        $settings = json_decode($project['settings'] ?? '{}', true);
        $licenseType = $settings['license_type'] ?? 'single_domain';
        
        return [
            'valid' => true,
            'type' => $licenseType,
            'domain' => $settings['licensed_domain'] ?? null,
            'hash' => $settings['license_hash'] ?? null
        ];
    }
    
    /**
     * AI içerik oluştur (simülasyon)
     */
    public function generateAiContent($type, $data)
    {
        $templates = [
            'about' => "Firmamız, müşteri memnuniyetini ön planda tutarak kaliteli hizmet sunmayı ilke edinmiştir.",
            'contact' => "Bize ulaşmak için aşağıdaki bilgileri kullanabilirsiniz.",
            'service' => "Sunduğumuz hizmetler, en son teknolojiler ve uzman kadromuz ile sizlere en iyi çözümleri sunmak için tasarlanmıştır."
        ];
        
        return [
            'success' => true,
            'content' => $templates[$type] ?? "AI tarafından oluşturulan içerik.",
            'type' => $type
        ];
    }
    
    /**
     * Yardımcı metodlar
     */
    private function getProject($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM site_builder_projects 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch();
    }
    
    private function getDefaultSettings($template)
    {
        return [
            'site_name' => 'Web Sitem',
            'tagline' => 'Profesyonel web sitesi',
            'logo' => null,
            'favicon' => null,
            'primary_color' => '#2196F3',
            'secondary_color' => '#ffffff',
            'font_family' => 'Poppins',
            'header_style' => 'default',
            'footer_style' => 'default',
            'menu_type' => 'horizontal',
            'license_type' => 'single_domain',
            'licensed_domain' => null,
            'license_hash' => null,
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'google_analytics' => null,
            'facebook_pixel' => null
        ];
    }
    
    private function getDefaultPages($template)
    {
        return [
            'home' => [
                'title' => 'Ana Sayfa',
                'slug' => '/',
                'blocks' => [
                    ['type' => 'hero', 'content' => ['title' => 'Hoş Geldiniz', 'subtitle' => 'Profesyonel web sitesi oluşturun']],
                    ['type' => 'features', 'content' => ['title' => 'Özelliklerimiz', 'items' => []]]
                ]
            ],
            'about' => [
                'title' => 'Hakkımızda',
                'slug' => '/hakkimizda',
                'blocks' => [['type' => 'text', 'content' => ['title' => 'Hakkımızda', 'body' => '']]]
            ],
            'contact' => [
                'title' => 'İletişim',
                'slug' => '/iletisim',
                'blocks' => [['type' => 'form', 'content' => []]]
            ]
        ];
    }
    
    private function generateSiteFiles($project)
    {
        $settings = json_decode($project['settings'] ?? '{}', true);
        $pages = json_decode($project['pages'] ?? '{}', true);
        
        $files = [];
        
        // index.html
        $files['index.html'] = $this->generateHtml($settings, $pages['home'] ?? []);
        
        // Diğer sayfalar
        foreach ($pages as $key => $page) {
            if ($key === 'home') continue;
            $files[$key . '.html'] = $this->generateHtml($settings, $page);
        }
        
        // CSS
        $files['css/style.css'] = $this->generateCss($settings);
        
        // JS
        $files['js/main.js'] = $this->generateJs();
        
        return $files;
    }
    
    private function generateHtml($settings, $page)
    {
        $blocks = $page['blocks'] ?? [];
        $blocksHtml = '';
        
        foreach ($blocks as $block) {
            $blocksHtml .= $this->renderBlock($block);
        }
        
        return '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . ($page['title'] ?? 'Web Sitem') . ' - ' . ($settings['site_name'] ?? '') . '</title>
    <meta name="description" content="' . ($settings['seo_description'] ?? '') . '">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>' . ($settings['site_name'] ?? '') . '</nav>
    </header>
    <main>' . $blocksHtml . '</main>
    <footer>
        <p>&copy; ' . date('Y') . ' ' . ($settings['site_name'] ?? '') . '</p>
    </footer>
    <script src="js/main.js"></script>
</body>
</html>';
    }
    
    private function renderBlock($block)
    {
        $type = $block['type'] ?? '';
        $content = $block['content'] ?? [];
        
        switch ($type) {
            case 'hero':
                return '<section class="hero"><h1>' . ($content['title'] ?? '') . '</h1><p>' . ($content['subtitle'] ?? '') . '</p></section>';
            case 'text':
                return '<section class="text-block"><h2>' . ($content['title'] ?? '') . '</h2><p>' . ($content['body'] ?? '') . '</p></section>';
            case 'features':
                return '<section class="features"><h2>' . ($content['title'] ?? '') . '</h2></section>';
            default:
                return '<section class="block-' . $type . '"></section>';
        }
    }
    
    private function generateCss($settings)
    {
        $primary = $settings['primary_color'] ?? '#2196F3';
        return "body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; }
header { background: {$primary}; color: white; padding: 20px; }
main { min-height: 60vh; padding: 20px; }
footer { background: #333; color: white; padding: 20px; text-align: center; }
.hero { text-align: center; padding: 60px 20px; background: {$primary}; color: white; }
.features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 40px 0; }";
    }
    
    private function generateJs()
    {
        return "// SiteBuilder Pro - Generated JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Site loaded successfully');
});";
    }
    
    private function generateLicenseHash($project, $type)
    {
        $data = $project['id'] . $project['name'] . $type . date('Y-m-d');
        return hash('sha256', $data);
    }
    
    private function saveExportHistory($projectId, $type, $fileCount)
    {
        $stmt = $this->db->prepare("
            INSERT INTO site_builder_exports 
            (project_id, export_type, file_count, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$projectId, $type, $fileCount]);
    }
}
