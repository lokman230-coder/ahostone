<?php
$c=current_customer(); $id=(int)($_GET['id']??0); $s=null; $h=null; $flash=get_flash();
try{
 $q=db()->prepare('SELECT s.*,p.name product_name,p.type,p.module_name FROM services s LEFT JOIN products p ON p.id=s.product_id WHERE s.id=? AND s.customer_id=? LIMIT 1');
 $q->execute([$id,$c['id']]); $s=$q->fetch();
 if($s){ $hq=db()->prepare('SELECT * FROM hosting_accounts WHERE service_id=? LIMIT 1'); $hq->execute([$s['id']]); $h=$hq->fetch() ?: []; }
}catch(Throwable $e){}
if(!$s): ?><div class="customer-panel-card"><h2>Hizmet bulunamadı</h2><a class="u2-btn soft" href="<?= url('client/services') ?>">Hizmetlere Dön</a></div><?php return; endif;
$metrics=function_exists('ao_hosting_metric_rows')?ao_hosting_metric_rows($h):[];
$panelPass=(string)($h['panel_password'] ?? '');
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="customer-panel-card premium-detail-hero">
 <div><span class="u2-kicker">Hizmet Detayı</span><h2><?= e($s['product_name']?:'Özel Hizmet') ?></h2><p><?= e($s['domain']) ?> · <?= e(ao_status_tr($s['status'] ?? '')) ?> · Sonraki ödeme: <?= e($s['next_due_date']) ?></p></div>
 <div class="button-row"><a class="u2-btn" href="<?= url('client/support') ?>">Destek Al</a><a class="u2-btn soft" href="<?= url('client/invoices') ?>">Faturalar</a></div>
</div>

<div class="ao-hosting-metrics-grid">
 <?php foreach($metrics as $m): $cls=$m['percent']>=90?'is-danger':($m['percent']>=75?'is-warn':''); ?>
  <div class="ao-hosting-metric <?= $cls ?>"><div class="metric-head"><span><?= e($m['label']) ?></span><i><?= e($m['icon']) ?></i></div><strong><?= (int)$m['percent'] ?>%</strong><div class="ao-hosting-bar"><span style="width:<?= (int)$m['percent'] ?>%"></span></div><small><?= e($m['used']) ?> / <?= e($m['limit'] ?: '∞') ?> <?= e($m['unit']) ?> · Kalan: <?= e($m['limit'] ? $m['left'] : '∞') ?></small></div>
 <?php endforeach; ?>
</div>

<div class="ao-hosting-credential-panel">
 <div class="u2-section-title"><div><h3>Hosting Erişim ve Şifre Yönetimi</h3><p>Şifreyi görüntüleyebilir, yeni şifre üretebilir veya kendi yazdığınız şifreyi sunucuya senkron değiştirebilirsiniz.</p></div><span class="u2-pill blue">Sunucu Senkron</span></div>
 <div class="ao-credential-grid">
  <div><small>Panel Türü</small><strong><?= e($h['panel_type'] ?? $s['module_name'] ?? 'cPanel / DirectAdmin') ?></strong></div>
  <div><small>Kullanıcı Adı</small><strong><?= e($h['username'] ?? $h['whm_username'] ?? $h['panel_username'] ?? 'demo_user') ?></strong></div>
  <div><small>Sunucu / Hostname</small><strong><?= e($h['server_hostname'] ?? $h['server_name'] ?? $h['hostname'] ?? 'server.demo.ahostone.test') ?></strong></div>
  <div><small>IP Adresi</small><strong><?= e($h['server_ip'] ?? $h['ip_address'] ?? '-') ?></strong></div>
  <div><small>Nameserver</small><strong><?= e($h['ns1'] ?? 'ns1.demo.ahostone.test') ?></strong><strong><?= e($h['ns2'] ?? 'ns2.demo.ahostone.test') ?></strong></div>
 </div>
 <form method="post" action="<?= url('client/services/password-update') ?>" class="ao-password-line" data-password-tool>
  <?= csrf_field() ?><input type="hidden" name="service_id" value="<?= (int)$s['id'] ?>">
  <input type="password" name="panel_password" value="<?= e($panelPass) ?>" autocomplete="new-password" placeholder="Yeni hosting şifresi">
  <button type="button" class="ao-mini-btn" data-toggle-password>Göster</button>
  <button type="button" class="ao-mini-btn" data-generate-password>Üret</button>
  <button class="ao-hosting-action-btn primary">Değiştir</button>
 </form>
</div>

<div class="customer-panel-card">
 <div class="u2-section-title"><div><h3>Panel Kısayolları</h3><p>Servis araçlarına tek tıkla erişin.</p></div><span class="u2-pill green">Aktif</span></div>
 <div class="cp-shortcut-grid">
  <a target="_blank" href="<?= url('client/service-panel-login?service_id='.(int)$s['id'].'&panel=cpanel') ?>">cPanel Giriş</a>
  <a target="_blank" href="<?= url('client/service-panel-login?service_id='.(int)$s['id'].'&panel=directadmin') ?>">DirectAdmin Giriş</a>
  <a target="_blank" href="<?= url('client/service-panel-login?service_id='.(int)$s['id'].'&panel=webmail') ?>">Webmail</a>
  <a target="_blank" href="<?= url('client/service-panel-login?service_id='.(int)$s['id'].'&panel=whm') ?>">WHM</a>
  <a target="_blank" href="<?= url('client/service-panel-login?service_id='.(int)$s['id'].'&panel=vps') ?>">VPS Panel</a>
  <a href="#mail-create">Mail Oluştur</a><a href="#dns-ssl">DNS / SSL</a><a href="#backup">Yedekler</a>
 </div>
</div>
<script id="aoHostingPasswordToolV2469">
(function(){function gen(){var c='abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';var p='';for(var i=0;i<16;i++)p+=c[Math.floor(Math.random()*c.length)];return p;}document.addEventListener('click',function(e){var t=e.target.closest('[data-toggle-password]');if(t){var f=t.closest('[data-password-tool]'),i=f&&f.querySelector('input[name="panel_password"]');if(i){i.type=i.type==='password'?'text':'password';t.textContent=i.type==='password'?'Göster':'Gizle';}}var g=e.target.closest('[data-generate-password]');if(g){var f2=g.closest('[data-password-tool]'),i2=f2&&f2.querySelector('input[name="panel_password"]');if(i2){i2.type='text';i2.value=gen();var b=f2.querySelector('[data-toggle-password]');if(b)b.textContent='Gizle';}}});})();
</script>
