<?php
/**
 * Ahost One - Müşteri SEO Araçları
 */
if (!isset($_SESSION['user_id'])) { header('Location: /giris'); exit; }
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
        $score = 100; $issues = []; $recommendations = [];
        preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $title);
        $titleText = !empty($title[1]) ? trim($title[1]) : '';
        if (empty($titleText) || strlen($titleText) < 10) { $score -= 20; $issues[] = 'Başlık eksik'; }
        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $desc);
        if (empty($desc[1])) { $score -= 15; $issues[] = 'Description yok'; }
        preg_match_all('/<img[^>]*>/i', $html, $imgs);
        $noAlt = 0;
        foreach ($imgs[0] as $img) { if (!preg_match('/alt=["\'][^"\']+["\']/', $img)) $noAlt++; }
        if ($noAlt > 0) { $score -= min(10, $noAlt * 2); $issues[] = "$noAlt görselde alt yok"; }
        $text = strip_tags($html);
        if (str_word_count($text) < 300) { $score -= 15; $issues[] = 'İçerik kısa'; }
        preg_match_all('/<h([1-6])[^>]*>/i', $html, $headings);
        if (count(array_filter($headings[1], fn($n) => $n == 1)) === 0) { $score -= 10; $issues[] = 'H1 yok'; }
        $seo_analysis = ['url' => $url, 'score' => max(0, $score), 'issues' => $issues, 'httpcode' => $httpcode];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Araçları | Müşteri Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;background:#f8fafc;color:#1e293b}
        .container{max-width:1000px;margin:0 auto;padding:30px 20px}
        .header{background:linear-gradient(135deg,#0f172a,#1e40af);color:#fff;padding:40px;border-radius:16px;margin-bottom:30px}
        .header h1{font-size:2rem;margin-bottom:10px}
        .card{background:#fff;border-radius:16px;padding:30px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
        .card h2{font-size:1.3rem;margin-bottom:20px;color:#0f172a}
        .input-group{display:flex;gap:10px}
        input{padding:14px 18px;border:2px solid #e2e8f0;border-radius:12px;font-size:1rem;flex:1}
        input:focus{outline:none;border-color:#2563eb}
        button{padding:14px 28px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-weight:600;cursor:pointer}
        button:hover{background:#1d4ed8}
        .score-box{text-align:center;padding:40px;background:linear-gradient(135deg,#0f172a,#1e40af);border-radius:16px;color:#fff;margin-bottom:20px}
        .score{font-size:5rem;font-weight:700}
        .score span{font-size:2rem;opacity:.9}
        .issues{list-style:none}
        .issues li{padding:12px 16px;margin-bottom:8px;background:#fef2f2;color:#dc2626;border-radius:8px;border-left:4px solid #ef4444}
        .success li{background:#f0fdf4;color:#166534;border-color:#22c55e}
        .cta-box{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;text-align:center;padding:30px;border-radius:16px}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔍 SEO Araçları</h1>
        <p>Web sitenizin SEO performansını analiz edin</p>
    </div>
    
    <div class="card">
        <h2>📊 Site Analizi</h2>
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
        <?php if (!empty($seo_analysis['issues'])): ?>
        <h2>⚠️ Sorunlar</h2>
        <ul class="issues">
            <?php foreach ($seo_analysis['issues'] as $issue): ?>
            <li><?= htmlspecialchars($issue) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <h2>✅ Sorun Yok</h2>
        <ul class="issues success">
            <li>Tüm SEO öğeleri doğru yapılandırılmış!</li>
        </ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="cta-box">
        <h3>💡 Profesyonel SEO Hizmeti</h3>
        <p>Ahost One SEO paketleri ile sıralamanızı yükseltin</p>
        <a href="/" style="display:inline-block;padding:12px 24px;background:#fff;color:#2563eb;border-radius:10px;text-decoration:none;font-weight:600">Paketleri İncele</a>
    </div>
</div>
</body>
</html>
