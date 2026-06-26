<section class="builder-public-page">
    <div class="builder-shell">
        <div class="builder-head">
            <span class="builder-badge">MobileBuilder Demo</span>
            <h1>Mobil uygulama demo oluştur</h1>
            <p>Ziyaretçi olarak şablon seçip mobil uygulama tasarımını önizleyebilirsiniz. APK/AAB çıktısı için kayıt + paket gerekir.</p>
        </div>
        
        <!-- Quick Template Cards -->
        <div class="template-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <a href="<?= url('mobilebuilder/preview-public?template=radio') ?>" class="template-card" style="background: linear-gradient(135deg, #e91e63, #9c27b0); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">📻</span>
                <h3 style="margin: 12px 0 4px;">Radyo Uygulaması</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;">Canlı dinleme, sosyal medya, WhatsApp</p>
            </a>
            <a href="<?= url('mobilebuilder/preview-public?template=business') ?>" class="template-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">🏢</span>
                <h3 style="margin: 12px 0 4px;">Kurumsal</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;">Hakkımızda, hizmetler, iletişim</p>
            </a>
            <a href="<?= url('mobilebuilder/preview-public?template=restaurant') ?>" class="template-card" style="background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">🍽</span>
                <h3 style="margin: 12px 0 4px;">Restoran</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;">Menü, rezervasyon, sipariş</p>
            </a>
            <a href="<?= url('mobilebuilder/preview-public?template=ecommerce') ?>" class="template-card" style="background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 16px; padding: 24px; text-decoration: none; color: #fff;">
                <span style="font-size: 48px;">🛒</span>
                <h3 style="margin: 12px 0 4px;">E-Ticaret</h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0;">Ürünler, sepet, ödeme</p>
            </a>
        </div>
        
        <div class="demo-form-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start;">
        <form class="demo-form" method="get" action="<?= url('mobilebuilder/preview-public') ?>">
            <h2>Şablon Seçin</h2>
            <label for="template">Uygulama Şablonu</label>
            <select name="template" id="template">
                <option value="business">Kurumsal Uygulama</option>
                <option value="realestate">Emlak Uygulaması</option>
                <option value="restaurant">Restoran Uygulaması</option>
                <option value="radio">Radyo Uygulaması</option>
                <option value="ecommerce">E-Ticaret Uygulaması</option>
                <option value="news">Haber Uygulaması</option>
                <option value="education">Eğitim Uygulaması</option>
                <option value="blank">Boş Uygulama</option>
            </select>
            
            <label for="appname">Uygulama Adı</label>
            <input type="text" id="appname" name="appname" placeholder="Örn: Ahost Cloud" maxlength="50">
            
            <label for="color">Ana Renk</label>
            <select id="color" name="color">
                <option value="blue">Mavi (#3B82F6)</option>
                <option value="green">Yeşil (#22C55E)</option>
                <option value="purple">Mor (#8B5CF6)</option>
                <option value="orange">Turuncu (#F97316)</option>
                <option value="red">Kırmızı (#EF4444)</option>
                <option value="pink">Pembe (#E91E63)</option>
            </select>
            
            <button type="submit" class="site-btn">Önizleme Oluştur</button>
        </form>
        
        <div class="mobile-preview-container">
            <div class="mobile-preview-frame">
                <div class="mobile-screen">
                    <div class="mobile-header">
                        <small>Kurumsal Uygulama</small>
                    </div>
                    <div class="mobile-content">
                        <div class="mobile-card">
                            <h4>Ana Sayfa</h4>
                            <p>Sürükle-bırak bloklar ile hızlı tasarım</p>
                        </div>
                        <div class="mobile-card">
                            <h4>Ürünler</h4>
                            <p>E-ticaret ve katalog yönetimi</p>
                        </div>
                        <div class="mobile-card">
                            <h4>Sepet</h4>
                            <p>Alışveriş ve ödeme akışı</p>
                        </div>
                    </div>
                    <div class="mobile-bottom-nav">
                        <span>🏠</span>
                        <span>📦</span>
                        <span>🛒</span>
                        <span>👤</span>
                    </div>
                </div>
            </div>
            
            <div class="builder-actions" style="flex-direction: column; align-items: center; margin-top: 24px;">
                <h3 style="color: #fff; margin-bottom: 16px;">Demo ile Ne Yapabilirsiniz?</h3>
                <div style="text-align: left; color: #94a3b8; line-height: 1.8;">
                    <p>✓ Şablon seçerek mobil uygulama tasarımını önizle</p>
                    <p>✓ Menü, bottom bar ve CTA tasarımlarını incele</p>
                    <p>✓ Renk ve tema değişikliklerini gör</p>
                    <p>✓ PWA ve Android görünümlerini test et</p>
                    <br>
                    <p style="color: #fbbf24;">⚠ APK/AAB çıktısı için kayıt + paket gerekir</p>
                </div>
                <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                    <a class="site-btn secondary" href="<?= url('mobilebuilder/build') ?>">APK/AAB Oluştur</a>
                    <a class="site-btn ghost" href="<?= url('mobil-uygulama') ?>">Mobil Hizmetleri Gör</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>