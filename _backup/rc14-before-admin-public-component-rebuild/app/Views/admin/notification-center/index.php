<?php $events=$summary['events']??[]; $templates=$summary['templates']??[]; ?>
<div class="ao-card">
  <h2>Notification Center Final</h2>
  <p>Mail, SMS ve WhatsApp bildirim olaylarını tek merkezden izleyin. EPP, fatura, ticket, hosting ve domain olayları burada toplanır.</p>
  <div class="ao-grid-3">
    <div class="ao-stat"><strong><?= count($events) ?></strong><span>Bildirim Olayı</span></div>
    <div class="ao-stat"><strong><?= count($templates) ?></strong><span>Şablon</span></div>
    <div class="ao-stat"><strong>Mail / SMS / WhatsApp</strong><span>Kanallar</span></div>
  </div>
</div>
<div class="ao-card"><h3>Olay Tetikleyicileri</h3><table class="ao-table"><thead><tr><th>Olay</th><th>Başlık</th><th>Kanal</th><th>Durum</th></tr></thead><tbody><?php foreach($events as $e): ?><tr><td><?= e($e['event_key']) ?></td><td><?= e($e['title']) ?></td><td><?= e($e['channel']) ?></td><td><?= e($e['status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="ao-card"><h3>Son Şablonlar</h3><table class="ao-table"><thead><tr><th>Başlık</th><th>Kanal</th><th>Durum</th></tr></thead><tbody><?php foreach($templates as $t): ?><tr><td><?= e($t['title'] ?? $t['template_key'] ?? '-') ?></td><td><?= e($t['channel'] ?? '-') ?></td><td><?= e($t['status'] ?? 'active') ?></td></tr><?php endforeach; ?></tbody></table></div>
