<?php
$pageTitle = $pageTitle ?? 'Müşteri Paneli';
$customer=current_customer();
$aoHeadContext = 'client';
$aoHeadTitleSuffix = 'Ahost One';
$aoHeadCss = [];
$aoHeadExternalCss = [];
require __DIR__ . '/../../shared/layout-head.php';
$aoPreviewId=(int)($_GET['theme_preview'] ?? ($_SESSION['theme_preview_id'] ?? 0));
?>
<body data-app="client" class="customer-body <?= e(ao_theme_body_class('client')) ?>" style="<?= ao_theme_style_vars('client') ?>"><?= ao_theme_preview_bar($aoPreviewId) ?>
<?php $aoHeaderContext='client'; require __DIR__ . '/../../shared/unified-header.php'; ?>
<div class="customer-shell">
<aside class="customer-sidebar">
  <div class="customer-logo"><img src="<?= e(ao_brand_logo_url()) ?>" alt="Ahost One" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'"><strong style="display:none">Ahost One</strong><span>Müşteri Paneli</span></div>
  <a href="<?= url('client') ?>">Özet</a>
  <a href="<?= url('client/services') ?>">Hizmetlerim</a>
  <a href="<?= url('client/domains') ?>">Domainlerim</a>
  <a href="<?= url('client/invoices') ?>">Faturalarım</a>
  <a href="<?= url('client/credit') ?>">Kredi/Bakiye</a>
  <a href="<?= url('client/support') ?>">Destek</a>
  <a href="<?= url('client/profile') ?>">Profil</a>
  <a href="<?= url('client/account-users') ?>">Hesap Kullanıcıları</a>
  <a href="<?= url('client/security') ?>">Güvenlik</a>
  <a href="<?= url('') ?>">Siteye Dön</a>
  <a class="danger-link" href="<?= url('client/logout') ?>">Çıkış Yap</a>
</aside>
<main class="customer-main">
<?php $impersonating=!empty($_SESSION['admin_impersonating_customer_id']); ?>

<header class="customer-page-head">
  <div><h1><?= e($pageTitle) ?></h1><p><?= $customer ? e($customer['first_name'].' '.$customer['last_name']) : '' ?></p></div>
</header>
<section>
