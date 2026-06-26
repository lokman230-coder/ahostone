<div class="ao-page-head"><div><h2>SEO Analizi</h2><p>AI destekli sayfa SEO taraması, anahtar kelime önerileri ve iyileştirme raporları.</p></div></div>
<div class="ao-card">
    <h3>🔍 URL SEO Analizi</h3>
    <p>Analiz edilecek URL'yi girin:</p>
    <div style="display:flex;gap:8px;margin-top:8px">
        <input type="text" id="seoUrlInput" placeholder="https://example.com" style="flex:1;padding:10px;border:1px solid var(--ao-border);border-radius:6px;background:var(--ao-bg);color:var(--ao-text)">
        <a class="ao-btn" href="#" onclick="window.location.href='<?= url('admin/ai-center/seo/analyze') ?>?url='+document.getElementById('seoUrlInput').value;return false;">Analiz Et</a>
    </div>
</div>
<div class="ao-grid two">
    <div class="ao-card">
        <h3>Kontrol Edilenler</h3>
        <ul>
            <li>✅ Title tag uzunluğu</li>
            <li>✅ Meta description</li>
            <li>✅ H1-H6 hiyerarşisi</li>
            <li>✅ Sayfa yükleme hızı</li>
            <li>✅ Mobil uyumluluk</li>
            <li>✅ Canonical tag</li>
            <li>✅ Schema markup</li>
            <li>✅ İç bağlantı yapısı</li>
        </ul>
    </div>
    <div class="ao-card">
        <h3>Anahtar Kelime Önerileri</h3>
        <p>Sektörünüze ve hedef kitlenize göre anahtar kelime fırsatlarını keşfedin.</p>
        <input type="text" id="keywordInput" placeholder="Sektör veya konu girin..." style="width:100%;padding:10px;border:1px solid var(--ao-border);border-radius:6px;background:var(--ao-bg);color:var(--ao-text);box-sizing:border-box;margin-bottom:10px">
        <a class="ao-btn soft" href="#" onclick="window.location.href='<?= url('admin/ai-center/seo/keywords') ?>?q='+document.getElementById('keywordInput').value;return false;">Öneri Al</a>
    </div>
</div>
