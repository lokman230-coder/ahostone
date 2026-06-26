<div class="ao-page-head"><div><h2>Vergi Yönetimi</h2><p>KDV oranları, vergi grupları ve bölgesel vergi kuralları.</p></div><a class="ao-btn" href="<?= url('admin/accounting/taxes/add') ?>">+ Vergi Ekle</a></div>
<div class="ao-card">
    <h3>Vergi Grupları</h3>
    <table class="ao-table">
        <thead><tr><th>Vergi Adı</th><th>Oran (%)</th><th>Ülke</th><th>Tür</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
            <tr><td>KDV</td><td>%20</td><td>Türkiye</td><td>Normal</td><td><span class="ao-badge active">Aktif</span></td><td><a class="ao-mini-btn" href="<?= url('admin/accounting/taxes/edit?id=1') ?>">Düzenle</a></td></tr>
            <tr><td>KDV İndirimli</td><td>%10</td><td>Türkiye</td><td>İndirimli</td><td><span class="ao-badge active">Aktif</span></td><td><a class="ao-mini-btn" href="<?= url('admin/accounting/taxes/edit?id=2') ?>">Düzenle</a></td></tr>
            <tr><td>Stopaj</td><td>%20</td><td>Türkiye</td><td>Stopaj</td><td><span class="ao-badge inactive">Pasif</span></td><td><a class="ao-mini-btn" href="<?= url('admin/accounting/taxes/edit?id=3') ?>">Düzenle</a></td></tr>
        </tbody>
    </table>
</div>
<div class="ao-card">
    <h3>Vergi Raporu</h3>
    <p>Aylık ve yıllık KDV beyanname özeti, tahsil edilen ve iade edilen vergi tutarları.</p>
    <div class="ao-actions"><a class="ao-btn soft" href="<?= url('admin/accounting/taxes/monthly-report') ?>">Aylık Rapor</a><a class="ao-btn soft" href="<?= url('admin/accounting/taxes/yearly-report') ?>">Yıllık Rapor</a></div>
</div>
