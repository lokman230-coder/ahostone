<?php
/**
 * MobileBuilder APK/AAB Build Service
 * Ahost One v25.0.0 RC20
 * 
 * Gerçek APK/AAB derleme sistemi
 * Gradle, Java JDK, Android SDK kontrolü
 */

namespace Ahost\Modules\Builder\MobileBuilder;

class MobileBuildService
{
    private $db;
    private $userId;
    private $buildDir;
    
    // Sistem yolları (sunucu yapılandırmasına göre değişebilir)
    const BUILD_BASE_DIR = '/var/www/ahost-builds/mobile-builder';
    const ANDROID_TEMPLATE_DIR = '/var/www/ahost-builds/android-templates';
    
    // Build timeout (saniye)
    const BUILD_TIMEOUT = 900; // 15 dakika
    
    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
        
        // Build dizinlerini oluştur
        $this->ensureDirectories();
    }
    
    /**
     * Dizinleri oluştur
     */
    private function ensureDirectories()
    {
        $this->buildDir = defined('AHOST_BUILD_DIR') ? AHOST_BUILD_DIR : dirname(__DIR__, 4) . '/storage/builds/mobile-builder';
        
        $dirs = [
            $this->buildDir,
            $this->buildDir . '/apk',
            $this->buildDir . '/aab',
            $this->buildDir . '/logs',
            $this->buildDir . '/queue',
            $this->buildDir . '/temp',
            $this->buildDir . '/keys',
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Sistem gereksinimlerini kontrol et
     */
    public function checkSystemRequirements(): array
    {
        $results = [];
        
        // Java JDK
        $javaVersion = $this->checkJava();
        $results['java'] = [
            'installed' => !empty($javaVersion),
            'version' => $javaVersion,
            'required' => '17+',
            'ok' => version_compare($javaVersion, '17', '>='),
        ];
        
        // Gradle
        $gradleVersion = $this->checkGradle();
        $results['gradle'] = [
            'installed' => !empty($gradleVersion),
            'version' => $gradleVersion,
            'required' => '8.0+',
            'ok' => version_compare($gradleVersion, '8.0', '>='),
        ];
        
        // Android SDK
        $sdkInfo = $this->checkAndroidSDK();
        $results['android_sdk'] = [
            'installed' => $sdkInfo['installed'],
            'version' => $sdkInfo['version'],
            'path' => $sdkInfo['path'],
            'required' => '33.0.0+',
            'ok' => $sdkInfo['installed'],
        ];
        
        // Android NDK (opsiyonel)
        $results['android_ndk'] = [
            'installed' => is_dir(getenv('ANDROID_NDK_HOME') ?: ($sdkInfo['path'] . '/ndk')),
            'required' => false,
        ];
        
        // CMake (opsiyonel)
        $cmakeVersion = trim(shell_exec('cmake --version 2>/dev/null | head -1'));
        $results['cmake'] = [
            'installed' => !empty($cmakeVersion),
            'version' => $cmakeVersion,
            'required' => false,
        ];
        
        // Build dizini yazılabilir mi?
        $results['build_dir'] = [
            'writable' => is_writable($this->buildDir),
            'path' => $this->buildDir,
        ];
        
        $results['all_ok'] = $results['java']['ok'] && $results['gradle']['ok'] && $results['android_sdk']['ok'];
        
        return $results;
    }
    
    /**
     * Java versiyonunu kontrol et
     */
    private function checkJava(): string
    {
        $output = shell_exec('java -version 2>&1');
        if (preg_match('/version "?(\d+\.\d+\.\d+)/', $output, $m)) {
            return $m[1];
        }
        return '';
    }
    
    /**
     * Gradle versiyonunu kontrol et
     */
    private function checkGradle(): string
    {
        $output = shell_exec('gradle --version 2>&1');
        if (preg_match('/Gradle (\d+\.\d+)/', $output, $m)) {
            return $m[1];
        }
        return '';
    }
    
    /**
     * Android SDK kontrol et
     */
    private function checkAndroidSDK(): array
    {
        $sdkPath = getenv('ANDROID_HOME') ?: getenv('ANDROID_SDK_ROOT') ?: '/opt/android-sdk';
        $buildTools = $sdkPath . '/build-tools';
        
        $version = '';
        if (is_dir($buildTools)) {
            $versions = glob($buildTools . '/*');
            if (!empty($versions)) {
                $version = basename(end($versions));
            }
        }
        
        return [
            'installed' => is_dir($sdkPath) && !empty($version),
            'version' => $version,
            'path' => $sdkPath,
        ];
    }
    
    /**
     * Proje bilgilerini al
     */
    public function getProject(int $projectId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM module_mobilebuilder_projects WHERE id = ?");
        $stmt->execute([$projectId]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Build başlat
     */
    public function startBuild(int $projectId, string $buildType = 'apk'): array
    {
        // Proje kontrolü
        $project = $this->getProject($projectId);
        if (!$project) {
            return ['success' => false, 'error' => 'Proje bulunamadı'];
        }
        
        // Yetki kontrolü
        if (!$this->canBuild($project)) {
            return ['success' => false, 'error' => 'Bu projeyi derleme yetkiniz yok'];
        }
        
        // Sistem gereksinimleri
        $sysReq = $this->checkSystemRequirements();
        if (!$sysReq['all_ok']) {
            return [
                'success' => false, 
                'error' => 'Sistem gereksinimleri karşılanmıyor. Lütfen sunucu ayarlarını kontrol edin.',
                'details' => $sysReq
            ];
        }
        
        // Build ID oluştur
        $buildId = $this->createBuildRecord($projectId, $buildType);
        
        // Arka planda build başlat
        $this->runBuildAsync($buildId, $project, $buildType);
        
        return [
            'success' => true,
            'build_id' => $buildId,
            'status' => 'building',
            'message' => 'Build başlatıldı',
            'log_url' => url('mobilebuilder/build-log?id=' . $buildId),
        ];
    }
    
    /**
     * Build kaydı oluştur
     */
    private function createBuildRecord(int $projectId, string $buildType): int
    {
        $logData = json_encode([
            'started' => date('Y-m-d H:i:s'),
            'steps' => [],
            'system_info' => $this->checkSystemRequirements(),
        ]);
        
        $stmt = $this->db->prepare("
            INSERT INTO module_mobilebuilder_builds 
            (project_id, build_type, status, build_log, created_at)
            VALUES (?, ?, 'building', ?, NOW())
        ");
        $stmt->execute([$projectId, $buildType, $logData]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Build'i asenkron çalıştır
     */
    private function runBuildAsync(int $buildId, array $project, string $buildType)
    {
        $projectJson = json_encode([
            'id' => $project['id'],
            'name' => $project['name'],
            'user_id' => $project['user_id'],
            'package_name' => $project['package_name'],
            'template' => $project['template'],
            'settings' => json_decode($project['settings'] ?? '{}', true),
        ]);
        
        $buildDir = $this->buildDir;
        
        // Arka planda PHP scripti çalıştır
        $scriptPath = __DIR__ . '/BuildRunner.php';
        
        $cmd = sprintf(
            'cd %s && php %s %d %d "%s" "%s" >> %s/logs/%d.log 2>&1 &',
            escapeshellarg($buildDir),
            escapeshellarg($scriptPath),
            $buildId,
            $project['user_id'],
            addslashes($projectJson),
            $buildType,
            escapeshellarg($buildDir),
            $buildId
        );
        
        exec($cmd);
    }
    
    /**
     * Build durumunu güncelle
     */
    public function updateBuildStatus(int $buildId, string $status, array $log = []): bool
    {
        $downloadPath = null;
        $fileSize = null;
        
        if ($status === 'completed') {
            $downloadPath = $this->getBuildOutputPath($buildId);
            $fileSize = file_exists($downloadPath) ? filesize($downloadPath) : 0;
        }
        
        $stmt = $this->db->prepare("
            UPDATE module_mobilebuilder_builds 
            SET status = ?, build_log = ?, download_path = ?, file_size = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $status,
            json_encode($log),
            $downloadPath,
            $fileSize,
            $buildId
        ]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Build çıktı yolu
     */
    private function getBuildOutputPath(int $buildId): string
    {
        return $this->buildDir . '/apk/build_' . $buildId . '.apk';
    }
    
    /**
     * Build log dosyasını oku
     */
    public function getBuildLog(int $buildId): array
    {
        $logFile = $this->buildDir . '/logs/' . $buildId . '.log';
        
        if (file_exists($logFile)) {
            return [
                'exists' => true,
                'content' => file_get_contents($logFile),
                'tail' => $this->getLastLines($logFile, 100),
            ];
        }
        
        return ['exists' => false, 'content' => '', 'tail' => ''];
    }
    
    /**
     * Son satırları al
     */
    private function getLastLines(string $file, int $lines): string
    {
        if (!file_exists($file)) return '';
        
        $content = file($file);
        if (!$content) return '';
        
        $slice = array_slice($content, -$lines);
        return implode('', $slice);
    }
    
    /**
     * Build durumunu al
     */
    public function getBuildStatus(int $buildId): array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, p.name as project_name, p.user_id as owner_id
            FROM module_mobilebuilder_builds b
            LEFT JOIN module_mobilebuilder_projects p ON b.project_id = p.id
            WHERE b.id = ?
        ");
        $stmt->execute([$buildId]);
        $build = $stmt->fetch();
        
        if (!$build) {
            return ['error' => 'Build bulunamadı'];
        }
        
        return [
            'id' => $build['id'],
            'project_id' => $build['project_id'],
            'project_name' => $build['project_name'],
            'build_type' => $build['build_type'],
            'status' => $build['status'],
            'download_url' => $build['download_path'] && $build['status'] === 'completed' 
                ? url('mobilebuilder/download?id=' . $buildId) 
                : null,
            'file_size' => $build['file_size'],
            'created_at' => $build['created_at'],
            'log_url' => url('mobilebuilder/build-log?id=' . $buildId),
            'build_log' => json_decode($build['build_log'] ?? '{}', true),
        ];
    }
    
    /**
     * Projenin build history'sini al
     */
    public function getBuildHistory(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM module_mobilebuilder_builds 
            WHERE project_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$projectId]);
        
        $builds = $stmt->fetchAll();
        
        return array_map(function($b) {
            return [
                'id' => $b['id'],
                'build_type' => $b['build_type'],
                'status' => $b['status'],
                'file_size' => $b['file_size'],
                'download_url' => $b['download_path'] && $b['status'] === 'completed'
                    ? url('mobilebuilder/download?id=' . $b['id'])
                    : null,
                'created_at' => $b['created_at'],
            ];
        }, $builds);
    }
    
    /**
     * Yetki kontrolü - kullanıcı kendi projesini mi build ediyor?
     */
    public function canBuild(array $project): bool
    {
        // Admin her zaman build edebilir
        if (!empty($_SESSION['admin_id'])) {
            return true;
        }
        
        // Kullanıcı kendi projesini mi build ediyor?
        return ($project['user_id'] ?? 0) == $this->userId;
    }
    
    /**
     * Build indirebilir mi?
     */
    public function canDownload(int $buildId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT b.*, p.user_id 
            FROM module_mobilebuilder_builds b
            LEFT JOIN module_mobilebuilder_projects p ON b.project_id = p.id
            WHERE b.id = ?
        ");
        $stmt->execute([$buildId]);
        $build = $stmt->fetch();
        
        if (!$build) return false;
        
        // Admin her zaman indirebilir
        if (!empty($_SESSION['admin_id'])) {
            return true;
        }
        
        return ($build['user_id'] ?? 0) == $userId && $build['status'] === 'completed';
    }
    
    /**
     * Build dosyasını indir
     */
    public function downloadBuild(int $buildId): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM module_mobilebuilder_builds WHERE id = ?");
        $stmt->execute([$buildId]);
        $build = $stmt->fetch();
        
        if (!$build || $build['status'] !== 'completed' || empty($build['download_path'])) {
            return false;
        }
        
        $filePath = $build['download_path'];
        if (!file_exists($filePath)) {
            return false;
        }
        
        $extension = $build['build_type'] === 'aab' ? 'aab' : 'apk';
        $filename = 'app-' . $buildId . '.' . $extension;
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($filePath);
        return true;
    }
    
    /**
     * Build queue listesini al
     */
    public function getQueue(): array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, p.name as project_name, p.package_name
            FROM module_mobilebuilder_builds b
            LEFT JOIN module_mobilebuilder_projects p ON b.project_id = p.id
            WHERE b.status IN ('pending', 'building')
            ORDER BY b.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Build iptal et
     */
    public function cancelBuild(int $buildId): bool
    {
        // Sadece pending veya building durumundakiler iptal edilebilir
        $stmt = $this->db->prepare("
            UPDATE module_mobilebuilder_builds 
            SET status = 'failed', build_log = '{\"cancelled\":true,\"cancelled_at\":\"' . NOW() . '\"}'
            WHERE id = ? AND status IN ('pending', 'building')
        ");
        $stmt->execute([$buildId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Build log güncelle (log dosyasına ekle)
     */
    public function appendToLog(int $buildId, string $message): void
    {
        $logFile = $this->buildDir . '/logs/' . $buildId . '.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }
    
    /**
     * En son build'i al
     */
    public function getLatestBuild(int $projectId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM module_mobilebuilder_builds 
            WHERE project_id = ? AND status = 'completed'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetch() ?: null;
    }
}
