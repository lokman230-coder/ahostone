<?php
/**
 * SSL Auto-Install
 * Ahost One - Site temasına uyumlu
 */
$certificates = db()->query("SELECT * FROM ssl_certificates ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>
<div class="ssl-manager">
    <!-- Header -->
    <div class="ssl-header">
        <div class="header-left">
            <h1>🔒 SSL Yönetimi</h1>
            <p>Let's Encrypt ve AutoSSL otomatik kurulum</p>
        </div>
        <button class="ao-btn ao-btn-primary">
            + Yeni Sertifika
        </button>
    </div>

    <!-- Stats -->
    <div class="ao-grid three">
        <div class="ao-card stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#16a34a">✅</div>
            <div class="stat-content">
                <div class="stat-value">24</div>
                <div class="stat-label">Aktif Sertifika</div>
            </div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706">⏰</div>
            <div class="stat-content">
                <div class="stat-value">5</div>
                <div class="stat-label">Yakında Bitecek</div>
            </div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#dc2626">❌</div>
            <div class="stat-content">
                <div class="stat-value">2</div>
                <div class="stat-label">Süresi Dolmuş</div>
            </div>
        </div>
    </div>

    <!-- Certificates List -->
    <div class="ao-card">
        <div class="card-header">
            <h3>Sertifikalar</h3>
            <div class="header-actions">
                <select class="ao-select">
                    <option>Tümü</option>
                    <option>Aktif</option>
                    <option>Yakında Bitecek</option>
                    <option>Süresi Dolmuş</option>
                </select>
            </div>
        </div>
        
        <table class="ao-table">
            <thead>
                <tr>
                    <th>Domain</th>
                    <th>Tür</th>
                    <th>Durum</th>
                    <th>Bitiş Tarihi</th>
                    <th>Kalan Gün</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>example.com</strong></td>
                    <td><span class="type-badge letsencrypt">Let's Encrypt</span></td>
                    <td><span class="status-badge active">Aktif</span></td>
                    <td>15 Mar 2025</td>
                    <td><span class="days-left">67 gün</span></td>
                    <td>
                        <button class="ao-btn-icon" title="Yenile">🔄</button>
                        <button class="ao-btn-icon" title="Detay">👁️</button>
                    </td>
                </tr>
                <tr>
                    <td><strong>subdomain.example.com</strong></td>
                    <td><span class="type-badge autossl">AutoSSL</span></td>
                    <td><span class="status-badge active">Aktif</span></td>
                    <td>22 Mar 2025</td>
                    <td><span class="days-left">74 gün</span></td>
                    <td>
                        <button class="ao-btn-icon" title="Yenile">🔄</button>
                        <button class="ao-btn-icon" title="Detay">👁️</button>
                    </td>
                </tr>
                <tr style="background:#fef3c7">
                    <td><strong>test-site.com</strong></td>
                    <td><span class="type-badge letsencrypt">Let's Encrypt</span></td>
                    <td><span class="status-badge expiring">Yakında Bitecek</span></td>
                    <td>28 Jan 2025</td>
                    <td><span class="days-left urgent">5 gün</span></td>
                    <td>
                        <button class="ao-btn ao-btn-primary ao-btn-sm">Hemen Yenile</button>
                    </td>
                </tr>
                <tr style="background:#fee2e2">
                    <td><strong>old-domain.com</strong></td>
                    <td><span class="type-badge comodo">Comodo</span></td>
                    <td><span class="status-badge expired">Süresi Dolmuş</span></td>
                    <td>10 Jan 2025</td>
                    <td><span class="days-left expired">-13 gün</span></td>
                    <td>
                        <button class="ao-btn ao-btn-secondary ao-btn-sm">Sil</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- SSL Types -->
    <div class="ao-card" style="margin-top:24px">
        <div class="card-header">
            <h3>📋 Sertifika Türleri</h3>
        </div>
        <div class="ssl-types">
            <div class="type-card">
                <div class="type-icon letsencrypt">🔐</div>
                <div class="type-info">
                    <h4>Let's Encrypt</h4>
                    <p>Ücretsiz, otomatik yenileme, 90 gün</p>
                    <span class="type-price">Ücretsiz</span>
                </div>
            </div>
            <div class="type-card">
                <div class="type-icon autossl">🛡️</div>
                <div class="type-info">
                    <h4>AutoSSL (cPanel)</h4>
                    <p>cPanel otomatik, ücretsiz</p>
                    <span class="type-price">Ücretsiz</span>
                </div>
            </div>
            <div class="type-card">
                <div class="type-icon comodo">🏆</div>
                <div class="type-info">
                    <h4>Comodo Positive SSL</h4>
                    <p>Alan adı doğrulamalı, 1 yıl</p>
                    <span class="type-price">₺299/yıl</span>
                </div>
            </div>
            <div class="type-card">
                <div class="type-icon wildcard">✨</div>
                <div class="type-info">
                    <h4>Wildcard SSL</h4>
                    <p>Tüm alt domainleri kapsar</p>
                    <span class="type-price">₺999/yıl</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
