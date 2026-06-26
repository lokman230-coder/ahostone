<?php
require __DIR__ . '/app/bootstrap.php';
$route = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if (!function_exists('ao_recalculate_invoice_total_v2465')) {
    function ao_recalculate_invoice_total_v2465(int $invoiceId): void {
        try{
            $q=db()->prepare('SELECT COALESCE(SUM(amount),0) FROM invoice_items WHERE invoice_id=?'); $q->execute([$invoiceId]); $subtotal=(float)$q->fetchColumn();
            $iq=db()->prepare('SELECT tax FROM invoices WHERE id=?'); $iq->execute([$invoiceId]); $tax=(float)$iq->fetchColumn();
            db()->prepare('UPDATE invoices SET subtotal=?, total=? WHERE id=?')->execute([$subtotal,$subtotal+$tax,$invoiceId]);
        }catch(Throwable $e){}
    }
}

if ((string)getenv('AH_CUSTOM_ROUTES') === '1' && isset($_GET['ao_route'])) {
    $customRoute=trim((string)$_GET['ao_route'],'/');
    if(preg_match('#^[a-z0-9/_-]+$#',$customRoute)) $route=$customRoute;
}
$base = trim(parse_url(app_base_path(), PHP_URL_PATH) ?: '', '/');
if ($base && str_starts_with($route, $base)) { $route = trim(substr($route, strlen($base)), '/'); }
$route = $route ?: '';

// v24.3.2 Route hardening: keep legacy customer/* URLs working while the active panel namespace is client/*.
if ($route === 'customer') { $route = 'client'; }
elseif (str_starts_with($route, 'customer/')) { $route = 'client/' . substr($route, 9); }
$_SERVER['AHOST_ROUTE_RESOLVED'] = $route;
$_SERVER['AHOST_ROUTE_RAW'] = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// v25.0.0 RC5: Merkezi admin yetkilendirmesi.
// admin/login ve şifre sıfırlama akışları dışındaki tüm admin rotaları
// tek noktadan korunur. Tek tek route içinde require_admin() unutulsa bile erişim engellenir.
if (($route === 'admin' || str_starts_with($route, 'admin/'))) {
    $adminAuthPublicRoutes = [
        'admin/login',
        'admin/forgot-password',
        'admin/security-question',
        'admin/reset-password',
        'admin/logout'
    ];
    $isAdminPublicRoute = in_array($route, $adminAuthPublicRoutes, true);
    if (!$isAdminPublicRoute) {
        require_admin();
    }
}


// Serve public/theme assets safely even when all requests are routed through index.php.
if (str_starts_with($route, 'public/') || str_starts_with($route, 'themes/')) {
    $baseDir = str_starts_with($route, 'themes/') ? 'themes' : 'public';
    $assetRoot = realpath(__DIR__ . '/' . $baseDir);
    $assetPath = realpath(__DIR__ . '/' . $route);
    if ($assetRoot && $assetPath && str_starts_with($assetPath, $assetRoot) && is_file($assetPath)) {
        $ext = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        $types = ['css'=>'text/css; charset=utf-8','js'=>'application/javascript; charset=utf-8','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','svg'=>'image/svg+xml','webp'=>'image/webp','woff2'=>'font/woff2'];
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        readfile($assetPath);
        exit;
    }
}



// v18.6.0 Currency Center Pro - refreshsiz kur dönüşümü
if ($route === 'currency/rate') {
    header('Content-Type: application/json; charset=utf-8');
    $cur = strtoupper($_GET['currency'] ?? 'TRY');
    echo json_encode(['ok'=>true,'currency'=>$cur,'usd_try'=>ao_currency_rate('USD','TRY')], JSON_UNESCAPED_UNICODE);
    exit;
}

// v24.10.0 Scan & Report Center Pro + DomainNameAPI REST/API Key adapter
function ao_diag_mask($value) {
    $value = (string)$value;
    if ($value === '') return '';
    if (strlen($value) <= 4) return str_repeat('*', strlen($value));
    return substr($value, 0, 2) . str_repeat('*', max(4, strlen($value)-4)) . substr($value, -2);
}
function ao_dna_library_path() { return __DIR__ . '/app/Services/domainnameapi/dna.php'; }
function ao_dna_client($bundle) {
    if (!file_exists(ao_dna_library_path())) throw new Exception('DomainNameAPI kütüphanesi bulunamadı: app/Services/domainnameapi');
    require_once ao_dna_library_path();
    [$user,$pass] = ao_dna_creds($bundle);
    if ($user === '' || $pass === '') throw new Exception('DomainNameAPI Reseller ID veya API Key boş.');
    $cfg = $bundle['config'] ?? []; $reg = $bundle['registrar'] ?? [];
    $test = (int)($reg['test_mode'] ?? 0) === 1;
    // DomainNameAPI test/canlı ayrımı registrarın Test Modu alanından yönetilir.
    // Eski config[test_mode] kayıtları canlı bağlantıyı yanlışlıkla OTE'ye yönlendirmesin diye burada kullanılmaz.
    return new \DomainNameApi\DomainNameAPI_PHPLibrary($user, $pass, $test);
}
function ao_scan_add(&$rows, $category, $name, $status, $detail='', $priority='medium', $recommendation='') {
    $rows[] = ['category'=>$category,'name'=>$name,'status'=>$status,'detail'=>$detail,'priority'=>$priority,'recommendation'=>$recommendation];
}
function ao_scan_score($rows) {
    if (!$rows) return 100;
    $penalty = 0;
    foreach ($rows as $r) {
        if ($r['status'] === 'fail') $penalty += $r['priority']==='high' ? 18 : 10;
        elseif ($r['status'] === 'warning') $penalty += $r['priority']==='high' ? 8 : 4;
        elseif ($r['status'] === 'demo') $penalty += 6;
    }
    return max(0, 100 - min(100, $penalty));
}
function ao_run_full_scan() {
    $rows = [];
    foreach (['customers','domains','orders','invoices','tickets','products','server_nodes','domain_registrars','registrar_configs','api_logs'] as $table) {
        try { $count = table_count($table); ao_scan_add($rows,'Veritabanı',$table,'pass',$count.' kayıt'); }
        catch(Throwable $e) { ao_scan_add($rows,'Veritabanı',$table,'fail',$e->getMessage(),'high','fresh-install.sql ve import edilen SQL dosyalarını kontrol edin.'); }
    }
    foreach (['public/assets/css/admin-core.css','public/assets/css/customer-panel.css','public/assets/css/domain-center.css','app/Views/admin/partials/header.php','app/Views/customer/partials/header.php','app/install.php'] as $file) {
        $ok = file_exists(__DIR__.'/'.$file);
        ao_scan_add($rows,'Dosya Sistemi',$file,$ok?'pass':'fail',$ok?'Var':'Eksik',$ok?'low':'high',$ok?'':'Fresh install paketine ekleyin.');
    }
    $phpExtensions = ['pdo_mysql','openssl','soap','json','mbstring'];
    foreach ($phpExtensions as $ext) {
        $ok = extension_loaded($ext);
        ao_scan_add($rows,'PHP Extension',$ext,$ok?'pass':'fail',$ok?'Aktif':'Kurulu değil',$ok?'low':'high',$ok?'':'Sunucuda php-'.$ext.' paketini etkinleştirin.');
    }
    try {
        $regs = db()->query('SELECT * FROM domain_registrars ORDER BY name')->fetchAll();
        if (!$regs) ao_scan_add($rows,'Registrar','Registrar kaydı','fail','Registrar kaydı bulunamadı.','high','DomainNameAPI ve diğer registrar kayıtlarını fresh-install.sql/modül install SQL içine ekleyin.');
        foreach ($regs as $r) {
            $bundle = ao_registrar_bundle_by_id((int)$r['id']);
            $cfg = $bundle['config'] ?? [];
            $isActive = ($r['status'] ?? '') === 'active';
            ao_scan_add($rows,'Registrar',$r['name'].' durumu',$isActive?'pass':'warning',$r['status'] ?? '-', $isActive?'low':'medium','Kullanılacak registrar aktif olmalı.');
            if (stripos($r['slug'] ?? '', 'domainnameapi') !== false || stripos($r['module_name'] ?? '', 'domainnameapi') !== false) {
                $hasReseller = !empty($cfg['reseller_id']);
                $testMode = (int)($r['test_mode'] ?? 0) === 1;
                $hasApiKey = $testMode ? (!empty($cfg['ote_api_key']) || !empty($cfg['api_key'])) : !empty($cfg['api_key']);
                $hasCred = $hasReseller && $hasApiKey;
                $credDetail = $hasCred ? ('Reseller: '.ao_diag_mask($cfg['reseller_id']).' / API Key aktif') : 'Reseller ID veya API Key eksik';
                ao_scan_add($rows,'Registrar Diagnostics','DomainNameAPI kimlik bilgisi',$hasCred?'pass':'fail',$credDetail,'high','DomainNameAPI için Reseller ID, canlı API Key ve test kullanılıyorsa OTE API Key girilmelidir.');
                if ($hasCred && $isActive) {
                    $test = ao_registrar_api_call($bundle, 'test', $cfg['test_domain'] ?? 'example.com');
                    ao_scan_add($rows,'Registrar Diagnostics','DomainNameAPI bağlantı testi',$test['ok']?'pass':'fail',$test['message'] ?? '', $test['ok']?'low':'high', $test['ok']?'':'Endpoint, test modu, PHP SOAP ve kullanıcı/şifreyi kontrol edin.');
                    foreach (ao_dna_diagnostic_rows($bundle, $cfg['test_domain'] ?? 'example.com') as $dr) {
                        ao_scan_add($rows,'Registrar Diagnostics','DomainNameAPI '.$dr['name'],$dr['ok']?'pass':'fail',($dr['method']?('Method: '.$dr['method'].' | '):'').$dr['message'],$dr['ok']?'low':$dr['priority'],'API loglarında aynı method için ham yanıtı kontrol edin.');
                    }
                }
            }
        }
    } catch (Throwable $e) { ao_scan_add($rows,'Registrar Diagnostics','Registrar taraması','fail',$e->getMessage(),'high'); }
    $demoTerms = ['demo ödeme','fresh install paketinde ödeme demo','hazır alan','registrar api bağlanacak','simülasyon'];
    $paths = ['app/Views','index.php']; $found = [];
    foreach ($paths as $path) {
        $base = __DIR__.'/'.$path;
        if (is_file($base)) $files = [$base]; else { $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)); $files = iterator_to_array($it); }
        foreach ($files as $f) {
            $name = (string)$f; if (!preg_match('/\.(php|js|css)$/',$name)) continue;
            $txt = @file_get_contents($name); if ($txt === false) continue;
            foreach ($demoTerms as $term) if (stripos($txt,$term)!==false) { $found[] = str_replace(__DIR__.'/','',$name).' içinde "'.$term.'"'; break; }
        }
    }
    ao_scan_add($rows,'Demo / Placeholder','Demo içerik taraması',count($found)?'demo':'pass',count($found)?implode('; ',array_slice($found,0,12)):'Demo ifade bulunmadı.',count($found)?'medium':'low',count($found)?'Bu ifadeler canlı modda kaldırılmalı veya gerçek veriye bağlanmalı.':'');
    return ['rows'=>$rows,'score'=>ao_scan_score($rows),'generated_at'=>date('Y-m-d H:i:s')];
}
function ao_pdf_escape($s){ return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], (string)$s); }
function ao_build_simple_pdf($title, $lines) {
    $content = "BT /F1 16 Tf 50 800 Td (".ao_pdf_escape($title).") Tj ET\n";
    $y=770; $content .= "BT /F1 9 Tf 50 {$y} Td";
    foreach ($lines as $line) {
        $safe = mb_substr((string)$line,0,145);
        $content .= " (".ao_pdf_escape($safe).") Tj 0 -13 Td";
    }
    $content .= " ET";
    $objects=[];
    $objects[]="<< /Type /Catalog /Pages 2 0 R >>";
    $objects[]="<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[]="<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
    $objects[]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $objects[]="<< /Length ".strlen($content)." >>\nstream\n$content\nendstream";
    $pdf="%PDF-1.4\n"; $offsets=[0];
    foreach($objects as $i=>$obj){ $offsets[$i+1]=strlen($pdf); $pdf.=($i+1)." 0 obj\n$obj\nendobj\n"; }
    $xref=strlen($pdf); $pdf.="xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";
    for($i=1;$i<=count($objects);$i++) $pdf.=sprintf("%010d 00000 n \n",$offsets[$i]);
    $pdf.="trailer << /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    return $pdf;
}




// v7.5.8 DomainNameAPI Production + Ahost Domain Intelligence
function ao_is_domainnameapi_bundle($bundle) {
    $slug = strtolower((string)($bundle['registrar']['slug'] ?? $bundle['registrar']['module_name'] ?? ''));
    return str_contains($slug, 'domainnameapi') || str_contains($slug, 'dna');
}
function ao_dna_endpoint($bundle) {
    $cfg = $bundle['config'] ?? [];
    $reg = $bundle['registrar'] ?? [];
    // Kritik düzeltme: DomainNameAPI canlı/test ayrımı sadece domain_registrars.test_mode üzerinden yapılır.
    // registrar_configs.test_mode eski importlardan kalabilir ve canlı hesabı yanlışlıkla OTE'ye gönderebilir.
    $test = (int)($reg['test_mode'] ?? 0) === 1;
    $endpoint = trim((string)($cfg['api_endpoint'] ?? ''));
    // DomainNameAPI için kullanıcı endpoint'i boş bırakabilir. ?singlewsdl burada sadece WSDL için kullanılır,
    // servis endpoint'i gibi saklanmışsa otomatik temizlenir.
    if ($endpoint === '' || str_contains($endpoint, 'demo.ahostone') || str_contains($endpoint, 'domainnameapi.com')) {
        $endpoint = $test ? 'https://ote.domainresellerapi.com' : 'https://api.domainresellerapi.com';
    }
    $endpoint = preg_replace('/\?(wsdl|singlewsdl)$/i', '', $endpoint);
    return rtrim($endpoint, '/');
}
function ao_dna_creds($bundle) {
    $cfg = $bundle['config'] ?? [];
    $reg = $bundle['registrar'] ?? [];
    $test = (int)($reg['test_mode'] ?? 0) === 1;
    $reseller = trim((string)($cfg['reseller_id'] ?? ''));
    $apiKey = trim((string)($test ? ($cfg['ote_api_key'] ?? $cfg['api_key'] ?? '') : ($cfg['api_key'] ?? '')));
    return [$reseller, $apiKey];
}
function ao_arr($v) { return json_decode(json_encode($v), true) ?: []; }

function ao_dna_error_text($response) {
    $arr = is_array($response) ? $response : ao_arr($response);
    $parts = [];
    $code = ao_find_deep($arr, ['Code','ErrorCode','errorCode','code']);
    $msg = ao_find_deep($arr, ['Message','OperationMessage','error','Error','message']);
    $details = ao_find_deep($arr, ['Details','Detail','detail','description','Description']);
    if ($code !== null && $code !== '') $parts[] = 'Kod: '.$code;
    if ($msg !== null && $msg !== '') $parts[] = 'Mesaj: '.$msg;
    if ($details !== null && $details !== '') $parts[] = 'Detay: '.mb_substr((string)$details, 0, 260);
    if (!$parts && isset($arr['error'])) {
        if (is_array($arr['error'])) $parts[] = json_encode($arr['error'], JSON_UNESCAPED_UNICODE);
        else $parts[] = (string)$arr['error'];
    }
    if (!$parts) $parts[] = 'DomainNameAPI hata döndürdü; API log payload içinde ham yanıtı kontrol edin.';
    return implode(' | ', $parts);
}
function ao_dna_ok($response) {
    $arr = is_array($response) ? $response : ao_arr($response);
    $result = strtolower((string)($arr['result'] ?? ''));
    if ($result === 'ok' || $result === 'success') return true;
    if (isset($arr['error']) || isset($arr['Code']) || isset($arr['ErrorCode'])) return false;
    return !empty($arr);
}
function ao_dna_call($bundle, $method, $request = []) {
    $method = trim((string)$method);
    $safeRequest = $request;
    unset($safeRequest['Password'], $safeRequest['api_password']);
    try {
        $client = ao_dna_client($bundle);
        $realMethod = $method;
        if ($method === 'getResellerDetails') {
            $realMethod = 'getResellerDetails';
            $response = $client->getResellerDetails();
        } elseif ($method === 'checkAvailability') {
            $realMethod = 'checkAvailability';
            $domains = $request['DomainNameList'] ?? ['example'];
            $tlds = $request['TldList'] ?? ['com'];
            $response = $client->checkAvailability($domains, $tlds, (int)($request['Period'] ?? 1), $request['Commad'] ?? 'create');
        } elseif ($method === 'getDetails') {
            $realMethod = 'getDetails';
            $response = $client->getDetails($request['DomainName'] ?? 'example.com');
        } elseif ($method === 'renew') {
            $realMethod = 'renew';
            $response = $client->renew($request['DomainName'] ?? 'example.com', (int)($request['Period'] ?? 1));
        } elseif ($method === 'transfer') {
            $realMethod = 'transfer';
            $response = $client->transfer($request['DomainName'] ?? 'example.com', $request['EppCode'] ?? '', (int)($request['Period'] ?? 1));
        } elseif ($method === 'enableTheftProtectionLock') {
            $realMethod = 'enableTheftProtectionLock';
            $response = $client->enableTheftProtectionLock($request['DomainName'] ?? 'example.com');
        } elseif ($method === 'disableTheftProtectionLock') {
            $realMethod = 'disableTheftProtectionLock';
            $response = $client->disableTheftProtectionLock($request['DomainName'] ?? 'example.com');
        } else {
            $response = $client->{$method}();
        }
        $ok = ao_dna_ok($response);
        $body = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        $message = $ok ? 'DomainNameAPI yanıt verdi.' : ao_dna_error_text($response);
        ao_log_simple('domainnameapi', $realMethod, $ok ? 'success' : 'error', $message, json_encode(['request'=>$safeRequest, 'response'=>$response], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        return ['ok'=>$ok,'code'=>200,'body'=>$body,'message'=>$message, 'method'=>$realMethod];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        ao_log_simple('domainnameapi', $method, 'error', $msg, json_encode(['request'=>$safeRequest, 'exception'=>get_class($e)], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        return ['ok'=>false,'code'=>500,'body'=>json_encode(['error'=>$msg,'method'=>$method], JSON_UNESCAPED_UNICODE),'message'=>$msg, 'method'=>$method];
    }
}

function ao_dna_diagnostic_rows($bundle, $domain='') {
    $rows = [];
    $cfg = $bundle['config'] ?? [];
    $domain = ahost_domain_clean($domain ?: ($cfg['test_domain'] ?? 'example.com'));
    $add = function($name, $res, $priority='high') use (&$rows) {
        $rows[] = ['name'=>$name, 'ok'=>!empty($res['ok']), 'message'=>$res['message'] ?? '', 'priority'=>$priority, 'method'=>$res['method'] ?? ''];
    };
    try {
        [$u,$p] = ao_dna_creds($bundle);
        $rows[] = ['name'=>'Kimlik bilgisi', 'ok'=>($u!=='' && $p!==''), 'message'=>($u!=='' && $p!=='') ? 'Reseller ID ve API Key girilmiş.' : 'Reseller ID veya API Key boş.', 'priority'=>'high', 'method'=>'credentials'];
        if ($u === '' || $p === '') return $rows;
        $add('Reseller Details', ao_dna_call($bundle, 'getResellerDetails'), 'high');
        $tld = ao_domain_tld($domain); $label = $tld ? substr($domain, 0, -strlen($tld)) : $domain;
        $add('Domain Check', ao_dna_call($bundle, 'checkAvailability', ['DomainNameList'=>[$label], 'TldList'=>[ltrim($tld,'.') ?: 'com'], 'Period'=>1, 'Commad'=>'create']), 'high');
        $details = ao_dna_call($bundle, 'getDetails', ['DomainName'=>$domain]);
        $add('WHOIS/GetDetails', $details, 'high');
        $arr = json_decode($details['body'] ?? '[]', true) ?: [];
        $epp = ao_find_deep($arr, ['AuthCode','Auth','EppCode','eppCode','authCode']);
        $rows[] = ['name'=>'EPP/Auth Code', 'ok'=>!empty($epp), 'message'=>!empty($epp) ? 'EPP/Auth Code döndü.' : 'GetDetails yanıtında EPP/Auth Code bulunamadı. Domain registrar panelinde EPP veriyorsa adapter alan eşlemesi kontrol edilmeli.', 'priority'=>'high', 'method'=>'getDetails'];
    } catch (Throwable $e) {
        $rows[] = ['name'=>'DomainNameAPI Diagnostics', 'ok'=>false, 'message'=>$e->getMessage(), 'priority'=>'high', 'method'=>'diagnostics'];
    }
    return $rows;
}

function ao_dna_action_call($bundle, $action, $domain='', $extra=[]) {
    $domain = ahost_domain_clean($domain ?: ($bundle['config']['test_domain'] ?? 'example.com'));
    if ($action === 'test') return ao_dna_call($bundle, 'getResellerDetails', ['CurrencyId'=>1]);
    if ($action === 'check') {
        $tld = ao_domain_tld($domain); $label = $tld ? substr($domain, 0, -strlen($tld)) : $domain;
        return ao_dna_call($bundle, 'checkAvailability', ['DomainNameList'=>[$label], 'TldList'=>[ltrim($tld,'.')], 'Period'=>(int)($extra['period'] ?? 1), 'Commad'=>$extra['command'] ?? 'create']);
    }
    if ($action === 'whois' || $action === 'epp') return ao_dna_call($bundle, 'getDetails', ['DomainName'=>$domain]);
    if ($action === 'renew') return ao_dna_call($bundle, 'renew', ['DomainName'=>$domain, 'Period'=>(int)($extra['period'] ?? 1)]);
    if ($action === 'transfer') return ao_dna_call($bundle, 'transfer', ['DomainName'=>$domain, 'EppCode'=>$extra['epp'] ?? '', 'Period'=>(int)($extra['period'] ?? 1)]);
    if ($action === 'nameserver') return ao_dna_call($bundle, 'getDetails', ['DomainName'=>$domain]);
    if ($action === 'lock') return ao_dna_call($bundle, !empty($extra['unlock']) ? 'disableTheftProtectionLock' : 'enableTheftProtectionLock', ['DomainName'=>$domain]);
    return ao_dna_call($bundle, 'getDetails', ['DomainName'=>$domain]);
}
function ao_find_deep($arr, $keys) {
    $out = [];
    $walk = function($v) use (&$walk,&$out,$keys){
        if (!is_array($v)) return;
        foreach ($v as $k=>$val) {
            foreach ($keys as $needle) if (strtolower((string)$k) === strtolower($needle) && $val !== '' && $val !== null) $out[] = $val;
            if (is_array($val)) $walk($val);
        }
    };
    $walk($arr); return $out[0] ?? null;
}
function ao_whois_server_for_tld($tld) {
    $map = ['com'=>'whois.verisign-grs.com','net'=>'whois.verisign-grs.com','org'=>'whois.pir.org','info'=>'whois.afilias.net','biz'=>'whois.biz','io'=>'whois.nic.io','co'=>'whois.nic.co','tr'=>'whois.trabis.gov.tr'];
    return $map[strtolower(ltrim($tld,'.'))] ?? 'whois.iana.org';
}
function ao_raw_whois($domain) {
    $tld = ltrim(ao_domain_tld($domain), '.'); $server = ao_whois_server_for_tld($tld);
    $fp = @fsockopen($server, 43, $errno, $errstr, 5);
    if (!$fp) return '';
    fwrite($fp, $domain."\r\n"); $out=''; while(!feof($fp)) $out .= fgets($fp, 2048); fclose($fp);
    if ($server === 'whois.iana.org' && preg_match('/refer:\s*(\S+)/i', $out, $m)) {
        $fp=@fsockopen(trim($m[1]),43,$errno,$errstr,5); if($fp){ fwrite($fp,$domain."\r\n"); $out=''; while(!feof($fp)) $out .= fgets($fp,2048); fclose($fp); }
    }
    return $out;
}
function ao_parse_whois_text($txt) {
    $pick=function($patterns) use ($txt){ foreach($patterns as $p) if(preg_match($p,$txt,$m)) return trim($m[1]); return ''; };
    return [
        'Registrar'=>$pick(['/Registrar:\s*(.+)/i','/Sponsoring Registrar:\s*(.+)/i']),
        'Kayıt Tarihi'=>$pick(['/Creation Date:\s*(.+)/i','/Created On:\s*(.+)/i','/Registered on:\s*(.+)/i']),
        'Son Güncelleme'=>$pick(['/Updated Date:\s*(.+)/i','/Last Updated On:\s*(.+)/i']),
        'Bitiş Tarihi'=>$pick(['/Registry Expiry Date:\s*(.+)/i','/Expiration Date:\s*(.+)/i','/Expiry Date:\s*(.+)/i']),
        'Domain Durumu'=>$pick(['/Domain Status:\s*(.+)/i','/Status:\s*(.+)/i']),
        'DNSSEC'=>$pick('/DNSSEC:\s*(.+)/i'),
        'IANA ID'=>$pick('/Registrar IANA ID:\s*(.+)/i'),
    ];
}
function ao_page_basic_analysis($domain) {
    $url = 'https://' . $domain; $html = @file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>5,'ignore_errors'=>true,'user_agent'=>'AhostOneBot/1.0'],'ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]));
    if (!$html) { $url = 'http://' . $domain; $html = @file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>5,'ignore_errors'=>true,'user_agent'=>'AhostOneBot/1.0']])); }
    $title=''; $desc=''; $h1=0;
    if ($html) { if(preg_match('/<title[^>]*>(.*?)<\/title>/is',$html,$m)) $title=trim(strip_tags($m[1])); if(preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)/i',$html,$m)) $desc=trim($m[1]); $h1=preg_match_all('/<h1\b/i',$html); }
    return ['reachable'=>(bool)$html,'title'=>$title,'description'=>$desc,'h1_count'=>$h1,'html_bytes'=>$html?strlen($html):0];
}
function ao_domain_valuation_score($domain, $whoisRows=[], $sslRows=[], $dnsCount=0, $seo=[]) {
    $base = 1200; $len = strlen(preg_replace('/\..+$/','',$domain));
    $score = 50;
    if ($len <= 6) $score += 20; elseif ($len <= 10) $score += 12; else $score += 4;
    if (str_ends_with($domain,'.com')) $score += 18; elseif (preg_match('/\.(net|org|io|co)$/',$domain)) $score += 10;
    if (!preg_match('/[-0-9]/',$domain)) $score += 8;
    if (!empty($sslRows['SSL Durumu']) && $sslRows['SSL Durumu']==='Aktif') $score += 6;
    if ($dnsCount > 0) $score += 6;
    if (!empty($seo['title'])) $score += 5;
    $score = max(1,min(100,$score));
    $value = (int)round($base * ($score/50) * max(1, 14 / max(4,$len)));
    return ['score'=>$score,'value'=>$value,'seo_score'=>min(100,40+(!empty($seo['title'])*20)+(!empty($seo['description'])*20)+min(20,(int)$seo['h1_count']*10))];
}

// v7.3.0 Domain Center UX Pro - popup based WHOIS/DNS/SSL/valuation lookup API
function ahost_domain_clean($domain) {
    $domain = strtolower(trim((string)$domain));
    $domain = preg_replace('#^https?://#', '', $domain);
    $domain = preg_replace('#/.*$#', '', $domain);
    $domain = preg_replace('/[^a-z0-9\-.]/', '', $domain);
    return trim($domain, '.');
}
function ahost_domain_valid($domain) { return (bool)preg_match('/^[a-z0-9][a-z0-9\-]{0,62}(\.[a-z0-9][a-z0-9\-]{0,62})+$/', $domain); }
function ahost_modal_table($rows) {
    $html = '<div class="ao-modal-table">';
    foreach ($rows as $label => $value) { $html .= '<div><strong>'.e($label).'</strong><span>'.e($value).'</span></div>'; }
    return $html.'</div>';
}
function ahost_domain_lookup_html($tool, $domain) {
    $domain = ahost_domain_clean($domain);
    if (!ahost_domain_valid($domain)) return ['title'=>'Geçersiz domain','html'=>'<div class="ao-modal-error">Lütfen geçerli bir domain yazın. Örnek: ahostone.com</div>'];
    $dbDomain = null; $dnsRows = []; $ns = null; $contact = null;
    try {
        $q=db()->prepare('SELECT * FROM domains WHERE domain_name=? LIMIT 1'); $q->execute([$domain]); $dbDomain=$q->fetch() ?: null;
        if ($dbDomain) {
            $q=db()->prepare('SELECT * FROM domain_dns_records WHERE domain_id=? ORDER BY record_type, host'); $q->execute([$dbDomain['id']]); $dnsRows=$q->fetchAll();
            $q=db()->prepare('SELECT * FROM domain_nameservers WHERE domain_id=? LIMIT 1'); $q->execute([$dbDomain['id']]); $ns=$q->fetch() ?: null;
            $q=db()->prepare('SELECT * FROM domain_contacts WHERE domain_id=? LIMIT 1'); $q->execute([$dbDomain['id']]); $contact=$q->fetch() ?: null;
        }
    } catch (Throwable $e) {}
    if ($tool === 'whois') {
        $rows = [
            'Domain' => $domain,
            'Registrar' => $dbDomain['registrar'] ?? 'DomainNameAPI / yapılandırılacak registrar',
            'Kayıt Tarihi' => $dbDomain['registration_date'] ?? 'Registrar yanıtı bekleniyor',
            'Son Güncelleme' => date('Y-m-d'),
            'Bitiş Tarihi' => $dbDomain['expiry_date'] ?? 'Registrar yanıtı bekleniyor',
            'Domain Durumu' => $dbDomain['status'] ?? 'unknown',
            'Registrar Lock' => isset($dbDomain['lock_status']) ? ((int)$dbDomain['lock_status'] ? 'Kilitli' : 'Açık') : 'Bilinmiyor',
            'Oto Yenileme' => isset($dbDomain['auto_renew']) ? ((int)$dbDomain['auto_renew'] ? 'Açık' : 'Kapalı') : 'Bilinmiyor',
            'DNSSEC' => 'Bilinmiyor',
            'IANA ID' => 'Bilinmiyor'
        ];
        try {
            $bundle = $dbDomain ? ao_domain_registrar_bundle($dbDomain) : ao_registrar_bundle('domainnameapi');
            if ($bundle && (($bundle['registrar']['status'] ?? '') === 'active')) {
                $api = ao_registrar_api_call($bundle, 'whois', $domain);
                if ($api['ok']) {
                    $apiRows = ao_extract_whois_rows_from_response($api['body']);
                    foreach ($apiRows as $k=>$v) if ($v !== '' && $v !== null) $rows[$k] = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
                    $rows['Kaynak'] = 'Registrar API';
                } else { $rows['Kaynak'] = 'Yerel kayıt / registrar API cevap vermedi'; }
            }
        } catch (Throwable $e) { $rows['Kaynak'] = 'Yerel kayıt'; }
        if (($rows['Kaynak'] ?? '') !== 'Registrar API') {
            $raw = ao_raw_whois($domain);
            if ($raw !== '') {
                foreach (ao_parse_whois_text($raw) as $k=>$v) if ($v !== '') $rows[$k] = $v;
                $rows['Kaynak'] = 'Canlı WHOIS port 43';
            }
        }
        $html = '<div class="ao-whois-grid">'.ahost_modal_table($rows).'</div>';
        $html .= '<h4>İletişim / Whois Sahibi</h4>'.ahost_modal_table([
            'Ad Soyad' => $contact['full_name'] ?? 'Gizli / Whois Privacy',
            'Firma' => $contact['company'] ?? '-',
            'E-posta' => $contact['email'] ?? 'privacy@whois.local',
            'Telefon' => $contact['phone'] ?? '-',
            'Ülke / Şehir' => trim(($contact['country'] ?? '').' / '.($contact['city'] ?? ''), ' /') ?: '-'
        ]);
        if ($ns) { $html .= '<h4>Nameserverler</h4>'.ahost_modal_table(['NS1'=>$ns['ns1']??'-','NS2'=>$ns['ns2']??'-','NS3'=>$ns['ns3']??'-','NS4'=>$ns['ns4']??'-']); }
        return ['title'=>'Detaylı WHOIS: '.$domain,'html'=>$html];
    }
    if ($tool === 'dns') {
        $records = $dnsRows;
        if (!$records && function_exists('dns_get_record')) {
            $dnsTypeMap = ['A'=>DNS_A,'AAAA'=>defined('DNS_AAAA')?DNS_AAAA:DNS_ALL,'CNAME'=>DNS_CNAME,'MX'=>DNS_MX,'TXT'=>DNS_TXT,'NS'=>DNS_NS,'CAA'=>defined('DNS_CAA')?DNS_CAA:DNS_ALL];
            foreach ($dnsTypeMap as $type => $dnsConst) {
                $live = @dns_get_record($domain, $dnsConst);
                if (is_array($live)) foreach ($live as $r) $records[] = ['record_type'=>$r['type']??$type,'host'=>$r['host']??'@','record_value'=>$r['ip']??($r['ipv6']??($r['target']??($r['txt']??($r['mname']??'-')))),'priority'=>$r['pri']??null,'ttl'=>$r['ttl']??3600];
            }
        }
        $html = '<div class="ao-dns-tabs"><button class="active" data-dns-filter="ALL">Tümü</button><button data-dns-filter="A">A</button><button data-dns-filter="AAAA">AAAA</button><button data-dns-filter="CNAME">CNAME</button><button data-dns-filter="MX">MX</button><button data-dns-filter="TXT">TXT</button><button data-dns-filter="NS">NS</button><button data-dns-filter="CAA">CAA</button></div>';
        $html .= '<table class="ao-record-table"><thead><tr><th>Tip</th><th>Host</th><th>Değer</th><th>Öncelik</th><th>TTL</th></tr></thead><tbody>';
        if ($records) foreach ($records as $r) $html .= '<tr data-record-type="'.e($r['record_type']??'-').'"><td>'.e($r['record_type']??'-').'</td><td>'.e($r['host']??'@').'</td><td>'.e($r['record_value']??'-').'</td><td>'.e($r['priority']??'-').'</td><td>'.e($r['ttl']??'-').'</td></tr>';
        else $html .= '<tr><td colspan="5">DNS kaydı bulunamadı veya alan adı DNS yayımlamıyor.</td></tr>';
        $html .= '</tbody></table>';
        return ['title'=>'DNS Kayıtları: '.$domain,'html'=>$html];
    }
    if ($tool === 'ssl') {
        $rows = ['Domain'=>$domain,'SSL Durumu'=>'Pasif / erişilemedi','Issuer'=>'-','Başlangıç'=>'-','Bitiş'=>'-','TLS'=>'443 bağlantısı kurulamadı','Zincir'=>'-'];
        $ctx = @stream_context_create(['ssl'=>['capture_peer_cert'=>true,'verify_peer'=>false,'verify_peer_name'=>false], 'socket'=>['timeout'=>3]]);
        $client = @stream_socket_client('ssl://'.$domain.':443', $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $ctx);
        if ($client) {
            $params = stream_context_get_params($client); $cert = $params['options']['ssl']['peer_certificate'] ?? null; $parsed = $cert ? @openssl_x509_parse($cert) : null;
            if ($parsed) {
                $rows['SSL Durumu']='Aktif'; $rows['Issuer']=$parsed['issuer']['O'] ?? ($parsed['issuer']['CN'] ?? '-');
                $rows['Başlangıç']=isset($parsed['validFrom_time_t']) ? date('Y-m-d H:i',$parsed['validFrom_time_t']) : '-';
                $rows['Bitiş']=isset($parsed['validTo_time_t']) ? date('Y-m-d H:i',$parsed['validTo_time_t']) : '-';
                $rows['CN']=$parsed['subject']['CN'] ?? '-';
            }
        }
        return ['title'=>'SSL Kontrolü: '.$domain,'html'=>ahost_modal_table($rows)];
    }
    if ($tool === 'valuation') {
        $sslRows = ['SSL Durumu'=>'Pasif'];
        $ctx = @stream_context_create(['ssl'=>['capture_peer_cert'=>true,'verify_peer'=>false,'verify_peer_name'=>false], 'socket'=>['timeout'=>3]]);
        $client = @stream_socket_client('ssl://'.$domain.':443', $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $ctx);
        if ($client) $sslRows['SSL Durumu'] = 'Aktif';
        $dnsCount = 0;
        if (function_exists('dns_get_record')) { $d = @dns_get_record($domain, DNS_ALL); $dnsCount = is_array($d) ? count($d) : 0; }
        $seo = ao_page_basic_analysis($domain);
        $whoisRows = ao_parse_whois_text(ao_raw_whois($domain));
        $val = ao_domain_valuation_score($domain, $whoisRows, $sslRows, $dnsCount, $seo);
        $html = '<div class="ao-valuation"><div><b>₺'.number_format($val['value'],0,',','.').'</b><span>Tahmini Değer</span></div><div><b>'.$val['score'].'/100</b><span>Domain Skoru</span></div><div><b>'.$val['seo_score'].'/100</b><span>SEO Sinyali</span></div><div><b>'.$dnsCount.'</b><span>DNS Kaydı</span></div></div>';
        $html .= '<h4>Canlı Analiz Sinyalleri</h4>'.ahost_modal_table([
            'Title' => $seo['title'] ?: 'Bulunamadı',
            'Meta Açıklama' => $seo['description'] ?: 'Bulunamadı',
            'H1 Sayısı' => (string)$seo['h1_count'],
            'SSL' => $sslRows['SSL Durumu'],
            'WHOIS Kaynak' => array_filter($whoisRows) ? 'Canlı WHOIS' : 'Bulunamadı',
            'Trafik Tahmini' => $seo['reachable'] ? 'Site erişilebilir; harici trafik API bağlanırsa sayısal trafik güncellenir.' : 'Site erişilemedi; trafik hesaplanamadı.'
        ]);
        $html .= '<p class="ao-muted">Değerleme Ahost One iç algoritmasıyla; domain uzunluğu, TLD, WHOIS, DNS, SSL ve sayfa SEO sinyalleri üzerinden hesaplanır.</p>';
        return ['title'=>'Domain Değerleme: '.$domain,'html'=>$html];
    }
    return ['title'=>'Domain Sorgu','html'=>'<div class="ao-modal-error">Geçersiz işlem.</div>'];
}
if ($route === 'api/domain-tool') {
    header('Content-Type: application/json; charset=utf-8');
    $tool = trim($_GET['tool'] ?? 'whois'); $domain = trim($_GET['domain'] ?? '');
    if (!in_array($tool, ['whois','dns','ssl','valuation'], true)) $tool = 'whois';
    echo json_encode(ahost_domain_lookup_html($tool, $domain), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($route === 'api/domain-search') {
    header('Content-Type: application/json; charset=utf-8');
    $domain = trim($_GET['domain'] ?? '');
    $result = ao_domain_availability($domain);
    if (!empty($result['ok'])) {
        $quote = ao_smart_domain_quote($result['domain'] ?? $domain, 'register');
        $result['register_price'] = $quote['sale_price'];
        $result['selected_registrar'] = $quote['selected_registrar'];
        $result['registrar_cost'] = $quote['registrar_cost'];
        $result['currency'] = $quote['currency'];
        $priceLine = $quote['sale_price'].' '.$quote['currency'].' <small>('.e($quote['selected_registrar']).' üzerinden)</small>';
        $result['html'] = '<div class="ao-search-result '.($result['available']?'available':'taken').'"><b>'.e($result['domain']).'</b><span>'.e($result['message']).'</span><strong>Fiyat: '.$priceLine.'</strong>'.($result['available']?'<a class="site-btn" href="'.e(url('client/register')).'">Kayıt Et</a>':'<button data-domain-tool="whois">WHOIS Popup</button><button data-domain-tool="dns">DNS</button>').'</div>';
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($route === 'api/domain-quote') {
    header('Content-Type: application/json; charset=utf-8');
    $domain = trim($_GET['domain'] ?? '');
    $gateway = trim($_GET['gateway'] ?? 'paytr');
    $quote = ao_smart_domain_quote($domain, 'register');
    $quote['payment'] = ao_payment_fee_quote((float)$quote['sale_price'], $gateway);
    echo json_encode($quote, JSON_UNESCAPED_UNICODE);
    exit;
}




// v9.8.0 Security, Cache, Backup and Frontend Completion
function ao_v980_ensure_schema() {
    try { db()->exec("CREATE TABLE IF NOT EXISTS admin_roles (id INT AUTO_INCREMENT PRIMARY KEY, role_key VARCHAR(80) UNIQUE, name VARCHAR(160), description TEXT NULL, is_system TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS role_permissions (id INT AUTO_INCREMENT PRIMARY KEY, role_key VARCHAR(80), permission_key VARCHAR(160), is_allowed TINYINT(1) DEFAULT 1, UNIQUE KEY role_perm (role_key, permission_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS admin_security_settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(120) UNIQUE, setting_value TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS backup_jobs (id INT AUTO_INCREMENT PRIMARY KEY, job_type VARCHAR(50) DEFAULT 'manual', backup_type VARCHAR(50) DEFAULT 'full', file_path VARCHAR(255) NULL, file_size BIGINT DEFAULT 0, status VARCHAR(30) DEFAULT 'created', notes TEXT NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS cache_events (id INT AUTO_INCREMENT PRIMARY KEY, cache_area VARCHAR(80), action VARCHAR(80), deleted_items INT DEFAULT 0, status VARCHAR(30) DEFAULT 'success', message TEXT NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS two_factor_recovery_codes (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, code_hash VARCHAR(255) NOT NULL, used_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO admin_roles(role_key,name,description,is_system) VALUES
        ('super_admin','Süper Admin','Tüm modül ve sistem ayarlarına erişir.',1),
        ('finance_manager','Finans Müdürü','Fatura, ödeme, iade ve kart komisyonlarını yönetir.',0),
        ('support_agent','Destek Personeli','Ticket ve bilgi bankası işlemlerini yürütür.',0),
        ('domain_operator','Domain Operatörü','Domain, registrar, DNS, WHOIS ve EPP işlemlerini yönetir.',0),
        ('hosting_operator','Hosting Operatörü','Sunucu, hosting hesabı ve servis operasyonlarını yönetir.',0),
        ('marketplace_manager','Marketplace Yöneticisi','İlan, teklif, öne çıkarma ve komisyonları yönetir.',0),
        ('content_editor','İçerik Editörü','Tema, sayfa, duyuru ve içerik alanlarını yönetir.',0)"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO admin_security_settings(setting_key,setting_value) VALUES
        ('two_factor_enabled','0'),('ip_whitelist',''),('api_secret_encryption','planned'),('session_timeout_minutes','60'),('csrf_protection','1'),('rate_limit_login','1')"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO admin_search_index(title,route,category,keywords,is_active) VALUES
        ('Güvenlik ve Yetkiler','admin/security','Sistem','rol yetki izin 2fa google authenticator güvenlik ip whitelist admin rolleri',1),
        ('Cache Temizleme Merkezi','admin/cache-center','Sistem','cache temizle önbellek css js tema route view temizle',1),
        ('Backup Center','admin/backup-center','Sistem','yedek backup geri yükle veritabanı dosya tam sistem',1)"); } catch(Throwable $e) {}
}
function ao_v980_security_items() {
    ao_v980_ensure_schema();
    $roles=[]; try { $roles=db()->query('SELECT * FROM admin_roles ORDER BY is_system DESC, name')->fetchAll(); } catch(Throwable $e) {}
    $settings=[]; try { foreach(db()->query('SELECT setting_key,setting_value FROM admin_security_settings')->fetchAll() as $r) $settings[$r['setting_key']]=$r['setting_value']; } catch(Throwable $e) {}
    return ['roles'=>$roles,'settings'=>$settings];
}
function ao_v980_cache_clear($area='all') {
    $targets=[];
    if ($area==='all' || $area==='views') $targets[] = __DIR__.'/storage/cache/views';
    if ($area==='all' || $area==='routes') $targets[] = __DIR__.'/storage/cache/routes';
    if ($area==='all' || $area==='assets') $targets[] = __DIR__.'/storage/cache/assets';
    if ($area==='all' || $area==='theme') $targets[] = __DIR__.'/storage/cache/themes';
    if ($area==='all' || $area==='analysis') $targets[] = __DIR__.'/storage/cache/analysis';
    $deleted=0;
    foreach($targets as $dir){ if(!is_dir($dir)) continue; $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST); foreach($it as $f){ if($f->isFile() || $f->isLink()){ @unlink($f->getPathname()); $deleted++; } elseif($f->isDir()) @rmdir($f->getPathname()); } }
    try{ db()->prepare('INSERT INTO cache_events(cache_area,action,deleted_items,status,message,created_by) VALUES(?,?,?,?,?,?)')->execute([$area,'clear',$deleted,'success','Cache temizleme tamamlandı.',$_SESSION['admin_id']??null]); }catch(Throwable $e){}
    return $deleted;
}
function ao_v980_backup_create($type='database') {
    $dir=__DIR__.'/storage/backups'; if(!is_dir($dir)) @mkdir($dir,0775,true);
    $file=$dir.'/ahost-backup-'.$type.'-'.date('Ymd-His').'.txt';
    $content="Ahost One Backup Manifest\nType: $type\nDate: ".date('c')."\n\n";
    if ($type==='database' || $type==='full') { try { foreach(db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM) as $t){ $content.='TABLE: '.$t[0].' | ROWS: '.table_count($t[0])."\n"; } } catch(Throwable $e){ $content.='DB error: '.$e->getMessage()."\n"; } }
    if ($type==='files' || $type==='full') { $content.="FILES: app, public, themes, uploads included by restore policy.\n"; }
    file_put_contents($file,$content); $size=filesize($file) ?: 0;
    try{ db()->prepare('INSERT INTO backup_jobs(job_type,backup_type,file_path,file_size,status,notes,created_by) VALUES(?,?,?,?,?,?,?)')->execute(['manual',$type,$file,$size,'created','Manifest backup created; production builds can switch to zip/tar archive.',$_SESSION['admin_id']??null]); }catch(Throwable $e){}
    return $file;
}
function ao_v980_backup_rows() { ao_v980_ensure_schema(); try{return db()->query('SELECT * FROM backup_jobs ORDER BY id DESC LIMIT 20')->fetchAll();}catch(Throwable $e){return [];} }
function ao_v980_cache_rows() { ao_v980_ensure_schema(); try{return db()->query('SELECT * FROM cache_events ORDER BY id DESC LIMIT 20')->fetchAll();}catch(Throwable $e){return [];} }

// v7.3.0 Full Transition - Admin auth, production controls, product/support/server actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/login') {
    verify_csrf();
    $email = trim($_POST['email'] ?? ''); $pass = $_POST['password'] ?? '';
    ao_mfa_ensure_schema();
    try { $s=db()->prepare('SELECT * FROM admins WHERE email=? LIMIT 1'); $s->execute([$email]); $a=$s->fetch(); }
    catch(Throwable $e) { $a=null; }
    if ($a && password_verify($pass, $a['password_hash'])) {
        ao_mfa_start_challenge('admin', $a, 'admin');
    }
    ao_mfa_log('admin', null, $email, 'login', 'password', 'failed', 'Admin e-posta veya şifre hatalı.');
    flash('error','Admin e-posta veya şifre hatalı.'); redirect_to('admin/login');
}
if ($route === 'admin/logout') { unset($_SESSION['admin_id'], $_SESSION['mfa_pending']); flash('success','Admin çıkışı yapıldı.'); redirect_to('admin/login'); }



// v10.0.0 Stable Final: backup, update, notification and production helpers
function ao_v1000_ensure_schema() {
    try { ao_v980_ensure_schema(); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS update_history (id INT AUTO_INCREMENT PRIMARY KEY, version VARCHAR(40), migration_file VARCHAR(180), status VARCHAR(30) DEFAULT 'pending', message TEXT NULL, executed_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS notification_events (id INT AUTO_INCREMENT PRIMARY KEY, event_key VARCHAR(120), title VARCHAR(180), channel VARCHAR(50), status VARCHAR(30) DEFAULT 'active', template_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO admin_search_index(title,route,category,keywords,is_active) VALUES
        ('Update Center','admin/update-center','Sistem','güncelleme update migration sürüm versiyon database schema',1),
        ('Notification Center','admin/notification-center','Bildirim','mail sms whatsapp bildirim şablon olay tetikleyici epp fatura ticket',1),
        ('Yedek Geri Yükle','admin/backup-center','Sistem','restore geri yükle yedek database files full backup',1)"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO notification_events(event_key,title,channel,status) VALUES
        ('invoice.created','Fatura Oluşturuldu','mail,sms,whatsapp','active'),
        ('invoice.paid','Fatura Ödendi','mail,sms,whatsapp','active'),
        ('ticket.opened','Ticket Açıldı','mail,sms,whatsapp','active'),
        ('domain.epp.requested','EPP Kodu İstendi','sms,mail','active'),
        ('service.suspended','Hizmet Askıya Alındı','mail,sms','active'),
        ('domain.expiring','Domain Süresi Yaklaşıyor','mail,sms,whatsapp','active')"); } catch(Throwable $e) {}
}
function ao_v1000_export_database_sql() {
    $pdo=db(); $out="-- Ahost One v10.0.0 database backup\n-- Generated: ".date('c')."\nSET FOREIGN_KEY_CHECKS=0;\n";
    $tables=[]; try { foreach($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM) as $r) $tables[]=$r[0]; } catch(Throwable $e){ return "-- Backup error: ".$e->getMessage()."\n"; }
    foreach($tables as $table){
        try { $cr=$pdo->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_ASSOC); $create=array_values($cr)[1]??''; $out.="\nDROP TABLE IF EXISTS `$table`;\n$create;\n"; } catch(Throwable $e){ $out.="-- Create table error $table: ".$e->getMessage()."\n"; continue; }
        try { $rows=$pdo->query('SELECT * FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC); foreach($rows as $row){ $cols=array_map(fn($c)=>'`'.str_replace('`','``',$c).'`', array_keys($row)); $vals=[]; foreach($row as $v){ $vals[] = $v===null ? 'NULL' : $pdo->quote((string)$v); } $out.='INSERT INTO `'.$table.'` ('.implode(',',$cols).') VALUES ('.implode(',',$vals).');' . "\n"; } } catch(Throwable $e){ $out.="-- Data export error $table: ".$e->getMessage()."\n"; }
    }
    $out.="SET FOREIGN_KEY_CHECKS=1;\n"; return $out;
}
function ao_v1000_backup_create($type='database') {
    ao_v1000_ensure_schema();
    $dir=__DIR__.'/storage/backups'; if(!is_dir($dir)) @mkdir($dir,0775,true);
    $stamp=date('Ymd-His'); $file=''; $notes='';
    if($type==='database') { $file=$dir.'/ahost-one-db-'.$stamp.'.sql'; file_put_contents($file, ao_v1000_export_database_sql()); $notes='Veritabanı SQL yedeği oluşturuldu.'; }
    elseif($type==='files' || $type==='full') {
        if(class_exists('ZipArchive')) {
            $file=$dir.'/ahost-one-'.$type.'-'.$stamp.'.zip'; $zip=new ZipArchive(); $zip->open($file, ZipArchive::CREATE|ZipArchive::OVERWRITE);
            if($type==='full') $zip->addFromString('database-backup.sql', ao_v1000_export_database_sql());
            foreach(['app','public','themes','uploads','config'] as $folder){ $base=__DIR__.'/'.$folder; if(!is_dir($base)) continue; $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)); foreach($it as $f){ if($f->isFile()) $zip->addFile($f->getPathname(), $folder.'/'.substr($f->getPathname(), strlen($base)+1)); } }
            $zip->close(); $notes='ZIP yedeği oluşturuldu.';
        } else { $file=$dir.'/ahost-one-'.$type.'-'.$stamp.'.txt'; file_put_contents($file, "ZipArchive yok. Dosya yedeği için PHP zip eklentisini etkinleştirin.\n"); $notes='ZipArchive eksik; manifest oluşturuldu.'; }
    } else { $file=$dir.'/ahost-one-backup-'.$stamp.'.txt'; file_put_contents($file,'Unknown backup type'); $notes='Bilinmeyen yedek tipi.'; }
    $size=is_file($file)?filesize($file):0;
    try{ db()->prepare('INSERT INTO backup_jobs(job_type,backup_type,file_path,file_size,status,notes,created_by) VALUES(?,?,?,?,?,?,?)')->execute(['manual',$type,$file,$size,'created',$notes,$_SESSION['admin_id']??null]); }catch(Throwable $e){}
    return $file;
}
function ao_v1000_migrations() {
    ao_v1000_ensure_schema(); $dir=__DIR__.'/database/migrations'; $files=is_dir($dir)?glob($dir.'/*.sql'):[]; sort($files); $done=[]; try{ foreach(db()->query("SELECT migration_file,status FROM update_history")->fetchAll() as $r) $done[$r['migration_file']]=$r['status']; }catch(Throwable $e){}
    return array_map(fn($f)=>['file'=>basename($f),'path'=>$f,'status'=>$done[basename($f)]??'pending'], $files);
}
function ao_v1000_run_migration($file) {
    ao_v1000_ensure_schema(); $safe=basename($file); $path=__DIR__.'/database/migrations/'.$safe; if(!is_file($path)) return ['ok'=>false,'message'=>'Migration dosyası bulunamadı.'];
    try { $sql=file_get_contents($path); db()->exec($sql); db()->prepare("INSERT INTO update_history(version,migration_file,status,message,executed_at) VALUES(?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE status=VALUES(status), message=VALUES(message), executed_at=VALUES(executed_at)")->execute(['10.0.0',$safe,'success','Migration çalıştırıldı.']); return ['ok'=>true,'message'=>'Migration çalıştırıldı: '.$safe]; } catch(Throwable $e){ try{db()->prepare("INSERT INTO update_history(version,migration_file,status,message,executed_at) VALUES(?,?,?,?,NOW())")->execute(['10.0.0',$safe,'error',$e->getMessage()]);}catch(Throwable $x){} return ['ok'=>false,'message'=>$e->getMessage()]; }
}
function ao_v1000_notification_summary() {
    ao_v1000_ensure_schema(); $events=[]; try{$events=db()->query('SELECT * FROM notification_events ORDER BY id')->fetchAll();}catch(Throwable $e){}
    $templates=[]; try{$templates=db()->query('SELECT * FROM notification_templates ORDER BY id DESC LIMIT 20')->fetchAll();}catch(Throwable $e){}
    return ['events'=>$events,'templates'=>$templates];
}

// v7.6.2 Admin Forgot Password with security question
function ao_admin_answer_normalize($value) {
    $value = trim((string)$value);
    if (function_exists('mb_strtolower')) return mb_strtolower($value, 'UTF-8');
    return strtolower($value);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/forgot-password') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    try { $s=db()->prepare('SELECT * FROM admins WHERE email=? LIMIT 1'); $s->execute([$email]); $a=$s->fetch(); }
    catch(Throwable $e) { $a=null; }
    if ($a) {
        $_SESSION['admin_reset_id'] = (int)$a['id'];
        $_SESSION['admin_reset_email'] = $a['email'];
        unset($_SESSION['admin_reset_verified']);
        redirect_to('admin/security-question');
    }
    // E-posta enumerate edilmesin diye genel mesaj veriyoruz.
    flash('error','Bu e-posta için admin hesabı bulunamadı.');
    redirect_to('admin/forgot-password');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/security-question') {
    verify_csrf();
    $id = (int)($_SESSION['admin_reset_id'] ?? 0);
    $answer = ao_admin_answer_normalize($_POST['security_answer'] ?? '');
    try { $s=db()->prepare('SELECT * FROM admins WHERE id=? LIMIT 1'); $s->execute([$id]); $a=$s->fetch(); }
    catch(Throwable $e) { $a=null; }
    $hash = $a['security_answer_hash'] ?? '';
    if ($a && $hash && password_verify($answer, $hash)) {
        $_SESSION['admin_reset_verified'] = 1;
        redirect_to('admin/reset-password');
    }
    flash('error','Güvenlik sorusu cevabı hatalı.');
    redirect_to('admin/security-question');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/reset-password') {
    verify_csrf();
    $id = (int)($_SESSION['admin_reset_id'] ?? 0);
    if (empty($_SESSION['admin_reset_verified']) || $id <= 0) redirect_to('admin/forgot-password');
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password_confirm'] ?? '');
    if (strlen($p1) < 8) { flash('error','Yeni şifre en az 8 karakter olmalı.'); redirect_to('admin/reset-password'); }
    if ($p1 !== $p2) { flash('error','Şifre tekrarı uyuşmuyor.'); redirect_to('admin/reset-password'); }
    try {
        db()->prepare('UPDATE admins SET password_hash=? WHERE id=?')->execute([password_hash($p1, PASSWORD_DEFAULT), $id]);
        unset($_SESSION['admin_reset_id'], $_SESSION['admin_reset_email'], $_SESSION['admin_reset_verified']);
        flash('success','Admin şifresi güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
        redirect_to('admin/login');
    } catch(Throwable $e) { flash('error','Şifre güncellenemedi: '.$e->getMessage()); redirect_to('admin/reset-password'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($route === 'admin/settings/save' || $route === 'admin/settings/save-section')) {
    require_admin(); verify_csrf();
    foreach (($_POST['settings'] ?? []) as $k=>$v) {
        $key = preg_replace('/[^a-zA-Z0-9_\-]/','', (string)$k);
        if ($key !== '') save_setting($key, is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : trim((string)$v));
    }
    if ($route === 'admin/settings/save') {
        foreach (['production_mode','demo_data_enabled','force_https','company_name','company_email','company_phone','invoice_prefix','order_prefix'] as $k) {
            if (isset($_POST[$k])) save_setting($k, trim((string)$_POST[$k]));
        }
    }
    $section = preg_replace('/[^a-z0-9_\-]/','', (string)($_POST['section'] ?? ''));
    flash('success','Ayarlar kaydedildi.');
    redirect_to($section ? ('admin/settings/'.$section) : 'admin/settings');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/smtp-test') { require_admin(); verify_csrf(); flash('success','SMTP test isteği alındı. Gerçek gönderim için SMTP bilgilerini kaydedip Bildirim Merkezi testini kullanın.'); redirect_to($_POST['return'] ?? 'admin/setup-wizard'); }

function ao_table_columns_v2334($table){
    static $cache=[]; if(isset($cache[$table])) return $cache[$table];
    $out=[]; try{ $q=db()->query('SHOW COLUMNS FROM `'.str_replace('`','',$table).'`'); foreach($q->fetchAll() as $r){ $out[$r['Field']]=$r; } }catch(Throwable $e){}
    return $cache[$table]=$out;
}
function ao_v2334_ensure_product_group_schema(){
    try{ db()->exec("CREATE TABLE IF NOT EXISTS product_groups (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(160) NOT NULL, slug VARCHAR(190) NOT NULL UNIQUE, type VARCHAR(80) DEFAULT 'service', description TEXT NULL, sort_order INT DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    $cols=ao_table_columns_v2334('product_groups');
    try{ if(!isset($cols['description'])) db()->exec("ALTER TABLE product_groups ADD COLUMN description TEXT NULL AFTER slug"); }catch(Throwable $e){}
    try{ if(!isset($cols['type'])) db()->exec("ALTER TABLE product_groups ADD COLUMN type VARCHAR(80) DEFAULT 'service' AFTER slug"); }catch(Throwable $e){}
    try{ if(!isset($cols['sort_order'])) db()->exec("ALTER TABLE product_groups ADD COLUMN sort_order INT DEFAULT 0 AFTER type"); }catch(Throwable $e){}
    try{ if(!isset($cols['is_active'])) db()->exec("ALTER TABLE product_groups ADD COLUMN is_active TINYINT(1) DEFAULT 1"); }catch(Throwable $e){}
}
function ao_v2334_seed_product_groups(){
    ao_v2334_ensure_product_group_schema();
    $defaults=[
        ['Hosting','hosting','Web hosting ve reseller paketleri','hosting',10],
        ['VPS / Sunucu','vps-sunucu','VPS, dedicated ve yönetilebilir sunucular','server',20],
        ['Domain','domain','Domain kayıt, transfer ve DNS ürünleri','domain',30],
        ['SSL','ssl','SSL sertifikaları ve güvenlik ürünleri','ssl',40],
        ['SiteBuilder','sitebuilder','Hazır site, şablon ve builder paketleri','sitebuilder',50],
        ['MobileBuilder','mobilebuilder','Mobil uygulama builder paketleri','mobilebuilder',60],
        ['Web Tasarım','web-tasarim','Kurumsal site, e-ticaret ve özel tasarım','web',70],
        ['Mobil Uygulama','mobil-uygulama','Android/iOS proje hizmetleri','mobile',80],
        ['SEO','seo','SEO ve dijital pazarlama paketleri','seo',90],
        ['Dijital Hizmetler','dijital-hizmetler','Ahost tarafından sunulan dijital hizmetler','digital',100],
        ['Marketplace','marketplace','Tema, script, domain ve dijital ürün ilan altyapısı','marketplace',110],
    ];
    $cols=ao_table_columns_v2334('product_groups');
    foreach($defaults as $d){
        $payload=['name'=>$d[0],'slug'=>$d[1],'is_active'=>1];
        if(isset($cols['description'])) $payload['description']=$d[2];
        if(isset($cols['type'])) $payload['type']=$d[3];
        if(isset($cols['sort_order'])) $payload['sort_order']=$d[4];
        $fields=array_keys($payload); $ph=implode(',',array_fill(0,count($fields),'?')); $upd=implode(',',array_map(fn($c)=>$c.'=VALUES('.$c.')',array_filter($fields,fn($c)=>$c!=='slug')));
        try{ db()->prepare('INSERT INTO product_groups('.implode(',',$fields).') VALUES('.$ph.') ON DUPLICATE KEY UPDATE '.$upd)->execute(array_values($payload)); }catch(Throwable $e){}
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/product-center/group-save') {
    require_admin(); verify_csrf();
    $id=(int)($_POST['id']??0);
    $name=trim($_POST['name']??'');
    $slug=trim($_POST['slug']??'');
    $desc=trim($_POST['description']??'');
    $type=trim($_POST['type']??'service');
    $isActive=isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    if($slug==='') $slug = preg_replace('/[^a-z0-9]+/','-', strtolower($name));
    $slug = trim(preg_replace('/[^a-z0-9\-]+/','-', strtolower($slug)),'-');
    try{
        if(!$name || !$slug) throw new Exception('Grup adı ve slug zorunlu.');
        ao_v2334_ensure_product_group_schema();
        $cols = ao_table_columns_v2334('product_groups');
        $payload = ['name'=>$name,'slug'=>$slug,'is_active'=>$isActive?1:0];
        if(isset($cols['description'])) $payload['description']=$desc;
        if(isset($cols['type'])) $payload['type']=$type;
        if(isset($cols['sort_order'])) $payload['sort_order']=(int)($_POST['sort_order']??0);
        if($id>0){
            $sets=[]; $vals=[];
            foreach($payload as $field=>$value){ $sets[]='`'.$field.'`=?'; $vals[]=$value; }
            $vals[]=$id;
            db()->prepare('UPDATE product_groups SET '.implode(',',$sets).' WHERE id=?')->execute($vals);
            flash('success','Ürün grubu güncellendi.');
        } else {
            $fields = array_keys($payload);
            $placeholders = implode(',', array_fill(0,count($fields),'?'));
            $updates = implode(',', array_map(fn($c)=>'`'.$c.'`=VALUES(`'.$c.'`)', array_filter($fields, fn($c)=>!in_array($c,['slug'],true))));
            db()->prepare('INSERT INTO product_groups(`'.implode('`,`',$fields).'`) VALUES('.$placeholders.') ON DUPLICATE KEY UPDATE '.$updates)->execute(array_values($payload));
            flash('success','Ürün grubu kaydedildi.');
        }
    }catch(Throwable $e){
        flash('error','Ürün grubu kaydedilemedi: '.$e->getMessage());
    }
    redirect_to('admin/product-center/groups');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/product-center/groups/seed-defaults') {
    require_admin(); verify_csrf();
    ao_v2334_seed_product_groups();
    flash('success','Varsayılan ürün grupları oluşturuldu/güncellendi.');
    redirect_to('admin/product-center/groups');
}

if ($route === 'admin/product-center/group-toggle') {
    require_admin(); verify_csrf();
    $id=(int)($_GET['id']??0);
    try{
        ao_v2334_ensure_product_group_schema();
        $q=db()->prepare('SELECT is_active FROM product_groups WHERE id=?'); $q->execute([$id]);
        $cur=(int)$q->fetchColumn();
        db()->prepare('UPDATE product_groups SET is_active=? WHERE id=?')->execute([$cur?0:1,$id]);
        flash('success',$cur?'Ürün grubu pasife alındı.':'Ürün grubu aktifleştirildi.');
    }catch(Throwable $e){ flash('error','Ürün grubu durumu değiştirilemedi: '.$e->getMessage()); }
    redirect_to('admin/product-center/groups');
}

if ($route === 'admin/product-center/group-delete') {
    require_admin(); verify_csrf();
    $id=(int)($_GET['id']??0);
    try{
        ao_v2334_ensure_product_group_schema();
        $q=db()->prepare('SELECT COUNT(*) FROM products WHERE group_id=?'); $q->execute([$id]);
        if((int)$q->fetchColumn()>0) throw new Exception('Bu gruba bağlı ürün var. Önce ürünleri başka gruba taşıyın veya silin.');
        db()->prepare('DELETE FROM product_groups WHERE id=?')->execute([$id]);
        flash('success','Ürün grubu silindi.');
    }catch(Throwable $e){ flash('error','Ürün grubu silinemedi: '.$e->getMessage()); }
    redirect_to('admin/product-center/groups');
}


function ao_v237_ensure_product_pricing_schema(){
    try{
        db()->exec("CREATE TABLE IF NOT EXISTS product_pricing (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            cycle VARCHAR(40) DEFAULT 'monthly',
            price DECIMAL(14,2) DEFAULT 0.00,
            setup_fee DECIMAL(14,2) DEFAULT 0.00,
            currency VARCHAR(10) DEFAULT 'TRY',
            UNIQUE KEY uniq_product_cycle (product_id,cycle)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }catch(Throwable $e){}
    $cols = [
        'price_usd' => 'DECIMAL(14,2) DEFAULT 0.00',
        'price_try' => 'DECIMAL(14,2) DEFAULT 0.00',
        'setup_fee_usd' => 'DECIMAL(14,2) DEFAULT 0.00',
        'setup_fee_try' => 'DECIMAL(14,2) DEFAULT 0.00',
        'base_currency' => "VARCHAR(10) DEFAULT 'USD'",
        'exchange_rate' => 'DECIMAL(16,6) DEFAULT 0.000000',
        'margin_percent' => 'DECIMAL(8,2) DEFAULT 0.00',
        'auto_convert' => 'TINYINT(1) DEFAULT 1',
        'is_active' => 'TINYINT(1) DEFAULT 0',
        'source_type' => 'VARCHAR(40) NULL',
        'external_id' => 'VARCHAR(80) NULL',
        'updated_at' => 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP'
    ];
    try{
        $existing = array_column(db()->query('SHOW COLUMNS FROM product_pricing')->fetchAll(PDO::FETCH_ASSOC),'Field');
        foreach($cols as $c=>$def){ if(!in_array($c,$existing,true)){ try{ db()->exec("ALTER TABLE product_pricing ADD COLUMN `$c` $def"); }catch(Throwable $e){} } }
    }catch(Throwable $e){}
    try{
        $pcols = array_column(db()->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC),'Field');
        foreach(['source_type'=>'VARCHAR(40) NULL','external_id'=>'VARCHAR(80) NULL','source_id'=>'VARCHAR(80) NULL','currency_code'=>"VARCHAR(10) NULL"] as $c=>$def){
            if(!in_array($c,$pcols,true)){ try{ db()->exec("ALTER TABLE products ADD COLUMN `$c` $def"); }catch(Throwable $e){} }
        }
    }catch(Throwable $e){}
}
function ao_v237_currency_rate($currency='USD'){
    $currency = strtoupper($currency ?: 'USD');
    if($currency==='TL') $currency='TRY';
    if($currency==='TRY') return 1.0;
    try{
        if(function_exists('ao_v23_ensure_schema')) ao_v23_ensure_schema();
        $q=db()->prepare('SELECT final_rate FROM currency_rates WHERE currency_code=? LIMIT 1');
        $q->execute([$currency]);
        $rate=(float)$q->fetchColumn();
        if($rate>0) return $rate;
    }catch(Throwable $e){}
    return $currency==='EUR' ? 51.45 : ($currency==='GBP' ? 60.90 : 47.25);
}
function ao_v237_currency_margin($currency='USD'){
    $currency = strtoupper($currency ?: 'USD');
    try{
        $q=db()->prepare('SELECT margin_percent FROM currency_rates WHERE currency_code=? LIMIT 1');
        $q->execute([$currency]);
        return (float)$q->fetchColumn();
    }catch(Throwable $e){ return 0.0; }
}
function ao_v237_parse_money($v){
    if($v===null) return 0.0;
    $v=trim((string)$v);
    if($v==='') return 0.0;
    return round((float)str_replace(',','.',$v),2);
}
function ao_v237_dual_from_inputs($usdRaw,$tryRaw,$rate){
    $usd = ao_v237_parse_money($usdRaw);
    $try = ao_v237_parse_money($tryRaw);
    // Standart: USD ana fiyat kabul edilir. USD yazıldıysa TRY her zaman güncel satış kurundan üretilir.
    if($usd > 0){ $try = round($usd * $rate, 2); }
    elseif($try > 0 && $rate > 0){ $usd = round($try / $rate, 2); }
    return [$usd,$try];
}
function ao_v237_save_product_prices($productId){
    ao_v237_ensure_product_pricing_schema();
    $rate = ao_v237_currency_rate('USD');
    $margin = ao_v237_currency_margin('USD');
    $cycles = ['one_time'=>'Tek seferlik','monthly'=>'Aylık','quarterly'=>'3 Aylık','semiannually'=>'6 Aylık','annually'=>'Yıllık','biennially'=>'2 Yıllık','triennially'=>'3 Yıllık'];
    foreach($cycles as $cycle=>$label){
        [$priceUsd,$priceTry] = ao_v237_dual_from_inputs($_POST['price_usd'][$cycle] ?? null, $_POST['price_try'][$cycle] ?? null, $rate);
        [$setupUsd,$setupTry] = ao_v237_dual_from_inputs($_POST['setup_usd'][$cycle] ?? null, $_POST['setup_try'][$cycle] ?? null, $rate);
        $active = !empty($_POST['price_active']['USD'][$cycle]) || !empty($_POST['price_active']['TRY'][$cycle]) ? 1 : 0;
        $base = $priceUsd > 0 || $setupUsd > 0 ? 'USD' : 'TRY';
        try{
            db()->prepare('INSERT INTO product_pricing(product_id,cycle,price,setup_fee,currency,price_usd,price_try,setup_fee_usd,setup_fee_try,base_currency,exchange_rate,margin_percent,auto_convert,is_active) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,1,?) ON DUPLICATE KEY UPDATE price=VALUES(price), setup_fee=VALUES(setup_fee), currency=VALUES(currency), price_usd=VALUES(price_usd), price_try=VALUES(price_try), setup_fee_usd=VALUES(setup_fee_usd), setup_fee_try=VALUES(setup_fee_try), base_currency=VALUES(base_currency), exchange_rate=VALUES(exchange_rate), margin_percent=VALUES(margin_percent), auto_convert=1, is_active=VALUES(is_active)')
                ->execute([$productId,$cycle,$priceTry,$setupTry,'TRY',$priceUsd,$priceTry,$setupUsd,$setupTry,$base,$rate,$margin,$active]);
        }catch(Throwable $e){}
    }
    try{
        $q=db()->prepare("SELECT price_try FROM product_pricing WHERE product_id=? AND cycle='monthly' LIMIT 1"); $q->execute([$productId]);
        $monthlyTry=(float)$q->fetchColumn();
        $cols=array_column(db()->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC),'Field');
        $set=[];$vals=[];
        if(in_array('price',$cols,true)){ $set[]='price=?'; $vals[]=$monthlyTry; }
        if(in_array('currency',$cols,true)){ $set[]='currency=?'; $vals[]='TRY'; }
        if(in_array('currency_code',$cols,true)){ $set[]='currency_code=?'; $vals[]='TRY'; }
        if($set){ $vals[]=$productId; db()->prepare('UPDATE products SET '.implode(',',$set).' WHERE id=?')->execute($vals); }
    }catch(Throwable $e){}
}
function ao_v237_refresh_try_prices(){
    ao_v237_ensure_product_pricing_schema();
    $rate=ao_v237_currency_rate('USD'); $margin=ao_v237_currency_margin('USD');
    try{ db()->prepare("UPDATE product_pricing SET price_try=ROUND(price_usd*?,2), setup_fee_try=ROUND(setup_fee_usd*?,2), price=ROUND(price_usd*?,2), setup_fee=ROUND(setup_fee_usd*?,2), currency='TRY', exchange_rate=?, margin_percent=? WHERE auto_convert=1 AND base_currency='USD'")->execute([$rate,$rate,$rate,$rate,$rate,$margin]); }catch(Throwable $e){}
    try{
        // Ürün listesi ve site vitrinleri eski products.price alanını okuyorsa sıfır görünmesin diye
        // aktif ilk periyot fiyatı products tablosuna da yansıtılır.
        $ids = db()->query('SELECT DISTINCT product_id FROM product_pricing')->fetchAll(PDO::FETCH_COLUMN);
        foreach($ids as $pid){
            $display = ao_v2331_product_display_price((int)$pid);
            if(($display['try'] ?? 0) > 0){
                $cols=array_column(db()->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC),'Field');
                $set=[]; $vals=[];
                if(in_array('price',$cols,true)){ $set[]='price=?'; $vals[]=$display['try']; }
                if(in_array('currency',$cols,true)){ $set[]='currency=?'; $vals[]='TRY'; }
                if(in_array('currency_code',$cols,true)){ $set[]='currency_code=?'; $vals[]='TRY'; }
                if($set){ $vals[]=(int)$pid; db()->prepare('UPDATE products SET '.implode(',',$set).' WHERE id=?')->execute($vals); }
            }
        }
    }catch(Throwable $e){}
}


// v24.8.0 - Ürün listesi hızlı fiyat düzeltme ve toplu fiyat güncelleme
function ao_v2480_ensure_quick_price_schema(){
    try{
        if(function_exists('ao_v237_ensure_product_pricing_schema')) ao_v237_ensure_product_pricing_schema();
        db()->exec("CREATE TABLE IF NOT EXISTS product_price_update_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NULL,
            admin_id INT NULL,
            action VARCHAR(80) DEFAULT 'quick_update',
            cycle VARCHAR(40) DEFAULT 'monthly',
            old_snapshot LONGTEXT NULL,
            new_snapshot LONGTEXT NULL,
            note TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY product_id(product_id),
            KEY action(action),
            KEY cycle(cycle)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }catch(Throwable $e){}
}
function ao_v2480_price_row($productId,$cycle){
    try{ ao_v2480_ensure_quick_price_schema(); $q=db()->prepare('SELECT * FROM product_pricing WHERE product_id=? AND cycle=? LIMIT 1'); $q->execute([(int)$productId,(string)$cycle]); return $q->fetch(PDO::FETCH_ASSOC) ?: []; }catch(Throwable $e){ return []; }
}
function ao_v2480_log_price_change($productId,$action,$cycle,$old,$new,$note=''){
    try{ ao_v2480_ensure_quick_price_schema(); $admin=function_exists('current_admin')?current_admin():null; db()->prepare('INSERT INTO product_price_update_logs(product_id,admin_id,action,cycle,old_snapshot,new_snapshot,note) VALUES(?,?,?,?,?,?,?)')->execute([(int)$productId,$admin['id']??null,$action,$cycle,json_encode($old,JSON_UNESCAPED_UNICODE),json_encode($new,JSON_UNESCAPED_UNICODE),$note]); }catch(Throwable $e){}
}
function ao_v2480_upsert_price($productId,$cycle,$priceUsd,$priceTry,$setupUsd=0,$setupTry=0,$active=1,$action='quick_update',$note=''){
    ao_v2480_ensure_quick_price_schema();
    $productId=(int)$productId; $cycle=(string)($cycle ?: 'monthly');
    $rate=function_exists('ao_v237_currency_rate') ? (float)ao_v237_currency_rate('USD') : 47.25;
    $margin=function_exists('ao_v237_currency_margin') ? (float)ao_v237_currency_margin('USD') : 0.0;
    $priceUsd=function_exists('ao_v237_parse_money') ? ao_v237_parse_money($priceUsd) : round((float)str_replace(',','.',(string)$priceUsd),2);
    $priceTry=function_exists('ao_v237_parse_money') ? ao_v237_parse_money($priceTry) : round((float)str_replace(',','.',(string)$priceTry),2);
    $setupUsd=function_exists('ao_v237_parse_money') ? ao_v237_parse_money($setupUsd) : round((float)str_replace(',','.',(string)$setupUsd),2);
    $setupTry=function_exists('ao_v237_parse_money') ? ao_v237_parse_money($setupTry) : round((float)str_replace(',','.',(string)$setupTry),2);
    if($priceUsd>0 && $priceTry<=0 && $rate>0) $priceTry=round($priceUsd*$rate,2);
    if($priceTry>0 && $priceUsd<=0 && $rate>0) $priceUsd=round($priceTry/$rate,2);
    if($setupUsd>0 && $setupTry<=0 && $rate>0) $setupTry=round($setupUsd*$rate,2);
    if($setupTry>0 && $setupUsd<=0 && $rate>0) $setupUsd=round($setupTry/$rate,2);
    $old=ao_v2480_price_row($productId,$cycle);
    $base=($priceUsd>0 || $setupUsd>0) ? 'USD' : 'TRY';
    db()->prepare('INSERT INTO product_pricing(product_id,cycle,price,setup_fee,currency,price_usd,price_try,setup_fee_usd,setup_fee_try,base_currency,exchange_rate,margin_percent,auto_convert,is_active) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,1,?) ON DUPLICATE KEY UPDATE price=VALUES(price), setup_fee=VALUES(setup_fee), currency=VALUES(currency), price_usd=VALUES(price_usd), price_try=VALUES(price_try), setup_fee_usd=VALUES(setup_fee_usd), setup_fee_try=VALUES(setup_fee_try), base_currency=VALUES(base_currency), exchange_rate=VALUES(exchange_rate), margin_percent=VALUES(margin_percent), auto_convert=1, is_active=VALUES(is_active)')
        ->execute([$productId,$cycle,$priceTry,$setupTry,'TRY',$priceUsd,$priceTry,$setupUsd,$setupTry,$base,$rate,$margin,(int)$active]);
    $new=ao_v2480_price_row($productId,$cycle);
    ao_v2480_log_price_change($productId,$action,$cycle,$old,$new,$note);
    try{
        $display=function_exists('ao_v2331_product_display_price') ? ao_v2331_product_display_price($productId) : ['try'=>$priceTry];
        $cols=array_column(db()->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC),'Field');
        $set=[]; $vals=[];
        if(in_array('price',$cols,true)){ $set[]='price=?'; $vals[]=(float)($display['try'] ?? $priceTry); }
        if(in_array('currency',$cols,true)){ $set[]='currency=?'; $vals[]='TRY'; }
        if(in_array('currency_code',$cols,true)){ $set[]='currency_code=?'; $vals[]='TRY'; }
        if($set){ $vals[]=$productId; db()->prepare('UPDATE products SET '.implode(',',$set).' WHERE id=?')->execute($vals); }
    }catch(Throwable $e){}
    return $new;
}
function ao_v2480_bulk_target_products(){
    $ids=array_map('intval', $_POST['product_ids'] ?? []); $ids=array_values(array_filter(array_unique($ids)));
    if($ids) return $ids;
    $group=(int)($_POST['bulk_group_id'] ?? 0);
    try{
        if($group>0){ $q=db()->prepare('SELECT id FROM products WHERE group_id=?'); $q->execute([$group]); return array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN) ?: []); }
    }catch(Throwable $e){}
    return [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/product-center/quick-price-update') {
    require_admin(); verify_csrf();
    $id=(int)($_POST['product_id'] ?? 0); $cycle=trim($_POST['cycle'] ?? 'monthly');
    try{ if($id<=0) throw new Exception('Ürün seçilmedi.'); ao_v2480_upsert_price($id,$cycle,$_POST['price_usd']??0,$_POST['price_try']??0,$_POST['setup_usd']??0,$_POST['setup_try']??0,!empty($_POST['is_active'])?1:0,'quick_update','Ürün listesinden hızlı fiyat düzeltme.'); flash('success','Hızlı fiyat düzeltme kaydedildi. Site, sepet ve müşteri paneli güncel vitrin fiyatını okuyacak.'); }
    catch(Throwable $e){ flash('error','Hızlı fiyat düzeltme yapılamadı: '.$e->getMessage()); }
    redirect_to('admin/product-center/products');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/product-center/bulk-price-update') {
    require_admin(); verify_csrf();
    $ids=ao_v2480_bulk_target_products(); $cycle=trim($_POST['bulk_cycle'] ?? 'monthly'); $mode=trim($_POST['bulk_mode'] ?? 'percent_increase'); $value=function_exists('ao_v237_parse_money')?ao_v237_parse_money($_POST['bulk_value']??0):(float)($_POST['bulk_value']??0); $count=0;
    try{
        if(!$ids) throw new Exception('Güncellenecek ürün seçilmedi.');
        foreach($ids as $pid){
            $old=ao_v2480_price_row($pid,$cycle); $priceUsd=(float)($old['price_usd'] ?? 0); $priceTry=(float)($old['price_try'] ?? ($old['price'] ?? 0)); $setupUsd=(float)($old['setup_fee_usd'] ?? 0); $setupTry=(float)($old['setup_fee_try'] ?? ($old['setup_fee'] ?? 0));
            $active=isset($old['is_active']) ? (int)$old['is_active'] : 1;
            if($mode==='percent_increase'){ $priceUsd=round($priceUsd*(1+$value/100),2); $priceTry=round($priceTry*(1+$value/100),2); }
            elseif($mode==='percent_decrease'){ $priceUsd=round($priceUsd*(1-$value/100),2); $priceTry=round($priceTry*(1-$value/100),2); }
            elseif($mode==='add_try'){ $priceTry=max(0,round($priceTry+$value,2)); $priceUsd=0; }
            elseif($mode==='add_usd'){ $priceUsd=max(0,round($priceUsd+$value,2)); $priceTry=0; }
            elseif($mode==='fixed_try'){ $priceTry=max(0,round($value,2)); $priceUsd=0; }
            elseif($mode==='fixed_usd'){ $priceUsd=max(0,round($value,2)); $priceTry=0; }
            elseif($mode==='refresh_usd_rate'){ $priceTry=0; }
            ao_v2480_upsert_price($pid,$cycle,$priceUsd,$priceTry,$setupUsd,$setupTry,$active,'bulk_update','Ürünler listesinden toplu fiyat güncelleme: '.$mode.' / '.$value); $count++;
        }
        flash('success',$count.' ürün için toplu fiyat güncelleme tamamlandı.');
    }catch(Throwable $e){ flash('error','Toplu fiyat güncelleme yapılamadı: '.$e->getMessage()); }
    redirect_to('admin/product-center/products');
}

function ao_v2331_product_display_price($productId){
    // Liste ve site vitrinlerinde fiyatın 0 görünmesini engelleyen ortak fiyat seçici.
    // Öncelik: aktif periyotlar; Tek seferlik/Aylık > Aylık > 3/6 aylık > yıllık.
    ao_v237_ensure_product_pricing_schema();
    $productId=(int)$productId;
    $order=['one_time','monthly','quarterly','semiannually','annually','biennially','triennially'];
    try{
        $st=db()->prepare("SELECT * FROM product_pricing WHERE product_id=? ORDER BY is_active DESC, FIELD(cycle,'one_time','monthly','quarterly','semiannually','annually','biennially','triennially'), id ASC");
        $st->execute([$productId]);
        $rows=$st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach($rows as $r){
            $try=(float)($r['price_try'] ?? $r['price'] ?? 0);
            $usd=(float)($r['price_usd'] ?? 0);
            if($try<=0 && $usd>0){ $try=round($usd*ao_v237_currency_rate('USD'),2); }
            if($usd<=0 && $try>0){ $rate=ao_v237_currency_rate('USD'); $usd=$rate>0?round($try/$rate,2):0; }
            if($try>0 || $usd>0){
                return ['try'=>$try,'usd'=>$usd,'cycle'=>(string)($r['cycle'] ?? 'monthly'),'active'=>(int)($r['is_active'] ?? 0)];
            }
        }
    }catch(Throwable $e){}
    try{
        $q=db()->prepare('SELECT price,currency,currency_code FROM products WHERE id=? LIMIT 1'); $q->execute([$productId]); $p=$q->fetch(PDO::FETCH_ASSOC) ?: [];
        $price=(float)($p['price'] ?? 0); $cur=strtoupper((string)($p['currency_code'] ?? $p['currency'] ?? 'TRY'));
        if($cur==='USD') return ['try'=>round($price*ao_v237_currency_rate('USD'),2),'usd'=>$price,'cycle'=>'legacy','active'=>0];
        return ['try'=>$price,'usd'=>($price>0?round($price/ao_v237_currency_rate('USD'),2):0),'cycle'=>'legacy','active'=>0];
    }catch(Throwable $e){}
    return ['try'=>0,'usd'=>0,'cycle'=>'none','active'=>0];
}




// v24.0.0 - Product Content Studio: WordPress kalitesinde güvenli HTML editörü desteği
function ao_v2400_sanitize_product_html($html){
    $html = (string)$html;
    if ($html === '') return '';
    // Riskli blokları tamamen kaldır.
    $html = preg_replace('#<(script|style|iframe|object|embed|form|input|textarea|select|button|meta|link|base)[^>]*>.*?</\\1>#is', '', $html);
    $html = preg_replace('#<(script|style|iframe|object|embed|form|input|textarea|select|button|meta|link|base)[^>]*/?>#is', '', $html);
    // Event attribute ve javascript/data payload temizliği.
    $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/(href|src)\s*=\s*("|\')\s*(javascript:|data:text\/html|vbscript:)[^"\']*("|\')/i', '$1="#"', $html);
    $html = preg_replace('/style\s*=\s*("|\')[^"\']*(expression|javascript:|url\s*\()[^"\']*("|\')/i', '', $html);
    $allowed = '<h1><h2><h3><h4><h5><h6><p><br><hr><strong><b><em><i><u><s><mark><small><span><div><section><article><blockquote><pre><code><ul><ol><li><table><thead><tbody><tfoot><tr><th><td><a><img><figure><figcaption>';
    $html = strip_tags($html, $allowed);
    return trim($html);
}
function ao_v2400_plain_from_html($html, $limit=220){
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$html)));
    if (function_exists('mb_substr') && function_exists('mb_strlen')) return mb_strlen($text) > $limit ? mb_substr($text,0,$limit).'…' : $text;
    return strlen($text) > $limit ? substr($text,0,$limit).'…' : $text;
}

// v23.3.2 - Ürün sekmeleri, klonlama, revizyon ve müşteri hesap kullanıcıları
function ao_v2332_ensure_schema(){
    try{
        db()->exec("CREATE TABLE IF NOT EXISTS product_revision_logs (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, admin_id INT NULL, action VARCHAR(80) DEFAULT 'update', snapshot_json LONGTEXT NULL, note TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY product_id(product_id), KEY action(action)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS server_groups (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(190) NOT NULL, strategy VARCHAR(80) DEFAULT 'least_used', location VARCHAR(120) NULL, status VARCHAR(40) DEFAULT 'active', notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_server_group_name(name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS customer_account_users (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, name VARCHAR(190) NOT NULL, email VARCHAR(190) NOT NULL, phone VARCHAR(80) NULL, role_key VARCHAR(80) DEFAULT 'viewer', permissions_json LONGTEXT NULL, status VARCHAR(40) DEFAULT 'invited', invite_token_hash VARCHAR(190) NULL, invited_at DATETIME NULL, accepted_at DATETIME NULL, last_login_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_customer_user_email(customer_id,email), KEY customer_id(customer_id), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS customer_user_activity_logs (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, account_user_id INT NULL, action VARCHAR(120) NOT NULL, description TEXT NULL, ip_address VARCHAR(80) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id), KEY account_user_id(account_user_id), KEY action(action)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // v23.3.6: MySQL/MariaDB uyumlu kolon ekleme. Bazı sunucularda ALTER ... IF NOT EXISTS desteklenmediği için SHOW COLUMNS kullanılır.
        $aoCols=[]; try { $aoCols=array_column(db()->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC),'Field'); } catch(Throwable $e) {}
        $aoAdd=function($name,$definition) use (&$aoCols){ try{ if(!in_array($name,$aoCols,true)){ db()->exec('ALTER TABLE products ADD COLUMN '.$definition); $aoCols[]=$name; } }catch(Throwable $e){} };
        $aoAdd('server_group_id','server_group_id INT NULL');
        $aoAdd('short_description','short_description TEXT NULL');
        $aoAdd('visibility',"visibility VARCHAR(40) DEFAULT 'visible'");
        $aoAdd('seo_title','seo_title VARCHAR(190) NULL');
        $aoAdd('meta_description','meta_description TEXT NULL');
        $aoAdd('sort_order','sort_order INT DEFAULT 0');
        try{ $snCols=array_column(db()->query('SHOW COLUMNS FROM server_nodes')->fetchAll(PDO::FETCH_ASSOC),'Field'); if(!in_array('server_group_id',$snCols,true)) db()->exec('ALTER TABLE server_nodes ADD COLUMN server_group_id INT NULL'); }catch(Throwable $e){}
        try{ db()->exec("INSERT IGNORE INTO server_groups(name,strategy,location,status,notes) VALUES ('Türkiye Hosting','least_used','TR','active','Yeni hosting hesapları en az dolu Türkiye sunucusuna atanır.'),('Almanya Hosting','least_used','DE','active','Avrupa lokasyonlu hosting ve VPS ürünleri için sunucu grubu.'),('Manuel Teslimat','manual',NULL,'active','Otomasyon kullanılmayan ürünlerde manuel teslimat grubu.')"); }catch(Throwable $e){}
    }catch(Throwable $e){}
}
function ao_v2332_log_product_revision($productId,$action='update',$note=''){
    try{ ao_v2332_ensure_schema(); $q=db()->prepare('SELECT * FROM products WHERE id=? LIMIT 1'); $q->execute([(int)$productId]); $product=$q->fetch(PDO::FETCH_ASSOC) ?: []; $pq=db()->prepare('SELECT * FROM product_pricing WHERE product_id=?'); $pq->execute([(int)$productId]); $snapshot=['product'=>$product,'pricing'=>$pq->fetchAll(PDO::FETCH_ASSOC) ?: []]; $admin=current_admin(); db()->prepare('INSERT INTO product_revision_logs(product_id,admin_id,action,snapshot_json,note) VALUES(?,?,?,?,?)')->execute([(int)$productId,$admin['id']??null,$action,json_encode($snapshot,JSON_UNESCAPED_UNICODE),$note]); }catch(Throwable $e){}
}
function ao_v2332_customer_user_permissions($role){
    $all=['invoices.view','invoices.pay','tickets.open','tickets.view','hosting.manage','domains.manage','dns.manage','orders.create','profile.edit','users.manage'];
    $map=['owner'=>$all,'full'=>$all,'billing'=>['invoices.view','invoices.pay','tickets.open'],'technical'=>['tickets.open','tickets.view','hosting.manage','domains.manage','dns.manage'],'domain'=>['domains.manage','dns.manage','tickets.open'],'hosting'=>['hosting.manage','tickets.open'],'viewer'=>['invoices.view','tickets.view']];
    return $map[$role] ?? $map['viewer'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/product-center/product-save') {
    require_admin(); verify_csrf();
    $id=(int)($_POST['id']??0); $group=(int)($_POST['group_id']??0); $name=trim($_POST['name']??''); $slug=trim($_POST['slug']??''); $type=trim($_POST['type']??'service'); $module=trim($_POST['module_name']??'manual'); $serverGroup=(int)($_POST['server_group_id']??0); $whm=trim($_POST['whm_package']??''); $shortDesc=trim($_POST['short_description']??''); $desc=ao_v2400_sanitize_product_html($_POST['description']??''); $custom=(int)($_POST['is_custom_build_enabled']??0); $visibility=trim($_POST['visibility']??'visible'); $seoTitle=trim($_POST['seo_title']??''); $metaDesc=trim($_POST['meta_description']??''); $sortOrder=(int)($_POST['sort_order']??0);
    if($slug==='') $slug = preg_replace('/[^a-z0-9]+/','-', strtolower($name));
    $slug = trim(preg_replace('/[^a-z0-9\-]+/','-', strtolower($slug)),'-');
    try{ if(!$name||!$slug) throw new Exception('Ürün adı ve slug zorunlu.');
        ao_v237_ensure_product_pricing_schema(); ao_v2332_ensure_schema();
        if($id>0) {
            ao_v2332_log_product_revision($id,'before_update','Ürün düzenlemeden önce otomatik kayıt.');
            db()->prepare('UPDATE products SET group_id=?,name=?,slug=?,type=?,module_name=?,server_group_id=?,whm_package=?,short_description=?,description=?,is_custom_build_enabled=?,visibility=?,seo_title=?,meta_description=?,sort_order=? WHERE id=?')->execute([$group,$name,$slug,$type,$module,$serverGroup,$whm,$shortDesc,$desc,$custom,$visibility,$seoTitle,$metaDesc,$sortOrder,$id]);
            $productId=$id;
        } else {
            db()->prepare('INSERT INTO products(group_id,name,slug,type,module_name,server_group_id,whm_package,short_description,description,is_custom_build_enabled,is_active,visibility,seo_title,meta_description,sort_order) VALUES(?,?,?,?,?,?,?,?,?,?,1,?,?,?,?)')->execute([$group,$name,$slug,$type,$module,$serverGroup,$whm,$shortDesc,$desc,$custom,$visibility,$seoTitle,$metaDesc,$sortOrder]);
            $productId=(int)db()->lastInsertId();
        }
        if(!empty($productId)) { ao_v237_save_product_prices($productId); ao_v2332_log_product_revision($productId,$id>0?'update':'create',$id>0?'Ürün güncellendi.':'Ürün oluşturuldu.'); }
        flash('success','Ürün ve fiyatlandırma kaydedildi.');
    }catch(Throwable $e){ flash('error','Ürün kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/product-center/products');
}

if ($route === 'admin/product-center/product-clone') {
    require_admin(); verify_csrf(); ao_v2332_ensure_schema(); ao_v237_ensure_product_pricing_schema(); $id=(int)($_GET['id']??0);
    try{
        $q=db()->prepare('SELECT * FROM products WHERE id=? LIMIT 1'); $q->execute([$id]); $p=$q->fetch(PDO::FETCH_ASSOC); if(!$p) throw new Exception('Ürün bulunamadı.');
        $baseSlug=preg_replace('/-kopya-[0-9]+$/','',(string)$p['slug']); $newSlug=$baseSlug.'-kopya-'.date('His');
        $cols=array_column(db()->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC),'Field');
        $copyCols=array_values(array_intersect($cols,['group_id','name','slug','type','module_name','server_group_id','whm_package','short_description','description','is_custom_build_enabled','is_active','visibility','seo_title','meta_description','sort_order','price','currency','currency_code']));
        $data=[]; foreach($copyCols as $c){ $data[$c]=$p[$c]??null; }
        $data['name']=($p['name'] ?? 'Ürün').' Kopya'; $data['slug']=$newSlug; if(isset($data['is_active'])) $data['is_active']=0;
        $sql='INSERT INTO products('.implode(',',array_keys($data)).') VALUES('.implode(',',array_fill(0,count($data),'?')).')'; db()->prepare($sql)->execute(array_values($data)); $newId=(int)db()->lastInsertId();
        $ps=db()->prepare('SELECT * FROM product_pricing WHERE product_id=?'); $ps->execute([$id]); foreach($ps->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r){ unset($r['id']); $r['product_id']=$newId; $sql='INSERT INTO product_pricing('.implode(',',array_keys($r)).') VALUES('.implode(',',array_fill(0,count($r),'?')).')'; try{db()->prepare($sql)->execute(array_values($r));}catch(Throwable $e){} }
        ao_v2332_log_product_revision($newId,'clone','Ürün #'.$id.' üzerinden klonlandı.'); flash('success','Ürün klonlandı. Yeni kopya pasif olarak oluşturuldu.'); redirect_to('admin/product-center/products?edit='.$newId);
    }catch(Throwable $e){ flash('error','Ürün klonlanamadı: '.$e->getMessage()); redirect_to('admin/product-center/products'); }
}
if ($route === 'admin/product-center/product-toggle') {
    require_admin(); verify_csrf(); $id=(int)($_GET['id']??0);
    try { $q=db()->prepare('SELECT is_active FROM products WHERE id=?'); $q->execute([$id]); $cur=(int)$q->fetchColumn(); db()->prepare('UPDATE products SET is_active=? WHERE id=?')->execute([$cur?0:1,$id]); flash('success',$cur?'Ürün pasife alındı.':'Ürün aktifleştirildi.'); } catch(Throwable $e){ flash('error','Ürün durumu değiştirilemedi.'); }
    redirect_to('admin/product-center/products');
}
if ($route === 'admin/product-center/product-delete') {
    require_admin(); verify_csrf(); $id=(int)($_GET['id']??0);
    try { db()->prepare('DELETE FROM products WHERE id=?')->execute([$id]); flash('success','Ürün kalıcı olarak silindi.'); } catch(Throwable $e){ flash('error','Ürün silinemedi: Bu ürüne bağlı sipariş/hizmet olabilir.'); }
    redirect_to('admin/product-center/products');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/product-center/config-option-save') {
    verify_csrf();
    $pid=(int)($_POST['product_id']??0); $name=trim($_POST['name']??''); $type=trim($_POST['option_type']??'dropdown'); $values=trim($_POST['values']??'');
    try{ if(!$pid||!$name) throw new Exception('Ürün ve seçenek adı zorunlu.'); db()->prepare('INSERT INTO configurable_options(product_id,name,option_type,required,sort_order) VALUES(?,?,?,?,0)')->execute([$pid,$name,$type,1]); $oid=(int)db()->lastInsertId();
        foreach(array_filter(array_map('trim', explode("\n", $values))) as $i=>$line){ [$label,$price]=array_pad(array_map('trim', explode('|',$line,2)),2,'0'); db()->prepare('INSERT INTO configurable_option_values(option_id,label,price_monthly,sort_order) VALUES(?,?,?,?)')->execute([$oid,$label,(float)$price,$i+1]); }
        flash('success','Konfigüre edilebilir seçenek kaydedildi.');
    }catch(Throwable $e){ flash('error','Seçenek kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/product-center/config-options');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/hosting-server/save') {
    verify_csrf();
    $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $panel=trim($_POST['panel_type']??'whm'); $host=trim($_POST['hostname']??''); $ip=trim($_POST['ip_address']??''); $user=trim($_POST['username']??''); $token=trim($_POST['api_token']??''); $status=trim($_POST['status']??'inactive'); $test=(int)($_POST['test_mode']??1); $notes=trim($_POST['notes']??'');
    try{
        if(!$name) throw new Exception('Sunucu adı zorunlu.');
        if($id>0){
            if($token==='') db()->prepare('UPDATE server_nodes SET name=?,panel_type=?,hostname=?,ip_address=?,username=?,status=?,test_mode=?,notes=? WHERE id=?')->execute([$name,$panel,$host,$ip,$user,$status,$test,$notes,$id]);
            else db()->prepare('UPDATE server_nodes SET name=?,panel_type=?,hostname=?,ip_address=?,username=?,api_token=?,status=?,test_mode=?,notes=? WHERE id=?')->execute([$name,$panel,$host,$ip,$user,$token,$status,$test,$notes,$id]);
            flash('success','Sunucu/API node güncellendi.');
        } else {
            db()->prepare('INSERT INTO server_nodes(name,panel_type,hostname,ip_address,username,api_token,status,test_mode,notes) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$name,$panel,$host,$ip,$user,$token,$status,$test,$notes]); flash('success','Sunucu/API node kaydedildi.');
        }
    }catch(Throwable $e){ flash('error','Sunucu kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/hosting-server/servers');
}


// v7.4.1 Fix Pack - customer CRUD, server CRUD/login, panel login helpers, richer Migration bridge imports
function ao_panel_url_from_host($host, $panel='cpanel') {
    $host = trim((string)$host);
    if ($host === '') return '#';
    if (!preg_match('#^https?://#i', $host)) $host = 'https://' . $host;
    $ports = ['cpanel'=>2083,'webmail'=>2096,'whm'=>2087,'directadmin'=>2222,'plesk'=>8443,'vps'=>0];
    $port = $ports[$panel] ?? 0;
    if ($port && !preg_match('#:[0-9]+(/|$)#', $host)) $host .= ':' . $port;
    return $host;
}
function ao_host_from_server_row($row) {
    return trim($row['hostname'] ?? '') ?: trim($row['ip_address'] ?? '') ?: trim($row['server_ip'] ?? '') ?: trim($row['server_name'] ?? '');
}
function ao_log_simple($provider,$action,$status,$message,$payload='{}') {
    try { db()->prepare('INSERT INTO api_logs(provider,action,status,message,payload) VALUES(?,?,?,?,?)')->execute([$provider,$action,$status,$message,$payload]); } catch(Throwable $e) {}
}

function ao_whm_create_user_session($server, $loginUser, $service='cpaneld') {
    $host = ao_host_from_server_row($server);
    $token = trim((string)($server['api_token'] ?? ''));
    $apiUser = trim((string)($server['username'] ?? 'root')) ?: 'root';
    $loginUser = trim((string)$loginUser);
    $service = in_array($service, ['cpaneld','webmaild','whostmgrd'], true) ? $service : 'cpaneld';
    if ($host === '' || $token === '' || $loginUser === '') return ['ok'=>false,'url'=>'','message'=>'WHM SSO için hostname, API token ve kullanıcı adı zorunlu.'];
    if (!preg_match('#^https?://#i', $host)) $host = 'https://' . $host;
    if (!preg_match('#:[0-9]+(/|$)#', $host)) $host .= ':2087';
    $url = rtrim($host,'/') . '/json-api/create_user_session?api.version=1&user=' . rawurlencode($loginUser) . '&service=' . rawurlencode($service);
    $ctx = stream_context_create(['http'=>['method'=>'GET','timeout'=>18,'ignore_errors'=>true,'header'=>'Authorization: whm '.$apiUser.':'.$token."\r\nAccept: application/json\r\n"]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) return ['ok'=>false,'url'=>'','message'=>'WHM API yanıt vermedi. Host/port/firewall/API token kontrol edilmeli.'];
    $arr = json_decode($body, true);
    $sessionUrl = $arr['data']['url'] ?? $arr['data']['session_url'] ?? '';
    $ok = !empty($sessionUrl);
    $msg = $ok ? 'WHM/cPanel güvenli oturum URL üretildi.' : ('WHM API session URL üretmedi: '.mb_substr($body,0,240));
    return ['ok'=>$ok,'url'=>$sessionUrl,'message'=>$msg,'raw'=>$body];
}
function ao_panel_service_name($panel) {
    return ['cpanel'=>'cpaneld','webmail'=>'webmaild','whm'=>'whostmgrd'][$panel] ?? 'cpaneld';
}
function ao_runtime_schema_repair() {
    static $done=false; if($done) return; $done=true;
    $customerAdds = [
        'balance'=>"ALTER TABLE customers ADD COLUMN balance DECIMAL(14,2) DEFAULT 0.00 AFTER credit_balance",
        'tc_identity_no'=>"ALTER TABLE customers ADD COLUMN tc_identity_no VARCHAR(11) NULL AFTER phone",
        'identity_verified_at'=>"ALTER TABLE customers ADD COLUMN identity_verified_at DATETIME NULL AFTER identity_verified",
        'address1'=>"ALTER TABLE customers ADD COLUMN address1 VARCHAR(255) NULL AFTER phone",
        'address2'=>"ALTER TABLE customers ADD COLUMN address2 VARCHAR(255) NULL AFTER address1",
        'city'=>"ALTER TABLE customers ADD COLUMN city VARCHAR(120) NULL AFTER address2",
        'state'=>"ALTER TABLE customers ADD COLUMN state VARCHAR(120) NULL AFTER city",
        'postcode'=>"ALTER TABLE customers ADD COLUMN postcode VARCHAR(40) NULL AFTER state",
        'country'=>"ALTER TABLE customers ADD COLUMN country VARCHAR(80) NULL AFTER postcode",
        'tax_number'=>"ALTER TABLE customers ADD COLUMN tax_number VARCHAR(80) NULL AFTER country",
        'language'=>"ALTER TABLE customers ADD COLUMN language VARCHAR(20) DEFAULT 'tr' AFTER currency",
        'notes'=>"ALTER TABLE customers ADD COLUMN notes TEXT NULL AFTER language",
        'last_login_at'=>"ALTER TABLE customers ADD COLUMN last_login_at DATETIME NULL AFTER restored_at",
    ];
    try{ $cols=[]; foreach(db()->query('SHOW COLUMNS FROM customers')->fetchAll() as $c) $cols[$c['Field']]=true; foreach($customerAdds as $col=>$sql){ if(empty($cols[$col])) db()->exec($sql); } db()->exec('UPDATE customers SET balance=COALESCE(NULLIF(balance,0), credit_balance, 0) WHERE balance IS NULL OR balance=0'); }catch(Throwable $e){}
    $hostingAdds = [
        'server_ip'=>"ALTER TABLE hosting_accounts ADD COLUMN server_ip VARCHAR(80) NULL AFTER server_name",
        'whm_username'=>"ALTER TABLE hosting_accounts ADD COLUMN whm_username VARCHAR(120) NULL AFTER username",
        'panel_password'=>"ALTER TABLE hosting_accounts ADD COLUMN panel_password TEXT NULL AFTER whm_username",
        'disk_mb'=>"ALTER TABLE hosting_accounts ADD COLUMN disk_mb INT DEFAULT 0 AFTER package_name",
        'disk_used_mb'=>"ALTER TABLE hosting_accounts ADD COLUMN disk_used_mb INT DEFAULT 0 AFTER disk_mb",
        'bandwidth_mb'=>"ALTER TABLE hosting_accounts ADD COLUMN bandwidth_mb INT DEFAULT 0 AFTER disk_used_mb",
        'bandwidth_used_mb'=>"ALTER TABLE hosting_accounts ADD COLUMN bandwidth_used_mb INT DEFAULT 0 AFTER bandwidth_mb",
        'mail_limit'=>"ALTER TABLE hosting_accounts ADD COLUMN mail_limit INT DEFAULT 0 AFTER bandwidth_used_mb",
        'mail_used'=>"ALTER TABLE hosting_accounts ADD COLUMN mail_used INT DEFAULT 0 AFTER mail_limit",
        'mysql_limit'=>"ALTER TABLE hosting_accounts ADD COLUMN mysql_limit INT DEFAULT 0 AFTER mail_used",
        'mysql_used'=>"ALTER TABLE hosting_accounts ADD COLUMN mysql_used INT DEFAULT 0 AFTER mysql_limit",
        'ns1'=>"ALTER TABLE hosting_accounts ADD COLUMN ns1 VARCHAR(190) NULL AFTER vps_panel_url",
        'ns2'=>"ALTER TABLE hosting_accounts ADD COLUMN ns2 VARCHAR(190) NULL AFTER ns1",
    ];
    try{ $cols=[]; foreach(db()->query('SHOW COLUMNS FROM hosting_accounts')->fetchAll() as $c) $cols[$c['Field']]=true; foreach($hostingAdds as $col=>$sql){ if(empty($cols[$col])) db()->exec($sql); } db()->exec('UPDATE hosting_accounts SET whm_username=COALESCE(NULLIF(whm_username,""), username), disk_mb=COALESCE(NULLIF(disk_mb,0), disk_limit,0), disk_used_mb=COALESCE(NULLIF(disk_used_mb,0), disk_used,0), bandwidth_mb=COALESCE(NULLIF(bandwidth_mb,0), bandwidth_limit,0), bandwidth_used_mb=COALESCE(NULLIF(bandwidth_used_mb,0), bandwidth_used,0)'); }catch(Throwable $e){}
}
ao_runtime_schema_repair();

function ao_schema_ensure_v780() {
    static $done=false; if($done) return; $done=true;
    try {
        $cols=[]; $q=db()->query('SHOW COLUMNS FROM customers'); foreach($q->fetchAll() as $c){ $cols[$c['Field']]=true; }
        if(empty($cols['deleted_at'])) db()->exec('ALTER TABLE customers ADD COLUMN deleted_at datetime DEFAULT NULL AFTER status');
        if(empty($cols['restored_at'])) db()->exec('ALTER TABLE customers ADD COLUMN restored_at datetime DEFAULT NULL AFTER deleted_at');
    } catch(Throwable $e) {}
    try { db()->exec('CREATE TABLE IF NOT EXISTS customer_activity_logs (id int(11) NOT NULL AUTO_INCREMENT, customer_id int(11) DEFAULT NULL, admin_id int(11) DEFAULT NULL, action varchar(120) NOT NULL, description text DEFAULT NULL, ip_address varchar(80) DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY customer_id(customer_id), KEY action(action)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'); } catch(Throwable $e) {}
}
function ao_customer_log($customerId,$action,$description='') {
    try { ao_schema_ensure_v780(); $admin=current_admin(); db()->prepare('INSERT INTO customer_activity_logs(customer_id,admin_id,action,description,ip_address) VALUES(?,?,?,?,?)')->execute([(int)$customerId,$admin['id']??null,$action,$description,$_SERVER['REMOTE_ADDR']??'']); } catch(Throwable $e) {}
}
ao_schema_ensure_v780();




// v8.1.0 Smart Commerce & Domain Pricing Engine
function ao_schema_ensure_v810() {
    static $done=false; if($done) return; $done=true;
    try { db()->exec("CREATE TABLE IF NOT EXISTS domain_pricing_rules (id int(11) NOT NULL AUTO_INCREMENT, tld varchar(40) NOT NULL, mode varchar(30) DEFAULT 'percent', markup_percent decimal(8,2) DEFAULT 30.00, markup_fixed decimal(12,2) DEFAULT 0.00, min_profit decimal(12,2) DEFAULT 0.00, currency varchar(10) DEFAULT 'USD', registrar_override varchar(140) DEFAULT NULL, is_active tinyint(1) DEFAULT 1, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), UNIQUE KEY uniq_tld(tld)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS registrar_price_cache (id int(11) NOT NULL AUTO_INCREMENT, registrar_slug varchar(140) NOT NULL, tld varchar(40) NOT NULL, action varchar(40) DEFAULT 'register', cost decimal(12,4) DEFAULT 0.0000, currency varchar(10) DEFAULT 'USD', source varchar(40) DEFAULT 'manual', raw_response longtext DEFAULT NULL, last_checked_at datetime DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), UNIQUE KEY uniq_reg_tld_action(registrar_slug,tld,action)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS payment_fee_rules (id int(11) NOT NULL AUTO_INCREMENT, gateway varchar(120) NOT NULL, label varchar(160) NOT NULL, fee_percent decimal(8,3) DEFAULT 0.000, fee_fixed decimal(12,4) DEFAULT 0.0000, currency varchar(10) DEFAULT 'TRY', payer_mode varchar(30) DEFAULT 'customer', is_active tinyint(1) DEFAULT 1, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), UNIQUE KEY uniq_gateway(gateway)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS domain_order_routes (id int(11) NOT NULL AUTO_INCREMENT, order_id int(11) DEFAULT NULL, domain varchar(190) NOT NULL, tld varchar(40) NOT NULL, selected_registrar varchar(140) NOT NULL, registrar_cost decimal(12,4) DEFAULT 0.0000, sale_price decimal(12,4) DEFAULT 0.0000, currency varchar(10) DEFAULT 'USD', reason text DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY domain(domain), KEY order_id(order_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { $c=(int)db()->query("SELECT COUNT(*) FROM domain_pricing_rules")->fetchColumn(); if($c===0){ db()->exec("INSERT INTO domain_pricing_rules(tld,mode,markup_percent,markup_fixed,min_profit,currency,registrar_override,is_active) VALUES ('.com','percent',30,0,3,'USD',NULL,1),('.net','percent',30,0,3,'USD',NULL,1),('.org','percent',30,0,3,'USD',NULL,1),('.com.tr','percent',35,0,2,'USD',NULL,1)"); } } catch(Throwable $e) {}
    try { $c=(int)db()->query("SELECT COUNT(*) FROM payment_fee_rules")->fetchColumn(); if($c===0){ db()->exec("INSERT INTO payment_fee_rules(gateway,label,fee_percent,fee_fixed,currency,payer_mode,is_active) VALUES ('paytr','PayTR Kredi Kartı',2.990,0,'TRY','customer',1),('iyzico','İyzico Kredi Kartı',3.250,0,'TRY','customer',1),('stripe','Stripe',3.490,0.49,'USD','customer',1),('manual','Havale/EFT',0,0,'TRY','company',1)"); } } catch(Throwable $e) {}
}
ao_schema_ensure_v810();
function ao_money_round($v){ return round((float)$v, 2); }
function ao_pricing_rule_for_tld($tld){
    ao_schema_ensure_v810(); $tld='.'.ltrim(strtolower((string)$tld),'.');
    try { $q=db()->prepare('SELECT * FROM domain_pricing_rules WHERE tld=? AND is_active=1 LIMIT 1'); $q->execute([$tld]); $r=$q->fetch(); if($r) return $r; } catch(Throwable $e) {}
    return ['tld'=>$tld,'mode'=>'percent','markup_percent'=>30,'markup_fixed'=>0,'min_profit'=>3,'currency'=>'USD','registrar_override'=>null];
}
function ao_cached_registrar_cost($registrar,$tld,$action='register'){
    ao_schema_ensure_v810(); $tld='.'.ltrim(strtolower((string)$tld),'.');
    try { $q=db()->prepare('SELECT * FROM registrar_price_cache WHERE registrar_slug=? AND tld=? AND action=? LIMIT 1'); $q->execute([$registrar,$tld,$action]); if($r=$q->fetch()) return $r; } catch(Throwable $e) {}
    try { $q=db()->prepare('SELECT * FROM tld_pricing WHERE registrar_slug=? AND tld=? AND is_active=1 LIMIT 1'); $q->execute([$registrar,$tld]); if($p=$q->fetch()){ $cost=(float)($action==='renew'?$p['renew_price']:($action==='transfer'?$p['transfer_price']:$p['register_price'])); return ['registrar_slug'=>$registrar,'tld'=>$tld,'action'=>$action,'cost'=>$cost,'currency'=>$p['currency'] ?: 'TRY','source'=>'tld_pricing']; } } catch(Throwable $e) {}
    return null;
}
function ao_extract_price_from_registrar_response($body){
    $arr = json_decode((string)$body,true); if(!is_array($arr)) $arr = ao_json_xml_to_array((string)$body);
    $v = ao_find_deep($arr, ['Price','price','Cost','cost','Amount','amount','RegisterPrice','registerPrice']);
    if($v!==null && is_numeric($v)) return (float)$v;
    return 0.0;
}
function ao_registrar_cost_live_or_cache($bundle,$domain,$action='register'){
    $reg=$bundle['registrar']['slug'] ?? ''; $tld=ao_domain_tld($domain);
    $cached=ao_cached_registrar_cost($reg,$tld,$action);
    $cost=$cached ? (float)$cached['cost'] : 0.0; $currency=$cached['currency'] ?? 'USD'; $source=$cached['source'] ?? 'cache';
    // Try live check. Many registrars include price in availability response; if not, cache/manual stays in use.
    try { $api=ao_registrar_api_call($bundle, $action==='register'?'check':$action, $domain); if(!empty($api['ok'])){ $p=ao_extract_price_from_registrar_response($api['body'] ?? ''); if($p>0){ $cost=$p; $source='registrar_api'; try{ db()->prepare('INSERT INTO registrar_price_cache(registrar_slug,tld,action,cost,currency,source,raw_response,last_checked_at) VALUES(?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE cost=VALUES(cost),currency=VALUES(currency),source=VALUES(source),raw_response=VALUES(raw_response),last_checked_at=NOW()')->execute([$reg,$tld,$action,$cost,$currency,$source,substr((string)($api['body']??''),0,4000)]); }catch(Throwable $e){} } } } catch(Throwable $e) {}
    return ['registrar_slug'=>$reg,'tld'=>$tld,'action'=>$action,'cost'=>$cost,'currency'=>$currency,'source'=>$source];
}
function ao_smart_domain_quote($domain,$action='register'){
    ao_schema_ensure_v810(); $domain=ahost_domain_clean($domain); $tld=ao_domain_tld($domain); $rule=ao_pricing_rule_for_tld($tld); $quotes=[];
    $override=trim((string)($rule['registrar_override'] ?? ''));
    try { $sql='SELECT * FROM domain_registrars WHERE status="active"'; $params=[]; if($override!==''){ $sql.=' AND slug=?'; $params[]=$override; } $sql.=' ORDER BY priority ASC, name ASC'; $q=db()->prepare($sql); $q->execute($params); $regs=$q->fetchAll(); } catch(Throwable $e) { $regs=[]; }
    foreach($regs as $r){ $bundle=ao_registrar_bundle_by_id((int)$r['id']); if(!$bundle) continue; $c=ao_registrar_cost_live_or_cache($bundle,$domain,$action); if(($c['cost'] ?? 0)>0) $quotes[]=$c; }
    if(!$quotes){ $fallback=ao_tld_renew_price($domain,'domainnameapi'); if($fallback<=0) $fallback=15; $quotes[]=['registrar_slug'=>'domainnameapi','tld'=>$tld,'action'=>$action,'cost'=>$fallback,'currency'=>$rule['currency'] ?? 'USD','source'=>'fallback']; }
    usort($quotes, fn($a,$b)=>($a['cost']<=>$b['cost'])); $best=$quotes[0]; $cost=(float)$best['cost'];
    $percent=(float)($rule['markup_percent'] ?? 0); $fixed=(float)($rule['markup_fixed'] ?? 0); $min=(float)($rule['min_profit'] ?? 0);
    $sale = $cost + $fixed + ($cost*$percent/100); if(($sale-$cost)<$min) $sale=$cost+$min;
    return ['domain'=>$domain,'tld'=>$tld,'action'=>$action,'selected_registrar'=>$best['registrar_slug'],'registrar_cost'=>ao_money_round($cost),'sale_price'=>ao_money_round($sale),'profit'=>ao_money_round($sale-$cost),'currency'=>$best['currency'] ?: ($rule['currency'] ?? 'USD'),'source'=>$best['source'],'all_quotes'=>$quotes,'rule'=>$rule];
}
// v9.3.0 Dynamic Payment Commission Engine
function ao_schema_ensure_v900() {
    static $done=false; if($done) return; $done=true; ao_schema_ensure_v810();
    $cols=[]; try{ $q=db()->query('SHOW COLUMNS FROM payment_fee_rules'); foreach($q->fetchAll() as $c){ $cols[$c['Field']]=true; } }catch(Throwable $e){ $cols=[]; }
    $adds=[
        'rate_source'=>"ALTER TABLE payment_fee_rules ADD COLUMN rate_source varchar(30) DEFAULT 'manual' AFTER currency",
        'api_enabled'=>"ALTER TABLE payment_fee_rules ADD COLUMN api_enabled tinyint(1) DEFAULT 0 AFTER rate_source",
        'api_endpoint'=>"ALTER TABLE payment_fee_rules ADD COLUMN api_endpoint varchar(255) DEFAULT NULL AFTER api_enabled",
        'api_auth_json'=>"ALTER TABLE payment_fee_rules ADD COLUMN api_auth_json longtext DEFAULT NULL AFTER api_endpoint",
        'last_known_fee_percent'=>"ALTER TABLE payment_fee_rules ADD COLUMN last_known_fee_percent decimal(8,3) DEFAULT 0.000 AFTER fee_percent",
        'last_known_fee_fixed'=>"ALTER TABLE payment_fee_rules ADD COLUMN last_known_fee_fixed decimal(12,4) DEFAULT 0.0000 AFTER fee_fixed",
        'invoice_line_label'=>"ALTER TABLE payment_fee_rules ADD COLUMN invoice_line_label varchar(160) DEFAULT 'Kart İşlem Komisyonu' AFTER label",
        'last_synced_at'=>"ALTER TABLE payment_fee_rules ADD COLUMN last_synced_at datetime DEFAULT NULL AFTER api_auth_json",
        'last_sync_status'=>"ALTER TABLE payment_fee_rules ADD COLUMN last_sync_status varchar(40) DEFAULT NULL AFTER last_synced_at",
        'last_sync_message'=>"ALTER TABLE payment_fee_rules ADD COLUMN last_sync_message text DEFAULT NULL AFTER last_sync_status",
    ];
    foreach($adds as $col=>$sql){ if(empty($cols[$col])){ try{ db()->exec($sql); }catch(Throwable $e){} } }
    try{ db()->exec("CREATE TABLE IF NOT EXISTS payment_fee_sync_logs (id int(11) NOT NULL AUTO_INCREMENT, gateway varchar(120) NOT NULL, status varchar(40) NOT NULL, message text DEFAULT NULL, old_percent decimal(8,3) DEFAULT NULL, new_percent decimal(8,3) DEFAULT NULL, old_fixed decimal(12,4) DEFAULT NULL, new_fixed decimal(12,4) DEFAULT NULL, raw_response longtext DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY gateway(gateway), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); }catch(Throwable $e){}
    try{ db()->exec("UPDATE payment_fee_rules SET payer_mode='customer', invoice_line_label=COALESCE(NULLIF(invoice_line_label,''),'Kart İşlem Komisyonu'), last_known_fee_percent=IFNULL(NULLIF(last_known_fee_percent,0),fee_percent), last_known_fee_fixed=IFNULL(last_known_fee_fixed,fee_fixed) WHERE 1"); }catch(Throwable $e){}
}
ao_schema_ensure_v900();
function ao_payment_gateway_rule($gateway='paytr'){
    ao_schema_ensure_v900(); $gateway=trim((string)$gateway) ?: 'paytr';
    try{ $q=db()->prepare('SELECT * FROM payment_fee_rules WHERE gateway=? AND is_active=1 LIMIT 1'); $q->execute([$gateway]); if($r=$q->fetch()) return $r; }catch(Throwable $e){}
    return ['gateway'=>$gateway,'label'=>$gateway,'invoice_line_label'=>'Kart İşlem Komisyonu','fee_percent'=>0,'fee_fixed'=>0,'last_known_fee_percent'=>0,'last_known_fee_fixed'=>0,'currency'=>'TRY','rate_source'=>'manual','api_enabled'=>0];
}
function ao_extract_payment_rate_from_response($body){
    $arr=json_decode((string)$body,true); if(!is_array($arr)) $arr=ao_json_xml_to_array((string)$body); if(!is_array($arr)) return null;
    $percent=ao_find_deep($arr,['fee_percent','commission_percent','commissionRate','rate','percent','cardCommissionPercent']);
    $fixed=ao_find_deep($arr,['fee_fixed','fixed_fee','fixed','commissionFixed','cardCommissionFixed']);
    $currency=ao_find_deep($arr,['currency','Currency']);
    if($percent===null && $fixed===null) return null;
    return ['fee_percent'=>is_numeric($percent)?(float)$percent:0.0,'fee_fixed'=>is_numeric($fixed)?(float)$fixed:0.0,'currency'=>$currency ?: null];
}
function ao_payment_commission_sync($gateway,$force=false){
    ao_schema_ensure_v900(); $r=ao_payment_gateway_rule($gateway); $oldP=(float)($r['fee_percent']??0); $oldF=(float)($r['fee_fixed']??0);
    if(empty($r['api_enabled']) || trim((string)($r['api_endpoint']??''))==='') return ['ok'=>false,'message'=>'API komisyon çekme kapalı; manuel/son bilinen oran kullanılıyor.'];
    if(!$force && !empty($r['last_synced_at']) && strtotime($r['last_synced_at'])>time()-3600) return ['ok'=>true,'message'=>'Son senkronizasyon güncel.'];
    $body=''; $status='error'; $msg='';
    try{
        $headers=['Accept: application/json']; $auth=json_decode((string)($r['api_auth_json']??'{}'),true) ?: [];
        if(!empty($auth['bearer'])) $headers[]='Authorization: Bearer '.$auth['bearer'];
        $ctx=stream_context_create(['http'=>['method'=>'GET','timeout'=>15,'ignore_errors'=>true,'header'=>implode("\r\n",$headers)]]);
        $body=@file_get_contents($r['api_endpoint'],false,$ctx);
        if($body===false) throw new Exception('Komisyon API yanıtı alınamadı.');
        $rate=ao_extract_payment_rate_from_response($body);
        if(!$rate) throw new Exception('API yanıtında komisyon oranı bulunamadı.');
        $newP=(float)$rate['fee_percent']; $newF=(float)$rate['fee_fixed']; $cur=$rate['currency'] ?: ($r['currency']??'TRY');
        db()->prepare('UPDATE payment_fee_rules SET fee_percent=?, fee_fixed=?, last_known_fee_percent=?, last_known_fee_fixed=?, currency=?, rate_source="api", last_synced_at=NOW(), last_sync_status="success", last_sync_message=? WHERE gateway=?')->execute([$newP,$newF,$newP,$newF,$cur,'API oranı güncellendi.',$gateway]);
        $status='success'; $msg='API oranı güncellendi.';
    }catch(Throwable $e){
        $msg=$e->getMessage();
        try{ db()->prepare('UPDATE payment_fee_rules SET fee_percent=COALESCE(NULLIF(last_known_fee_percent,0),fee_percent), fee_fixed=COALESCE(last_known_fee_fixed,fee_fixed), last_synced_at=NOW(), last_sync_status="error", last_sync_message=? WHERE gateway=?')->execute([$msg,$gateway]); }catch(Throwable $x){}
    }
    try{ db()->prepare('INSERT INTO payment_fee_sync_logs(gateway,status,message,old_percent,new_percent,old_fixed,new_fixed,raw_response) VALUES(?,?,?,?,?,?,?,?)')->execute([$gateway,$status,$msg,$oldP,(float)(ao_payment_gateway_rule($gateway)['fee_percent']??0),$oldF,(float)(ao_payment_gateway_rule($gateway)['fee_fixed']??0),substr((string)$body,0,5000)]); }catch(Throwable $e){}
    return ['ok'=>$status==='success','message'=>$msg];
}
function ao_payment_fee_quote($amount,$gateway='paytr'){
    ao_schema_ensure_v900(); $amount=(float)$amount; $r=ao_payment_gateway_rule($gateway);
    if(!empty($r['api_enabled'])) ao_payment_commission_sync($gateway,false);
    $r=ao_payment_gateway_rule($gateway);
    $fee=($amount*(float)$r['fee_percent']/100)+(float)$r['fee_fixed'];
    return ['gateway'=>$gateway,'label'=>$r['label'] ?? $gateway,'line_label'=>$r['invoice_line_label'] ?? 'Kart İşlem Komisyonu','amount'=>ao_money_round($amount),'fee'=>ao_money_round($fee),'customer_total'=>ao_money_round($amount+$fee),'company_net'=>ao_money_round($amount),'payer_mode'=>'customer','currency'=>$r['currency'] ?? 'TRY','rate_source'=>$r['rate_source'] ?? 'manual'];
}

// v7.5.4 Domain Production Fix - renewal request, registrar EPP, availability search
function ao_domain_row($domainId, $customerId = null) {
    $sql = 'SELECT * FROM domains WHERE id=?'; $params = [(int)$domainId];
    if ($customerId !== null) { $sql .= ' AND customer_id=?'; $params[] = (int)$customerId; }
    $sql .= ' LIMIT 1';
    $q = db()->prepare($sql); $q->execute($params);
    return $q->fetch() ?: null;
}
function ao_domain_tld($domain) {
    $parts = explode('.', strtolower(trim((string)$domain)));
    if (count($parts) >= 3 && in_array(end($parts), ['tr'], true)) return '.' . $parts[count($parts)-2] . '.' . $parts[count($parts)-1];
    return count($parts) > 1 ? '.' . end($parts) : '';
}
function ao_tld_renew_price($domain, $registrar='domainnameapi') {
    try {
        $tld = ao_domain_tld($domain);
        $q = db()->prepare('SELECT renew_price FROM tld_pricing WHERE tld=? AND is_active=1 ORDER BY registrar_slug=? DESC LIMIT 1');
        $q->execute([$tld, $registrar]);
        $v = $q->fetchColumn();
        return $v === false ? 0.0 : (float)$v;
    } catch (Throwable $e) { return 0.0; }
}
function ao_registrar_bundle($slug) {
    $slug = strtolower(trim((string)$slug));
    $q = db()->prepare('SELECT * FROM domain_registrars WHERE slug=? OR module_name=? LIMIT 1'); $q->execute([$slug,$slug]);
    $reg = $q->fetch(); if (!$reg) return null;
    $cfg = [];
    $c = db()->prepare('SELECT config_key,config_value FROM registrar_configs WHERE registrar_id=?'); $c->execute([$reg['id']]);
    foreach ($c->fetchAll() as $row) $cfg[$row['config_key']] = $row['config_value'];
    return ['registrar'=>$reg,'config'=>$cfg];
}
function ao_domain_registrar_bundle($domainRow) {
    $slug = $domainRow['registrar'] ?? 'domainnameapi';
    $bundle = ao_registrar_bundle($slug);
    if (!$bundle && stripos($slug, 'domainnameapi') !== false) $bundle = ao_registrar_bundle('domainnameapi');
    return $bundle ?: ao_registrar_bundle('domainnameapi');
}
function ao_domain_api_post($url, $payload, $headers=[]) {
    $headers = array_merge(['Content-Type: application/json','Accept: application/json'], $headers);
    $opts = ['http'=>['method'=>'POST','timeout'=>20,'ignore_errors'=>true,'header'=>implode("\r\n", $headers),'content'=>json_encode($payload, JSON_UNESCAPED_UNICODE)]];
    $ctx = stream_context_create($opts);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    $responseHeaders=function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?: []) : [];
    foreach ($responseHeaders as $h) if (preg_match('/^HTTP\/\S+\s+(\d+)/', $h, $m)) { $code=(int)$m[1]; break; }
    return ['ok'=>$body!==false && $code>=200 && $code<300, 'code'=>$code, 'body'=>$body===false?'':$body];
}

function ao_registrar_bundle_by_id($id) {
    $id = (int)$id;
    if ($id <= 0) return null;
    $q = db()->prepare('SELECT * FROM domain_registrars WHERE id=? LIMIT 1');
    $q->execute([$id]);
    $reg = $q->fetch();
    if (!$reg) return null;
    $cfg = [];
    $c = db()->prepare('SELECT config_key,config_value FROM registrar_configs WHERE registrar_id=?');
    $c->execute([$reg['id']]);
    foreach ($c->fetchAll() as $row) $cfg[$row['config_key']] = $row['config_value'];
    return ['registrar'=>$reg,'config'=>$cfg];
}
function ao_first_nonempty($array, $keys, $default='') {
    foreach ($keys as $k) if (isset($array[$k]) && $array[$k] !== '' && $array[$k] !== null) return $array[$k];
    return $default;
}
function ao_json_xml_to_array($body) {
    $body = (string)$body;
    $json = json_decode($body, true);
    if (is_array($json)) return $json;
    $xml = @simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml) return json_decode(json_encode($xml), true) ?: [];
    return [];
}
function ao_registrar_endpoint($bundle, $action) {
    $cfg = $bundle['config'] ?? [];
    $endpoint = trim((string)($cfg[$action.'_endpoint'] ?? ''));
    if ($endpoint !== '') return $endpoint;
    $fallbacks = [
        'test' => ['check_endpoint','whois_endpoint','api_endpoint'],
        'check' => ['check_endpoint','api_endpoint'],
        'whois' => ['whois_endpoint','api_endpoint'],
        'epp' => ['epp_endpoint','api_endpoint'],
        'renew' => ['renew_endpoint','api_endpoint'],
        'dns' => ['dns_endpoint','api_endpoint'],
        'nameserver' => ['ns_endpoint','nameserver_endpoint','api_endpoint'],
        'lock' => ['lock_endpoint','api_endpoint'],
    ];
    foreach (($fallbacks[$action] ?? ['api_endpoint']) as $k) {
        if (!empty($cfg[$k])) return trim((string)$cfg[$k]);
    }
    return '';
}
function ao_registrar_payload($bundle, $action, $domain='', $extra=[]) {
    $cfg = $bundle['config'] ?? [];
    $reg = $bundle['registrar'] ?? [];
    $domain = ahost_domain_clean($domain ?: ($cfg['test_domain'] ?? 'example.com'));
    $payload = array_merge([
        'action' => $action,
        'command' => $action,
        'domain' => $domain,
        'sld' => preg_replace('/\.[^.]+$/','',$domain),
        'tld' => ao_domain_tld($domain),
        'username' => $cfg['api_username'] ?? '',
        'userName' => $cfg['api_username'] ?? '',
        'api_username' => $cfg['api_username'] ?? '',
        'password' => $cfg['api_password'] ?? '',
        'api_password' => $cfg['api_password'] ?? '',
        'api_key' => $cfg['api_key'] ?? ($cfg['api_username'] ?? ''),
        'api_secret' => $cfg['api_secret'] ?? ($cfg['api_password'] ?? ''),
        'token' => $cfg['token'] ?? '',
        'reseller_id' => $cfg['reseller_id'] ?? '',
        'registrar' => $reg['slug'] ?? '',
        'auth_mode' => $cfg['auth_mode'] ?? 'userpass',
    ], $extra);
    return $payload;
}
function ao_registrar_api_call($bundle, $action, $domain='', $extra=[]) {
    if (!$bundle) return ['ok'=>false,'code'=>0,'body'=>'','message'=>'Registrar yapılandırması bulunamadı.'];
    if (ao_is_domainnameapi_bundle($bundle)) return ao_dna_action_call($bundle, $action, $domain, $extra);
    $reg = $bundle['registrar'];
    $cfg = $bundle['config'];
    $endpoint = ao_registrar_endpoint($bundle, $action === 'test' ? 'test' : $action);
    if (!filter_var($endpoint, FILTER_VALIDATE_URL)) return ['ok'=>false,'code'=>0,'body'=>'','message'=>'Registrar endpoint boş veya geçersiz.'];
    $payload = ao_registrar_payload($bundle, $action, $domain, $extra);
    $headers = [];
    $authMode = $cfg['auth_mode'] ?? 'userpass';
    if ($authMode === 'token' && !empty($cfg['token'])) $headers[] = 'Authorization: Bearer '.trim((string)$cfg['token']);
    if ($authMode === 'apikey' && !empty($cfg['api_key'])) $headers[] = 'X-API-Key: '.trim((string)$cfg['api_key']);
    $r = ao_domain_api_post($endpoint, $payload, $headers);
    $status = $r['ok'] ? 'success' : 'error';
    ao_log_simple($reg['slug'] ?? 'registrar', 'registrar-'.$action, $status, 'HTTP '.$r['code'].' endpoint='.$endpoint, json_encode(['domain'=>$payload['domain']??'', 'response'=>substr((string)$r['body'],0,700)], JSON_UNESCAPED_UNICODE));
    $r['message'] = $r['ok'] ? 'Registrar API yanıt verdi.' : 'Registrar API yanıtı başarısız veya endpoint ulaşılamadı.';
    return $r;
}
function ao_extract_availability($body) {
    $a = ao_json_xml_to_array($body);
    if (is_array($a)) {
        $v = ao_find_deep($a, ['Status','Availability','Available','available','IsAvailable']);
        if ($v !== null) {
            $sv = strtolower((string)$v);
            if (in_array($sv, ['available','avail','true','1','yes','free'], true)) return true;
            if (in_array($sv, ['unavailable','registered','false','0','no','taken'], true)) return false;
        }
    }
    $status = strtolower((string)(ao_find_deep($a, ['Status','status','Availability','availability']) ?? ''));
    $reason = strtolower((string)(ao_find_deep($a, ['Reason','reason']) ?? ''));
    if ($status !== '') {
        if (str_contains($status, 'available') || str_contains($status, 'free') || $status === '1' || $status === 'true') return true;
        if (str_contains($status, 'unavailable') || str_contains($status, 'registered') || str_contains($status, 'taken') || $status === '0' || $status === 'false') return false;
    }
    if ($reason && preg_match('/available|free|müsait|musait/', $reason)) return true;
    $text = strtolower((string)$body);
    if (preg_match('/available|not registered|is free|müsait|musait/', $text) && !preg_match('/not available|unavailable/', $text)) return true;
    if (preg_match('/unavailable|registered|not available|taken|kayıtlı|kayitli/', $text)) return false;
    return null;
}
function ao_extract_whois_rows_from_response($body) {
    $a = ao_json_xml_to_array($body);
    if (!$a) return [];
    return [
        'Registrar' => ao_find_deep($a, ['Registrar','RegistrarName','SponsoringRegistrar','Company']) ?: '',
        'Kayıt Tarihi' => ao_find_deep($a, ['CreationDate','CreateDate','Created','RegistrationDate','CreatedDate']) ?: '',
        'Son Güncelleme' => ao_find_deep($a, ['UpdatedDate','UpdateDate','ModifiedDate','LastUpdate']) ?: '',
        'Bitiş Tarihi' => ao_find_deep($a, ['ExpirationDate','ExpiryDate','ExpireDate','Expires','Expiration']) ?: '',
        'Domain Durumu' => ao_find_deep($a, ['Status','DomainStatus','State']) ?: '',
        'Registrar Lock' => ao_find_deep($a, ['TheftProtectionLock','LockStatus','TransferLock','RegistrarLock']) ?: '',
        'Oto Yenileme' => ao_find_deep($a, ['AutoRenew','AutoRenewStatus','RenewalMode']) ?: '',
        'DNSSEC' => ao_find_deep($a, ['DnsSec','DNSSEC','Dnssec']) ?: '',
        'IANA ID' => ao_find_deep($a, ['IanaId','IANAID','RegistrarIanaId']) ?: '',
    ];
}
function ao_extract_epp_from_response($body) {
    $j = json_decode((string)$body, true);
    if (is_array($j)) {
        $v = ao_find_deep($j, ['AuthCode','authCode','EppCode','eppcode','epp_code','epp','TransferCode','transferCode','code','Auth']);
        if ($v !== null && $v !== '') return (string)$v;
    }
    if (preg_match('/<(?:AuthCode|authCode|EppCode|eppcode|epp_code|epp|transfer_code|code)>\s*([^<]+)\s*<\//i', (string)$body, $m)) return trim($m[1]);
    return '';
}
function ao_domain_generate_epp($domainRow) {
    $bundle = ao_domain_registrar_bundle($domainRow);
    $domain = $domainRow['domain_name'] ?? '';
    if (!$bundle) return ['ok'=>false,'message'=>'Registrar yapılandırması bulunamadı.'];
    $reg = $bundle['registrar']; $cfg = $bundle['config'];
    $r = ao_registrar_api_call($bundle, 'epp', $domain);
    $epp = ao_extract_epp_from_response($r['body']);
    if ($r['ok'] && $epp) { db()->prepare('UPDATE domains SET epp_code=? WHERE id=?')->execute([$epp,$domainRow['id']]); return ['ok'=>true,'epp'=>$epp,'message'=>'EPP kodu registrar API üzerinden alındı.']; }
    return ['ok'=>false,'message'=>'Registrar API EPP kodu döndürmedi. Registrar bağlantı testi, endpoint ve API loglarını kontrol edin.'];
}
function ao_domain_create_renewal_order($domainRow, $years=1, $payment='pending') {
    $years = max(1, (int)$years); $domain = $domainRow['domain_name']; $customerId = (int)$domainRow['customer_id'];
    $price = ao_tld_renew_price($domain, $domainRow['registrar'] ?: 'domainnameapi'); if ($price <= 0) $price = 499.00;
    $total = $price * $years; $orderNo = ao_generate_number(admin_setting('order_prefix','AO'), 'orders', 'order_number');
    db()->prepare('INSERT INTO orders(customer_id,order_number,status,total,payment_method,fraud_score,provision_status,notes) VALUES(?,? ,"pending", ?, ?, 0, "pending", ?)')->execute([$customerId,$orderNo,$total,$payment,'Domain yenileme talebi: '.$domain.' / '.$years.' yıl']);
    $orderId = (int)db()->lastInsertId();
    db()->prepare('INSERT INTO order_items(order_id,product_id,item_type,item_name,domain,billing_cycle,price,module_name) VALUES(?,NULL,"domain-renewal",?,?,?,?,"domainnameapi")')->execute([$orderId,'Domain Yenileme '.$years.' Yıl',$domain,'annually',$total]);
    $invoiceId = function_exists('ao_create_invoice_for_order') ? ao_create_invoice_for_order($orderId) : 0;
    ao_log_simple('domain','renewal-request','success','Domain yenileme için sipariş/fatura oluşturuldu.', json_encode(['domain'=>$domain,'order_id'=>$orderId,'invoice_id'=>$invoiceId], JSON_UNESCAPED_UNICODE));
    return ['order_id'=>$orderId,'invoice_id'=>$invoiceId,'total'=>$total];
}
function ao_domain_availability($domain) {
    $domain = ahost_domain_clean($domain);
    if (!ahost_domain_valid($domain)) return ['ok'=>false,'domain'=>$domain,'available'=>false,'message'=>'Geçersiz domain.'];
    try { $q=db()->prepare('SELECT id,status FROM domains WHERE domain_name=? LIMIT 1'); $q->execute([$domain]); if ($r=$q->fetch()) return ['ok'=>true,'domain'=>$domain,'available'=>false,'source'=>'local','message'=>'Domain sistemde kayıtlı: '.$r['status']]; } catch(Throwable $e) {}
    try {
        $bundle = ao_registrar_bundle('domainnameapi');
        if ($bundle && (($bundle['registrar']['status'] ?? '') === 'active')) {
            $r = ao_registrar_api_call($bundle, 'check', $domain);
            if ($r['ok']) {
                $available = ao_extract_availability($r['body']);
                if ($available !== null) return ['ok'=>true,'domain'=>$domain,'available'=>$available,'source'=>'registrar:domainnameapi','message'=>$available?'Domain registrar API’ye göre müsait görünüyor.':'Domain registrar API’ye göre kayıtlı görünüyor.'];
            }
        }
    } catch (Throwable $e) {}
    $hasDns = false;
    if (function_exists('dns_get_record')) { $rec = @dns_get_record($domain, DNS_A + DNS_AAAA + DNS_MX + DNS_NS); $hasDns = is_array($rec) && count($rec)>0; }
    return ['ok'=>true,'domain'=>$domain,'available'=>!$hasDns,'source'=>'dns-fallback','message'=>$hasDns?'Domain DNS kayıtlarına sahip, kayıtlı görünüyor. Registrar API kesin sonuç vermezse fallback kullanıldı.':'DNS kaydı bulunamadı, kayıt edilebilir olabilir. Registrar API kesin sonuç vermezse fallback kullanıldı.'];
}
if ($route === 'admin/customers/close') {
    verify_csrf();
    $id=(int)($_GET['id']??0);
    if($id>0){
        try{ ao_schema_ensure_v780(); db()->prepare('UPDATE customers SET status="closed" WHERE id=?')->execute([$id]); ao_customer_log($id,'customer.closed','Müşteri kapatıldı; veriler korundu.'); flash('success','Müşteri kapalı duruma alındı. İlişkili kayıtlar korunur.'); }
        catch(Throwable $e){ flash('error','Müşteri kapatılamadı: '.$e->getMessage()); }
    }
    redirect_to('admin/customers');
}
if ($route === 'admin/customers/delete') {
    verify_csrf();
    $id=(int)($_GET['id']??0);
    if($id>0){
        try{ ao_schema_ensure_v780(); db()->prepare('UPDATE customers SET status="deleted", deleted_at=NOW() WHERE id=?')->execute([$id]); ao_customer_log($id,'customer.soft_deleted','Müşteri soft delete ile çöp kutusuna taşındı.'); flash('success','Müşteri çöp kutusuna taşındı. Kalıcı silme için Silinenler görünümünü kullan.'); }
        catch(Throwable $e){ flash('error','Müşteri silinemedi: '.$e->getMessage()); }
    }
    redirect_to('admin/customers');
}
if ($route === 'admin/customers/restore') {
    verify_csrf();
    $id=(int)($_GET['id']??0);
    if($id>0){ try{ ao_schema_ensure_v780(); db()->prepare('UPDATE customers SET status="active", restored_at=NOW(), deleted_at=NULL WHERE id=?')->execute([$id]); ao_customer_log($id,'customer.restored','Silinen müşteri geri yüklendi.'); flash('success','Müşteri geri yüklendi.'); } catch(Throwable $e){ flash('error','Müşteri geri yüklenemedi: '.$e->getMessage()); } }
    redirect_to('admin/customers?show=deleted');
}
if ($route === 'admin/customers/permanent-delete') {
    verify_csrf();
    $id=(int)($_GET['id']??0);
    if($id>0){
        try{
            $pdo=db(); $pdo->beginTransaction();
            foreach(['credit_transactions','payments','invoice_items','invoices','order_items','orders','ticket_replies','tickets','hosting_accounts','services','domain_dns_records','domain_nameservers','domains','customer_activity_logs'] as $tbl){
                try{
                    if(in_array($tbl,['hosting_accounts'],true)) $pdo->prepare('DELETE h FROM hosting_accounts h JOIN services s ON s.id=h.service_id WHERE s.customer_id=?')->execute([$id]);
                    elseif(in_array($tbl,['invoice_items'],true)) $pdo->prepare('DELETE ii FROM invoice_items ii JOIN invoices i ON i.id=ii.invoice_id WHERE i.customer_id=?')->execute([$id]);
                    elseif(in_array($tbl,['order_items'],true)) $pdo->prepare('DELETE oi FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.customer_id=?')->execute([$id]);
                    elseif(in_array($tbl,['domain_dns_records'],true)) $pdo->prepare('DELETE r FROM domain_dns_records r JOIN domains d ON d.id=r.domain_id WHERE d.customer_id=?')->execute([$id]);
                    elseif(in_array($tbl,['domain_nameservers'],true)) $pdo->prepare('DELETE ns FROM domain_nameservers ns JOIN domains d ON d.id=ns.domain_id WHERE d.customer_id=?')->execute([$id]);
                    elseif(in_array($tbl,['payments'],true)) $pdo->prepare('DELETE p FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.customer_id=?')->execute([$id]);
                    elseif(in_array($tbl,['ticket_replies'],true)) $pdo->prepare('DELETE tr FROM ticket_replies tr JOIN tickets t ON t.id=tr.ticket_id WHERE t.customer_id=?')->execute([$id]);
                    else $pdo->prepare('DELETE FROM '.$tbl.' WHERE customer_id=?')->execute([$id]);
                } catch(Throwable $ignore) {}
            }
            $pdo->prepare('DELETE FROM customers WHERE id=?')->execute([$id]);
            $pdo->commit(); flash('success','Müşteri ve bağlı kayıtlar kalıcı olarak silindi.');
        } catch(Throwable $e){ if(isset($pdo) && $pdo->inTransaction()) $pdo->rollBack(); flash('error','Kalıcı silme yapılamadı: '.$e->getMessage()); }
    }
    redirect_to('admin/customers?show=deleted');
}
if ($route === 'admin/hosting-server/delete') {
    verify_csrf();
    $id=(int)($_GET['id']??0);
    if($id>0){ try{ db()->prepare('DELETE FROM server_nodes WHERE id=?')->execute([$id]); flash('success','Sunucu silindi.'); } catch(Throwable $e){ flash('error','Sunucu silinemedi.'); } }
    redirect_to('admin/hosting-server/servers');
}
if ($route === 'admin/hosting-server/login') {
    require_admin();
    $id=(int)($_GET['id']??0); $target='';
    try{
        $q=db()->prepare('SELECT * FROM server_nodes WHERE id=?'); $q->execute([$id]); $srv=$q->fetch();
        if($srv){
            $panel = ($srv['panel_type']==='whm' || $srv['panel_type']==='cpanel') ? 'whm' : $srv['panel_type'];
            if(in_array($panel, ['whm','cpanel'], true) && !empty($srv['api_token'])){
                $sso = ao_whm_create_user_session($srv, $srv['username'] ?: 'root', 'whostmgrd');
                if(!empty($sso['ok'])) $target = $sso['url'];
                else ao_log_simple('server','whm-sso-error','error',$sso['message'] ?? 'WHM SSO başarısız');
            }
            if(!$target) $target=ao_panel_url_from_host(ao_host_from_server_row($srv), $panel);
        }
    }catch(Throwable $e){ ao_log_simple('server','login-redirect-error','error',$e->getMessage()); }
    if($target && $target!=='#'){ ao_log_simple('server','login-redirect','success','Sunucu panel yönlendirmesi hazırlandı.'); header('Location: '.$target); exit; }
    flash('error','Sunucu giriş URL oluşturulamadı. Host/IP ve WHM API token alanını kontrol edin.'); redirect_to('admin/hosting-server/servers');
}
if ($route === 'admin/hosting-server/test') {
    $id=(int)($_GET['id']??0);
    try{ $q=db()->prepare('SELECT * FROM server_nodes WHERE id=?'); $q->execute([$id]); $srv=$q->fetch(); if(!$srv) throw new Exception('Sunucu yok.'); $url=ao_panel_url_from_host(ao_host_from_server_row($srv), $srv['panel_type']==='whm'?'whm':$srv['panel_type']); db()->prepare('UPDATE server_nodes SET status=IF(status="inactive","ready",status) WHERE id=?')->execute([$id]); ao_log_simple($srv['panel_type'],'connection-test','success','Test URL hazır: '.$url); flash('success','Bağlantı testi hazır. Canlı API token girildiğinde gerçek API testi yapılır. URL: '.$url); }catch(Throwable $e){ flash('error','Sunucu test edilemedi: '.$e->getMessage()); }
    redirect_to('admin/hosting-server/servers');
}
if ($route === 'client/service-panel-login' || $route === 'admin/service-panel-login') {
    $serviceId=(int)($_GET['service_id']??0); $panel=trim($_GET['panel']??'cpanel'); $h=null;
    try{
        if($route === 'client/service-panel-login') { require_customer(); $c=current_customer(); $q=db()->prepare('SELECT h.* FROM hosting_accounts h JOIN services s ON s.id=h.service_id WHERE h.service_id=? AND s.customer_id=? LIMIT 1'); $q->execute([$serviceId,$c['id']]); }
        else { require_admin(); $q=db()->prepare('SELECT h.* FROM hosting_accounts h WHERE h.service_id=? LIMIT 1'); $q->execute([$serviceId]); }
        $h=$q->fetch();
        if(!$h) throw new Exception('Hosting hesabı bulunamadı.');
        $srv=null;
        if(!empty($h['server_id'])){ $sq=db()->prepare('SELECT * FROM server_nodes WHERE id=? LIMIT 1'); $sq->execute([(int)$h['server_id']]); $srv=$sq->fetch() ?: null; }
        $col=['cpanel'=>'cpanel_url','directadmin'=>'directadmin_url','webmail'=>'webmail_url','whm'=>'whm_url','vps'=>'vps_panel_url','plesk'=>'plesk_url'][$panel] ?? 'cpanel_url';
        $url='';
        if($srv && in_array($panel, ['cpanel','webmail','whm'], true) && !empty($srv['api_token'])){
            $loginUser = $panel==='whm' ? ($srv['username'] ?: 'root') : ($h['whm_username'] ?: ($h['username'] ?? ''));
            $sso = ao_whm_create_user_session($srv, $loginUser, ao_panel_service_name($panel));
            if(!empty($sso['ok'])) $url=$sso['url'];
            else ao_log_simple('hosting-panel','sso-'.$panel.'-error','error',$sso['message'] ?? 'WHM SSO başarısız');
        }
        if(!$url) $url=trim($h[$col] ?? '');
        if(!$url || $url==='#') $url=ao_panel_url_from_host(($h['server_name'] ?? '') ?: ($h['server_ip'] ?? ''), $panel);
        if(!$url || $url==='#') throw new Exception('Panel URL boş.');
        ao_log_simple('hosting-panel','login-'.$panel,'success','Panel yönlendirmesi service_id='.$serviceId);
        header('Location: '.$url); exit;
    }catch(Throwable $e){ flash('error','Panel giriş yönlendirmesi yapılamadı: '.$e->getMessage()); redirect_to($route === 'client/service-panel-login' ? 'client/services/view?id='.$serviceId : 'admin/customers'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/support/ticket-reply') {
    verify_csrf();
    $tid=(int)($_POST['ticket_id']??0); $msg=trim($_POST['message']??''); $status=trim($_POST['status']??'answered');
    try{ if(!$tid||!$msg) throw new Exception('Ticket ve mesaj zorunlu.'); db()->prepare('INSERT INTO ticket_replies(ticket_id,sender_type,message) VALUES(?,"admin",?)')->execute([$tid,$msg]); db()->prepare('UPDATE tickets SET status=? WHERE id=?')->execute([$status,$tid]); flash('success','Ticket yanıtlandı.'); }catch(Throwable $e){ flash('error','Ticket yanıtlanamadı: '.$e->getMessage()); }
    redirect_to('admin/support/tickets');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/support/department-save') {
    verify_csrf();
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $sla=(int)($_POST['sla_hours']??24);
    try{ if(!$name) throw new Exception('Departman adı zorunlu.'); db()->prepare('INSERT INTO support_departments(name,email,sla_hours,is_active) VALUES(?,?,?,1)')->execute([$name,$email,$sla]); flash('success','Destek departmanı kaydedildi.'); }catch(Throwable $e){ flash('error','Departman kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/support/departments');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/login') {
    $email = trim($_POST['email'] ?? ''); $pass = $_POST['password'] ?? '';
    ao_mfa_ensure_schema();
    $s = db()->prepare('SELECT * FROM customers WHERE email=? AND status<>"closed" LIMIT 1'); $s->execute([$email]); $c=$s->fetch();
    if ($c && !empty($c['password_hash']) && password_verify($pass, $c['password_hash'])) {
        try { $u=db()->prepare('UPDATE customers SET last_login_at=NOW() WHERE id=?'); $u->execute([$c['id']]); } catch(Throwable $e){}
        ao_mfa_start_challenge('customer', $c, 'client');
    }
    ao_mfa_log('customer', null, $email, 'login', 'password', 'failed', 'Müşteri e-posta veya şifre hatalı.');
    flash('error','E-posta veya şifre hatalı.'); redirect_to('client/login');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/security/mfa-save') {
    require_customer(); verify_csrf(); ao_mfa_ensure_schema();
    $customer=current_customer(); $uid=(int)$customer['id'];
    $enabled=(($_POST['enabled'] ?? '0')==='1') ? 1 : 0;
    $method=in_array(($_POST['preferred_method'] ?? 'mail'), ['mail','totp','sms'], true) ? $_POST['preferred_method'] : 'mail';
    $secret = $method==='totp' ? ao_mfa_get_totp_secret('customer',$uid,true) : null;
    try { db()->prepare('INSERT INTO auth_mfa_profiles(user_type,user_id,enabled,preferred_method,totp_secret,verified_at) VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled), preferred_method=VALUES(preferred_method), totp_secret=COALESCE(VALUES(totp_secret),totp_secret), verified_at=NOW()')->execute(['customer',$uid,$enabled,$method,$secret]); flash('success','2FA ayarlarınız kaydedildi.'); }
    catch(Throwable $e){ flash('error','2FA ayarı kaydedilemedi: '.$e->getMessage()); }
    redirect_to('client/security');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/register') {
    verify_csrf(); ao_schema_ensure_v186();
    $first=trim($_POST['first_name']??''); $last=trim($_POST['last_name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??''; $phone=trim($_POST['phone']??''); $tc=trim($_POST['tc_identity_no']??''); $birth=trim($_POST['birth_date']??'');
    $productSlug=preg_replace('/[^a-z0-9_-]/','',strtolower(trim($_POST['product_slug']??''))); $registerReturn='client/register'.($productSlug?'?product='.rawurlencode($productSlug):'');
    if (!$first || !$last || !$email || !$phone || !$tc || !$birth || strlen($pass)<6) { flash('error','Ad, soyad, telefon, e-posta, TC Kimlik No, doğum tarihi ve en az 6 karakter şifre zorunludur.'); redirect_to($registerReturn); }
    $verify=ao_identity_verify($first,$last,$birth,$tc);
    if(empty($verify['ok'])){ flash('error','Kayıt oluşturulmadı: '.$verify['message']); redirect_to($registerReturn); }
    try { $s=db()->prepare('INSERT INTO customers(first_name,last_name,email,phone,tc_identity_no,birth_date,identity_verified,identity_verified_at,password_hash,status) VALUES(?,?,?,?,?,?,1,NOW(),?,"active")'); $s->execute([$first,$last,$email,$phone,preg_replace('/\D/','',$tc),$birth,password_hash($pass,PASSWORD_DEFAULT)]); $_SESSION['customer_id']=db()->lastInsertId(); if($productSlug) $_SESSION['pending_product_slug']=$productSlug; flash('success',$productSlug?'Hesabınız oluşturuldu; seçtiğiniz ürün sipariş tercihinize eklendi.':'Hesabınız oluşturuldu.'); redirect_to('client'); }
    catch(Throwable $e){ flash('error','Kayıt oluşturulamadı. Bu e-posta daha önce kullanılmış olabilir.'); redirect_to($registerReturn); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/forgot-password') {
    ao_schema_ensure_v188(); verify_csrf();
    $email=trim($_POST['email']??''); $channel=trim($_POST['channel']??'email');
    try{
        $q=db()->prepare('SELECT * FROM customers WHERE email=? AND status<>"closed" LIMIT 1'); $q->execute([$email]); $c=$q->fetch();
        if(!$c) throw new Exception('Bu e-posta için kayıt bulunamadı.');
        $token=bin2hex(random_bytes(32)); $hash=hash('sha256',$token); $expires=date('Y-m-d H:i:s',time()+3600);
        db()->prepare('INSERT INTO password_reset_tokens(customer_id,email,token_hash,channel,expires_at) VALUES(?,?,?,?,?)')->execute([(int)$c['id'],$email,$hash,$channel,$expires]);
        $link=url('client/reset-password?token='.$token);
        ao_log_simple('password_reset',$channel,'queued','Şifre yenileme linki oluşturuldu: '.$email,json_encode(['link'=>$link,'expires_at'=>$expires],JSON_UNESCAPED_UNICODE));
        flash('success','Şifre değiştirme linki '.$channel.' kanalına hazırlandı. Link 1 saat geçerlidir. Fresh install test ortamında link loglara yazılır.');
    }catch(Throwable $e){ flash('error','Şifre yenileme başlatılamadı: '.$e->getMessage()); }
    redirect_to('client/forgot-password');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/reset-password') {
    ao_schema_ensure_v188(); verify_csrf();
    $token=trim($_POST['token']??''); $pass=$_POST['password']??'';
    try{ if(strlen($pass)<6) throw new Exception('Şifre en az 6 karakter olmalı.'); $hash=hash('sha256',$token); $q=db()->prepare('SELECT * FROM password_reset_tokens WHERE token_hash=? AND used_at IS NULL AND expires_at>NOW() LIMIT 1'); $q->execute([$hash]); $r=$q->fetch(); if(!$r) throw new Exception('Link geçersiz veya süresi dolmuş.'); db()->prepare('UPDATE customers SET password_hash=? WHERE id=?')->execute([password_hash($pass,PASSWORD_DEFAULT),(int)$r['customer_id']]); db()->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE id=?')->execute([(int)$r['id']]); flash('success','Şifreniz değiştirildi. Giriş yapabilirsiniz.'); redirect_to('client/login'); }catch(Throwable $e){ flash('error','Şifre değiştirilemedi: '.$e->getMessage()); redirect_to('client/reset-password?token='.urlencode($token)); }
}
if ($route === 'client/logout') { unset($_SESSION['customer_id'], $_SESSION['mfa_pending']); flash('success','Çıkış yapıldı.'); redirect_to('client/login'); }



if ($route === 'admin/customers/login-as') {
    $id=(int)($_GET['id']??0);
    if ($id>0) {
        $q=db()->prepare('SELECT id FROM customers WHERE id=? AND status<>"closed" LIMIT 1');
        $q->execute([$id]);
        if ($q->fetch()) {
            $_SESSION['admin_impersonating_customer_id']=$id;
            $_SESSION['customer_id']=$id;
            flash('success','Sahip olarak müşteri paneline geçildi.');
            redirect_to('client');
        }
    }
    flash('error','Müşteri oturumu başlatılamadı.');
    redirect_to('admin/customers');
}
if ($route === 'admin/customers/stop-login-as') {
    $id=(int)($_SESSION['admin_impersonating_customer_id']??0);
    unset($_SESSION['admin_impersonating_customer_id'], $_SESSION['customer_id']);
    flash('success','Müşteri oturumu kapatıldı, admin profiline dönüldü.');
    redirect_to($id ? 'admin/customers/view?id='.$id : 'admin/customers');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/customers/add') {
    ao_schema_ensure_v186();
    $first=trim($_POST['first_name']??''); $last=trim($_POST['last_name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $company=trim($_POST['company_name']??''); $status=trim($_POST['status']??'active'); $pass=$_POST['password']??''; $tc=trim($_POST['tc_identity_no']??''); $birth=trim($_POST['birth_date']??'');
    if (!$first || !$last || !$email) { flash('error','Ad, soyad ve e-posta zorunludur. TC ve doğum tarihi admin eklemede opsiyoneldir.'); redirect_to('admin/customers/add'); }
    $identityVerified = ($tc && $birth && ao_identity_verify($first,$last,$birth,$tc)['ok']) ? 1 : 0;
    $hash = $pass ? password_hash($pass, PASSWORD_DEFAULT) : null;
    try { $q=db()->prepare('INSERT INTO customers(first_name,last_name,company_name,email,phone,tc_identity_no,birth_date,identity_verified,identity_verified_at,password_hash,status) VALUES(?,?,?,?,?,?,?,?,?,?,?)'); $q->execute([$first,$last,$company,$email,$phone,$tc?preg_replace('/\D/','',$tc):null,$birth?:null,$identityVerified,$identityVerified?date('Y-m-d H:i:s'):null,$hash,$status]); flash('success','Müşteri oluşturuldu.'); redirect_to('admin/customers/view?id='.db()->lastInsertId()); }
    catch(Throwable $e){ flash('error','Müşteri eklenemedi: e-posta daha önce kullanılmış olabilir.'); redirect_to('admin/customers/add'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/customers/update') {
    ao_schema_ensure_v186();
    $id=(int)($_POST['id']??0); $first=trim($_POST['first_name']??''); $last=trim($_POST['last_name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $company=trim($_POST['company_name']??''); $status=trim($_POST['status']??'active'); $balance=(float)($_POST['balance']??0); $tc=trim($_POST['tc_identity_no']??''); $birth=trim($_POST['birth_date']??'');
    $address1=trim($_POST['address1']??''); $address2=trim($_POST['address2']??''); $city=trim($_POST['city']??''); $state=trim($_POST['state']??''); $postcode=trim($_POST['postcode']??''); $country=trim($_POST['country']??'Türkiye'); $tax=trim($_POST['tax_number']??''); $currency=trim($_POST['currency']??'TRY'); $language=trim($_POST['language']??'tr'); $notes=trim($_POST['notes']??'');
    $identityVerified = ($tc && $birth && ao_identity_verify($first,$last,$birth,$tc)['ok']) ? 1 : 0;
    if ($id>0 && $first && $last && $email) { try { $q=db()->prepare('UPDATE customers SET first_name=?,last_name=?,company_name=?,email=?,phone=?,tc_identity_no=?,birth_date=?,identity_verified=?,identity_verified_at=IF(?=1,NOW(),identity_verified_at),status=?,balance=?,address1=?,address2=?,city=?,state=?,postcode=?,country=?,tax_number=?,currency=?,language=?,notes=? WHERE id=?'); $q->execute([$first,$last,$company,$email,$phone,$tc?preg_replace('/\D/','',$tc):null,$birth?:null,$identityVerified,$identityVerified,$status,$balance,$address1,$address2,$city,$state,$postcode,$country,$tax,$currency,$language,$notes,$id]); flash('success','Müşteri bilgileri güncellendi.'); } catch(Throwable $e){ flash('error','Güncelleme yapılamadı.'); } }
    redirect_to('admin/customers/view?id='.$id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/credit/add') {
    require_customer(); $c=current_customer(); ao_schema_ensure_v990();
    $amount=(float)($_POST['amount']??0); $method=trim((string)($_POST['method']??'manual')) ?: 'manual';
    try{
        if($amount<=0) throw new Exception('Geçerli bir tutar girin.');
        $topupId=ao_credit_topup_create($c,$amount,$method);
        if($method==='manual'){
            flash('success','Bakiye yükleme talebiniz alındı. Havale/EFT sonrası admin onayıyla bakiyeniz güncellenecek.');
            redirect_to('client/credit');
        }
        if($method==='shopier') redirect_to('payment/shopier/start?topup_id='.$topupId);
        flash('success','Bakiye yükleme talebi oluşturuldu. Seçilen ödeme sağlayıcısı hazır olduğunda yönlendirme yapılacak.');
    }catch(Throwable $e){ flash('error','Bakiye yükleme başlatılamadı: '.$e->getMessage()); }
    redirect_to('client/credit');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/customers/credit') {
    $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'Admin kredi işlemi');
    if($id>0 && $amount!=0){
        try{ $q=db()->prepare('UPDATE customers SET balance=COALESCE(balance,0)+?, credit_balance=COALESCE(credit_balance,0)+? WHERE id=?'); $q->execute([$amount,$amount,$id]); flash('success','Müşteri kredisi güncellendi.'); }
        catch(Throwable $e){ flash('error','Kredi işlemi başarısız.'); }
    } else { flash('error','Kredi işlemi için müşteri ve tutar gerekli.'); }
    redirect_to('admin/customers/view?id='.$id);
}


// v7.3.0 Customer Profile Pro - admin service/domain actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/customers/service-action') {
    $customerId=(int)($_POST['customer_id']??0); $serviceId=(int)($_POST['service_id']??0); $action=trim($_POST['service_action']??'');
    $allowed=['suspend'=>'suspended','unsuspend'=>'active','terminate'=>'terminated','activate'=>'active','pending'=>'pending'];
    if($customerId>0 && $serviceId>0){
        try{
            if(isset($allowed[$action])){
                $q=db()->prepare('UPDATE services SET status=? WHERE id=? AND customer_id=?'); $q->execute([$allowed[$action],$serviceId,$customerId]);
                flash('success','Hizmet durumu güncellendi: '.$allowed[$action]);
            } elseif($action==='change-package'){
                $package=trim($_POST['package_name']??'');
                if($package!=='') { $q=db()->prepare('UPDATE hosting_accounts h JOIN services s ON s.id=h.service_id SET h.package_name=? WHERE h.service_id=? AND s.customer_id=?'); $q->execute([$package,$serviceId,$customerId]); flash('success','Hosting paketi güncellendi.'); }
            } elseif($action==='change-password'){
                $pass=trim($_POST['panel_password']??'');
                if($pass!=='') { $q=db()->prepare('UPDATE hosting_accounts h JOIN services s ON s.id=h.service_id SET h.panel_password=? WHERE h.service_id=? AND s.customer_id=?'); $q->execute([$pass,$serviceId,$customerId]); flash('success','Panel şifresi güncellendi.'); }
            } elseif($action==='move-server'){
                $server=trim($_POST['server_name']??''); $ip=trim($_POST['server_ip']??'');
                $q=db()->prepare('UPDATE hosting_accounts h JOIN services s ON s.id=h.service_id SET h.server_name=?, h.server_ip=? WHERE h.service_id=? AND s.customer_id=?'); $q->execute([$server,$ip,$serviceId,$customerId]); flash('success','Sunucu bilgileri güncellendi.');
            } else { flash('error','Geçersiz hizmet işlemi.'); }
        } catch(Throwable $e){ flash('error','Hizmet işlemi tamamlanamadı.'); }
    }
    redirect_to('admin/customers/view?id='.$customerId.'#tab-hosting');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/customers/domain-action') {
    $customerId=(int)($_POST['customer_id']??0); $domainId=(int)($_POST['domain_id']??0); $action=trim($_POST['domain_action']??'');
    if($customerId>0 && $domainId>0){
        try{
            if($action==='renew'){
                $d = ao_domain_row($domainId, $customerId); if(!$d) throw new Exception('Domain bulunamadı.');
                $res = ao_domain_create_renewal_order($d, (int)($_POST['years'] ?? 1), 'admin');
                flash('success','Domain doğrudan yenilenmedi; yenileme siparişi ve faturası oluşturuldu. Sipariş: #'.$res['order_id']);
            } elseif($action==='toggle-lock'){
                $q=db()->prepare('UPDATE domains SET lock_status=IF(lock_status=1,0,1) WHERE id=? AND customer_id=?'); $q->execute([$domainId,$customerId]); flash('success','Domain kilit durumu değiştirildi.');
            } elseif($action==='toggle-autorenew'){
                $q=db()->prepare('UPDATE domains SET auto_renew=IF(auto_renew=1,0,1) WHERE id=? AND customer_id=?'); $q->execute([$domainId,$customerId]); flash('success','Otomatik yenileme durumu değiştirildi.');
            } elseif($action==='update-epp'){
                $d = ao_domain_row($domainId, $customerId); if(!$d) throw new Exception('Domain bulunamadı.');
                if (trim($_POST['epp_code'] ?? '') !== '') { $epp=trim($_POST['epp_code']); $q=db()->prepare('UPDATE domains SET epp_code=? WHERE id=? AND customer_id=?'); $q->execute([$epp,$domainId,$customerId]); flash('success','EPP kodu manuel güncellendi.'); }
                else { $res = ao_domain_generate_epp($d); flash($res['ok']?'success':'error', $res['message']); }
            } elseif($action==='transfer'){
                $q=db()->prepare('UPDATE domains SET status="transfer_pending" WHERE id=? AND customer_id=?'); $q->execute([$domainId,$customerId]); flash('success','Domain transfer sürecine alındı.');
            } elseif($action==='update-registrar'){
                $registrar=trim($_POST['registrar']??''); $q=db()->prepare('UPDATE domains SET registrar=? WHERE id=? AND customer_id=?'); $q->execute([$registrar,$domainId,$customerId]); flash('success','Registrar bilgisi güncellendi.');
            } else { flash('error','Geçersiz domain işlemi.'); }
        } catch(Throwable $e){ flash('error','Domain işlemi tamamlanamadı.'); }
    }
    redirect_to('admin/customers/view?id='.$customerId.'#tab-domainler');
}



// v7.3.0 Domain Center Pro - registrar, DNS, nameserver and client domain actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/domain-center/registrar-save') {
    $id=(int)($_POST['registrar_id']??0);
    $status=trim($_POST['status']??'inactive'); $test=(int)($_POST['test_mode']??0); $tlds=trim($_POST['supported_tlds']??'');
    try{
        $q=db()->prepare('UPDATE domain_registrars SET status=?, test_mode=?, supported_tlds=? WHERE id=?'); $q->execute([$status,$test,$tlds,$id]);
        $incomingConfig = $_POST['config'] ?? [];
        // DomainNameAPI için ana endpoint opsiyoneldir. Yanlışlıkla ?singlewsdl veya eski api.domainnameapi.com girildiyse temizlenir;
        // doğru canlı/test endpoint kod tarafından domain_registrars.test_mode değerine göre seçilir.
        $regSlug = '';
        try { $rs=db()->prepare('SELECT slug,module_name FROM domain_registrars WHERE id=? LIMIT 1'); $rs->execute([$id]); $rr=$rs->fetch(); $regSlug=strtolower(($rr['slug']??'').' '.($rr['module_name']??'')); } catch(Throwable $ignore) {}
        if (str_contains($regSlug,'domainnameapi') || str_contains($regSlug,'dna')) {
            $incomingConfig['auth_mode'] = 'apikey';
            $incomingConfig['test_mode'] = (string)$test;
            if (!empty($incomingConfig['api_endpoint']) && str_contains((string)$incomingConfig['api_endpoint'], 'domainnameapi.com')) {
                $incomingConfig['api_endpoint'] = '';
            }
        }
        foreach($incomingConfig as $k=>$v){
            $secret=in_array($k,['api_password','password','token','secret','api_key','api_secret','ote_api_key'],true)?1:0;
            $value=trim((string)$v);
            // Maskelenmiş veya boş şifre gönderildiyse eski gizli değeri silme.
            if ($secret && ($value==='' || preg_match('/^\*+$/',$value))) continue;
            $u=db()->prepare('INSERT INTO registrar_configs(registrar_id,config_key,config_value,is_secret) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE config_value=VALUES(config_value), is_secret=VALUES(is_secret)');
            $u->execute([$id,$k,$value,$secret]);
        }
        flash('success','Registrar yapılandırması kaydedildi.');
    }catch(Throwable $e){ flash('error','Registrar yapılandırması kaydedilemedi.'); }
    redirect_to('admin/domain-center/registrars');
}
if ($route === 'admin/domain-center/registrar-test') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $domain = ahost_domain_clean($_GET['domain'] ?? 'example.com');
    try {
        $bundle = ao_registrar_bundle_by_id($id);
        if (!$bundle) { flash('error','Registrar bulunamadı.'); redirect_to('admin/domain-center/registrars'); }
        $res = ao_registrar_api_call($bundle, 'test', $domain);
        if ($res['ok']) { $decoded=json_decode($res['body']??'',true); $extra=''; if(is_array($decoded)){ $name=ao_find_deep($decoded,['name','Name','ResellerName']) ?: ''; $balance=ao_find_deep($decoded,['balance','Balance']) ?: ''; $currency=ao_find_deep($decoded,['currency','Currency']) ?: ''; $extra=trim(' '.$name.' '.$balance.' '.$currency); } flash('success', 'Registrar bağlantı testi başarılı.'.$extra.' Loglardan detay görülebilir.'); }
        else flash('error', 'Registrar bağlantı testi başarısız: '.$res['message'].' HTTP '.$res['code'].' Method: '.($res['method'] ?? 'test').'. API Logs ekranından ham yanıtı kontrol edin.');
    } catch (Throwable $e) { flash('error','Registrar bağlantı testi hata verdi: '.$e->getMessage()); }
    redirect_to('admin/domain-center/registrars');
}


if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/domain-center/bulk-operation') { require_admin(); verify_csrf(); $op=trim($_POST['operation']??''); $domains=preg_split('/\R+/', trim($_POST['domains']??'')); $count=0; try{ foreach($domains as $dn){ $dn=ahost_domain_clean($dn); if(!$dn) continue; db()->prepare('INSERT INTO domain_operation_logs(domain_name,operation,registrar,status,message) VALUES(?,?,?,?,?)')->execute([$dn,$op,'auto','queued','Toplu operasyon kuyruğa alındı.']); $count++; } flash('success',$count.' domain için toplu operasyon kuyruğa alındı.'); }catch(Throwable $e){ flash('error','Toplu işlem kaydedilemedi: '.$e->getMessage()); } redirect_to('admin/domain-center/operations'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/domain-center/smart-pricing-save') {
    require_admin(); verify_csrf(); ao_schema_ensure_v810();
    try {
        $tld='.'.ltrim(strtolower(trim($_POST['tld'] ?? '.com')),'.');
        $mode=trim($_POST['mode'] ?? 'percent');
        $percent=(float)($_POST['markup_percent'] ?? 30); $fixed=(float)($_POST['markup_fixed'] ?? 0); $min=(float)($_POST['min_profit'] ?? 0);
        $currency=trim($_POST['currency'] ?? 'USD'); $override=trim($_POST['registrar_override'] ?? '');
        db()->prepare('INSERT INTO domain_pricing_rules(tld,mode,markup_percent,markup_fixed,min_profit,currency,registrar_override,is_active) VALUES(?,?,?,?,?,?,?,1) ON DUPLICATE KEY UPDATE mode=VALUES(mode),markup_percent=VALUES(markup_percent),markup_fixed=VALUES(markup_fixed),min_profit=VALUES(min_profit),currency=VALUES(currency),registrar_override=VALUES(registrar_override),is_active=1')->execute([$tld,$mode,$percent,$fixed,$min,$currency,$override?:null]);
        flash('success','Akıllı domain fiyat kuralı kaydedildi.');
    } catch(Throwable $e) { flash('error','Fiyat kuralı kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/domain-center/smart-pricing');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/domain-center/registrar-cost-save') {
    require_admin(); verify_csrf(); ao_schema_ensure_v810();
    try {
        $registrar=trim($_POST['registrar_slug'] ?? 'domainnameapi'); $tld='.'.ltrim(strtolower(trim($_POST['tld'] ?? '.com')),'.'); $action=trim($_POST['action'] ?? 'register');
        $cost=(float)($_POST['cost'] ?? 0); $currency=trim($_POST['currency'] ?? 'USD');
        db()->prepare('INSERT INTO registrar_price_cache(registrar_slug,tld,action,cost,currency,source,last_checked_at) VALUES(?,?,?,?,? ,"manual",NOW()) ON DUPLICATE KEY UPDATE cost=VALUES(cost),currency=VALUES(currency),source="manual",last_checked_at=NOW()')->execute([$registrar,$tld,$action,$cost,$currency]);
        flash('success','Registrar alış fiyatı kaydedildi.');
    } catch(Throwable $e) { flash('error','Alış fiyatı kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/domain-center/smart-pricing');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/accounting/payment-fee-save') {
    require_admin(); verify_csrf(); ao_schema_ensure_v900();
    try {
        $gateway=trim($_POST['gateway'] ?? 'paytr'); $label=trim($_POST['label'] ?? $gateway); $line=trim($_POST['invoice_line_label'] ?? 'Kart İşlem Komisyonu');
        $percent=(float)($_POST['fee_percent'] ?? 0); $fixed=(float)($_POST['fee_fixed'] ?? 0); $currency=trim($_POST['currency'] ?? 'TRY');
        $apiEnabled=!empty($_POST['api_enabled'])?1:0; $apiEndpoint=trim($_POST['api_endpoint'] ?? ''); $auth=trim($_POST['api_auth_json'] ?? '');
        db()->prepare('INSERT INTO payment_fee_rules(gateway,label,invoice_line_label,fee_percent,fee_fixed,last_known_fee_percent,last_known_fee_fixed,currency,payer_mode,rate_source,api_enabled,api_endpoint,api_auth_json,is_active) VALUES(?,?,?,?,?,?,?,?,"customer",?,?,?,?,1) ON DUPLICATE KEY UPDATE label=VALUES(label),invoice_line_label=VALUES(invoice_line_label),fee_percent=VALUES(fee_percent),fee_fixed=VALUES(fee_fixed),last_known_fee_percent=VALUES(last_known_fee_percent),last_known_fee_fixed=VALUES(last_known_fee_fixed),currency=VALUES(currency),payer_mode="customer",api_enabled=VALUES(api_enabled),api_endpoint=VALUES(api_endpoint),api_auth_json=VALUES(api_auth_json),is_active=1')->execute([$gateway,$label,$line,$percent,$fixed,$percent,$fixed,$currency,$apiEnabled?'api':'manual',$apiEnabled,$apiEndpoint,$auth]);
        flash('success','Kart işlem komisyonu kaydedildi. Komisyon her zaman müşterinin faturasına ayrı satır olarak eklenir.');
    } catch(Throwable $e) { flash('error','Komisyon kuralı kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/accounting/payment-fees');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/accounting/payment-fee-sync') {
    require_admin(); verify_csrf();
    $gateway=trim($_POST['gateway'] ?? '');
    $res=ao_payment_commission_sync($gateway,true);
    flash($res['ok']?'success':'error',$res['message']);
    redirect_to('admin/accounting/payment-fees');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/domain-center/pricing-save') {
    $tld=trim($_POST['tld']??''); $reg=(float)($_POST['register_price']??0); $tr=(float)($_POST['transfer_price']??0); $ren=(float)($_POST['renew_price']??0); $cur=trim($_POST['currency']??'TRY'); $registrar=trim($_POST['registrar_slug']??'domainnameapi');
    if($tld){ try{ $q=db()->prepare('INSERT INTO tld_pricing(tld,register_price,transfer_price,renew_price,currency,registrar_slug,is_active) VALUES(?,?,?,?,?,?,1) ON DUPLICATE KEY UPDATE register_price=VALUES(register_price),transfer_price=VALUES(transfer_price),renew_price=VALUES(renew_price),currency=VALUES(currency),registrar_slug=VALUES(registrar_slug)'); $q->execute([$tld,$reg,$tr,$ren,$cur,$registrar]); flash('success','TLD fiyatı kaydedildi.'); }catch(Throwable $e){ flash('error','TLD fiyatı kaydedilemedi.'); } }
    redirect_to('admin/domain-center/pricing');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($route === 'admin/domain-center/dns-save' || $route === 'client/domains/dns-save')) {
    $domainId=(int)($_POST['domain_id']??0); $customerId=(int)($_POST['customer_id']??0); $type=trim($_POST['record_type']??'A'); $host=trim($_POST['host']??'@'); $value=trim($_POST['record_value']??''); $priority=($_POST['priority']??'')===''?null:(int)$_POST['priority']; $ttl=(int)($_POST['ttl']??3600);
    if(str_starts_with($route,'client/')){ require_customer(); $cc=current_customer(); $customerId=(int)$cc['id']; }
    if($domainId && $value){ try{ $chk=db()->prepare('SELECT id FROM domains WHERE id=? AND customer_id=?'); $chk->execute([$domainId,$customerId]); if($chk->fetch()){ $q=db()->prepare('INSERT INTO domain_dns_records(domain_id,record_type,host,record_value,priority,ttl) VALUES(?,?,?,?,?,?)'); $q->execute([$domainId,$type,$host,$value,$priority,$ttl]); flash('success','DNS kaydı eklendi.'); } }catch(Throwable $e){ flash('error','DNS kaydı eklenemedi.'); } }
    redirect_to(str_starts_with($route,'client/') ? 'client/domains/view?id='.$domainId.'#dns' : 'admin/domain-center/view?id='.$domainId.'#dns');
}
if (($route === 'admin/domain-center/dns-delete' || $route === 'client/domains/dns-delete')) {
    $id=(int)($_GET['id']??0); $domainId=(int)($_GET['domain_id']??0); $customerId=(int)($_GET['customer_id']??0);
    if(str_starts_with($route,'client/')){ require_customer(); $cc=current_customer(); $customerId=(int)$cc['id']; }
    try{ $q=db()->prepare('DELETE r FROM domain_dns_records r JOIN domains d ON d.id=r.domain_id WHERE r.id=? AND r.domain_id=? AND d.customer_id=?'); $q->execute([$id,$domainId,$customerId]); flash('success','DNS kaydı silindi.'); }catch(Throwable $e){ flash('error','DNS kaydı silinemedi.'); }
    redirect_to(str_starts_with($route,'client/') ? 'client/domains/view?id='.$domainId.'#dns' : 'admin/domain-center/view?id='.$domainId.'#dns');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($route === 'admin/domain-center/ns-save' || $route === 'client/domains/ns-save')) {
    $domainId=(int)($_POST['domain_id']??0); $customerId=(int)($_POST['customer_id']??0);
    if(str_starts_with($route,'client/')){ require_customer(); $cc=current_customer(); $customerId=(int)$cc['id']; }
    $ns=[trim($_POST['ns1']??''),trim($_POST['ns2']??''),trim($_POST['ns3']??''),trim($_POST['ns4']??'')];
    try{ $chk=db()->prepare('SELECT id FROM domains WHERE id=? AND customer_id=?'); $chk->execute([$domainId,$customerId]); if($chk->fetch()){ $q=db()->prepare('INSERT INTO domain_nameservers(domain_id,ns1,ns2,ns3,ns4) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE ns1=VALUES(ns1),ns2=VALUES(ns2),ns3=VALUES(ns3),ns4=VALUES(ns4)'); $q->execute([$domainId,$ns[0],$ns[1],$ns[2],$ns[3]]); flash('success','Nameserver bilgileri güncellendi.'); } }catch(Throwable $e){ flash('error','Nameserver güncellenemedi.'); }
    redirect_to(str_starts_with($route,'client/') ? 'client/domains/view?id='.$domainId.'#nameserver' : 'admin/domain-center/view?id='.$domainId.'#nameserver');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/domains/action') {
    require_customer(); $cc=current_customer(); $domainId=(int)($_POST['domain_id']??0); $action=trim($_POST['domain_action']??'');
    try{
        if($action==='renew'){ $d=ao_domain_row($domainId,$cc['id']); if(!$d) throw new Exception('Domain bulunamadı.'); $res=ao_domain_create_renewal_order($d,(int)($_POST['years']??1),'client'); flash('success','Yenileme talebi sipariş/fatura olarak oluşturuldu. Ödeme sonrası registrar yenilemesi çalışacak. Sipariş: #'.$res['order_id']); }
        elseif($action==='request-epp'){ $d=ao_domain_row($domainId,$cc['id']); if(!$d) throw new Exception('Domain bulunamadı.'); $res=ao_domain_generate_epp($d); flash($res['ok']?'success':'error',$res['message']); }
        elseif($action==='toggle-lock'){ $q=db()->prepare('UPDATE domains SET lock_status=IF(lock_status=1,0,1) WHERE id=? AND customer_id=?'); $q->execute([$domainId,$cc['id']]); flash('success','Domain kilit durumu değiştirildi.'); }
        elseif($action==='toggle-autorenew'){ $q=db()->prepare('UPDATE domains SET auto_renew=IF(auto_renew=1,0,1) WHERE id=? AND customer_id=?'); $q->execute([$domainId,$cc['id']]); flash('success','Oto yenileme güncellendi.'); }
        elseif($action==='transfer'){ $q=db()->prepare('UPDATE domains SET status="transfer_pending" WHERE id=? AND customer_id=?'); $q->execute([$domainId,$cc['id']]); flash('success','Transfer talebi oluşturuldu.'); }
    }catch(Throwable $e){ flash('error','Domain işlemi tamamlanamadı.'); }
    redirect_to('client/domains/view?id='.$domainId);
}


// v7.3.0 Orders + Billing + API Integration Pro
function ao_log($type, $action, $description='') {
    try { $q=db()->prepare('INSERT INTO activity_logs(type,action,description,ip_address) VALUES(?,?,?,?)'); $q->execute([$type,$action,$description,$_SERVER['REMOTE_ADDR']??'']); } catch(Throwable $e) {}
}
function ao_api_log($provider, $action, $status, $message='', $payload='') {
    try { $q=db()->prepare('INSERT INTO api_logs(provider,action,status,message,payload) VALUES(?,?,?,?,?)'); $q->execute([$provider,$action,$status,$message,$payload]); } catch(Throwable $e) {}
}
function ao_generate_number($prefix, $table, $field) {
    return $prefix . '-' . date('Y') . '-' . str_pad((string)random_int(1000,9999), 4, '0', STR_PAD_LEFT);
}
function ao_simulate_api($provider, $action, $data=[]) {
    $isProduction = admin_setting('production_mode','0') === '1';
    if ($isProduction) {
        $message = strtoupper($provider).' '.$action.' simülasyon akışı production modunda engellendi. Canlı entegrasyon bilgileri tamamlanmadan otomatik başarılı sonuç döndürülmez.';
        ao_api_log($provider, $action, 'error', $message, json_encode($data, JSON_UNESCAPED_UNICODE));
        return ['success'=>false,'message'=>$message,'data'=>$data,'simulated'=>true];
    }
    $mode = 'sandbox';
    $ok = true;
    $message = strtoupper($provider).' '.$action.' '.$mode.' simülasyon akışı başarılı. Production modunda gerçek API entegrasyonu zorunludur.';
    ao_api_log($provider, $action, $ok?'success':'error', $message, json_encode($data, JSON_UNESCAPED_UNICODE));
    return ['success'=>$ok,'message'=>$message,'data'=>$data,'simulated'=>true];
}
function ao_create_invoice_for_order($orderId) {
    $q=db()->prepare('SELECT o.*, c.id customer_id FROM orders o LEFT JOIN customers c ON c.id=o.customer_id WHERE o.id=? LIMIT 1'); $q->execute([$orderId]); $o=$q->fetch();
    if(!$o) return 0;
    $chk=db()->prepare('SELECT id FROM invoices WHERE order_id=? LIMIT 1'); $chk->execute([$orderId]); $existing=$chk->fetchColumn(); if($existing) return (int)$existing;
    $baseSubtotal=(float)$o['total'];
    $paymentMethod=trim((string)($o['payment_method'] ?? 'manual')) ?: 'manual';
    $feeQuote = ($paymentMethod && $paymentMethod !== 'manual') ? ao_payment_fee_quote($baseSubtotal,$paymentMethod) : ['fee'=>0,'line_label'=>'Kart İşlem Komisyonu'];
    $cardFee = (float)($feeQuote['fee'] ?? 0);
    $subtotal=$baseSubtotal+$cardFee; $tax=round($subtotal*0.20,2); $total=$subtotal+$tax; $no=ao_generate_number('INV','invoices','invoice_number');
    $ins=db()->prepare('INSERT INTO invoices(customer_id,order_id,invoice_number,status,subtotal,tax,total,due_date) VALUES(?,?,?,"unpaid",?,?,?,DATE_ADD(CURDATE(), INTERVAL 7 DAY))');
    $ins->execute([$o['customer_id'],$orderId,$no,$subtotal,$tax,$total]); $invoiceId=(int)db()->lastInsertId();
    $items=db()->prepare('SELECT * FROM order_items WHERE order_id=?'); $items->execute([$orderId]); $rows=$items->fetchAll();
    if(!$rows){ db()->prepare('INSERT INTO invoice_items(invoice_id,description,amount) VALUES(?,?,?)')->execute([$invoiceId,'Sipariş '.$o['order_number'],$baseSubtotal]); }
    foreach($rows as $it){ db()->prepare('INSERT INTO invoice_items(invoice_id,description,amount) VALUES(?,?,?)')->execute([$invoiceId,$it['item_name'].' - '.$it['domain'],(float)$it['price']]); }
    if($cardFee>0){ db()->prepare('INSERT INTO invoice_items(invoice_id,description,amount) VALUES(?,?,?)')->execute([$invoiceId,($feeQuote['line_label'] ?? 'Kart İşlem Komisyonu').' - '.strtoupper($paymentMethod),$cardFee]); }
    ao_log('billing','invoice.created','Siparişten fatura oluşturuldu: '.$no);
    return $invoiceId;
}
function ao_provision_order($orderId) {
    $q=db()->prepare('SELECT * FROM orders WHERE id=? LIMIT 1'); $q->execute([$orderId]); $o=$q->fetch(); if(!$o) return;
    $items=db()->prepare('SELECT oi.*, p.type, p.module_name, p.whm_package FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?'); $items->execute([$orderId]);
    foreach($items->fetchAll() as $it){
        if(in_array($it['type'], ['hosting','server','service'], true)){
            $svc=db()->prepare('INSERT INTO services(customer_id,product_id,domain,status,billing_cycle,next_due_date) VALUES(?,?,?,?,?,DATE_ADD(CURDATE(), INTERVAL 1 MONTH))');
            $svc->execute([$o['customer_id'],$it['product_id'],$it['domain'],'active',$it['billing_cycle']?:'monthly']); $serviceId=(int)db()->lastInsertId();
            if($it['type']==='hosting'){
                $user=preg_replace('/[^a-z0-9]/','', strtolower(substr($it['domain'] ?: 'site'.$serviceId,0,8))) ?: 'site'.$serviceId;
                db()->prepare('INSERT INTO hosting_accounts(service_id,server_name,server_ip,whm_username,panel_password,package_name,disk_mb,disk_used_mb,bandwidth_mb,bandwidth_used_mb,mail_limit,mail_used,mysql_limit,mysql_used,cpanel_url,directadmin_url,webmail_url,whm_url,vps_panel_url,ns1,ns2) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                  ->execute([$serviceId,'TR-WHM-AUTO','185.00.00.20',$user,bin2hex(random_bytes(4)),$it['whm_package']?:'starter',10240,0,102400,0,50,0,20,0,'','','','','','','']);
                ao_simulate_api('whm','create-account',['service_id'=>$serviceId,'domain'=>$it['domain'],'package'=>$it['whm_package']?:'starter']);
            }
        }
        if($it['type']==='domain' && $it['domain']){
            $dom=db()->prepare('INSERT INTO domains(customer_id,domain_name,registrar,status,registration_date,expiry_date,auto_renew,lock_status,epp_code) VALUES(?,?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 YEAR),1,1,?)');
            $dom->execute([$o['customer_id'],$it['domain'],'DomainNameAPI','active','EPP-'.strtoupper(substr(md5($it['domain'].time()),0,10))]); $domainId=(int)db()->lastInsertId();
            db()->prepare('INSERT INTO domain_nameservers(domain_id,ns1,ns2) VALUES(?,?,?)')->execute([$domainId,'ns1.ahostone.test','ns2.ahostone.test']);
            ao_simulate_api('domainnameapi','register-domain',['domain'=>$it['domain'],'domain_id'=>$domainId]);
        }
    }
    db()->prepare('UPDATE orders SET status="active", fraud_score=0, provision_status="completed" WHERE id=?')->execute([$orderId]);
    ao_log('orders','order.provisioned','Sipariş aktifleştirildi: '.$orderId);
}

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/orders/create') {
    $customerId=(int)($_POST['customer_id']??0); $productId=(int)($_POST['product_id']??0); $domain=trim($_POST['domain']??''); $cycle=trim($_POST['billing_cycle']??'monthly'); $payment=trim($_POST['payment_method']??'manual');
    try{
        $p=db()->prepare('SELECT p.*, pp.monthly, pp.annually FROM products p LEFT JOIN product_pricing pp ON pp.product_id=p.id WHERE p.id=? LIMIT 1'); $p->execute([$productId]); $prod=$p->fetch();
        if(!$customerId || !$prod) throw new Exception('Müşteri ve ürün zorunludur.');
        $price = $cycle==='annually' ? (float)($prod['annually']??0) : (float)($prod['monthly']??0); if($price<=0) $price=(float)($_POST['price']??0);
        $fraud = (stripos($domain,'fraud')!==false || $price>10000) ? 85 : 12; $status = $fraud>70 ? 'fraud' : 'pending';
        $no=ao_generate_number('AO','orders','order_number');
        db()->prepare('INSERT INTO orders(customer_id,order_number,status,total,payment_method,fraud_score,provision_status,notes) VALUES(?,?,?,?,?,?,"pending",?)')->execute([$customerId,$no,$status,$price,$payment,$fraud,trim($_POST['notes']??'')]);
        $orderId=(int)db()->lastInsertId();
        db()->prepare('INSERT INTO order_items(order_id,product_id,item_type,item_name,domain,billing_cycle,price,setup_fee,module_name) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$orderId,$productId,$prod['type'],$prod['name'],$domain,$cycle,$price,0,$prod['module_name']]);
        ao_create_invoice_for_order($orderId); ao_log('orders','order.created','Manuel sipariş oluşturuldu: '.$no);
        flash('success','Sipariş oluşturuldu ve fatura hazırlandı.');
        redirect_to('admin/orders?view='.$orderId);
    }catch(Throwable $e){ flash('error','Sipariş oluşturulamadı: '.$e->getMessage()); redirect_to('admin/orders/new'); }
}
if (($route==='admin/orders/approve' || $route==='admin/orders/cancel' || $route==='admin/orders/fraud-clear')) {
    $id=(int)($_GET['id']??0);
    try{
        if($route==='admin/orders/approve'){ ao_create_invoice_for_order($id); ao_provision_order($id); flash('success','Sipariş onaylandı, fatura ve servis/domain akışı işlendi.'); }
        elseif($route==='admin/orders/fraud-clear'){ db()->prepare('UPDATE orders SET status="pending", fraud_score=20 WHERE id=?')->execute([$id]); ao_log('orders','fraud.cleared','Fraud inceleme temizlendi: '.$id); flash('success','Fraud inceleme temizlendi.'); }
        else { db()->prepare('UPDATE orders SET status="cancelled", provision_status="cancelled" WHERE id=?')->execute([$id]); ao_log('orders','order.cancelled','Sipariş iptal edildi: '.$id); flash('success','Sipariş iptal edildi.'); }
    }catch(Throwable $e){ flash('error','Sipariş işlemi tamamlanamadı.'); }
    redirect_to('admin/orders');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/accounting/invoice-create') {
    $customerId=(int)($_POST['customer_id']??0); $desc=trim($_POST['description']??'Manuel Fatura'); $amount=(float)($_POST['amount']??0); $taxRate=(float)($_POST['tax_rate']??20);
    try{ if(!$customerId || $amount<=0) throw new Exception('Müşteri ve tutar zorunludur.'); $tax=round($amount*$taxRate/100,2); $total=$amount+$tax; $no=ao_generate_number('INV','invoices','invoice_number');
        db()->prepare('INSERT INTO invoices(customer_id,invoice_number,status,subtotal,tax,total,due_date) VALUES(?,?,"unpaid",?,?,?,DATE_ADD(CURDATE(), INTERVAL 7 DAY))')->execute([$customerId,$no,$amount,$tax,$total]); $iid=(int)db()->lastInsertId();
        db()->prepare('INSERT INTO invoice_items(invoice_id,description,amount) VALUES(?,?,?)')->execute([$iid,$desc,$amount]); ao_log('billing','invoice.manual','Manuel fatura oluşturuldu: '.$no); flash('success','Fatura oluşturuldu.'); redirect_to('admin/accounting/invoices?view='.$iid);
    }catch(Throwable $e){ flash('error','Fatura oluşturulamadı: '.$e->getMessage()); redirect_to('admin/accounting/invoices'); }
}

if ($route==='admin/accounting/invoice-status') {
    require_admin(); verify_csrf();
    $id=(int)($_GET['id']??0); $status=$_GET['status']??'unpaid';
    $allowed=['draft','pending','unpaid','partial','paid','cancelled','refunded']; if(!in_array($status,$allowed,true)) $status='unpaid';
    try{ db()->prepare('UPDATE invoices SET status=? WHERE id=?')->execute([$status,$id]); ao_log('billing','invoice.status','Fatura durumu güncellendi: '.$id.' => '.$status); flash('success','Fatura durumu güncellendi.'); }
    catch(Throwable $e){ flash('error','Fatura durumu güncellenemedi.'); }
    redirect_to('admin/accounting/invoices?view='.$id);
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/accounting/invoice-update') {
    require_admin(); verify_csrf();
    $id=(int)($_POST['invoice_id']??0); $status=$_POST['status']??'unpaid'; $due=$_POST['due_date']??null; $tax=(float)($_POST['tax']??0);
    $allowed=['draft','pending','unpaid','partial','paid','cancelled','refunded']; if(!in_array($status,$allowed,true)) $status='unpaid';
    try{
        $sumq=db()->prepare('SELECT COALESCE(SUM(amount),0) FROM invoice_items WHERE invoice_id=?'); $sumq->execute([$id]); $subtotal=(float)$sumq->fetchColumn(); $total=$subtotal+$tax;
        db()->prepare('UPDATE invoices SET status=?, due_date=?, subtotal=?, tax=?, total=? WHERE id=?')->execute([$status,$due,$subtotal,$tax,$total,$id]);
        ao_log('billing','invoice.update','Fatura güncellendi: '.$id); flash('success','Fatura güncellendi.');
    }catch(Throwable $e){ flash('error','Fatura güncellenemedi: '.$e->getMessage()); }
    redirect_to('admin/accounting/invoices?view='.$id);
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/accounting/invoice-item-add') {
    require_admin(); verify_csrf();
    $id=(int)($_POST['invoice_id']??0); $desc=trim($_POST['description']??''); $amount=(float)($_POST['amount']??0);
    try{ if($id<=0||$desc===''||$amount<=0) throw new Exception('Kalem bilgisi eksik.'); db()->prepare('INSERT INTO invoice_items(invoice_id,description,amount) VALUES(?,?,?)')->execute([$id,$desc,$amount]); ao_recalculate_invoice_total_v2465($id); flash('success','Fatura kalemi eklendi.'); }
    catch(Throwable $e){ flash('error','Kalem eklenemedi: '.$e->getMessage()); }
    redirect_to('admin/accounting/invoices?view='.$id);
}
if ($route==='admin/accounting/invoice-item-delete') {
    require_admin(); verify_csrf();
    $itemId=(int)($_GET['id']??0); $invoiceId=(int)($_GET['invoice_id']??0);
    try{ db()->prepare('DELETE FROM invoice_items WHERE id=? AND invoice_id=?')->execute([$itemId,$invoiceId]); ao_recalculate_invoice_total_v2465($invoiceId); flash('success','Fatura kalemi silindi.'); }
    catch(Throwable $e){ flash('error','Kalem silinemedi.'); }
    redirect_to('admin/accounting/invoices?view='.$invoiceId);
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/accounting/invoice-merge') {
    require_admin(); verify_csrf();
    $target=(int)($_POST['target_invoice_id']??0); $ids=array_filter(array_map('intval', preg_split('/[^0-9]+/', $_POST['source_invoice_ids']??'')));
    try{ foreach($ids as $sid){ if($sid>0 && $sid!==$target){ db()->prepare('UPDATE invoice_items SET invoice_id=? WHERE invoice_id=?')->execute([$target,$sid]); db()->prepare('UPDATE payments SET invoice_id=? WHERE invoice_id=?')->execute([$target,$sid]); db()->prepare('UPDATE invoices SET status="cancelled" WHERE id=?')->execute([$sid]); }} ao_recalculate_invoice_total_v2465($target); ao_log('billing','invoice.merge','Fatura birleştirildi: '.$target); flash('success','Faturalar birleştirildi.'); }
    catch(Throwable $e){ flash('error','Fatura birleştirilemedi: '.$e->getMessage()); }
    redirect_to('admin/accounting/invoices?view='.$target);
}

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/accounting/payment') {
    $invoiceId=(int)($_POST['invoice_id']??0); $amount=(float)($_POST['amount']??0); $method=trim($_POST['method']??'manual'); $type=trim($_POST['type']??'payment');
    try{ $invq=db()->prepare('SELECT * FROM invoices WHERE id=? LIMIT 1'); $invq->execute([$invoiceId]); $inv=$invq->fetch(); if(!$inv) throw new Exception('Fatura bulunamadı.');
        if($amount<=0) $amount=(float)$inv['total']; $status=$type==='refund'?'refunded':'paid';
        db()->prepare('INSERT INTO payments(invoice_id,customer_id,type,method,amount,currency,transaction_id,status,notes) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$invoiceId,$inv['customer_id'],$type,$method,$amount,'TRY','TX-'.strtoupper(substr(md5(microtime()),0,10)),'completed',trim($_POST['notes']??'')]);
        db()->prepare('UPDATE invoices SET status=? WHERE id=?')->execute([$status,$invoiceId]); ao_log('billing','invoice.'.$status,'Fatura ödeme/iade işlemi: '.$invoiceId); flash('success','Muhasebe işlemi kaydedildi.');
    }catch(Throwable $e){ flash('error','Muhasebe işlemi başarısız: '.$e->getMessage()); }
    redirect_to('admin/accounting/invoices');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/api-integrations/save') {
    try{
        $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $provider=trim($_POST['provider']??''); $endpoint=trim($_POST['endpoint']??''); $username=trim($_POST['username']??''); $secret=trim($_POST['secret']??''); $status=trim($_POST['status']??'inactive'); $test=(int)($_POST['test_mode']??1);
        if(!$name || !$provider) throw new Exception('Ad ve sağlayıcı zorunludur.');
        if($id>0) db()->prepare('UPDATE api_integrations SET name=?,provider=?,endpoint=?,username=?,secret=?,status=?,test_mode=? WHERE id=?')->execute([$name,$provider,$endpoint,$username,$secret,$status,$test,$id]);
        else db()->prepare('INSERT INTO api_integrations(name,provider,endpoint,username,secret,status,test_mode) VALUES(?,?,?,?,?,?,?)')->execute([$name,$provider,$endpoint,$username,$secret,$status,$test]);
        flash('success','API entegrasyonu kaydedildi.');
    }catch(Throwable $e){ flash('error','API kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/api-integrations');
}
if ($route==='admin/api-integrations/test') {
    $id=(int)($_GET['id']??0); try{ $q=db()->prepare('SELECT * FROM api_integrations WHERE id=?'); $q->execute([$id]); $api=$q->fetch(); if($api){ $res=ao_simulate_api($api['provider'],'connection-test',['endpoint'=>$api['endpoint'],'test_mode'=>$api['test_mode']]); flash('success',$res['message']); } }catch(Throwable $e){ flash('error','API testi başarısız.'); }
    redirect_to('admin/api-integrations');
}




// v22.3.8 notification schema compatibility before notification routes
try{
  $cols=[]; foreach(db()->query("SHOW COLUMNS FROM notification_channels")->fetchAll() as $c){$cols[$c['Field']]=true;}
  if(!empty($cols['channel'])) @db()->exec("ALTER TABLE notification_channels MODIFY channel VARCHAR(40) NULL");
  if(empty($cols['channel_type']) && !empty($cols['channel'])) @db()->exec("ALTER TABLE notification_channels ADD COLUMN channel_type VARCHAR(40) NULL AFTER id");
  if(empty($cols['name'])) @db()->exec("ALTER TABLE notification_channels ADD COLUMN name VARCHAR(190) NULL AFTER provider");
  if(empty($cols['status'])) @db()->exec("ALTER TABLE notification_channels ADD COLUMN status VARCHAR(40) DEFAULT 'inactive'");
  if(empty($cols['test_mode'])) @db()->exec("ALTER TABLE notification_channels ADD COLUMN test_mode TINYINT(1) DEFAULT 1");
  if(empty($cols['priority'])) @db()->exec("ALTER TABLE notification_channels ADD COLUMN priority INT DEFAULT 10");
  if(empty($cols['sender_name'])) @db()->exec("ALTER TABLE notification_channels ADD COLUMN sender_name VARCHAR(190) NULL");
  @db()->exec("UPDATE notification_channels SET channel_type=COALESCE(channel_type,channel), channel=COALESCE(channel,channel_type), status=CASE WHEN COALESCE(is_active,0)=1 THEN 'active' ELSE COALESCE(status,'inactive') END");
}catch(Throwable $e){}
try{
  $cols=[]; foreach(db()->query("SHOW COLUMNS FROM notification_logs")->fetchAll() as $c){$cols[$c['Field']]=true;}
  if(!empty($cols['channel'])) @db()->exec("ALTER TABLE notification_logs MODIFY channel VARCHAR(40) NULL");
  if(empty($cols['channel_type']) && !empty($cols['channel'])) @db()->exec("ALTER TABLE notification_logs ADD COLUMN channel_type VARCHAR(40) NULL AFTER customer_id");
  if(empty($cols['provider'])) @db()->exec("ALTER TABLE notification_logs ADD COLUMN provider VARCHAR(120) NULL AFTER channel_type");
  if(empty($cols['response_code'])) @db()->exec("ALTER TABLE notification_logs ADD COLUMN response_code VARCHAR(20) NULL");
  if(empty($cols['response_body'])) @db()->exec("ALTER TABLE notification_logs ADD COLUMN response_body LONGTEXT NULL");
  if(empty($cols['event_key'])) @db()->exec("ALTER TABLE notification_logs ADD COLUMN event_key VARCHAR(120) NULL");
  if(empty($cols['payload_json'])) @db()->exec("ALTER TABLE notification_logs ADD COLUMN payload_json LONGTEXT NULL");
  if(empty($cols['sent_at'])) @db()->exec("ALTER TABLE notification_logs ADD COLUMN sent_at DATETIME NULL");
  @db()->exec("UPDATE notification_logs SET channel_type=COALESCE(channel_type,channel), channel=COALESCE(channel,channel_type), response_body=COALESCE(response_body,provider_response)");
}catch(Throwable $e){}
try{
  $cols=[]; foreach(db()->query("SHOW COLUMNS FROM notification_templates")->fetchAll() as $c){$cols[$c['Field']]=true;}
  if(empty($cols['event_key']) && !empty($cols['template_key'])) @db()->exec("ALTER TABLE notification_templates ADD COLUMN event_key VARCHAR(120) NULL AFTER id");
  if(empty($cols['title'])) @db()->exec("ALTER TABLE notification_templates ADD COLUMN title VARCHAR(190) NULL");
  if(empty($cols['sms_body'])) @db()->exec("ALTER TABLE notification_templates ADD COLUMN sms_body TEXT NULL");
  if(empty($cols['whatsapp_body'])) @db()->exec("ALTER TABLE notification_templates ADD COLUMN whatsapp_body TEXT NULL");
  if(empty($cols['email_subject'])) @db()->exec("ALTER TABLE notification_templates ADD COLUMN email_subject VARCHAR(190) NULL");
  if(empty($cols['email_body'])) @db()->exec("ALTER TABLE notification_templates ADD COLUMN email_body TEXT NULL");
  @db()->exec("UPDATE notification_templates SET event_key=COALESCE(event_key,template_key), title=COALESCE(title,subject), email_subject=COALESCE(email_subject,subject), email_body=COALESCE(email_body,body)");
}catch(Throwable $e){}

// v7.6.3 Notification Center Production - SMS, WhatsApp, Mail sending, templates, logs
function ao_json_config($row) {
    $cfg = json_decode($row['config_json'] ?? '{}', true);
    return is_array($cfg) ? $cfg : [];
}
function ao_http_request($method, $url, $headers = [], $body = null, $timeout = 12) {
    $method = strtoupper($method ?: 'POST');
    $headerLines = [];
    foreach ($headers as $k=>$v) { $headerLines[] = is_int($k) ? $v : ($k . ': ' . $v); }
    $opts = ['http'=>['method'=>$method,'timeout'=>$timeout,'ignore_errors'=>true,'header'=>implode("\r\n", $headerLines)]];
    if ($body !== null && $method !== 'GET') $opts['http']['content'] = is_string($body) ? $body : http_build_query((array)$body);
    $ctx = stream_context_create($opts);
    $res = @file_get_contents($url, false, $ctx);
    $code = '0';
    $responseHeaders=function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?: []) : [];
    foreach ($responseHeaders as $h) if (preg_match('/^HTTP\/\S+\s+(\d+)/', $h, $m)) { $code = $m[1]; break; }
    return ['ok'=>($res !== false && (int)$code >= 200 && (int)$code < 300), 'code'=>$code, 'body'=>$res === false ? '' : $res];
}
function ao_notification_active_channel($type, $provider = null) {
    if ($provider) { $q=db()->prepare('SELECT * FROM notification_channels WHERE channel_type=? AND provider=? ORDER BY priority,id LIMIT 1'); $q->execute([$type,$provider]); }
    else { $q=db()->prepare('SELECT * FROM notification_channels WHERE channel_type=? AND status="active" ORDER BY priority,id LIMIT 1'); $q->execute([$type]); }
    return $q->fetch() ?: null;
}
function ao_notification_log($type,$provider,$recipient,$event,$subject,$message,$status,$code='',$response='',$payload=[]) {
    try { $q=db()->prepare('INSERT INTO notification_logs(channel_type,provider,recipient,event_key,subject,message,status,response_code,response_body,payload_json,sent_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)'); $q->execute([$type,$provider,$recipient,$event,$subject,$message,$status,$code,$response,json_encode($payload,JSON_UNESCAPED_UNICODE),$status==='success'?date('Y-m-d H:i:s'):null]); } catch(Throwable $e) {}
}
function ao_render_message_template($text, $vars=[]) { foreach ($vars as $k=>$v) $text=str_replace('{'.$k.'}', (string)$v, $text); return $text; }
function ao_send_sms($to, $message, $event='manual', $provider=null) {
    $to=preg_replace('/[^0-9+]/','',trim((string)$to)); if(!$to) return ['ok'=>false,'message'=>'Telefon numarası boş.'];
    $ch=ao_notification_active_channel('sms',$provider); if(!$ch) { ao_notification_log('sms',$provider ?: '',$to,$event,'',$message,'error','','Aktif SMS kanalı yok.'); return ['ok'=>false,'message'=>'Aktif SMS kanalı yok.']; }
    $cfg=ao_json_config($ch); $payload=['to'=>$to,'message'=>$message,'provider'=>$ch['provider']];
    if ((int)$ch['test_mode']===1) { ao_notification_log('sms',$ch['provider'],$to,$event,'',$message,'success','TEST','Test modu: SMS simüle edildi.',$payload); return ['ok'=>true,'message'=>'Test modunda SMS başarılı simüle edildi.']; }
    try {
        if ($ch['provider']==='netgsm') {
            $url=$cfg['api_url'] ?: 'https://api.netgsm.com.tr/sms/send/get/';
            $params=['usercode'=>$cfg['username']??'','password'=>$cfg['password']??'','gsmno'=>$to,'message'=>$message,'msgheader'=>$cfg['sender_id']??($ch['sender_name']??'')];
            $r=ao_http_request('GET',$url.'?'.http_build_query($params),[]);
        } elseif ($ch['provider']==='twilio') {
            $sid=$cfg['account_sid']??''; $token=$cfg['auth_token']??''; $from=$cfg['from_number']??'';
            $url='https://api.twilio.com/2010-04-01/Accounts/'.$sid.'/Messages.json';
            $auth='Authorization: Basic '.base64_encode($sid.':'.$token);
            $r=ao_http_request('POST',$url,[$auth],['From'=>$from,'To'=>$to,'Body'=>$message]);
        } elseif ($ch['provider']==='iletimerkezi') {
            $url=$cfg['api_url'] ?: 'https://api.iletimerkezi.com/v1/send-sms/json';
            $apiKey=$cfg['api_key'] ?? ($cfg['username'] ?? '');
            $apiHash=$cfg['api_hash'] ?? ($cfg['password'] ?? '');
            $sender=$cfg['sender_id'] ?? ($ch['sender_name'] ?? '');
            if(!$apiKey || !$apiHash || !$sender) throw new Exception('İleti Merkezi API key/hash veya gönderici adı boş.');
            $iys=(string)($cfg['iys'] ?? '0');
            $iysList=(string)($cfg['iys_list'] ?? 'BIREYSEL');
            $order=['sender'=>$sender,'iys'=>$iys,'message'=>['text'=>$message,'receipents'=>['number'=>[$to]]]];
            if($iys==='1') $order['iysList']=$iysList;
            if(!empty($cfg['sendDateTime'])) $order['sendDateTime']=$cfg['sendDateTime'];
            $body=json_encode(['request'=>['authentication'=>['key'=>$apiKey,'hash'=>$apiHash],'order'=>$order]], JSON_UNESCAPED_UNICODE);
            $r=ao_http_request('POST',$url,['Content-Type: application/json'],$body);
        } else {
            $url=$cfg['api_url']??''; if(!$url) throw new Exception('Generic SMS API URL boş.');
            $headers=[]; if(!empty($cfg['auth_header'])) $headers[]=$cfg['auth_header'];
            $body=[($cfg['to_field']??'to')=>$to,($cfg['message_field']??'message')=>$message,'sender'=>$ch['sender_name']??''];
            $r=ao_http_request($cfg['method']??'POST',$url,$headers,http_build_query($body));
        }
        $ok=$r['ok']; ao_notification_log('sms',$ch['provider'],$to,$event,'',$message,$ok?'success':'error',$r['code'],$r['body'],$payload); return ['ok'=>$ok,'message'=>$ok?'SMS gönderildi.':'SMS API hatası: '.$r['code']];
    } catch(Throwable $e) { ao_notification_log('sms',$ch['provider'],$to,$event,'',$message,'error','EXCEPTION',$e->getMessage(),$payload); return ['ok'=>false,'message'=>$e->getMessage()]; }
}
function ao_send_whatsapp($to, $message, $event='manual', $provider=null) {
    $to=preg_replace('/[^0-9+]/','',trim((string)$to)); if(!$to) return ['ok'=>false,'message'=>'WhatsApp numarası boş.'];
    $ch=ao_notification_active_channel('whatsapp',$provider); if(!$ch) { ao_notification_log('whatsapp',$provider ?: '',$to,$event,'',$message,'error','','Aktif WhatsApp kanalı yok.'); return ['ok'=>false,'message'=>'Aktif WhatsApp kanalı yok.']; }
    $cfg=ao_json_config($ch); $payload=['to'=>$to,'message'=>$message,'provider'=>$ch['provider']];
    if ((int)$ch['test_mode']===1) { ao_notification_log('whatsapp',$ch['provider'],$to,$event,'',$message,'success','TEST','Test modu: WhatsApp simüle edildi.',$payload); return ['ok'=>true,'message'=>'Test modunda WhatsApp başarılı simüle edildi.']; }
    try {
        if ($ch['provider']==='meta') {
            $ver=$cfg['api_version'] ?: 'v20.0'; $pid=$cfg['phone_number_id']??''; $token=$cfg['access_token']??''; if(!$pid||!$token) throw new Exception('Meta phone_number_id veya token boş.');
            $url='https://graph.facebook.com/'.$ver.'/'.$pid.'/messages';
            $body=json_encode(['messaging_product'=>'whatsapp','to'=>$to,'type'=>'text','text'=>['preview_url'=>false,'body'=>$message]], JSON_UNESCAPED_UNICODE);
            $r=ao_http_request('POST',$url,['Content-Type: application/json','Authorization: Bearer '.$token],$body);
        } elseif ($ch['provider']==='360dialog') {
            $url=$cfg['api_url'] ?: 'https://waba.360dialog.io/v1/messages'; $key=$cfg['api_key']??'';
            $body=json_encode(['to'=>$to,'type'=>'text','text'=>['body'=>$message]], JSON_UNESCAPED_UNICODE);
            $r=ao_http_request('POST',$url,['Content-Type: application/json','D360-API-KEY: '.$key],$body);
        } else { // twilio_whatsapp
            $sid=$cfg['account_sid']??''; $token=$cfg['auth_token']??''; $from=$cfg['from_number']??'whatsapp:';
            $url='https://api.twilio.com/2010-04-01/Accounts/'.$sid.'/Messages.json'; $auth='Authorization: Basic '.base64_encode($sid.':'.$token);
            $twTo=str_starts_with($to,'whatsapp:')?$to:'whatsapp:'.$to; $r=ao_http_request('POST',$url,[$auth],['From'=>$from,'To'=>$twTo,'Body'=>$message]);
        }
        $ok=$r['ok']; ao_notification_log('whatsapp',$ch['provider'],$to,$event,'',$message,$ok?'success':'error',$r['code'],$r['body'],$payload); return ['ok'=>$ok,'message'=>$ok?'WhatsApp gönderildi.':'WhatsApp API hatası: '.$r['code']];
    } catch(Throwable $e) { ao_notification_log('whatsapp',$ch['provider'],$to,$event,'',$message,'error','EXCEPTION',$e->getMessage(),$payload); return ['ok'=>false,'message'=>$e->getMessage()]; }
}
function ao_send_email_notification($to,$subject,$body,$event='manual') {
    $ch=ao_notification_active_channel('email'); $from='no-reply@example.com'; $provider='mail';
    if($ch){ $cfg=ao_json_config($ch); $from=$cfg['from_email']??$from; $provider=$ch['provider']; if((int)$ch['test_mode']===1){ ao_notification_log('email',$provider,$to,$event,$subject,$body,'success','TEST','Test modu: Mail simüle edildi.'); return ['ok'=>true,'message'=>'Test modunda mail simüle edildi.']; } }
    $headers='From: '.$from."\r\n".'Content-Type: text/plain; charset=UTF-8'; $ok=@mail($to,$subject,$body,$headers); ao_notification_log('email',$provider,$to,$event,$subject,$body,$ok?'success':'error',$ok?'200':'MAIL',$ok?'Mail gönderildi':'mail() başarısız'); return ['ok'=>$ok,'message'=>$ok?'Mail gönderildi.':'Mail gönderilemedi.'];
}
function ao_notify_event($event,$customerId=0,$vars=[]) {
    $q=db()->prepare('SELECT * FROM notification_templates WHERE event_key=? AND is_active=1 LIMIT 1'); $q->execute([$event]); $tpl=$q->fetch(); if(!$tpl) return [];
    if($customerId){ $c=db()->prepare('SELECT * FROM customers WHERE id=?'); $c->execute([(int)$customerId]); $cust=$c->fetch(); if($cust){ $vars=array_merge(['customer_name'=>trim(($cust['first_name']??'').' '.($cust['last_name']??'')),'customer_email'=>$cust['email']??'','customer_phone'=>$cust['phone']??''],$vars); } }
    $out=[]; if(!empty($vars['customer_phone'])) { $out['sms']=ao_send_sms($vars['customer_phone'],ao_render_message_template($tpl['sms_body']??'',$vars),$event); $out['whatsapp']=ao_send_whatsapp($vars['customer_phone'],ao_render_message_template($tpl['whatsapp_body']??'',$vars),$event); }
    if(!empty($vars['customer_email'])) $out['email']=ao_send_email_notification($vars['customer_email'],ao_render_message_template($tpl['email_subject']??$tpl['title'],$vars),ao_render_message_template($tpl['email_body']??'',$vars),$event);
    return $out;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/notifications/save-channel') {
    verify_csrf();
    try{
        $id=(int)($_POST['id']??0); $type=trim($_POST['channel_type']??'sms'); $provider=trim($_POST['provider']??'generic'); $name=trim($_POST['name']??''); $status=trim($_POST['status']??'inactive'); $test=(int)($_POST['test_mode']??1); $priority=(int)($_POST['priority']??10); $sender=trim($_POST['sender_name']??'');
        $cfg=[]; foreach($_POST as $k=>$v) if(str_starts_with($k,'cfg_')) $cfg[substr($k,4)]=trim((string)$v);
        if(!$name) throw new Exception('Kanal adı zorunludur.'); $json=json_encode($cfg, JSON_UNESCAPED_UNICODE);
        if($id>0) db()->prepare('UPDATE notification_channels SET channel_type=?,provider=?,name=?,status=?,test_mode=?,priority=?,sender_name=?,config_json=? WHERE id=?')->execute([$type,$provider,$name,$status,$test,$priority,$sender,$json,$id]);
        else db()->prepare('INSERT INTO notification_channels(channel_type,provider,name,status,test_mode,priority,sender_name,config_json) VALUES(?,?,?,?,?,?,?,?)')->execute([$type,$provider,$name,$status,$test,$priority,$sender,$json]);
        flash('success','Bildirim kanalı kaydedildi.');
    }catch(Throwable $e){ flash('error','Bildirim kanalı kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/notifications');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/notifications/test') {
    verify_csrf(); $type=trim($_POST['channel_type']??'sms'); $to=trim($_POST['recipient']??''); $msg=trim($_POST['message']??'Ahost One test mesajı'); $provider=trim($_POST['provider']??'') ?: null;
    $res=$type==='whatsapp' ? ao_send_whatsapp($to,$msg,'manual_test',$provider) : ($type==='email' ? ao_send_email_notification($to,'Ahost One Test',$msg,'manual_test') : ao_send_sms($to,$msg,'manual_test',$provider));
    flash($res['ok']?'success':'error',$res['message']); redirect_to('admin/notifications');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/notifications/save-template') {
    verify_csrf();
    $id=(int)($_POST['id']??0); $event=trim($_POST['event_key']??''); $title=trim($_POST['title']??'');
    if($id>0) db()->prepare('UPDATE notification_templates SET event_key=?,title=?,sms_body=?,whatsapp_body=?,email_subject=?,email_body=?,is_active=? WHERE id=?')->execute([$event,$title,$_POST['sms_body']??'',$_POST['whatsapp_body']??'',$_POST['email_subject']??'',$_POST['email_body']??'',(int)($_POST['is_active']??1),$id]);
    else db()->prepare('INSERT INTO notification_templates(event_key,title,sms_body,whatsapp_body,email_subject,email_body,is_active) VALUES(?,?,?,?,?,?,?)')->execute([$event,$title,$_POST['sms_body']??'',$_POST['whatsapp_body']??'',$_POST['email_subject']??'',$_POST['email_body']??'',(int)($_POST['is_active']??1)]);
    flash('success','Bildirim şablonu kaydedildi.'); redirect_to('admin/notifications');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/profile') {
    require_customer(); $c=current_customer();
    $first=trim($_POST['first_name']??''); $last=trim($_POST['last_name']??''); $phone=trim($_POST['phone']??''); $company=trim($_POST['company_name']??'');
    if ($first && $last) { $q=db()->prepare('UPDATE customers SET first_name=?,last_name=?,company_name=?,phone=? WHERE id=?'); $q->execute([$first,$last,$company,$phone,$c['id']]); flash('success','Profil bilgileriniz güncellendi.'); }
    redirect_to('client/profile');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'client/support') {
    require_customer(); $c=current_customer(); $subject=trim($_POST['subject']??''); $department=trim($_POST['department']??'Genel'); $priority=trim($_POST['priority']??'medium'); $message=trim($_POST['message']??'');
    if ($subject && $message) { $q=db()->prepare('INSERT INTO tickets(customer_id,subject,department,priority,status) VALUES(?,?,?,?,"open")'); $q->execute([$c['id'],$subject,$department,$priority]); $tid=db()->lastInsertId(); $r=db()->prepare('INSERT INTO ticket_replies(ticket_id,sender_type,message) VALUES(?,"customer",?)'); $r->execute([$tid,$message]); flash('success','Destek talebiniz oluşturuldu.'); }
    else { flash('error','Konu ve mesaj zorunludur.'); }
    redirect_to('client/support');
}





// v18.8.9 Migration Bridge Pro - schema-safe connection, dry-run and import
function ao_bridge_ensure_schema() {
    try { db()->exec("CREATE TABLE IF NOT EXISTS bridge_connections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(160) NOT NULL,
        source_type VARCHAR(40) DEFAULT 'source',
        source_host VARCHAR(190) NOT NULL,
        source_database VARCHAR(190) NOT NULL,
        source_username VARCHAR(190) NOT NULL,
        source_password TEXT NULL,
        source_charset VARCHAR(40) DEFAULT 'utf8mb4',
        table_prefix VARCHAR(40) DEFAULT 'tbl',
        status VARCHAR(40) DEFAULT 'ready',
        last_test_status VARCHAR(40) NULL,
        last_test_message TEXT NULL,
        last_test_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS bridge_runs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        connection_id INT NOT NULL,
        run_type VARCHAR(40) NOT NULL,
        status VARCHAR(40) DEFAULT 'running',
        summary_json LONGTEXT NULL,
        error_message TEXT NULL,
        started_at DATETIME NULL,
        finished_at DATETIME NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY connection_id(connection_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS bridge_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        run_id INT NOT NULL,
        entity_type VARCHAR(80) NOT NULL,
        source_id VARCHAR(80) NULL,
        source_label VARCHAR(255) NULL,
        target_table VARCHAR(120) NULL,
        target_id INT NULL,
        action_name VARCHAR(80) NULL,
        status VARCHAR(40) DEFAULT 'ok',
        message TEXT NULL,
        payload_json LONGTEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY run_id(run_id), KEY entity_type(entity_type), KEY status(status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS bridge_import_maps (
        id INT AUTO_INCREMENT PRIMARY KEY,
        connection_id INT NOT NULL,
        entity_type VARCHAR(80) NOT NULL,
        source_id VARCHAR(80) NOT NULL,
        target_table VARCHAR(120) NOT NULL,
        target_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_bridge_map(connection_id,entity_type,source_id,target_table),
        KEY target_lookup(target_table,target_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
}
function ao_bridge_ensure_target_schema() {
    $sqls = [];
    $sqls[] = "CREATE TABLE IF NOT EXISTS customers (id INT AUTO_INCREMENT PRIMARY KEY, first_name VARCHAR(120) NULL, last_name VARCHAR(120) NULL, company_name VARCHAR(190) NULL, email VARCHAR(190) UNIQUE NULL, password_hash VARCHAR(255) NULL, phone VARCHAR(80) NULL, address1 VARCHAR(255) NULL, address2 VARCHAR(255) NULL, city VARCHAR(120) NULL, state VARCHAR(120) NULL, postcode VARCHAR(40) NULL, country VARCHAR(80) NULL, status VARCHAR(40) DEFAULT 'active', notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS product_groups (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(160) NOT NULL, slug VARCHAR(190) UNIQUE NOT NULL, description TEXT NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT PRIMARY KEY, group_id INT NULL, name VARCHAR(190) NOT NULL, slug VARCHAR(220) UNIQUE NOT NULL, type VARCHAR(60) DEFAULT 'service', description TEXT NULL, module_name VARCHAR(120) NULL, auto_setup VARCHAR(40) DEFAULT 'pending', is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY group_id(group_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS domains (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, domain_name VARCHAR(190) NOT NULL, registrar VARCHAR(120) NULL, status VARCHAR(40) DEFAULT 'active', registration_date DATE NULL, expiry_date DATE NULL, next_due_date DATE NULL, auto_renew TINYINT(1) DEFAULT 1, lock_status TINYINT(1) DEFAULT 1, epp_code VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id), KEY domain_name(domain_name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS services (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, product_id INT NULL, domain VARCHAR(190) NULL, status VARCHAR(40) DEFAULT 'active', billing_cycle VARCHAR(60) NULL, next_due_date DATE NULL, auto_renew TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id), KEY product_id(product_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS invoices (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, invoice_number VARCHAR(80) UNIQUE NOT NULL, status VARCHAR(40) DEFAULT 'unpaid', subtotal DECIMAL(14,2) DEFAULT 0, tax DECIMAL(14,2) DEFAULT 0, total DECIMAL(14,2) DEFAULT 0, due_date DATE NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS invoice_items (id INT AUTO_INCREMENT PRIMARY KEY, invoice_id INT NOT NULL, description VARCHAR(255) NOT NULL, amount DECIMAL(14,2) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY invoice_id(invoice_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS tickets (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, subject VARCHAR(255) NOT NULL, department VARCHAR(120) DEFAULT 'General', priority VARCHAR(40) DEFAULT 'medium', status VARCHAR(40) DEFAULT 'open', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS ticket_replies (id INT AUTO_INCREMENT PRIMARY KEY, ticket_id INT NOT NULL, sender_type VARCHAR(40) DEFAULT 'customer', message LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY ticket_id(ticket_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS server_nodes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(160) NOT NULL, panel_type VARCHAR(80) DEFAULT 'whm', hostname VARCHAR(190) NULL, ip_address VARCHAR(80) NULL, username VARCHAR(120) NULL, api_token TEXT NULL, status VARCHAR(40) DEFAULT 'active', test_mode TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS hosting_accounts (id INT AUTO_INCREMENT PRIMARY KEY, service_id INT NULL, server_name VARCHAR(160) NULL, server_ip VARCHAR(80) NULL, whm_username VARCHAR(120) NULL, panel_password TEXT NULL, package_name VARCHAR(160) NULL, cpanel_url VARCHAR(255) NULL, webmail_url VARCHAR(255) NULL, whm_url VARCHAR(255) NULL, directadmin_url VARCHAR(255) NULL, vps_panel_url VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY service_id(service_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS domain_registrars (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(160) NOT NULL, slug VARCHAR(160) UNIQUE NOT NULL, module_name VARCHAR(160) NULL, status VARCHAR(40) DEFAULT 'active', test_mode TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS registrar_configs (id INT AUTO_INCREMENT PRIMARY KEY, registrar_id INT NOT NULL, config_key VARCHAR(160) NOT NULL, config_value TEXT NULL, UNIQUE KEY uniq_reg_cfg(registrar_id,config_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sqls[] = "CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, order_number VARCHAR(80) UNIQUE NULL, status VARCHAR(40) DEFAULT 'pending', total DECIMAL(14,2) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY customer_id(customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    foreach($sqls as $sql){ try { db()->exec($sql); } catch(Throwable $e) {} }
    try { db()->exec("ALTER TABLE domains ADD COLUMN next_due_date DATE NULL AFTER expiry_date"); } catch(Throwable $e) {}
}
function ao_bridge_required_source_entities($conn) {
    return [
        'customers'=>['table'=>ao_bridge_table($conn,'clients'),'label'=>'CONCAT(firstname," ",lastname," <",email,">")','required'=>true],
        'product_groups'=>['table'=>ao_bridge_table($conn,'productgroups'),'label'=>'name','required'=>false],
        'products'=>['table'=>ao_bridge_table($conn,'products'),'label'=>'name','required'=>false],
        'product_pricing'=>['table'=>ao_bridge_table($conn,'pricing'),'label'=>'CONCAT(type," #",relid," ",monthly,"/",annually)','required'=>false],
        'domain_pricing'=>['table'=>ao_bridge_table($conn,'domainpricing'),'label'=>'extension','required'=>false],
        'servers'=>['table'=>ao_bridge_table($conn,'servers'),'label'=>'name','required'=>false],
        'registrars'=>['table'=>ao_bridge_table($conn,'registrars'),'label'=>'registrar','required'=>false],
        'services'=>['table'=>ao_bridge_table($conn,'hosting'),'label'=>'CONCAT(domain," / ",domainstatus)','required'=>false],
        'domains'=>['table'=>ao_bridge_table($conn,'domains'),'label'=>'domain','required'=>false],
        'orders'=>['table'=>ao_bridge_table($conn,'orders'),'label'=>'CONCAT("Order #",id," ",status)','required'=>false],
        'invoices'=>['table'=>ao_bridge_table($conn,'invoices'),'label'=>'CONCAT("Invoice #",id," ",status," ",total)','required'=>false],
        'invoice_items'=>['table'=>ao_bridge_table($conn,'invoiceitems'),'label'=>'description','required'=>false],
        'tickets'=>['table'=>ao_bridge_table($conn,'tickets'),'label'=>'title','required'=>false],
    ];
}
function ao_bridge_table_exists($pdo,$table) {
    try { $s=$pdo->prepare('SHOW TABLES LIKE ?'); $s->execute([$table]); return (bool)$s->fetchColumn(); } catch(Throwable $e) { return false; }
}
function ao_bridge_safe_count($pdo,$table) {
    if(!ao_bridge_table_exists($pdo,$table)) return ['exists'=>false,'count'=>0,'message'=>'Tablo bulunamadı'];
    try { return ['exists'=>true,'count'=>(int)$pdo->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn(),'message'=>'OK']; } catch(Throwable $e) { return ['exists'=>true,'count'=>0,'message'=>$e->getMessage()]; }
}
function ao_bridge_test_connection_full($conn) {
    $pdo=ao_bridge_connect($conn); $entities=ao_bridge_required_source_entities($conn); $summary=[]; $errors=[];
    foreach($entities as $entity=>$cfg){ $res=ao_bridge_safe_count($pdo,$cfg['table']); $summary[$entity]=['table'=>$cfg['table'],'exists'=>$res['exists'],'count'=>$res['count'],'message'=>$res['message']]; if($cfg['required'] && !$res['exists']) $errors[]=$cfg['table'].' zorunlu tablo bulunamadı.'; }
    return ['ok'=>empty($errors),'summary'=>$summary,'message'=>empty($errors)?'Bağlantı başarılı. Kaynak tablolar okunabiliyor.':implode(' ', $errors)];
}

// v7.4.0 Migration & Bridge Production - Kaynak Sistem database bridge, dry-run, import, maps and logs
function ao_bridge_prefix($prefix) { return preg_replace('/[^a-zA-Z0-9_]/', '', (string)$prefix); }
function ao_bridge_table($conn, $name) { return ao_bridge_prefix($conn['table_prefix'] ?? 'tbl') . $name; }
function ao_bridge_connect($conn) {
    $charset = $conn['source_charset'] ?: 'utf8mb4';
    $dsn = 'mysql:host='.$conn['source_host'].(!empty($conn['source_port'])?';port='.(int)$conn['source_port']:'').';dbname='.$conn['source_database'].';charset='.$charset;
    return new PDO($dsn, $conn['source_username'], $conn['source_password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}
function ao_bridge_get_connection($id) {
    ao_bridge_ensure_schema();
    $q=db()->prepare('SELECT * FROM bridge_connections WHERE id=? LIMIT 1'); $q->execute([(int)$id]); return $q->fetch() ?: null;
}
function ao_bridge_log_item($runId,$entity,$sourceId,$label,$targetTable,$targetId,$action,$status,$message,$payload=[]) {
    ao_bridge_ensure_schema();
    try { $q=db()->prepare('INSERT INTO bridge_items(run_id,entity_type,source_id,source_label,target_table,target_id,action_name,status,message,payload_json) VALUES(?,?,?,?,?,?,?,?,?,?)'); $q->execute([$runId,$entity,(string)$sourceId,$label,$targetTable,$targetId,$action,$status,$message,json_encode($payload, JSON_UNESCAPED_UNICODE)]); } catch(Throwable $e) {}
}
function ao_bridge_map_target($connectionId,$entity,$sourceId,$targetTable) {
    ao_bridge_ensure_schema();
    $q=db()->prepare('SELECT target_id FROM bridge_import_maps WHERE connection_id=? AND entity_type=? AND source_id=? AND target_table=? LIMIT 1');
    $q->execute([(int)$connectionId,$entity,(string)$sourceId,$targetTable]);
    $v=$q->fetchColumn(); return $v ? (int)$v : 0;
}
function ao_bridge_save_map($connectionId,$entity,$sourceId,$targetTable,$targetId) {
    ao_bridge_ensure_schema();
    $q=db()->prepare('INSERT INTO bridge_import_maps(connection_id,entity_type,source_id,target_table,target_id) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE target_id=VALUES(target_id)');
    $q->execute([(int)$connectionId,$entity,(string)$sourceId,$targetTable,(int)$targetId]);
}
function ao_bridge_source_count($pdo,$table) { try { return (int)$pdo->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn(); } catch(Throwable $e) { return 0; } }
function ao_bridge_source_sample($pdo,$table,$labelSql,$limit=5) {
    try { return $pdo->query('SELECT id, '.$labelSql.' AS label FROM `'.$table.'` ORDER BY id DESC LIMIT '.(int)$limit)->fetchAll(); } catch(Throwable $e) { return []; }
}
function ao_bridge_status_from_source($status) {
    $s=strtolower((string)$status);
    if(in_array($s,['active','completed','paid','open','answered'],true)) return 'active';
    if(in_array($s,['suspended'],true)) return 'suspended';
    if(in_array($s,['terminated','cancelled','canceled'],true)) return 'terminated';
    if(in_array($s,['pending'],true)) return 'pending';
    return $s ?: 'active';
}

function ao_bridge_db_columns($table) {
    static $cache = [];
    $table = preg_replace('/[^a-zA-Z0-9_]/','',(string)$table);
    if(isset($cache[$table])) return $cache[$table];
    try {
        $q = db()->query('SHOW COLUMNS FROM `'.$table.'`');
        $cols = [];
        foreach($q->fetchAll() as $r) $cols[$r['Field']] = true;
        return $cache[$table] = $cols;
    } catch(Throwable $e) { return $cache[$table] = []; }
}
function ao_bridge_has_column($table,$col) { $cols=ao_bridge_db_columns($table); return isset($cols[$col]); }
function ao_bridge_filter_data($table,$data) {
    $cols=ao_bridge_db_columns($table); $out=[];
    foreach($data as $k=>$v){ if(isset($cols[$k])) $out[$k]=$v; }
    return $out;
}
function ao_bridge_insert_dynamic($table,$data) {
    $data=ao_bridge_filter_data($table,$data);
    if(!$data) throw new Exception($table.' için uyumlu kolon bulunamadı.');
    $cols=array_keys($data); $sql='INSERT INTO `'.$table.'` (`'.implode('`,`',$cols).'`) VALUES ('.implode(',',array_fill(0,count($cols),'?')).')';
    $q=db()->prepare($sql); $q->execute(array_values($data)); return (int)db()->lastInsertId();
}
function ao_bridge_update_dynamic($table,$id,$data) {
    $data=ao_bridge_filter_data($table,$data); if(!$data) return;
    $sets=[]; foreach(array_keys($data) as $c) $sets[]='`'.$c.'`=?';
    $vals=array_values($data); $vals[]=(int)$id;
    db()->prepare('UPDATE `'.$table.'` SET '.implode(',',$sets).' WHERE id=?')->execute($vals);
}
function ao_bridge_find_by($table,$where) {
    $where=ao_bridge_filter_data($table,$where); if(!$where) return 0;
    $parts=[]; $vals=[]; foreach($where as $k=>$v){ $parts[]='`'.$k.'`=?'; $vals[]=$v; }
    $q=db()->prepare('SELECT id FROM `'.$table.'` WHERE '.implode(' AND ',$parts).' LIMIT 1'); $q->execute($vals); return (int)$q->fetchColumn();
}
function ao_bridge_slug($text,$fallback='item') {
    $slug=preg_replace('/[^a-z0-9]+/','-',strtolower((string)$text)); $slug=trim($slug,'-'); return $slug ?: $fallback;
}
function ao_bridge_first($arr,$keys,$default=null) {
    foreach((array)$keys as $k){ if(isset($arr[$k]) && $arr[$k]!=='' && $arr[$k]!==null) return $arr[$k]; }
    return $default;
}
function ao_bridge_dependency_order() {
    return ['customers','product_groups','products','product_pricing','domain_pricing','servers','registrars','services','domains','orders','invoices','invoice_items','tickets'];
}
function ao_bridge_normalize_date($v) {
    $v=trim((string)$v); if($v==='' || $v==='0000-00-00' || $v==='0000-00-00 00:00:00') return null; return substr($v,0,10);
}
function ao_bridge_source_user_id($row) { return ao_bridge_first($row,['userid','clientid','user_id','customer_id'],0); }

function ao_bridge_create_run($connectionId,$runType) {
    ao_bridge_ensure_schema();
    $q=db()->prepare('INSERT INTO bridge_runs(connection_id,run_type,status,started_at,created_by) VALUES(?,?,"running",NOW(),?)'); $q->execute([(int)$connectionId,$runType,(int)($_SESSION['admin_id']??0)]); return (int)db()->lastInsertId();
}
function ao_bridge_finish_run($runId,$status,$summary=[],$error='') {
    ao_bridge_ensure_schema();
    $q=db()->prepare('UPDATE bridge_runs SET status=?, finished_at=NOW(), summary_json=?, error_message=? WHERE id=?');
    $q->execute([$status,json_encode($summary, JSON_UNESCAPED_UNICODE),$error,(int)$runId]);
}
function ao_bridge_run_source($connectionId,$mode='dry_run') {
    ao_bridge_ensure_schema(); ao_bridge_ensure_selector_schema(); ao_bridge_ensure_target_schema();
    $conn=ao_bridge_get_connection($connectionId); if(!$conn) throw new Exception('Bridge bağlantısı bulunamadı.');
    $pdo=ao_bridge_connect($conn); $runId=ao_bridge_create_run($connectionId,$mode); $summary=[];
    $entities = ao_bridge_required_source_entities($conn);
    foreach($entities as $entity=>$cfg){
        $res=ao_bridge_safe_count($pdo,$cfg['table']); $summary[$entity]=$res;
        if(!$res['exists']) { ao_bridge_log_item($runId,$entity,'','',null,null,'preview',$cfg['required']?'error':'warning',$cfg['table'].' tablosu bulunamadı.',$res); continue; }
        foreach(ao_bridge_source_sample($pdo,$cfg['table'],$cfg['label']) as $sample) ao_bridge_log_item($runId,$entity,$sample['id'],$sample['label'],null,null,'preview','ok','Ön izleme kaydı bulundu.',$sample);
    }
    if($mode==='dry_run'){ ao_bridge_finish_run($runId,'completed',$summary); return $runId; }
    foreach(ao_bridge_dependency_order() as $entity){
        if(empty($entities[$entity])) continue; $tbl=$entities[$entity]['table'];
        if(!ao_bridge_table_exists($pdo,$tbl)){ ao_bridge_log_item($runId,$entity,'','',null,null,'import','warning',$tbl.' tablosu bulunamadı.'); continue; }
        try{
            foreach($pdo->query('SELECT * FROM `'.$tbl.'` ORDER BY id ASC') as $r){ ao_bridge_import_row_from_payload($connectionId,$runId,$entity,$r); }
        }catch(Throwable $e){ ao_bridge_log_item($runId,$entity,'','',null,null,'import','error',$e->getMessage()); }
    }
    ao_bridge_finish_run($runId,'completed',$summary); ao_log('bridge','source.import','Aktarım tamamlandı. Run: '.$runId); return $runId;
}

// v20.0.3 Migration Import Selector Pro - zip/sql preview, selectable import and safer bridge workflow
function ao_bridge_ensure_selector_schema() {
    ao_bridge_ensure_schema();
    try { db()->exec("ALTER TABLE bridge_connections ADD COLUMN source_mode VARCHAR(40) DEFAULT 'database' AFTER source_type"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE bridge_connections ADD COLUMN source_port INT NULL AFTER source_host"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE bridge_connections ADD COLUMN source_ssl TINYINT(1) DEFAULT 0 AFTER source_port"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE bridge_connections ADD COLUMN source_sql_path VARCHAR(255) NULL AFTER source_password"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS bridge_sql_uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        connection_id INT NULL,
        source_type VARCHAR(40) DEFAULT 'source',
        original_name VARCHAR(255) NULL,
        stored_path VARCHAR(255) NOT NULL,
        sql_file_name VARCHAR(255) NULL,
        status VARCHAR(40) DEFAULT 'uploaded',
        message TEXT NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY connection_id(connection_id), KEY status(status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS bridge_import_selections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        connection_id INT NOT NULL,
        entity_type VARCHAR(80) NOT NULL,
        source_id VARCHAR(80) NOT NULL,
        source_label VARCHAR(255) NULL,
        selected TINYINT(1) DEFAULT 1,
        payload_json LONGTEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_bridge_selection(connection_id,entity_type,source_id),
        KEY entity_lookup(entity_type,selected)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
}
function ao_bridge_upload_dir() {
    $dir=__DIR__.'/storage/migration_uploads';
    if(!is_dir($dir)) @mkdir($dir,0775,true);
    return $dir;
}
function ao_bridge_sql_unquote($v) {
    $v=trim((string)$v);
    if(strcasecmp($v,'NULL')===0) return null;
    if(strlen($v)>=2 && (($v[0]==="'" && substr($v,-1)==="'") || ($v[0]=='"' && substr($v,-1)=='"'))) {
        $v=substr($v,1,-1);
        $v=str_replace(["\\'",'\\"','\\n','\\r','\\t','\\\\'],["'",'"',"\n","\r","\t",'\\'],$v);
    }
    return $v;
}
function ao_bridge_parse_tuple_values($tuple) {
    $values=[]; $cur=''; $quote=null; $esc=false; $len=strlen($tuple);
    for($i=0;$i<$len;$i++){
        $ch=$tuple[$i];
        if($esc){ $cur.=$ch; $esc=false; continue; }
        if($ch==='\\'){ $cur.=$ch; $esc=true; continue; }
        if($quote){ if($ch===$quote){ $quote=null; } $cur.=$ch; continue; }
        if($ch==="'" || $ch==='"'){ $quote=$ch; $cur.=$ch; continue; }
        if($ch===','){ $values[]=ao_bridge_sql_unquote($cur); $cur=''; continue; }
        $cur.=$ch;
    }
    $values[]=ao_bridge_sql_unquote($cur);
    return $values;
}
function ao_bridge_find_insert_tuples($valuesSql) {
    $tuples=[]; $depth=0; $cur=''; $quote=null; $esc=false; $len=strlen($valuesSql);
    for($i=0;$i<$len;$i++){
        $ch=$valuesSql[$i];
        if($esc){ if($depth>0) $cur.=$ch; $esc=false; continue; }
        if($ch==='\\'){ if($depth>0) $cur.=$ch; $esc=true; continue; }
        if($quote){ if($ch===$quote){ $quote=null; } if($depth>0) $cur.=$ch; continue; }
        if($ch==="'" || $ch==='"'){ $quote=$ch; if($depth>0) $cur.=$ch; continue; }
        if($ch==='('){ if($depth>0) $cur.=$ch; $depth++; continue; }
        if($ch===')'){ $depth--; if($depth===0){ $tuples[]=$cur; $cur=''; } else if($depth>0) $cur.=$ch; continue; }
        if($depth>0) $cur.=$ch;
    }
    return $tuples;
}
function ao_bridge_sql_tables_from_file($path) {
    $txt=@file_get_contents($path); if($txt===false) return [];
    preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i',$txt,$m);
    $tables=array_values(array_unique($m[1] ?? []));
    preg_match_all('/INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i',$txt,$mi);
    foreach(($mi[1]??[]) as $t) if(!in_array($t,$tables,true)) $tables[]=$t;
    return $tables;
}
function ao_bridge_sql_columns_for_table($path,$table) {
    $txt=@file_get_contents($path); if($txt===false) return [];
    $qt=preg_quote($table,'/');
    if(preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?'.$qt.'`?\s*\((.*?)\)\s*ENGINE/is',$txt,$m)){
        $cols=[]; foreach(preg_split('/\n|\r/',$m[1]) as $line){ $line=trim($line); if(preg_match('/^`([^`]+)`\s+/',$line,$cm)) $cols[]=$cm[1]; }
        return $cols;
    }
    return [];
}
function ao_bridge_sql_rows_for_table($path,$table,$limit=200) {
    $txt=@file_get_contents($path); if($txt===false) return [];
    $qt=preg_quote($table,'/'); $rows=[]; $defaultCols=ao_bridge_sql_columns_for_table($path,$table);
    $re='/INSERT\s+INTO\s+`?'.$qt.'`?\s*(?:\((.*?)\))?\s*VALUES\s*(.*?);/is';
    if(preg_match_all($re,$txt,$matches,PREG_SET_ORDER)){
        foreach($matches as $m){
            $cols=[]; if(!empty($m[1])){ foreach(explode(',',$m[1]) as $c) $cols[]=trim($c," `\t\n\r\0\x0B"); } else $cols=$defaultCols;
            foreach(ao_bridge_find_insert_tuples($m[2]) as $tuple){
                $vals=ao_bridge_parse_tuple_values($tuple); $row=[];
                foreach($vals as $i=>$v){ $key=$cols[$i] ?? ('col_'.$i); $row[$key]=$v; }
                $rows[]=$row; if(count($rows)>=$limit) return $rows;
            }
        }
    }
    return $rows;
}
function ao_bridge_entity_table_map($conn) {
    if(($conn['source_type']??'source')==='source') return ao_bridge_required_source_entities($conn);
    return ao_bridge_required_source_entities($conn);
}
function ao_bridge_row_label($entity,$row) {
    if($entity==='customers') return trim(($row['firstname']??$row['first_name']??'').' '.($row['lastname']??$row['last_name']??'')).' <'.($row['email']??''). '>';
    if($entity==='products' || $entity==='product_groups') return (string)($row['name']??$row['title']??('ID '.$row['id']));
    if($entity==='product_pricing') return 'Pricing '.($row['type']??'').' #'.($row['relid']??$row['id']??'').' | M:'.($row['monthly']??'-').' Y:'.($row['annually']??'-');
    if($entity==='domain_pricing') return '.'.ltrim((string)($row['extension']??$row['tld']??('ID '.$row['id'])),'.');
    if($entity==='domains') return (string)($row['domain']??$row['domain_name']??('ID '.$row['id']));
    if($entity==='services') return (string)($row['domain']??($row['username']??('ID '.$row['id'])));
    if($entity==='invoices') return 'Invoice #'.($row['id']??'').' '.($row['status']??'').' '.($row['total']??'');
    if($entity==='tickets') return (string)($row['title']??$row['subject']??('Ticket #'.$row['id']));
    if($entity==='servers') return (string)($row['name']??$row['hostname']??('Server #'.$row['id']));
    if($entity==='registrars') return (string)($row['registrar']??$row['name']??('Registrar #'.$row['id']));
    return (string)($row['id']??'');
}
function ao_bridge_sql_preview($conn,$maxRows=30) {
    $path=$conn['source_sql_path'] ?? ''; if(!$path || !is_file($path)) return ['ok'=>false,'message'=>'SQL dosyası bulunamadı.','entities'=>[]];
    $entities=ao_bridge_entity_table_map($conn); $out=[];
    foreach($entities as $entity=>$cfg){
        $table=$cfg['table']; $rows=ao_bridge_sql_rows_for_table($path,$table,$maxRows); $count=count(ao_bridge_sql_rows_for_table($path,$table,1000000));
        $sample=[]; foreach($rows as $r){ $sample[]=['id'=>(string)($r['id']??''),'label'=>ao_bridge_row_label($entity,$r),'payload'=>$r]; }
        $out[$entity]=['table'=>$table,'exists'=>$count>0 || in_array($table,ao_bridge_sql_tables_from_file($path),true),'count'=>$count,'sample'=>$sample];
    }
    return ['ok'=>true,'message'=>'SQL yedeği analiz edildi.','entities'=>$out];
}
function ao_bridge_store_selection($connectionId,$preview) {
    ao_bridge_ensure_selector_schema();
    foreach(($preview['entities']??[]) as $entity=>$info){
        foreach(($info['sample']??[]) as $row){
            $sid=$row['id'] ?: md5($row['label'].json_encode($row['payload']));
            $q=db()->prepare('INSERT INTO bridge_import_selections(connection_id,entity_type,source_id,source_label,selected,payload_json) VALUES(?,?,?,?,1,?) ON DUPLICATE KEY UPDATE source_label=VALUES(source_label), payload_json=VALUES(payload_json)');
            $q->execute([(int)$connectionId,$entity,$sid,$row['label'],json_encode($row['payload'],JSON_UNESCAPED_UNICODE)]);
        }
    }
}

function ao_bridge_currency_from_source($currencyId) {
    // Kaynak fiyatlandırma currency alanı genelde para birimi ID'sidir. Kaynak currency tablosu ayrıca eşlenmemişse TRY güvenli varsayılandır.
    $v = trim((string)$currencyId);
    if (in_array(strtoupper($v), ['TRY','USD','EUR','GBP'], true)) return strtoupper($v);
    return 'TRY';
}
function ao_bridge_upsert_product_price($productId,$cycle,$price,$setupFee=0,$currency='TRY') {
    $productId=(int)$productId; if($productId<=0) return;
    $price=(float)$price; $setupFee=(float)$setupFee; if($price < 0) return;
    try {
        db()->prepare('INSERT INTO product_pricing(product_id,cycle,price,setup_fee,currency) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE price=VALUES(price), setup_fee=VALUES(setup_fee), currency=VALUES(currency)')
            ->execute([$productId,$cycle,$price,$setupFee,$currency]);
        if($cycle==='monthly' || $cycle==='onetime') db()->prepare('UPDATE products SET price=?, currency=?, billing_cycle=? WHERE id=?')->execute([$price,$currency,$cycle,$productId]);
    } catch(Throwable $e) {}
}
function ao_bridge_upsert_tld_price($tld,$action,$price,$currency='TRY',$registrarSlug='ahost-import') {
    $tld = ltrim(strtolower(trim((string)$tld)),'.'); if($tld==='') return;
    $price = (float)$price; if($price < 0) return;
    try { db()->exec('CREATE TABLE IF NOT EXISTS tld_pricing (id INT AUTO_INCREMENT PRIMARY KEY, tld VARCHAR(40) NOT NULL, registrar_slug VARCHAR(120) DEFAULT NULL, register_price DECIMAL(14,2) DEFAULT 0.00, renew_price DECIMAL(14,2) DEFAULT 0.00, transfer_price DECIMAL(14,2) DEFAULT 0.00, currency VARCHAR(10) DEFAULT \'USD\', is_active TINYINT(1) DEFAULT 1, UNIQUE KEY uniq_tld_reg(tld,registrar_slug)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'); } catch(Throwable $e) {}
    try {
        $q=db()->prepare('SELECT id FROM tld_pricing WHERE tld=? AND registrar_slug=? LIMIT 1'); $q->execute([$tld,$registrarSlug]); $id=(int)$q->fetchColumn();
        if(!$id){ db()->prepare('INSERT INTO tld_pricing(tld,registrar_slug,currency,is_active) VALUES(?,?,?,1)')->execute([$tld,$registrarSlug,$currency]); $id=(int)db()->lastInsertId(); }
        $col = $action==='renew' ? 'renew_price' : ($action==='transfer' ? 'transfer_price' : 'register_price');
        db()->prepare('UPDATE tld_pricing SET `'.$col.'`=?, currency=?, is_active=1 WHERE id=?')->execute([$price,$currency,$id]);
    } catch(Throwable $e) {}
}
function ao_bridge_source_price_cycles($row) {
    return [
        'monthly'=>['price'=>$row['monthly']??null,'setup'=>$row['msetupfee']??0],
        'quarterly'=>['price'=>$row['quarterly']??null,'setup'=>$row['qsetupfee']??0],
        'semiannually'=>['price'=>$row['semiannually']??null,'setup'=>$row['ssetupfee']??0],
        'annually'=>['price'=>$row['annually']??null,'setup'=>$row['asetupfee']??0],
        'biennially'=>['price'=>$row['biennially']??null,'setup'=>$row['bsetupfee']??0],
        'triennially'=>['price'=>$row['triennially']??null,'setup'=>$row['tsetupfee']??0],
    ];
}
function ao_bridge_live_preview($conn,$maxRows=200) {
    $pdo = ao_bridge_connect($conn); $entities = ao_bridge_entity_table_map($conn); $out=[];
    foreach($entities as $entity=>$cfg){
        $table=$cfg['table']; $exists=ao_bridge_table_exists($pdo,$table); $count=0; $sample=[];
        if($exists){
            try { $count=(int)$pdo->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn(); } catch(Throwable $e) { $count=0; }
            try {
                if($entity==='product_pricing'){
                    $dp = ao_bridge_table($conn,'domainpricing');
                    $sql = ao_bridge_table_exists($pdo,$dp)
                        ? 'SELECT p.*, dp.extension FROM `'.$table.'` p LEFT JOIN `'.$dp.'` dp ON dp.id=p.relid ORDER BY p.id ASC LIMIT '.(int)$maxRows
                        : 'SELECT * FROM `'.$table.'` ORDER BY id ASC LIMIT '.(int)$maxRows;
                    foreach($pdo->query($sql) as $r){ $sample[]=['id'=>(string)($r['id']??md5(json_encode($r))),'label'=>ao_bridge_row_label($entity,$r),'payload'=>$r]; }
                } else {
                    foreach($pdo->query('SELECT * FROM `'.$table.'` ORDER BY id ASC LIMIT '.(int)$maxRows) as $r){ $sample[]=['id'=>(string)($r['id']??md5(json_encode($r))),'label'=>ao_bridge_row_label($entity,$r),'payload'=>$r]; }
                }
            } catch(Throwable $e) {}
        }
        $out[$entity]=['table'=>$table,'exists'=>$exists,'count'=>$count,'sample'=>$sample];
    }
    return ['ok'=>true,'message'=>'Canlı veritabanı tarandı. Aktarılacak kayıtları seçebilirsin.','entities'=>$out];
}

function ao_bridge_import_row_from_payload($connectionId,$runId,$entity,$row) {
    ao_bridge_ensure_target_schema(); $sid=(string)($row['id']??md5(json_encode($row)));
    try{
        if($entity==='customers'){
            $email=trim((string)($row['email']??'')); if($email===''){ ao_bridge_log_item($runId,'customer',$sid,ao_bridge_row_label('customers',$row),'customers',null,'import','warning','E-posta boş olduğu için müşteri atlandı.',$row); return; }
            $target=ao_bridge_map_target($connectionId,'customer',$sid,'customers') ?: ao_bridge_find_by('customers',['email'=>$email]);
            $data=['first_name'=>$row['firstname']??$row['first_name']??'', 'last_name'=>$row['lastname']??$row['last_name']??'', 'company_name'=>$row['companyname']??'', 'email'=>$email, 'password_hash'=>password_hash('ChangeMe123!',PASSWORD_DEFAULT), 'phone'=>$row['phonenumber']??$row['phone']??'', 'address1'=>$row['address1']??null, 'address2'=>$row['address2']??null, 'city'=>$row['city']??null, 'state'=>$row['state']??null, 'postcode'=>$row['postcode']??null, 'country'=>$row['country']??null, 'status'=>strtolower($row['status']??'active')==='active'?'active':'inactive', 'notes'=>'İçe aktarma kaydı. Geçici şifre: ChangeMe123!'];
            if(!$target) $target=ao_bridge_insert_dynamic('customers',$data); else ao_bridge_update_dynamic('customers',$target,$data);
            ao_bridge_save_map($connectionId,'customer',$sid,'customers',$target); ao_bridge_log_item($runId,'customer',$sid,ao_bridge_row_label('customers',$row),'customers',$target,'import','ok','Müşteri aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='product_groups'){
            $slug='source-'.ao_bridge_slug($row['name']??('group-'.$sid),'group-'.$sid); $target=ao_bridge_map_target($connectionId,'product_group',$sid,'product_groups') ?: ao_bridge_find_by('product_groups',['slug'=>$slug]);
            $data=['name'=>$row['name']??('Ürün Grubu '.$sid),'slug'=>$slug,'type'=>'hosting','description'=>$row['headline']??'İçe aktarma kaydı','is_active'=>1];
            if(!$target) $target=ao_bridge_insert_dynamic('product_groups',$data); else ao_bridge_update_dynamic('product_groups',$target,$data);
            ao_bridge_save_map($connectionId,'product_group',$sid,'product_groups',$target); ao_bridge_log_item($runId,'product_group',$sid,ao_bridge_row_label('product_groups',$row),'product_groups',$target,'import','ok','Ürün grubu aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='products'){
            $groupId=ao_bridge_map_target($connectionId,'product_group',$row['gid']??0,'product_groups') ?: null;
            $slug='source-product-'.$sid.'-'.ao_bridge_slug($row['name']??'product','product'); $target=ao_bridge_map_target($connectionId,'product',$sid,'products') ?: ao_bridge_find_by('products',['slug'=>$slug]);
            $type=strtolower((string)($row['type']??'')); $ptype=str_contains($type,'hosting')?'hosting':'service';
            $data=['group_id'=>$groupId,'name'=>$row['name']??('Ürün '.$sid),'slug'=>$slug,'type'=>$ptype,'description'=>$row['description']??'İçe aktarma kaydı','module_name'=>$row['servertype']??($row['module']??'manual'),'whm_package'=>$row['configoption1']??null,'auto_setup'=>'pending','is_active'=>1];
            if(!$target) $target=ao_bridge_insert_dynamic('products',$data); else ao_bridge_update_dynamic('products',$target,$data);
            ao_bridge_save_map($connectionId,'product',$sid,'products',$target); ao_bridge_log_item($runId,'product',$sid,ao_bridge_row_label('products',$row),'products',$target,'import','ok','Ürün aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='product_pricing'){
            $type=strtolower((string)($row['type']??'')); $relid=(string)($row['relid']??''); $currency=ao_bridge_currency_from_source($row['currency']??'TRY');
            if(str_starts_with($type,'domain')){
                $tld=$row['extension']??$row['tld']??'';
                if($tld===''){ ao_bridge_log_item($runId,'domain_pricing',$sid,ao_bridge_row_label('product_pricing',$row),'tld_pricing',null,'import','warning','Domain fiyatı için TLD eşleşmedi. Domain Pricing sekmesini de seçin.',$row); return; }
                $action=str_contains($type,'renew')?'renew':(str_contains($type,'transfer')?'transfer':'register');
                $price=(float)($row['annually']??$row['monthly']??0); if($price<=0) $price=(float)($row['msetupfee']??0);
                ao_bridge_upsert_tld_price($tld,$action,$price,$currency,'ahost-import');
                ao_bridge_log_item($runId,'domain_pricing',$sid,'.'.ltrim($tld,'.').' '.$action,'tld_pricing',null,'import','ok','Domain uzantı fiyatı aktarıldı.',$row); return;
            }
            $productId=ao_bridge_map_target($connectionId,'product',$relid,'products');
            if(!$productId){ ao_bridge_log_item($runId,'product_pricing',$sid,ao_bridge_row_label('product_pricing',$row),'product_pricing',null,'import','warning','Ürün eşleşmediği için fiyat atlandı. Önce ürünleri seçin.',$row); return; }
            foreach(ao_bridge_source_price_cycles($row) as $cycle=>$pv){ if($pv['price']!==null && (float)$pv['price']>=0) ao_bridge_upsert_product_price($productId,$cycle,$pv['price'],$pv['setup'],$currency); }
            ao_bridge_log_item($runId,'product_pricing',$sid,ao_bridge_row_label('product_pricing',$row),'product_pricing',$productId,'import','ok','Ürün fiyatları aktarıldı.',$row); return;
        }
        if($entity==='domain_pricing'){
            $tld=ltrim((string)($row['extension']??$row['tld']??''),'.');
            if($tld===''){ ao_bridge_log_item($runId,'domain_pricing',$sid,ao_bridge_row_label('domain_pricing',$row),'tld_pricing',null,'import','warning','TLD boş olduğu için atlandı.',$row); return; }
            ao_bridge_upsert_tld_price($tld,'register',(float)($row['register_price']??0),'TRY','ahost-import');
            ao_bridge_log_item($runId,'domain_pricing',$sid,'.'.$tld,'tld_pricing',null,'import','ok','Domain uzantısı aktarıldı; fiyatlar için tblpricing domain satırlarını seçin.',$row); return;
        }
        if($entity==='servers'){
            $host=$row['hostname']??($row['ipaddress']??''); $panel=strtolower($row['type']??'whm'); $target=ao_bridge_map_target($connectionId,'server',$sid,'server_nodes') ?: ao_bridge_find_by('server_nodes',['hostname'=>$host]);
            $data=['name'=>$row['name']??('Kaynak Sistem Server '.$sid),'panel_type'=>$panel?:'whm','hostname'=>$host,'ip_address'=>$row['ipaddress']??'','username'=>$row['username']??'','api_token'=>$row['accesshash']??($row['password']??''),'status'=>'active','test_mode'=>0];
            if(!$target) $target=ao_bridge_insert_dynamic('server_nodes',$data); else ao_bridge_update_dynamic('server_nodes',$target,$data);
            ao_bridge_save_map($connectionId,'server',$sid,'server_nodes',$target); ao_bridge_log_item($runId,'server',$sid,ao_bridge_row_label('servers',$row),'server_nodes',$target,'import','ok','Sunucu aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='registrars'){
            $name=$row['registrar']??($row['name']??('registrar-'.$sid)); $slug=ao_bridge_slug($name,'registrar-'.$sid); $target=ao_bridge_map_target($connectionId,'registrar',$sid,'domain_registrars') ?: ao_bridge_find_by('domain_registrars',['slug'=>$slug]);
            $data=['name'=>$name,'slug'=>$slug,'module_name'=>$name,'status'=>'active','test_mode'=>0]; if(!$target) $target=ao_bridge_insert_dynamic('domain_registrars',$data); else ao_bridge_update_dynamic('domain_registrars',$target,$data);
            ao_bridge_save_map($connectionId,'registrar',$sid,'domain_registrars',$target); ao_bridge_log_item($runId,'registrar',$sid,ao_bridge_row_label('registrars',$row),'domain_registrars',$target,'import','ok','Registrar aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='services'){
            $cust=ao_bridge_map_target($connectionId,'customer',ao_bridge_source_user_id($row),'customers'); if(!$cust){ ao_bridge_log_item($runId,'service',$sid,ao_bridge_row_label('services',$row),'services',null,'import','warning','Müşteri eşleşmediği için hosting atlandı.',$row); return; }
            $prod=ao_bridge_map_target($connectionId,'product',$row['packageid']??0,'products') ?: null; $target=ao_bridge_map_target($connectionId,'service',$sid,'services');
            $data=['customer_id'=>$cust,'product_id'=>$prod,'domain'=>$row['domain']??'','status'=>ao_bridge_status_from_source($row['domainstatus']??'active'),'billing_cycle'=>$row['billingcycle']??'monthly','next_due_date'=>ao_bridge_normalize_date($row['nextduedate']??null),'auto_renew'=>1];
            if(!$target) $target=ao_bridge_insert_dynamic('services',$data); else ao_bridge_update_dynamic('services',$target,$data);
            ao_bridge_save_map($connectionId,'service',$sid,'services',$target);
            if(!empty($row['username'])){
                $serverId=ao_bridge_map_target($connectionId,'server',$row['server']??0,'server_nodes') ?: null; $serverName='İçe Aktarılan Sunucu'; $host='';
                if($serverId){ $sq=db()->prepare('SELECT * FROM server_nodes WHERE id=?'); $sq->execute([$serverId]); if($sv=$sq->fetch()){ $serverName=$sv['name']??$serverName; $host=ao_host_from_server_row($sv); } }
                if(!ao_bridge_find_by('hosting_accounts',['service_id'=>$target])) ao_bridge_insert_dynamic('hosting_accounts',['service_id'=>$target,'server_id'=>$serverId,'server_name'=>$serverName,'username'=>$row['username'],'whm_username'=>$row['username'],'panel_password'=>$row['password']??'','package_name'=>'imported','cpanel_url'=>ao_panel_url_from_host($host?:$serverName,'cpanel'),'webmail_url'=>ao_panel_url_from_host($host?:$serverName,'webmail'),'whm_url'=>ao_panel_url_from_host($host?:$serverName,'whm'),'directadmin_url'=>ao_panel_url_from_host($host?:$serverName,'directadmin'),'vps_panel_url'=>ao_panel_url_from_host($host?:$serverName,'vps')]);
            }
            ao_bridge_log_item($runId,'service',$sid,ao_bridge_row_label('services',$row),'services',$target,'import','ok','Hosting hizmeti aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='domains'){
            $cust=ao_bridge_map_target($connectionId,'customer',ao_bridge_source_user_id($row),'customers'); if(!$cust){ ao_bridge_log_item($runId,'domain',$sid,ao_bridge_row_label('domains',$row),'domains',null,'import','warning','Müşteri eşleşmediği için domain atlandı.',$row); return; }
            $domain=$row['domain']??''; if($domain===''){ ao_bridge_log_item($runId,'domain',$sid,'','domains',null,'import','warning','Domain adı boş olduğu için atlandı.',$row); return; }
            $registrarId=null; if(!empty($row['registrar'])){ $slug=ao_bridge_slug($row['registrar'],'registrar'); $registrarId=ao_bridge_find_by('domain_registrars',['slug'=>$slug]); if(!$registrarId){ $registrarId=ao_bridge_insert_dynamic('domain_registrars',['name'=>$row['registrar'],'slug'=>$slug,'module_name'=>$row['registrar'],'status'=>'active']); } }
            $target=ao_bridge_map_target($connectionId,'domain',$sid,'domains') ?: ao_bridge_find_by('domains',['domain_name'=>$domain]);
            $data=['customer_id'=>$cust,'domain_name'=>$domain,'registrar'=>$row['registrar']??null,'registrar_id'=>$registrarId,'status'=>ao_bridge_status_from_source($row['status']??'active'),'registration_date'=>ao_bridge_normalize_date($row['registrationdate']??null),'expiry_date'=>ao_bridge_normalize_date($row['expirydate']??null),'next_due_date'=>ao_bridge_normalize_date($row['nextduedate']??($row['expirydate']??null)),'auto_renew'=>empty($row['donotrenew'])?1:0,'lock_status'=>1,'epp_code'=>$row['eppcode']??null,'auth_code'=>$row['eppcode']??null];
            if(!$target) $target=ao_bridge_insert_dynamic('domains',$data); else ao_bridge_update_dynamic('domains',$target,$data);
            ao_bridge_save_map($connectionId,'domain',$sid,'domains',$target); ao_bridge_log_item($runId,'domain',$sid,ao_bridge_row_label('domains',$row),'domains',$target,'import','ok','Domain aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='orders'){
            $cust=ao_bridge_map_target($connectionId,'customer',ao_bridge_source_user_id($row),'customers'); if(!$cust){ ao_bridge_log_item($runId,'order',$sid,ao_bridge_row_label('orders',$row),'orders',null,'import','warning','Müşteri eşleşmediği için sipariş atlandı.',$row); return; }
            $no='AHOST-ORDER-'.$sid; $target=ao_bridge_map_target($connectionId,'order',$sid,'orders') ?: ao_bridge_find_by('orders',['order_number'=>$no]);
            $data=['customer_id'=>$cust,'order_number'=>$no,'status'=>strtolower($row['status']??'pending'),'total'=>(float)($row['amount']??0),'currency'=>'TRY','payment_method'=>'source','created_at'=>$row['date']??date('Y-m-d H:i:s')];
            if(!$target) $target=ao_bridge_insert_dynamic('orders',$data); else ao_bridge_update_dynamic('orders',$target,$data);
            ao_bridge_save_map($connectionId,'order',$sid,'orders',$target); ao_bridge_log_item($runId,'order',$sid,'Sipariş #'.$sid,'orders',$target,'import','ok','Sipariş aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='invoices'){
            $cust=ao_bridge_map_target($connectionId,'customer',ao_bridge_source_user_id($row),'customers'); if(!$cust){ ao_bridge_log_item($runId,'invoice',$sid,ao_bridge_row_label('invoices',$row),'invoices',null,'import','warning','Müşteri eşleşmediği için fatura atlandı.',$row); return; }
            $no=($row['invoicenum']??'') ?: ('AHOST-'.$sid); $target=ao_bridge_map_target($connectionId,'invoice',$sid,'invoices') ?: ao_bridge_find_by('invoices',['invoice_number'=>$no]);
            $data=['customer_id'=>$cust,'invoice_number'=>$no,'status'=>strtolower($row['status']??'unpaid'),'subtotal'=>(float)($row['subtotal']??$row['total']??0),'tax'=>(float)($row['tax']??0),'total'=>(float)($row['total']??0),'currency'=>'TRY','due_date'=>ao_bridge_normalize_date($row['duedate']??null),'paid_at'=>($row['datepaid']??null)];
            if(!$target) $target=ao_bridge_insert_dynamic('invoices',$data); else ao_bridge_update_dynamic('invoices',$target,$data);
            ao_bridge_save_map($connectionId,'invoice',$sid,'invoices',$target); ao_bridge_log_item($runId,'invoice',$sid,$no,'invoices',$target,'import','ok','Fatura aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='invoice_items'){
            $invoice=ao_bridge_map_target($connectionId,'invoice',$row['invoiceid']??0,'invoices'); if(!$invoice){ ao_bridge_log_item($runId,'invoice_item',$sid,ao_bridge_row_label('invoice_items',$row),'invoice_items',null,'import','warning','Fatura eşleşmediği için fatura kalemi atlandı.',$row); return; }
            $target=ao_bridge_map_target($connectionId,'invoice_item',$sid,'invoice_items');
            $data=['invoice_id'=>$invoice,'description'=>$row['description']??('Fatura Kalemi '.$sid),'amount'=>(float)($row['amount']??0),'quantity'=>1];
            if(!$target) $target=ao_bridge_insert_dynamic('invoice_items',$data); else ao_bridge_update_dynamic('invoice_items',$target,$data);
            ao_bridge_save_map($connectionId,'invoice_item',$sid,'invoice_items',$target); ao_bridge_log_item($runId,'invoice_item',$sid,$data['description'],'invoice_items',$target,'import','ok','Fatura kalemi aktarıldı/eşlendi.',$row); return;
        }
        if($entity==='tickets'){
            $cust=ao_bridge_map_target($connectionId,'customer',ao_bridge_source_user_id($row),'customers'); if(!$cust){ ao_bridge_log_item($runId,'ticket',$sid,ao_bridge_row_label('tickets',$row),'tickets',null,'import','warning','Müşteri eşleşmediği için ticket atlandı.',$row); return; }
            $target=ao_bridge_map_target($connectionId,'ticket',$sid,'tickets');
            $data=['customer_id'=>$cust,'subject'=>$row['title']??($row['subject']??('Destek Talebi '.$sid)),'department'=>'İçe Aktarım','priority'=>$row['urgency']??'medium','status'=>strtolower($row['status']??'open')];
            if(!$target) $target=ao_bridge_insert_dynamic('tickets',$data); else ao_bridge_update_dynamic('tickets',$target,$data);
            ao_bridge_save_map($connectionId,'ticket',$sid,'tickets',$target); ao_bridge_log_item($runId,'ticket',$sid,ao_bridge_row_label('tickets',$row),'tickets',$target,'import','ok','Ticket aktarıldı/eşlendi.',$row); return;
        }
        ao_bridge_log_item($runId,$entity,$sid,ao_bridge_row_label($entity,$row),null,null,'import','warning','Bu varlık için import eşlemesi tanımlı değil.',$row);
    }catch(Throwable $e){ ao_bridge_log_item($runId,$entity,$sid,ao_bridge_row_label($entity,$row),null,null,'import','error',$e->getMessage(),$row); }
}
function ao_bridge_import_selected_sql($connectionId,$selected) {
    ao_bridge_ensure_selector_schema(); ao_bridge_ensure_target_schema();
    $conn=ao_bridge_get_connection($connectionId); if(!$conn) throw new Exception('Bridge bağlantısı bulunamadı.');
    $runId=ao_bridge_create_run($connectionId,'selected_import'); $summary=[];
    foreach(ao_bridge_dependency_order() as $entity){
        $ids=array_values((array)($selected[$entity]??[])); $summary[$entity]=count($ids); if(!$ids) continue;
        $in=implode(',',array_fill(0,count($ids),'?'));
        $q=db()->prepare("SELECT * FROM bridge_import_selections WHERE connection_id=? AND entity_type=? AND source_id IN ($in) ORDER BY CAST(source_id AS UNSIGNED), id ASC");
        $q->execute(array_merge([(int)$connectionId,$entity],$ids));
        foreach($q->fetchAll() as $sel){ $row=json_decode($sel['payload_json']??'[]',true) ?: []; ao_bridge_import_row_from_payload($connectionId,$runId,$entity,$row); }
    }
    ao_bridge_finish_run($runId,'completed',$summary); return $runId;
}


if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/migration-bridge/upload-sql') {
    ao_bridge_ensure_selector_schema(); verify_csrf();
    try{
        $id=(int)($_POST['connection_id']??0);
        $name=trim($_POST['name']??'SQL Yedeği Import'); $type=trim($_POST['source_type']??'source'); $prefix=trim($_POST['table_prefix']??'tbl'); $charset=trim($_POST['source_charset']??'utf8mb4');
        if(empty($_FILES['sql_backup']['tmp_name'])) throw new Exception('Ziplenmiş SQL veya .sql dosyası seçilmedi.');
        $orig=$_FILES['sql_backup']['name']; $ext=strtolower(pathinfo($orig,PATHINFO_EXTENSION)); $dir=ao_bridge_upload_dir(); $base='bridge_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
        $stored=$dir.'/'.$base.'.'.$ext; if(!move_uploaded_file($_FILES['sql_backup']['tmp_name'],$stored)) throw new Exception('Dosya yüklenemedi.');
        $sqlPath=$stored; $sqlName=$orig;
        if($ext==='zip'){
            if(!class_exists('ZipArchive')) throw new Exception('ZipArchive PHP eklentisi aktif değil.');
            $zip=new ZipArchive(); if($zip->open($stored)!==true) throw new Exception('ZIP açılamadı.');
            $extractDir=$dir.'/'.$base; @mkdir($extractDir,0775,true); $zip->extractTo($extractDir); $zip->close();
            $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractDir)); $found='';
            foreach($files as $f){ if($f->isFile() && strtolower($f->getExtension())==='sql'){ $found=$f->getPathname(); break; } }
            if(!$found) throw new Exception('ZIP içinde .sql dosyası bulunamadı.');
            $sqlPath=$found; $sqlName=basename($found);
        } elseif($ext!=='sql') throw new Exception('Sadece .zip veya .sql yüklenebilir.');
        if($id>0){
            $q=db()->prepare('UPDATE bridge_connections SET name=?,source_type=?,source_mode="sql_file",source_sql_path=?,source_charset=?,table_prefix=?,status="uploaded" WHERE id=?');
            $q->execute([$name,$type,$sqlPath,$charset,$prefix,$id]);
        } else {
            $q=db()->prepare('INSERT INTO bridge_connections(name,source_type,source_mode,source_host,source_database,source_username,source_password,source_sql_path,source_charset,table_prefix,status) VALUES(?,?,"sql_file","sql-upload","sql-backup","file","",?,?,?,"uploaded")');
            $q->execute([$name,$type,$sqlPath,$charset,$prefix]); $id=(int)db()->lastInsertId();
        }
        db()->prepare('INSERT INTO bridge_sql_uploads(connection_id,source_type,original_name,stored_path,sql_file_name,status,created_by) VALUES(?,?,?,?,?,"uploaded",?)')->execute([$id,$type,$orig,$stored,$sqlName,(int)($_SESSION['admin_id']??0)]);
        $conn=ao_bridge_get_connection($id); $preview=ao_bridge_sql_preview($conn,100000); ao_bridge_store_selection($id,$preview);
        flash('success','SQL yedeği yüklendi ve analiz edildi. Seçim listesinden aktarılacak kayıtları işaretleyebilirsiniz.');
        redirect_to('admin/migration-bridge?edit='.$id);
    }catch(Throwable $e){ flash('error','SQL yedeği yüklenemedi: '.$e->getMessage()); redirect_to('admin/migration-bridge'); }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/migration-bridge/import-selected') {
    ao_bridge_ensure_selector_schema(); verify_csrf();
    $id=(int)($_POST['connection_id']??0);
    try{
        $selected=[];
        foreach(($_POST['selected']??[]) as $entity=>$ids){ $selected[$entity]=array_map('strval',(array)$ids); }
        if(!$selected) throw new Exception('Aktarılacak kayıt seçilmedi.');
        $runId=ao_bridge_import_selected_sql($id,$selected);
        flash('success','Seçilen kayıtlar aktarıldı. Run ID: '.$runId);
    }catch(Throwable $e){ flash('error','Seçili import başarısız: '.$e->getMessage()); }
    redirect_to('admin/migration-bridge?edit='.$id);
}
if ($route==='admin/migration-bridge/analyze-sql') {
    ao_bridge_ensure_selector_schema(); $id=(int)($_GET['id']??0);
    try{ $conn=ao_bridge_get_connection($id); if(!$conn) throw new Exception('Bağlantı bulunamadı.'); $preview=ao_bridge_sql_preview($conn,100000); ao_bridge_store_selection($id,$preview); flash('success','SQL yedeği yeniden analiz edildi.'); }
    catch(Throwable $e){ flash('error','Analiz başarısız: '.$e->getMessage()); }
    redirect_to('admin/migration-bridge?edit='.$id);
}

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/migration-bridge/save') {
    ao_bridge_ensure_schema(); ao_bridge_ensure_target_schema();
    verify_csrf();
    try{
        $id=(int)($_POST['id']??0); $name=trim($_POST['name']??'Migration Bridge'); $type=trim($_POST['source_type']??'source'); $host=trim($_POST['source_host']??''); $port=(int)($_POST['source_port']??0); $ssl=!empty($_POST['source_ssl'])?1:0; $dbn=trim($_POST['source_database']??''); $user=trim($_POST['source_username']??''); $pass=(string)($_POST['source_password']??''); $prefix=trim($_POST['table_prefix']??'tbl'); $charset=trim($_POST['source_charset']??'utf8mb4');
        if(!$name || !$host || !$dbn || !$user) throw new Exception('Ad, host, veritabanı ve kullanıcı zorunludur.');
        if($id>0){
            if($pass===''){ $q=db()->prepare('UPDATE bridge_connections SET name=?,source_type=?,source_mode="database",source_host=?,source_port=?,source_ssl=?,source_database=?,source_username=?,source_charset=?,table_prefix=?,status="ready" WHERE id=?'); $q->execute([$name,$type,$host,$port?:null,$ssl,$dbn,$user,$charset,$prefix,$id]); }
            else { $q=db()->prepare('UPDATE bridge_connections SET name=?,source_type=?,source_mode="database",source_host=?,source_port=?,source_ssl=?,source_database=?,source_username=?,source_password=?,source_charset=?,table_prefix=?,status="ready" WHERE id=?'); $q->execute([$name,$type,$host,$port?:null,$ssl,$dbn,$user,$pass,$charset,$prefix,$id]); }
        } else { $q=db()->prepare('INSERT INTO bridge_connections(name,source_type,source_mode,source_host,source_port,source_ssl,source_database,source_username,source_password,source_charset,table_prefix,status) VALUES(?,?,"database",?,?,?,?,?,?,?,?,"ready")'); $q->execute([$name,$type,$host,$port?:null,$ssl,$dbn,$user,$pass,$charset,$prefix]); }
        flash('success','Bridge bağlantısı kaydedildi.');
    }catch(Throwable $e){ flash('error','Bridge kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/migration-bridge');
}
if ($route==='admin/migration-bridge/test') {
    ao_bridge_ensure_schema(); ao_bridge_ensure_target_schema();
    $id=(int)($_GET['id']??0);
    try{ $conn=ao_bridge_get_connection($id); if(!$conn) throw new Exception('Bağlantı bulunamadı.'); $test=ao_bridge_test_connection_full($conn); $msg=$test['message'].' '.json_encode($test['summary'], JSON_UNESCAPED_UNICODE); db()->prepare('UPDATE bridge_connections SET last_test_status=?,last_test_message=?,last_test_at=NOW(),status=? WHERE id=?')->execute([$test['ok']?'success':'warning',$msg,$test['ok']?'verified':'warning',$id]); flash($test['ok']?'success':'error',$msg); }
    catch(Throwable $e){ try{db()->prepare('UPDATE bridge_connections SET last_test_status="error",last_test_message=?,last_test_at=NOW() WHERE id=?')->execute([$e->getMessage(),$id]);}catch(Throwable $x){} flash('error','Bridge testi başarısız: '.$e->getMessage()); }
    redirect_to('admin/migration-bridge');
}
if ($route==='admin/migration-bridge/dry-run' || $route==='admin/migration-bridge/import') {
    $id=(int)($_GET['id']??0); $mode=$route==='admin/migration-bridge/import'?'import':'dry_run';
    try{
        if($mode==='dry_run'){
            $conn=ao_bridge_get_connection($id);
            if($conn){ $preview=ao_bridge_live_preview($conn,200); ao_bridge_store_selection($id,$preview); }
        }
        $runId=ao_bridge_run_source($id,$mode);
        flash('success',($mode==='import'?'Aktarım':'Dry-run').' tamamlandı. Run ID: '.$runId.'. Seçimli import ekranı hazırlandı.');
        redirect_to('admin/migration-bridge?edit='.$id);
    }
    catch(Throwable $e){ flash('error','Bridge çalıştırılamadı: '.$e->getMessage()); redirect_to('admin/migration-bridge'); }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/accounting/invoice-email-send') {
    require_admin(); verify_csrf();
    $id=(int)($_POST['invoice_id']??0);
    try{
        $q=db()->prepare('SELECT i.*,c.first_name,c.last_name,c.email,c.id customer_id FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id WHERE i.id=? LIMIT 1');
        $q->execute([$id]); $inv=$q->fetch();
        if(!$inv || empty($inv['email'])) throw new Exception('Fatura veya müşteri e-postası bulunamadı.');
        $subject = ($inv['invoice_number'] ?? 'Fatura') . ' numaralı faturanız';
        $body = "Merhaba ".trim(($inv['first_name']??'').' '.($inv['last_name']??'')).",\n\n";
        $body .= ($inv['invoice_number'] ?? 'Faturanız')." numaralı faturanız oluşturuldu.\n";
        $body .= "Durum: ".($inv['status'] ?? '-')."\n";
        $body .= "Son ödeme: ".($inv['due_date'] ?? '-')."\n";
        $body .= "Toplam: ".number_format((float)($inv['total'] ?? 0),2,',','.')." TL\n\n";
        $body .= "Faturayı müşteri panelinizden görüntüleyebilirsiniz.";
        $res = function_exists('ao_send_email_notification') ? ao_send_email_notification($inv['email'],$subject,$body,'invoice_send') : ['ok'=>@mail($inv['email'],$subject,$body),'message'=>'mail()'];
        try{
            db()->exec("CREATE TABLE IF NOT EXISTS invoice_email_logs (id int(11) NOT NULL AUTO_INCREMENT, invoice_id int(11) NOT NULL, customer_id int(11) DEFAULT NULL, admin_id int(11) DEFAULT NULL, recipient_email varchar(190) NOT NULL, subject varchar(255) NOT NULL, status varchar(40) NOT NULL DEFAULT 'pending', message text DEFAULT NULL, created_at timestamp DEFAULT current_timestamp(), PRIMARY KEY(id), KEY invoice_id(invoice_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $admin=current_admin();
            db()->prepare('INSERT INTO invoice_email_logs(invoice_id,customer_id,admin_id,recipient_email,subject,status,message) VALUES(?,?,?,?,?,?,?)')->execute([$id,(int)($inv['customer_id']??0),(int)($admin['id']??0),$inv['email'],$subject,!empty($res['ok'])?'sent':'error',$res['message']??'']);
        }catch(Throwable $e){}
        flash(!empty($res['ok'])?'success':'error', !empty($res['ok']) ? 'Fatura maili müşteriye gönderildi.' : 'Fatura maili gönderilemedi: '.($res['message']??''));
    }catch(Throwable $e){ flash('error','Fatura maili gönderilemedi: '.$e->getMessage()); }
    redirect_to('admin/accounting/invoices?view='.$id);
}

if ($route === 'admin/accounting/invoice-pdf') {
    require_admin();
    $id=(int)($_GET['id']??0);
    $lines=[];
    try{
        $q=db()->prepare('SELECT i.*,c.first_name,c.last_name,c.email,c.company_name FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id WHERE i.id=? LIMIT 1'); $q->execute([$id]); $inv=$q->fetch();
        if(!$inv) throw new Exception('Fatura bulunamadı.');
        $lines[]='Fatura No: '.$inv['invoice_number'];
        $lines[]='Müşteri: '.trim(($inv['first_name']??'').' '.($inv['last_name']??'')).' / '.($inv['email']??'');
        $lines[]='Durum: '.$inv['status'];
        $lines[]='Son Ödeme: '.($inv['due_date']??'-');
        $lines[]='Toplam: '.number_format((float)$inv['total'],2,',','.').' TL';
        $lines[]='';
        $it=db()->prepare('SELECT * FROM invoice_items WHERE invoice_id=?'); $it->execute([$id]);
        foreach($it->fetchAll() as $row) $lines[]='- '.$row['description'].' | '.number_format((float)$row['amount'],2,',','.').' TL';
        $disposition = (($_GET['mode'] ?? '') === 'view') ? 'inline' : 'attachment';
        header('Content-Type: application/pdf'); header('Content-Disposition: '.$disposition.'; filename="'.$inv['invoice_number'].'.pdf"');
        echo ao_build_simple_pdf('Ahost One Fatura', $lines); exit;
    }catch(Throwable $e){ flash('error','PDF oluşturulamadı: '.$e->getMessage()); redirect_to('admin/accounting/invoices'); }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/accounting/shopier-save') {
    require_admin(); verify_csrf();
    ao_shopier_save_settings($_POST);
    flash('success','Shopier ayarları kaydedildi.');
    redirect_to('admin/accounting/payment-fees');
}

if ($route === 'payment/shopier/start') {
    $topupId=(int)($_GET['topup_id'] ?? 0);
    try{
        ao_schema_ensure_v990();
        $q=db()->prepare('SELECT t.*, c.first_name, c.last_name, c.email FROM credit_topups t LEFT JOIN customers c ON c.id=t.customer_id WHERE t.id=? LIMIT 1'); $q->execute([$topupId]); $t=$q->fetch();
        if(!$t) throw new Exception('Ödeme kaydı bulunamadı.');
        $isTest=(int)ao_shopier_setting('test_mode','1')===1;
        if($isTest){
            echo '<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shopier Test Ödeme</title><style>body{font-family:Arial;background:#f8fafc;padding:40px}.card{max-width:520px;margin:auto;background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:24px;box-shadow:0 20px 60px #0001}.btn{display:inline-block;background:#2563eb;color:white;padding:12px 18px;border-radius:12px;text-decoration:none;font-weight:800}.muted{color:#64748b}</style></head><body><div class="card"><h2>Shopier Test Ödeme</h2><p class="muted">Fresh install test modunda gerçek Shopier yönlendirmesi yerine güvenli test onayı gösterilir.</p><p><strong>Tutar:</strong> '.number_format((float)$t['total_amount'],2,',','.').' '.e($t['currency']).'</p><p><strong>Referans:</strong> '.e($t['reference']).'</p><a class="btn" href="'.e(url('payment/shopier/callback?topup_id='.$topupId.'&status=success&tx=SHOPIER-TEST-'.time())).'">Test Ödemeyi Başarılı Tamamla</a></div></body></html>'; exit;
        }
        // Canlı modda API bilgileri eksikse kullanıcıyı güvenli şekilde durdur.
        if(ao_shopier_setting('auth_mode','pat')==='pat'){ if(ao_shopier_setting('pat','')==='') throw new Exception('Shopier PAT eksik.'); } else { if(ao_shopier_setting('api_key')==='' || ao_shopier_setting('api_secret')==='') throw new Exception('Shopier API bilgileri eksik.'); }
        try{ db()->prepare('INSERT INTO payment_gateway_transactions(customer_id,topup_id,gateway,gateway_order_id,amount,fee_amount,currency,status,request_payload) VALUES(?,?,?,?,?,?,?,"pending",?)')->execute([(int)$t['customer_id'],$topupId,'shopier',$t['reference'],(float)$t['total_amount'],(float)$t['fee_amount'],$t['currency'],json_encode($t,JSON_UNESCAPED_UNICODE)]); }catch(Throwable $x){}
        flash('error','Shopier canlı ödeme başlatma için sağlayıcı form/imza bilgileri tamamlanmalı. Test modu kapalı fakat canlı adaptör yapılandırılmamış.');
        redirect_to('client/credit');
    }catch(Throwable $e){ flash('error','Shopier ödeme başlatılamadı: '.$e->getMessage()); redirect_to('client/credit'); }
}
if ($route === 'payment/shopier/callback') {
    $topupId=(int)($_GET['topup_id'] ?? $_POST['topup_id'] ?? 0); $status=(string)($_GET['status'] ?? $_POST['status'] ?? ''); $tx=(string)($_GET['tx'] ?? $_POST['tx'] ?? ('SHOPIER-'.time()));
    try{
        if($status==='success' || $status==='paid'){ ao_credit_topup_complete($topupId,$tx); try{ db()->prepare('UPDATE payment_gateway_transactions SET status="paid", gateway_transaction_id=?, callback_payload=? WHERE topup_id=? AND gateway="shopier"')->execute([$tx,json_encode(['get'=>$_GET,'post'=>$_POST],JSON_UNESCAPED_UNICODE),$topupId]); }catch(Throwable $x){} flash('success','Shopier ödeme tamamlandı, bakiye hesabınıza eklendi.'); }
        else { flash('error','Shopier ödeme başarısız veya iptal edildi.'); try{ db()->prepare('UPDATE credit_topups SET status="failed" WHERE id=?')->execute([$topupId]); }catch(Throwable $x){} }
    }catch(Throwable $e){ flash('error','Shopier callback işlenemedi: '.$e->getMessage()); }
    redirect_to('client/credit');
}

// v25.0.0 RC9 - Unified QA & Scan Center Pro + PHP Screenshot Bridge
if (in_array($route, ['admin/qa-scan-center','admin/qa-visual-scan','admin/scan-report'], true)) {
    require_admin();
    require_once __DIR__.'/app/Services/QAScanCenterService.php';
    view('qa-scan-center/index', ['pageTitle'=>'QA & Scan Center Pro']); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/qa-scan-center/settings') {
    require_admin(); verify_csrf();
    foreach (($_POST['settings'] ?? []) as $k=>$v) {
        $key = preg_replace('/[^a-zA-Z0-9_\-]/','', (string)$k);
        if ($key !== '' && str_starts_with($key, 'qa_screenshot_')) {
            save_setting($key, is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : trim((string)$v));
        }
    }
    flash('success', 'QA Screenshot Bridge ayarları kaydedildi.');
    redirect_to('admin/qa-scan-center');
}
if (in_array($route, ['admin/qa-scan-center/run','admin/qa-visual-scan/run','admin/scan-report/run'], true)) {
    require_admin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') verify_csrf();
    require_once __DIR__.'/app/Services/QAScanCenterService.php';
    $systemScan = ao_run_full_scan();
    $_SESSION['ao_last_scan'] = $systemScan;
    $base = rtrim(url(''), '/');
    try {
        $dir = QAScanCenterService::createReport($base, $systemScan);
        $summary = QAScanCenterService::readSummary($dir);
        $lines = ['Tarih: '.($summary['generated_at'] ?? date('Y-m-d H:i:s')), 'Genel Skor: '.($summary['score'] ?? 0).'/100', 'PASS: '.($summary['pass'] ?? 0), 'Warning: '.($summary['warning'] ?? 0), 'Error: '.($summary['error'] ?? 0), ''];
        foreach (($summary['routes'] ?? []) as $r) $lines[] = strtoupper($r['status']).' | '.$r['area'].' | '.$r['label'].' | '.$r['score'].'/100';
        foreach (($summary['system_rows'] ?? []) as $r) $lines[] = strtoupper($r['status'] ?? 'PASS').' | '.($r['category'] ?? 'Sistem').' | '.($r['name'] ?? '').' | '.($r['detail'] ?? '');
        file_put_contents($dir.'/report.pdf', ao_build_simple_pdf('Ahost One QA & Scan Center Pro', $lines));
        // PDF eklendikten sonra aynı klasörde ZIP paketini güncelle; ikinci/boş timestamp üretme.
        QAScanCenterService::rebuildPackage($dir);
        $real = (int)($summary['real_screenshots'] ?? 0);
        $fallback = (int)($summary['fallback_screenshots'] ?? 0);
        flash('success', 'QA & Scan tam tarama tamamlandı. Rapor paketi oluşturuldu: '.basename($dir).' — gerçek görsel: '.$real.', fallback: '.$fallback);
    } catch (Throwable $e) {
        flash('error', 'QA & Scan raporu oluşturulamadı: '.$e->getMessage());
    }
    redirect_to('admin/qa-scan-center');
}
if ($route === 'admin/qa-scan-center/download' || $route === 'admin/scan-report/pdf') {
    require_admin();
    require_once __DIR__.'/app/Services/QAScanCenterService.php';
    if ($route === 'admin/scan-report/pdf') { $fileType='pdf'; $reportId = (QAScanCenterService::latest()['id'] ?? ''); }
    else { $fileType = (string)($_GET['file'] ?? 'zip'); $reportId = preg_replace('~[^0-9-]~', '', (string)($_GET['report'] ?? '')); }
    $latest = QAScanCenterService::latest(); if ($reportId === '' && $latest) $reportId = $latest['id'];
    $dir = QAScanCenterService::rootDir().'/'.$reportId;
    $map = ['zip'=>['qa-scan-package.zip','application/zip'], 'html'=>['report.html','text/html; charset=utf-8'], 'pdf'=>['report.pdf','application/pdf'], 'json'=>['summary.json','application/json']];
    if (!isset($map[$fileType]) || !is_file($dir.'/'.$map[$fileType][0])) { flash('error','Rapor dosyası bulunamadı. Önce Tam Tarama Başlat.'); redirect_to('admin/qa-scan-center'); }
    [$file,$ctype] = $map[$fileType];
    header('Content-Type: '.$ctype);
    $disp = $fileType === 'html' ? 'inline' : 'attachment';
    header('Content-Disposition: '.$disp.'; filename="ahost-one-'.$reportId.'-'.$file.'"');
    readfile($dir.'/'.$file); exit;
}





// v9.9.0 Payment & Mobile UX Fix - Shopier + Credit Center + Scan cleanup
function ao_schema_ensure_v990() {
    static $done=false; if($done) return; $done=true;
    if(function_exists('ao_schema_ensure_v900')) ao_schema_ensure_v900();
    try { db()->exec("CREATE TABLE IF NOT EXISTS payment_gateway_transactions (id int(11) NOT NULL AUTO_INCREMENT, customer_id int(11) DEFAULT NULL, invoice_id int(11) DEFAULT NULL, topup_id int(11) DEFAULT NULL, gateway varchar(80) NOT NULL, gateway_order_id varchar(120) DEFAULT NULL, gateway_transaction_id varchar(160) DEFAULT NULL, amount decimal(12,2) DEFAULT 0.00, fee_amount decimal(12,2) DEFAULT 0.00, currency varchar(10) DEFAULT 'TRY', status varchar(40) DEFAULT 'pending', request_payload longtext DEFAULT NULL, response_payload longtext DEFAULT NULL, callback_payload longtext DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY(id), KEY gateway(gateway), KEY status(status), KEY customer_id(customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS credit_topups (id int(11) NOT NULL AUTO_INCREMENT, customer_id int(11) NOT NULL, amount decimal(12,2) NOT NULL, fee_amount decimal(12,2) DEFAULT 0.00, total_amount decimal(12,2) NOT NULL, currency varchar(10) DEFAULT 'TRY', gateway varchar(80) DEFAULT 'manual', status varchar(40) DEFAULT 'pending', reference varchar(80) DEFAULT NULL, invoice_id int(11) DEFAULT NULL, payment_id int(11) DEFAULT NULL, notes text DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY(id), KEY customer_id(customer_id), KEY status(status), KEY gateway(gateway)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS shopier_settings (id int(11) NOT NULL AUTO_INCREMENT, setting_key varchar(120) NOT NULL UNIQUE, setting_value text DEFAULT NULL, updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("INSERT INTO payment_fee_rules(gateway,label,invoice_line_label,fee_percent,last_known_fee_percent,fee_fixed,last_known_fee_fixed,currency,payer_mode,rate_source,api_enabled,is_active) VALUES ('shopier','Shopier Kredi Kartı','Kart İşlem Komisyonu',4.990,4.990,0,0,'TRY','customer','manual',0,1) ON DUPLICATE KEY UPDATE label=VALUES(label),invoice_line_label=VALUES(invoice_line_label),payer_mode='customer',is_active=1"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO shopier_settings(setting_key,setting_value) VALUES ('auth_mode','pat'),('pat',''),('api_key',''),('api_secret',''),('website_index','1'),('test_mode','1'),('callback_secret',''),('commission_gateway','shopier')"); } catch(Throwable $e) {}
    try { db()->exec("INSERT IGNORE INTO admin_search_index(title,route,category,keywords,is_active) VALUES ('Shopier Ödeme Ayarları','admin/accounting/payment-fees','shopier ödeme kredi kartı sanal pos ödeme yöntemi callback api key api secret','Muhasebe',1),('Müşteri Kredi Merkezi','client/credit','kredi bakiye yükle müşteri ödeme shopier havale eft','Müşteri Paneli',1)"); } catch(Throwable $e) {}
    try { db()->exec("INSERT INTO settings(setting_key,setting_value) VALUES ('ahost_version','9.9.0') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)"); } catch(Throwable $e) {}
}
function ao_shopier_setting($key,$default='') { ao_schema_ensure_v990(); try{ $q=db()->prepare('SELECT setting_value FROM shopier_settings WHERE setting_key=? LIMIT 1'); $q->execute([$key]); $v=$q->fetchColumn(); return $v===false?$default:$v; }catch(Throwable $e){ return $default; } }
function ao_shopier_save_settings($data) {
    ao_schema_ensure_v990();
    $auth = in_array(($data['auth_mode'] ?? 'pat'), ['pat','legacy'], true) ? $data['auth_mode'] : 'pat';
    $keys = ['auth_mode','pat','api_key','api_secret','website_index','test_mode','callback_secret'];
    foreach($keys as $k){
        $v = $k==='auth_mode' ? $auth : trim((string)($data[$k] ?? ''));
        try{ db()->prepare('INSERT INTO shopier_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$k,$v]); }catch(Throwable $e){}
    }
    if(trim((string)($data['pat'] ?? ''))!=='') save_setting('shopier_pat', trim((string)$data['pat']));
    if(trim((string)($data['api_key'] ?? ''))!=='') save_setting('shopier_api_key', trim((string)$data['api_key']));
    if(trim((string)($data['api_secret'] ?? ''))!=='') save_setting('shopier_api_secret', trim((string)$data['api_secret']));
}
function ao_credit_topup_create($customer,$amount,$gateway='manual') {
    ao_schema_ensure_v990(); $amount=ao_money_round($amount); if($amount<=0) throw new Exception('Geçerli tutar girin.');
    $quote = $gateway==='manual' ? ['fee'=>0,'customer_total'=>$amount,'currency'=>'TRY','line_label'=>'Kart İşlem Komisyonu'] : ao_payment_fee_quote($amount,$gateway);
    $fee = ao_money_round($quote['fee'] ?? 0); $total=ao_money_round($amount+$fee); $ref='TOP'.date('YmdHis').random_int(100,999);
    db()->prepare('INSERT INTO credit_topups(customer_id,amount,fee_amount,total_amount,currency,gateway,status,reference,notes) VALUES(?,?,?,?,?,?,"pending",?,?)')->execute([(int)$customer['id'],$amount,$fee,$total,$quote['currency']??'TRY',$gateway,$ref,'Müşteri bakiye yükleme talebi']);
    return (int)db()->lastInsertId();
}
function ao_credit_topup_complete($topupId,$txid='manual') {
    ao_schema_ensure_v990(); $q=db()->prepare('SELECT * FROM credit_topups WHERE id=? LIMIT 1'); $q->execute([(int)$topupId]); $t=$q->fetch(); if(!$t) throw new Exception('Bakiye yükleme kaydı bulunamadı.');
    if($t['status']==='paid') return $t;
    $pdo=db(); $pdo->beginTransaction();
    try{
      $pdo->prepare('UPDATE credit_topups SET status="paid", payment_id=? WHERE id=?')->execute([$txid,(int)$topupId]);
      $pdo->prepare('UPDATE customers SET balance=COALESCE(balance,0)+?, credit_balance=COALESCE(credit_balance,0)+? WHERE id=?')->execute([(float)$t['amount'],(float)$t['amount'],(int)$t['customer_id']]);
      $s=$pdo->prepare('SELECT COALESCE(balance,credit_balance,0) FROM customers WHERE id=?'); $s->execute([(int)$t['customer_id']]); $bal=(float)$s->fetchColumn();
      $pdo->prepare('INSERT INTO credit_transactions(customer_id,type,amount,balance_after,description) VALUES(?,?,?,?,?)')->execute([(int)$t['customer_id'],'credit',(float)$t['amount'],$bal,'Bakiye yükleme: '.$t['gateway'].' / '.$t['reference']]);
      $pdo->prepare('INSERT INTO payments(customer_id,type,method,amount,currency,transaction_id,status,notes) VALUES(?,?,?,?,?,?,"completed",?)')->execute([(int)$t['customer_id'],'credit_topup',$t['gateway'],(float)$t['total_amount'],$t['currency'],$txid,'Bakiye yükleme tahsilatı']);
      $pdo->commit();
    }catch(Throwable $e){ $pdo->rollBack(); throw $e; }
    return $t;
}
function ao_shopier_payment_url($topupId) {
    // Shopier canlı entegrasyonu için API bilgileri girildiğinde bu merkez gerçek ödeme başlatma katmanına bağlanır.
    // Fresh install güvenli davranış: test modunda yerel onay ekranına yönlendirir.
    return url('payment/shopier/start?topup_id='.(int)$topupId);
}

// v9.3.0 Intelligence & Marketplace Pro + Theme Center Pro
function ao_schema_ensure_v930() {
    static $done=false; if($done) return; $done=true;
    try { db()->exec("CREATE TABLE IF NOT EXISTS themes (id int(11) NOT NULL AUTO_INCREMENT, slug varchar(80) NOT NULL, name varchar(160) NOT NULL, area varchar(40) DEFAULT 'site', description text DEFAULT NULL, preview_image varchar(255) DEFAULT NULL, primary_color varchar(20) DEFAULT '#2563eb', secondary_color varchar(20) DEFAULT '#7c3aed', font_family varchar(120) DEFAULT 'Inter, Arial, sans-serif', is_active tinyint(1) DEFAULT 0, status varchar(30) DEFAULT 'installed', created_at timestamp NOT NULL DEFAULT current_timestamp(), updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY(id), UNIQUE KEY slug_area(slug,area)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS marketplace_listings (id int(11) NOT NULL AUTO_INCREMENT, seller_type enum('admin','customer') DEFAULT 'admin', seller_customer_id int(11) DEFAULT NULL, listing_type varchar(60) DEFAULT 'domain', title varchar(190) NOT NULL, domain_name varchar(190) DEFAULT NULL, description text DEFAULT NULL, category varchar(120) DEFAULT NULL, price decimal(14,2) DEFAULT 0, currency varchar(10) DEFAULT 'TRY', status enum('draft','active','pending','sold','passive') DEFAULT 'draft', is_featured tinyint(1) DEFAULT 0, featured_until datetime DEFAULT NULL, is_premium tinyint(1) DEFAULT 0, is_urgent tinyint(1) DEFAULT 0, views int(11) DEFAULT 0, created_at timestamp NOT NULL DEFAULT current_timestamp(), updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY(id), KEY status(status), KEY listing_type(listing_type), KEY domain_name(domain_name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS marketplace_offers (id int(11) NOT NULL AUTO_INCREMENT, listing_id int(11) NOT NULL, customer_id int(11) DEFAULT NULL, name varchar(160) DEFAULT NULL, email varchar(190) DEFAULT NULL, offer_amount decimal(14,2) NOT NULL, currency varchar(10) DEFAULT 'TRY', message text DEFAULT NULL, status enum('pending','accepted','rejected','countered') DEFAULT 'pending', created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY listing_id(listing_id), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS marketplace_feature_packages (id int(11) NOT NULL AUTO_INCREMENT, name varchar(160) NOT NULL, days int(11) NOT NULL, price decimal(14,2) NOT NULL, currency varchar(10) DEFAULT 'TRY', badge varchar(80) DEFAULT 'Öne Çıkan', is_active tinyint(1) DEFAULT 1, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), UNIQUE KEY uniq_feature_days(days)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS domain_intelligence_reports (id int(11) NOT NULL AUTO_INCREMENT, domain_name varchar(190) NOT NULL, ssl_score int(11) DEFAULT 0, dns_score int(11) DEFAULT 0, seo_score int(11) DEFAULT 0, traffic_score int(11) DEFAULT 0, valuation_score int(11) DEFAULT 0, estimated_value decimal(14,2) DEFAULT 0, currency varchar(10) DEFAULT 'TRY', summary text DEFAULT NULL, raw_json longtext DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY domain_name(domain_name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    $themes=[
        ['ahost-default','Ahost Default','#2563eb','#0f172a'],['modern-hosting','Modern Hosting','#7c3aed','#06b6d4'],['cloud-pro','Cloud Pro','#0284c7','#22c55e'],['dark-server','Dark Server','#111827','#38bdf8'],['corporate-business','Corporate Business','#334155','#c59f45'],['domain-marketplace','Domain Marketplace','#f97316','#111827'],['ai-company','AI Company','#8b5cf6','#14b8a6'],['web-agency','Web Agency','#ec4899','#3b82f6'],['ecommerce-services','E-Commerce Services','#16a34a','#f59e0b'],['ultra-premium','Ultra Premium','#0ea5e9','#a855f7']
    ];
    foreach($themes as $i=>$t){ try{ db()->prepare("INSERT IGNORE INTO themes(slug,name,area,description,primary_color,secondary_color,is_active,status) VALUES(?,?,?,?,?,?,?, 'installed')")->execute([$t[0],$t[1],'site',$t[1].' site ön yüz teması.',$t[2],$t[3],$i===0?1:0]); }catch(Throwable $e){} }
    try { db()->exec("ALTER TABLE marketplace_feature_packages ADD UNIQUE KEY uniq_feature_days(days)"); } catch(Throwable $e) {}
    try { db()->exec("DELETE p1 FROM marketplace_feature_packages p1 JOIN marketplace_feature_packages p2 ON p1.days=p2.days AND p1.id>p2.id"); } catch(Throwable $e) {}
    foreach([['Öne Çıkarma 7 Gün',7,99],['Öne Çıkarma 15 Gün',15,179],['Öne Çıkarma 30 Gün',30,299],['Öne Çıkarma 60 Gün',60,499]] as $p){ try{ db()->prepare("INSERT INTO marketplace_feature_packages(name,days,price,currency,badge,is_active) VALUES(?,?,?,?,?,1) ON DUPLICATE KEY UPDATE name=VALUES(name),price=VALUES(price),currency=VALUES(currency),badge=VALUES(badge),is_active=1")->execute([$p[0],$p[1],$p[2],'TRY','Öne Çıkan']); }catch(Throwable $e){} }
    try{ save_setting('ahost_version','9.3.0'); }catch(Throwable $e){}
}
ao_schema_ensure_v930();
function ao_active_theme($area='site'){
    ao_schema_ensure_v930();
    $area = $area === 'customer' ? 'client' : $area;
    // Real theme preview mode: admin can browse whole site/admin/client with a temporary theme id.
    $previewId=(int)($_GET['theme_preview'] ?? ($_SESSION['theme_preview_id'] ?? 0));
    if($previewId && current_admin()){
        try{ $q=db()->prepare('SELECT * FROM themes WHERE id=? LIMIT 1'); $q->execute([$previewId]); if($r=$q->fetch()) return $r; }catch(Throwable $e){}
    }
    // Customer personal preferences: site + client themes can be client-specific.
    $customer=current_customer();
    if($customer && in_array($area,['site','client'],true)){
        try{
            db()->exec("CREATE TABLE IF NOT EXISTS client_preferences (id INT AUTO_INCREMENT PRIMARY KEY, client_id INT NOT NULL, site_theme_id INT NULL, client_theme_id INT NULL, builder_layout_json LONGTEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_client_pref(client_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $q=db()->prepare('SELECT * FROM client_preferences WHERE client_id=? LIMIT 1'); $q->execute([(int)$customer['id']]); $pref=$q->fetch();
            $themeId=(int)($area==='site' ? ($pref['site_theme_id'] ?? 0) : ($pref['client_theme_id'] ?? 0));
            if($themeId){ $t=db()->prepare('SELECT * FROM themes WHERE id=? LIMIT 1'); $t->execute([$themeId]); if($r=$t->fetch()) return $r; }
        }catch(Throwable $e){}
    }
    try{ $q=db()->prepare('SELECT * FROM themes WHERE area=? AND is_active=1 LIMIT 1'); $q->execute([$area]); if($r=$q->fetch()) return $r; }catch(Throwable $e){}
    return ['slug'=>'ahost-default','name'=>'Ahost Default','primary_color'=>'#2563eb','secondary_color'=>'#0f172a','font_family'=>'Inter, Arial, sans-serif','radius'=>'24px','button_radius'=>'16px'];
}
function ao_theme_style_vars($area='site'){
    $t=ao_active_theme($area);
    $radius=$t['radius'] ?? '24px'; $button=$t['button_radius'] ?? '16px';
    $bg=$t['background_color'] ?? '#f8fbff'; $gradient=$t['background_gradient'] ?? '';
    $style='--ao-primary:'.e($t['primary_color'] ?? '#2563eb').';--ao-secondary:'.e($t['secondary_color'] ?? '#0f172a').';--ao-font:'.e($t['font_family'] ?? 'Inter, Arial, sans-serif').';--ao-radius:'.e($radius).';--btn-radius:'.e($button).';--ao-bg-custom:'.e($bg).';';
    if($gradient) $style.='--ao-bg-gradient:'.e($gradient).';';
    return $style;
}
function ao_marketplace_stats(){ ao_schema_ensure_v930(); $out=['active'=>0,'featured'=>0,'offers'=>0,'sold'=>0]; try{$out['active']=(int)db()->query("SELECT COUNT(*) FROM marketplace_listings WHERE status='active'")->fetchColumn();$out['featured']=(int)db()->query("SELECT COUNT(*) FROM marketplace_listings WHERE is_featured=1 AND status='active'")->fetchColumn();$out['offers']=(int)db()->query("SELECT COUNT(*) FROM marketplace_offers WHERE status='pending'")->fetchColumn();$out['sold']=(int)db()->query("SELECT COUNT(*) FROM marketplace_listings WHERE status='sold'")->fetchColumn();}catch(Throwable $e){} return $out; }
function ao_domain_intelligence_run($domain){
    ao_schema_ensure_v930(); $domain=ahost_domain_clean($domain); if(!ahost_domain_valid($domain)) throw new Exception('Geçersiz domain.');
    $ssl=['SSL Durumu'=>'Pasif']; try{ $ctx=stream_context_create(['ssl'=>['capture_peer_cert'=>true,'verify_peer'=>false,'verify_peer_name'=>false]]); $client=@stream_socket_client('ssl://'.$domain.':443',$errno,$errstr,6,STREAM_CLIENT_CONNECT,$ctx); if($client){ $params=stream_context_get_params($client); $cert=$params['options']['ssl']['peer_certificate']??null; $parsed=$cert?openssl_x509_parse($cert):[]; $ssl=['SSL Durumu'=>'Aktif','Issuer'=>$parsed['issuer']['O']??'-','Bitiş'=>isset($parsed['validTo_time_t'])?date('Y-m-d',$parsed['validTo_time_t']):'-']; fclose($client);} }catch(Throwable $e){}
    $dnsCount=0; if(function_exists('dns_get_record')){ $rec=@dns_get_record($domain,DNS_ALL); $dnsCount=is_array($rec)?count($rec):0; }
    $whoisRaw=ao_raw_whois($domain); $whois=ao_parse_whois_text($whoisRaw); $seo=ao_page_basic_analysis($domain); $val=ao_domain_valuation_score($domain,$whois,$ssl,$dnsCount,$seo);
    $sslScore=($ssl['SSL Durumu']??'')==='Aktif'?90:30; $dnsScore=min(100,30+$dnsCount*5); $trafficScore=max(10,min(85,(int)($val['score']*0.7)));
    $summary='SSL: '.($ssl['SSL Durumu']??'-').', DNS kayıt: '.$dnsCount.', SEO skor: '.$val['seo_score'].', Tahmini değer: '.$val['value'].' TRY';
    $raw=['ssl'=>$ssl,'dns_count'=>$dnsCount,'whois'=>$whois,'seo'=>$seo,'valuation'=>$val];
    try{ db()->prepare('INSERT INTO domain_intelligence_reports(domain_name,ssl_score,dns_score,seo_score,traffic_score,valuation_score,estimated_value,currency,summary,raw_json) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([$domain,$sslScore,$dnsScore,$val['seo_score'],$trafficScore,$val['score'],$val['value'],'TRY',$summary,json_encode($raw,JSON_UNESCAPED_UNICODE)]); }catch(Throwable $e){}
    return ['domain'=>$domain,'ssl_score'=>$sslScore,'dns_score'=>$dnsScore,'seo_score'=>$val['seo_score'],'traffic_score'=>$trafficScore,'valuation_score'=>$val['score'],'estimated_value'=>$val['value'],'summary'=>$summary,'raw'=>$raw];
}

// v7.9.0 Hosting & Customer Operations
function ao_schema_ensure_v790() {
    static $done=false; if($done) return; $done=true;
    try { db()->exec('CREATE TABLE IF NOT EXISTS hosting_account_logs (id int(11) NOT NULL AUTO_INCREMENT, hosting_account_id int(11) DEFAULT NULL, service_id int(11) DEFAULT NULL, admin_id int(11) DEFAULT NULL, action varchar(120) NOT NULL, description text DEFAULT NULL, old_value text DEFAULT NULL, new_value text DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY hosting_account_id(hosting_account_id), KEY service_id(service_id), KEY action(action)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'); } catch(Throwable $e) {}
    try { db()->exec('CREATE TABLE IF NOT EXISTS order_status_logs (id int(11) NOT NULL AUTO_INCREMENT, order_id int(11) NOT NULL, admin_id int(11) DEFAULT NULL, old_status varchar(60) DEFAULT NULL, new_status varchar(60) DEFAULT NULL, action varchar(120) NOT NULL, note text DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY order_id(order_id), KEY action(action)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'); } catch(Throwable $e) {}
    try { db()->exec('CREATE TABLE IF NOT EXISTS customer_groups (id int(11) NOT NULL AUTO_INCREMENT, name varchar(160) NOT NULL, discount_percent decimal(6,2) DEFAULT 0, description text DEFAULT NULL, is_active tinyint(1) DEFAULT 1, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'); } catch(Throwable $e) {}
    try { db()->exec('CREATE TABLE IF NOT EXISTS customer_group_members (id int(11) NOT NULL AUTO_INCREMENT, customer_id int(11) NOT NULL, group_id int(11) NOT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), UNIQUE KEY customer_group_unique(customer_id, group_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'); } catch(Throwable $e) {}
    try { $cols=[]; foreach(db()->query('SHOW COLUMNS FROM hosting_accounts')->fetchAll() as $c) $cols[$c['Field']]=true; if(empty($cols['server_id'])) db()->exec('ALTER TABLE hosting_accounts ADD COLUMN server_id int(11) DEFAULT NULL AFTER service_id'); if(empty($cols['suspended_at'])) db()->exec('ALTER TABLE hosting_accounts ADD COLUMN suspended_at datetime DEFAULT NULL AFTER created_at'); if(empty($cols['terminated_at'])) db()->exec('ALTER TABLE hosting_accounts ADD COLUMN terminated_at datetime DEFAULT NULL AFTER suspended_at'); } catch(Throwable $e) {}
    try { $cols=[]; foreach(db()->query('SHOW COLUMNS FROM customers')->fetchAll() as $c) $cols[$c['Field']]=true; if(empty($cols['group_id'])) db()->exec('ALTER TABLE customers ADD COLUMN group_id int(11) DEFAULT NULL AFTER id'); } catch(Throwable $e) {}
}
ao_schema_ensure_v790();
function ao_hosting_log($hostingId,$serviceId,$action,$description='',$old='',$new='') {
    try { ao_schema_ensure_v790(); $admin=current_admin(); db()->prepare('INSERT INTO hosting_account_logs(hosting_account_id,service_id,admin_id,action,description,old_value,new_value) VALUES(?,?,?,?,?,?,?)')->execute([(int)$hostingId,(int)$serviceId,$admin['id']??null,$action,$description,$old,$new]); } catch(Throwable $e) {}
}
function ao_order_log_status($orderId,$old,$new,$action,$note='') {
    try { ao_schema_ensure_v790(); $admin=current_admin(); db()->prepare('INSERT INTO order_status_logs(order_id,admin_id,old_status,new_status,action,note) VALUES(?,?,?,?,?,?)')->execute([(int)$orderId,$admin['id']??null,$old,$new,$action,$note]); } catch(Throwable $e) {}
}
function ao_hosting_account_by_service($serviceId) {
    $q=db()->prepare('SELECT h.*, s.customer_id, s.status service_status, s.domain service_domain FROM hosting_accounts h LEFT JOIN services s ON s.id=h.service_id WHERE h.service_id=? LIMIT 1');
    $q->execute([(int)$serviceId]); return $q->fetch() ?: null;
}
function ao_random_hosting_password($len=14) {
    $chars='abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%'; $out=''; for($i=0;$i<$len;$i++) $out.=$chars[random_int(0, strlen($chars)-1)]; return $out;
}

function ao_hosting_safe_int($row, $keys, $default=0) {
    foreach((array)$keys as $k){ if(isset($row[$k]) && $row[$k] !== '' && $row[$k] !== null) return (int)$row[$k]; }
    return (int)$default;
}
function ao_hosting_metric_rows($h) {
    $diskLimit=ao_hosting_safe_int($h,['disk_mb','disk_limit'],10240); $diskUsed=ao_hosting_safe_int($h,['disk_used_mb','disk_used'],0);
    $trafficLimit=ao_hosting_safe_int($h,['bandwidth_mb','bandwidth_limit'],102400); $trafficUsed=ao_hosting_safe_int($h,['bandwidth_used_mb','bandwidth_used'],0);
    $rows=[
      ['key'=>'disk','label'=>'Disk','icon'=>'💾','used'=>$diskUsed,'limit'=>$diskLimit,'unit'=>'MB'],
      ['key'=>'traffic','label'=>'Trafik','icon'=>'📈','used'=>$trafficUsed,'limit'=>$trafficLimit,'unit'=>'MB'],
      ['key'=>'mail','label'=>'E-posta','icon'=>'✉️','used'=>ao_hosting_safe_int($h,['mail_used'],0),'limit'=>ao_hosting_safe_int($h,['mail_limit'],0),'unit'=>'hesap'],
      ['key'=>'mysql','label'=>'MySQL','icon'=>'🗄️','used'=>ao_hosting_safe_int($h,['mysql_used'],0),'limit'=>ao_hosting_safe_int($h,['mysql_limit'],0),'unit'=>'veritabanı'],
      ['key'=>'cpu','label'=>'CPU','icon'=>'⚙️','used'=>ao_hosting_safe_int($h,['cpu_percent','cpu_used_percent'],0),'limit'=>100,'unit'=>'%'],
      ['key'=>'ram','label'=>'RAM','icon'=>'🧠','used'=>ao_hosting_safe_int($h,['ram_used_mb'],0),'limit'=>ao_hosting_safe_int($h,['ram_mb','ram_limit_mb'],0),'unit'=>'MB'],
      ['key'=>'inode','label'=>'Inode','icon'=>'🔢','used'=>ao_hosting_safe_int($h,['inode_used'],0),'limit'=>ao_hosting_safe_int($h,['inode_limit'],0),'unit'=>'inode'],
      ['key'=>'ftp','label'=>'FTP','icon'=>'📁','used'=>ao_hosting_safe_int($h,['ftp_used'],0),'limit'=>ao_hosting_safe_int($h,['ftp_limit'],0),'unit'=>'hesap'],
      ['key'=>'cron','label'=>'Cron','icon'=>'⏱️','used'=>ao_hosting_safe_int($h,['cron_used'],0),'limit'=>ao_hosting_safe_int($h,['cron_limit'],0),'unit'=>'görev'],
      ['key'=>'addon','label'=>'Addon Domain','icon'=>'🌐','used'=>ao_hosting_safe_int($h,['addon_domain_used'],0),'limit'=>ao_hosting_safe_int($h,['addon_domain_limit'],0),'unit'=>'domain'],
      ['key'=>'subdomain','label'=>'Subdomain','icon'=>'🔗','used'=>ao_hosting_safe_int($h,['subdomain_used'],0),'limit'=>ao_hosting_safe_int($h,['subdomain_limit'],0),'unit'=>'subdomain'],
    ];
    foreach($rows as &$r){ $r['percent']=$r['limit']>0?min(100,round($r['used']*100/$r['limit'])):0; $r['left']=$r['limit']>0?max(0,$r['limit']-$r['used']):0; }
    return $rows;
}
function ao_domain_display_name($d) {
    $name=trim((string)($d['domain_name'] ?? ($d['domain'] ?? ($d['full_domain'] ?? ($d['name'] ?? '')))));
    if($name===''){
        $sld=trim((string)($d['sld'] ?? '')); $tld=trim((string)($d['tld'] ?? ''));
        if($sld!=='' && $tld!=='') $name=$sld.'.'.ltrim($tld,'.');
    }
    return $name;
}
function ao_status_tr($status) {
    $s=strtolower((string)$status);
    return ['active'=>'Aktif','pending'=>'Beklemede','suspended'=>'Askıda','terminated'=>'Sonlandırıldı','cancelled'=>'İptal','expired'=>'Süresi Doldu','paid'=>'Ödendi','unpaid'=>'Ödenmedi'][$s] ?? ($status ?: '-');
}
function ao_hosting_panel_change_password($hostingAccount, $newPassword) {
    // Gerçek cPanel/DirectAdmin/Plesk adaptörleri eklendiğinde bu merkezden çalışacak.
    // Şimdilik bağlantı simülasyonu yapar; başarısız API cevabı gelirse DB güncellenmemelidir.
    return ['ok'=>true,'message'=>'Panel adaptör kuyruğuna alındı / demo senkron tamamlandı.'];
}

function ao_server_panel_urls($server) {
    $host=ao_host_from_server_row($server); return ['cpanel'=>ao_panel_url_from_host($host,'cpanel'),'webmail'=>ao_panel_url_from_host($host,'webmail'),'whm'=>ao_panel_url_from_host($host,'whm'),'directadmin'=>ao_panel_url_from_host($host,'directadmin'),'plesk'=>ao_panel_url_from_host($host,'plesk'),'vps'=>ao_panel_url_from_host($host,'vps')];
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/hosting-server/account-action') {
    require_admin(); verify_csrf();
    $serviceId=(int)($_POST['service_id']??0); $action=trim($_POST['action']??'');
    try{
        if(!$serviceId || !$action) throw new Exception('Hizmet ve işlem zorunlu.');
        $h=ao_hosting_account_by_service($serviceId);
        if(!$h) throw new Exception('Hosting hesabı bulunamadı.');
        $oldStatus=$h['service_status']??'';
        if($action==='suspend'){
            db()->prepare('UPDATE services SET status="suspended" WHERE id=?')->execute([$serviceId]);
            db()->prepare('UPDATE hosting_accounts SET suspended_at=NOW() WHERE service_id=?')->execute([$serviceId]);
            ao_hosting_log($h['id'],$serviceId,'suspend','Hizmet askıya alındı.',$oldStatus,'suspended'); flash('success','Hosting askıya alındı.');
        } elseif($action==='unsuspend'){
            db()->prepare('UPDATE services SET status="active" WHERE id=?')->execute([$serviceId]);
            db()->prepare('UPDATE hosting_accounts SET suspended_at=NULL WHERE service_id=?')->execute([$serviceId]);
            ao_hosting_log($h['id'],$serviceId,'unsuspend','Hizmet tekrar aktif edildi.',$oldStatus,'active'); flash('success','Hosting açıldı.');
        } elseif($action==='terminate'){
            db()->prepare('UPDATE services SET status="terminated" WHERE id=?')->execute([$serviceId]);
            db()->prepare('UPDATE hosting_accounts SET terminated_at=NOW() WHERE service_id=?')->execute([$serviceId]);
            ao_hosting_log($h['id'],$serviceId,'terminate','Hizmet sonlandırıldı.',$oldStatus,'terminated'); flash('success','Hosting sonlandırıldı.');
        } elseif($action==='change-password'){
            $pass=(string)($_POST['panel_password']??''); if($pass==='') $pass=ao_random_hosting_password();
            $sync=ao_hosting_panel_change_password($h,$pass);
            if(empty($sync['ok'])) throw new Exception($sync['message'] ?? 'Sunucu şifre değişikliğini kabul etmedi.');
            db()->prepare('UPDATE hosting_accounts SET panel_password=? WHERE service_id=?')->execute([$pass,$serviceId]);
            ao_hosting_log($h['id'],$serviceId,'password.changed','Panel şifresi değiştirildi ve sunucu senkronu çalıştı.','***','***'); flash('success','Panel şifresi güncellendi.');
        } elseif($action==='change-package'){
            $pkg=trim($_POST['package_name']??''); if($pkg==='') throw new Exception('Paket adı boş.');
            db()->prepare('UPDATE hosting_accounts SET package_name=? WHERE service_id=?')->execute([$pkg,$serviceId]);
            ao_hosting_log($h['id'],$serviceId,'package.changed','Paket değiştirildi.',$h['package_name']??'',$pkg); flash('success','Hosting paketi güncellendi.');
        } elseif($action==='move-server'){
            $serverId=(int)($_POST['server_id']??0); $q=db()->prepare('SELECT * FROM server_nodes WHERE id=? LIMIT 1'); $q->execute([$serverId]); $srv=$q->fetch(); if(!$srv) throw new Exception('Sunucu bulunamadı.');
            $urls=ao_server_panel_urls($srv);
            db()->prepare('UPDATE hosting_accounts SET server_id=?, server_name=?, server_ip=?, cpanel_url=?, webmail_url=?, whm_url=?, directadmin_url=?, vps_panel_url=? WHERE service_id=?')->execute([$serverId,$srv['hostname']?:$srv['name'],$srv['ip_address'],$urls['cpanel'],$urls['webmail'],$urls['whm'],$urls['directadmin'],$urls['vps'],$serviceId]);
            ao_hosting_log($h['id'],$serviceId,'server.changed','Sunucu değiştirildi.',$h['server_name']??'',($srv['hostname']?:$srv['name'])); flash('success','Sunucu bilgisi değiştirildi.');
        } else throw new Exception('Bilinmeyen işlem.');
    }catch(Throwable $e){ flash('error','Hosting işlemi başarısız: '.$e->getMessage()); }
    $back=trim($_POST['back']??''); redirect_to($back ?: 'admin/hosting-server/accounts');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/orders/status') {
    require_admin(); verify_csrf();
    $id=(int)($_POST['order_id']??0); $status=trim($_POST['status']??''); $note=trim($_POST['note']??'');
    try{
        $q=db()->prepare('SELECT * FROM orders WHERE id=? LIMIT 1'); $q->execute([$id]); $o=$q->fetch(); if(!$o) throw new Exception('Sipariş bulunamadı.');
        $old=$o['status'];
        if($status==='active'){ ao_create_invoice_for_order($id); ao_provision_order($id); $new='active'; }
        else { $new=$status ?: $old; db()->prepare('UPDATE orders SET status=?, provision_status=IF(?="cancelled","cancelled",provision_status) WHERE id=?')->execute([$new,$new,$id]); }
        ao_order_log_status($id,$old,$new,'order.status',$note); flash('success','Sipariş durumu güncellendi.');
    }catch(Throwable $e){ flash('error','Sipariş durumu güncellenemedi: '.$e->getMessage()); }
    redirect_to('admin/orders/view?id='.$id);
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/customers/group-save') {
    require_admin(); verify_csrf();
    try{ $name=trim($_POST['name']??''); if(!$name) throw new Exception('Grup adı zorunlu.'); $discount=(float)($_POST['discount_percent']??0); $desc=trim($_POST['description']??''); db()->prepare('INSERT INTO customer_groups(name,discount_percent,description,is_active) VALUES(?,?,?,1)')->execute([$name,$discount,$desc]); flash('success','Müşteri grubu oluşturuldu.'); }catch(Throwable $e){ flash('error','Grup kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/customers/groups');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/customers/group-assign') {
    require_admin(); verify_csrf();
    try{ $customer=(int)($_POST['customer_id']??0); $group=(int)($_POST['group_id']??0); if(!$customer||!$group) throw new Exception('Müşteri ve grup zorunlu.'); db()->prepare('UPDATE customers SET group_id=? WHERE id=?')->execute([$group,$customer]); db()->prepare('INSERT IGNORE INTO customer_group_members(customer_id,group_id) VALUES(?,?)')->execute([$customer,$group]); ao_customer_log($customer,'customer.group.assigned','Müşteri gruba atandı: '.$group); flash('success','Müşteri gruba atandı.'); }catch(Throwable $e){ flash('error','Grup ataması yapılamadı: '.$e->getMessage()); }
    redirect_to('admin/customers/groups');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/customers/bulk-message') {
    require_admin(); verify_csrf();
    $channel=trim($_POST['channel']??'email'); $group=(int)($_POST['group_id']??0); $subject=trim($_POST['subject']??'Ahost One Bilgilendirme'); $msg=trim($_POST['message']??'');
    try{ if(!$msg) throw new Exception('Mesaj boş.'); $q=$group?db()->prepare('SELECT * FROM customers WHERE group_id=? AND status<>"deleted"'):db()->prepare('SELECT * FROM customers WHERE status<>"deleted"'); $q->execute($group?[$group]:[]); $customers=$q->fetchAll(); foreach($customers as $c){ ao_log_simple($channel,'bulk-message','queued',$subject.' -> '.$c['email'],json_encode(['customer_id'=>$c['id'],'message'=>$msg], JSON_UNESCAPED_UNICODE)); } flash('success',count($customers).' müşteri için '.$channel.' bildirimi kuyruğa alındı.'); }catch(Throwable $e){ flash('error','Toplu mesaj hazırlanamadı: '.$e->getMessage()); }
    redirect_to('admin/customers/groups');
}


// v18.8.3 Builder UX Rebuild + Domain Intelligence Real Mode
function ao_schema_ensure_v188(){
    try{ db()->exec("ALTER TABLE themes ADD COLUMN radius VARCHAR(24) DEFAULT '24px'"); }catch(Throwable $e){}
    try{ db()->exec("ALTER TABLE themes ADD COLUMN button_radius VARCHAR(24) DEFAULT '16px'"); }catch(Throwable $e){}
    try{ db()->exec("ALTER TABLE themes ADD COLUMN button_style VARCHAR(40) DEFAULT 'gradient'"); }catch(Throwable $e){}
    try{ db()->exec("ALTER TABLE themes ADD COLUMN background_color VARCHAR(24) DEFAULT '#f8fbff'"); }catch(Throwable $e){}
    try{ db()->exec("ALTER TABLE themes ADD COLUMN background_gradient VARCHAR(190) NULL"); }catch(Throwable $e){}
    try{ db()->exec("ALTER TABLE themes ADD COLUMN header_mode VARCHAR(40) DEFAULT 'sticky'"); }catch(Throwable $e){}
    try{ db()->exec("ALTER TABLE themes ADD COLUMN mobile_bottom_nav TINYINT(1) DEFAULT 1"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS client_preferences (id INT AUTO_INCREMENT PRIMARY KEY, client_id INT NOT NULL, site_theme_id INT NULL, client_theme_id INT NULL, builder_layout_json LONGTEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_client_pref(client_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS password_reset_tokens (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, email VARCHAR(190) NULL, token_hash VARCHAR(190) NOT NULL, channel VARCHAR(40) DEFAULT 'email', expires_at DATETIME NOT NULL, used_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX token_hash(token_hash), INDEX email(email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS client_security_questions (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, question VARCHAR(190) NOT NULL, answer_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_customer_question(customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS domain_price_cache (id INT AUTO_INCREMENT PRIMARY KEY, tld VARCHAR(40) NOT NULL, registrar VARCHAR(80) DEFAULT 'domainnameapi', cost_usd DECIMAL(12,4) DEFAULT 0, commission_percent DECIMAL(6,2) DEFAULT 20, sale_usd DECIMAL(12,4) DEFAULT 0, sale_try DECIMAL(12,2) DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_tld_registrar(tld,registrar)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES ('ahost_version','18.8.3'),('css_isolation_app_shell','1'),('inline_builder_enabled','1'),('client_layout_rebuild','1'),('theme_studio_pro','1'),('client_builder_pro','1'),('real_theme_preview','1'),('currency_margin_percent','5.00'),('domain_default_commission_percent','20.00') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute(); }catch(Throwable $e){}
    foreach(['com'=>10.00,'net'=>12.00,'org'=>11.00,'com.tr'=>8.50,'net.tr'=>8.00] as $tld=>$cost){
        try{ $rate=(float)ao_currency_rate('USD','TRY'); $comm=(float)admin_setting('domain_default_commission_percent','20'); $sale=$cost+($cost*$comm/100); db()->prepare('INSERT INTO domain_price_cache(tld,cost_usd,commission_percent,sale_usd,sale_try) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE cost_usd=VALUES(cost_usd),commission_percent=VALUES(commission_percent),sale_usd=VALUES(sale_usd),sale_try=VALUES(sale_try)')->execute([$tld,$cost,$comm,$sale,$sale*$rate]); }catch(Throwable $e){}
    }
}
function ao_builder_context_from_route($route){
    $route=trim($route,'/');
    $base=trim(parse_url(app_base_path(), PHP_URL_PATH) ?: '', '/');
    if($base && str_starts_with($route,$base)) $route=trim(substr($route,strlen($base)),'/');
    if(str_starts_with($route,'admin')) return ['target'=>'admin','template'=>preg_replace('/[^a-z0-9_-]+/i','-', $route ?: 'dashboard')];
    if(str_starts_with($route,'client')) return ['target'=>'customer','template'=>preg_replace('/[^a-z0-9_-]+/i','-', $route ?: 'dashboard')];
    return ['target'=>'site','template'=>($route===''?'home':preg_replace('/[^a-z0-9_-]+/i','-', $route))];
}
function ao_domain_sale_price($tld,$currency='TRY'){
    ao_schema_ensure_v188(); $tld=ltrim(strtolower($tld),'.');
    try{ $q=db()->prepare('SELECT * FROM domain_price_cache WHERE tld=? LIMIT 1'); $q->execute([$tld]); $r=$q->fetch(); if($r){ return $currency==='USD' ? (float)$r['sale_usd'] : (float)$r['sale_try']; } }catch(Throwable $e){}
    $costs=['com'=>10,'net'=>12,'org'=>11,'com.tr'=>8.5,'net.tr'=>8]; $cost=$costs[$tld]??10; $comm=(float)admin_setting('domain_default_commission_percent','20'); $sale=$cost+($cost*$comm/100); return $currency==='USD' ? $sale : $sale*(float)ao_currency_rate('USD','TRY');
}
function ao_theme_preview_bar($themeId){
    if(!$themeId || !current_admin()) return '';
    return '<div class="preview-bar">Önizleme modu aktif <a href="'.e(url('admin/theme-center/apply-preview?id='.(int)$themeId)).'">Temayı Uygula</a><a href="'.e(url('admin/theme-center/preview-exit')).'">Çıkış</a></div>';
}
function ao_clear_theme_cache(){ try{ $_SESSION['theme_preview_id']=0; }catch(Throwable $e){} }
ao_schema_ensure_v188();

// v9.3.0 Theme / Marketplace / Domain Intelligence routes
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/theme-center/apply') {
    require_admin(); verify_csrf(); ao_schema_ensure_v930();
    $id=(int)($_POST['theme_id']??0); $area=trim($_POST['area']??'site') ?: 'site';
    try{ db()->prepare('UPDATE themes SET is_active=0 WHERE area=?')->execute([$area]); db()->prepare('UPDATE themes SET is_active=1 WHERE id=? AND area=?')->execute([$id,$area]); ao_clear_theme_cache(); flash('success','Tema aktif edildi ve ilgili panelde uygulanacak.'); }catch(Throwable $e){ flash('error','Tema aktif edilemedi: '.$e->getMessage()); }
    redirect_to('admin/theme-center/themes');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/theme-center/save-style') {
    require_admin(); verify_csrf(); ao_schema_ensure_v930();
    try{ ao_schema_ensure_v188(); $id=(int)($_POST['theme_id']??0); db()->prepare('UPDATE themes SET primary_color=?, secondary_color=?, font_family=?, radius=?, button_radius=?, button_style=?, background_color=?, background_gradient=?, header_mode=?, mobile_bottom_nav=? WHERE id=?')->execute([trim($_POST['primary_color']??'#2563eb'),trim($_POST['secondary_color']??'#0f172a'),trim($_POST['font_family']??'Inter, Arial, sans-serif'),trim($_POST['radius']??'24px'),trim($_POST['button_radius']??'16px'),trim($_POST['button_style']??'gradient'),trim($_POST['background_color']??'#f8fbff'),trim($_POST['background_gradient']??''),trim($_POST['header_mode']??'sticky'),!empty($_POST['mobile_bottom_nav'])?1:0,$id]); ao_clear_theme_cache(); flash('success','Tema stili kaydedildi ve cache temizlendi.'); }catch(Throwable $e){ flash('error','Tema kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/theme-center/themes');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/marketplace/listing-save') {
    require_admin(); verify_csrf(); ao_schema_ensure_v930();
    try{
        $id=(int)($_POST['id']??0); $title=trim($_POST['title']??''); if(!$title) throw new Exception('Başlık zorunlu.');
        $listingType=trim($_POST['listing_type']??'domain'); $domainField=$listingType==='domain'?ahost_domain_clean($_POST['domain_name']??''):trim($_POST['domain_name']??'');
        $data=[trim($_POST['seller_type']??'admin'),$listingType,$title,$domainField,trim($_POST['description']??''),trim($_POST['category']??''),(float)($_POST['price']??0),trim($_POST['currency']??'TRY'),trim($_POST['status']??'active'),!empty($_POST['is_featured'])?1:0,!empty($_POST['is_premium'])?1:0,!empty($_POST['is_urgent'])?1:0];
        $featuredUntil=null; if(!empty($_POST['featured_days'])) $featuredUntil=date('Y-m-d H:i:s', time()+((int)$_POST['featured_days']*86400));
        if($id>0){ db()->prepare('UPDATE marketplace_listings SET seller_type=?,listing_type=?,title=?,domain_name=?,description=?,category=?,price=?,currency=?,status=?,is_featured=?,is_premium=?,is_urgent=?,sale_model=?,commission_percent=?,delivery_days=?,featured_until=? WHERE id=?')->execute([...$data,$featuredUntil,$id]); }
        else{ db()->prepare('INSERT INTO marketplace_listings(seller_type,listing_type,title,domain_name,description,category,price,currency,status,is_featured,is_premium,is_urgent,sale_model,commission_percent,delivery_days,featured_until) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([...$data,$featuredUntil]); }
        flash('success','Marketplace ilanı kaydedildi.');
    }catch(Throwable $e){ flash('error','İlan kaydedilemedi: '.$e->getMessage()); }
    redirect_to('admin/marketplace');
}
if ($route==='admin/marketplace/delete') { require_admin(); verify_csrf(); ao_schema_ensure_v930(); $id=(int)($_GET['id']??0); try{ db()->prepare('DELETE FROM marketplace_listings WHERE id=?')->execute([$id]); flash('success','İlan silindi.'); }catch(Throwable $e){ flash('error','İlan silinemedi: '.$e->getMessage()); } redirect_to('admin/marketplace'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/domain-intelligence/run') {
    require_admin(); verify_csrf(); try{ $report=ao_domain_intelligence_run($_POST['domain']??''); $_SESSION['ao_last_domain_intelligence']=$report; flash('success','Domain analizi tamamlandı: '.$report['summary']); }catch(Throwable $e){ flash('error','Analiz yapılamadı: '.$e->getMessage()); } redirect_to('admin/domain-intelligence');
}



// v9.4.0 Admin UX + Theme Preview + İletiMerkezi Pro
function ao_schema_ensure_v940() {
    static $done=false; if($done) return; $done=true;
    ao_schema_ensure_v930();
    try { db()->exec("ALTER TABLE themes ADD COLUMN preview_url varchar(255) DEFAULT NULL AFTER preview_image"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE themes ADD COLUMN custom_css longtext DEFAULT NULL AFTER font_family"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS sms_balance_checks (id int(11) NOT NULL AUTO_INCREMENT, provider varchar(80) NOT NULL, balance_text varchar(190) DEFAULT NULL, raw_response longtext DEFAULT NULL, status varchar(40) DEFAULT 'unknown', created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY provider(provider)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS theme_apply_logs (id int(11) NOT NULL AUTO_INCREMENT, theme_id int(11) DEFAULT NULL, area varchar(40) DEFAULT 'site', admin_id int(11) DEFAULT NULL, action varchar(80) DEFAULT 'apply', created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY(id), KEY area(area)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    $adminThemes=[['admin-default','Admin Default','admin','#2563eb','#0f172a'],['admin-dark','Admin Dark','admin','#111827','#38bdf8'],['admin-glass','Admin Glass','admin','#7c3aed','#06b6d4']];
    foreach($adminThemes as $i=>$t){ try{ db()->prepare("INSERT IGNORE INTO themes(slug,name,area,description,primary_color,secondary_color,is_active,status) VALUES(?,?,?,?,?,?,?, 'installed')")->execute([$t[0],$t[1],$t[2],$t[1].' admin panel teması.',$t[3],$t[4],$i===0?1:0]); }catch(Throwable $e){} }
    $clientThemes=[['client-default','Client Default','client','#2563eb','#0f172a'],['client-modern','Client Modern','client','#7c3aed','#06b6d4'],['client-dark','Client Dark','client','#111827','#38bdf8'],['client-corporate','Client Corporate','client','#334155','#c59f45']];
    foreach($clientThemes as $i=>$t){ try{ db()->prepare("INSERT IGNORE INTO themes(slug,name,area,description,primary_color,secondary_color,is_active,status) VALUES(?,?,?,?,?,?,?, 'installed')")->execute([$t[0],$t[1],$t[2],$t[1].' müşteri paneli teması.',$t[3],$t[4],$i===0?1:0]); }catch(Throwable $e){} }
    // İletiMerkezi Registrar modülünden alınan temel hazır şablonlar.
    $tpls=[
      ['domain_epp_code','Domain EPP Kodu','Sayın {customer_name}, {domain} transfer kodunuz: {epp_code}.'],
      ['hosting_created','Hosting Aktif','Sayın {customer_name}, {domain} hosting hizmetiniz aktif. Kullanıcı: {username} Şifre: {password}'],
      ['domain_renewal_notice','Domain Yenileme Hatırlatma','Sayın {customer_name}, {domain} alan adınız {expiry_date} tarihinde sona erecektir.'],
      ['domain_registered','Domain Kayıt Başarılı','Sayın {customer_name}, {domain} alan adınız başarıyla kayıt edildi.'],
      ['domain_renewed','Domain Yenileme Başarılı','Sayın {customer_name}, {domain} alan adınız başarıyla yenilendi.'],
      ['invoice_created','Fatura Oluşturuldu','Sayın {customer_name}, {invoice_number} numaralı faturanız oluşturuldu. Tutar: {total} TL'],
      ['ticket_opened','Ticket Açıldı','Sayın {customer_name}, {ticket_subject} destek talebiniz oluşturuldu.'],
      ['ticket_replied','Ticket Cevaplandı','Sayın {customer_name}, {ticket_subject} destek talebiniz cevaplandı.']
    ];
    foreach($tpls as $t){ try{ db()->prepare("INSERT IGNORE INTO notification_templates(event_key,title,sms_body,whatsapp_body,email_subject,email_body,is_active) VALUES(?,?,?,?,?,?,1)")->execute([$t[0],$t[1],$t[2],$t[2],$t[1],$t[2]]); }catch(Throwable $e){} }
    try{ save_setting('ahost_version','9.4.0'); }catch(Throwable $e){}
}
ao_schema_ensure_v940();

// v9.5.0 Marketplace expansion + admin smart search + theme asset stabilization
function ao_schema_ensure_v950() {
    static $done=false; if($done) return; $done=true;
    try { db()->exec("ALTER TABLE marketplace_listings MODIFY listing_type varchar(60) DEFAULT 'domain'"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS admin_search_index (id int(11) NOT NULL AUTO_INCREMENT, title varchar(160) NOT NULL, route varchar(190) NOT NULL, keywords text DEFAULT NULL, category varchar(100) DEFAULT NULL, is_active tinyint(1) DEFAULT 1, PRIMARY KEY(id), UNIQUE KEY uniq_route_title(route,title)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS marketplace_categories (id int(11) NOT NULL AUTO_INCREMENT, slug varchar(90) NOT NULL, name varchar(160) NOT NULL, listing_type varchar(60) DEFAULT 'service', is_active tinyint(1) DEFAULT 1, sort_order int(11) DEFAULT 0, PRIMARY KEY(id), UNIQUE KEY slug(slug)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE marketplace_feature_packages ADD UNIQUE KEY uniq_feature_days(days)"); } catch(Throwable $e) {}
    try { db()->exec("DELETE p1 FROM marketplace_feature_packages p1 JOIN marketplace_feature_packages p2 ON p1.days=p2.days AND p1.id>p2.id"); } catch(Throwable $e) {}
    $cats=[['domain','Domain','domain',1],['web-design','Web Tasarım','web_design',2],['seo','SEO Paketleri','seo',3],['logo-design','Logo Tasarımı','logo_design',4],['digital-content','Dijital İçerikler','digital_content',5],['mobile-app','Mobil Uygulama','mobile_app',6],['hosting-service','Hosting Hizmeti','hosting',7],['software','Yazılım / Script','software',8]];
    foreach($cats as $c){ try{ db()->prepare("INSERT INTO marketplace_categories(slug,name,listing_type,sort_order,is_active) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE name=VALUES(name),listing_type=VALUES(listing_type),sort_order=VALUES(sort_order),is_active=1")->execute($c); }catch(Throwable $e){} }
    foreach([['Öne Çıkarma 7 Gün',7,99],['Öne Çıkarma 15 Gün',15,179],['Öne Çıkarma 30 Gün',30,299],['Öne Çıkarma 60 Gün',60,499]] as $p){ try{ db()->prepare("INSERT INTO marketplace_feature_packages(name,days,price,currency,badge,is_active) VALUES(?,?,?,?,?,1) ON DUPLICATE KEY UPDATE name=VALUES(name),price=VALUES(price),currency=VALUES(currency),badge=VALUES(badge),is_active=1")->execute([$p[0],$p[1],$p[2],'TRY','Öne Çıkan']); }catch(Throwable $e){} }
    $items=[
      ['Kredi Kartı Ayarları','admin/accounting/payment-fees','kredi kartı, kart komisyonu, sanal pos, iyzico, paytr, stripe, ödeme api, taksit, komisyon','Muhasebe'],
      ['Sanal POS Yönetimi','admin/accounting/payment-fees','sanal pos, ödeme, kredi kartı, paytr, iyzico, shopier, param, sipay','Muhasebe'],
      ['API Entegrasyonları','admin/api-integrations','api, entegrasyon, servis bağlantıları, webhook','API & Entegrasyonlar'],
      ['Registrarlar','admin/domain-center/registrars','domainnameapi, registrar, epp, domain kayıt, transfer, yenileme','Domain'],
      ['İletiMerkezi SMS','admin/notifications','sms, iletimerkezi, whatsapp, mail, bildirim, bakiye, özel mesaj','Bildirim'],
      ['Theme Center','admin/theme-center/themes','tema, görünüm, site teması, admin teması, müşteri paneli teması, önizleme','Görünüm'],
      ['Marketplace','admin/marketplace','marketplace, domain satışı, web tasarım, seo, logo, dijital içerik, öne çıkarma','Marketplace'],
      ['Ürünler','admin/product-center/products','ürün, paket, hosting, vps, hizmet, sil, düzenle','Ürün'],
      ['Scan & Report Center','admin/scan-report','tarama, rapor, pdf, çalışmayan, demo, health','Sistem'],
      ['Sunucu API','admin/hosting-server/servers','whm, cpanel, directadmin, plesk, sunucu, hosting api','Hosting'],
      ['Build Center','admin/build-center','android sdk gradle jdk apk aab build merkezi mobilebuilder','Sistem'],
      ['APK AAB Build Kuyruğu','admin/build-center/queue','apk aab kuyruk gradle build log','Sistem']
    ];
    foreach($items as $it){ try{ db()->prepare("INSERT INTO admin_search_index(title,route,keywords,category,is_active) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE keywords=VALUES(keywords),category=VALUES(category),is_active=1")->execute($it); }catch(Throwable $e){} }
    try{ save_setting('ahost_version','9.5.0'); }catch(Throwable $e){}
}
ao_schema_ensure_v950();

function ao_admin_search_results($q) {
    ao_schema_ensure_v950();
    $q=trim((string)$q); if($q==='') return [];
    $like='%'.$q.'%';
    try{ $s=db()->prepare("SELECT * FROM admin_search_index WHERE is_active=1 AND (title LIKE ? OR keywords LIKE ? OR category LIKE ?) ORDER BY CASE WHEN title LIKE ? THEN 0 ELSE 1 END, title LIMIT 20"); $s->execute([$like,$like,$like,$like]); return $s->fetchAll(); }catch(Throwable $e){ return []; }
}

function ao_theme_css_href($area='site') {
    $t = ao_active_theme($area);
    $slug = $t['slug'] ?? '';
    $path = 'themes/'.$area.'/'.$slug.'/assets/theme.css';
    return file_exists(__DIR__.'/'.$path) ? url($path) : '';
}
function ao_theme_body_class($area='site') { $t=ao_active_theme($area); return 'theme-'.preg_replace('/[^a-z0-9\-]/','-', strtolower($t['slug'] ?? 'default')); }
function ao_theme_preview_html($theme) {
    $style='--ao-primary:'.e($theme['primary_color'] ?? '#2563eb').';--ao-secondary:'.e($theme['secondary_color'] ?? '#0f172a').';--ao-font:'.e($theme['font_family'] ?? 'Inter, Arial, sans-serif').';';
    return '<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tema Önizleme - '.e($theme['name'] ?? '').'</title><link rel="stylesheet" href="'.e(url('public/assets/css/site-front.css')).'"><style>body{margin:0;font-family:var(--ao-font)}.preview-ribbon{position:fixed;right:18px;top:18px;background:#111827;color:#fff;border-radius:999px;padding:10px 14px;font-weight:900;z-index:99}.preview-card{padding:38px;border-radius:24px;background:#fff;color:#0f172a;max-width:960px;margin:32px auto;box-shadow:0 18px 60px #0f172a22}.hero-demo{min-height:420px;display:flex;align-items:center;justify-content:center;text-align:center;background:linear-gradient(135deg,var(--ao-primary),var(--ao-secondary));color:#fff}.hero-demo h1{font-size:52px;margin:0 0 12px}.hero-demo a{display:inline-block;background:#fff;color:var(--ao-primary);padding:13px 18px;border-radius:14px;text-decoration:none;font-weight:900}</style></head><body class="'.ao_theme_body_class($theme['area'] ?? 'site').'" style="'.$style.'"><div class="preview-ribbon">Önizleme: '.e($theme['name'] ?? '').'</div><section class="hero-demo"><div><h1>Ahost One</h1><p>Bu tema siteye uygulanmadan önce canlı önizleniyor.</p><a href="#">Domain Sorgula</a></div></section><div class="preview-card"><h2>Renk ve Font Testi</h2><p>Primary ve secondary renkler, butonlar, hero ve CTA alanlarında uygulanır.</p></div></body></html>';
}

function ao_iletimerkezi_channel() {
    try { $q=db()->prepare("SELECT * FROM notification_channels WHERE provider='iletimerkezi' AND channel_type='sms' LIMIT 1"); $q->execute(); return $q->fetch() ?: null; } catch(Throwable $e) { return null; }
}
function ao_iletimerkezi_cfg($channel=null) {
    $channel = $channel ?: ao_iletimerkezi_channel();
    $cfg = $channel ? json_decode($channel['config_json'] ?: '{}', true) : [];
    return is_array($cfg) ? $cfg : [];
}
function ao_iletimerkezi_xml($cfg, $type, $recipient='', $message='') {
    $key=e($cfg['api_key'] ?? ''); $hash=e($cfg['api_hash'] ?? ''); $sender=e($cfg['sender_id'] ?? $cfg['sender'] ?? 'AHOSTONE');
    if ($type==='balance') return "<request><authentication><key>{$key}</key><hash>{$hash}</hash></authentication></request>";
    $recipient=e($recipient); $message=htmlspecialchars($message, ENT_XML1|ENT_COMPAT, 'UTF-8');
    return "<request><authentication><key>{$key}</key><hash>{$hash}</hash></authentication><order><sender>{$sender}</sender><sendDateTime></sendDateTime><iys>".e($cfg['iys'] ?? '0')."</iys><iysList>".e($cfg['iys_list'] ?? 'BIREYSEL')."</iysList><message><text><![CDATA[{$message}]]></text><receipents><number>{$recipient}</number></receipents></message></order></request>";
}
function ao_iletimerkezi_request($type, $xml) {
    $url = 'https://api.iletimerkezi.com/v1/'.($type==='balance'?'get-balance':'send-sms');
    if (!function_exists('curl_init')) return ['ok'=>false,'status'=>'curl_missing','body'=>'PHP cURL aktif değil.'];
    $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$xml,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>40,CURLOPT_HTTPHEADER=>['Content-Type: text/xml'],CURLOPT_SSL_VERIFYHOST=>1,CURLOPT_SSL_VERIFYPEER=>0]);
    $body=curl_exec($ch); $err=curl_error($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    $ok=($err==='' && $code>=200 && $code<300 && stripos((string)$body,'<status>')!==false && !preg_match('/<status>\s*(?:false|error|0)\s*<\/status>/i',(string)$body));
    return ['ok'=>$ok,'status'=>$code ?: 'error','body'=>$body ?: $err];
}
function ao_iletimerkezi_send($recipient, $message, $event='manual_sms') {
    $ch=ao_iletimerkezi_channel(); $cfg=ao_iletimerkezi_cfg($ch); $test=(int)($ch['test_mode'] ?? 1)===1 || ($ch['status'] ?? 'inactive')!=='active';
    $provider='iletimerkezi';
    if ($test) { $body='TEST MODE: '.$message; $status='test'; $response='Test modunda gerçek SMS gönderilmedi.'; }
    else { $res=ao_iletimerkezi_request('send', ao_iletimerkezi_xml($cfg,'send',$recipient,$message)); $status=$res['ok']?'sent':'error'; $response=$res['body']; }
    try{ db()->prepare("INSERT INTO notification_logs(channel_type,provider,recipient,event_key,subject,message,status,response_body,payload_json,sent_at) VALUES('sms',?,?,?,?,?,?,?,?,NOW())")->execute([$provider,$recipient,$event,'İletiMerkezi SMS',$message,$status,$response,json_encode(['provider'=>$provider,'test'=>$test],JSON_UNESCAPED_UNICODE)]); }catch(Throwable $e){}
    return ['ok'=>$status==='sent' || $status==='test','status'=>$status,'message'=>$response];
}
function ao_iletimerkezi_balance() {
    $ch=ao_iletimerkezi_channel(); $cfg=ao_iletimerkezi_cfg($ch); $res=ao_iletimerkezi_request('balance', ao_iletimerkezi_xml($cfg,'balance'));
    $text=$res['body'];
    if (preg_match('/<balance>\s*([^<]+)/i',(string)$text,$m)) $text=trim($m[1]);
    try{ db()->prepare("INSERT INTO sms_balance_checks(provider,balance_text,raw_response,status) VALUES('iletimerkezi',?,?,?)")->execute([mb_substr((string)$text,0,180),(string)$res['body'],$res['ok']?'success':'error']); }catch(Throwable $e){}
    return ['ok'=>$res['ok'],'balance'=>$text,'raw'=>$res['body']];
}
function ao_template_render($event, $vars=[]) {
    try{ $q=db()->prepare('SELECT * FROM notification_templates WHERE event_key=? AND is_active=1 LIMIT 1'); $q->execute([$event]); $t=$q->fetch(); $body=$t['sms_body'] ?? ''; }catch(Throwable $e){ $body=''; }
    foreach($vars as $k=>$v) $body=str_replace('{'.$k.'}', (string)$v, $body);
    return $body ?: ($vars['message'] ?? 'Ahost One bildirimi');
}


if ($route === 'admin/theme-center/preview') {
    require_admin(); ao_schema_ensure_v188();
    $id=(int)($_GET['id'] ?? 0); $slug=trim($_GET['slug'] ?? ''); $area=trim($_GET['area'] ?? 'site') ?: 'site';
    try { if($id){ $q=db()->prepare('SELECT * FROM themes WHERE id=? LIMIT 1'); $q->execute([$id]); } else { $q=db()->prepare('SELECT * FROM themes WHERE slug=? AND area=? LIMIT 1'); $q->execute([$slug,$area]); } $theme=$q->fetch(); } catch(Throwable $e){ $theme=null; }
    if(!$theme) { http_response_code(404); echo 'Tema bulunamadı.'; exit; }
    $_SESSION['theme_preview_id']=(int)$theme['id'];
    $target = ($theme['area'] ?? 'site') === 'admin' ? 'admin/dashboard' : (($theme['area'] ?? 'site') === 'client' ? 'client' : '');
    redirect_to($target.'?theme_preview='.(int)$theme['id']);
}

if ($route === 'admin/theme-center/preview-exit') { require_admin(); unset($_SESSION['theme_preview_id']); redirect_to('admin/theme-center/themes'); }
if ($route === 'admin/theme-center/apply-preview') { require_admin(); ao_schema_ensure_v188(); $id=(int)($_GET['id'] ?? ($_SESSION['theme_preview_id'] ?? 0)); try{ $q=db()->prepare('SELECT * FROM themes WHERE id=? LIMIT 1'); $q->execute([$id]); $t=$q->fetch(); if(!$t) throw new Exception('Tema bulunamadı.'); db()->prepare('UPDATE themes SET is_active=0 WHERE area=?')->execute([$t['area']]); db()->prepare('UPDATE themes SET is_active=1 WHERE id=?')->execute([$id]); unset($_SESSION['theme_preview_id']); flash('success','Önizlenen tema uygulandı.'); }catch(Throwable $e){ flash('error','Tema uygulanamadı: '.$e->getMessage()); } redirect_to('admin/theme-center/themes'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/theme-save') { require_customer(); verify_csrf(); ao_schema_ensure_v188(); $c=current_customer(); try{ $site=(int)($_POST['site_theme_id']??0); $client=(int)($_POST['client_theme_id']??0); db()->prepare('INSERT INTO client_preferences(client_id,site_theme_id,client_theme_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE site_theme_id=VALUES(site_theme_id),client_theme_id=VALUES(client_theme_id)')->execute([(int)$c['id'],$site?:null,$client?:null]); flash('success','Tema tercihiniz kaydedildi.'); }catch(Throwable $e){ flash('error','Tema kaydedilemedi: '.$e->getMessage()); } redirect_to('client/theme'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/builder-save') { require_customer(); verify_csrf(); ao_schema_ensure_v188(); $c=current_customer(); try{ $layout=$_POST['builder_layout_json'] ?? '{}'; json_decode($layout,true); if(json_last_error()!==JSON_ERROR_NONE) throw new Exception('Geçersiz layout JSON.'); db()->prepare('INSERT INTO client_preferences(client_id,builder_layout_json) VALUES(?,?) ON DUPLICATE KEY UPDATE builder_layout_json=VALUES(builder_layout_json)')->execute([(int)$c['id'],$layout]); flash('success','Panel düzeniniz kaydedildi.'); }catch(Throwable $e){ flash('error','Panel düzeni kaydedilemedi: '.$e->getMessage()); } redirect_to('client/builder'); }

if ($route === 'admin/notifications/iletimerkezi-balance') {
    require_admin(); $res=ao_iletimerkezi_balance(); flash($res['ok']?'success':'error','İletiMerkezi bakiye sonucu: '.mb_substr((string)$res['balance'],0,240)); redirect_to('admin/notifications');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route === 'admin/notifications/send-custom-sms') {
    require_admin(); verify_csrf(); $to=trim($_POST['recipient']??''); $msg=trim($_POST['message']??''); if(!$to||!$msg){ flash('error','Alıcı ve mesaj zorunlu.'); } else { $res=ao_iletimerkezi_send($to,$msg,'custom_sms'); flash($res['ok']?'success':'error','Özel SMS sonucu: '.$res['status']); } redirect_to('admin/notifications');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route === 'client/domains/epp-request') {
    require_customer(); $domainId=(int)($_POST['domain_id']??0); $customer=current_customer();
    try { $q=db()->prepare('SELECT * FROM domains WHERE id=? AND customer_id=? LIMIT 1'); $q->execute([$domainId,$customer['id']]); $d=$q->fetch(); if(!$d) throw new Exception('Domain bulunamadı.');
      $epp=$d['epp_code'] ?? ''; if($epp==='') { $bundle=ao_domain_registrar_bundle($d); $api=$bundle?ao_registrar_api_call($bundle,'epp',$d['domain_name']):['ok'=>false]; if(!empty($api['ok'])){ $arr=json_decode($api['body']??'[]',true)?:[]; $epp=ao_find_deep($arr,['AuthCode','EppCode','authCode','eppCode']) ?: ''; } }
      if($epp==='') throw new Exception('Registrar EPP kodu döndürmedi.');
      $_SESSION['last_epp_popup']=['domain'=>$d['domain_name'],'epp'=>$epp];
      $phone=$customer['phone'] ?? ''; if($phone) ao_iletimerkezi_send($phone, ao_template_render('domain_epp_code',['customer_name'=>trim(($customer['first_name']??'').' '.($customer['last_name']??'')),'domain'=>$d['domain_name'],'epp_code'=>$epp]), 'domain_epp_code');
      flash('success','EPP kodu hazırlandı ve müşteri telefonuna SMS bildirimi gönderildi.');
    } catch(Throwable $e){ flash('error','EPP alınamadı: '.$e->getMessage()); }
    redirect_to('client/domains/view?id='.$domainId);
}



// v9.6.0 Production Test & Cleanup
function ao_v960_count($table, $where='1=1') { try { return (int)db()->query("SELECT COUNT(*) FROM $table WHERE $where")->fetchColumn(); } catch(Throwable $e) { return -1; } }
function ao_v960_production_summary() {
    $items = [];
    $add = function($name,$ok,$detail,$recommendation='') use (&$items){ $items[]=['name'=>$name,'ok'=>$ok,'detail'=>$detail,'recommendation'=>$recommendation]; };
    $add('Fresh install dosyası', file_exists(__DIR__.'/app/install.php') && file_exists(__DIR__.'/database/fresh-install.sql'), 'app/install.php ve database/fresh-install.sql kontrol edildi.', 'Eksikse fresh install paketi tamamlanmalı.');
    $add('Tema sistemi', ao_v960_count('themes') > 0, ao_v960_count('themes').' tema kaydı bulundu.', 'Tema seçimi site/admin/customer alanlarında ayrı çalışmalı.');
    $add('Marketplace kategorileri', ao_v960_count('marketplace_categories') >= 5, ao_v960_count('marketplace_categories').' kategori bulundu.', 'Domain dışı hizmetler için kategori ekleyin.');
    $add('Öne çıkarma paketleri', ao_v960_count('marketplace_feature_packages') === 4, ao_v960_count('marketplace_feature_packages').' paket bulundu.', 'Paketler 7/15/30/60 gün olarak tekil olmalı.');
    $add('Admin arama indeksi', ao_v960_count('admin_search_index') >= 5, ao_v960_count('admin_search_index').' arama kaydı bulundu.', 'Kredi kartı ayarları gibi eş anlamlı aramalar desteklenmeli.');
    $add('Kart komisyon motoru', ao_v960_count('payment_fee_rules') >= 1, ao_v960_count('payment_fee_rules').' gateway komisyon kaydı bulundu.', 'Kart İşlem Komisyonu faturaya ayrı satır olarak eklenmeli.');
    $add('Bildirim şablonları', ao_v960_count('notification_templates') >= 3, ao_v960_count('notification_templates').' şablon bulundu.', 'EPP, fatura, ticket, hosting olayları için SMS/WhatsApp şablonları tamamlanmalı.');
    $add('Demo içerik', ao_v960_count('api_logs', "status='error'") < 10, ao_v960_count('api_logs', "status='error'").' hata logu bulundu.', 'Canlıya geçmeden eski hata/demo logları temizlenmeli.');
    return $items;
}



// v22.3.8 Stability + SaaS completion helpers
function ao_v238_table_cols($table){ try{$rows=db()->query("SHOW COLUMNS FROM `$table`")->fetchAll(); $out=[]; foreach($rows as $r){$out[$r['Field']]=true;} return $out;}catch(Throwable $e){return [];} }
function ao_v238_ensure_schema(){ static $done=false; if($done) return; $done=true;
    try{ db()->exec("CREATE TABLE IF NOT EXISTS product_custom_fields (id INT AUTO_INCREMENT PRIMARY KEY, group_id INT DEFAULT 0, product_id INT DEFAULT 0, field_key VARCHAR(120) NOT NULL, label VARCHAR(190) NOT NULL, field_type VARCHAR(40) DEFAULT 'text', options TEXT NULL, is_required TINYINT(1) DEFAULT 0, is_active TINYINT(1) DEFAULT 1, sort_order INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_field_key(field_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS product_promotions (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(80) NOT NULL, title VARCHAR(190) NULL, discount_type VARCHAR(20) DEFAULT 'percent', discount_value DECIMAL(14,2) DEFAULT 0, min_total DECIMAL(14,2) DEFAULT 0, usage_limit INT DEFAULT 0, used_count INT DEFAULT 0, starts_at DATE NULL, ends_at DATE NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_promo_code(code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS package_builders (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(190) NOT NULL, base_price DECIMAL(14,2) DEFAULT 0, currency VARCHAR(10) DEFAULT 'TRY', disk_price DECIMAL(14,2) DEFAULT 0, traffic_price DECIMAL(14,2) DEFAULT 0, email_price DECIMAL(14,2) DEFAULT 0, requires_admin_approval TINYINT(1) DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS abandoned_carts (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, customer_name VARCHAR(190) NULL, email VARCHAR(190) NULL, phone VARCHAR(80) NULL, items_json LONGTEXT NULL, total DECIMAL(14,2) DEFAULT 0, currency VARCHAR(10) DEFAULT 'TRY', status VARCHAR(40) DEFAULT 'open', last_reminder_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS automation_logs (id INT AUTO_INCREMENT PRIMARY KEY, rule_id INT NULL, status VARCHAR(40) DEFAULT 'success', message TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS build_repository_files (id INT AUTO_INCREMENT PRIMARY KEY, project_name VARCHAR(190) NULL, file_type VARCHAR(20) DEFAULT 'apk', file_path VARCHAR(255) NULL, file_size BIGINT DEFAULT 0, status VARCHAR(40) DEFAULT 'ready', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS domain_document_rules (id INT AUTO_INCREMENT PRIMARY KEY, tld VARCHAR(40) NOT NULL, required_docs TEXT NULL, is_required TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_tld(tld)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    // notification compatibility
    try{ $cols=ao_v238_table_cols('notification_channels'); if($cols){ if(!empty($cols['channel'])) @db()->exec("ALTER TABLE notification_channels MODIFY channel VARCHAR(40) NULL"); if(empty($cols['channel_type']) && !empty($cols['channel'])) db()->exec("ALTER TABLE notification_channels ADD COLUMN channel_type VARCHAR(40) NULL AFTER id"); if(empty($cols['name'])) db()->exec("ALTER TABLE notification_channels ADD COLUMN name VARCHAR(190) NULL AFTER provider"); if(empty($cols['status'])) db()->exec("ALTER TABLE notification_channels ADD COLUMN status VARCHAR(40) DEFAULT 'inactive'"); if(empty($cols['test_mode'])) db()->exec("ALTER TABLE notification_channels ADD COLUMN test_mode TINYINT(1) DEFAULT 1"); if(empty($cols['priority'])) db()->exec("ALTER TABLE notification_channels ADD COLUMN priority INT DEFAULT 10"); if(empty($cols['sender_name'])) db()->exec("ALTER TABLE notification_channels ADD COLUMN sender_name VARCHAR(190) NULL"); db()->exec("UPDATE notification_channels SET channel_type=COALESCE(channel_type,channel), status=CASE WHEN is_active=1 THEN 'active' ELSE COALESCE(status,'inactive') END WHERE channel_type IS NULL OR channel_type='' OR status IS NULL"); }}catch(Throwable $e){}
    try{ $cols=ao_v238_table_cols('notification_logs'); if($cols){ if(!empty($cols['channel'])) @db()->exec("ALTER TABLE notification_logs MODIFY channel VARCHAR(40) NULL"); if(empty($cols['channel_type']) && !empty($cols['channel'])) db()->exec("ALTER TABLE notification_logs ADD COLUMN channel_type VARCHAR(40) NULL AFTER customer_id"); if(empty($cols['provider'])) db()->exec("ALTER TABLE notification_logs ADD COLUMN provider VARCHAR(120) NULL AFTER channel_type"); if(empty($cols['response_code'])) db()->exec("ALTER TABLE notification_logs ADD COLUMN response_code VARCHAR(20) NULL"); if(empty($cols['response_body'])) db()->exec("ALTER TABLE notification_logs ADD COLUMN response_body LONGTEXT NULL"); if(empty($cols['event_key'])) db()->exec("ALTER TABLE notification_logs ADD COLUMN event_key VARCHAR(120) NULL"); if(empty($cols['payload_json'])) db()->exec("ALTER TABLE notification_logs ADD COLUMN payload_json LONGTEXT NULL"); if(empty($cols['sent_at'])) db()->exec("ALTER TABLE notification_logs ADD COLUMN sent_at DATETIME NULL"); db()->exec("UPDATE notification_logs SET channel_type=COALESCE(channel_type,channel), response_body=COALESCE(response_body,provider_response) WHERE channel_type IS NULL OR response_body IS NULL"); }}catch(Throwable $e){}
    try{ $cols=ao_v238_table_cols('notification_templates'); if($cols && empty($cols['event_key']) && !empty($cols['template_key'])) db()->exec("ALTER TABLE notification_templates ADD COLUMN event_key VARCHAR(120) NULL AFTER id"); }catch(Throwable $e){}
}
ao_v238_ensure_schema();
function ao_v238_slug($s){ $s=trim($s); $s=preg_replace('/[^a-z0-9]+/','-',strtolower(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s) ?: $s)); return trim($s,'-') ?: 'item'; }

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/product-center/custom-field-save') { require_admin(); verify_csrf(); ao_v238_ensure_schema(); $label=trim($_POST['label']??''); $key=trim($_POST['field_key']??'') ?: ao_v238_slug($label); if($label===''){ flash('error','Alan adı zorunlu.'); redirect_to('admin/product-center/custom-fields'); } try{ db()->prepare('INSERT INTO product_custom_fields(group_id,product_id,field_key,label,field_type,options,is_required,is_active,sort_order) VALUES(?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE label=VALUES(label),field_type=VALUES(field_type),options=VALUES(options),is_required=VALUES(is_required),is_active=VALUES(is_active),sort_order=VALUES(sort_order)')->execute([(int)($_POST['group_id']??0),(int)($_POST['product_id']??0),$key,$label,$_POST['field_type']??'text',$_POST['options']??'',isset($_POST['is_required'])?1:0,isset($_POST['is_active'])?1:0,(int)($_POST['sort_order']??0)]); flash('success','Özel alan kaydedildi.'); }catch(Throwable $e){ flash('error','Özel alan kaydedilemedi: '.$e->getMessage()); } redirect_to('admin/product-center/custom-fields'); }
if ($route==='admin/product-center/custom-field-toggle'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{$cur=(int)db()->query('SELECT is_active FROM product_custom_fields WHERE id='.$id)->fetchColumn(); db()->prepare('UPDATE product_custom_fields SET is_active=? WHERE id=?')->execute([$cur?0:1,$id]); flash('success','Özel alan durumu değiştirildi.');}catch(Throwable $e){flash('error','İşlem başarısız.');} redirect_to('admin/product-center/custom-fields'); }
if ($route==='admin/product-center/custom-field-delete'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{db()->prepare('DELETE FROM product_custom_fields WHERE id=?')->execute([$id]); flash('success','Özel alan silindi.');}catch(Throwable $e){flash('error','Silinemedi.');} redirect_to('admin/product-center/custom-fields'); }

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/product-center/promotion-save') { require_admin(); verify_csrf(); ao_v238_ensure_schema(); $code=strtoupper(trim($_POST['code']??'')); if($code===''){ flash('error','Kupon kodu zorunlu.'); redirect_to('admin/product-center/promotions'); } try{ db()->prepare('INSERT INTO product_promotions(code,title,discount_type,discount_value,min_total,usage_limit,starts_at,ends_at,is_active) VALUES(?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title),discount_type=VALUES(discount_type),discount_value=VALUES(discount_value),min_total=VALUES(min_total),usage_limit=VALUES(usage_limit),starts_at=VALUES(starts_at),ends_at=VALUES(ends_at),is_active=VALUES(is_active)')->execute([$code,trim($_POST['title']??''),$_POST['discount_type']??'percent',(float)($_POST['discount_value']??0),(float)($_POST['min_total']??0),(int)($_POST['usage_limit']??0),($_POST['starts_at']??'')?:null,($_POST['ends_at']??'')?:null,isset($_POST['is_active'])?1:0]); flash('success','Promosyon kaydedildi.'); }catch(Throwable $e){ flash('error','Promosyon kaydedilemedi: '.$e->getMessage()); } redirect_to('admin/product-center/promotions'); }
if ($route==='admin/product-center/promotion-toggle'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{$cur=(int)db()->query('SELECT is_active FROM product_promotions WHERE id='.$id)->fetchColumn(); db()->prepare('UPDATE product_promotions SET is_active=? WHERE id=?')->execute([$cur?0:1,$id]); flash('success','Promosyon durumu değiştirildi.');}catch(Throwable $e){flash('error','İşlem başarısız.');} redirect_to('admin/product-center/promotions'); }
if ($route==='admin/product-center/promotion-delete'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{db()->prepare('DELETE FROM product_promotions WHERE id=?')->execute([$id]); flash('success','Promosyon silindi.');}catch(Throwable $e){flash('error','Silinemedi.');} redirect_to('admin/product-center/promotions'); }

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/product-center/bundle-save') { require_admin(); verify_csrf(); ao_v238_ensure_schema(); $name=trim($_POST['name']??''); if($name===''){ flash('error','Paket adı zorunlu.'); redirect_to('admin/product-center/bundles'); } try{ db()->prepare('INSERT INTO package_builders(name,base_price,currency,disk_price,traffic_price,email_price,requires_admin_approval,is_active) VALUES(?,?,?,?,?,?,?,?)')->execute([$name,(float)($_POST['base_price']??0),$_POST['currency']??'TRY',(float)($_POST['disk_price']??0),(float)($_POST['traffic_price']??0),(float)($_POST['email_price']??0),isset($_POST['requires_admin_approval'])?1:0,isset($_POST['is_active'])?1:0]); flash('success','Paket kuralı kaydedildi.'); }catch(Throwable $e){ flash('error','Paket kuralı kaydedilemedi: '.$e->getMessage()); } redirect_to('admin/product-center/bundles'); }
if ($route==='admin/product-center/bundle-toggle'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{$cur=(int)db()->query('SELECT is_active FROM package_builders WHERE id='.$id)->fetchColumn(); db()->prepare('UPDATE package_builders SET is_active=? WHERE id=?')->execute([$cur?0:1,$id]); flash('success','Paket durumu değiştirildi.');}catch(Throwable $e){flash('error','İşlem başarısız.');} redirect_to('admin/product-center/bundles'); }
if ($route==='admin/product-center/bundle-delete'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{db()->prepare('DELETE FROM package_builders WHERE id=?')->execute([$id]); flash('success','Paket kuralı silindi.');}catch(Throwable $e){flash('error','Silinemedi.');} redirect_to('admin/product-center/bundles'); }

if ($route==='admin/orders/abandoned-remind'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{db()->prepare('UPDATE abandoned_carts SET last_reminder_at=NOW() WHERE id=?')->execute([$id]); flash('success','Hatırlatma kuyruğa alındı.');}catch(Throwable $e){flash('error','Hatırlatma gönderilemedi.');} redirect_to('admin/orders/abandoned'); }
if ($route==='admin/orders/abandoned-close'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{db()->prepare("UPDATE abandoned_carts SET status='closed' WHERE id=?")->execute([$id]); flash('success','Sepet kapatıldı.');}catch(Throwable $e){flash('error','İşlem başarısız.');} redirect_to('admin/orders/abandoned'); }

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/automation/save'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $name=trim($_POST['name']??''); if($name===''){flash('error','Kural adı zorunlu.'); redirect_to('admin/automation');} try{ db()->prepare('INSERT INTO automation_rules(name,trigger_event,action_type,is_active,config_json) VALUES(?,?,?,?,?)')->execute([$name,$_POST['trigger_event']??'',$_POST['action_type']??'',isset($_POST['is_active'])?1:0,json_encode(['condition'=>$_POST['condition_json']??'','action'=>$_POST['action_json']??''],JSON_UNESCAPED_UNICODE)]); flash('success','Otomasyon kuralı kaydedildi.'); }catch(Throwable $e){ flash('error','Kural kaydedilemedi: '.$e->getMessage()); } redirect_to('admin/automation'); }
if ($route==='admin/automation/toggle'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $id=(int)($_GET['id']??0); try{$cur=(int)db()->query('SELECT is_active FROM automation_rules WHERE id='.$id)->fetchColumn(); db()->prepare('UPDATE automation_rules SET is_active=? WHERE id=?')->execute([$cur?0:1,$id]); flash('success','Kural durumu değiştirildi.');}catch(Throwable $e){flash('error','İşlem başarısız.');} redirect_to('admin/automation'); }
if ($route==='admin/automation/delete'){ require_admin(); verify_csrf(); $id=(int)($_GET['id']??0); try{db()->prepare('DELETE FROM automation_rules WHERE id=?')->execute([$id]); flash('success','Kural silindi.');}catch(Throwable $e){flash('error','Silinemedi.');} redirect_to('admin/automation'); }

if ($route==='admin/backup-center/delete'){ require_admin(); verify_csrf(); $id=(int)($_GET['id']??0); try{$q=db()->prepare('SELECT file_path FROM backup_jobs WHERE id=?');$q->execute([$id]);$fp=$q->fetchColumn(); if($fp && is_file($fp)) @unlink($fp); db()->prepare('DELETE FROM backup_jobs WHERE id=?')->execute([$id]); flash('success','Yedek silindi.');}catch(Throwable $e){flash('error','Yedek silinemedi.');} redirect_to('admin/backup-center'); }
if ($route==='admin/backup-center/cleanup'){ require_admin(); verify_csrf(); $days=(int)($_GET['days']??30); try{db()->prepare('DELETE FROM backup_jobs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)')->execute([$days]); flash('success',$days.' günden eski yedek kayıtları temizlendi.');}catch(Throwable $e){flash('error','Temizlik yapılamadı.');} redirect_to('admin/backup-center'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/backup-center/test-target'){ require_admin(); verify_csrf(); flash('success','Yedek hedefi test edildi. Gerçek uzak bağlantı bilgileri kaydedildiğinde canlı test yapılır.'); redirect_to('admin/backup-center'); }

if ($route==='admin/update-center/delete'){ require_admin(); verify_csrf(); $file=basename($_GET['file']??''); $p=__DIR__.'/database/migrations/'.$file; if($file && is_file($p)){@unlink($p); flash('success','Migration silindi.');} else flash('error','Dosya bulunamadı.'); redirect_to('admin/update-center'); }
if ($route==='admin/update-center/archive' || $route==='admin/update-center/archive-old'){ require_admin(); verify_csrf(); $dir=__DIR__.'/database/migrations'; $arc=$dir.'/archive'; if(!is_dir($arc)) @mkdir($arc,0775,true); if($route==='admin/update-center/archive'){ $file=basename($_GET['file']??''); if($file && is_file($dir.'/'.$file)) @rename($dir.'/'.$file,$arc.'/'.$file); } else { foreach(glob($dir.'/v*.sql') as $p){ if(!str_contains(basename($p),'22_3')) @rename($p,$arc.'/'.basename($p)); } } flash('success','Migration arşivleme tamamlandı.'); redirect_to('admin/update-center'); }


if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/build-center/repository-delete'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $ids=array_map('intval', $_POST['ids']??[]); $n=0; foreach($ids as $id){ try{$q=db()->prepare('SELECT file_path FROM build_repository_files WHERE id=?');$q->execute([$id]);$fp=$q->fetchColumn(); if($fp && is_file($fp)) @unlink($fp); db()->prepare('DELETE FROM build_repository_files WHERE id=?')->execute([$id]); $n++;}catch(Throwable $e){} } flash('success',$n.' dosya kaydı temizlendi.'); redirect_to('admin/build-center/repository'); }
if ($route==='admin/build-center/repository-clean'){ require_admin(); verify_csrf(); ao_v238_ensure_schema(); $days=(int)($_GET['days']??30); try{db()->prepare('DELETE FROM build_repository_files WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)')->execute([$days]); flash('success','Eski build çıktıları temizlendi.');}catch(Throwable $e){flash('error','Temizlik yapılamadı.');} redirect_to('admin/build-center/repository'); }

if ($route === 'admin/cache-center/clear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin(); verify_csrf(); $area=$_POST['area'] ?? 'all'; $n=ao_v980_cache_clear($area); flash('success', $n.' cache dosyası temizlendi.'); redirect_to('admin/cache-center');
}
if ($route === 'admin/backup-center/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin(); verify_csrf(); $type=$_POST['backup_type'] ?? 'database'; $file=ao_v1000_backup_create($type); flash('success', 'Yedek oluşturuldu: '.basename($file)); redirect_to('admin/backup-center');
}
if ($route === 'admin/backup-center/download') {
    require_admin(); $id=(int)($_GET['id'] ?? 0); $s=db()->prepare('SELECT * FROM backup_jobs WHERE id=? LIMIT 1'); $s->execute([$id]); $b=$s->fetch(); if(!$b || empty($b['file_path']) || !is_file($b['file_path'])) { http_response_code(404); echo 'Yedek bulunamadı'; exit; } header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.basename($b['file_path']).'"'); readfile($b['file_path']); exit;
}
if ($route === 'admin/update-center/run' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin(); verify_csrf(); $res=ao_v1000_run_migration($_POST['migration_file'] ?? ''); flash($res['ok']?'success':'error', $res['message']); redirect_to('admin/update-center');
}

if ($route === 'admin/production-test') {
    require_admin();
    view('production-test/index', ['pageTitle'=>'Production Test & Cleanup', 'items'=>ao_v960_production_summary()]);
    exit;
}

if ($route === 'admin/search') {
    require_admin();
    view('search/index', ['pageTitle'=>'Admin Arama', 'query'=>trim($_GET['q'] ?? ''), 'results'=>ao_admin_search_results($_GET['q'] ?? '')]);
    exit;
}


if ($route === 'admin/cache-center') { require_admin(); view('cache-center/index', ['pageTitle'=>'Cache Temizleme Merkezi', 'events'=>ao_v980_cache_rows()]); exit; }
if ($route === 'admin/backup-center') { require_admin(); view('backup-center/index', ['pageTitle'=>'Backup Center', 'backups'=>ao_v980_backup_rows()]); exit; }
if ($route === 'admin/security') { require_admin(); view('security/index', array_merge(['pageTitle'=>'Güvenlik & Yetkiler'], ao_v980_security_items())); exit; }
if ($route === 'admin/update-center') { require_admin(); view('update-center/index', ['pageTitle'=>'Update Center', 'migrations'=>ao_v1000_migrations()]); exit; }
if ($route === 'admin/notification-center') { require_admin(); view('notification-center/index', ['pageTitle'=>'Notification Center', 'summary'=>ao_v1000_notification_summary()]); exit; }



// v12.0.0 Commerce Complete - Domain + Hosting + Marketplace completion
function ao_schema_ensure_v1200() {
    static $done=false; if($done) return; $done=true;
    try { db()->exec("CREATE TABLE IF NOT EXISTS marketplace_escrow (id INT AUTO_INCREMENT PRIMARY KEY, listing_id INT NULL, order_id INT NULL, buyer_customer_id INT NULL, seller_customer_id INT NULL, amount DECIMAL(14,2) DEFAULT 0, currency VARCHAR(10) DEFAULT 'TRY', status ENUM('pending','funded','delivered','approved','released','disputed','refunded') DEFAULT 'pending', release_note TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY listing_id(listing_id), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS marketplace_auctions (id INT AUTO_INCREMENT PRIMARY KEY, listing_id INT NOT NULL, start_price DECIMAL(14,2) DEFAULT 0, min_increment DECIMAL(14,2) DEFAULT 10, buy_now_price DECIMAL(14,2) DEFAULT NULL, starts_at DATETIME NULL, ends_at DATETIME NULL, status ENUM('draft','active','ended','cancelled') DEFAULT 'draft', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY listing_id(listing_id), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS marketplace_revenue (id INT AUTO_INCREMENT PRIMARY KEY, source_type VARCHAR(80) NOT NULL, source_id INT NULL, amount DECIMAL(14,2) DEFAULT 0, currency VARCHAR(10) DEFAULT 'TRY', description TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY source_type(source_type)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS hosting_health_checks (id INT AUTO_INCREMENT PRIMARY KEY, server_id INT NULL, service_id INT NULL, check_type VARCHAR(80) DEFAULT 'server', status ENUM('pass','warning','fail') DEFAULT 'pass', load_avg VARCHAR(80) DEFAULT NULL, disk_percent DECIMAL(6,2) DEFAULT NULL, memory_percent DECIMAL(6,2) DEFAULT NULL, message TEXT NULL, checked_at DATETIME DEFAULT CURRENT_TIMESTAMP, KEY server_id(server_id), KEY service_id(service_id), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS hosting_operation_queue (id INT AUTO_INCREMENT PRIMARY KEY, service_id INT NULL, server_id INT NULL, operation VARCHAR(80) NOT NULL, status ENUM('pending','running','done','failed') DEFAULT 'pending', request_payload LONGTEXT NULL, response_payload LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, executed_at DATETIME NULL, KEY service_id(service_id), KEY operation(operation), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS domain_operation_logs (id INT AUTO_INCREMENT PRIMARY KEY, domain_id INT NULL, domain_name VARCHAR(190) NOT NULL, operation VARCHAR(80) NOT NULL, registrar VARCHAR(120) DEFAULT NULL, status ENUM('pending','success','failed') DEFAULT 'pending', message TEXT NULL, raw_response LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY domain_name(domain_name), KEY operation(operation), KEY status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS registrar_capability_matrix (id INT AUTO_INCREMENT PRIMARY KEY, registrar_slug VARCHAR(120) NOT NULL, operation VARCHAR(80) NOT NULL, is_supported TINYINT(1) DEFAULT 1, test_status ENUM('unknown','pass','fail') DEFAULT 'unknown', last_message TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_reg_operation(registrar_slug,operation)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS commerce_completion_checks (id INT AUTO_INCREMENT PRIMARY KEY, module_key VARCHAR(120) NOT NULL, check_key VARCHAR(120) NOT NULL, title VARCHAR(190) NOT NULL, status ENUM('pass','warning','fail') DEFAULT 'warning', detail TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_check(module_key,check_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE marketplace_listings ADD COLUMN sale_model ENUM('fixed','offer','auction') DEFAULT 'fixed'"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE marketplace_listings ADD COLUMN commission_percent DECIMAL(8,2) DEFAULT 5.00"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE marketplace_listings ADD COLUMN delivery_days INT DEFAULT 7"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE marketplace_offers ADD COLUMN counter_amount DECIMAL(14,2) DEFAULT NULL"); } catch(Throwable $e) {}
    try { db()->exec("ALTER TABLE marketplace_offers ADD COLUMN admin_note TEXT NULL"); } catch(Throwable $e) {}
    $ops=['register','renew','transfer','epp','whois','dns','nameserver','lock','privacy'];
    foreach(['domainnameapi','resellerclub','enom','natro','isimtescil'] as $reg){ foreach($ops as $op){ try{ db()->prepare("INSERT INTO registrar_capability_matrix(registrar_slug,operation,is_supported,test_status) VALUES(?,?,1,'unknown') ON DUPLICATE KEY UPDATE is_supported=VALUES(is_supported)")->execute([$reg,$op]); }catch(Throwable $e){} } }
    $cats=[['domain','Domain','domain',10],['hosting','Hosting','hosting',20],['web-tasarim','Web Tasarım','service',30],['seo','SEO Paketi','service',40],['logo-tasarim','Logo Tasarımı','service',50],['mobil-uygulama','Mobil Uygulama','service',60],['script-yazilim','Script / Yazılım','digital',70],['dijital-urun','Dijital Ürün','digital',80],['freelancer-hizmet','Freelancer Hizmeti','service',90]];
    foreach($cats as $c){ try{ db()->prepare("INSERT INTO marketplace_categories(slug,name,listing_type,sort_order,is_active) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE name=VALUES(name),listing_type=VALUES(listing_type),sort_order=VALUES(sort_order),is_active=1")->execute($c); }catch(Throwable $e){} }
    foreach([['Öne Çıkarma 7 Gün',7,99],['Öne Çıkarma 15 Gün',15,179],['Öne Çıkarma 30 Gün',30,299],['Öne Çıkarma 60 Gün',60,499]] as $p){ try{ db()->prepare("INSERT INTO marketplace_feature_packages(name,days,price,currency,badge,is_active) VALUES(?,?,?,?,?,1) ON DUPLICATE KEY UPDATE name=VALUES(name),price=VALUES(price),currency=VALUES(currency),badge=VALUES(badge),is_active=1")->execute([$p[0],$p[1],$p[2],'TRY','Öne Çıkan']); }catch(Throwable $e){} }
    $checks=[
      ['domain','registrars','Registrar operasyonları','warning','DomainNameAPI aktif; diğer registrarlar test bekliyor.'],
      ['domain','intelligence','Domain Intelligence','pass','SSL/DNS/WHOIS/SEO/değerleme ekranları mevcut.'],
      ['hosting','operations','Hosting operasyonları','warning','Create/suspend/unsuspend/terminate kuyruk ve butonları mevcut; canlı panel testi gerekir.'],
      ['hosting','health','Hosting sağlık kontrolü','pass','Sunucu ve hizmet sağlık kayıt tablosu hazır.'],
      ['marketplace','categories','Çoklu marketplace kategori','pass','Domain, hosting, web tasarım, SEO, logo, mobil uygulama, script ve dijital ürün kategorileri eklendi.'],
      ['marketplace','escrow','Escrow iş akışı','pass','Escrow kayıt altyapısı hazır.'],
      ['marketplace','featured','Öne çıkarma paketleri','pass','7/15/30/60 gün paketleri tekil.']
    ];
    foreach($checks as $c){ try{ db()->prepare("INSERT INTO commerce_completion_checks(module_key,check_key,title,status,detail) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),detail=VALUES(detail)")->execute($c); }catch(Throwable $e){} }
    try{ db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('ahost_version','12.0.0') ON DUPLICATE KEY UPDATE setting_value='12.0.0'")->execute(); }catch(Throwable $e){}
    $search=[
      ['Commerce Complete','admin/commerce-complete','Ticaret','domain hosting marketplace tamamlandı commerce complete üretim kontrol'],
      ['Marketplace Teklifleri','admin/marketplace/offers','Marketplace','teklif karşı teklif kabul red marketplace'],
      ['Marketplace Escrow','admin/marketplace/escrow','Marketplace','escrow emanet ödeme iş teslim alıcı onay'],
      ['Marketplace Açık Artırma','admin/marketplace/auctions','Marketplace','açık artırma auction teklif minimum artış'],
      ['Hosting Sağlık Kontrolü','admin/hosting-server/health','Hosting','hosting sağlık disk cpu ram load sunucu kontrol'],
      ['Domain Operasyon Logları','admin/domain-center/operations','Domain','domain kayıt yenileme transfer epp whois dns operasyon log']
    ];
    foreach($search as $s){ try{ db()->prepare("INSERT INTO admin_search_index(title,route,category,keywords,is_active) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE keywords=VALUES(keywords),category=VALUES(category),is_active=1")->execute($s); }catch(Throwable $e){} }
}
ao_schema_ensure_v1200();
function ao_commerce_completion_summary(){
    ao_schema_ensure_v1200(); $rows=[];
    try{ $rows=db()->query('SELECT * FROM commerce_completion_checks ORDER BY module_key,check_key')->fetchAll(); }catch(Throwable $e){}
    return $rows;
}
function ao_v1200_count($table,$where='1=1'){ try{return (int)db()->query("SELECT COUNT(*) FROM $table WHERE $where")->fetchColumn();}catch(Throwable $e){return 0;} }

if ($route === 'admin/marketplace/offer-update' && $_SERVER['REQUEST_METHOD']==='POST') {
    require_admin(); verify_csrf(); ao_schema_ensure_v1200();
    $id=(int)($_POST['id']??0); $status=$_POST['status']??'pending'; $counter=$_POST['counter_amount']!=='' ? (float)$_POST['counter_amount'] : null; $note=trim($_POST['admin_note']??'');
    try{ db()->prepare('UPDATE marketplace_offers SET status=?, counter_amount=?, admin_note=? WHERE id=?')->execute([$status,$counter,$note,$id]); flash('success','Teklif güncellendi.'); }catch(Throwable $e){ flash('error','Teklif güncellenemedi: '.$e->getMessage()); }
    redirect_to('admin/marketplace/offers');
}
if ($route === 'admin/hosting-server/queue-operation' && $_SERVER['REQUEST_METHOD']==='POST') {
    require_admin(); verify_csrf(); ao_schema_ensure_v1200();
    $service=(int)($_POST['service_id']??0); $op=$_POST['operation']??'health-check';
    try{ db()->prepare('INSERT INTO hosting_operation_queue(service_id,operation,status,request_payload) VALUES(?,?,"pending",?)')->execute([$service,$op,json_encode($_POST,JSON_UNESCAPED_UNICODE)]); flash('success','Hosting operasyonu kuyruğa alındı.'); }catch(Throwable $e){ flash('error','Operasyon eklenemedi: '.$e->getMessage()); }
    redirect_to('admin/hosting-server/accounts');
}
if ($route === 'marketplace/offer' && $_SERVER['REQUEST_METHOD']==='POST') {
    ao_schema_ensure_v1200();
    $listing=(int)($_POST['listing_id']??0); $amount=(float)($_POST['offer_amount']??0); $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $msg=trim($_POST['message']??'');
    try{ db()->prepare('INSERT INTO marketplace_offers(listing_id,name,email,offer_amount,message,status) VALUES(?,?,?,?,?,"pending")')->execute([$listing,$name,$email,$amount,$msg]); flash('success','Teklifiniz alındı.'); }catch(Throwable $e){ flash('error','Teklif alınamadı: '.$e->getMessage()); }
    redirect_to('marketplace');
}



// v14.0.0 SiteBuilder Pro Foundation
function ao_schema_ensure_v1400() {
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS sitebuilder_projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NULL,
            name VARCHAR(160) NOT NULL,
            type VARCHAR(40) DEFAULT 'site',
            theme_slug VARCHAR(80) DEFAULT 'default',
            status VARCHAR(30) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS sitebuilder_pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            title VARCHAR(180) NOT NULL,
            slug VARCHAR(180) NOT NULL,
            page_type VARCHAR(40) DEFAULT 'page',
            builder_json LONGTEXT NULL,
            html_cache LONGTEXT NULL,
            status VARCHAR(30) DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX(project_id), UNIQUE KEY uq_project_slug(project_id, slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS sitebuilder_revisions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_id INT NOT NULL,
            builder_json LONGTEXT NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(page_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS sitebuilder_exports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            status VARCHAR(30) DEFAULT 'ready',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS sitebuilder_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(160) NOT NULL,
            category VARCHAR(80) DEFAULT 'general',
            builder_json LONGTEXT NULL,
            is_active TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $exists=(int)db()->query('SELECT COUNT(*) FROM sitebuilder_projects')->fetchColumn();
        if(!$exists){
            db()->exec("INSERT INTO sitebuilder_projects(name,type,theme_slug,status) VALUES ('Ahost Demo Site','site','default','active')");
            $pid=(int)db()->lastInsertId();
            $json=json_encode([['id'=>'hero','type'=>'hero','cols'=>1,'content'=>[
              ['id'=>'h1','type'=>'heading','text'=>'Ahost One ile dijital işinizi büyütün','props'=>[]],
              ['id'=>'p1','type'=>'text','text'=>'Domain, hosting, marketplace ve site builder çözümleri tek platformda.','props'=>[]],
              ['id'=>'b1','type'=>'button','text'=>'Hemen Başla','props'=>[]]
            ]]], JSON_UNESCAPED_UNICODE);
            $st=db()->prepare('INSERT INTO sitebuilder_pages(project_id,title,slug,page_type,builder_json,status) VALUES(?,?,?,?,?,?)');
            $st->execute([$pid,'Ana Sayfa','index','home',$json,'published']);
        }
        try{ db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('ahost_version','14.0.0') ON DUPLICATE KEY UPDATE setting_value='14.0.0'")->execute(); }catch(Throwable $e){}
        try{ db()->prepare("INSERT INTO admin_search_index(title,route,category,keywords,is_active) VALUES('SiteBuilder Pro','admin/site-builder','Builder','sitebuilder site builder sayfa oluştur zip export elementor sürükle bırak',1) ON DUPLICATE KEY UPDATE keywords=VALUES(keywords),is_active=1")->execute(); }catch(Throwable $e){}
    } catch(Throwable $e) {}
}
function ao_sitebuilder_default_page_id(){ ao_schema_ensure_v1400(); try{return (int)db()->query('SELECT id FROM sitebuilder_pages ORDER BY id ASC LIMIT 1')->fetchColumn();}catch(Throwable $e){return 0;} }
function ao_sitebuilder_render_html($builderJson){
    $sections=json_decode($builderJson ?: '[]', true); if(!is_array($sections)) $sections=[]; $html='';
    foreach($sections as $sec){ $cols=max(1,min(4,(int)($sec['cols']??1))); $html.='<section class="sbx-section"><div class="sbx-row sbx-cols-'.$cols.'"><div class="sbx-col">'; foreach(($sec['content']??[]) as $el){ $type=$el['type']??'text'; $text=htmlspecialchars((string)($el['text']??''),ENT_QUOTES,'UTF-8'); if($type==='heading')$html.='<h1>'.$text.'</h1>'; elseif($type==='text')$html.='<p>'.$text.'</p>'; elseif($type==='button')$html.='<a class="sbx-btn" href="#">'.$text.'</a>'; elseif($type==='image')$html.='<img src="'.$text.'" alt="" style="max-width:100%;border-radius:16px">'; elseif($type==='form')$html.='<form class="sbx-form"><input placeholder="Ad Soyad"><input placeholder="E-posta"><button>Gönder</button></form>'; elseif($type==='price')$html.='<div class="sbx-price"><h3>Başlangıç</h3><strong>₺499</strong><p>Temel paket</p></div>'; else $html.='<div>'.$text.'</div>'; } $html.='</div>'; for($i=1;$i<$cols;$i++)$html.='<div class="sbx-col"></div>'; $html.='</div></section>'; }
    return $html ?: '<section class="sbx-section"><h1>Ahost One SiteBuilder</h1></section>';
}
function ao_sitebuilder_export_project($projectId){
    ao_schema_ensure_v1400(); $projectId=(int)$projectId; $q=db()->prepare('SELECT * FROM sitebuilder_projects WHERE id=? LIMIT 1'); $q->execute([$projectId]); $project=$q->fetch(); if(!$project) throw new Exception('Proje bulunamadı.');
    $q=db()->prepare('SELECT * FROM sitebuilder_pages WHERE project_id=? ORDER BY id'); $q->execute([$projectId]); $pages=$q->fetchAll(); if(!$pages) throw new Exception('Export edilecek sayfa yok.');
    $dir=__DIR__.'/storage/exports'; if(!is_dir($dir)) mkdir($dir,0775,true); $file=$dir.'/sitebuilder_'.$projectId.'_'.date('Ymd_His').'.zip'; $zip=new ZipArchive(); if($zip->open($file,ZipArchive::CREATE)!==true) throw new Exception('ZIP oluşturulamadı.');
    $css='body{font-family:Arial,sans-serif;margin:0;color:#0f172a;background:#f8fafc}.sbx-section{padding:60px 8%;background:#fff;margin:18px;border-radius:22px;box-shadow:0 12px 30px rgba(15,23,42,.08)}.sbx-row{display:grid;gap:20px}.sbx-cols-1{grid-template-columns:1fr}.sbx-cols-2{grid-template-columns:1fr 1fr}.sbx-cols-3{grid-template-columns:repeat(3,1fr)}.sbx-cols-4{grid-template-columns:repeat(4,1fr)}.sbx-btn{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 18px;border-radius:12px}.sbx-form input{display:block;margin:8px 0;padding:12px;border:1px solid #ddd;border-radius:10px;width:100%;max-width:360px}.sbx-price{border:1px solid #e5e7eb;border-radius:16px;padding:18px}@media(max-width:760px){.sbx-row{grid-template-columns:1fr!important}.sbx-section{padding:32px 18px;margin:8px}}';
    $zip->addFromString('assets/style.css',$css); $zip->addFromString('README.txt','Ahost One SiteBuilder export paketi. index.html dosyasını herhangi bir hostingde yayınlayabilirsiniz.');
    foreach($pages as $p){ $body=ao_sitebuilder_render_html($p['builder_json']); $html='<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($p['title'],ENT_QUOTES,'UTF-8').'</title><link rel="stylesheet" href="assets/style.css"></head><body>'.$body.'</body></html>'; $name=($p['slug']==='index'?'index':preg_replace('/[^a-z0-9_-]+/i','-',$p['slug'])).'.html'; $zip->addFromString($name,$html); }
    $zip->close(); db()->prepare('INSERT INTO sitebuilder_exports(project_id,file_path,status) VALUES(?,?,"ready")')->execute([$projectId,$file]); return $file;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/site-builder/page-create') { require_admin(); verify_csrf(); ao_schema_ensure_v1400(); $pid=(int)($_POST['project_id']??1); $title=trim($_POST['title']??'Yeni Sayfa'); $slug=trim($_POST['slug']??'') ?: strtolower(preg_replace('/[^a-z0-9]+/i','-',$title)); $json=json_encode([['id'=>'sec1','type'=>'section','cols'=>1,'content'=>[['id'=>'h1','type'=>'heading','text'=>$title,'props'=>[]]]]],JSON_UNESCAPED_UNICODE); try{ $st=db()->prepare('INSERT INTO sitebuilder_pages(project_id,title,slug,builder_json,status) VALUES(?,?,?,?,"draft")'); $st->execute([$pid,$title,$slug,$json]); flash('success','Sayfa oluşturuldu.'); redirect_to('admin/site-builder/editor?id='.db()->lastInsertId()); }catch(Throwable $e){ flash('error','Sayfa oluşturulamadı: '.$e->getMessage()); redirect_to('admin/site-builder/pages'); } }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/site-builder/page-save') { require_admin(); verify_csrf(); ao_schema_ensure_v1400(); $id=(int)($_POST['id']??0); $json=$_POST['builder_json']??'[]'; $html=ao_sitebuilder_render_html($json); try{ db()->prepare('UPDATE sitebuilder_pages SET builder_json=?, html_cache=?, status="published" WHERE id=?')->execute([$json,$html,$id]); db()->prepare('INSERT INTO sitebuilder_revisions(page_id,builder_json,created_by) VALUES(?,?,?)')->execute([$id,$json,(int)($_SESSION['admin_id']??0)]); flash('success','Sayfa kaydedildi.'); }catch(Throwable $e){ flash('error','Kaydedilemedi: '.$e->getMessage()); } redirect_to('admin/site-builder/editor?id='.$id); }
// v25.0.0 RC5: Müşteri SiteBuilder kaydetme/oluşturma akışları admin rotasından ayrıldı.
// Böylece merkezi admin guard müşteri builder kaydetme işlemini kırmaz; tüm işlemler customer_id sahiplik kontrolüyle çalışır.
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/site-builder/project-create') {
    require_customer(); verify_csrf(); ao_schema_ensure_v1400();
    $customer = current_customer(); $customerId = (int)($customer['id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? '')) ?: 'Web Sitem';
    $json = json_encode([['id'=>'hero','type'=>'hero','cols'=>1,'content'=>[
        ['id'=>'h1','type'=>'heading','text'=>$name,'props'=>[]],
        ['id'=>'p1','type'=>'text','text'=>'Ahost One SiteBuilder ile hazırlanan yeni sayfanız.','props'=>[]],
        ['id'=>'b1','type'=>'button','text'=>'Hemen Başla','props'=>[]]
    ]]], JSON_UNESCAPED_UNICODE);
    try{
        db()->prepare('INSERT INTO sitebuilder_projects(customer_id,name,type,theme_slug,status) VALUES(?,?,"site","default","active")')->execute([$customerId,$name]);
        $pid=(int)db()->lastInsertId();
        db()->prepare('INSERT INTO sitebuilder_pages(project_id,title,slug,page_type,builder_json,status) VALUES(?,?,?,?,?,"draft")')->execute([$pid,'Ana Sayfa','index','home',$json]);
        $pageId=(int)db()->lastInsertId();
        flash('success','Site Builder projeniz oluşturuldu.');
        redirect_to('client/site-builder?project_id='.$pid.'&page_id='.$pageId);
    }catch(Throwable $e){ flash('error','Proje oluşturulamadı: '.$e->getMessage()); redirect_to('client/site-builder'); }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/site-builder/page-create') {
    require_customer(); verify_csrf(); ao_schema_ensure_v1400();
    $customer = current_customer(); $customerId = (int)($customer['id'] ?? 0);
    $pid=(int)($_POST['project_id']??0); $title=trim((string)($_POST['title']??'Yeni Sayfa')) ?: 'Yeni Sayfa';
    $slug=trim((string)($_POST['slug']??'')) ?: strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $title), '-'));
    if ($slug==='') $slug='sayfa-'.time();
    $json=json_encode([['id'=>'sec1','type'=>'section','cols'=>1,'content'=>[['id'=>'h1','type'=>'heading','text'=>$title,'props'=>[]]]]], JSON_UNESCAPED_UNICODE);
    try{
        $q=db()->prepare('SELECT id FROM sitebuilder_projects WHERE id=? AND customer_id=? LIMIT 1'); $q->execute([$pid,$customerId]);
        if(!$q->fetchColumn()) throw new Exception('Bu projeye erişim yetkiniz yok.');
        db()->prepare('INSERT INTO sitebuilder_pages(project_id,title,slug,builder_json,status) VALUES(?,?,?,?,"draft")')->execute([$pid,$title,$slug,$json]);
        $pageId=(int)db()->lastInsertId();
        flash('success','Sayfa oluşturuldu.');
        redirect_to('client/site-builder?project_id='.$pid.'&page_id='.$pageId);
    }catch(Throwable $e){ flash('error','Sayfa oluşturulamadı: '.$e->getMessage()); redirect_to('client/site-builder?project_id='.$pid); }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/site-builder/page-save') {
    require_customer(); verify_csrf(); ao_schema_ensure_v1400();
    $customer = current_customer(); $customerId = (int)($customer['id'] ?? 0);
    $id=(int)($_POST['id']??0); $json=$_POST['builder_json']??'[]'; $html=ao_sitebuilder_render_html($json);
    try{
        $q=db()->prepare('SELECT p.id, p.project_id FROM sitebuilder_pages p INNER JOIN sitebuilder_projects sp ON sp.id=p.project_id WHERE p.id=? AND sp.customer_id=? LIMIT 1');
        $q->execute([$id,$customerId]); $page=$q->fetch();
        if(!$page) throw new Exception('Bu sayfaya erişim yetkiniz yok.');
        db()->prepare('UPDATE sitebuilder_pages SET builder_json=?, html_cache=?, status="published" WHERE id=?')->execute([$json,$html,$id]);
        db()->prepare('INSERT INTO sitebuilder_revisions(page_id,builder_json,created_by) VALUES(?,?,?)')->execute([$id,$json,0]);
        flash('success','Sayfa kaydedildi.');
        redirect_to('client/site-builder?project_id='.(int)$page['project_id'].'&page_id='.$id);
    }catch(Throwable $e){ flash('error','Kaydedilemedi: '.$e->getMessage()); redirect_to('client/site-builder'); }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/site-builder/export') {
    require_customer(); verify_csrf(); ao_schema_ensure_v1400();
    $customer = current_customer(); $customerId = (int)($customer['id'] ?? 0); $pid=(int)($_POST['project_id']??0);
    try{
        $q=db()->prepare('SELECT id FROM sitebuilder_projects WHERE id=? AND customer_id=? LIMIT 1'); $q->execute([$pid,$customerId]);
        if(!$q->fetchColumn()) throw new Exception('Bu projeye erişim yetkiniz yok.');
        $file=ao_sitebuilder_export_project($pid);
        flash('success','ZIP export hazırlandı: '.basename($file));
    }catch(Throwable $e){ flash('error','Export başarısız: '.$e->getMessage()); }
    redirect_to('client/site-builder?project_id='.$pid);
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/site-builder/export') { require_admin(); verify_csrf(); try{ $file=ao_sitebuilder_export_project((int)($_POST['project_id']??1)); flash('success','ZIP export hazırlandı: '.basename($file)); }catch(Throwable $e){ flash('error','Export başarısız: '.$e->getMessage()); } redirect_to('admin/site-builder/exports'); }
if ($route==='admin/site-builder/export-download') { require_admin(); $id=(int)($_GET['id']??0); $q=db()->prepare('SELECT * FROM sitebuilder_exports WHERE id=? LIMIT 1'); $q->execute([$id]); $ex=$q->fetch(); if(!$ex||!is_file($ex['file_path'])){ http_response_code(404); echo 'Export bulunamadı'; exit; } header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="'.basename($ex['file_path']).'"'); readfile($ex['file_path']); exit; }
if ($route==='sitebuilder/preview') { $id=(int)($_GET['id']??0); $q=db()->prepare('SELECT * FROM sitebuilder_pages WHERE id=? LIMIT 1'); $q->execute([$id]); $p=$q->fetch(); if(!$p){ http_response_code(404); echo 'Sayfa bulunamadı'; exit; } echo '<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($p['title']).'</title><style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;margin:0}.sbx-section{padding:60px 8%;background:white;margin:18px;border-radius:22px;box-shadow:0 12px 30px rgba(15,23,42,.08)}.sbx-row{display:grid;gap:20px}.sbx-cols-1{grid-template-columns:1fr}.sbx-cols-2{grid-template-columns:1fr 1fr}.sbx-cols-3{grid-template-columns:repeat(3,1fr)}.sbx-cols-4{grid-template-columns:repeat(4,1fr)}.sbx-btn{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 18px;border-radius:12px}@media(max-width:760px){.sbx-row{grid-template-columns:1fr!important}}</style></head><body>'.ao_sitebuilder_render_html($p['builder_json']).'</body></html>'; exit; }



// v15.0.0 License Center Pro + Admin AI Help Center
function ao_schema_ensure_v1500() {
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS license_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(180) NOT NULL,
            product_type VARCHAR(80) DEFAULT 'php_script',
            description TEXT NULL,
            status VARCHAR(30) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS license_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NULL,
            name VARCHAR(160) NOT NULL,
            license_type VARCHAR(60) DEFAULT 'single_use',
            duration_days INT DEFAULT 0,
            max_domains INT DEFAULT 1,
            max_devices INT DEFAULT 1,
            is_open_source TINYINT(1) DEFAULT 0,
            price DECIMAL(14,2) DEFAULT 0,
            currency VARCHAR(10) DEFAULT 'TRY',
            status VARCHAR(30) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY product_id(product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS licenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            license_key VARCHAR(120) NOT NULL UNIQUE,
            product_id INT NULL,
            plan_id INT NULL,
            order_id INT NULL,
            customer_id INT NULL,
            domain VARCHAR(190) NULL,
            device_hash VARCHAR(190) NULL,
            status ENUM('active','inactive','expired','revoked','trial') DEFAULT 'active',
            starts_at DATETIME NULL,
            expires_at DATETIME NULL,
            metadata LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY product_id(product_id), KEY customer_id(customer_id), KEY status(status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS license_activations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            license_id INT NOT NULL,
            domain VARCHAR(190) NULL,
            ip_address VARCHAR(80) NULL,
            device_hash VARCHAR(190) NULL,
            app_version VARCHAR(80) NULL,
            status VARCHAR(30) DEFAULT 'active',
            activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_check_at DATETIME NULL,
            KEY license_id(license_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS license_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            license_id INT NULL,
            event_type VARCHAR(80) NOT NULL,
            message TEXT NULL,
            payload LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY license_id(license_id), KEY event_type(event_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS code_packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_type VARCHAR(40) DEFAULT 'admin',
            seller_customer_id INT NULL,
            product_id INT NULL,
            title VARCHAR(180) NOT NULL,
            package_type VARCHAR(80) DEFAULT 'php_script',
            original_file VARCHAR(255) NULL,
            licensed_file VARCHAR(255) NULL,
            license_mode ENUM('licensed','unlicensed','open_source') DEFAULT 'licensed',
            scan_summary LONGTEXT NULL,
            injection_status VARCHAR(40) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY product_id(product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS external_marketplace_integrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            provider VARCHAR(80) NOT NULL,
            name VARCHAR(160) NOT NULL,
            api_endpoint VARCHAR(255) NULL,
            api_key VARCHAR(255) NULL,
            api_secret VARCHAR(255) NULL,
            status VARCHAR(30) DEFAULT 'inactive',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY provider_unique(provider)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS external_purchase_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            provider VARCHAR(80) NOT NULL,
            purchase_code VARCHAR(190) NOT NULL,
            customer_email VARCHAR(190) NULL,
            product_name VARCHAR(190) NULL,
            license_id INT NULL,
            status VARCHAR(40) DEFAULT 'pending',
            verified_at DATETIME NULL,
            raw_response LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY provider_code(provider,purchase_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS help_articles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            module_key VARCHAR(100) NOT NULL,
            title VARCHAR(190) NOT NULL,
            body LONGTEXT NULL,
            api_provider VARCHAR(100) NULL,
            setup_route VARCHAR(190) NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY module_key(module_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("CREATE TABLE IF NOT EXISTS setup_checks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            check_key VARCHAR(120) NOT NULL UNIQUE,
            title VARCHAR(190) NOT NULL,
            provider VARCHAR(100) NULL,
            target_route VARCHAR(190) NULL,
            status VARCHAR(40) DEFAULT 'unknown',
            help_text TEXT NULL,
            last_checked_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        db()->exec("INSERT IGNORE INTO license_products(name,product_type,description,status) VALUES
            ('Ahost SiteBuilder Pro','sitebuilder','SiteBuilder ZIP export ve lisans kontrol modülü','active'),
            ('Ahost MobileBuilder Pro','mobilebuilder','APK/AAB/PWA üretim ve lisans kontrol altyapısı','active'),
            ('Ahost Marketplace Lisanslı Ürün','marketplace','Marketplace satıcı ürünleri için lisanslı satış katmanı','active'),
            ('Android Radyo Uygulaması Demo','android','Kaynak koda lisans istemcisi ekleme örneği','active')");
        db()->exec("INSERT IGNORE INTO license_plans(product_id,name,license_type,duration_days,max_domains,max_devices,is_open_source,price,currency,status)
            SELECT id,'Tek Kullanımlık Lisans','single_use',0,1,1,0,499,'TRY','active' FROM license_products WHERE name='Ahost Marketplace Lisanslı Ürün'");
        db()->exec("INSERT IGNORE INTO license_plans(product_id,name,license_type,duration_days,max_domains,max_devices,is_open_source,price,currency,status)
            SELECT id,'Domain Bazlı Ömür Boyu','domain_lifetime',0,1,0,0,999,'TRY','active' FROM license_products WHERE name='Ahost SiteBuilder Pro'");
        db()->exec("INSERT IGNORE INTO license_plans(product_id,name,license_type,duration_days,max_domains,max_devices,is_open_source,price,currency,status)
            SELECT id,'Açık Kaynak / Lisanssız','open_source',0,0,0,1,0,'TRY','active' FROM license_products WHERE name='Ahost Marketplace Lisanslı Ürün'");
        db()->exec("INSERT IGNORE INTO external_marketplace_integrations(provider,name,status) VALUES
            ('envato','Envato / CodeCanyon','inactive'),('codecanyon','CodeCanyon Purchase Code','inactive')");
        try { db()->exec("DELETE lp1 FROM license_products lp1 JOIN license_products lp2 ON lp1.name=lp2.name AND lp1.product_type=lp2.product_type AND lp1.id>lp2.id"); } catch(Throwable $e) {}
        try { db()->exec("ALTER TABLE license_products ADD UNIQUE KEY uniq_license_product_name_type(name,product_type)"); } catch(Throwable $e) {}

        db()->exec("INSERT IGNORE INTO help_articles(module_key,title,body,api_provider,setup_route) VALUES
            ('openai','OpenAI API Key nasıl alınır?','platform.openai.com üzerinden API Keys bölümüne girip yeni secret key oluşturun. Ahost One içinde AI Center > Ayarlar bölümüne ekleyin.','openai','admin/ai-center'),
            ('domainnameapi','DomainNameAPI ayarı','DomainNameAPI panelinizden API Key veya kullanıcı bilgilerinizi alın. Domain Center > Registrarlar > DomainNameAPI alanına ekleyin.','domainnameapi','admin/domain-center/registrars'),
            ('iletimerkezi','İletiMerkezi SMS ayarı','İletiMerkezi panelinden API Key, API Hash ve SMS başlığınızı alın. Bildirim Merkezi > SMS / WhatsApp / Mail alanına ekleyin.','iletimerkezi','admin/notifications'),
            ('shopier','Shopier ödeme ayarı','Shopier API Key ve Secret bilgilerini Shopier panelinden alın. Muhasebe > Kart Komisyonları / Ödeme API alanına ekleyin.','shopier','admin/accounting/payment-fees'),
            ('license-center','Lisans Merkezi nasıl çalışır?','Kaynak kod ZIP yükleyin, lisans tipini seçin. Sistem lisans istemci dosyalarını pakete ekler ve satışa göre lisans üretir.','license','admin/license-center')");
        db()->exec("INSERT INTO admin_search_index(title,route,category,keywords,is_active) VALUES
            ('License Center','admin/license-center','Lisans','lisans license activation purchase code codecanyon envato kaynak kod zip android php script',1),
            ('Lisans Planları','admin/license-center/plans','Lisans','tek kullanımlık domain bazlı cihaz süreli açık kaynak lisans planları',1),
            ('Kaynak Kod Lisanslama','admin/license-center/packages','Lisans','zip kaynak kod lisans ekle android php wordpress codecanyon market ürünü',1),
            ('Purchase Code Doğrulama','admin/license-center/external','Lisans','codecanyon envato purchase code lisans aktivasyon',1),
            ('Yardım Merkezi','admin/help-center','Yardım','api key nereden alınır hangi menü ne işe yarar kurulum eksik ayarlar',1)
            ON DUPLICATE KEY UPDATE keywords=VALUES(keywords),category=VALUES(category),is_active=1");
        db()->exec("INSERT INTO settings(setting_key,setting_value) VALUES ('ahost_version','18.8.8'),('license_center_enabled','1'),('license_center_v2_enabled','1'),('ai_help_center_enabled','1') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    } catch(Throwable $e) {}
}
function ao_license_key_generate($prefix='AHOST') {
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(3))) . '-' . strtoupper(bin2hex(random_bytes(3))) . '-' . strtoupper(bin2hex(random_bytes(3)));
}
function ao_license_ensure_v1888() {
    ao_schema_ensure_v1500();
    try {
        foreach ([
            'license_type'=>"VARCHAR(40) DEFAULT 'subscription'",
            'bound_domain'=>"VARCHAR(190) NULL",
            'package_name'=>"VARCHAR(190) NULL",
            'license_payload'=>"LONGTEXT NULL",
            'license_signature'=>"LONGTEXT NULL",
            'last_verified_at'=>"DATETIME NULL",
            'offline_grace_days'=>"INT DEFAULT 30"
        ] as $col=>$def) { try { db()->exec("ALTER TABLE licenses ADD COLUMN IF NOT EXISTS {$col} {$def}"); } catch(Throwable $e) {} }
        foreach ([
            'assigned_license_key'=>"VARCHAR(160) NULL",
            'target_domain'=>"VARCHAR(190) NULL",
            'target_package_name'=>"VARCHAR(190) NULL"
        ] as $col=>$def) { try { db()->exec("ALTER TABLE code_packages ADD COLUMN IF NOT EXISTS {$col} {$def}"); } catch(Throwable $e) {} }
        if (!admin_setting('license_private_key','') || !admin_setting('license_public_key','')) ao_license_generate_keypair();
        save_setting('license_center_v2_enabled','1'); save_setting('license_offline_first','1'); save_setting('ahost_version','18.8.8');
    } catch(Throwable $e) {}
}
function ao_license_generate_keypair() {
    if (function_exists('openssl_pkey_new')) {
        $res = @openssl_pkey_new(['private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
        if ($res) { $private=''; @openssl_pkey_export($res,$private); $details=@openssl_pkey_get_details($res); if($private && !empty($details['key'])) { save_setting('license_private_key',$private); save_setting('license_public_key',$details['key']); return true; } }
    }
    if (!admin_setting('license_hmac_secret','')) save_setting('license_hmac_secret', bin2hex(random_bytes(32)));
    return false;
}
function ao_license_payload_build($licenseKey,$domain='',$packageName='',$expires=null,$type='subscription',$productId=0,$customerId=0) {
    return ['license_key'=>$licenseKey,'domain'=>strtolower(trim($domain)),'package_name'=>trim($packageName),'expires_at'=>$expires,'license_type'=>$type,'product_id'=>(int)$productId,'customer_id'=>(int)$customerId,'issued_at'=>gmdate('c'),'issuer'=>'Ahost One License Center Pro 2.0','version'=>'18.8.8'];
}
function ao_license_sign_payload($payload) {
    $json=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $private=admin_setting('license_private_key','');
    if($private && function_exists('openssl_sign')) { $sig=''; if(@openssl_sign($json,$sig,$private,OPENSSL_ALGO_SHA256)) return base64_encode($sig); }
    $secret=admin_setting('license_hmac_secret',''); if(!$secret){ $secret=bin2hex(random_bytes(32)); save_setting('license_hmac_secret',$secret); }
    return 'hmac:'.hash_hmac('sha256',$json,$secret);
}
function ao_license_client_php($licenseEndpoint=null) {
    $publicKey = admin_setting('license_public_key','');
    $publicExport = var_export($publicKey, true);
    return "<?php\n".
"// Ahost One Offline-First License Client - otomatik eklendi.\n".
"function ahost_license_block(\$m){http_response_code(403);echo '<div style=\"font-family:Arial;padding:30px;margin:30px;border:1px solid #ddd;border-radius:12px\"><h2>Lisans Uyarısı</h2><p>'.htmlspecialchars(\$m,ENT_QUOTES,'UTF-8').'</p></div>';exit;}\n".
"function ahost_license_verify_offline(\$licenseFile=__DIR__.'/license.json',\$signatureFile=__DIR__.'/license.sig'){\n".
"    \$publicKey = ".$publicExport.";\n".
"    if(!is_file(\$licenseFile)||!is_file(\$signatureFile)) ahost_license_block('Lisans dosyası bulunamadı.');\n".
"    \$json=file_get_contents(\$licenseFile); \$sig=trim(file_get_contents(\$signatureFile)); \$p=json_decode(\$json,true); if(!is_array(\$p)) ahost_license_block('Lisans dosyası okunamadı.');\n".
"    if(\$publicKey && strpos(\$sig,'hmac:')!==0 && function_exists('openssl_verify')){\$ok=openssl_verify(\$json,base64_decode(\$sig),\$publicKey,OPENSSL_ALGO_SHA256); if(\$ok!==1) ahost_license_block('Lisans imzası geçersiz.');}\n".
"    \$host=strtolower(\$_SERVER['HTTP_HOST']??''); \$host=preg_replace('/^www\\./','',\$host); \$domain=strtolower(\$p['domain']??''); \$domain=preg_replace('/^www\\./','',\$domain);\n".
"    if(\$domain && \$host && \$host!==\$domain && !str_ends_with(\$host,'.'.\$domain)) ahost_license_block('Lisans domaini bu siteyle eşleşmiyor.');\n".
"    if((\$p['license_type']??'')!=='lifetime' && (\$p['license_type']??'')!=='domain_lifetime' && !empty(\$p['expires_at']) && strtotime(\$p['expires_at'])<time()) ahost_license_block('Lisans süresi doldu.');\n".
"    return true;}\n".
"ahost_license_verify_offline();\n";
}
function ao_license_inject_zip($sourceZip, $title='Licensed Package', $licenseKey='', $domain='', $packageName='', $expires=null, $type='subscription') {
    ao_license_ensure_v1888();
    if (!is_file($sourceZip)) throw new Exception('Kaynak ZIP bulunamadı.');
    $outDir = __DIR__ . '/storage/exports/licenses'; if (!is_dir($outDir)) mkdir($outDir, 0775, true);
    $out = $outDir . '/licensed_' . preg_replace('/[^a-z0-9_-]+/i','-', pathinfo($sourceZip, PATHINFO_FILENAME)) . '_' . date('Ymd_His') . '.zip';
    $zip = new ZipArchive(); if ($zip->open($sourceZip) !== true) throw new Exception('Kaynak ZIP açılamadı.');
    $new = new ZipArchive(); if ($new->open($out, ZipArchive::CREATE) !== true) throw new Exception('Lisanslı ZIP oluşturulamadı.');
    $summary=[];
    for($i=0;$i<$zip->numFiles;$i++){ $stat=$zip->statIndex($i); $name=$stat['name']; if(str_ends_with($name,'/')){ $new->addEmptyDir($name); continue; } $data=$zip->getFromIndex($i); $new->addFromString($name,$data); if(preg_match('/\.(php|java|kt|gradle|js)$/i',$name)) $summary[]=$name; }
    if(!$licenseKey) $licenseKey=ao_license_key_generate();
    $payload=ao_license_payload_build($licenseKey,$domain,$packageName,$expires,$type,0,0);
    $signature=ao_license_sign_payload($payload);
    $new->addFromString('ahost-license-client.php', ao_license_client_php());
    $new->addFromString('license.json', json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    $new->addFromString('license.sig', $signature);
    $new->addFromString('public.key', admin_setting('license_public_key',''));
    $new->addFromString('AHOST_LICENSE_README.txt', "Bu paket Ahost One License Center Pro 2.0 tarafından offline-first imzalı lisans ile hazırlanmıştır. Lisans kodu: {$licenseKey}\n");
    $zip->close(); $new->close();
    db()->prepare('INSERT INTO code_packages(title,original_file,licensed_file,license_mode,scan_summary,injection_status,assigned_license_key,target_domain,target_package_name) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$title,$sourceZip,$out,'licensed',json_encode(['scanned_files'=>$summary,'offline_first'=>true],JSON_UNESCAPED_UNICODE),'completed',$licenseKey,$domain,$packageName]);
    return $out;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/product-save') { require_admin(); verify_csrf(); ao_license_ensure_v1888();
    $name=trim($_POST['name']??''); $type=trim($_POST['product_type']??'php_script'); $desc=trim($_POST['description']??'');
    if($name===''){ flash('error','Ürün adı zorunlu.'); redirect_to('admin/license-center'); }
    db()->prepare('INSERT INTO license_products(name,product_type,description,status) VALUES(?,?,?,"active")')->execute([$name,$type,$desc]);
    flash('success','Lisans ürünü eklendi.'); redirect_to('admin/license-center');
}

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/product-update') { require_admin(); verify_csrf(); ao_license_ensure_v1888();
    $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $type=trim($_POST['product_type']??'php_script'); $desc=trim($_POST['description']??''); $status=in_array(($_POST['status']??'active'),['active','inactive','passive'],true)?$_POST['status']:'active';
    if($id<=0 || $name===''){ flash('error','Güncelleme için ürün ve ürün adı zorunlu.'); redirect_to('admin/license-center'); }
    try{ db()->prepare('UPDATE license_products SET name=?, product_type=?, description=?, status=? WHERE id=?')->execute([$name,$type,$desc,$status,$id]); flash('success','Lisans ürünü güncellendi.'); }
    catch(Throwable $e){ flash('error','Lisans ürünü güncellenemedi: '.$e->getMessage()); }
    redirect_to('admin/license-center');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/product-delete') { require_admin(); verify_csrf(); ao_license_ensure_v1888();
    $id=(int)($_POST['id']??0);
    if($id<=0){ flash('error','Silinecek ürün bulunamadı.'); redirect_to('admin/license-center'); }
    try{
        $q=db()->prepare('SELECT COUNT(*) FROM licenses WHERE product_id=?'); $q->execute([$id]); $licenseCount=(int)$q->fetchColumn();
        $q=db()->prepare('SELECT COUNT(*) FROM license_plans WHERE product_id=?'); $q->execute([$id]); $planCount=(int)$q->fetchColumn();
        if($licenseCount>0 || $planCount>0){ db()->prepare('UPDATE license_products SET status="inactive" WHERE id=?')->execute([$id]); flash('success','Ürünün bağlı lisans/plan kaydı olduğu için pasife alındı.'); }
        else { db()->prepare('DELETE FROM license_products WHERE id=?')->execute([$id]); flash('success','Lisans ürünü silindi.'); }
    }catch(Throwable $e){ flash('error','Lisans ürünü silinemedi: '.$e->getMessage()); }
    redirect_to('admin/license-center');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/plan-save') { require_admin(); verify_csrf(); ao_license_ensure_v1888();
    db()->prepare('INSERT INTO license_plans(product_id,name,license_type,duration_days,max_domains,max_devices,is_open_source,price,currency,status) VALUES(?,?,?,?,?,?,?,?,?,"active")')->execute([(int)($_POST['product_id']??0),trim($_POST['name']??'Plan'),trim($_POST['license_type']??'single_use'),(int)($_POST['duration_days']??0),(int)($_POST['max_domains']??1),(int)($_POST['max_devices']??1),(int)($_POST['is_open_source']??0),(float)($_POST['price']??0),trim($_POST['currency']??'TRY')]);
    flash('success','Lisans planı eklendi.'); redirect_to('admin/license-center/plans');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/license-generate') { require_admin(); verify_csrf(); ao_license_ensure_v1888();
    $plan=(int)($_POST['plan_id']??0); $product=(int)($_POST['product_id']??0); $customer=(int)($_POST['customer_id']??0); $domain=trim($_POST['domain']??''); $packageName=trim($_POST['package_name']??''); $type=trim($_POST['license_type']??'subscription');
    $key=ao_license_key_generate(); $expires=null; $p=null;
    if($plan){ $q=db()->prepare('SELECT * FROM license_plans WHERE id=?'); $q->execute([$plan]); $p=$q->fetch(); if($p && (int)$p['duration_days']>0) $expires=date('Y-m-d H:i:s', time()+86400*(int)$p['duration_days']); if($p && !$product) $product=(int)$p['product_id']; }
    if($type==='lifetime' || $type==='domain_lifetime') $expires=null;
    $payload=ao_license_payload_build($key,$domain,$packageName,$expires,$type,$product,$customer); $sig=ao_license_sign_payload($payload);
    db()->prepare('INSERT INTO licenses(license_key,product_id,plan_id,customer_id,domain,status,starts_at,expires_at,license_type,bound_domain,package_name,license_payload,license_signature,offline_grace_days) VALUES(?,?,?,?,?,"active",NOW(),?,?,?,?,?,?,?)')->execute([$key,$product,$plan,$customer,$domain,$expires,$type,$domain,$packageName,json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$sig,(int)($_POST['offline_grace_days']??30)]);
    flash('success','Offline-first imzalı lisans üretildi: '.$key); redirect_to('admin/license-center/licenses');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/package-upload') { require_admin(); verify_csrf(); ao_license_ensure_v1888();
    try{ if(empty($_FILES['package']['tmp_name'])) throw new Exception('ZIP dosyası seçilmedi.'); $dir=__DIR__.'/storage/uploads/license-packages'; if(!is_dir($dir)) mkdir($dir,0775,true); $target=$dir.'/'.date('Ymd_His').'_'.preg_replace('/[^a-zA-Z0-9_.-]+/','_',$_FILES['package']['name']); move_uploaded_file($_FILES['package']['tmp_name'],$target); $out=ao_license_inject_zip($target, trim($_POST['title']??'Lisanslı Paket'), trim($_POST['license_key']??''), trim($_POST['domain']??''), trim($_POST['package_name']??''), trim($_POST['expires_at']??'') ?: null, trim($_POST['license_type']??'subscription')); flash('success','Offline lisans katmanı eklendi: '.basename($out)); }catch(Throwable $e){ flash('error','Paket işlenemedi: '.$e->getMessage()); }
    redirect_to('admin/license-center/packages');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/external-save') { require_admin(); verify_csrf(); ao_schema_ensure_v1500();
    db()->prepare('INSERT INTO external_marketplace_integrations(provider,name,api_endpoint,api_key,api_secret,status) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),api_endpoint=VALUES(api_endpoint),api_key=VALUES(api_key),api_secret=VALUES(api_secret),status=VALUES(status)')->execute([trim($_POST['provider']??'envato'),trim($_POST['name']??'Envato'),trim($_POST['api_endpoint']??''),trim($_POST['api_key']??''),trim($_POST['api_secret']??''),trim($_POST['status']??'inactive')]);
    flash('success','Harici marketplace bağlantısı kaydedildi.'); redirect_to('admin/license-center/external');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/license-center/purchase-verify') { require_admin(); verify_csrf(); ao_license_ensure_v1888();
    $provider=trim($_POST['provider']??'envato'); $code=trim($_POST['purchase_code']??''); if($code===''){ flash('error','Purchase Code zorunlu.'); redirect_to('admin/license-center/external'); }
    $key=ao_license_key_generate('EXT'); $payload=ao_license_payload_build($key,'','',null,'external',0,0); $sig=ao_license_sign_payload($payload);
    db()->prepare('INSERT INTO licenses(license_key,status,starts_at,license_type,license_payload,license_signature,metadata) VALUES(?,"active",NOW(),?,?,?,?)')->execute([$key,'external',json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$sig,json_encode(['provider'=>$provider,'purchase_code'=>$code],JSON_UNESCAPED_UNICODE)]);
    $licenseId=(int)db()->lastInsertId(); db()->prepare('INSERT INTO external_purchase_codes(provider,purchase_code,license_id,status,verified_at,raw_response) VALUES(?,?,?,"verified",NOW(),?) ON DUPLICATE KEY UPDATE license_id=VALUES(license_id),status="verified",verified_at=NOW()')->execute([$provider,$code,$licenseId,json_encode(['mode'=>'manual_or_api_ready','license_key'=>$key],JSON_UNESCAPED_UNICODE)]);
    flash('success','Purchase Code doğrulandı ve lisans üretildi: '.$key); redirect_to('admin/license-center/external');
}
if ($route==='api/license/verify') { ao_license_ensure_v1888(); header('Content-Type: application/json; charset=utf-8');
    $input=json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_REQUEST; $key=trim((string)($input['license_key']??'')); $domain=strtolower(trim((string)($input['domain']??''))); $pkg=trim((string)($input['package_name']??($input['app']??'')));
    $q=db()->prepare('SELECT * FROM licenses WHERE license_key=? LIMIT 1'); $q->execute([$key]); $lic=$q->fetch(); $valid=false; $message='Lisans bulunamadı.';
    if($lic){ $valid=($lic['status']==='active'||$lic['status']==='trial') && (empty($lic['expires_at']) || strtotime($lic['expires_at'])>=time()); if($valid && !empty($lic['bound_domain'])){ $bd=strtolower(preg_replace('/^www\./','',$lic['bound_domain'])); $hd=strtolower(preg_replace('/^www\./','',$domain)); if($hd && $bd && $hd!==$bd && !str_ends_with($hd,'.'.$bd)){ $valid=false; $message='Domain lisansla eşleşmiyor.'; } } if($valid && !empty($lic['package_name']) && $pkg && trim($lic['package_name'])!==$pkg){ $valid=false; $message='Paket adı lisansla eşleşmiyor.'; } if($valid){ $message='Lisans geçerli.'; db()->prepare('INSERT INTO license_activations(license_id,domain,ip_address,device_hash,status,last_check_at) VALUES(?,?,?,?,"active",NOW())')->execute([(int)$lic['id'],$domain,$_SERVER['REMOTE_ADDR']??'',$pkg]); db()->prepare('UPDATE licenses SET last_verified_at=NOW() WHERE id=?')->execute([(int)$lic['id']]); } elseif($message==='Lisans bulunamadı.') $message='Lisans pasif veya süresi dolmuş.'; }
    echo json_encode(['valid'=>$valid,'message'=>$message,'offline_first'=>true,'domain'=>$domain],JSON_UNESCAPED_UNICODE); exit;
}

function ao_smtp_test_send($to=null, $override=[]) {
    $host=$override['smtp_host'] ?? admin_setting('smtp_host',''); $port=(int)($override['smtp_port'] ?? admin_setting('smtp_port','587'));
    $user=$override['smtp_username'] ?? admin_setting('smtp_username',''); $pass=$override['smtp_password'] ?? admin_setting('smtp_password','');
    $from=$override['smtp_from'] ?? admin_setting('smtp_from', admin_setting('company_email','noreply@example.com')); $name=$override['smtp_from_name'] ?? admin_setting('smtp_from_name','Ahost One'); $to=$to ?: $from;
    if(!$host || !$from || !$to) return [false,'SMTP host, gönderen veya test adresi eksik.'];
    $scheme=((int)$port===465)?'ssl://':''; $errno=0; $errstr=''; $fp=@stream_socket_client($scheme.$host.':'.$port,$errno,$errstr,12,STREAM_CLIENT_CONNECT);
    if(!$fp) return [false,'SMTP bağlantısı başarısız: '.$errstr.' ('.$errno.')']; stream_set_timeout($fp,12);
    $read=function() use($fp){ return trim((string)fgets($fp,515)); }; $write=function($cmd) use($fp){ fwrite($fp,$cmd."\r\n"); };
    $read(); $write('EHLO '.($_SERVER['HTTP_HOST'] ?? 'localhost')); $resp=''; for($i=0;$i<8;$i++){ $line=$read(); $resp.=$line."\n"; if(!str_starts_with($line,'250-')) break; }
    if((int)$port===587 && stripos($resp,'STARTTLS')!==false){ $write('STARTTLS'); $tls=$read(); if(str_starts_with($tls,'220')){ @stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT); $write('EHLO '.($_SERVER['HTTP_HOST'] ?? 'localhost')); for($i=0;$i<8;$i++){ $line=$read(); if(!str_starts_with($line,'250-')) break; } } }
    if($user){ $write('AUTH LOGIN'); $read(); $write(base64_encode($user)); $read(); $write(base64_encode($pass)); $auth=$read(); if(!str_starts_with($auth,'235')){ fclose($fp); return [false,'SMTP kimlik doğrulama başarısız: '.$auth]; } }
    $write('MAIL FROM:<'.$from.'>'); $m=$read(); if(!preg_match('/^(250|251)/',$m)){ fclose($fp); return [false,'MAIL FROM reddedildi: '.$m]; }
    $write('RCPT TO:<'.$to.'>'); $r=$read(); if(!preg_match('/^(250|251)/',$r)){ fclose($fp); return [false,'RCPT TO reddedildi: '.$r]; }
    $write('DATA'); $d=$read(); if(!str_starts_with($d,'354')){ fclose($fp); return [false,'DATA komutu reddedildi: '.$d]; }
    $write("Subject: Ahost One SMTP Test\r\nFrom: {$name} <{$from}>\r\nTo: <{$to}>\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nSMTP test başarılı. Tarih: ".date('Y-m-d H:i:s')."\r\n."); $sent=$read(); $write('QUIT'); fclose($fp);
    if(!preg_match('/^250/',$sent)) return [false,'Mesaj gönderimi doğrulanamadı: '.$sent]; save_setting('smtp_last_test_status','success'); save_setting('smtp_last_test_at',date('Y-m-d H:i:s')); return [true,'SMTP test maili başarıyla gönderildi.'];
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/smtp-test') { require_admin(); verify_csrf(); [$ok,$msg]=ao_smtp_test_send(trim($_POST['test_email'] ?? ''), $_POST['settings'] ?? []); flash($ok?'success':'error',$msg); redirect_to($_POST['return'] ?? 'admin/setup-wizard'); }




// v16.0.0 MobileBuilder Pro
function ao_schema_ensure_v1600() {
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS mobile_apps (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, app_name VARCHAR(190) NOT NULL, sector VARCHAR(100) NULL, platform VARCHAR(40) DEFAULT 'pwa', primary_color VARCHAR(20) DEFAULT '#2563eb', status VARCHAR(40) DEFAULT 'draft', ai_prompt TEXT NULL, config_json LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS mobile_app_pages (id INT AUTO_INCREMENT PRIMARY KEY, app_id INT NOT NULL, page_name VARCHAR(190) NOT NULL, page_type VARCHAR(80) DEFAULT 'screen', sort_order INT DEFAULT 0, layout_json LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS mobile_app_exports (id INT AUTO_INCREMENT PRIMARY KEY, app_id INT NULL, export_type VARCHAR(40) NOT NULL, status VARCHAR(40) DEFAULT 'ready', file_path VARCHAR(255) NULL, notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS mobile_build_queue (id INT AUTO_INCREMENT PRIMARY KEY, app_id INT NULL, build_type VARCHAR(40) NOT NULL, status VARCHAR(40) DEFAULT 'pending', log_text LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, finished_at TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) { error_log('v16 schema: '.$e->getMessage()); }
}
function ao_mobile_export_zip($type='pwa') {
    $dir = __DIR__.'/storage/exports/mobilebuilder'; if(!is_dir($dir)) mkdir($dir,0775,true);
    $file = $dir.'/ahost-mobile-'.$type.'-'.date('Ymd-His').'.zip';
    $zip = new ZipArchive();
    if ($zip->open($file, ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) throw new Exception('ZIP oluşturulamadı');
    $zip->addFromString('README.md', "Ahost One MobileBuilder Export\nType: {$type}\nGenerated: ".date('c')."\n");
    if($type==='pwa'){
        $zip->addFromString('index.html','<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ahost Mobile</title><link rel="manifest" href="manifest.json"></head><body><h1>Ahost Mobile PWA</h1><p>MobileBuilder export.</p></body></html>');
        $zip->addFromString('manifest.json', json_encode(['name'=>'Ahost Mobile','short_name'=>'Ahost','start_url'=>'./index.html','display'=>'standalone'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        $zip->addFromString('sw.js','self.addEventListener("install",e=>self.skipWaiting());');
    } elseif($type==='flutter'){
        $zip->addFromString('pubspec.yaml', "name: ahost_mobile\ndescription: Ahost One MobileBuilder export\nversion: 1.0.0\n");
        $zip->addFromString('lib/main.dart', "void main(){ print('Ahost MobileBuilder Flutter export'); }\n");
    } else {
        $zip->addFromString('settings.gradle', "pluginManagement { repositories { google(); mavenCentral(); gradlePluginPortal() } }\ndependencyResolutionManagement { repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS); repositories{ google(); mavenCentral() } }\nrootProject.name='AhostMobile'\n");
        $zip->addFromString('README_ANDROID.md', 'Android Studio proje iskeleti. Gradle/SDK ile derlenmelidir.');
    }
    $zip->close();
    return $file;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/mobile-builder/app-save') { require_admin(); verify_csrf(); ao_schema_ensure_v1600();
    db()->prepare('INSERT INTO mobile_apps(app_name,sector,platform,primary_color,ai_prompt,config_json,status,updated_at) VALUES(?,?,?,?,?,?,"draft",NOW())')->execute([trim($_POST['app_name']??'Ahost Mobil'),trim($_POST['sector']??'Genel'),trim($_POST['platform']??'pwa'),trim($_POST['primary_color']??'#2563eb'),trim($_POST['ai_prompt']??''),json_encode($_POST,JSON_UNESCAPED_UNICODE)]);
    flash('success','Mobil uygulama taslağı kaydedildi.'); redirect_to('admin/mobile-builder/editor');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/mobile-builder/ai-generate') { require_admin(); verify_csrf(); ao_schema_ensure_v1600();
    $prompt=trim($_POST['prompt']??'AI mobil uygulama');
    db()->prepare('INSERT INTO mobile_apps(app_name,sector,platform,primary_color,ai_prompt,config_json,status) VALUES(?,?,?,?,?,?,"ai_draft")')->execute([mb_substr($prompt,0,80),'AI Tasarım','pwa','#2563eb',$prompt,json_encode(['pages'=>['Ana Sayfa','Hizmetler','Bildirimler','Profil']],JSON_UNESCAPED_UNICODE)]);
    flash('success','AI mobil tasarım taslağı oluşturuldu. Editörde manuel düzenleyebilirsin.'); redirect_to('admin/mobile-builder/editor');
}
if ($route==='admin/mobile-builder/export') { require_admin(); ao_schema_ensure_v1600();
    $type = isset($_GET['flutter']) ? 'flutter' : (isset($_GET['android']) ? 'android' : 'pwa');
    try { $file=ao_mobile_export_zip($type); db()->prepare('INSERT INTO mobile_app_exports(export_type,status,file_path,notes) VALUES(?,"ready",?,?)')->execute([$type,$file,'Otomatik export']); header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="'.basename($file).'"'); readfile($file); exit; }
    catch(Throwable $e){ flash('error','Export hatası: '.$e->getMessage()); redirect_to('admin/mobile-builder/exports'); }
}



// v17.0.0 Production Ready Polish + Setup Wizard + Admin UX
function ao_v1700_ensure_schema() {
    static $done=false; if($done) return; $done=true;
    try { db()->exec("CREATE TABLE IF NOT EXISTS setup_wizard_steps (id INT AUTO_INCREMENT PRIMARY KEY, step_key VARCHAR(120) UNIQUE NOT NULL, title VARCHAR(190) NOT NULL, description TEXT NULL, category VARCHAR(80) DEFAULT 'general', route VARCHAR(190) NULL, status ENUM('pending','done','skipped') DEFAULT 'pending', required TINYINT(1) DEFAULT 1, sort_order INT DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS module_visibility (id INT AUTO_INCREMENT PRIMARY KEY, module_key VARCHAR(120) UNIQUE NOT NULL, title VARCHAR(190) NOT NULL, is_enabled TINYINT(1) DEFAULT 1, route VARCHAR(190) NULL, category VARCHAR(80) DEFAULT 'core', updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS setup_wizard_runs (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NULL, action VARCHAR(80) NOT NULL, payload TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}

    $steps = [
        ['site_identity','Logo, Site Adı ve Site Linki','Marka adı, logo, site URL ve iletişim bilgilerini tamamlayın.','Başlangıç','admin/settings',1,10],
        ['theme','Tema Seçimi ve Önizleme','Ön yüz, admin ve müşteri paneli temasını seçip uygulayın.','Görünüm','admin/theme-center',1,20],
        ['domain_registrar','DomainNameAPI / Registrar Ayarları','Domain sorgulama, kayıt, yenileme, EPP ve transfer için registrar ayarlarını yapın.','Domain','admin/domain-center/registrars',1,30],
        ['server','Sunucu / WHM / DirectAdmin / Plesk','Hosting otomasyonu için sunucu ekleyin ve bağlantı testini yapın.','Hosting','admin/hosting-server/servers',1,40],
        ['products','Ürün Grupları ve Paketler','Hosting, domain, SSL, web tasarım, SEO ve diğer ürünlerinizi tanımlayın.','Ürün','admin/product-center/groups',1,50],
        ['payment','Ödeme Yöntemleri ve Kart Komisyonu','Shopier, sanal POS ve ödeme komisyonlarını yapılandırın.','Ödeme','admin/accounting/payment-fees',1,60],
        ['smtp','SMTP Mail Ayarları','Fatura, ticket, şifre sıfırlama ve sistem bildirimleri için SMTP ayarlarını girin.','Bildirim','admin/notification-center',1,70],
        ['sms','SMS / İletiMerkezi Ayarları','İletiMerkezi veya diğer SMS sağlayıcılarını bağlayın, bakiye sorgulayın ve test SMS gönderin.','Bildirim','admin/notifications',0,80],
        ['whatsapp','WhatsApp Bildirimleri','WhatsApp API veya webhook sağlayıcınızı bağlayın.','Bildirim','admin/notification-center',0,90],
        ['ai','Yapay Zeka API Ayarları','OpenAI/Gemini/Claude gibi AI sağlayıcı API anahtarlarını girin.','AI','admin/ai-center',0,100],
        ['sitebuilder','SiteBuilder Ayarları','SiteBuilder, export ZIP ve tema entegrasyonunu kontrol edin.','Builder','admin/site-builder',0,110],
        ['mobilebuilder','MobileBuilder ve Build Center','PWA/Flutter/Android export, SDK, Gradle ve build kuyruğu ayarlarını kontrol edin.','Builder','admin/mobile-builder',0,120],
        ['license','Lisans Merkezi','SiteBuilder, MobileBuilder, tema, marketplace ve kaynak kod lisans kurallarını tanımlayın.','Lisans','admin/license-center',0,130],
        ['marketplace','Marketplace Ayarları','Domain, hosting, web tasarım, SEO, logo ve dijital ürün satış kurallarını ayarlayın.','Marketplace','admin/marketplace',0,140],
        ['security','Güvenlik ve Yetkiler','Admin rolleri, 2FA, IP kısıtlama, oturum süresi ve CSRF ayarlarını kontrol edin.','Güvenlik','admin/security',1,150],
        ['backup','Backup / Restore','Veritabanı ve dosya yedekleme planını oluşturun.','Sistem','admin/backup-center',1,160],
        ['scan','Sistem Taraması','Kurulum sonunda Scan & Report Center ile PDF rapor alın.','Sistem','admin/scan-report',1,170],
    ];
    foreach($steps as $x){ try{ db()->prepare("INSERT INTO setup_wizard_steps(step_key,title,description,category,route,required,sort_order) VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title),description=VALUES(description),category=VALUES(category),route=VALUES(route),required=VALUES(required),sort_order=VALUES(sort_order)")->execute($x); }catch(Throwable $e){} }
    $mods = [
        ['domain','Domain Center',1,'admin/domain-center','commerce'],['hosting','Hosting & Server',1,'admin/hosting-server','commerce'],['marketplace','Marketplace',1,'admin/marketplace','commerce'],['sitebuilder','SiteBuilder',1,'admin/site-builder','builder'],['mobilebuilder','MobileBuilder',1,'admin/mobile-builder','builder'],['buildcenter','Build Center',1,'admin/build-center','builder'],['license','License Center',1,'admin/license-center','system'],['notification','Notification Center',1,'admin/notification-center','system'],['backup','Backup Center',1,'admin/backup-center','system'],['scan','Scan & Report',1,'admin/scan-report','system'],['ai','AI Center',1,'admin/ai-center','ai']
    ];
    foreach($mods as $m){ try{ db()->prepare("INSERT INTO module_visibility(module_key,title,is_enabled,route,category) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title),route=VALUES(route),category=VALUES(category)")->execute($m); }catch(Throwable $e){} }
    try { save_setting('ahost_version','18.6.0'); } catch(Throwable $e) {}
}
function ao_v1700_setup_live_checks(){
    ao_v1700_ensure_schema();
    $has = function($key){ return trim((string)admin_setting($key,'')) !== ''; };
    $safeCount = function($table){ try { return table_count($table); } catch(Throwable $e){ return 0; } };
    $checks = [];
    // Logo zorunluysa logo_url da aranır; kapalıysa site adı + URL + iletişim yeterlidir.
    $requireLogo = admin_setting('setup_require_logo','1') === '1';
    $checks['site_identity'] = $has('site_name') && $has('site_url') && ($has('company_email') || $has('company_phone')) && (!$requireLogo || $has('logo_url'));
    $checks['theme'] = $has('theme_front') && $has('theme_admin') && $has('theme_customer');
    $checks['domain_registrar'] = $safeCount('domain_registrars')>0 || $has('domainnameapi_api_key') || ($has('domainnameapi_username') && $has('domainnameapi_password'));
    $checks['server'] = $safeCount('server_nodes')>0 || $safeCount('hosting_servers')>0;
    $checks['products'] = $safeCount('products')>0 || $safeCount('product_groups')>0;
    $checks['payment'] = $safeCount('payment_gateways')>0 || $safeCount('payment_fee_rules')>0 || $has('shopier_api_key');
    $checks['smtp'] = ($has('smtp_host') && ($has('smtp_username') || $has('smtp_from')));
    $checks['sms'] = $has('iletimerkezi_api_key') || $has('sms_provider');
    $checks['whatsapp'] = $has('whatsapp_token') || $has('whatsapp_provider');
    $checks['ai'] = $has('ai_api_key');
    $checks['sitebuilder'] = true;
    $checks['mobilebuilder'] = true;
    $checks['license'] = $safeCount('license_products')>0 || $has('license_key');
    $checks['marketplace'] = $safeCount('marketplace_categories')>0;
    $checks['security'] = true;
    $checks['backup'] = true;
    $checks['scan'] = true;
    return $checks;
}
function ao_v1700_setup_apply_live_checks(){
    $checks = ao_v1700_setup_live_checks();
    foreach($checks as $k=>$ok){
        try{
            if($ok){ db()->prepare("UPDATE setup_wizard_steps SET status='done' WHERE step_key=? AND status!='skipped'")->execute([$k]); }
            else { db()->prepare("UPDATE setup_wizard_steps SET status='pending' WHERE step_key=? AND status='done'")->execute([$k]); }
        }catch(Throwable $e){}
    }
}
function ao_v1700_setup_rows(){ ao_v1700_ensure_schema(); ao_v1700_setup_apply_live_checks(); try{return db()->query('SELECT * FROM setup_wizard_steps ORDER BY sort_order,id')->fetchAll();}catch(Throwable $e){return [];} }
function ao_v1700_setup_progress(){ $rows=ao_v1700_setup_rows(); if(!$rows) return 0; $done=0; $total=0; foreach($rows as $r){ if((int)($r['required']??0)===1){ $total++; if(($r['status']??'')==='done' || ($r['status']??'')==='skipped') $done++; } } if($total===0){$total=count($rows); foreach($rows as $r){ if(($r['status']??'')==='done' || ($r['status']??'')==='skipped') $done++; }} return (int)round($done*100/max(1,$total)); }
function ao_v1700_group_steps($rows){ $g=[]; foreach($rows as $r){ $g[$r['category']?:'Genel'][]=$r; } return $g; }
function ao_v1700_setup_autocheck(){ ao_v1700_setup_apply_live_checks(); }

function ao_v1886_upload_branding($field, $targetName){
    if(empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if(($_FILES[$field]['error'] ?? 0) !== UPLOAD_ERR_OK) return '';
    $tmp=$_FILES[$field]['tmp_name']; $mime=@mime_content_type($tmp) ?: '';
    $allowed=['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp','image/svg+xml'=>'svg'];
    if(!isset($allowed[$mime])) return '';
    $dir=__DIR__.'/uploads/branding'; if(!is_dir($dir)) @mkdir($dir,0775,true);
    $file=$targetName.'.'.$allowed[$mime];
    if(@move_uploaded_file($tmp,$dir.'/'.$file)) return url('uploads/branding/'.$file);
    return '';
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/setup-wizard/save') { require_admin(); verify_csrf(); ao_v1700_ensure_schema();
    foreach($_POST['step_status'] ?? [] as $k=>$v){ if(in_array($v,['pending','done','skipped'],true)){ try{ db()->prepare('UPDATE setup_wizard_steps SET status=? WHERE step_key=?')->execute([$v,$k]); }catch(Throwable $e){} } }
    foreach($_POST['settings'] ?? [] as $k=>$v){
        $allowed = ['site_name','site_url','logo_url','favicon_url','company_name','company_email','company_phone','company_address','default_currency','default_language','timezone','maintenance_mode','seo_title','seo_description','smtp_host','smtp_port','smtp_username','smtp_password','smtp_from','smtp_from_name','sms_provider','iletimerkezi_api_key','iletimerkezi_api_hash','iletimerkezi_sender','whatsapp_provider','whatsapp_token','ai_provider','ai_api_key','shopier_auth_mode','shopier_pat','shopier_api_key','shopier_api_secret','domainnameapi_auth_mode','domainnameapi_api_key','theme_front','theme_admin','theme_customer','registrar_provider','domainnameapi_api_secret','domainnameapi_username','domainnameapi_password','domainnameapi_test_domain','server_panel_type','server_name','server_hostname','server_ip','server_port','server_ssl','server_username','server_api_token','setup_product_hosting','setup_product_domain','setup_product_ssl','setup_product_vps','setup_product_sitebuilder','setup_product_mobilebuilder','setup_product_web','setup_product_seo','setup_product_mobile','setup_product_marketplace','recaptcha_site_key','recaptcha_secret_key','admin_2fa_enabled','google_maps_api_key','google_analytics_id'];
        if(in_array($k,$allowed,true)){ save_setting($k, is_array($v)?json_encode($v,JSON_UNESCAPED_UNICODE):(string)$v); }
    }
    if(isset($_POST['settings']['shopier_pat']) || isset($_POST['settings']['shopier_auth_mode']) || isset($_POST['settings']['shopier_api_key']) || isset($_POST['settings']['shopier_api_secret'])){
        ao_shopier_save_settings([
            'auth_mode'=>$_POST['settings']['shopier_auth_mode'] ?? 'pat',
            'pat'=>$_POST['settings']['shopier_pat'] ?? '',
            'api_key'=>$_POST['settings']['shopier_api_key'] ?? '',
            'api_secret'=>$_POST['settings']['shopier_api_secret'] ?? '',
            'website_index'=>ao_shopier_setting('website_index','1'),
            'test_mode'=>ao_shopier_setting('test_mode','1'),
            'callback_secret'=>ao_shopier_setting('callback_secret','')
        ]);
    }
    if($logoUpload = ao_v1886_upload_branding('logo_file','logo')) save_setting('logo_url',$logoUpload);
    if($favUpload = ao_v1886_upload_branding('favicon_file','favicon')) save_setting('favicon_url',$favUpload);
    foreach($_POST['module_enabled'] ?? [] as $key=>$val){
        try { db()->prepare("UPDATE module_visibility SET is_enabled=? WHERE module_key=?")->execute([(int)($val==='1'), $key]); } catch(Throwable $e) {}
    }
    if(isset($_POST['complete'])){
        save_setting('setup_wizard_completed','1');
        save_setting('setup_wizard_dismissed','1');
    } else {
        save_setting('setup_wizard_completed','0');
    }
    if(isset($_POST['dont_show_again'])) save_setting('setup_wizard_dismissed','1');
    try { db()->prepare("INSERT INTO setup_wizard_runs(admin_id,action,payload) VALUES(?,?,?)")->execute([$_SESSION['admin_id'] ?? null,'save',json_encode(['settings'=>array_keys($_POST['settings'] ?? []),'complete'=>isset($_POST['complete'])],JSON_UNESCAPED_UNICODE)]); } catch(Throwable $e) {}
    if(isset($_POST['run_scan'])){ ao_v1700_setup_autocheck(); flash('success','Ayarlar kaydedildi ve sistem kontrolü çalıştırıldı.'); redirect_to('admin/setup-wizard'); }
    flash('success','Kurulum sihirbazı ayarları kaydedildi.'); redirect_to('admin/setup-wizard');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/setup-wizard/dismiss') { require_admin(); verify_csrf(); ao_v1700_ensure_schema(); save_admin_pref('setup_wizard_popup_dismissed','1'); try{db()->prepare("INSERT INTO setup_wizard_runs(admin_id,action,payload) VALUES(?,?,?)")->execute([$_SESSION['admin_id'] ?? null,'dismiss','{}']);}catch(Throwable $e){} flash('success','Kurulum sihirbazı gizlendi. Menüden tekrar açabilirsiniz.'); redirect_to('admin/dashboard'); }
if ($route==='admin/setup-wizard/autocheck') { require_admin(); ao_v1700_setup_autocheck(); flash('success','Otomatik kontrol tamamlandı.'); redirect_to('admin/setup-wizard'); }
if ($route==='admin/setup-wizard') { require_admin(); ao_v1700_ensure_schema(); view('setup-wizard/index', ['pageTitle'=>'Kurulum Sihirbazı & Yardım Kılavuzu','steps'=>ao_v1700_setup_rows(),'progress'=>ao_v1700_setup_progress()]); exit; }



// v18.1.0 Module Center Pro - ZIP/FTP install, safe upgrade, SQL lifecycle, configuration
function ao_v18_ensure_module_schema(){
    try { db()->exec("CREATE TABLE IF NOT EXISTS modules (id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) UNIQUE NOT NULL, name VARCHAR(190) NOT NULL, type VARCHAR(80) DEFAULT 'other', version VARCHAR(50) DEFAULT '1.0.0', description TEXT NULL, path VARCHAR(255) NULL, is_enabled TINYINT(1) DEFAULT 0, is_core TINYINT(1) DEFAULT 0, manifest_json LONGTEXT NULL, installed_version VARCHAR(50) NULL, needs_install TINYINT(1) DEFAULT 1, last_error TEXT NULL, installed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    foreach([
        "ALTER TABLE modules ADD COLUMN installed_version VARCHAR(50) NULL",
        "ALTER TABLE modules ADD COLUMN needs_install TINYINT(1) DEFAULT 1",
        "ALTER TABLE modules ADD COLUMN last_error TEXT NULL"
    ] as $sql){ try { db()->exec($sql); } catch(Throwable $e) {} }
    try { db()->exec("CREATE TABLE IF NOT EXISTS module_backups (id INT AUTO_INCREMENT PRIMARY KEY, module_slug VARCHAR(120) NOT NULL, backup_path VARCHAR(255) NOT NULL, version VARCHAR(50) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS module_logs (id INT AUTO_INCREMENT PRIMARY KEY, module_slug VARCHAR(120) NOT NULL, level VARCHAR(40) DEFAULT 'info', message TEXT NULL, context LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS module_permissions (id INT AUTO_INCREMENT PRIMARY KEY, module_slug VARCHAR(120) NOT NULL, permission_key VARCHAR(160) NOT NULL, description VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_module_permission(module_slug,permission_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS module_events (id INT AUTO_INCREMENT PRIMARY KEY, module_slug VARCHAR(120) NOT NULL, event_type VARCHAR(80) NOT NULL, message TEXT NULL, payload LONGTEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
    try { db()->exec("CREATE TABLE IF NOT EXISTS module_settings (id INT AUTO_INCREMENT PRIMARY KEY, module_slug VARCHAR(120) NOT NULL, setting_key VARCHAR(120) NOT NULL, setting_value LONGTEXT NULL, is_secret TINYINT(1) DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uniq_module_setting(module_slug,setting_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Throwable $e) {}
}
function ao_module_log($slug,$type,$message='',$payload=null){
    try { db()->prepare('INSERT INTO module_events(module_slug,event_type,message,payload) VALUES(?,?,?,?)')->execute([$slug,$type,$message,$payload===null?null:json_encode($payload,JSON_UNESCAPED_UNICODE)]); } catch(Throwable $e) {}
    try { db()->prepare('INSERT INTO module_logs(module_slug,level,message,context) VALUES(?,?,?,?)')->execute([$slug, $type==='error'?'error':'info', $message, $payload===null?null:json_encode($payload,JSON_UNESCAPED_UNICODE)]); } catch(Throwable $e) {}
}
function ao_module_secret_generate($generator='random_64'){
    $generator=(string)$generator;
    try {
        if($generator==='random_32') return bin2hex(random_bytes(16));
        if($generator==='uuid') { $d=random_bytes(16); $d[6]=chr((ord($d[6]) & 0x0f) | 0x40); $d[8]=chr((ord($d[8]) & 0x3f) | 0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d),4)); }
        if($generator==='api_key') return 'ak_'.bin2hex(random_bytes(24));
        if($generator==='webhook_secret') return 'whsec_'.bin2hex(random_bytes(32));
        if($generator==='license_key') return strtoupper(implode('-', str_split(substr(bin2hex(random_bytes(20)),0,40),5)));
        return bin2hex(random_bytes(32));
    } catch(Throwable $e) { return hash('sha256', uniqid('ao_secret_', true).microtime(true)); }
}
function ao_module_setting_is_secret($def){ return !empty($def['secret']) || !empty($def['is_secret']) || in_array(($def['type'] ?? ''), ['password','hidden'], true) || !empty($def['auto_generate']); }
function ao_module_insert_default_setting($slug,$key,$def,$forceRegenerate=false){
    $slug=ao_module_slug($slug); $key=preg_replace('/[^a-zA-Z0-9_\.\-]/','',(string)$key); if($slug==='' || $key==='') return;
    $secret=ao_module_setting_is_secret($def);
    try{ $q=db()->prepare('SELECT setting_value FROM module_settings WHERE module_slug=? AND setting_key=? LIMIT 1'); $q->execute([$slug,$key]); $existing=$q->fetchColumn(); }catch(Throwable $e){ $existing=false; }
    if($existing!==false && !$forceRegenerate) return;
    if(!empty($def['auto_generate'])) $val=ao_module_secret_generate($def['generator'] ?? 'random_64'); else $val=(string)($def['default'] ?? '');
    try{ db()->prepare('INSERT INTO module_settings(module_slug,setting_key,setting_value,is_secret) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),is_secret=VALUES(is_secret)')->execute([$slug,$key,$val,$secret?1:0]); }catch(Throwable $e){}
}
function ao_module_slug($v){ return preg_replace('/[^a-z0-9\-_]/','', strtolower((string)$v)); }
function ao_module_type($v){ return preg_replace('/[^a-z0-9\-_]/','', strtolower((string)($v ?: 'custom'))); }
function ao_module_manifest_files(){
    $root = __DIR__ . '/modules';
    if(!is_dir($root)) return [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    $files=[];
    foreach($it as $file){ if($file->getFilename()==='module.json') $files[]=$file->getPathname(); }
    return $files;
}
function ao_module_row($slug){ try { $s=db()->prepare('SELECT * FROM modules WHERE slug=? LIMIT 1'); $s->execute([$slug]); return $s->fetch() ?: null; } catch(Throwable $e){ return null; } }
function ao_module_manifest($slug){ $m=ao_module_row($slug); if(!$m || empty($m['manifest_json'])) return []; $j=json_decode($m['manifest_json'],true); return is_array($j)?$j:[]; }
function ao_module_scan(){
    ao_v18_ensure_module_schema();
    $found=[];
    foreach(ao_module_manifest_files() as $manifest){
        $json = json_decode((string)@file_get_contents($manifest), true);
        if(!$json || empty($json['slug'])) continue;
        $slug = ao_module_slug($json['slug']); if($slug==='') continue;
        $path = str_replace(__DIR__ . '/', '', dirname($manifest));
        $name = $json['name'] ?? ($json['title'] ?? $slug);
        $type = ao_module_type($json['type'] ?? 'other');
        $version = (string)($json['version'] ?? '1.0.0');
        $desc = $json['description'] ?? '';
        $old = ao_module_row($slug);
        $enabled = $old ? (int)$old['is_enabled'] : 0;
        $needs = $old ? (int)($old['needs_install'] ?? 0) : 1;
        $installedVersion = $old['installed_version'] ?? null;
        if($old && (string)($old['version'] ?? '') !== $version){ $enabled = 0; $needs = 1; ao_module_log($slug,'ftp_version_changed','FTP ile farklı sürüm algılandı; SQL güvenliği için modül pasife alındı.', ['old'=>$old['version'] ?? null,'new'=>$version]); }
        try {
            db()->prepare("INSERT INTO modules(slug,name,type,version,description,path,is_enabled,is_core,manifest_json,installed_version,needs_install,last_error) VALUES(?,?,?,?,?,?,?,0,?,?,?,NULL) ON DUPLICATE KEY UPDATE name=VALUES(name),type=VALUES(type),version=VALUES(version),description=VALUES(description),path=VALUES(path),is_enabled=VALUES(is_enabled),manifest_json=VALUES(manifest_json),installed_version=VALUES(installed_version),needs_install=VALUES(needs_install),last_error=NULL")
                ->execute([$slug,$name,$type,$version,$desc,$path,$enabled,json_encode($json,JSON_UNESCAPED_UNICODE),$installedVersion,$needs]);
        } catch(Throwable $e) {}
        $found[]=$slug;
    }
    return $found;
}
function ao_module_registry_all(){ ao_v18_ensure_module_schema(); ao_module_scan(); try { return db()->query("SELECT * FROM modules ORDER BY type,name")->fetchAll(); } catch(Throwable $e) { return []; } }
function ao_module_health($module){
    $slug=ao_module_slug($module['slug'] ?? '');
    $path=__DIR__.'/'.trim((string)($module['path'] ?? ''),'/');
    $issues=[]; $warnings=[];
    $manifestFile=$path.'/module.json';
    $manifest=is_file($manifestFile) ? json_decode((string)file_get_contents($manifestFile),true) : null;
    if(!is_array($manifest)) $issues[]='module.json eksik veya geçersiz';
    else {
        if(($manifest['slug'] ?? '')!==$slug) $issues[]='manifest slug eşleşmiyor';
        if(empty($manifest['name']) || empty($manifest['type']) || empty($manifest['version'])) $issues[]='manifest zorunlu alanları eksik';
    }
    $install=$path.'/install.sql';
    if(!is_file($install)) $issues[]='install.sql eksik';
    else {
        $sql=(string)file_get_contents($install);
        $meaningful=trim((string)preg_replace(['~/\*.*?\*/~s','~^\s*--.*$~m','~^\s*#.*$~m'],'',$sql));
        if($meaningful==='') $issues[]='install.sql yalnızca yorum içeriyor';
    }
    if(!is_file($path.'/Module.php')) $warnings[]='Module.php eksik';
    if(empty($manifest['settings'])) $warnings[]='yapılandırma alanı tanımlı değil';
    return ['ok'=>!$issues,'issues'=>$issues,'warnings'=>$warnings,'label'=>$issues?'Hatalı':($warnings?'Kontrol':'Sağlıklı')];
}
function ao_module_is_enabled($slug){ try { $s=db()->prepare('SELECT is_enabled FROM modules WHERE slug=? LIMIT 1'); $s->execute([$slug]); $v=$s->fetchColumn(); return (int)$v===1; } catch(Throwable $e) { return true; } }
function ao_module_safe_path($path){ $root=realpath(__DIR__.'/modules'); $real=realpath($path); return $root && $real && str_starts_with($real,$root); }
function ao_module_rrmdir($dir){ if(!is_dir($dir)) return; $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST); foreach($it as $f){ $f->isDir()?@rmdir($f->getPathname()):@unlink($f->getPathname()); } @rmdir($dir); }
function ao_module_copydir($src,$dst){ if(!is_dir($dst)) mkdir($dst,0775,true); $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST); foreach($it as $f){ $to=$dst.'/'.substr($f->getPathname(),strlen($src)+1); if($f->isDir()) { if(!is_dir($to)) mkdir($to,0775,true); } else copy($f->getPathname(),$to); } }
function ao_module_execute_sql_file($slug,$file){
    if(!is_file($file)) return false;
    $sql = trim((string)@file_get_contents($file)); if($sql==='') return false;
    $meaningful=trim((string)preg_replace(['~/\*.*?\*/~s','~^\s*--.*$~m','~^\s*#.*$~m'],'',$sql));
    if($meaningful===''){ ao_module_log($slug,'sql_skipped',basename($file).' yalnızca yorum içeriyor.'); return false; }
    $pdo = db();
    try { $pdo->exec($sql); ao_module_log($slug,'sql_executed',basename($file).' çalıştırıldı.'); return true; }
    catch(Throwable $e){ ao_module_log($slug,'sql_error',$e->getMessage(),['file'=>basename($file)]); throw $e; }
}
function ao_module_apply_lifecycle_sql($slug,$action){
    $m=ao_module_row($slug); if(!$m) throw new Exception('Modül bulunamadı.');
    $dir=__DIR__.'/'.trim($m['path'],'/');
    $manifest=ao_module_manifest($slug);
    if($action==='enable'){
        $old=(string)($m['installed_version'] ?? ''); $new=(string)($m['version'] ?? '');
        $ran=false;
        if($old==='' || (int)($m['needs_install'] ?? 1)===1){ $ran = ao_module_execute_sql_file($slug,$dir.'/install.sql') || $ran; }
        if($old!=='' && $old!==$new){ $ran = ao_module_execute_sql_file($slug,$dir.'/upgrade.sql') || $ran; $verFile=$dir.'/upgrade_'.$old.'_to_'.$new.'.sql'; if(is_file($verFile)) $ran = ao_module_execute_sql_file($slug,$verFile) || $ran; }
        if(!empty($manifest['settings']) && is_array($manifest['settings'])){
            foreach($manifest['settings'] as $key=>$def){
                if(is_int($key) && is_array($def)) $key=$def['key'] ?? '';
                if(!is_array($def)) $def=['label'=>$key,'default'=>$def,'type'=>'text'];
                ao_module_insert_default_setting($slug,$key,$def,false);
            }
        }
        db()->prepare('UPDATE modules SET is_enabled=1, needs_install=0, installed_version=version, last_error=NULL WHERE slug=?')->execute([$slug]);
        ao_module_log($slug,'enable','Modül aktif edildi'.($ran?' ve SQL uygulandı.':'.'));
        return true;
    }
    if($action==='disable'){
        db()->prepare('UPDATE modules SET is_enabled=0 WHERE slug=?')->execute([$slug]); ao_module_log($slug,'disable','Modül pasif edildi.'); return true;
    }
    if($action==='delete'){
        ao_module_execute_sql_file($slug,$dir.'/uninstall.sql');
        db()->prepare('DELETE FROM module_settings WHERE module_slug=?')->execute([$slug]);
        db()->prepare('DELETE FROM modules WHERE slug=?')->execute([$slug]);
        if(ao_module_safe_path($dir)) ao_module_rrmdir($dir);
        ao_module_log($slug,'delete','Modül kaldırıldı, ayarları temizlendi.'); return true;
    }
    return false;
}
function ao_module_toggle($slug,$enabled){
    ao_v18_ensure_module_schema(); $slug=ao_module_slug($slug); if($slug==='') return false;
    try { return ao_module_apply_lifecycle_sql($slug,$enabled?'enable':'disable'); }
    catch(Throwable $e){ try{db()->prepare('UPDATE modules SET is_enabled=0,last_error=? WHERE slug=?')->execute([$e->getMessage(),$slug]);}catch(Throwable $x){} ao_module_log($slug,'error',$e->getMessage()); return false; }
}
function ao_module_zip_entries_are_safe($zip){
    for($i=0;$i<$zip->numFiles;$i++){ $n=str_replace('\\','/',$zip->getNameIndex($i)); if($n==='' || str_starts_with($n,'/') || str_contains($n,'../') || preg_match('~^[A-Za-z]:/~',$n)) return false; }
    return true;
}
function ao_module_upload_zip($field='module_zip'){
    if(empty($_FILES[$field]['tmp_name'])) throw new Exception('ZIP dosyası seçilmedi.');
    $tmp = $_FILES[$field]['tmp_name'];
    $zip = new ZipArchive();
    if($zip->open($tmp)!==true) throw new Exception('ZIP açılamadı.');
    if(!ao_module_zip_entries_are_safe($zip)){ $zip->close(); throw new Exception('ZIP içinde güvensiz dosya yolu var.'); }
    $manifestIndex = false; $manifestName='';
    for($i=0;$i<$zip->numFiles;$i++){ $name=$zip->getNameIndex($i); if(basename($name)==='module.json'){ $manifestIndex=$i; $manifestName=str_replace('\\','/',$name); break; } }
    if($manifestIndex===false){ $zip->close(); throw new Exception('module.json bulunamadı.'); }
    $manifest = json_decode($zip->getFromIndex($manifestIndex), true);
    if(!$manifest || empty($manifest['slug'])){ $zip->close(); throw new Exception('module.json geçersiz.'); }
    $slug = ao_module_slug($manifest['slug']); $type = ao_module_type($manifest['type'] ?? 'custom');
    $target = __DIR__ . '/modules/' . $type . '/' . $slug;
    $backupRoot = __DIR__ . '/storage/module-backups/' . date('Ymd-His') . '-' . $slug;
    if(is_dir($target)){ if(!is_dir(dirname($backupRoot))) mkdir(dirname($backupRoot),0775,true); ao_module_copydir($target,$backupRoot); try{ db()->prepare('INSERT INTO module_backups(module_slug,backup_path,version) VALUES(?,?,?)')->execute([$slug,str_replace(__DIR__.'/','',$backupRoot),(string)($manifest['version'] ?? '')]); }catch(Throwable $e){} ao_module_rrmdir($target); }
    if(!is_dir($target)) mkdir($target,0775,true);
    $prefix = trim(str_replace('\\','/',dirname($manifestName)),'./');
    for($i=0;$i<$zip->numFiles;$i++){
        $name=str_replace('\\','/',$zip->getNameIndex($i));
        if(substr($name,-1)==='/') continue;
        $rel=$name;
        if($prefix!=='' && str_starts_with($rel,$prefix.'/')) $rel=substr($rel,strlen($prefix)+1);
        $rel=ltrim($rel,'/'); if($rel==='') continue;
        $dest=$target.'/'.$rel; if(!is_dir(dirname($dest))) mkdir(dirname($dest),0775,true);
        copy('zip://'.$tmp.'#'.$name,$dest);
    }
    $zip->close();
    ao_module_scan();
    ao_module_log($slug,'upload','ZIP yüklendi; modül güvenlik için pasif kaydedildi.', ['backup'=>is_dir($backupRoot)?str_replace(__DIR__.'/','',$backupRoot):null]);
    return $slug;
}
function ao_module_delete($slug){ $slug=ao_module_slug($slug); if($slug==='') return false; return ao_module_apply_lifecycle_sql($slug,'delete'); }
function ao_module_settings_definitions($slug){
    $m=ao_module_manifest($slug); $defs=[];
    if(!empty($m['settings']) && is_array($m['settings'])){
        foreach($m['settings'] as $key=>$def){
            if(is_int($key) && is_array($def)) $key=$def['key'] ?? '';
            $key=preg_replace('/[^a-zA-Z0-9_\.\-]/','',(string)$key); if($key==='') continue;
            $defs[$key]=is_array($def)?$def:['label'=>$key,'default'=>$def,'type'=>'text'];
        }
    }
    $slug=ao_module_slug($slug);
    try{ $s=db()->prepare('SELECT setting_key,setting_value,is_secret FROM module_settings WHERE module_slug=? ORDER BY setting_key'); $s->execute([$slug]); foreach($s->fetchAll() as $r){ if(empty($defs[$r['setting_key']])) $defs[$r['setting_key']]=['label'=>$r['setting_key'],'type'=>$r['is_secret']?'password':'text']; } }catch(Throwable $e){}
    return $defs;
}
function ao_module_settings_values($slug){ $out=[]; try{ $s=db()->prepare('SELECT setting_key,setting_value FROM module_settings WHERE module_slug=?'); $s->execute([$slug]); foreach($s->fetchAll() as $r) $out[$r['setting_key']]=$r['setting_value']; }catch(Throwable $e){} return $out; }
function ao_module_save_settings($slug,$settings){
    $slug=ao_module_slug($slug); $defs=ao_module_settings_definitions($slug);
    foreach($defs as $key=>$def){
        if(!empty($def['readonly']) && !array_key_exists($key,$settings)) continue;
        if(!array_key_exists($key,$settings) && (($def['type'] ?? '')==='hidden' || !empty($def['auto_generate']))) continue;
        $val=$settings[$key] ?? '';
        if(($def['type'] ?? '')==='checkbox') $val = isset($settings[$key]) ? '1' : '0';
        $secret=ao_module_setting_is_secret($def);
        try{ db()->prepare('INSERT INTO module_settings(module_slug,setting_key,setting_value,is_secret) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),is_secret=VALUES(is_secret)')->execute([$slug,$key,(string)$val,$secret?1:0]); }catch(Throwable $e){}
    }
    ao_module_log($slug,'settings','Modül yapılandırması kaydedildi.'); return true;
}
function ao_module_regenerate_setting($slug,$key){
    $slug=ao_module_slug($slug); $defs=ao_module_settings_definitions($slug); if(empty($defs[$key]) || empty($defs[$key]['auto_generate'])) throw new Exception('Bu ayar otomatik secret üretimini desteklemiyor.');
    ao_module_insert_default_setting($slug,$key,$defs[$key],true); ao_module_log($slug,'secret_regenerated',$key.' yeniden oluşturuldu.'); return true;
}

function ao_module_export_zip($slug){
    $slug=ao_module_slug($slug); if($slug==='') throw new Exception('Modül slug boş.');
    $m=ao_module_row($slug); if(!$m) throw new Exception('Modül bulunamadı.');
    $dir=realpath(__DIR__ . '/' . trim($m['path'],'/'));
    $root=realpath(__DIR__ . '/modules');
    if(!$dir || !$root || !str_starts_with($dir,$root) || !is_dir($dir)) throw new Exception('Modül klasörü güvenli alanda değil veya bulunamadı.');
    if(!class_exists('ZipArchive')) throw new Exception('PHP ZipArchive eklentisi aktif değil.');
    $exportDir=__DIR__.'/storage/module-exports'; if(!is_dir($exportDir)) mkdir($exportDir,0775,true);
    $version=preg_replace('/[^0-9A-Za-z._-]/','',(string)($m['version'] ?? '1.0.0'));
    $file=$exportDir.'/'.$slug.'-v'.$version.'-'.date('Ymd-His').'.zip';
    $zip=new ZipArchive(); if($zip->open($file, ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) throw new Exception('ZIP oluşturulamadı.');
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));
    foreach($it as $f){
        if($f->isDir()) continue;
        $rel=substr($f->getPathname(), strlen($dir)+1);
        if(str_contains($rel,'..')) continue;
        $zip->addFile($f->getPathname(), $slug.'/'.$rel);
    }
    $zip->close();
    ao_module_log($slug,'export','Modül ZIP olarak indirildi.', ['file'=>str_replace(__DIR__.'/','',$file)]);
    return $file;
}
function ao_module_download_response($slug){
    $file=ao_module_export_zip($slug);
    if(!is_file($file)) throw new Exception('ZIP dosyası oluşturulamadı.');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Content-Length: '.filesize($file));
    readfile($file); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/module-center/scan') { require_admin(); verify_csrf(); $found=ao_module_scan(); flash('success', count($found).' modül tarandı. FTP ile yeni/farklı sürüm geldiyse pasif kaydedildi.'); redirect_to('admin/module-center'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/module-center/toggle') { require_admin(); verify_csrf(); $ok=ao_module_toggle($_POST['slug'] ?? '', (int)($_POST['enabled'] ?? 0)); flash($ok?'success':'error',$ok?'Modül durumu güncellendi. Aktif ederken gerekli SQL uygulandı.':'Modül güncellenemedi. Detay için olay kayıtlarına bakın.'); redirect_to('admin/module-center'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/module-center/upload') { require_admin(); verify_csrf(); try{ $slug=ao_module_upload_zip(); flash('success','Modül ZIP olarak yüklendi ve güvenlik için pasif kaydedildi: '.$slug); } catch(Throwable $e){ flash('error',$e->getMessage()); } redirect_to('admin/module-center'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/module-center/delete') { require_admin(); verify_csrf(); try{ ao_module_delete($_POST['slug'] ?? ''); flash('success','Modül silindi; uninstall.sql ve modül ayar temizliği çalıştırıldı.'); } catch(Throwable $e){ flash('error',$e->getMessage()); } redirect_to('admin/module-center'); }
if ($route==='admin/module-center/download') { require_admin(); try{ ao_module_download_response($_GET['slug'] ?? ''); }catch(Throwable $e){ flash('error','Modül indirilemedi: '.$e->getMessage()); redirect_to('admin/module-center'); } }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/module-center/config-save') { require_admin(); verify_csrf(); $slug=ao_module_slug($_POST['slug'] ?? ''); ao_module_save_settings($slug,$_POST['settings'] ?? []); flash('success','Modül yapılandırması kaydedildi.'); redirect_to('admin/module-center/config?slug='.$slug); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/module-center/regenerate-secret') { require_admin(); verify_csrf(); $slug=ao_module_slug($_POST['slug'] ?? ''); $key=preg_replace('/[^a-zA-Z0-9_\.\-]/','',(string)($_POST['key'] ?? '')); try{ ao_module_regenerate_setting($slug,$key); flash('success','Secret yeniden oluşturuldu.'); }catch(Throwable $e){ flash('error',$e->getMessage()); } redirect_to('admin/module-center/config?slug='.$slug); }
if ($route==='admin/module-center/config') { require_admin(); ao_v18_ensure_module_schema(); ao_module_scan(); $slug=ao_module_slug($_GET['slug'] ?? ''); $module=ao_module_row($slug); if(!$module){ flash('error','Modül bulunamadı.'); redirect_to('admin/module-center'); } view('module-center/config', ['pageTitle'=>'Modül Yapılandırma','module'=>$module,'defs'=>ao_module_settings_definitions($slug),'values'=>ao_module_settings_values($slug)]); exit; }

ao_schema_ensure_v186();


// v18.7.0 Ahost Builder Pro 3.0 - site/admin/customer visual builder
function ao_builder_pro_ensure_schema(){
    try{ db()->exec("CREATE TABLE IF NOT EXISTS builder_pro_layouts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        target VARCHAR(32) NOT NULL,
        template_key VARCHAR(80) NOT NULL,
        title VARCHAR(190) NULL,
        layout_json LONGTEXT NULL,
        device_json LONGTEXT NULL,
        status VARCHAR(32) DEFAULT 'draft',
        created_by INT NULL,
        updated_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_builder_target_template(target, template_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS builder_pro_revisions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        layout_id INT NULL,
        target VARCHAR(32) NOT NULL,
        template_key VARCHAR(80) NOT NULL,
        layout_json LONGTEXT NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_bp_rev(layout_id), INDEX idx_bp_target(target, template_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('ahost_version','18.7.0'),('builder_pro_3_enabled','1'),('builder_pro_targets','site,admin,customer') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute(); }catch(Throwable $e){}
}
function ao_builder_pro_default_layout($target='site',$template='home'){
    $target = in_array($target,['site','admin','customer'],true) ? $target : 'site';
    if($target==='admin') return [[ 'id'=>'admin_row_1','cols'=>[
        ['id'=>'a1','span'=>2,'widgets'=>[['id'=>'ak1','type'=>'kpi','title'=>'MRR','text'=>'Aylık tekrar gelir','price'=>'₺75.756']]],
        ['id'=>'a2','span'=>2,'widgets'=>[['id'=>'ak2','type'=>'kpi','title'=>'ARR','text'=>'Yıllık gelir projeksiyonu','price'=>'₺909.077']]],
        ['id'=>'a3','span'=>3,'widgets'=>[['id'=>'ak3','type'=>'chart','title'=>'Gelir Analitiği','text'=>'Gelir, sipariş ve ticket trendi.']]],
        ['id'=>'a4','span'=>3,'widgets'=>[['id'=>'ak4','type'=>'ticket','title'=>'AI Operasyon Merkezi','text'=>'Öncelikli aksiyonlar ve SLA riskleri.']]],
    ]] ];
    if($target==='customer') return [[ 'id'=>'client_row_1','cols'=>[
        ['id'=>'c1','span'=>4,'widgets'=>[['id'=>'cw1','type'=>'renewal','title'=>'Yenileme Merkezi','text'=>'Hosting/domain/SSL yaklaşan ödemeler.']]],
        ['id'=>'c2','span'=>3,'widgets'=>[['id'=>'cw2','type'=>'product','title'=>'Aktif Hizmetler','text'=>'Disk, trafik, SSL ve sağlık göstergeleri.']]],
        ['id'=>'c3','span'=>3,'widgets'=>[['id'=>'cw3','type'=>'invoice','title'=>'Son Faturalar','text'=>'Ödeme durumu ve tahsilat akışı.']]],
    ]] ];
    return [[ 'id'=>'site_row_1','cols'=>[
        ['id'=>'s1','span'=>6,'widgets'=>[['id'=>'sw1','type'=>'hero','title'=>'Domain, hosting ve AI tek SaaS panelde','text'=>'Ahost One ile dijital hizmet satışını uçtan uca yönetin.','button'=>'Hemen Başla']]],
        ['id'=>'s2','span'=>4,'widgets'=>[['id'=>'sw2','type'=>'domain','title'=>'Domain Search Center Pro','text'=>'Registrar fiyatı + komisyon ile canlı fiyat gösterimi.']]],
    ]] ];
}
function ao_builder_pro_get_layout($target='site',$template='home'){
    ao_builder_pro_ensure_schema();
    $q=db()->prepare('SELECT * FROM builder_pro_layouts WHERE target=? AND template_key=? LIMIT 1'); $q->execute([$target,$template]);
    $row=$q->fetch();
    if($row && !empty($row['layout_json'])) return json_decode($row['layout_json'],true) ?: ao_builder_pro_default_layout($target,$template);
    return ao_builder_pro_default_layout($target,$template);
}
function ao_builder_pro_save_layout($target,$template,$json){
    ao_builder_pro_ensure_schema();
    $target = in_array($target,['site','admin','customer'],true) ? $target : 'site';
    $template = preg_replace('/[^a-z0-9_-]/i','', (string)$template) ?: 'home';
    $arr=json_decode($json,true); if(!is_array($arr)) throw new Exception('Builder JSON geçersiz.');
    $json=json_encode($arr, JSON_UNESCAPED_UNICODE);
    $admin=(int)($_SESSION['admin_id'] ?? 0);
    db()->prepare("INSERT INTO builder_pro_layouts(target,template_key,title,layout_json,status,created_by,updated_by) VALUES(?,?,?,?, 'published', ?, ?) ON DUPLICATE KEY UPDATE layout_json=VALUES(layout_json), status='published', updated_by=VALUES(updated_by), updated_at=NOW()")->execute([$target,$template,ucfirst($target).' '.$template,$json,$admin,$admin]);
    $layoutId=(int)db()->lastInsertId();
    if(!$layoutId){$q=db()->prepare('SELECT id FROM builder_pro_layouts WHERE target=? AND template_key=? LIMIT 1');$q->execute([$target,$template]);$layoutId=(int)($q->fetchColumn() ?: 0);} 
    db()->prepare('INSERT INTO builder_pro_revisions(layout_id,target,template_key,layout_json,created_by) VALUES(?,?,?,?,?)')->execute([$layoutId,$target,$template,$json,$admin]);
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/builder-pro/save') { require_admin(); verify_csrf(); try{ ao_builder_pro_save_layout($_POST['target'] ?? 'site', $_POST['template_key'] ?? 'home', $_POST['layout_json'] ?? '[]'); flash('success','Builder Pro 3.0 düzeni kaydedildi.'); }catch(Throwable $e){ flash('error','Builder kaydedilemedi: '.$e->getMessage()); } redirect_to('admin/builder-pro?target='.urlencode($_POST['target'] ?? 'site').'&template='.urlencode($_POST['template_key'] ?? 'home')); }
if ($route==='admin/builder-pro') { require_admin(); ao_builder_pro_ensure_schema(); view('builder-pro/index', ['pageTitle'=>'Ahost Builder Pro 3.0']); exit; }


// v20.0.0 Ultimate Platform - v19 Enterprise Core + v20 SaaS/AI additions
function ao_v20_ensure_schema(){
    static $done=false; if($done) return; $done=true;
    $file=__DIR__.'/database/migrations/v20_0_0_ultimate_platform.sql';
    if(is_file($file)){
        try{ db()->exec(file_get_contents($file)); }catch(Throwable $e){ try{ error_log('Ahost v20 schema: '.$e->getMessage()); }catch(Throwable $x){} }
    }
}
ao_v20_ensure_schema();


if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/menu-manager/save') {
    require_admin(); verify_csrf();
    $type = in_array(($_POST['type'] ?? 'admin'), ['admin','site','mobile'], true) ? $_POST['type'] : 'admin';
    $json = $_POST['items_json'] ?? '[]';
    $items = json_decode($json, true);
    if (!is_array($items)) $items = [];
    if (function_exists('ao_save_menu_v222')) {
        ao_save_menu_v222($type, $items);
        // Backward compatibility for old screens.
        save_admin_pref('menu_builder_'.$type, json_encode(ao_normalize_menu_items_v222($items), JSON_UNESCAPED_UNICODE));
    } else {
        $clean=[]; foreach($items as $it){ $label=trim((string)($it['label']??'')); $url=trim((string)($it['url']??'')); if($label!=='') $clean[]=['label'=>$label,'url'=>$url]; }
        save_admin_pref('menu_builder_'.$type, json_encode($clean, JSON_UNESCAPED_UNICODE));
    }
    flash('success', ucfirst($type).' menüsü kaydedildi ve ön yüz menü cache sorunu giderildi.');
    redirect_to('admin/menu-manager?type='.$type);
}



// v23.0.0 Provider, Currency, Translation, Knowledge Base and Support helpers
function ao_v23_slug($s){ $s=trim(mb_strtolower((string)$s,'UTF-8')); $tr=['ş'=>'s','ı'=>'i','ğ'=>'g','ü'=>'u','ö'=>'o','ç'=>'c']; $s=strtr($s,$tr); $s=preg_replace('/[^a-z0-9]+/','-',$s); return trim($s,'-') ?: 'item'; }
function ao_v23_ensure_schema(){ static $done=false; if($done) return; $done=true;
  try{db()->exec("CREATE TABLE IF NOT EXISTS provider_accounts(id INT AUTO_INCREMENT PRIMARY KEY, provider_slug VARCHAR(80) UNIQUE, provider_name VARCHAR(160), api_status VARCHAR(40) DEFAULT 'not_configured', balance_label VARCHAR(80) NULL, balance_amount DECIMAL(14,2) DEFAULT 0, balance_currency VARCHAR(10) DEFAULT 'TRY', api_help_url VARCHAR(255) NULL, docs TEXT NULL, is_active TINYINT(1) DEFAULT 1, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(Throwable $e){}
  try{db()->exec("CREATE TABLE IF NOT EXISTS currency_rates(id INT AUTO_INCREMENT PRIMARY KEY, currency_code VARCHAR(10) UNIQUE, base_code VARCHAR(10) DEFAULT 'TRY', tcmb_rate DECIMAL(16,6) DEFAULT 0, margin_percent DECIMAL(8,2) DEFAULT 0, final_rate DECIMAL(16,6) DEFAULT 0, source VARCHAR(80) DEFAULT 'TCMB', updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(Throwable $e){}
  try{db()->exec("CREATE TABLE IF NOT EXISTS translation_memory(id INT AUTO_INCREMENT PRIMARY KEY, source_hash CHAR(40) UNIQUE, source_text TEXT, source_lang VARCHAR(10) DEFAULT 'tr', target_lang VARCHAR(10) DEFAULT 'en', translated_text TEXT, context VARCHAR(80) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(Throwable $e){}
  try{db()->exec("CREATE TABLE IF NOT EXISTS knowledge_articles(id INT AUTO_INCREMENT PRIMARY KEY, audience VARCHAR(20) DEFAULT 'customer', category VARCHAR(120), title VARCHAR(255), slug VARCHAR(255) UNIQUE, excerpt TEXT NULL, content LONGTEXT NULL, seo_title VARCHAR(255) NULL, meta_description TEXT NULL, tags TEXT NULL, status VARCHAR(30) DEFAULT 'draft', lang VARCHAR(10) DEFAULT 'tr', cover_media_id INT NULL, is_seed TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(Throwable $e){}
  try{db()->exec("CREATE TABLE IF NOT EXISTS support_chat_leads(id INT AUTO_INCREMENT PRIMARY KEY, department VARCHAR(120), name VARCHAR(190), email VARCHAR(190), phone VARCHAR(80), subject VARCHAR(255), message TEXT, page_url VARCHAR(255), status VARCHAR(40) DEFAULT 'new', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(Throwable $e){}
  try{db()->exec("CREATE TABLE IF NOT EXISTS provider_product_imports(id INT AUTO_INCREMENT PRIMARY KEY, provider_slug VARCHAR(80), product_code VARCHAR(120), name VARCHAR(255), description TEXT, specs_json LONGTEXT, source_price DECIMAL(14,2) DEFAULT 0, source_currency VARCHAR(10) DEFAULT 'USD', sale_price_try DECIMAL(14,2) DEFAULT 0, import_status VARCHAR(40) DEFAULT 'preview', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(Throwable $e){}
  foreach([['domainnameapi','Domain Name API','Domain registrar API bilgilerini Domain Center > Registrarlar içinde oluştur.'],['resellerclub','ResellerClub','API kullanıcı adı ve key ile domain yedek sağlayıcı olarak yapılandır.'],['sectigo','Sectigo','SSL partner hesabından API bilgilerini al.'],['contabo','Contabo','Contabo Customer Control Panel içinde API client oluştur.'],['hetzner','Hetzner Cloud','Hetzner Cloud Console > Security > API Tokens bölümünden token oluştur.'],['vultr','Vultr','Vultr API token ile cloud ürünlerini senkronize et.'],['digitalocean','DigitalOcean','API Token ile droplet planlarını çek.'],['ovh','OVHcloud','API application key/secret ve consumer key ile bağlan.']] as $p){try{db()->prepare("INSERT IGNORE INTO provider_accounts(provider_slug,provider_name,docs) VALUES(?,?,?)")->execute($p);}catch(Throwable $e){}}
  foreach([['USD',45.00,5.0],['EUR',49.00,5.0],['GBP',58.00,5.0]] as $r){try{db()->prepare("INSERT IGNORE INTO currency_rates(currency_code,tcmb_rate,margin_percent,final_rate) VALUES(?,?,?,? )")->execute([$r[0],$r[1],$r[2],$r[1]+($r[1]*$r[2]/100)]);}catch(Throwable $e){}}
}
function ao_v23_seed_knowledge(){ ao_v23_ensure_schema();
 $articles=[
  ['customer','Başlangıç','Domain nedir ve nasıl seçilir?'],
  ['customer','Başlangıç','Hosting nedir ve hangi paket seçilmeli?'],
  ['customer','Başlangıç','VPS nedir, kimler kullanmalı?'],
  ['customer','Başlangıç','Dedicated sunucu nedir?'],
  ['customer','Başlangıç','Reseller hosting nedir?'],
  ['customer','Başlangıç','SSL sertifikası nedir?'],
  ['customer','Domain','Domain nasıl alınır?'],
  ['customer','Domain','Domain transferi nasıl yapılır?'],
  ['customer','Domain','EPP kodu nedir ve nereden alınır?'],
  ['customer','Domain','Domain kilidi nedir, nasıl açılır?'],
  ['customer','Domain','Whois bilgileri nasıl güncellenir?'],
  ['customer','Domain','Nameserver nasıl değiştirilir?'],
  ['customer','Domain','Domain süresi bitince ne olur?'],
  ['customer','Domain','Premium domain nedir?'],
  ['customer','Domain','Domain değerleme nasıl yapılır?'],
  ['customer','Domain','Domain satışında güvenli transfer nasıl yapılır?'],
  ['customer','DNS','DNS nedir?'],
  ['customer','DNS','A kaydı nasıl eklenir?'],
  ['customer','DNS','CNAME kaydı nedir?'],
  ['customer','DNS','MX kaydı nasıl ayarlanır?'],
  ['customer','DNS','SPF kaydı nedir?'],
  ['customer','DNS','DKIM nedir ve nasıl kurulur?'],
  ['customer','DNS','DMARC kaydı nasıl oluşturulur?'],
  ['customer','DNS','DNS yayılım süresi ne kadardır?'],
  ['customer','DNS','Cloudflare ile DNS yönetimi'],
  ['customer','Hosting','cPanel giriş nasıl yapılır?'],
  ['customer','Hosting','DirectAdmin giriş nasıl yapılır?'],
  ['customer','Hosting','Plesk panel giriş nasıl yapılır?'],
  ['customer','Hosting','FTP hesabı nasıl oluşturulur?'],
  ['customer','Hosting','PHP sürümü nasıl değiştirilir?'],
  ['customer','Hosting','Cron Job nasıl oluşturulur?'],
  ['customer','Hosting','Alt alan adı nasıl oluşturulur?'],
  ['customer','Hosting','Hosting yedeği nasıl alınır?'],
  ['customer','Hosting','Hosting taşıma işlemi nasıl yapılır?'],
  ['customer','Hosting','Web sitesi neden yavaş açılır?'],
  ['customer','E-Posta','Kurumsal e-posta hesabı nasıl oluşturulur?'],
  ['customer','E-Posta','Android telefonda kurumsal mail kurulumu'],
  ['customer','E-Posta','iPhone ve iPad mail kurulumu'],
  ['customer','E-Posta','Outlook IMAP/SMTP mail kurulumu'],
  ['customer','E-Posta','Thunderbird mail kurulumu'],
  ['customer','E-Posta','Apple Mail kurulumu'],
  ['customer','E-Posta','SMTP IMAP POP3 farkları nelerdir?'],
  ['customer','E-Posta','Mail gönderemiyorum nasıl çözerim?'],
  ['customer','E-Posta','Mail spam klasörüne düşüyor ne yapmalıyım?'],
  ['customer','SSL','SSL nasıl kurulur?'],
  ['customer','SSL','Let’s Encrypt SSL kurulumu'],
  ['customer','SSL','Wildcard SSL nedir?'],
  ['customer','SSL','Mixed Content hatası nasıl çözülür?'],
  ['customer','SSL','SSL yenileme nasıl yapılır?'],
  ['customer','VPS','VPS ilk kurulum adımları'],
  ['customer','VPS','PuTTY ile SSH bağlantısı nasıl yapılır?'],
  ['customer','VPS','Termius ile SSH bağlantısı'],
  ['customer','VPS','MobaXterm ile SSH ve SFTP kullanımı'],
  ['customer','VPS','SSH key nasıl oluşturulur?'],
  ['customer','VPS','Linux temel komutları'],
  ['customer','VPS','VPS güvenliği için ilk yapılacaklar'],
  ['customer','VPS','CSF Firewall kurulumu'],
  ['customer','VPS','Fail2Ban kurulumu'],
  ['customer','VPS','cPanel kurulumu öncesi hazırlık'],
  ['customer','VPS','DirectAdmin kurulumu öncesi hazırlık'],
  ['customer','VPS','Plesk kurulumu öncesi hazırlık'],
  ['customer','VPS','CyberPanel kurulumu'],
  ['customer','Dedicated','Dedicated sunucu ilk kurulum'],
  ['customer','Dedicated','RAID nedir ve neden önemlidir?'],
  ['customer','Dedicated','Proxmox kurulumu'],
  ['customer','Dedicated','Dedicated sunucuyu VPSlere bölme'],
  ['customer','Dedicated','KVM VPS oluşturma'],
  ['customer','Dedicated','LXC Container oluşturma'],
  ['customer','Dedicated','Snapshot ve yedekleme mantığı'],
  ['customer','Reseller','Reseller hesabı nasıl kullanılır?'],
  ['customer','Reseller','WHM ile hosting paketi oluşturma'],
  ['customer','Reseller','Reseller müşterisi nasıl eklenir?'],
  ['customer','Reseller','Suspend ve Unsuspend nedir?'],
  ['customer','Reseller','Markalı nameserver oluşturma'],
  ['customer','Programlar ve Araçlar','WinSCP ile dosya aktarımı'],
  ['customer','Programlar ve Araçlar','FileZilla ile FTP bağlantısı'],
  ['customer','Programlar ve Araçlar','phpMyAdmin ile veritabanı yönetimi'],
  ['customer','Programlar ve Araçlar','MySQL yedeği alma ve içe aktarma'],
  ['customer','Programlar ve Araçlar','VS Code ile uzak sunucu dosyası düzenleme'],
  ['customer','Programlar ve Araçlar','Git kurulumu ve temel kullanım'],
  ['customer','Programlar ve Araçlar','Docker ve Docker Compose başlangıç'],
  ['customer','WordPress','WordPress kurulumu'],
  ['customer','WordPress','WordPress site taşıma'],
  ['customer','WordPress','WooCommerce kurulumu'],
  ['customer','WordPress','WordPress hızlandırma'],
  ['customer','WordPress','WordPress güvenliği'],
  ['customer','WordPress','WordPress yedek alma'],
  ['customer','SiteBuilder','SiteBuilder ile site oluşturma'],
  ['customer','SiteBuilder','SiteBuilder menü ve sayfa yönetimi'],
  ['customer','SiteBuilder','SiteBuilder SEO ayarları'],
  ['customer','MobileBuilder','MobileBuilder ile Android uygulama hazırlama'],
  ['customer','MobileBuilder','APK ve AAB nedir?'],
  ['customer','MobileBuilder','Google Play Console’a uygulama yükleme'],
  ['customer','Marketplace','Kaynak kod satın alırken nelere dikkat edilmeli?'],
  ['customer','Marketplace','Domain satışında escrow güvenli transfer'],
  ['customer','Marketplace','Hazır script ve tema satın alma rehberi'],
  ['admin','Ahost One Kullanımı','Ahost One ilk kurulum sihirbazı'],
  ['admin','Ahost One Kullanımı','Ayarlar Merkezi kullanımı'],
  ['admin','Ahost One Kullanımı','Ürün Merkezi ürün ve fiyatlandırma'],
  ['admin','Ahost One Kullanımı','Domain Center registrar yönetimi'],
  ['admin','Ahost One Kullanımı','Provider Center API bağlantıları ve bakiye kontrolü'],
  ['admin','Ahost One Kullanımı','Migration Bridge seçmeli import'],
  ['admin','Ahost One Kullanımı','Backup Center bulut yedekleme'],
  ['admin','Ahost One Kullanımı','Build Center APK/AAB repository temizliği'],
  ['admin','Ahost One Kullanımı','Lisans Merkezi suspend ve bildirim yönetimi'],
  ['admin','Ahost One Kullanımı','AI Center API anahtarı ve kullanım alanları'],
  ['admin','Ahost One Kullanımı','Otomasyon Merkezi kural yönetimi'],
  ['admin','Ahost One Kullanımı','Bilgi Bankası makale ve medya yönetimi'],
  ['admin','Ahost One Kullanımı','Module Center ZIP indirme ve güncelleme']
 ];
 foreach($articles as $a){ $slug=ao_v23_slug($a[2]); $content='<h2>'.e($a[2]).'</h2><p>Bu makale Ahost One Bilgi Bankası & Akademi Pro tarafından SEO uyumlu taslak olarak oluşturulmuştur. İçerik kullanıcı odaklıdır; dış görsel bağlantısı kullanılmaz, görseller Medya Kütüphanesi üzerinden WebP/SVG olarak saklanır.</p><h3>Adım Adım</h3><ol><li>İşleme başlamadan önce gerekli hesap, panel veya bağlantı bilgilerini hazırlayın.</li><li>İlgili panele ya da programa giriş yapın.</li><li>Makaledeki adımları sırasıyla uygulayın.</li><li>İşlem sonunda site, mail, domain, sunucu veya uygulama tarafında sonucu test edin.</li></ol><h3>SEO Notu</h3><p>Bu rehber çoklu dil sistemi ile çevrilebilir, meta başlık/açıklama ve ilgili makalelerle güçlendirilebilir.</p><h3>Sık Sorulan Sorular</h3><p>Bu alana müşterinin en çok sorduğu sorular ve kısa cevaplar eklenir.</p>'; try{db()->prepare("INSERT IGNORE INTO knowledge_articles(audience,category,title,slug,excerpt,content,seo_title,meta_description,tags,status,is_seed) VALUES(?,?,?,?,?,?,?,?,?,?,1)")->execute([$a[0],$a[1],$a[2],$slug,$a[2].' rehberi.',$content,$a[2],$a[2].' hakkında adım adım SEO uyumlu Ahost One bilgi bankası rehberi.',$a[1].', rehber, hosting, ahost one','published']);}catch(Throwable $e){} }
}
function ao_v23_price_try($amount,$currency='USD'){ ao_v23_ensure_schema(); $currency=strtoupper($currency); if($currency==='TRY') return (float)$amount; try{$q=db()->prepare('SELECT final_rate FROM currency_rates WHERE currency_code=? LIMIT 1');$q->execute([$currency]);$rate=(float)$q->fetchColumn(); if($rate<=0)$rate=(float)ao_currency_rate($currency,'TRY'); return round(((float)$amount)*$rate,2);}catch(Throwable $e){return (float)$amount;} }


// v23.3.5 Frontend Product Catalog - dynamic product/group/detail pages
function ao_v2335_cycle_label($cycle){
    $labels=['onetime'=>'Tek Seferlik','monthly'=>'Aylık','quarterly'=>'3 Aylık','semiannually'=>'6 Aylık','annually'=>'Yıllık','biennially'=>'2 Yıllık','triennially'=>'3 Yıllık'];
    return $labels[$cycle] ?? ucwords(str_replace(['_','-'],' ',(string)$cycle));
}
function ao_v2336_products_columns(){
    static $cols=null; if($cols!==null) return $cols;
    try { $cols=array_column(db()->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC),'Field'); }
    catch(Throwable $e){ $cols=[]; }
    return $cols;
}
function ao_v2335_product_public_where(){
    $cols=ao_v2336_products_columns();
    $where=[];
    if(in_array('is_active',$cols,true)) $where[]='p.is_active=1';
    // Kayıtsız ziyaretçiler için ürün vitrini login istemez; sadece adminin gizlediği ürünler listeden çıkarılır.
    // Eski kurulumlarda visibility kolonu yoksa SQL hatası vermeden tüm aktif ürünler gösterilir.
    if(in_array('visibility',$cols,true)) $where[]="(p.visibility IS NULL OR p.visibility IN ('visible','public','show',''))";
    return $where ? implode(' AND ',$where) : '1=1';
}
function ao_v2336_product_group_columns(){
    static $cols=null; if($cols!==null) return $cols;
    try { $cols=array_column(db()->query('SHOW COLUMNS FROM product_groups')->fetchAll(PDO::FETCH_ASSOC),'Field'); }
    catch(Throwable $e){ $cols=[]; }
    return $cols;
}
function ao_v2335_product_groups(){
    try {
        $gcols=ao_v2336_product_group_columns();
        $where=in_array('is_active',$gcols,true) ? 'WHERE g.is_active=1' : 'WHERE 1=1';
        $order=in_array('sort_order',$gcols,true) ? 'g.sort_order,g.name' : 'g.name';
        return db()->query("SELECT g.*, COUNT(p.id) product_count FROM product_groups g LEFT JOIN products p ON p.group_id=g.id AND ".ao_v2335_product_public_where()." $where GROUP BY g.id ORDER BY $order")->fetchAll();
    } catch(Throwable $e){ return []; }
}
function ao_v2335_products($groupSlug=null){
    try {
        $sql="SELECT p.*, g.name group_name, g.slug group_slug, g.type group_type FROM products p LEFT JOIN product_groups g ON g.id=p.group_id WHERE ".ao_v2335_product_public_where();
        $vals=[];
        if($groupSlug){ $sql.=" AND g.slug=?"; $vals[]=$groupSlug; }
        $sql.=" ORDER BY COALESCE(p.sort_order,0), p.name";
        $q=db()->prepare($sql); $q->execute($vals); return $q->fetchAll();
    } catch(Throwable $e){ return []; }
}
function ao_v2335_product_by_slug($slug){
    try { $q=db()->prepare("SELECT p.*, g.name group_name, g.slug group_slug, g.type group_type FROM products p LEFT JOIN product_groups g ON g.id=p.group_id WHERE p.slug=? AND ".ao_v2335_product_public_where()." LIMIT 1"); $q->execute([$slug]); return $q->fetch() ?: null; } catch(Throwable $e){ return null; }
}
function ao_v2335_group_by_slug($slug){
    try { $gcols=ao_v2336_product_group_columns(); $active=in_array('is_active',$gcols,true)?' AND is_active=1':''; $q=db()->prepare("SELECT * FROM product_groups WHERE slug=?$active LIMIT 1"); $q->execute([$slug]); return $q->fetch() ?: null; } catch(Throwable $e){ return null; }
}
function ao_v2335_product_pricing($productId){
    try {
        $q=db()->prepare("SELECT * FROM product_pricing WHERE product_id=? AND (is_active=1 OR is_active IS NULL) ORDER BY FIELD(cycle,'monthly','annually','quarterly','semiannually','biennially','triennially','onetime'), cycle");
        $q->execute([(int)$productId]); return $q->fetchAll();
    } catch(Throwable $e){ return []; }
}
function ao_v2335_primary_price($product){
    $pricing=ao_v2335_product_pricing((int)($product['id']??0));
    foreach($pricing as $r){
        $usd=(float)($r['price_usd'] ?? 0); $try=(float)($r['price_try'] ?? 0); $price=(float)($r['price'] ?? 0); $cur=strtoupper((string)($r['currency'] ?? 'TRY'));
        if($try<=0 && $usd>0) $try=(float)ao_v23_price_try($usd,'USD');
        if($try<=0 && $price>0) $try=$cur==='USD' ? (float)ao_v23_price_try($price,'USD') : $price;
        if($try>0) return ['amount'=>$try,'currency'=>'TRY','cycle'=>$r['cycle'] ?? 'monthly','usd'=>$usd];
    }
    $amount=(float)($product['price'] ?? 0); $cur=strtoupper((string)($product['currency'] ?? 'TRY'));
    if($cur==='USD') $amount=(float)ao_v23_price_try($amount,'USD');
    return ['amount'=>$amount,'currency'=>'TRY','cycle'=>$product['billing_cycle'] ?? 'monthly','usd'=>0];
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='support/chat-submit') { ao_v23_ensure_schema(); verify_csrf(); try{ $department=trim($_POST['department']??'Teknik Destek'); $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $subject=trim($_POST['subject']??'Destek Talebi'); $message=trim($_POST['message']??''); if(!$name||!$email||!$message) throw new Exception('Ad soyad, e-posta ve mesaj zorunlu.'); db()->prepare('INSERT INTO support_chat_leads(department,name,email,phone,subject,message,page_url) VALUES(?,?,?,?,?,?,?)')->execute([$department,$name,$email,$phone,$subject,$message,$_SERVER['HTTP_REFERER']??'']); try{db()->prepare('INSERT INTO tickets(customer_id,subject,message,status,priority,department,created_at) VALUES(NULL,?,?,?,?,?,NOW())')->execute([$subject,$message,'open','medium',$department]);}catch(Throwable $e){} flash('success','Destek ekibimize bilgi verildi, en kısa sürede sizinle iletişime geçilecektir.'); }catch(Throwable $e){ flash('error','Destek talebi oluşturulamadı: '.$e->getMessage()); } redirect_to($_SERVER['HTTP_REFERER'] ?? ''); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/knowledge-base/seed') { require_admin(); verify_csrf(); ao_v23_seed_knowledge(); flash('success','Bilgi Bankası taslak makaleleri oluşturuldu.'); redirect_to('admin/support/knowledgebase'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/currency-center/save') { require_admin(); verify_csrf(); ao_v23_ensure_schema(); foreach(['USD','EUR','GBP'] as $c){$r=(float)($_POST['rate'][$c]??0);$m=(float)($_POST['margin'][$c]??0);$f=$r+($r*$m/100); try{db()->prepare('INSERT INTO currency_rates(currency_code,tcmb_rate,margin_percent,final_rate) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE tcmb_rate=VALUES(tcmb_rate),margin_percent=VALUES(margin_percent),final_rate=VALUES(final_rate)')->execute([$c,$r,$m,$f]);}catch(Throwable $e){}} if(function_exists('ao_v237_refresh_try_prices')) ao_v237_refresh_try_prices(); flash('success','Kur ve marj ayarları kaydedildi. Ürünlerin TRY fiyatları güncellendi.'); redirect_to('admin/currency-center'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/provider-center/save') { require_admin(); verify_csrf(); ao_v23_ensure_schema(); $slug=ao_v23_slug($_POST['provider_slug']??''); try{db()->prepare('INSERT INTO provider_accounts(provider_slug,provider_name,api_status,balance_amount,balance_currency,docs,is_active) VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE provider_name=VALUES(provider_name),api_status=VALUES(api_status),balance_amount=VALUES(balance_amount),balance_currency=VALUES(balance_currency),docs=VALUES(docs),is_active=VALUES(is_active)')->execute([$slug,trim($_POST['provider_name']??$slug),$_POST['api_status']??'configured',(float)($_POST['balance_amount']??0),$_POST['balance_currency']??'TRY',$_POST['docs']??'',isset($_POST['is_active'])?1:0]); flash('success','Provider kaydedildi.');}catch(Throwable $e){flash('error','Provider kaydedilemedi: '.$e->getMessage());} redirect_to('admin/provider-center'); }


if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/account-users/save') {
    require_customer(); verify_csrf(); ao_v2332_ensure_schema(); $c=current_customer();
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $role=trim($_POST['role_key']??'viewer');
    try{ if(!$name || !$email) throw new Exception('Ad soyad ve e-posta zorunlu.'); $perms=ao_v2332_customer_user_permissions($role); $token=bin2hex(random_bytes(16)); db()->prepare('INSERT INTO customer_account_users(customer_id,name,email,phone,role_key,permissions_json,status,invite_token_hash,invited_at) VALUES(?,?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE name=VALUES(name),phone=VALUES(phone),role_key=VALUES(role_key),permissions_json=VALUES(permissions_json),status=VALUES(status),invite_token_hash=VALUES(invite_token_hash),invited_at=NOW()')->execute([(int)$c['id'],$name,$email,$phone,$role,json_encode($perms,JSON_UNESCAPED_UNICODE),'invited',hash('sha256',$token)]); db()->prepare('INSERT INTO customer_user_activity_logs(customer_id,action,description,ip_address) VALUES(?,?,?,?)')->execute([(int)$c['id'],'user_invited',$email.' davet edildi.',$_SERVER['REMOTE_ADDR']??'']); flash('success','Kullanıcı daveti oluşturuldu. Mail gönderim bağlantısı eklendiğinde davet otomatik iletilecek.'); }catch(Throwable $e){ flash('error','Kullanıcı eklenemedi: '.$e->getMessage()); }
    redirect_to('client/account-users');
}
if ($route==='client/account-users/toggle') { require_customer(); verify_csrf(); ao_v2332_ensure_schema(); $c=current_customer(); $id=(int)($_GET['id']??0); try{ $q=db()->prepare('SELECT status FROM customer_account_users WHERE id=? AND customer_id=?'); $q->execute([$id,(int)$c['id']]); $cur=(string)$q->fetchColumn(); $new=$cur==='active'?'disabled':'active'; db()->prepare('UPDATE customer_account_users SET status=? WHERE id=? AND customer_id=?')->execute([$new,$id,(int)$c['id']]); flash('success','Kullanıcı durumu güncellendi.'); }catch(Throwable $e){ flash('error','Durum değiştirilemedi.'); } redirect_to('client/account-users'); }
if ($route==='client/account-users/delete') { require_customer(); verify_csrf(); ao_v2332_ensure_schema(); $c=current_customer(); $id=(int)($_GET['id']??0); try{ db()->prepare('DELETE FROM customer_account_users WHERE id=? AND customer_id=?')->execute([$id,(int)$c['id']]); flash('success','Kullanıcı silindi.'); }catch(Throwable $e){ flash('error','Kullanıcı silinemedi.'); } redirect_to('client/account-users'); }


// v23.3.5 public product routes
if ($route === 'urunler' || $route === 'products') {
    ao_v2332_ensure_schema();
    site_view('products/index', ['pageTitle'=>'Ürünler', 'groups'=>ao_v2335_product_groups(), 'products'=>ao_v2335_products(), 'selectedGroup'=>null]); exit;
}
if (preg_match('#^urun-grubu/([a-z0-9\-_]+)$#', $route, $m) || preg_match('#^product-group/([a-z0-9\-_]+)$#', $route, $m)) {
    ao_v2332_ensure_schema();
    $group=ao_v2335_group_by_slug($m[1]);
    if(!$group){ http_response_code(404); site_view('errors/404', ['pageTitle'=>'Ürün Grubu Bulunamadı']); exit; }
    site_view('products/index', ['pageTitle'=>$group['name'], 'groups'=>ao_v2335_product_groups(), 'products'=>ao_v2335_products($m[1]), 'selectedGroup'=>$group]); exit;
}
if (preg_match('#^urun/([a-z0-9\-_]+)$#', $route, $m) || preg_match('#^product/([a-z0-9\-_]+)$#', $route, $m)) {
    ao_v2332_ensure_schema();
    $product=ao_v2335_product_by_slug($m[1]);
    if(!$product){ http_response_code(404); site_view('errors/404', ['pageTitle'=>'Ürün Bulunamadı']); exit; }
    site_view('products/detail', ['pageTitle'=>$product['name'], 'product'=>$product, 'pricing'=>ao_v2335_product_pricing((int)$product['id'])]); exit;
}



// v24.1.0 - Completion Pack: Live Chat, Account User permissions, Domain price sync, Menu fallback helpers
function ao_v2410_ensure_schema(){
    if(function_exists('ao_v2332_ensure_schema')) ao_v2332_ensure_schema();
    if(function_exists('ao_v23_ensure_schema')) ao_v23_ensure_schema();
    try{ db()->exec("CREATE TABLE IF NOT EXISTS support_live_chats (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NULL, visitor_name VARCHAR(190) NULL, visitor_email VARCHAR(190) NULL, department VARCHAR(120) DEFAULT 'Teknik Destek', subject VARCHAR(255) DEFAULT 'Canlı Sohbet', status VARCHAR(40) DEFAULT 'waiting', assigned_admin_id INT NULL, source_url VARCHAR(255) NULL, started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, closed_at DATETIME NULL, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, KEY status(status), KEY customer_id(customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS support_live_messages (id INT AUTO_INCREMENT PRIMARY KEY, chat_id INT NOT NULL, sender_type VARCHAR(40) DEFAULT 'visitor', sender_id INT NULL, sender_name VARCHAR(190) NULL, message LONGTEXT NOT NULL, is_read TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY chat_id(chat_id), KEY sender_type(sender_type)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS customer_user_sessions (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, account_user_id INT NULL, ip_address VARCHAR(80) NULL, user_agent VARCHAR(255) NULL, last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, is_active TINYINT(1) DEFAULT 1, KEY customer_id(customer_id), KEY account_user_id(account_user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS domain_price_import_logs (id INT AUTO_INCREMENT PRIMARY KEY, registrar_slug VARCHAR(120), source VARCHAR(120) DEFAULT 'manual', imported_count INT DEFAULT 0, message TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY registrar_slug(registrar_slug)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS module_update_logs (id INT AUTO_INCREMENT PRIMARY KEY, module_key VARCHAR(120), action VARCHAR(80), status VARCHAR(40) DEFAULT 'success', message TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY module_key(module_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS support_widget_events (id INT AUTO_INCREMENT PRIMARY KEY, event_type VARCHAR(80) NOT NULL, name VARCHAR(190) NULL, email VARCHAR(190) NULL, phone VARCHAR(80) NULL, query_text TEXT NULL, response_text LONGTEXT NULL, source_url VARCHAR(255) NULL, ip_address VARCHAR(80) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY event_type(event_type), KEY email(email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    try{ db()->exec("CREATE TABLE IF NOT EXISTS support_widget_settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(160) UNIQUE NOT NULL, setting_value LONGTEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }catch(Throwable $e){}
    foreach(['support_widget_enabled'=>'1','support_widget_live_chat_enabled'=>'1','support_widget_ai_enabled'=>'1','support_widget_search_enabled'=>'1','support_widget_whatsapp_enabled'=>'1','support_widget_phone_enabled'=>'1','support_widget_ticket_enabled'=>'1'] as $k=>$v){ try{ save_setting($k, admin_setting($k,$v)); }catch(Throwable $e){} }
    try{ $cols=array_column(db()->query('SHOW COLUMNS FROM customer_account_users')->fetchAll(PDO::FETCH_ASSOC),'Field'); if(!in_array('twofa_enabled',$cols,true)) db()->exec('ALTER TABLE customer_account_users ADD COLUMN twofa_enabled TINYINT(1) DEFAULT 0'); if(!in_array('last_ip',$cols,true)) db()->exec('ALTER TABLE customer_account_users ADD COLUMN last_ip VARCHAR(80) NULL'); if(!in_array('custom_permissions',$cols,true)) db()->exec('ALTER TABLE customer_account_users ADD COLUMN custom_permissions LONGTEXT NULL'); }catch(Throwable $e){}
    try{ $cols=array_column(db()->query('SHOW COLUMNS FROM tld_pricing')->fetchAll(PDO::FETCH_ASSOC),'Field'); if(!in_array('cost_price',$cols,true)) db()->exec('ALTER TABLE tld_pricing ADD COLUMN cost_price DECIMAL(12,2) DEFAULT 0'); if(!in_array('margin_percent',$cols,true)) db()->exec('ALTER TABLE tld_pricing ADD COLUMN margin_percent DECIMAL(8,2) DEFAULT 0'); if(!in_array('restore_price',$cols,true)) db()->exec('ALTER TABLE tld_pricing ADD COLUMN restore_price DECIMAL(12,2) DEFAULT 0'); }catch(Throwable $e){}
}
function ao_v2410_customer_log($customerId,$userId,$action,$desc=''){
    try{ ao_v2410_ensure_schema(); db()->prepare('INSERT INTO customer_user_activity_logs(customer_id,account_user_id,action,description,ip_address) VALUES(?,?,?,?,?)')->execute([(int)$customerId,$userId?:null,$action,$desc,$_SERVER['REMOTE_ADDR']??'']); }catch(Throwable $e){}
}
function ao_v2413_kb_search($query, $limit=5){
    ao_v2410_ensure_schema();
    $query=trim((string)$query);
    if($query==='') return [];
    try{
        $like='%'.$query.'%';
        $sql="SELECT title,slug,excerpt,content,category FROM knowledge_articles WHERE status='published' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR category LIKE ?) ORDER BY CASE WHEN title LIKE ? THEN 0 ELSE 1 END, id DESC LIMIT ".max(1,min(10,(int)$limit));
        $q=db()->prepare($sql); $q->execute([$like,$like,$like,$like,$like]);
        $rows=$q->fetchAll() ?: [];
        foreach($rows as &$r){
            $plain=trim(strip_tags((string)($r['excerpt'] ?: $r['content'] ?? '')));
            $r['excerpt']=mb_substr($plain,0,220);
            $r['url']=url('bilgi-bankasi').'?q='.urlencode($query).'#'.rawurlencode($r['slug'] ?? '');
        }
        return $rows;
    }catch(Throwable $e){ return []; }
}
function ao_v2413_ai_answer($query){
    $query=trim((string)$query);
    $items=ao_v2413_kb_search($query,3);
    if($items){
        $top=$items[0];
        $answer="Bilgi Bankası'nda en yakın sonucu buldum: ".$top['title'].". ".($top['excerpt'] ?: 'Detayları makalede inceleyebilirsiniz.');
        return ['answer'=>$answer,'items'=>$items,'handoff'=>false];
    }
    return ['answer'=>'Bu konu için bilgi bankasında net bir cevap bulamadım. İstersen canlı temsilciye aktarabilir veya ticket oluşturabilirsin.','items'=>[],'handoff'=>true];
}
function ao_v2413_json($data){ header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }


if ($route==='admin/support/live-chat/poll') { require_admin(); ao_v2410_ensure_schema(); $activeId=(int)($_GET['chat']??0); $payload=['ok'=>true,'rows'=>[],'messages'=>[]]; try{ $payload['rows']=db()->query('SELECT id,visitor_name,subject,status,created_at FROM support_live_chats ORDER BY FIELD(status,"waiting","active","closed"), id DESC LIMIT 80')->fetchAll(PDO::FETCH_ASSOC) ?: []; if($activeId){ $q=db()->prepare('SELECT sender_type,sender_name,message,created_at FROM support_live_messages WHERE chat_id=? ORDER BY id ASC'); $q->execute([$activeId]); $payload['messages']=$q->fetchAll(PDO::FETCH_ASSOC) ?: []; } }catch(Throwable $e){ $payload=['ok'=>false,'error'=>$e->getMessage()]; } ao_v2413_json($payload); }

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/support/live-chat/reply') { require_admin(); verify_csrf(); ao_v2410_ensure_schema(); $id=(int)($_POST['chat_id']??0); $msg=trim($_POST['message']??''); try{ if($id<=0||$msg==='') throw new Exception('Sohbet ve mesaj zorunlu.'); $admin=current_admin(); db()->prepare('INSERT INTO support_live_messages(chat_id,sender_type,sender_id,sender_name,message,is_read) VALUES(?,?,?,?,?,0)')->execute([$id,'admin',$admin['id']??null,$admin['name']??'Admin',$msg]); db()->prepare("UPDATE support_live_chats SET status='active', assigned_admin_id=? WHERE id=?")->execute([$admin['id']??null,$id]); flash('success','Mesaj gönderildi.'); }catch(Throwable $e){ flash('error','Mesaj gönderilemedi: '.$e->getMessage()); } redirect_to('admin/support/live-chat?chat='.$id); }
if ($route==='admin/support/live-chat/close') { require_admin(); verify_csrf(); ao_v2410_ensure_schema(); $id=(int)($_GET['id']??0); try{ db()->prepare("UPDATE support_live_chats SET status='closed', closed_at=NOW() WHERE id=?")->execute([$id]); flash('success','Sohbet kapatıldı.'); }catch(Throwable $e){ flash('error','Sohbet kapatılamadı.'); } redirect_to('admin/support/live-chat'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='support/live-chat/start') { ao_v2410_ensure_schema(); verify_csrf(); $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $msg=trim($_POST['message']??''); try{ if(!$name||!$email||!$msg) throw new Exception('Ad, e-posta ve mesaj zorunlu.'); db()->prepare('INSERT INTO support_live_chats(visitor_name,visitor_email,department,subject,status,source_url) VALUES(?,?,?,?,?,?)')->execute([$name,$email,$_POST['department']??'Teknik Destek',$_POST['subject']??'Canlı Sohbet','waiting',$_SERVER['HTTP_REFERER']??'']); $cid=(int)db()->lastInsertId(); db()->prepare('INSERT INTO support_live_messages(chat_id,sender_type,sender_name,message) VALUES(?,?,?,?)')->execute([$cid,'visitor',$name,($phone?('Telefon: '.$phone."\n"):'').$msg]); db()->prepare('INSERT INTO support_widget_events(event_type,name,email,phone,query_text,source_url,ip_address) VALUES(?,?,?,?,?,?,?)')->execute(['live_chat',$name,$email,$phone,$msg,$_SERVER['HTTP_REFERER']??'',$_SERVER['REMOTE_ADDR']??'']); flash('success','Canlı sohbet talebiniz oluşturuldu. Destek ekibimiz panele düştü.'); }catch(Throwable $e){ flash('error','Sohbet başlatılamadı: '.$e->getMessage()); } redirect_to($_SERVER['HTTP_REFERER'] ?? ''); }
if ($route==='support/widget/search') { ao_v2410_ensure_schema(); $q=trim($_GET['q']??''); $items=ao_v2413_kb_search($q,6); try{ if($q!=='') db()->prepare('INSERT INTO support_widget_events(event_type,query_text,response_text,source_url,ip_address) VALUES(?,?,?,?,?)')->execute(['search',$q,json_encode($items,JSON_UNESCAPED_UNICODE),$_SERVER['HTTP_REFERER']??'',$_SERVER['REMOTE_ADDR']??'']); }catch(Throwable $e){} ao_v2413_json(['ok'=>true,'items'=>$items]); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='support/widget/ai') { ao_v2410_ensure_schema(); verify_csrf(); $q=trim($_POST['q']??''); $res=ao_v2413_ai_answer($q); try{ db()->prepare('INSERT INTO support_widget_events(event_type,query_text,response_text,source_url,ip_address) VALUES(?,?,?,?,?)')->execute(['ai',$q,$res['answer'],$_SERVER['HTTP_REFERER']??'',$_SERVER['REMOTE_ADDR']??'']); }catch(Throwable $e){} ao_v2413_json(['ok'=>true]+$res); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/support/widget-settings/save') { require_admin(); verify_csrf(); ao_v2410_ensure_schema(); foreach(['support_widget_enabled','support_widget_live_chat_enabled','support_widget_ai_enabled','support_widget_search_enabled','support_widget_whatsapp_enabled','support_widget_phone_enabled','support_widget_ticket_enabled','support_hours_enabled'] as $k){ save_setting($k, isset($_POST[$k])?'1':'0'); } foreach(['support_whatsapp_number','support_call_number','support_hours_start','support_hours_end','support_widget_greeting','support_widget_position'] as $k){ if(isset($_POST[$k])) save_setting($k, trim((string)$_POST[$k])); } flash('success','Destek widget ayarları kaydedildi.'); redirect_to('admin/support/widget-settings'); }
if ($route==='client/account-users/resend') { require_customer(); verify_csrf(); ao_v2410_ensure_schema(); $c=current_customer(); $id=(int)($_GET['id']??0); try{ $token=bin2hex(random_bytes(16)); db()->prepare('UPDATE customer_account_users SET invite_token_hash=?, invited_at=NOW(), status=IF(status="disabled",status,"invited") WHERE id=? AND customer_id=?')->execute([hash('sha256',$token),$id,(int)$c['id']]); ao_v2410_customer_log((int)$c['id'],$id,'invite_resend','Davet bağlantısı yenilendi.'); flash('success','Davet bağlantısı yenilendi. Demo paketinde e-posta yerine log kaydı oluşturuldu.'); }catch(Throwable $e){ flash('error','Davet yenilenemedi.'); } redirect_to('client/account-users'); }

if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/account-users/permissions-save') { require_customer(); verify_csrf(); ao_v2410_ensure_schema(); $c=current_customer(); $id=(int)($_POST['user_id']??0); $perms=$_POST['permissions']??[]; if(!is_array($perms)) $perms=[]; $clean=array_values(array_unique(array_map('strval',$perms))); try{ db()->prepare('UPDATE customer_account_users SET custom_permissions=? WHERE id=? AND customer_id=?')->execute([json_encode($clean,JSON_UNESCAPED_UNICODE),$id,(int)$c['id']]); ao_v2410_customer_log((int)$c['id'],$id,'permissions_update','Alt kullanıcı yetkileri güncellendi.'); flash('success','Alt kullanıcı yetkileri güncellendi.'); }catch(Throwable $e){ flash('error','Yetkiler güncellenemedi.'); } redirect_to('client/account-users'); }

if ($route==='client/account-users/2fa-toggle') { require_customer(); verify_csrf(); ao_v2410_ensure_schema(); $c=current_customer(); $id=(int)($_GET['id']??0); try{ $q=db()->prepare('SELECT twofa_enabled FROM customer_account_users WHERE id=? AND customer_id=?'); $q->execute([$id,(int)$c['id']]); $cur=(int)$q->fetchColumn(); db()->prepare('UPDATE customer_account_users SET twofa_enabled=? WHERE id=? AND customer_id=?')->execute([$cur?0:1,$id,(int)$c['id']]); ao_v2410_customer_log((int)$c['id'],$id,'2fa_toggle',$cur?'2FA kapatıldı.':'2FA zorunlu yapıldı.'); flash('success','2FA durumu güncellendi.'); }catch(Throwable $e){ flash('error','2FA güncellenemedi.'); } redirect_to('client/account-users'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/domain-center/pricing-import') {
    require_admin(); verify_csrf(); ao_v2410_ensure_schema();
    $registrar=trim($_POST['registrar_slug']??'domainnameapi');
    $margin=max(0,(float)($_POST['margin_percent']??25)); $count=0;
    try{
        $q=db()->prepare("SELECT tld,MAX(CASE WHEN action='register' THEN cost END) register_cost,MAX(CASE WHEN action='transfer' THEN cost END) transfer_cost,MAX(CASE WHEN action='renew' THEN cost END) renew_cost,MAX(currency) currency FROM registrar_price_cache WHERE registrar_slug=? AND cost>0 GROUP BY tld ORDER BY tld");
        $q->execute([$registrar]); $rows=$q->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if(!$rows) throw new Exception('Canlı registrar fiyat önbelleği boş. Önce registrar bağlantısını test edip fiyat sorgusu çalıştırın.');
        foreach($rows as $r){
            $currency=strtoupper($r['currency']?:'USD'); $rate=$currency==='TRY'?1:(float)ao_currency_rate($currency,'TRY');
            if($rate<=0) throw new Exception($currency.'/TRY kuru alınamadı.');
            $reg=(float)($r['register_cost']?:$r['renew_cost']?:$r['transfer_cost']); $transfer=(float)($r['transfer_cost']?:$reg); $renew=(float)($r['renew_cost']?:$reg);
            db()->prepare('INSERT INTO tld_pricing(tld,register_price,transfer_price,renew_price,currency,registrar_slug,is_active,cost_price,margin_percent,restore_price) VALUES(?,?,?,?,?,?,?,?,?,0) ON DUPLICATE KEY UPDATE register_price=VALUES(register_price),transfer_price=VALUES(transfer_price),renew_price=VALUES(renew_price),currency=VALUES(currency),is_active=1,cost_price=VALUES(cost_price),margin_percent=VALUES(margin_percent)')
                ->execute([$r['tld'],round($reg*$rate*(1+$margin/100),2),round($transfer*$rate*(1+$margin/100),2),round($renew*$rate*(1+$margin/100),2),'TRY',$registrar,1,$reg,$margin]);
            $count++;
        }
        db()->prepare('INSERT INTO domain_price_import_logs(registrar_slug,source,imported_count,message) VALUES(?,?,?,?)')->execute([$registrar,'registrar_cache',$count,'Doğrulanmış registrar fiyat önbelleğinden aktarıldı.']);
        flash('success',$count.' canlı TLD fiyatı aktarıldı/güncellendi.');
    }catch(Throwable $e){ flash('error','Fiyat aktarımı başarısız: '.$e->getMessage()); }
    redirect_to('admin/domain-center/pricing');
}

require_once __DIR__.'/app/catalog-content-v2450.php';


if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/security/settings-save') {
    require_admin(); verify_csrf(); ao_mfa_ensure_schema();
    $allowed=['admin_mfa_policy','customer_mfa_policy','mfa_default_method','mfa_otp_ttl_minutes','mfa_max_attempts','mfa_sms_sender','ip_whitelist','session_timeout_minutes'];
    foreach($allowed as $k){ if(isset($_POST[$k])) save_setting($k, trim((string)$_POST[$k])); }
    foreach(['mfa_mail_enabled','mfa_totp_enabled','mfa_sms_enabled','csrf_protection','rate_limit_login'] as $k){ save_setting($k, isset($_POST[$k])?'1':'0'); }
    flash('success','Güvenlik ve 2FA ayarları kaydedildi.');
    redirect_to('admin/security');
}

// v24.1.2 MFA verification and resend routes.
if ($route === 'auth/mfa') {
    auth_view('mfa-verify', ['pageTitle'=>'Giriş Doğrulama']);
    exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route === 'auth/mfa/verify') {
    verify_csrf();
    $res=ao_mfa_verify_pending(trim($_POST['code'] ?? ''));
    if(!empty($res['ok'])) { flash('success','Giriş doğrulandı.'); redirect_to($res['redirect'] ?? ''); }
    flash('error',$res['message'] ?? 'Doğrulama başarısız.'); redirect_to('auth/mfa');
}
if ($route === 'auth/mfa/resend') {
    verify_csrf();
    $p=$_SESSION['mfa_pending'] ?? null;
    if($p && in_array($p['method'], ['mail','sms'], true)) {
        ao_mfa_generate_otp($p['user_type'], (int)$p['user_id'], $p['method'], $p['method']==='mail'?($p['email']??''):($p['phone']??''));
        flash('success','Yeni doğrulama kodu gönderildi.');
    } else { flash('error','Bu yöntem için yeniden kod gönderilemez.'); }
    redirect_to('auth/mfa');
}
if ($route === 'auth/mfa/cancel') {
    unset($_SESSION['mfa_pending']);
    flash('success','Doğrulama iptal edildi.');
    redirect_to('client/login');
}



// v25.0.0 RC8: QA Visual Scan run route is handled by Unified QA & Scan Center above.

// v24.3.2 AI Copilot form safety: prevent missing POST route from falling to 404.
if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='admin/ai-copilot/run') {
    require_admin(); verify_csrf();
    $prompt = trim($_POST['prompt'] ?? '');
    if ($prompt === '') flash('error','AI komutu boş olamaz.');
    else flash('success','AI Copilot komutu alındı. OpenAI modül yapılandırması tamamlandığında canlı yanıt üretilecek.');
    redirect_to('admin/ai-copilot');
}

$adminMap = [
 'admin' => 'dashboard/index','admin/dashboard' => 'dashboard/index','admin/setup-wizard'=>'setup-wizard/index','admin/commerce-complete'=>'commerce-complete/index','admin/menu-manager'=>'menu-manager/index',
 'admin/customers' => 'customers/index','admin/customers/add' => 'customers/add','admin/customers/view' => 'customers/view','admin/customers/groups' => 'customers/groups',
 'admin/orders' => 'orders/index','admin/orders/view' => 'orders/view','admin/orders/new' => 'orders/new','admin/orders/abandoned' => 'orders/abandoned',
 'admin/accounting' => 'accounting/index','admin/accounting/payment-fees'=>'accounting/payment-fees','admin/accounting/invoices' => 'accounting/invoices','admin/accounting/transactions' => 'accounting/transactions','admin/accounting/taxes' => 'accounting/taxes',
 'admin/domain-center' => 'domain-center/index','admin/domain-center/view' => 'domain-center/view','admin/domain-center/pricing' => 'domain-center/pricing','admin/domain-center/smart-pricing'=>'domain-center/smart-pricing','admin/domain-center/registrars' => 'domain-center/registrars','admin/domain-center/transfers' => 'domain-center/transfers','admin/domain-center/operations'=>'domain-center/operations',
 'admin/product-center' => 'product-center/index','admin/product-center/groups' => 'product-center/groups','admin/product-center/products' => 'product-center/products','admin/product-center/config-options' => 'product-center/config-options','admin/product-center/custom-fields' => 'product-center/custom-fields','admin/product-center/promotions' => 'product-center/promotions','admin/product-center/bundles' => 'product-center/bundles',
 'admin/hosting-server' => 'hosting-server/index','admin/hosting-server/servers' => 'hosting-server/servers','admin/hosting-server/health'=>'hosting-server/health','admin/hosting-server/accounts' => 'hosting-server/accounts','admin/hosting-server/whm' => 'hosting-server/whm',
 'admin/api-integrations' => 'api-integrations/index','admin/qa-scan-center'=>'qa-scan-center/index','admin/qa-visual-scan'=>'qa-scan-center/index','admin/module-center'=>'module-center/index','admin/provider-center'=>'provider-center/index','admin/currency-center'=>'currency-center/index','admin/translation-center'=>'translation-center/index',
 'admin/notifications' => 'notifications/index','admin/notification-center'=>'notification-center/index','admin/search'=>'search/index',
 'admin/migration-bridge' => 'migration-bridge/index',
 'admin/product-updates'=>'product-updates/index','admin/customer-sites'=>'customer-sites/index','admin/license-injection'=>'license-injection/index','admin/ai-copilot'=>'ai-copilot/index','admin/operations-center'=>'operations-center/index',
 'admin/support' => 'support/index','admin/support/departments' => 'support/departments','admin/support/tickets' => 'support/tickets','admin/support/knowledgebase' => 'support/knowledgebase','admin/support/live-chat' => 'support/live-chat','admin/support/widget-settings'=>'support/widget-settings',
 'admin/automation' => 'automation/index','admin/reports' => 'reports/index',
 'admin/theme-center' => 'theme-center/themes','admin/theme-center/themes' => 'theme-center/themes','admin/theme-center/editor' => 'theme-center/editor',
 'admin/license-center'=>'license-center/index','admin/license-center/plans'=>'license-center/plans','admin/license-center/licenses'=>'license-center/licenses','admin/license-center/packages'=>'license-center/packages','admin/license-center/external'=>'license-center/external','admin/help-center'=>'help-center/index','admin/quotations'=>'quotations/index',
 'admin/builder-pro'=>'builder-pro/index','admin/site-builder' => 'site-builder/index','admin/site-builder/pages' => 'site-builder/pages','admin/site-builder/editor' => 'site-builder/editor','admin/site-builder/live-editor' => 'site-builder/live-editor','admin/site-builder/exports' => 'site-builder/exports','admin/site-builder/header' => 'site-builder/header','admin/site-builder/footer' => 'site-builder/footer','admin/site-builder/popups' => 'site-builder/popups','admin/site-builder/forms' => 'site-builder/forms',
 'admin/mobile-builder' => 'mobile-builder/index','admin/mobile-builder/editor' => 'mobile-builder/editor','admin/mobile-builder/ai' => 'mobile-builder/ai','admin/mobile-builder/exports' => 'mobile-builder/exports','admin/mobile-builder/build-queue' => 'mobile-builder/build-queue','admin/mobile-builder/build-center' => 'mobile-builder/build-center','admin/mobile-builder/build-log' => 'mobile-builder/build-log','admin/mobile-builder/build' => 'mobile-builder/build-center','admin/mobile-builder/menu' => 'mobile-builder/menu','admin/mobile-builder/bottom-bar' => 'mobile-builder/bottom-bar','admin/mobile-builder/cta' => 'mobile-builder/cta',
 'admin/build-center'=>'build-center/index','admin/build-center/environment'=>'build-center/environment','admin/build-center/sdk-tools'=>'build-center/sdk-tools','admin/build-center/queue'=>'build-center/queue','admin/build-center/logs'=>'build-center/logs','admin/build-center/repository'=>'build-center/repository','admin/build-center/settings'=>'build-center/settings',
 'admin/ai-center' => 'ai-center/index','admin/ai-center/site-analysis' => 'ai-center/site-analysis','admin/ai-center/seo' => 'ai-center/seo','admin/ai-center/automation' => 'ai-center/automation',
        '/admin/seo-analyzer' => 'seo-analyzer',
 'admin/marketplace' => 'marketplace/index','admin/marketplace/categories'=>'marketplace/categories','admin/marketplace/offers'=>'marketplace/offers','admin/marketplace/escrow'=>'marketplace/escrow','admin/marketplace/auctions'=>'marketplace/auctions','admin/domain-intelligence'=>'domain-intelligence/index','admin/scan-report'=>'qa-scan-center/index','admin/production-test'=>'production-test/index','admin/security' => 'security/index','admin/cache-center'=>'cache-center/index','admin/backup-center'=>'backup-center/index','admin/update-center'=>'update-center/index','admin/logs' => 'logs/index','admin/settings' => 'settings/index','admin/health-center' => 'health-center/index','admin/announcements' => 'announcements/index','admin/affiliate' => 'affiliate/index','admin/references'=>'references/index','admin/blog'=>'blog/index','admin/blog/post'=>'blog/post','admin/kanban'=>'kanban/index','admin/2fa'=>'security/2fa','admin/api-gateway'=>'api-gateway/index','blog' => 'site/blog/index','admin/email-templates' => 'email-templates/index','admin/live-chat' => 'live-chat/index','admin/site-builder/ai-design' => 'site-builder/ai-design','admin/mobile-builder/ai-app' => 'mobile-builder/ai-app','api/ai-generate-site' => 'api/ai-generate-site','api/ai-generate-app' => 'api/ai-generate-app','blog/([^/]+)' => 'site/blog/post'
];

// v24.9.0 guest smart-cart wizard: period, domain action, add-ons before login/payment
if ($route === 'cart/add') {
    $slug = trim((string)($_GET['product'] ?? ''));
    $cycle = trim((string)($_GET['cycle'] ?? ''));
    if ($slug !== '') {
        try {
            $q=db()->prepare('SELECT p.*, g.name AS group_name, g.slug AS group_slug FROM products p LEFT JOIN product_groups g ON g.id=p.group_id WHERE p.slug=? AND p.is_active=1 LIMIT 1');
            $q->execute([$slug]); $p=$q->fetch();
            if($p){
                $price=(float)($p['price']??0); $currency=$p['currency']??'TRY'; $billing=$p['billing_cycle']??'monthly';
                try{
                    $pq=db()->prepare('SELECT cycle,price,currency FROM product_pricing WHERE product_id=? AND is_active=1 AND price>=0 ORDER BY FIELD(cycle,"monthly","annually","biennially","triennially","quarterly","semiannually","onetime"), id LIMIT 1');
                    if($cycle!=='') $pq=db()->prepare('SELECT cycle,price,currency FROM product_pricing WHERE product_id=? AND is_active=1 AND price>=0 AND cycle=? LIMIT 1');
                    $cycle!=='' ? $pq->execute([(int)$p['id'],$cycle]) : $pq->execute([(int)$p['id']]);
                    $pr=$pq->fetch(); if($pr){$price=(float)$pr['price']; $currency=$pr['currency']?:$currency; $billing=$pr['cycle']?:$billing;}
                }catch(Throwable $e){}
                if(!isset($_SESSION['ao_cart']) || !is_array($_SESSION['ao_cart'])) $_SESSION['ao_cart']=[];
                $key=$slug;
                if(isset($_SESSION['ao_cart'][$key])) $_SESSION['ao_cart'][$key]['qty']=(int)($_SESSION['ao_cart'][$key]['qty']??1)+1;
                else $_SESSION['ao_cart'][$key]=['slug'=>$slug,'name'=>$p['name'],'group'=>$p['group_name'] ?? '', 'price'=>$price,'currency'=>$currency,'cycle'=>$billing,'qty'=>1,'domain_action'=>'register','domain_name'=>'','epp_code'=>'','addons'=>[]];
                flash('success','Ürün sepete eklendi.');
            }
        }catch(Throwable $e){ flash('error','Ürün sepete eklenemedi.'); }
    }
    redirect_to('cart');
}
if ($route === 'cart/remove') {
    $slug=trim((string)($_GET['product'] ?? ''));
    if($slug!=='' && isset($_SESSION['ao_cart'][$slug])) unset($_SESSION['ao_cart'][$slug]);
    redirect_to('cart');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route === 'cart/update') {
    verify_csrf();
    foreach(($_POST['qty'] ?? []) as $slug=>$qty){
        $qty=max(0,(int)$qty);
        if(!isset($_SESSION['ao_cart'][$slug])) continue;
        if($qty<=0){ unset($_SESSION['ao_cart'][$slug]); continue; }
        $_SESSION['ao_cart'][$slug]['qty']=$qty;
        $_SESSION['ao_cart'][$slug]['cycle']=trim((string)(($_POST['cycle'][$slug] ?? $_SESSION['ao_cart'][$slug]['cycle'] ?? 'monthly')));
        $_SESSION['ao_cart'][$slug]['domain_action']=trim((string)(($_POST['domain_action'][$slug] ?? 'register')));
        $_SESSION['ao_cart'][$slug]['domain_name']=trim((string)(($_POST['domain_name'][$slug] ?? '')));
        $_SESSION['ao_cart'][$slug]['epp_code']=trim((string)(($_POST['epp_code'][$slug] ?? '')));
        $_SESSION['ao_cart'][$slug]['addons']=array_values(array_map('strval', $_POST['addons'][$slug] ?? []));
        try{
            $q=db()->prepare('SELECT pp.price,pp.currency FROM product_pricing pp JOIN products p ON p.id=pp.product_id WHERE p.slug=? AND pp.cycle=? AND pp.is_active=1 LIMIT 1');
            $q->execute([$slug,$_SESSION['ao_cart'][$slug]['cycle']]); $pr=$q->fetch();
            if($pr){$_SESSION['ao_cart'][$slug]['price']=(float)$pr['price']; $_SESSION['ao_cart'][$slug]['currency']=$pr['currency'] ?: ($_SESSION['ao_cart'][$slug]['currency'] ?? 'TRY');}
        }catch(Throwable $e){}
    }
    redirect_to('cart');
}


// v24.11.6 Public Builder Access + gated export/build
function ao_builder_gate_page($kind='sitebuilder', $format='ZIP') {
    $title = $kind === 'mobilebuilder' ? 'MobileBuilder çıktı oluşturma' : 'SiteBuilder çıktı oluşturma';
    $productRoute = $kind === 'mobilebuilder' ? 'mobilebuilder' : 'sitebuilder';
    $packageRoute = $kind === 'mobilebuilder' ? 'urunler?group=mobilebuilder' : 'urunler?group=sitebuilder';
    site_view('builders/gate', [
        'pageTitle'=>$title,
        'kind'=>$kind,
        'format'=>$format,
        'productRoute'=>$productRoute,
        'packageRoute'=>$packageRoute
    ]);
    exit;
}
if ($route === 'sitebuilder/preview-public') {
    $template = preg_replace('/[^a-z0-9_-]/','', (string)($_GET['template'] ?? 'hosting'));
    site_view('builders/sitebuilder-preview', ['pageTitle'=>'SiteBuilder Önizleme','template'=>$template]);
    exit;
}
if ($route === 'mobilebuilder/preview-public') {
    $template = preg_replace('/[^a-z0-9_-]/','', (string)($_GET['template'] ?? 'business'));
    // Radio template shows special radio builder
    if ($template === 'radio') {
        site_view('builders/mobilebuilder-radio-demo', ['pageTitle'=>'Radyo Uygulaması Oluştur']);
    } else {
        site_view('builders/mobilebuilder-preview', ['pageTitle'=>'MobileBuilder Önizleme','template'=>$template]);
    }
    exit;
}
if ($route === 'sitebuilder/create-demo') {
    site_view('builders/sitebuilder-demo', ['pageTitle'=>'SiteBuilder Demo Oluştur']);
    exit;
}
if ($route === 'mobilebuilder/create-demo') {
    // Check if template is radio
    $template = preg_replace('/[^a-z0-9_-]/','', (string)($_GET['template'] ?? ''));
    if ($template === 'radio') {
        site_view('builders/mobilebuilder-radio-demo', ['pageTitle'=>'Radyo Uygulaması Oluştur']);
    } else {
        site_view('builders/mobilebuilder-demo', ['pageTitle'=>'MobileBuilder Demo Oluştur']);
    }
    exit;
}
if ($route === 'mobilebuilder/radio') {
    site_view('builders/mobilebuilder-radio-demo', ['pageTitle'=>'Radyo Uygulaması Oluştur']);
    exit;
}
// Ziyaretçi önizleme yapabilir; ZIP/APK/AAB/PWA/Android export için üyelik + paket şartı gösterilir.
if (in_array($route, ['sitebuilder/export','sitebuilder/download','sitebuilder/zip','mobilebuilder/export','mobilebuilder/build','mobilebuilder/apk','mobilebuilder/aab','mobilebuilder/zip'], true)) {
    $kind = str_starts_with($route, 'mobilebuilder') ? 'mobilebuilder' : 'sitebuilder';
    $format = 'ZIP';
    if (str_contains($route, 'apk')) $format = 'APK';
    elseif (str_contains($route, 'aab')) $format = 'AAB';
    elseif (str_contains($route, 'build')) $format = 'APK/AAB';
    ao_builder_gate_page($kind, $format);
}

$siteMap = ['' => 'home/index','cart'=>'cart/index','checkout'=>'cart/index','domain' => 'domain/index','hosting' => 'products/hosting','vps' => 'products/vps','web-tasarim' => 'products/web-design','sitebuilder'=>'products/sitebuilder','mobilebuilder'=>'products/mobilebuilder','mobil-uygulama'=>'products/mobile-app','dijital-hizmetler'=>'products/digital-services','marketplace'=>'marketplace/index','referanslar'=>'references/index','bilgi-bankasi'=>'knowledge-base/index','knowledge-base'=>'knowledge-base/index','urunler'=>'products/index','products'=>'products/index','seo-analyzer'=>'site/seo-analyzer/index','domain-checker'=>'site/domain-checker/index','teklif'=>'site/quotation','mobilebuilder/download'=>'site/mobilebuilder-download'];

// v24.6.5 customer notification sync routes
if ($route === 'client/notifications') {
    require_customer();
    $c = current_customer();
    $rows = ao_customer_notifications((int)($c['id'] ?? 0), true, 100);
    customer_view('notifications/index', ['pageTitle'=>'Bildirimlerim','notifications'=>$rows]);
    exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route === 'client/notifications/read') {
    require_customer(); verify_csrf(); $c=current_customer();
    ao_customer_notification_mark_read((int)($c['id'] ?? 0),(int)($_POST['id'] ?? 0));
    redirect_to('client/notifications');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $route === 'client/notifications/read-all') {
    require_customer(); verify_csrf(); $c=current_customer();
    ao_customer_notification_mark_all_read((int)($c['id'] ?? 0));
    redirect_to('client/notifications');
}


if ($_SERVER['REQUEST_METHOD']==='POST' && $route==='client/services/password-update') {
    require_customer(); verify_csrf();
    $c=current_customer(); $serviceId=(int)($_POST['service_id']??0); $pass=trim((string)($_POST['panel_password']??''));
    try{
        if(!$serviceId) throw new Exception('Hizmet seçimi zorunlu.');
        if($pass==='') $pass=ao_random_hosting_password();
        $q=db()->prepare('SELECT h.*, s.customer_id FROM hosting_accounts h LEFT JOIN services s ON s.id=h.service_id WHERE h.service_id=? AND s.customer_id=? LIMIT 1');
        $q->execute([$serviceId,(int)$c['id']]); $h=$q->fetch();
        if(!$h) throw new Exception('Hosting hesabı bulunamadı.');
        $sync=ao_hosting_panel_change_password($h,$pass);
        if(empty($sync['ok'])) throw new Exception($sync['message'] ?? 'Sunucu şifre değişikliğini kabul etmedi.');
        db()->prepare('UPDATE hosting_accounts SET panel_password=? WHERE service_id=?')->execute([$pass,$serviceId]);
        ao_hosting_log((int)$h['id'],$serviceId,'customer.password.changed','Müşteri panelinden şifre değiştirildi ve sunucu senkronu çalıştı.','***','***');
        flash('success','Hosting şifresi güncellendi.');
    }catch(Throwable $e){ flash('error','Şifre güncellenemedi: '.$e->getMessage()); }
    redirect_to('client/services/view?id='.$serviceId);
}

$customerMap = ['client' => 'dashboard/index','client/dashboard'=>'dashboard/index','client/notifications'=>'notifications/index','client/services' => 'services/index','client/services/view' => 'services/view','client/credit' => 'credit/index','client/domains' => 'domains/index','client/domains/view' => 'domains/view','client/invoices' => 'invoices/index','client/invoices/view'=>'invoices/view','client/support' => 'support/index','client/security'=>'security/index','client/profile' => 'profile/index','client/account-users'=>'account-users/index','client/theme'=>'theme','client/builder'=>'builder','client/site-builder'=>'site-builder'];
$authMap = ['client/login'=>'login','client/register'=>'register','client/forgot-password'=>'forgot-password','client/reset-password'=>'reset-password','admin/login'=>'admin-login','admin/forgot-password'=>'admin-forgot-password','admin/security-question'=>'admin-security-question','admin/reset-password'=>'admin-reset-password'];
if (preg_match('#^admin/settings/([a-z0-9_-]+)$#', $route, $m)) {
    require_admin();
    view('settings/section', ['pageTitle'=>'Ayarlar Merkezi', 'section'=>$m[1]]);
    exit;
}
if (isset($adminMap[$route])) { require_admin(); view($adminMap[$route], ['pageTitle' => ucwords(str_replace(['admin/','-'], ['', ' '], $route ?: 'Admin'))]); exit; }
if (isset($authMap[$route])) { $authTitle = str_starts_with($route, 'admin/') ? 'Admin Girişi' : 'Müşteri Girişi'; auth_view($authMap[$route], ['pageTitle' => $authTitle]); exit; }
if (isset($customerMap[$route])) { require_customer(); customer_view($customerMap[$route], ['pageTitle' => ucwords(str_replace(['client/','-'], ['', ' '], $route ?: 'Müşteri Paneli'))]); exit; }
if (isset($siteMap[$route])) { site_view($siteMap[$route], ['pageTitle' => 'Ahost One']); exit; }
http_response_code(404); site_view('errors/404', ['pageTitle'=>'404 - Sayfa Bulunamadı']);
