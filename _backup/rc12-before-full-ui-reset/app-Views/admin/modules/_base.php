<?php
/**
 * Ahost One - Admin Module Base Template
 * Site temasına tam uyumlu görünüm
 */
$page = $_GET['page'] ?? 'index';
$module = $_GET['module'] ?? basename(dirname(__DIR__));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Modül' ?> - Ahost One</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/modules/<?= $module ?>.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="ao-admin">
        <!-- Sidebar -->
        <aside class="ao-sidebar">
            <div class="ao-logo">
                <a href="/admin">
                    <span class="ao-logo-icon">🚀</span>
                    <span class="ao-logo-text">Ahost One</span>
                </a>
            </div>
            <nav class="ao-nav">
                <a href="/admin" class="ao-nav-item">
                    <span class="ao-icon">📊</span> Dashboard
                </a>
                <a href="/admin/customers" class="ao-nav-item">
                    <span class="ao-icon">👥</span> Müşteriler
                </a>
                <a href="/admin/orders" class="ao-nav-item">
                    <span class="ao-icon">🛒</span> Siparişler
                </a>
                <a href="/admin/hosting" class="ao-nav-item">
                    <span class="ao-icon">🖥️</span> Hosting
                </a>
                <a href="/admin/domain-center" class="ao-nav-item">
                    <span class="ao-icon">🌐</span> Domain
                </a>
                <a href="/admin/invoices" class="ao-nav-item">
                    <span class="ao-icon">📄</span> Faturalar
                </a>
                <a href="/admin/products" class="ao-nav-item">
                    <span class="ao-icon">📦</span> Ürünler
                </a>
                <div class="ao-nav-divider"></div>
                <a href="/admin/<?= $module ?>" class="ao-nav-item active">
                    <span class="ao-icon">📁</span> <?= $module_title ?? 'Modül' ?>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="ao-main">
            <!-- Header -->
            <header class="ao-header">
                <div class="ao-header-left">
                    <h1 class="ao-page-title"><?= $page_title ?? 'Modül Adı' ?></h1>
                    <nav class="ao-breadcrumb">
                        <a href="/admin">Anasayfa</a>
                        <span>/</span>
                        <span><?= $module_title ?? 'Modül' ?></span>
                    </nav>
                </div>
                <div class="ao-header-right">
                    <button class="ao-btn ao-btn-primary">
                        <span>+</span> Yeni Ekle
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="ao-content">
                <?php if(isset($content)) echo $content; ?>
            </div>
        </main>
    </div>
</body>
</html>
