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
ao-full-ui-reset.css?v=25.0.0-rc20
```

Hâlâ `rc5`, `rc14`, `rc18` veya `rc19` görünüyorsa sunucuda eski config/dosya/cache çalışıyor demektir.

## Koruma kuralları

- SQL migration eklenmedi.
- Seed/demo data canlı veriye uygulanmamalıdır.
- Fresh install sadece boş kurulum içindir.
- Mevcut müşteri/içerik/ürün/domain verileri file-only güncelleme ile korunur.


## RC21 Auth/Header Polish
- RC20 bazlıdır.
- Müşteri/admin giriş linkleri ayrıştırıldı.
- Public header logo sola hizalandı, logo-menü boşluğu dengelendi.
- SQL/migration/seed yoktur.
