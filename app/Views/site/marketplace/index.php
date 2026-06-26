<?php
if(function_exists('ao_schema_ensure_v1200')) { try { ao_schema_ensure_v1200(); } catch(Throwable $e) {} }
$featured=[]; $listings=[]; $categories=[]; $stats=['active'=>0,'featured'=>0,'premium'=>0,'volume'=>0];
try{
  $featured=db()->query("SELECT * FROM marketplace_listings WHERE status='active' AND is_featured=1 ORDER BY featured_until DESC, id DESC LIMIT 6")->fetchAll();
  $listings=db()->query("SELECT * FROM marketplace_listings WHERE status='active' ORDER BY is_featured DESC,is_premium DESC,id DESC LIMIT 24")->fetchAll();
  $row=db()->query("SELECT COUNT(*) active, SUM(is_featured=1) featured, SUM(is_premium=1) premium, COALESCE(SUM(price),0) volume FROM marketplace_listings WHERE status='active'")->fetch(PDO::FETCH_ASSOC);
  if($row) $stats=array_merge($stats,$row);
}catch(Throwable $e){}
try{$categories=db()->query("SELECT * FROM marketplace_categories WHERE is_active=1 ORDER BY sort_order,id LIMIT 12")->fetchAll();}catch(Throwable $e){}
$catFallback=[
  ['name'=>'Domain','subtitle'=>'Alan adı alım satımı','icon'=>'🌐'],
  ['name'=>'Web Tasarım','subtitle'=>'Kurumsal site hizmetleri','icon'=>'🎨'],
  ['name'=>'Logo Tasarım','subtitle'=>'Marka kimliği','icon'=>'🖌️'],
  ['name'=>'Mobil Uygulama','subtitle'=>'Android/iOS projeleri','icon'=>'📱'],
  ['name'=>'Hosting Hizmeti','subtitle'=>'Barındırma ve taşıma','icon'=>'🖥'],
  ['name'=>'Yazılım / Script','subtitle'=>'Hazır yazılım ürünleri','icon'=>'💻'],
  ['name'=>'SEO Paketi','subtitle'=>'Büyüme ve trafik','icon'=>'📈'],
  ['name'=>'Dijital İçerik','subtitle'=>'Metin, görsel ve kampanya','icon'=>'✨'],
];
function ao_market_icon_rc14($type){$type=(string)$type; if($type==='domain') return '🌐'; if($type==='project') return '💻'; return '🛍️';}
function ao_market_desc_rc14($l){$d=trim((string)($l['description'] ?? '')); if($d==='') $d=trim((string)($l['domain_name'] ?? 'Premium dijital ilan')); return mb_substr(strip_tags($d),0,150);}
?>
<section class="ao-public-page ao-marketplace-page">
  <div class="ao-public-shell">
    <section class="ao-market-hero">
      <span class="ao-kicker">Ahost Marketplace Pro</span>
      <h1>Domain, yazılım, tasarım ve dijital hizmetler için premium pazar yeri.</h1>
      <p>Domain, web tasarım, SEO, mobil uygulama, hazır script, SiteBuilder tema ve dijital hizmet ilanlarını modern SaaS vitrininde keşfedin.</p>
      <form class="ao-market-filter" method="get" action="#ilanlar">
        <input name="q" placeholder="Domain, script, tasarım veya hizmet ara...">
        <select name="type"><option value="">Tüm türler</option><option value="domain">Domain</option><option value="service">Hizmet</option><option value="project">Proje / Script</option></select>
        <select name="budget"><option value="">Bütçe</option><option>0 - 5.000 TL</option><option>5.000 - 25.000 TL</option><option>25.000 TL+</option></select>
        <button type="submit">Ara</button>
      </form>
      <div class="ao-market-stats">
        <div><strong><?= number_format((int)$stats['active'],0,',','.') ?></strong><span>Aktif ilan</span></div>
        <div><strong><?= number_format((int)$stats['featured'],0,',','.') ?></strong><span>Öne çıkan</span></div>
        <div><strong><?= number_format((int)$stats['premium'],0,',','.') ?></strong><span>Premium ilan</span></div>
        <div><strong><?= number_format((float)$stats['volume'],0,',','.') ?> TL</strong><span>Vitrin değeri</span></div>
      </div>
      <div class="ao-content-actions"><a class="ao-content-btn" href="#ilanlar">İlanları Keşfet</a><a class="ao-content-btn secondary" href="<?= url('client/register') ?>">Satıcı / Müşteri Ol</a></div>
    </section>
    <section class="ao-section-head"><h2>Kategoriler</h2><p>Domain, tasarım, yazılım ve dijital hizmetleri tek merkezden yönetin.</p></section>
    <section class="ao-category-grid">
      <?php $catRows=$categories ?: $catFallback; foreach($catRows as $i=>$c): $ico=$c['icon'] ?? ($catFallback[$i%count($catFallback)]['icon'] ?? '✨'); $sub=$c['subtitle'] ?? $c['listing_type'] ?? 'Dijital ürün'; ?>
        <a href="#ilanlar"><span><?= e($ico) ?></span><b><?= e($c['name']) ?></b><small><?= e($sub) ?></small></a>
      <?php endforeach; ?>
    </section>
    <section id="ilanlar" class="ao-section-head"><h2>Tüm İlanlar</h2><p>Aktif marketplace ilanları ve teklif formları.</p></section>
    <?php if($listings): ?>
      <div class="ao-market-listings">
        <?php foreach($listings as $l): ?>
        <article class="ao-market-card">
          <div class="top"><span><?= e(ao_market_icon_rc14($l['listing_type'] ?? 'service')) ?></span><em><?= e($l['category'] ?: ($l['listing_type'] ?? 'İlan')) ?></em></div>
          <h3><?= e($l['title']) ?></h3>
          <p><?= e(ao_market_desc_rc14($l)) ?></p>
          <div class="meta"><span><?= e($l['listing_type'] ?? 'service') ?></span><?= !empty($l['is_premium'])?'<span>🏆 Premium</span>':'' ?><?= !empty($l['is_urgent'])?'<span>⚡ Hızlı satış</span>':'' ?></div>
          <strong class="price"><?= number_format((float)($l['price'] ?? 0),2,',','.') ?> <?= e($l['currency'] ?? 'TRY') ?></strong>
          <a class="ao-content-btn secondary" href="#">Teklif Ver</a>
        </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="ao-empty-premium"><h3>Marketplace vitrin hazır.</h3><p>Admin panelinden aktif ilan yayınlandığında premium kartlar burada otomatik görünür.</p><a class="ao-content-btn" href="<?= url('client/register') ?>">İlan / Hizmet Talebi Aç</a></div>
    <?php endif; ?>
  </div>
</section>
