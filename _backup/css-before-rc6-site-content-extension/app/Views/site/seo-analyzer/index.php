<?php
/**
 * Ahost One - Ücretsiz SEO Analizi (Demo)
 * Temel SEO kontrolü - Detaylı analiz için kayıt olun
 */
$seo_analysis = null;
$error = null;
if ($_POST['action'] === 'analyze' && !empty($_POST['url'])) {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    if (!preg_match('/^https?:\/\//i', $url)) $url = 'https://' . $url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $html = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$html || $httpcode !== 200) {
        $error = "Siteye erişilemedi";
    } else {
        $score = 100;
        $issues = [];
        $details = [];
        // Sadece 5 temel kontrol
        preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $title);
        $titleText = !empty($title[1]) ? trim($title[1]) : '';
        $details['title'] = ['text' => $titleText, 'ok' => !empty($titleText) && strlen($titleText) >= 10];
        if (empty($titleText) || strlen($titleText) < 10) { $score -= 25; $issues[] = 'Başlık eksik veya çok kısa'; }
        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $desc);
        $descText = !empty($desc[1]) ? trim($desc[1]) : '';
        $details['description'] = ['text' => $descText, 'ok' => !empty($descText)];
        if (empty($descText)) { $score -= 25; $issues[] = 'Meta description yok'; }
        if (!preg_match('/<meta[^>]*name=["\']viewport["\']/i', $html)) { $score -= 25; $issues[] = 'Mobil uyumlu değil'; }
        else { $details['viewport'] = true; }
        preg_match_all('/<img[^>]*>/i', $html, $imgs);
        $noAlt = 0;
        foreach ($imgs[0] as $img) { if (!preg_match('/alt=["\'][^"\']+["\']/', $img)) $noAlt++; }
        if ($noAlt > count($imgs[0]) * 0.5) { $score -= 15; $issues[] = 'Çoğu görselde alt etiketi yok'; }
        $text = strip_tags($html);
        if (str_word_count($text) < 200) { $score -= 10; $issues[] = 'İçerik yetersiz'; }
        $seo_analysis = ['url' => $url, 'score' => max(0, $score), 'issues' => $issues, 'httpcode' => $httpcode, 'details' => $details];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ücretsiz SEO Analizi | Ahost One</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;background:#f8fafc;color:#1e293b;line-height:1.6}
        .container{max-width:700px;margin:0 auto;padding:40px 20px}
        .header{text-align:center;margin-bottom:40px}
        .header h1{font-size:2.5rem;font-weight:700;color:#0f172a;margin-bottom:10px}
        .header p{font-size:1.1rem;color:#64748b}
        .card{background:#fff;border-radius:20px;padding:35px;margin-bottom:25px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
        .card h2{font-size:1.3rem;margin-bottom:20px;color:#0f172a}
        .input-group{display:flex;gap:10px}
        input{flex:1;padding:16px 20px;border:2px solid #e2e8f0;border-radius:12px;font-size:1rem;transition:border-color .2s}
        input:focus{outline:none;border-color:#2563eb}
        button{padding:16px 30px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-weight:600;font-size:1rem;cursor:pointer;transition:background .2s}
        button:hover{background:#1d4ed8}
        .score-box{text-align:center;padding:40px;background:linear-gradient(135deg,#0f172a,#1e40af);border-radius:20px;color:#fff;margin-bottom:25px}
        .score{font-size:5rem;font-weight:700}
        .score span{font-size:2rem;opacity:.9}
        .score p{margin-top:10px;font-size:1.1rem;opacity:.9}
        .issues{list-style:none}
        .issues li{padding:15px 20px;background:#fef2f2;color:#dc2626;border-radius:10px;margin-bottom:10px;border-left:4px solid #ef4444;font-size:.95rem}
        .issues li::before{content:"⚠️ ";margin-right:8px}
        .cta-box{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;text-align:center;padding:40px;border-radius:20px}
        .cta-box h3{font-size:1.5rem;margin-bottom:10px}
        .cta-box p{margin-bottom:20px;opacity:.9}
        .cta-btn{display:inline-block;padding:14px 28px;background:#fff;color:#2563eb;border-radius:12px;text-decoration:none;font-weight:600;transition:all .2s}
        .cta-btn:hover{transform:translateY(-2px);box-shadow:0 4px 15px rgba(0,0,0,.2)}
        .features{background:#fff;border-radius:20px;padding:30px;margin-top:25px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
        .features h3{text-align:center;margin-bottom:25px;font-size:1.2rem}
        .features-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}
        .feature{text-align:center;padding:20px;background:#f8fafc;border-radius:12px}
        .feature-icon{font-size:2rem;margin-bottom:8px}
        .feature h4{font-size:.95rem;margin-bottom:5px}
        .feature p{font-size:.8rem;color:#64748b}
        .locked{background:#fef3c7;border:1px dashed #f59e0b;border-radius:12px;padding:20px;text-align:center;margin-top:20px}
        .locked p{color:#92400e;font-size:.9rem;margin-bottom:10px}
        .locked a{color:#2563eb;font-weight:600;text-decoration:none}
        .check-list{list-style:none;margin-top:20px}
        .check-list li{padding:12px 0;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:10px}
        .check-list li:last-child{border-bottom:none}
        .check-ok{color:#10b981;font-weight:600}
        .check-no{color:#ef4444;font-weight:600}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔍 Ücretsiz SEO Analizi</h1>
        <p>Web sitenizin SEO performansını öğrenin</p>
    </div>
    
    <div class="card">
        <h2>📊 Hızlı Analiz</h2>
        <form method="POST">
            <input type="hidden" name="action" value="analyze">
            <div class="input-group">
                <input type="url" name="url" placeholder="https://siteniz.com" required value="<?= htmlspecialchars($_POST['url'] ?? '') ?>">
                <button type="submit">🔍 Analiz Et</button>
            </div>
        </form>
    </div>
    
    <?php if ($error): ?>
    <div class="card" style="border-left:4px solid #ef4444">
        <p style="color:#dc2626">❌ <?= htmlspecialchars($error) ?></p>
    </div>
    <?php elseif ($seo_analysis): ?>
    <div class="score-box">
        <div class="score"><?= $seo_analysis['score'] ?><span>/100</span></div>
        <p><?= $seo_analysis['score'] >= 70 ? 'İyi performans!' : ($seo_analysis['score'] >= 40 ? 'Orta seviye' : 'İyileştirme gerekli') ?></p>
    </div>
    
    <div class="card">
        <h2>📋 Temel Kontroller</h2>
        <ul class="check-list">
            <li>
                <?php if (!empty($seo_analysis['details']['title']['ok'])): ?>
                <span class="check-ok">✅</span>
                <?php else: ?>
                <span class="check-no">❌</span>
                <?php endif; ?>
                Başlık (Title)
            </li>
            <li>
                <?php if (!empty($seo_analysis['details']['description']['ok'])): ?>
                <span class="check-ok">✅</span>
                <?php else: ?>
                <span class="check-no">❌</span>
                <?php endif; ?>
                Meta Açıklama
            </li>
            <li>
                <?php if (!empty($seo_analysis['details']['viewport'])): ?>
                <span class="check-ok">✅</span>
                <?php else: ?>
                <span class="check-no">❌</span>
                <?php endif; ?>
                Mobil Uyumluluk
            </li>
        </ul>
        
        <?php if (!empty($seo_analysis['issues'])): ?>
        <h3 style="margin-top:25px">⚠️ Sorunlar</h3>
        <ul class="issues">
            <?php foreach ($seo_analysis['issues'] as $issue): ?>
            <li><?= htmlspecialchars($issue) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    
    <div class="locked">
        <p>🔒 <strong>Detaylı analiz için kayıt olun:</strong></p>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:15px">• 20+ SEO öğesi kontrolü<br>• Analiz tarihçesi<br>• Rakip karşılaştırma<br>• Detaylı raporlar</p>
        <a href="/kayit">Ücretsiz Kayıt Ol</a>
    </div>
    <?php endif; ?>
    
    <div class="features">
        <h3>💡 Daha İyi Sonuçlar İçin</h3>
        <div class="features-grid">
            <div class="feature"><div class="feature-icon">📝</div><h4>Başlık</h4><p>50-60 karakter arası</p></div>
            <div class="feature"><div class="feature-icon">📋</div><h4>Açıklama</h4><p>150-160 karakter</p></div>
            <div class="feature"><div class="feature-icon">📱</div><h4>Mobil</h4><p>Responsive tasarım</p></div>
            <div class="feature"><div class="feature-icon">🖼️</div><h4>Görseller</h4><p>Alt etiketleri</p></div>
        </div>
    </div>
    
    <div class="cta-box" style="margin-top:25px">
        <h3>🚀 Profesyonel SEO Hizmeti</h3>
        <p>Ahost One ile sitenizi üst sıralara taşıyın</p>
        <a href="/" class="cta-btn">Detaylı Bilgi →</a>
    </div>
</div>
</body>
</html>
