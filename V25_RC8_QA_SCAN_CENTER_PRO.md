# Ahost One v25.0.0 RC8 — QA & Scan Center Pro

## Ana karar

RC7'de ayrı görünen **QA Görsel Tarama Merkezi** ve **Scan & Report Center** tek modülde birleştirildi.

Yeni merkez:

- `admin/qa-scan-center`
- Eski `admin/qa-visual-scan` ve `admin/scan-report` adresleri uyumluluk için aynı merkeze yönlendirilir/gösterir.

## Eklenenler

- Tek ekran: **QA & Scan Center Pro**
- Sekmeler:
  - Genel Bakış
  - Görsel Tarama
  - Sistem Taraması
  - Veritabanı
  - Route & Link
  - Modüller
  - API Kontrolü
  - Raporlar
- Tek buton: **Tam Tarama Başlat**
- Tarama çıktıları:
  - `report.html`
  - `report.pdf`
  - `summary.json`
  - `desktop/*.svg` / Playwright ile gerçek screenshot
  - `mobile/*.svg` / Playwright ile gerçek screenshot
  - `logs/console.log`
  - `logs/network.json`
  - `qa-scan-package.zip`

## Rapor indirme

Panelde son rapor için:

- ZIP Rapor İndir
- HTML Rapor Görüntüle
- PDF Rapor İndir

Raporlar şu klasöre kaydedilir:

`storage/reports/qa-scans/{tarih-saat}/`

## Yedek

RC8 öncesi dosyalar yedeklendi:

`_backup/rc8-before-qa-scan-unification/`
