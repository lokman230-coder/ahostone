<link rel="stylesheet" href="<?= url('modules/migration/migration-bridge-pro/assets/css/migration-bridge-pro.css') ?>">
<div class="mbp-wrap">
  <div class="mbp-hero">
    <div>
      <h1>Migration Bridge Pro</h1>
      <p>Kaynak Sistem/WiseCP/Blesta verilerini seçmeli aktarın. Ürün fiyatları ve domain uzantı fiyatları dahil.</p>
    </div>
    <a class="mbp-btn secondary" href="/admin/modules/migration-bridge-pro/logs">Loglar</a>
  </div>

  <div class="mbp-grid two">
    <form class="mbp-card" method="post" action="/admin/modules/migration-bridge-pro/connect">
      <h2>Canlı Veritabanı Bağlantısı</h2>
      <label>Kaynak</label>
      <select name="source_type"><option>Kaynak Sistem</option><option>WiseCP</option><option>Blesta</option></select>
      <label>Host</label><input name="host" value="127.0.0.1">
      <label>Port</label><input name="port" value="3306">
      <label>Veritabanı</label><input name="database" required>
      <label>Kullanıcı</label><input name="username" required>
      <label>Şifre</label><input type="password" name="password">
      <input type="hidden" name="charset" value="utf8mb4">
      <button class="mbp-btn" type="submit">Tara ve Seçmeli Önizleme Aç</button>
    </form>

    <form class="mbp-card" method="post" action="/admin/modules/migration-bridge-pro/upload" enctype="multipart/form-data">
      <h2>SQL / ZIP Yükle</h2>
      <p class="muted">.sql, .zip ve .gz dosyalarını analiz eder. ZIP içindeki SQL dosyalarını listeler.</p>
      <input type="file" name="migration_file" accept=".sql,.zip,.gz" required>
      <button class="mbp-btn" type="submit">Yükle ve Listele</button>
      <div class="mbp-note">Not: Büyük yedeklerde PHP upload_max_filesize ve post_max_size değerlerini kontrol edin.</div>
    </form>
  </div>
</div>
