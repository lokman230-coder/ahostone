<?php
try{$carts=db()->query('SELECT * FROM abandoned_carts ORDER BY updated_at DESC, id DESC')->fetchAll();}catch(Throwable $e){$carts=[];}
$totalLost=array_sum(array_map(fn($c)=>(float)($c['total']??0),$carts));
$reminders=count(array_filter($carts,fn($c)=>!empty($c['last_reminder_at'])));
$recovered=count(array_filter($carts,fn($c)=>($c['status']??'')==='recovered'));
$rate=count($carts)>0?round(($recovered/count($carts))*100):0;
?>
<div class="ao-hero-slim abandoned-hero-v2410"><h2>Yarım Kalan Sepetler</h2><p>Tamamlanmamış siparişleri takip et, kayıp geliri ölç, hatırlatma ve kuponlarla satışa dönüştür.</p></div>
<div class="abandoned-kpi-grid-v2410">
  <div class="abandoned-kpi-card"><span>🛒 Yarım Kalan Sepet</span><strong><?= count($carts) ?></strong><em>Son kayıtlar</em></div>
  <div class="abandoned-kpi-card"><span>💰 Tahmini Kayıp Gelir</span><strong><?= number_format($totalLost,2,',','.') ?> ₺</strong><em>Potansiyel kazanç</em></div>
  <div class="abandoned-kpi-card"><span>📧 Gönderilen Hatırlatma</span><strong><?= $reminders ?></strong><em>Bu ay / toplam</em></div>
  <div class="abandoned-kpi-card"><span>📈 Geri Kazanım Oranı</span><strong>%<?= $rate ?></strong><em>Dönüşüm</em></div>
</div>
<div class="ao-grid two abandoned-admin-grid-v2410">
  <div class="ao-card"><h3>Akıllı Filtreler</h3><div class="abandoned-filter-pills"><a href="?range=today">Bugün</a><a href="?range=7">7 Gün</a><a href="?range=30" class="active">30 Gün</a><a href="?range=90">90 Gün</a><a href="?range=all">Tümü</a></div><p class="ao-muted">Filtreler sonraki aşamada rapor ve otomasyon kurallarıyla bağlanacak.</p></div>
  <div class="ao-card"><h3>Hatırlatma Otomasyonu</h3><div class="abandoned-rules"><span>1 saat sonra e-posta</span><span>24 saat sonra hatırlatma</span><span>3 gün sonra kupon</span><span>7 gün sonra son hatırlatma</span></div></div>
</div>
<div class="ao-card abandoned-table-card-v2410"><h3>Sepet Listesi</h3><div class="ao-table-wrap"><table class="ao-table"><thead><tr><th>Müşteri</th><th>E-posta</th><th>Telefon</th><th>Tutar</th><th>Ürünler</th><th>Son Aktivite</th><th>Durum</th><th>İşlem</th></tr></thead><tbody><?php foreach($carts as $c): ?><tr><td><?= e($c['customer_name']??'-') ?></td><td><?= e($c['email']??'-') ?></td><td><?= e($c['phone']??'-') ?></td><td><strong><?= number_format((float)($c['total']??0),2,',','.') ?> <?= e($c['currency']??'TRY') ?></strong></td><td><?= e(mb_substr($c['items_json']??'',0,80)) ?></td><td><?= e($c['updated_at']??'-') ?></td><td><span class="ao-pill"><?= e($c['status']??'open') ?></span></td><td class="ao-action-row"><a class="ao-btn small" href="<?= url('admin/orders/abandoned-remind?id='.(int)$c['id'].'&csrf_token='.csrf_token()) ?>">Mail Gönder</a><a class="ao-btn small soft" href="<?= url('admin/orders/new?cart_id='.(int)$c['id']) ?>">Siparişe Dönüştür</a><a class="ao-btn small soft" href="<?= url('admin/promotions?cart_id='.(int)$c['id']) ?>">Kupon Gönder</a><a class="ao-btn small ao-mini-danger" data-confirm="Sepet kapatılsın mı?" href="<?= url('admin/orders/abandoned-close?id='.(int)$c['id'].'&csrf_token='.csrf_token()) ?>">Kapat</a></td></tr><?php endforeach; if(!$carts): ?><tr><td colspan="8">Henüz yarım kalan sepet yok.</td></tr><?php endif; ?></tbody></table></div></div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
