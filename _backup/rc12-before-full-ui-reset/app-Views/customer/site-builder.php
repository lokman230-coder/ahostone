<?php
/**
 * Ahost One v25.0.0 RC5 - Müşteri Site Builder
 * Müşterilerin kendi sitelerini düzenleyebileceği canlı builder
 */
require_customer();

// Get customer data
$customer = current_customer();
$customerId = (int)($customer['id'] ?? 0);

// Get customer's sitebuilder projects
$projects = [];
try {
    $q = db()->prepare('SELECT * FROM sitebuilder_projects WHERE customer_id=? ORDER BY id DESC LIMIT 10');
    $q->execute([$customerId]);
    $projects = $q->fetchAll();
} catch (Throwable $e) {}

// Get current project/page if editing
$projectId = (int)($_GET['project_id'] ?? 0);
$pageId = (int)($_GET['page_id'] ?? 0);

$currentProject = null;
$currentPage = null;

if ($projectId) {
    try {
        $q = db()->prepare('SELECT * FROM sitebuilder_projects WHERE id=? AND customer_id=? LIMIT 1');
        $q->execute([$projectId, $customerId]);
        $currentProject = $q->fetch();
    } catch (Throwable $e) {}
}

if ($pageId) {
    try {
        $q = db()->prepare('SELECT p.* FROM sitebuilder_pages p INNER JOIN sitebuilder_projects sp ON sp.id=p.project_id WHERE p.id=? AND sp.customer_id=? LIMIT 1');
        $q->execute([$pageId, $customerId]);
        $currentPage = $q->fetch();
    } catch (Throwable $e) {}
}

// If no page selected but we have a project
if (!$currentPage && $currentProject) {
    try {
        $q = db()->prepare('SELECT * FROM sitebuilder_pages WHERE project_id=? ORDER BY id ASC LIMIT 1');
        $q->execute([$projectId]);
        $currentPage = $q->fetch();
    } catch (Throwable $e) {}
}

// Get pages for current project
$pages = [];
if ($currentProject) {
    try {
        $q = db()->prepare('SELECT * FROM sitebuilder_pages WHERE project_id=? ORDER BY id ASC');
        $q->execute([$projectId]);
        $pages = $q->fetchAll();
    } catch (Throwable $e) {}
}

// Check if customer has permission
$canEdit = $currentProject && ((int)$currentProject['customer_id'] === $customerId);
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $currentPage ? e($currentPage['title']) : 'Site Builder' ?> - Ahost One</title>
    <link rel="stylesheet" href="<?= assetv('css/admin-core.css') ?>">
    <link rel="stylesheet" href="<?= assetv('css/admin-modern-v21.css') ?>">
    <link rel="stylesheet" href="<?= assetv('css/live-builder.css') ?>">
    <style>
        body.customer-builder-mode { background: #f1f5f9; }
        .ao-builder-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            background: #0f172a;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .ao-builder-bar-left, .ao-builder-bar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .ao-builder-bar .ao-btn.soft {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: none;
        }
        .ao-builder-bar .ao-btn.soft:hover {
            background: rgba(255,255,255,0.2);
        }
        .ao-builder-divider { color: #475569; }
        .ao-builder-sep { color: #64748b; margin: 0 4px; }
    </style>
</head>
<body class="customer-builder-mode">
    <?php if ($currentPage && $canEdit): ?>
    <!-- CUSTOMER LIVE BUILDER MODE -->
    <div class="ao-builder-bar">
        <div class="ao-builder-bar-left">
            <a href="<?= url('client/site-builder') ?>" class="ao-btn soft">← Projelerim</a>
            <span class="ao-builder-divider">|</span>
            <span><strong><?= e($currentProject['name'] ?? '') ?></strong></span>
            <span class="ao-builder-sep">→</span>
            <span><?= e($currentPage['title']) ?></span>
        </div>
        <div class="ao-builder-bar-right">
            <a class="ao-btn soft" target="_blank" href="<?= url('sitebuilder/preview?id='.$pageId) ?>">👁 Önizle</a>
            <button class="ao-btn primary" id="savePage">💾 Kaydet</button>
        </div>
    </div>
    
    <form method="post" action="<?= url('client/site-builder/page-save') ?>" id="customerBuilderForm" style="display:none;">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $pageId ?>">
        <input type="hidden" id="customer_builder_json" name="builder_json" value='<?= e($currentPage['builder_json'] ?? '') ?>'>
    </form>
    
    <div class="lb-wrapper">
        <!-- SIDEBAR -->
        <div class="lb-sidebar">
            <div class="lb-sidebar-section">
                <h3>Öğe Ekle</h3>
                <div class="lb-widget-grid">
                    <div class="lb-widget" data-widget-type="heading" draggable="true">
                        <span class="lb-widget-icon">H</span>
                        <span class="lb-widget-label">Başlık</span>
                    </div>
                    <div class="lb-widget" data-widget-type="text" draggable="true">
                        <span class="lb-widget-icon">¶</span>
                        <span class="lb-widget-label">Metin</span>
                    </div>
                    <div class="lb-widget" data-widget-type="button" draggable="true">
                        <span class="lb-widget-icon">▶</span>
                        <span class="lb-widget-label">Buton</span>
                    </div>
                    <div class="lb-widget" data-widget-type="image" draggable="true">
                        <span class="lb-widget-icon">🖼</span>
                        <span class="lb-widget-label">Görsel</span>
                    </div>
                </div>
            </div>
            
            <div class="lb-sidebar-section">
                <h3>Bloklar</h3>
                <div class="lb-widget-grid">
                    <div class="lb-widget" data-widget-type="feature" draggable="true">
                        <span class="lb-widget-icon">⭐</span>
                        <span class="lb-widget-label">Özellik</span>
                    </div>
                    <div class="lb-widget" data-widget-type="price" draggable="true">
                        <span class="lb-widget-icon">₺</span>
                        <span class="lb-widget-label">Fiyat</span>
                    </div>
                    <div class="lb-widget" data-widget-type="divider" draggable="true">
                        <span class="lb-widget-icon">―</span>
                        <span class="lb-widget-label">Ayraç</span>
                    </div>
                    <div class="lb-widget" data-widget-type="spacer" draggable="true">
                        <span class="lb-widget-icon">↕</span>
                        <span class="lb-widget-label">Boşluk</span>
                    </div>
                </div>
            </div>
            
            <div class="lb-sidebar-section">
                <h3>Sayfalarım</h3>
                <div style="padding: 8px 0;">
                    <?php foreach ($pages as $pg): ?>
                    <a href="<?= url('client/site-builder?project_id='.$projectId.'&page_id='.$pg['id']) ?>" 
                       class="ao-btn soft <?= $pg['id'] == $pageId ? 'primary' : '' ?>" 
                       style="display: block; margin-bottom: 6px; text-align: left; font-size: 13px; padding: 8px 12px;">
                        ✏️ <?= e($pg['title']) ?>
                        <?php if ($pg['status'] === 'published'): ?>
                        <span style="color: #22c55e; margin-left: 4px;">✓</span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- CANVAS -->
        <div class="lb-canvas-wrap">
            <div class="lb-toolbar">
                <button type="button" class="lb-toolbar-btn" id="lbUndo" title="Geri Al (Ctrl+Z)">↩ Geri</button>
                <button type="button" class="lb-toolbar-btn" id="lbRedo" title="Yinele (Ctrl+Shift+Z)">↪ İleri</button>
                <div class="lb-toolbar-sep"></div>
                <button type="button" class="lb-toolbar-btn primary" id="lbSaveCustomer">💾 Kaydet</button>
                <span style="margin-left: auto; font-size: 12px; color: #94a3b8;">Tüm değişiklikler otomatik kaydedilir</span>
            </div>
            
            <div class="lb-canvas" id="lbCanvas"></div>
        </div>
        
        <!-- INSPECTOR -->
        <div class="lb-inspector" id="lbInspector">
            <div style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                <p style="font-size: 48px; margin: 0 0 12px;">🎨</p>
                <p style="margin: 0;">Düzenlemek istediğiniz öğeye tıklayın</p>
            </div>
        </div>
    </div>
    
    <script src="<?= assetv('js/admin/live-builder.js') ?>"></script>
    <script>
    document.getElementById('savePage').addEventListener('click', function() {
        if (window.LiveBuilder) {
            window.LiveBuilder.saveState();
            document.getElementById('customer_builder_json').value = document.getElementById('live_builder_json').value;
        }
        document.getElementById('customerBuilderForm').submit();
    });
    
    document.getElementById('lbSaveCustomer').addEventListener('click', function() {
        if (window.LiveBuilder) {
            window.LiveBuilder.saveState();
            document.getElementById('customer_builder_json').value = document.getElementById('live_builder_json').value;
        }
        document.getElementById('customerBuilderForm').submit();
    });
    </script>
    
    <?php else: ?>
    <!-- PROJECT LIST -->
    <div style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
        <div class="ao-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
                <div>
                    <h2 style="margin: 0;">Site Builder</h2>
                    <p style="color: #64748b; margin: 8px 0 0;">Kendi web sitenizi oluşturun ve düzenleyin</p>
                </div>
                <div style="display: flex; gap: 8px; align-items:center; flex-wrap:wrap;">
                    <form method="post" action="<?= url('client/site-builder/project-create') ?>" style="margin:0; display:flex; gap:8px; align-items:center;">
                        <?= csrf_field() ?>
                        <input name="name" value="Web Sitem" style="min-width:150px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px;">
                        <button class="ao-btn primary" type="submit">➕ Yeni Proje Oluştur</button>
                    </form>
                    <a href="<?= url('client/dashboard') ?>" class="ao-btn soft">← Panele Dön</a>
                </div>
            </div>
            
            <?php if (empty($projects)): ?>
            <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 16px;">
                <p style="font-size: 48px; margin: 0 0 16px;">🚀</p>
                <h3 style="margin: 0 0 8px;">Henüz projeniz yok</h3>
                <p style="color: #64748b; margin: 0 0 24px;">Yeni proje oluşturarak ilk sayfanızı düzenlemeye başlayabilirsiniz.</p>
                <form method="post" action="<?= url('client/site-builder/project-create') ?>" style="display:inline-flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:center;">
                    <?= csrf_field() ?>
                    <input name="name" value="Web Sitem" style="padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px;">
                    <button class="ao-btn primary" type="submit">Site Oluştur</button>
                </form>
            </div>
            <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-top: 24px;">
                <?php foreach ($projects as $proj): ?>
                <div class="ao-card" style="padding: 20px;">
                    <h4 style="margin: 0 0 8px;"><?= e($proj['name']) ?></h4>
                    <p style="color: #64748b; margin: 0 0 12px; font-size: 14px;">
                        <?= ucfirst($proj['type'] ?? 'site') ?> • <?= ucfirst($proj['status'] ?? 'active') ?>
                    </p>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin: 0 0 12px;">
                        <form method="post" action="<?= url('client/site-builder/page-create') ?>" style="margin:0; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="project_id" value="<?= (int)$proj['id'] ?>">
                            <input name="title" value="Yeni Sayfa" style="width:120px; padding:8px 10px; border:1px solid #e2e8f0; border-radius:9px; font-size:13px;">
                            <button class="ao-btn primary" type="submit" style="padding:8px 10px;">+ Sayfa</button>
                        </form>
                        <form method="post" action="<?= url('client/site-builder/export') ?>" style="margin:0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="project_id" value="<?= (int)$proj['id'] ?>">
                            <button class="ao-btn soft" type="submit" style="padding:8px 10px;">ZIP Export</button>
                        </form>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <?php
                        // Get pages for this project
                        $projPages = [];
                        try {
                            $qp = db()->prepare('SELECT id, title FROM sitebuilder_pages WHERE project_id=? ORDER BY id ASC LIMIT 5');
                            $qp->execute([$proj['id']]);
                            $projPages = $qp->fetchAll();
                        } catch (Throwable $e) {}
                        ?>
                        <?php foreach ($projPages as $pg): ?>
                        <a href="<?= url('client/site-builder?project_id='.$proj['id'].'&page_id='.$pg['id']) ?>" class="ao-btn soft" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>✏️ <?= e($pg['title']) ?></span>
                            <?php if ($pg['status'] === 'published'): ?>
                            <span style="color: #22c55e; font-size: 12px;">✓ Yayında</span>
                            <?php else: ?>
                            <span style="color: #94a3b8; font-size: 12px;">Taslak</span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
