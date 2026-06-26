# Ahost One v25.0.0 RC24

RC23 bazlı güvenli admin sidebar rollback paketi. RC22/RC23 RC10 gruplu sidebar denemesi geri alındı; RC21 sade sidebar yapısı ve RC23 dashboard düzeni korundu.

# Ahost One v25.0.0 RC20

## RC20 stratejisi

Bu paket, **RC14 public/site görünümünü baz alır**. RC19'dan yalnızca gerekli parçalar seçilerek taşınmıştır:

- SiteBuilder public preview düzeltmesi
- MobileBuilder public preview düzeltmesi
- Radyo uygulaması preview/player düzeltmesi
- Stream URL girildiğinde telefon önizlemesindeki play tuşunun gerçek yayını çalması
- `version` / `asset_version` senkronu
- `install.php` dosyasının eski RC5 config üretmemesi
- `fresh-install.sql` sürüm değerinin güncel olması

RC19'daki geniş public CSS/layout değişiklikleri bu pakete taşınmamıştır. Amaç, RC14'te düzgün olan site ön yüzünü koruyup sadece önizleme/player ve sürüm senkron sorunlarını düzeltmektir.

## Canlı sistem uyarısı

Canlıda müşteri, içerik, ürün, sipariş, domain ve Türkiye verileri varsa `install.php` çalıştırılmamalıdır. Güncelleme için dosyaları yükleyin, mevcut `config/config.php` ve veritabanı verilerini koruyun.

Canlı güncellemeden sonra DevTools > Network üzerinde ana CSS isteği şöyle görünmelidir:

```text
ao-full-ui-reset.css?v=25.0.0-rc24
```

Hâlâ `rc5`, `rc14`, `rc18` veya `rc19` görünüyorsa sunucuda eski config/dosya/cache çalışıyor demektir.

## Koruma kuralları

- SQL migration eklenmedi.
- Seed/demo data canlı veriye uygulanmamalıdır.
- Fresh install sadece boş kurulum içindir.
- Mevcut müşteri/içerik/ürün/domain verileri file-only güncelleme ile korunur.


## RC22 Auth/Header Polish
- RC20 bazlıdır.
- Müşteri/admin giriş linkleri ayrıştırıldı.
- Public header logo sola hizalandı, logo-menü boşluğu dengelendi.
- SQL/migration/seed yoktur.


## v25.0.0 RC23

RC22 baz alınarak sadece admin dashboard kart/grid düzeni v24.11.7 referansına yaklaştırıldı. Public site, preview/player, auth/header ve RC10 sidebar birleşimi korunur. SQL/migration/seed yoktur; canlı veriye dokunmaz.
