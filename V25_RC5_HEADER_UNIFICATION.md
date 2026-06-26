# Ahost One v25.0.0 RC5 — Header Unification

Bu paket RC4 üzerine hazırlanmıştır.

## Yapılanlar

- Site ana menü header, admin giriş header, müşteri giriş header ve müşteri panel üst header tek PHP dosyasına bağlandı:
  - `app/Views/shared/unified-header.php`
- Header CSS tek kanonik dosyaya alındı:
  - `public/assets/css/ao-unified-header.css`
- Ortak head yükleyicisi eklendi:
  - `app/Views/shared/layout-head.php`
- Site, auth ve customer partial header dosyaları bu ortak head/header yapısını kullanacak şekilde düzenlendi.
- Eski `ao-premium-header.css` dosyası, dışarıdan eski referans kalırsa kırılma yaşanmaması için compatibility wrapper olarak bırakıldı.
- Admin panelin iç sidebar/topbar layout'u ayrı tutuldu. Bu bölüm public site header değil, yönetim paneli uygulama kabuğudur.

## Kontrol Edilecek Sayfalar

1. `/` ana sayfa header
2. `/hosting`, `/domain`, `/marketplace` header
3. `/admin/login` admin giriş header
4. `/admin/forgot-password`, `/admin/security-question`, `/admin/reset-password` auth header
5. `/client/login`, `/client/register` header
6. `/client` müşteri panel üst header

## Not

Bu çalışma tüm projeyi tek CSS'e indirmez. Sadece header katmanı tek CSS / tek PHP haline getirilmiştir. Sayfa, panel ve builder gövde CSS'leri ayrı kalır.
