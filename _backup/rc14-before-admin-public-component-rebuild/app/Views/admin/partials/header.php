<?php
$pageTitle = $pageTitle ?? 'Admin Paneli';
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$admin = function_exists('current_admin') ? current_admin() : [];
$aoHeadContext = 'admin';
$aoHeadTitleSuffix = 'Ahost One Admin';
require __DIR__ . '/../../shared/layout-head.php';
$aoAdminNav = [
  'Genel' => [
    ['🏠','Dashboard','admin'],
    ['👥','Müşteriler','admin/customers'],
    ['🛒','Siparişler','admin/orders'],
    ['₺','Finans','admin/accounting'],
  ],
  'Ürün & Altyapı' => [
    ['📦','Ürün Merkezi','admin/product-center'],
    ['🌐','Domain Center','admin/domain-center'],
    ['🖥','Hosting & Sunucu','admin/hosting-server'],
    ['🛍','Marketplace','admin/marketplace'],
  ],
  'Builder' => [
    ['🎨','SiteBuilder','admin/site-builder'],
    ['📱','MobileBuilder','admin/mobile-builder'],
    ['⚙','Build Center','admin/build-center'],
    ['🎭','Tema Merkezi','admin/theme-center'],
  ],
  'Operasyon' => [
    ['🎧','Destek','admin/support'],
    ['✉','Bildirimler','admin/notification-center'],
    ['🤖','AI Center','admin/ai-center'],
    ['🧪','QA & Scan','admin/qa-scan-center'],
  ],
  'Sistem' => [
    ['🔌','API Entegrasyonları','admin/api-integrations'],
    ['⚙','Ayarlar','admin/settings'],
    ['🧩','Modüller','admin/module-center'],
    ['❔','Yardım','admin/help-center'],
  ],
];
?>
<body data-app="admin" class="ao-full-ui-reset admin-body">
<div class="ao-admin-shell">
  <aside class="ao-admin-sidebar">
    <a class="ao-admin-logo" href="<?= url('admin') ?>"><span>⚡</span><strong>Ahost One</strong></a>
    <nav class="ao-admin-nav" aria-label="Admin menü">
      <?php foreach($aoAdminNav as $group => $items): ?>
        <div class="ao-admin-nav-label"><?= e($group) ?></div>
        <?php foreach($items as $item): $isActive = $currentPath === $item[2] || str_starts_with($currentPath, $item[2].'/'); ?>
          <a class="<?= $isActive ? 'active' : '' ?>" href="<?= url($item[2]) ?>"><span><?= e($item[0]) ?></span><?= e($item[1]) ?></a>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <div class="ao-admin-nav-label">Hızlı</div>
      <a href="<?= url('') ?>" target="_blank">↗ Siteyi Gör</a>
      <a href="<?= url('admin/logout') ?>">⎋ Çıkış</a>
    </nav>
  </aside>
  <main class="ao-admin-main">
    <header class="ao-admin-topbar">
      <button class="ao-admin-mobile-toggle ao-btn" type="button" onclick="document.body.classList.toggle('sidebar-open')">☰</button>
      <form class="ao-admin-search" method="get" action="<?= url('admin/search') ?>"><input name="q" placeholder="Müşteri, sipariş, domain, modül ara..." value="<?= e($_GET['q'] ?? '') ?>"></form>
      <a class="ao-btn ao-btn--ghost" href="<?= url('admin/orders/new') ?>">➕ Yeni Sipariş</a>
      <a class="ao-btn ao-btn--ghost" target="_blank" href="<?= url('') ?>">Siteyi Gör</a>
      <a class="ao-icon-link" href="<?= url('admin/notification-center') ?>">🔔</a>
      <a class="ao-btn ao-btn--primary" href="<?= url('admin/settings') ?>"><?= e($admin['username'] ?? 'Admin') ?></a>
    </header>
    <section class="ao-admin-content">
