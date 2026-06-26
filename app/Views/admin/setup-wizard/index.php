<?php
$steps = $steps ?? [];
$progress = (int)($progress ?? 0);
$moduleRows = [];
try { $moduleRows = db()->query("SELECT * FROM module_visibility ORDER BY category,title")->fetchAll(); } catch(Throwable $e) {}
function wizard_setting($key,$default=''){ return admin_setting($key,$default); }
$orderedCategories = ['Başlangıç','Site Bilgileri','Tema Seçimi','SMTP','Domain','Hosting','Ürün','Ödeme','AI','Builder','Lisans','Marketplace','Güvenlik','Sistem'];
$statusLabels = ['pending'=>'Bekliyor','done'=>'Tamamlandı','skipped'=>'Atlandı'];
$availableThemes = ['default'=>'Default','enterprise'=>'Enterprise SaaS','cloud-pro'=>'Cloud Pro','dark'=>'Dark Pro','corporate'=>'Corporate'];
try { $dbThemes = db()->query("SELECT slug,name FROM themes WHERE is_active=1 ORDER BY name")->fetchAll(); if($dbThemes){ $availableThemes=[]; foreach($dbThemes as $t){ $availableThemes[$t['slug']]=$t['name']; } } } catch(Throwable $e) {}
$doneCount = 0; $missingCount = 0; foreach($steps as $srow){ if(($srow['status'] ?? '') === 'done') $doneCount++; elseif(($srow['required'] ?? 0)) $missingCount++; }
?>
<div class="ao-page-head">
  <div>
    <span class="eyebrow">Ahost One ilk kurulum</span>
    <h2>Kurulum Sihirbazı</h2>
    <p>Bu sihirbaz tamamlandığında sistemin çalışması için gereken temel ayarlar bitmiş olur. Tamamlanan alanlar otomatik işaretlenir.</p>
  </div>
  <div class="ao-progress-ring"><strong><?= $progress ?>%</strong><span>tamamlandı</span></div>
</div>

<form method="post" action="<?= url('admin/setup-wizard/save') ?>" class="wizard-shell" id="aoSetupWizard" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="wizard-topbar">
    <button type="button" class="btn" data-wizard-prev>← Önceki</button>
    <div class="wizard-progress"><div class="wizard-progress-fill" style="width:<?= max(0,min(100,$progress)) ?>%"></div></div>
    <button type="button" class="btn primary" data-wizard-next>Sonraki →</button>
  </div>

  <div class="wizard-tabs">
    <?php foreach($orderedCategories as $i=>$cat): ?>
      <button type="button" class="wizard-tab <?= $i===0?'active':'' ?>" data-step="<?= $i ?>"><?= e($cat) ?></button>
    <?php endforeach; ?>
  </div>

  <?php $stepIndex=0; ?>
  <section class="wizard-step-panel active" data-panel="<?= $stepIndex++ ?>">
    <div class="ao-card premium"><h3>1. Başlangıç</h3><p class="muted">Temel kurulum tamamlandı. Bu sihirbazda site bilgileri, logo, tema, SMTP, registrar, sunucu ve sistem kontrollerini adım adım tamamlayın.</p><div class="setup-summary"><strong>Not:</strong> Logo, tema ve SMTP ayarları artık install.php içinde değil; bu Kurulum Sihirbazı içinde yönetilir.</div></div>
  </section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>">
    <div class="ao-card premium"><h3>2. Site Bilgileri ve Logo</h3><p class="muted">Logo, site adı, firma bilgileri, varsayılan dil/para birimi ve temel SEO bilgileri burada tamamlanır.</p>
      <div class="wizard-grid two">
        <label>Site Adı<input name="settings[site_name]" value="<?= e(wizard_setting('site_name','Ahost One')) ?>"></label>
        <label>Site URL<input name="settings[site_url]" value="<?= e(wizard_setting('site_url',url(''))) ?>"></label>
        <label>Logo Yükle<input type="file" name="logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"><span class="field-help">Link yerine dosya yükleyin; logo menüye göre otomatik boyutlanır.</span></label>
        <label>Mevcut Logo URL<input name="settings[logo_url]" value="<?= e(wizard_setting('logo_url','')) ?>" placeholder="Yükleme yapılmazsa bu değer kullanılır"></label>
        <label>Favicon Yükle<input type="file" name="favicon_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"></label>
        <label>Mevcut Favicon URL<input name="settings[favicon_url]" value="<?= e(wizard_setting('favicon_url','')) ?>"></label>
        <label>Firma Adı<input name="settings[company_name]" value="<?= e(wizard_setting('company_name','')) ?>"></label>
        <label>Firma E-posta<input name="settings[company_email]" value="<?= e(wizard_setting('company_email','')) ?>"></label>
        <label>Telefon<input name="settings[company_phone]" value="<?= e(wizard_setting('company_phone','')) ?>"></label>
        <label>Adres<input name="settings[company_address]" value="<?= e(wizard_setting('company_address','')) ?>"></label>
        <label>Varsayılan Para Birimi<input name="settings[default_currency]" value="<?= e(wizard_setting('default_currency','TRY')) ?>"></label>
        <label>Varsayılan Dil<input name="settings[default_language]" value="<?= e(wizard_setting('default_language','tr')) ?>"></label>
        <label>Timezone<input name="settings[timezone]" value="<?= e(wizard_setting('timezone','Europe/Istanbul')) ?>"></label>
        <label>Bakım Modu<select name="settings[maintenance_mode]"><option value="0" <?= wizard_setting('maintenance_mode','0')==='0'?'selected':'' ?>>Kapalı</option><option value="1" <?= wizard_setting('maintenance_mode','0')==='1'?'selected':'' ?>>Açık</option></select></label>
      </div>
    </div>
  </section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>3. Tema Seçimi</h3><p class="muted">Tema ismi yazmak yerine sistemdeki temalardan kart ile seç.</p><div class="theme-select-grid"><?php foreach($availableThemes as $slug=>$name): ?><label class="theme-select-card"><span class="theme-preview-swatch"></span><strong><?= e($name) ?></strong><input type="radio" name="settings[theme_front]" value="<?= e($slug) ?>" <?= wizard_setting('theme_front','default')===$slug?'checked':'' ?>> <em>Site için seç</em></label><?php endforeach; ?></div><div class="wizard-grid two"><label>Admin Teması<select name="settings[theme_admin]"><?php foreach($availableThemes as $slug=>$name): ?><option value="<?= e($slug) ?>" <?= wizard_setting('theme_admin','default')===$slug?'selected':'' ?>><?= e($name) ?></option><?php endforeach; ?></select></label><label>Müşteri Paneli Teması<select name="settings[theme_customer]"><?php foreach($availableThemes as $slug=>$name): ?><option value="<?= e($slug) ?>" <?= wizard_setting('theme_customer','default')===$slug?'selected':'' ?>><?= e($name) ?></option><?php endforeach; ?></select></label></div><a class="btn" href="<?= url('admin/theme-center') ?>">Theme Center’a Git</a></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>4. SMTP, Mail, SMS ve WhatsApp</h3><p class="muted">SMTP, İletiMerkezi ve WhatsApp bildirimleri. Ayarları kaydetmeden önce test maili gönderebilirsin.</p><div class="wizard-grid three"><label>SMTP Host<input name="settings[smtp_host]" value="<?= e(wizard_setting('smtp_host','')) ?>"></label><label>SMTP Port<input name="settings[smtp_port]" value="<?= e(wizard_setting('smtp_port','587')) ?>"></label><label>SMTP Kullanıcı<input name="settings[smtp_username]" value="<?= e(wizard_setting('smtp_username','')) ?>"></label><label>SMTP Şifre<input name="settings[smtp_password]" value="<?= e(wizard_setting('smtp_password','')) ?>"></label><label>Gönderici E-posta<input name="settings[smtp_from]" value="<?= e(wizard_setting('smtp_from','')) ?>"></label><label>Gönderici Adı<input name="settings[smtp_from_name]" value="<?= e(wizard_setting('smtp_from_name','Ahost One')) ?>"></label><label>Test E-posta<input name="test_email" value="<?= e(wizard_setting('company_email', wizard_setting('smtp_from',''))) ?>"></label><label>İletiMerkezi API Key<input name="settings[iletimerkezi_api_key]" value="<?= e(wizard_setting('iletimerkezi_api_key','')) ?>"></label><label>İletiMerkezi Hash<input name="settings[iletimerkezi_api_hash]" value="<?= e(wizard_setting('iletimerkezi_api_hash','')) ?>" placeholder="API Hash / Secret"></label><label>WhatsApp Token<input name="settings[whatsapp_token]" value="<?= e(wizard_setting('whatsapp_token','')) ?>"></label></div><input type="hidden" name="return" value="admin/setup-wizard"><div class="compact-actions"><button class="btn ghost" formaction="<?= url('admin/smtp-test') ?>" formmethod="post">SMTP Test Gönder</button><a class="btn" href="<?= url('admin/notification-center') ?>">Bildirim Merkezine Git</a></div></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>5. Domain ve Registrar</h3><p class="muted">DomainNameAPI ve diğer registrarlar için normal bağlantı alanları.</p><div class="wizard-grid three"><label>Registrar<select name="settings[registrar_provider]"><option value="domainnameapi" <?= wizard_setting('registrar_provider','domainnameapi')==='domainnameapi'?'selected':'' ?>>DomainNameAPI</option><option value="resellerclub" <?= wizard_setting('registrar_provider','domainnameapi')==='resellerclub'?'selected':'' ?>>ResellerClub</option><option value="openprovider" <?= wizard_setting('registrar_provider','domainnameapi')==='openprovider'?'selected':'' ?>>OpenProvider</option></select></label><label>Auth Mode<select name="settings[domainnameapi_auth_mode]"><option value="apikey" <?= wizard_setting('domainnameapi_auth_mode','apikey')==='apikey'?'selected':'' ?>>API Key</option><option value="userpass" <?= wizard_setting('domainnameapi_auth_mode','apikey')==='userpass'?'selected':'' ?>>Kullanıcı Adı + Şifre</option></select></label><label>Test Domain<input name="settings[domainnameapi_test_domain]" value="<?= e(wizard_setting('domainnameapi_test_domain','example.com')) ?>"></label><label>API Key<input name="settings[domainnameapi_api_key]" value="<?= e(wizard_setting('domainnameapi_api_key','')) ?>"></label><label>API Secret<input name="settings[domainnameapi_api_secret]" value="<?= e(wizard_setting('domainnameapi_api_secret','')) ?>"></label><label>Kullanıcı Adı<input name="settings[domainnameapi_username]" value="<?= e(wizard_setting('domainnameapi_username','')) ?>"></label><label>Şifre<input type="password" name="settings[domainnameapi_password]" value="<?= e(wizard_setting('domainnameapi_password','')) ?>"></label></div><div class="compact-actions"><a class="btn" href="<?= url('admin/domain-center/registrars') ?>">Registrar Ayarlarına Git</a><a class="btn ghost" href="<?= url('admin/scan-report') ?>">Bağlantıyı Test Et</a></div></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>6. Sunucu ve Hosting Otomasyonu</h3><p class="muted">Kurulum içinde temel sunucu bilgilerini aç ve test et.</p><details open class="wizard-accordion"><summary>Sunucu bağlantı bilgileri</summary><div class="wizard-grid three"><label>Panel Türü<select name="settings[server_panel_type]"><option value="whm">cPanel / WHM</option><option value="directadmin">DirectAdmin</option><option value="plesk">Plesk</option><option value="cyberpanel">CyberPanel</option><option value="virtualmin">Virtualmin</option></select></label><label>Sunucu Adı<input name="settings[server_name]" value="<?= e(wizard_setting('server_name','')) ?>"></label><label>Hostname<input name="settings[server_hostname]" value="<?= e(wizard_setting('server_hostname','')) ?>"></label><label>IP Adresi<input name="settings[server_ip]" value="<?= e(wizard_setting('server_ip','')) ?>"></label><label>Port<input name="settings[server_port]" value="<?= e(wizard_setting('server_port','2087')) ?>"></label><label>SSL Kullan<select name="settings[server_ssl]"><option value="1">Evet</option><option value="0">Hayır</option></select></label><label>Root/Admin Kullanıcı<input name="settings[server_username]" value="<?= e(wizard_setting('server_username','')) ?>"></label><label>API Token / Login Key<input name="settings[server_api_token]" value="<?= e(wizard_setting('server_api_token','')) ?>"></label></div><div class="compact-actions"><a class="btn" href="<?= url('admin/hosting-server/servers') ?>">Sunucu Ekle / Test Et</a><a class="btn ghost" href="<?= url('admin/scan-report') ?>">Sistem Testi</a></div></details></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>">
  <div class="ao-card premium wizard-products-card">
    <h3>7. Ürün, Paket ve Kategoriler</h3>
    <p class="muted">Fresh install sırasında oluşturulacak ürün ailelerini seç. Ayrıntılı paket/fiyat yönetimi kurulumdan sonra Ürün Merkezi içinde yapılır.</p>
    <div class="wizard-product-grid">
      <?php $productFamilies = [
        'setup_product_hosting'=>['Hosting','Web hosting, reseller ve paket altyapısı','🖥',true],
        'setup_product_domain'=>['Domain','Domain kayıt, transfer ve DNS ürünleri','🌐',true],
        'setup_product_ssl'=>['SSL','SSL sertifikaları ve güvenlik ürünleri','🔒',true],
        'setup_product_vps'=>['VPS / Sunucu','VPS, dedicated ve yönetilebilir sunucu','🧱',false],
        'setup_product_sitebuilder'=>['SiteBuilder','Hazır site, şablon ve builder paketleri','🎨',true],
        'setup_product_mobilebuilder'=>['MobileBuilder','Mobil uygulama builder paketleri','📱',true],
        'setup_product_web'=>['Web Tasarım','Kurumsal site, e-ticaret ve özel tasarım','💻',false],
        'setup_product_mobile'=>['Mobil Uygulama','Android/iOS proje hizmetleri','📲',false],
        'setup_product_seo'=>['SEO','SEO ve dijital pazarlama paketleri','📈',false],
        'setup_product_marketplace'=>['Marketplace','Tema, script ve ilan altyapısı','🛍',true],
      ]; ?>
      <?php foreach($productFamilies as $key=>$pf): $checked = wizard_setting($key, $pf[3] ? '1' : '0') === '1'; ?>
        <label class="wizard-product-card <?= $checked ? 'is-checked' : '' ?>">
          <input type="checkbox" name="settings[<?= e($key) ?>]" value="1" <?= $checked ? 'checked' : '' ?>>
          <span class="wizard-product-icon"><?= e($pf[2]) ?></span>
          <strong><?= e($pf[0]) ?></strong>
          <small><?= e($pf[1]) ?></small>
          <b class="wizard-product-check">✓</b>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="setup-summary"><strong>Not:</strong> Bu adım ürün ailelerini hazırlar; detaylı paketler ve fiyatlar Ürün Merkezi'nde düzenlenir.</div>
    <div class="compact-actions"><a class="btn ghost" href="<?= url('admin/product-center/groups') ?>">Ürün Merkezine Git</a></div>
  </div>
</section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>">
  <div class="ao-card premium payment-setup-card">
    <h3>8. Ödeme ve Komisyon</h3>
    <p class="muted">Shopier artık varsayılan olarak Kişisel Erişim Anahtarı (PAT) ile çalışır. Legacy API Key/Secret alanları geriye dönük uyumluluk için opsiyoneldir.</p>
    <div class="payment-provider-card">
      <div><strong>Shopier PAT</strong><small>Kişisel Erişim Anahtarını Shopier panelinden bir kez kopyalayıp buraya yapıştırın.</small></div>
      <label class="provider-mode"><select name="settings[shopier_auth_mode]"><option value="pat" <?= wizard_setting('shopier_auth_mode','pat')==='pat'?'selected':'' ?>>PAT Kullan</option><option value="legacy" <?= wizard_setting('shopier_auth_mode','pat')==='legacy'?'selected':'' ?>>Legacy API Kullan</option></select></label>
    </div>
    <div class="wizard-grid one">
      <label>Shopier PAT / Kişisel Erişim Anahtarı<textarea name="settings[shopier_pat]" rows="4" placeholder="eyJ0eXAiOiJKV1QiLCJhbGciOi..." autocomplete="off"><?= e(wizard_setting('shopier_pat', function_exists('ao_shopier_setting') ? ao_shopier_setting('pat','') : '')) ?></textarea><span class="field-help">PAT uzun olabilir; sistem bu alanı TEXT olarak saklar.</span></label>
    </div>
    <details class="wizard-accordion compact"><summary>Legacy API Key / Secret alanları</summary><div class="wizard-grid two"><label>Shopier API Key<input name="settings[shopier_api_key]" value="<?= e(wizard_setting('shopier_api_key', function_exists('ao_shopier_setting') ? ao_shopier_setting('api_key','') : '')) ?>"></label><label>Shopier API Secret<input name="settings[shopier_api_secret]" value="<?= e(wizard_setting('shopier_api_secret', function_exists('ao_shopier_setting') ? ao_shopier_setting('api_secret','') : '')) ?>"></label></div></details>
    <div class="compact-actions"><a class="btn" href="<?= url('admin/accounting/payment-fees') ?>">Ödeme / Komisyon Ayarları</a></div>
  </div>
</section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>9. Yapay Zeka Ayarları</h3><p class="muted">OpenAI, Gemini, Claude, DeepSeek ve Grok API anahtarlarını bağla.</p><div class="wizard-grid two"><label>AI Sağlayıcı<select name="settings[ai_provider]"><option value="openai" <?= wizard_setting('ai_provider','openai')==='openai'?'selected':'' ?>>OpenAI</option><option value="gemini" <?= wizard_setting('ai_provider','openai')==='gemini'?'selected':'' ?>>Gemini</option><option value="claude" <?= wizard_setting('ai_provider','openai')==='claude'?'selected':'' ?>>Claude</option></select></label><label>AI API Key<input name="settings[ai_api_key]" value="<?= e(wizard_setting('ai_api_key','')) ?>"></label></div><a class="btn" href="<?= url('admin/ai-center') ?>">AI Center’a Git</a></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>10. Builder</h3><p class="muted">SiteBuilder, MobileBuilder ve Build Center alanlarını kontrol et.</p><div class="wizard-grid three"><a class="btn" href="<?= url('admin/site-builder') ?>">SiteBuilder</a><a class="btn" href="<?= url('admin/mobile-builder') ?>">MobileBuilder</a><a class="btn" href="<?= url('admin/build-center') ?>">Build Center</a></div></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>11. Lisans Merkezi</h3><p class="muted">SiteBuilder, MobileBuilder, AI, tema ve marketplace lisanslarını kontrol et.</p><a class="btn" href="<?= url('admin/license-center') ?>">License Center’a Git</a></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>12. Marketplace</h3><p class="muted">Domain, hosting, web tasarım, SEO, logo, script ve dijital ürün marketplace ayarlarını tamamla.</p><div class="wizard-grid two"><a class="btn" href="<?= url('admin/marketplace') ?>">Marketplace Yönetimi</a><a class="btn" href="<?= url('admin/marketplace/categories') ?>">Kategori Ayarları</a></div></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>13. Güvenlik</h3><p class="muted">Admin rolleri, 2FA, IP kısıtlama, oturum süresi, reCAPTCHA ve CSRF ayarlarını kontrol et.</p><div class="wizard-grid three"><label>reCAPTCHA Site Key<input name="settings[recaptcha_site_key]" value="<?= e(wizard_setting('recaptcha_site_key','')) ?>"></label><label>reCAPTCHA Secret<input name="settings[recaptcha_secret_key]" value="<?= e(wizard_setting('recaptcha_secret_key','')) ?>"></label><label>Admin 2FA<select name="settings[admin_2fa_enabled]"><option value="0">Kapalı</option><option value="1">Açık</option></select></label></div><div class="compact-actions"><a class="btn" href="<?= url('admin/security') ?>">Güvenlik Merkezi</a><a class="btn" href="<?= url('admin/settings/security') ?>">Güvenlik Ayarları</a></div></div></section>
<section class="wizard-step-panel" data-panel="<?= $stepIndex++ ?>"><div class="ao-card premium"><h3>14. Sistem ve Son Kontrol</h3><p class="muted">Tamamlananlar otomatik işaretlenir. Eksik kalanlar bu listede görünür.</p><div class="setup-steps compact"><?php foreach($steps as $s): ?><div class="setup-step status-<?= e($s['status']) ?>"><div class="step-main"><strong><?= e($s['title']) ?></strong><p><?= e($s['description']) ?></p><?php if(!empty($s['route'])): ?><a href="<?= url($s['route']) ?>">Ayar ekranına git →</a><?php endif; ?></div><div class="step-side"><span class="pill <?= e($s['required']?'required':'optional') ?>"><?= $s['required']?'Zorunlu':'Opsiyonel' ?></span><span class="pill status-pill status-<?= e($s['status']) ?>"><?= e($statusLabels[$s['status']] ?? $s['status']) ?></span><select name="step_status[<?= e($s['step_key']) ?>]"><option value="pending" <?= $s['status']==='pending'?'selected':'' ?>>Bekliyor</option><option value="done" <?= $s['status']==='done'?'selected':'' ?>>Tamamlandı</option><option value="skipped" <?= $s['status']==='skipped'?'selected':'' ?>>Atlandı</option></select></div></div><?php endforeach; ?></div><label class="wizard-check"><input type="checkbox" name="complete" value="1" <?= admin_setting('setup_wizard_completed','0')==='1'?'checked':'' ?>> Kurulum tamamlandı, sistem açılış popup'ını kapat</label><div class="setup-summary"><strong>Tamamlanan:</strong> <?= (int)$doneCount ?> &nbsp; <strong>Eksik zorunlu:</strong> <?= (int)$missingCount ?></div><div class="wizard-actions"><button class="btn primary" name="run_scan" value="1">Sistem Kaydet ve Tara</button><a class="btn" href="<?= url('admin/setup-wizard/autocheck') ?>">Otomatik Kontrol Et</a><a class="btn" href="<?= url('admin/scan-report') ?>">Sistem Taraması</a><button class="btn">Tüm Ayarları Kaydet</button></div></div></section>
</form>

<div class="ao-card" id="api-yardim"><h3>API Ayar Yardımı</h3><div class="api-help-grid"><div><strong>DomainNameAPI</strong><p>Domain Center → Registrarlar. API Key veya kullanıcı/şifre girilir.</p></div><div><strong>Shopier / Sanal POS</strong><p>Modül Merkezi veya Ödeme Ayarları üzerinden API Key, Secret, callback URL ve test modu ayarlanır.</p></div><div><strong>SMTP</strong><p>Bildirim Merkezi içinde SMTP host, port, kullanıcı, şifre ve gönderici adı tanımlanır.</p></div><div><strong>AI API</strong><p>OpenAI/Gemini/Claude API anahtarı AI Center’a girilir.</p></div></div></div>
