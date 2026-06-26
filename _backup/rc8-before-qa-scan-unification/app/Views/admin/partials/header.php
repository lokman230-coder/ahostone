<?php
$pageTitle = $pageTitle ?? 'Ahost One Admin';
$currentRoute = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$aoPreviewId=(int)($_GET['theme_preview'] ?? ($_SESSION['theme_preview_id'] ?? 0));
if (!function_exists('ao_nav_open_v21')) {
  function ao_nav_open_v21($needles){ $uri=trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),'/'); foreach((array)$needles as $n){ if($n!=='' && str_contains($uri,$n)) return ' open'; } return ''; }
}
?>
<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($pageTitle) ?> - Ahost One</title>
<link rel="stylesheet" href="<?= assetv('css/admin-core.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/admin-sidebar.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/admin-modern-v21.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/admin-saas-v22.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/admin-v222-fix.css') ?>">
<?php if(str_contains($currentRoute,'admin/product-center')): ?><link rel="stylesheet" href="<?= assetv('css/product-center.css') ?>"><?php endif; ?>
<?php if(str_contains($currentRoute,'admin/domain-center') || str_contains($currentRoute,'admin/domain-intelligence')): ?><link rel="stylesheet" href="<?= assetv('css/domain-center.css') ?>"><?php endif; ?>
<?php if(str_contains($currentRoute,'admin/site-builder') || str_contains($currentRoute,'admin/builder-pro') || str_contains($currentRoute,'admin/theme-center')): ?><link rel="stylesheet" href="<?= assetv('css/site-builder.css') ?>"><?php endif; ?>
<?php if(str_contains($currentRoute,'admin/mobile-builder')): ?><link rel="stylesheet" href="<?= assetv('css/mobile-builder-pro.css') ?>"><?php endif; ?>
<?php if(str_contains($currentRoute,'admin/menu-manager')): ?><link rel="stylesheet" href="<?= assetv('css/admin-menu-manager.css') ?>"><?php endif; ?>
<?php if(str_contains($currentRoute,'admin/support')): ?><link rel="stylesheet" href="<?= assetv('css/support-center.css') ?>"><?php endif; ?>
<?php if(str_contains($currentRoute,'admin/marketplace')): ?><link rel="stylesheet" href="<?= assetv('css/marketplace.css') ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= assetv('css/mobile-responsive-v2415.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/mobile-cleanup-v2416.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/css-isolation-v2417.css') ?>">
<?php $themeCss=ao_theme_css_href('admin'); if($themeCss): ?><link rel="stylesheet" href="<?= e($themeCss) ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= assetv('css/premium-saas-v2441.css') ?>">
<script>window.AHOST_BASE_URL = <?= json_encode(rtrim(url(''), '/')) ?>;</script>
<link rel="stylesheet" href="<?= assetv('css/ahost-v2463-fixes.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/ahost-v2465-polish.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/ahost-v2468-polish.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/ahost-v2469-hosting-sync.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/ahost-v2470-dashboard.css') ?>">

<style>
[data-v222-dropdown]{position:relative}
[data-v222-dropdown].is-open .ao-v222-mega,[data-v222-dropdown].is-open .ao-v222-menu{display:grid!important;opacity:1!important;visibility:visible!important;pointer-events:auto!important;transform:translateY(0)!important;z-index:9998!important}
@media(max-width:820px){
 .ao-saas-topbar{position:sticky;top:0;z-index:9996;gap:8px;overflow:visible!important}
 .ao-v222-mega,.ao-v222-menu{position:fixed!important;left:12px!important;right:12px!important;top:70px!important;max-height:70vh;overflow:auto;background:#fff!important;border:1px solid #e2e8f0!important;border-radius:18px!important;box-shadow:0 24px 70px rgba(15,23,42,.25)!important;color:#0f172a!important}
 .ao-v222-menu a,.ao-v222-mega a{color:#0f172a!important;background:#f8fafc!important;border:1px solid #edf2f7!important;border-radius:14px!important;margin:4px!important}
 .ao-v222-quick,.ao-v222-new,.ao-v222-user{min-height:44px}
}
</style>

  <link rel="stylesheet" href="<?= assetv('css/ahost-v2411-language-domain-polish.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/ahost-v2411-master-header-ui.css') ?>">
<link rel="stylesheet" href="<?= assetv('css/ao-admin-rc7-polish.css') ?>">
</head>
<body data-app="admin" class="<?= e(ao_theme_body_class('admin')) ?>" style="<?= ao_theme_style_vars('admin') ?>"><?= ao_theme_preview_bar($aoPreviewId) ?><div class="ao-admin">
<?php if(admin_pref('setup_wizard_popup_dismissed','0')!=='1' && !str_contains($currentRoute,'admin/setup-wizard')): ?>
<div class="setup-wizard-modal" id="setupWizardModal"><div class="setup-wizard-modal-card"><div class="setup-wizard-modal-head"><span class="eyebrow">İlk Kurulum</span><h3>Ahost One kurulumunu tamamlayalım</h3><p>Logo, site adı, ödeme, SMTP, SMS, tema, sunucu ve modül ayarlarını canlı kullanım için tamamlayabilirsin.</p></div><div class="setup-wizard-modal-actions"><a class="btn primary" href="<?= url('admin/setup-wizard') ?>">Kurulum Sihirbazını Aç</a><form method="post" action="<?= url('admin/setup-wizard/dismiss') ?>"><?= csrf_field() ?><input type="hidden" name="dont_show_again" value="1"><button class="btn ghost">Sonra, bir daha gösterme</button></form></div></div></div>
<?php endif; ?>
<aside class="ao-sidebar"><div class="ao-brand"><img src="<?= e(ao_brand_logo_url()) ?>" alt="Ahost One" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'"><strong style="display:none">Ahost One</strong><span>v24.7.0</span></div><nav class="ao-nav">
<a class="<?= $currentRoute==='admin/dashboard'||$currentRoute==='admin'?'active':'' ?>" href="<?= url('admin/dashboard') ?>">⌂ Kontrol Paneli</a>
<div class="nav-label">Ana İşlemler</div>
<div class="nav-group<?= ao_nav_open_v21('admin/customers') ?>"><a href="<?= url('admin/customers') ?>">👥 Müşteriler</a><div><a href="<?= url('admin/customers') ?>">Tüm Müşteriler</a><a href="<?= url('admin/customers/add') ?>">Yeni Müşteri</a><a href="<?= url('admin/customers/groups') ?>">Müşteri Grupları</a></div></div>
<div class="nav-group<?= ao_nav_open_v21('admin/product-center') ?>"><a href="<?= url('admin/product-center') ?>">📦 Ürünler Yönetimi</a><div><a href="<?= url('admin/product-center/groups') ?>">Ürün Grupları</a><a href="<?= url('admin/product-center/products') ?>">Ürünler</a><a href="<?= url('admin/product-center/config-options') ?>">Konfigüre Seçenekler</a><a href="<?= url('admin/product-center/custom-fields') ?>">Özel Alanlar</a><a href="<?= url('admin/product-center/promotions') ?>">Promosyonlar</a><a href="<?= url('admin/product-center/bundles') ?>">Paket Oluşturucu</a></div></div>
<div class="nav-group<?= ao_nav_open_v21('admin/orders') ?>"><a href="<?= url('admin/orders') ?>">🛒 Siparişler</a><div><a href="<?= url('admin/orders') ?>">Tüm Siparişler</a><a href="<?= url('admin/orders/new') ?>">Yeni Sipariş</a><a href="<?= url('admin/orders/abandoned') ?>">Yarım Kalan Sepetler</a></div></div>
<div class="nav-group<?= ao_nav_open_v21(['admin/domain-center','admin/domain-intelligence','admin/marketplace']) ?>"><a href="<?= url('admin/domain-center') ?>">🌐 Domain</a><div><a href="<?= url('admin/domain-center') ?>">Domain Center</a><a href="<?= url('admin/domain-center/pricing') ?>">TLD Fiyatları</a><a href="<?= url('admin/domain-center/smart-pricing') ?>">Akıllı Fiyat</a><a href="<?= url('admin/api-integrations#domain') ?>">Registrarlar / API</a><a href="<?= url('admin/domain-center/transfers') ?>">Transferler</a><a href="<?= url('admin/domain-center/operations') ?>">Operasyon Logları</a><a href="<?= url('admin/domain-intelligence') ?>">DNS / SSL / SEO</a><a href="<?= url('admin/marketplace') ?>">Marketplace</a></div></div>
<div class="nav-label">Hizmetler</div>
<div class="nav-group<?= ao_nav_open_v21('admin/hosting-server') ?>"><a href="<?= url('admin/hosting-server') ?>">🖥 Hosting & Sunucu</a><div><a href="<?= url('admin/hosting-server/servers') ?>">Sunucular</a><a href="<?= url('admin/hosting-server/accounts') ?>">Hosting Hesapları</a><a href="<?= url('admin/hosting-server/whm') ?>">WHM / cPanel</a><a href="<?= url('admin/hosting-server/health') ?>">Sağlık Kontrolü</a></div></div>
<div class="nav-group<?= ao_nav_open_v21('admin/accounting') ?>"><a href="<?= url('admin/accounting') ?>">₺ Finans</a><div><a href="<?= url('admin/accounting/invoices') ?>">Faturalar</a><a href="<?= url('admin/accounting/transactions') ?>">Tahsilatlar / İşlemler</a><a href="<?= url('admin/accounting/payment-fees') ?>">Ödeme Komisyonları</a><a href="<?= url('admin/accounting/taxes') ?>">Vergiler</a><a href="<?= url('admin/reports') ?>">Raporlar</a></div></div>
<div class="nav-label">Destek & İletişim</div>
<div class="nav-group<?= ao_nav_open_v21('admin/support') ?>"><a href="<?= url('admin/support') ?>">🎧 Destek Merkezi</a><div><a href="<?= url('admin/support') ?>">Genel Bakış</a><a href="<?= url('admin/support/tickets') ?>">Ticketlar</a><a href="<?= url('admin/support/departments') ?>">Departmanlar</a><a href="<?= url('admin/support/knowledgebase') ?>">Bilgi Bankası</a><a href="<?= url('admin/support/live-chat') ?>">Canlı Sohbet</a></div></div>
<div class="nav-group<?= ao_nav_open_v21(['admin/notifications','admin/notification-center','admin/announcements']) ?>"><a href="<?= url('admin/notification-center') ?>">✉ Bildirimler</a><div><a href="<?= url('admin/notifications') ?>">SMS / WhatsApp / Mail</a><a href="<?= url('admin/notification-center') ?>">Notification Center</a><a href="<?= url('admin/announcements') ?>">Duyurular</a></div></div>
<div class="nav-label">İçerikler</div>
<div class="nav-group<?= ao_nav_open_v21(['admin/site-builder/pages','admin/references','admin/announcements','admin/support/knowledgebase']) ?>"><a href="<?= url('admin/site-builder/pages') ?>">📝 İçerikler</a><div><a href="<?= url('admin/site-builder/pages') ?>">Sayfalar</a><a href="<?= url('admin/references') ?>">Referanslar</a><a href="<?= url('admin/announcements') ?>">Duyurular</a><a href="<?= url('admin/support/knowledgebase') ?>">Bilgi Bankası / Makaleler</a><a href="<?= url('admin/support/knowledgebase') ?>">SSS</a><a href="<?= url('admin/site-builder/pages') ?>">Yasal Sayfalar</a></div></div>
<div class="nav-label">Site & Görünüm</div>
<div class="nav-group<?= ao_nav_open_v21(['admin/site-builder','admin/theme-center','admin/builder-pro','admin/mobile-builder','admin/references']) ?>"><a href="<?= url('admin/site-builder') ?>">🎨 Builder & Tema</a><div><a href="<?= url('admin/site-builder/pages') ?>">Sayfalar</a><a href="<?= url('admin/site-builder/editor') ?>">Site Builder</a><a href="<?= url('admin/theme-center/themes') ?>">Temalar</a><a href="<?= url('admin/theme-center/editor') ?>">Tema Editörü</a><a href="<?= url('admin/builder-pro') ?>">Builder Pro</a><a href="<?= url('admin/mobile-builder') ?>">Mobil Builder</a><a href="<?= url('admin/references') ?>">Referanslar</a></div></div>
<div class="nav-group<?= ao_nav_open_v21('admin/menu-manager') ?>"><a href="<?= url('admin/menu-manager') ?>">☰ Menü Yönetimi</a><div><a href="<?= url('admin/menu-manager?type=admin') ?>">Admin Menü</a><a href="<?= url('admin/menu-manager?type=site') ?>">Site Menü</a><a href="<?= url('admin/menu-manager?type=mobile') ?>">Mobil Menü</a></div></div>
<div class="nav-label">Sistem</div>
<div class="nav-group<?= ao_nav_open_v21(['admin/settings','admin/setup-wizard','admin/module-center','admin/api-integrations','admin/security','admin/scan-report','admin/update-center','admin/backup-center','admin/cache-center','admin/health-center','admin/migration-bridge','admin/automation','admin/build-center']) ?>"><a href="<?= url('admin/settings') ?>">⚙ Ayarlar</a><div><a href="<?= url('admin/setup-wizard') ?>">Kurulum Sihirbazı</a><a href="<?= url('admin/settings') ?>">Genel Ayarlar</a><a href="<?= url('admin/module-center') ?>">Modül Merkezi</a><a href="<?= url('admin/api-integrations') ?>">API Entegrasyonları</a><a href="<?= url('admin/provider-center') ?>">Provider Center</a><a href="<?= url('admin/currency-center') ?>">Kur Merkezi</a><a href="<?= url('admin/translation-center') ?>">Çeviri Merkezi</a><a href="<?= url('admin/security') ?>">Güvenlik</a><a href="<?= url('admin/scan-report') ?>">Sistem Taraması</a><a href="<?= url('admin/qa-visual-scan') ?>">QA Görsel Tarama</a><a href="<?= url('admin/backup-center') ?>">Yedekleme</a><a href="<?= url('admin/update-center') ?>">Güncelleme</a><a href="<?= url('admin/migration-bridge') ?>">Migration & Bridge</a><a href="<?= url('admin/automation') ?>">Otomasyonlar</a></div></div>
<div class="nav-group<?= ao_nav_open_v21(['admin/license-center','admin/ai-center','admin/ai-copilot','admin/operations-center','admin/help-center','admin/logs']) ?>"><a href="<?= url('admin/operations-center') ?>">🧩 Gelişmiş</a><div><a href="<?= url('admin/license-center') ?>">License Center</a><a href="<?= url('admin/ai-center') ?>">AI Center</a><a href="<?= url('admin/ai-copilot') ?>">AI Copilot</a><a href="<?= url('admin/operations-center') ?>">Operasyon Merkezi</a><a href="<?= url('admin/build-center') ?>">Build Center</a><a href="<?= url('admin/logs') ?>">Loglar</a><a href="<?= url('admin/help-center') ?>">Yardım Merkezi</a></div></div>
<div class="nav-separator"></div><a href="<?= url('client/login') ?>">👤 Müşteri Paneli</a><a href="<?= url('') ?>">↗ Site Ön Yüzü</a><a href="<?= url('admin/logout') ?>">⎋ Çıkış</a></nav><div class="ao-powered">© <?= date('Y') ?> Ahost One</div></aside>
<main class="ao-main"><header class="ao-topbar ao-saas-topbar ao-v222-topbar"><button class="ao-mobile-toggle" type="button" onclick="document.body.classList.toggle('sidebar-open')">☰</button>
  <div class="ao-v222-dd" data-v222-dropdown>
    <button class="ao-v222-quick" type="button" aria-expanded="false">☰ Hızlı Erişim <span>⌄</span></button>
    <div class="ao-v222-mega">
      <a href="<?= url('admin/domain-center') ?>"><b>🌐 Domain Center</b><small>Domain, transfer, DNS, SSL</small></a>
      <a href="<?= url('admin/hosting-server') ?>"><b>🖥 Hosting Center</b><small>Sunucular, WHM, hesaplar</small></a>
      <a href="<?= url('admin/site-builder') ?>"><b>🎨 SiteBuilder</b><small>Sayfalar, builder, şablonlar</small></a>
      <a href="<?= url('admin/mobile-builder') ?>"><b>📱 MobileBuilder</b><small>Projeler, APK, yayın</small></a>
      <a href="<?= url('admin/product-center') ?>"><b>📦 Ürün Merkezi</b><small>Paketler ve fiyatlar</small></a>
      <a href="<?= url('admin/marketplace') ?>"><b>🛍 Marketplace</b><small>İlan, kategori, satış</small></a>
      <a href="<?= url('admin/accounting') ?>"><b>₺ Finans</b><small>Fatura, tahsilat, rapor</small></a>
      <a href="<?= url('admin/support') ?>"><b>🎧 Destek Merkezi</b><small>Ticket ve bilgi bankası</small></a>
      <a href="<?= url('admin/theme-center/themes') ?>"><b>🎭 Tema Merkezi</b><small>Tema seçimi ve editör</small></a>
      <a href="<?= url('admin/menu-manager?type=site') ?>"><b>☰ Menü Yönetimi</b><small>Site, admin, mobil menü</small></a>
      <a href="<?= url('admin/settings') ?>"><b>⚙ Ayarlar Merkezi</b><small>Sekmeli sistem ayarları</small></a>
      <a href="<?= url('admin/setup-wizard') ?>"><b>🧭 Kurulum Sihirbazı</b><small>Adım adım kurulum</small></a>
    </div>
  </div>
  <form class="ao-admin-search ao-v222-search" method="get" action="<?= url('admin/search') ?>"><input name="q" placeholder="Müşteri, sipariş, domain, modül ara..." value="<?= e($_GET['q'] ?? '') ?>"><button>🔎</button></form>
  <div class="ao-v222-dd" data-v222-dropdown>
    <button class="ao-v222-new" type="button">➕ Yeni <span>⌄</span></button>
    <div class="ao-v222-menu small">
      <a href="<?= url('admin/customers/add') ?>">👥 Müşteri</a><a href="<?= url('admin/orders/new') ?>">🛒 Sipariş</a><a href="<?= url('admin/product-center/products') ?>">📦 Ürün</a><a href="<?= url('admin/domain-center') ?>">🌐 Domain</a><a href="<?= url('admin/accounting/invoices') ?>">₺ Fatura</a><a href="<?= url('admin/support/tickets') ?>">🎧 Destek Talebi</a><a href="<?= url('admin/site-builder/pages') ?>">📄 Sayfa</a><a href="<?= url('admin/marketplace') ?>">🛍 Marketplace İlanı</a>
    </div>
  </div>
  <a class="ao-v222-site" target="_blank" href="<?= url('') ?>">🌐 Siteyi Gör</a>
  <a class="ao-v222-bell" href="<?= url('admin/notification-center') ?>">🔔</a>
  <div class="ao-v222-dd" data-v222-dropdown>
    <button class="ao-v222-user" type="button">👤 <?= e(current_admin()['username'] ?? 'admin') ?> <span>⌄</span></button>
    <div class="ao-v222-menu small right"><a href="<?= url('admin/settings') ?>">Profil / Ayarlar</a><a href="<?= url('admin/health-center') ?>">Sistem Sağlığı</a><a href="<?= url('admin/cache-center') ?>">Cache Temizle</a><a target="_blank" href="<?= url('') ?>">Siteyi Gör</a><a href="<?= url('admin/logout') ?>">Çıkış Yap</a></div>
  </div>
</header><section class="ao-content">
