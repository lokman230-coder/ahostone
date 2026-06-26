<?php /* RC10 reference shell. Current render_view flow uses site/partials/header.php for compatibility. */ ?>
<?php require __DIR__ . '/../site/partials/header.php'; ?>
<?= $content ?? '' ?>
<?php require __DIR__ . '/../site/partials/footer.php'; ?>
