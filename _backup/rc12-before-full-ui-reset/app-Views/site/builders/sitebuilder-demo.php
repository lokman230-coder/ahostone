<section class="builder-public-page">
    <div class="builder-shell">
        <div class="builder-head">
            <span class="builder-badge">SiteBuilder Demo</span>
            <h1>Demo site oluştur</h1>
            <p>Ziyaretçi olarak şablon seçip web sitesi tasarımını önizleyebilirsiniz. ZIP export ve proje kaydetme için kayıt + paket gerekir.</p>
        </div>
        
        <!-- Quick Template Cards -->
        <div class="template-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <a href="<?= url('sitebuilder/preview-public?template=hosting') ?>" class="template-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">🌐</span>
                <h3 style="margin: 12px 0 4px;">Hosting</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;">Domain, hosting, cloud çözümleri</p>
            </a>
            <a href="<?= url('sitebuilder/preview-public?template=corporate') ?>" class="template-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">🏢</span>
                <h3 style="margin: 12px 0 4px;">Kurumsal</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;">Şirket tanıtımı, hizmetler</p>
            </a>
            <a href="<?= url('sitebuilder/preview-public?template=agency') ?>" class="template-card" style="background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">🎨</span>
                <h3 style="margin: 12px 0 4px;">Ajans</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;"> kreatif ajans, portfolyo</p>
            </a>
            <a href="<?= url('sitebuilder/preview-public?template=landing') ?>" class="template-card" style="background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">🚀</span>
                <h3 style="margin: 12px 0 4px;">Landing Page</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;">Ürün/hizmet tanıtımı</p>
            </a>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start;">
        <form class="demo-form" method="get" action="<?= url('sitebuilder/preview-public') ?>">
            <h2>Şablon Seçin</h2>
            <label for="template">Site Şablonu</label>
            <select name="template" id="template">
                <option value="hosting">Hosting Firması</option>
                <option value="agency">Ajans / Kurumsal</option>
                <option value="landing">Landing Page</option>
                <option value="corporate">Kurumsal Şirket</option>
                <option value="software">Yazılım Firması</option>
                <option value="ecommerce">E-Ticaret Ön Site</option>
                <option value="restaurant">Restoran</option>
                <option value="realestate">Emlak</option>
                <option value="radio">Radyo / Medya</option>
                <option value="news">Haber Portali</option>
            </select>
            
            <label for="sitename">Site Adı</label>
            <input type="text" id="sitename" name="sitename" placeholder="Örn: Ahost Cloud" maxlength="50">
            
            <label for="color">Ana Renk</label>
            <select id="color" name="color">
                <option value="blue">Mavi (#3B82F6)</option>
                <option value="green">Yeşil (#22C55E)</option>
                <option value="purple">Mor (#8B5CF6)</option>
                <option value="orange">Turuncu (#F97316)</option>
                <option value="dark">Koyu (#0F172A)</option>
            </select>
            
            <button type="submit" class="site-btn">Önizleme Oluştur</button>
        </form>
        
        <div class="builder-preview-grid">
            <div class="site-preview-frame">
                <div class="preview-navbar">
                    <strong>Logo</strong>
                    <span>Hosting</span>
                    <span>Domain</span>
                    <span>Destek</span>
                </div>
                <div class="preview-hero">
                    <div>
                        <small>HOSTING FİRMASI</small>
                        <h2>Dijital Dünyada<br>Güvenilir Çözümler</h2>
                        <p>Domain, hosting, SSL ve cloud çözümleri tek panelde.</p>
                        <button>Hemen Başla</button>
                    </div>
                    <div class="preview-card">
                        <strong>99.9%</strong>
                        <small>Çalışma Süresi</small>
                    </div>
                </div>
                <div class="preview-cards">
                    <div>SSD Hosting</div>
                    <div>Cloud Sunucu</div>
                    <div>SSL Sertifika</div>
                </div>
            </div>
        </div>
        </div>
        
        <div class="builder-actions" style="margin-top: 48px; flex-direction: column; align-items: center;">
            <h3 style="color: #fff; margin-bottom: 16px;">Demo ile Ne Yapabilirsiniz?</h3>
            <div style="text-align: left; color: #94a3b8; line-height: 1.8; max-width: 600px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <p>✓ Şablon seçerek web sitesi tasarımını önizle</p>
                <p>✓ Hero, fiyatlandırma ve blokları gör</p>
                <p>✓ Header/Footer yapısını incele</p>
                <p>✓ Sürükle-bırak blok sistemini test et</p>
                <p>✓ SEO ve performans optimizasyonlarını gör</p>
                <p>✓ Radyo/Medya şablonları ile canlı yayın sayfası</p>
            </div>
            <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                <a class="site-btn" href="<?= url('sitebuilder/preview-public?template=hosting') ?>">Demo Önizle</a>
                <a class="site-btn secondary" href="<?= url('sitebuilder/export') ?>">ZIP Oluştur</a>
                <a class="site-btn ghost" href="<?= url('marketplace') ?>">Hazır Temalar</a>
            </div>
        </div>
    </div>
</section>