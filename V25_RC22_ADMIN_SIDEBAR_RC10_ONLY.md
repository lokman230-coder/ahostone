# Ahost One v25.0.0 RC22 - Admin Sidebar RC10 Only

Baz: RC21 Auth/Header Polish.

Amaç: RC10'daki zarif sol admin sidebar görünümünü almak; RC10'daki Ayarlar, Ürün Grupları, tab sistemi veya admin içeriklerini taşımamak.

Yapılanlar:
- Admin sol sidebar HTML yapısı RC10 referansından seçilerek alındı.
- RC21 topbar, auth düzeltmeleri, public site görünümü ve RC19 preview/player birleşimi korundu.
- Sidebar CSS'i `ao-full-ui-reset.css` sonuna RC22 izole blok olarak eklendi.
- Aktif menü, alt menü, grup başlıkları, ikon/yazı dengesi ve scrollbar RC10 zarifliğine yaklaştırıldı.
- `version` ve `asset_version` `25.0.0-rc22` yapıldı.

Dokunulmadı:
- SQL migration yok.
- Seed/demo data yok.
- Fresh install canlıya uygulanmadı.
- Admin içerik sayfaları RC10'dan taşınmadı.
- Ayarlar ve Ürün Grupları sekme sistemi RC10'dan taşınmadı.

Canlı kontrol:
- CSS URL: `ao-full-ui-reset.css?v=25.0.0-rc22`
- Admin sidebar RC10 benzeri zarif görünmeli.
