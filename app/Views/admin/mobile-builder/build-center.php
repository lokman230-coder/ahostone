<?php
// MobileBuilder APK/AAB Build Center
require_admin();

$buildServicePath = dirname(__DIR__, 4) . '/modules/builder/mobilebuilder/Services/MobileBuildService.php';

if (!file_exists($buildServicePath)) {
    echo '<div class="ao-card"><div class="err">Build servisi bulunamadı!</div></div>';
    return;
}

require_once $buildServicePath;
$buildService = new \Ahost\Modules\Builder\MobileBuilder\MobileBuildService();

// POST: Build başlat
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_build'])) {
    $projectId = (int)($_POST['project_id'] ?? 0);
    $buildType = $_POST['build_type'] ?? 'apk';
    
    if ($projectId > 0) {
        $result = $buildService->startBuild($projectId, $buildType);
        if ($result['success']) {
            $message = 'Build başlatıldı! Build ID: #' . $result['build_id'];
            $messageType = 'success';
        } else {
            $message = 'Hata: ' . ($result['error'] ?? 'Bilinmeyen hata');
            $messageType = 'error';
        }
    }
}

// Sistem gereksinimleri
$requirements = $buildService->checkSystemRequirements();

// Build kuyruğu
$queue = $buildService->getQueue();

// Tüm build history
$stmt = db()->query("
    SELECT b.*, p.name as project_name, p.package_name 
    FROM module_mobilebuilder_builds b 
    LEFT JOIN module_mobilebuilder_projects p ON b.project_id = p.id 
    ORDER BY b.created_at DESC LIMIT 50
");
$builds = $stmt->fetchAll();

// Projeler
$projects = db()->query("SELECT * FROM module_mobilebuilder_projects WHERE status != 'archived' ORDER BY updated_at DESC")->fetchAll();
?>
<div class="ao-card ao-hero-card">
    <span class="ao-kicker">📱 APK/AAB Build Center</span>
    <h2>MobileBuilder Build Sistemi</h2>
    <p>Gerçek APK ve AAB dosyaları oluşturun. Build için Java JDK 17+, Gradle 8.0+ ve Android SDK gereklidir.</p>
</div>

<?php if ($message): ?>
<div class="ao-card" style="margin-bottom: 20px;">
    <?php if ($messageType === 'success'): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px; color: #047857;">
        ✓ <?= e($message) ?>
    </div>
    <?php else: ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px; color: #dc2626;">
        ✗ <?= e($message) ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Sistem Durumu -->
<div class="ao-grid three" style="margin-bottom: 24px;">
    <div class="ao-card">
        <h3>🖥️ Sistem Gereksinimleri</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 0; font-weight: 600;">Java JDK</td>
                <td style="text-align: right;">
                    <?php if ($requirements['java']['ok']): ?>
                    <span style="color: #22c55e;">✓ <?= e($requirements['java']['version']) ?></span>
                    <?php else: ?>
                    <span style="color: #ef4444;">✗ <?= e($requirements['java']['version'] ?: 'Bulunamadı') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 0; font-weight: 600;">Gradle</td>
                <td style="text-align: right;">
                    <?php if ($requirements['gradle']['ok']): ?>
                    <span style="color: #22c55e;">✓ <?= e($requirements['gradle']['version']) ?></span>
                    <?php else: ?>
                    <span style="color: #ef4444;">✗ <?= e($requirements['gradle']['version'] ?: 'Bulunamadı') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 0; font-weight: 600;">Android SDK</td>
                <td style="text-align: right;">
                    <?php if ($requirements['android_sdk']['ok']): ?>
                    <span style="color: #22c55e;">✓ <?= e($requirements['android_sdk']['version']) ?></span>
                    <?php else: ?>
                    <span style="color: #ef4444;">✗ <?= e($requirements['android_sdk']['version'] ?: 'Bulunamadı') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: 600;">Build Dizin</td>
                <td style="text-align: right;">
                    <?php if ($requirements['build_dir']['writable']): ?>
                    <span style="color: #22c55e;">✓ Yazılabilir</span>
                    <?php else: ?>
                    <span style="color: #ef4444;">✗ Salt okunur</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php if (!$requirements['all_ok']): ?>
        <div style="background: #fef2f2; border-radius: 8px; padding: 12px; margin-top: 14px; font-size: 13px; color: #991b1b;">
            ⚠ APK/AAB build için sunucu gereksinimleri karşılanmıyor. Lütfen sistem yöneticinize başvurun.
        </div>
        <?php endif; ?>
    </div>
    
    <div class="ao-card">
        <h3>📊 Build İstatistikleri</h3>
        <?php
        $totalBuilds = db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds")->fetchColumn();
        $completedBuilds = db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds WHERE status = 'completed'")->fetchColumn();
        $failedBuilds = db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds WHERE status = 'failed'")->fetchColumn();
        $successRate = $totalBuilds > 0 ? round(($completedBuilds / $totalBuilds) * 100) : 0;
        ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div style="background: #f0f9ff; padding: 16px; border-radius: 10px; text-align: center;">
                <div style="font-size: 28px; font-weight: 800; color: #0369a1;"><?= $totalBuilds ?></div>
                <div style="font-size: 12px; color: #64748b;">Toplam Build</div>
            </div>
            <div style="background: #ecfdf5; padding: 16px; border-radius: 10px; text-align: center;">
                <div style="font-size: 28px; font-weight: 800; color: #047857;"><?= $completedBuilds ?></div>
                <div style="font-size: 12px; color: #64748b;">Başarılı</div>
            </div>
            <div style="background: #fef2f2; padding: 16px; border-radius: 10px; text-align: center;">
                <div style="font-size: 28px; font-weight: 800; color: #dc2626;"><?= $failedBuilds ?></div>
                <div style="font-size: 12px; color: #64748b;">Başarısız</div>
            </div>
            <div style="background: #faf5ff; padding: 16px; border-radius: 10px; text-align: center;">
                <div style="font-size: 28px; font-weight: 800; color: #7c3aed;"><?= $successRate ?>%</div>
                <div style="font-size: 12px; color: #64748b;">Başarı Oranı</div>
            </div>
        </div>
    </div>
    
    <div class="ao-card">
        <h3>⚡ Hızlı Build</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label>Proje Seçin</label>
                <select name="project_id" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <option value="">Proje seçin...</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['package_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Build Tipi</label>
                <div style="display: flex; gap: 10px;">
                    <label style="flex: 1; display: flex; align-items: center; gap: 6px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="build_type" value="apk" checked>
                        <span>📦 APK</span>
                    </label>
                    <label style="flex: 1; display: flex; align-items: center; gap: 6px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="build_type" value="aab">
                        <span>📱 AAB</span>
                    </label>
                </div>
            </div>
            <button type="submit" name="start_build" value="1" class="ao-btn primary" style="width: 100%;" <?= !$requirements['all_ok'] ? 'disabled' : '' ?>>
                ⚡ Build Başlat
            </button>
        </form>
    </div>
</div>

<!-- Build Kuyruğu -->
<?php if (!empty($queue)): ?>
<div class="ao-card" style="margin-bottom: 24px;">
    <h3>⏳ Build Kuyruğu (<?= count($queue) ?>)</h3>
    <table class="ao-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Proje</th>
                <th>Tip</th>
                <th>Durum</th>
                <th>Tarih</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($queue as $q): ?>
            <tr>
                <td><code>#<?= $q['id'] ?></code></td>
                <td><?= e($q['project_name'] ?? 'Bilinmeyen') ?></td>
                <td><span class="ao-badge"><?= strtoupper($q['build_type']) ?></span></td>
                <td>
                    <span class="ao-badge <?= $q['status'] === 'building' ? 'blue' : 'orange' ?>">
                        <?= $q['status'] === 'building' ? '⚙ Buildleniyor' : '⏳ Bekliyor' ?>
                    </span>
                </td>
                <td><small><?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></small></td>
                <td>
                    <a href="<?= url('admin/mobile-builder/build-log?id=' . $q['id']) ?>" class="ao-btn soft">📋 Log</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Build History -->
<div class="ao-card">
    <div class="card-head">
        <h3>📜 Build Geçmişi</h3>
        <a href="<?= url('admin/build-center') ?>" class="ao-btn soft">Build Center →</a>
    </div>
    <?php if (empty($builds)): ?>
    <div style="text-align: center; padding: 40px; color: #64748b;">
        <p style="font-size: 40px; margin: 0 0 12px;">📦</p>
        <p>Henüz build işlemi yok.</p>
    </div>
    <?php else: ?>
    <table class="ao-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Proje</th>
                <th>Paket</th>
                <th>Tip</th>
                <th>Durum</th>
                <th>Boyut</th>
                <th>Tarih</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($builds as $b): ?>
            <tr>
                <td><code>#<?= $b['id'] ?></code></td>
                <td>
                    <strong><?= e($b['project_name'] ?? 'Silinen Proje') ?></strong>
                </td>
                <td><code style="font-size: 11px;"><?= e($b['package_name'] ?? '-') ?></code></td>
                <td><span class="ao-badge"><?= strtoupper($b['build_type']) ?></span></td>
                <td>
                    <?php 
                    $statusMap = [
                        'completed' => ['green', '✓ Başarılı'],
                        'building' => ['blue', '⚙ Buildleniyor'],
                        'pending' => ['orange', '⏳ Bekliyor'],
                        'failed' => ['red', '✗ Başarısız']
                    ];
                    $status = $statusMap[$b['status']] ?? ['gray', $b['status']];
                    ?>
                    <span class="ao-badge <?= $status[0] ?>"><?= $status[1] ?></span>
                </td>
                <td>
                    <?php if ($b['file_size']): ?>
                    <small><?= number_format($b['file_size'] / 1024 / 1024, 2) ?> MB</small>
                    <?php else: ?>
                    <small style="color: #94a3b8;">-</small>
                    <?php endif; ?>
                </td>
                <td><small><?= date('d.m.Y H:i', strtotime($b['created_at'])) ?></small></td>
                <td>
                    <div style="display: flex; gap: 4px;">
                        <?php if ($b['status'] === 'completed' && $b['download_path']): ?>
                        <a href="<?= url('mobilebuilder/download?id=' . $b['id']) ?>" class="ao-btn primary" style="padding: 6px 10px;">⬇ APK</a>
                        <?php endif; ?>
                        <a href="<?= url('admin/mobile-builder/build-log?id=' . $b['id']) ?>" class="ao-btn soft" style="padding: 6px 10px;">📋</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
