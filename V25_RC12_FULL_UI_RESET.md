# Ahost One v25.0.0 RC12 — Full UI Reset

Amaç: RC11 sonrası devam eden görünüm bozukluklarını eski görsel katmanı yamalayarak değil, aktif UI/CSS katmanını sıfırdan temizleyerek çözmek.

## Uygulananlar

- Aktif görünüm CSS’i tek dosyaya indirildi:
  - `public/assets/css/ao-full-ui-reset.css`
- Site/public/auth header tek dosyadan çalışacak şekilde sadeleştirildi:
  - `app/Views/shared/unified-header.php`
- Tüm standart sayfalar tek head yükleyicisine bağlandı:
  - `app/Views/shared/layout-head.php`
- Eski CSS dosyaları aktif alandan kaldırıldı ve yedeğe taşındı.
- Tema CSS’leri aktif alandan kaldırıldı ve yedeğe taşındı.
- View içindeki eski direct CSS linkleri kaldırıldı.
- View içindeki eski inline `<style>` blokları kaldırıldı.
- Site header, auth header, admin shell, customer shell yeniden sade premium SaaS temel görünümle yazıldı.
- Admin panel sidebar/topbar temizlendi.
- Müşteri panel sidebar/topbar temizlendi.
- Public site footer/mobile nav sadeleştirildi.
- Ortak tab/dropdown/mobile menü davranışı `ao-ui.js` içine alındı.

## Korunanlar

- Backend işlevleri
- Route/controller/model yapısı
- Veritabanı ve kurulum yapısı
- Admin guard / customer guard
- CSRF ve owner kontrol mantığı
- QA & Scan Center, Build Center, API entegrasyonları ve modüllerin işlevsel sayfa yapıları

## Yedek

- `_backup/rc12-before-full-ui-reset/`

Bu klasörde eski `app/Views`, `public/assets/css` ve `themes` yedeği bulunur.

## Not

Bu sürüm, görünüm çakışmalarını sonlandırmak için eski CSS ve inline stilleri aktif yükten çıkarmıştır. Sayfa işlevleri korunarak görsel temel tek CSS sistemine alınmıştır.
