<?php
/**
 * Domain Parking
 * Ahost One - Site temasına uyumlu
 */
$domains = db()->query("SELECT * FROM domain_parking ORDER BY created_at DESC")->fetchAll();
?>
<div class="parking-manager">
    <div class="parking-header">
        <div class="header-left">
            <h1>🅿️ Domain Parking</h1>
            <p>Satılık domainleriniz için profesyonel landing sayfaları</p>
        </div>
        <button class="ao-btn ao-btn-primary">+ Domain Ekle</button>
    </div>

    <!-- Stats -->
    <div class="ao-grid four">
        <div class="ao-card stat-card">
            <div class="stat-icon">🌐</div>
            <div class="stat-value">48</div>
            <div class="stat-label">Park Edilen Domain</div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon">👁️</div>
            <div class="stat-value">12,456</div>
            <div class="stat-label">Toplam Görüntülenme</div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon">📧</div>
            <div class="stat-value">234</div>
            <div class="stat-label">İletişim Formu</div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">₺45,000</div>
            <div class="stat-label">Tahmini Değer</div>
        </div>
    </div>

    <!-- Domains Grid -->
    <div class="ao-grid three">
        <?php foreach($domains as $domain): ?>
        <div class="domain-card">
            <div class="domain-preview" style="background: linear-gradient(135deg, #667eea, #764ba2)">
                <div class="preview-content">
                    <div class="domain-name"><?= e($domain['domain']) ?></div>
                    <div class="domain-price"><?= $domain['price'] ? '₺'.number_format($domain['price']) : 'Fiyat Belirtilmedi' ?></div>
                </div>
            </div>
            <div class="domain-info">
                <div class="info-row">
                    <span class="label">Tema:</span>
                    <span class="value"><?= e($domain['template'] ?? 'Varsayılan') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Görüntülenme:</span>
                    <span class="value"><?= number_format($domain['views'] ?? 0) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Durum:</span>
                    <span class="value"><span class="status-dot <?= $domain['is_active'] ? 'online' : '' ?>"></span> <?= $domain['is_active'] ? 'Aktif' : 'Pasif' ?></span>
                </div>
            </div>
            <div class="domain-actions">
                <button class="ao-btn ao-btn-secondary">Düzenle</button>
                <button class="ao-btn ao-btn-primary">Önizle</button>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Add New Card -->
        <div class="domain-card add-new">
            <div class="add-content">
                <div class="add-icon">+</div>
                <div class="add-text">Yeni Domain Ekle</div>
            </div>
        </div>
    </div>

    <!-- Templates -->
    <div class="ao-card" style="margin-top:24px">
        <div class="card-header">
            <h3>🎨 Parking Şablonları</h3>
        </div>
        <div class="templates-grid">
            <div class="template-card selected">
                <div class="template-preview" style="background:linear-gradient(135deg,#667eea,#764ba2)"></div>
                <div class="template-name">Modern Gradient</div>
            </div>
            <div class="template-card">
                <div class="template-preview" style="background:linear-gradient(135deg,#1e40af,#06b6d4)"></div>
                <div class="template-name">Business Blue</div>
            </div>
            <div class="template-card">
                <div class="template-preview" style="background:linear-gradient(135deg,#10b981,#06b6d4)"></div>
                <div class="template-name">Nature Green</div>
            </div>
            <div class="template-card">
                <div class="template-preview" style="background:#1e293b"></div>
                <div class="template-name">Dark Pro</div>
            </div>
        </div>
    </div>
</div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
