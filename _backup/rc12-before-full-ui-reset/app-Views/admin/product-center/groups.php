<?php
$flash=get_flash();
try { if(function_exists('ao_v2334_ensure_product_group_schema')) ao_v2334_ensure_product_group_schema(); } catch(Throwable $e) {}
$groups=[];
try{$groups=db()->query('SELECT * FROM product_groups ORDER BY COALESCE(sort_order,0),name')->fetchAll();}catch(Throwable $e){}
$editId=(int)($_GET['edit']??0);
$edit=null;
if($editId>0){
  foreach($groups as $row){ if((int)$row['id']===$editId){ $edit=$row; break; } }
}
$typeLabels=[
 'hosting'=>'Hosting','domain'=>'Domain','server'=>'VPS / Sunucu','ssl'=>'SSL','sitebuilder'=>'SiteBuilder','mobilebuilder'=>'MobileBuilder','web'=>'Web Tasarım','mobile'=>'Mobil Uygulama','seo'=>'SEO','digital'=>'Dijital Hizmetler','marketplace'=>'Marketplace','service'=>'Diğer'
];
?>
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

<div class="ao-page-head v236-product-head">
  <div>
    <span class="ao-kicker">Ürün Merkezi</span>
    <h2>Ürün Grupları</h2>
    <p>Ahost One'da hosting, domain, builder, web tasarım, mobil uygulama ve marketplace ailelerini tek yerden yönetin.</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap"><button type="button" class="ao-btn" onclick="document.querySelector('.group-form-collapsed')?.classList.toggle('is-open')">+ Yeni Grup Ekle</button><form method="post" action="<?= url('admin/product-center/groups/seed-defaults') ?>">
    <?= csrf_field() ?>
    <button class="ao-btn soft" type="submit">✨ Varsayılan Grupları Oluştur</button>
  </form></div>
</div>

<?php if($groups): ?><div class="product-group-tabs-v2411"><?php foreach($groups as $i=>$g): ?><a class="<?= $editId==(int)$g['id']?'active':'' ?>" href="<?= url('admin/product-center/groups?edit='.(int)$g['id']) ?>"><?= e($g['name']) ?></a><?php endforeach; ?></div><?php endif; ?>

<div class="v236-product-layout">
  <section class="ao-card v236-product-form-card group-form-collapsed <?= $edit?'is-open':'' ?>">
    <div class="v236-card-title">
      <div>
        <span><?= $edit ? 'Düzenleme Modu' : 'Yeni Grup' ?></span>
        <h3><?= $edit ? e($edit['name']) : 'Ürün Grubu Ekle' ?></h3>
      </div>
      <?php if($edit): ?><a class="ao-light-btn" href="<?= url('admin/product-center/groups') ?>">+ Yeni</a><?php endif; ?>
    </div>
    <form class="ao-form v236-product-form" method="post" action="<?= url('admin/product-center/group-save') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <label>Grup Adı<input name="name" required placeholder="Hosting" value="<?= e($edit['name'] ?? '') ?>"></label>
      <label>Slug<input name="slug" placeholder="hosting" value="<?= e($edit['slug'] ?? '') ?>"></label>
      <label>Tip<select name="type">
        <?php foreach($typeLabels as $value=>$label): ?><option value="<?= e($value) ?>" <?= (($edit['type'] ?? 'hosting')===$value)?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?>
      </select></label>
      <label>Sıra<input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
      <label>Durum<select name="is_active"><option value="1" <?= (!isset($edit['is_active']) || (int)$edit['is_active']===1)?'selected':'' ?>>Aktif</option><option value="0" <?= (isset($edit['is_active']) && (int)$edit['is_active']===0)?'selected':'' ?>>Pasif</option></select></label>
      <label class="full">Açıklama<textarea name="description" rows="4" placeholder="Bu grubun ne için kullanılacağını yazın."><?= e($edit['description'] ?? '') ?></textarea></label>
      <div class="v236-form-actions full">
        <button class="ao-btn" type="submit"><?= $edit ? 'Değişiklikleri Kaydet' : 'Kaydet' ?></button>
        <?php if($edit): ?><a class="ao-btn soft" href="<?= url('admin/product-center/groups') ?>">Vazgeç</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="ao-card v236-product-list-card">
    <div class="v236-card-title">
      <div><span>Kayıtlı Gruplar</span><h3><?= count($groups) ?> grup</h3></div>
    </div>
    <?php if(!$groups): ?>
      <div class="v236-empty-state">
        <strong>Henüz ürün grubu yok.</strong>
        <p>Başlamak için varsayılan ürün gruplarını oluşturabilir veya soldaki formdan yeni grup ekleyebilirsiniz.</p>
        <form method="post" action="<?= url('admin/product-center/groups/seed-defaults') ?>"><?= csrf_field() ?><button class="ao-btn">Varsayılan Grupları Oluştur</button></form>
      </div>
    <?php else: ?>
      <div class="v236-group-list">
        <?php foreach($groups as $g):
          $active=!isset($g['is_active']) || (int)$g['is_active']===1;
          $type=$g['type'] ?? 'service';
        ?>
          <article class="v236-group-row <?= $active?'is-active':'is-passive' ?>">
            <div class="v236-group-main">
              <div class="v236-group-icon"><?= e(mb_substr((string)$g['name'],0,1,'UTF-8')) ?></div>
              <div>
                <h4><?= e($g['name']) ?></h4>
                <p><?= e($g['description'] ?? 'Açıklama girilmemiş.') ?></p>
              </div>
            </div>
            <div class="v236-group-meta"><small>Slug</small><code><?= e($g['slug']) ?></code></div>
            <div class="v236-group-meta"><small>Tip</small><span><?= e($typeLabels[$type] ?? $type) ?></span></div>
            <div class="v236-group-meta"><small>Sıra</small><span><?= (int)($g['sort_order'] ?? 0) ?></span></div>
            <div class="v236-group-status"><span class="ao-badge <?= $active?'active':'inactive' ?>"><?= $active?'Aktif':'Pasif' ?></span></div>
            <div class="v236-group-actions">
              <a class="ao-mini-btn" href="<?= url('admin/product-center/groups?edit='.(int)$g['id']) ?>">✏️ Düzenle</a>
              <a class="ao-mini-btn" href="<?= url('admin/product-center/group-toggle?id='.(int)$g['id'].'&csrf_token='.csrf_token()) ?>"><?= $active?'Pasife Al':'Aktif Et' ?></a>
              <a class="ao-mini-btn danger" onclick="return confirm('Bu ürün grubunu silmek istediğinize emin misiniz? Bu gruba bağlı ürün varsa silinmez.');" href="<?= url('admin/product-center/group-delete?id='.(int)$g['id'].'&csrf_token='.csrf_token()) ?>">🗑️ Sil</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
