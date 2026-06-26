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

<style>
.workflow-builder {}
.automation-flow { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.flow-box { flex: 1; min-width: 200px; background: #f8fafc; border-radius: 12px; padding: 16px; }
.flow-box.trigger { border: 2px solid #2563eb; }
.flow-box.action { border: 2px solid #10b981; }
.flow-label { font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 8px; }
.flow-content select { width: 100%; }
.flow-connector { font-size: 1.5rem; color: #64748b; }
.flow-add .ao-btn-icon { width: 40px; height: 40px; border-radius: 50%; background: #2563eb; color: #fff; font-size: 1.2rem; }

.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.card-header h3 { margin: 0; }
.card-filter select { min-width: 120px; }

.automation-list { display: flex; flex-direction: column; gap: 12px; }
.automation-item { display: flex; align-items: center; gap: 16px; padding: 16px; background: #f8fafc; border-radius: 12px; }
.automation-status { flex-shrink: 0; }

.ao-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
.ao-switch input { opacity: 0; width: 0; height: 0; }
.ao-switch .slider { position: absolute; cursor: pointer; inset: 0; background: #ccc; border-radius: 24px; transition: .3s; }
.ao-switch .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: .3s; }
.ao-switch input:checked + .slider { background: #2563eb; }
.ao-switch input:checked + .slider:before { transform: translateX(20px); }

.automation-flow-mini { flex: 1; display: flex; align-items: center; gap: 8px; }
.mini-trigger, .mini-action { padding: 6px 12px; background: #fff; border-radius: 8px; font-size: 0.9rem; }
.mini-trigger { border-left: 3px solid #2563eb; }
.mini-action { border-left: 3px solid #10b981; }
.mini-arrow { color: #64748b; }

.automation-meta { display: flex; flex-direction: column; font-size: 0.8rem; color: #64748b; }
.automation-meta span { white-space: nowrap; }
.automation-actions { display: flex; gap: 8px; }
</style>
