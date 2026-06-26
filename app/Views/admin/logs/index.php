<?php
$logs = [];
try { $logs = db()->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 300")->fetchAll(); } catch(Throwable $e) {}
?>
<div class="ao-page-head"><div><h2>İşlem Günlükleri</h2><p>Tüm admin ve müşteri aktivitelerinin kayıtları.</p></div>
<div class="ao-actions no-margin"><a class="ao-btn soft" href="<?= url('admin/logs/export') ?>">Dışa Aktar</a><a class="ao-btn soft" href="<?= url('admin/logs/clear') ?>" onclick="return confirm('Tüm log kayıtları silinecek. Emin misiniz?')">Temizle</a></div></div>
<div class="ao-card">
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <h3 style="margin:0;margin-right:auto">Aktivite Logları</h3>
        <a href="?type=admin" class="ao-mini-btn">Admin</a>
        <a href="?type=customer" class="ao-mini-btn">Müşteri</a>
        <a href="?type=system" class="ao-mini-btn">Sistem</a>
        <a href="?type=api" class="ao-mini-btn">API</a>
    </div>
    <table class="ao-table">
        <thead><tr><th>Tarih</th><th>Kullanıcı</th><th>Tür</th><th>Olay</th><th>IP</th><th>Detay</th></tr></thead>
        <tbody>
        <?php foreach($logs as $log): ?>
        <tr>
            <td><small><?= e(substr($log['created_at']??'',0,16)) ?></small></td>
            <td><?= e($log['user']??$log['user_email']??'Sistem') ?></td>
            <td><span class="ao-badge <?= e($log['type']??'system') ?>"><?= e($log['type']??'system') ?></span></td>
            <td><?= e($log['action']??$log['event']??'-') ?></td>
            <td><small><?= e($log['ip_address']??'-') ?></small></td>
            <td><small><?= e(substr($log['description']??'',0,60)) ?></small></td>
        </tr>
        <?php endforeach; if(!$logs): ?><tr><td colspan="6">Henüz log kaydı bulunmuyor.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
