
<?php
// Quotations admin list
require_admin();

// Sayfa numarası
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

// Filtreler
$status = $_GET['status'] ?? '';
$serviceType = $_GET['service_type'] ?? '';
$search = trim($_GET['search'] ?? '');

// SQL sorgusu oluştur
$where = ['1=1'];
$params = [];

if ($status) {
    $where[] = 'q.status = ?';
    $params[] = $status;
}

if ($serviceType) {
    $where[] = 'q.service_type = ?';
    $params[] = $serviceType;
}

if ($search) {
    $where[] = '(q.quotation_number LIKE ? OR q.customer_name LIKE ? OR q.customer_email LIKE ? OR q.project_name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = implode(' AND ', $where);

// Toplam kayıt
$totalStmt = db()->prepare("SELECT COUNT(*) FROM quotations q WHERE {$whereClause}");
$totalStmt->execute($params);
$total = $totalStmt->fetchColumn();

$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Kayıtları al
$stmt = db()->prepare("
    SELECT q.*, u.name as assigned_name
    FROM quotations q
    LEFT JOIN admin_users u ON q.assigned_to = u.id
    WHERE {$whereClause}
    ORDER BY q.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
");
$stmt->execute($params);
$quotations = $stmt->fetchAll();

// İstatistikler
$stats = [
    'total' => db()->query('SELECT COUNT(*) FROM quotations')->fetchColumn(),
    'pending' => db()->query("SELECT COUNT(*) FROM quotations WHERE status = 'pending'")->fetchColumn(),
    'reviewed' => db()->query("SELECT COUNT(*) FROM quotations WHERE status = 'reviewed'")->fetchColumn(),
    'quoted' => db()->query("SELECT COUNT(*) FROM quotations WHERE status = 'quoted'")->fetchColumn(),
];

// Durum renkleri
$statusColors = [
    'pending' => 'orange',
    'reviewed' => 'blue',
    'quoted' => 'purple',
    'accepted' => 'green',
    'rejected' => 'red',
    'cancelled' => 'gray',
];

// Hizmet türü etiketleri
$serviceLabels = [
    'website' => '🌐 Web Sitesi',
    'mobile_app' => '📱 Mobil Uygulama',
    'web_app' => '💻 Web Uygulaması',
    'custom_software' => '⚙️ Özel Yazılım',
    'other' => '📦 Diğer',
];

// Öncelik renkleri
$urgencyColors = [
    'low' => 'green',
    'normal' => 'blue',
    'high' => 'orange',
    'urgent' => 'red',
];
?>
<div class="ao-card ao-hero-card">
    <span class="ao-kicker">Teklif Yönetimi</span>
    <h2>Teklif Talepleri</h2>
    <p>Web sitesi, mobil uygulama ve özel yazılım için gelen teklif taleplerini yönetin.</p>
    <div class="ao-actions">
        <a class="ao-btn <?= !$status ? 'primary' : 'soft' ?>" href="<?= url('admin/quotations') ?>">Tümü</a>
        <a class="ao-btn <?= $status === 'pending' ? 'primary' : 'soft' ?>" href="<?= url('admin/quotations?status=pending') ?>">Bekleyen (<?= $stats['pending'] ?>)</a>
        <a class="ao-btn <?= $status === 'quoted' ? 'primary' : 'soft' ?>" href="<?= url('admin/quotations?status=quoted') ?>">Fiyatlandırılan (<?= $stats['quoted'] ?>)</a>
    </div>
</div>

<div class="ao-grid two" style="margin-bottom: 24px;">
    <div class="ao-card">
        <div style="display: flex; gap: 24px;">
            <div style="text-align: center;">
                <div style="font-size: 32px; font-weight: 800; color: #3b82f6;"><?= number_format($stats['total']) ?></div>
                <div style="font-size: 13px; color: #64748b;">Toplam Talep</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 32px; font-weight: 800; color: #f97316;"><?= number_format($stats['pending']) ?></div>
                <div style="font-size: 13px; color: #64748b;">Bekleyen</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 32px; font-weight: 800; color: #8b5cf6;"><?= number_format($stats['quoted']) ?></div>
                <div style="font-size: 13px; color: #64748b;">Fiyatlandırıldı</div>
            </div>
        </div>
    </div>
    <div class="ao-card">
        <form method="GET" action="<?= url('admin/quotations') ?>" style="display: flex; gap: 8px;">
            <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
            <?php if ($serviceType): ?><input type="hidden" name="service_type" value="<?= e($serviceType) ?>"><?php endif; ?>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Teklif no, müşteri adı, e-posta..." style="flex: 1; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px;">
            <button type="submit" class="ao-btn">🔍 Ara</button>
            <?php if ($search): ?>
            <a href="<?= url('admin/quotations?' . ($status ? 'status=' . $status : '')) ?>" class="ao-btn soft">✕</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="ao-card">
    <?php if (empty($quotations)): ?>
    <div style="text-align: center; padding: 60px 20px; color: #64748b;">
        <p style="font-size: 48px; margin: 0 0 16px;">📋</p>
        <p style="margin: 0;">Henüz teklif talebi yok.</p>
    </div>
    <?php else: ?>
    <table class="ao-table">
        <thead>
            <tr>
                <th>Teklif No</th>
                <th>Müşteri</th>
                <th>Hizmet</th>
                <th>Proje</th>
                <th>Bütçe</th>
                <th>Öncelik</th>
                <th>Durum</th>
                <th>Tarih</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quotations as $q): ?>
            <tr>
                <td><code style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px;"><?= e($q['quotation_number']) ?></code></td>
                <td>
                    <strong><?= e($q['customer_name']) ?></strong><br>
                    <small style="color: #64748b;"><?= e($q['customer_email']) ?></small>
                </td>
                <td><?= $serviceLabels[$q['service_type']] ?? $q['service_type'] ?></td>
                <td>
                    <strong><?= e($q['project_name']) ?></strong>
                    <?php if ($q['customer_company']): ?>
                    <br><small style="color: #64748b;"><?= e($q['customer_company']) ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($q['budget_min'] || $q['budget_max']): ?>
                    <span style="font-weight: 600;">
                        <?= $q['budget_min'] ? number_format($q['budget_min'], 0, ',', '.') : '?' ?>
                        <?php if ($q['budget_min'] && $q['budget_max']): ?>-<?php endif; ?>
                        <?= $q['budget_max'] ? number_format($q['budget_max'], 0, ',', '.') : '' ?>
                        ₺
                    </span>
                    <?php else: ?>
                    <span style="color: #94a3b8;">Belirtilmedi</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                    $urgencyLabels = ['low' => '🟢 Düşük', 'normal' => '🔵 Normal', 'high' => '🟠 Yüksek', 'urgent' => '🔴 Acil'];
                    ?>
                    <span class="ao-badge <?= $urgencyColors[$q['urgency']] ?? 'gray' ?>">
                        <?= $urgencyLabels[$q['urgency']] ?? $q['urgency'] ?>
                    </span>
                </td>
                <td>
                    <span class="ao-badge <?= $statusColors[$q['status']] ?? 'gray' ?>">
                        <?= ucfirst($q['status']) ?>
                    </span>
                </td>
                <td>
                    <small><?= date('d.m.Y', strtotime($q['created_at'])) ?></small>
                    <br><small style="color: #94a3b8;"><?= date('H:i', strtotime($q['created_at'])) ?></small>
                </td>
                <td>
                    <div style="display: flex; gap: 4px;">
                        <a href="<?= url('admin/quotations/view?id=' . $q['id']) ?>" class="ao-btn soft" style="padding: 6px 10px;">👁</a>
                        <a href="<?= url('admin/quotations/quote?id=' . $q['id']) ?>" class="ao-btn primary" style="padding: 6px 10px;">💰</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 20px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="<?= url('admin/quotations?page=' . $p . ($status ? '&status=' . $status : '') . ($search ? '&search=' . urlencode($search) : '')) ?>" 
           class="ao-btn <?= $p === $page ? 'primary' : 'soft' ?>" style="min-width: 40px;">
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
