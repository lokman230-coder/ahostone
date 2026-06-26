<section class="builder-public-page">
    <div class="builder-shell builder-gate">
        <div class="builder-gate-card">
            <span class="builder-badge"><?= e(strtoupper($format ?? 'ZIP')) ?> ÇIKTI KİLİTLİ</span>
            <h1><?= ($kind ?? '') === 'mobilebuilder' ? 'APK/AAB oluşturmak için paket gerekli' : 'ZIP/site çıktısı oluşturmak için paket gerekli' ?></h1>
            <p>Önizleme ve tasarım denemesi ziyaretçilere açıktır. Gerçek çıktı oluşturma, dosya indirme, APK/AAB/ZIP üretimi ve proje kaydetme işlemleri için kayıt olup uygun paketi satın almanız gerekir.</p>
            <div class="builder-gate-actions">
                <a class="site-btn" href="<?= url($packageRoute ?? 'urunler') ?>">Paketleri İncele</a>
                <a class="site-btn secondary" href="<?= url('client/register') ?>">Kayıt Ol</a>
                <a class="site-btn ghost" href="<?= url(($kind ?? '') === 'mobilebuilder' ? 'mobilebuilder/preview-public' : 'sitebuilder/preview-public') ?>">Önizlemeye Dön</a>
            </div>
            <div class="builder-lock-list">
                <div>✓ Ziyaretçi: Şablon seçebilir ve önizleme yapabilir.</div>
                <div>✓ Müşteri: Proje kaydedebilir, düzenleyebilir ve paketine göre çıktı alabilir.</div>
                <div>✓ Paketli kullanıcı: ZIP / APK / AAB / PWA çıktısı oluşturabilir.</div>
            </div>
        </div>
    </div>
</section>