<?php
/**
 * Ahost One v25 RC5 Shared Layout Head
 *
 * Amaç:
 * - Site ana menü, admin giriş, müşteri giriş ve müşteri panel header CSS çağrısını tek noktadan yönetmek.
 * - Header için tek kanonik CSS: public/assets/css/ao-unified-header.css
 * - Sayfaya özel CSS dosyaları ayrı kalır; bu dosya tüm siteyi tek CSS'e indirmez, header katmanını tekleştirir.
 */
$pageTitle = $pageTitle ?? 'Ahost One';
$aoHeadContext = $aoHeadContext ?? 'site';
$aoHeadTitleSuffix = $aoHeadTitleSuffix ?? 'Ahost One';
$aoHeadLang = function_exists('ao_current_language') ? ao_current_language() : 'tr';
$aoHeadBodyCss = $aoHeadBodyCss ?? [];
$aoHeadCss = $aoHeadCss ?? [];
$aoHeadExternalCss = $aoHeadExternalCss ?? [];
$aoHeadScripts = $aoHeadScripts ?? [];
$aoHeadInlineScripts = $aoHeadInlineScripts ?? [];
$aoCanonicalHeaderCss = 'css/ao-unified-header.css';
$aoDesignCoreCss = ['css/ao-tokens.css','css/ao-components.css'];
$aoDesignContextCss = [
  'site' => ['css/ao-site-shell.css','css/ao-site-content.css'],
  'auth' => ['css/ao-auth-shell.css'],
  'admin-login' => ['css/ao-auth-shell.css'],
  'client' => ['css/ao-customer-shell.css'],
  'customer' => ['css/ao-customer-shell.css'],
];
$aoDesignLegacyCss = ['css/ao-legacy-compat.css'];
$aoDesignScripts = ['js/ao-ui.js'];

if (!is_array($aoHeadCss)) { $aoHeadCss = [$aoHeadCss]; }
if (!is_array($aoHeadExternalCss)) { $aoHeadExternalCss = [$aoHeadExternalCss]; }
if (!is_array($aoHeadScripts)) { $aoHeadScripts = [$aoHeadScripts]; }
if (!is_array($aoHeadInlineScripts)) { $aoHeadInlineScripts = [$aoHeadInlineScripts]; }

// RC11: Site/header görseli tek sistemden gelir. Emekli edilmiş CSS'ler yükten filtrelenir.
// Sıra: token + component + context shell/content + legacy compat + en son kanonik header CSS.
$aoContextCss = $aoDesignContextCss[$aoHeadContext] ?? [];
$aoRetiredCss = [
  'css/site-front.css','css/site-saas-v2211.css','css/frontend-home-v2430.css','css/frontend-products.css',
  'css/mobile-responsive-v2415.css','css/mobile-cleanup-v2416.css','css/desktop-menu.css','css/mobile-menu.css',
  'css/ahost-v2411-language-domain-polish.css','css/ahost-v2411-master-header-ui.css','css/ahost-v2490-checkout-header.css',
  'css/ao-premium-header.css','css/unified-header-v2440.css','css/v20-ultimate.css'
];
$aoHeadCss = array_values(array_filter($aoHeadCss, fn($css) => !in_array((string)$css, $aoRetiredCss, true)));
$aoHeadCss = array_values(array_filter(array_unique(array_merge(
    $aoDesignCoreCss,
    $aoContextCss,
    $aoDesignLegacyCss,
    [$aoCanonicalHeaderCss]
))));
$aoHeadScripts = array_values(array_filter(array_unique(array_merge($aoHeadScripts, $aoDesignScripts))));
?>
<!doctype html>
<html lang="<?= e($aoHeadLang ?: 'tr') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle) ?><?= $aoHeadTitleSuffix ? ' - '.e($aoHeadTitleSuffix) : '' ?></title>
  <?php foreach ($aoHeadCss as $cssFile): ?>
  <link rel="stylesheet" href="<?= assetv($cssFile) ?>">
  <?php endforeach; ?>
  <?php foreach ($aoHeadExternalCss as $cssHref): if(!$cssHref) continue; ?>
  <link rel="stylesheet" href="<?= e($cssHref) ?>">
  <?php endforeach; ?>
  <script>window.AHOST_BASE_URL = <?= json_encode(rtrim(url(''), '/')) ?>;</script>
  <?php foreach ($aoHeadScripts as $jsFile): if(!$jsFile) continue; ?>
  <script defer src="<?= assetv($jsFile) ?>"></script>
  <?php endforeach; ?>
  <?php foreach ($aoHeadInlineScripts as $script): if(!$script) continue; ?>
  <script><?= $script ?></script>
  <?php endforeach; ?>
</head>
