<?php
/** Ahost One RC12 Single Public/Auth Header */
$aoHeaderContext = $aoHeaderContext ?? 'site';
$aoIsAuth = in_array($aoHeaderContext, ['auth','admin-login','customer-login'], true);
$aoIsClient = $aoHeaderContext === 'client';
$aoCustomer = function_exists('current_customer') ? current_customer() : null;
$aoCartCount = function_exists('ao_cart_count') ? (int)ao_cart_count() : 0;
$aoLogo = function_exists('ao_brand_logo_url') ? ao_brand_logo_url() : '';
$aoMenu = [
  ['Hosting','hosting'],
  ['Domain','domain'],
  ['Sunucular','vps'],
  ['Site Builder','sitebuilder'],
  ['Mobile Builder','mobilebuilder'],
  ['Marketplace','marketplace'],
  ['Blog','blog'],
  ['Destek','bilgi-bankasi'],
];
?>
<header class="ao-public-header" data-header-context="<?= e($aoHeaderContext) ?>">
  <div class="ao-public-header__inner">
    <a class="ao-brand" href="<?= url('') ?>" aria-label="Ahost One Ana Sayfa">
      <?php if($aoLogo): ?><img src="<?= e($aoLogo) ?>" alt="Ahost One" onerror="this.remove()"><?php endif; ?>
      <span>Ahost One</span>
    </a>
    <button class="ao-menu-toggle" type="button" data-ao-menu-toggle aria-expanded="false" aria-controls="aoPublicMenu">☰</button>
    <nav class="ao-public-nav" id="aoPublicMenu" data-ao-menu>
      <?php foreach($aoMenu as $item): ?>
        <a href="<?= url($item[1]) ?>"><?= e($item[0]) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="ao-public-actions">
      <?php if(!$aoIsAuth): ?>
        <a class="ao-icon-link" href="<?= url('cart') ?>" aria-label="Sepet">🛒<?php if($aoCartCount): ?><b><?= $aoCartCount ?></b><?php endif; ?></a>
      <?php endif; ?>
      <?php if($aoCustomer): ?>
        <a class="ao-btn ao-btn--ghost" href="<?= url('client') ?>">Panel</a>
      <?php else: ?>
        <a class="ao-btn ao-btn--primary" href="<?= url('client/login') ?>">Giriş</a>
      <?php endif; ?>
    </div>
  </div>
</header>
