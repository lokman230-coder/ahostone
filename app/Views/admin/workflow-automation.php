<?php
/**
 * Workflow Automation
 * Ahost One - Site temasına uyumlu
 */
$triggers = \AhostModule_workflow_automation::getTriggers();
$actions = \AhostModule_workflow_automation::getActions();
?>
<div class="workflow-builder">
    <!-- Create New -->
    <div class="ao-card">
        <h3>⚙️ Yeni Otomasyon Oluştur</h3>
        <div class="automation-flow">
            <!-- Trigger -->
            <div class="flow-box trigger">
                <div class="flow-label">Tetikleyici</div>
                <div class="flow-content">
                    <select class="ao-select" id="triggerSelect">
                        <option value="">Seçin...</option>
                        <?php foreach($triggers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= $t['icon'] ?> <?= $t['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Connector -->
            <div class="flow-connector">
                <span>→</span>
            </div>
            
            <!-- Action -->
            <div class="flow-box action">
                <div class="flow-label">Eylem</div>
                <div class="flow-content">
                    <select class="ao-select" id="actionSelect">
                        <option value="">Seçin...</option>
                        <?php foreach($actions as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= $a['icon'] ?> <?= $a['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Add -->
            <div class="flow-add">
                <button class="ao-btn-icon" title="Ekle">+</button>
            </div>
        </div>
        
        <div class="automation-settings" style="margin-top:24px">
            <div class="ao-form-group">
                <label>Otomasyon Adı</label>
                <input type="text" class="ao-input" placeholder="örn: Gecikmiş Ödeme Bildirimi">
            </div>
            <button class="ao-btn ao-btn-primary">Kaydet</button>
        </div>
    </div>
    
    <!-- Active Automations -->
    <div class="ao-card" style="margin-top:24px">
        <div class="card-header">
            <h3>📋 Aktif Otomasyonlar</h3>
            <div class="card-filter">
                <select class="ao-select">
                    <option>Tümü</option>
                    <option>Aktif</option>
                    <option>Pasif</option>
                </select>
            </div>
        </div>
        
        <div class="automation-list">
            <div class="automation-item">
                <div class="automation-status">
                    <label class="ao-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="automation-flow-mini">
                    <span class="mini-trigger">🛒 Sipariş Tamamlandı</span>
                    <span class="mini-arrow">→</span>
                    <span class="mini-action">📧 E-posta Gönder</span>
                </div>
                <div class="automation-meta">
                    <span class="run-count">Çalıştı: 234</span>
                    <span class="last-run">Son: 2 saat önce</span>
                </div>
                <div class="automation-actions">
                    <button class="ao-btn-icon">✏️</button>
                    <button class="ao-btn-icon">▶️</button>
                </div>
            </div>
            
            <div class="automation-item">
                <div class="automation-status">
                    <label class="ao-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="automation-flow-mini">
                    <span class="mini-trigger">⏰ Hosting Bitiyor</span>
                    <span class="mini-arrow">→</span>
                    <span class="mini-action">📱 SMS Gönder</span>
                </div>
                <div class="automation-meta">
                    <span class="run-count">Çalıştı: 89</span>
                    <span class="last-run">Son: 5 dakika önce</span>
                </div>
                <div class="automation-actions">
                    <button class="ao-btn-icon">✏️</button>
                    <button class="ao-btn-icon">▶️</button>
                </div>
            </div>
            
            <div class="automation-item">
                <div class="automation-status">
                    <label class="ao-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="automation-flow-mini">
                    <span class="mini-trigger">🎫 Destek Talebi</span>
                    <span class="mini-arrow">→</span>
                    <span class="mini-action">🔔 Slack Bildirimi</span>
                </div>
                <div class="automation-meta">
                    <span class="run-count">Çalıştı: 12</span>
                    <span class="last-run">Son: 1 gün önce</span>
                </div>
                <div class="automation-actions">
                    <button class="ao-btn-icon">✏️</button>
                    <button class="ao-btn-icon">▶️</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
