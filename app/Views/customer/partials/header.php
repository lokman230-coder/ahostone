<?php
$pageTitle = $pageTitle ?? 'Müşteri Paneli';
$customer = function_exists('current_customer') ? current_customer() : null;
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$aoHeadContext = 'customer';
$aoHeadTitleSuffix = 'Ahost One';
require __DIR__ . '/../../shared/layout-head.php';
$aoCustomerNav = [
  ['⌂','Özet','client'],
  ['📦','Hizmetlerim','client/services'],
  ['🌐','Domainlerim','client/domains'],
  ['🧾','Faturalarım','client/invoices'],
  ['💳','Kredi/Bakiye','client/credit'],
  ['🎧','Destek','client/support'],
  ['🎨','SiteBuilder','client/site-builder'],
  ['📱','MobileBuilder','client/builder'],
  ['👤','Profil','client/profile'],
  ['🔐','Güvenlik','client/security'],
];
?>
<body data-app="client" class="ao-full-ui-reset customer-body">
<?php $aoHeaderContext='client'; require __DIR__ . '/../../shared/unified-header.php'; ?>
<div class="ao-customer-shell customer-shell">
  <aside class="ao-customer-sidebar customer-sidebar">
    <div class="ao-customer-logo customer-logo"><strong>Ahost One</strong><span>Müşteri Paneli</span></div>
    <?php foreach($aoCustomerNav as $item): $active = $currentPath === $item[2] || str_starts_with($currentPath, $item[2].'/'); ?>
      <a class="<?= $active ? 'active' : '' ?>" href="<?= url($item[2]) ?>"><span><?= e($item[0]) ?></span><?= e($item[1]) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('') ?>">↗ Siteye Dön</a>
    <a class="danger-link" href="<?= url('client/logout') ?>">⎋ Çıkış Yap</a>
  </aside>
  <main class="ao-customer-main customer-main">
    <header class="ao-customer-page-head customer-page-head">
      <button class="ao-btn ao-btn--ghost" type="button" onclick="document.body.classList.toggle('customer-open')">☰ Menü</button>
      <div><h1><?= e($pageTitle) ?></h1><p><?= $customer ? e(trim(($customer['first_name'] ?? '').' '.($customer['last_name'] ?? ''))) : 'Ahost One müşteri paneli' ?></p></div>
    </header>
    <section>
