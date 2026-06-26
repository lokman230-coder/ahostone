<?php
ao_v23_ensure_schema();
try{
  $articles=db()->query("SELECT * FROM knowledge_articles WHERE audience='customer' AND status IN ('published','draft') ORDER BY category,title LIMIT 200")->fetchAll();
}catch(Throwable $e){$articles=[];}
ob_start();
$grouped=[]; foreach($articles as $a){ $grouped[$a['category'] ?: 'Genel'][]=$a; }
?>
<?php if($grouped): ?>
  <?php foreach($grouped as $cat=>$rows): ?>
  <section class="ao-content-panel">
    <div class="ao-content-meta"><strong><?= e($cat) ?></strong><span>•</span><span><?= count($rows) ?> makale</span></div>
    <div class="ao-content-grid">
      <?php foreach($rows as $a): ?>
      <article class="ao-content-card">
        <span class="ao-content-badge"><?= e($a['category'] ?: 'Genel') ?></span>
        <h3><?= e($a['title']) ?></h3>
        <p><?= e($a['excerpt'] ?: mb_substr(strip_tags((string)($a['content'] ?? '')),0,145,'UTF-8')) ?></p>
        <div class="ao-content-actions"><a class="ao-content-btn secondary" href="<?= url('site/knowledge-base/'.$a['slug']) ?>">Oku</a></div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
<?php else: ?>
  <div class="ao-content-empty"><h3>Henüz makale yok</h3><p>Admin panelinden yayınlanan bilgi bankası makaleleri burada tek tasarım diliyle listelenir.</p></div>
<?php endif; ?>
<?php
$content=ob_get_clean();
$heroTitle='Ahost One Akademi';
$kicker='Bilgi Bankası';
$summary='Hosting, domain, mail, VPS ve sunucu rehberleri; admin içeriklerinden gelen makalelerle ortak premium içerik görünümünde.';
$breadcrumbs=[['label'=>'Ana Sayfa','href'=>url('')],['label'=>'Bilgi Bankası']];
require __DIR__.'/../shared/content-page.php';
