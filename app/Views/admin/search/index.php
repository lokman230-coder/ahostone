<?php $query=$query ?? ''; $results=$results ?? []; ?>
<div class="ao-page-head"><div><h2>Admin Arama</h2><p>Menü adını bilmeden ayar, modül ve işlemleri hızlı bul.</p></div></div>
<div class="ao-card">
  <form method="get" action="<?= url('admin/search') ?>" class="ao-form-inline">
    <input name="q" value="<?= e($query) ?>" placeholder="Örn: kredi kartı ayarları, sanal pos, domainnameapi, sms" style="min-width:360px">
    <button class="ao-btn">Ara</button>
  </form>
</div>
<div class="ao-card"><h3>Sonuçlar</h3>
<?php if($query===''): ?><p class="muted">Aramak istediğin ayar veya modülü yaz.</p><?php elseif(!$results): ?><p>Sonuç bulunamadı. Farklı anahtar kelime dene.</p><?php else: ?>
<table class="ao-table"><thead><tr><th>Başlık</th><th>Kategori</th><th>Anahtar Kelimeler</th><th>Git</th></tr></thead><tbody>
<?php foreach($results as $r): ?><tr><td><b><?= e($r['title']) ?></b></td><td><?= e($r['category']) ?></td><td><small><?= e($r['keywords']) ?></small></td><td><a class="ao-btn" href="<?= url($r['route']) ?>">Aç</a></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?></div>
