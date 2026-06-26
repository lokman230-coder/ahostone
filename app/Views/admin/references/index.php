<?php
ao_v2450_ensure_showcase_schema();
$flash=get_flash(); $rows=[]; $edit=null;
try{
    $rows=db()->query('SELECT * FROM portfolio_references ORDER BY sort_order,id')->fetchAll() ?: [];
    $id=(int)($_GET['edit']??0);
    if($id){ $q=db()->prepare('SELECT * FROM portfolio_references WHERE id=?'); $q->execute([$id]); $edit=$q->fetch() ?: null; }
}catch(Throwable $e){}
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><span class="ao-kicker">İçerik Yönetimi</span><h2>Referanslar</h2><p>Web sitesi ve Android uygulama portföyünü yönetin.</p></div><a class="ao-btn soft" target="_blank" href="<?= url('referanslar') ?>">Vitrini Gör</a></div>
<div class="ao-grid two">
  <div class="ao-card">
    <h3><?= $edit?'Referansı Düzenle':'Yeni Referans' ?></h3>
    <form class="ao-form" method="post" action="<?= url('admin/references/save') ?>">
      <?= csrf_field() ?><?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
      <div class="ao-form-grid">
        <label>Başlık<input name="title" required value="<?= e($edit['title']??'') ?>"></label>
        <label>Slug<input name="slug" value="<?= e($edit['slug']??'') ?>"></label>
        <label>Tür<select name="reference_type"><option value="website" <?= ($edit['reference_type']??'')==='website'?'selected':'' ?>>Web Sitesi</option><option value="android" <?= ($edit['reference_type']??'')==='android'?'selected':'' ?>>Android Uygulama</option></select></label>
        <label>Sektör<input name="sector" value="<?= e($edit['sector']??'') ?>"></label>
        <label class="full">Kısa Açıklama<textarea name="short_description" rows="3"><?= e($edit['short_description']??'') ?></textarea></label>
        <label class="full">Detaylı İçerik<textarea name="description" rows="5"><?= e($edit['description']??'') ?></textarea></label>
        <label>Görsel URL<input name="image_url" value="<?= e($edit['image_url']??'') ?>"></label>
        <label>Proje URL<input name="project_url" value="<?= e($edit['project_url']??'') ?>"></label>
        <label>Teknolojiler<input name="technologies" value="<?= e($edit['technologies']??'') ?>"></label>
        <label>Sıralama<input type="number" name="sort_order" value="<?= e($edit['sort_order']??0) ?>"></label>
        <label><input type="checkbox" name="is_featured" value="1" <?= !empty($edit['is_featured'])?'checked':'' ?>> Öne çıkan</label>
        <label><input type="checkbox" name="is_active" value="1" <?= !$edit||!empty($edit['is_active'])?'checked':'' ?>> Yayında</label>
      </div>
      <button class="ao-btn">Kaydet</button><?php if($edit): ?> <a class="ao-btn ghost" href="<?= url('admin/references') ?>">Vazgeç</a><?php endif; ?>
    </form>
  </div>
  <div class="ao-card"><h3>Portföy Özeti</h3><div class="ao-grid two"><div class="ao-stat"><span>Web Sitesi</span><strong><?= count(array_filter($rows,fn($r)=>$r['reference_type']==='website')) ?></strong></div><div class="ao-stat"><span>Android</span><strong><?= count(array_filter($rows,fn($r)=>$r['reference_type']==='android')) ?></strong></div></div></div>
</div>
<div class="ao-card"><table class="ao-table"><thead><tr><th>Referans</th><th>Tür</th><th>Sektör</th><th>Durum</th><th>İşlem</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?= e($r['title']) ?></strong><br><small><?= e($r['technologies']) ?></small></td><td><?= $r['reference_type']==='android'?'Android':'Web Sitesi' ?></td><td><?= e($r['sector']) ?></td><td><?= $r['is_active']?'Yayında':'Taslak' ?></td><td><a href="<?= url('admin/references?edit='.(int)$r['id']) ?>">Düzenle</a> · <a onclick="return confirm('Referans silinsin mi?')" href="<?= url('admin/references/delete?id='.(int)$r['id'].'&csrf_token='.csrf_token()) ?>">Sil</a></td></tr><?php endforeach; ?></tbody></table></div>
