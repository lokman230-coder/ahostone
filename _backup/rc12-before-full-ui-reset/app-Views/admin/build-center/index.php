<?php
$stats = [
  ['label'=>'Toplam Build','value'=>'128','note'=>'Tüm zamanlar','icon'=>'⏱','tone'=>'blue'],
  ['label'=>'Başarılı Build','value'=>'96','note'=>'%75 Başarı Oranı','icon'=>'🤖','tone'=>'green'],
  ['label'=>'Sıradaki Build','value'=>'5','note'=>'Kuyrukta bekleyen','icon'=>'⏳','tone'=>'orange'],
  ['label'=>'Toplam İndirme','value'=>'248','note'=>'APK & AAB','icon'=>'⬇','tone'=>'purple'],
  ['label'=>'Ortalama Süre','value'=>'06:24','note'=>'Dakika / Build','icon'=>'↻','tone'=>'blue'],
];
$env = [
  ['Android SDK','34.0.0','Yüklü','green'],
  ['Gradle','8.5','Yüklü','green'],
  ['JDK','17.0.10','Yüklü','green'],
  ['Android NDK','25.2.9519653','Yüklü','green'],
  ['CMake','3.22.1','Yüklü','green'],
];
$builds = [
  ['#128','Restoran App','Android','AAB','Başarılı','04:18','21.05.2024 14:32'],
  ['#127','E-Ticaret App','Android','APK','Başarılı','03:57','21.05.2024 13:11'],
  ['#126','Spor App','Android','AAB','Başarısız','02:10','21.05.2024 12:45'],
  ['#125','Haber App','Android','APK','Başarılı','03:21','21.05.2024 11:20'],
  ['#124','Restoran App','Android','APK','Başarılı','04:05','21.05.2024 10:05'],
];
?>
<div class="premium-page build-center-pro">
  <div class="premium-page-head">
    <div>
      <h2>Build Center Pro</h2>
      <p>Android ve iOS uygulama build süreçlerini yönetin, APK ve AAB dosyalarınızı oluşturun.</p>
    </div>
    <div class="premium-actions">
      <a class="btn primary" href="<?= url('admin/build-center/environment') ?>">↯ Build Ortamını Kontrol Et</a>
      <a class="btn ghost" href="<?= url('admin/build-center/settings') ?>">⚙ Ayarlar</a>
    </div>
  </div>

  <div class="premium-stats five">
    <?php foreach($stats as $s): ?>
      <div class="stat-card <?= e($s['tone']) ?>"><span class="stat-icon"><?= e($s['icon']) ?></span><div><small><?= e($s['label']) ?></small><strong><?= e($s['value']) ?></strong><em><?= e($s['note']) ?></em></div></div>
    <?php endforeach; ?>
  </div>

  <div class="premium-grid two-one">
    <section class="premium-card">
      <h3>Build Ortamı Durumu</h3>
      <div class="env-list">
        <?php foreach($env as $e): ?><div class="env-row"><span><?= e($e[0]) ?></span><b><?= e($e[1]) ?></b><em class="pill green"><?= e($e[2]) ?></em></div><?php endforeach; ?>
      </div>
      <a class="full-link" href="<?= url('admin/build-center/sdk-tools') ?>">Tümünü Kontrol Et</a>
    </section>

    <section class="premium-card wide">
      <div class="card-head"><h3>Son Build İşlemleri</h3><a href="<?= url('admin/build-center/logs') ?>">Tüm Build'ları Görüntüle</a></div>
      <div class="table-wrap"><table class="premium-table"><thead><tr><th>#</th><th>Uygulama Adı</th><th>Platform</th><th>Tür</th><th>Durum</th><th>Süre</th><th>Tarih</th><th>İşlemler</th></tr></thead><tbody>
        <?php foreach($builds as $b): ?><tr><td><?= e($b[0]) ?></td><td><?= e($b[1]) ?></td><td>🤖 <?= e($b[2]) ?></td><td><?= e($b[3]) ?></td><td><span class="pill <?= $b[4]==='Başarılı'?'green':'red' ?>"><?= e($b[4]) ?></span></td><td><?= e($b[5]) ?></td><td><?= e($b[6]) ?></td><td><a class="icon-btn">⬇</a><a class="icon-btn">⧉</a><a class="icon-btn danger">🗑</a></td></tr><?php endforeach; ?>
      </tbody></table></div>
    </section>
  </div>

  <div class="premium-grid three">
    <section class="premium-card"><h3>Build Kuyruğu <span class="badge">5</span></h3><div class="mini-list"><p>#129 <b>Mobil Uygulama</b> <span class="pill orange">Kuyrukta</span></p><p>#130 <b>E-Ticaret App</b> <span class="pill orange">Kuyrukta</span></p><p>#131 <b>Restoran App</b> <span class="pill blue">Hazırlanıyor</span></p></div><a class="full-link" href="<?= url('admin/build-center/queue') ?>">Kuyruğu Görüntüle</a></section>
    <section class="premium-card"><h3>Hızlı İşlemler</h3><div class="quick-actions"><a>🤖 Yeni APK Build Oluştur</a><a>📦 Yeni AAB Build Oluştur</a><a>🧪 Build Ortamını Kontrol Et</a><a>🗂 Repository'ye Git</a></div></section>
    <section class="premium-card"><h3>Depolama Kullanımı</h3><div class="donut"><div>Toplam<br><strong>128.45 GB</strong><br>Kullanılan</div></div><ul class="legend"><li><span></span> APK Dosyaları 58.45 GB</li><li><span></span> AAB Dosyaları 42.22 GB</li><li><span></span> Log Dosyaları 18.33 GB</li><li><span></span> Diğer 9.45 GB</li></ul><a class="full-link" href="<?= url('admin/build-center/repository') ?>">Depolama Yönetimi</a></section>
  </div>
</div>