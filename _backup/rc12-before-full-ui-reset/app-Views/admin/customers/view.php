<?php
$id=(int)($_GET['id']??0); $flash=get_flash(); $c=null; $services=$domains=$invoices=$tickets=$orders=$hosting=[]; $customerSwitchList=[];
try {
    $q=db()->prepare('SELECT * FROM customers WHERE id=?'); $q->execute([$id]); $c=$q->fetch();
    if($c){
        $queries=[
            'services'=>'SELECT s.*,p.name product_name,p.type product_type,p.module_name FROM services s LEFT JOIN products p ON p.id=s.product_id WHERE s.customer_id=? ORDER BY s.id DESC',
            'domains'=>'SELECT * FROM domains WHERE customer_id=? ORDER BY id DESC',
            'invoices'=>'SELECT * FROM invoices WHERE customer_id=? ORDER BY id DESC',
            'tickets'=>'SELECT * FROM tickets WHERE customer_id=? ORDER BY id DESC',
            'orders'=>'SELECT * FROM orders WHERE customer_id=? ORDER BY id DESC'
        ];
        foreach($queries as $k=>$sql){ $st=db()->prepare($sql); $st->execute([$id]); $$k=$st->fetchAll(); }
        $hst=db()->prepare('SELECT h.*,s.id service_id,s.domain,s.status service_status,s.billing_cycle,s.next_due_date,p.name product_name FROM hosting_accounts h JOIN services s ON s.id=h.service_id LEFT JOIN products p ON p.id=s.product_id WHERE s.customer_id=? ORDER BY h.id DESC'); $hst->execute([$id]); $hosting=$hst->fetchAll();
        try { $customerSwitchList = db()->query('SELECT id,first_name,last_name,email,company_name FROM customers WHERE status<>"deleted" ORDER BY first_name,last_name LIMIT 500')->fetchAll(); } catch(Throwable $e) {}
    }
} catch(Throwable $e) {}
function admin_pct($u,$l){ return $l>0 ? min(100,round($u*100/$l)) : 0; }
?>
<?php if(!$c): ?><div class="ao-card"><h2>Müşteri bulunamadı</h2><a class="ao-btn" href="<?= url('admin/customers') ?>">Listeye Dön</a></div><?php return; endif; ?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-profile-head ao-card">
    <div>
        <span class="ao-kicker">Müşteri Profili Pro</span>
        <h2>#<?= (int)$c['id'] ?> - <?= e($c['first_name'].' '.$c['last_name']) ?></h2>
        <p><?= e($c['company_name'] ?: 'Bireysel müşteri') ?> · <?= e($c['email']) ?> · <span class="ao-badge <?= e($c['status']) ?>"><?= e($c['status']) ?></span></p>
    </div>
    <div class="ao-actions no-margin">
        <a class="ao-btn" href="<?= url('admin/customers/login-as?id='.(int)$c['id']) ?>">Sahip Olarak Gir</a>
        <a class="ao-btn soft" href="<?= url('client') ?>">Müşteri Paneli</a>
        <a class="ao-light-btn" href="<?= url('admin/customers') ?>">Listeye Dön</a>
        <a class="ao-btn danger" onclick="return confirm('Müşteri kapalı duruma alınsın mı?')" href="<?= url('admin/customers/close?id='.(int)$c['id'].'&csrf_token='.csrf_token()) ?>">Müşteriyi Kapat</a> <a class="ao-btn danger" onclick="return confirm('Müşteri çöp kutusuna taşınsın mı?')" href="<?= url('admin/customers/delete?id='.(int)$c['id'].'&csrf_token='.csrf_token()) ?>">Müşteriyi Sil</a>
    </div>
</div>

<div class="admin-customer-switch">
  <label for="adminCustomerSwitch">Müşteri Değiştir</label>
  <select id="adminCustomerSwitch" onchange="if(this.value){location.href='<?= url('admin/customers/view?id=') ?>'+this.value}">
    <option value="">Müşteri seç...</option>
    <?php foreach($customerSwitchList as $sw): ?>
      <option value="<?= (int)$sw['id'] ?>" <?= (int)$sw['id']===(int)$c['id']?'selected':'' ?>>#<?= (int)$sw['id'] ?> · <?= e(trim(($sw['first_name']??'').' '.($sw['last_name']??''))) ?><?= !empty($sw['company_name'])?' · '.e($sw['company_name']):'' ?> · <?= e($sw['email']??'') ?></option>
    <?php endforeach; ?>
  </select>
  <a class="ao-light-btn" href="<?= url('admin/customers/view?id='.max(1,(int)$c['id']-1)) ?>">← Önceki</a>
  <a class="ao-light-btn" href="<?= url('admin/customers/view?id='.((int)$c['id']+1)) ?>">Sonraki →</a>
</div>

<div class="ao-profile-summary">
    <div class="ao-stat"><span>Hizmet</span><strong><?= count($services) ?></strong></div>
    <div class="ao-stat"><span>Domain</span><strong><?= count($domains) ?></strong></div>
    <div class="ao-stat"><span>Fatura</span><strong><?= count($invoices) ?></strong></div>
    <div class="ao-stat"><span>Bakiye</span><strong><?= number_format((float)$c['balance'],2,',','.') ?> ₺</strong></div>
</div>

<div class="ao-card ao-tab-shell" data-ao-tabs>
    <div class="ao-real-tabs" role="tablist">
        <button class="active" data-tab="genel">Genel Bilgiler</button>
        <button data-tab="iletisim">İletişim & Fatura</button>
        <button data-tab="urunler">Ürünler</button>
        <button data-tab="hosting">Hosting Yönetimi</button>
        <button data-tab="domainler">Domainler</button>
        <button data-tab="siparisler">Siparişler</button>
        <button data-tab="faturalar">Faturalar</button>
        <button data-tab="destek">Destek</button>
        <button data-tab="kredi">Kredi</button>
        <button data-tab="yenilemeler">Yenilemeler</button>
        <button data-tab="guvenlik">Güvenlik</button>
        <button data-tab="loglar">Günlükler</button>
    </div>

    <section id="tab-genel" class="ao-tab-panel active">
        <h3>Genel Bilgiler</h3>
        <form class="ao-form" method="post" action="<?= url('admin/customers/update') ?>">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <div class="ao-form-grid">
                <label>Ad<input name="first_name" value="<?= e($c['first_name']) ?>"></label>
                <label>Soyad<input name="last_name" value="<?= e($c['last_name']) ?>"></label>
                <label>Firma<input name="company_name" value="<?= e($c['company_name']) ?>"></label>
                <label>E-posta<input name="email" value="<?= e($c['email']) ?>"></label>
                <label>Telefon<input name="phone" value="<?= e($c['phone']) ?>"></label>
                <label>Durum<select name="status"><option value="active" <?= $c['status']==='active'?'selected':'' ?>>Aktif</option><option value="inactive" <?= $c['status']==='inactive'?'selected':'' ?>>Pasif</option><option value="closed" <?= $c['status']==='closed'?'selected':'' ?>>Kapalı</option></select></label>
                <label>Bakiye<input name="balance" type="number" step="0.01" value="<?= e($c['balance']) ?>"></label>
                <label>Para Birimi<input name="currency" value="<?= e($c['currency'] ?? 'TRY') ?>"></label>
                <label>Dil<input name="language" value="<?= e($c['language'] ?? 'tr') ?>"></label>
                <label>Vergi No<input name="tax_number" value="<?= e($c['tax_number'] ?? '') ?>"></label>
                <label>TC Kimlik No <small>(admin için opsiyonel)</small><input name="tc_identity_no" value="<?= e($c['tc_identity_no'] ?? '') ?>"></label>
                <label>Doğum Tarihi <small>(admin için opsiyonel)</small><input type="date" name="birth_date" value="<?= e($c['birth_date'] ?? '') ?>"></label>
                <label>Kimlik Durumu<input value="<?= !empty($c['identity_verified']) ? 'Doğrulandı' : 'Doğrulanmadı / Opsiyonel' ?>" readonly></label>
            </div>
            <h3>Adres / Fatura Bilgileri</h3>
            <div class="ao-form-grid">
                <label class="full">Adres 1<input name="address1" value="<?= e($c['address1'] ?? '') ?>"></label>
                <label class="full">Adres 2<input name="address2" value="<?= e($c['address2'] ?? '') ?>"></label>
                <label>Şehir<input name="city" value="<?= e($c['city'] ?? '') ?>"></label>
                <label>İlçe/Bölge<input name="state" value="<?= e($c['state'] ?? '') ?>"></label>
                <label>Posta Kodu<input name="postcode" value="<?= e($c['postcode'] ?? '') ?>"></label>
                <label>Ülke<input name="country" value="<?= e($c['country'] ?? 'Türkiye') ?>"></label>
                <label class="full">Admin Notu<textarea name="notes" rows="4"><?= e($c['notes'] ?? '') ?></textarea></label>
            </div>
            <button class="ao-btn">Bilgileri Güncelle</button>
        </form>
    </section>

    <section id="tab-iletisim" class="ao-tab-panel"><h3>İletişim & Fatura Özeti</h3><div class="ao-info-list"><p><strong>E-posta:</strong> <?= e($c['email']) ?></p><p><strong>Telefon:</strong> <?= e($c['phone']) ?></p><p><strong>Adres:</strong> <?= e(trim(($c['address1']??'').' '.($c['address2']??'').' '.($c['city']??'').' '.($c['country']??''))) ?></p><p><strong>Vergi No:</strong> <?= e($c['tax_number'] ?? '-') ?></p></div></section>

    <section id="tab-urunler" class="ao-tab-panel"><h3>Ürünler / Hizmetler</h3><table class="ao-table"><tr><th>Ürün</th><th>Domain</th><th>Durum</th><th>Döngü</th><th>Sonraki Ödeme</th><th>İşlem</th></tr><?php foreach($services as $s): ?><tr><td><?= e($s['product_name'] ?: 'Özel Hizmet') ?></td><td><?= e($s['domain']) ?></td><td><?= e($s['status']) ?></td><td><?= e($s['billing_cycle']) ?></td><td><?= e($s['next_due_date']) ?></td><td><a class="ao-light-btn" href="#tab-hosting">Yönet</a></td></tr><?php endforeach; if(!$services): ?><tr><td colspan="6">Hizmet yok.</td></tr><?php endif; ?></table></section>

    <section id="tab-hosting" class="ao-tab-panel">
        <h3>Hosting / Sunucu / Panel Yönetimi</h3>
        <p class="ao-muted">Ahost One entegre müşteri hizmet yönetimi: suspend, unsuspend, terminate, paket değiştir, panel şifresi değiştir, sunucu taşı, panel kısayolları ve kaynak kullanımı.</p>
        <?php foreach($hosting as $h): $dp=admin_pct((int)$h['disk_used_mb'],(int)$h['disk_mb']); $bp=admin_pct((int)$h['bandwidth_used_mb'],(int)$h['bandwidth_mb']); $mp=admin_pct((int)$h['mail_used'],(int)$h['mail_limit']); $dbp=admin_pct((int)$h['mysql_used'],(int)$h['mysql_limit']); ?>
        <div class="ao-service-box">
            <div class="ao-service-head"><div><h4><?= e($h['product_name']) ?> · <?= e($h['domain']) ?></h4><p><?= e($h['server_name']) ?> / <?= e($h['server_ip']) ?> · Durum: <strong><?= e($h['service_status']) ?></strong> · Paket: <strong><?= e($h['package_name']) ?></strong></p></div><div><a class="ao-light-btn" target="_blank" href="<?= url('admin/service-panel-login?service_id='.(int)$h['service_id'].'&panel=cpanel') ?>">cPanel</a><a class="ao-light-btn" target="_blank" href="<?= url('admin/service-panel-login?service_id='.(int)$h['service_id'].'&panel=directadmin') ?>">DirectAdmin</a><a class="ao-light-btn" target="_blank" href="<?= url('admin/service-panel-login?service_id='.(int)$h['service_id'].'&panel=webmail') ?>">Webmail</a><a class="ao-light-btn" target="_blank" href="<?= url('admin/service-panel-login?service_id='.(int)$h['service_id'].'&panel=whm') ?>">WHM</a><a class="ao-light-btn" target="_blank" href="<?= url('admin/service-panel-login?service_id='.(int)$h['service_id'].'&panel=vps') ?>">VPS</a></div></div>
            <div class="ao-resource-grid"><div><span>Disk</span><b><?= $dp ?>%</b><i><em style="width:<?= $dp ?>%"></em></i><small><?= (int)$h['disk_used_mb'] ?>/<?= (int)$h['disk_mb'] ?> MB</small></div><div><span>Trafik</span><b><?= $bp ?>%</b><i><em style="width:<?= $bp ?>%"></em></i><small><?= (int)$h['bandwidth_used_mb'] ?>/<?= (int)$h['bandwidth_mb'] ?> MB</small></div><div><span>Mail</span><b><?= (int)$h['mail_used'] ?>/<?= (int)$h['mail_limit'] ?></b><i><em style="width:<?= $mp ?>%"></em></i><small>Hesap</small></div><div><span>MySQL</span><b><?= (int)$h['mysql_used'] ?>/<?= (int)$h['mysql_limit'] ?></b><i><em style="width:<?= $dbp ?>%"></em></i><small>Veritabanı</small></div></div>
            <table class="ao-table"><tr><th>Kullanıcı</th><th>Şifre</th><th>Nameserver</th><th>Sonraki Ödeme</th></tr><tr><td><?= e($h['whm_username']) ?></td><td><code><?= e($h['panel_password'] ?: 'Panel şifresi boş veya şifreli geldi') ?></code><br><small>Panel şifresi güvenlik nedeniyle görünmüyorsa manuel sıfırlama önerilir.</small></td><td><?= e($h['ns1']) ?><br><?= e($h['ns2']) ?></td><td><?= e($h['next_due_date']) ?></td></tr></table>
            <div class="ao-action-grid">
                <form method="post" action="<?= url('admin/customers/service-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="service_id" value="<?= (int)$h['service_id'] ?>"><input type="hidden" name="service_action" value="suspend"><button class="ao-btn danger">Suspend</button></form>
                <form method="post" action="<?= url('admin/customers/service-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="service_id" value="<?= (int)$h['service_id'] ?>"><input type="hidden" name="service_action" value="unsuspend"><button class="ao-btn">Unsuspend</button></form>
                <form method="post" action="<?= url('admin/customers/service-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="service_id" value="<?= (int)$h['service_id'] ?>"><input type="hidden" name="service_action" value="terminate"><button class="ao-btn dark">Terminate</button></form>
                <form class="inline-form" method="post" action="<?= url('admin/customers/service-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="service_id" value="<?= (int)$h['service_id'] ?>"><input type="hidden" name="service_action" value="change-package"><input name="package_name" value="<?= e($h['package_name']) ?>"><button class="ao-light-btn">Paket Değiştir</button></form>
                <form class="inline-form" method="post" action="<?= url('admin/customers/service-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="service_id" value="<?= (int)$h['service_id'] ?>"><input type="hidden" name="service_action" value="change-password"><input name="panel_password" value="<?= e($h['panel_password']) ?>"><button class="ao-light-btn">Şifre Değiştir</button></form>
                <form class="inline-form" method="post" action="<?= url('admin/customers/service-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="service_id" value="<?= (int)$h['service_id'] ?>"><input type="hidden" name="service_action" value="move-server"><input name="server_name" value="<?= e($h['server_name']) ?>"><input name="server_ip" value="<?= e($h['server_ip']) ?>"><button class="ao-light-btn">Sunucu Taşı</button></form>
            </div>
            <div class="ao-info-list"><strong>Hazır işlem seti:</strong> mail oluşturma, dosya yöneticisi, yedek alma, SSL kur, DNS yönet, cron, FTP, veritabanı, PHP sürümü ve kaynak senkronizasyonu API bağlantısı fazında aktif edilecek.</div>
        </div>
        <?php endforeach; if(!$hosting): ?><div class="ao-info-list">Hosting hesabı yok.</div><?php endif; ?>
    </section>

    <section id="tab-domainler" class="ao-tab-panel">
        <h3>Domain Yönetimi</h3>
        <p class="ao-muted">Kayıt, transfer, yenileme, DNS/nameserver, EPP, registrar lock, oto yenileme, registrar değişikliği ve WHOIS/DNS/SSL kısayolları.</p>
        <table class="ao-table"><tr><th>Domain</th><th>Registrar</th><th>Durum</th><th>Bitiş</th><th>EPP</th><th>Ayarlar</th></tr><?php foreach($domains as $d): $domainRegistrar=$d['registrar'] ?? ($d['registrar_name'] ?? (!empty($d['registrar_id']) ? ('Registrar #'.(int)$d['registrar_id']) : 'DomainNameAPI')); $domainEpp=$d['epp_code'] ?? ($d['auth_code'] ?? ''); ?><tr><td><strong><?= e($d['domain_name'] ?? '-') ?></strong><br><small>Auto renew: <?= !empty($d['auto_renew'])?'Açık':'Kapalı' ?> · Lock: <?= !empty($d['lock_status'])?'Kilitli':'Açık' ?></small></td><td><?= e($domainRegistrar) ?></td><td><?= e($d['status'] ?? '-') ?></td><td><?= e($d['expiry_date'] ?? '-') ?></td><td><code><?= e($domainEpp ?: 'Henüz yok') ?></code></td><td><div class="domain-action-stack">
            <?php foreach(['renew'=>'Yenileme Siparişi','transfer'=>'Transfer','toggle-lock'=>'Kilit Aç/Kapat','toggle-autorenew'=>'Oto Yenileme'] as $act=>$label): ?><form method="post" action="<?= url('admin/customers/domain-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="domain_id" value="<?= (int)$d['id'] ?>"><input type="hidden" name="domain_action" value="<?= e($act) ?>"><button class="ao-light-btn"><?= e($label) ?></button></form><?php endforeach; ?>
            <form class="inline-form" method="post" action="<?= url('admin/customers/domain-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="domain_id" value="<?= (int)$d['id'] ?>"><input type="hidden" name="domain_action" value="update-epp"><input name="epp_code" value="<?= e($domainEpp) ?>"><button class="ao-light-btn">EPP Kaydet / Boşsa Registrar’dan İste</button></form>
            <form class="inline-form" method="post" action="<?= url('admin/customers/domain-action') ?>"><input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="domain_id" value="<?= (int)$d['id'] ?>"><input type="hidden" name="domain_action" value="update-registrar"><input name="registrar" value="<?= e($domainRegistrar) ?>"><button class="ao-light-btn">Registrar</button></form>
            <a class="ao-light-btn" href="<?= url('admin/domain-center/view?id='.(int)$d['id'].'#dns') ?>">DNS</a><a class="ao-light-btn" href="<?= url('admin/domain-center/view?id='.(int)$d['id'].'#nameserver') ?>">Nameserver</a><a class="ao-light-btn" href="<?= url('admin/domain-center/view?id='.(int)$d['id'].'#whois') ?>">WHOIS</a><a class="ao-light-btn" href="<?= url('admin/domain-center/view?id='.(int)$d['id'].'#epp') ?>">EPP/SSL</a>
        </div></td></tr><?php endforeach; if(!$domains): ?><tr><td colspan="6">Domain yok.</td></tr><?php endif; ?></table>
    </section>


    <section id="tab-yenilemeler" class="ao-tab-panel"><h3>Yenileme Merkezi</h3><div class="renewal-card"><h4>Hosting Yenilemeleri</h4><table class="ao-table"><tr><th>Hizmet</th><th>Domain</th><th>Yenileme</th><th>Kalan</th><th>Oto Yenileme</th></tr><?php foreach($services as $s): $days=ao_days_until($s['next_due_date'] ?? null); ?><tr><td><?= e($s['product_name'] ?: 'Hizmet') ?></td><td><?= e($s['domain']) ?></td><td><?= e($s['next_due_date']) ?></td><td><span class="renewal-pill <?= $days<16?'badge-danger':($days<45?'badge-warn':'badge-ok') ?>"><?= $days ?> gün</span></td><td><?= !empty($s['auto_renew'])?'Açık':'Kapalı' ?></td></tr><?php endforeach; ?></table></div><div class="renewal-card"><h4>Domain Yenilemeleri</h4><table class="ao-table"><tr><th>Domain</th><th>Bitiş</th><th>Kalan</th><th>Oto Yenileme</th></tr><?php foreach($domains as $d): $days=ao_days_until($d['expiry_date'] ?? null); ?><tr><td><?= e($d['domain_name']) ?></td><td><?= e($d['expiry_date']) ?></td><td><span class="renewal-pill <?= $days<16?'badge-danger':($days<45?'badge-warn':'badge-ok') ?>"><?= $days ?> gün</span></td><td><?= !empty($d['auto_renew'])?'Açık':'Kapalı' ?></td></tr><?php endforeach; ?></table></div></section>
    <section id="tab-siparisler" class="ao-tab-panel"><h3>Siparişler</h3><table class="ao-table"><tr><th>No</th><th>Durum</th><th>Tutar</th><th>Ödeme</th><th>Tarih</th></tr><?php foreach($orders as $o): ?><tr><td><?= e($o['order_number']) ?></td><td><?= e($o['status']) ?></td><td><?= number_format((float)$o['total'],2,',','.') ?> ₺</td><td><?= e($o['payment_method']) ?></td><td><?= e($o['created_at']) ?></td></tr><?php endforeach; if(!$orders): ?><tr><td colspan="5">Sipariş yok.</td></tr><?php endif; ?></table></section>
    <section id="tab-faturalar" class="ao-tab-panel"><h3>Faturalar</h3><table class="ao-table"><tr><th>No</th><th>Durum</th><th>Tutar</th><th>Son Ödeme</th></tr><?php foreach($invoices as $i): ?><tr><td><?= e($i['invoice_number']) ?></td><td><?= e($i['status']) ?></td><td><?= number_format((float)$i['total'],2,',','.') ?> ₺</td><td><?= e($i['due_date']) ?></td></tr><?php endforeach; if(!$invoices): ?><tr><td colspan="4">Fatura yok.</td></tr><?php endif; ?></table></section>
    <section id="tab-destek" class="ao-tab-panel"><h3>Destek Talepleri</h3><table class="ao-table"><tr><th>Konu</th><th>Departman</th><th>Öncelik</th><th>Durum</th></tr><?php foreach($tickets as $t): ?><tr><td><?= e($t['subject']) ?></td><td><?= e($t['department'] ?? ($t['department_name'] ?? (!empty($t['department_id']) ? ('Departman #'.(int)$t['department_id']) : 'Genel'))) ?></td><td><?= e($t['priority']) ?></td><td><?= e($t['status']) ?></td></tr><?php endforeach; if(!$tickets): ?><tr><td colspan="4">Ticket yok.</td></tr><?php endif; ?></table></section>
    <section id="tab-kredi" class="ao-tab-panel"><h3>Kredi Bakiyesi</h3><div class="ao-stat"><span>Mevcut Kredi</span><strong><?= number_format((float)$c['balance'],2,',','.') ?> ₺</strong></div><form class="ao-form" method="post" action="<?= url('admin/customers/credit') ?>"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><div class="ao-form-grid"><label>Tutar (+ ekle / - düş)<input name="amount" type="number" step="0.01" value="100.00"></label><label>Not<input name="note" value="Admin kredi işlemi"></label></div><button class="ao-btn">Kredi Güncelle</button></form></section>
    <section id="tab-guvenlik" class="ao-tab-panel"><h3>Güvenlik</h3><div class="ao-info-list"><p><strong>Son giriş:</strong> <?= e($c['last_login_at'] ?? 'Henüz yok') ?></p><p><strong>2FA:</strong> <?= !empty($c['two_factor_enabled']) ? 'Aktif' : 'Kapalı' ?></p><p><strong>Sahip olarak gir:</strong> Admin panelinden müşteri panelini test etmek için aktif.</p></div></section>
    <section id="tab-loglar" class="ao-tab-panel"><h3>Giriş ve İşlem Günlüğü</h3><p>Giriş geçmişi, mail logları, ödeme hareketleri, admin değişiklikleri, API işlemleri, otomasyon logları ve registrar/WHM cevapları burada toplanacak.</p></section>
</div>
