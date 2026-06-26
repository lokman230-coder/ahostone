<?php
$homeGroups=[]; $homeProducts=[];
try{
    $homeGroups=ao_v2335_product_groups();
    $homeProducts=ao_v2335_products();
}catch(Throwable $e){}
$homeByGroup=[];
foreach($homeProducts as $item) $homeByGroup[(string)($item['group_slug']??'diger')][]=$item;
function ao_home_product_icon($type){
    $type=mb_strtolower((string)$type,'UTF-8');
    if(str_contains($type,'hosting')) return 'H';
    if(str_contains($type,'server')||str_contains($type,'vps')) return 'VPS';
    if(str_contains($type,'domain')) return 'DNS';
    if(str_contains($type,'ssl')) return 'SSL';
    if(str_contains($type,'mobile')) return 'APP';
    if(str_contains($type,'web')||str_contains($type,'site')) return 'WEB';
    if(str_contains($type,'seo')) return 'SEO';
    if(str_contains($type,'market')) return 'PRO';
    return 'SaaS';
}
?>
<section class="e-site-hero">
  <div>
    <span class="u2-kicker">Yeni nesil dijital hizmet platformu</span>
    <h1>İşinizi büyüten tüm dijital çözümler <span class="ao-gradient-text">tek merkezde</span>.</h1>
    <p>Hosting, domain, sunucu, web tasarım, mobil uygulama, SEO ve dijital hizmetleri modern bir satın alma ve yönetim deneyimiyle keşfedin.</p>
    <div class="hero-actions"><a class="u2-btn" href="#tum-urunler">Ürünleri Keşfet</a><a class="u2-btn dark" href="<?= url('referanslar') ?>">Referansları İncele</a><a class="u2-btn soft" href="<?= url('domain') ?>">Domain Sorgula</a></div>
  </div>
  <div class="e-domain-card" data-domain-widget>
    <span class="u2-kicker">Akıllı Domain Arama</span>
    <h2>Markanız için doğru adı bulun</h2>
    <form><div class="searchline"><input data-domain-input placeholder="ornekdomain.com"><button type="button" data-domain-search>Sorgula</button></div>
    <div class="e-tld-row"><span>.com <span class="ao-price" data-price-base="<?= number_format(ao_domain_sale_price('com'),2,'.','') ?>">₺<?= number_format(ao_domain_sale_price('com'),2,',','.') ?></span></span><span>.net <span class="ao-price" data-price-base="<?= number_format(ao_domain_sale_price('net'),2,'.','') ?>">₺<?= number_format(ao_domain_sale_price('net'),2,',','.') ?></span></span><span>.org <span class="ao-price" data-price-base="<?= number_format(ao_domain_sale_price('org'),2,'.','') ?>">₺<?= number_format(ao_domain_sale_price('org'),2,',','.') ?></span></span></div>
    <div class="home-domain-tools"><button type="button" data-domain-tool="whois">WHOIS</button><button type="button" data-domain-tool="dns">DNS</button><button type="button" data-domain-tool="ssl">SSL</button><button type="button" data-domain-tool="valuation">Değerleme</button></div></form>
    <div class="ao-domain-search-result" data-domain-search-result></div>
  </div>
</section>

<section class="e-stats-strip"><div><strong><?= count($homeProducts) ?>+</strong><span>Düzenlenebilir ürün</span></div><div><strong><?= count($homeGroups) ?></strong><span>Çözüm kategorisi</span></div><div><strong>7/24</strong><span>Merkezi yönetim</span></div><div><strong>Premium</strong><span>SaaS müşteri deneyimi</span></div></section>

<section class="home-catalog" id="tum-urunler">
  <div class="home-catalog-head"><div><span class="u2-kicker">Tüm ürün ve hizmetler</span><h2>İhtiyacınıza uygun çözümü seçin</h2><p>Bu katalog Ürün Merkezi ile senkron çalışır. Admin panelindeki değişiklikler ana sayfaya otomatik yansır.</p></div><a class="u2-btn soft" href="<?= url('urunler') ?>">Katalog Görünümü</a></div>
  <nav class="home-group-nav" aria-label="Ürün grupları">
    <?php foreach($homeGroups as $group): if(empty($homeByGroup[$group['slug']])) continue; ?><a href="#grup-<?= e($group['slug']) ?>"><?= e($group['name']) ?></a><?php endforeach; ?>
  </nav>
  <?php foreach($homeGroups as $group): $groupProducts=$homeByGroup[$group['slug']]??[]; if(!$groupProducts) continue; ?>
    <section class="home-product-group" id="grup-<?= e($group['slug']) ?>">
      <div class="home-group-title"><div><span><?= e($group['type']??'Çözüm') ?></span><h3><?= e($group['name']) ?></h3><p><?= e($group['description']??'') ?></p></div><a href="<?= url('urun-grubu/'.$group['slug']) ?>">Grubu incele →</a></div>
      <div class="home-product-grid">
        <?php foreach($groupProducts as $product): $price=ao_v2335_primary_price($product); $amount=(float)($price['amount']??0); ?>
          <article class="home-product-card">
            <div class="home-product-top"><span class="home-product-icon"><?= e(ao_home_product_icon(($product['type']??'').' '.($group['type']??''))) ?></span><span class="home-product-group-name"><?= e($group['name']) ?></span></div>
            <h4><?= e($product['name']) ?></h4>
            <p><?= e(mb_substr(strip_tags((string)($product['short_description']??$product['description']??'')),0,145)) ?></p>
            <div class="home-product-price"><?php if($amount>0): ?><strong class="ao-price" data-price-base="<?= e(number_format($amount,2,'.','')) ?>">₺<?= number_format($amount,2,',','.') ?></strong><span>/ <?= e(ao_v2335_cycle_label($price['cycle']??'monthly')) ?></span><?php else: ?><strong>Teklif Al</strong><?php endif; ?></div>
            <div class="home-product-actions"><a class="inspect" href="<?= url('urun/'.$product['slug']) ?>">İncele</a><a class="buy" href="<?= url('cart/add?product='.rawurlencode($product['slug'])) ?>">Satın Al</a></div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</section>

<section class="home-reference-cta"><div><span class="u2-kicker">Web ve Android projeleri</span><h2>Fikirden yayına uzanan seçili çalışmalar</h2><p>10 web sitesi ve 10 Android uygulama referansını ayrı portföy vitrininde inceleyin.</p></div><a class="u2-btn" href="<?= url('referanslar') ?>">Referansları Gör</a></section>

<section class="e-section"><div class="u2-section-title"><div><span class="u2-kicker">Premium müşteri deneyimi</span><h2>İncelemeden satın almaya kadar sade bir yolculuk</h2></div></div><div class="e-testimonials"><div class="u2-card e-service"><h3>Şeffaf katalog</h3><p>Ürün grupları, içerikler ve güncel fiyatlar tek sayfada karşılaştırılır.</p></div><div class="u2-card e-service"><h3>Detaylı inceleme</h3><p>Her ürünün açıklaması, özellikleri ve fiyat seçenekleri ayrı detay sayfasında sunulur.</p></div><div class="u2-card e-service"><h3>Merkezi yönetim</h3><p>Admin panelindeki içerik ve fiyat değişiklikleri siteye otomatik yansır.</p></div></div></section>

<style>
.home-domain-tools{display:flex;gap:8px;flex-wrap:wrap}.home-catalog{max-width:1280px;margin:0 auto;padding:70px 24px}.home-catalog-head,.home-group-title,.home-reference-cta{display:flex;justify-content:space-between;align-items:flex-end;gap:28px}.home-catalog-head h2{font-size:clamp(32px,4vw,50px);margin:8px 0}.home-catalog-head p,.home-group-title p,.home-reference-cta p{color:#64748b;line-height:1.7}.home-group-nav{display:flex;gap:8px;overflow:auto;padding:24px 0 8px;scrollbar-width:thin}.home-group-nav a{white-space:nowrap;padding:9px 13px;border:1px solid #dbe4f0;border-radius:999px;color:#334155;text-decoration:none;font-size:13px;font-weight:800;background:#fff}.home-product-group{padding-top:58px;scroll-margin-top:120px}.home-group-title{align-items:center;margin-bottom:20px}.home-group-title span{font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:#2563eb;font-weight:900}.home-group-title h3{font-size:30px;margin:5px 0}.home-group-title a{text-decoration:none;font-weight:900;white-space:nowrap}.home-product-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:16px}.home-product-card{display:flex;flex-direction:column;min-width:0;padding:20px;border:1px solid #e1e8f2;border-radius:20px;background:#fff;box-shadow:0 8px 28px rgba(15,23,42,.055);transition:.2s}.home-product-card:hover{transform:translateY(-4px);border-color:#bfdbfe;box-shadow:0 18px 38px rgba(15,23,42,.1)}.home-product-top{display:flex;align-items:center;justify-content:space-between;gap:10px}.home-product-icon{display:grid;place-items:center;min-width:42px;height:32px;padding:0 9px;border-radius:10px;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:950}.home-product-group-name{font-size:10px;color:#64748b;font-weight:850;text-align:right}.home-product-card h4{font-size:18px;line-height:1.25;margin:18px 0 9px}.home-product-card p{font-size:13px;line-height:1.65;color:#64748b;margin:0 0 18px;flex:1}.home-product-price{display:flex;align-items:baseline;gap:5px;padding-top:14px;border-top:1px solid #edf2f7}.home-product-price strong{font-size:21px;color:#0f172a}.home-product-price span{font-size:11px;color:#64748b}.home-product-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:15px}.home-product-actions a{text-align:center;text-decoration:none;padding:10px 8px;border-radius:11px;font-size:12px;font-weight:900}.home-product-actions .inspect{background:#f1f5f9;color:#0f172a}.home-product-actions .buy{background:#0f172a;color:#fff}.home-reference-cta{max-width:1232px;margin:20px auto 50px;padding:42px;border-radius:26px;background:linear-gradient(135deg,#eef6ff,#f5f3ff)}.home-reference-cta h2{font-size:34px;margin:8px 0}@media(max-width:1180px){.home-product-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:760px){.home-catalog{padding:48px 14px}.home-catalog-head,.home-group-title,.home-reference-cta{align-items:flex-start;flex-direction:column}.home-product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.home-reference-cta{margin:10px 14px 40px;padding:28px}}@media(max-width:480px){.home-product-grid{grid-template-columns:1fr}.home-product-card{padding:18px}.home-group-title h3{font-size:25px}}
</style>
