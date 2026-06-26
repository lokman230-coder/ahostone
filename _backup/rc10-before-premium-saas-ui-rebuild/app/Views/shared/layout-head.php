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

if (!is_array($aoHeadCss)) { $aoHeadCss = [$aoHeadCss]; }
if (!is_array($aoHeadExternalCss)) { $aoHeadExternalCss = [$aoHeadExternalCss]; }
if (!is_array($aoHeadScripts)) { $aoHeadScripts = [$aoHeadScripts]; }
if (!is_array($aoHeadInlineScripts)) { $aoHeadInlineScripts = [$aoHeadInlineScripts]; }

// Header CSS her zaman sonlara yakın yüklenir ki sayfa CSS'leri header'ı bozmasın.
$aoHeadCss = array_values(array_filter(array_unique(array_merge($aoHeadCss, [$aoCanonicalHeaderCss]))));
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
