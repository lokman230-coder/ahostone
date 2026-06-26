<?php ao_schema_ensure_v188(); $c=current_customer(); $themes=db()->query("SELECT * FROM themes WHERE area IN ('site','client') ORDER BY area,name")->fetchAll(); $pref=[]; try{$q=db()->prepare('SELECT * FROM client_preferences WHERE client_id=? LIMIT 1');$q->execute([$c['id']]);$pref=$q->fetch()?:[];}catch(Throwable $e){} ?>
<div class="customer-panel-card">
  <span class="u2-kicker">Kişisel Tema</span>
  <h2>Tema Değiştir</h2>
  <p>Site ön yüzünde ve müşteri panelinde size özel tema tercihi seçin. Kaydedince her girişinizde bu tercihler uygulanır.</p>
  <form method="post" action="<?= url('client/theme-save') ?>" class="theme-fields" style="margin-top:18px">
    <?= csrf_field() ?>
    <label>Site Teması
      <select name="site_theme_id">
        <option value="0">Varsayılan site teması</option>
        <?php foreach($themes as $t): if($t['area']!=='site') continue; ?><option value="<?= (int)$t['id'] ?>" <?= ((int)($pref['site_theme_id']??0)===(int)$t['id'])?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Müşteri Paneli Teması
      <select name="client_theme_id">
        <option value="0">Varsayılan müşteri paneli teması</option>
        <?php foreach($themes as $t): if($t['area']!=='client') continue; ?><option value="<?= (int)$t['id'] ?>" <?= ((int)($pref['client_theme_id']??0)===(int)$t['id'])?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <div><button class="u2-btn">Temayı Kaydet</button></div>
  </form>
</div>
<div class="customer-panel-card" style="margin-top:20px"><h3>Önizleme Notu</h3><p>Seçtiğiniz tema sadece sizin oturumunuza uygulanır. Diğer müşterilerin veya ana sitenin global teması değişmez.</p></div>
