<?php $rows = $notifications ?? []; ?>
<div class="customer-panel-card premium-detail-hero ao-notification-hero">
  <div><span class="u2-kicker">Bildirim Merkezi</span><h2>Bildirimlerim</h2><p>Okunmamış ve geçmiş müşteri bildirimlerinizi tek ekrandan yönetin.</p></div>
  <?php if($rows): ?><form method="post" action="<?= url('client/notifications/read-all') ?>"><?= csrf_field() ?><button class="u2-btn soft">✓ Tümünü okundu yap</button></form><?php endif; ?>
</div>
<div class="customer-panel-card ao-notification-list" role="status" aria-live="polite">
  <?php if(!$rows): ?><div class="ao-empty-state"><strong>Henüz bildirim bulunmuyor.</strong><p>Yeni sipariş, fatura, destek ve sistem bildirimleri burada görünür.</p></div><?php else: ?>
    <?php foreach($rows as $n): ?><article class="ao-notification-card <?= empty($n['read_at'])?'is-unread':'' ?>">
      <div class="ao-notification-icon">🔔</div><div class="ao-notification-content"><h3><?= e($n['title'] ?? 'Bildirim') ?></h3><p><?= e($n['message'] ?? '') ?></p><small><?= e($n['created_at'] ?? '') ?></small></div>
      <div class="ao-notification-actions"><?php if(!empty($n['target_url'])): ?><a class="u2-btn small soft" href="<?= url($n['target_url']) ?>">Git</a><?php endif; ?><?php if(empty($n['read_at'])): ?><form method="post" action="<?= url('client/notifications/read') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$n['id'] ?>"><button class="u2-btn small">Okundu</button></form><?php endif; ?></div>
    </article><?php endforeach; ?>
  <?php endif; ?>
</div>
