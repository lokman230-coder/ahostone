# Ahost One v25.0.0 RC10 — Premium SaaS UI Rebuild

Baz sürüm: v25.0.0 RC9 — PHP Screenshot Bridge

## Amaç
RC10, parça parça CSS düzeltmesi yerine Ahost One arayüzünü tek premium SaaS tasarım sistemine bağlar. Mevcut işlevler, route/controller/model/fresh install ve güvenlik akışı korunur; UI/layout/CSS/JS katmanı standartlaştırılır.

## Eklenen tasarım sistemi

### Yeni CSS dosyaları
- `public/assets/css/ao-tokens.css`
- `public/assets/css/ao-components.css`
- `public/assets/css/ao-site-shell.css`
- `public/assets/css/ao-auth-shell.css`
- `public/assets/css/ao-admin-shell.css`
- `public/assets/css/ao-customer-shell.css`
- `public/assets/css/ao-legacy-compat.css`

### Yeni JS
- `public/assets/js/ao-ui.js`
  - Ortak dropdown davranışı
  - Ortak tab/sekme davranışı
  - Mobil shell yardımcı davranışları

### Yeni referans layout/component yapısı
- `app/Views/layouts/site-shell.php`
- `app/Views/layouts/auth-shell.php`
- `app/Views/layouts/admin-shell.php`
- `app/Views/layouts/customer-shell.php`
- `app/Views/components/page-header.php`
- `app/Views/components/stat-card.php`
- `app/Views/components/card.php`
- `app/Views/components/tabs.php`
- `app/Views/components/empty-state.php`

## Header/shell standardı

### Public/Auth header
Şu alanlar tek unified header sistemine bağlı kalır:
- Site header
- Admin login header
- Müşteri login header
- Register / forgot password / reset password ekranları

Kanonik dosyalar:
- `app/Views/shared/unified-header.php`
- `public/assets/css/ao-unified-header.css`
- RC10 görünüm katmanı: `ao-auth-shell.css`, `ao-site-shell.css`

### Admin panel shell
Admin iç panel kendi sidebar/topbar yapısını korur ama RC10 `ao-admin-shell.css` ile tek premium admin shell standardına bağlanır.

### Müşteri panel shell
Müşteri paneli unified public header + customer sidebar yapısını korur ama RC10 `ao-customer-shell.css` ile tek müşteri panel standardına bağlanır.

## CSS yükleme stratejisi
Eski CSS dosyaları silinmedi. Yeni RC10 katmanı eski/sayfa CSS'lerinden sonra yüklenir ve görünümü normalize eder.

Güncellenen dosyalar:
- `app/Views/shared/layout-head.php`
- `app/Views/admin/partials/header.php`
- `app/Views/admin/partials/footer.php`
- `config/config.php`
- `config/config.example.php`

## Yedek
Silme yapılmadı. Eski dosyalar şu klasöre yedeklendi:
- `_backup/rc10-before-premium-saas-ui-rebuild/`

## Fresh install / güvenlik
Korundu:
- Fresh install yapısı
- Admin guard
- CSRF
- Customer owner kontrolleri
- Recursive module SQL kararları
- RC9 QA & Scan Center Pro + PHP Screenshot Bridge

## Not
Bu RC10, tasarım sisteminin temel rebuild katmanıdır. Tüm view dosyalarını tek tek tamamen component render sistemine taşımadan önce risk azaltmak için eski view'lar korunmuş, görünüm yeni token/component/shell CSS katmanı ile normalize edilmiştir.
