<?php
$pageTitle = $pageTitle ?? 'Müşteri Girişi';
$flash = get_flash();
$aoAuthIsAdmin = stripos((string)$pageTitle, 'admin') !== false;
$aoHeadContext = $aoAuthIsAdmin ? 'admin-login' : 'auth';
$aoHeadTitleSuffix = 'Ahost One';
$aoHeadCss = [];
$aoHeadExternalCss = [];
require __DIR__ . '/../../shared/layout-head.php';
?>
<body data-app="auth" data-auth-context="<?= $aoAuthIsAdmin ? 'admin-login' : 'customer-login' ?>" class="auth-body auth-with-site-menu">
<?php $aoHeaderContext = $aoAuthIsAdmin ? 'admin-login' : 'auth'; require __DIR__ . '/../../shared/unified-header.php'; ?>
<main class="auth-shell">
  <section class="auth-hero">
    <div class="badge">⚡ Ahost One otomasyon altyapısı</div>
    <h1><?= $aoAuthIsAdmin ? 'Ahost One yönetim merkezine giriş yapın.' : 'Modern müşteri paneline giriş yapın.' ?></h1>
    <p><?= $aoAuthIsAdmin ? 'Siparişleri, müşterileri, domainleri, destek taleplerini ve sistem ayarlarını güvenle yönetin.' : 'Hizmetlerinizi, domainlerinizi, faturalarınızı ve destek taleplerinizi tek panelden güvenle yönetin.' ?></p>
    <div class="auth-feature-grid">
      <div><strong>7/24</strong><span>Destek</span></div>
      <div><strong>AI</strong><span>Akıllı Panel</span></div>
      <div><strong>Pro</strong><span>Hosting Yönetimi</span></div>
    </div>
  </section>
  <section class="auth-card">
    <div class="auth-brand"><b>Ahost One</b><span><?= $aoAuthIsAdmin ? 'Control Center' : 'Müşteri Paneli' ?></span></div>
    <?php if($flash): ?><div class="auth-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
