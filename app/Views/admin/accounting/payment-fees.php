<?php $flash=get_flash(); ao_schema_ensure_v900(); $rows=[]; try{$rows=db()->query('SELECT * FROM payment_fee_rules ORDER BY gateway')->fetchAll();}catch(Throwable $e){} ?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><h2>Kart İşlem Komisyonları</h2><p>Komisyon artık paylaşılmaz veya firma üstlenmez; kredi kartı/sanal POS ödemelerinde müşterinin sepetine ve faturasına ayrı kalem olarak eklenir.</p></div><a class="ao-btn soft" href="<?= url('admin/domain-center/smart-pricing') ?>">Domain Fiyat Motoru</a></div>
<div class="ao-card">
  <form class="ao-form" method="post" action="<?= url('admin/accounting/payment-fee-save') ?>"><?= csrf_field() ?>
    <div class="ao-form-grid">
      <label>Gateway<input name="gateway" value="paytr"></label>
      <label>Etiket<input name="label" value="PayTR Kredi Kartı"></label>
      <label>Fatura Kalem Adı<input name="invoice_line_label" value="Kart İşlem Komisyonu"></label>
      <label>Komisyon %<input name="fee_percent" type="number" step="0.001" value="2.99"></label>
      <label>Sabit Komisyon<input name="fee_fixed" type="number" step="0.0001" value="0"></label>
      <label>Para Birimi<input name="currency" value="TRY"></label>
      <label>API'den Otomatik Çek<select name="api_enabled"><option value="0">Kapalı / Manuel</option><option value="1">Açık</option></select></label>
      <label>Komisyon API Endpoint<input name="api_endpoint" placeholder="Sağlayıcı komisyon endpoint'i varsa"></label>
      <label>API Auth JSON<textarea name="api_auth_json" rows="2" placeholder='{"bearer":"..."}'></textarea></label>
    </div>
    <button class="ao-btn">Kaydet</button>
  </form>
</div>
<div class="ao-card"><table class="ao-table"><thead><tr><th>Gateway</th><th>Etiket</th><th>Fatura Kalemi</th><th>%</th><th>Sabit</th><th>Kaynak</th><th>Son Sync</th><th>İşlem</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r['gateway']) ?></td><td><?= e($r['label']) ?></td><td><?= e($r['invoice_line_label'] ?? 'Kart İşlem Komisyonu') ?></td><td><?= e($r['fee_percent']) ?></td><td><?= e($r['fee_fixed'].' '.$r['currency']) ?></td><td><?= !empty($r['api_enabled'])?'API':'Manuel/Son Bilinen' ?></td><td><?= e(($r['last_sync_status']??'-').' '.($r['last_synced_at']??'')) ?></td><td><?php if(!empty($r['api_enabled'])): ?><form method="post" action="<?= url('admin/accounting/payment-fee-sync') ?>" style="display:inline"><?= csrf_field() ?><input type="hidden" name="gateway" value="<?= e($r['gateway']) ?>"><button class="ao-btn small">API Senkronize Et</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>

<div class="ao-card">
  <h3>Shopier Ödeme Yöntemi</h3>
  <p>Shopier, müşteri bakiye yükleme ve kredi kartı ödemeleri için kullanılabilir. Test modunda güvenli yerel onay ekranı açılır; canlı modda API bilgileri tamamlanınca gerçek ödeme akışına bağlanır.</p>
  <form class="ao-form" method="post" action="<?= url('admin/accounting/shopier-save') ?>"><?= csrf_field() ?>
    <div class="ao-form-grid">
      <label>Shopier Yetkilendirme<select name="auth_mode"><option value="pat" <?= (function_exists('ao_shopier_setting') && ao_shopier_setting('auth_mode','pat')==='pat')?'selected':'' ?>>PAT / Kişisel Erişim Anahtarı</option><option value="legacy" <?= (function_exists('ao_shopier_setting') && ao_shopier_setting('auth_mode','pat')==='legacy')?'selected':'' ?>>Legacy API Key + Secret</option></select></label>
      <label class="full">PAT / Kişisel Erişim Anahtarı<textarea name="pat" rows="5" placeholder="Shopier PAT anahtarını buraya yapıştırın"><?= e(function_exists('ao_shopier_setting') ? ao_shopier_setting('pat') : '') ?></textarea><span class="field-help">Yeni Shopier panelinde görünen uzun Kişisel Erişim Anahtarıdır.</span></label>
      <label>Legacy API Key<input name="api_key" value="<?= e(function_exists('ao_shopier_setting') ? ao_shopier_setting('api_key') : '') ?>"></label>
      <label>Legacy API Secret<input name="api_secret" value="<?= e(function_exists('ao_shopier_setting') ? ao_shopier_setting('api_secret') : '') ?>"></label>
      <label>Website Index<input name="website_index" value="<?= e(function_exists('ao_shopier_setting') ? ao_shopier_setting('website_index','1') : '1') ?>"></label>
      <label>Callback Secret<input name="callback_secret" value="<?= e(function_exists('ao_shopier_setting') ? ao_shopier_setting('callback_secret') : '') ?>"></label>
      <label>Test Modu<select name="test_mode"><option value="1" <?= (function_exists('ao_shopier_setting') && ao_shopier_setting('test_mode','1')==='1')?'selected':'' ?>>Açık</option><option value="0" <?= (function_exists('ao_shopier_setting') && ao_shopier_setting('test_mode','1')==='0')?'selected':'' ?>>Kapalı / Canlı</option></select></label>
    </div>
    <button class="ao-btn">Shopier Ayarlarını Kaydet</button>
  </form>
</div>

<div class="ao-card"><h3>Fatura Davranışı</h3><p>Örnek: Ürün 100 TL, gateway komisyonu %3 ise fatura kalemleri <strong>Ürün 100 TL</strong> + <strong>Kart İşlem Komisyonu 3 TL</strong> şeklinde oluşur. KDV toplam ara tutar üzerinden hesaplanır.</p></div>
