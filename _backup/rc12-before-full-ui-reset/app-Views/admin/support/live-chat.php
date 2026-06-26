<?php
$flash=get_flash(); $rows=[]; $messages=[]; $activeId=(int)($_GET['chat']??0);
try{ if(function_exists('ao_v2410_ensure_schema')) ao_v2410_ensure_schema(); $rows=db()->query('SELECT * FROM support_live_chats ORDER BY FIELD(status,"waiting","active","closed"), id DESC LIMIT 80')->fetchAll() ?: []; if($activeId){ $q=db()->prepare('SELECT * FROM support_live_messages WHERE chat_id=? ORDER BY id ASC'); $q->execute([$activeId]); $messages=$q->fetchAll() ?: []; } }catch(Throwable $e){}
?>
<link rel="stylesheet" href="<?= asset('css/support-center.css') ?>?v=24.5.0">
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><span class="ao-kicker">Support Center</span><h2>Canlı Sohbet</h2><p>Bekleyen, aktif ve kapanan sohbetleri yönetin; gerekirse sohbeti ticketa dönüştürün.</p></div><a class="ao-btn soft" href="<?= url('admin/support/tickets') ?>">Ticketlar</a></div>
<div class="ao-livechat-grid">
  <div class="ao-card ao-chat-list"><h3>Sohbet Kuyruğu</h3><?php foreach($rows as $r): ?><a class="ao-chat-row <?= $activeId===(int)$r['id']?'active':'' ?>" href="<?= url('admin/support/live-chat?chat='.(int)$r['id']) ?>"><strong><?= e($r['visitor_name'] ?: 'Ziyaretçi') ?></strong><span><?= e($r['subject']) ?></span><em><?= e($r['status']) ?></em></a><?php endforeach; if(!$rows): ?><p class="ao-muted">Henüz sohbet yok.</p><?php endif; ?></div>
  <div class="ao-card ao-chat-window">
    <?php if($activeId): ?><div class="ao-chat-head"><h3>Sohbet #<?= $activeId ?></h3><a class="ao-mini-btn danger" href="<?= url('admin/support/live-chat/close?id='.$activeId.'&csrf_token='.csrf_token()) ?>">Kapat</a></div><div class="ao-chat-messages"><?php foreach($messages as $m): ?><div class="msg <?= e($m['sender_type']) ?>"><b><?= e($m['sender_name'] ?: $m['sender_type']) ?></b><p><?= nl2br(e($m['message'])) ?></p><small><?= e($m['created_at']) ?></small></div><?php endforeach; if(!$messages): ?><p class="ao-muted">Mesaj yok.</p><?php endif; ?></div><form method="post" action="<?= url('admin/support/live-chat/reply') ?>" class="ao-chat-reply"><?= csrf_field() ?><input type="hidden" name="chat_id" value="<?= $activeId ?>"><textarea name="message" rows="3" placeholder="Yanıtınızı yazın..." required></textarea><button class="ao-btn">Gönder</button></form><?php else: ?><div class="ao-empty-state"><h3>Sohbet seçin</h3><p>Soldan bekleyen veya aktif sohbeti açın.</p></div><?php endif; ?>
  </div>
</div>

<script>
(function aoLiveChatPoll(){
  const active = new URLSearchParams(location.search).get('chat') || '';
  const list = document.querySelector('.ao-chat-list');
  const win = document.querySelector('.ao-chat-messages');
  if(!list) return;
  async function tick(){
    try{
      const r = await fetch((window.AHOST_BASE_URL||'') + '/admin/support/live-chat/poll' + (active ? ('?chat='+encodeURIComponent(active)) : ''), {headers:{'Accept':'application/json'}});
      const j = await r.json();
      if(!j.ok) return;
      if(j.rows && list){
        const head = list.querySelector('h3')?.outerHTML || '<h3>Sohbet Kuyruğu</h3>';
        const rows = j.rows.map(x=>`<a class="ao-chat-row ${String(x.id)===String(active)?'active':''}" href="${(window.AHOST_BASE_URL||'')}/admin/support/live-chat?chat=${x.id}"><strong>${x.visitor_name||'Ziyaretçi'}</strong><span>${x.subject||''}</span><em>${x.status||''}</em></a>`).join('');
        list.innerHTML = head + (rows || '<p class="ao-muted">Henüz sohbet yok.</p>');
      }
      if(j.messages && win){
        win.innerHTML = j.messages.map(m=>`<div class="ao-msg ${m.sender_type==='admin'?'admin':'visitor'}"><b>${m.sender_name||m.sender_type}</b><p>${(m.message||'').replace(/[<>&]/g, c=>({'<':'&lt;','>':'&gt;','&':'&amp;'}[c]))}</p><small>${m.created_at||''}</small></div>`).join('');
      }
    }catch(e){}
  }
  setInterval(tick, 8000);
})();
</script>
