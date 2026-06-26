<?php
/**
 * Ahost One - SEO Analyzer Pro
 */
$seo_analysis = null;
$error = null;
if ($_POST['action'] === 'analyze' && !empty($_POST['url'])) {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
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
        $error = "Siteye erisilemedi (HTTP $httpcode)";
    } else {
        $score = 100; $issues = []; $recommendations = [];
        // Title
        preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $title);
        if (empty($title[1]) || strlen($title[1]) < 10) { $score -= 20; $issues[] = 'Baslik eksik veya cok kisa'; $recommendations[] = 'Basligi 10-60 karakter arasinda yazin'; }
        elseif (strlen($title[1]) > 60) { $score -= 10; $issues[] = 'Baslik 60 karakterden uzun'; }
        // Meta description
        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $desc);
        if (empty($desc[1]) || strlen($desc[1]) < 50) { $score -= 15; $issues[] = 'Meta description eksik veya yetersiz'; $recommendations[] = "Description'i 50-160 karakter arasinda yazin"; }
        // Viewport
        if (!preg_match('/<meta[^>]*name=["\']viewport["\']/i', $html)) { $score -= 10; $issues[] = 'Viewport meta etiketi yok'; $recommendations[] = 'Mobil uyumluluk icin viewport ekleyin'; }
        // Open Graph
        if (!preg_match('/<meta[^>]*property=["\']og:title["\']/i', $html)) { $score -= 10; $issues[] = 'Open Graph etiketleri eksik'; $recommendations[] = 'Sosyal paylasim icin OG taglarini ekleyin'; }
        // Content
        $text = strip_tags($html);
        $wordCount = str_word_count($text);
        if ($wordCount < 300) { $score -= 15; $issues[] = "Icerik cok kisa ($wordCount kelime)"; $recommendations[] = 'Minimum 300 kelimelik icerik yazin'; }
        // Headings
        preg_match_all('/<h[1-6][^>]*>/i', $html, $headings);
        if (count($headings[0]) === 0) { $score -= 15; $issues[] = 'Baslik etiketleri (H1-H6) bulunamadi'; $recommendations[] = 'H1, H2, H3 etiketlerini kullanin'; }
        // Images without alt
        preg_match_all('/<img[^>]*>/i', $html, $imgs);
        $noAlt = 0;
        foreach ($imgs[0] as $img) { if (!preg_match('/alt=["\'][^"\']+["\']/', $img)) $noAlt++; }
        if ($noAlt > 0) { $score -= min(10, $noAlt * 2); $issues[] = "$noAlt gorselde alt etiketi yok"; $recommendations[] = 'Tum gorsellere alt text ekleyin'; }
        $seo_analysis = ['url' => $url, 'score' => max(0, $score), 'issues' => $issues, 'recommendations' => $recommendations, 'wordCount' => $wordCount, 'httpcode' => $httpcode];
    }
}
?>
<div class="ao-container">
    <div class="ao-header">
        <h1>🔍 SEO Analyzer Pro</h1>
        <p>Web sitenizin SEO performansini analiz edin</p>
    </div>
    <div class="ao-card">
        <h3>📊 Site Analizi</h3>
        <form method="POST" class="ao-form">
            <input type="hidden" name="action" value="analyze">
            <div class="ao-form-group">
                <label>Web Sitesi URL</label>
                <input type="url" name="url" class="ao-input" placeholder="https://example.com" required value="<?= htmlspecialchars($_POST['url'] ?? '') ?>">
            </div>
            <button type="submit" class="ao-btn ao-btn-primary">🔍 Analiz Et</button>
        </form>
    </div>
    <?php if ($error): ?>
    <div class="ao-card" style="border-color:#ef4444;background:#fef2f2">
        <p style="color:#dc2626">❌ <?= htmlspecialchars($error) ?></p>
    </div>
    <?php elseif ($seo_analysis): ?>
    <div class="ao-card">
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px">
            <div style="width:120px;height:120px;background:<?= $seo_analysis['score'] >= 70 ? '#10b981' : ($seo_analysis['score'] >= 40 ? '#f59e0b' : '#ef4444') ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:bold;color:#fff"><?= $seo_analysis['score'] ?></div>
            <div>
                <h3 style="margin:0 0 5px">SEO Skoru</h3>
                <p style="margin:0;color:#64748b"><?= $seo_analysis['score'] >= 70 ? 'Iyi performans' : ($seo_analysis['score'] >= 40 ? 'Orta seviye' : 'Dusuk seviye') ?></p>
                <p style="margin:5px 0 0;color:#94a3b8;font-size:.85rem">Kelime: <?= number_format($seo_analysis['wordCount']) ?> | HTTP: <?= $seo_analysis['httpcode'] ?></p>
            </div>
        </div>
        <?php if (!empty($seo_analysis['issues'])): ?>
        <h4>⚠️ Tespit Edilen Sorunlar</h4>
        <ul style="color:#dc2626">
            <?php foreach ($seo_analysis['issues'] as $issue): ?>
            <li><?= htmlspecialchars($issue) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php if (!empty($seo_analysis['recommendations'])): ?>
        <h4>✅ Oneriler</h4>
        <ul style="color:#059669">
            <?php foreach ($seo_analysis['recommendations'] as $rec): ?>
            <li><?= htmlspecialchars($rec) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="ao-card">
        <h3>📚 SEO Ipuclari</h3>
        <div class="ao-grid-2">
            <div class="ao-box"><h4>Baslik Optimizasyonu</h4><p>50-60 karakter arasinda, anahtar kelime ile baslayan benzersiz basliklar kullanin.</p></div>
            <div class="ao-box"><h4>Meta Description</h4><p>150-160 karakter arasinda, call-to-action iceren aciklamalar yazin.</p></div>
            <div class="ao-box"><h4>Gorsel Optimizasyonu</h4><p>Lazy loading, WebP format, alt text ve boyut optimizasyonu yapin.</p></div>
            <div class="ao-box"><h4> Mobil Uyumluluk</h4><p>Responsive tasarim, hizli yukleme ve dokunmatik hedefler kullanin.</p></div>
        </div>
    </div>
</div>
