<?php
/**
 * Ahost One RC12 Full UI Reset Head
 * Tek aktif görsel CSS: public/assets/css/ao-full-ui-reset.css
 */
$pageTitle = $pageTitle ?? 'Ahost One';
$aoHeadContext = $aoHeadContext ?? 'site';
$aoHeadTitleSuffix = $aoHeadTitleSuffix ?? 'Ahost One';
$aoHeadLang = function_exists('ao_current_language') ? ao_current_language() : 'tr';
$aoHeadScripts = $aoHeadScripts ?? [];
$aoHeadInlineScripts = $aoHeadInlineScripts ?? [];
if (!is_array($aoHeadScripts)) { $aoHeadScripts = [$aoHeadScripts]; }
if (!is_array($aoHeadInlineScripts)) { $aoHeadInlineScripts = [$aoHeadInlineScripts]; }
$aoCoreCss = ['css/ao-full-ui-reset.css'];
$aoCoreJs = ['js/ao-ui.js'];
$aoHeadScripts = array_values(array_filter(array_unique(array_merge($aoCoreJs, $aoHeadScripts))));
?>
<!doctype html>
<html lang="<?= e($aoHeadLang ?: 'tr') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <title><?= e($pageTitle) ?><?= $aoHeadTitleSuffix ? ' - '.e($aoHeadTitleSuffix) : '' ?></title>
  <?php foreach ($aoCoreCss as $cssFile): ?>
  <link rel="stylesheet" href="<?= assetv($cssFile) ?>">
  <?php endforeach; ?>
  <script>window.AHOST_BASE_URL = <?= json_encode(rtrim(url(''), '/')) ?>;</script>
  <?php foreach ($aoHeadScripts as $jsFile): if(!$jsFile) continue; ?>
  <script defer src="<?= assetv($jsFile) ?>"></script>
  <?php endforeach; ?>
  <?php foreach ($aoHeadInlineScripts as $script): if(!$script) continue; ?>
  <script><?= $script ?></script>
  <?php endforeach; ?>
</head>
