<?php
$template = $template ?? ($_GET['template'] ?? 'business');
$apps = [
    'business' => ['icon' => '🏢', 'name' => 'Kurumsal Uygulama', 'desc' => 'Hakkımızda, hizmetler, bildirim ve iletişim akışı.', 'accent' => 'Mavi SaaS'],
    'radio' => ['icon' => '📻', 'name' => 'Radyo Uygulaması', 'desc' => 'Canlı yayın, program rehberi, podcast ve WhatsApp.', 'accent' => 'Medya'],
    'shop' => ['icon' => '🛒', 'name' => 'Mağaza Uygulaması', 'desc' => 'Ürün kartları, sepet, ödeme ve müşteri hesabı.', 'accent' => 'E-Ticaret'],
];
$active = $apps[$template] ?? $apps['business'];
?>
<section class="ao-public-page builder-public-page ao-mobile-preview-page">
    <div class="ao-public-shell builder-shell">
        <div class="builder-head ao-builder-hero-card">
            <span class="builder-badge">MobileBuilder Önizleme</span>
            <h1>Ziyaretçi olarak mobil uygulama tasarımını deneyin</h1>
            <p>Telefon önizlemesi açıktır. APK, AAB, Android kaynak ZIP veya PWA export için kayıt olup paket satın almanız gerekir.</p>
            <div class="builder-actions">
                <a class="site-btn" href="<?= url('mobilebuilder/create-demo') ?>">Demo Oluştur</a>
                <a class="site-btn secondary" href="<?= url('mobilebuilder/build') ?>">APK/AAB Oluştur</a>
                <a class="site-btn ghost" href="<?= url('urunler?group=mobilebuilder') ?>">Paketleri Gör</a>
            </div>
        </div>

        <div class="mobile-preview-layout ao-builder-workspace">
            <aside class="builder-panel ao-builder-side-panel">
                <div class="ao-panel-title-row">
                    <span>📱</span>
                    <div>
                        <small>Uygulama şablonları</small>
                        <h3>Uygulama Tipi</h3>
                    </div>
                </div>
                <div class="ao-builder-template-list">
                    <?php foreach ($apps as $key => $item): ?>
                        <a class="ao-template-option <?= $key === $template ? 'active' : '' ?>" href="<?= url('mobilebuilder/preview-public?template=' . $key) ?>">
                            <span><?= $item['icon'] ?></span>
                            <b><?= e($item['name']) ?></b>
                            <small><?= e($item['accent']) ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="builder-note ao-builder-note">
                    <b>Build kuyruğu</b>
                    APK/AAB/PWA çıktısı paketli kullanıcılar için açılır.
                </div>
            </aside>

            <div class="phone-mock ao-phone-mock large">
                <div class="radio-status-bar"><span>9:41</span><span>📶 5G 🔋</span></div>
                <div class="phone-screen">
                    <div class="phone-app-header">
                        <span><?= $active['icon'] ?></span>
                        <div>
                            <small><?= e($active['accent']) ?></small>
                            <h3><?= e($active['name']) ?></h3>
                        </div>
                    </div>
                    <div class="phone-hero">
                        <b>Logo + Splash + Ana ekran</b>
                        <span><?= e($active['desc']) ?></span>
                    </div>
                    <div class="phone-list">
                        <span>🏠 Ana Sayfa</span>
                        <span>🔔 Bildirimler</span>
                        <span>💬 İletişim</span>
                    </div>
                    <div class="phone-bottom"><b>⌂</b><b>▣</b><b>☰</b></div>
                </div>
            </div>
        </div>
    </div>
</section>
