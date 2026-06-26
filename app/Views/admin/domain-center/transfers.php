<?php $transfers=[];
try { $transfers=db()->query("SELECT * FROM domains WHERE status='pending_transfer' ORDER BY id DESC LIMIT 100")->fetchAll(); } catch(Throwable $e) {}
?>
<div class="ao-page-head"><div><h2>Domain Transferleri</h2><p>Gelen ve giden transfer talepleri, EPP kodu doğrulama.</p></div></div>
<div class="ao-card">
    <h3>Bekleyen Transferler</h3>
    <table class="ao-table">
        <thead><tr><th>Domain</th><th>Yön</th><th>EPP Kodu</th><th>Talep Tarihi</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach($transfers as $d): ?>
        <tr><td><?= e($d['domain']) ?></td><td>Gelen</td><td><code><?= e($d['epp_code']??'N/A') ?></code></td><td><?= e(substr($d['created_at']??'',0,10)) ?></td><td><span class="ao-badge pending">Bekliyor</span></td><td><a class="ao-mini-btn" href="<?= url('admin/domain-center/transfers/action?id='.(int)$d['id']) ?>">İşle</a></td></tr>
        <?php endforeach; if(!$transfers): ?><tr><td colspan="6">Bekleyen transfer yok.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
