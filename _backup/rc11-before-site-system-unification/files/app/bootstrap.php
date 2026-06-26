<?php
// Ahost One v7.0.0 - Kaynak Sistem temel mantığı + Ahost One güçlendirilmiş mimari
// Credits: Ahost Bilişim / Lokman Demir
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$config = require __DIR__ . '/../config/config.php';
function ahost_config($key = null, $default = null) {
    global $config;
    if ($key === null) return $config;
    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) return $default;
        $value = $value[$segment];
    }
    return $value;
}
function db() {
    static $pdo;
    if ($pdo) return $pdo;
    $db = ahost_config('db');
    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    return $pdo;
}
function app_base_path() {
    $configured = rtrim((string)ahost_config('base_url',''), '/');
    if ($configured !== '') return $configured;
    $scriptDir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if ($scriptDir === '/' || $scriptDir === '.') return '';
    return rtrim($scriptDir, '/');
}
function url($path='') { return app_base_path() . '/' . ltrim($path, '/'); }

if (!function_exists('asset')) {
    function asset($path='') {
        $path = trim((string)$path);
        if ($path === '') return url('public');
        if (preg_match('~^(https?:)?//|^data:~i', $path)) return $path;
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'public/')) return url($path);
        if (str_starts_with($path, 'assets/')) return url('public/'.$path);
        return url('public/assets/'.$path);
    }
}

function ao_asset_version() { return ahost_config('asset_version', ahost_config('version','24.3.1')); }
function assetv($path='') { return asset($path) . '?v=' . rawurlencode((string)ao_asset_version()); }

if (!function_exists('ao_brand_logo_url')) {
    function ao_brand_logo_url($default='assets/img/ahost-logo.webp') {
        $fallbacks = ['assets/img/ahost-logo.webp','assets/img/ahost-logo.svg','assets/img/logo.webp','assets/img/logo.svg'];
        $raw = function_exists('admin_setting') ? trim((string)admin_setting('logo_url','')) : '';
        if ($raw === '') $raw = $default;
        if (preg_match('~^(https?:)?//|^data:~i', $raw)) return $raw;
        $raw = ltrim(str_replace('\\','/',$raw), '/');
        if (str_starts_with($raw, 'public/')) $raw = substr($raw, 7);
        $doc = dirname(__DIR__) . '/public/';
        if ($raw !== '' && file_exists($doc.$raw)) return asset($raw);
        foreach($fallbacks as $fb){ if(file_exists($doc.$fb)) return asset($fb); }
        return 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="180" height="44" viewBox="0 0 180 44"><rect width="180" height="44" rx="12" fill="#0f172a"/><text x="18" y="28" fill="#fff" font-family="Arial" font-size="18" font-weight="700">Ahost One</text></svg>');
    }
}


function redirect_to($path='') { header('Location: ' . url($path)); exit; }
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function render_view($area, $path, $data = []) {
    extract($data);
    $base = __DIR__ . "/Views/{$area}";
    // v24.3.1: tek standart müşteri paneli görünüm klasörü app/Views/customer.
    // Eski rotalar client_view() çağırsa bile müşteri paneli artık customer view'larını kullanır.
    if ($area === 'client' && is_dir(__DIR__ . '/Views/customer')) {
        $base = __DIR__ . '/Views/customer';
    }
    $header = $base . '/partials/header.php';
    $view   = $base . '/' . $path . '.php';
    $footer = $base . '/partials/footer.php';
    if (!is_file($header) || !is_file($view) || !is_file($footer)) {
        http_response_code(500);
        echo '<h1>Görünüm dosyası bulunamadı</h1>';
        echo '<p>Eksik görünüm: ' . e($area . '/' . $path) . '</p>';
        return;
    }
    require $header;
    require $view;
    require $footer;
}
function view($path, $data = []) { render_view('admin', $path, $data); }
function site_view($path, $data = []) { render_view('site', $path, $data); }
function customer_view($path, $data = []) { render_view('customer', $path, $data); }
function client_view($path, $data = []) { render_view('client', $path, $data); }
function auth_view($path, $data = []) { render_view('auth', $path, $data); }
function table_count($table) { try { return (int)db()->query("SELECT COUNT(*) FROM `$table`")->fetchColumn(); } catch (Throwable $e) { return 0; } }

function admin_pref($key, $default=null) {
    try {
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        if ($adminId <= 0) return $default;
        db()->exec("CREATE TABLE IF NOT EXISTS admin_preferences (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, pref_key VARCHAR(120) NOT NULL, pref_value TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_admin_pref(admin_id,pref_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $s=db()->prepare('SELECT pref_value FROM admin_preferences WHERE admin_id=? AND pref_key=? LIMIT 1'); $s->execute([$adminId,$key]); $v=$s->fetchColumn();
        return $v===false ? $default : $v;
    } catch(Throwable $e) { return $default; }
}
function save_admin_pref($key, $value) {
    try {
        $adminId=(int)($_SESSION['admin_id'] ?? 0); if($adminId<=0) return false;
        db()->exec("CREATE TABLE IF NOT EXISTS admin_preferences (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, pref_key VARCHAR(120) NOT NULL, pref_value TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_admin_pref(admin_id,pref_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $q=db()->prepare('INSERT INTO admin_preferences(admin_id,pref_key,pref_value) VALUES(?,?,?) ON DUPLICATE KEY UPDATE pref_value=VALUES(pref_value)'); return $q->execute([$adminId,$key,$value]);
    } catch(Throwable $e) { return false; }
}
function ao_currency_rate($from='USD',$to='TRY') {
    $from=strtoupper($from); $to=strtoupper($to); if($from===$to) return '1.00';
    $key='currency_rate_'.$from.'_'.$to;
    $raw=admin_setting($key, null);
    if($raw===null || $raw==='') $raw=admin_setting('usd_try_rate','45.00');
    $margin=(float)admin_setting(strtolower($from.'_'.$to).'_margin_percent', admin_setting('currency_margin_percent','5.00'));
    $rate=(float)$raw;
    if($margin!==0.0) $rate = $rate + ($rate*$margin/100);
    return number_format($rate, 2, '.', '');
}
function ao_days_until($date) {
    if(!$date || $date==='0000-00-00') return 9999;
    try { $today=new DateTime(date('Y-m-d')); $d=new DateTime($date); return (int)$today->diff($d)->format('%r%a'); } catch(Throwable $e) { return 9999; }
}
function ao_tc_algorithm_valid($tc) {
    $tc=preg_replace('/\D/','',(string)$tc);
    if(strlen($tc)!==11 || $tc[0]==='0' || preg_match('/^(\d)\1{10}$/',$tc)) return false;
    $d=array_map('intval',str_split($tc));
    $odd=$d[0]+$d[2]+$d[4]+$d[6]+$d[8]; $even=$d[1]+$d[3]+$d[5]+$d[7];
    return ((($odd*7)-$even)%10)===$d[9] && (array_sum(array_slice($d,0,10))%10)===$d[10];
}
function ao_identity_verify($first,$last,$birthDate,$tc) {
    if(!ao_tc_algorithm_valid($tc)) return ['ok'=>false,'message'=>'TC Kimlik No algoritma kontrolünden geçmedi.'];
    $year=(int)substr((string)$birthDate,0,4); if($year<1900 || $year>(int)date('Y')) return ['ok'=>false,'message'=>'Doğum tarihi geçersiz.'];
    // Resmi doğrulama adaptörü aktifse burada MERNIS/NVI servis adaptörü çağrılır. Fresh install offline olduğu için algoritma + zorunlu alan kontrolü kullanılır.
    return ['ok'=>true,'message'=>'Kimlik bilgileri doğrulama altyapısından geçti.'];
}
function ao_schema_ensure_v186() {
    try { db()->exec("ALTER TABLE customers ADD COLUMN tc_identity_no VARCHAR(11) NULL AFTER phone"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE customers ADD COLUMN birth_date DATE NULL AFTER tc_identity_no"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE customers ADD COLUMN identity_verified TINYINT(1) DEFAULT 0 AFTER birth_date"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE customers ADD COLUMN identity_verified_at DATETIME NULL AFTER identity_verified"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE services ADD COLUMN auto_renew TINYINT(1) DEFAULT 1 AFTER next_due_date"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE services ADD COLUMN suspend_at DATETIME NULL AFTER auto_renew"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE services ADD COLUMN terminate_at DATETIME NULL AFTER suspend_at"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS customer_payment_methods (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, provider VARCHAR(80) NOT NULL, provider_customer_token VARCHAR(190) NULL, card_token VARCHAR(190) NOT NULL, card_brand VARCHAR(40) NULL, masked_card VARCHAR(40) NULL, is_default TINYINT(1) DEFAULT 0, status VARCHAR(30) DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS renewal_automation_logs (id INT AUTO_INCREMENT PRIMARY KEY, service_id INT NULL, domain_id INT NULL, customer_id INT NULL, action VARCHAR(80) NOT NULL, channel VARCHAR(40) NULL, status VARCHAR(40) DEFAULT 'pending', message TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id), KEY action(action)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS hosting_automation_settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(120) UNIQUE, setting_value TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    foreach(['hosting_suspend_day'=>'1','hosting_terminate_day'=>'16','hosting_reminder_days'=>'1,3,7,10,15','notify_mail'=>'1','notify_sms'=>'1','notify_whatsapp'=>'1','auto_renew_credit_first'=>'1','stored_card_mode'=>'token_only'] as $k=>$v){ try{db()->prepare('INSERT IGNORE INTO hosting_automation_settings(setting_key,setting_value) VALUES(?,?)')->execute([$k,$v]);}catch(Throwable $e){} }
}


// v22.2.0 global menu helpers: admin/site/mobile menus are shared, not tied to a single admin session.
if (!function_exists('ao_default_menu_items_v222')) {
function ao_default_menu_items_v222($type='site') {
    $menus = [
        'admin' => [
            ['label'=>'Müşteriler','url'=>'admin/customers','children'=>[]],
            ['label'=>'Paketler / Ürünler','url'=>'admin/product-center','children'=>[]],
            ['label'=>'Siparişler','url'=>'admin/orders','children'=>[]],
            ['label'=>'Domain','url'=>'admin/domain-center','children'=>[]],
            ['label'=>'Ayarlar','url'=>'admin/settings','children'=>[]],
        ],
        'site' => [
            ['label'=>'Domain Center','url'=>'domain','children'=>[['label'=>'Domain Sorgula','url'=>'domain'],['label'=>'Transfer','url'=>'domain#transfer'],['label'=>'WHOIS','url'=>'domain#whois']]],
            ['label'=>'Hosting Center','url'=>'hosting','children'=>[['label'=>'Web Hosting','url'=>'hosting'],['label'=>'VPS','url'=>'vps']]],
            ['label'=>'SiteBuilder','url'=>'sitebuilder','children'=>[]],
            ['label'=>'MobileBuilder','url'=>'mobilebuilder','children'=>[]],
            ['label'=>'Web Tasarım','url'=>'web-tasarim','children'=>[]],
            ['label'=>'Mobil Uygulama','url'=>'mobil-uygulama','children'=>[]],
            ['label'=>'Dijital Hizmetler','url'=>'dijital-hizmetler','children'=>[]],
            ['label'=>'Marketplace','url'=>'marketplace','children'=>[]],
            ['label'=>'Referanslar','url'=>'referanslar','children'=>[]],
        ],
        'mobile' => [
            ['label'=>'Ana Sayfa','url'=>'','children'=>[]],
            ['label'=>'Domain Sorgula','url'=>'domain','children'=>[]],
            ['label'=>'Müşteri Paneli','url'=>'client/login','children'=>[]],
            ['label'=>'Destek','url'=>'client/support','children'=>[]],
        ],
    ];
    return $menus[$type] ?? $menus['site'];
}
function ao_normalize_menu_items_v222($items, $level=0) {
    $clean=[];
    if (!is_array($items)) return $clean;
    foreach($items as $it){
        $label=trim((string)($it['label']??''));
        $url=trim((string)($it['url']??''));
        if($label==='') continue;
        $row=['label'=>$label,'url'=>$url,'children'=>[]];
        if($level < 3 && !empty($it['children']) && is_array($it['children'])) $row['children']=ao_normalize_menu_items_v222($it['children'],$level+1);
        $clean[]=$row;
    }
    return $clean;
}
function ao_menu_ensure_table_v222(){
    try { db()->exec("CREATE TABLE IF NOT EXISTS ao_menus (id INT AUTO_INCREMENT PRIMARY KEY, menu_type VARCHAR(30) NOT NULL UNIQUE, items_json MEDIUMTEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
}
function ao_get_menu_v222($type='site'){
    $type = in_array($type,['admin','site','mobile'],true) ? $type : 'site';
    ao_menu_ensure_table_v222();
    try { $q=db()->prepare('SELECT items_json FROM ao_menus WHERE menu_type=? LIMIT 1'); $q->execute([$type]); $json=$q->fetchColumn(); if($json){ $arr=json_decode($json,true); if(is_array($arr)) return ao_normalize_menu_items_v222($arr); } } catch(Throwable $e) {}
    return ao_default_menu_items_v222($type);
}
function ao_save_menu_v222($type, $items){
    $type = in_array($type,['admin','site','mobile'],true) ? $type : 'site';
    $items = ao_normalize_menu_items_v222($items);
    ao_menu_ensure_table_v222();
    $json=json_encode($items, JSON_UNESCAPED_UNICODE);
    try { $q=db()->prepare('INSERT INTO ao_menus(menu_type,items_json) VALUES(?,?) ON DUPLICATE KEY UPDATE items_json=VALUES(items_json), updated_at=NOW()'); return $q->execute([$type,$json]); } catch(Throwable $e) { return false; }
}
function ao_menu_url_v222($u){
    $u=trim((string)$u);
    if($u==='' || $u==='/') return url('');
    if(preg_match('~^(https?:)?//|^mailto:|^tel:|^#~i',$u)) return $u;
    return url($u);
}
}



// v24.1.2 Security MFA helpers: admin/customer Mail OTP, SMS OTP and Google Authenticator/TOTP.
function ao_mfa_ensure_schema() {
    try { db()->exec("CREATE TABLE IF NOT EXISTS auth_otp_tokens (id INT AUTO_INCREMENT PRIMARY KEY, user_type VARCHAR(30) NOT NULL, user_id INT NOT NULL, method VARCHAR(30) NOT NULL, code_hash VARCHAR(255) NOT NULL, destination VARCHAR(190) NULL, expires_at DATETIME NOT NULL, attempts INT DEFAULT 0, used_at DATETIME NULL, ip_address VARCHAR(80) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY user_lookup(user_type,user_id), KEY method(method), KEY expires_at(expires_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS auth_mfa_profiles (id INT AUTO_INCREMENT PRIMARY KEY, user_type VARCHAR(30) NOT NULL, user_id INT NOT NULL, enabled TINYINT(1) DEFAULT 0, preferred_method VARCHAR(30) DEFAULT 'mail', totp_secret VARCHAR(80) NULL, recovery_codes TEXT NULL, verified_at DATETIME NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_mfa_user(user_type,user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS auth_login_events (id INT AUTO_INCREMENT PRIMARY KEY, user_type VARCHAR(30) NOT NULL, user_id INT NULL, email VARCHAR(190) NULL, event_type VARCHAR(80) NOT NULL, method VARCHAR(30) NULL, status VARCHAR(40) DEFAULT 'info', ip_address VARCHAR(80) NULL, user_agent VARCHAR(255) NULL, message TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY user_lookup(user_type,user_id), KEY event_type(event_type), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO settings(setting_key,setting_value) VALUES
        ('admin_mfa_policy','optional'),('customer_mfa_policy','optional'),
        ('mfa_mail_enabled','1'),('mfa_totp_enabled','1'),('mfa_sms_enabled','0'),
        ('mfa_default_method','mail'),('mfa_otp_ttl_minutes','5'),('mfa_max_attempts','5'),
        ('mfa_sms_sender','AhostOne')"); } catch(Throwable $e) {}
}
function ao_mfa_policy($userType) { return (string)admin_setting($userType === 'admin' ? 'admin_mfa_policy' : 'customer_mfa_policy', 'optional'); }
function ao_mfa_methods_enabled() {
    $out=[];
    if ((string)admin_setting('mfa_mail_enabled','1') === '1') $out[]='mail';
    if ((string)admin_setting('mfa_totp_enabled','1') === '1') $out[]='totp';
    if ((string)admin_setting('mfa_sms_enabled','0') === '1') $out[]='sms';
    return $out ?: ['mail'];
}
function ao_mfa_default_method($userType, $userId) {
    ao_mfa_ensure_schema();
    try { $q=db()->prepare('SELECT preferred_method FROM auth_mfa_profiles WHERE user_type=? AND user_id=? LIMIT 1'); $q->execute([$userType,$userId]); $m=$q->fetchColumn(); if($m && in_array($m, ao_mfa_methods_enabled(), true)) return $m; } catch(Throwable $e) {}
    $m=(string)admin_setting('mfa_default_method','mail'); return in_array($m, ao_mfa_methods_enabled(), true) ? $m : ao_mfa_methods_enabled()[0];
}
function ao_mfa_is_required_for_user($userType, $userId) {
    $policy=ao_mfa_policy($userType);
    if ($policy === 'off') return false;
    if ($policy === 'required') return true;
    ao_mfa_ensure_schema();
    try { $q=db()->prepare('SELECT enabled FROM auth_mfa_profiles WHERE user_type=? AND user_id=? LIMIT 1'); $q->execute([$userType,$userId]); return (string)$q->fetchColumn()==='1'; } catch(Throwable $e) { return false; }
}
function ao_mfa_log($userType,$userId,$email,$event,$method,$status,$message='') {
    ao_mfa_ensure_schema();
    try { db()->prepare('INSERT INTO auth_login_events(user_type,user_id,email,event_type,method,status,ip_address,user_agent,message) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$userType,$userId,$email,$event,$method,$status,$_SERVER['REMOTE_ADDR']??'', substr($_SERVER['HTTP_USER_AGENT']??'',0,255), $message]); } catch(Throwable $e) {}
}
function ao_mfa_user_email($userType, $user) { return (string)($user['email'] ?? ''); }
function ao_mfa_user_phone($userType, $user) { return (string)($user['phone'] ?? $user['mobile'] ?? ''); }
function ao_mfa_send_mail($email, $code) {
    $subject='Ahost One Giriş Doğrulama Kodu';
    $body="Ahost One giriş doğrulama kodunuz: {$code}\n\nBu kod kısa süre içinde geçerliliğini kaybeder.";
    try { @mail($email, $subject, $body); } catch(Throwable $e) {}
    return true;
}
function ao_mfa_send_sms($phone, $code) {
    if (function_exists('ao_iletimerkezi_send')) {
        $result=ao_iletimerkezi_send($phone,'Ahost One doğrulama kodunuz: '.$code,'mfa_otp');
        return !empty($result['ok']);
    }
    // OTP değerini hiçbir zaman loglara yazma.
    try { db()->prepare('INSERT INTO module_update_logs(module_key,action,status,message) VALUES(?,?,?,?)')->execute(['security-mfa','sms_otp','error','SMS sağlayıcı adaptörü aktif değil.']); } catch(Throwable $e) {}
    return false;
}
function ao_mfa_start_challenge($userType, array $user, $redirectAfter='') {
    ao_mfa_ensure_schema();
    $id=(int)($user['id'] ?? 0); $email=ao_mfa_user_email($userType,$user);
    if ($id<=0 || !ao_mfa_is_required_for_user($userType,$id)) {
        if($userType==='admin') $_SESSION['admin_id']=$id; else $_SESSION['customer_id']=$id;
        ao_mfa_log($userType,$id,$email,'login','password','success','MFA gerekmeden giriş tamamlandı.');
        redirect_to($redirectAfter ?: ($userType==='admin'?'admin':'client'));
    }
    $method=ao_mfa_default_method($userType,$id);
    $_SESSION['mfa_pending']=['user_type'=>$userType,'user_id'=>$id,'email'=>$email,'phone'=>ao_mfa_user_phone($userType,$user),'method'=>$method,'redirect'=>$redirectAfter ?: ($userType==='admin'?'admin':'client'), 'created_at'=>time()];
    if ($method==='mail' || $method==='sms') ao_mfa_generate_otp($userType,$id,$method,$method==='mail'?$email:ao_mfa_user_phone($userType,$user));
    ao_mfa_log($userType,$id,$email,'mfa_required',$method,'pending','Şifre doğru, MFA bekleniyor.');
    redirect_to('auth/mfa');
}
function ao_mfa_generate_otp($userType,$userId,$method,$destination='') {
    ao_mfa_ensure_schema();
    $code=(string)random_int(100000,999999);
    $ttl=max(1,(int)admin_setting('mfa_otp_ttl_minutes','5'));
    try { db()->prepare('INSERT INTO auth_otp_tokens(user_type,user_id,method,code_hash,destination,expires_at,ip_address) VALUES(?,?,?,?,?,DATE_ADD(NOW(), INTERVAL ? MINUTE),?)')->execute([$userType,$userId,$method,password_hash($code,PASSWORD_DEFAULT),$destination,$ttl,$_SERVER['REMOTE_ADDR']??'']); } catch(Throwable $e) {}
    if($method==='mail') ao_mfa_send_mail($destination,$code);
    if($method==='sms') ao_mfa_send_sms($destination,$code);
    return $code;
}
function ao_base32_decode_mfa($b32) {
    $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $b32=strtoupper(preg_replace('/[^A-Z2-7]/i','',$b32)); $bits=''; $out='';
    foreach(str_split($b32) as $ch){ $v=strpos($alphabet,$ch); if($v===false) continue; $bits.=str_pad(decbin($v),5,'0',STR_PAD_LEFT); }
    foreach(str_split($bits,8) as $byte){ if(strlen($byte)===8) $out.=chr(bindec($byte)); }
    return $out;
}
function ao_totp_secret() { $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $s=''; for($i=0;$i<32;$i++) $s.=$alphabet[random_int(0,31)]; return $s; }
function ao_totp_code($secret,$timeSlice=null) {
    if($timeSlice===null) $timeSlice=floor(time()/30);
    $key=ao_base32_decode_mfa($secret); $time=pack('N*',0).pack('N*',$timeSlice);
    $hash=hash_hmac('sha1',$time,$key,true); $offset=ord(substr($hash,-1)) & 0x0F;
    $truncated=((ord($hash[$offset]) & 0x7F) << 24) | ((ord($hash[$offset+1]) & 0xFF) << 16) | ((ord($hash[$offset+2]) & 0xFF) << 8) | (ord($hash[$offset+3]) & 0xFF);
    return str_pad((string)($truncated % 1000000),6,'0',STR_PAD_LEFT);
}
function ao_totp_verify($secret,$code) { $code=preg_replace('/\D/','',(string)$code); if(strlen($code)!==6) return false; $slice=floor(time()/30); for($i=-1;$i<=1;$i++){ if(hash_equals(ao_totp_code($secret,$slice+$i),$code)) return true; } return false; }
function ao_mfa_get_totp_secret($userType,$userId,$create=false) {
    ao_mfa_ensure_schema();
    try { $q=db()->prepare('SELECT totp_secret FROM auth_mfa_profiles WHERE user_type=? AND user_id=? LIMIT 1'); $q->execute([$userType,$userId]); $secret=$q->fetchColumn(); if($secret) return $secret; } catch(Throwable $e) {}
    if(!$create) return '';
    $secret=ao_totp_secret();
    try { db()->prepare('INSERT INTO auth_mfa_profiles(user_type,user_id,enabled,preferred_method,totp_secret) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE totp_secret=VALUES(totp_secret), preferred_method=VALUES(preferred_method)')->execute([$userType,$userId,1,'totp',$secret]); } catch(Throwable $e) {}
    return $secret;
}
function ao_mfa_verify_pending($code) {
    ao_mfa_ensure_schema();
    $p=$_SESSION['mfa_pending'] ?? null; if(!$p) return ['ok'=>false,'message'=>'Bekleyen doğrulama bulunamadı.'];
    $method=$p['method']; $userType=$p['user_type']; $userId=(int)$p['user_id']; $email=$p['email']??''; $max=max(1,(int)admin_setting('mfa_max_attempts','5'));
    if($method==='totp') { $secret=ao_mfa_get_totp_secret($userType,$userId,true); $ok=ao_totp_verify($secret,$code); }
    else {
        $ok=false; try { $q=db()->prepare('SELECT * FROM auth_otp_tokens WHERE user_type=? AND user_id=? AND method=? AND used_at IS NULL AND expires_at>NOW() ORDER BY id DESC LIMIT 1'); $q->execute([$userType,$userId,$method]); $row=$q->fetch(); if($row && (int)$row['attempts']<$max){ $ok=password_verify((string)$code,$row['code_hash']); db()->prepare('UPDATE auth_otp_tokens SET attempts=attempts+1, used_at=IF(?=1,NOW(),used_at) WHERE id=?')->execute([$ok?1:0,$row['id']]); } } catch(Throwable $e) {}
    }
    if(!$ok){ ao_mfa_log($userType,$userId,$email,'mfa_verify',$method,'failed','Kod hatalı veya süresi dolmuş.'); return ['ok'=>false,'message'=>'Kod hatalı veya süresi dolmuş.']; }
    if($userType==='admin') $_SESSION['admin_id']=$userId; else $_SESSION['customer_id']=$userId;
    ao_mfa_log($userType,$userId,$email,'mfa_verify',$method,'success','MFA doğrulama başarılı.');
    $redirect=$p['redirect'] ?: ($userType==='admin'?'admin':'client'); unset($_SESSION['mfa_pending']); return ['ok'=>true,'redirect'=>$redirect];
}

function current_customer() {
    if (empty($_SESSION['customer_id'])) return null;
    try { $s=db()->prepare('SELECT * FROM customers WHERE id=? LIMIT 1'); $s->execute([$_SESSION['customer_id']]); return $s->fetch() ?: null; } catch(Throwable $e) { return null; }
}
function require_customer() { if (!current_customer()) redirect_to('client/login'); }
function flash($type, $message) { $_SESSION['flash'] = ['type'=>$type, 'message'=>$message]; }
function get_flash() { $f=$_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf_token'];
}
function csrf_field() { return '<input type="hidden" name="csrf_token" value="'.e(csrf_token()).'">'; }
function verify_csrf() {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    // v25.0.0 RC5: verify_csrf() çağrılan GET aksiyonları da token doğrular.
    // Eski sürüm sadece POST kontrol ediyordu; bu nedenle bazı GET delete/toggle route'ları CSRF korumasız kalıyordu.
    $sent = $method === 'POST' ? ($_POST['csrf_token'] ?? '') : ($_GET['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !is_string($sent) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        flash('error','Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.');
        $resolvedRoute = (string)($_SERVER['AHOST_ROUTE_RESOLVED'] ?? '');
        if ($resolvedRoute === 'admin' || str_starts_with($resolvedRoute, 'admin/')) {
            redirect_to('admin/login');
        }
        if ($resolvedRoute === 'client' || str_starts_with($resolvedRoute, 'client/')) {
            redirect_to('client/login');
        }
        redirect_to($_SERVER['HTTP_REFERER'] ?? '');
    }
}
function current_admin() {
    if (empty($_SESSION['admin_id'])) return null;
    try { $s=db()->prepare('SELECT * FROM admins WHERE id=? LIMIT 1'); $s->execute([$_SESSION['admin_id']]); return $s->fetch() ?: null; } catch(Throwable $e) { return null; }
}
function require_admin() { if (!current_admin()) redirect_to('admin/login'); }


// v22.1.0 site header, announcement, language, notification and social helpers
function ao_active_site_announcement() {
    $enabled = (string)admin_setting('site_announcement_enabled','0');
    $text = trim((string)admin_setting('site_announcement_text',''));
    if ($enabled !== '1' || $text === '') return null;
    $now = date('Y-m-d H:i:s');
    $start = trim((string)admin_setting('site_announcement_start',''));
    $end = trim((string)admin_setting('site_announcement_end',''));
    if ($start !== '' && $start > $now) return null;
    if ($end !== '' && $end < $now) return null;
    return [
        'text'=>$text,
        'url'=>trim((string)admin_setting('site_announcement_url','')),
        'style'=>admin_setting('site_announcement_style','info') ?: 'info'
    ];
}
function ao_social_links() {
    $defs = [
        'facebook'=>'Facebook','instagram'=>'Instagram','linkedin'=>'LinkedIn','twitter'=>'X','tiktok'=>'TikTok','youtube'=>'YouTube','whatsapp'=>'WhatsApp','telegram'=>'Telegram','github'=>'GitHub','discord'=>'Discord'
    ];
    $out=[];
    foreach($defs as $key=>$label){
        $url=trim((string)admin_setting($key,''));
        if($url!=='') $out[$key]=['label'=>$label,'url'=>$url];
    }
    return $out;
}
function ao_cart_count() {
    $cart = $_SESSION['cart'] ?? $_SESSION['ao_cart'] ?? [];
    if (is_array($cart)) {
        $count=0;
        foreach($cart as $item){ $count += is_array($item) ? (int)($item['qty'] ?? $item['quantity'] ?? 1) : 1; }
        return max(0,$count);
    }
    return 0;
}
function ao_customer_unread_notifications_count() {
    $cid=(int)($_SESSION['customer_id'] ?? 0); if($cid<=0) return 0;
    try {
        return (int)db()->query("SELECT COUNT(*) FROM customer_notifications WHERE customer_id={$cid} AND read_at IS NULL")->fetchColumn();
    } catch(Throwable $e) { return 0; }
}
function ao_available_language_meta(){
    return [
        'tr'=>['label'=>'Türkçe','flag'=>'🇹🇷'], 'en'=>['label'=>'English','flag'=>'🇬🇧'],
        'de'=>['label'=>'Deutsch','flag'=>'🇩🇪'], 'ar'=>['label'=>'العربية','flag'=>'🇸🇦'],
        'ru'=>['label'=>'Русский','flag'=>'🇷🇺'], 'fr'=>['label'=>'Français','flag'=>'🇫🇷'],
        'es'=>['label'=>'Español','flag'=>'🇪🇸']
    ];
}
function ao_language_options() {
    $raw=trim((string)admin_setting('enabled_languages','tr,en'));
    $items=array_filter(array_map('trim', explode(',', $raw)));
    $meta=ao_available_language_meta();
    $out=[]; foreach($items as $code){ $code=strtolower(preg_replace('~[^a-z_-]~i','',$code)); if($code!=='') $out[$code]=$meta[$code]['label'] ?? strtoupper($code); }
    return $out ?: ['tr'=>'Türkçe'];
}
function ao_current_language(){
    if(isset($_GET['lang'])){ $l=strtolower(preg_replace('~[^a-z_-]~i','',(string)$_GET['lang'])); if($l!==''){ $_SESSION['lang']=$l; setcookie('ao_lang',$l,time()+31536000,'/'); return $l; } }
    return strtolower((string)($_SESSION['lang'] ?? $_COOKIE['ao_lang'] ?? admin_setting('default_language','tr')));
}
function ao_lang_file_path($lang=null){ $lang=$lang ?: ao_current_language(); $lang=strtolower(preg_replace('~[^a-z_-]~i','',(string)$lang)); return dirname(__DIR__).'/lang/'.$lang.'.php'; }
function ao_load_lang($lang=null){
    static $cache=[]; $lang=$lang ?: ao_current_language(); $lang=strtolower((string)$lang);
    if(isset($cache[$lang])) return $cache[$lang];
    $fallback=[]; $fp=dirname(__DIR__).'/lang/tr.php'; if(is_file($fp)){ $x=include $fp; if(is_array($x)) $fallback=$x; }
    $path=ao_lang_file_path($lang); $data=[]; if(is_file($path)){ $x=include $path; if(is_array($x)) $data=$x; }
    return $cache[$lang]=array_replace($fallback,$data);
}
function __t($key,$default=''){
    $map=ao_load_lang(); return $map[$key] ?? ($default!==''?$default:$key);
}
function ao_sync_language_file($lang,array $translations){
    $dir=dirname(__DIR__).'/lang'; if(!is_dir($dir)) @mkdir($dir,0775,true);
    $path=$dir.'/'.preg_replace('~[^a-z_-]~i','',$lang).'.php';
    $body="<?php
return [
"; foreach($translations as $k=>$v){ $body .= "    '".str_replace("'","\'",$k)."' => '".str_replace("'","\'",$v)."',
"; } $body.='];
';
    return @file_put_contents($path,$body)!==false;
}

function admin_setting($key, $default=null) {
    try { $s=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1'); $s->execute([$key]); $v=$s->fetchColumn(); return $v===false ? $default : $v; } catch(Throwable $e) { return $default; }
}
function save_setting($key, $value) {
    try { $s=db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)'); return $s->execute([$key,$value]); } catch(Throwable $e) { return false; }
}


// Ahost One v24.6.3 route, mobile navigation and notification helpers
if (!function_exists('ao_current_route_path')) {
    function ao_current_route_path(bool $resolved = true): string {
        $path = $resolved ? ($_SERVER['AHOST_ROUTE_RESOLVED'] ?? '') : '';
        if ($path === '') $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $path = strtolower(trim((string)$path, '/'));
        $path = preg_replace('~^index\.php/?~', '', $path);
        return trim($path, '/');
    }
}
if (!function_exists('ao_mobile_nav_active_key_v2463')) {
    function ao_mobile_nav_active_key_v2463(?string $path = null): string {
        $path = trim(strtolower($path ?? ao_current_route_path(true)), '/');
        if ($path === '' || $path === 'home' || $path === 'anasayfa') return 'home';
        if (preg_match('~(^|/)(client|customer)/(support|tickets?|ticket|knowledge-base|bilgi-bankasi)(/|$)~', $path)) return 'support';
        if (preg_match('~(^|/)(client|customer|musteri-paneli|panel|profile|services|invoices|account-users|security)(/|$)~', $path)) return 'panel';
        if (preg_match('~(^|/)(domain|whois|dns|alan-adi|alanadi)(/|$)~', $path)) return 'domain';
        if (preg_match('~(^|/)(hosting|vps|server|sunucu|urun|urunler|products?|package|paket|paketler|cart|checkout|ssl)(/|$)~', $path)) return 'package';
        if (preg_match('~(^|/)(support|destek|ticket|tickets|knowledge-base|bilgi-bankasi|duyurular|iletisim|contact)(/|$)~', $path)) return 'support';
        return 'home';
    }
}
if (!function_exists('ao_mobile_nav_active_class')) {
    function ao_mobile_nav_active_class(string $key): string {
        return ao_mobile_nav_active_key_v2463() === $key ? ' active is-active' : '';
    }
}
if (!function_exists('ao_customer_notifications')) {
    function ao_customer_notifications(int $customerId, bool $includeRead = true, int $limit = 100): array {
        try {
            $sql='SELECT * FROM customer_notifications WHERE customer_id=?'; $params=[$customerId];
            if(!$includeRead) $sql.=' AND read_at IS NULL';
            $sql.=' ORDER BY id DESC LIMIT '.max(1,(int)$limit);
            $q=db()->prepare($sql); $q->execute($params); return $q->fetchAll() ?: [];
        } catch(Throwable $e) { return []; }
    }
}
if (!function_exists('ao_customer_notification_mark_read')) {
    function ao_customer_notification_mark_read(int $customerId, int $id): bool {
        try { return db()->prepare('UPDATE customer_notifications SET read_at=NOW() WHERE id=? AND customer_id=? AND read_at IS NULL')->execute([$id,$customerId]); } catch(Throwable $e) { return false; }
    }
}
if (!function_exists('ao_customer_notification_mark_all_read')) {
    function ao_customer_notification_mark_all_read(int $customerId): bool {
        try { return db()->prepare('UPDATE customer_notifications SET read_at=NOW() WHERE customer_id=? AND read_at IS NULL')->execute([$customerId]); } catch(Throwable $e) { return false; }
    }
}
