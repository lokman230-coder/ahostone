<?php
ao_schema_ensure_v1200();
$featured=[]; $listings=[]; $categories=[]; $stats=['active'=>0,'featured'=>0,'premium'=>0,'volume'=>0];
try{
  $featured=db()->query("SELECT * FROM marketplace_listings WHERE status='active' AND is_featured=1 ORDER BY featured_until DESC, id DESC LIMIT 6")->fetchAll();
  $listings=db()->query("SELECT * FROM marketplace_listings WHERE status='active' ORDER BY is_featured DESC,is_premium DESC,id DESC LIMIT 24")->fetchAll();
  $row=db()->query("SELECT COUNT(*) active, SUM(is_featured=1) featured, SUM(is_premium=1) premium, COALESCE(SUM(price),0) volume FROM marketplace_listings WHERE status='active'")->fetch(PDO::FETCH_ASSOC);
  if($row) $stats=array_merge($stats,$row);
}catch(Throwable $e){}
try{$categories=db()->query("SELECT * FROM marketplace_categories WHERE is_active=1 ORDER BY sort_order,id LIMIT 12")->fetchAll();}catch(Throwable $e){}
$catFallback=[
  ['name'=>'Domain','listing_type'=>'Alan adı alım satımı','icon'=>'🌐'],
  ['name'=>'Web Tasarım','listing_type'=>'Kurumsal site hizmetleri','icon'=>'🎨'],
  ['name'=>'E-Ticaret','listing_type'=>'Online satış projeleri','icon'=>'🛒'],
  ['name'=>'Mobil Uygulama','listing_type'=>'Android/iOS projeleri','icon'=>'📱'],
  ['name'=>'SEO','listing_type'=>'Büyüme ve trafik','icon'=>'📈'],
  ['name'=>'Hazır Script','listing_type'=>'Yazılım ürünleri','icon'=>'💻'],
  ['name'=>'SiteBuilder Tema','listing_type'=>'Hazır şablonlar','icon'=>'✨'],
  ['name'=>'Dijital Hizmet','listing_type'=>'Profesyonel hizmetler','icon'=>'🚀'],
];
function ao_v2339_market_icon($type){$type=(string)$type; if($type==='domain') return '🌐'; if($type==='project') return '💻'; return '🛍️';}
function ao_v2339_market_desc($l){$d=trim((string)($l['description'] ?? '')); if($d==='') $d=trim((string)($l['domain_name'] ?? 'Premium dijital ilan')); return mb_substr(strip_tags($d),0,150);}
?>
<section class="platform-page ao-marketplace-page">
  <div class="ao-marketplace-hero">
    <div class="ao-marketplace-orb"></div>
    <span class="badge">Ahost Marketplace Pro</span>
    <h1>Domain, yazılım ve dijital hizmetler için premium pazar yeri.</h1>
    <p>Domain, web tasarım, SEO, mobil uygulama, hazır script, SiteBuilder tema ve dijital hizmet ilanlarını modern SaaS vitrininde keşfedin.</p>
    <form class="ao-marketplace-search" method="get" action="#ilanlar">
      <input name="q" placeholder="Domain, script, tasarım veya hizmet ara...">
      <select name="type"><option value="">Tüm türler</option><option value="domain">Domain</option><option value="service">Hizmet</option><option value="project">Proje / Script</option></select>
      <select name="budget"><option value="">Bütçe</option><option>0 - 5.000 TL</option><option>5.000 - 25.000 TL</option><option>25.000 TL+</option></select>
      <button type="submit">Ara</button>
    </form>
    <div class="ao-marketplace-stats">
      <div class="ao-marketplace-stat"><strong><?= number_format((int)$stats['active'],0,',','.') ?></strong><span>Aktif ilan</span></div>
      <div class="ao-marketplace-stat"><strong><?= number_format((int)$stats['featured'],0,',','.') ?></strong><span>Öne çıkan</span></div>
      <div class="ao-marketplace-stat"><strong><?= number_format((int)$stats['premium'],0,',','.') ?></strong><span>Premium ilan</span></div>
      <div class="ao-marketplace-stat"><strong><?= number_format((float)$stats['volume'],0,',','.') ?> TL</strong><span>Vitrin değeri</span></div>
    </div>
    <div class="hero-actions" style="margin-top:22px"><a class="site-btn" href="#ilanlar">İlanları Keşfet</a><a class="site-btn secondary ao-order-btn" href="<?= url('client/register') ?>">Satıcı / Müşteri Ol</a></div>
  </div>

  <div class="ao-marketplace-section-head"><div><h2>Kategoriler</h2><p>Domain, tasarım, yazılım ve dijital hizmetleri tek merkezden yönetin.</p></div></div>
  <div class="ao-marketplace-category-grid">
    <?php $catRows=$categories ?: $catFallback; foreach($catRows as $i=>$c): $ico=$c['icon'] ?? ($catFallback[$i%count($catFallback)]['icon'] ?? '✨'); ?>
      <a href="#ilanlar"><span class="ico"><?= e($ico) ?></span><span><?= e($c['name']) ?></span><small><?= e($c['listing_type'] ?? 'Dijital ürün') ?></small></a>
    <?php endforeach; ?>
  </div>
</section>

<?php if($featured): ?>
<section class="platform-page ao-marketplace-page" style="padding-top:0">
  <div class="ao-marketplace-section-head"><div><h2>Öne Çıkan İlanlar</h2><p>Premium vitrinde öne çıkarılan fırsatlar.</p></div><a class="site-btn secondary ao-order-btn" href="#ilanlar">Tümünü Gör</a></div>
  <div class="ao-marketplace-grid">
    <?php foreach($featured as $l): ?>
      <div class="ao-marketplace-card featured">
        <div class="top"><span class="ico"><?= e(ao_v2339_market_icon($l['listing_type'] ?? 'service')) ?></span><span class="tag">⭐ Öne Çıkan</span></div>
        <h3><?= e($l['title']) ?></h3><p><?= e(ao_v2339_market_desc($l)) ?></p>
        <div class="ao-marketplace-meta"><span><?= e($l['listing_type'] ?? 'service') ?></span><?= !empty($l['is_premium'])?'<span>🏆 Premium</span>':'' ?><?= !empty($l['is_urgent'])?'<span>⚡ Acil</span>':'' ?></div>
        <div class="ao-marketplace-seller"><span>✅</span><div><b>Doğrulanmış Satıcı</b><small>Ahost Marketplace güvencesi</small></div></div>
        <div class="ao-marketplace-price"><strong><?= number_format((float)$l['price'],2,',','.') ?> <?= e($l['currency']) ?></strong><a class="site-btn" href="#ilanlar">Teklif Ver</a></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="platform-page ao-marketplace-page" id="ilanlar" style="padding-top:0">
  <div class="ao-marketplace-section-head"><div><h2>Tüm İlanlar</h2><p>Aktif marketplace ilanları ve teklif formları.</p></div></div>
  <?php if($listings): ?>
    <div class="ao-marketplace-grid">
      <?php foreach($listings as $l): ?>
      <div class="ao-marketplace-card">
        <div class="top"><span class="ico"><?= e(ao_v2339_market_icon($l['listing_type'] ?? 'service')) ?></span><span class="tag"><?= e($l['category'] ?: ($l['listing_type'] ?? 'İlan')) ?></span></div>
        <h3><?= e($l['title']) ?></h3>
        <p><?= e(ao_v2339_market_desc($l)) ?></p>
        <div class="ao-marketplace-meta"><span><?= e($l['listing_type']) ?></span><?= !empty($l['is_premium'])?'<span>🏆 Premium</span>':'' ?><?= !empty($l['is_urgent'])?'<span>⚡ Hızlı satış</span>':'' ?></div>
        <div class="ao-marketplace-seller"><span>🛡️</span><div><b>Güvenli Teklif</b><small>Teklifler kayıt altına alınır</small></div></div>
        <div class="ao-marketplace-price"><strong><?= number_format((float)$l['price'],2,',','.') ?> <?= e($l['currency']) ?></strong></div>
        <form method="post" action="<?= url('marketplace/offer') ?>">
          <input type="hidden" name="listing_id" value="<?= (int)$l['id'] ?>">
          <input name="name" placeholder="Adınız">
          <input name="email" placeholder="E-posta">
          <input name="offer_amount" type="number" step="0.01" placeholder="Teklif">
          <button>Teklif Ver</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="marketplace-empty-premium"><h3>Marketplace vitrin hazır.</h3><p>Admin panelinden aktif ilan yayınlandığında premium kartlar burada otomatik görünür. Domain, hazır site, script, mobil uygulama, SEO ve dijital hizmet satışları için altyapı hazır.</p><div class="hero-actions" style="justify-content:center"><a class="site-btn" href="<?= url('client/register') ?>">İlan / Hizmet Talebi Aç</a><a class="site-btn secondary ao-order-btn" href="<?= url('dijital-hizmetler') ?>">Dijital Hizmetleri Gör</a></div></div>
  <?php endif; ?>
</section>

<section class="platform-page ao-marketplace-page ao-seller-trust-strip">
  <div class="ao-marketplace-section-head"><div><h2>Doğrulanmış Satıcılar ve Güvenli Ödeme</h2><p>Satıcı profili, teklif, escrow ve güvenli teslim süreçleri marketplace deneyiminin merkezinde.</p></div></div>
  <div class="ao-marketplace-grid">
    <div class="ao-marketplace-card"><div class="top"><span class="ico">✅</span><span class="tag">Doğrulanmış</span></div><h3>Satıcı Profili</h3><p>Puan, satış sayısı, rozet ve iletişim kayıtları tek profilde gösterilir.</p><div class="ao-marketplace-meta"><span>4.9 puan</span><span>124 satış</span></div></div>
    <div class="ao-marketplace-card"><div class="top"><span class="ico">🤝</span><span class="tag">Teklif</span></div><h3>Teklif ve Karşı Teklif</h3><p>İlan sahibi teklifleri admin panelinden takip eder; gerekirse karşı teklif üretir.</p><div class="ao-marketplace-meta"><span>Kayıtlı teklif</span><span>Bildirim</span></div></div>
    <div class="ao-marketplace-card"><div class="top"><span class="ico">🛡️</span><span class="tag">Escrow</span></div><h3>Güvenli Ödeme / Escrow</h3><p>Ödeme teslim onayı sonrası satıcıya aktarılacak şekilde güvenli akışa hazırlanır.</p><div class="ao-marketplace-meta"><span>Komisyon</span><span>Teslim onayı</span></div></div>
  </div>
</section>
