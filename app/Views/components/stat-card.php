<?php $label=$label ?? ''; $value=$value ?? '0'; $hint=$hint ?? ''; $tone=$tone ?? ''; ?>
<div class="ao-stat-card <?= e($tone) ?>"><span><?= e($label) ?></span><b><?= e($value) ?></b><?php if($hint): ?><small><?= e($hint) ?></small><?php endif; ?></div>
