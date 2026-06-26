<?php
// API Gateway Admin Panel
$keys = db()->query("SELECT k.*, c.name as customer_name 
    FROM api_keys k 
    LEFT JOIN customers c ON c.id=k.customer_id 
    ORDER BY k.created_at DESC LIMIT 50")->fetchAll();

$webhooks = db()->query("SELECT w.*, c.name as customer_name 
    FROM webhooks w 
    LEFT JOIN customers c ON c.id=w.customer_id 
    ORDER BY w.created_at DESC LIMIT 50")->fetchAll();

$stats = [
    'total_keys' => db()->query("SELECT COUNT(*) FROM api_keys")->fetchColumn(),
    'active_keys' => db()->query("SELECT COUNT(*) FROM api_keys WHERE is_active=1")->fetchColumn(),
    'total_webhooks' => db()->query("SELECT COUNT(*) FROM webhooks")->fetchColumn(),
    'api_calls_today' => db()->query("SELECT COUNT(*) FROM api_logs WHERE DATE(created_at)=CURDATE()")->fetchColumn()
];

// Generate new API key
if(isset($_POST['create_key'])) {
    $name = trim($_POST['name'] ?? 'Yeni API Anahtarı');
    $api_key = bin2hex(random_bytes(16));
    $secret_key = bin2hex(random_bytes(32));
    $permissions = json_encode($_POST['permissions'] ?? ['read']);
    
    db()->prepare("INSERT INTO api_keys (name, api_key, secret_key, permissions) VALUES (?,?,?,?)")
        ->execute([$name, $api_key, $secret_key, $permissions]);
    
    flash('success', 'API anahtarı oluşturuldu. Secret: ' . substr($secret_key, 0, 8) . '...');
    redirect_to('admin/api-gateway');
}
?>
<style>
.api-docs { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
.api-endpoint { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
.api-endpoint .method { display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; margin-right: 8px; }
.api-endpoint .method.get { background: #dcfce7; color: #16a34a; }
.api-endpoint .method.post { background: #dbeafe; color: #2563eb; }
.api-endpoint .method.put { background: #fef3c7; color: #d97706; }
.api-endpoint .method.delete { background: #fee2e2; color: #dc2626; }
.api-endpoint code { font-family: monospace; color: #64748b; }
.api-key-box { background: #1e293b; color: #f1f5f9; padding: 20px; border-radius: 16px; margin: 16px 0; font-family: monospace; }
.api-key-box .label { color: #94a3b8; font-size: 0.85rem; margin-bottom: 8px; }
.api-key-box .key { font-size: 1.1rem; word-break: break-all; }
</style>

<div class="ao-page-head">
    <div>
        <h2>🔌 API Gateway</h2>
        <p>REST API ve Webhook yönetimi. Developer portal entegrasyonu.</p>
    </div>
    <div class="ao-actions">
        <button class="ao-btn" onclick="document.getElementById('newKeyModal').showModal()">+ API Anahtarı Oluştur</button>
    </div>
</div>

<div class="ao-stats-grid">
    <div class="ao-stat"><span>Toplam Anahtar</span><strong><?= $stats['total_keys'] ?></strong></div>
    <div class="ao-stat"><span>Aktif Anahtar</span><strong><?= $stats['active_keys'] ?></strong></div>
    <div class="ao-stat"><span>Webhook</span><strong><?= $stats['total_webhooks'] ?></strong></div>
    <div class="ao-stat"><span>Bugünkü İstek</span><strong><?= number_format($stats['api_calls_today']) ?></strong></div>
</div>

<div class="ao-tabs" data-ao-tabs>
    <button class="active" data-tab="keys">API Anahtarları</button>
    <button data-tab="webhooks">Webhooks</button>
    <button data-tab="docs">API Dökümanı</button>
    <button data-tab="logs">Loglar</button>
</div>

<!-- API Keys -->
<div id="tab-keys" class="ao-tab-panel active">
    <div class="ao-card">
        <h3>API Anahtarları</h3>
        <table class="ao-table">
            <thead><tr><th>İsim</th><th>API Key</th><th>İzinler</th><th>Durum</th><th>Son Kullanım</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach($keys as $k): ?>
                <tr>
                    <td><?= e($k['name']) ?><br><small><?= e($k['customer_name'] ?? 'Admin') ?></small></td>
                    <td><code style="font-size:0.8rem"><?= e(substr($k['api_key'], 0, 12)) ?>...</code></td>
                    <td><?= e(implode(', ', json_decode($k['permissions'] ?: '[]', true) ?: [])) ?></td>
                    <td><span class="ao-badge <?= $k['is_active'] ? 'active' : 'inactive' ?>"><?= $k['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                    <td><?= $k['last_used_at'] ? date('d.m.Y H:i', strtotime($k['last_used_at'])) : 'Hiç' ?></td>
                    <td>
                        <a href="<?= url('admin/api-gateway/toggle-key?id='.$k['id'].'&csrf_token='.csrf_token()) ?>" class="ao-mini-btn"><?= $k['is_active'] ? 'Pasifleştir' : 'Aktifleştir' ?></a>
                        <a href="<?= url('admin/api-gateway/delete-key?id='.$k['id'].'&csrf_token='.csrf_token()) ?>" class="ao-mini-btn danger" onclick="return confirm('Silinecek?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; if(empty($keys)): ?>
                <tr><td colspan="6" style="text-align:center;padding:40px">Henüz API anahtarı yok. <button class="ao-mini-btn" onclick="document.getElementById('newKeyModal').showModal()">Oluştur</button></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Webhooks -->
<div id="tab-webhooks" class="ao-tab-panel">
    <div class="ao-card">
        <h3>Webhook'lar</h3>
        <table class="ao-table">
            <thead><tr><th>İsim</th><th>URL</th><th>Olaylar</th><th>Durum</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach($webhooks as $w): ?>
                <tr>
                    <td><?= e($w['name']) ?></td>
                    <td><small><?= e(substr($w['url'], 0, 50)) ?>...</small></td>
                    <td><?= e(implode(', ', json_decode($w['events'] ?: '[]', true) ?: [])) ?></td>
                    <td><span class="ao-badge <?= $w['is_active'] ? 'active' : 'inactive' ?>"><?= $w['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                    <td>
                        <a href="<?= url('admin/api-gateway/webhook?id='.$w['id']) ?>" class="ao-mini-btn">Düzenle</a>
                    </td>
                </tr>
            <?php endforeach; if(empty($webhooks)): ?>
                <tr><td colspan="5" style="text-align:center;padding:40px">Henüz webhook yok.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- API Documentation -->
<div id="tab-docs" class="ao-tab-panel">
    <div class="ao-card">
        <h3>📚 API Dökümanı</h3>
        <p style="margin-bottom:20px">Base URL: <code style="background:#f1f5f9;padding:4px 8px;border-radius:4px"><?= e($_SERVER['HTTP_HOST'] ?? 'site.com') ?>/api/v1</code></p>
        
        <h4 style="margin-bottom:16px">Endpoint'ler</h4>
        <div class="api-docs">
            <div class="api-endpoint">
                <span class="method get">GET</span>
                <code>/domains/search?q={query}</code>
                <p style="margin:8px 0 0;font-size:0.9rem;color:#64748b">Domain arama</p>
            </div>
            <div class="api-endpoint">
                <span class="method get">GET</span>
                <code>/products</code>
                <p style="margin:8px 0 0;font-size:0.9rem;color:#64748b">Ürün listesi</p>
            </div>
            <div class="api-endpoint">
                <span class="method post">POST</span>
                <code>/orders</code>
                <p style="margin:8px 0 0;font-size:0.9rem;color:#64748b">Sipariş oluştur</p>
            </div>
            <div class="api-endpoint">
                <span class="method get">GET</span>
                <code>/invoices/{id}</code>
                <p style="margin:8px 0 0;font-size:0.9rem;color:#64748b">Fatura detayı</p>
            </div>
            <div class="api-endpoint">
                <span class="method get">GET</span>
                <code>/customers/{id}</code>
                <p style="margin:8px 0 0;font-size:0.9rem;color:#64748b">Müşteri bilgisi</p>
            </div>
            <div class="api-endpoint">
                <span class="method post">POST</span>
                <code>/tickets</code>
                <p style="margin:8px 0 0;font-size:0.9rem;color:#64748b">Destek talebi</p>
            </div>
        </div>
        
        <div class="api-key-box" style="margin-top:24px">
            <div class="label">Authentication Header</div>
            <div class="key">Authorization: Bearer {API_KEY}</div>
        </div>
        
        <h4 style="margin-top:24px">Webhook Events</h4>
        <ul style="margin-left:20px">
            <li><code>order.created</code> - Yeni sipariş</li>
            <li><code>order.completed</code> - Sipariş tamamlandı</li>
            <li><code>invoice.paid</code> - Fatura ödendi</li>
            <li><code>domain.registered</code> - Domain kaydedildi</li>
            <li><code>ticket.created</code> - Yeni destek talebi</li>
            <li><code>customer.registered</code> - Yeni müşteri</li>
        </ul>
    </div>
</div>

<!-- Logs -->
<div id="tab-logs" class="ao-tab-panel">
    <?php $logs = db()->query("SELECT l.*, k.name as key_name FROM api_logs l LEFT JOIN api_keys k ON k.id=l.api_key_id ORDER BY l.created_at DESC LIMIT 100")->fetchAll(); ?>
    <div class="ao-card">
        <h3>API İstek Logları</h3>
        <table class="ao-table">
            <thead><tr><th>Zaman</th><th>Anahtar</th><th>Endpoint</th><th>Method</th><th>Status</th><th>Süre</th></tr></thead>
            <tbody>
            <?php foreach($logs as $l): ?>
                <tr>
                    <td><small><?= date('d.m H:i:s', strtotime($l['created_at'])) ?></small></td>
                    <td><small><?= e($l['key_name'] ?? '-') ?></small></td>
                    <td><code style="font-size:0.8rem"><?= e($l['endpoint']) ?></code></td>
                    <td><span class="ao-badge <?= $l['method'] ?>"><?= e($l['method']) ?></span></td>
                    <td><span class="ao-badge <?= $l['status_code'] >= 200 && $l['status_code'] < 300 ? 'active' : 'inactive' ?>"><?= $l['status_code'] ?></span></td>
                    <td><?= $l['execution_time_ms'] ?>ms</td>
                </tr>
            <?php endforeach; if(empty($logs)): ?>
                <tr><td colspan="6" style="text-align:center;padding:40px">Henüz log yok.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Key Modal -->
<dialog id="newKeyModal" style="border:none;border-radius:20px;padding:30px;max-width:500px;width:90%">
    <h3 style="margin-bottom:20px">+ Yeni API Anahtarı</h3>
    <form method="post">
        <?= csrf_field() ?>
        <div class="ao-form">
            <label>İsim <input type="text" name="name" required placeholder="Uygulama adı"></label>
            <label>İzinler</label>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px">
                <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="permissions[]" value="read" checked> Okuma</label>
                <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="permissions[]" value="write"> Yazma</label>
                <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="permissions[]" value="delete"> Silme</label>
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:20px">
            <button type="submit" name="create_key" class="ao-btn">Oluştur</button>
            <button type="button" class="ao-btn secondary" onclick="document.getElementById('newKeyModal').close()">İptal</button>
        </div>
    </form>
</dialog>
