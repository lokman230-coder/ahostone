<?php /* RC10 reference shell. Current render_view flow uses customer/partials/header.php for compatibility. */ ?>
<?php require __DIR__ . '/../customer/partials/header.php'; ?>
<?= $content ?? '' ?>
<?php require __DIR__ . '/../customer/partials/footer.php'; ?>
