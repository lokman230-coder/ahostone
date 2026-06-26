<?php
$classes = trim('ao-site-content '.($class ?? ''));
$shellClass = trim('ao-content-shell '.(!empty($narrow) ? 'narrow' : '').' '.($shellClass ?? ''));
?>
<section class="<?= e($classes) ?>">
  <div class="<?= e($shellClass) ?>">
    <?php if(!empty($breadcrumbs)){ $items=$breadcrumbs; require __DIR__.'/breadcrumb.php'; } ?>
    <?php if(!empty($heroTitle) || !empty($heroHtml)): ?>
      <header class="ao-content-hero">
        <?php if(!empty($kicker)): ?><span class="ao-content-kicker"><?= e($kicker) ?></span><?php endif; ?>
        <?php if(!empty($heroTitle)): ?><h1><?= e($heroTitle) ?></h1><?php endif; ?>
        <?php if(!empty($summary)): ?><p><?= e($summary) ?></p><?php endif; ?>
        <?= $heroHtml ?? '' ?>
        <?php if(!empty($actions) && is_array($actions)): ?><div class="ao-content-actions"><?php foreach($actions as $a): ?><a class="ao-content-btn <?= !empty($a['secondary'])?'secondary':'' ?>" href="<?= e($a['href'] ?? '#') ?>"><?= e($a['label'] ?? 'Detay') ?></a><?php endforeach; ?></div><?php endif; ?>
      </header>
    <?php endif; ?>
    <?= $content ?? '' ?>
  </div>
</section>
