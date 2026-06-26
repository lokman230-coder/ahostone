<?php
/**
 * AI Mobile App Generator for MobileBuilder
 * OpenAI ile mobil uygulama oluşturma
 */

$openai_configured = false;
try {
    $openai_key = db()->query("SELECT setting_value FROM system_settings WHERE setting_key='module_openai_api_key'")->fetchColumn();
    $openai_configured = !empty($openai_key);
} catch(Throwable $e) {}
?>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
<div class="ai-app-container">
    <div class="ai-app-hero">
        <h1>📱 AI Mobil Uygulama Oluşturucu</h1>
        <p>OpenAI destekli yapay zeka ile iOS ve Android uygulamaları oluşturun. Flutter, PWA veya Native.</p>
        
        <?php if(!$openai_configured): ?>
        <div style="background:rgba(255,255,255,0.1);padding:16px;border-radius:12px;margin-top:16px">
            <p style="margin:0">⚠️ OpenAI API anahtarı yapılandırılmamış. <a href="<?= url('admin/settings/module-openai') ?>" style="color:#60a5fa">Ayarlara git</a></p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="ao-card">
        <h2 style="margin-bottom:24px">🚀 Uygulama Oluştur</h2>
        
        <form id="aiAppForm">
            <div class="ao-form-grid">
                <label class="full">
                    Uygulama Adı
                    <input type="text" name="app_name" placeholder="Ahost Mobile" required>
                </label>
                
                <label class="full">
                    Uygulama Açıklaması
                    <textarea name="app_description" placeholder="Kısa bir açıklama yazın..."></textarea>
                </label>
                
                <label>
                    Platform
                    <select name="platform">
                        <option value="pwa">PWA (Web uygulaması)</option>
                        <option value="flutter">Flutter (iOS + Android)</option>
                        <option value="react-native">React Native</option>
                    </select>
                </label>
                
                <label>
                    Uygulama Kategorisi
                    <select name="category">
                        <option value="business">İş / Kurumsal</option>
                        <option value="ecommerce">E-ticaret</option>
                        <option value="social">Sosyal Medya</option>
                        <option value="education">Eğitim</option>
                        <option value="health">Sağlık</option>
                        <option value="finance">Finans</option>
                        <option value="entertainment">Eğlence</option>
                        <option value="utility">Araç / Utility</option>
                    </select>
                </label>
            </div>
            
            <h3 style="margin-top:32px">📱 Platform Seçimi</h3>
            <div class="platform-cards">
                <div class="platform-card selected" data-platform="pwa">
                    <div class="icon">🌐</div>
                    <h3>PWA</h3>
                    <p>Web tabanlı, tüm platformlarda çalışır</p>
                </div>
                <div class="platform-card" data-platform="android">
                    <div class="icon">🤖</div>
                    <h3>Android</h3>
                    <p>Google Play için APK</p>
                </div>
                <div class="platform-card" data-platform="ios">
                    <div class="icon">🍎</div>
                    <h3>iOS</h3>
                    <p>App Store için IPA</p>
                </div>
            </div>
            
            <h3 style="margin-top:32px">✨ Gerekli Özellikler</h3>
            <div class="ao-grid two" style="gap:16px">
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="login"> Giriş / Kayıt</label>
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="dashboard"> Dashboard</label>
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="profile"> Profil Sayfası</label>
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="notifications"> Bildirimler</label>
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="chat"> Sohbet / Mesajlaşma</label>
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="cart"> Sepet / Ödeme</label>
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="calendar"> Takvim</label>
                <label style="display:flex;align-items:center;gap:10px"><input type="checkbox" name="features[]" value="camera"> Kamera / Fotoğraf</label>
            </div>
            
            <h3 style="margin-top:32px">🎨 Tasarım Tercihleri</h3>
            <div class="ao-form-grid">
                <label>
                    Renk Şeması
                    <select name="color_scheme">
                        <option value="blue">Mavi (Profesyonel)</option>
                        <option value="purple">Mor (Yaratıcı)</option>
                        <option value="green">Yeşil (Taze)</option>
                        <option value="dark">Karanlık (Modern)</option>
                    </select>
                </label>
                <label>
                    UI Stili
                    <select name="ui_style">
                        <option value="material">Material Design</option>
                        <option value="flat">Flat Design</option>
                        <option value="glass">Glass Morphism</option>
                        <option value="minimal">Minimal</option>
                    </select>
                </label>
            </div>
            
            <input type="hidden" name="selected_platform" id="selectedPlatform" value="pwa">
            
            <div style="text-align:center;margin-top:32px">
                <button type="submit" class="ao-btn" id="buildBtn" <?= !$openai_configured ? 'disabled' : '' ?> style="padding:16px 48px;font-size:1.1rem">
                    <span>✨</span> AI ile Uygulama Oluştur
                </button>
            </div>
        </form>
        
        <div class="app-preview" id="appPreview" style="display:none">
            <h3>🎉 Uygulamanız Hazır!</h3>
            <p>AI uygulama oluşturma işlemi tamamlandı.</p>
            
            <div class="phone-frame">
                <div class="phone-screen" id="phoneScreen">
                    <div style="background:linear-gradient(135deg,#667eea,#764ba2);height:100%;display:flex;align-items:center;justify-content:center;color:#fff;flex-direction:column">
                        <div style="font-size:4rem">📱</div>
                        <div style="font-size:1.5rem;font-weight:bold;margin-top:12px" id="previewAppName">Uygulama Adı</div>
                    </div>
                </div>
            </div>
            
            <div class="build-steps">
                <div class="build-step">
                    <div class="num">1</div>
                    <h4>Tasarım</h4>
                    <p>AI tasarım oluşturdu</p>
                </div>
                <div class="build-step">
                    <div class="num">2</div>
                    <h4> kodlama</h4>
                    <p>Kaynak kod hazır</p>
                </div>
                <div class="build-step">
                    <div class="num">3</div>
                    <h4>Derleme</h4>
                    <p>APK/IPA hazır</p>
                </div>
            </div>
            
            <div style="display:flex;gap:12px;justify-content:center">
                <a href="#" class="ao-btn" id="downloadBtn">📥 İndir</a>
                <a href="#" class="ao-btn secondary" id="previewBtn">👁 Önizle</a>
            </div>
        </div>
    </div>
    
    <div class="ao-card" style="margin-top:24px">
        <h3>📖 Nasıl Çalışır?</h3>
        <div class="build-steps">
            <div class="build-step">
                <div class="num">1</div>
                <h4>Tasarım Girin</h4>
                <p>Uygulama adı ve özellikleri belirtin</p>
            </div>
            <div class="build-step">
                <div class="num">2</div>
                <h4>AI Analiz</h4>
                <p>OpenAI tasarım ve kod oluşturur</p>
            </div>
            <div class="build-step">
                <div class="num">3</div>
                <h4>Build Center</h4>
                <p>Android/iOS için derleme yapılır</p>
            </div>
            <div class="build-step">
                <div class="num">4</div>
                <h4>İndirin</h4>
                <p>APK veya IPA dosyasını alın</p>
            </div>
        </div>
    </div>
</div>

<script>
// Platform selection
document.querySelectorAll('.platform-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.platform-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('selectedPlatform').value = this.dataset.platform;
    });
});

// Form submission
document.getElementById('aiAppForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('buildBtn');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Oluşturuluyor...';
    
    const formData = new FormData(this);
    
    // Show preview
    document.getElementById('appPreview').style.display = 'block';
    document.getElementById('previewAppName').textContent = formData.get('app_name') || 'Uygulama';
    
    // Simulate build (in real implementation, call AI API)
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<span>✨</span> AI ile Uygulama Oluştur';
    }, 2000);
});
</script>
