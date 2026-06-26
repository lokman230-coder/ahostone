# Ahost One RC10 Layout Standard

RC10, arayüzü sayfa sayfa makyajlamak yerine premium SaaS design system katmanına bağlar.

Aktif amaç:
- Mevcut controller/model/route/fresh install akışı korunur.
- Header, shell, kart, tablo, form, sekme, modal ve içerik görünümü ortak CSS ve component standardına alınır.
- Eski CSS dosyaları silinmez; RC10 katmanı en son yüklenerek görünümü normalize eder.

Shell ayrımı:
- `site-shell.php`: site ön yüzü.
- `auth-shell.php`: site header + admin login + müşteri login + kayıt/şifre sayfaları.
- `admin-shell.php`: admin iç panel topbar/sidebar standardı.
- `customer-shell.php`: müşteri panel standardı.

