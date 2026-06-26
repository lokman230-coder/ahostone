<?php
// MobileBuilder Pro - Build Center Entegrasyonu
require_admin();

$buildServicePath = dirname(__DIR__, 4) . '/modules/builder/mobilebuilder/Services/MobileBuildService.php';
$requirements = ['java' => ['ok' => false], 'gradle' => ['ok' => false], 'android_sdk' => ['ok' => false], 'build_dir' => ['writable' => false]];

if (file_exists($buildServicePath)) {
    require_once $buildServicePath;
    if (class_exists('Ahost\Modules\Builder\MobileBuilder\MobileBuildService')) {
        $buildService = new \Ahost\Modules\Builder\MobileBuilder\MobileBuildService();
        $requirements = $buildService->checkSystemRequirements();
    }
}

// Son build'leri al
$recentBuilds = [];
$stmt = db()->query("
    SELECT b.*, p.name as project_name 
    FROM module_mobilebuilder_builds b 
    LEFT JOIN module_mobilebuilder_projects p ON b.project_id = p.id 
    ORDER BY b.created_at DESC LIMIT 10
");
$recentBuilds = $stmt->fetchAll();

// Build istatistikleri
$stats = [
    'total' => db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds")->fetchColumn(),
    'completed' => db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds WHERE status = 'completed'")->fetchColumn(),
    'building' => db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds WHERE status = 'building'")->fetchColumn(),
    'failed' => db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds WHERE status = 'failed'")->fetchColumn(),
];

// Projeler
$projects = db()->query("SELECT * FROM module_mobilebuilder_projects ORDER BY updated_at DESC LIMIT 20")->fetchAll();
?>
<div class="ao-card ao-hero-card">
    <span class="ao-kicker">Ahost One v25.0.0 RC5</span>
    <h2>MobileBuilder Pro</h2>
    <p>Android / PWA / Flutter proje çıktısı için mobil uygulama oluşturma merkezi. APK/AAB build sistemi entegre.</p>
    <div class="ao-actions">
        <a class="ao-btn" href="<?= url('admin/mobile-builder/editor') ?>">Uygulama Tasarla</a>
        <a class="ao-btn soft" href="<?= url('admin/mobile-builder/ai') ?>">AI ile Oluştur</a>
        <a class="ao-btn secondary" href="<?= url('admin/mobile-builder/exports') ?>">Export Merkezi</a>
        <a class="ao-btn" href="<?= url('admin/mobile-builder/build-center') ?>">📱 APK/AAB Build</a>
    </div>
</div>

<!-- Sistem Gereksinimleri -->
<div class="ao-grid two" style="margin-bottom: 24px;">
    <div class="ao-card">
        <h3>🖥️ Sistem Gereksinimleri</h3>
        <div class="env-list">
            <div class="env-row <?= $requirements['java']['ok'] ? 'ok' : 'error' ?>">
                <span>Java JDK</span>
                <b><?= $requirements['java']['version'] ?: 'Bulunamadı' ?></b>
                <em class="pill <?= $requirements['java']['ok'] ? 'green' : 'red' ?>">
                    <?= $requirements['java']['ok'] ? '✓ Yüklü' : '✗ Gerekli: 17+' ?>
                </em>
            </div>
            <div class="env-row <?= $requirements['gradle']['ok'] ? 'ok' : 'error' ?>">
                <span>Gradle</span>
                <b><?= $requirements['gradle']['version'] ?: 'Bulunamadı' ?></b>
                <em class="pill <?= $requirements['gradle']['ok'] ? 'green' : 'red' ?>">
                    <?= $requirements['gradle']['ok'] ? '✓ Yüklü' : '✗ Gerekli: 8.0+' ?>
                </em>
            </div>
            <div class="env-row <?= $requirements['android_sdk']['ok'] ? 'ok' : 'error' ?>">
                <span>Android SDK</span>
                <b><?= $requirements['android_sdk']['version'] ?: 'Bulunamadı' ?></b>
                <em class="pill <?= $requirements['android_sdk']['ok'] ? 'green' : 'red' ?>">
                    <?= $requirements['android_sdk']['ok'] ? '✓ Yüklü' : '✗ Gerekli: 33+' ?>
                </em>
            </div>
            <div class="env-row <?= $requirements['build_dir']['writable'] ? 'ok' : 'error' ?>">
                <span>Build Dizin</span>
                <b><?= substr($requirements['build_dir']['path'] ?? '', -30) ?></b>
                <em class="pill <?= $requirements['build_dir']['writable'] ? 'green' : 'red' ?>">
                    <?= $requirements['build_dir']['writable'] ? '✓ Yazılabilir' : '✗ Salt okunur' ?>
                </em>
            </div>
        </div>
        <?php if (!$requirements['all_ok']): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px; margin-top: 14px;">
            <strong style="color: #dc2626;">⚠ Build sistemi hazır değil</strong>
            <p style="margin: 8px 0 0; font-size: 13px; color: #7f1d1d;">
                APK/AAB oluşturmak için sunucuya Java JDK 17+, Gradle 8.0+ ve Android SDK kurulmalıdır.
                <a href="<?= url('admin/build-center/environment') ?>" style="color: #2563eb;">Build Center →</a>
            </p>
        </div>
        <?php else: ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px; margin-top: 14px;">
            <strong style="color: #047857;">✓ Build sistemi hazır!</strong>
            <p style="margin: 8px 0 0; font-size: 13px; color: #065f46;">
                APK ve AAB dosyaları oluşturulabilir.
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="ao-card">
        <h3>📊 Build İstatistikleri</h3>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px;">
            <div style="text-align: center; padding: 16px; background: #f0f9ff; border-radius: 12px;">
                <div style="font-size: 28px; font-weight: 800; color: #0369a1;"><?= $stats['total'] ?></div>
                <div style="font-size: 12px; color: #64748b;">Toplam</div>
            </div>
            <div style="text-align: center; padding: 16px; background: #ecfdf5; border-radius: 12px;">
                <div style="font-size: 28px; font-weight: 800; color: #047857;"><?= $stats['completed'] ?></div>
                <div style="font-size: 12px; color: #64748b;">Başarılı</div>
            </div>
            <div style="text-align: center; padding: 16px; background: #fef3c7; border-radius: 12px;">
                <div style="font-size: 28px; font-weight: 800; color: #b45309;"><?= $stats['building'] ?></div>
                <div style="font-size: 12px; color: #64748b;">Build'leniyor</div>
            </div>
            <div style="text-align: center; padding: 16px; background: #fef2f2; border-radius: 12px;">
                <div style="font-size: 28px; font-weight: 800; color: #b91c1c;"><?= $stats['failed'] ?></div>
                <div style="font-size: 12px; color: #64748b;">Başarısız</div>
            </div>
        </div>
        <a href="<?= url('admin/mobile-builder/build-center') ?>" class="ao-btn primary" style="width: 100%;">📱 Build Center'e Git</a>
    </div>
</div>

<!-- Projeler -->
<div class="ao-card">
    <div class="card-head"><h3>📱 Projeler</h3><a href="<?= url('admin/mobile-builder/editor') ?>" class="ao-btn">+ Yeni Proje</a></div>
    <?php if (empty($projects)): ?>
    <div style="text-align: center; padding: 40px; color: #64748b;">
        <p style="font-size: 40px; margin: 0 0 12px;">📱</p>
        <p>Henüz proje yok. Hemen bir uygulama oluşturun!</p>
    </div>
    <?php else: ?>
    <table class="ao-table">
        <thead>
            <tr>
                <th>Proje</th>
                <th>Şablon</th>
                <th>Paket Adı</th>
                <th>Durum</th>
                <th>Güncellendi</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $p): ?>
            <tr>
                <td><strong><?= e($p['name']) ?></strong></td>
                <td><span class="ao-badge"><?= e($p['template']) ?></span></td>
                <td><code style="font-size: 12px;"><?= e($p['package_name']) ?></code></td>
                <td><span class="ao-badge <?= $p['status'] === 'active' ? 'green' : ($p['status'] === 'archived' ? 'gray' : 'blue') ?>"><?= e($p['status']) ?></span></td>
                <td><small><?= date('d.m.Y H:i', strtotime($p['updated_at'])) ?></small></td>
                <td>
                    <div style="display: flex; gap: 4px;">
                        <a href="<?= url('admin/mobile-builder/editor?id=' . $p['id']) ?>" class="ao-btn soft" style="padding: 6px 10px;">✏️</a>
                        <a href="<?= url('admin/mobile-builder/build?id=' . $p['id']) ?>" class="ao-btn primary" style="padding: 6px 10px;">📱</a>
                        <a href="<?= url('admin/mobile-builder/build-log?project=' . $p['id']) ?>" class="ao-btn soft" style="padding: 6px 10px;">📋</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Son Build'ler -->
<?php if (!empty($recentBuilds)): ?>
<div class="ao-card" style="margin-top: 24px;">
    <div class="card-head"><h3>🕐 Son Build İşlemleri</h3><a href="<?= url('admin/mobile-builder/build-center') ?>" class="ao-btn soft">Tümünü Gör</a></div>
    <table class="ao-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Proje</th>
                <th>Tip</th>
                <th>Durum</th>
                <th>Boyut</th>
                <th>Tarih</th>
                <th>İndir</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentBuilds as $b): ?>
            <tr>
                <td><code>#<?= $b['id'] ?></code></td>
                <td><?= e($b['project_name'] ?? 'Silinen Proje') ?></td>
                <td><span class="ao-badge"><?= strtoupper($b['build_type']) ?></span></td>
                <td>
                    <?php 
                    $statusColors = ['completed' => 'green', 'building' => 'blue', 'pending' => 'orange', 'failed' => 'red'];
                    $statusLabels = ['completed' => '✓ Başarılı', 'building' => '⚙ Build', 'pending' => '⏳ Bekliyor', 'failed' => '✗ Başarısız'];
                    ?>
                    <span class="ao-badge <?= $statusColors[$b['status']] ?? 'gray' ?>"><?= $statusLabels[$b['status']] ?? $b['status'] ?></span>
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
                    <?php if ($b['status'] === 'completed' && $b['download_path']): ?>
                    <a href="<?= url('mobilebuilder/download?id=' . $b['id']) ?>" class="ao-btn primary" style="padding: 6px 12px;">⬇</a>
                    <?php else: ?>
                    <span style="color: #94a3b8;">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
