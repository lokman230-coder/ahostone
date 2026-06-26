<?php
// MobileBuilder Build Log Görüntüleme
require_admin();

$buildId = (int)($_GET['id'] ?? 0);
$projectId = (int)($_GET['project'] ?? 0);

$buildServicePath = dirname(__DIR__, 4) . '/modules/builder/mobilebuilder/Services/MobileBuildService.php';

if (!file_exists($buildServicePath)) {
    echo '<div class="ao-card"><div class="err">Build servisi bulunamadı!</div></div>';
    return;
}

require_once $buildServicePath;
$buildService = new \Ahost\Modules\Builder\MobileBuilder\MobileBuildService();

// Build ID verildiyse
if ($buildId > 0) {
    $build = $buildService->getBuildStatus($buildId);
    $logData = $buildService->getBuildLog($buildId);
} else {
    // Projeye göre son build'i al
    $stmt = db()->prepare("SELECT id FROM module_mobilebuilder_builds WHERE project_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$projectId]);
    $buildId = $stmt->fetchColumn();
    
    if ($buildId) {
        $build = $buildService->getBuildStatus($buildId);
        $logData = $buildService->getBuildLog($buildId);
    } else {
        $build = ['error' => 'Build bulunamadı'];
        $logData = ['exists' => false, 'content' => '', 'tail' => ''];
    }
}

if (isset($build['error'])) {
    echo '<div class="ao-card"><div class="err">' . e($build['error']) . '</div></div>';
    return;
}

// Build geçmişi (aynı projenin)
$projectBuilds = [];
if (!empty($build['project_id'])) {
    $stmt = db()->prepare("SELECT * FROM module_mobilebuilder_builds WHERE project_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$build['project_id']]);
    $projectBuilds = $stmt->fetchAll();
}
?>
<div class="ao-card ao-hero-card">
    <span class="ao-kicker">📋 Build Log</span>
    <h2>Build #<?= $build['id'] ?> - <?= e($build['project_name'] ?? 'Proje') ?></h2>
    <div class="ao-actions">
        <a href="<?= url('admin/mobile-builder/build-center') ?>" class="ao-btn">← Build Center</a>
        <?php if ($build['status'] === 'completed' && $build['download_url']): ?>
        <a href="<?= e($build['download_url']) ?>" class="ao-btn primary">⬇ İndir</a>
        <?php endif; ?>
    </div>
</div>

<div class="ao-grid two" style="margin-bottom: 24px;">
    <div class="ao-card">
        <h3>📊 Build Detayları</h3>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600; width: 120px;">Build ID</td>
                <td><code>#<?= $build['id'] ?></code></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Proje</td>
                <td><?= e($build['project_name'] ?? '-') ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Tip</td>
                <td><span class="ao-badge"><?= strtoupper($build['build_type']) ?></span></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Durum</td>
                <td>
                    <?php 
                    $statusColors = ['completed' => 'green', 'building' => 'blue', 'pending' => 'orange', 'failed' => 'red'];
                    $statusLabels = ['completed' => '✓ Başarılı', 'building' => '⚙ Buildleniyor', 'pending' => '⏳ Bekliyor', 'failed' => '✗ Başarısız'];
                    ?>
                    <span class="ao-badge <?= $statusColors[$build['status']] ?? 'gray' ?>">
                        <?= $statusLabels[$build['status']] ?? $build['status'] ?>
                    </span>
                </td>
            </tr>
            <?php if ($build['file_size']): ?>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Dosya Boyutu</td>
                <td><?= number_format($build['file_size'] / 1024 / 1024, 2) ?> MB</td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Tarih</td>
                <td><?= date('d.m.Y H:i:s', strtotime($build['created_at'])) ?></td>
            </tr>
        </table>
        
        <?php if ($build['status'] === 'completed' && !empty($build['download_url'])): ?>
        <div style="margin-top: 16px;">
            <a href="<?= e($build['download_url']) ?>" class="ao-btn primary" style="width: 100%;">
                ⬇ APK/AAB İndir
            </a>
        </div>
        <?php elseif ($build['status'] === 'building' || $build['status'] === 'pending'): ?>
        <div style="margin-top: 16px; background: #fef3c7; border-radius: 8px; padding: 12px; text-align: center;">
            <p style="margin: 0; color: #92400e; font-size: 13px;">
                ⏳ Build işlemi devam ediyor. Sayfayı yenileyin.
            </p>
            <button onclick="location.reload()" class="ao-btn" style="margin-top: 10px;">🔄 Yenile</button>
        </div>
        <?php elseif ($build['status'] === 'failed'): ?>
        <div style="margin-top: 16px; background: #fef2f2; border-radius: 8px; padding: 12px;">
            <p style="margin: 0 0 10px; color: #991b1b; font-size: 13px;">
                ✗ Build başarısız oldu. Hata detayları için logu inceleyin.
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="ao-card">
        <h3>🕐 Proje Build Geçmişi</h3>
        <?php if (empty($projectBuilds)): ?>
        <p style="color: #64748b;">Bu proje için başka build yok.</p>
        <?php else: ?>
        <table class="ao-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tip</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projectBuilds as $pb): ?>
                <tr <?= $pb['id'] == $buildId ? 'style="background: #eff6ff;"' : '' ?>>
                    <td><code>#<?= $pb['id'] ?></code></td>
                    <td><span class="ao-badge"><?= strtoupper($pb['build_type']) ?></span></td>
                    <td>
                        <span class="ao-badge <?= $statusColors[$pb['status']] ?? 'gray' ?>">
                            <?= $statusLabels[$pb['status']] ?? $pb['status'] ?>
                        </span>
                    </td>
                    <td><small><?= date('d.m.Y H:i', strtotime($pb['created_at'])) ?></small></td>
                    <td>
                        <a href="<?= url('admin/mobile-builder/build-log?id=' . $pb['id']) ?>" class="ao-btn soft" style="padding: 4px 8px;">📋</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Build Log -->
<div class="ao-card">
    <h3>📜 Build Log</h3>
    <?php if ($logData['exists']): ?>
    <div style="background: #0f172a; border-radius: 10px; padding: 16px; max-height: 500px; overflow: auto;">
        <pre style="margin: 0; color: #e2e8f0; font-family: 'Monaco', 'Menlo', monospace; font-size: 12px; line-height: 1.6; white-space: pre-wrap; word-break: break-all;"><?= e($logData['tail'] ?: $logData['content']) ?></pre>
    </div>
    <?php else: ?>
    <div style="background: #f8fafc; border-radius: 10px; padding: 24px; text-align: center; color: #64748b;">
        <p>Log dosyası bulunamadı.</p>
        <?php if ($build['status'] === 'pending' || $build['status'] === 'building'): ?>
        <p style="font-size: 13px; margin-top: 8px;">Build başlamadı veya log dosyası henüz oluşturulmadı.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.err { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px; color: #dc2626; }
</style>
