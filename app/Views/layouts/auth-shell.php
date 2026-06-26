<?php /* RC10 reference shell. Current render_view flow uses auth/partials/header.php for compatibility. */ ?>
<?php require __DIR__ . '/../auth/partials/header.php'; ?>
<?= $content ?? '' ?>
<?php require __DIR__ . '/../auth/partials/footer.php'; ?>
