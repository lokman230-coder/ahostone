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
<div class="premium-page build-center-pro"><div class="premium-page-head"><div><h2>Build Logları</h2><p>Başarılı ve hatalı build kayıtları.</p></div><a class="btn ghost" href="<?= url('admin/build-center') ?>">← Dashboard</a></div><section class="premium-card"><h3>Build Logları</h3><p>Bu ekran v16.5.0 Build Center Pro kalıcı mimarisi için hazırlandı. Yönetim, log ve durum kayıtları buradan takip edilir.</p><div class="env-list"><?php foreach($env as $e): ?><div class="env-row"><span><?= e($e[0]) ?></span><b><?= e($e[1]) ?></b><em class="pill green"><?= e($e[2]) ?></em></div><?php endforeach; ?></div></section></div>