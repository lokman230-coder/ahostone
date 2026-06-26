<?php
require_once __DIR__ . '/../shared/content-renderer.php';
try {
    $pdo = db();
    $posts = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug FROM blog_posts p LEFT JOIN blog_categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY COALESCE(p.published_at,p.created_at) DESC LIMIT 12")->fetchAll();
    $featured = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug FROM blog_posts p LEFT JOIN blog_categories c ON c.id=p.category_id WHERE p.status='published' AND p.is_featured=1 ORDER BY COALESCE(p.published_at,p.created_at) DESC LIMIT 3")->fetchAll();
    $categories = $pdo->query("SELECT c.*, COUNT(p.id) as post_count FROM blog_categories c LEFT JOIN blog_posts p ON p.category_id=c.id AND p.status='published' WHERE c.is_active=1 GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();
} catch(Throwable $e) { $posts=[]; $featured=[]; $categories=[]; }
ob_start();
?>
<?php if($featured): ?>
<section class="ao-content-panel">
  <div class="ao-content-meta"><strong>Öne Çıkan Yazılar</strong><span>•</span><span>Admin içeriklerinden otomatik yayınlanır</span></div>
  <?= ao_site_content_grid($featured, ['type'=>'blog-featured','href'=>fn($i)=>url('blog/'.$i['slug']),'link_text'=>'Devamını Oku','empty_title'=>'Öne çıkan yazı yok']) ?>
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
<?= ao_site_content_grid($posts, [
  'type'=>'blog',
  'href'=>fn($i)=>url('blog/'.$i['slug']),
  'link_text'=>'Devamını Oku',
  'empty_title'=>'Henüz yazı yok',
  'empty_text'=>'Admin panelinden yayınlanan blog yazıları burada ortak içerik tasarımıyla listelenir.'
]) ?>
<?php
$content = ob_get_clean();
ao_site_content_page([
  'content'=>$content,
  'heroTitle'=>'Ahost One Blog',
  'kicker'=>'Blog & Rehberler',
  'summary'=>'Hosting, domain, güvenlik ve teknoloji rehberleri tek premium içerik görünümüyle yayında.',
  'breadcrumbs'=>[['label'=>'Ana Sayfa','href'=>url('')], ['label'=>'Blog']]
]);
