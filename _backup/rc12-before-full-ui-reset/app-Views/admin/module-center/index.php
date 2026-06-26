<?php
$modules = ao_module_registry_all();
$types = [];
foreach($modules as $m){ $types[$m['type'] ?? 'other'] = ($types[$m['type'] ?? 'other'] ?? 0) + 1; }
$moduleHealth = [];
foreach($modules as $m) $moduleHealth[$m['slug']] = ao_module_health($m);
?>
<div class="ao-page-head">
  <div>
    <h2>Modül Merkezi Pro</h2>
    <p>ZIP ile yüklenen veya FTP ile <code>/modules</code> klasörüne bırakılan modüller burada yönetilir. Yeni/versiyonu değişmiş FTP modülü güvenlik için pasif alınır; aktif edilince gerekli SQL yaşam döngüsü çalışır.</p>
  </div>
  <div class="ao-actions no-margin">
    <form method="post" action="<?= url('admin/module-center/scan') ?>"><?= csrf_field() ?><button class="ao-btn">FTP Modüllerini Tara</button></form>
  </div>
</div>

<div class="ao-stats-grid">
  <div class="ao-stat"><span>Toplam Modül</span><strong><?= count($modules) ?></strong></div>
  <div class="ao-stat"><span>Aktif</span><strong><?= count(array_filter($modules, fn($m)=>!empty($m['is_enabled']))) ?></strong></div>
  <div class="ao-stat"><span>Pasif</span><strong><?= count(array_filter($modules, fn($m)=>empty($m['is_enabled']))) ?></strong></div>
  <div class="ao-stat"><span>SQL Bekleyen</span><strong><?= count(array_filter($modules, fn($m)=>!empty($m['needs_install']))) ?></strong></div>
  <div class="ao-stat"><span>Sağlıksız Paket</span><strong><?= count(array_filter($moduleHealth, fn($h)=>empty($h['ok']))) ?></strong></div>
</div>

<div class="ao-grid two">
  <div class="ao-card">
    <h3>ZIP Modül Yükle</h3>
    <p>ZIP içinde kökte veya tek klasör altında <code>module.json</code> olabilir. Sistem dosyayı doğru hedefe açar, eski modülü yedekler, eski klasörü temizler ve modülü pasif kaydeder.</p>
    <form method="post" enctype="multipart/form-data" action="<?= url('admin/module-center/upload') ?>" class="ao-form-grid">
      <?= csrf_field() ?>
      <label>Modül ZIP<input type="file" name="module_zip" accept=".zip" required></label>
      <button class="ao-btn">Yükle ve Pasif Kaydet</button>
    </form>
  </div>
  <div class="ao-card">
    <h3>Çalışma Mantığı</h3>
    <ul>
      <li><b>ZIP yükleme:</b> eski klasör yedeklenir, hedef temizlenir, yeni dosyalar açılır.</li>
      <li><b>FTP yükleme:</b> Tara butonu ile algılanır; yeni/farklı sürüm modül pasif kalır.</li>
      <li><b>Aktif et:</b> <code>install.sql</code>, gerekiyorsa <code>upgrade.sql</code> çalışır.</li>
      <li><b>Sil:</b> <code>uninstall.sql</code> çalışır, ayarlar ve modül klasörü temizlenir.</li>
      <li><b>Yapılandır:</b> modül ayarları ayrı ekranda düzenlenir.</li>
      <li><b>ZIP İndir:</b> sorunlu modül tek tıkla ZIP yapılır; dışarıda düzenlenip tekrar yüklenebilir.</li>
    </ul>
  </div>
</div>

<div class="ao-card">
  <h3>Yüklü Modüller</h3>
  <table class="ao-table">
    <thead><tr><th>Modül</th><th>Slug</th><th>Tip</th><th>Versiyon</th><th>Paket Sağlığı</th><th>SQL</th><th>Durum</th><th>İşlem</th></tr></thead>
    <tbody>
    <?php foreach($modules as $m): ?>
      <tr>
        <td><strong><?= e($m['name'] ?? $m['title'] ?? '-') ?></strong><br><small><?= e($m['description'] ?? '') ?></small><br><small><?= e($m['path'] ?? '-') ?></small><?php if(!empty($m['last_error'])): ?><br><small style="color:#b42318">Hata: <?= e($m['last_error']) ?></small><?php endif; ?></td>
        <td><code><?= e($m['slug'] ?? '') ?></code></td>
        <td><span class="ao-badge"><?= e($m['type'] ?? 'other') ?></span></td>
        <td><?= e($m['version'] ?? '-') ?><br><small>Kurulu: <?= e($m['installed_version'] ?? '-') ?></small></td>
        <?php $health=$moduleHealth[$m['slug']] ?? ['ok'=>false,'label'=>'Hatalı','issues'=>['Sağlık bilgisi yok'],'warnings'=>[]]; ?>
        <td><span class="ao-badge <?= $health['ok']?'active':'warning' ?>"><?= e($health['label']) ?></span><?php foreach(array_merge($health['issues'],$health['warnings']) as $issue): ?><br><small><?= e($issue) ?></small><?php endforeach; ?></td>
        <td><span class="ao-badge <?= !empty($m['needs_install'])?'warning':'active' ?>"><?= !empty($m['needs_install'])?'Bekliyor':'Tamam' ?></span></td>
        <td><span class="ao-badge <?= !empty($m['is_enabled'])?'active':'inactive' ?>"><?= !empty($m['is_enabled'])?'Aktif':'Pasif' ?></span></td>
        <td>
          <form method="post" action="<?= url('admin/module-center/toggle') ?>" style="display:inline">
            <?= csrf_field() ?><input type="hidden" name="slug" value="<?= e($m['slug'] ?? '') ?>"><input type="hidden" name="enabled" value="<?= !empty($m['is_enabled'])?'0':'1' ?>">
            <button class="ao-mini-btn"><?= !empty($m['is_enabled'])?'Pasif Yap':'Aktif Et' ?></button>
          </form>
          <a class="ao-mini-btn" href="<?= url('admin/module-center/config?slug='.urlencode($m['slug'] ?? '')) ?>">Yapılandır</a>
          <a class="ao-mini-btn" href="<?= url('admin/module-center/download?slug='.urlencode($m['slug'] ?? '')) ?>">ZIP İndir</a>
          <form method="post" action="<?= url('admin/module-center/delete') ?>" style="display:inline" onsubmit="return confirm('Bu modül silinsin mi? uninstall.sql çalışır, ayarlar ve modül dosyaları temizlenir.');">
            <?= csrf_field() ?><input type="hidden" name="slug" value="<?= e($m['slug'] ?? '') ?>">
            <button class="ao-mini-btn danger">Sil</button>
          </form>
        </td>
      </tr>
    <?php endforeach; if(!$modules): ?><tr><td colspan="8">Henüz modül bulunamadı. <code>/modules</code> klasörüne modül yükleyin veya ZIP yükleyin.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
