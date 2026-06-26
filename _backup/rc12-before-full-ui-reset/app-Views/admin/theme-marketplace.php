<?php
/**
 * Theme Marketplace
 * Ahost One - Site temasına uyumlu
 */
$themes = [
    ['id'=>1,'name'=>'Nexus Dark','category'=>'Admin','price'=>199,'sales'=>234,'rating'=>4.8,'preview'=>'🌙'],
    ['id'=>2,'name'=>'Ocean Blue','category'=>'Admin','price'=>149,'sales'=>156,'rating'=>4.6,'preview'=>'🌊'],
    ['id'=>3,'name'=>'Forest Green','category'=>'Landing','price'=>99,'sales'=>89,'rating'=>4.9,'preview'=>'🌲'],
    ['id'=>4,'name'=>'Sunset Pro','category'=>'Landing','price'=>129,'sales'=>67,'rating'=>4.7,'preview'=>'🌅'],
    ['id'=>5,'name'=>'Minimal Light','category'=>'Universal','price'=>79,'sales'=>445,'rating'=>5.0,'preview'=>'☀️'],
    ['id'=>6,'name'=>'Tech Dark','category'=>'Admin','price'=>199,'sales'=>123,'rating'=>4.5,'preview'=>'💻'],
];
?>
<div class="marketplace">
    <!-- Header -->
    <div class="marketplace-header">
        <div class="header-left">
            <h1>🎨 Tema Marketi</h1>
            <p>Profesyonel admin panelleri ve landing page temaları</p>
        </div>
        <div class="header-actions">
            <button class="ao-btn ao-btn-primary">+ Tema Yükle</button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <div class="filter-tabs">
            <button class="filter-tab active">Tümü</button>
            <button class="filter-tab">Admin Paneli</button>
            <button class="filter-tab">Landing Page</button>
            <button class="filter-tab">E-ticaret</button>
        </div>
        <div class="filter-sort">
            <select class="ao-select">
                <option>En Çok Satan</option>
                <option>En Yüksek Puan</option>
                <option>Fiyat (Düşük-Yüksek)</option>
                <option>Fiyat (Yüksek-Düşük)</option>
            </select>
        </div>
    </div>

    <!-- Themes Grid -->
    <div class="themes-grid">
        <?php foreach($themes as $theme): ?>
        <div class="theme-card">
            <div class="theme-preview">
                <span class="preview-icon"><?= $theme['preview'] ?></span>
                <div class="preview-overlay">
                    <button class="ao-btn ao-btn-primary">Önizle</button>
                    <button class="ao-btn ao-btn-secondary">Satın Al</button>
                </div>
            </div>
            <div class="theme-info">
                <div class="theme-category"><?= $theme['category'] ?></div>
                <h4 class="theme-name"><?= $theme['name'] ?></h4>
                <div class="theme-meta">
                    <div class="theme-rating">
                        <span class="stars"><?= str_repeat('⭐', floor($theme['rating'])) ?></span>
                        <span class="rating"><?= $theme['rating'] ?></span>
                    </div>
                    <div class="theme-sales"><?= $theme['sales'] ?> satış</div>
                </div>
                <div class="theme-price">
                    <span class="price">₺<?= $theme['price'] ?></span>
                    <button class="buy-btn">Sepete Ekle</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Stats -->
    <div class="ao-card stats-card">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-icon">🎨</span>
                <div class="stat-content">
                    <strong>48</strong>
                    <span>Toplam Tema</span>
                </div>
            </div>
            <div class="stat-item">
                <span class="stat-icon">💰</span>
                <div class="stat-content">
                    <strong>₺45,890</strong>
                    <span>Toplam Satış</span>
                </div>
            </div>
            <div class="stat-item">
                <span class="stat-icon">👥</span>
                <div class="stat-content">
                    <strong>2,340</strong>
                    <span>Mutlu Müşteri</span>
                </div>
            </div>
            <div class="stat-item">
                <span class="stat-icon">⭐</span>
                <div class="stat-content">
                    <strong>4.7</strong>
                    <span>Ortalama Puan</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.marketplace {}
.marketplace-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.marketplace-header h1 { margin: 0 0 4px; font-size: 1.75rem; }
.marketplace-header p { margin: 0; color: #64748b; }

.filters { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
.filter-tabs { display: flex; gap: 8px; }
.filter-tab { padding: 10px 20px; background: #f1f5f9; border: none; border-radius: 10px; cursor: pointer; font-weight: 500; color: #64748b; transition: all 0.2s; }
.filter-tab:hover { background: #e2e8f0; }
.filter-tab.active { background: #2563eb; color: #fff; }

.themes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; margin-bottom: 24px; }
.theme-card { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; transition: all 0.3s; }
.theme-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }
.theme-preview { height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.preview-icon { font-size: 4rem; }
.preview-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.8); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; opacity: 0; transition: opacity 0.3s; }
.theme-card:hover .preview-overlay { opacity: 1; }
.preview-overlay .ao-btn { min-width: 140px; }

.theme-info { padding: 20px; }
.theme-category { font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.theme-name { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin: 4px 0 12px; }
.theme-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.theme-rating .stars { font-size: 0.9rem; }
.theme-rating .rating { font-size: 0.85rem; color: #64748b; margin-left: 4px; }
.theme-sales { font-size: 0.85rem; color: #64748b; }
.theme-price { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #e2e8f0; }
.theme-price .price { font-size: 1.25rem; font-weight: 700; color: #2563eb; }
.buy-btn { padding: 8px 16px; background: #f1f5f9; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.buy-btn:hover { background: #2563eb; color: #fff; }

.stats-card { margin-top: 24px; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
@media(max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
.stat-item { display: flex; align-items: center; gap: 16px; }
.stat-icon { font-size: 2rem; }
.stat-content strong { display: block; font-size: 1.25rem; color: #1e293b; }
.stat-content span { font-size: 0.85rem; color: #64748b; }
</style>
