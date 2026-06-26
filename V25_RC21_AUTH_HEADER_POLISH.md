# Ahost One v25.0.0 RC21 — Auth Header Polish

Baz: RC20 (RC14 frontend + RC19 preview/version merge).

## Kapsam
- Müşteri/admin giriş ekranındaki `Kayıt Ol`, `Şifremi Unuttum`, `Siteye Dön` linkleri ayrıştırıldı.
- CSS yoksa bile linklerin bitişik görünmemesi için auth view dosyalarında anchor aralarına satır boşluğu eklendi.
- Public ana menüde logo sola hizalandı; logo ile menüler arasına dengeli boşluk verildi.
- Menü nav hizalaması merkez yerine sol başlangıçlı hale getirildi; aksiyonlar sağda korundu.
- Version / asset_version RC21 olarak güncellendi.

## Veri Güvenliği
- SQL migration yok.
- Seed yok.
- Fresh install canlıya uygulanmaz.
- Canlı müşteri/içerik/ürün/domain verilerine dokunulmadı.

## Yedek
`_backup/rc21-before-auth-header-polish/`
