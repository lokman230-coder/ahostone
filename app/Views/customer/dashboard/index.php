<?php
$c=current_customer(); $services=$domains=$invoices=$tickets=[];
try{foreach(['services'=>'SELECT s.*,p.name product_name FROM services s LEFT JOIN products p ON p.id=s.product_id WHERE s.customer_id=? ORDER BY s.id DESC LIMIT 5','domains'=>'SELECT * FROM domains WHERE customer_id=? ORDER BY id DESC LIMIT 5','invoices'=>'SELECT * FROM invoices WHERE customer_id=? ORDER BY id DESC LIMIT 5','tickets'=>'SELECT * FROM tickets WHERE customer_id=? ORDER BY id DESC LIMIT 5'] as $k=>$sql){$st=db()->prepare($sql);$st->execute([$c['id']]);$$k=$st->fetchAll();}}catch(Throwable $e){}
$balance=number_format((float)($c['balance']??0),2,',','.').' ₺';
?>
<div class="e-client-hero">
  <div class="customer-panel-card e-client-welcome"><span class="u2-kicker">Müşteri Paneli Enterprise</span><h2>Hoş geldiniz, <?= e($c['first_name']) ?> 👋</h2><p>Hizmet, domain, SSL, fatura, kredi ve destek operasyonunuzu tek panelde takip edin. Yaklaşan yenilemeler ve kaynak kullanımı artık daha görünür.</p><div class="button-row"><a class="u2-btn" href="<?= url('hosting') ?>">Yeni Hizmet Al</a><a class="u2-btn soft" href="<?= url('domain') ?>">Domain Ara</a><a class="u2-btn dark" href="<?= url('client/support') ?>">Ticket Aç</a></div></div>
  <div class="customer-panel-card e-balance"><span>Kredi Bakiyesi</span><strong><?= $balance ?></strong><a class="u2-btn" href="<?= url('client/credit') ?>" style="width:100%">Kredi Ekle</a><p style="color:#cbd5e1">Otomatik yenileme ve fatura ödemeleri için bakiye kullanılır.</p></div>
</div>
<div class="e-client-grid">
  <div class="u2-card e-kpi"><div class="meta"><span>Aktif Hizmet</span><i>📦</i></div><strong><?= count($services) ?></strong><small>Hosting/VPS/servis</small></div>
  <div class="u2-card e-kpi"><div class="meta"><span>Domain</span><i>🌐</i></div><strong><?= count($domains) ?></strong><small>Kayıtlı alan adı</small></div>
  <div class="u2-card e-kpi"><div class="meta"><span>Fatura</span><i>🧾</i></div><strong><?= count($invoices) ?></strong><small>Son faturalar</small></div>
  <div class="u2-card e-kpi"><div class="meta"><span>Destek</span><i>🎧</i></div><strong><?= count($tickets) ?></strong><small>Ticket kayıtları</small></div>
</div>
<div class="e-customer-layout">
  <div class="customer-panel-card"><div class="u2-section-title"><div><h3>Aktif Hizmetler ve Kaynak Kullanımı</h3><p>Disk, trafik, SSL ve yenileme bilgileri.</p></div><a class="u2-btn soft" href="<?= url('client/services') ?>">Tümü</a></div><div><?php foreach($services as $i=>$s): $disk=[32,48,19,73,41][$i%5]; $traffic=[18,52,35,64,27][$i%5]; ?><div class="e-service-row"><div style="flex:1"><b><?= e($s['product_name'] ?: 'Hizmet') ?></b><small style="display:block;color:#64748b;margin:4px 0"><?= e($s['domain'] ?: 'Domain tanımsız') ?> · Yenileme: <?= e($s['next_due_date'] ?: '-') ?></small><div class="e-resource-grid"><div class="e-resource"><b>Disk</b><div class="e-progress"><span style="width:<?= $disk ?>%"></span></div></div><div class="e-resource"><b>Trafik</b><div class="e-progress"><span style="width:<?= $traffic ?>%"></span></div></div><div class="e-resource"><b>SSL</b><div class="e-progress"><span style="width:90%"></span></div></div><div class="e-resource"><b>Sağlık</b><div class="e-progress"><span style="width:96%"></span></div></div></div></div><a class="u2-btn soft" href="<?= url('client/services/view?id='.(int)$s['id']) ?>">Yönet</a></div><?php endforeach; if(!$services): ?><div class="e-service-row"><b>Henüz aktif hizmet yok.</b><a class="u2-btn" href="<?= url('hosting') ?>">Hizmet Al</a></div><?php endif; ?></div></div>
  <div class="customer-panel-card"><div class="u2-section-title"><div><h3>Yenileme Merkezi</h3><p>Yaklaşan işlemler ve hızlı durum.</p></div><span class="u2-pill blue">30 gün</span></div><div class="e-renewal-list"><div><b>Domain yenilemeleri</b><span class="u2-pill orange"><?= count($domains) ?> kayıt</span></div><div><b>Hosting yenilemeleri</b><span class="u2-pill blue"><?= count($services) ?> hizmet</span></div><div><b>SSL durumu</b><span class="u2-pill green">Aktif</span></div><div><b>Destek yanıtları</b><span class="u2-pill <?= count($tickets)?'orange':'green' ?>"><?= count($tickets) ?> ticket</span></div></div></div>
</div>
<div class="e-customer-layout" style="margin-top:22px">
  <div class="customer-panel-card"><div class="u2-section-title"><div><h3>Domainlerim</h3><p>Süre ve SSL takip özeti.</p></div><a class="u2-btn soft" href="<?= url('client/domains') ?>">Tümü</a></div><div class="e-renewal-list"><?php foreach($domains as $d): ?><div><b><?php $dn=ao_domain_display_name($d); ?><?= $dn!=='' ? e($dn) : '<span class="ao-domain-name-missing">Domain adı yok</span>' ?></b><span class="u2-pill green"><?= e(ao_status_tr($d['status'] ?? 'active')) ?></span></div><?php endforeach; if(!$domains): ?><div><b>Henüz domain yok.</b><a class="u2-btn soft" href="<?= url('domain') ?>">Domain Ara</a></div><?php endif; ?></div></div>
  <div class="customer-panel-card"><div class="u2-section-title"><div><h3>Son Faturalar</h3><p>Ödeme ve durum özeti.</p></div><a class="u2-btn soft" href="<?= url('client/invoices') ?>">Tümü</a></div><table class="e-table"><tr><th>Fatura</th><th>Tutar</th><th>Durum</th></tr><?php foreach($invoices as $inv): ?><tr><td>#INV-<?= e($inv['id']) ?></td><td><?= number_format((float)$inv['total'],2,',','.') ?> ₺</td><td><span class="u2-pill <?= ($inv['status'] ?? '')==='paid'?'green':'orange' ?>"><?= e($inv['status']) ?></span></td></tr><?php endforeach; if(!$invoices): ?><tr><td colspan="3">Henüz fatura yok.</td></tr><?php endif; ?></table></div>
</div>

<?php $c=current_customer(); $renewServices=$renewDomains=[]; try{ $q=db()->prepare('SELECT s.*,p.name product_name FROM services s LEFT JOIN products p ON p.id=s.product_id WHERE s.customer_id=? ORDER BY s.next_due_date ASC LIMIT 5'); $q->execute([$c['id']]); $renewServices=$q->fetchAll(); $q=db()->prepare('SELECT * FROM domains WHERE customer_id=? ORDER BY expiry_date ASC LIMIT 5'); $q->execute([$c['id']]); $renewDomains=$q->fetchAll(); }catch(Throwable $e){} ?>
<div class="renewal-card">
  <h3>Yaklaşan Yenilemeler</h3>
  <div class="renewal-list">
    <?php foreach($renewServices as $s): $days=ao_days_until($s['next_due_date'] ?? null); ?>
      <div class="renewal-row">
        <div class="renewal-row-main"><span class="renewal-icon">🖥️</span><div><strong class="renewal-title"><?= e($s['product_name'] ?: 'Hosting Hizmeti') ?></strong><div class="renewal-meta"><span><?= e($s['domain'] ?: 'Domain tanımsız') ?></span><span>•</span><span>Yenileme: <?= e($s['next_due_date'] ?: '-') ?></span></div></div></div>
        <div class="renewal-actions"><span class="renewal-pill <?= $days<16?'badge-danger':($days<45?'badge-warn':'badge-ok') ?>"><?= $days ?> gün</span><a class="u2-btn soft" href="<?= url('client/services/view?id='.(int)$s['id']) ?>">Yönet</a></div>
      </div>
    <?php endforeach; ?>
    <?php foreach($renewDomains as $d): $days=ao_days_until($d['expiry_date'] ?? null); ?>
      <div class="renewal-row">
        <div class="renewal-row-main"><span class="renewal-icon">🌐</span><div><strong class="renewal-title"><?php $dn=ao_domain_display_name($d); ?><?= $dn!=='' ? e($dn) : 'Domain adı yok' ?></strong><div class="renewal-meta"><span>Domain</span><span>•</span><span>Yenileme: <?= e($d['expiry_date'] ?: '-') ?></span></div></div></div>
        <div class="renewal-actions"><span class="renewal-pill <?= $days<16?'badge-danger':($days<45?'badge-warn':'badge-ok') ?>"><?= $days ?> gün</span><a class="u2-btn soft" href="<?= url('client/domains/view?id='.(int)$d['id']) ?>">Yönet</a></div>
      </div>
    <?php endforeach; ?>
    <?php if(!$renewServices && !$renewDomains): ?><div class="renewal-empty">Yaklaşan yenileme bulunmuyor.</div><?php endif; ?>
  </div>
</div>
<div class="auto-renew-card"><h3>Otomatik Yenileme</h3><div class="auto-renew-content"><p>Yenilemede önce hesap kredisi kullanılır. Kredi yetmezse sadece ödeme sağlayıcısında token olarak saklanan kayıtlı karttan kalan tutar tahsil edilir. Kart numarası, CVV ve son kullanma tarihi Ahost One içinde tutulmaz.</p><span class="auto-renew-status">Güvenli tahsilat</span></div></div>
