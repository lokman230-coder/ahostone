<?php
/**
 * cPanel/WHM API Yönetimi
 * Ahost One - Site temasına uyumlu
 */
$servers = db()->query("SELECT * FROM cpanel_servers ORDER BY created_at DESC")->fetchAll();
?>
<div class="cpanel-manager">
    <!-- Header Actions -->
    <div class="ao-header-actions" style="margin-bottom:24px">
        <button class="ao-btn ao-btn-primary" onclick="showAddServer()">
            <span>+</span> Sunucu Ekle
        </button>
    </div>

    <!-- Servers Grid -->
    <div class="ao-grid three">
        <?php foreach($servers as $server): ?>
        <div class="ao-card server-card">
            <div class="server-header">
                <div class="server-status">
                    <span class="status-dot <?= $server['is_active'] ? 'online' : 'offline' ?>"></span>
                    <span class="status-text"><?= $server['is_active'] ? 'Aktif' : 'Pasif' ?></span>
                </div>
                <div class="server-actions">
                    <button class="ao-btn-icon" title="Düzenle">✏️</button>
                    <button class="ao-btn-icon" title="Sil">🗑️</button>
                </div>
            </div>
            
            <div class="server-info">
                <h4><?= e($server['name']) ?></h4>
                <div class="server-detail">
                    <span class="label">Host:</span>
                    <span class="value"><?= e($server['host']) ?>:<?= $server['port'] ?></span>
                </div>
                <div class="server-detail">
                    <span class="label">Kullanıcı:</span>
                    <span class="value"><?= e($server['username']) ?></span>
                </div>
            </div>
            
            <div class="server-stats">
                <div class="stat">
                    <span class="stat-value">156</span>
                    <span class="stat-label">Hesap</span>
                </div>
                <div class="stat">
                    <span class="stat-value">142</span>
                    <span class="stat-label">Aktif</span>
                </div>
                <div class="stat">
                    <span class="stat-value">8</span>
                    <span class="stat-label">Askıda</span>
                </div>
            </div>
            
            <div class="server-footer">
                <button class="ao-btn ao-btn-secondary ao-btn-block">
                    🔄 Bağlantıyı Test Et
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if(empty($servers)): ?>
        <div class="ao-card empty-state">
            <div class="empty-icon">🖥️</div>
            <h3>Sunucu Yok</h3>
            <p>cPanel sunucusu eklemek için "Sunucu Ekle" butonuna tıklayın.</p>
            <button class="ao-btn ao-btn-primary">Sunucu Ekle</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="ao-card" style="margin-top:24px">
        <div class="card-header">
            <h3>⚡ Hızlı İşlemler</h3>
        </div>
        <div class="quick-actions">
            <button class="action-btn">
                <span class="action-icon">🔄</span>
                <span class="action-label">Tümünü Senkronize Et</span>
            </button>
            <button class="action-btn">
                <span class="action-icon">📊</span>
                <span class="action-label">Disk Kullanımı</span>
            </button>
            <button class="action-btn">
                <span class="action-icon">⚠️</span>
                <span class="action-label">Askıdaki Hesaplar</span>
            </button>
            <button class="action-btn">
                <span class="action-icon">🔒</span>
                <span class="action-label">Güvenlik Taraması</span>
            </button>
        </div>
    </div>
</div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
