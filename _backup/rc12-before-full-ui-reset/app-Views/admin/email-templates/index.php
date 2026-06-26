<?php
// Email Templates Admin Panel
ao_email_templates_ensure_schema();

$templates = db()->query("SELECT * FROM email_templates ORDER BY type, name")->fetchAll();
$types = ['customer','order','invoice','domain','support','system','affiliate'];
?>
<style>
.email-preview { background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden; }
.email-preview-header { background:#f8fafc;padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center }
.email-preview-body { padding:0 }
.email-frame { width:100%;height:600px;border:none;background:#fff }
.variables-box { background:#fef3c7;border:1px solid #fbbf24;border-radius:12px;padding:16px;margin-bottom:20px }
.variables-box h4 { margin:0 0 12px;color:#92400e }
.variables-list { display:flex;flex-wrap:wrap;gap:8px }
.var-chip { background:#fff;border:1px solid #fbbf24;padding:4px 12px;border-radius:20px;font-size:0.85rem;font-family:monospace;cursor:pointer }
.var-chip:hover { background:#fef3c7 }
</style>

<div class="ao-page-head">
    <div>
        <h2>📧 E-posta Şablonları</h2>
        <p>Profesyonel HTML e-posta şablonları oluşturun ve yönetin.</p>
    </div>
    <div class="ao-actions">
        <a class="ao-btn" href="<?= url('admin/email-templates/create') ?>">+ Yeni Şablon</a>
    </div>
</div>

<div class="ao-grid two" style="gap:24px">
    <!-- Template List -->
    <div>
        <div class="ao-card">
            <h3>Şablonlar</h3>
            <div class="ao-tabs" data-ao-tabs>
                <button class="active" data-tab="all">Tümü</button>
                <?php foreach($types as $t): ?>
                <button data-tab="<?= $t ?>"><?= ucfirst($t) ?></button>
                <?php endforeach; ?>
            </div>
            
            <div id="tab-all" class="ao-tab-panel active">
                <div style="display:grid;gap:12px">
                <?php foreach($templates as $t): ?>
                    <a href="<?= url('admin/email-templates/edit?id='.$t['id']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:16px;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none;color:inherit;transition:all 0.2s" onmouseover="this.style.borderColor='#2563eb'" onmouseout="this.style.borderColor='#e2e8f0'">
                        <div>
                            <strong style="color:#1e293b"><?= e($t['name']) ?></strong><br>
                            <small style="color:#64748b">{<?= e($t['slug']) ?>}</small>
                        </div>
                        <span class="ao-badge <?= $t['is_active'] ? 'active' : 'inactive' ?>"><?= $t['is_active'] ? 'Aktif' : 'Pasif' ?></span>
                    </a>
                <?php endforeach; ?>
                </div>
            </div>
            
            <?php foreach($types as $type): ?>
            <div id="tab-<?= $type ?>" class="ao-tab-panel">
                <?php $typeTemplates = array_filter($templates, fn($t) => $t['type'] === $type); ?>
                <?php if($typeTemplates): ?>
                <div style="display:grid;gap:12px">
                <?php foreach($typeTemplates as $t): ?>
                    <a href="<?= url('admin/email-templates/edit?id='.$t['id']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:16px;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none;color:inherit">
                        <div>
                            <strong><?= e($t['name']) ?></strong>
                        </div>
                        <span class="ao-badge <?= $t['is_active'] ? 'active' : 'inactive' ?>"><?= $t['is_active'] ? 'Aktif' : 'Pasif' ?></span>
                    </a>
                <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="text-align:center;padding:40px;color:#64748b">Bu kategoride şablon yok.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Preview -->
    <div>
        <div class="ao-card">
            <h3>📱 Önizleme</h3>
            <div class="variables-box">
                <h4>🔧 Kullanılabilir Değişkenler</h4>
                <div class="variables-list">
                    <span class="var-chip" onclick="insertVar('{customer_name}')">{customer_name}</span>
                    <span class="var-chip" onclick="insertVar('{customer_email}')">{customer_email}</span>
                    <span class="var-chip" onclick="insertVar('{order_id}')">{order_id}</span>
                    <span class="var-chip" onclick="insertVar('{invoice_id}')">{invoice_id}</span>
                    <span class="var-chip" onclick="insertVar('{amount}')">{amount}</span>
                    <span class="var-chip" onclick="insertVar('{domain}')">{domain}</span>
                    <span class="var-chip" onclick="insertVar('{due_date}')">{due_date}</span>
                    <span class="var-chip" onclick="insertVar('{site_url}')">{site_url}</span>
                    <span class="var-chip" onclick="insertVar('{client_area_url}')">{client_area_url}</span>
                    <span class="var-chip" onclick="insertVar('{year}')">{year}</span>
                </div>
            </div>
            <div class="email-preview">
                <div class="email-preview-header">
                    <span><strong>E-posta Önizleme</strong></span>
                    <button class="ao-btn secondary" onclick="refreshPreview()">🔄 Yenile</button>
                </div>
                <div class="email-preview-body">
                    <iframe id="emailPreviewFrame" class="email-frame" srcdoc="<?= e($templates[0]['content'] ?? '<p>E-posta içeriği</p>') ?>"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple variable insertion helper
function insertVar(variable) {
    const textarea = document.querySelector('textarea[name="content"]');
    if(textarea) {
        textarea.value += variable;
        textarea.focus();
    }
}

// Preview refresh (would need backend integration)
function refreshPreview() {
    const content = document.querySelector('textarea[name="content"]')?.value || '';
    document.getElementById('emailPreviewFrame').srcdoc = content || '<p>İçerik girin...</p>';
}
</script>
