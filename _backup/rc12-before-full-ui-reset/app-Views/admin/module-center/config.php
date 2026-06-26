<?php $slug = $module['slug'] ?? ''; ?>
<div class="ao-page-head">
  <div>
    <h2>Modül Yapılandırma</h2>
    <p><b><?= e($module['name'] ?? $slug) ?></b> modülünün ayarlarını buradan düzenleyebilirsiniz. Auto secret alanları sistem tarafından üretilir.</p>
  </div>
  <div class="ao-actions no-margin"><a class="ao-btn soft" href="<?= url('admin/module-center') ?>">Modül Merkezine Dön</a></div>
</div>

<div class="ao-card">
  <h3>Yapılandırma Ayarları</h3>
  <?php if(empty($defs)): ?>
    <p>Bu modül için tanımlı yapılandırma alanı yok. Modül <code>module.json</code> içinde <code>settings</code> alanı tanımlarsa burada otomatik görünür.</p>
  <?php else: ?>
  <form method="post" action="<?= url('admin/module-center/config-save') ?>" class="ao-form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="slug" value="<?= e($slug) ?>">
    <?php foreach($defs as $key=>$def):
      $label = $def['label'] ?? $def['title'] ?? $key;
      $type = $def['type'] ?? 'text';
      $value = $values[$key] ?? ($def['default'] ?? '');
      $placeholder = $def['placeholder'] ?? '';
      $isSecret = !empty($def['secret']) || !empty($def['is_secret']) || in_array($type, ['password','hidden'], true) || !empty($def['auto_generate']);
      $readonly = !empty($def['readonly']) || !empty($def['auto_generate']);
    ?>
      <label><?= e($label) ?>
        <?php if($type==='textarea'): ?>
          <textarea name="settings[<?= e($key) ?>]" placeholder="<?= e($placeholder) ?>" <?= $readonly?'readonly':'' ?>><?= e($value) ?></textarea>
        <?php elseif($type==='select' && !empty($def['options']) && is_array($def['options'])): ?>
          <select name="settings[<?= e($key) ?>]" <?= $readonly?'disabled':'' ?>>
            <?php foreach($def['options'] as $ov=>$ot): if(is_int($ov)) $ov=$ot; ?>
              <option value="<?= e($ov) ?>" <?= (string)$ov===(string)$value?'selected':'' ?>><?= e($ot) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif($type==='checkbox'): ?>
          <select name="settings[<?= e($key) ?>]" <?= $readonly?'disabled':'' ?>><option value="0" <?= !$value?'selected':'' ?>>Kapalı</option><option value="1" <?= $value?'selected':'' ?>>Açık</option></select>
        <?php elseif($isSecret): ?>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input id="setting_<?= e($key) ?>" type="password" name="<?= $readonly?'':'settings['.e($key).']' ?>" value="<?= e($value) ?>" placeholder="<?= e($placeholder) ?>" <?= $readonly?'readonly':'' ?> style="flex:1;min-width:260px">
            <button type="button" class="ao-mini-btn" onclick="var i=document.getElementById('setting_<?= e($key) ?>'); i.type=i.type==='password'?'text':'password';">Göster</button>
            <button type="button" class="ao-mini-btn" onclick="navigator.clipboard&&navigator.clipboard.writeText(document.getElementById('setting_<?= e($key) ?>').value)">Kopyala</button>
          </div>
        <?php else: ?>
          <input type="text" name="settings[<?= e($key) ?>]" value="<?= e($value) ?>" placeholder="<?= e($placeholder) ?>" <?= $readonly?'readonly':'' ?>>
        <?php endif; ?>
        <?php if(!empty($def['auto_generate'])): ?><small>Bu alan otomatik üretilir. Generator: <code><?= e($def['generator'] ?? 'random_64') ?></code></small><?php endif; ?>
        <?php if(!empty($def['help'])): ?><small><?= e($def['help']) ?></small><?php endif; ?>
      </label>
    <?php endforeach; ?>
    <button class="ao-btn">Ayarları Kaydet</button>
  </form>

  <?php foreach($defs as $key=>$def): if(empty($def['auto_generate'])) continue; ?>
    <form method="post" action="<?= url('admin/module-center/regenerate-secret') ?>" style="margin-top:10px" onsubmit="return confirm('<?= e($key) ?> yeniden oluşturulsun mu? Eski callback/webhook secret geçersiz olur.');">
      <?= csrf_field() ?>
      <input type="hidden" name="slug" value="<?= e($slug) ?>">
      <input type="hidden" name="key" value="<?= e($key) ?>">
      <button class="ao-mini-btn danger">“<?= e($def['label'] ?? $key) ?>” Yeniden Oluştur</button>
    </form>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="ao-card">
  <h3>Modül Bilgisi</h3>
  <table class="ao-table"><tbody>
    <tr><th>Slug</th><td><code><?= e($slug) ?></code></td></tr>
    <tr><th>Tip</th><td><?= e($module['type'] ?? '-') ?></td></tr>
    <tr><th>Versiyon</th><td><?= e($module['version'] ?? '-') ?></td></tr>
    <tr><th>Kurulu Versiyon</th><td><?= e($module['installed_version'] ?? '-') ?></td></tr>
    <tr><th>Durum</th><td><?= !empty($module['is_enabled'])?'Aktif':'Pasif' ?></td></tr>
    <tr><th>Yol</th><td><?= e($module['path'] ?? '-') ?></td></tr>
  </tbody></table>
</div>
