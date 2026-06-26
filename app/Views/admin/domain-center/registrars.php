<?php
$registrars=[]; $configs=[]; $flash=get_flash();
try{
    $registrars=db()->query('SELECT * FROM domain_registrars ORDER BY FIELD(slug,"domainnameapi","resellerclub","natro","isimtescil","enom","ahost-registrar"), name')->fetchAll();
    $rows=db()->query('SELECT rc.*,dr.slug FROM registrar_configs rc JOIN domain_registrars dr ON dr.id=rc.registrar_id ORDER BY rc.registrar_id, rc.config_key')->fetchAll();
    foreach($rows as $r){ $configs[$r['registrar_id']][$r['config_key']]=$r; }
}catch(Throwable $e){}
$authModes=[
    'apikey'=>'API key + API secret/hash',
    'token'=>'Bearer/Token',
    'custom'=>'Özel / karma yapılandırma'
];
$advancedKeys=[
    'check_endpoint'=>'Domain Sorgu Endpoint',
    'whois_endpoint'=>'WHOIS Endpoint',
    'epp_endpoint'=>'EPP Endpoint',
    'renew_endpoint'=>'Yenileme Endpoint',
    'register_endpoint'=>'Kayıt Endpoint',
    'transfer_endpoint'=>'Transfer Endpoint',
    'dns_endpoint'=>'DNS Endpoint',
    'ns_endpoint'=>'Nameserver Endpoint',
    'lock_endpoint'=>'Registrar Lock Endpoint'
];
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><h2>Registrarlar</h2><p>Domain kayıt, transfer, yenileme, WHOIS, EPP, DNS ve nameserver işlemleri için registrar yapılandırmaları burada yönetilir.</p></div><a class="ao-btn soft" href="<?= url('admin/domain-center/pricing') ?>">TLD Fiyatları</a></div>
<div class="ao-card registrar-help-card">
  <strong>DomainNameAPI yeni bağlantı yapısı:</strong> Panel girişi <b>dm.domainnameapi.com</b> üzerinde e-posta/şifre ile yapılır; Ahost One bağlantısı ise <b>Reseller ID + API Key + OTE/Test API Key</b> ile kurulur. Username/password alanları DomainNameAPI için kullanılmaz.
</div>
<div class="registrar-accordion" data-registrar-accordion>
<?php foreach($registrars as $r):
    $cfg=$configs[$r['id']]??[];
    $isDna = stripos(($r['slug']??'').' '.($r['module_name']??''),'domainnameapi')!==false || stripos(($r['slug']??''),'dna')!==false;
    $auth=$isDna ? 'apikey' : ($cfg['auth_mode']['config_value']??'apikey');
    $testDomain=$cfg['test_domain']['config_value']??'example.com';
?>
    <div class="ao-card registrar-item <?= $isDna?'domainnameapi-item':'' ?>" data-registrar-item data-registrar-id="<?= (int)$r['id'] ?>">
        <button type="button" class="registrar-summary" data-registrar-toggle aria-expanded="false">
            <span>
                <strong><?= e($r['name']) ?></strong>
                <small><?= e($r['module_name']) ?> · <?= $r['supported_tlds'] ? e($r['supported_tlds']) : 'Tüm registrar TLDleri' ?></small>
            </span>
            <span class="ao-badge <?= $r['status']==='active'?'active':'inactive' ?>"><?= e($r['status']) ?></span>
            <em>Detay</em>
        </button>
        <div class="registrar-body" data-registrar-body hidden>
            <p class="ao-muted"><?= e($r['notes'] ?? '') ?></p>
            <?php if($isDna): ?>
              <div class="ao-info-list dna-api-note">
                <b>Your API Information</b> mantığı kullanılacak: <b>Reseller ID</b>, <b>Canlı API Key</b> ve <b>OTE/Test API Key</b>. Yardım: <a href="https://dm.domainnameapi.com" target="_blank" rel="noopener">dm.domainnameapi.com</a>
              </div>
            <?php endif; ?>
            <form class="ao-form registrar-config-form" method="post" action="<?= url('admin/domain-center/registrar-save') ?>" data-auth-form>
                <?= csrf_field() ?>
                <input type="hidden" name="registrar_id" value="<?= (int)$r['id'] ?>">
                <div class="ao-form-grid">
                    <label>Durum<select name="status"><option value="active" <?= $r['status']==='active'?'selected':'' ?>>Aktif</option><option value="inactive" <?= $r['status']!=='active'?'selected':'' ?>>Pasif</option></select></label>
                    <label>Çalışma Modu<select name="test_mode"><option value="1" <?= $r['test_mode']?'selected':'' ?>>Test / OTE</option><option value="0" <?= !$r['test_mode']?'selected':'' ?>>Canlı</option></select></label>
                    <label class="full">Desteklenen TLD filtresi <input name="supported_tlds" value="<?= e($r['supported_tlds'] ?? '') ?>" placeholder="Boş bırakılırsa registrarın desteklediği tüm TLD'ler kabul edilir"></label>
                    <?php if($isDna): ?>
                        <input type="hidden" name="config[auth_mode]" value="apikey">
                        <input type="hidden" name="config[api_endpoint]" value="">
                        <label>Reseller ID<input name="config[reseller_id]" value="<?= e($cfg['reseller_id']['config_value']??'') ?>" placeholder="Örn: 12345"></label>
                        <label>Canlı API Key<input type="password" name="config[api_key]" value="<?= e($cfg['api_key']['config_value']??'') ?>" autocomplete="new-password" placeholder="Canlı API anahtarı"></label>
                        <label>OTE / Test API Key<input type="password" name="config[ote_api_key]" value="<?= e($cfg['ote_api_key']['config_value']??'') ?>" autocomplete="new-password" placeholder="Test API anahtarı"></label>
                        <label>Bağlantı Test Domaini<input name="config[test_domain]" value="<?= e($testDomain) ?>" placeholder="example.com"></label>
                    <?php else: ?>
                        <label>Kimlik Doğrulama Tipi<select name="config[auth_mode]" data-auth-mode>
                            <?php foreach($authModes as $val=>$label): ?><option value="<?= e($val) ?>" <?= $auth===$val?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?>
                        </select></label>
                        <label>Ana API Endpoint<input name="config[api_endpoint]" value="<?= e($cfg['api_endpoint']['config_value']??'') ?>" placeholder="https://api.example.com"></label>
                        <label data-auth-field="apikey custom">API Key<input name="config[api_key]" value="<?= e($cfg['api_key']['config_value']??'') ?>"></label>
                        <label data-auth-field="apikey custom">API Secret / Hash<input type="password" name="config[api_secret]" value="<?= e($cfg['api_secret']['config_value']??'') ?>"></label>
                        <label data-auth-field="token custom">Token / Bearer Token<input type="password" name="config[token]" value="<?= e($cfg['token']['config_value']??'') ?>"></label>
                        <label>Reseller / Bayi ID<input name="config[reseller_id]" value="<?= e($cfg['reseller_id']['config_value']??'') ?>"></label>
                        <label>Bağlantı Test Domaini<input name="config[test_domain]" value="<?= e($testDomain) ?>" placeholder="example.com"></label>
                    <?php endif; ?>
                </div>
                <?php if(!$isDna): ?>
                <details class="registrar-advanced">
                    <summary>Gelişmiş endpointler</summary>
                    <p class="ao-muted">Boş bırakırsan tüm işlemler ana endpoint üzerinden denenir.</p>
                    <div class="ao-form-grid">
                    <?php foreach($advancedKeys as $key=>$label): $val=$cfg[$key]['config_value']??''; ?>
                        <label><?= e($label) ?><input name="config[<?= e($key) ?>]" value="<?= e($val) ?>"></label>
                    <?php endforeach; ?>
                    </div>
                </details>
                <?php endif; ?>
                <div class="ao-actions no-margin">
                    <button class="ao-btn">Yapılandırmayı Kaydet</button>
                    <a class="ao-btn soft" href="<?= url('admin/domain-center/registrar-test?id='.(int)$r['id'].'&domain='.urlencode($testDomain)) ?>">API Bağlantısını Test Et</a>
                </div>
            </form>
            <div class="ao-info-list"><strong>Canlı kontrol:</strong> API Bağlantısını Test Et seçilen test/canlı mod, Reseller ID ve API Key bilgileriyle registrar yanıtını kontrol eder; sonuçlar API loglarına yazılır.</div>
        </div>
    </div>
<?php endforeach; if(!$registrars): ?><div class="ao-card">Registrar kaydı yok.</div><?php endif; ?>
</div>
