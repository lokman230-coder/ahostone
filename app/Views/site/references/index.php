<?php
ao_v2450_ensure_showcase_schema(); $rows=[];
try{$rows=db()->query('SELECT * FROM portfolio_references WHERE is_active=1 ORDER BY is_featured DESC,sort_order,id')->fetchAll() ?: [];}catch(Throwable $e){}
$cats=['all'=>'Tümü','website'=>'Web Siteleri','android'=>'Android','ios'=>'iOS','ecommerce'=>'E-Ticaret','corporate'=>'Kurumsal'];
function ao_reference_cards_v2464($items){ foreach($items as $r): $type=$r['reference_type'] ?: 'website'; $cover=trim((string)($r['cover_image_url'] ?: $r['image_url'] ?: 'public/assets/img/placeholder-product.svg')); $logo=trim((string)($r['logo_url'] ?? '')); ?>
  <article class="ao-content-card ref-card" data-ref-type="<?= e($type) ?>" data-ref-sector="<?= e(mb_strtolower((string)($r['sector']??''),'UTF-8')) ?>">
    <div class="ref-cover <?= e($type) ?>" style="background-image:url('<?= e(url($cover)) ?>')"><span><?= $type==='android'?'Android':'Web' ?></span></div>
    <div class="ref-body">
      <?php if($logo): ?><img class="ref-logo" src="<?= e(url($logo)) ?>" alt="<?= e($r['title'] ?? '') ?> logo"><?php endif; ?>
      <small><?= e($r['sector'] ?? '') ?></small><h3><?= e($r['title'] ?? '') ?></h3><p><?= e($r['short_description'] ?? '') ?></p><div class="ref-tech"><?= e($r['technologies'] ?? '') ?></div><?php if(!empty($r['project_url'])): ?><a target="_blank" rel="noopener" href="<?= e($r['project_url']) ?>">Projeyi İncele →</a><?php endif; ?></div>
  </article>
<?php endforeach; }

?>
<section class="ao-site-content ao-references-page"><div class="ao-content-shell ref-page">
  <header class="ao-content-hero ref-hero"><span class="ao-content-kicker">Seçili Çalışmalar</span><h1>Fikirleri çalışan dijital ürünlere dönüştürüyoruz.</h1><p>Web siteleri ve Android uygulamalarını kategorilerle yan yana keşfedin; sayfa aşağı kaymadan filtreleyin.</p><div class="ao-content-actions"><a class="ao-content-btn" href="<?= url('client/register') ?>">Projenizi Başlatın</a><a class="ao-content-btn secondary" href="<?= url('urunler') ?>">Çözümleri İnceleyin</a></div></header>
  <nav class="ref-tabs" aria-label="Referans kategorileri"><?php foreach($cats as $k=>$label): ?><a href="#" class="ref-tab <?= $k==='all'?'is-active':'' ?>" data-ref-filter="<?= e($k) ?>"><?= e($label) ?></a><?php endforeach; ?></nav>
  <div class="ref-grid" data-ref-grid><?php ao_reference_cards_v2464($rows); ?></div>
</div></section>
<script>document.addEventListener('click',function(e){var t=e.target.closest('[data-ref-filter]'); if(!t)return; e.preventDefault(); var f=t.dataset.refFilter; document.querySelectorAll('[data-ref-filter]').forEach(function(x){x.classList.toggle('is-active',x===t)}); var grid=document.querySelector('[data-ref-grid]'); if(!grid)return; grid.classList.toggle('is-filtered',f!=='all'); grid.querySelectorAll('.ref-card').forEach(function(card){var ok=f==='all'||card.dataset.refType===f||(f==='ecommerce'&&(card.dataset.refSector||'').includes('ticaret'))||(f==='corporate'&&(card.dataset.refSector||'').includes('kurumsal')); card.classList.toggle('is-visible',ok);});});</script>
