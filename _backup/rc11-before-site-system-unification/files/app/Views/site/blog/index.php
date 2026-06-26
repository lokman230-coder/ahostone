<?php
// Blog listing - RC6 shared site content UI
try {
    $pdo = db();
    $posts = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug FROM blog_posts p LEFT JOIN blog_categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY COALESCE(p.published_at,p.created_at) DESC LIMIT 12")->fetchAll();
    $featured = $pdo->query("SELECT p.*, c.name as category_name FROM blog_posts p LEFT JOIN blog_categories c ON c.id=p.category_id WHERE p.status='published' AND p.is_featured=1 ORDER BY COALESCE(p.published_at,p.created_at) DESC LIMIT 3")->fetchAll();
    $categories = $pdo->query("SELECT c.*, COUNT(p.id) as post_count FROM blog_categories c LEFT JOIN blog_posts p ON p.category_id=c.id AND p.status='published' WHERE c.is_active=1 GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();
} catch(Throwable $e) { $posts=[]; $featured=[]; $categories=[]; }
$pageTitle = 'Ahost One Blog';
require __DIR__ . '/../partials/header.php';
ob_start();
?>
<?php if($featured): ?>
<section class="ao-content-panel">
  <div class="ao-content-meta"><strong>Öne Çıkan Yazılar</strong><span>•</span><span>Admin içeriklerinden otomatik yayınlanır</span></div>
  <div class="ao-content-grid">
    <?php foreach($featured as $f): ?>
    <article class="ao-content-card">
      <?php if(!empty($f['featured_image'])): ?><img src="<?= e($f['featured_image']) ?>" alt="<?= e($f['title']) ?>"><?php endif; ?>
      <span class="ao-content-badge"><?= e($f['category_name'] ?? 'Genel') ?></span>
      <h3><?= e($f['title']) ?></h3>
      <p><?= e(mb_substr(strip_tags((string)($f['excerpt'] ?? '')),0,135,'UTF-8')) ?></p>
      <div class="ao-content-meta"><?= date('d.m.Y', strtotime($f['published_at'] ?? $f['created_at'] ?? 'now')) ?></div>
      <div class="ao-content-actions"><a class="ao-content-btn secondary" href="<?= url('blog/'.$f['slug']) ?>">Devamını Oku</a></div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if($categories): ?>
<nav class="ao-content-pills" aria-label="Blog kategorileri">
  <a class="ao-content-pill active" href="<?= url('blog') ?>">Tümü</a>
  <?php foreach($categories as $cat): if((int)($cat['post_count'] ?? 0) <= 0) continue; ?>
  <a class="ao-content-pill" href="<?= url('blog/category/'.$cat['slug']) ?>"><?= e($cat['name']) ?></a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<section class="ao-content-grid">
  <?php foreach($posts as $p): ?>
  <article class="ao-content-card">
    <?php if(!empty($p['featured_image'])): ?><img src="<?= e($p['featured_image']) ?>" alt="<?= e($p['title']) ?>"><?php endif; ?>
    <span class="ao-content-badge"><?= e($p['category_name'] ?? 'Genel') ?></span>
    <h3><?= e($p['title']) ?></h3>
    <p><?= e(mb_substr(strip_tags((string)($p['excerpt'] ?? $p['content'] ?? '')),0,155,'UTF-8')) ?><?= mb_strlen(strip_tags((string)($p['excerpt'] ?? $p['content'] ?? '')),'UTF-8')>155?'…':'' ?></p>
    <div class="ao-content-meta"><span><?= date('d.m.Y', strtotime($p['published_at'] ?? $p['created_at'] ?? 'now')) ?></span><?php if(isset($p['view_count'])): ?><span>•</span><span><?= (int)$p['view_count'] ?> görüntülenme</span><?php endif; ?></div>
    <div class="ao-content-actions"><a class="ao-content-btn secondary" href="<?= url('blog/'.$p['slug']) ?>">Devamını Oku</a></div>
  </article>
  <?php endforeach; ?>
</section>
<?php if(!$posts): ?><div class="ao-content-empty"><h3>Henüz yazı yok</h3><p>Admin panelinden yayınlanan blog yazıları burada ortak içerik tasarımıyla listelenir.</p></div><?php endif; ?>
<?php
$content = ob_get_clean();
$heroTitle = 'Ahost One Blog';
$kicker = 'Blog & Rehberler';
$summary = 'Hosting, domain, güvenlik ve teknoloji rehberleri tek premium içerik görünümüyle yayında.';
$breadcrumbs = [['label'=>'Ana Sayfa','href'=>url('')], ['label'=>'Blog']];
require __DIR__ . '/../shared/content-page.php';
require __DIR__ . '/../partials/footer.php';
