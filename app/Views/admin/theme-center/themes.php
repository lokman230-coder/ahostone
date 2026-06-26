<?php
if(function_exists('ao_schema_ensure_v188')) { try { ao_schema_ensure_v188(); } catch(Throwable $e) {} }
$themes=[];
try{ $themes=db()->query('SELECT * FROM themes ORDER BY FIELD(area,"site","admin","client"), name ASC')->fetchAll(); }catch(Throwable $e){ $themes=[]; }
$areas=['site'=>'Site Ön Yüz','admin'=>'Admin Panel','client'=>'Müşteri Paneli'];
$activeByArea=[]; foreach($themes as $t){ if(!empty($t['is_active'])) $activeByArea[$t['area']]=$t; }
?>
<div class="ao-admin-page-head"><div><span>Theme Studio Pro</span><h1>Tema Merkezi</h1><p>Site, admin ve müşteri panel temalarını tek premium kart sistemiyle yönetin.</p></div><div class="ao-actions"><a class="ao-btn" href="<?= url('admin/theme-center/editor') ?>">Tema Editörü</a><a class="ao-btn" href="<?= url('admin/setup-wizard') ?>">Kurulum Sihirbazı</a></div></div>
<div class="ao-admin-stat-grid">
  <div class="ao-admin-stat"><i>🎭</i><small>Toplam Tema</small><strong><?= count($themes) ?></strong><span>Yüklü tema</span></div>
  <div class="ao-admin-stat"><i>🌐</i><small>Site Teması</small><strong><?= e($activeByArea['site']['name'] ?? '-') ?></strong><span>Ön yüz</span></div>
  <div class="ao-admin-stat"><i>🧭</i><small>Admin Teması</small><strong><?= e($activeByArea['admin']['name'] ?? '-') ?></strong><span>Yönetim</span></div>
  <div class="ao-admin-stat"><i>👤</i><small>Müşteri Paneli</small><strong><?= e($activeByArea['client']['name'] ?? '-') ?></strong><span>Client</span></div>
</div>
<?php foreach($areas as $area=>$label): $list=array_values(array_filter($themes,fn($t)=>($t['area']??'site')===$area)); ?>
<section class="ao-admin-card"><div class="ao-card-head"><h2><?= e($label) ?></h2><span class="ao-badge"><?= count($list) ?> tema</span></div><?php if(!$list): ?><div class="ao-empty-premium">Bu alan için tema kaydı yok.</div><?php else: ?><div class="ao-theme-grid"><?php foreach($list as $t): $isActive=!empty($t['is_active']); $id=(int)($t['id']??0); ?>
  <article class="ao-theme-card <?= $isActive?'active':'' ?>"><div class="shot"><span style="background:<?= e($t['primary_color'] ?? '#2563eb') ?>"></span><b style="background:<?= e($t['secondary_color'] ?? '#0f172a') ?>"></b><i style="background:<?= e($t['background_color'] ?? '#f8fbff') ?>"></i></div><h3><?= e($t['name'] ?? 'Tema') ?></h3><p><?= e($t['description'] ?? ($t['slug'] ?? '')) ?></p><div class="ao-actions"><a class="ao-mini-btn" href="<?= url('admin/theme-center/editor?id='.$id) ?>">Düzenle</a><a class="ao-mini-btn" target="_blank" href="<?= url('admin/theme-center/preview?id='.$id) ?>">Önizle</a><form method="post" action="<?= url('admin/theme-center/apply') ?>"><?= csrf_field() ?><input type="hidden" name="theme_id" value="<?= $id ?>"><input type="hidden" name="area" value="<?= e($t['area'] ?? 'site') ?>"><button class="ao-mini-btn" <?= $isActive?'disabled':'' ?>><?= $isActive?'Aktif':'Uygula' ?></button></form></div></article>
<?php endforeach; ?></div><?php endif; ?></section>
<?php endforeach; ?>
