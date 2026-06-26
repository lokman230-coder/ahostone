<section class="builder-public-page">
    <div class="builder-shell">
        <div class="builder-head">
            <span class="builder-badge">MobileBuilder Önizleme</span>
            <h1>Ziyaretçi olarak mobil uygulama tasarımını deneyin</h1>
            <p>Telefon önizlemesi açıktır. APK, AAB, Android kaynak ZIP veya PWA export için kayıt olup paket satın almanız gerekir.</p>
            <div class="builder-actions">
                <a class="site-btn" href="<?= url('mobilebuilder/create-demo') ?>">Demo Oluştur</a>
                <a class="site-btn secondary" href="<?= url('mobilebuilder/build') ?>">APK/AAB Oluştur</a>
                <a class="site-btn ghost" href="<?= url('urunler?group=mobilebuilder') ?>">Paketleri Gör</a>
            </div>
        </div>
        <div class="mobile-preview-layout">
            <aside class="builder-panel">
                <h3>Uygulama Tipi</h3>
                <a class="<?= ($template ?? 'business')==='business'?'active':'' ?>" href="<?= url('mobilebuilder/preview-public?template=business') ?>">Kurumsal</a>
                <a class="<?= ($template ?? '')==='radio'?'active':'' ?>" href="<?= url('mobilebuilder/preview-public?template=radio') ?>">Radyo</a>
                <a class="<?= ($template ?? '')==='shop'?'active':'' ?>" href="<?= url('mobilebuilder/preview-public?template=shop') ?>">E-Ticaret</a>
                <div class="builder-note">Build kuyruğu paketli kullanıcılar için açılır.</div>
            </aside>
            <div class="phone-mock">
                <div class="phone-top"></div>
                <div class="phone-screen">
                    <h3><?= ($template ?? '')==='radio'?'Radyo Uygulaması':(($template ?? '')==='shop'?'Mağaza Uygulaması':'Kurumsal Uygulama') ?></h3>
                    <div class="phone-hero">Logo + Splash + Ana ekran</div>
                    <div class="phone-list"><span>🏠 Ana Sayfa</span><span>🔔 Bildirimler</span><span>💬 İletişim</span></div>
                    <div class="phone-bottom"><b>⌂</b><b>▣</b><b>☰</b></div>
                </div>
            </div>
        </div>
    </div>
</section>