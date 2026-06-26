<?php
$groups = $groups ?? [];
$products = $products ?? [];
$selectedGroup = $selectedGroup ?? null;
function ao_v2339_product_icon($type){
  $type = mb_strtolower((string)$type,'UTF-8');
  if(str_contains($type,'hosting')) return '☁️';
  if(str_contains($type,'server') || str_contains($type,'vps')) return '🖥️';
  if(str_contains($type,'domain')) return '🌐';
  if(str_contains($type,'ssl')) return '🔒';
  if(str_contains($type,'web')) return '🎨';
  if(str_contains($type,'mobile')) return '📱';
  if(str_contains($type,'seo')) return '📈';
  return '🚀';
}
function ao_v2339_product_features($p){
  $text = trim(strip_tags((string)($p['short_description'] ?? $p['description'] ?? '')));
  $features = [];
  $patterns = [
    'DISK\\s+([^A-Z]+?)(?=\\s+(TRAF|TRAFİK|BANDWIDTH|DATABASE|EMAIL|TLD|$))' => '$1 SSD Disk',
    '(TRAF|TRAFİK|BANDWIDTH)\\s+([^A-Z]+?)(?=\\s+(DATABASE|EMAIL|TLD|DISK|$))' => '$2 Trafik',
    'DATABASE\\s+([^A-Z]+?)(?=\\s+(EMAIL|TLD|DISK|TRAF|TRAFİK|$))' => '$1 Veritabanı',
    'EMAIL\\s+([^A-Z]+?)(?=\\s+(TLD|DISK|TRAF|TRAFİK|DATABASE|$))' => '$1 E-posta Hesabı',
    'TLD\\s+([^A-Z]+?)(?=\\s+(DISK|TRAF|TRAFİK|DATABASE|EMAIL|$))' => '$1 Alt Domain',
  ];
  foreach($patterns as $rx=>$fmt){
    if(preg_match('~'.$rx.'~iu', $text, $m)){
      $val = trim($m[count($m)-1]);
      $val = preg_replace('~\\s+~',' ',$val);
      $features[] = trim(str_replace(['$1','$2'], [$m[1]??$val,$m[2]??$val], $fmt));
    }
  }
  $features = array_values(array_filter(array_unique($features)));
  if(count($features) >= 2) return array_slice($features,0,5);
  $type = mb_strtolower((string)($p['type'] ?? $p['group_name'] ?? ''),'UTF-8');
  if(str_contains($type,'hosting')) return ['NVMe SSD altyapı','Ücretsiz SSL','7/24 destek','Kolay panel yönetimi'];
  if(str_contains($type,'server') || str_contains($type,'vps')) return ['Yüksek performans','Ölçeklenebilir kaynak','Yönetim desteği','Anlık teslimat'];
  if(str_contains($type,'domain')) return ['Hızlı kayıt','DNS yönetimi','Transfer desteği'];
  if(str_contains($type,'ssl')) return ['Güvenli bağlantı','SEO uyumlu','Kolay kurulum'];
  return ['Kurumsal çözüm','Hızlı teslimat','Uzman destek'];
}
?>
<section class="platform-page product-catalog-page">
  <div class="platform-hero product-catalog-hero">
    <div>
      <span class="badge">Ürün Merkezi</span>
      <h1><?= $selectedGroup ? e($selectedGroup['name']) : 'Ahost One Ürünleri' ?></h1>
      <p><?= $selectedGroup ? e($selectedGroup['description'] ?? 'Bu gruba ait aktif ürünler.') : 'Hosting, VPS, domain, SiteBuilder, MobileBuilder, web tasarım ve dijital hizmet paketlerini tek vitrinde inceleyin.' ?></p>
      <div class="hero-actions"><a class="site-btn" href="<?= url('client/register') ?>">Sipariş / Teklif Başlat</a><a class="site-btn secondary ao-order-btn" href="<?= url('domain') ?>">Domain Sorgula</a></div>
    </div>
    <div class="platform-visual"></div>
  </div>

  <?php if($groups): ?>
  <div class="ao-product-group-pills">
    <a class="<?= !$selectedGroup ? 'active' : '' ?>" href="<?= url('urunler') ?>">Tüm Ürünler</a>
    <?php foreach($groups as $g): if((int)($g['product_count'] ?? 0) <= 0) continue; ?>
      <a class="<?= ($selectedGroup && $selectedGroup['slug']===$g['slug']) ? 'active' : '' ?>" href="<?= url('urun-grubu/'.$g['slug']) ?>"><?= e($g['name']) ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if(!$products): ?>
    <div class="platform-card ao-empty-products"><h3>Henüz yayında ürün yok</h3><p>Admin panelinden aktif ve görünür ürün eklediğinizde bu alanda otomatik listelenir.</p><a href="<?= url('client/register') ?>">Bilgi Al →</a></div>
  <?php else: ?>
    <div class="platform-grid ao-product-grid">
      <?php foreach($products as $p): $price=ao_v2335_primary_price($p); $features=ao_v2339_product_features($p); ?>
        <div class="platform-card ao-product-card">
          <div class="ao-product-icon"><?= e(ao_v2339_product_icon($p['type'] ?? $p['group_name'] ?? '')) ?></div>
          <span class="badge"><?= e($p['group_name'] ?? 'Ürün') ?></span>
          <h3><?= e($p['name']) ?></h3>
          <p><?= e(mb_substr(strip_tags((string)($p['short_description'] ?? $p['description'] ?? '')),0,150)) ?></p>
          <ul class="ao-product-features"><?php foreach($features as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
          <div class="ao-product-price">
            <?php if(($price['amount'] ?? 0) > 0): ?>
              <strong><?= number_format((float)$price['amount'], 2, ',', '.') ?> TL</strong>
              <span>/ <?= e(ao_v2335_cycle_label($price['cycle'] ?? 'monthly')) ?></span>
            <?php else: ?>
              <strong>Teklif Al</strong>
            <?php endif; ?>
          </div>
          <div class="hero-actions ao-product-actions"><a class="site-btn" href="<?= url('urun/'.$p['slug']) ?>">İncele</a><a class="site-btn secondary ao-order-btn" href="<?= url('cart/add?product='.rawurlencode($p['slug'])) ?>">Satın Al</a></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
