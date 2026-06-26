<?php
$domains = [];
try { $domains = db()->query("SELECT d.*, c.first_name, c.last_name FROM domains d LEFT JOIN customers c ON c.id=d.customer_id ORDER BY d.id DESC LIMIT 200")->fetchAll(); } catch(Throwable $e) {}
$stats = ['active'=>0,'expired'=>0,'transfer_pending'=>0,'pending'=>0];
foreach($domains as $d) { $s=$d['status']??''; if(isset($stats[$s])) $stats[$s]++; }
?>
<div class="ao-page-head">
    <div><h2>Domain Center Pro</h2><p>Domain kayıt, transfer, yenileme, WHOIS, DNS, nameserver, EPP ve registrar yönetimi.</p></div>
    <div class="ao-actions no-margin"><a class="ao-btn" href="<?= url('domain') ?>">+ Domain Sorgula</a><a class="ao-btn soft" href="<?= url('admin/domain-center/registrars') ?>">Registrarlar</a></div>
</div>
<div class="ao-stats-grid"><div class="ao-stat"><span>Toplam Domain</span><strong><?= count($domains) ?></strong></div><div class="ao-stat"><span>Aktif</span><strong><?= $stats['active'] ?></strong></div><div class="ao-stat"><span>Transfer Bekliyor</span><strong><?= $stats['transfer_pending'] ?></strong></div><div class="ao-stat"><span>Bekleyen</span><strong><?= $stats['pending'] ?></strong></div></div>
<div class="ao-card">
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;"><h3 style="margin:0;margin-right:auto">Domain Listesi</h3><a href="<?= url('admin/domain-center/sync-all') ?>" class="ao-mini-btn">Tüm Domainleri Güncelle</a><a href="<?= url('admin/domain-center/transfers') ?>" class="ao-mini-btn">Transferler</a><a href="<?= url('admin/domain-center/pricing') ?>" class="ao-mini-btn">TLD Fiyatları</a></div>
    <table class="ao-table"><thead><tr><th>Domain</th><th>Müşteri</th><th>Registrar</th><th>Kayıt</th><th>Bitiş</th><th>Kalan</th><th>Auto Renew</th><th>Kilit</th><th>Son Senkron</th><th>Durum</th><th>İşlem</th></tr></thead><tbody>
        <?php foreach($domains as $d):
          $exp=!empty($d['expiry_date'])?strtotime($d['expiry_date']):0; $days=$exp?floor(($exp-time())/86400):null;
          $dayClass=$days===null?'domain-days-warn':($days>=30?'domain-days-ok':($days>=15?'domain-days-warn':($days>=7?'domain-days-orange':'domain-days-danger')));
          $registrar=$d['registrar']??($d['registrar_name']??(!empty($d['registrar_id'])?'Registrar #'.(int)$d['registrar_id']:'DomainNameAPI'));
        ?><tr><td><strong><?= e($d['domain_name']) ?></strong></td><td><?= e(trim(($d['first_name']??'').' '.($d['last_name']??''))) ?></td><td><?= e($registrar ?: 'DomainNameAPI') ?></td><td><small><?= e(substr($d['registration_date']??'',0,10)) ?></small></td><td><small><?= e(substr($d['expiry_date']??'',0,10)) ?></small></td><td><span class="domain-sync-pill <?= $dayClass ?>"><?= $days===null?'-':((int)$days.' gün') ?></span></td><td><?= !empty($d['auto_renew'])?'Açık':'Kapalı' ?></td><td><?= !empty($d['lock_status'])?'Kilitli':'Açık' ?></td><td><small><?= e($d['last_synced_at']??($d['updated_at']??'-')) ?></small></td><td><span class="ao-badge <?= e($d['status']??'') ?>"><?= e($d['status']??'-') ?></span></td><td><div class="ao-mini-dropdown"><a class="ao-mini-btn" href="<?= url('admin/domain-center/view?id='.(int)$d['id']) ?>">Yönet ▾</a><div class="ao-mini-dropdown-menu"><a href="<?= url('admin/domain-center/view?id='.(int)$d['id']) ?>">Domain Detayı</a><a href="<?= url('admin/domain-center/sync?id='.(int)$d['id']) ?>">Registrar Senkronize Et</a><a href="<?= url('admin/domain-center/operations?domain_id='.(int)$d['id'].'&op=nameserver') ?>">Nameserver</a><a href="<?= url('admin/domain-center/operations?domain_id='.(int)$d['id'].'&op=lock') ?>">Transfer Kilidi</a><a href="<?= url('admin/domain-center/operations?domain_id='.(int)$d['id'].'&op=epp') ?>">EPP Kodu</a><a href="<?= url('admin/domain-center/operations?domain_id='.(int)$d['id'].'&op=dns') ?>">DNS Yönetimi</a></div></div></td></tr><?php endforeach; if(!$domains): ?><tr><td colspan="11">Domain bulunamadı.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div class="ao-grid two"><div class="ao-card"><h3>Domain Sorgulama</h3><p>Müsaitlik kontrolü, TLD fiyatı ve hızlı satın alma ekranı site ön yüzüyle bağlanacak.</p><a class="ao-btn" href="<?= url('domain') ?>">Domain Sorgula</a></div><div class="ao-card"><h3>Registrar Bağlantısı</h3><p>DomainNameAPI ve diğer registrarlar için yapılandırma alanları hazır.</p><a class="ao-btn soft" href="<?= url('admin/domain-center/registrars') ?>">Yapılandır</a></div></div>
