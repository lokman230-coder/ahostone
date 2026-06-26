<?php $scan = $scan ?? ($_SESSION['ao_last_scan'] ?? ao_run_full_scan()); ?>
<div class="ao-page-head">
  <div>
    <h2>Scan & Report Center Pro</h2>
    <p>Admin, müşteri paneli, site ön yüzü, API/registrar, demo içerik ve sistem gereksinimlerini tek çatı altında tarar.</p>
  </div>
  <div class="ao-actions">
    <a class="ao-btn" href="<?= url('admin/scan-report/run') ?>">Genel Taramayı Çalıştır</a>
    <a class="ao-btn soft" href="<?= url('admin/scan-report/pdf') ?>">PDF Rapor İndir</a>
  </div>
</div>
<div class="ao-grid four">
  <div class="ao-stat"><span>Genel Skor</span><strong><?= (int)$scan['score'] ?>/100</strong></div>
  <div class="ao-stat"><span>PASS</span><strong><?= count(array_filter($scan['rows'], fn($r)=>$r['status']==='pass')) ?></strong></div>
  <div class="ao-stat"><span>Hata</span><strong><?= count(array_filter($scan['rows'], fn($r)=>$r['status']==='fail')) ?></strong></div>
  <div class="ao-stat"><span>Demo/Placeholder</span><strong><?= count(array_filter($scan['rows'], fn($r)=>$r['status']==='demo')) ?></strong></div>
</div>
<div class="ao-card">
  <h3>Tarama Sonuçları</h3>
  <table class="ao-table">
    <thead><tr><th>Kategori</th><th>Kontrol</th><th>Durum</th><th>Öncelik</th><th>Detay</th><th>Öneri</th></tr></thead>
    <tbody>
    <?php foreach($scan['rows'] as $r): ?>
      <tr>
        <td><?= e($r['category']) ?></td>
        <td><strong><?= e($r['name']) ?></strong></td>
        <td><span class="ao-badge <?= $r['status']==='pass'?'active':($r['status']==='fail'?'closed':'pending') ?>"><?= e(strtoupper($r['status'])) ?></span></td>
        <td><?= e($r['priority']) ?></td>
        <td><small><?= e($r['detail']) ?></small></td>
        <td><small><?= e($r['recommendation']) ?></small></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="ao-card">
  <h3>Bu merkezin kapsadığı taramalar</h3>
  <p>Full System Scanner, Site Health Scanner, Registrar Diagnostics, API Scanner, UI/UX Scanner, Demo Content Detector ve PDF Report Generator bu ekranda birleşti.</p>
</div>
