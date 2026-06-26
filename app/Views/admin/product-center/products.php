<?php
$flash = get_flash();
$products = [];
$groups = [];
$serverGroups = [];
$editProduct = null;
$editId = (int)($_GET['edit'] ?? 0);
$pricing = [];
$revisions = [];
try {
    if(function_exists('ao_v237_ensure_product_pricing_schema')) ao_v237_ensure_product_pricing_schema();
    if(function_exists('ao_v2332_ensure_schema')) ao_v2332_ensure_schema();
    $products = db()->query("SELECT p.*, g.name group_name FROM products p LEFT JOIN product_groups g ON g.id=p.group_id ORDER BY p.id DESC")->fetchAll();
    $groups = db()->query('SELECT * FROM product_groups ORDER BY name')->fetchAll();
    try { $serverGroups = db()->query('SELECT * FROM server_groups ORDER BY name')->fetchAll(); } catch(Throwable $e) {}
    if ($editId > 0) {
        $st = db()->prepare('SELECT * FROM products WHERE id=? LIMIT 1');
        $st->execute([$editId]);
        $editProduct = $st->fetch() ?: null;
        $ps = db()->prepare('SELECT * FROM product_pricing WHERE product_id=?');
        $ps->execute([$editId]);
        foreach(($ps->fetchAll() ?: []) as $pr){ $pricing[$pr['cycle']] = $pr; }
        try { $rv=db()->prepare('SELECT * FROM product_revision_logs WHERE product_id=? ORDER BY id DESC LIMIT 8'); $rv->execute([$editId]); $revisions=$rv->fetchAll() ?: []; } catch(Throwable $e) {}
    }
} catch (Throwable $e) {}

$isEdit = is_array($editProduct);
$isAdd = (($_GET['action'] ?? '') === 'add');
$showProductForm = $isEdit || $isAdd;
$formTitle = $isEdit ? 'Ürünü Düzenle' : 'Yeni Ürün';
$formDesc = $isEdit ? 'Seçili ürünü Ahost One mantığında sekmelerle düzenleyin.' : 'Yeni ürün, hosting paketi, kaynak kod veya dijital hizmet oluşturun.';
function product_selected($a, $b){ return (string)$a === (string)$b ? 'selected' : ''; }
function product_checked($v){ return !empty($v) ? 'checked' : ''; }
function price_dual_value($pricing, $cycle, $field){ if(!isset($pricing[$cycle])) return ''; return number_format((float)($pricing[$cycle][$field] ?? 0), 2, '.', ''); }
function price_active_checked($pricing, $cycle){ return !empty($pricing[$cycle]['is_active']) ? 'checked' : ''; }
$usdRate = function_exists('ao_v237_currency_rate') ? ao_v237_currency_rate('USD') : (function_exists('ao_v23_price_try') ? ao_v23_price_try(1,'USD') : 47.25);
$marginPercent = function_exists('ao_v237_currency_margin') ? ao_v237_currency_margin('USD') : 0;
$cycles = ['one_time'=>'Tek/Aylık','monthly'=>'Aylık','quarterly'=>'3 Aylık','semiannually'=>'6 Aylık','annually'=>'Yıllık','biennially'=>'2 Yıllık','triennially'=>'3 Yıllık'];
$visibility = $editProduct['visibility'] ?? 'visible';
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php /* RC12: direct CSS link removed; single UI CSS is loaded by layout-head. */ ?>
<script defer src="<?= asset('js/admin/product-content-studio.js') ?>?v=24.5.0"></script>

<div class="ao-page-head v237-products-head">
    <div><span class="ao-kicker">Ürün Merkezi</span><h2>Ürünler</h2><p>Ahost One tarzı sekmeli ürün, çift para birimli fiyatlandırma, otomasyon ve görünürlük yönetimi.</p></div>
    <div class="ao-actions"><a class="ao-btn" href="<?= url('admin/product-center/products?action=add') ?>">+ Ürün Ekle</a><a class="ao-btn soft" href="<?= url('admin/product-center/groups?action=add') ?>">+ Ürün Grubu Ekle</a><?php if($showProductForm): ?><a class="ao-btn ghost" href="<?= url('admin/product-center/products') ?>">Listeye Dön</a><?php endif; ?></div>
</div>

<?php if($showProductForm): ?>
<div class="ao-card v237-product-card">
    <div class="ao-card-head v237-card-head"><div><span><?= $isEdit ? 'Düzenleme Modu' : 'Yeni Kayıt' ?></span><h3><?= e($formTitle) ?></h3><p><?= e($formDesc) ?></p></div><?php if($isEdit): ?><span class="ao-badge">ID #<?= (int)$editProduct['id'] ?></span><?php endif; ?></div>
    <?php if($isEdit): ?><div class="v2332-product-actions"><a class="ao-btn soft" href="<?= url('admin/product-center/product-clone?id='.(int)$editProduct['id'].'&csrf_token='.csrf_token()) ?>">🧬 Ürünü Klonla</a><a class="ao-btn soft" href="#tab-gecmis" data-tab-go="gecmis">🕘 Geçmiş</a></div><?php endif; ?>
    <form class="ao-form v237-product-form" method="post" action="<?= url('admin/product-center/product-save') ?>">
        <?= csrf_field() ?>
        <?php if($isEdit): ?><input type="hidden" name="id" value="<?= (int)$editProduct['id'] ?>"><?php endif; ?>
        <div class="v2332-tabs" role="tablist">
          <button type="button" class="active" data-tab="genel">Genel Bilgiler</button>
          <button type="button" data-tab="icerik">İçerik / HTML Editör</button>
          <button type="button" data-tab="fiyat">Fiyatlandırma</button>
          <button type="button" data-tab="otomasyon">Modül / Otomasyon</button>
          <button type="button" data-tab="limit">Stok ve Limitler</button>
          <button type="button" data-tab="domain">Domain Ayarları</button>
          <button type="button" data-tab="seo">SEO ve Görünürlük</button>
          <button type="button" data-tab="gelismis">Gelişmiş</button>
          <?php if($isEdit): ?><button type="button" data-tab="gecmis">Geçmiş</button><?php endif; ?>
        </div>

        <div class="v2332-pane active" id="tab-genel">
          <div class="v237-section-title"><strong>Genel Bilgiler</strong><small>Ürün adı, grup, tip ve açıklama</small></div>
          <div class="ao-form-grid v237-grid">
            <label>Grup<select name="group_id"><?php foreach($groups as $g): ?><option value="<?= (int)$g['id'] ?>" <?= product_selected($editProduct['group_id'] ?? '', $g['id']) ?>><?= e($g['name']) ?></option><?php endforeach; ?></select></label>
            <label>Ürün Adı<input name="name" required value="<?= e($editProduct['name'] ?? '') ?>" placeholder="Linux Hosting Başlangıç"></label>
            <label>Slug<input name="slug" value="<?= e($editProduct['slug'] ?? '') ?>" placeholder="linux-hosting-baslangic"></label>
            <label>Tip<?php $type=$editProduct['type'] ?? 'service'; ?><select name="type"><option value="hosting" <?= product_selected($type,'hosting') ?>>Hosting</option><option value="domain" <?= product_selected($type,'domain') ?>>Domain</option><option value="server" <?= product_selected($type,'server') ?>>VPS/Sunucu</option><option value="service" <?= product_selected($type,'service') ?>>Dijital Hizmet</option><option value="marketplace" <?= product_selected($type,'marketplace') ?>>Marketplace / Kaynak Kod</option><option value="ssl" <?= product_selected($type,'ssl') ?>>SSL</option></select></label>
            <label class="full">Kısa Açıklama<textarea name="short_description" rows="3" maxlength="500" placeholder="Ürün kartlarında görünecek kısa satış metni."><?= e($editProduct['short_description'] ?? ao_v2400_plain_from_html($editProduct['description'] ?? '', 180)) ?></textarea><small>Listeleme, ürün kartları ve SEO özetlerinde kullanılır. Detaylı HTML içerik bir sonraki İçerik sekmesindedir.</small></label>
          </div>
        </div>

        <div class="v2332-pane" id="tab-icerik">
          <div class="v237-section-title"><strong>Product Content Studio</strong><small>WordPress kalitesinde görsel editör + güvenli HTML kaynak modu</small></div>
          <div class="ao-content-studio" data-content-studio>
            <div class="ao-editor-toolbar" aria-label="Ürün HTML editör araçları">
              <button type="button" data-cmd="formatBlock" data-value="h2">Başlık</button>
              <button type="button" data-cmd="bold"><b>B</b></button>
              <button type="button" data-cmd="italic"><i>I</i></button>
              <button type="button" data-cmd="underline"><u>U</u></button>
              <button type="button" data-cmd="insertUnorderedList">Liste</button>
              <button type="button" data-cmd="insertOrderedList">Numara</button>
              <button type="button" data-action="link">Link</button>
              <button type="button" data-action="image">Görsel</button>
              <button type="button" data-block="button">Buton</button>
              <button type="button" data-block="features">Özellik Bloğu</button>
              <button type="button" data-block="faq">SSS</button>
              <button type="button" data-block="table">Tablo</button>
              <button type="button" data-block="notice">Uyarı</button>
              <button type="button" data-block="media">Medya Bloğu</button>
              <button type="button" data-block="pricing">Satış Kutusu</button>
              <button type="button" data-mode="visual" class="active">Görsel</button>
              <button type="button" data-mode="html">HTML</button>
            </div>
            <div class="ao-editor-help">İzinli HTML: başlık, paragraf, liste, tablo, link, görsel, kod ve ürün blokları. Script, iframe ve event kodları kayıtta temizlenir.</div>
            <div class="ao-editor-visual" contenteditable="true" data-editor-visual><?= $editProduct ? ao_v2400_sanitize_product_html($editProduct['description'] ?? '') : '<h2>Ürün Başlığı</h2><p>Ürünün öne çıkan faydasını buraya yazın.</p><ul><li>Ücretsiz SSL</li><li>Profesyonel destek</li><li>Hızlı teslimat</li></ul>' ?></div>
            <textarea class="ao-editor-html" data-editor-html name="description" rows="14"><?= e($editProduct ? ao_v2400_sanitize_product_html($editProduct['description'] ?? '') : '<h2>Ürün Başlığı</h2><p>Ürünün öne çıkan faydasını buraya yazın.</p><ul><li>Ücretsiz SSL</li><li>Profesyonel destek</li><li>Hızlı teslimat</li></ul>') ?></textarea>
            <div class="ao-editor-blocks">
              <span>Hazır bloklar:</span>
              <button type="button" data-block="hero">Satış Hero</button>
              <button type="button" data-block="compare">Karşılaştırma</button>
              <button type="button" data-block="steps">Teslimat Adımları</button>
            </div>
          </div>
        </div>

        <div class="v2332-pane" id="tab-fiyat">
          <div class="v237-section-title"><strong>Fiyatlandırma</strong><small>USD ana fiyat; TRY otomatik hesaplanır. Fiyat tablosu sadece bu ürün formunda görünür.</small></div>
          <div class="ao-alert info" style="margin-bottom:14px">Aktif satış kuru: <strong>1 USD = <?= e(number_format((float)$usdRate,2,',','.')) ?> TL</strong><?php if($marginPercent): ?> · kar marjı: %<?= e(number_format((float)$marginPercent,2,',','.')) ?><?php endif; ?>. USD değişmeden kalır; kur değişince TRY otomatik güncellenir.</div>
          <div class="v233-pricing-compact" data-usd-rate="<?= e(number_format((float)$usdRate,6,'.','')) ?>">
            <div class="v233-paytype compact">
              <span>Ödeme Türü</span>
              <label><input type="radio" name="payment_type" value="free"><b>Ücretsiz</b></label>
              <label><input type="radio" name="payment_type" value="one_time"><b>Tek Seferlik</b></label>
              <label><input type="radio" name="payment_type" value="recurring" checked><b>Yinelenen</b></label>
            </div>
            <div class="v233-rate-mini">1 USD = <?= e(number_format((float)$usdRate,2,',','.')) ?> TL<?php if($marginPercent): ?> · Kar marjı %<?= e(number_format((float)$marginPercent,2,',','.')) ?><?php endif; ?></div>
            <table class="v233-pricing-table compact">
              <thead><tr><th>Periyot</th><th>USD Kurulum</th><th>USD Fiyat</th><th>TRY Kurulum</th><th>TRY Fiyat</th><th>Aktif</th></tr></thead>
              <tbody>
              <?php foreach($cycles as $cycle=>$label): ?>
                <tr>
                  <th><?= e($label) ?></th>
                  <td><input class="js-price-usd" data-cycle="<?= e($cycle) ?>" data-kind="setup" name="setup_usd[<?= e($cycle) ?>]" value="<?= e(price_dual_value($pricing,$cycle,'setup_fee_usd')) ?>" inputmode="decimal"></td>
                  <td><input class="js-price-usd" data-cycle="<?= e($cycle) ?>" data-kind="price" name="price_usd[<?= e($cycle) ?>]" value="<?= e(price_dual_value($pricing,$cycle,'price_usd')) ?>" inputmode="decimal"></td>
                  <td><input class="js-price-try" data-cycle="<?= e($cycle) ?>" data-kind="setup" name="setup_try[<?= e($cycle) ?>]" value="<?= e(price_dual_value($pricing,$cycle,'setup_fee_try')) ?>" inputmode="decimal"></td>
                  <td><input class="js-price-try" data-cycle="<?= e($cycle) ?>" data-kind="price" name="price_try[<?= e($cycle) ?>]" value="<?= e(price_dual_value($pricing,$cycle,'price_try')) ?>" inputmode="decimal"></td>
                  <td class="center"><input type="checkbox" name="price_active[USD][<?= e($cycle) ?>]" value="1" <?= price_active_checked($pricing,$cycle) ?>></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="v2332-pane" id="tab-otomasyon">
          <div class="v237-section-title"><strong>Modül / Otomasyon</strong><small>Panel modülü, sunucu grubu ve otomatik aktivasyon</small></div>
          <div class="ao-form-grid v237-grid">
            <label>Modül<?php $module=$editProduct['module_name'] ?? 'manual'; ?><select name="module_name"><option value="whm" <?= product_selected($module,'whm') ?>>WHM/cPanel</option><option value="cpanel" <?= product_selected($module,'cpanel') ?>>cPanel</option><option value="directadmin" <?= product_selected($module,'directadmin') ?>>DirectAdmin</option><option value="plesk" <?= product_selected($module,'plesk') ?>>Plesk</option><option value="registrar" <?= product_selected($module,'registrar') ?>>Registrar</option><option value="shopier" <?= product_selected($module,'shopier') ?>>Shopier</option><option value="marketplace" <?= product_selected($module,'marketplace') ?>>Marketplace</option><option value="manual" <?= product_selected($module,'manual') ?>>Manuel</option></select></label>
            <label>Sunucu Grubu<select name="server_group_id"><option value="0">Seçilmedi / Manuel</option><?php foreach($serverGroups as $sg): ?><option value="<?= (int)$sg['id'] ?>" <?= product_selected($editProduct['server_group_id'] ?? '', $sg['id']) ?>><?= e($sg['name']) ?> · <?= e($sg['strategy']) ?></option><?php endforeach; ?></select></label>
            <label>WHM / Panel Paket Adı<input name="whm_package" placeholder="starter" value="<?= e($editProduct['whm_package'] ?? '') ?>"></label>
            <label>Provision Modu<select name="provision_mode"><option value="manual">Manuel Onay</option><option value="paid">Ödeme sonrası otomatik</option><option value="order">Sipariş sonrası otomatik</option></select></label>
          </div>
        </div>

        <div class="v2332-pane" id="tab-limit"><div class="v237-section-title"><strong>Stok ve Limitler</strong><small>v23.3.2 altyapısı hazır; stok alanları sonraki küçük sürümde gerçek tabloya bağlanabilir.</small></div><div class="ao-form-grid v237-grid"><label>Sıralama<input name="sort_order" value="<?= e($editProduct['sort_order'] ?? 0) ?>" inputmode="numeric"></label><label class="v237-switch full"><input type="checkbox" name="is_custom_build_enabled" value="1" <?= product_checked($editProduct['is_custom_build_enabled'] ?? 0) ?>><span></span> Müşteri özel paket oluşturabilsin</label></div></div>
        <div class="v2332-pane" id="tab-domain"><div class="v237-section-title"><strong>Domain Ayarları</strong><small>Domain gereksinimi, ücretsiz domain ve uzantı kuralları için ayrılmış sekme.</small></div><div class="ao-alert info">Domain bağlantılı ürünlerde bu sekme ürün/domain paket eşleştirmesi için kullanılacak. v23.3.3’te kompakt arayüz yerleşimi eklendi.</div></div>
        <div class="v2332-pane" id="tab-seo"><div class="v237-section-title"><strong>SEO ve Görünürlük</strong><small>Ön yüzde ürün görünürlüğü ve meta alanları</small></div><div class="ao-form-grid v237-grid"><label>Görünürlük<select name="visibility"><option value="visible" <?= product_selected($visibility,'visible') ?>>Ön yüzde göster</option><option value="hidden" <?= product_selected($visibility,'hidden') ?>>Gizli</option><option value="private" <?= product_selected($visibility,'private') ?>>Sadece direkt link</option></select></label><label>SEO Başlık<input name="seo_title" value="<?= e($editProduct['seo_title'] ?? '') ?>"></label><label class="full">Meta Açıklama<textarea name="meta_description" rows="3"><?= e($editProduct['meta_description'] ?? '') ?></textarea></label></div></div>
        <div class="v2332-pane" id="tab-gelismis"><div class="v237-section-title"><strong>Gelişmiş Ayarlar</strong><small>Hook/Event, ürün şablonları ve bağımlılıklar için ayrılmış alan.</small></div><div class="ao-alert info">Ürün kaydedildiğinde revizyon geçmişi ve aktivite logu oluşturulur. Böylece yanlış değişikliklerde eski ayarlar görülebilir.</div></div>
        <?php if($isEdit): ?><div class="v2332-pane" id="tab-gecmis"><div class="v237-section-title"><strong>Ürün Revizyon Geçmişi</strong><small>Son değişiklikler</small></div><?php if($revisions): ?><div class="v2332-history"><?php foreach($revisions as $r): ?><div><strong><?= e($r['action']) ?></strong><small><?= e($r['created_at']) ?></small><p><?= e($r['note'] ?? '') ?></p></div><?php endforeach; ?></div><?php else: ?><p class="ao-muted">Henüz revizyon kaydı yok.</p><?php endif; ?></div><?php endif; ?>

        <div class="v237-form-actions"><button class="ao-btn"><?= $isEdit ? 'Değişiklikleri Kaydet' : 'Ürünü Kaydet' ?></button><?php if($isEdit): ?><a class="ao-btn soft" href="<?= url('admin/product-center/products') ?>">Vazgeç</a><?php endif; ?></div>
    </form>
</div>
<?php endif; ?>

<div class="ao-card ao-v2480-site-suggestions">
  <div class="ao-card-head v237-card-head"><div><span>Site / Sepet / Müşteri Paneli Kontrol Notu</span><h3>Ürün fiyatı değişince nerelere yansımalı?</h3><p>Admin listesindeki hızlı/toplu fiyat düzeltme sadece admin ekranında kalmamalı; vitrin, sepet, yenileme ve fatura tarafı aynı güncel fiyat seçiciyi kullanmalı.</p></div><span class="ao-badge info">v24.8.0</span></div>
  <div class="ao-v2480-suggestion-grid">
    <div><strong>Sitede</strong><small>Paket kartı, ürün detay fiyatı, karşılaştırma tablosu ve kampanyalı/eski fiyat görünümü güncellenmeli.</small></div>
    <div><strong>Sepette</strong><small>Eski fiyat cache kalmamalı; kupon, vergi, kur ve periyot toplamı güncel fiyatla yeniden hesaplanmalı.</small></div>
    <div><strong>Müşteri Panelinde</strong><small>Hizmet kartı ve yenileme fiyatı, aktif periyot fiyatına göre doğru görünmeli.</small></div>
    <div><strong>Faturada</strong><small>Yeni fatura oluşturulurken güncel ürün fiyatı alınmalı; geçmiş faturalar değiştirilmemeli.</small></div>
  </div>
</div>

<div class="ao-card ao-v2480-bulk-card">
  <div class="ao-card-head v237-card-head"><div><span>Hızlı Yönetim</span><h3>Toplu Fiyat Güncelle</h3><p>Ürün detayına girmeden seçili ürünlere zam, indirim, sabit fiyat veya kurdan yenileme uygula.</p></div><button type="button" class="ao-btn" data-v2480-open-bulk>Toplu Fiyat Güncelle</button></div>
  <form class="ao-form ao-v2480-bulk-form" method="post" action="<?= url('admin/product-center/bulk-price-update') ?>" data-v2480-bulk-form hidden>
    <?= csrf_field() ?>
    <div class="ao-form-grid v237-grid">
      <label>Ürün Grubu ile Uygula<select name="bulk_group_id"><option value="0">Sadece listeden seçilen ürünler</option><?php foreach($groups as $g): ?><option value="<?= (int)$g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?></select><small>Ürün seçmezsen grup seçerek toplu uygulayabilirsin.</small></label>
      <label>Periyot<select name="bulk_cycle"><option value="one_time">Tek seferlik</option><option value="monthly" selected>Aylık</option><option value="quarterly">3 Aylık</option><option value="semiannually">6 Aylık</option><option value="annually">Yıllık</option><option value="biennially">2 Yıllık</option><option value="triennially">3 Yıllık</option></select></label>
      <label>İşlem<select name="bulk_mode"><option value="percent_increase">% Zam</option><option value="percent_decrease">% İndirim</option><option value="add_try">TL Tutar Ekle</option><option value="add_usd">USD Tutar Ekle</option><option value="fixed_try">Sabit TL Fiyat Ata</option><option value="fixed_usd">Sabit USD Fiyat Ata</option><option value="refresh_usd_rate">USD bazlı fiyatları güncel kurdan TL'ye çevir</option></select></label>
      <label>Değer<input name="bulk_value" inputmode="decimal" placeholder="10 veya 99.90"><small>% işlemlerde yüzde, sabit/ekleme işlemlerinde tutar girilir.</small></label>
    </div>
    <div class="ao-v2480-preview"><strong>Önizleme:</strong> Seçili ürünler aşağıdaki listeden alınır. Grup seçilirse listedeki seçim olmadan da uygulanır. İşlem geçmişi loglanır.</div>
    <div class="v237-form-actions"><button class="ao-btn" onclick="return confirm('Seçili/gruptaki ürün fiyatları güncellensin mi?')">Toplu Güncellemeyi Uygula</button><button type="button" class="ao-btn soft" data-v2480-close-bulk>Kapat</button></div>
  </form>
</div>

<div class="ao-card v237-product-list-card"><div class="ao-card-head v237-card-head"><div><span>Katalog</span><h3>Ürün Listesi</h3><p>Liste ekranında büyük fiyat tablosuna girmeden fiyatı hızlı düzeltin veya seçili ürünlere toplu işlem uygulayın.</p></div><span class="ao-badge"><?= count($products) ?> ürün</span></div>
<div class="admin-products-search">
  <input type="search" id="adminProductQuickSearch" placeholder="Ürün adı, grup, tip, modül, fiyat veya durum ara...">
  <span class="hint">Anlık filtreleme</span>
</div>
<div class="v237-table-wrap"><table class="ao-table v237-products-table" id="adminProductsTable"><thead><tr><th><input type="checkbox" data-v2480-check-all title="Tümünü seç"></th><th>Ürün</th><th>Grup</th><th>Tip</th><th>Modül</th><th>Hızlı Fiyat</th><th>Durum</th><th>İşlem</th></tr></thead><tbody><?php foreach($products as $p): ?><?php $mp=function_exists('ao_v2331_product_display_price')?ao_v2331_product_display_price((int)$p['id']):['try'=>(float)($p['price']??0),'usd'=>0,'cycle'=>'legacy']; $cycle=!empty($mp['cycle'])&&$mp['cycle']!=='none'&&$mp['cycle']!=='legacy'?$mp['cycle']:'monthly'; ?><tr data-search="<?= e(mb_strtolower(($p['name']??'').' '.($p['slug']??'').' '.($p['group_name']??'').' '.($p['type']??'').' '.($p['module_name']??'').' '.(!empty($p['is_active'])?'aktif':'pasif'),'UTF-8')) ?>"><td><input type="checkbox" name="product_ids[]" value="<?= (int)$p['id'] ?>" form="aoDetachedBulkCollector" data-v2480-row-check></td><td><strong><?= e($p['name']) ?></strong><br><small><?= e($p['slug']) ?></small></td><td><?= e($p['group_name']) ?></td><td><?= e($p['type']) ?></td><td><?= e($p['module_name']) ?></td><td><form class="ao-v2480-quick-price" method="post" action="<?= url('admin/product-center/quick-price-update') ?>"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><select name="cycle" title="Periyot"><option value="one_time" <?= product_selected($cycle,'one_time') ?>>Tek</option><option value="monthly" <?= product_selected($cycle,'monthly') ?>>Aylık</option><option value="quarterly" <?= product_selected($cycle,'quarterly') ?>>3 Ay</option><option value="semiannually" <?= product_selected($cycle,'semiannually') ?>>6 Ay</option><option value="annually" <?= product_selected($cycle,'annually') ?>>Yıllık</option><option value="biennially" <?= product_selected($cycle,'biennially') ?>>2 Yıl</option><option value="triennially" <?= product_selected($cycle,'triennially') ?>>3 Yıl</option></select><input name="price_try" inputmode="decimal" value="<?= number_format((float)($mp['try']??0),2,'.','') ?>" title="TL fiyat"><input name="price_usd" inputmode="decimal" value="<?= number_format((float)($mp['usd']??0),2,'.','') ?>" title="USD fiyat"><label class="ao-v2480-active"><input type="checkbox" name="is_active" value="1" <?= !empty($mp['active'])?'checked':'checked' ?>> Aktif</label><button class="ao-mini-btn">Kaydet</button><small>TL / USD · <?= e($cycle) ?></small></form></td><td><span class="ao-badge <?= !empty($p['is_active'])?'success':'muted' ?>"><?= !empty($p['is_active'])?'Aktif':'Pasif' ?></span></td><td><div class="v237-row-actions"><a class="ao-mini-btn" href="<?= url('admin/product-center/products?edit='.(int)$p['id']) ?>">✏️ Düzenle</a><a class="ao-mini-btn" href="<?= url('admin/product-center/product-clone?id='.(int)$p['id'].'&csrf_token='.csrf_token()) ?>">🧬 Klonla</a><a class="ao-mini-btn" href="<?= url('admin/product-center/product-toggle?id='.(int)$p['id'].'&csrf_token='.csrf_token()) ?>"><?= !empty($p['is_active'])?'Pasife Al':'Aktif Et' ?></a><a class="ao-mini-btn danger" onclick="return confirm('Ürün kalıcı silinsin mi?')" href="<?= url('admin/product-center/product-delete?id='.(int)$p['id'].'&csrf_token='.csrf_token()) ?>">🗑️ Sil</a></div></td></tr><?php endforeach; ?></tbody></table></div></div>
<form id="aoDetachedBulkCollector" class="ao-v2480-hidden-bulk"></form>

<script>
document.addEventListener('click',function(e){const b=e.target.closest('[data-tab],[data-tab-go]');if(!b)return;const tab=b.dataset.tab||b.dataset.tabGo;document.querySelectorAll('.v2332-tabs button').forEach(x=>x.classList.toggle('active',x.dataset.tab===tab));document.querySelectorAll('.v2332-pane').forEach(x=>x.classList.toggle('active',x.id==='tab-'+tab));});
document.addEventListener('input',function(e){const box=document.querySelector('.v233-pricing-compact,.v233-pricing-table-wrap');if(!box)return;const rate=parseFloat(box.dataset.usdRate||'0')||0;const el=e.target;if(!el.matches('.js-price-usd,.js-price-try'))return;const cycle=el.dataset.cycle,kind=el.dataset.kind;const val=parseFloat(String(el.value||'0').replace(',','.'))||0;if(el.classList.contains('js-price-usd')){const t=document.querySelector('.js-price-try[data-cycle="'+cycle+'"][data-kind="'+kind+'"]');if(t&&rate>0)t.value=(val*rate).toFixed(2);}else{const u=document.querySelector('.js-price-usd[data-cycle="'+cycle+'"][data-kind="'+kind+'"]');if(u&&rate>0)u.value=(val/rate).toFixed(2);}});
</script>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
<script id="aoProductSearchV2468">
(function(){
 var input=document.getElementById('adminProductQuickSearch');
 var table=document.getElementById('adminProductsTable');
 if(!input||!table) return;
 input.addEventListener('input', function(){
   var q=(input.value||'').toLocaleLowerCase('tr-TR').trim();
   table.querySelectorAll('tbody tr').forEach(function(tr){
     var hay=(tr.getAttribute('data-search')||tr.textContent||'').toLocaleLowerCase('tr-TR');
     tr.style.display = !q || hay.indexOf(q)>-1 ? '' : 'none';
   });
 });
})();
</script>


<script id="aoProductQuickPriceV2480">
(function(){
  var open=document.querySelector('[data-v2480-open-bulk]'), close=document.querySelector('[data-v2480-close-bulk]'), form=document.querySelector('[data-v2480-bulk-form]');
  if(open&&form){open.addEventListener('click',function(){form.hidden=!form.hidden;});}
  if(close&&form){close.addEventListener('click',function(){form.hidden=true;});}
  var all=document.querySelector('[data-v2480-check-all]');
  if(all){all.addEventListener('change',function(){document.querySelectorAll('[data-v2480-row-check]').forEach(function(c){c.checked=all.checked;});});}
  if(form){form.addEventListener('submit',function(){form.querySelectorAll('input[data-v2480-cloned]').forEach(function(x){x.remove();});document.querySelectorAll('[data-v2480-row-check]:checked').forEach(function(c){var h=document.createElement('input');h.type='hidden';h.name='product_ids[]';h.value=c.value;h.setAttribute('data-v2480-cloned','1');form.appendChild(h);});});}
})();
</script>
