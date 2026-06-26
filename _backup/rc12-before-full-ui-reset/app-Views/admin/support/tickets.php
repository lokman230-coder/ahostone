<?php
$tickets = [];
$stats = ['open'=>0,'answered'=>0,'customer_reply'=>0,'closed'=>0,'on_hold'=>0];
try {
    $tickets = db()->query("SELECT t.*, c.first_name, c.last_name FROM tickets t LEFT JOIN customers c ON c.id=t.customer_id ORDER BY t.created_at DESC, t.id DESC LIMIT 200")->fetchAll();
    foreach($tickets as $tkt) { $s=$tkt['status']; if(isset($stats[$s])) $stats[$s]++; }
} catch(Throwable $e) {}
?>
<div class="ao-page-head">
    <div><h2>Destek Ticketları</h2><p>Müşteri talepleri, departman yönlendirme, SLA takibi.</p></div>
    <a class="ao-btn" href="<?= url('admin/support/new') ?>">+ Yeni Ticket</a>
</div>
<div class="ao-stats-grid">
    <div class="ao-stat"><span>Açık</span><strong><?= $stats['open'] ?></strong></div>
    <div class="ao-stat"><span>Yanıtlandı</span><strong><?= $stats['answered'] ?></strong></div>
    <div class="ao-stat"><span>Müşteri Yanıtı Bekleniyor</span><strong><?= $stats['customer_reply'] ?></strong></div>
    <div class="ao-stat"><span>Kapalı</span><strong><?= $stats['closed'] ?></strong></div>
</div>
<div class="ao-card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <h3 style="margin:0">Ticket Listesi</h3>
        <div style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;">
            <a href="?status=" class="ao-mini-btn">Tümü</a>
            <a href="?status=open" class="ao-mini-btn">Açık</a>
            <a href="?status=answered" class="ao-mini-btn">Yanıtlandı</a>
            <a href="?status=customer_reply" class="ao-mini-btn">Müşteri Yanıtı</a>
            <a href="?status=on_hold" class="ao-mini-btn">Beklemede</a>
            <a href="?status=closed" class="ao-mini-btn">Kapalı</a>
        </div>
    </div>
    <table class="ao-table">
        <thead><tr><th>ID</th><th>Konu</th><th>Müşteri</th><th>Departman</th><th>Öncelik</th><th>Durum</th><th>Son Güncelleme</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php
        $filtered = $tickets;
        if(!empty($_GET['status'])) $filtered = array_filter($tickets, fn($t)=>$t['status']===$_GET['status']);
        foreach($filtered as $tkt): ?>
        <tr>
            <td>#<?= (int)$tkt['id'] ?></td>
            <td><?= e($tkt['subject']??'(Konu yok)') ?></td>
            <td><?= e(($tkt['first_name']??'').' '.($tkt['last_name']??'')) ?></td>
            <td><?= e($tkt['department']??'Genel') ?></td>
            <td><span class="ao-badge <?= e($tkt['priority']??'medium') ?>"><?= e($tkt['priority']??'medium') ?></span></td>
            <td><span class="ao-badge <?= e($tkt['status']) ?>"><?= e($tkt['status']) ?></span></td>
            <td><small><?= e(substr($tkt['created_at']??'',0,16)) ?></small></td>
            <td><a class="ao-mini-btn" href="<?= url('admin/support/view?id='.(int)$tkt['id']) ?>">Aç</a></td>
        </tr>
        <?php endforeach; if(!$filtered): ?><tr><td colspan="8">Ticket bulunamadı.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<div class="ao-card">
  <h3>Ticket Yanıtla / Durum Güncelle</h3>
  <form class="ao-form" method="post" action="<?= url('admin/support/ticket-reply') ?>"><?= csrf_field() ?>
    <div class="ao-form-grid">
      <label>Ticket ID<input type="number" name="ticket_id" required placeholder="1"></label>
      <label>Durum<select name="status"><option value="answered">Yanıtlandı</option><option value="open">Açık</option><option value="on_hold">Beklemede</option><option value="closed">Kapalı</option></select></label>
      <label class="full">Yanıt<textarea name="message" rows="4" required placeholder="Müşteriye gönderilecek yanıt..."></textarea></label>
    </div>
    <button class="ao-btn">Yanıtı Kaydet</button>
  </form>
</div>

<div class="ao-grid two">
    <div class="ao-card"><h3>🕐 SLA Durumu</h3><p>İlk yanıt süresi, çözüm süresi ve SLA ihlalleri.</p><ul><li>Acil: 1 saat</li><li>Yüksek: 4 saat</li><li>Normal: 24 saat</li><li>Düşük: 48 saat</li></ul></div>
    <div class="ao-card"><h3>🤖 AI Destek Asistanı</h3><p>Gelen ticketları otomatik analiz eder, benzer çözümleri önerir ve gerektiğinde otomatik yanıt taslağı oluşturur.</p><a class="ao-btn soft" href="<?= url('admin/ai-center') ?>">AI Center</a></div>
</div>
