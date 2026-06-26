<?php
$cart=$_SESSION['ao_cart'] ?? $_SESSION['cart'] ?? [];
$total=0;
$cycleLabels=['onetime'=>'Tek Seferlik','monthly'=>'Aylık','quarterly'=>'3 Aylık','semiannually'=>'6 Aylık','annually'=>'Yıllık','biennially'=>'2 Yıllık','triennially'=>'3 Yıllık'];
$domainLabels=['register'=>'Yeni domain kayıt','transfer'=>'Domain transfer','existing'=>'Mevcut domainimi kullanacağım','dns'=>'Sadece DNS güncellemek istiyorum'];
$defaultAddons=[['key'=>'ssl','name'=>'SSL Sertifikası','price'=>0],['key'=>'backup','name'=>'Günlük Yedekleme','price'=>0],['key'=>'email','name'=>'Kurumsal E-Posta','price'=>0],['key'=>'litespeed','name'=>'LiteSpeed / Performans','price'=>0]];
function ao_cart_cycles_v249($slug,$current){
  $rows=[];
  try{ $q=db()->prepare('SELECT pp.cycle, pp.price, pp.currency FROM product_pricing pp JOIN products p ON p.id=pp.product_id WHERE p.slug=? AND pp.is_active=1 AND pp.price>=0 ORDER BY FIELD(pp.cycle,"monthly","annually","biennially","triennially","quarterly","semiannually","onetime"), pp.id'); $q->execute([$slug]); $rows=$q->fetchAll(); }catch(Throwable $e){}
  if(!$rows) $rows=[['cycle'=>$current,'price'=>null,'currency'=>'TRY']];
  return $rows;
}
function ao_cart_addons_v249($slug){
  $rows=[];
  try{ $q=db()->prepare('SELECT a.addon_key AS `key`, a.name, a.price, a.currency FROM product_checkout_addons a JOIN products p ON p.id=a.product_id WHERE p.slug=? AND a.is_active=1 ORDER BY a.sort_order,a.id'); $q->execute([$slug]); $rows=$q->fetchAll(); }catch(Throwable $e){}
  return $rows ?: $GLOBALS['defaultAddons'];
}
?>
<section class="site-section"><div class="site-container"><h1>Sepet ve Sipariş Akışı</h1><p>Ödeme adımına kadar giriş yapmadan ürün, domain ve ek hizmetleri düzenleyebilirsiniz.</p>
<?php if(!$cart): ?><div class="u2-card"><p>Sepetiniz boş.</p><a class="u2-btn" href="<?= url('urunler') ?>">Ürünleri İncele</a></div><?php else: ?>
<form method="post" action="<?= url('cart/update') ?>"><?= csrf_field() ?><div class="cart-smart-list">
<?php foreach($cart as $slug=>$item):
  $qty=max(1,(int)($item['qty']??1)); $price=(float)($item['price']??0); $cycle=$item['cycle'] ?? 'monthly'; $selectedAddons=$item['addons'] ?? []; if(!is_array($selectedAddons)) $selectedAddons=[]; $addonTotal=0; foreach(ao_cart_addons_v249($slug) as $ad){ if(in_array($ad['key'],$selectedAddons,true)) $addonTotal+=(float)($ad['price']??0); }
  $line=($price+$addonTotal)*$qty; $total+=$line; $cycles=ao_cart_cycles_v249($slug,$cycle); $addons=ao_cart_addons_v249($slug); ?>
  <article class="cart-smart-item"><div class="cart-item-main"><small><?= e($item['group'] ?? '') ?></small><h3><?= e($item['name'] ?? $slug) ?></h3><p><span class="cycle-label"><?= e($cycleLabels[$cycle] ?? $cycle) ?></span> · <?= e($item['currency'] ?? 'TRY') ?></p></div><div class="cart-smart-controls"><label>Adet <input type="number" min="0" name="qty[<?= e($slug) ?>]" value="<?= $qty ?>"></label><strong>₺<?= number_format($line,2,',','.') ?></strong><a href="<?= url('cart/remove?product='.rawurlencode($slug)) ?>">Sil</a></div></article>
  <div class="ao-checkout-wizard">
    <section class="ao-checkout-step"><h2>1. Fatura Periyodu</h2><div class="ao-choice-grid"><?php foreach($cycles as $r): $c=$r['cycle'] ?? 'monthly'; ?><label class="ao-choice"><input type="radio" name="cycle[<?= e($slug) ?>]" value="<?= e($c) ?>" <?= $c===$cycle?'checked':'' ?>><span><strong><?= e($cycleLabels[$c] ?? $c) ?></strong><br><small><?= isset($r['price']) ? number_format((float)$r['price'],2,',','.').' '.e($r['currency'] ?? 'TRY') : 'Mevcut fiyat' ?></small></span></label><?php endforeach; ?></div></section>
    <section class="ao-checkout-step"><h2>2. Domain İşlemi</h2><div class="ao-choice-grid"><?php foreach($domainLabels as $dk=>$dl): ?><label class="ao-choice"><input type="radio" name="domain_action[<?= e($slug) ?>]" value="<?= e($dk) ?>" <?= (($item['domain_action'] ?? 'register')===$dk)?'checked':'' ?>><span><?= e($dl) ?></span></label><?php endforeach; ?></div><div class="ao-domain-inputs" style="margin-top:12px"><input name="domain_name[<?= e($slug) ?>]" value="<?= e($item['domain_name'] ?? '') ?>" placeholder="ornek.com"><input name="epp_code[<?= e($slug) ?>]" value="<?= e($item['epp_code'] ?? '') ?>" placeholder="Transfer kodu / EPP"></div><p class="muted">Sadece DNS seçilirse ödeme sonrası nameserver bilgileri gösterilir.</p></section>
    <section class="ao-checkout-step"><h2>3. Ek Paketler / Ek Özellikler</h2><?php foreach($addons as $ad): ?><label class="ao-addon-row"><span><input type="checkbox" name="addons[<?= e($slug) ?>][]" value="<?= e($ad['key']) ?>" <?= in_array($ad['key'],$selectedAddons,true)?'checked':'' ?>> <strong><?= e($ad['name']) ?></strong></span><em><?= ((float)($ad['price']??0)>0) ? '+ ₺'.number_format((float)$ad['price'],2,',','.') : 'Admin fiyat tanımlayabilir' ?></em></label><?php endforeach; ?></section>
  </div>
<?php endforeach; ?></div><div class="cart-smart-summary"><div><span>Ara Toplam</span><strong>₺<?= number_format($total,2,',','.') ?></strong></div><div><span>Vergi / indirim</span><em>Ödeme adımında hesaplanır</em></div><div class="grand"><span>Genel Toplam</span><strong>₺<?= number_format($total,2,',','.') ?></strong></div><button class="u2-btn soft">Sepeti Güncelle</button><div class="ao-auth-reminder">Giriş veya kayıt işlemi ödeme ekranında devreye girer. Öncesinde sepeti düzenlemeye devam edebilirsiniz.</div><a class="u2-btn" href="<?= url('checkout') ?>">Ödeme Adımına Geç</a></div></form>
<?php endif; ?></div></section>
<style>.cart-smart-list{display:grid;gap:14px;margin:28px 0}.cart-smart-item{display:flex;justify-content:space-between;gap:18px;align-items:center;padding:18px;border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 10px 28px rgba(15,23,42,.06)}.cart-smart-item h3{margin:4px 0}.cart-smart-item small{font-weight:900;color:#2563eb}.cart-smart-controls{display:flex;gap:12px;align-items:center;flex-wrap:wrap}.cart-smart-controls input{width:70px;padding:10px;border:1px solid #cbd5e1;border-radius:10px}.cart-smart-controls a{font-weight:900;color:#dc2626;text-decoration:none}.cart-smart-summary{max-width:460px;margin-left:auto;padding:22px;border:1px solid #e2e8f0;border-radius:20px;background:#f8fafc;display:grid;gap:12px}.cart-smart-summary>div{display:flex;justify-content:space-between}.cart-smart-summary .grand{font-size:20px;border-top:1px solid #e2e8f0;padding-top:12px}@media(max-width:700px){.cart-smart-item{align-items:flex-start;flex-direction:column}.cart-smart-summary{max-width:none;margin-left:0}}</style>
