<?php
$flash=get_flash(); $customers=[]; $products=[];
try{ $customers=db()->query('SELECT id,first_name,last_name,email FROM customers ORDER BY first_name')->fetchAll(); $products=db()->query('SELECT p.* FROM products p WHERE p.is_active=1 ORDER BY p.name')->fetchAll(); }catch(Throwable $e){}
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><h2>Yeni Sipariş Oluştur</h2><p>Müşteri adına manuel sipariş, domain işlemi, fatura ve ürün/hizmet oluşturma akışını tek ekrandan yönet.</p></div><a class="ao-btn soft" href="<?= url('admin/orders') ?>">Siparişlere Dön</a></div>
<div class="ao-grid two admin-order-create-v2410">
  <div class="ao-card">
    <h3>Sipariş Bilgileri</h3>
    <form method="post" action="<?= url('admin/orders/create') ?>" class="ao-form" id="aoManualOrderForm">
      <?= csrf_field() ?>
      <div class="ao-form-grid">
        <label>Müşteri<select name="customer_id" required data-summary="customer"><option value="">Seçiniz</option><?php foreach($customers as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['first_name'].' '.$c['last_name'].' - '.$c['email']) ?></option><?php endforeach; ?></select></label>
        <label>Ürün<select name="product_id" required data-summary="product"><option value="">Seçiniz</option><?php foreach($products as $p): ?><option value="<?= (int)$p['id'] ?>" data-name="<?= e($p['name']) ?>" data-type="<?= e($p['type']) ?>"><?php $op=function_exists('ao_v2331_product_display_price')?ao_v2331_product_display_price((int)$p['id']):['try'=>(float)($p['price']??0),'usd'=>0]; ?><?= e($p['name'].' / '.$p['type']) ?> - <?= number_format((float)($op['try']??0),2,',','.') ?> ₺<?= ($op['usd']??0)>0?' / '.number_format((float)$op['usd'],2,'.','').' USD':'' ?></option><?php endforeach; ?></select></label>
        <label>Domain / Hizmet Alanı<input name="domain" placeholder="ornek.com" data-summary="domain"></label>
        <label>Domain İşlemi<select name="domain_action" data-summary="domain_action"><option value="none">Sadece hizmet alanı</option><option value="register">Domain kayıt</option><option value="transfer">Domain transfer</option><option value="renew">Domain yenileme</option><option value="dns">DNS / Nameserver değişimi</option><option value="existing">Mevcut domain kullan</option></select></label>
        <label data-domain-extra="transfer" hidden>Transfer Kodu / EPP<input name="epp_code" placeholder="EPP / Auth Code"></label>
        <label data-domain-extra="register renew" hidden>Kayıt/Yenileme Süresi<select name="domain_years"><option value="1">1 Yıl</option><option value="2">2 Yıl</option><option value="3">3 Yıl</option><option value="5">5 Yıl</option></select></label>
        <label data-domain-extra="dns" hidden>Nameserver 1<input name="ns1" placeholder="ns1.ornek.com"></label>
        <label data-domain-extra="dns" hidden>Nameserver 2<input name="ns2" placeholder="ns2.ornek.com"></label>
        <label>Periyot<select name="billing_cycle" data-summary="cycle"><option value="monthly">Aylık</option><option value="annually">Yıllık</option><option value="biennially">2 Yıllık</option><option value="triennially">3 Yıllık</option><option value="onetime">Tek Seferlik</option></select></label>
        <label>Ödeme Yöntemi<select name="payment_method"><option value="manual">Manuel</option><option value="credit-card">Kredi Kartı</option><option value="bank-transfer">Havale/EFT</option><option value="customer-credit">Müşteri Kredisi</option></select></label>
        <label>Özel Tutar<input type="number" step="0.01" name="price" placeholder="Ürün fiyatı 0 ise kullanılır" data-summary="price"></label>
        <label>İndirim<input type="number" step="0.01" name="discount" placeholder="0.00" data-summary="discount"></label>
        <label>Vergi Oranı %<input type="number" step="0.01" name="tax_rate" value="20" data-summary="tax"></label>
        <label class="full">Not<textarea name="notes" placeholder="Admin iç notu"></textarea></label>
      </div>
      <div class="ao-actions order-create-actions">
        <button class="ao-btn" name="create_action" value="order_invoice">Sipariş + Fatura Oluştur</button>
        <button class="ao-btn soft" name="create_action" value="invoice_service">Fatura + Ürün Oluştur</button>
        <button class="ao-btn soft" name="create_action" value="service_no_payment">Ödeme Almadan Ürün Oluştur</button>
        <button class="ao-btn soft" name="create_action" value="invoice_only">Sadece Fatura Oluştur</button>
        <button class="ao-btn soft" name="create_action" value="draft">Taslak Sipariş Oluştur</button>
      </div>
    </form>
  </div>
  <div class="ao-card automation-preview-card">
    <h3>Otomasyon Akışı</h3>
    <div class="automation-live-summary">
      <div><span>Müşteri</span><strong data-live="customer">Seçilmedi</strong></div>
      <div><span>Ürün</span><strong data-live="product">Seçilmedi</strong></div>
      <div><span>Domain İşlemi</span><strong data-live="domain_action">Sadece hizmet alanı</strong></div>
      <div><span>Domain / Hizmet Alanı</span><strong data-live="domain">-</strong></div>
      <div><span>Periyot</span><strong data-live="cycle">Aylık</strong></div>
      <div><span>Ara Toplam</span><strong data-live="subtotal">0,00 ₺</strong></div>
      <div><span>İndirim</span><strong data-live="discount">0,00 ₺</strong></div>
      <div><span>Vergi</span><strong data-live="tax_amount">0,00 ₺</strong></div>
      <div class="grand"><span>Genel Toplam</span><strong data-live="grand_total">0,00 ₺</strong></div>
    </div>
    <ul class="automation-flow-list">
      <li>Sipariş kaydı veya taslak oluşturulur.</li>
      <li>Seçime göre fatura hazırlanır.</li>
      <li>Ödeme alınmadan ürün oluşturma seçilirse hizmet beklemede/provision kuyruğuna alınır.</li>
      <li>Domain kayıt/transfer/yenileme/DNS işlemi registrar kuyruğuna yazılır.</li>
      <li>Tüm işlem API ve admin loglarına kaydedilir.</li>
    </ul>
  </div>
</div>
<script>
(function(){
  var form=document.getElementById('aoManualOrderForm'); if(!form) return;
  var cycleLabels={monthly:'Aylık',annually:'Yıllık',biennially:'2 Yıllık',triennially:'3 Yıllık',onetime:'Tek Seferlik'};
  var domainLabels={none:'Sadece hizmet alanı',register:'Domain kayıt',transfer:'Domain transfer',renew:'Domain yenileme',dns:'DNS / Nameserver değişimi',existing:'Mevcut domain kullan'};
  function money(v){return (Number(v)||0).toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2})+' ₺';}
  function setLive(k,v){var el=document.querySelector('[data-live="'+k+'"]'); if(el) el.textContent=v;}
  function update(){
    var customer=form.querySelector('[data-summary="customer"]'); var product=form.querySelector('[data-summary="product"]'); var domain=form.querySelector('[data-summary="domain"]'); var act=form.querySelector('[data-summary="domain_action"]'); var cycle=form.querySelector('[data-summary="cycle"]');
    var price=parseFloat((form.querySelector('[data-summary="price"]')||{}).value||'0'); var disc=parseFloat((form.querySelector('[data-summary="discount"]')||{}).value||'0'); var tax=parseFloat((form.querySelector('[data-summary="tax"]')||{}).value||'0');
    var taxAmount=Math.max(0,(price-disc)*tax/100); var grand=Math.max(0,price-disc+taxAmount);
    setLive('customer', customer && customer.selectedOptions[0] ? customer.selectedOptions[0].textContent : 'Seçilmedi');
    setLive('product', product && product.selectedOptions[0] ? product.selectedOptions[0].textContent : 'Seçilmedi');
    setLive('domain_action', domainLabels[act.value] || act.value);
    setLive('domain', domain.value || '-'); setLive('cycle', cycleLabels[cycle.value] || cycle.value);
    setLive('subtotal', money(price)); setLive('discount', money(disc)); setLive('tax_amount', money(taxAmount)); setLive('grand_total', money(grand));
    document.querySelectorAll('[data-domain-extra]').forEach(function(row){ row.hidden = (row.getAttribute('data-domain-extra').split(/\s+/).indexOf(act.value)===-1); });
  }
  form.addEventListener('input',update); form.addEventListener('change',update); update();
})();
</script>
<style>
.admin-order-create-v2410 .automation-live-summary{display:grid;gap:10px}.admin-order-create-v2410 .automation-live-summary>div{display:flex;justify-content:space-between;gap:14px;padding:12px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc}.admin-order-create-v2410 .automation-live-summary span{color:#64748b;font-weight:800}.admin-order-create-v2410 .automation-live-summary strong{text-align:right;color:#0f172a}.admin-order-create-v2410 .automation-live-summary .grand{background:#eff6ff;border-color:#bfdbfe;font-size:17px}.order-create-actions{display:flex;flex-wrap:wrap;gap:10px}.automation-flow-list{margin-top:18px;line-height:1.8;color:#334155}
</style>
