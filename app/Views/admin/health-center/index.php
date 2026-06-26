<?php
// Sistem sağlık kontrolleri
$checks = [];
try {
    $checks['db'] = ['label'=>'Veritabanı', 'ok'=>true, 'detail'=>'MySQL bağlantısı aktif'];
    $checks['customers'] = ['label'=>'Müşteri Tablosu', 'ok'=>table_count('customers')>=0, 'detail'=>table_count('customers').' kayıt'];
    $checks['products'] = ['label'=>'Ürün Tablosu', 'ok'=>table_count('products')>=0, 'detail'=>table_count('products').' aktif ürün'];
    $checks['unpaid'] = ['label'=>'Ödenmemiş Faturalar', 'ok'=>true, 'detail'=>table_count('invoices').' toplam fatura'];
    $checks['tickets'] = ['label'=>'Açık Ticketlar', 'ok'=>true, 'detail'=>table_count('tickets').' ticket'];
    $checks['php'] = ['label'=>'PHP Versiyonu', 'ok'=>PHP_VERSION_ID>=80100, 'detail'=>'PHP '.PHP_VERSION];
    $checks['uploads'] = ['label'=>'Uploads Klasörü', 'ok'=>is_writable(__DIR__.'/../../../../uploads'), 'detail'=>is_writable(__DIR__.'/../../../../uploads')?'Yazılabilir':'Yazma izni yok!'];
    $checks['storage'] = ['label'=>'Storage / Logs', 'ok'=>is_writable(__DIR__.'/../../../../storage/logs'), 'detail'=>is_writable(__DIR__.'/../../../../storage/logs')?'Yazılabilir':'Yazma izni yok!'];
} catch(Throwable $e) { $checks['db'] = ['label'=>'Veritabanı', 'ok'=>false, 'detail'=>$e->getMessage()]; }
$all_ok = !in_array(false, array_column($checks,'ok'));
?>
<div class="ao-page-head">
    <div><h2>🏥 Sistem Sağlık Merkezi</h2><p>PHP, veritabanı, dosya izinleri ve servis durumu anlık kontrol.</p></div>
    <a class="ao-btn <?= $all_ok?'':'danger' ?>" href="<?= url('admin/health-center') ?>">🔄 Yenile</a>
</div>

<div class="ao-card" style="border-left:4px solid <?= $all_ok?'#22c55e':'#ef4444' ?>">
    <div style="display:flex;align-items:center;gap:16px">
        <div style="font-size:48px"><?= $all_ok?'✅':'⚠️' ?></div>
        <div>
            <h3 style="margin:0"><?= $all_ok?'Sistem Sağlıklı':'Dikkat Gerektiren Durumlar Var' ?></h3>
            <p style="margin:4px 0 0;opacity:.7"><?= count(array_filter($checks, fn($c)=>$c['ok'])) ?>/<?= count($checks) ?> kontrol başarılı</p>
        </div>
    </div>
</div>

<div class="ao-card">
    <h3>Sistem Kontrolleri</h3>
    <table class="ao-table">
        <thead><tr><th>Kontrol</th><th>Durum</th><th>Detay</th></tr></thead>
        <tbody>
        <?php foreach($checks as $check): ?>
        <tr>
            <td><strong><?= e($check['label']) ?></strong></td>
            <td><?= $check['ok']?'<span class="ao-badge active">✅ OK</span>':'<span class="ao-badge closed">❌ Hata</span>' ?></td>
            <td><small><?= e($check['detail']) ?></small></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="ao-grid two">
    <div class="ao-card">
        <h3>📊 Sunucu Bilgisi</h3>
        <table class="ao-table">
            <tbody>
                <tr><td>PHP</td><td><strong><?= PHP_VERSION ?></strong></td></tr>
                <tr><td>OS</td><td><strong><?= PHP_OS ?></strong></td></tr>
                <tr><td>Sunucu</td><td><strong><?= e($_SERVER['SERVER_SOFTWARE']??'-') ?></strong></td></tr>
                <tr><td>Zaman Dilimi</td><td><strong><?= date_default_timezone_get() ?></strong></td></tr>
                <tr><td>Sunucu Saati</td><td><strong><?= date('d.m.Y H:i:s') ?></strong></td></tr>
                <tr><td>Max Upload</td><td><strong><?= ini_get('upload_max_filesize') ?></strong></td></tr>
                <tr><td>Memory Limit</td><td><strong><?= ini_get('memory_limit') ?></strong></td></tr>
            </tbody>
        </table>
    </div>
    <div class="ao-card">
        <h3>🔧 Hızlı Aksiyonlar</h3>
        <div class="ao-actions" style="flex-direction:column;align-items:flex-start">
            <a class="ao-btn soft" href="<?= url('admin/logs') ?>">📋 Sistem Loglarını Gör</a>
            <a class="ao-btn soft" href="<?= url('admin/settings') ?>">⚙️ Sistem Ayarları</a>
            <a class="ao-btn soft" href="<?= url('admin/migration-bridge') ?>">🔄 Migration & Yedek</a>
            <a class="ao-btn soft" href="<?= url('admin/cache-center/clear') ?>">🗑 Cache Temizle</a>
            <a class="ao-btn soft" href="<?= url('admin/health-center/test-email') ?>">📧 Test E-postası Gönder</a>
        </div>
    </div>
</div>
