<?php
$pageTitle = $pageTitle ?? 'Ahost One';
$aoHeadContext = 'site';
$aoHeadTitleSuffix = '';
// RC11: Site tarafında eski tema/header/frontend CSS parçaları aktif yükten çıkarıldı.
// Site görünümü artık ortak içerik CSS + tek unified header CSS üzerinden gelir.
$aoHeadCss = [];
$aoHeadExternalCss = [];
$aoHeadScripts = ['js/front/site-header-v221.js'];
require __DIR__ . '/../../shared/layout-head.php';
$aoPreviewId=(int)($_GET['theme_preview'] ?? ($_SESSION['theme_preview_id'] ?? 0));
?>
<body data-app="site" class="ao-site <?= e(ao_theme_body_class('site')) ?>" style="<?= ao_theme_style_vars('site') ?>"><?= ao_theme_preview_bar($aoPreviewId) ?>
<?php $aoHeaderContext='site'; require __DIR__ . '/../../shared/unified-header.php'; ?>
<main>
