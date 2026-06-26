<?php
$pageTitle = $page_title ?? ($module_title ?? 'Modül');
require __DIR__ . '/../partials/header.php';
?>
<div class="ao-page-header">
  <div>
    <h1><?= e($pageTitle) ?></h1>
    <p><?= e($module_title ?? 'Ahost One modül yönetimi') ?></p>
  </div>
  <a class="ao-btn ao-btn--primary" href="#">+ Yeni Ekle</a>
</div>
<div class="ao-card">
  <?php if(isset($content)) echo $content; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
