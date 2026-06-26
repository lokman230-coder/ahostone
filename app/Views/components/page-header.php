<?php $title=$title ?? ($pageTitle ?? 'Sayfa'); $subtitle=$subtitle ?? ''; $actions=$actions ?? []; ?>
<div class="ao-page-header">
  <div><h1><?= e($title) ?></h1><?php if($subtitle): ?><p><?= e($subtitle) ?></p><?php endif; ?></div>
  <?php if($actions): ?><div class="ao-toolbar-actions"><?php foreach($actions as $a): ?><a class="ao-btn <?= e($a['class'] ?? '') ?>" href="<?= e($a['url'] ?? '#') ?>"><?= e($a['label'] ?? 'İşlem') ?></a><?php endforeach; ?></div><?php endif; ?>
</div>
