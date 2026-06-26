<section class="builder-public-page">
    <div class="builder-shell">
        <div class="builder-head">
            <span class="builder-badge">SiteBuilder Önizleme</span>
            <h1>Ziyaretçi olarak site tasarımını deneyin</h1>
            <p>Şablon seçebilir, canlı önizleme görebilir ve tasarım akışını inceleyebilirsiniz. ZIP export ve proje kaydetme için kayıt + paket gerekir.</p>
            <div class="builder-actions">
                <a class="site-btn" href="<?= url('sitebuilder/create-demo') ?>">Demo Oluştur</a>
                <a class="site-btn secondary" href="<?= url('sitebuilder/export') ?>">ZIP Oluştur</a>
                <a class="site-btn ghost" href="<?= url('urunler?group=sitebuilder') ?>">Paketleri Gör</a>
            </div>
        </div>
        <div class="builder-preview-grid">
            <aside class="builder-panel">
                <h3>Şablonlar</h3>
                <a class="<?= ($template ?? 'hosting')==='hosting'?'active':'' ?>" href="<?= url('sitebuilder/preview-public?template=hosting') ?>">Hosting Firması</a>
                <a class="<?= ($template ?? '')==='agency'?'active':'' ?>" href="<?= url('sitebuilder/preview-public?template=agency') ?>">Ajans / Kurumsal</a>
                <a class="<?= ($template ?? '')==='landing'?'active':'' ?>" href="<?= url('sitebuilder/preview-public?template=landing') ?>">Landing Page</a>
                <div class="builder-note">Çıktı alma ve kalıcı kayıt paket satın alma sonrası aktif olur.</div>
            </aside>
            <div class="site-preview-frame">
                <div class="preview-navbar"><strong><?= ($template ?? 'hosting')==='agency'?'Ahost Agency':'Ahost Cloud' ?></strong><span>Hizmetler</span><span>Fiyatlar</span><span>İletişim</span></div>
                <div class="preview-hero">
                    <div>
                        <small>Premium SaaS Şablonu</small>
                        <h2><?= ($template ?? 'hosting')==='landing'?'Yeni ürününüz için hızlı landing page':(($template ?? '')==='agency'?'Kurumsal ajans sitenizi dakikalar içinde kurun':'Hosting satış sitenizi SiteBuilder ile oluşturun') ?></h2>
                        <p>Hero, fiyatlandırma, özellikler, SSS ve iletişim bloklarını sürükle bırak mantığıyla düzenleyin.</p>
                        <button>Teklif Al</button>
                    </div>
                    <div class="preview-card"><b>SEO Skoru</b><strong>92</strong><span>Mobil uyumlu</span></div>
                </div>
                <div class="preview-cards"><div>⚡ Hızlı Kurulum</div><div>🎨 Tema Blokları</div><div>🔍 SEO Alanları</div></div>
            </div>
        </div>
    </div>
</section>