# Ahost One v25.0.0 RC13 — Body Content Stabilization

Bu sürümde RC12'de header toparlanmasına rağmen site ana gövdesi, admin dashboard ve müşteri panel içeriklerinde görülen ham HTML / üst üste binen kart / çıplak link / eksik grid sorunları için aktif görünüm katmanı güçlendirildi.

## Odak
- Header korunmuştur.
- Site ana sayfa gövdesi premium hero, domain arama kartı, TLD chipleri, ürün kartları ve istatistik kartlarıyla stabilize edilmiştir.
- Admin dashboard eski v21/v22/v2470 class yapıları için tek CSS uyumluluk katmanı eklenmiştir.
- Kart, KPI, panel, tablo, boş durum, hızlı işlem, rozet ve form görünümleri tek standarda bağlanmıştır.
- Müşteri panel kart/tablo/gövde bileşenleri için uyumluluk stilleri eklenmiştir.

## Not
Bu sürüm görünüm dosyalarını yeniden parçalamaz; tek aktif CSS olan `ao-full-ui-reset.css` içinde body/content component stabilizasyonu yapar.
