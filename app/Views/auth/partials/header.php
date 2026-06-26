<?php
$pageTitle = $pageTitle ?? 'Giriş';
$flash = function_exists('get_flash') ? get_flash() : null;
$aoAuthIsAdmin = stripos((string)$pageTitle, 'admin') !== false || str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin');
$aoHeadContext = $aoAuthIsAdmin ? 'admin-login' : 'auth';
$aoHeadTitleSuffix = 'Ahost One';
require __DIR__ . '/../../shared/layout-head.php';
?>
<body data-app="auth" data-auth-context="<?= $aoAuthIsAdmin ? 'admin-login' : 'customer-login' ?>" class="auth-body ao-full-ui-reset">
<?php $aoHeaderContext = $aoAuthIsAdmin ? 'admin-login' : 'auth'; require __DIR__ . '/../../shared/unified-header.php'; ?>
<main class="auth-shell">
  <section class="auth-hero">
    <span class="badge">Premium SaaS Platform</span>
    <h1><?= $aoAuthIsAdmin ? 'Ahost One yönetim merkezine hoş geldiniz.' : 'Ahost One müşteri paneline hoş geldiniz.' ?></h1>
    <p><?= $aoAuthIsAdmin ? 'Müşterileri, siparişleri, domainleri, hostingleri ve sistem ayarlarını tek merkezden yönetin.' : 'Hizmetlerinizi, domainlerinizi, faturalarınızı ve destek taleplerinizi tek panelden yönetin.' ?></p>
    <div class="auth-feature-grid"><div><strong>AI</strong><span>Akıllı araçlar</span></div><div><strong>Pro</strong><span>Hosting yönetimi</span></div><div><strong>24/7</strong><span>Destek</span></div></div>
  </section>
  <section class="auth-card">
    <div class="auth-brand"><b>Ahost One</b><span><?= $aoAuthIsAdmin ? 'Admin Paneli' : 'Müşteri Paneli' ?></span></div>
    <?php if($flash): ?><div class="auth-alert <?= e($flash['type'] ?? 'info') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
