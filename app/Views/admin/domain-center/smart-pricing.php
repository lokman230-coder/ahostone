<?php
$flash=get_flash();
ao_schema_ensure_v810();
$rules=[]; $cache=[]; $regs=[]; $fees=[]; $sample=null;
try { $rules=db()->query('SELECT * FROM domain_pricing_rules ORDER BY tld')->fetchAll(); } catch(Throwable $e) {}
try { $cache=db()->query('SELECT * FROM registrar_price_cache ORDER BY tld, cost ASC')->fetchAll(); } catch(Throwable $e) {}
try { $regs=db()->query('SELECT * FROM domain_registrars ORDER BY status DESC, name')->fetchAll(); } catch(Throwable $e) {}
try { $fees=db()->query('SELECT * FROM payment_fee_rules ORDER BY gateway')->fetchAll(); } catch(Throwable $e) {}
if (!empty($_GET['domain'])) { try { $sample=ao_smart_domain_quote($_GET['domain'],'register'); $sample['payment']=ao_payment_fee_quote((float)$sample['sale_price'], $_GET['gateway'] ?? 'paytr'); } catch(Throwable $e) { $sample=['error'=>$e->getMessage()]; } }
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><h2>Smart Registrar Router & Fiyat Motoru</h2><p>Domain alış fiyatlarını registrar bazlı takip eder, en ucuz registrarı seçer ve kâr/komisyon ekleyerek müşteriye satış fiyatı üretir.</p></div><a class="ao-btn soft" href="<?= url('admin/domain-center/registrars') ?>">Registrarlar</a></div>

<div class="ao-grid two">
 <div class="ao-card">
  <h3>Akıllı Fiyat Kuralı</h3>
  <form class="ao-form" method="post" action="<?= url('admin/domain-center/smart-pricing-save') ?>">
   <?= csrf_field() ?>
   <div class="ao-form-grid">
    <label>TLD<input name="tld" value=".com"></label>
    <label>Model<select name="mode"><option value="percent">Yüzde + Sabit</option><option value="fixed">Sabit kâr</option></select></label>
    <label>Kâr %<input type="number" step="0.01" name="markup_percent" value="30"></label>
    <label>Sabit Kâr<input type="number" step="0.01" name="markup_fixed" value="0"></label>
    <label>Minimum Kâr<input type="number" step="0.01" name="min_profit" value="3"></label>
    <label>Para Birimi<input name="currency" value="USD"></label>
    <label>Registrar Override<select name="registrar_override"><option value="">Otomatik en ucuz</option><?php foreach($regs as $r): ?><option value="<?= e($r['slug']) ?>"><?= e($r['name']) ?> (<?= e($r['status']) ?>)</option><?php endforeach; ?></select></label>
   </div>
   <button class="ao-btn">Kuralı Kaydet</button>
  </form>
 </div>
 <div class="ao-card">
  <h3>Registrar Alış Fiyatı</h3>
  <form class="ao-form" method="post" action="<?= url('admin/domain-center/registrar-cost-save') ?>">
   <?= csrf_field() ?>
   <div class="ao-form-grid">
    <label>Registrar<select name="registrar_slug"><?php foreach($regs as $r): ?><option value="<?= e($r['slug']) ?>"><?= e($r['name']) ?></option><?php endforeach; ?></select></label>
    <label>TLD<input name="tld" value=".com"></label>
    <label>İşlem<select name="action"><option value="register">Kayıt</option><option value="renew">Yenileme</option><option value="transfer">Transfer</option></select></label>
    <label>Alış Fiyatı<input type="number" step="0.0001" name="cost" value="15.00"></label>
    <label>Para Birimi<input name="currency" value="USD"></label>
   </div>
   <button class="ao-btn">Alış Fiyatını Kaydet</button>
  </form>
 </div>
</div>

<div class="ao-card">
 <h3>Canlı Fiyat Testi</h3>
 <form class="ao-form" method="get" action="<?= url('admin/domain-center/smart-pricing') ?>">
  <div class="ao-form-grid"><label>Domain<input name="domain" value="<?= e($_GET['domain'] ?? 'example.com') ?>"></label><label>Ödeme Kanalı<select name="gateway"><?php foreach($fees as $f): ?><option value="<?= e($f['gateway']) ?>" <?= (($_GET['gateway']??'paytr')===$f['gateway'])?'selected':'' ?>><?= e($f['label']) ?></option><?php endforeach; ?></select></label></div>
  <button class="ao-btn soft">Hesapla</button>
 </form>
 <?php if($sample): ?>
  <?php if(!empty($sample['error'])): ?><div class="ao-alert error"><?= e($sample['error']) ?></div><?php else: ?>
  <div class="ao-kpi-grid">
   <div class="ao-kpi"><span>Seçilen Registrar</span><strong><?= e($sample['selected_registrar']) ?></strong></div>
   <div class="ao-kpi"><span>Alış</span><strong><?= e($sample['registrar_cost'].' '.$sample['currency']) ?></strong></div>
   <div class="ao-kpi"><span>Satış</span><strong><?= e($sample['sale_price'].' '.$sample['currency']) ?></strong></div>
   <div class="ao-kpi"><span>Kâr</span><strong><?= e($sample['profit'].' '.$sample['currency']) ?></strong></div>
  </div>
  <h4>Ödeme Komisyonu</h4>
  <p>Komisyon: <strong><?= e($sample['payment']['fee'].' '.$sample['payment']['currency']) ?></strong> · Müşteri Toplamı: <strong><?= e($sample['payment']['customer_total'].' '.$sample['payment']['currency']) ?></strong> · Firma Net: <strong><?= e($sample['payment']['company_net'].' '.$sample['payment']['currency']) ?></strong></p>
  <?php endif; ?>
 <?php endif; ?>
</div>

<div class="ao-card"><h3>Fiyat Kuralları</h3><table class="ao-table"><thead><tr><th>TLD</th><th>%</th><th>Sabit</th><th>Minimum</th><th>Override</th><th>Para Birimi</th></tr></thead><tbody><?php foreach($rules as $r): ?><tr><td><strong><?= e($r['tld']) ?></strong></td><td><?= e($r['markup_percent']) ?></td><td><?= e($r['markup_fixed']) ?></td><td><?= e($r['min_profit']) ?></td><td><?= e($r['registrar_override'] ?: 'Otomatik') ?></td><td><?= e($r['currency']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="ao-card"><h3>Registrar Alış Fiyatları</h3><table class="ao-table"><thead><tr><th>Registrar</th><th>TLD</th><th>İşlem</th><th>Alış</th><th>Kaynak</th><th>Son Kontrol</th></tr></thead><tbody><?php foreach($cache as $c): ?><tr><td><?= e($c['registrar_slug']) ?></td><td><?= e($c['tld']) ?></td><td><?= e($c['action']) ?></td><td><?= e($c['cost'].' '.$c['currency']) ?></td><td><?= e($c['source']) ?></td><td><?= e($c['last_checked_at']) ?></td></tr><?php endforeach; if(!$cache): ?><tr><td colspan="6">Henüz alış fiyatı yok. Üst formdan manuel girilebilir veya registrar API fiyat döndürürse otomatik önbelleğe alınır.</td></tr><?php endif; ?></tbody></table></div>
