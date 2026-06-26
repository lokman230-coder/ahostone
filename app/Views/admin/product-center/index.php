<?php
$groups = [];
$products = [];
$total_revenue = 0;
try {
    $groups = db()->query("SELECT pg.*, COUNT(p.id) product_count FROM product_groups pg LEFT JOIN products p ON p.group_id=pg.id GROUP BY pg.id ORDER BY pg.sort_order")->fetchAll();
    $products = db()->query("SELECT p.*, pg.name group_name FROM products p LEFT JOIN product_groups pg ON pg.id=p.group_id ORDER BY p.id DESC")->fetchAll();
} catch(Throwable $e) {}
$active_group = $_GET['group'] ?? '';
?>
<div class="ao-page-head">
    <div><h2>Ürün Merkezi Pro</h2><p>Ürün grupları, fiyatlandırma, configurable options, bundle ürünler ve cross-sell yönetimi.</p></div>
    <div class="ao-actions no-margin">
        <a class="ao-btn" href="<?= url('admin/product-center/products?action=add') ?>">+ Yeni Ürün</a>
        <a class="ao-btn soft" href="<?= url('admin/product-center/groups') ?>">Gruplar</a>
    </div>
</div>

<div class="ao-stats-grid">
    <div class="ao-stat"><span>Toplam Ürün</span><strong><?= count($products) ?></strong></div>
    <div class="ao-stat"><span>Ürün Grubu</span><strong><?= count($groups) ?></strong></div>
    <div class="ao-stat"><span>Aktif Hizmet</span><strong><?= table_count('services') ?></strong></div>
    <div class="ao-stat"><span>Bu Ay Satış</span><strong><?= table_count('orders') ?> Sipariş</strong></div>
</div>

<!-- Grup filtre butonları -->
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px">
    <a href="<?= url('admin/product-center') ?>" class="ao-mini-btn <?= !$active_group?'active':'' ?>" style="<?= !$active_group?'background:#2563eb;color:#fff':'' ?>">Tümü</a>
    <?php foreach($groups as $g): ?>
    <a href="?group=<?= (int)$g['id'] ?>" class="ao-mini-btn" style="<?= $active_group==$g['id']?'background:#2563eb;color:#fff':'' ?>">
        <?= e($g['name']) ?> <span style="opacity:.6">(<?= (int)$g['product_count'] ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<div class="ao-card">
    <h3 style="margin-top:0">Ürün Listesi</h3>
    <table class="ao-table">
        <thead><tr><th>Ürün</th><th>Grup</th><th>Tip</th><th>Aylık</th><th>Yıllık</th><th>WHM Paketi</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php
        $filtered = $products;
        if($active_group) $filtered = array_filter($products, fn($p)=>$p['group_id']==$active_group);
        foreach($filtered as $p): ?>
        <tr>
            <td>
                <strong><?= e($p['name']) ?></strong>
                <?php if($p['is_custom_build_enabled']): ?><br><small style="color:#6c63ff">⚡ Paket Oluşturucu Aktif</small><?php endif; ?>
            </td>
            <td><span class="ao-badge"><?= e($p['group_name']??'-') ?></span></td>
            <td><?= e($p['type']) ?></td>
            <td><?php $dp=function_exists('ao_v2331_product_display_price')?ao_v2331_product_display_price((int)$p['id']):['try'=>(float)($p['price']??0),'usd'=>0]; ?><?= ($dp['try']??0)>0?number_format((float)$dp['try'],2,',','.').' ₺':'-' ?><br><small><?= ($dp['usd']??0)>0?number_format((float)$dp['usd'],2,'.','').' USD':'' ?></small></td>
            <td><?php try{ $yp=db()->prepare("SELECT price_try,price_usd FROM product_pricing WHERE product_id=? AND cycle='annually' LIMIT 1"); $yp->execute([(int)$p['id']]); $yr=$yp->fetch()?:[]; }catch(Throwable $e){ $yr=[]; } ?><?= ((float)($yr['price_try']??0))>0?number_format((float)$yr['price_try'],2,',','.').' ₺':'-' ?><br><small><?= ((float)($yr['price_usd']??0))>0?number_format((float)$yr['price_usd'],2,'.','').' USD':'' ?></small></td>
            <td><code style="background:#eef2ff;color:#1e40af;border-radius:6px;padding:2px 7px"><?= e($p['whm_package']??'—') ?></code></td>
            <td><span class="ao-badge <?= $p['is_active']?'active':'inactive' ?>"><?= $p['is_active']?'Aktif':'Pasif' ?></span></td>
            <td>
                <a class="ao-mini-btn" href="<?= url('admin/product-center/products?edit='.(int)$p['id']) ?>">Düzenle</a>
                <a class="ao-mini-btn" href="<?= url('admin/product-center/config-options?product='.(int)$p['id']) ?>">Seçenekler</a>
            </td>
        </tr>
        <?php endforeach; if(!$filtered): ?><tr><td colspan="8">Bu grupta ürün yok.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<div class="ao-grid two">
    <div class="ao-card" style="border-left:4px solid #6c63ff">
        <h3>⚡ Paket Oluşturucu (Custom Build)</h3>
        <p>Müşteri sipariş sırasında disk, RAM, CPU, e-posta, MySQL gibi kaynakları kendisi seçer. Her seçim fiyata eklenir.</p>
        <ul>
            <li>Dropdown / radio / checkbox seçenekler</li>
            <li>Kaynak bazlı fiyat artışı</li>
            <li>WHM paketi ile otomatik eşleşme</li>
        </ul>
        <a class="ao-btn soft" href="<?= url('admin/product-center/config-options') ?>">Seçenekleri Yönet</a>
    </div>
    <div class="ao-card" style="border-left:4px solid #f59e0b">
        <h3>🔗 Bundle & Cross-sell</h3>
        <p>Birden fazla ürünü paket halinde sat, ya da müşteri sepetine tamamlayıcı ürünler öner.</p>
        <ul>
            <li>Hosting + Domain + SSL bundle</li>
            <li>Sipariş tamamlanırken cross-sell popup</li>
            <li>İndirimli paket fiyatı tanımlama</li>
        </ul>
        <a class="ao-btn soft" href="<?= url('admin/product-center/bundles') ?>">Bundle Yönet</a>
        <a class="ao-btn soft" href="<?= url('admin/product-center/promotions') ?>">Promosyonlar</a>
    </div>
</div>
