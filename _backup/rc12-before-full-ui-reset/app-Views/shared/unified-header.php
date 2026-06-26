<?php
// Ahost One Unified Header/Menu Component v25.0.0 RC5 - tek PHP header
$aoHeaderContext = $aoHeaderContext ?? 'site';
$aoCustomer = function_exists('current_customer') ? current_customer() : null;
$aoAdmin = function_exists('current_admin') ? current_admin() : null;
$aoUnread = function_exists('ao_customer_unread_notifications_count') ? ao_customer_unread_notifications_count() : 0;
$aoCartCount = function_exists('ao_cart_count') ? ao_cart_count() : 0;
$aoAnnouncement = function_exists('ao_active_site_announcement') ? ao_active_site_announcement() : null;
$aoSocials = function_exists('ao_social_links') ? ao_social_links() : [];
$aoLanguages = function_exists('ao_language_options') ? ao_language_options() : ['tr'=>'Türkçe'];
$aoLangMeta = function_exists('ao_available_language_meta') ? ao_available_language_meta() : ['tr'=>['flag'=>'🇹🇷','label'=>'Türkçe']];
$aoCurrentLang = function_exists('ao_current_language') ? ao_current_language() : 'tr';
$aoIsAdminLogin = $aoHeaderContext === 'admin-login';
$aoIsClient = $aoHeaderContext === 'client';
$aoImpersonating = !empty($_SESSION['admin_impersonating_customer_id']);
$aoCurrencyEnabled = (string)admin_setting('topbar_currency_enabled','1') === '1';
$aoUsdTryRate = (float)(function_exists('ao_currency_rate') ? ao_currency_rate('USD','TRY') : admin_setting('usd_try_rate','45'));
if ($aoUsdTryRate <= 0) $aoUsdTryRate = 45;
$aoSiteMenu = [
    ['label'=>function_exists('__t')?__t('site.menu.hosting','Hosting'):'Hosting','url'=>'hosting','children'=>[['label'=>'Web Hosting','url'=>'hosting'],['label'=>'WordPress Hosting','url'=>'urunler'],['label'=>'VPS / VDS','url'=>'vps']]],
    ['label'=>function_exists('__t')?__t('site.menu.domain','Domain'):'Domain','url'=>'domain','children'=>[]],
    ['label'=>function_exists('__t')?__t('site.menu.site_builder','Site Builder'):'Site Builder','url'=>'sitebuilder','children'=>[]],
    ['label'=>function_exists('__t')?__t('site.menu.mobile_builder','Mobile Builder'):'Mobile Builder','url'=>'mobilebuilder','children'=>[]],
    ['label'=>function_exists('__t')?__t('site.menu.marketplace','Marketplace'):'Marketplace','url'=>'marketplace','children'=>[['label'=>'Tema Marketplace','url'=>'marketplace'],['label'=>'Modül Marketplace','url'=>'marketplace'],['label'=>'Şablonlar','url'=>'marketplace']]],
    ['label'=>function_exists('__t')?__t('site.menu.support','Destek'):'Destek','url'=>'bilgi-bankasi','children'=>[['label'=>'Bilgi Bankası','url'=>'bilgi-bankasi'],['label'=>'Ticket Aç','url'=>'client/support']]],
];
if (!function_exists('ao_social_icon_svg_v2468')) {
function ao_social_icon_svg_v2468($label){
    $k = mb_strtolower((string)$label,'UTF-8');
    if(str_contains($k,'instagram')) return '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.2"/><circle cx="17" cy="7" r=".8"/></svg>';
    if($k==='x' || str_contains($k,'twitter')) return '<svg viewBox="0 0 24 24"><path d="M5 5l14 14M19 5L5 19"/></svg>';
    if(str_contains($k,'youtube')) return '<svg viewBox="0 0 24 24"><path d="M4 9s0-3 3-3h10c3 0 3 3 3 3v6s0 3-3 3H7c-3 0-3-3-3-3z"/><path d="M10 9l5 3-5 3z"/></svg>';
    if(str_contains($k,'linkedin')) return '<svg viewBox="0 0 24 24"><path d="M6 10v8M6 6v.1M10 18v-8M10 13c0-2 1-3 3-3s3 1.5 3 4v4"/></svg>';
    if(str_contains($k,'whatsapp')) return '<svg viewBox="0 0 24 24"><path d="M4 20l1.2-4A8 8 0 1 1 8 18.2z"/><path d="M9 8c.5 3 2 5 5 6"/></svg>';
    if(str_contains($k,'telegram')) return '<svg viewBox="0 0 24 24"><path d="M21 4L3 11l6 2 2 6z"/><path d="M9 13l7-5"/></svg>';
    return '<svg viewBox="0 0 24 24"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v2H6v4h3v5h4v-5h3l1-4h-4V9c0-.6.4-1 1-1z"/></svg>';
}
}
$aoCustomerName = $aoCustomer ? trim((string)($aoCustomer['name'] ?? (($aoCustomer['first_name'] ?? '').' '.($aoCustomer['last_name'] ?? '')))) : '';
if ($aoCustomerName === '') $aoCustomerName = 'Müşteri';
?>
<header class="ao-unified-header" data-context="<?= e($aoHeaderContext) ?>">
  <div class="ao-utility-bar">
    <div class="ao-utility-inner">
      <div class="ao-utility-message"><span class="ao-new-badge">Yeni</span><?php if($aoAnnouncement && !$aoIsAdminLogin): ?><?= !empty($aoAnnouncement['url'])?'<a href="'.e($aoAnnouncement['url']).'">'.e($aoAnnouncement['text'] ?? '').'</a>':'<span>'.e($aoAnnouncement['text'] ?? '').'</span>' ?><?php else: ?><span>Sitelerinizi mobile taşıyın. <a href="<?= url('mobil-uygulama') ?>">İncele</a></span><?php endif; ?></div>
      <div class="ao-utility-actions">
        <?php if($aoSocials): ?><span class="ao-follow-label">Bizi Takip Edin:</span><div class="ao-social-icons ao-social-top" aria-label="Sosyal ağlar"><?php foreach(array_slice($aoSocials,0,4) as $soc): ?><a href="<?= e($soc['url']) ?>" target="_blank" rel="noopener" title="<?= e($soc['label']) ?>" aria-label="<?= e($soc['label']) ?>"><?= ao_social_icon_svg_v2468($soc['label'] ?? '') ?></a><?php endforeach; ?></div><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="ao-unified-navwrap">
    <a class="ao-unified-brand" href="<?= url('') ?>"><img src="<?= e(ao_brand_logo_url()) ?>" alt="Ahost One" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'"><strong style="display:none">Ahost One</strong></a>
    <button type="button" class="ao-unified-toggle" data-ao-menu-toggle aria-expanded="false" aria-controls="aoUnifiedMenu" aria-label="Ana menüyü aç">☰</button>
    <nav class="ao-unified-nav" id="aoUnifiedMenu" data-ao-menu aria-label="Ana menü">
      <div class="ao-nav-links"><?php foreach($aoSiteMenu as $mi): $children=$mi['children'] ?? []; ?><div class="ao-unified-item <?= $children?'has-children':'' ?>"><a href="<?= ($mi['url'] ?? '')==='#' ? '#' : e(function_exists('ao_menu_url_v222') ? ao_menu_url_v222($mi['url'] ?? '') : url($mi['url'] ?? '')) ?>" <?= $children?'aria-haspopup="true" aria-expanded="false"':'' ?>><?= e($mi['label'] ?? '') ?><?= $children?'<span class="ao-nav-dot" aria-hidden="true"></span>':'' ?></a><?php if($children): ?><div class="ao-unified-submenu"><?php foreach($children as $ch): ?><a href="<?= e(function_exists('ao_menu_url_v222') ? ao_menu_url_v222($ch['url'] ?? '') : url($ch['url'] ?? '')) ?>"><?= e($ch['label'] ?? '') ?></a><?php endforeach; ?></div><?php endif; ?></div><?php endforeach; ?></div>
      <div class="ao-unified-actions">
        <?php if($aoCurrencyEnabled): ?><div class="ao-currency-toggle" title="Para birimi"><button type="button" data-ao-currency="USD" aria-label="USD">$</button><button type="button" data-ao-currency="TRY" aria-label="TRY">₺</button></div><?php endif; ?>
        <a class="ao-cart-action" href="<?= url('cart') ?>" title="Sepet" aria-label="Sepet"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.4 11.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 7H6M10 21h.01M18 21h.01"/></svg><?= $aoCartCount>0?'<b>'.(int)$aoCartCount.'</b>':'' ?></a>
        <?php if((string)admin_setting('language_menu_enabled','1')==='1' && count($aoLanguages)>1): ?><div class="ao-lang-dropdown ao-flag-lang"><button type="button" class="ao-lang-btn" aria-label="Dil seç"><span class="ao-flag-round"><?= e($aoLangMeta[$aoCurrentLang]['flag'] ?? '🌐') ?></span> ▾</button><div class="ao-lang-menu"><?php foreach($aoLanguages as $code=>$label): ?><a href="?lang=<?= e($code) ?>"><span class="ao-flag-round"><?= e($aoLangMeta[$code]['flag'] ?? '🌐') ?></span><span><?= e($label) ?></span></a><?php endforeach; ?></div></div><?php endif; ?>
        <?php if($aoCustomer): ?>
          <span class="ao-welcome-text"><?= e(function_exists('__t')?__t('account.welcome','Hoş Geldin'):'Hoş Geldin') ?>, <?= e($aoCustomerName) ?></span>
          <div class="ao-account-dropdown"><button type="button" class="ao-main-action ao-account-btn"><?= e(function_exists('__t')?__t('account.my_account','Hesabım'):'Hesabım') ?> ▾</button><div class="ao-account-menu"><a href="<?= url('client') ?>">Panelim</a><a href="<?= url('client/services') ?>">Hizmetlerim</a><a href="<?= url('client/domains') ?>">Domainlerim</a><a href="<?= url('client/invoices') ?>">Faturalarım</a><a href="<?= url('client/support') ?>">Destek Taleplerim</a><a href="<?= url('client/profile') ?>">Profilim</a><a href="<?= url('client/security') ?>">Hesap Ayarları</a><a href="<?= url('client/notifications') ?>">Bildirimlerim<?= $aoUnread>0?' ('.(int)$aoUnread.')':'' ?></a><a class="danger" href="<?= url('client/logout') ?>">Çıkış Yap</a></div></div>
        <?php else: ?>
          <div class="ao-login-dropdown"><button type="button" class="ao-main-action ao-login-btn"><?= e(function_exists('__t')?__t('auth.login','Giriş'):'Giriş') ?> ▾</button><div class="ao-login-menu"><form method="post" action="<?= url('client/login') ?>"><?= csrf_field() ?><label><?= e(function_exists('__t')?__t('auth.email','E-posta'):'E-posta') ?><input type="email" name="email" required></label><label><?= e(function_exists('__t')?__t('auth.password','Şifre'):'Şifre') ?><input type="password" name="password" required></label><label class="remember"><input type="checkbox" name="remember" value="1"> <?= e(function_exists('__t')?__t('auth.remember','Beni hatırla'):'Beni hatırla') ?></label><button type="submit"><?= e(function_exists('__t')?__t('auth.submit','Giriş Yap'):'Giriş Yap') ?></button><div><a href="<?= url('client/forgot-password') ?>"><?= e(function_exists('__t')?__t('auth.forgot','Şifremi unuttum'):'Şifremi unuttum') ?></a><a href="<?= url('client/register') ?>"><?= e(function_exists('__t')?__t('auth.register','Yeni hesap oluştur'):'Yeni hesap oluştur') ?></a></div></form></div></div>
        <?php endif; ?>
      </div>
    </nav>
  </div>
  <?php if($aoImpersonating): ?><div class="ao-impersonation-alert"><strong>ADMIN IMPERSONATION MODE</strong><span>Şu anda müşteri panelindesiniz.</span><a href="<?= url('admin/customers/stop-login-as') ?>">Admin profiline dön</a></div><?php endif; ?>
</header>
<script>
(function(){
  document.addEventListener('click', function(e){
    var btn=e.target.closest('[data-ao-menu-toggle]');
    if(btn){var menu=document.querySelector('[data-ao-menu]'); if(!menu) return; var open=menu.classList.toggle('is-open'); btn.setAttribute('aria-expanded', open?'true':'false'); return;}
    var parentLink=e.target.closest('.ao-unified-item.has-children>a');
    if(parentLink && window.matchMedia('(max-width:1180px)').matches){e.preventDefault(); var parent=parentLink.parentElement; var expanded=parent.classList.toggle('is-expanded'); parentLink.setAttribute('aria-expanded',expanded?'true':'false');}
  });
  var currencyButtons=document.querySelectorAll('button[data-ao-currency]'); var rate=<?= json_encode($aoUsdTryRate) ?>;
  function formatPrice(value,currency){return currency==='USD'?'$'+(value/rate).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}):'₺'+value.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2});}
  function applyCurrency(currency){try{localStorage.setItem('ao_currency',currency);}catch(_){} currencyButtons.forEach(function(button){var active=button.getAttribute('data-ao-currency')===currency; button.classList.toggle('is-active',active); button.setAttribute('aria-pressed',active?'true':'false');}); document.querySelectorAll('[data-price-base]').forEach(function(el){var value=parseFloat(el.getAttribute('data-price-base')||'0'); if(Number.isFinite(value)) el.textContent=formatPrice(value,currency);});}
  currencyButtons.forEach(function(button){button.addEventListener('click',function(){applyCurrency(button.getAttribute('data-ao-currency'));});});
  var savedCurrency='USD'; try{savedCurrency=localStorage.getItem('ao_currency')||'USD';}catch(_){} applyCurrency(savedCurrency==='TRY'?'TRY':'USD');
})();
</script>
