<div class="ao-page-head"><div><h2>Site Analizi</h2><p>Güvenlik taraması, performans analizi ve UX iyileştirme önerileri.</p></div></div>
<div class="ao-card">
    <h3>🔐 Güvenlik Taraması</h3>
    <div class="ao-grid two" style="margin-top:12px">
        <div><b>SSL Sertifikası</b><p>Sitenizin SSL durumunu kontrol edin.</p><a class="ao-btn soft" href="<?= url('admin/ai-center/ssl-check') ?>">Kontrol Et</a></div>
        <div><b>Malware Tarama</b><p>Bilinen zararlı kod imzaları için tarama.</p><a class="ao-btn soft" href="<?= url('admin/ai-center/malware-scan') ?>">Tara</a></div>
    </div>
</div>
<div class="ao-card">
    <h3>⚡ Performans</h3>
    <p>Core Web Vitals, LCP, FID ve CLS skorları analiz edilir. Önerilen iyileştirmeler listelenir.</p>
    <div style="display:flex;gap:8px">
        <input type="text" placeholder="https://example.com" style="flex:1;padding:10px;border:1px solid var(--ao-border);border-radius:6px;background:var(--ao-bg);color:var(--ao-text)">
        <a class="ao-btn" href="<?= url('admin/ai-center/performance-test') ?>">Test Et</a>
    </div>
</div>
