<?php
$c = current_customer();
$invoiceId = (int)($_GET['id'] ?? 0);
$invoice = null;
$items = [];
$payments = [];
$error = '';

try {
    if (!$c || $invoiceId <= 0) {
        $error = 'Fatura bulunamadı.';
    } else {
        $q = db()->prepare('SELECT * FROM invoices WHERE id=? AND customer_id=? LIMIT 1');
        $q->execute([$invoiceId, (int)$c['id']]);
        $invoice = $q->fetch();
        if (!$invoice) {
            $error = 'Fatura bulunamadı veya bu faturayı görüntüleme yetkiniz yok.';
        } else {
            try {
                $s = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id=? ORDER BY id ASC');
                $s->execute([$invoiceId]);
                $items = $s->fetchAll() ?: [];
            } catch (Throwable $e) { $items = []; }
            try {
                $p = db()->prepare('SELECT * FROM payments WHERE invoice_id=? AND customer_id=? ORDER BY id DESC');
                $p->execute([$invoiceId, (int)$c['id']]);
                $payments = $p->fetchAll() ?: [];
            } catch (Throwable $e) { $payments = []; }
        }
    }
} catch (Throwable $e) {
    $error = 'Fatura detayı okunurken bir hata oluştu.';
}

$statusMap = [
    'paid' => ['Ödendi','green'],
    'unpaid' => ['Ödenmedi','orange'],
    'draft' => ['Taslak','blue'],
    'cancelled' => ['İptal','red'],
    'refunded' => ['İade Edildi','blue'],
    'partial' => ['Kısmi Ödendi','orange'],
    'partially_paid' => ['Kısmi Ödendi','orange'],
    'pending' => ['Beklemede','orange'],
];
$statusKey = strtolower((string)($invoice['status'] ?? ''));
$status = $statusMap[$statusKey] ?? [($invoice['status'] ?? 'Bilinmiyor'), 'orange'];
$currency = $invoice['currency'] ?? 'TRY';
function ao_inv_money($amount, $currency='TRY') { return number_format((float)$amount, 2, ',', '.') . ' ' . e($currency === 'TRY' ? '₺' : $currency); }
?>

<div class="customer-panel-card premium-detail-hero ao-invoice-hero">
  <div>
    <span class="u2-kicker">Fatura Detayı</span>
    <h2><?= $invoice ? e($invoice['invoice_number'] ?: ('#INV-'.$invoice['id'])) : 'Fatura Detayı' ?></h2>
    <p>Fatura kalemleri, ödeme durumu ve ödeme işlemlerinizi tek ekrandan takip edin.</p>
  </div>
  <div class="button-row">
    <a class="u2-btn soft" href="<?= url('client/invoices') ?>">← Faturalara Dön</a>
    <?php if ($invoice): ?><button class="u2-btn soft" onclick="window.print()">Yazdır / PDF</button><?php endif; ?>
  </div>
</div>

<?php if ($error): ?>
  <div class="customer-panel-card"><h3>Fatura bulunamadı</h3><p><?= e($error) ?></p><a class="u2-btn" href="<?= url('client/invoices') ?>">Faturalara Dön</a></div>
<?php else: ?>

<div class="payment-panel invoice-summary-grid">
  <div class="customer-panel-card"><h3>Durum</h3><span class="u2-pill <?= e($status[1]) ?>"><?= e($status[0]) ?></span><p>Fatura No: <strong><?= e($invoice['invoice_number'] ?: ('#INV-'.$invoice['id'])) ?></strong></p></div>
  <div class="customer-panel-card"><h3>Toplam</h3><div class="invoice-total-amount"><?= ao_inv_money($invoice['total'] ?? 0, $currency) ?></div><p>Ara Toplam: <?= ao_inv_money($invoice['subtotal'] ?? 0, $currency) ?></p></div>
  <div class="customer-panel-card"><h3>Son Ödeme</h3><div class="invoice-total-amount small"><?= e($invoice['due_date'] ?: '-') ?></div><p>Oluşturma: <?= e($invoice['created_at'] ?? '-') ?></p></div>
</div>

<div class="customer-panel-card">
  <div class="u2-section-title"><div><h3>Fatura Kalemleri</h3><p>Hizmet, domain, SSL ve ek hizmet satırları.</p></div></div>
  <div class="table-wrap">
    <table class="table-like">
      <tr><th>Açıklama</th><th>Adet</th><th>Birim Tutar</th><th>Toplam</th></tr>
      <?php foreach ($items as $it): $qty=max(1,(int)($it['quantity'] ?? 1)); $amount=(float)($it['amount'] ?? 0); ?>
        <tr><td><?= e($it['description'] ?? '-') ?></td><td><?= $qty ?></td><td><?= ao_inv_money($amount, $currency) ?></td><td><strong><?= ao_inv_money($amount*$qty, $currency) ?></strong></td></tr>
      <?php endforeach; if (!$items): ?>
        <tr><td colspan="4">Bu faturaya ait kalem bulunamadı.</td></tr>
      <?php endif; ?>
      <tr><td colspan="3" style="text-align:right">Ara Toplam</td><td><?= ao_inv_money($invoice['subtotal'] ?? 0, $currency) ?></td></tr>
      <tr><td colspan="3" style="text-align:right">Vergi</td><td><?= ao_inv_money($invoice['tax'] ?? 0, $currency) ?></td></tr>
      <tr><td colspan="3" style="text-align:right"><strong>Genel Toplam</strong></td><td><strong><?= ao_inv_money($invoice['total'] ?? 0, $currency) ?></strong></td></tr>
    </table>
  </div>
</div>

<div class="payment-panel">
  <div class="customer-panel-card">
    <h3>Ödeme İşlemleri</h3>
    <?php if (!in_array($statusKey, ['paid','cancelled','refunded'], true)): ?>
      <p>Bu fatura için ödeme bekleniyor. Ödeme ekranı aktif edildiğinde güvenli ödeme adımına yönlendirilirsiniz.</p>
      <div class="button-row"><a class="u2-btn" href="<?= url('client/credit') ?>">Bakiye / Ödeme Yap</a><a class="u2-btn soft" href="<?= url('client/support') ?>">Destek Talebi Aç</a></div>
    <?php else: ?>
      <p>Bu faturanın mevcut durumu: <strong><?= e($status[0]) ?></strong></p>
      <div class="button-row"><a class="u2-btn soft" href="<?= url('client/support') ?>">Fatura İçin Destek Al</a></div>
    <?php endif; ?>
  </div>
  <div class="customer-panel-card">
    <h3>Ödeme Geçmişi</h3>
    <?php if ($payments): ?>
      <div class="e-renewal-list">
        <?php foreach($payments as $p): ?>
          <div><b><?= e($p['gateway'] ?? 'manual') ?> / <?= e($p['status'] ?? '') ?></b><span class="u2-pill green"><?= ao_inv_money($p['amount'] ?? 0, $p['currency'] ?? $currency) ?></span></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>Bu faturaya bağlı ödeme kaydı bulunmuyor.</p>
    <?php endif; ?>
  </div>
</div>

<?php endif; ?>
