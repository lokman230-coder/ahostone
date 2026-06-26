<?php
$flash=get_flash(); $customer=current_customer(); $users=[]; $logs=[];
try{ if(function_exists('ao_v2332_ensure_schema')) ao_v2332_ensure_schema(); $q=db()->prepare('SELECT * FROM customer_account_users WHERE customer_id=? ORDER BY id DESC'); $q->execute([(int)$customer['id']]); $users=$q->fetchAll() ?: []; $l=db()->prepare('SELECT * FROM customer_user_activity_logs WHERE customer_id=? ORDER BY id DESC LIMIT 8'); $l->execute([(int)$customer['id']]); $logs=$l->fetchAll() ?: []; }catch(Throwable $e){}
$roles=['full'=>'Tam Yetkili','billing'=>'Muhasebe','technical'=>'Teknik Yetkili','domain'=>'Domain Yetkilisi','hosting'=>'Hosting Yetkilisi','viewer'=>'Sadece Görüntüleme'];
$permNames=['invoices.view'=>'Faturaları Gör','invoices.pay'=>'Fatura Öde','tickets.open'=>'Ticket Aç','tickets.view'=>'Ticket Gör','hosting.manage'=>'Hosting Yönet','domains.manage'=>'Domain Yönet','dns.manage'=>'DNS Yönet','orders.create'=>'Sipariş Ver','profile.edit'=>'Profil Düzenle','users.manage'=>'Kullanıcı Yönet'];
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="client-grid two">
  <div class="client-card">
    <h3>Hesap Kullanıcıları</h3>
    <p class="muted">Firma hesabınıza muhasebe, teknik destek, domain/hosting yetkilisi veya sadece görüntüleme kullanıcısı ekleyin.</p>
    <form method="post" action="<?= url('client/account-users/save') ?>" class="client-form">
      <?= csrf_field() ?>
      <label>Ad Soyad<input name="name" required placeholder="Ayşe Yılmaz"></label>
      <label>E-posta<input type="email" name="email" required placeholder="ornek@firma.com"></label>
      <label>Telefon<input name="phone" placeholder="05xx xxx xx xx"></label>
      <label>Yetki Şablonu<select name="role_key"><?php foreach($roles as $k=>$v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?></select></label>
      <button class="customer-action">Davet Oluştur</button>
    </form>
  </div>
  <div class="client-card">
    <h3>Yetki Şablonları ve Güvenlik</h3>
    <div class="role-list"><?php foreach($roles as $k=>$v): $perms=function_exists('ao_v2332_customer_user_permissions')?ao_v2332_customer_user_permissions($k):[]; ?><div><strong><?= e($v) ?></strong><small><?= e(implode(', ', array_map(fn($x)=>$permNames[$x]??$x,$perms))) ?></small></div><?php endforeach; ?></div>
  </div>
</div>

<div class="client-card">
  <h3>Ek Kullanıcılar</h3>
  <div class="table-wrap"><table class="client-table"><thead><tr><th>Kullanıcı</th><th>Rol</th><th>Yetkiler</th><th>Durum</th><th>Güvenlik</th><th>İşlem</th></tr></thead><tbody>
  <?php foreach($users as $u): $perms=json_decode($u['permissions_json']??'[]',true) ?: []; ?><tr><td><strong><?= e($u['name']) ?></strong><br><small><?= e($u['email']) ?> <?= $u['phone']?' · '.e($u['phone']):'' ?></small></td><td><?= e($roles[$u['role_key']] ?? $u['role_key']) ?></td><td><small><?= e(implode(', ', array_map(fn($x)=>$permNames[$x]??$x,$perms))) ?></small></td><td><span class="status <?= e($u['status']) ?>"><?= e($u['status']) ?></span></td><td><?= !empty($u['twofa_enabled'])?'2FA Zorunlu':'2FA Kapalı' ?></td><td><a href="<?= url('client/account-users/toggle?id='.(int)$u['id'].'&csrf_token='.csrf_token()) ?>">Aktif/Pasif</a> · <a href="<?= url('client/account-users/2fa-toggle?id='.(int)$u['id'].'&csrf_token='.csrf_token()) ?>">2FA <?= !empty($u['twofa_enabled'])?'Kapat':'Aç' ?></a> · <a href="<?= url('client/account-users/resend?id='.(int)$u['id'].'&csrf_token='.csrf_token()) ?>">Davet Yenile</a> · <a onclick="return confirm('Kullanıcı silinsin mi?')" href="<?= url('client/account-users/delete?id='.(int)$u['id'].'&csrf_token='.csrf_token()) ?>">Sil</a></td></tr><?php endforeach; ?>
  <?php if(!$users): ?><tr><td colspan="6">Henüz ek kullanıcı yok.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>

<div class="client-card"><h3>Kullanıcı İşlem Logları</h3><?php if($logs): ?><ul class="log-list"><?php foreach($logs as $log): ?><li><strong><?= e($log['action']) ?></strong> <?= e($log['description']) ?> <small><?= e($log['created_at']) ?></small></li><?php endforeach; ?></ul><?php else: ?><p class="muted">Henüz işlem kaydı yok.</p><?php endif; ?></div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
<div class="client-card">
  <h3>Yetki Düzenle</h3>
  <p class="muted">Alt kullanıcı için modül bazlı gerçek izinleri seçin. Boş bırakılırsa yetki şablonu kullanılır.</p>
  <?php foreach($users as $u): $current=json_decode($u['custom_permissions'] ?? '[]', true); if(!is_array($current)) $current=[]; ?>
  <form method="post" action="<?= url('client/account-users/permissions-save') ?>" class="client-form" style="border:1px solid #e5edf8;border-radius:18px;padding:16px;margin:12px 0">
    <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
    <strong><?= e($u['name'] ?? $u['email']) ?></strong>
    <div class="role-list">
      <?php foreach($permNames as $pk=>$pv): ?>
        <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="permissions[]" value="<?= e($pk) ?>" <?= in_array($pk,$current,true)?'checked':'' ?>> <?= e($pv) ?></label>
      <?php endforeach; ?>
    </div>
    <button class="customer-action">Yetkileri Kaydet</button>
  </form>
  <?php endforeach; if(!$users): ?><p class="muted">Önce alt kullanıcı daveti oluşturun.</p><?php endif; ?>
</div>
