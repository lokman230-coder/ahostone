<?php
$pageTitle = $pageTitle ?? 'Ahost One';
$aoPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$aoIsProductPage = preg_match('~(^|/)(urun|urunler|product|products|hosting|vps|dedicated|ssl)(/|$)~i', $aoPath);
$aoIsDomainPage = preg_match('~(^|/)(domain|whois|dns|ssl)(/|$)~i', $aoPath);
$aoIsMarketplace = str_contains($aoPath, 'marketplace');
$aoHeadContext = 'site';
$aoHeadTitleSuffix = '';
$aoHeadCss = [
  'css/site-front.css',
  'css/site-saas-v2211.css',
  'css/frontend-home-v2430.css',
  'css/frontend-products.css',
  'css/support-widget-pro.css',
  'css/mobile-cleanup-v2416.css',
  'css/css-isolation-v2417.css',
  'css/premium-saas-v2441.css',
  'css/ahost-v2463-fixes.css',
  'css/ahost-v2465-polish.css',
  'css/ahost-v2468-polish.css',
];
if ($aoIsDomainPage) { $aoHeadCss[] = 'css/domain-center.css'; }
if (str_contains($aoPath, 'sitebuilder') || str_contains($aoPath, 'mobilebuilder')) { $aoHeadCss[] = 'css/site-builder.css'; }
if (str_contains($aoPath, 'sitebuilder/preview') || str_contains($aoPath, 'mobilebuilder/preview') || str_contains($aoPath, 'builders/gate') || str_contains($aoPath, 'sitebuilder/create-demo') || str_contains($aoPath, 'mobilebuilder/create-demo')) { $aoHeadCss[] = 'css/builder-public-v2411.css'; }
if ($aoIsMarketplace) { $aoHeadCss[] = 'css/marketplace.css'; }
// RC6 extension: shared site content CSS is loaded last to normalize legacy public pages without deleting old CSS.
$aoHeadCss[] = 'css/ao-site-content.css';
$aoHeadExternalCss = [];
$themeCss = ao_theme_css_href('site');
if ($themeCss) { $aoHeadExternalCss[] = $themeCss; }
$aoHeadScripts = ['js/front/site-header-v221.js'];
require __DIR__ . '/../../shared/layout-head.php';
$aoPreviewId=(int)($_GET['theme_preview'] ?? ($_SESSION['theme_preview_id'] ?? 0));
?>
<body data-app="site" class="ao-site <?= e(ao_theme_body_class('site')) ?>" style="<?= ao_theme_style_vars('site') ?>"><?= ao_theme_preview_bar($aoPreviewId) ?>
<?php
$aoAnnouncement = ao_active_site_announcement();
$aoSocials = ao_social_links();
$aoCustomer = current_customer();
$aoUnread = ao_customer_unread_notifications_count();
$aoCartCount = ao_cart_count();
$aoLang = ao_current_language();
$aoHeaderContext='site';
require __DIR__ . '/../../shared/unified-header.php';
?>
<main>
