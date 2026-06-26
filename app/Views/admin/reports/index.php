<?php
// Özet veriler
$total_revenue = 0; $total_customers = 0; $total_services = 0; $total_domains = 0;
try {
    $total_revenue = db()->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='paid'")->fetchColumn();
    $total_customers = table_count('customers');
    $total_services = table_count('services');
    $total_domains = table_count('domains');
} catch(Throwable $e) {}
?>
<div class="ao-page-head">
    <div><h2>Raporlar</h2><p>Satış, müşteri, ürün, domain, destek ve gelir analizleri. Veriyi görün, kararı doğru verin.</p></div>
    <a class="ao-btn soft" href="<?= url('admin/reports/export') ?>">📥 Dışa Aktar</a>
</div>
<div class="ao-stats-grid">
    <div class="ao-stat"><span>Toplam Gelir</span><strong><?= number_format((float)$total_revenue,0,',','.') ?> ₺</strong></div>
    <div class="ao-stat"><span>Toplam Müşteri</span><strong><?= $total_customers ?></strong></div>
    <div class="ao-stat"><span>Aktif Hizmet</span><strong><?= $total_services ?></strong></div>
    <div class="ao-stat"><span>Toplam Domain</span><strong><?= $total_domains ?></strong></div>
</div>

<div class="ao-card ao-tab-shell" data-ao-tabs>
    <div class="ao-real-tabs" role="tablist">
        <button class="active" data-tab="gelir">💰 Gelir</button>
        <button data-tab="musteri">👥 Müşteri</button>
        <button data-tab="urun">📦 Ürün</button>
        <button data-tab="destek">🎫 Destek</button>
        <button data-tab="domain">🌐 Domain</button>
    </div>

    <section id="tab-gelir" class="ao-tab-panel active">
        <h3>Gelir Analizi</h3>
        <div class="ao-grid two" style="margin-bottom:16px">
            <div style="background:#f8fafc;border:1px solid var(--ao-border);border-radius:14px;padding:18px">
                <b>Bugün</b>
                <?php try { $v=db()->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='paid' AND DATE(created_at)=CURDATE()")->fetchColumn(); } catch(Throwable $e){$v=0;} ?>
                <div style="font-size:28px;font-weight:900;margin:8px 0"><?= number_format((float)$v,2,',','.') ?> ₺</div>
            </div>
            <div style="background:#f8fafc;border:1px solid var(--ao-border);border-radius:14px;padding:18px">
                <b>Bu Ay</b>
                <?php try { $v2=db()->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='paid' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn(); } catch(Throwable $e){$v2=0;} ?>
                <div style="font-size:28px;font-weight:900;margin:8px 0"><?= number_format((float)$v2,2,',','.') ?> ₺</div>
            </div>
        </div>
        <table class="ao-table">
            <thead><tr><th>Fatura No</th><th>Müşteri</th><th>Tutar</th><th>Tarih</th></tr></thead>
            <tbody>
            <?php try { $paid=db()->query("SELECT i.*,c.first_name,c.last_name FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id WHERE i.status='paid' ORDER BY i.id DESC LIMIT 20")->fetchAll();
            foreach($paid as $inv): ?>
            <tr><td><?= e($inv['invoice_number']) ?></td><td><?= e(($inv['first_name']??'').' '.($inv['last_name']??'')) ?></td><td><?= number_format((float)$inv['total'],2,',','.') ?> ₺</td><td><?= e(substr($inv['created_at'],0,10)) ?></td></tr>
            <?php endforeach; if(!$paid??true): ?><tr><td colspan="4">Ödenen fatura yok.</td></tr><?php endif; } catch(Throwable $e){ echo '<tr><td colspan="4">Veri yüklenemedi.</td></tr>'; } ?>
            </tbody>
        </table>
    </section>

    <section id="tab-musteri" class="ao-tab-panel">
        <h3>Müşteri Raporu</h3>
        <div class="ao-grid" style="margin-bottom:16px">
            <?php
            try {
                $active_c = db()->query("SELECT COUNT(*) FROM customers WHERE status='active'")->fetchColumn();
                $inactive_c = db()->query("SELECT COUNT(*) FROM customers WHERE status='inactive'")->fetchColumn();
                $new_c = db()->query("SELECT COUNT(*) FROM customers WHERE MONTH(created_at)=MONTH(NOW())")->fetchColumn();
            } catch(Throwable $e) { $active_c=$inactive_c=$new_c=0; }
            ?>
            <div style="background:#f8fafc;border:1px solid var(--ao-border);border-radius:14px;padding:18px;text-align:center"><b>Aktif</b><div style="font-size:30px;font-weight:900;color:#166534"><?= $active_c ?></div></div>
            <div style="background:#f8fafc;border:1px solid var(--ao-border);border-radius:14px;padding:18px;text-align:center"><b>Pasif</b><div style="font-size:30px;font-weight:900;color:#92400e"><?= $inactive_c ?></div></div>
            <div style="background:#f8fafc;border:1px solid var(--ao-border);border-radius:14px;padding:18px;text-align:center"><b>Bu Ay Yeni</b><div style="font-size:30px;font-weight:900;color:#1d4ed8"><?= $new_c ?></div></div>
        </div>
    </section>

    <section id="tab-urun" class="ao-tab-panel">
        <h3>Ürün Satış Raporu</h3>
        <table class="ao-table">
            <thead><tr><th>Ürün</th><th>Grup</th><th>Aktif Hizmet</th><th>Aylık Fiyat</th></tr></thead>
            <tbody>
            <?php try { $prods=db()->query("SELECT p.*,pg.name gname,pp.monthly,COUNT(s.id) scount FROM products p LEFT JOIN product_groups pg ON pg.id=p.group_id LEFT JOIN product_pricing pp ON pp.product_id=p.id LEFT JOIN services s ON s.product_id=p.id GROUP BY p.id ORDER BY scount DESC")->fetchAll();
            foreach($prods as $pr): ?>
            <tr><td><?= e($pr['name']) ?></td><td><?= e($pr['gname']??'-') ?></td><td><strong><?= (int)$pr['scount'] ?></strong></td><td><?= $pr['monthly']>0?number_format((float)$pr['monthly'],2,',','.').' ₺':'-' ?></td></tr>
            <?php endforeach; } catch(Throwable $e) { echo '<tr><td colspan="4">Veri yüklenemedi.</td></tr>'; } ?>
            </tbody>
        </table>
    </section>

    <section id="tab-destek" class="ao-tab-panel">
        <h3>Destek Raporu</h3>
        <?php try {
            $t_open=db()->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetchColumn();
            $t_closed=db()->query("SELECT COUNT(*) FROM tickets WHERE status='closed'")->fetchColumn();
            $t_total=db()->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
        } catch(Throwable $e){$t_open=$t_closed=$t_total=0;}
        ?>
        <div class="ao-grid two" style="margin-bottom:16px">
            <div style="background:#f8fafc;border:1px solid var(--ao-border);border-radius:14px;padding:18px;text-align:center"><b>Açık Ticket</b><div style="font-size:30px;font-weight:900;color:#dc2626"><?= $t_open ?></div></div>
            <div style="background:#f8fafc;border:1px solid var(--ao-border);border-radius:14px;padding:18px;text-align:center"><b>Kapalı</b><div style="font-size:30px;font-weight:900;color:#166534"><?= $t_closed ?></div></div>
        </div>
        <p>Toplam <?= $t_total ?> ticket kayıtlı.</p>
    </section>

    <section id="tab-domain" class="ao-tab-panel">
        <h3>Domain Raporu</h3>
        <?php try { $doms=db()->query("SELECT d.*,c.first_name,c.last_name FROM domains d LEFT JOIN customers c ON c.id=d.customer_id ORDER BY d.expiry_date LIMIT 50")->fetchAll(); } catch(Throwable $e){$doms=[];} ?>
        <table class="ao-table">
            <thead><tr><th>Domain</th><th>Müşteri</th><th>Bitiş</th><th>Durum</th></tr></thead>
            <tbody>
            <?php foreach($doms as $d): ?>
            <tr><td><?= e($d['domain_name']??$d['domain']??'') ?></td><td><?= e(($d['first_name']??'').' '.($d['last_name']??'')) ?></td><td><?= e($d['expiry_date']??'-') ?></td><td><span class="ao-badge <?= e($d['status']) ?>"><?= e($d['status']) ?></span></td></tr>
            <?php endforeach; if(!$doms): ?><tr><td colspan="4">Domain bulunamadı.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
