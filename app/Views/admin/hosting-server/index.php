<?php
$servers = table_count('servers');
$hostingAccounts = table_count('hosting_accounts');
$services = table_count('services');
?>
<div class="ao-admin-page-head"><div><span>Altyapı Merkezi</span><h1>Hosting & Server Center</h1><p>WHM, cPanel, DirectAdmin, Plesk ve VPS altyapınızı tek panelden yönetin.</p></div><a class="ao-btn ao-btn--primary" href="<?= url('admin/hosting-server/servers') ?>">+ Sunucu Ekle</a></div>
<div class="ao-admin-stat-grid">
  <a class="ao-admin-stat" href="<?= url('admin/hosting-server/servers') ?>"><i>🖥</i><small>Toplam Sunucu</small><strong><?= $servers ?></strong><span>Sunucu envanteri</span></a>
  <a class="ao-admin-stat" href="<?= url('admin/hosting-server/accounts') ?>"><i>👤</i><small>Hosting Hesabı</small><strong><?= $hostingAccounts ?></strong><span>Panel hesapları</span></a>
  <a class="ao-admin-stat" href="<?= url('admin/product-center') ?>"><i>📦</i><small>Aktif Hizmet</small><strong><?= $services ?></strong><span>Otomasyon bağlı</span></a>
</div>
<div class="ao-admin-grid two">
  <section class="ao-admin-card"><h2>Sunucu Türleri</h2><div class="ao-link-list"><a href="<?= url('admin/hosting-server/whm') ?>">WHM / cPanel</a><a href="<?= url('admin/hosting-server/servers?type=directadmin') ?>">DirectAdmin</a><a href="<?= url('admin/hosting-server/servers?type=plesk') ?>">Plesk</a><a href="<?= url('admin/hosting-server/servers?type=vps') ?>">VPS Yönetimi</a></div></section>
  <section class="ao-admin-card"><h2>Sunucu Durumu</h2><p>Henüz sunucu eklenmediyse API bağlantısı, lisans, sağlık ve kaynak takip kartları burada görünür.</p><a class="ao-btn" href="<?= url('admin/hosting-server/health') ?>">Sağlık Kontrolü</a></section>
</div>
