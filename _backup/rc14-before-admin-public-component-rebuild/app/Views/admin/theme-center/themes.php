<?php
ao_schema_ensure_v188();
try { ao_schema_ensure_v940(); } catch(Throwable $e) {}
$themes=[];
try{
  $themes=db()->query('SELECT * FROM themes ORDER BY FIELD(area,"site","admin","client"), name ASC')->fetchAll();
}catch(Throwable $e){ $themes=[]; }
$areas=['site'=>'Site Ön Yüz','admin'=>'Admin Panel','client'=>'Müşteri Paneli'];
$activeByArea=[]; foreach($themes as $t){ if(!empty($t['is_active'])) $activeByArea[$t['area']]=$t; }
?>
<div class="ao-page-head v222-theme-head"><div><span class="eyebrow">Theme Studio Pro</span><h2>Tema Merkezi</h2><p>Veritabanındaki tüm tema paketleri listelenir. Tema değişince yalnız renk değil; header, kart, hero, menü, dashboard ve müşteri paneli karakteri değişecek şekilde preset mantığına hazırlandı.</p></div><div class="compact-actions"><a class="ao-btn soft" href="<?= url('admin/theme-center/editor') ?>">Tema Editörü</a><a class="ao-btn" href="<?= url('admin/setup-wizard') ?>">Kurulum Sihirbazı</a></div></div>
<div class="ao-stats-grid v222-theme-stats"><div class="ao-stat"><span>Toplam Tema</span><strong><?= count($themes) ?></strong></div><div class="ao-stat"><span>Site Teması</span><strong><?= e($activeByArea['site']['name'] ?? '-') ?></strong></div><div class="ao-stat"><span>Admin Teması</span><strong><?= e($activeByArea['admin']['name'] ?? '-') ?></strong></div><div class="ao-stat"><span>Müşteri Paneli</span><strong><?= e($activeByArea['client']['name'] ?? '-') ?></strong></div></div>
<?php if(count($themes)<10): ?><div class="ao-card"><h3>Tema senkronizasyonu gerekli olabilir</h3><p>Beklenen tema sayısı görünmüyorsa install.sql/migration kayıtları eksik import edilmiş olabilir. v22.3.0 fresh install tüm tema seed kayıtlarını otomatik işler.</p></div><?php endif; ?>
<?php foreach($areas as $area=>$label): $list=array_values(array_filter($themes,fn($t)=>($t['area']??'site')===$area)); ?>
<section class="v222-theme-section"><div class="v222-section-title"><h3><?= e($label) ?></h3><span><?= count($list) ?> tema</span></div>
<?php if(!$list): ?><div class="ao-card">Bu alan için tema kaydı yok.</div><?php else: ?><div class="v222-theme-grid">
<?php foreach($list as $t): $isActive=!empty($t['is_active']); $id=(int)($t['id']??0); ?>
  <article class="v222-theme-card <?= $isActive?'active':'' ?>" style="--p:<?= e($t['primary_color'] ?? '#2563eb') ?>;--s:<?= e($t['secondary_color'] ?? '#0f172a') ?>;--bg:<?= e($t['background_color'] ?? '#f8fbff') ?>">
    <div class="theme-shot"><span></span><b></b><i></i></div>
    <div class="theme-card-body"><h3><?= e($t['name'] ?? 'Tema') ?></h3><p><?= e($t['description'] ?? ($t['slug'] ?? '')) ?></p></div>
    <div class="theme-meta"><span><?= e(strtoupper($t['area'] ?? 'site')) ?></span><span><?= e($t['slug'] ?? '') ?></span></div>
    <div class="v222-theme-actions">
      <a class="ao-btn soft" href="<?= url('admin/theme-center/editor?id='.$id) ?>">Düzenle</a>
      <a class="ao-btn soft" target="_blank" href="<?= url('admin/theme-center/preview?id='.$id) ?>">Önizle</a>
      <form method="post" action="<?= url('admin/theme-center/apply') ?>"><?= csrf_field() ?><input type="hidden" name="theme_id" value="<?= $id ?>"><input type="hidden" name="area" value="<?= e($t['area'] ?? 'site') ?>"><button class="ao-btn" <?= $isActive?'disabled':'' ?>><?= $isActive?'Aktif':'Uygula' ?></button></form>
    </div>
    <?= $isActive?'<span class="theme-active-badge">Aktif</span>':'' ?>
  </article>
<?php endforeach; ?>
</div><?php endif; ?></section>
<?php endforeach; ?>
