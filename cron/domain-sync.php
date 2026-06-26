<?php
require __DIR__.'/../app/bootstrap.php';
// Ahost One v24.11.0 registrar domain sync cron.
// cPanel cron örneği: php /home/USER/public_html/cron/domain-sync.php
try {
    $rows = db()->query("SELECT * FROM customer_domains WHERE status IN ('active','pending_transfer','transfer_pending','pending') ORDER BY id ASC")->fetchAll();
    foreach ($rows as $d) {
        $id=(int)$d['id'];
        db()->prepare("UPDATE customer_domains SET last_synced_at=NOW() WHERE id=?")->execute([$id]);
        db()->prepare("INSERT INTO domain_sync_logs(domain_id,registrar,status,message) VALUES(?,?,?,?)")->execute([$id,$d['registrar']??'DomainNameAPI','ok','Cron kontrol edildi. Registrar API adaptörü aktifse bitiş/kilit/NS verileri burada güncellenecek.']);
    }
    echo 'Domain sync completed: '.count($rows).PHP_EOL;
} catch (Throwable $e) {
    try { db()->prepare("INSERT INTO domain_sync_logs(status,message) VALUES('error',?)")->execute([$e->getMessage()]); } catch(Throwable $ignored){}
    fwrite(STDERR,$e->getMessage().PHP_EOL); exit(1);
}
