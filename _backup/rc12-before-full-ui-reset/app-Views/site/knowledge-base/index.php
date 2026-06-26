<?php
require_once __DIR__ . '/../shared/content-renderer.php';
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
    <?= ao_site_content_grid($rows, [
      'type'=>'knowledge-base',
      'badge_key'=>'category',
      'href'=>fn($i)=>url('bilgi-bankasi/'.$i['slug']),
      'link_text'=>'Oku',
      'empty_title'=>'Henüz makale yok'
    ]) ?>
  </section>
  <?php endforeach; ?>
<?php else: ?>
  <?= ao_site_content_grid([], ['empty_title'=>'Henüz makale yok','empty_text'=>'Admin panelinden yayınlanan bilgi bankası makaleleri burada tek tasarım diliyle listelenir.']) ?>
<?php endif; ?>
<?php
$content=ob_get_clean();
ao_site_content_page([
  'content'=>$content,
  'heroTitle'=>'Ahost One Akademi',
  'kicker'=>'Bilgi Bankası',
  'summary'=>'Hosting, domain, mail, VPS ve sunucu rehberleri; admin içeriklerinden gelen makalelerle ortak premium içerik görünümünde.',
  'breadcrumbs'=>[['label'=>'Ana Sayfa','href'=>url('')],['label'=>'Bilgi Bankası']]
]);
