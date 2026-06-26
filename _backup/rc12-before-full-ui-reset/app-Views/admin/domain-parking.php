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

<style>
.parking-manager {}
.parking-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:24px }
.parking-header h1 { margin:0 0 4px }
.parking-header p { margin:0;color:#64748b }

.ao-grid.four { display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:24px }
.ao-grid.three { display:grid;grid-template-columns:repeat(3,1fr);gap:20px }
@media(max-width:1024px){.ao-grid.three{grid-template-columns:repeat(2,1fr)}.ao-grid.four{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.ao-grid.three,.ao-grid.four{grid-template-columns:1fr}}

.stat-card { display:flex;flex-direction:column;align-items:center;text-align:center;padding:24px }
.stat-icon { font-size:2rem;margin-bottom:8px }
.stat-value { font-size:1.75rem;font-weight:700;color:#1e293b }
.stat-label { font-size:.9rem;color:#64748b }

.domain-card { background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0 }
.domain-preview { padding:40px 20px;text-align:center;color:#fff }
.domain-name { font-size:1.25rem;font-weight:600;margin-bottom:8px }
.domain-price { font-size:1.5rem;font-weight:700 }
.domain-info { padding:16px }
.info-row { display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:.9rem }
.info-row .label { color:#64748b }
.domain-actions { display:flex;gap:8px;padding:16px;border-top:1px solid #e2e8f0 }
.domain-actions .ao-btn { flex:1;justify-content:center }

.domain-card.add-new { border:2px dashed #e2e8f0;background:#f8fafc;cursor:pointer;display:flex;align-items:center;justify-content:center;min-height:280px }
.domain-card.add-new:hover { border-color:#2563eb;background:#eff6ff }
.add-content { text-align:center }
.add-icon { font-size:3rem;color:#94a3b8;margin-bottom:8px }
.add-text { color:#64748b;font-weight:500 }

.card-header { margin-bottom:20px }
.card-header h3 { margin:0 }

.templates-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px }
@media(max-width:768px){.templates-grid{grid-template-columns:repeat(2,1fr)}}
.template-card { border:2px solid #e2e8f0;border-radius:12px;overflow:hidden;cursor:pointer }
.template-card.selected { border-color:#2563eb }
.template-preview { height:80px }
.template-name { padding:12px;text-align:center;font-size:.9rem;font-weight:500 }
</style>
