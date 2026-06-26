<?php
/**
 * AI Logo Generator
 * Ahost One - Site temasına uyumlu
 */
$styles = \AhostModule_ai_logo_generator::getStyles();
?>
<div class="logo-generator">
    <div class="ao-grid two">
        <!-- Generator Form -->
        <div class="ao-card">
            <h3>🎨 Logo Oluştur</h3>
            <form id="logoForm">
                <div class="ao-form-group">
                    <label>Marka Adı</label>
                    <input type="text" class="ao-input" placeholder="Örn: TechStart" id="brandName">
                </div>
                
                <div class="ao-form-group">
                    <label>Slogan (Opsiyonel)</label>
                    <input type="text" class="ao-input" placeholder="Örn: Innovation Delivered">
                </div>
                
                <div class="ao-form-group">
                    <label>Logo Stili</label>
                    <div class="style-grid">
                        <?php foreach($styles as $style): ?>
                        <div class="style-option" data-style="<?= $style['id'] ?>">
                            <div class="style-preview"><?= $style['preview'] ?></div>
                            <div class="style-name"><?= $style['name'] ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="ao-form-group">
                    <label>Renk Tercihi</label>
                    <div class="color-picker">
                        <input type="color" id="primaryColor" value="#2563eb">
                        <input type="color" id="secondaryColor" value="#06b6d4">
                    </div>
                </div>
                
                <button type="submit" class="ao-btn ao-btn-primary ao-btn-block">
                    ✨ Logo Oluştur
                </button>
            </form>
        </div>
        
        <!-- Preview -->
        <div class="ao-card preview-card">
            <h3>📸 Önizleme</h3>
            <div class="logo-preview" id="logoPreview">
                <div class="preview-placeholder">
                    <div class="placeholder-icon">🎨</div>
                    <p>Logo önizlemesi burada görünecek</p>
                </div>
            </div>
            <div class="preview-actions" style="display:none" id="previewActions">
                <button class="ao-btn ao-btn-secondary">⬇️ İndir (PNG)</button>
                <button class="ao-btn ao-btn-secondary">⬇️ İndir (SVG)</button>
                <button class="ao-btn ao-btn-primary">🔄 Yeniden Oluştur</button>
            </div>
        </div>
    </div>
    
    <!-- History -->
    <div class="ao-card" style="margin-top:24px">
        <h3>📁 Oluşturulan Logolar</h3>
        <div class="logo-history">
            <div class="history-item">
                <div class="history-preview" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                    <span style="color:#fff;font-weight:bold">TechStart</span>
                </div>
                <div class="history-info">
                    <strong>TechStart</strong>
                    <span>Modern stili • 2 saat önce</span>
                </div>
                <div class="history-actions">
                    <button class="ao-btn-icon">👁️</button>
                    <button class="ao-btn-icon">⬇️</button>
                    <button class="ao-btn-icon">🗑️</button>
                </div>
            </div>
            <div class="history-item">
                <div class="history-preview" style="background:linear-gradient(135deg,#10b981,#06b6d4)">
                    <span style="color:#fff;font-weight:bold">GreenCo</span>
                </div>
                <div class="history-info">
                    <strong>GreenCo</strong>
                    <span>Klasik stili • 1 gün önce</span>
                </div>
                <div class="history-actions">
                    <button class="ao-btn-icon">👁️</button>
                    <button class="ao-btn-icon">⬇️</button>
                    <button class="ao-btn-icon">🗑️</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.logo-generator {}
.style-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 8px; }
.style-option { padding: 16px 12px; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.2s; }
.style-option:hover { border-color: #3b82f6; }
.style-option.selected { border-color: #2563eb; background: #eff6ff; }
.style-preview { font-size: 1.5rem; margin-bottom: 8px; }
.style-name { font-size: 0.85rem; font-weight: 500; color: #475569; }

.color-picker { display: flex; gap: 12px; margin-top: 8px; }
.color-picker input[type="color"] { width: 50px; height: 50px; border: none; border-radius: 12px; cursor: pointer; }

.preview-card { text-align: center; }
.logo-preview { min-height: 300px; background: #f8fafc; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 20px 0; }
.preview-placeholder { text-align: center; color: #94a3b8; }
.placeholder-icon { font-size: 4rem; margin-bottom: 12px; }

.history-item { display: flex; align-items: center; gap: 16px; padding: 16px; border-bottom: 1px solid #e2e8f0; }
.history-item:last-child { border-bottom: none; }
.history-preview { width: 80px; height: 80px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.history-info { flex: 1; }
.history-info strong { display: block; font-size: 1rem; color: #1e293b; }
.history-info span { font-size: 0.85rem; color: #64748b; }
.history-actions { display: flex; gap: 8px; }
</style>
