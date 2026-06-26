# Ahost One v25.0.0 RC20 — RC14 Frontend + RC19 Preview Merge

## Amaç

RC14'te düzgün olan public/site ön yüzü korunmuştur. RC19'da düzgünleşen SiteBuilder/MobileBuilder önizleme ve radyo stream player parçaları seçilerek RC14 bazına taşınmıştır.

## Taşınan parçalar

- `app/Views/site/builders/sitebuilder-preview.php`
- `app/Views/site/builders/mobilebuilder-preview.php`
- `app/Views/site/builders/mobilebuilder-radio-demo.php`
- `app/Views/site/builders/gate.php`
- Radyo player JS: Stream URL alanı `<audio>` kaynağına bağlanır, play/pause ve volume çalışır.
- `version` ve `asset_version` senkronu: `25.0.0-rc20`
- `install.php` artık RC5 config üretmez.
- `database/fresh-install.sql` sürüm değeri RC20'dir.

## Taşınmayan parçalar

- RC19'un geniş public CSS/layout rewrite bloğu taşınmadı.
- Ürünler, hosting, sitebuilder ana ürün sayfası, marketplace gibi RC14'te düzgün olan public sayfalara müdahale edilmedi.

## Veri güvenliği

- SQL migration eklenmedi.
- Seed/demo data canlı veriye uygulanmamalıdır.
- Canlı müşteri, içerik, ürün, sipariş, domain kayıtları korunur.
- Güncellemede `install.php` çalıştırılmamalıdır.

## Kontrol

Canlıda CSS isteği şu olmalıdır:

`ao-full-ui-reset.css?v=25.0.0-rc20`
