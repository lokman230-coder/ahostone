<?php
/**
 * Ahost One - Kapsamlı Yardım Merkezi v3
 * Tum moduller icin adim adim kilavuzlar
 */
$page = $_GET['page'] ?? 'home';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;
?>
<style>
.hc{--p:#2563eb;--s:#10b981;--w:#f59e0b;--bg:#f8fafc}
.hc .hero{background:linear-gradient(135deg,#0f172a,#1e40af);color:#fff;padding:50px;border-radius:20px;text-align:center;margin-bottom:30px}
.hc .hero h1{font-size:2rem;margin:0 0 10px}
.hc .hero p{font-size:1.1rem;opacity:.9;margin:0}
.hc .sec{background:#fff;border-radius:20px;padding:30px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.hc .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px}
.hc .card{border:1px solid #e2e8f0;border-radius:15px;padding:20px;cursor:pointer;transition:all .3s}
.hc .card:hover{transform:translateY(-3px);box-shadow:0 8px 25px rgba(0,0,0,.1);border-color:var(--p)}
.hc .card h3{margin:0 0 6px;font-size:1.1rem}
.hc .card p{color:#64748b;margin:0;font-size:.9rem}
.hc .card .icon{font-size:2rem;margin-bottom:10px}
.hc .card .badge{display:inline-block;background:#f1f5f9;padding:3px 10px;border-radius:15px;font-size:.75rem;margin-top:8px}
.hc .step{display:flex;gap:15px;padding:15px;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:12px;cursor:pointer}
.hc .step:hover{border-color:var(--p);background:#f8fafc}
.hc .step-num{width:45px;height:45px;background:var(--p);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:bold;flex-shrink:0}
.hc .step h4{margin:0 0 4px;font-size:1rem}
.hc .step p{color:#64748b;margin:0;font-size:.85rem}
.hc .time{background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:10px;font-size:.75rem;margin-top:6px;display:inline-block}
.hc .box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px;margin:12px 0}
.hc .box.s{background:#dcfce7;border-color:#86efac}
.hc .box.w{background:#fef3c7;border-color:#fcd34d}
.hc .code{background:#0f172a;color:#e2e8f0;padding:14px;border-radius:10px;font-family:monospace;margin:12px 0;font-size:.85rem;overflow-x:auto}
.hc .code .s{color:#10b981}
.hc .btn{display:inline-block;padding:10px 20px;border-radius:10px;font-weight:600;text-decoration:none}
.hc .btn-p{background:var(--p);color:#fff}
.hc .btn-s{background:#f1f5f9;color:#475569}
.hc .nav{display:flex;justify-content:space-between;padding-top:20px;border-top:1px solid #e2e8f0;margin-top:25px}
.hc table{width:100%;border-collapse:collapse;margin:12px 0}
.hc table th,.hc table td{padding:10px;text-align:left;border-bottom:1px solid #e2e8f0;font-size:.9rem}
.hc table th{background:#f8fafc;font-weight:600}
</style>

<div class="hc">
<?php if($page === 'home'): ?>
<div class="hero">
    <h1>📚 Ahost One Yardim Merkezi v3</h1>
    <p>Sistemi 1 saat icinde tam kapasiteye alin</p>
</div>

<div class="sec">
    <h2>🚀 Hizli Baslangic (6 Adim)</h2>
    <div class="grid">
        <div class="step" onclick="location.href='?page=guide&step=1'"><div class="step-num">1</div><div><h4>Sistem Ayarlari</h4><p>Site adi, logo, para birimi</p><span class="time">⏱ 5 dk</span></div></div>
        <div class="step" onclick="location.href='?page=guide&step=2'"><div class="step-num">2</div><div><h4>💳 Odeme</h4><p>PayTR, Iyzico, Shopier</p><span class="time">⏱ 15 dk</span></div></div>
        <div class="step" onclick="location.href='?page=guide&step=3'"><div class="step-num">3</div><div><h4>🌐 Domain</h4><p>DomainNameAPI</p><span class="time">⏱ 10 dk</span></div></div>
        <div class="step" onclick="location.href='?page=guide&step=4'"><div class="step-num">4</div><div><h4>📱 SMS</h4><p>IletiMerkezi</p><span class="time">⏱ 5 dk</span></div></div>
        <div class="step" onclick="location.href='?page=guide&step=5'"><div class="step-num">5</div><div><h4>📧 E-posta</h4><p>SMTP</p><span class="time">⏱ 10 dk</span></div></div>
        <div class="step" onclick="location.href='?page=guide&step=6'"><div class="step-num">6</div><div><h4>📦 Urunler</h4><p>Hosting paketleri</p><span class="time">⏱ 15 dk</span></div></div>
    </div>
</div>

<div class="sec">
    <h2>💳 Odeme Sistemleri</h2>
    <div class="grid">
        <div class="card" onclick="location.href='?page=module&name=paytr'"><div class="icon">💳</div><h3>PayTR</h3><p>Turkiye'nin en hizli onay.</p><span class="badge">Onerilen</span></div>
        <div class="card" onclick="location.href='?page=module&name=iyzico'"><div class="icon">💳</div><h3>Iyzico</h3><p>Taksit ve 3D Secure.</p></div>
        <div class="card" onclick="location.href='?page=module&name=shopier'"><div class="icon">🛒</div><h3>Shopier</h3><p>Kolay entegrasyon.</p></div>
        <div class="card" onclick="location.href='?page=module&name=paystack'"><div class="icon">🌍</div><h3>Paystack</h3><p>Afrika odemeleri.</p></div>
    </div>
</div>

<div class="sec">
    <h2>📣 Otomasyon Modulleri</h2>
    <div class="grid">
        <div class="card" onclick="location.href='?page=module&name=dunning'"><div class="icon">🔔</div><h3>Dunning</h3><p>Otomatik hatirlatmalar.</p></div>
        <div class="card" onclick="location.href='?page=module&name=abandoned-cart'"><div class="icon">🛒</div><h3>Sepet Kurtarma</h3><p>Brakilani sepetleri kurtar.</p></div>
        <div class="card" onclick="location.href='?page=module&name=coupons'"><div class="icon">🎟️</div><h3>Kuponlar</h3><p>Indirim kodu sistemi.</p></div>
        <div class="card" onclick="location.href='?page=module&name=subscription'"><div class="icon">🔄</div><h3>Abonelik</h3><p>Otomatik yenileme.</p></div>
    </div>
</div>

<div class="sec">
    <h2>🛠️ Builder Modulleri</h2>
    <div class="grid">
        <div class="card" onclick="location.href='?page=mobilebuilder'"><div class="icon">📱</div><h3>MobileBuilder</h3><p>APK/AAB/PWA uygulama olustur.</p><span class="badge">Yeni</span></div>
        <div class="card" onclick="location.href='?page=sitebuilder'"><div class="icon">🌐</div><h3>SiteBuilder</h3><p>Surukle birak site olustur.</p><span class="badge">Yeni</span></div>
    </div>
</div>

<div class="sec">
    <h2>🤖 AI & Ozel Moduller</h2>
    <div class="grid">
        <div class="card" onclick="location.href='?page=module&name=ai-design'"><div class="icon">🎨</div><h3>AI Site Builder</h3><p>OpenAI ile site tasarimi.</p></div>
        <div class="card" onclick="location.href='?page=module&name=ai-app'"><div class="icon">📱</div><h3>AI App Builder</h3><p>OpenAI ile uygulama.</p></div>
        <div class="card" onclick="location.href='?page=module&name=blog'"><div class="icon">📝</div><h3>Blog</h3><p>SEO dostu blog.</p></div>
        <div class="card" onclick="location.href='?page=module&name=live-chat'"><div class="icon">💬</div><h3>Canli Destek</h3><p>WhatsApp & AI chatbot.</p></div>
    </div>
</div>

<?php elseif($step > 0): ?>

<?php if($step === 1): ?>
<div class="hero" style="padding:35px"><h1>📝 Adim 1: Sistem Ayarlari</h1></div>
<div class="sec">
    <h3>Ayarlar</h3>
    <ol style="line-height:2">
        <li><strong>Admin → Ayarlar → Genel</strong></li>
        <li>Bilgileri doldurun:</li>
    </ol>
    <table>
        <tr><th>Alan</th><th>Ornek</th></tr>
        <tr><td>Site Adi</td><td>Ahost One</td></tr>
        <tr><td>Site URL</td><td>https://ornek-domain.com</td></tr>
        <tr><td>Sirket</td><td>Ahost Hosting Ltd.</td></tr>
        <tr><td>Saat Dilimi</td><td>Europe/Istanbul</td></tr>
        <tr><td>Para Birimi</td><td>TRY</td></tr>
    </table>
</div>
<div class="nav"><span></span><a href="?page=guide&step=2" class="btn btn-p">Sonraki: Odeme →</a></div>
<?php elseif($step === 2): ?>
<div class="hero" style="padding:35px"><h1>💳 Adim 2: Odeme Entegrasyonu</h1></div>
<div class="sec">
    <h3>PayTR</h3>
    <div class="box s"><strong>✅</strong> <a href="https://www.paytr.com" target="_blank">paytr.com</a> adresinden basvurun</div>
    <div class="code">Panel: <a href="https://www.paytr.com/magaza/bayi-entegrasyon" style="color:#60a5fa">paytr.com/magaza/bayi-entegrasyon</a></div>
    <h4>API Bilgileri:</h4>
    <div class="code">Merchant ID: <span class="s">1234567</span><br>Merchant Key: <span class="s">Jg8HsK9xL2m...</span><br>Merchant Salt: <span class="s">abc123XYZ...</span></div>
    <h4>Adimlar:</h4>
    <ol style="line-height:2">
        <li>Admin → Ayarlar → Odeme Yontemleri</li>
        <li>PayTR → Yapilandir</li>
        <li>Bilgileri girin</li>
        <li>Test Mode secin</li>
    </ol>
    <div class="box w"><strong>Test Kart:</strong> 4508 0344 4444 4444 | 12/26 | 123 | 3D: 12345678</div>
</div>
<div class="sec">
    <h3>Iyzico</h3>
    <div class="box"><strong>📋</strong> <a href="https://www.iyzico.com" target="_blank">iyzico.com</a> basvurun</div>
    <ol style="line-height:2">
        <li><a href="https://merchant.iyzipay.com" target="_blank">Iyzico Panel</a></li>
        <li>Ayarlar → API Anahtarlari</li>
    </ol>
</div>
<div class="nav"><a href="?page=guide&step=1" class="btn btn-s">←</a><a href="?page=guide&step=3" class="btn btn-p">Sonraki: Domain →</a></div>
<?php elseif($step === 3): ?>
<div class="hero" style="padding:35px"><h1>🌐 Adim 3: Domain Registrar</h1></div>
<div class="sec">
    <h3>DomainNameAPI</h3>
    <div class="box s"><strong>✅</strong> <a href="https://dm.domainnameapi.com" target="_blank">dm.domainnameapi.com</a></div>
    <h4>Adimlar:</h4>
    <ol style="line-height:2">
        <li>Admin → Domain Center → Registrar Ayarlari</li>
        <li>DomainNameAPI secin</li>
        <li>Reseller ID ve API Key girin</li>
        <li>Test Mode secin</li>
        <li>API'yi Test Et</li>
    </ol>
</div>
<div class="nav"><a href="?page=guide&step=2" class="btn btn-s">←</a><a href="?page=guide&step=4" class="btn btn-p">Sonraki: SMS →</a></div>
<?php elseif($step === 4): ?>
<div class="hero" style="padding:35px"><h1>📱 Adim 4: SMS Entegrasyonu</h1></div>
<div class="sec">
    <h3>IletiMerkezi</h3>
    <div class="box s"><strong>✅</strong> <a href="https://www.iletimerkezi.com" target="_blank">iletimerkezi.com</a></div>
    <div class="code">API Key: <span class="s">xxxxxxxxxxxxxxxx</span><br>API Secret: <span class="s">xxxxxxxxxxxxxxxx</span><br>Baslik: <span class="s">AHOSTONE</span></div>
    <h4>Adimlar:</h4>
    <ol style="line-height:2">
        <li>Admin → Ayarlar → SMS Ayarlari</li>
        <li>IletiMerkezi secin</li>
        <li>Bilgileri girin</li>
        <li>Baglantiiyi Test Et</li>
    </ol>
</div>
<div class="nav"><a href="?page=guide&step=3" class="btn btn-s">←</a><a href="?page=guide&step=5" class="btn btn-p">Sonraki: E-posta →</a></div>
<?php elseif($step === 5): ?>
<div class="hero" style="padding:35px"><h1>📧 Adim 5: E-posta Ayarlari</h1></div>
<div class="sec">
    <h3>SMTP</h3>
    <div class="box"><strong>💡</strong> cPanel'de e-posta hesabi olusturun</div>
    <div class="code">Host: <span class="s">mail.siteniz.com</span><br>Port: <span class="s">587</span> veya <span class="s">465</span><br>Username: <span class="s">destek@siteniz.com</span><br>Password: <span class="s">......</span></div>
    <h4>Adimlar:</h4>
    <ol style="line-height:2">
        <li>Admin → Ayarlar → E-posta</li>
        <li>SMTP sekmesi</li>
        <li>Bilgileri girin</li>
        <li>Test Et</li>
    </ol>
</div>
<div class="nav"><a href="?page=guide&step=4" class="btn btn-s">←</a><a href="?page=guide&step=6" class="btn btn-p">Sonraki: Urunler →</a></div>
<?php elseif($step === 6): ?>
<div class="hero" style="padding:35px"><h1>📦 Adim 6: Urunler</h1></div>
<div class="sec">
    <h3>Hosting Paketi</h3>
    <ol style="line-height:2">
        <li>Admin → Urun Merkezi → Yeni Urun</li>
        <li>Paket adi verin</li>
        <li>Fiyatlandirma girin</li>
    </ol>
    <table>
        <tr><th>Paket</th><th>Aylik</th><th>Disk</th></tr>
        <tr><td>Baslangic</td><td>₺49</td><td>10 GB</td></tr>
        <tr><td>Profesyonel</td><td>₺99</td><td>30 GB</td></tr>
        <tr><td>Kurumsal</td><td>₺199</td><td>100 GB</td></tr>
    </table>
</div>
<div class="sec">
    <h3>🎉 Tebrikler!</h3>
    <p>6 adim tamamlandi! Sisteminiz hazir.</p>
</div>
<div class="nav"><a href="?page=guide&step=5" class="btn btn-s">←</a><a href="?page=home" class="btn btn-p">🏠 Ana Sayfa</a></div>
<?php endif; ?>

<?php elseif($page === 'mobilebuilder'): ?>
<a href="?page=home" class="btn btn-s" style="display:inline-block;margin-bottom:20px;padding:10px 20px;text-decoration:none">← Geri</a>

<div class="hero"><h1>📱 MobileBuilder Pro</h1><p>APK, AAB ve PWA uygulamasi olusturun</p></div>

<div class="sec">
    <h2>🎯 Ozellikler</h2>
    <div class="grid">
        <div class="card"><div class="icon">📦</div><h3>APK Export</h3><p>Android uygulama kaynak</p></div>
        <div class="card"><div class="icon">🎯</div><h3>AAB Export</h3><p>Google Play Bundle</p></div>
        <div class="card"><div class="icon">🌐</div><h3>PWA Export</h3><p>Progressive Web App</p></div>
        <div class="card"><div class="icon">🔥</div><h3>Firebase</h3><p>Bildirim ve analitik</p></div>
    </div>
</div>

<div class="sec">
    <h2>📱 Sablonlar</h2>
    <table>
        <tr><th>Sablon</th><th>Aciklama</th><th>Ozellikler</th></tr>
        <tr><td>Bos Uygulama</td><td>Sifirdan baslayin</td><td>Tum ozellikler</td></tr>
        <tr><td>Emlak</td><td>Gayrimenkul</td><td>Ilan, harita, filtreleme</td></tr>
        <tr><td>Restoran</td><td>Cafe/RESTORAN</td><td>Menu, rezervasyon, siparis</td></tr>
        <tr><td>Radyo</td><td>Radyo/Podcast</td><td>Canli, program rehberi</td></tr>
        <tr><td>Kurumsal</td><td>Sirket</td><td>Hakkimizda, hizmetler</td></tr>
        <tr><td>E-Ticaret</td><td>Magaza</td><td>Sepet, odeme, siparis</td></tr>
        <tr><td>Haber</td><td>Medya</td><td>Kategori, bildirimler</td></tr>
        <tr><td>Egitim</td><td>Kurs</td><td>Video, sinav, sertifika</td></tr>
    </table>
</div>

<div class="sec">
    <h2>🔧 Kurulum</h2>
    <ol style="line-height:2">
        <li><strong>Admin → Builder → Mobil Builder</strong></li>
        <li>Yeni Proje olustur</li>
        <li>Sablon secin veya bos baslayin</li>
        <li>Uygulama adini ve paket adini girin</li>
        <li>Logo ve renkleri ayarlayin</li>
        <li>Build sekmesinde APK/AAB olusturun</li>
    </ol>
</div>

<div class="sec">
    <h2>🔑 Lisanslama</h2>
    <div class="grid">
        <div class="card"><h3>Tek Domain</h3><p>Bir domain icin lisans</p></div>
        <div class="card"><h3>Acik Kaynak</h3><p>Sinirsiz kullanim</p></div>
    </div>
</div>

<div class="sec">
    <h2>📦 Paket Sistemi</h2>
    <table>
        <tr><th>Paket</th><th>Fiyat Carpani</th><th>Ozellikler</th></tr>
        <tr><td>Paket 1 - Temel</td><td>1x</td><td>Temel ozellikler</td></tr>
        <tr><td>Paket 2 - Pro</td><td>2x</td><td>Tum ozellikler</td></tr>
    </table>
</div>

<div class="sec">
    <h2>🔒 Build Ortami</h2>
    <div class="box w"><strong>Not:</strong> APK/AAB olusturmak icin sunucuda Java JDK, Android SDK ve Gradle kurulu olmalidir.</div>
    <h4>Gerekli Yazilimlar:</h4>
    <ul style="line-height:2">
        <li>Java JDK 17+</li>
        <li>Android SDK (Command Line Tools)</li>
        <li>Gradle 8.x</li>
        <li>Keystore dosyasi (opsiyonel)</li>
    </ul>
    <h4>Sunucu Kurulumu:</h4>
    <div class="code">mkdir -p /opt/android-sdk/cmdline-tools<br>cd /opt/android-sdk/cmdline-tools<br>wget https://dl.google.com/android/repository/commandlinetools-linux-11076708_latest.zip<br>unzip commandlinetools-linux-11076708_latest.zip<br>mv cmdline-tools latest<br><br>export ANDROID_HOME=/opt/android-sdk<br>sdkmanager "platform-tools" "platforms;android-34" "build-tools;34.0.0"</div>
</div>

<div class="sec">
    <h2>📱 APK/AAB Build Center</h2>
    <ol style="line-height:2">
        <li><strong>Admin → MobileBuilder</strong> paneline gidin</li>
        <li>Sistem gereksinimlerinin yesil olup olmadigini kontrol edin</li>
        <li>Proje secin veya yeni proje olusturun</li>
        <li><strong>Build Center</strong>'e tiklayin</li>
        <li>APK veya AAB secin</li>
        <li><strong>Build Baslat</strong> butonuna tiklayin</li>
        <li>Build log'undan durumu izleyin</li>
        <li>Build tamamlandiginda <strong>Indir</strong> butonu aktif olur</li>
    </ol>
    <div class="box s"><strong>Guvenli Indirme:</strong> APK/AAB dosyalari dogrudan public erisime acik degil. Kullanicilar sadece kendi build'lerini indirebilir.</div>
</div>

<div class="sec">
    <h2>📋 Teklif Sistemi</h2>
    <div class="box s">Site ziyaretcileri web sitesi veya mobil uygulama icin teklif talep edebilir.</div>
    <h4>Teklif Formu:</h4>
    <ul style="line-height:2">
        <li>Ziyaretci: <code>/teklif</code> adresinden formu doldurur</li>
        <li>Admin: <code>/admin/quotations</code> adresinden talepleri gorur</li>
        <li>Hizmet turleri: Web Sitesi, Mobil Uygulama, Web App, Ozel Yazilim</li>
        <li>Butce ve zamanlama secenekleri mevcut</li>
    </ul>
    <h4>Admin Islemleri:</h4>
    <ul style="line-height:2">
        <li>Teklifleri goruntuleme ve filtreleme</li>
        <li>Durum guncelleme (Bekleyen, Inceleniyor, Fiyatlandirildi)</li>
        <li>Fiyat teklifi olusturma</li>
    </ul>
</div>

<?php elseif($page === 'sitebuilder'): ?>
<a href="?page=home" class="btn btn-s" style="display:inline-block;margin-bottom:20px;padding:10px 20px;text-decoration:none">← Geri</a>

<div class="hero"><h1>🌐 SiteBuilder Pro</h1><p>Surukle birak ile profesyonel web sitesi olusturun</p></div>

<div class="sec">
    <h2>🎯 Ozellikler</h2>
    <div class="grid">
        <div class="card"><div class="icon">🎨</div><h3>Surukle-Birak</h3><p>Kolay sayfa olusturma</p></div>
        <div class="card"><div class="icon">📐</div><h3>Bloklar</h3><p>16+ icerik bloku</p></div>
        <div class="card"><div class="icon">📦</div><h3>ZIP Export</h3><p>Indirip kullan</p></div>
        <div class="card"><div class="icon">🤖</div><h3>AI Yardimci</h3><p>Icerik olusturma</p></div>
    </div>
</div>

<div class="sec">
    <h2>📐 Sablonlar</h2>
    <table>
        <tr><th>Sablon</th><th>Kategori</th></tr>
        <tr><td>Hosting Firmasi</td><td>Is</td></tr>
        <tr><td>Kurumsal Sirket</td><td>Is</td></tr>
        <tr><td>Yazilim Firmasi</td><td>Is</td></tr>
        <tr><td>Ajans</td><td>Is</td></tr>
        <tr><td>Radyo</td><td>Medya</td></tr>
        <tr><td>Haber</td><td>Medya</td></tr>
        <tr><td>E-Ticaret On Site</td><td>E-Ticaret</td></tr>
        <tr><td>Landing Page</td><td>Pazarlama</td></tr>
        <tr><td>Portfolyo</td><td>Kisisel</td></tr>
        <tr><td>Blog</td><td>Kisisel</td></tr>
        <tr><td>Restoran</td><td>Is</td></tr>
        <tr><td>Emlak</td><td>Is</td></tr>
    </table>
</div>

<div class="sec">
    <h2>🧱 Icerik Bloklari</h2>
    <table>
        <tr><th>Blok</th><th>Aciklama</th></tr>
        <tr><td>Hero Section</td><td>Buyuk baslik ve gorsel</td></tr>
        <tr><td>Metin Bloku</td><td>Yazi ve paragraflar</td></tr>
        <tr><td>Gorsel</td><td>Resim galerisi</td></tr>
        <tr><td>Video</td><td>Video gomme</td></tr>
        <tr><td>Buton</td><td>CAGRI ve tiklama</td></tr>
        <tr><td>Ozellikler</td><td>Ozellik listesi</td></tr>
        <tr><td>Fiyatlandirma</td><td>Fiyat tablolari</td></tr>
        <tr><td>Referanslar</td><td>Musteri yorumlari</td></tr>
        <tr><td>SSS</td><td>Sikca sorulan sorular</td></tr>
        <tr><td>CTA</td><td>CAGRI to Action</td></tr>
        <tr><td>Form</td><td>Form olusturucu</td></tr>
        <tr><td>Harita</td><td>Google Maps</td></tr>
        <tr><td>Sayac</td><td>Istatistik gosterge</td></tr>
        <tr><td>Galeri</td><td>Resim galerisi</td></tr>
        <tr><td>Ekip</td><td>Team Uyeleri</td></tr>
        <tr><td>Blog Grid</td><td>Blog yazilari</td></tr>
    </table>
</div>

<div class="sec">
    <h2>🔧 Kurulum</h2>
    <ol style="line-height:2">
        <li><strong>Admin → Builder → Site Builder</strong></li>
        <li>Yeni Proje olustur</li>
        <li>Sablon secin</li>
        <li>Surukle birak ile sayfayi duzenleyin</li>
        <li>Header/Footer/Menu ayarlayin</li>
        <li>Kaydedin ve onizleme yapin</li>
        <li>ZIP olarak disa aktarin</li>
    </ol>
</div>

<div class="sec">
    <h2>🏗️ Builder Bilesenleri</h2>
    <div class="grid">
        <div class="card"><h3>Header Builder</h3><p>Logo, menu, dil secimi</p></div>
        <div class="card"><h3>Footer Builder</h3><p>Menu, iletisim, sosyal</p></div>
        <div class="card"><h3>Menu Builder</h3><p>Alt menu, mega menu</p></div>
        <div class="card"><h3>Sayfa Builder</h3><p>Hakkimizda, Iletisim</p></div>
    </div>
</div>

<div class="sec">
    <h2>🔑 Lisanslama</h2>
    <div class="grid">
        <div class="card"><h3>Tek Domain</h3><p>Bir domain icin lisans</p></div>
        <div class="card"><h3>Acik Kaynak</h3><p>Sinirsiz kullanim</p></div>
    </div>
</div>

<div class="sec">
    <h2>💰 Satis Stratejisi</h2>
    <div class="box s"><strong>Hosting ile Entegrasyon:</strong></div>
    <ol style="line-height:2">
        <li>Musteri hosting satin alir</li>
        <li>Site Builder acilir</li>
        <li>Site kurulur ve yayinlanir</li>
    </ol>
    <div class="box w"><strong>Pazarlama:</strong> Ziyaretci bedava site olusturur, detayli ozellikler icin kayit olur.</div>
</div>

<?php elseif($page === 'module'): ?>

<?php 
$name = $_GET['name'] ?? '';
$modules = [
    'dunning'=>['name'=>'Dunning Sistemi','icon'=>'🔔','desc'=>'Otomatik odeme hatirlatmalari'],
    'abandoned-cart'=>['name'=>'Sepet Kurtarma','icon'=>'🛒','desc'=>'Brakilani sepetleri otomatik kurtar'],
    'coupons'=>['name'=>'Kupon Sistemi','icon'=>'🎟️','desc'=>'Indirim kodu ve kampanya'],
    'subscription'=>['name'=>'Abonelik','icon'=>'🔄','desc'=>'Otomatik yenileme'],
    'paytr'=>['name'=>'PayTR','icon'=>'💳','desc'=>'Turkiye odeme altyapisi','url'=>'https://www.paytr.com'],
    'iyzico'=>['name'=>'Iyzico','icon'=>'💳','desc'=>'Taksit ve 3D Secure','url'=>'https://www.iyzico.com'],
    'shopier'=>['name'=>'Shopier','icon'=>'🛒','desc'=>'Kolay entegrasyon','url'=>'https://www.shopier.com'],
    'paystack'=>['name'=>'Paystack','icon'=>'🌍','desc'=>'Afrika odemeleri'],
    'ai-design'=>['name'=>'AI Site Builder','icon'=>'🎨','desc'=>'OpenAI ile site tasarimi'],
    'ai-app'=>['name'=>'AI App Builder','icon'=>'📱','desc'=>'OpenAI ile uygulama'],
    'blog'=>['name'=>'Blog','icon'=>'📝','desc'=>'SEO dostu blog'],
    'live-chat'=>['name'=>'Canli Destek','icon'=>'💬','desc'=>'WhatsApp & AI chatbot'],
    'mobilebuilder'=>['name'=>'MobileBuilder Pro','icon'=>'📱','desc'=>'APK/AAB/PWA uygulama olusturucu','route'=>'admin/builder/mobile'],
    'sitebuilder'=>['name'=>'SiteBuilder Pro','icon'=>'🌐','desc'=>'Surukle birak site olusturucu','route'=>'admin/builder/site'],
];
$m = $modules[$name] ?? ['name'=>ucfirst($name),'icon'=>'📦','desc'=>'Modul detaylari'];
?>
<a href="?page=home" class="btn btn-s" style="display:inline-block;margin-bottom:20px;padding:10px 20px;text-decoration:none">← Geri</a>

<div class="hero" style="padding:35px"><h1><?= $m['icon'] ?> <?= $m['name'] ?></h1><p><?= $m['desc'] ?></p></div>

<div class="sec">
    <h3>📋 Ayarlar</h3>
    <?php if(!empty($m['url'])): ?>
    <div class="box s"><strong>🌐 Panel:</strong> <a href="<?= $m['url'] ?>" target="_blank"><?= $m['url'] ?></a></div>
    <?php endif; ?>
    <?php if(!empty($m['route'])): ?>
    <div class="box s"><strong>📍 Yol:</strong> Admin → Builder → <?= str_replace('admin/builder/','',$m['route']) ?></div>
    <?php endif; ?>
    <h4>Admin'de Nerede:</h4>
    <div class="code">Admin → <?= $m['name'] ?></div>
</div>
<?php endif; ?>
</div>
