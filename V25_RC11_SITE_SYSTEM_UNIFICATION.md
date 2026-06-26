# Ahost One v25.0.0 RC11 — Site System Unification

## Amaç

Site sistemindeki görsel bozulmaların ana sebebi olan çoklu header, çoklu header CSS, tema CSS çakışması ve aynı içerik tipleri için ayrı ayrı kullanılan frontend CSS katmanları temizlendi.

Bu sürümde amaç, sistemi bozmadan aktif görünümü tek kaynaklara bağlamaktır.

## Yapılanlar

### 1. Header tekilleştirme

Aktif header HTML kaynağı tek dosyaya indirildi:

- `app/Views/shared/unified-header.php`

Aktif header CSS kaynağı tek dosyaya indirildi:

- `public/assets/css/ao-unified-header.css`

Aşağıdaki alanlar aynı header dosyasını kullanır:

- Site ana header
- Admin login header
- Müşteri login header
- Müşteri panel üst header

Not: PHP render sistemi `partials/header.php` beklediği için site/auth/customer partial dosyaları tamamen silinmedi. Bunlar artık ayrı header HTML üretmez; sadece layout başlangıcı olarak ortak header dosyasını çağırır.

### 2. Eski header CSS temizliği

Aktif yükten çıkarılan ve yedeğe alınan eski header/frontend CSS dosyaları:

- `site-front.css`
- `site-saas-v2211.css`
- `frontend-home-v2430.css`
- `frontend-products.css`
- `mobile-responsive-v2415.css`
- `mobile-cleanup-v2416.css`
- `desktop-menu.css`
- `mobile-menu.css`
- `support-widget-pro.css`
- `builder-public-v2411.css`
- `ahost-v2411-language-domain-polish.css`
- `ahost-v2411-master-header-ui.css`
- `ahost-v2490-checkout-header.css`
- `ao-premium-header.css`
- `unified-header-v2440.css`
- `v20-ultimate.css`

Bu dosyaların site için gerekli stilleri `ao-site-content.css` içine taşındı. Böylece site tarafında tek içerik CSS katmanı kullanılmaya başlandı.

### 3. Tema CSS çakışmaları pasifleştirildi

Tema seçimi korunur; body class ve CSS variable değerleri çalışmaya devam eder. Ancak tema paketlerindeki `theme.css` dosyaları artık otomatik yüklenmez. Böylece tema CSS’leri header ve site içerik görünümünü ezemez.

Tema header dosyaları aktif temalardan kaldırıldı ve yedeğe alındı.

### 4. Site içerik sistemi tekilleştirildi

Blog, bilgi bankası ve benzer içerik listeleri için ortak renderer eklendi:

- `app/Views/site/shared/content-renderer.php`

Bu renderer şu yapıların ortak görünümünü üretir:

- içerik kartı
- içerik grid’i
- boş içerik alanı
- tarih/meta gösterimi
- açıklama kırpma
- ortak içerik sayfası çağrısı

Blog liste ve bilgi bankası liste sayfaları bu ortak renderer’a bağlandı.

### 5. Tek site içerik CSS’i

Site içerik görünümü tek CSS altında toplandı:

- `public/assets/css/ao-site-content.css`

Bu CSS artık şunları kapsar:

- Ana sayfa hero ve ürün katalog alanları
- Blog / içerik kartları
- Bilgi bankası kartları
- Ürün detay içerikleri
- Domain arama ve domain araçları
- Marketplace site görünümü
- Builder public preview alanları
- Mobil alt menü görünümü
- Support widget görünümü
- Eski frontend CSS’lerden taşınan gerekli stiller

### 6. Admin tarafında kırık CSS referansları temizlendi

Eski `v20-ultimate.css` dosyası kaldırıldığı için bu CSS’i çağıran admin modüllerinden direkt link kaldırıldı. Gerekli stiller `ao-admin-shell.css` içine taşındı.

## Yedek

Yedek klasörü:

- `_backup/rc11-before-site-system-unification/`

Bu klasörde eski header, CSS ve tema dosyaları tutulur.

## Korunanlar

- Fresh install yapısı
- Admin guard
- CSRF
- Müşteri owner kontrolleri
- Route/controller akışı
- Admin panel iç işlevleri
- Müşteri panel işlevleri
- QA & Scan Center
- PHP Screenshot Bridge

## Kontrol

- PHP syntax kontrolünden geçti.
- Eksik CSS/JS asset referansı kontrol edildi.
- ZIP bütünlük testi yapıldı.

## Not

Bu çalışma agresif backend değişikliği yapmaz. Amaç, site/public görünümde CSS ve header çakışmasını bitirmek, aynı işi yapan frontend dosyalarını tek aktif sistemde toplamak ve bundan sonraki görsel bozulmaları azaltmaktır.
