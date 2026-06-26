<?php
ao_schema_ensure_v188(); try{ao_schema_ensure_v940();}catch(Throwable $e){}
$id=(int)($_GET['id']??0); $theme=null;
if($id){ try{$q=db()->prepare('SELECT * FROM themes WHERE id=? LIMIT 1');$q->execute([$id]);$theme=$q->fetch();}catch(Throwable $e){} }
if(!$theme){ $theme=ao_active_theme('site'); }
$theme = array_merge(['id'=>0,'name'=>'Tema','area'=>'site','slug'=>'default','primary_color'=>'#2563eb','secondary_color'=>'#0f172a','font_family'=>'Inter, Arial, sans-serif','radius'=>'24px','button_radius'=>'16px','button_style'=>'gradient','background_color'=>'#f8fbff','background_gradient'=>'linear-gradient(135deg,#fff,#eef4ff)','header_mode'=>'sticky','mobile_bottom_nav'=>1], $theme ?: []);
?>
<div class="ao-page-head v222-theme-head"><div><span class="eyebrow">Theme Studio Pro</span><h2>Tema Editörü</h2><p>Windows 98 formu yerine kartlı, canlı önizlemeli ve SaaS seviyesinde tema düzenleyici.</p></div><a class="ao-btn soft" href="<?= url('admin/theme-center/themes') ?>">Tema Listesi</a></div>
<div class="theme-studio-grid v222-editor-grid">
  <section class="theme-studio-panel v222-editor-card">
    <div class="v222-editor-card-head"><div><span><?= e(strtoupper($theme['area'])) ?></span><h3><?= e($theme['name']) ?></h3></div><b><?= e($theme['slug']) ?></b></div>
    <form method="post" action="<?= url('admin/theme-center/save-style') ?>" class="theme-fields v222-theme-form">
      <?= csrf_field() ?><input type="hidden" name="theme_id" value="<?= (int)$theme['id'] ?>">
      <div class="v222-form-grid two"><label>Ana Renk <input type="color" name="primary_color" value="<?= e($theme['primary_color']) ?>"></label><label>İkinci Renk <input type="color" name="secondary_color" value="<?= e($theme['secondary_color']) ?>"></label></div>
      <div class="v222-form-grid two"><label>Font Ailesi <select name="font_family"><option <?= str_contains($theme['font_family'],'Inter')?'selected':'' ?> value="Inter, Arial, sans-serif">Inter</option><option <?= str_contains($theme['font_family'],'Poppins')?'selected':'' ?> value="Poppins, Arial, sans-serif">Poppins</option><option <?= str_contains($theme['font_family'],'Manrope')?'selected':'' ?> value="Manrope, Arial, sans-serif">Manrope</option></select></label><label>Buton Tipi <select name="button_style"><option value="gradient" <?= ($theme['button_style']==='gradient')?'selected':'' ?>>Gradient</option><option value="solid" <?= ($theme['button_style']==='solid')?'selected':'' ?>>Dolu</option><option value="soft" <?= ($theme['button_style']==='soft')?'selected':'' ?>>Soft</option></select></label></div>
      <div class="v222-form-grid two"><label>Kart Radius <input name="radius" value="<?= e($theme['radius']) ?>"></label><label>Buton Radius <input name="button_radius" value="<?= e($theme['button_radius']) ?>"></label></div>
      <label>Arka Plan Rengi <input type="color" name="background_color" value="<?= e($theme['background_color']) ?>"></label>
      <label>Gradient CSS <input name="background_gradient" value="<?= e($theme['background_gradient']) ?>"></label>
      <div class="v222-form-grid two"><label>Menü Davranışı <select name="header_mode"><option value="sticky" <?= ($theme['header_mode']==='sticky')?'selected':'' ?>>Kaygan / Sticky</option><option value="fixed" <?= ($theme['header_mode']==='fixed')?'selected':'' ?>>Sabit</option><option value="static" <?= ($theme['header_mode']==='static')?'selected':'' ?>>Normal</option></select></label><label class="v222-switch"><input type="checkbox" name="mobile_bottom_nav" value="1" <?= !empty($theme['mobile_bottom_nav'])?'checked':'' ?>><span></span> Mobil alt menü aktif</label></div>
      <div class="v222-editor-actions"><button class="ao-btn">Değişiklikleri Kaydet</button><?php if(!empty($theme['id'])): ?><a class="ao-btn soft" target="_blank" href="<?= url('admin/theme-center/preview?id='.(int)$theme['id']) ?>">Önizle</a><?php endif; ?></div>
    </form>
  </section>
  <section class="theme-studio-panel v222-editor-card"><h3>Canlı Önizleme</h3><div class="theme-preview-box v222-preview-box" style="--ao-primary:<?= e($theme['primary_color']) ?>;--ao-secondary:<?= e($theme['secondary_color']) ?>;--ao-bg:<?= e($theme['background_color']) ?>;--btn-radius:<?= e($theme['button_radius']) ?>"><div class="preview-top"></div><div><h2>Ahost One</h2><p>Header, kart ve buton karakteri tema paketine göre değişir.</p><a class="demo-btn">Buton</a></div><div class="preview-cards"><span></span><span></span><span></span></div></div></section>
</div>
