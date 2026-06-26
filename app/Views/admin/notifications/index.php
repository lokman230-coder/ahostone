<?php
function ao_col_exists_v238($table,$col){ try{$q=db()->prepare("SHOW COLUMNS FROM `$table` LIKE ?");$q->execute([$col]);return (bool)$q->fetch();}catch(Throwable $e){return false;} }
function ao_val_v238($row,$keys,$default=''){ foreach((array)$keys as $k){ if(isset($row[$k]) && $row[$k]!==null && $row[$k]!=='') return $row[$k]; } return $default; }
try{ $hasNew=ao_col_exists_v238('notification_channels','channel_type'); $channels=db()->query($hasNew?'SELECT * FROM notification_channels ORDER BY channel_type, priority, id':'SELECT * FROM notification_channels ORDER BY channel, id')->fetchAll(); }catch(Throwable $e){ $channels=[]; }
try{ $tplKey=ao_col_exists_v238('notification_templates','event_key')?'event_key':'template_key'; $templates=db()->query("SELECT * FROM notification_templates ORDER BY $tplKey")->fetchAll(); }catch(Throwable $e){ $templates=[]; }
try{ $logs=db()->query('SELECT * FROM notification_logs ORDER BY id DESC LIMIT 50')->fetchAll(); }catch(Throwable $e){ $logs=[]; }
$editChannel=null; if(!empty($_GET['channel_id'])){ try{$q=db()->prepare('SELECT * FROM notification_channels WHERE id=?');$q->execute([(int)$_GET['channel_id']]);$editChannel=$q->fetch();}catch(Throwable $e){} }
$cfg=[]; if($editChannel && !empty($editChannel['config_json'])){ $cfg=json_decode($editChannel['config_json'],true) ?: []; }
?>
<div class="ao-hero-slim"><h2>Bildirim Merkezi</h2><p>Mail, SMS ve WhatsApp kanallarını tek ekrandan yönet. İletiMerkezi için API Key + Hash alanları desteklenir.</p></div>
<div class="ao-grid two">
 <div class="ao-card"><h2>Bildirim Kanalları</h2><div class="ao-table-wrap"><table class="ao-table"><thead><tr><th>Tip</th><th>Sağlayıcı</th><th>Ad</th><th>Durum</th><th>Test</th><th>İşlem</th></tr></thead><tbody>
 <?php foreach($channels as $ch): $type=ao_val_v238($ch,['channel_type','channel']); $status=ao_val_v238($ch,['status'], !empty($ch['is_active'])?'active':'inactive'); ?><tr><td><?= e($type) ?></td><td><?= e($ch['provider']??'') ?></td><td><?= e($ch['name']??($ch['provider']??'')) ?></td><td><span class="ao-pill <?= $status==='active'?'green':'red' ?>"><?= e($status) ?></span></td><td><?= (int)($ch['test_mode']??1)?'Açık':'Kapalı' ?></td><td><a class="ao-btn small" href="<?= url('admin/notifications?channel_id='.(int)$ch['id']) ?>">Düzenle</a></td></tr><?php endforeach; ?>
 <?php if(!$channels): ?><tr><td colspan="6">Henüz kanal yok. Sağdaki formdan ekleyebilirsin.</td></tr><?php endif; ?></tbody></table></div></div>
 <div class="ao-card"><h2><?= $editChannel?'Kanal Düzenle':'Yeni Kanal' ?></h2><form method="post" action="<?= url('admin/notifications/save-channel') ?>" class="ao-form ao-form-grid"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($editChannel['id']??0) ?>">
  <label>Tip<select name="channel_type"><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option><option value="email">Mail</option></select></label>
  <label>Sağlayıcı<select name="provider"><option value="iletimerkezi">İletiMerkezi</option><option value="netgsm">NetGSM</option><option value="meta">Meta WhatsApp</option><option value="mail">SMTP / Mail</option><option value="generic">Generic Webhook</option></select></label>
  <label>Kanal Adı<input name="name" value="<?= e(ao_val_v238($editChannel?:[],['name'])) ?>" placeholder="İletiMerkezi SMS"></label>
  <label>Durum<select name="status"><option value="active">Aktif</option><option value="inactive">Pasif</option></select></label>
  <label>Test Modu<select name="test_mode"><option value="1">Açık</option><option value="0">Kapalı - Canlı Gönder</option></select></label>
  <label>Öncelik<input name="priority" value="<?= e(ao_val_v238($editChannel?:[],['priority'],'10')) ?>"></label>
  <label>Gönderici Adı<input name="sender_name" value="<?= e(ao_val_v238($editChannel?:[],['sender_name'])) ?>"></label>
  <label>İletiMerkezi API Key<input name="cfg_api_key" value="<?= e($cfg['api_key']??'') ?>"></label>
  <label>İletiMerkezi Hash / Secret<input name="cfg_api_hash" value="<?= e($cfg['api_hash']??'') ?>"></label>
  <label>SMTP Host<input name="cfg_smtp_host" value="<?= e($cfg['smtp_host']??'') ?>"></label>
  <label>SMTP User<input name="cfg_smtp_user" value="<?= e($cfg['smtp_user']??'') ?>"></label>
  <label>SMTP Pass<input name="cfg_smtp_pass" value="<?= e($cfg['smtp_pass']??'') ?>"></label>
  <div><button class="ao-btn primary">Kaydet</button></div></form></div>
</div>
<div class="ao-grid two">
 <div class="ao-card"><h2>Test Mesajı Gönder</h2><form method="post" action="<?= url('admin/notifications/test') ?>" class="ao-form ao-form-grid"><?= csrf_field() ?><label>Tip<select name="channel_type"><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option><option value="email">Mail</option></select></label><label>Sağlayıcı<input name="provider" placeholder="iletimerkezi"></label><label>Alıcı<input name="recipient" placeholder="905xxxxxxxxx veya e-posta"></label><label style="grid-column:1/-1">Mesaj<textarea name="message">Ahost One test mesajı başarıyla gönderildi.</textarea></label><button class="ao-btn primary">Test Gönder</button></form></div>
 <div class="ao-card"><h2>Olay Şablonları</h2><div class="ao-table-wrap"><table class="ao-table"><thead><tr><th>Olay</th><th>Başlık</th><th>Durum</th></tr></thead><tbody><?php foreach($templates as $t): ?><tr><td><?= e(ao_val_v238($t,['event_key','template_key'])) ?></td><td><?= e(ao_val_v238($t,['title','subject'])) ?></td><td><?= (int)($t['is_active']??1)?'Aktif':'Pasif' ?></td></tr><?php endforeach; if(!$templates): ?><tr><td colspan="3">Şablon bulunamadı.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<div class="ao-card"><h2>Son Bildirim Logları</h2><div class="ao-table-wrap"><table class="ao-table"><thead><tr><th>Tarih</th><th>Tip</th><th>Sağlayıcı</th><th>Alıcı</th><th>Durum</th><th>Cevap</th></tr></thead><tbody><?php foreach($logs as $log): ?><tr><td><?= e($log['created_at']??'') ?></td><td><?= e(ao_val_v238($log,['channel_type','channel'])) ?></td><td><?= e($log['provider']??'') ?></td><td><?= e($log['recipient']??'') ?></td><td><?= e($log['status']??'') ?></td><td><?= e(mb_substr(ao_val_v238($log,['response_body','provider_response']),0,120)) ?></td></tr><?php endforeach; if(!$logs): ?><tr><td colspan="6">Henüz log yok.</td></tr><?php endif; ?></tbody></table></div></div>
