<?php
$service = $service ?? [];
$title = $service['title'] ?? 'Ahost One Hizmetleri';
$kicker = $service['kicker'] ?? 'Ahost One';
$summary = $service['summary'] ?? 'Hosting, domain, builder ve dijital hizmetleri tek merkezden yönetin.';
$primary = $service['primary'] ?? ['Teklif / Sipariş Başlat','client/register'];
$secondary = $service['secondary'] ?? ['Dijital Hizmetler','dijital-hizmetler'];
$cards = $service['cards'] ?? [];
$features = $service['features'] ?? [];
?>
<section class="ao-public-page ao-service-page">
  <div class="ao-public-shell">
    <section class="ao-service-hero">
      <div>
        <span class="ao-kicker"><?= e($kicker) ?></span>
        <h1><?= e($title) ?></h1>
        <p><?= e($summary) ?></p>
        <div class="ao-content-actions">
          <a class="ao-content-btn" href="<?= url($primary[1]) ?>"><?= e($primary[0]) ?></a>
          <a class="ao-content-btn secondary" href="<?= url($secondary[1]) ?>"><?= e($secondary[0]) ?></a>
        </div>
      </div>
      <aside class="ao-service-panel">
        <b>Tek panel avantajı</b>
        <ul>
          <li>Otomatik sipariş ve fatura akışı</li>
          <li>Müşteri panelinden kolay yönetim</li>
          <li>Destek, bildirim ve yenileme takibi</li>
        </ul>
      </aside>
    </section>
    <section class="ao-service-grid">
      <?php foreach($cards as $card): ?>
      <article class="ao-service-card-pro">
        <div class="ao-service-icon"><?= e($card['icon'] ?? '✨') ?></div>
        <h3><?= e($card['title'] ?? 'Hizmet') ?></h3>
        <p><?= e($card['text'] ?? '') ?></p>
        <a class="ao-content-btn secondary" href="<?= url($card['href'] ?? 'client/register') ?>"><?= e($card['action'] ?? 'Başla') ?> →</a>
      </article>
      <?php endforeach; ?>
    </section>
    <section class="ao-feature-strip">
      <?php foreach($features as $feature): ?>
      <div><strong><?= e($feature[0]) ?></strong><span><?= e($feature[1]) ?></span></div>
      <?php endforeach; ?>
    </section>
    <section class="ao-content-cta">
      <h2>Projenizi Ahost One ekosistemine taşıyın</h2>
      <p>Domain, hosting, ürün, teklif, müşteri paneli ve destek süreçleri aynı premium tasarım diliyle çalışır.</p>
      <div class="ao-content-actions"><a class="ao-content-btn" href="<?= url('teklif') ?>">Teklif Al</a><a class="ao-content-btn secondary" href="<?= url('urunler') ?>">Ürünleri Gör</a></div>
    </section>
  </div>
</section>
