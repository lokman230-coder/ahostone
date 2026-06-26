<div class="ao-page-head"><div><h2>AI Otomasyon</h2><p>AI destekli otomasyon kuralları, tetikleyiciler ve iş akışları.</p></div><a class="ao-btn" href="<?= url('admin/ai-center/automation/add') ?>">+ Kural Ekle</a></div>
<div class="ao-card">
    <h3>Aktif AI Otomasyon Kuralları</h3>
    <table class="ao-table">
        <thead><tr><th>Kural Adı</th><th>Tetikleyici</th><th>Eylem</th><th>Son Çalışma</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
            <tr><td>Churn Uyarısı</td><td>Müşteri 30 gün giriş yapmadı</td><td>E-posta gönder</td><td>-</td><td><span class="ao-badge inactive">Pasif</span></td><td><a class="ao-mini-btn" href="<?= url('admin/ai-center/automation/toggle?id=1') ?>">Etkinleştir</a></td></tr>
            <tr><td>Ticket Öneri</td><td>Yeni ticket açıldı</td><td>AI yanıt önerisi oluştur</td><td>-</td><td><span class="ao-badge inactive">Pasif</span></td><td><a class="ao-mini-btn" href="<?= url('admin/ai-center/automation/toggle?id=2') ?>">Etkinleştir</a></td></tr>
            <tr><td>SEO Raporu</td><td>Her Pazartesi sabah</td><td>SEO raporu gönder</td><td>-</td><td><span class="ao-badge inactive">Pasif</span></td><td><a class="ao-mini-btn" href="<?= url('admin/ai-center/automation/toggle?id=3') ?>">Etkinleştir</a></td></tr>
        </tbody>
    </table>
</div>
