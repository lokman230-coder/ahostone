<div class="ao-page-head">
    <div><h2>📢 Duyuru Alanı & Bildirimler</h2><p>Site üst duyuru barı, müşteri paneli bildirimleri ve planlı bakım duyurularını yönetin.</p></div>
    <a class="ao-btn" href="<?= url('admin/settings/header') ?>">Üst Bar Ayarları</a>
</div>

<div class="ao-card ao-tab-shell" data-ao-tabs>
    <div class="ao-real-tabs" role="tablist">
        <button class="active" data-tab="sitebar">Site Üst Duyuru</button>
        <button data-tab="customer">Müşteri Bildirimleri</button>
        <button data-tab="maintenance">Planlı Bakım</button>
        <button data-tab="history">Geçmiş</button>
    </div>

    <section id="tab-sitebar" class="ao-tab-panel active">
        <h3>Site Üst Duyuru Alanı</h3>
        <p>Bu alan ana menünün üstünde görünür. Tarih aralığı ve aktif/pasif durumu admin ayarlarından yönetilir.</p>
        <form class="ao-form" method="post" action="<?= url('admin/settings/header') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= e(url('admin/announcements')) ?>">
            <div class="ao-form-grid">
                <label>Aktif<input type="hidden" name="settings[site_announcement_enabled]" value="0"><select name="settings[site_announcement_enabled]"><option value="1" <?= admin_setting('site_announcement_enabled','1')==='1'?'selected':'' ?>>Aktif</option><option value="0" <?= admin_setting('site_announcement_enabled','1')!=='1'?'selected':'' ?>>Pasif</option></select></label>
                <label>Stil<select name="settings[site_announcement_style]"><option value="info">Bilgi</option><option value="success">Başarı</option><option value="warning">Uyarı</option><option value="danger">Kritik</option></select></label>
                <label class="full">Duyuru Metni<textarea name="settings[site_announcement_text]" rows="3"><?= e(admin_setting('site_announcement_text','Ahost One v22.1.0 ile SiteBuilder, MobileBuilder ve Marketplace yenilendi.')) ?></textarea></label>
                <label class="full">Duyuru Linki<input name="settings[site_announcement_url]" value="<?= e(admin_setting('site_announcement_url','')) ?>" placeholder="https://..."></label>
                <label>Başlangıç<input name="settings[site_announcement_start]" value="<?= e(admin_setting('site_announcement_start','')) ?>" placeholder="YYYY-MM-DD HH:MM:SS"></label>
                <label>Bitiş<input name="settings[site_announcement_end]" value="<?= e(admin_setting('site_announcement_end','')) ?>" placeholder="YYYY-MM-DD HH:MM:SS"></label>
            </div>
            <button class="ao-btn" type="submit">Duyuruyu Kaydet</button>
        </form>
    </section>

    <section id="tab-customer" class="ao-tab-panel">
        <h3>Müşteri Bildirimleri</h3>
        <p>Bildirim ikonu sadece müşteri giriş yaptıktan sonra çalışır. Okunmamış bildirim varsa üst barda yanıp söner.</p>
        <table class="ao-table"><thead><tr><th>Müşteri</th><th>Başlık</th><th>Durum</th><th>Tarih</th></tr></thead><tbody><tr><td colspan="4">Bildirim kayıtları veritabanına eklendiğinde burada listelenecek.</td></tr></tbody></table>
    </section>

    <section id="tab-maintenance" class="ao-tab-panel">
        <h3>🔧 Planlı Bakım Duyurusu</h3>
        <p>Bakım duyurusunu site üst barına bağlayabilir, müşterilere panel bildirimi olarak gösterebilirsiniz.</p>
        <div class="ao-form"><div class="ao-form-grid"><label>Bakım Başlangıcı<input type="datetime-local"></label><label>Tahmini Süre<input type="text" placeholder="Örn: 2 saat"></label><label class="full">Etkilenen Servisler<input type="text" placeholder="Web Hosting, WHM, DNS"></label><label class="full">Açıklama<textarea rows="3"></textarea></label></div><button type="submit" class="ao-btn danger">Bakım Bildirimi Yayınla</button></div>
    </section>

    <section id="tab-history" class="ao-tab-panel"><h3>Geçmiş Duyurular</h3><table class="ao-table"><thead><tr><th>Başlık</th><th>Kanal</th><th>Yayın</th><th>Durum</th></tr></thead><tbody><tr><td colspan="4">Geçmiş duyuru bulunamadı.</td></tr></tbody></table></section>
</div>
