</main><footer class="site-footer">© <?= date('Y') ?> Ahost One - Tüm hakları saklıdır</footer>
<?php
$aoWidgetEnabled = (string)admin_setting('support_widget_enabled','1') === '1';
$aoSupportPhone = preg_replace('/\D+/', '', (string)admin_setting('support_call_number',''));
$aoSupportWhats = preg_replace('/\D+/', '', (string)admin_setting('support_whatsapp_number',''));
$aoSupportAi = (string)admin_setting('support_widget_ai_enabled', admin_setting('support_ai_enabled','1')) === '1';
$aoSupportSearch = (string)admin_setting('support_widget_search_enabled','1') === '1';
$aoSupportLive = (string)admin_setting('support_widget_live_chat_enabled','1') === '1';
$aoSupportTicket = (string)admin_setting('support_widget_ticket_enabled','1') === '1';
$aoSupportWhatsOn = (string)admin_setting('support_widget_whatsapp_enabled','1') === '1';
$aoSupportPhoneOn = (string)admin_setting('support_widget_phone_enabled','1') === '1';
$aoGreeting = admin_setting('support_widget_greeting','Merhaba 👋 Size nasıl yardımcı olabiliriz?');
$aoHoursEnabled = (string)admin_setting('support_hours_enabled','0') === '1';
$aoWhatsActive = true;
if ($aoHoursEnabled) { $now=date('H:i'); $aoWhatsActive = ($now >= admin_setting('support_hours_start','09:00') && $now <= admin_setting('support_hours_end','18:00')); }
$aoCust = function_exists('current_customer') ? current_customer() : null;
?>
<?php if($aoWidgetEnabled): ?>
<div class="ao-support-widget-pro <?= admin_setting('support_widget_position','right')==='left'?'left':'right' ?>" aria-label="Hızlı destek">
  <button type="button" class="ao-support-main" data-support-open><span>💬</span><strong>Destek</strong></button>
  <div class="ao-support-quick">
    <?php if($aoSupportPhone && $aoSupportPhoneOn): ?><a class="call" href="tel:<?= e($aoSupportPhone) ?>" title="Bizi Ara">📞</a><?php endif; ?>
    <?php if($aoSupportWhats && $aoSupportWhatsOn): ?><a class="wa <?= $aoWhatsActive?'':'disabled' ?>" <?= $aoWhatsActive?'target="_blank" rel="noopener" href="https://wa.me/'.e($aoSupportWhats).'"':'href="#" aria-disabled="true"' ?> title="WhatsApp">🟢</a><?php endif; ?>
  </div>
</div>
<div class="ao-support-modal ao-support-pro-modal" data-support-modal hidden>
  <div class="ao-support-box ao-support-pro-box">
    <button type="button" class="ao-support-close" data-support-close aria-label="Kapat">×</button>
    <div class="ao-support-pro-head"><div><strong>AI Destek Merkezi</strong><p><?= e($aoGreeting) ?></p></div><span>Canlı + AI destek</span></div>
    <div class="ao-support-tabs" role="tablist">
      <?php if($aoSupportSearch): ?><button type="button" class="active" data-support-tab="search">🔍 Ara</button><?php endif; ?>
      <?php if($aoSupportAi): ?><button type="button" data-support-tab="ai">🤖 AI Sor</button><?php endif; ?>
      <?php if($aoSupportLive): ?><button type="button" data-support-tab="live">💬 Canlı Sohbet</button><?php endif; ?>
      <?php if($aoSupportTicket): ?><a href="<?= url('client/support') ?>">🎫 Ticket</a><?php endif; ?>
      <?php if($aoSupportWhats && $aoSupportWhatsOn && $aoWhatsActive): ?><a target="_blank" rel="noopener" href="https://wa.me/<?= e($aoSupportWhats) ?>">📱 WhatsApp</a><?php endif; ?>
      <?php if($aoSupportPhone && $aoSupportPhoneOn): ?><a href="tel:<?= e($aoSupportPhone) ?>">📞 Ara</a><?php endif; ?>
    </div>
    <?php if($aoSupportSearch): ?>
    <section class="ao-support-pane active" data-support-pane="search">
      <label>Bilgi Bankasında Ara<input data-support-search-input placeholder="Domain, hosting, SSL, ödeme..."></label>
      <div class="ao-support-results" data-support-search-results><p>Bir kelime yazıp arama yapın.</p></div>
    </section>
    <?php endif; ?>
    <?php if($aoSupportAi): ?>
    <section class="ao-support-pane" data-support-pane="ai">
      <form data-support-ai-form><?= csrf_field() ?><label>Yapay Zekaya Sor<textarea name="q" placeholder="Sorunuzu yazın..."></textarea></label><button class="ao-support-submit" type="submit">Cevapla</button></form>
      <div class="ao-support-results" data-support-ai-result><p>AI destek bilgi bankası içeriklerinden cevap arar.</p></div>
    </section>
    <?php endif; ?>
    <?php if($aoSupportLive): ?>
    <section class="ao-support-pane" data-support-pane="live">
      <form method="post" action="<?= url('support/live-chat/start') ?>" class="ao-support-form ao-support-live-form">
        <?= csrf_field() ?>
        <div class="ao-support-depts wide">
          <?php foreach(['Teknik Destek','Satış Öncesi','Muhasebe','Domain','Hosting','SiteBuilder / MobileBuilder'] as $i=>$d): ?>
          <label><input type="radio" name="department" value="<?= e($d) ?>" <?= $i===0?'checked':'' ?>><?= e($d) ?></label>
          <?php endforeach; ?>
        </div>
        <label>Ad Soyad<input name="name" required value="<?= e(trim(($aoCust['first_name']??'').' '.($aoCust['last_name']??''))) ?>"></label>
        <label>E-posta<input type="email" name="email" required value="<?= e($aoCust['email']??'') ?>"></label>
        <label>Telefon<input name="phone" value="<?= e($aoCust['phone']??'') ?>"></label>
        <label>Konu<input name="subject" required value="Canlı Sohbet" placeholder="Kısa konu başlığı"></label>
        <label class="wide">Mesaj<textarea name="message" required placeholder="Sorununuzu detaylı yazın..."></textarea></label>
        <button class="ao-support-submit" type="submit">Canlı Sohbet Başlat</button>
      </form>
    </section>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<script>window.AHOST_BASE_URL=<?= json_encode(rtrim(app_base_path(), '/')) ?>;</script>
<script src="<?= asset('js/front/support-widget-pro.js') ?>?v=24.5.0"></script>

<script id="ao-client-dropdown-v2202">
(function(){
  const dropdowns=document.querySelectorAll('.client-login-dropdown');
  dropdowns.forEach(dd=>{
    const btn=dd.querySelector('.client-login-toggle');
    if(!btn) return;
    btn.addEventListener('click',function(e){
      e.preventDefault(); e.stopPropagation();
      const isOpen=dd.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      dropdowns.forEach(other=>{ if(other!==dd){ other.classList.remove('open'); const b=other.querySelector('.client-login-toggle'); if(b)b.setAttribute('aria-expanded','false'); } });
    });
    dd.addEventListener('click',e=>e.stopPropagation());
  });
  document.addEventListener('click',()=>dropdowns.forEach(dd=>{dd.classList.remove('open'); const b=dd.querySelector('.client-login-toggle'); if(b)b.setAttribute('aria-expanded','false');}));
  document.addEventListener('keydown',e=>{ if(e.key==='Escape') dropdowns.forEach(dd=>{dd.classList.remove('open'); const b=dd.querySelector('.client-login-toggle'); if(b)b.setAttribute('aria-expanded','false');}); });
})();
</script><script src="<?= url('public/assets/js/front/domain-tools.js') ?>?v=24.5.0"></script>
<script src="<?= url('public/assets/js/domain-popup.js') ?>?v=24.5.0"></script><script id="ao-currency-live-v186">
(function(){
 const rate=parseFloat(document.querySelector('.fx-rate')?.textContent?.match(/[0-9]+(?:\.[0-9]+)?/)?.[0]||'45');
 const fmt=(v,c)=>c==='USD'?'$'+(v/rate).toFixed(2):'₺'+Number(v).toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2});
 function apply(cur){localStorage.setItem('ao_currency',cur);document.querySelectorAll('#aoCurrency').forEach(s=>s.value=cur);document.querySelectorAll('[data-price-base]').forEach(el=>{let base=parseFloat(el.dataset.priceBase||'0');el.textContent=fmt(base,cur);el.classList.add('pulse');setTimeout(()=>el.classList.remove('pulse'),220);});}
 document.querySelectorAll('#aoCurrency').forEach(s=>{s.value=localStorage.getItem('ao_currency')||'TRY';s.addEventListener('change',()=>apply(s.value));}); apply(localStorage.getItem('ao_currency')||'TRY');
})();</script><?php if (function_exists('current_admin') && current_admin()): $bp=ao_builder_context_from_route(trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')); ?>
<div class="ao-floating-edit"><details><summary>✏ <span class="ao-fab-label">Düzenle</span></summary><div class="ao-floating-edit-menu"><small><?= e(strtoupper($bp['target'].' / '.$bp['template'])) ?></small><a href="#" class="ao-inline-edit-start">Bu sayfayı düzenle</a><a href="<?= url('admin/theme-center/editor') ?>">Tema düzenle</a><a href="<?= url('admin/builder-pro?target='.$bp['target'].'&template='.$bp['template'].'&device=mobile') ?>">Mobil görünüm</a><a href="<?= url('admin/theme-center/themes') ?>">Temalar</a></div></details></div>
<?php endif; ?><nav class="mobile-bottom-nav"><a class="mobile-nav-item<?= ao_mobile_nav_active_class('home') ?>" href="<?= url('') ?>"><b>⌂</b>Ana</a><a class="mobile-nav-item<?= ao_mobile_nav_active_class('domain') ?>" href="<?= url('domain') ?>"><b>🌐</b>Domain</a><a class="mobile-nav-item<?= ao_mobile_nav_active_class('package') ?>" href="<?= url('hosting') ?>"><b>📦</b>Paket</a><button type="button" class="mobile-nav-item mobile-support-toggle<?= ao_mobile_nav_active_class('support') ?>" data-mobile-support><b>🎧</b>Destek</button><a class="mobile-nav-item<?= ao_mobile_nav_active_class('panel') ?>" href="<?= url('client') ?>"><b>👤</b>Panel</a></nav>
<div class="mobile-support-panel" data-mobile-support-panel hidden>
  <?php if($aoSupportSearch): ?><button type="button" data-support-open data-mobile-tab="search">Bilgi Bankasında Ara</button><?php endif; ?>
  <?php if($aoSupportAi): ?><button type="button" data-support-open data-mobile-tab="ai">AI Destek</button><?php endif; ?>
  <?php if($aoSupportLive): ?><button type="button" data-support-open data-mobile-tab="live">Canlı Sohbet</button><?php endif; ?>
  <?php if($aoSupportWhats && $aoWhatsActive): ?><a target="_blank" rel="noopener" href="https://wa.me/<?= e($aoSupportWhats) ?>">WhatsApp</a><?php elseif($aoSupportWhats): ?><span>WhatsApp mesai dışında</span><?php endif; ?>
  <?php if($aoSupportPhone): ?><a href="tel:<?= e($aoSupportPhone) ?>">Ara</a><?php endif; ?>
  <?php if($aoSupportTicket): ?><a href="<?= url('client/support') ?>">Ticket Aç</a><?php endif; ?>
</div><script src="<?= url('public/assets/js/inline-builder-v1881.js') ?>?v=24.5.0"></script></body></html>