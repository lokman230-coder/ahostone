<?php
/**
 * AI Design Assistant for SiteBuilder
 * OpenAI ile site tasarımı oluşturma
 */

// Check if OpenAI module is configured
$openai_configured = false;
try {
    $openai_key = db()->query("SELECT setting_value FROM system_settings WHERE setting_key='module_openai_api_key'")->fetchColumn();
    $openai_configured = !empty($openai_key);
} catch(Throwable $e) {}

$page_id = (int)($_GET['page_id'] ?? 0);
$project_id = (int)($_GET['project_id'] ?? 0);
?>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
<div class="ai-design-container">
    <div class="ai-hero">
        <h1>🎨 AI Site Tasarım Asistanı</h1>
        <p>OpenAI destekli yapay zeka ile dakikalar içinde profesyonel site tasarımı oluşturun.</p>
        <?php if(!$openai_configured): ?>
        <div style="background:rgba(255,255,255,0.1);padding:16px;border-radius:12px;margin-top:16px">
            <p style="margin:0">⚠️ OpenAI API anahtarı yapılandırılmamış. <a href="<?= url('admin/settings/module-openai') ?>" style="color:#60a5fa">Ayarlara git</a></p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="ai-features">
        <div class="ai-feature">
            <div class="icon">🚀</div>
            <h3>Hızlı Üretim</h3>
            <p>Dakikalar içinde tam site tasarımı oluşturun.</p>
        </div>
        <div class="ai-feature">
            <div class="icon">🎯</div>
            <h3>Profesyonel Tasarım</h3>
            <p>Modern ve şık tasarımlar elde edin.</p>
        </div>
        <div class="ai-feature">
            <div class="icon">📱</div>
            <h3>Mobil Uyumlu</h3>
            <p>Tüm cihazlarda mükemmel görünüm.</p>
        </div>
        <div class="ai-feature">
            <div class="icon">✨</div>
            <h3>Kolay Düzenleme</h3>
            <p>Builder ile istediğiniz gibi özelleştirin.</p>
        </div>
    </div>
    
    <div class="ai-form-card">
        <h2 style="margin-bottom:24px">📝 Site Tasarımı Oluştur</h2>
        
        <form id="aiDesignForm">
            <div class="ai-form-group">
                <label>Site Türü</label>
                <select name="site_type" id="siteType" required>
                    <option value="">Seçin...</option>
                    <option value="landing">Landing Page</option>
                    <option value="portfolio">Portfolyo</option>
                    <option value="blog">Blog</option>
                    <option value="business">Kurumsal Site</option>
                    <option value="ecommerce">E-ticaret</option>
                    <option value="saas">SaaS Platform</option>
                    <option value="agency">Ajans</option>
                </select>
            </div>
            
            <div class="ai-form-group">
                <label>Şirket/Site Adı</label>
                <input type="text" name="site_name" placeholder="Ahost One" required>
            </div>
            
            <div class="ai-form-group">
                <label>Slogan veya Açıklama</label>
                <input type="text" name="tagline" placeholder="Hosting ve domain çözümleri">
            </div>
            
            <div class="ai-form-group">
                <label>Hizmetler/Ürünler (virgülle ayırın)</label>
                <input type="text" name="services" placeholder="Hosting, Domain, SSL, VPS">
            </div>
            
            <div class="ai-form-group">
                <label>Renk Paleti</label>
                <select name="color_scheme">
                    <option value="modern">Modern Mavi (Profesyonel)</option>
                    <option value="elegant">Şık Siyah & Altın</option>
                    <option value="fresh">Taze Yeşil</option>
                    <option value="creative">Yaratıcı Mor</option>
                    <option value="warm">Sıcak Turuncu</option>
                    <option value="minimal">Minimal Beyaz</option>
                </select>
            </div>
            
            <div class="ai-form-group">
                <label>Tasarım Talebi (Opsiyonel)</label>
                <textarea name="custom_prompt" placeholder="Örn: Modern, minimalist bir tasarım olsun. Hero section büyük olsun..."></textarea>
            </div>
            
            <div class="ai-form-group">
                <label>Hazır Şablonlar</label>
                <div class="preset-cards">
                    <div class="preset-card" data-preset="startup">
                        <h4>🚀 Startup</h4>
                        <p>Modern girişim siteleri için</p>
                    </div>
                    <div class="preset-card" data-preset="saas">
                        <h4>☁️ SaaS</h4>
                        <p>Yazılım platformları için</p>
                    </div>
                    <div class="preset-card" data-preset="portfolio">
                        <h4>👤 Portfolyo</h4>
                        <p>Kişisel ve profesyonel</p>
                    </div>
                    <div class="preset-card" data-preset="agency">
                        <h4>🏢 Ajans</h4>
                        <p>Dijital ajanslar için</p>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="page_id" value="<?= $page_id ?>">
            <input type="hidden" name="project_id" value="<?= $project_id ?>">
            
            <button type="submit" class="ai-btn" id="generateBtn" <?= !$openai_configured ? 'disabled' : '' ?>>
                <span>✨</span> AI ile Tasarım Oluştur
            </button>
        </form>
        
        <div class="ai-result" id="aiResult" style="display:none">
            <h3>🎉 Tasarım Oluşturuldu!</h3>
            <p>AI tasarımınız hazır. Şimdi düzenlemek ister misiniz?</p>
            <div style="display:flex;gap:12px;margin-top:16px">
                <a href="#" class="ao-btn" id="editDesignBtn">🖊 Tasarımı Düzenle</a>
                <a href="#" class="ao-btn secondary" id="previewDesignBtn">👁 Önizle</a>
            </div>
        </div>
    </div>
</div>

<script>
const presets = document.querySelectorAll('.preset-card');
presets.forEach(card => {
    card.addEventListener('click', function() {
        presets.forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        
        // Auto-fill form based on preset
        const preset = this.dataset.preset;
        const siteType = document.getElementById('siteType');
        
        switch(preset) {
            case 'startup':
                siteType.value = 'landing';
                break;
            case 'saas':
                siteType.value = 'saas';
                break;
            case 'portfolio':
                siteType.value = 'portfolio';
                break;
            case 'agency':
                siteType.value = 'agency';
                break;
        }
    });
});

document.getElementById('aiDesignForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Oluşturuluyor...';
    
    // Collect form data
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= url('api/ai-generate-site') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData)
        });
        
        const result = await response.json();
        
        if(result.success) {
            document.getElementById('aiResult').style.display = 'block';
            document.getElementById('editDesignBtn').href = '<?= url('admin/site-builder/editor?id=') ?>' + result.page_id;
            document.getElementById('previewDesignBtn').href = '<?= url('sitebuilder/preview?id=') ?>' + result.page_id;
        } else {
            alert('Hata: ' + (result.error || 'Bilinmeyen hata'));
        }
    } catch(err) {
        alert('Bağlantı hatası: ' + err.message);
    }
    
    btn.disabled = false;
    btn.innerHTML = '<span>✨</span> AI ile Tasarım Oluştur';
});
</script>
