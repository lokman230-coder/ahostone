<?php
$type = in_array(($_GET['type'] ?? 'site'), ['admin','site','mobile'], true) ? $_GET['type'] : 'site';
$items = function_exists('ao_get_menu_v222') ? ao_get_menu_v222($type) : [];
$names=['admin'=>'Admin Menü','site'=>'Site Menü','mobile'=>'Mobil Menü'];
$flash=get_flash();
$menuSources = ['types'=>[
  ['value'=>'custom','label'=>'Özel URL'],
  ['value'=>'external','label'=>'Harici Link'],
  ['value'=>'home','label'=>'Ana Sayfa'],
  ['value'=>'product_list','label'=>'Ürünler Sayfası'],
  ['value'=>'product','label'=>'Ürün'],
  ['value'=>'product_group','label'=>'Ürün Grubu'],
  ['value'=>'page','label'=>'Sayfa'],
  ['value'=>'knowledge','label'=>'Bilgi Bankası'],
  ['value'=>'domain','label'=>'Domain Sorgula'],
  ['value'=>'marketplace','label'=>'Marketplace'],
], 'sources'=>[
  'home'=>[['label'=>'Ana Sayfa','url'=>'']],
  'product_list'=>[['label'=>'Tüm Ürünler','url'=>'urunler']],
  'knowledge'=>[['label'=>'Bilgi Bankası','url'=>'bilgi-bankasi']],
  'domain'=>[['label'=>'Domain Sorgula','url'=>'domain'],['label'=>'Domain Transfer','url'=>'domain#transfer'],['label'=>'WHOIS','url'=>'domain#whois']],
  'marketplace'=>[['label'=>'Marketplace','url'=>'marketplace']],
  'product'=>[], 'product_group'=>[], 'page'=>[]
]];
try {
  $gs = db()->query("SELECT name,slug FROM product_groups ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
  foreach($gs as $g){ if(!empty($g['slug'])) $menuSources['sources']['product_group'][]=['label'=>$g['name'],'url'=>'urun-grubu/'.$g['slug']]; }
} catch(Throwable $e) {}
try {
  $ps = db()->query("SELECT name,slug FROM products ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
  foreach($ps as $p){ if(!empty($p['slug'])) $menuSources['sources']['product'][]=['label'=>$p['name'],'url'=>'urun/'.$p['slug']]; }
} catch(Throwable $e) {}
try {
  if(function_exists('ao_schema_ensure_v1400')) ao_schema_ensure_v1400();
  $pg = db()->query("SELECT title,slug FROM sitebuilder_pages WHERE status IN ('published','draft') ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
  foreach($pg as $p){ if(!empty($p['slug'])) $menuSources['sources']['page'][]=['label'=>$p['title'],'url'=>($p['slug']==='index'?'':'sitebuilder/preview?slug='.$p['slug'])]; }
} catch(Throwable $e) {}
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="v21-head"><div><h2>Menü Yönetimi</h2><p>Site, admin ve mobil menüler artık global kaydedilir; site ön yüzüne anında yansır. Yavru menü için öğeyi sağa/sola alabilir, sürükle-bırak ile sıralayabilirsin.</p></div><div class="v21-actions"><a class="ao-btn soft" target="_blank" href="<?= url('') ?>">Siteyi Gör</a><button form="menuForm" class="ao-btn">Kaydet</button></div></div>
<div class="menu-type-tabs v222-tabs"><a class="<?= $type==='site'?'active':'' ?>" href="<?= url('admin/menu-manager?type=site') ?>">Site Menü</a><a class="<?= $type==='mobile'?'active':'' ?>" href="<?= url('admin/menu-manager?type=mobile') ?>">Mobil Menü</a><a class="<?= $type==='admin'?'active':'' ?>" href="<?= url('admin/menu-manager?type=admin') ?>">Admin Menü</a></div>
<div class="menu-builder-wrap v222-menu-builder">
 <div class="ao-card v21-panel menu-palette"><h3><?= e($names[$type]) ?></h3><p>Yeni öğe ekle, ☰ ile sürükle, <b>Alt</b> butonu ile yavru menü yap. <b>Üst</b> butonu ile tekrar ana menüye çıkar.</p><button type="button" class="ao-btn soft" onclick="addMenuItem()">+ Yeni Menü Öğesi</button><button type="button" class="ao-btn ghost" onclick="addPreset()">+ Hazır Modüller</button><hr><p><b>Örnek:</b> Hosting ana menü, altında Web Hosting / VPS / Dedicated. Kaydedince site header menüsü otomatik güncellenir.</p></div>
 <form class="ao-card v21-panel menu-canvas" id="menuForm" method="post" action="<?= url('admin/menu-manager/save') ?>">
  <?= csrf_field() ?><input type="hidden" name="type" value="<?= e($type) ?>"><input type="hidden" name="items_json" id="items_json">
  <div class="v21-panel-head"><h3><?= e($names[$type]) ?> Düzenleyici</h3><span class="v21-badge green">Global kayıt</span></div>
  <div class="menu-help-line">Sürükle-bırak sıralama • Bağlantı Tipi ile ürün/ürün grubu/sayfa seç • Alt/Üst ile yavru menü • Sil • Kaydet</div>
  <noscript><div class="ao-menu-js-warning">Menü bağlantı tipi seçenekleri için JavaScript açık olmalı.</div></noscript><ul class="menu-list v222-menu-list" id="menuList"></ul>
 </form>
</div>
<script>
window.AO_MENU_INITIAL = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
window.AO_MENU_LINK_OPTIONS = <?= json_encode($menuSources, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script defer src="<?= url('public/assets/js/admin/admin-v222-fix.js') ?>?v=24.5.0"></script>

