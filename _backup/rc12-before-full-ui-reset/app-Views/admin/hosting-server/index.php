<?php
$servers = [];
try { $servers = db()->query("SELECT * FROM servers ORDER BY id DESC")->fetchAll(); } catch(Throwable $e) {}
?>
<div class="ao-page-head">
    <div><h2>Hosting & Server Center</h2><p>WHM, cPanel, DirectAdmin, Plesk ve VPS sunucu yönetimi.</p></div>
    <a class="ao-btn" href="<?= url('admin/hosting-server/servers') ?>">+ Sunucu Ekle</a>
</div>
<div class="ao-stats-grid">
    <div class="ao-stat"><span>Toplam Sunucu</span><strong><?= count($servers) ?></strong></div>
    <div class="ao-stat"><span>Aktif Hosting</span><strong><?= table_count('hosting_accounts') ?></strong></div>
    <div class="ao-stat"><span>WHM Entegrasyonu</span><strong><?= count(array_filter($servers, fn($s)=>($s['type']??'')==='whm')) ?></strong></div>
</div>
<div class="ao-grid two">
    <div class="ao-card">
        <h3>🖥 Sunucu Türleri</h3>
        <ul>
            <li><a href="<?= url('admin/hosting-server/whm') ?>">WHM / cPanel</a></li>
            <li><a href="<?= url('admin/hosting-server/add?type=directadmin') ?>">DirectAdmin</a></li>
            <li><a href="<?= url('admin/hosting-server/add?type=plesk') ?>">Plesk</a></li>
            <li><a href="<?= url('admin/hosting-server/vps') ?>">VPS Yönetimi</a></li>
        </ul>
    </div>
    <div class="ao-card">
        <h3>📊 Sunucu Durumu</h3>
        <?php if(!$servers): ?>
        <p>Henüz sunucu eklenmedi. <a href="<?= url('admin/hosting-server/servers') ?>">Sunucu ekle →</a></p>
        <?php else: ?>
        <table class="ao-table"><thead><tr><th>Sunucu</th><th>Tip</th><th>Durum</th></tr></thead><tbody>
        <?php foreach($servers as $srv): ?>
        <tr><td><?= e($srv['name']??$srv['hostname']) ?></td><td><?= e($srv['type']??'-') ?></td><td><span class="ao-badge active">Aktif</span></td></tr>
        <?php endforeach; ?></tbody></table>
        <?php endif; ?>
    </div>
</div>
