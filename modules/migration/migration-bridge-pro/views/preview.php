<link rel="stylesheet" href="<?= url('modules/migration/migration-bridge-pro/assets/css/migration-bridge-pro.css') ?>">
<script src="<?= url('modules/migration/migration-bridge-pro/assets/js/migration-bridge-pro.js') ?>" defer></script>
<div class="mbp-wrap">
  <div class="mbp-hero compact">
    <div>
      <h1>Seçmeli Import Önizleme</h1>
      <p>Aktarılacak kayıtları seçin. Ürün fiyatları ve domain uzantı fiyatları ayrı sekmede gösterilir.</p>
    </div>
    <div class="mbp-actions"><button class="mbp-btn secondary" type="button" data-select-all>Hepsini Aktar</button><button class="mbp-btn secondary" type="button" data-skip-all>Hepsini Atla</button></div>
  </div>
  <?php $labels = ['customers'=>'Müşteriler','products'=>'Ürünler + Fiyatlar','domain_extensions'=>'Domain Uzantı Fiyatları','hosting'=>'Hostingler','domains'=>'Domainler','invoices'=>'Faturalar','tickets'=>'Ticketlar','currencies'=>'Para Birimleri']; ?>
  <form method="post" action="/admin/modules/migration-bridge-pro/import">
    <input type="hidden" name="scan_id" value="<?= (int)$scanId ?>">
    <div class="mbp-tabs">
      <?php foreach ($labels as $key=>$label): if (empty($preview[$key])) continue; ?>
        <button type="button" class="mbp-tab" data-tab="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?> <span><?= count($preview[$key]) ?></span></button>
      <?php endforeach; ?>
    </div>
    <?php foreach ($labels as $key=>$label): if (empty($preview[$key])) continue; ?>
      <section class="mbp-panel" data-panel="<?= htmlspecialchars($key) ?>">
        <div class="mbp-panel-head"><h2><?= htmlspecialchars($label) ?></h2><input class="mbp-search" placeholder="Bu sekmede ara..."></div>
        <div class="mbp-table-wrap">
          <table class="mbp-table">
            <thead><tr><th>İşlem</th><th>Kayıt</th><th>Durum</th><th>Fiyat / Detay</th></tr></thead>
            <tbody>
            <?php foreach ($preview[$key] as $item): $p = $item['payload']; ?>
              <tr>
                <td>
                  <select name="actions[<?= (int)$item['id'] ?>]">
                    <option value="import" <?= $item['action']==='import'?'selected':'' ?>>Aktar</option>
                    <option value="skip">Atla</option>
                    <option value="update">Güncelle</option>
                    <option value="merge">Birleştir</option>
                    <option value="exclude">Hariç Tut</option>
                  </select>
                </td>
                <td><strong><?= htmlspecialchars($item['title'] ?? '') ?></strong><br><small><?= htmlspecialchars($item['subtitle'] ?? '') ?></small></td>
                <td><span class="mbp-badge <?= htmlspecialchars($item['conflict_status']) ?>"><?= htmlspecialchars($item['conflict_status']) ?></span></td>
                <td><?= mbp_detail_html($key, $p) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endforeach; ?>
    <div class="mbp-sticky"><button class="mbp-btn" type="submit">Seçilenleri İçe Aktar</button></div>
  </form>
</div>
<?php
function mbp_detail_html(string $key, array $p): string {
  if ($key === 'products') {
    $rows = [];
    foreach (($p['pricing'] ?? []) as $pr) {
      $cur = strtoupper((string)($pr['currency_code'] ?? ('#'.($pr['currency'] ?? '-'))));
      $rows[] = '<strong>'.htmlspecialchars($cur).'</strong> '
        .'Aylık: '.htmlspecialchars((string)($pr['monthly'] ?? '-'))
        .' / 3 Aylık: '.htmlspecialchars((string)($pr['quarterly'] ?? '-'))
        .' / 6 Aylık: '.htmlspecialchars((string)($pr['semiannually'] ?? '-'))
        .' / Yıllık: '.htmlspecialchars((string)($pr['annually'] ?? '-'))
        .' / 2Y: '.htmlspecialchars((string)($pr['biennially'] ?? '-'))
        .' / 3Y: '.htmlspecialchars((string)($pr['triennially'] ?? '-'));
    }
    return $rows ? implode('<br>', $rows).'<br><small class="muted">Import sırasında USD/TL çift alan olarak yazılır; tekrar importta mevcut kayıt güncellenir.</small>' : '<span class="muted">Fiyat yok</span>';
  }
  if ($key === 'domain_extensions') {
    $rows = [];
    foreach (($p['pricing'] ?? []) as $type=>$prices) {
      foreach ($prices as $pr) {
        $cur = strtoupper((string)($pr['currency_code'] ?? ('#'.($pr['currency'] ?? '-'))));
        $label = match($type) { 'domainregister'=>'Kayıt', 'domaintransfer'=>'Transfer', 'domainrenew'=>'Yenileme', default=>$type };
        $rows[] = htmlspecialchars($label).' <strong>'.htmlspecialchars($cur).'</strong>'
          .' / 1Y: '.htmlspecialchars((string)($pr['msetupfee'] ?? '-'))
          .' / 2Y: '.htmlspecialchars((string)($pr['qsetupfee'] ?? '-'))
          .' / 3Y: '.htmlspecialchars((string)($pr['ssetupfee'] ?? '-'))
          .' / 5Y: '.htmlspecialchars((string)($pr['bsetupfee'] ?? '-'));
      }
    }
    return $rows ? implode('<br>', $rows).'<br><small class="muted">Uzantı fiyatları kayıt/transfer/yenileme olarak USD/TL saklanır.</small>' : '<span class="muted">Fiyat yok</span>';
  }
  return '<code>'.htmlspecialchars(mb_strimwidth(json_encode($p, JSON_UNESCAPED_UNICODE),0,220,'...')).'</code>';
}
?>
