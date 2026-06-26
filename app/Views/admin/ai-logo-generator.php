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
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
