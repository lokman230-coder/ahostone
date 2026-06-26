<?php $flash=get_flash(); $pending=$_SESSION['mfa_pending'] ?? null; if(!$pending){ flash('error','Bekleyen doğrulama bulunamadı.'); redirect_to('client/login'); } $method=$pending['method'] ?? 'mail'; $label=['mail'=>'Mail OTP','sms'=>'SMS OTP','totp'=>'Google Authenticator'][$method] ?? strtoupper($method); $secret=''; $otpauth=''; if($method==='totp'){ $secret=ao_mfa_get_totp_secret($pending['user_type'], (int)$pending['user_id'], true); $issuer=urlencode(admin_setting('site_name','Ahost One')); $account=urlencode(($pending['user_type']==='admin'?'Admin':'Müşteri').':'.($pending['email']??$pending['user_id'])); $otpauth='otpauth://totp/'.$issuer.':'.$account.'?secret='.$secret.'&issuer='.$issuer; } ?>
<div class="auth-shell">
  <div class="auth-card mfa-card">
    <h1>Giriş Doğrulama</h1>
    <p class="muted">Hesabınız için ek güvenlik doğrulaması gerekiyor.</p>
    <?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <div class="mfa-method-box"><b><?= e($label) ?></b><?php if($method==='mail'): ?><span><?= e($pending['email'] ?? '') ?> adresine kod gönderildi.</span><?php endif; ?><?php if($method==='sms'): ?><span><?= e($pending['phone'] ?? '') ?> numarasına kod gönderildi.</span><?php endif; ?></div>
    <?php if($method==='totp'): ?>
      <div class="mfa-totp-setup">
        <p>Google Authenticator, Microsoft Authenticator veya benzeri uygulamaya aşağıdaki gizli anahtarı ekleyin.</p>
        <code><?= e($secret) ?></code>
        <small>URI: <?= e($otpauth) ?></small>
      </div>
    <?php endif; ?>
    <form method="post" action="<?= url('auth/mfa/verify') ?>" class="auth-form">
      <?= csrf_field() ?>
      <label>6 Haneli Kod<input name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" autofocus></label>
      <button class="auth-btn">Doğrula ve Giriş Yap</button>
    </form>
    <div class="mfa-actions">
      <?php if($method==='mail' || $method==='sms'): ?><a href="<?= url('auth/mfa/resend?csrf_token='.urlencode(csrf_token())) ?>">Kodu tekrar gönder</a><?php endif; ?>
      <a href="<?= url('auth/mfa/cancel') ?>">İptal et</a>
    </div>
  </div>
</div>
<style>
.mfa-card{max-width:520px}.mfa-method-box{display:grid;gap:6px;padding:14px;border:1px solid rgba(15,23,42,.12);border-radius:14px;background:#f8fafc;margin:14px 0}.mfa-method-box b{color:#0f172a}.mfa-method-box span,.mfa-totp-setup p,.mfa-totp-setup small{color:#64748b}.mfa-totp-setup{padding:14px;border-radius:14px;background:#0f172a;color:white;margin-bottom:14px}.mfa-totp-setup code{display:block;background:rgba(255,255,255,.12);padding:12px;border-radius:10px;letter-spacing:2px;margin:8px 0}.mfa-actions{display:flex;justify-content:space-between;margin-top:14px;gap:10px}.mfa-actions a{color:#2563eb;text-decoration:none;font-weight:700}
</style>
