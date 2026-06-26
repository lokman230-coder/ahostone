<?php $c=current_customer(); $flash=get_flash(); ao_schema_ensure_v990(); $transactions=[]; $topups=[]; try{$q=db()->prepare('SELECT * FROM credit_transactions WHERE customer_id=? ORDER BY id DESC LIMIT 10');$q->execute([$c['id']]);$transactions=$q->fetchAll();}catch(Throwable $e){} try{$q=db()->prepare('SELECT * FROM credit_topups WHERE customer_id=? ORDER BY id DESC LIMIT 10');$q->execute([$c['id']]);$topups=$q->fetchAll();}catch(Throwable $e){} ?>
<?php if($flash): ?><div class="auth-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="customer-panel-card cp-hero-card">
  <div>
    <span class="cp-kicker">Kredi Merkezi</span>
    <h2>Bakiye / Kredi Yükle</h2>
    <p>Hizmet yenileme, fatura ödeme ve ek hizmet satın alma işlemlerinde kullanabileceğiniz müşteri bakiyenizi yönetin.</p>
  </div>
  <div class="cp-balance-box"><span>Mevcut Bakiye</span><strong><?= number_format((float)$c['balance'],2,',','.') ?> ₺</strong></div>
</div>
<div class="customer-panel-card">
  <h3>Bakiye Yükle</h3>
  <form class="cp-form" method="post" action="<?= url('client/credit/add') ?>">
    <div class="cp-form-grid">
      <label>Tutar <input name="amount" type="number" min="1" step="0.01" value="250.00"></label>
      <label>Ödeme Yöntemi <select name="method"><option value="shopier">Shopier Kredi Kartı</option><option value="paytr">PayTR Kredi Kartı</option><option value="iyzico">İyzico Kredi Kartı</option><option value="manual">Havale/EFT</option></select></label>
    </div>
    <button class="cp-btn">Ödemeye Geç</button>
    <p class="cp-muted">Kart ödemelerinde ilgili sağlayıcı komisyonu <strong>Kart İşlem Komisyonu</strong> olarak ayrı kalem halinde hesaplanır.</p>
  </form>
</div>
<div class="customer-panel-card">
  <h3>Son Bakiye Yükleme Talepleri</h3>
  <table class="cp-table"><thead><tr><th>Referans</th><th>Yöntem</th><th>Tutar</th><th>Komisyon</th><th>Toplam</th><th>Durum</th></tr></thead><tbody><?php foreach($topups as $t): ?><tr><td><?= e($t['reference']) ?></td><td><?= e($t['gateway']) ?></td><td><?= number_format((float)$t['amount'],2,',','.') ?> <?= e($t['currency']) ?></td><td><?= number_format((float)$t['fee_amount'],2,',','.') ?></td><td><?= number_format((float)$t['total_amount'],2,',','.') ?></td><td><?= e($t['status']) ?></td></tr><?php endforeach; if(!$topups): ?><tr><td colspan="6">Henüz bakiye yükleme talebi yok.</td></tr><?php endif; ?></tbody></table>
</div>
<div class="customer-panel-card">
  <h3>Kredi Hareketleri</h3>
  <table class="cp-table"><thead><tr><th>Tarih</th><th>Tür</th><th>Açıklama</th><th>Tutar</th><th>Bakiye</th></tr></thead><tbody><?php foreach($transactions as $t): ?><tr><td><?= e($t['created_at']) ?></td><td><?= e($t['type']) ?></td><td><?= e($t['description']) ?></td><td><?= number_format((float)$t['amount'],2,',','.') ?> ₺</td><td><?= number_format((float)$t['balance_after'],2,',','.') ?> ₺</td></tr><?php endforeach; if(!$transactions): ?><tr><td colspan="5">Kredi hareketi bulunmuyor.</td></tr><?php endif; ?></tbody></table>
</div>
<div class="customer-panel-card">
  <h3>Kredi ile Yapılabilecekler</h3>
  <div class="cp-shortcut-grid">
    <a href="<?= url('client/invoices') ?>">Fatura Öde</a>
    <a href="<?= url('client/services') ?>">Hizmet Yenile</a>
    <a href="<?= url('hosting') ?>">Yeni Hosting Al</a>
    <a href="<?= url('client/support') ?>">Muhasebe Destek</a>
  </div>
</div>
