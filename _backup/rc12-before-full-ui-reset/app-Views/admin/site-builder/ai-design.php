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
<style>
.ai-design-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
.ai-hero { background: linear-gradient(135deg, #0f172a, #1e40af, #06b6d4); color: #fff; padding: 48px; border-radius: 24px; text-align: center; margin-bottom: 32px; }
.ai-hero h1 { font-size: 2.5rem; margin-bottom: 16px; }
.ai-hero p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 24px; }
.ai-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 32px; }
.ai-feature { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; text-align: center; transition: all 0.3s; }
.ai-feature:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }
.ai-feature .icon { font-size: 3rem; margin-bottom: 12px; }
.ai-feature h3 { margin-bottom: 8px; color: #1e293b; }
.ai-feature p { color: #64748b; font-size: 0.95rem; }
.ai-form-card { background: #fff; border-radius: 24px; padding: 40px; box-shadow: 0 4px 30px rgba(0,0,0,0.08); }
.ai-form-group { margin-bottom: 24px; }
.ai-form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #1e293b; }
.ai-form-group input, .ai-form-group select, .ai-form-group textarea { width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: border-color 0.2s; }
.ai-form-group input:focus, .ai-form-group select:focus, .ai-form-group textarea:focus { outline: none; border-color: #2563eb; }
.ai-form-group textarea { min-height: 120px; resize: vertical; }
.ai-btn { display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #2563eb, #06b6d4); color: #fff; border: none; padding: 16px 32px; border-radius: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.ai-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(37, 99, 235, 0.4); }
.ai-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.ai-result { margin-top: 32px; background: #f8fafc; border-radius: 16px; padding: 24px; }
.ai-preview { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-top: 16px; }
.preset-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px; }
.preset-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; text-align: center; }
.preset-card:hover, .preset-card.selected { border-color: #2563eb; background: #eff6ff; }
.preset-card.selected::after { content: '✓'; position: absolute; top: 8px; right: 8px; width: 24px; height: 24px; background: #2563eb; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.preset-card h4 { margin: 0 0 8px; color: #1e293b; }
.preset-card p { font-size: 0.85rem; color: #64748b; margin: 0; }
</style>

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
