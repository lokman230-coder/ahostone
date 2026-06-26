<?php
namespace Ahost\Modules\Builder\MobileBuilder;

class MobileBuilderController
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
        
        return [
            'templates' => $templates,
            'projects' => $projects
        ];
    }
    
    /**
     * Şablonları getir
     */
    public function getTemplates()
    {
        return [
            [
                'id' => 'blank',
                'name' => 'Boş Uygulama',
                'icon' => 'fa-gem',
                'description' => 'Sıfırdan başlayın',
                'category' => 'general',
                'features' => ['Özel tasarım', 'Sınırsız sayfa', 'Tüm özellikler']
            ],
            [
                'id' => 'realestate',
                'name' => 'Emlak Uygulaması',
                'icon' => 'fa-home',
                'description' => 'Gayrimenkul satış ve kiralama',
                'category' => 'business',
                'features' => ['İlan yönetimi', 'Harita entegrasyonu', 'Filtreleme', 'Favoriler']
            ],
            [
                'id' => 'restaurant',
                'name' => 'Restoran Uygulaması',
                'icon' => 'fa-utensils',
                'description' => 'Restoran ve cafe için',
                'category' => 'business',
                'features' => ['Menü sistemi', 'Rezervasyon', 'Sipariş', 'Promosyonlar']
            ],
            [
                'id' => 'radio',
                'name' => 'Radyo Uygulaması',
                'icon' => 'fa-radio',
                'description' => 'Radyo ve podcast için',
                'category' => 'media',
                'features' => ['Canlı dinleme', 'Podcast', 'Program rehberi', 'Bildirimler']
            ],
            [
                'id' => 'corporate',
                'name' => 'Kurumsal Uygulama',
                'icon' => 'fa-building',
                'description' => 'Şirketler için profesyonel uygulama',
                'category' => 'business',
                'features' => ['Hakkımızda', 'Hizmetler', 'Blog', 'İletişim formu']
            ],
            [
                'id' => 'ecommerce',
                'name' => 'E-Ticaret Uygulaması',
                'icon' => 'fa-shopping-cart',
                'description' => 'Online alışveriş için',
                'category' => 'business',
                'features' => ['Ürün kataloğu', 'Sepet', 'Ödeme', 'Sipariş takibi']
            ],
            [
                'id' => 'news',
                'name' => 'Haber Uygulaması',
                'icon' => 'fa-newspaper',
                'description' => 'Haber ve medya için',
                'category' => 'media',
                'features' => ['Kategori sistemi', 'Bildirimler', 'Video haber', 'Arşiv']
            ],
            [
                'id' => 'education',
                'name' => 'Eğitim Uygulaması',
                'icon' => 'fa-graduation-cap',
                'description' => 'Kurs ve eğitim platformu',
                'category' => 'education',
                'features' => ['Kurslar', 'Video içerik', 'Sınav sistemi', 'Sertifika']
            ]
        ];
    }
    
    /**
     * Kullanıcı projelerini getir
     */
    public function getUserProjects()
    {
        if (!$this->userId) return [];
        
        $stmt = $this->db->prepare("
            SELECT * FROM mobile_builder_projects 
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
        $name = $data['name'] ?? 'Yeni Uygulama';
        $template = $data['template'] ?? 'blank';
        $packageName = $this->generatePackageName($name);
        
        $stmt = $this->db->prepare("
            INSERT INTO mobile_builder_projects 
            (user_id, name, template, package_name, settings, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $this->userId,
            $name,
            $template,
            $packageName,
            json_encode($this->getDefaultSettings($template))
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
            UPDATE mobile_builder_projects 
            SET settings = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([json_encode($settings), $projectId, $this->userId]);
        
        return ['success' => $stmt->rowCount() > 0];
    }
    
    /**
     * APK oluştur (simülasyon - gerçek build için sunucuda Gradle gerekli)
     */
    public function buildApk($projectId)
    {
        $project = $this->getProject($projectId);
        if (!$project) {
            return ['success' => false, 'error' => 'Proje bulunamadı'];
        }
        
        // Build log oluştur
        $buildLog = [
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'completed',
            'output' => [
                'app-debug.apk' => 'Uygulamanız hazır!',
                'size' => '~15 MB'
            ]
        ];
        
        // Build history kaydet
        $this->saveBuildHistory($projectId, 'apk', $buildLog);
        
        return [
            'success' => true,
            'message' => 'APK oluşturuldu (Demo modu - gerçek build için sunucu yapılandırması gerekli)',
            'download_url' => '#apk-' . $projectId,
            'build_log' => $buildLog
        ];
    }
    
    /**
     * AAB oluştur
     */
    public function buildAab($projectId)
    {
        $project = $this->getProject($projectId);
        if (!$project) {
            return ['success' => false, 'error' => 'Proje bulunamadı'];
        }
        
        $buildLog = [
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'completed',
            'output' => [
                'app.aab' => 'Android App Bundle hazır!',
                'size' => '~12 MB'
            ]
        ];
        
        $this->saveBuildHistory($projectId, 'aab', $buildLog);
        
        return [
            'success' => true,
            'message' => 'AAB oluşturuldu (Demo modu)',
            'download_url' => '#aab-' . $projectId,
            'build_log' => $buildLog
        ];
    }
    
    /**
     * Proje kaydet (PWA/Web)
     */
    public function exportPwa($projectId)
    {
        $project = $this->getProject($projectId);
        if (!$project) {
            return ['success' => false, 'error' => 'Proje bulunamadı'];
        }
        
        return [
            'success' => true,
            'message' => 'PWA kaynak kodu hazır',
            'files' => $this->generatePwaFiles($project)
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
            'domain' => $settings['licensed_domain'] ?? 'Belirtilmemiş',
            'expires' => $settings['license_expires'] ?? null
        ];
    }
    
    /**
     * Yardımcı metodlar
     */
    private function getProject($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM mobile_builder_projects 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch();
    }
    
    private function generatePackageName($name)
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', ucwords($name));
        return 'com.' . strtolower(substr($clean, 0, 10)) . '.app';
    }
    
    private function getDefaultSettings($template)
    {
        $defaults = [
            'app_name' => 'Uygulamam',
            'package_name' => 'com.example.app',
            'version_code' => 1,
            'version_name' => '1.0.0',
            'logo' => null,
            'splash_screen' => null,
            'primary_color' => '#2196F3',
            'secondary_color' => '#ffffff',
            'firebase_enabled' => false,
            'firebase_config' => [],
            'api_url' => '',
            'menu_items' => [],
            'left_menu' => [],
            'bottom_menu' => [
                ['id' => 'home', 'title' => 'Ana Sayfa', 'icon' => 'home', 'page' => 'home'],
                ['id' => 'search', 'title' => 'Ara', 'icon' => 'search', 'page' => 'search'],
                ['id' => 'profile', 'title' => 'Profil', 'icon' => 'person', 'page' => 'profile']
            ]
        ];
        
        // Şablona göre özelleştir
        if ($template === 'realestate') {
            $defaults['menu_items'] = [
                ['title' => 'İlanlar', 'page' => 'listings'],
                ['title' => 'Favoriler', 'page' => 'favorites'],
                ['title' => 'Harita', 'page' => 'map']
            ];
        } elseif ($template === 'restaurant') {
            $defaults['menu_items'] = [
                ['title' => 'Menü', 'page' => 'menu'],
                ['title' => 'Rezervasyon', 'page' => 'reservation'],
                ['title' => 'Sipariş', 'page' => 'order']
            ];
        } elseif ($template === 'radio') {
            $defaults['menu_items'] = [
                ['title' => 'Canlı', 'page' => 'live'],
                ['title' => 'Programlar', 'page' => 'programs'],
                ['title' => 'Podcast', 'page' => 'podcast']
            ];
        }
        
        return $defaults;
    }
    
    private function saveBuildHistory($projectId, $type, $log)
    {
        $stmt = $this->db->prepare("
            INSERT INTO mobile_builder_builds 
            (project_id, build_type, status, build_log, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$projectId, $type, $log['status'], json_encode($log)]);
    }
    
    private function generatePwaFiles($project)
    {
        $settings = json_decode($project['settings'] ?? '{}', true);
        
        return [
            'index.html' => $this->generateIndexHtml($settings),
            'manifest.json' => $this->generateManifest($settings),
            'sw.js' => $this->generateServiceWorker(),
            'style.css' => $this->generateStyles($settings)
        ];
    }
    
    private function generateIndexHtml($settings)
    {
        return '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="' . ($settings['primary_color'] ?? '#2196F3') . '">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="style.css">
    <title>' . ($settings['app_name'] ?? 'Uygulamam') . '</title>
</head>
<body>
    <div id="app">
        <header style="background:' . ($settings['primary_color'] ?? '#2196F3') . ';">
            <h1>' . ($settings['app_name'] ?? 'Uygulamam') . '</h1>
        </header>
        <main>
            <p>PWA içeriği burada gösterilecek.</p>
        </main>
    </div>
    <script src="sw.js"></script>
</body>
</html>';
    }
    
    private function generateManifest($settings)
    {
        return json_encode([
            'name' => $settings['app_name'] ?? 'Uygulamam',
            'short_name' => substr($settings['app_name'] ?? 'App', 0, 12),
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => $settings['primary_color'] ?? '#2196F3',
            'theme_color' => $settings['primary_color'] ?? '#2196F3',
            'icons' => []
        ], JSON_PRETTY_PRINT);
    }
    
    private function generateServiceWorker()
    {
        return "const CACHE_NAME = 'v1';
const urlsToCache = ['/', '/index.html'];

self.addEventListener('install', e => {
    e.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache)));
});

self.addEventListener('fetch', e => {
    e.respondWith(caches.match(e.request).then(r => r || fetch(e.request)));
});";
    }
    
    private function generateStyles($settings)
    {
        return "body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; }
header { padding: 20px; color: white; text-align: center; }
main { padding: 20px; }
";
    }
}
