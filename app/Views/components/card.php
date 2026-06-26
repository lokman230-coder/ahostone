<?php $title=$title ?? ''; $body=$body ?? ''; ?>
<section class="ao-card"><?php if($title): ?><h2><?= e($title) ?></h2><?php endif; ?><?= $body ?></section>
