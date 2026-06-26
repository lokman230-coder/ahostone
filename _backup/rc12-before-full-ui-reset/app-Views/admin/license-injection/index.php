<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="v20-page">
  <div class="v20-hero"><span>v20 Ultimate Platform</span><h2>License Injection Center</h2><p>Dışarıdan yüklenen ZIP dosyalarını Ahost One içinde domain/package kilitli lisanslı teslim paketine çevirir.</p></div>
  <div class="v20-grid"><div class='v20-card'><h3>Offline-first Lisans</h3><p>Ahost One erişilemese bile license.json + signature + public key ile doğrular.</p><span>RSA-4096</span></div><div class='v20-card'><h3>Domain / Package Lock</h3><p>Web ürünlerinde domain, Android ürünlerde package name kontrol edilir.</p><span>Anti-abuse</span></div><div class='v20-card'><h3>ZIP Enjeksiyon</h3><p>license.php / LicenseManager / license.json dosyaları pakete eklenir.</p><span>Signed package</span></div></div>
  <div class="v20-panel"><h3>Çalışma prensibi</h3><p>Bu merkez fresh install’da veri üretmez; gerçek kayıt, gerçek entegrasyon ve yapılandırılmış API/bağlantı varsa çalışır. Veri yoksa demo sayı göstermeden “Veri bulunamadı” mantığı kullanılır.</p></div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
