<?php
$scanRoot = __DIR__ . '/../../../../storage/reports/qa-scans';
$reports = [];
if (is_dir($scanRoot)) {
  foreach (glob($scanRoot.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
    $reports[] = ['name'=>basename($dir),'html'=>is_file($dir.'/report.html'),'pdf'=>is_file($dir.'/report.pdf'),'dir'=>$dir];
  }
  rsort($reports);
}
$routes = [
  'Site' => ['','products','domain','cart','blog','knowledgebase','announcements','references','quotation'],
  'Admin' => ['admin/dashboard','admin/settings','admin/api-integrations','admin/domain-center','admin/hosting-server','admin/automation','admin/build-center','admin/help-center'],
  'Müşteri' => ['client/login','client/dashboard','client/services','client/domains','client/tickets','client/billing'],
];
?>
<div class="rc7-page">
  <div class="rc7-head">
    <div><h2>QA Görsel Tarama Merkezi</h2><p>Site, admin ve müşteri paneli için masaüstü/mobil ekran görüntüsü, kırık link, JS hata ve görsel kalite raporu üretir.</p></div>
    <div class="rc7-actions"><form method="post" action="<?= url('admin/qa-visual-scan/run') ?>"><?= csrf_field() ?><button class="rc7-btn">▶ Tarama Raporu Oluştur</button></form><a class="rc7-btn ghost" href="<?= url('admin/scan-report') ?>">Sistem Taraması</a></div>
  </div>
  <div class="rc7-metric-grid"><div class="rc7-metric"><span>🖥</span><small>Masaüstü</small><strong>1440px</strong></div><div class="rc7-metric"><span>📱</span><small>Mobil</small><strong>390px</strong></div><div class="rc7-metric"><span>🔎</span><small>Kapsam</small><strong>Site + Admin + Müşteri</strong></div><div class="rc7-metric"><span>📄</span><small>Rapor</small><strong>HTML / PDF</strong></div></div>
  <div class="rc7-grid">
    <section class="rc7-card"><h3>Tarama Kapsamı</h3><div class="rc7-kv"><?php foreach($routes as $group=>$items): ?><div><strong><?= e($group) ?></strong><span><?= count($items) ?> sayfa</span></div><?php endforeach; ?></div><p class="rc7-muted">Admin ve müşteri paneli için test oturumu gerekir. Giriş bilgisi yoksa raporda login ekranları ve public route kontrolleri gösterilir.</p></section>
    <section class="rc7-card"><h3>Playwright CLI</h3><p class="rc7-muted">Sunucuda Node.js varsa gerçek ekran görüntüsü almak için:</p><pre>npm install
node tools/qa-visual-scan.js --base=https://siteadresiniz.com</pre><p class="rc7-muted">Çıktılar <code>storage/reports/qa-scans</code> altına kaydedilir.</p></section>
  </div>
  <section class="rc7-card"><h3>Son Raporlar</h3><table class="rc7-table"><thead><tr><th>Rapor</th><th>HTML</th><th>PDF</th><th>Durum</th></tr></thead><tbody><?php foreach($reports as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= $r['html']?'<span class="rc7-pill green">Hazır</span>':'<span class="rc7-pill orange">Yok</span>' ?></td><td><?= $r['pdf']?'<span class="rc7-pill green">Hazır</span>':'<span class="rc7-pill orange">Opsiyonel</span>' ?></td><td><span class="rc7-pill">Kaydedildi</span></td></tr><?php endforeach; if(!$reports): ?><tr><td colspan="4" class="rc7-muted">Henüz rapor yok.</td></tr><?php endif; ?></tbody></table></section>
  <section class="rc7-card"><h3>Kontrol Edilecek UI Hataları</h3><div class="rc7-grid"><div><strong>Görsel</strong><p class="rc7-muted">Taşma, hizalama, alt alta dökülen kartlar, kötü istatistik dizilimleri.</p></div><div><strong>İşlev</strong><p class="rc7-muted">Sekme çalışmaması, aynı içeriği gösteren sayfalar, 404/500 linkler.</p></div><div><strong>Kaynak</strong><p class="rc7-muted">Eksik CSS/JS/görsel, console hataları, yavaş yüklenen sayfalar.</p></div></div></section>
</div>
