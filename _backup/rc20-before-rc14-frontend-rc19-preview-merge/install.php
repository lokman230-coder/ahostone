<?php
/**
 * Ahost One v25.0.0 RC5 - Kurulum
 * Premium SaaS Hosting Platform
 */
session_start();
$root = __DIR__;
$cfgPath = $root . '/config/config.php';
$schemaPath = $root . '/database/fresh-install.sql';

// Sistem gereksinimleri kontrolü
$requirements = [
    'php_version' => ['ok' => version_compare(PHP_VERSION, '8.1', '>='), 'current' => PHP_VERSION, 'required' => '8.1+'],
    'pdo_mysql' => ['ok' => extension_loaded('pdo_mysql'), 'current' => extension_loaded('pdo_mysql') ? 'Yüklü' : 'Eksik', 'required' => 'Gerekli'],
    'curl' => ['ok' => extension_loaded('curl'), 'current' => extension_loaded('curl') ? 'Yüklü' : 'Eksik', 'required' => 'Önerilen'],
    'gd' => ['ok' => extension_loaded('gd'), 'current' => extension_loaded('gd') ? 'Yüklü' : 'Eksik', 'required' => 'Önerilen'],
    'storage' => ['ok' => is_writable($root . '/storage') || @mkdir($root . '/storage', 0755, true), 'current' => is_writable($root . '/storage') ? 'Yazılabilir' : 'Oluşturuldu', 'required' => 'Gerekli'],
];
$allOk = $requirements['php_version']['ok'] && $requirements['pdo_mysql']['ok'] && $requirements['storage']['ok'];

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function detect_base_url() {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return rtrim($scheme . '://' . $host, '/');
}
function split_sql($sql) {
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $parts = []; $buf = ''; $inStr = false; $len = strlen($sql);
    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        if (($ch === "'" || $ch === '"') && ($i === 0 || $sql[$i-1] !== '\\')) {
            $inStr = !$inStr;
        }
        if ($ch === ';' && !$inStr) {
            $s = trim($buf);
            if ($s !== '') $parts[] = $s;
            $buf = '';
        } else {
            $buf .= $ch;
        }
    }
    $s = trim($buf);
    if ($s !== '') $parts[] = $s;
    return $parts;
}
function save_setting($pdo, $k, $v) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(160) UNIQUE NOT NULL, setting_value LONGTEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->prepare("INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$k, (string)$v]);
    } catch (Throwable $e) {}
}

$done = false; $error = ''; $warnings = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = (string)($_POST['db_pass'] ?? '');
    $baseUrl = rtrim(trim($_POST['base_url'] ?? ''), '/');
    if ($baseUrl === '') $baseUrl = detect_base_url();
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $securityAnswer = (string)($_POST['security_answer'] ?? '');
    $clean = !empty($_POST['clean_install']);
    
    try {
        if ($dbName === '' || $dbUser === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminPass) < 10 || strlen($securityAnswer) < 4) {
            throw new Exception('Tüm alanları doldurun. Şifre en az 10, güvenlik cevabı en az 4 karakter olmalı.');
        }
        
        // DB baglantisi
        $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        if ($clean) { $pdo->exec("DROP DATABASE IF EXISTS `" . str_replace('`', '``', $dbName) . "`"); }
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', $dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo->exec("USE `" . str_replace('`', '``', $dbName) . "`");
        
        if (!file_exists($schemaPath)) { throw new Exception('database/fresh-install.sql bulunamadı!'); }
        
        // SQL calistir
        $runSqlFile = function($file) use ($pdo) {
            $sql = file_get_contents($file);
            foreach (split_sql($sql) as $stmt) {
                $t = ltrim($stmt);
                if ($t === '' || str_starts_with($t, '/*')) continue;
                if (preg_match('/^SOURCE\s+/i', $t)) continue;
                if (preg_match('/^CREATE\s+DATABASE\s+/i', $t)) continue;
                if (preg_match('/^USE\s+/i', $t)) continue;
                if (preg_match('/^LOCK\s+TABLES/i', $t) || preg_match('/^UNLOCK\s+TABLES/i', $t)) continue;
                if (preg_match('/^ALTER\s+TABLE\s+(.+?)\s+ADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\s+(.+)$/is', $t, $m)) {
                    $stmt = 'ALTER TABLE ' . $m[1] . ' ADD COLUMN ' . $m[2];
                }
                try { $pdo->exec($stmt); }
                catch (Throwable $e) {
                    $msg = strtolower($e->getMessage());
                    $safe = ['already exists', 'duplicate column', 'duplicate entry', 'duplicate key', 'check that column/key exists', 'can\'t drop', 'cannot drop', 'unknown column', 'multiple primary key'];
                    $ignore = false;
                    foreach ($safe as $s) { if (strpos($msg, $s) !== false) { $ignore = true; break; } }
                    if (!$ignore) throw $e;
                }
            }
        };
        
        $runSqlFile($schemaPath);
        
        // Modul SQL - v25 RC4: glob("**") PHP'de gerçek recursive davranmadığı için RecursiveIterator kullanılır.
        $moduleSqlFiles = [];
        $moduleRoot = $root . '/modules';
        if (is_dir($moduleRoot)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($moduleRoot, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $fileInfo) {
                if ($fileInfo->isFile() && strtolower($fileInfo->getFilename()) === 'install.sql') {
                    $moduleSqlFiles[] = $fileInfo->getPathname();
                }
            }
        }
        natsort($moduleSqlFiles);
        foreach ($moduleSqlFiles as $msf) { $runSqlFile($msf); }
        
        // Migration SQL
        $migrationDir = $root . '/database/migrations';
        if (is_dir($migrationDir)) {
            $migrationFiles = glob($migrationDir . '/*.sql') ?: [];
            natsort($migrationFiles);
            foreach ($migrationFiles as $mf) { $runSqlFile($mf); }
        }
        
        // Admin kullanici
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $answerHash = password_hash($securityAnswer, PASSWORD_DEFAULT);
        try { $pdo->exec("ALTER TABLE admins ADD PRIMARY KEY (id)"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE admins MODIFY id INT(11) NOT NULL AUTO_INCREMENT"); } catch (Throwable $e) {}
        $s = $pdo->prepare("SELECT id FROM admins WHERE id=1 OR email=? ORDER BY id ASC LIMIT 1");
        $s->execute([$adminEmail]);
        $existingId = $s->fetchColumn();
        if ($existingId) {
            $pdo->prepare("UPDATE admins SET name='Ahost Admin', email=?, password_hash=?, security_question='Güvenlik cevabı nedir?', security_answer_hash=?, role='super_admin' WHERE id=?")
                ->execute([$adminEmail, $hash, $answerHash, $existingId]);
        } else {
            $pdo->prepare("INSERT INTO admins (id,name,email,password_hash,security_question,security_answer_hash,role,created_at) VALUES (1,'Ahost Admin',?,?,?,?,'super_admin',NOW())")
                ->execute([$adminEmail, $hash, 'Güvenlik cevabı nedir?', $answerHash]);
        }
        
        // Ayarlar
        foreach (['site_url' => $baseUrl, 'base_url' => $baseUrl, 'ahost_version' => '25.0.0-rc5', 'setup_wizard_completed' => '0', 'setup_wizard_dismissed' => '0'] as $k => $v) {
            save_setting($pdo, $k, $v);
        }
        
        // Config
        $config = "<?php\nreturn [\n 'app_name'=>'Ahost One',\n 'version'=>'25.0.0-rc5',\n 'asset_version'=>'25.0.0-rc5',\n 'base_url'=>" . var_export($baseUrl, true) . ",\n 'db'=>['host'=>" . var_export($dbHost, true) . ",'name'=>" . var_export($dbName, true) . ",'user'=>" . var_export($dbUser, true) . ",'pass'=>" . var_export($dbPass, true) . ",'charset'=>'utf8mb4'],\n 'whm'=>['enabled'=>false,'hostname'=>'','username'=>'','api_token'=>''],\n 'security'=>['install_file_auto_renamed'=>true],\n];\n";
        file_put_contents($cfgPath, $config);
        
        // Storage dizinleri
        $dirs = [
            $root . '/storage', $root . '/storage/builds', $root . '/storage/builds/mobile-builder',
            $root . '/storage/builds/mobile-builder/apk', $root . '/storage/builds/mobile-builder/aab',
            $root . '/storage/builds/mobile-builder/logs', $root . '/storage/builds/mobile-builder/temp',
            $root . '/storage/builds/mobile-builder/keys', $root . '/storage/uploads',
            $root . '/storage/exports', $root . '/storage/attachments', $root . '/storage/cache',
        ];
        foreach ($dirs as $d) { if (!is_dir($d)) @mkdir($d, 0755, true); @chmod($d, 0755); }
        
        // Uyarilar
        if (!$requirements['curl']['ok']) $warnings[] = 'CURL eklentisi yok - API entegrasyonları çalışmayabilir';
        if (!$requirements['gd']['ok']) $warnings[] = 'GD eklentisi yok - görsel işleme sınırlı';
        
        // install.php sil
        $backup = $root . '/install.completed.' . date('YmdHis') . '.php';
        @rename(__FILE__, $backup);
        if (file_exists(__FILE__)) @unlink(__FILE__);
        
        $done = true;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ahost One v25.0.0 RC5 - Kurulum</title>
<style>
*{box-sizing:border-box}
body{font-family:system-ui,-apple-system,sans-serif;background:linear-gradient(135deg,#0f172a,#1e293b);margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;color:#fff}
.box{max-width:800px;width:100%;background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.5)}
.box-head{background:linear-gradient(135deg,#3b82f6,#1d4ed8);padding:32px 40px}
.box-head h1{margin:0 0 8px;font-size:1.8rem}
.box-head p{margin:0;opacity:0.9;font-size:0.95rem}
.box-body{padding:32px 40px}
.req-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:24px}
.req-item{padding:14px;border-radius:12px;text-align:center;font-size:14px}
.req-item.ok{background:#ecfdf5}
.req-item.ok .icon{color:#047857}
.req-item.ok .txt{color:#047857}
.req-item.error{background:#fef2f2}
.req-item.error .icon{color:#dc2626}
.req-item.error .txt{color:#dc2626}
.req-item .icon{font-size:24px;margin-bottom:6px}
.req-item .txt{font-weight:600}
.form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
@media(max-width:600px){.form-grid{grid-template-columns:1fr}}
label{display:block;font-weight:600;color:#334155;margin-bottom:6px;font-size:14px}
input,select{width:100%;padding:14px;border:2px solid #e2e8f0;border-radius:12px;font-size:15px;transition:border-color .2s}
input:focus,select:focus{outline:none;border-color:#3b82f6}
.full{grid-column:1/-1}
.btn{width:100%;padding:16px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;border:none;border-radius:14px;font-size:16px;font-weight:700;cursor:pointer;transition:all .2s}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(59,130,246,0.4)}
.btn:disabled{background:#94a3b8;cursor:not-allowed;transform:none}
.err{background:#fef2f2;border:2px solid #fecaca;border-radius:12px;padding:16px;color:#dc2626;margin-bottom:20px}
.ok-box{background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:20px;padding:32px;text-align:center}
.ok-box h2{color:#fff;margin:0 0 8px}
.ok-box p{color:#94a3b8;margin:0}
.login-info{background:rgba(255,255,255,0.1);border-radius:12px;padding:20px;margin:20px 0;text-align:left}
.login-info p{margin:8px 0}
.login-info strong{color:#fbbf24}
.links{display:flex;gap:12px;justify-content:center;margin-top:20px;flex-wrap:wrap}
.links a{padding:12px 24px;border-radius:12px;text-decoration:none;font-weight:600}
.links a.primary{background:#3b82f6;color:#fff}
.links a.secondary{background:#1e293b;color:#fff}
.next-steps{background:#f8fafc;border-radius:16px;padding:24px;margin-top:24px;color:#334155}
.next-steps h3{margin:0 0 12px;color:#0f172a}
.next-steps ul{margin:0;padding-left:20px}
.next-steps li{margin:6px 0}
.note{background:#eff6ff;border-radius:12px;padding:16px;color:#1e40af;font-size:14px;margin-top:20px}
.warn{background:#fef3c7;border-radius:12px;padding:14px;color:#92400e;font-size:14px;margin-top:12px}
.section{border:1px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:20px}
.section h2{color:#0f172a;margin:0 0 16px;font-size:1rem;border-bottom:2px solid #e2e8f0;padding-bottom:10px}
.check-label{display:flex;align-items:center;gap:10px;cursor:pointer;margin-top:16px}
.check-label input{width:auto}
</style>
</head>
<body>
<div class="box">
<div class="box-head">
<h1>🚀 Ahost One v25.0.0 RC5 Kurulum</h1>
<p>Sistem gereksinimleri otomatik kontrol edilir</p>
</div>
<div class="box-body">

<?php if ($error): ?>
<div class="err"><strong>Hata:</strong> <?=h($error)?></div>
<?php endif; ?>

<?php if ($done): ?>
<div class="ok-box">
<h2>🎉 Kurulum Tamamlandı!</h2>
<p>Tebrikler! Ahost One başarıyla kuruldu.</p>
<div class="login-info">
<p><strong>👤 Admin E-posta:</strong> <?=h($adminEmail)?></p>
<p><strong>🔐 Admin Şifre:</strong> <?=h($adminPass)?></p>
</div>
<div class="links">
<a href="<?=detect_base_url()?>" class="primary">🏠 Siteye Git</a>
<a href="<?=detect_base_url()?>/admin/login" class="secondary">🔐 Admin Girişi</a>
</div>
</div>

<?php if (!empty($warnings)): ?>
<div class="warn"><strong>⚠️ Uyarılar:</strong><ul style="margin:8px 0 0;padding-left:20px"><?php foreach($warnings as $w):?><li><?=h($w)?></li><?php endforeach;?></ul></div>
<?php endif; ?>

<div class="next-steps">
<h3>📋 Sonraki Adımlar</h3>
<ol>
<li><strong>Admin Girişi:</strong> <?=detect_base_url()?>/admin/login</li>
<li><strong>Kurulum Sihirbazı:</strong> İlk girişte otomatik açılır</li>
<li><strong>Site Logosu:</strong> Admin → Ayarlar → Genel</li>
<li><strong>Ödeme:</strong> PayTR, Iyzico entegrasyonları</li>
<li><strong>SMTP:</strong> E-posta gönderimi için ayarla</li>
</ol>
<h3 style="margin-top:16px">📱 APK/AAB Build (Opsiyonel)</h3>
<p style="font-size:13px;color:#64748b;margin:8px 0 0">Android SDK kuruluysa: Java JDK 17+, Gradle 8.0+, Android SDK kurulmalı</p>
</div>

<?php else: ?>
<div class="req-grid">
<?php foreach($requirements as $key => $req):
$labels = ['php_version'=>'PHP','pdo_mysql'=>'PDO MySQL','curl'=>'CURL','gd'=>'GD','storage'=>'Storage'];
?>
<div class="req-item <?=$req['ok']?'ok':'error'?>">
<div class="icon"><?=$req['ok']?'✓':'✗'?></div>
<div class="txt"><?=$labels[$key]?><br><small style="opacity:0.7"><?=$req['current']?></small></div>
</div>
<?php endforeach; ?>
</div>

<form method="post">
<div class="section">
<h2>1. Veritabanı</h2>
<div class="form-grid">
<label>Host<input name="db_host" value="localhost"></label>
<label>Veritabanı<input name="db_name" required placeholder="ahost_one"></label>
<label>Kullanıcı<input name="db_user" required placeholder="root"></label>
<label>Şifre<input name="db_pass" type="password" placeholder="Boş bırakılabilir"></label>
<label class="full">Site URL<input name="base_url" placeholder="<?=detect_base_url()?> (boş bırakılabilir)"></label>
</div>
</div>

<div class="section">
<h2>2. Admin Hesabı</h2>
<div class="form-grid">
<label>E-posta<input name="admin_email" type="email" required placeholder="admin@site.com"></label>
<label>Şifre<input name="admin_pass" type="password" required placeholder="En az 10 karakter" minlength="10"></label>
<label class="full">Güvenlik Cevabı<input name="security_answer" required placeholder="Hesap kurtarma için (en az 4 karakter)" minlength="4"></label>
</div>
</div>

<label class="check-label">
<input type="checkbox" name="clean_install" value="1" checked>
<span style="font-weight:400">Her kurulumda veritabanını silip temiz kur</span>
</label>

<button type="submit" class="btn" <?=!$allOk?'disabled title="Gereksinimler karşılanmadı"':''?> style="margin-top:20px">
<?php if ($allOk): ?>🚀 Kurulumu Başlat<?php else: ?>⚠️ Gereksinimler Eksik<?php endif; ?>
</button>
</form>
<?php endif; ?>

<div class="note">
<strong>💡 Not:</strong> Kurulum tamamlandığında install.php otomatik silinir. 
Logo, tema, SMTP ve ödeme ayarlarını Admin Panelde yapılandırın.
</div>

</div>
</div>
</body>
</html>
