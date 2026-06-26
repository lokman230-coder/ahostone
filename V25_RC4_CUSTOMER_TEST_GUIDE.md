# Ahost One v25.0.0 RC4 - Müşteri Test Rehberi

Bu paket fresh install ve müşteri deneyimi testi için hazırlanmış RC4 final adayıdır.

## Temel Kurulum Testi

1. ZIP’i sunucuya çıkarın.
2. Boş veritabanı oluşturun.
3. `install.php` ekranından kurulumu yapın.
4. Kurulumda kendi admin e-posta/şifrenizi belirleyin.
5. Kurulum sonrası admin paneline giriş yapın.

## Müşteri Test Akışı

1. Ön siteden yeni müşteri kaydı oluşturun.
2. Müşteri paneline giriş yapın.
3. `Site Builder` ekranına girin.
4. Yeni proje oluşturun.
5. Yeni sayfa oluşturun.
6. Sayfayı düzenleyip kaydedin.
7. Önizleme ve ZIP export akışını deneyin.
8. Başka müşteri hesabı açıp ilk müşterinin `project_id` / `page_id` değerlerini URL’den deneyin; erişim engellenmelidir.

## Ürün Akışı Testleri

- Ürün inceleme sayfaları
- Sepete ekleme
- Dönem seçimi
- Domain seçimi / mevcut domain kullanımı
- Eklenti/addon adımı
- Ödeme öncesi login/register akışı
- Fatura/sipariş oluşumu

## Admin Testleri

- Admin dashboard
- Ürün Merkezi
- Domain Center
- Marketplace
- Site Builder
- Mobile Builder
- Müşteri detayında hizmet yönetimi
- Admin şifre sıfırlama
- CSRF token olmadan GET silme/toggle URL denemeleri

## Görsel Testler

- Public header
- Admin header/sidebar
- Müşteri paneli
- Mobil responsive görünüm
- Site Builder canlı editor
- Mobile Builder ekranları

## Raporlanacak Eksikler

- Bozuk butonlar
- Boş/placeholder sayfalar
- SQL hataları
- Kurulum hataları
- Görsel taşmalar
- Header bozulmaları
- Yetki ihlalleri
- Müşterinin başka müşteriye ait projeye erişebilmesi
