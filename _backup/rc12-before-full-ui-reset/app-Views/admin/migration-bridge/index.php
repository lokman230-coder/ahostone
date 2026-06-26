<?php
if (function_exists('ao_bridge_ensure_schema')) { ao_bridge_ensure_schema(); ao_bridge_ensure_target_schema(); }
$connections = [];
$runs = [];
$items = [];
$edit = null;
try { $connections = db()->query('SELECT * FROM bridge_connections ORDER BY id DESC')->fetchAll(); } catch(Throwable $e) {}
try { $runs = db()->query('SELECT r.*, c.name connection_name FROM bridge_runs r LEFT JOIN bridge_connections c ON c.id=r.connection_id ORDER BY r.id DESC LIMIT 10')->fetchAll(); } catch(Throwable $e) {}
try { $items = db()->query('SELECT i.*, r.run_type FROM bridge_items i LEFT JOIN bridge_runs r ON r.id=i.run_id ORDER BY i.id DESC LIMIT 25')->fetchAll(); } catch(Throwable $e) {}
if (!empty($_GET['edit'])) { foreach ($connections as $c) if ((int)$c['id']===(int)$_GET['edit']) $edit=$c; }
if (function_exists('ao_bridge_ensure_selector_schema')) { ao_bridge_ensure_selector_schema(); }
$bridgePreview = null; $selections = [];
if ($edit) {
    try {
        if (!empty($edit['source_sql_path']) && function_exists('ao_bridge_sql_preview')) {
            $bridgePreview = ao_bridge_sql_preview($edit, 200);
        } elseif (function_exists('ao_bridge_live_preview')) {
            // Canlı DB bağlantısında da kayıt bazlı seçmeli import ekranı gösterilir.
            $bridgePreview = ao_bridge_live_preview($edit, 200);
        }
        // v23.2.0: Canlı DB önizlemesi sadece JSON/sayı olarak kalmasın; seçim tablosuna da yazılsın.
        if ($bridgePreview && function_exists('ao_bridge_store_selection')) {
            ao_bridge_store_selection((int)$edit['id'], $bridgePreview);
        }
    } catch(Throwable $e) { $bridgePreview = ['ok'=>false,'message'=>$e->getMessage(),'entities'=>[]]; }
    try { $q=db()->prepare('SELECT * FROM bridge_import_selections WHERE connection_id=? ORDER BY entity_type,id ASC'); $q->execute([(int)$edit['id']]); foreach($q->fetchAll() as $row){ $selections[$row['entity_type']][]=$row; } } catch(Throwable $e) {}
}
?>
<div class="ao-page-head">
    <div>
        <h2>Migration & Bridge Production</h2>
        <p>Kaynak sistem veritabanı, cPanel/WHM ve diğer paneller için canlı geçişe uygun bağlantı, dry-run, eşleştirme ve import merkezi.</p>
    </div>
    <div class="ao-actions"><a class="ao-btn soft" href="<?= e(url('admin/logs')) ?>">Logları Gör</a></div>
</div>

<div class="ao-alert success">
    <strong>v20.0.3 Migration Import Selector Pro:</strong> Canlı DB bağlanabilir veya ziplenmiş SQL yedeği yükleyebilirsin. Önce kayıtlar listelenir; tikli olan müşteriler, ürünler, domainler, faturalar ve diğer kayıtlar aktarılır. Bridge map çift kayıt oluşmasını engeller.
</div>
<div class="ao-alert info">
    <strong>v23.1.0 Migration Bridge Pro:</strong> Dry-run artık sadece sayı/JSON göstermez; canlı DB veya ZIP/SQL kaynağından müşteri, hosting, domain, ürün, ürün fiyatı, domain uzantısı, fatura ve ticket kayıtları seçilebilir tablo olarak gelir. Seçtiklerini aktar, istemediklerini atla.
</div>

<div class="ao-card">
    <h3>Ziplenmiş SQL / SQL Yedeği ile Import</h3>
    <p class="ao-muted">Kaynak sistem, WiseCP veya Blesta yedeğini zip ya da .sql olarak yükle. Sistem tabloları okuyup çekilecek kayıtları listeler; sadece seçtiklerin aktarılır.</p>
    <form method="post" action="<?= e(url('admin/migration-bridge/upload-sql')) ?>" enctype="multipart/form-data" class="ao-form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="connection_id" value="<?= e($edit['id'] ?? 0) ?>">
        <label>Import Adı<input name="name" value="<?= e($edit['name'] ?? 'Kaynak Sistem SQL Yedeği Import') ?>"></label>
        <label>Kaynak Tipi
            <select name="source_type">
                <option value="source">Kaynak Sistem</option>
                <option value="wisecp">WiseCP</option>
                <option value="blesta">Blesta</option>
                <option value="clientexec">ClientExec</option>
            </select>
        </label>
        <label>Tablo Prefix<input name="table_prefix" value="<?= e($edit['table_prefix'] ?? 'tbl') ?>"><small>Kaynak sistem için genelde tbl</small></label>
        <label>Charset<input name="source_charset" value="<?= e($edit['source_charset'] ?? 'utf8mb4') ?>"></label>
        <label>Zip / SQL Dosyası<input type="file" name="sql_backup" accept=".zip,.sql" required></label>
        <div class="ao-actions"><button class="ao-btn" type="submit">Yükle ve Listele</button><?php if($edit && !empty($edit['source_sql_path'])): ?><a class="ao-btn soft" href="<?= e(url('admin/migration-bridge/analyze-sql?id='.$edit['id'])) ?>">Yeniden Analiz Et</a><?php endif; ?></div>
    </form>
</div>

<div class="ao-grid two">
    <div class="ao-card">
        <h3><?= $edit ? 'Bridge Bağlantısını Düzenle' : 'Yeni Migration Bridge Bağlantısı' ?></h3>
        <form method="post" action="<?= e(url('admin/migration-bridge/save')) ?>" class="ao-form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
            <label>Bağlantı Adı<input name="name" value="<?= e($edit['name'] ?? 'Kaynak Sistem Production Bridge') ?>" required></label>
            <label>Kaynak Tipi
                <select name="source_type"><option value="source" <?= (($edit['source_type']??'source')==='source')?'selected':'' ?>>Kaynak Sistem</option><option value="wisecp" <?= (($edit['source_type']??'')==='wisecp')?'selected':'' ?>>WiseCP hazırlık</option><option value="blesta" <?= (($edit['source_type']??'')==='blesta')?'selected':'' ?>>Blesta hazırlık</option></select>
            </label>
            <label>DB Host<input name="source_host" value="<?= e($edit['source_host'] ?? 'localhost') ?>" required></label>
            <label>DB Port<input name="source_port" value="<?= e($edit['source_port'] ?? '3306') ?>"></label>
            <label><input type="checkbox" name="source_ssl" value="1" <?= !empty($edit['source_ssl'])?'checked':'' ?>> SSL ile bağlan</label>
            <label>DB Adı<input name="source_database" value="<?= e($edit['source_database'] ?? '') ?>" required></label>
            <label>DB Kullanıcı<input name="source_username" value="<?= e($edit['source_username'] ?? '') ?>" required></label>
            <label>DB Şifre<input type="password" name="source_password" placeholder="<?= $edit ? 'Değiştirmeyeceksen boş bırak' : '' ?>"></label>
            <label>Tablo Prefix<input name="table_prefix" value="<?= e($edit['table_prefix'] ?? 'tbl') ?>"><small>Kaynak sistem varsayılan: tbl → tblclients, tblhosting...</small></label>
            <label>Charset<input name="source_charset" value="<?= e($edit['source_charset'] ?? 'utf8mb4') ?>"></label>
            <div class="ao-actions"><button class="ao-btn" type="submit">Kaydet</button><?php if($edit): ?><a class="ao-btn soft" href="<?= e(url('admin/migration-bridge')) ?>">Yeni Bağlantı</a><a class="ao-btn soft" href="<?= e(url('admin/migration-bridge/test?id='.$edit['id'])) ?>">Bağlantıyı Test Et</a><a class="ao-btn soft" href="<?= e(url('admin/migration-bridge/dry-run?id='.$edit['id'])) ?>">Dry-run ve Seçim Oluştur</a><a class="ao-btn danger" onclick="return confirm('Import canlı kayıt oluşturabilir. Önce seçmeli import ekranını kullanman önerilir. Devam edilsin mi?')" href="<?= e(url('admin/migration-bridge/import?id='.$edit['id'])) ?>">Tümünü Import Et</a><?php endif; ?></div>
        </form>
    </div>

    <div class="ao-card">
        <h3>Canlı Geçiş Durumu</h3>
        <ul class="ao-checklist">
            <li>Kaynak sistem veritabanı yedeği alındı</li>
            <li>Ahost One temiz veritabanı hazır</li>
            <li>Bağlantı testi / SQL yedeği analizi yapılır</li>
            <li>Dry-run sayıları ve seçili kayıtlar kontrol edilir</li>
            <li>Import sonrası müşteri/hizmet/domain/fatura örnekleri kontrol edildi</li>
            <li>Registrar ve server API bilgileri Ahost One içinde yapılandırıldı</li><li>v7.4.1: Kaynak Sistem tblservers ve tblregistrars yapılandırmaları da import kapsamına alındı</li>
        </ul>
        <p class="ao-muted">Gerçek geçişte önce dry-run raporunu kontrol etmeden import çalıştırma.</p>
    </div>
</div>

<div class="ao-card">
    <h3>Nasıl Kullanılır?</h3>
    <div class="ao-grid four">
        <div class="ao-stat"><strong>1</strong><span>Bağlantıyı kaydet</span></div>
        <div class="ao-stat"><strong>2</strong><span>Bağlantıyı test et</span></div>
        <div class="ao-stat"><strong>3</strong><span>Dry-run sayıları kontrol et</span></div>
        <div class="ao-stat"><strong>4</strong><span>Import et</span></div>
    </div>
    <p class="ao-muted">Desteklenen Kaynak tablolar: tblclients, tblproductgroups, tblproducts, tblhosting, tbldomains, tblorders, tblinvoices, tblinvoiceitems, tbltickets, tblservers, tblregistrars. Eksik tablo varsa sahte veri oluşturulmaz; uyarı gösterilir.</p>
</div>


<?php if($edit && $bridgePreview): ?>
<div class="ao-card">
    <h3>Seçimli Import Önizleme</h3>
    <p class="ao-muted"><?= e($bridgePreview['message'] ?? '') ?> Her başlık altında ilk kayıtlar listelenir. Aktarmak istemediklerinin tikini kaldır.</p>
    <form method="post" action="<?= e(url('admin/migration-bridge/import-selected')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="connection_id" value="<?= e($edit['id']) ?>">
        <?php foreach(($bridgePreview['entities'] ?? []) as $entity => $info): ?>
            <details class="ao-card" open>
                <summary><strong><?= e(ucwords(str_replace('_',' ', $entity))) ?></strong> — <?= e($info['table'] ?? '') ?> — <?= (int)($info['count'] ?? 0) ?> kayıt</summary>
                <?php if(empty($info['exists'])): ?>
                    <p class="ao-muted">Tablo bulunamadı veya kayıt yok.</p>
                <?php else: ?>
                    <div class="ao-actions"><label><input type="checkbox" onclick="this.closest('details').querySelectorAll('input[type=checkbox][data-row]').forEach(cb=>cb.checked=this.checked)" checked> Tümünü seç/kaldır</label></div>
                    <table class="ao-table">
                        <thead><tr><th>Seç</th><th>ID</th><th>Önizleme</th></tr></thead>
                        <tbody>
                        <?php foreach(($selections[$entity] ?? []) as $row): ?>
                            <tr>
                                <td><input data-row type="checkbox" name="selected[<?= e($entity) ?>][]" value="<?= e($row['source_id']) ?>" <?= !empty($row['selected'])?'checked':'' ?>></td>
                                <td><?= e($row['source_id']) ?></td>
                                <td><?= e($row['source_label']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($selections[$entity])): ?><tr><td colspan="3">Önizleme kaydı yok. Yeniden analiz et.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                    <p class="ao-muted">Not: Büyük kaynaklarda ilk 200 kayıt listelenir. Aktar / atla kararını burada ver; bridge map çift kayıt oluşmasını engeller. Ürün fiyatları ve domain uzantı fiyatları için Product Pricing ve Domain Pricing sekmelerini de seç.</p>
                <?php endif; ?>
            </details>
        <?php endforeach; ?>
        <div class="ao-actions"><button class="ao-btn danger" onclick="return confirm('Sadece işaretli kayıtlar aktarılacak. Devam edilsin mi?')">Seçilenleri Aktar</button></div>
    </form>
</div>
<?php endif; ?>

<div class="ao-card">
    <h3>Bridge Bağlantıları</h3>
    <table class="ao-table">
        <thead><tr><th>Ad</th><th>Tip</th><th>Host / DB</th><th>Prefix</th><th>Durum</th><th>Son Test</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php if(!$connections): ?><tr><td colspan="7">Henüz bridge bağlantısı yok.</td></tr><?php endif; ?>
        <?php foreach($connections as $c): ?>
            <tr>
                <td><strong><?= e($c['name']) ?></strong></td>
                <td><?= e(strtoupper($c['source_type'])) ?></td>
                <td><?= e($c['source_host']) ?><br><small><?= e($c['source_database']) ?></small></td>
                <td><?= e($c['table_prefix']) ?></td>
                <td><span class="ao-badge <?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
                <td><?= e($c['last_test_status'] ?: 'not_tested') ?><br><small><?= e($c['last_test_message'] ?: '-') ?></small></td>
                <td class="ao-actions">
                    <a class="ao-btn mini soft" href="<?= e(url('admin/migration-bridge?edit='.$c['id'])) ?>">Düzenle</a>
                    <a class="ao-btn mini soft" href="<?= e(url('admin/migration-bridge/test?id='.$c['id'])) ?>">Test</a>
                    <a class="ao-btn mini soft" href="<?= e(url('admin/migration-bridge/dry-run?id='.$c['id'])) ?>">Dry-run</a>
                    <a class="ao-btn mini danger" onclick="return confirm('Import canlı kayıt oluşturabilir. Dry-run kontrol edildi mi?')" href="<?= e(url('admin/migration-bridge?edit='.$c['id'])) ?>">Seçimli Import</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="ao-grid two">
    <div class="ao-card">
        <h3>Son Bridge Çalıştırmaları</h3>
        <table class="ao-table">
            <thead><tr><th>ID</th><th>Bağlantı</th><th>Tip</th><th>Durum</th><th>Özet</th></tr></thead>
            <tbody>
            <?php if(!$runs): ?><tr><td colspan="5">Henüz run yok.</td></tr><?php endif; ?>
            <?php foreach($runs as $r): ?>
                <tr><td>#<?= e($r['id']) ?></td><td><?= e($r['connection_name']) ?></td><td><?= e($r['run_type']) ?></td><td><?= e($r['status']) ?></td><td><small><?= e($r['summary_json'] ?: $r['error_message']) ?></small></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="ao-card">
        <h3>Son Bridge Kayıtları</h3>
        <table class="ao-table">
            <thead><tr><th>Varlık</th><th>Kaynak</th><th>Hedef</th><th>Durum</th><th>Mesaj</th></tr></thead>
            <tbody>
            <?php if(!$items): ?><tr><td colspan="5">Henüz kayıt yok.</td></tr><?php endif; ?>
            <?php foreach($items as $i): ?>
                <tr><td><?= e($i['entity_type']) ?></td><td><?= e($i['source_label'] ?: $i['source_id']) ?></td><td><?= e(($i['target_table']?:'-').' #'.($i['target_id']?:'-')) ?></td><td><?= e($i['status']) ?></td><td><small><?= e($i['message']) ?></small></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="ao-grid three">
    <div class="ao-card"><h3>cPanel / WHM Bridge</h3><p>WHM server nodes üzerinden hesap listesi, paket eşleştirme ve kaynak kullanımı senkronizasyonu için üretim hazırlığı tamamlandı. Sunucu bilgileri <b>Hosting & Server Center</b> içinden eklenir.</p></div>
    <div class="ao-card"><h3>Registrar Bridge</h3><p>DomainNameAPI / ResellerClub / diğer registrar domain eşleştirmeleri Domain Center içindeki registrar yapılandırmalarıyla çalışacak şekilde ayrıldı.</p></div>
    <div class="ao-card"><h3>Güvenli Geçiş</h3><p>Bridge tekrar çalıştırıldığında <b>bridge_import_maps</b> tablosu çift müşteri, ürün, domain ve fatura oluşmasını engeller.</p></div>
</div>
