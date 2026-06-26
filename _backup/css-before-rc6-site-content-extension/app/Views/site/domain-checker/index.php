<?php
/**
 * Ahost One - Ücretsiz Domain Sorgulama
 */
$domain_result = null;
$error = null;
if ($_POST['action'] === 'check' && !empty($_POST['domain'])) {
    $domain = preg_replace('/\s+/', '', trim($_POST['domain']));
    $domain = preg_replace('/^https?:\/\//i', '', $domain);
    $domain = preg_replace('/\/.*$/', '', $domain);
    if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*\.[a-zA-Z]{2,}$/', $domain)) {
        $error = "Geçerli bir domain adresi girin";
    } else {
        // Check availability via DNS
        $dns_records = [];
        $record_types = ['A', 'AAAA', 'MX', 'NS', 'TXT', 'SOA', 'CNAME'];
        foreach ($record_types as $type) {
            $records = @dns_get_record($domain, constant('DNS_' . $type));
            if ($records) $dns_records[$type] = $records;
        }
        $has_a = !empty($dns_records['A']) || !empty($dns_records['AAAA']);
        $ttl = 120;
        $nameservers = [];
        if (!empty($dns_records['NS'])) {
            foreach ($dns_records['NS'] as $ns) { $nameservers[] = $ns['target']; }
        }
        $mx = !empty($dns_records['MX']) ? count($dns_records['MX']) : 0;
        // Get WHOIS info
        $whois_data = @whois_query($domain);
        // Check SSL
        $ssl_info = null;
        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
        $socket = @stream_socket_client("ssl://$domain:443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
        if ($socket) {
            $cert = stream_context_get_params($socket);
            if (!empty($cert['options']['ssl']['peer_certificate'])) {
                $ssl_info = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
            }
            fclose($socket);
        }
        $domain_result = [
            'domain' => $domain,
            'has_a' => $has_a,
            'nameservers' => $nameservers,
            'mx_count' => $mx,
            'whois' => $whois_data,
            'ssl' => $ssl_info,
            'dns_records' => $dns_records
        ];
    }
}
function whois_query($domain) {
    $server = 'whois.verisign-grs.com';
    $port = 43;
    $timeout = 10;
    $fp = @fsockopen($server, $port, $errno, $errstr, $timeout);
    if (!$fp) return ['error' => 'WHOIS sunucusuna bağlanılamadı'];
    fwrite($fp, "$domain\r\n");
    $response = '';
    while (!feof($fp)) { $response .= fgets($fp, 1024); }
    fclose($fp);
    $lines = explode("\n", $response);
    $data = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, ':') !== false) {
            list($key, $value) = array_map('trim', explode(':', $line, 2));
            if (!empty($key) && !empty($value)) $data[strtolower($key)] = $value;
        }
    }
    return $data;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ücretsiz Domain Sorgulama | Ahost One</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;background:#f8fafc;color:#1e293b;line-height:1.6}
        .container{max-width:900px;margin:0 auto;padding:40px 20px}
        .header{text-align:center;margin-bottom:40px}
        .header h1{font-size:2.5rem;font-weight:700;color:#0f172a;margin-bottom:10px}
        .header p{font-size:1.1rem;color:#64748b}
        .card{background:#fff;border-radius:16px;padding:30px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
        .form-group{margin-bottom:20px}
        .input-group{display:flex;gap:10px}
        .input-group input{flex:1;padding:14px 18px;border:2px solid #e2e8f0;border-radius:12px;font-size:1rem;transition:border-color .2s}
        .input-group input:focus{outline:none;border-color:#2563eb}
        .btn{padding:14px 28px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-weight:600;font-size:1rem;cursor:pointer;transition:background .2s}
        .btn:hover{background:#1d4ed8}
        .domain-status{display:flex;align-items:center;gap:20px;padding:25px;background:linear-gradient(135deg,#0f172a,#1e40af);border-radius:16px;color:#fff;margin-bottom:25px}
        .domain-status .icon{font-size:3rem}
        .domain-status h2{font-size:1.5rem;margin-bottom:5px}
        .domain-status .badge{padding:5px 15px;background:<?php echo isset($domain_result) ? ($domain_result['has_a'] ? '#10b981' : '#f59e0b') : '#fff' ?>;color:#fff;border-radius:20px;font-size:.85rem;font-weight:600}
        .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px}
        .info-item{background:#f8fafc;padding:20px;border-radius:12px;text-align:center}
        .info-item .icon{font-size:2rem;margin-bottom:10px}
        .info-item .label{font-size:.85rem;color:#64748b;margin-bottom:5px}
        .info-item .value{font-size:1.2rem;font-weight:600;color:#0f172a}
        .whois-table{width:100%;margin-top:20px}
        .whois-table th,.whois-table td{padding:12px 15px;text-align:left;border-bottom:1px solid #e2e8f0;font-size:.9rem}
        .whois-table th{background:#f8fafc;font-weight:600;color:#475569}
        .cta-box{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;text-align:center;padding:40px;border-radius:16px}
        .features{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:30px}
        .feature{text-align:center;padding:20px}
        .feature-icon{font-size:2.5rem;margin-bottom:10px}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🌐 Ücretsiz Domain Sorgulama</h1>
        <p>Domain bilgilerini anında öğrenin</p>
    </div>
    
    <div class="card">
        <form method="POST">
            <input type="hidden" name="action" value="check">
            <div class="input-group">
                <input type="text" name="domain" placeholder="example.com" required value="<?= htmlspecialchars($_POST['domain'] ?? '') ?>">
                <button type="submit" class="btn">🔍 Sorgula</button>
            </div>
        </form>
    </div>
    
    <?php if ($error): ?>
    <div class="card" style="border-left:4px solid #ef4444">
        <p style="color:#dc2626">❌ <?= htmlspecialchars($error) ?></p>
    </div>
    <?php elseif ($domain_result): ?>
    <div class="domain-status">
        <div class="icon">🌐</div>
        <div>
            <h2><?= htmlspecialchars($domain_result['domain']) ?></h2>
            <span class="badge"><?= $domain_result['has_a'] ? 'AKTİF' : 'PASİF' ?></span>
        </div>
    </div>
    
    <div class="card">
        <h3 style="margin-bottom:20px">📊 DNS Bilgileri</h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="icon">🌐</div>
                <div class="label">DNS Kayıtları</div>
                <div class="value"><?= count($domain_result['dns_records']) ?></div>
            </div>
            <div class="info-item">
                <div class="icon">📧</div>
                <div class="label">MX Kayıtları</div>
                <div class="value"><?= $domain_result['mx_count'] ?></div>
            </div>
            <div class="info-item">
                <div class="icon">🔒</div>
                <div class="label">SSL Sertifikası</div>
                <div class="value"><?= $domain_result['ssl'] ? 'VAR' : 'YOK' ?></div>
            </div>
            <div class="info-item">
                <div class="icon">🖥️</div>
                <div class="label">Nameserver</div>
                <div class="value"><?= count($domain_result['nameservers']) ?></div>
            </div>
        </div>
        
        <?php if (!empty($domain_result['nameservers'])): ?>
        <h4 style="margin:20px 0 10px">Name Servers</h4>
        <div style="background:#f8fafc;padding:15px;border-radius:8px;font-family:monospace">
            <?= implode('<br>', array_map('htmlspecialchars', $domain_result['nameservers'])) ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($domain_result['whois']) && empty($domain_result['whois']['error'])): ?>
        <h4 style="margin:20px 0 10px">WHOIS Bilgileri</h4>
        <table class="whois-table">
            <?php foreach (array_slice($domain_result['whois'], 0, 10) as $key => $value): ?>
            <tr><th><?= htmlspecialchars(ucfirst($key)) ?></th><td><?= htmlspecialchars($value) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="cta-box">
        <h3>🚀 Domain Almak İster misiniz?</h3>
        <p>.com, .net, .org ve daha fazlası uygun fiyatlarla</p>
        <a href="/" class="btn" style="background:#fff;color:#2563eb">Domain Ara</a>
    </div>
    
    <div class="features">
        <div class="feature"><div class="feature-icon">⚡</div><h4>Anlık Sorgulama</h4><p>DNS ve WHOIS bilgileri</p></div>
        <div class="feature"><div class="feature-icon">🔒</div><h4>SSL Kontrolü</h4><p>Güvenlik durumu</p></div>
        <div class="feature"><div class="feature-icon">🌐</div><h4>WHOIS</h4><p>Kayıt bilgileri</p></div>
    </div>
</div>
</body>
</html>
