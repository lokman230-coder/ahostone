# Ahost One v25.0.0 RC9 — PHP Screenshot Bridge

## Amaç

QA & Scan Center Pro artık sadece placeholder ekran görüntüsü üretmez. Visual Scan için PHP tabanlı bir Screenshot Bridge eklendi.

## Yeni servisler

- `app/Services/PHPScreenshotBridge.php`
- `app/Services/QAScanCenterService.php` güncellendi.

## Motorlar

1. **Auto**
   - Önce sunucuda Chrome/Chromium ve PHP `exec` desteğini arar.
   - Varsa gerçek PNG screenshot üretir.
   - Yoksa Remote Screenshot API denenir.
   - O da yoksa SVG fallback üretir.

2. **Local Chrome / Chromium**
   - Playwright gerekmez.
   - PHP `exec` ile şu mantıkta çalışır:
     `google-chrome --headless --screenshot=... --window-size=... URL`
   - Çıktılar gerçek PNG dosyasıdır.

3. **Remote Screenshot API**
   - Shared hosting veya Chrome kurulamayan sunucular için.
   - JSON POST bekler:
     - `url`
     - `width`
     - `height`
     - `full_page`
     - `wait_ms`
     - `token`
   - PNG binary veya `image_base64` JSON döndürebilir.

4. **Basic fallback**
   - Gerçek screenshot alamaz.
   - Sistem taraması, route/link raporu, HTML/PDF/ZIP raporu devam eder.
   - Görseller SVG fallback olarak üretilir.

## Admin ayarları

`Admin > QA & Scan Center Pro > Screenshot Motoru`

Yeni ayarlar:

- `qa_screenshot_engine`
- `qa_screenshot_chrome_path`
- `qa_screenshot_remote_endpoint`
- `qa_screenshot_remote_token`
- `qa_screenshot_wait_ms`
- `qa_screenshot_timeout`
- `qa_screenshot_desktop_width`
- `qa_screenshot_desktop_height`
- `qa_screenshot_mobile_width`
- `qa_screenshot_mobile_height`

## Rapor çıktısı

Her tarama şu yapıda kayıt üretir:

```text
storage/reports/qa-scans/{scan_id}/
├── report.html
├── report.pdf
├── summary.json
├── qa-scan-package.zip
├── desktop/
│   ├── admin-dashboard.png veya .svg
│   └── ...
├── mobile/
│   ├── admin-dashboard.png veya .svg
│   └── ...
└── logs/
    ├── console.log
    └── network.json
```

## Önemli not

PHP tek başına gerçek ekran görüntüsü alamaz. Gerçek piksel görüntüsü için Local Chrome/Chromium veya Remote Screenshot API gerekir. RC9 ile Playwright zorunluluğu kaldırılmıştır.
