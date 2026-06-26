<div class="ao-page-head">
  <div>
    <h2>Production Test & Cleanup</h2>
    <p>Canlıya geçiş öncesi tema, marketplace, arama, komisyon, bildirim ve demo/log kontrollerini tek ekranda toplar.</p>
  </div>
  <div class="ao-actions">
    <a class="ao-btn" href="<?= url('admin/scan-report/run') ?>">Genel Tarama Çalıştır</a>
    <a class="ao-btn soft" href="<?= url('admin/scan-report/pdf') ?>">PDF Rapor</a>
  </div>
</div>
<div class="ao-grid four">
  <div class="ao-stat"><span>Kontrol</span><strong><?= count($items ?? []) ?></strong></div>
  <div class="ao-stat"><span>Başarılı</span><strong><?= count(array_filter($items ?? [], fn($i)=>!empty($i['ok']))) ?></strong></div>
  <div class="ao-stat"><span>Düzeltilecek</span><strong><?= count(array_filter($items ?? [], fn($i)=>empty($i['ok']))) ?></strong></div>
  <div class="ao-stat"><span>Sürüm</span><strong>v24.5.0</strong></div>
</div>
<div class="ao-card">
  <h3>Canlı Geçiş Kontrolleri</h3>
  <table class="ao-table">
    <thead><tr><th>Kontrol</th><th>Durum</th><th>Detay</th><th>Öneri</th></tr></thead>
    <tbody>
    <?php foreach(($items ?? []) as $item): ?>
      <tr>
        <td><strong><?= e($item['name']) ?></strong></td>
        <td><span class="ao-badge <?= !empty($item['ok']) ? 'active' : 'pending' ?>"><?= !empty($item['ok']) ? 'PASS' : 'CHECK' ?></span></td>
        <td><?= e($item['detail'] ?? '') ?></td>
        <td><small><?= e($item['recommendation'] ?? '') ?></small></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="ao-card">
  <h3>v24.5.0 Denetim Notları</h3>
  <p>Premium SaaS arayüzü, birleşik menüler, mobil düzen, modül sağlığı, başlangıç ürün kataloğu, referans vitrini ve canlıya geçiş güvenliği birlikte güncellendi.</p>
</div>
