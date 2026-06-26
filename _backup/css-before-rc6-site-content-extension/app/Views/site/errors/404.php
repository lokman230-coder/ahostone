<section class="ao-404-pro">
  <div class="ao-404-card">
    <span class="eyebrow">Ahost One 404 Pro</span>
    <h1>Kaybolmadınız, sizi doğru hizmete yönlendirelim.</h1>
    <p>Aradığınız sayfa taşınmış, silinmiş veya bağlantı hatalı yazılmış olabilir.</p>
    <form class="ao-404-domain" action="<?= url('domain') ?>" method="get">
      <input name="q" placeholder="Domain adınızı sorgulayın: heryertatil.com">
      <button class="btn primary">Sorgula</button>
    </form>
    <div class="ao-404-actions">
      <a class="btn" href="<?= url('') ?>">Ana Sayfa</a>
      <a class="btn" href="<?= url('domain') ?>">Domain Sorgula</a>
      <a class="btn" href="<?= url('hosting') ?>">Hosting Paketleri</a>
      <a class="btn" href="<?= url('marketplace') ?>">Marketplace</a>
      <a class="btn" href="<?= url('client') ?>">Müşteri Paneli</a>
    </div>
    <div class="ao-404-suggestions">
      <strong>Belki bunları arıyordunuz:</strong>
      <span>Domain sorgulama</span><span>Hosting</span><span>VPS</span><span>Destek</span><span>Bilgi Bankası</span>
    </div>
    <?php if(function_exists('current_admin') && current_admin()): ?>
      <div class="ao-404-admin">Admin notu: Bu 404 loglanabilir. URL: <?= e($_SERVER['REQUEST_URI'] ?? '') ?></div>
    <?php endif; ?>
  </div>
</section>
<style>.ao-404-pro{min-height:70vh;display:flex;align-items:center;justify-content:center;padding:60px 20px;background:radial-gradient(circle at top left,#e0f2fe,#f8fafc 45%,#eef2ff)}.ao-404-card{max-width:980px;background:#fff;border:1px solid #dbe5f2;border-radius:32px;padding:42px;box-shadow:0 30px 80px #0f172a18}.ao-404-card h1{font-size:clamp(32px,5vw,64px);line-height:1.05;margin:12px 0}.ao-404-card p{font-size:20px;color:#64748b}.ao-404-domain{display:flex;gap:12px;margin:24px 0}.ao-404-domain input{flex:1;border:1px solid #dbe5f2;border-radius:18px;padding:16px;font-weight:800}.ao-404-actions{display:flex;gap:10px;flex-wrap:wrap;margin:20px 0}.ao-404-suggestions{display:flex;gap:10px;flex-wrap:wrap;color:#64748b}.ao-404-suggestions span{background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:8px 12px;font-weight:800}.ao-404-admin{margin-top:20px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:16px;padding:12px;font-weight:800}@media(max-width:720px){.ao-404-domain{flex-direction:column}.ao-404-card{padding:26px}}</style>
