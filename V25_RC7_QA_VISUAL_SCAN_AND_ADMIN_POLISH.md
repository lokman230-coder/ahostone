# Ahost One v25.0.0 RC7 — QA Visual Scan & Admin Polish

Baz sürüm: v25.0.0 RC6 — Site Content UI Unification

## Eklenenler
- Yeni Admin modülü: **QA Görsel Tarama Merkezi**
  - `admin/qa-visual-scan`
  - Site, admin ve müşteri paneli route listesini raporlar.
  - HTML rapor üretir.
  - Node.js + Playwright varsa masaüstü ve mobil ekran görüntüsü alır.
  - Çıktı klasörü: `storage/reports/qa-scans/{tarih}`
  - CLI: `node tools/qa-visual-scan.js --base=https://siteadresiniz.com`

## Düzeltilen / Standardize Edilenler
- Admin genel sekme sistemi için RC7 JS eklendi:
  - `public/assets/js/admin/ao-tabs-rc7.js`
- Admin genel panel/tab görünümü için RC7 CSS eklendi:
  - `public/assets/css/ao-admin-rc7-polish.css`
- Ayarlar Merkezi ve diğer `data-ao-tabs` yapılarında sadece aktif sekme içeriğinin görünmesi sağlandı.
- Mobil/masaüstü admin tab davranışı güçlendirildi.
- Admin menüde Domain > Registrarlar bağlantısı API Entegrasyonları içindeki Domain sekmesine yönlendirildi.
- API Entegrasyonları tek merkez mantığıyla sekmeli yapıya alındı:
  - Domain Registrarları
  - Sunucular / Hosting Panelleri
  - SMS / WhatsApp
  - Yapay Zeka
  - Ödeme
  - Mail / SMTP
  - Diğer API’ler
- DomainNameAPI alanları yeni karara göre düzenlendi:
  - Reseller ID
  - API Key
  - OTE / Test API Key opsiyonel
- Otomasyonlar KPI alanı premium kart yapısına geçirildi.
- Build Center Pro alt sayfalarında “başlık değişip içerik aynı kalıyor” sorunu giderildi:
  - Build Ortamı
  - SDK & Araçlar
  - Build Logları
  - Build Kuyruğu
  - Ayarlar
  her biri ayrı içerik gösterir.

## Yedek
- `_backup/rc7-before-qa-visual-module/`

## Korunanlar
- RC5 unified header yapısı
- RC6 site content CSS/parça yapısı
- Fresh install yapısı
- Admin guard / CSRF kararları
- Eski CSS’ler silinmedi
