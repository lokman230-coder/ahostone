# Ahost One v25.0.0 RC6 — Site Content UI Extension

Bu devam çalışması, RC6 site içerik ortaklaştırmasını dosya ekleme seviyesinden çıkarıp kalan public site ekranlarına da yayar.

## Korunanlar
- RC5 unified header yapısı korundu.
- Admin panel iç sidebar/topbar CSS yapısına dokunulmadı.
- Fresh install yapısı korundu.
- `schema.sql` ve `ssdhost_nexus.sql` tekrar eklenmedi.
- Eski CSS dosyaları silinmedi.

## Yedek
Değişiklik öncesi dosyalar şu klasöre yedeklendi:

`_backup/css-before-rc6-site-content-extension/`

Bu klasörde README ve değiştirilen site view/CSS dosyalarının kopyaları bulunur.

## Ortaklaştırılan / normalize edilen alanlar
- Ürün liste ve ürün landing sayfaları
- Hosting / VPS / Web Tasarım / Mobil Uygulama / SiteBuilder / MobileBuilder / Dijital Hizmetler landing ekranları
- Marketplace vitrin, kategori, ilan kartları ve teklif formu
- Domain merkezi ve domain checker
- Sepet / sipariş akışı
- Teklif formu
- Referanslar
- SEO analyzer
- Builder gate ve MobileBuilder radio demo public ekranı
- 404 ekranı
- Ana sayfa ürün katalog inline CSS’i ortak CSS’e taşındı

## CSS yaklaşımı
Ana ortak frontend içerik CSS dosyası:

`public/assets/css/ao-site-content.css`

Legacy CSS dosyaları silinmedi. Header içinde `ao-site-content.css`, legacy CSS dosyalarından sonra yüklenecek şekilde en sona alındı. Böylece geri dönüş kolaylığı korunurken ortak site içerik dili daha baskın uygulanır.

## Kontrol
- `app/Views/site` altındaki PHP view dosyaları `php -l` ile kontrol edildi.
- Site view’ları içinde kalan inline `<style>` blokları temizlendi.
