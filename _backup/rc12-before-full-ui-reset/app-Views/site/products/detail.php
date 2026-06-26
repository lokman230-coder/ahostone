<?php
$product=$product ?? []; $pricing=$pricing ?? [];
$plainDesc = trim(function_exists('ao_v2400_plain_from_html') ? ao_v2400_plain_from_html($product['description'] ?? '', 800) : strip_tags((string)($product['description'] ?? '')));
$name = $product['name'] ?? 'Ürün'; $type=strtolower((string)($product['type'] ?? $product['group_name'] ?? ''));
if($plainDesc===''){
  if(str_contains($type,'mobile') || str_contains(mb_strtolower($name,'UTF-8'),'android')){
    $defaultHtml='<h2>'.e($name).' ile mobilde güçlü başlangıç</h2><p>Bu paket, markanız için profesyonel Android uygulama başlangıcı, yayınlama hazırlığı ve yönetilebilir içerik yapısı sunar.</p><div class="ao-product-feature-grid"><div><b>Modern Arayüz</b><p>Markaya uygun açılış, menü, içerik ve iletişim ekranları.</p></div><div><b>Yönetilebilir İçerik</b><p>Admin panelinden içerik, görsel ve bağlantıları düzenleyebilme.</p></div><div><b>Yayın Hazırlığı</b><p>Google Play sürecine uygun temel paketleme ve kontrol listesi.</p></div></div><h3>Kimler için uygun?</h3><ul><li>Kurumsal uygulama başlangıcı isteyen firmalar</li><li>Radyo, haber, hizmet veya katalog uygulaması isteyen markalar</li><li>Sonradan geliştirilebilir mobil altyapı isteyen işletmeler</li></ul><h3>Kurulum ve teslimat</h3><p>Sipariş sonrası ihtiyaç analizi yapılır, marka bilgileri alınır ve uygulama iskeleti hazırlanır. Ek modüller ürün yapılandırmasından seçilebilir.</p><div class="ao-product-faq"><b>Sık Sorulan Soru:</b> İçerikleri sonradan değiştirebilir miyim?<br>Evet, uygun paketlerde içerikler admin paneli üzerinden güncellenebilir.</div>';
  } elseif(str_contains($type,'hosting') || str_contains(mb_strtolower($name,'UTF-8'),'linux')){
    $defaultHtml='<h2>'.e($name).' hosting paketi</h2><p>Hızlı, güvenli ve yönetilebilir web hosting altyapısı ile sitenizi yayına alın. Paket özellikleri admin panelinden sonradan değiştirilebilir.</p><div class="ao-product-feature-grid"><div><b>SSD Altyapı</b><p>Web siteleri için hızlı disk ve optimize edilmiş sunucu ortamı.</p></div><div><b>Kolay Yönetim</b><p>Panel, e-posta, veritabanı ve dosya yönetimi için pratik yapı.</p></div><div><b>Güvenli Başlangıç</b><p>SSL, yedekleme ve güvenlik ek paketleriyle genişletilebilir.</p></div></div><h3>Kimler için uygun?</h3><ul><li>Kurumsal web sitesi sahipleri</li><li>Blog, tanıtım sitesi ve küçük işletmeler</li><li>Başlangıç seviyesinde ekonomik hosting arayanlar</li></ul><h3>Teknik bilgiler</h3><table><tr><th>Kurulum</th><td>Ödeme sonrası otomasyon veya manuel onay ile</td></tr><tr><th>Yönetim</th><td>Müşteri panelinden hizmet takibi</td></tr><tr><th>Yükseltme</th><td>Üst pakete geçiş desteklenir</td></tr></table>';
  } else {
    $defaultHtml='<h2>'.e($name).' paketi</h2><p>Bu ürün, Ahost One üzerinden yönetilebilir profesyonel hizmet paketi olarak hazırlanmıştır. İçerik, fiyatlandırma ve ek özellikler admin panelinden sonradan değiştirilebilir.</p><div class="ao-product-feature-grid"><div><b>Yönetilebilir</b><p>Ürün açıklaması, fiyat ve görünürlük ayarları panelden düzenlenir.</p></div><div><b>Esnek Yapı</b><p>Ek paket, yapılandırma ve özel alanlarla genişletilebilir.</p></div><div><b>Siparişe Hazır</b><p>Sepet, domain ve ödeme akışıyla entegre çalışır.</p></div></div><h3>Kimler için uygun?</h3><ul><li>Hızlı siparişe açılacak dijital hizmetler</li><li>Teklif veya abonelik modeliyle satılan paketler</li><li>Sonradan özelleştirilecek ürün grupları</li></ul><h3>Kurulum / teslimat</h3><p>Sipariş sonrası ürün türüne göre otomasyon veya manuel teslimat akışı başlatılır.</p>';
  }
} else {
  $defaultHtml = function_exists('ao_v2400_sanitize_product_html') ? ao_v2400_sanitize_product_html($product['description'] ?? '') : $product['description'];
}
$summary = trim($product['short_description'] ?? '') ?: ($plainDesc ?: 'Bu paket, profesyonel hizmet yapısı ve yönetilebilir ürün içeriğiyle siparişe hazırdır.');
?>

<?php ob_start(); ?>
<section class="ao-content-grid two">
  <div class="ao-content-card">
    <span class="ao-content-badge"><?= e($product['group_name'] ?? 'Ürün') ?></span>
    <h3>Ürün Özeti</h3>
    <p><?= e($summary) ?></p>
    <div class="ao-content-actions">
      <a class="ao-content-btn" href="<?= url('cart/add?product='.rawurlencode($product['slug']??'')) ?>">Satın Al / Teklif İste</a>
      <a class="ao-content-btn secondary" href="<?= url('urunler') ?>">Tüm Ürünler</a>
    </div>
  </div>
  <div class="ao-content-card">
    <span class="ao-content-badge">Fiyatlandırma</span>
    <?php if(!$pricing): $price=ao_v2335_primary_price($product); ?>
      <div class="ao-content-price"><strong><?= ($price['amount'] ?? 0)>0 ? number_format((float)$price['amount'],2,',','.').' TL' : 'Teklif Al' ?></strong><span><?= ($price['amount'] ?? 0)>0 ? '/ '.e(ao_v2335_cycle_label($price['cycle'] ?? 'monthly')) : '' ?></span></div>
    <?php else: ?>
      <div class="ao-content-table" style="grid-template-columns:1.2fr 1fr 1fr 1fr">
        <div class="head">Periyot</div><div class="head">USD</div><div class="head">TRY</div><div class="head">Kurulum</div>
        <?php foreach($pricing as $r):
          $usd=(float)($r['price_usd'] ?? 0); $try=(float)($r['price_try'] ?? 0); if($try<=0 && $usd>0) $try=ao_v23_price_try($usd,'USD');
          $setupTry=(float)($r['setup_fee_try'] ?? ($r['setup_fee'] ?? 0));
        ?>
          <div><?= e(ao_v2335_cycle_label($r['cycle'] ?? 'monthly')) ?></div><div><?= $usd>0 ? number_format($usd,2,'.','').' USD' : '-' ?></div><div><strong><?= $try>0 ? number_format($try,2,',','.').' TL' : 'Teklif' ?></strong></div><div><?= $setupTry>0 ? number_format($setupTry,2,',','.').' TL' : '-' ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<section class="ao-content-panel">
  <div class="ao-content-meta"><strong>Ürün Açıklaması</strong><span>•</span><span>Admin içerik görünümü</span></div>
  <div class="ao-content-rich ao-product-rich-content"><?= $defaultHtml ?></div>
</section>
<?php
$content=ob_get_clean();
$heroTitle=$name;
$kicker=$product['group_name'] ?? 'Ürün';
$summary=$summary;
$breadcrumbs=[['label'=>'Ana Sayfa','href'=>url('')],['label'=>'Ürünler','href'=>url('urunler')],['label'=>$name]];
$actions=[['label'=>'Satın Al / Teklif İste','href'=>url('cart/add?product='.rawurlencode($product['slug']??''))],['label'=>'Tüm Ürünler','href'=>url('urunler'),'secondary'=>true]];
require __DIR__.'/../shared/content-page.php';
?>
