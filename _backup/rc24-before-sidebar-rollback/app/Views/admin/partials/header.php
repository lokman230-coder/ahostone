<?php
$pageTitle = $pageTitle ?? 'Admin Paneli';
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$currentRoute = $currentPath;
if (!function_exists('ao_nav_open_v21')) {
  function ao_nav_open_v21($needles){
    $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    foreach ((array)$needles as $n) {
      if ($n !== '' && ($uri === $n || str_starts_with($uri, rtrim($n,'/').'/') || str_contains($uri, $n))) return ' open';
    }
    return '';
  }
}
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
$quickLinks = [
  ['Müşteri Ekle','admin/customers/add','👥'],
  ['Yeni Sipariş','admin/orders/new','🛒'],
  ['Domain Center','admin/domain-center','🌐'],
  ['SiteBuilder','admin/site-builder','🎨'],
  ['MobileBuilder','admin/mobile-builder','📱'],
  ['API Entegrasyonları','admin/api-integrations','🔌'],
  ['QA & Scan','admin/qa-scan-center','🧪'],
  ['Ayarlar','admin/settings','⚙'],
];
?>
<body data-app="admin" class="ao-full-ui-reset admin-body">
<div class="ao-admin-shell ao-admin-shell-rc10-sidebar">
  <aside class="ao-admin-sidebar ao-sidebar ao-sidebar-rc10"><div class="ao-brand"><img src="<?= e(ao_brand_logo_url()) ?>" alt="Ahost One" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'"><strong style="display:none">Ahost One</strong><span>v25 RC22</span></div><nav class="ao-nav">
<a class="<?= $currentPath==='admin/dashboard'||$currentPath==='admin'?'active':'' ?>" href="<?= url('admin/dashboard') ?>">⌂ Kontrol Paneli</a>
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
<div class="nav-group<?= ao_nav_open_v21(['admin/settings','admin/setup-wizard','admin/module-center','admin/api-integrations','admin/security','admin/qa-scan-center','admin/scan-report','admin/qa-visual-scan','admin/update-center','admin/backup-center','admin/cache-center','admin/health-center','admin/migration-bridge','admin/automation','admin/build-center']) ?>"><a href="<?= url('admin/settings') ?>">⚙ Ayarlar</a><div><a href="<?= url('admin/setup-wizard') ?>">Kurulum Sihirbazı</a><a href="<?= url('admin/settings') ?>">Genel Ayarlar</a><a href="<?= url('admin/module-center') ?>">Modül Merkezi</a><a href="<?= url('admin/api-integrations') ?>">API Entegrasyonları</a><a href="<?= url('admin/provider-center') ?>">Provider Center</a><a href="<?= url('admin/currency-center') ?>">Kur Merkezi</a><a href="<?= url('admin/translation-center') ?>">Çeviri Merkezi</a><a href="<?= url('admin/security') ?>">Güvenlik</a><a href="<?= url('admin/qa-scan-center') ?>">QA &amp; Scan Center</a><a href="<?= url('admin/backup-center') ?>">Yedekleme</a><a href="<?= url('admin/update-center') ?>">Güncelleme</a><a href="<?= url('admin/migration-bridge') ?>">Migration & Bridge</a><a href="<?= url('admin/automation') ?>">Otomasyonlar</a></div></div>
<div class="nav-group<?= ao_nav_open_v21(['admin/license-center','admin/ai-center','admin/ai-copilot','admin/operations-center','admin/help-center','admin/logs']) ?>"><a href="<?= url('admin/operations-center') ?>">🧩 Gelişmiş</a><div><a href="<?= url('admin/license-center') ?>">License Center</a><a href="<?= url('admin/ai-center') ?>">AI Center</a><a href="<?= url('admin/ai-copilot') ?>">AI Copilot</a><a href="<?= url('admin/operations-center') ?>">Operasyon Merkezi</a><a href="<?= url('admin/build-center') ?>">Build Center</a><a href="<?= url('admin/logs') ?>">Loglar</a><a href="<?= url('admin/help-center') ?>">Yardım Merkezi</a></div></div>
<div class="nav-separator"></div><a href="<?= url('client/login') ?>">👤 Müşteri Paneli</a><a href="<?= url('') ?>">↗ Site Ön Yüzü</a><a href="<?= url('admin/logout') ?>">⎋ Çıkış</a></nav><div class="ao-powered">© <?= date('Y') ?> Ahost One</div></aside>
  <main class="ao-admin-main">
    <header class="ao-admin-topbar">
      <button class="ao-admin-mobile-toggle ao-btn" type="button" onclick="document.body.classList.toggle('sidebar-open')">☰</button>
      <details class="ao-quick-menu">
        <summary>☰ Hızlı Erişim</summary>
        <div>
          <?php foreach($quickLinks as $q): ?><a href="<?= url($q[1]) ?>"><span><?= e($q[2]) ?></span><?= e($q[0]) ?></a><?php endforeach; ?>
        </div>
      </details>
      <form class="ao-admin-search" method="get" action="<?= url('admin/search') ?>"><input name="q" placeholder="Müşteri, sipariş, domain, modül ara..." value="<?= e($_GET['q'] ?? '') ?>"></form>
      <a class="ao-btn ao-btn--ghost" href="<?= url('admin/orders/new') ?>">➕ Yeni Sipariş</a>
      <a class="ao-btn ao-btn--ghost" target="_blank" href="<?= url('') ?>">Siteyi Gör</a>
      <a class="ao-icon-link" href="<?= url('admin/notification-center') ?>">🔔</a>
      <a class="ao-btn ao-btn--primary" href="<?= url('admin/settings') ?>"><?= e($admin['username'] ?? 'Admin') ?></a>
    </header>
    <section class="ao-admin-content">
