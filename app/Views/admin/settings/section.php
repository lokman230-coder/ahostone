<?php require __DIR__.'/_helpers.php'; $section=$section ?? 'general'; $sections=ao_settings_sections(); $info=$sections[$section] ?? $sections['general']; $flash=get_flash(); ?>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><h2>Ayarlar Merkezi</h2><p>Ana bölümler ayrı sayfa, alt ayarlar sekmeli yapıdadır. Tek kalabalık ayar sayfası kaldırıldı.</p></div></div>
<?php ao_render_settings_nav($section); ?>
<div class="ao-card">
  <h3><?= e($info[0]) ?></h3><p><?= e($info[1]) ?></p>
  <?php if($section==='general'): ?>
    <?php if(admin_setting('production_mode','0')==='1'): ?><div class="ao-mode-live">🟢 Canlı Ortam Aktif</div><?php else: ?><div class="ao-mode-warn">🟠 Sistem test/sandbox modunda. Gerçek ödeme alınamaz.</div><?php endif; ?>
  <?php endif; ?>
  <?php if($section==='ai'): ?>
    <div class="ao-mode-warn"><b>API Key Alma Rehberi:</b> AI Center → Yardım bölümünde OpenAI, Gemini, Claude, DeepSeek ve Grok için adım adım kurulum anlatımı gösterilecek.</div>
  <?php endif; ?>
  <form method="post" action="<?= url('admin/settings/save-section') ?>">
    <?= csrf_field() ?><input type="hidden" name="section" value="<?= e($section) ?>">
    <div class="ao-settings-form"><?php foreach(ao_setting_specs($section) as $spec) ao_setting_input($spec); ?></div>
    <div class="ao-actions"><button class="ao-btn">Değişiklikleri Kaydet</button><a class="ao-btn secondary" href="<?= url('admin/settings') ?>">Bölümlere Dön</a></div>
  </form>
</div>
