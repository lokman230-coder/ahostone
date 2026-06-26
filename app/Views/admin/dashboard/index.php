<?php
function v22_count($table,$where='1=1') { try { return (int)db()->query("SELECT COUNT(*) FROM `$table` WHERE $where")->fetchColumn(); } catch(Throwable $e) { return 0; } }
function v22_sum($table,$col,$where='1=1') { try { return (float)db()->query("SELECT COALESCE(SUM($col),0) FROM `$table` WHERE $where")->fetchColumn(); } catch(Throwable $e) { return 0; } }
function v22_rows($sql) { try { $q=db()->query($sql); return $q ? $q->fetchAll() : []; } catch(Throwable $e) { return []; } }
function v22_money($v){ return number_format((float)$v,2,',','.').' ₺'; }
function v22_service_count_like($needles){
  $parts=[]; foreach((array)$needles as $n){ $n=str_replace("'","''",$n); $parts[]="LOWER(COALESCE(name,'')) LIKE '%$n%'"; $parts[]="LOWER(COALESCE(type,'')) LIKE '%$n%'"; }
  return v22_count('services', $parts ? '('.implode(' OR ',$parts).')' : '1=0');
}
function v2470_date_value($row){
  foreach(['next_due_date','expiry_date','expires_at','renewal_date','due_date'] as $k){
    if(!empty($row[$k]) && substr((string)$row[$k],0,10)!=='0000-00-00') return substr((string)$row[$k],0,10);
  }
  return '';
}
function v2470_days_left($date){
  if(!$date) return null;
  try{
    $today=new DateTimeImmutable('today');
    $target=new DateTimeImmutable(substr((string)$date,0,10));
    return (int)$today->diff($target)->format('%r%a');
  }catch(Throwable $e){ return null; }
}
function v2470_days_badge_class($days){
  if($days===null) return 'muted';
  if($days < 0) return 'red';
  if($days <= 7) return 'red';
  if($days <= 15) return 'orange';
  if($days <= 30) return 'yellow';
  return 'green';
}
function v2470_days_label($days){
  if($days===null) return '-';
  if($days < 0) return abs($days).' gün gecikmiş';
  if($days === 0) return 'Bugün';
  return $days.' gün';
}
function v2470_customer_name($row){
  $name=trim((string)($row['customer_name'] ?? ''));
  if($name!=='') return $name;
  $name=trim((string)($row['first_name'] ?? '').' '.(string)($row['last_name'] ?? ''));
  if($name!=='') return $name;
  return trim((string)($row['company_name'] ?? '')) ?: '-';
}
function v2470_customer_link($row){
  $id=(int)($row['customer_id'] ?? 0);
  $name=v2470_customer_name($row);
  return $id>0 ? '<a class="ao-smart-link" href="'.e(url('admin/customers/view?id='.$id)).'">'.e($name).'</a>' : e($name);
}
function v2470_status_badge($status){
  $raw=(string)($status ?: '-');
  $st=strtolower($raw);
  $tr=function_exists('ao_status_tr') ? ao_status_tr($raw) : $raw;
  $cls= in_array($st,['paid','completed','active','aktif'],true) ? 'green' : (in_array($st,['pending','beklemede','unpaid'],true) ? 'orange' : (in_array($st,['cancelled','canceled','terminated','overdue','suspended'],true) ? 'red' : 'muted'));
  return '<span class="v21-badge '.$cls.'">'.e($tr).'</span>';
}
function v2470_domain_name($row){
  if(function_exists('ao_domain_display_name')){
    $dn=ao_domain_display_name($row);
    if($dn!=='') return $dn;
  }
  if(!empty($row['domain_name'])) return (string)$row['domain_name'];
  if(!empty($row['domain'])) return (string)$row['domain'];
  if(!empty($row['sld']) || !empty($row['tld'])) return trim((string)($row['sld'] ?? '').'.'.ltrim((string)($row['tld'] ?? ''),'.'),'.');
  return '';
}

$customers=v22_count('customers');
$services=v22_count('services');
$orders=v22_count('orders');
$domains=v22_count('domains');
$hosting=v22_service_count_like(['hosting','cpanel','directadmin','plesk','vps','sunucu']);
$sitebuilder=v22_service_count_like(['sitebuilder','site builder','builder']);
$mobilebuilder=v22_service_count_like(['mobilebuilder','mobile builder','android','ios','mobil']);
$webdesign=v22_service_count_like(['web tasarım','web design','kurumsal web']);
$revenue=v22_sum('invoices','total',"status='paid'");
$pendingOrders=v22_count('orders',"status='pending'");
$openTickets=v22_count('tickets',"status IN ('open','pending')");
$unpaidInvoices=v22_count('invoices',"status<>'paid'");

$recentOrders=v22_rows("SELECT o.*, CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,'')) customer_name, c.company_name
 FROM orders o LEFT JOIN customers c ON c.id=o.customer_id ORDER BY o.id DESC LIMIT 6");

$recentTickets=v22_rows("SELECT t.*, CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,'')) customer_name, c.company_name
 FROM tickets t LEFT JOIN customers c ON c.id=t.customer_id ORDER BY t.id DESC LIMIT 5");

$recentDomains=v22_rows("SELECT d.*, CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,'')) customer_name, c.company_name
 FROM domains d LEFT JOIN customers c ON c.id=d.customer_id
 ORDER BY COALESCE(d.next_due_date,d.expiry_date,'2999-12-31') ASC, d.id DESC LIMIT 7");

$recentHosting=v22_rows("SELECT s.id service_id, s.customer_id, s.domain, s.status, s.next_due_date, s.billing_cycle,
   p.name product_name, p.type product_type,
   ha.username, ha.package_name, ha.server_name,
   CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,'')) customer_name, c.company_name
 FROM services s
 LEFT JOIN customers c ON c.id=s.customer_id
 LEFT JOIN products p ON p.id=s.product_id
 LEFT JOIN hosting_accounts ha ON ha.service_id=s.id
 WHERE (LOWER(COALESCE(p.type,'')) LIKE '%hosting%' OR LOWER(COALESCE(p.name,'')) LIKE '%hosting%' OR LOWER(COALESCE(ha.package_name,''))<>'' OR LOWER(COALESCE(s.domain,''))<>'')
 ORDER BY COALESCE(s.next_due_date,'2999-12-31') ASC, s.id DESC LIMIT 7");
?>
<div class="v22-hero ao-card">
  <div><span class="v22-pill">Ahost One v25 RC23 Dashboard</span><h2>Kontrol Paneli</h2><p>Hosting, domain, sipariş, fatura ve destek kayıtlarını tek ekrandan hızlıca yönet.</p></div>
  <div class="v22-hero-actions"><a class="ao-btn" href="<?= url('admin/orders/new') ?>">+ Sipariş Oluştur</a><a class="ao-btn soft" href="<?= url('admin/setup-wizard') ?>">Kurulum Kontrolü</a></div>
</div>

<div class="v22-kpi-grid">
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/customers') ?>"><i>👥</i><small>Müşteriler</small><strong><?= $customers ?></strong><span>Yönet</span></a>
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/domain-center') ?>"><i>🌐</i><small>Domainler</small><strong><?= $domains ?></strong><span>Domain Center</span></a>
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/hosting-server') ?>"><i>🖥</i><small>Hosting/Sunucu</small><strong><?= $hosting ?></strong><span>Hosting Center</span></a>
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/site-builder') ?>"><i>🎨</i><small>SiteBuilder</small><strong><?= $sitebuilder ?></strong><span>Projeler</span></a>
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/mobile-builder') ?>"><i>📱</i><small>MobileBuilder</small><strong><?= $mobilebuilder ?></strong><span>Uygulamalar</span></a>
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/product-center') ?>"><i>💻</i><small>Web Tasarım</small><strong><?= $webdesign ?></strong><span>Hizmetler</span></a>
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/accounting') ?>"><i>💰</i><small>Tahsil Edilen</small><strong><?= v22_money($revenue) ?></strong><span>Finans</span></a>
 <a class="ao-card v22-kpi v2470-kpi-link" href="<?= url('admin/support/tickets?status=open') ?>"><i>🎧</i><small>Açık Ticket</small><strong><?= $openTickets ?></strong><span>Destek</span></a>
</div>

<div class="v2470-dashboard-grid">
 <div class="ao-card v22-panel">
  <div class="v22-panel-head"><h3>Son Siparişler</h3><a href="<?= url('admin/orders') ?>">Tümünü Gör →</a></div>
  <?php if($recentOrders): ?><table class="v21-table v2470-click-table"><tr><th>Sipariş</th><th>Müşteri</th><th>Tutar</th><th>Durum</th><th>Tarih</th></tr>
  <?php foreach($recentOrders as $o): $st=strtolower($o['status']??''); ?>
   <tr>
    <td><a class="ao-smart-link strong" href="<?= url('admin/orders/view?id='.(int)$o['id']) ?>">#<?= e($o['order_number'] ?? (int)$o['id']) ?></a></td>
    <td><?= v2470_customer_link($o) ?></td>
    <td><a class="ao-smart-link" href="<?= url('admin/orders/view?id='.(int)$o['id']) ?>"><?= v22_money($o['total'] ?? 0) ?></a></td>
    <td><a href="<?= url('admin/orders?status='.urlencode($o['status'] ?? '')) ?>"><?= v2470_status_badge($o['status'] ?? '-') ?></a></td>
    <td><?= e(substr($o['created_at'] ?? '',0,10)) ?></td>
   </tr>
  <?php endforeach; ?></table><?php else: ?><div class="v21-empty">Henüz sipariş kaydı yok. Demo veri kullanılmadı.</div><?php endif; ?>
 </div>

 <div class="ao-card v22-panel">
  <div class="v22-panel-head"><h3>Yaklaşan / Son Domainler</h3><a href="<?= url('admin/domain-center') ?>">Domain Center →</a></div>
  <?php if($recentDomains): ?><table class="v21-table v2470-click-table"><tr><th>Domain</th><th>Durum</th><th>Yenileme</th><th>Kalan</th></tr>
  <?php foreach($recentDomains as $d): $date=v2470_date_value($d); $days=v2470_days_left($date); $dn=v2470_domain_name($d); if($dn==='') continue; ?>
   <tr>
    <td><a class="ao-smart-link strong" href="<?= url('admin/domain-center/view?id='.(int)$d['id']) ?>"><?= e($dn) ?></a><small><?= v2470_customer_link($d) ?></small></td>
    <td><a href="<?= url('admin/domain-center?status='.urlencode($d['status'] ?? '')) ?>"><?= v2470_status_badge($d['status'] ?? '-') ?></a></td>
    <td><?= e($date ?: '-') ?></td>
    <td><span class="v2470-days <?= e(v2470_days_badge_class($days)) ?>"><?= e(v2470_days_label($days)) ?></span></td>
   </tr>
  <?php endforeach; ?></table><?php else: ?><div class="v21-empty">Henüz domain kaydı yok.</div><?php endif; ?>
 </div>

 <div class="ao-card v22-panel">
  <div class="v22-panel-head"><h3>Yaklaşan / Son Hostingler</h3><a href="<?= url('admin/hosting-server/accounts') ?>">Hostingler →</a></div>
  <?php if($recentHosting): ?><table class="v21-table v2470-click-table"><tr><th>Hizmet</th><th>Müşteri</th><th>Durum</th><th>Sonraki Ödeme</th><th>Kalan</th></tr>
  <?php foreach($recentHosting as $h): $date=v2470_date_value($h); $days=v2470_days_left($date); $serviceName=trim((string)($h['product_name'] ?? '')) ?: (trim((string)($h['domain'] ?? '')) ?: 'Hosting Hizmeti'); ?>
   <tr>
    <td><a class="ao-smart-link strong" href="<?= url('admin/hosting-server/accounts?service_id='.(int)$h['service_id']) ?>"><?= e($serviceName) ?></a><small><?= e(trim((string)($h['domain'] ?? '')) ?: trim((string)($h['package_name'] ?? ''))) ?></small></td>
    <td><?= v2470_customer_link($h) ?></td>
    <td><a href="<?= url('admin/hosting-server/accounts?status='.urlencode($h['status'] ?? '')) ?>"><?= v2470_status_badge($h['status'] ?? '-') ?></a></td>
    <td><?= e($date ?: '-') ?></td>
    <td><span class="v2470-days <?= e(v2470_days_badge_class($days)) ?>"><?= e(v2470_days_label($days)) ?></span></td>
   </tr>
  <?php endforeach; ?></table><?php else: ?><div class="v21-empty">Yaklaşan hosting kaydı yok.</div><?php endif; ?>
 </div>
</div>

<div class="v22-bottom-grid v2470-bottom-grid">
 <div class="ao-card v22-panel">
  <div class="v22-panel-head"><h3>Destek Talepleri</h3><a href="<?= url('admin/support/tickets') ?>">Talepler →</a></div>
  <?php if($recentTickets): ?><div class="v21-activity v2470-activity">
    <?php foreach($recentTickets as $t): ?>
    <div>
      <i>🎧</i>
      <p>
        <b><a class="ao-smart-link strong" href="<?= url('admin/support/tickets?ticket='.(int)$t['id']) ?>">#<?= (int)$t['id'] ?> <?= e($t['subject'] ?? 'Destek talebi') ?></a></b>
        <span><?= v2470_customer_link($t) ?> · <a href="<?= url('admin/support/tickets?status='.urlencode($t['status'] ?? '')) ?>"><?= e(function_exists('ao_status_tr') ? ao_status_tr($t['status'] ?? '-') : ($t['status'] ?? '-')) ?></a></span>
      </p>
    </div>
    <?php endforeach; ?>
  </div><?php else: ?><div class="v21-empty">Açık destek talebi bulunmuyor.</div><?php endif; ?>
 </div>
 <div class="ao-card v22-panel v22-quick-card"><div class="v22-panel-head"><h3>Hızlı İşlemler</h3></div><div class="v21-quick"><a href="<?= url('admin/customers/add') ?>">👥<br>Müşteri Ekle</a><a href="<?= url('admin/orders/new') ?>">🛒<br>Sipariş</a><a href="<?= url('admin/domain-center') ?>">🌐<br>Domain</a><a href="<?= url('admin/site-builder') ?>">🎨<br>SiteBuilder</a><a href="<?= url('admin/mobile-builder') ?>">📱<br>MobileBuilder</a><a href="<?= url('admin/menu-manager') ?>">☰<br>Menüler</a></div></div>
 <div class="ao-card v22-panel v22-market"><h3>SiteBuilder + MobileBuilder</h3><p>Kullanıcıların web sitesi ve mobil uygulama projelerini tek ekosistemde yönetiriz.</p><div><a class="ao-btn" href="<?= url('admin/site-builder') ?>">SiteBuilder'a Git</a><a class="ao-btn soft" href="<?= url('admin/mobile-builder') ?>">MobileBuilder'a Git</a></div></div>
 <div class="ao-card v22-panel"><div class="v22-panel-head"><h3>Sistem Özeti</h3><a href="<?= url('admin/scan-report') ?>">Tarama →</a></div><div class="v22-mini-stats"><a href="<?= url('admin/orders?status=pending') ?>">Bekleyen Sipariş <b><?= $pendingOrders ?></b></a><a href="<?= url('admin/accounting/invoices?status=unpaid') ?>">Bekleyen Fatura <b><?= $unpaidInvoices ?></b></a><a href="<?= url('admin/hosting-server/accounts?status=active') ?>">Aktif Hizmet <b><?= $services ?></b></a></div></div>
</div>
