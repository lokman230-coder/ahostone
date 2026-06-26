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
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
