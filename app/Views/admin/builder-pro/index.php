<?php
$target = preg_replace('/[^a-z0-9_-]/i','', $_GET['target'] ?? 'site');
$template = preg_replace('/[^a-z0-9_-]/i','', $_GET['template'] ?? 'home');
$target = in_array($target, ['site','admin','customer'], true) ? $target : 'site';
$template = $template ?: 'home';
try { ao_builder_pro_ensure_schema(); } catch(Throwable $e) {}
$layout = [];
try {
    $q=db()->prepare('SELECT * FROM builder_pro_layouts WHERE target=? AND template_key=? LIMIT 1');
    $q->execute([$target,$template]);
    $row=$q->fetch();
    if($row && !empty($row['layout_json'])) $layout=json_decode($row['layout_json'], true) ?: [];
} catch(Throwable $e) {}
if(!$layout){
    $layout = [[
        'id'=>'default_row','cols'=>[
            ['id'=>'c1','span'=>6,'widgets'=>[['id'=>'w1','type'=>'hero','title'=>'Ahost One Builder Pro 3.0','text'=>'Site ön yüzü, admin ve müşteri panelini aynı görsel builder ile düzenleyin.','button'=>'Başla']]],
            ['id'=>'c2','span'=>4,'widgets'=>[['id'=>'w2','type'=>'domain','title'=>'Domain Search Center','text'=>'Registrar fiyatı + komisyon ile canlı fiyat gösterimi.']]]
        ]
    ]];
}
$blocks = [
    'hero'=>['Hero','Başlık, açıklama ve CTA'],
    'domain'=>['Domain Sorgu','WHOIS/DNS/SSL/değerleme widget'],
    'product'=>['Paket / Ürün','Hosting, domain, lisans, SEO, mobil uygulama'],
    'pricing'=>['Fiyat Tablosu','Plan karşılaştırma ve satın alma'],
    'kpi'=>['Dashboard KPI','Admin/Müşteri panel kartları'],
    'renewal'=>['Yenileme Kartı','Hosting/domain ödeme tarihi'],
    'invoice'=>['Fatura Kartı','Son faturalar ve durumlar'],
    'ticket'=>['Ticket Kartı','Destek SLA ve ticketlar'],
    'chart'=>['Grafik','Gelir, sipariş, kaynak veya SLA'],
    'form'=>['Form','Teklif, iletişim, başvuru'],
    'media'=>['Medya / Slider','Görsel, video, galeri'],
    'testimonial'=>['Müşteri Yorumu','Referans ve sosyal kanıt'],
    'faq'=>['SSS','Sık sorulan sorular'],
    'text'=>['Metin / Tanıtım','Serbest içerik bloğu'],
];
?>
<?php /* RC12: direct CSS link removed; single UI CSS is loaded by layout-head. */ ?>
<div class="bp-hero">
    <span class="tag">Ahost Builder Pro 3.0 · v24.11.3</span>
    <h2>Elementor'dan daha esnek: site, admin ve müşteri paneli tek builder.</h2>
    <p>1/1’den 1/10’a kadar kolon, yan yana/alt alta blok, kenar ve orta tutmaçlarla genişletme, blok kütüphanesi, masaüstü/tablet/mobil düzen ve rol kontrollü panel tasarımı.</p>
    <div class="bp-stat">
        <div><b>Sürükle-Bırak</b><span>Blokları sürükleyin</span></div>
        <div><b>3 Alan</b><span>Site + Admin + Müşteri</span></div>
        <div><b>14+ Blok</b><span>Paket, ürün, domain, KPI</span></div>
        <div><b>Yükseklik</b><span>Alt tutamak sürükle</span></div>
    </div>
</div>

<form id="bpForm" method="post" action="<?= url('admin/builder-pro/save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="target" id="bp_target" value="<?= e($target) ?>">
    <input type="hidden" name="template_key" value="<?= e($template) ?>">
    <input type="hidden" name="layout_json" id="bp_json" value='<?= e(json_encode($layout, JSON_UNESCAPED_UNICODE)) ?>'>
</form>

<div class="bp-toolbar bp-card">
    <div>
        <strong>Çalışma Alanı:</strong>
        <div class="bp-targets" style="margin-top:8px">
            <a class="bp-pill bp-target <?= $target==='site'?'active':'' ?>" data-target="site" href="<?= url('admin/builder-pro?target=site&template=home') ?>">Site Ön Yüz</a>
            <a class="bp-pill bp-target <?= $target==='admin'?'active':'' ?>" data-target="admin" href="<?= url('admin/builder-pro?target=admin&template=dashboard') ?>">Admin Panel</a>
            <a class="bp-pill bp-target <?= $target==='customer'?'active':'' ?>" data-target="customer" href="<?= url('admin/builder-pro?target=customer&template=dashboard') ?>">Müşteri Paneli</a>
        </div>
    </div>
    <div class="bp-actions">
        <div class="bp-device"><button type="button" data-device="desktop">Desktop</button><button type="button" data-device="tablet">Tablet</button><button type="button" data-device="mobile">Mobil</button></div>
        <button type="button" class="bp-btn soft" id="bpUndo">Geri Al</button>
        <button type="button" class="bp-btn soft" id="bpRedo">İleri Al</button>
        <button type="button" class="bp-btn" id="bpSave">Kaydet</button>
    </div>
</div>

<div class="bp-shell">
    <aside class="bp-left bp-card">
        <div class="bp-title"><h3>Blok Kütüphanesi</h3><span class="bp-pill">Pro</span></div>
        <div class="bp-help">Blok seçip tuvale ekleyin. Satır ekleyip 1/1 - 1/10 kolon düzeni oluşturun. Kolon yanındaki mavi tutmaçla genişlet/daralt yapın.</div>
        <input class="bp-search" id="bpSearch" placeholder="Blok ara: paket, domain, fatura...">
        <div class="bp-library">
            <?php foreach($blocks as $key=>$info): ?>
            <div class="bp-block-item" data-type="<?= e($key) ?>"><span><b><?= e($info[0]) ?></b><small><?= e($info[1]) ?></small></span><strong>＋</strong></div>
            <?php endforeach; ?>
        </div>
    </aside>

    <main>
        <div class="bp-toolbar bp-card">
            <div class="bp-actions">
                <select id="bpCols" class="bp-pill">
                    <?php for($i=1;$i<=10;$i++): ?><option value="<?= $i ?>"><?= $i ?> kolon · <?= $i===1?'1/1':'1/'.$i ?></option><?php endfor; ?>
                </select>
                <button type="button" class="bp-btn secondary" id="bpAddRow">Satır Ekle</button>
            </div>
            <span class="bp-pill"><?= e(strtoupper($target)) ?> / <?= e($template) ?></span>
        </div>
        <div class="bp-canvas" id="bpCanvas"></div>
    </main>

    <aside class="bp-right bp-card">
        <div class="bp-title"><h3>Blok Ayarları</h3><span class="bp-pill">Inspector</span></div>
        <div id="bpInspector"><p>Bir blok seçin.</p></div>
        <hr>
        <h3>Desteklenen Alanlar</h3>
        <ul>
            <li>Site ön yüzü: hero, paket, domain, yorum, SSS.</li>
            <li>Admin: KPI, grafik, AI öneri, sistem sağlığı.</li>
            <li>Müşteri: hizmet, domain, fatura, yenileme, kredi.</li>
            <li>Masaüstü/tablet/mobil ayrı düzen altyapısı.</li>
        </ul>
    </aside>
</div>
<script src="<?= url('public/assets/js/admin/builder-pro-v188.js') ?>?v=24.11.3"></script>
