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

<style>
.cpanel-manager {}
.ao-grid.three { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
@media(max-width: 1024px) { .ao-grid.three { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 768px) { .ao-grid.three { grid-template-columns: 1fr; } }

.server-card { padding: 0; overflow: hidden; }
.server-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
.server-status { display: flex; align-items: center; gap: 8px; }
.status-dot { width: 10px; height: 10px; border-radius: 50%; }
.status-dot.online { background: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.5); }
.status-dot.offline { background: #ef4444; }
.status-text { font-size: 0.85rem; font-weight: 500; color: #64748b; }
.server-actions { display: flex; gap: 8px; }
.ao-btn-icon { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 4px; }
.ao-btn-icon:hover { opacity: 0.7; }

.server-info { padding: 20px; }
.server-info h4 { margin: 0 0 12px; font-size: 1.1rem; color: #1e293b; }
.server-detail { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; }
.server-detail .label { color: #64748b; font-size: 0.9rem; }
.server-detail .value { color: #1e293b; font-size: 0.9rem; font-weight: 500; }

.server-stats { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 1px solid #e2e8f0; }
.stat { text-align: center; padding: 16px; border-right: 1px solid #e2e8f0; }
.stat:last-child { border-right: none; }
.stat-value { display: block; font-size: 1.5rem; font-weight: 700; color: #2563eb; }
.stat-label { font-size: 0.8rem; color: #64748b; }

.server-footer { padding: 16px 20px; border-top: 1px solid #e2e8f0; }
.ao-btn-block { width: 100%; justify-content: center; }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-icon { font-size: 4rem; margin-bottom: 16px; }
.empty-state h3 { margin: 0 0 8px; color: #1e293b; }
.empty-state p { color: #64748b; margin: 0 0 20px; }

.quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
@media(max-width: 768px) { .quick-actions { grid-template-columns: repeat(2, 1fr); } }
.action-btn { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.2s; }
.action-btn:hover { background: #eff6ff; border-color: #2563eb; }
.action-icon { font-size: 1.5rem; }
.action-label { font-size: 0.85rem; color: #475569; font-weight: 500; text-align: center; }
</style>
