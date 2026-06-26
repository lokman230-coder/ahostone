<?php require __DIR__.'/_helpers.php'; $section=$section ?? 'general'; $sections=ao_settings_sections(); $info=$sections[$section] ?? $sections['general']; $flash=get_flash(); ?>
<style>
.ao-settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin:15px 0}.ao-setting-card{display:block;padding:14px;border:1px solid #dbe5f2;border-radius:16px;background:#fff;text-decoration:none;color:#0f172a}.ao-setting-card b{display:block;margin-bottom:6px}.ao-setting-card span{font-size:13px;color:#64748b}.ao-setting-card.active{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}.ao-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 18px}.ao-tabs a{padding:9px 12px;border-radius:12px;border:1px solid #dbe5f2;text-decoration:none;color:#0f172a;background:#f8fafc}.ao-tabs a.active{background:#2563eb;color:#fff;border-color:#2563eb}.ao-settings-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px}.ao-field{display:block;font-weight:700}.ao-field span{display:block;margin-bottom:7px}.ao-field input,.ao-field select,.ao-field textarea{width:100%;border:1px solid #cbd5e1;border-radius:14px;padding:12px;background:#f8fafc}.ao-switch{display:flex;align-items:center;gap:10px}.ao-switch input{display:none}.ao-switch i{width:54px;height:30px;border-radius:40px;background:#cbd5e1;position:relative}.ao-switch i:before{content:'';position:absolute;width:24px;height:24px;border-radius:50%;background:#fff;left:3px;top:3px;transition:.2s}.ao-switch input:checked+i{background:#2563eb}.ao-switch input:checked+i:before{left:27px}.ao-secret{display:flex;gap:8px}.ao-secret input{flex:1}.ao-secret button{border:0;border-radius:12px;padding:0 12px}.ao-mode-warn{padding:13px 15px;border-radius:14px;margin:12px 0;background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}.ao-mode-live{padding:13px 15px;border-radius:14px;margin:12px 0;background:#ecfdf5;color:#047857;border:1px solid #bbf7d0}.ao-actions{margin-top:18px;display:flex;gap:10px}.ao-btn.secondary{background:#e2e8f0;color:#0f172a}
</style>
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
