<?php /* RC10 reference shell. Current render_view flow uses admin/partials/header.php for compatibility. */ ?>
<?php require __DIR__ . '/../admin/partials/header.php'; ?>
<?= $content ?? '' ?>
<?php require __DIR__ . '/../admin/partials/footer.php'; ?>
