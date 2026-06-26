<?php ao_schema_ensure_v188(); $c=current_customer(); $pref=[]; try{$q=db()->prepare('SELECT * FROM client_preferences WHERE client_id=? LIMIT 1');$q->execute([$c['id']]);$pref=$q->fetch()?:[];}catch(Throwable $e){} $layout=$pref['builder_layout_json'] ?? json_encode(['cards'=>['services','domains','invoices','renewals','credit','tickets'],'columns'=>'2'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE); ?>
<div class="customer-panel-card">
  <span class="u2-kicker">Client Builder Pro</span>
  <h2>Panelimi Düzenle</h2>
  <p>Kartları gizle/göster, sıralama ve kolon düzenini kendine göre sakla. Bu düzen sadece senin müşteri panelinde uygulanır.</p>
  <form method="post" action="<?= url('client/builder-save') ?>" style="margin-top:18px">
    <?= csrf_field() ?>
    <textarea name="builder_layout_json" style="width:100%;min-height:260px;border:1px solid #dbe6f4;border-radius:18px;padding:16px;font-family:monospace"><?= e($layout) ?></textarea>
    <p><button class="u2-btn">Panel Düzenimi Kaydet</button> <a class="u2-btn soft" href="<?= url('client') ?>">Panele Dön</a></p>
  </form>
</div>
<div class="customer-panel-card" style="margin-top:20px"><h3>Hazır Bloklar</h3><p>Hizmetlerim, Domainlerim, Faturalarım, Kredi, Yenileme Merkezi, Ticketlar ve Hızlı İşlemler blokları desteklenir.</p></div>
