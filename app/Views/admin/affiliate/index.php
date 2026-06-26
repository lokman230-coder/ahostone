<div class="ao-page-head">
    <div><h2>🤝 Affiliate & Referans Programı</h2><p>Referans linkleri, komisyon yönetimi ve ödeme takibi. Müşterilerini sana yeni müşteri getirenler kazanır.</p></div>
    <a class="ao-btn" href="<?= url('admin/affiliate/add') ?>">+ Affiliate Ekle</a>
</div>
<div class="ao-stats-grid">
    <div class="ao-stat"><span>Aktif Affiliate</span><strong>0</strong></div>
    <div class="ao-stat"><span>Bu Ay Yönlendirme</span><strong>0</strong></div>
    <div class="ao-stat"><span>Onaylı Komisyon</span><strong>₺0</strong></div>
    <div class="ao-stat"><span>Ödenecek</span><strong>₺0</strong></div>
</div>
<div class="ao-card ao-tab-shell" data-ao-tabs>
    <div class="ao-real-tabs" role="tablist">
        <button class="active" data-tab="affiliates">Affiliate Listesi</button>
        <button data-tab="settings">Program Ayarları</button>
        <button data-tab="payouts">Ödemeler</button>
    </div>
    <section id="tab-affiliates" class="ao-tab-panel active">
        <h3>Affiliate Listesi</h3>
        <table class="ao-table">
            <thead><tr><th>Müşteri</th><th>Referans Kodu</th><th>Yönlendirme</th><th>Komisyon</th><th>Bakiye</th><th>Durum</th><th>İşlem</th></tr></thead>
            <tbody><tr><td colspan="7">Henüz affiliate kaydı yok.</td></tr></tbody>
        </table>
    </section>
    <section id="tab-settings" class="ao-tab-panel">
        <h3>Program Ayarları</h3>
        <div class="ao-form">
            <div class="ao-form-grid">
                <label>Komisyon Türü<select><option>Yüzde (%)</option><option>Sabit Tutar (₺)</option><option>Karma</option></select></label>
                <label>Komisyon Oranı<input type="number" value="10" placeholder="10"> %</label>
                <label>Minimum Ödeme Tutarı<input type="number" value="100" placeholder="100"> ₺</label>
                <label>Çerez Süresi<input type="number" value="30" placeholder="30"> gün</label>
                <label>Onay Bekleme Süresi<input type="number" value="30" placeholder="30"> gün (para iadesi riskine karşı)</label>
                <label class="full">Ödeme Yöntemi<select><option>Müşteri Bakiyesi</option><option>Banka Havalesi</option><option>Her İkisi</option></select></label>
            </div>
            <button type="submit" class="ao-btn">Ayarları Kaydet</button>
        </div>
    </section>
    <section id="tab-payouts" class="ao-tab-panel">
        <h3>Komisyon Ödemeleri</h3>
        <table class="ao-table">
            <thead><tr><th>Affiliate</th><th>Tutar</th><th>Yöntem</th><th>Tarih</th><th>Durum</th></tr></thead>
            <tbody><tr><td colspan="5">Henüz ödeme kaydı yok.</td></tr></tbody>
        </table>
    </section>
</div>
