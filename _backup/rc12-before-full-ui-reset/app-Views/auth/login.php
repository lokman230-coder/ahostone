<h1>Müşteri Girişi</h1>
<p>Hizmetlerinizi, domainlerinizi, faturalarınızı ve destek taleplerinizi yönetmek için giriş yapın.</p>
<form method="post">
  <label>E-posta</label>
  <input type="email" name="email" placeholder="ornek@firma.com" required>
  <label>Şifre</label>
  <input type="password" name="password" placeholder="••••••••" required>
  <button type="submit">Giriş Yap</button>
</form>
<div class="auth-links"><a href="<?= url('client/register') ?>">Kayıt Ol</a><a href="<?= url('client/forgot-password') ?>">Şifremi Unuttum</a><a href="<?= url('') ?>">Siteye Dön</a></div>
