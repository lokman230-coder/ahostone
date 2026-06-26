<?php
$flash=get_flash(); if(function_exists('ao_v2410_ensure_schema')) ao_v2410_ensure_schema();
$bool=function($k,$d='1'){ return (string)admin_setting($k,$d)==='1'; };
?>
<link rel="stylesheet" href="<?= asset('css/support-center.css') ?>?v=24.5.0">
<?php if($flash): ?><div class="ao-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="ao-page-head"><div><span class="ao-kicker">Support Widget Pro</span><h2>Frontend Destek Widget Ayarları</h2><p>Site ön yüzündeki sağ alt canlı destek, AI cevap, bilgi bankası arama, WhatsApp, telefon ve ticket kısayollarını yönetin.</p></div><a class="ao-btn soft" href="<?= url('admin/support/live-chat') ?>">Canlı Sohbetler</a></div>
<form method="post" action="<?= url('admin/support/widget-settings/save') ?>" class="ao-card ao-v23-form">
  <?= csrf_field() ?>
  <label><span>Widget Aktif</span><input type="checkbox" name="support_widget_enabled" value="1" <?= $bool('support_widget_enabled')?'checked':'' ?>></label>
  <label><span>Canlı Sohbet</span><input type="checkbox" name="support_widget_live_chat_enabled" value="1" <?= $bool('support_widget_live_chat_enabled')?'checked':'' ?>></label>
  <label><span>AI Destek</span><input type="checkbox" name="support_widget_ai_enabled" value="1" <?= $bool('support_widget_ai_enabled')?'checked':'' ?>></label>
  <label><span>Bilgi Bankası Arama</span><input type="checkbox" name="support_widget_search_enabled" value="1" <?= $bool('support_widget_search_enabled')?'checked':'' ?>></label>
  <label><span>WhatsApp</span><input type="checkbox" name="support_widget_whatsapp_enabled" value="1" <?= $bool('support_widget_whatsapp_enabled')?'checked':'' ?>></label>
  <label><span>Telefon Arama</span><input type="checkbox" name="support_widget_phone_enabled" value="1" <?= $bool('support_widget_phone_enabled')?'checked':'' ?>></label>
  <label><span>Ticket Aç</span><input type="checkbox" name="support_widget_ticket_enabled" value="1" <?= $bool('support_widget_ticket_enabled')?'checked':'' ?>></label>
  <label><span>Mesai Saati Kontrolü</span><input type="checkbox" name="support_hours_enabled" value="1" <?= $bool('support_hours_enabled','0')?'checked':'' ?>></label>
  <label>WhatsApp Numarası<input name="support_whatsapp_number" value="<?= e(admin_setting('support_whatsapp_number','')) ?>" placeholder="905551112233"></label>
  <label>Telefon Numarası<input name="support_call_number" value="<?= e(admin_setting('support_call_number','')) ?>" placeholder="905551112233"></label>
  <label>Mesai Başlangıç<input name="support_hours_start" value="<?= e(admin_setting('support_hours_start','09:00')) ?>" placeholder="09:00"></label>
  <label>Mesai Bitiş<input name="support_hours_end" value="<?= e(admin_setting('support_hours_end','18:00')) ?>" placeholder="18:00"></label>
  <label class="full">Karşılama Metni<input name="support_widget_greeting" value="<?= e(admin_setting('support_widget_greeting','Merhaba 👋 Size nasıl yardımcı olabiliriz?')) ?>"></label>
  <label>Konum<select name="support_widget_position"><option value="right" <?= admin_setting('support_widget_position','right')==='right'?'selected':'' ?>>Sağ Alt</option><option value="left" <?= admin_setting('support_widget_position','right')==='left'?'selected':'' ?>>Sol Alt</option></select></label>
  <div class="full"><button class="ao-btn">Kaydet</button></div>
</form>
<div class="ao-card"><h3>Not</h3><p>AI cevap sistemi bilgi bankası makalelerinde arama yapar. Cevap bulunamazsa ziyaretçiyi canlı temsilciye veya ticket açmaya yönlendirir.</p></div>
