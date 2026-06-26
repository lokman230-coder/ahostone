# Ahost One v25.0.0 RC6 — Site Content UI Unification

## Amaç
Admin panelinden oluşturulan veya düzenlenen site içeriklerinin frontend tarafında tek premium içerik tasarım diline bağlanması.

## Korunan alanlar
- RC5 unified header yapısı korundu.
- `app/Views/shared/unified-header.php` yapısına müdahale edilmedi.
- Admin panel iç sidebar/topbar yapısına dokunulmadı.
- Fresh install yapısı korundu.
- `schema.sql` ve `ssdhost_nexus.sql` tekrar eklenmedi.
- Admin guard, CSRF, recursive module SQL ve müşteri owner kontrol kararları korunacak şekilde site view ağırlıklı çalışıldı.
- Eski CSS dosyaları silinmedi.

## Eklenen / güçlendirilen ortak dosyalar
- `public/assets/css/ao-site-content.css`
- `app/Views/site/shared/content-page.php`
- `app/Views/site/shared/content-card.php`
- `app/Views/site/shared/content-list.php`
- `app/Views/site/shared/breadcrumb.php`
- `app/Views/site/shared/content-cta.php`

## Frontend entegrasyonu yapılan alanlar
- Blog liste sayfası: `app/Views/site/blog/index.php`
- Blog detay sayfası: `app/Views/site/blog/post.php`
- Bilgi bankası liste sayfası: `app/Views/site/knowledge-base/index.php`
- Ürün detay sayfası: `app/Views/site/products/detail.php`
- Site header CSS yükleme listesine `css/ao-site-content.css` eklendi.

## Yedek
Eski CSS dosyaları silinmeden şu klasörde yedeklendi:

`_backup/css-before-rc6-site-content/`

Bu klasördeki `README.md`, görsel bozulma durumunda geri dönüş notlarını içerir.

## Not
RC6 agresif CSS silme yapmaz. Eski modül CSS’leri çalışmaya devam eder; yeni ortak içerik CSS’i admin kaynaklı içeriklerin görünümünü birleştiren üst katman olarak kullanılır.
