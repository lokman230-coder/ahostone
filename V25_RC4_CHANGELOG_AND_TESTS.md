# Ahost One v25.0.0 RC4 - Security / Owner / Install Quality Patch

Bu paket, RC3 güvenlik/fresh-install paketini baz alır. OpenHands optimize paketindeki değişiklikler ayrıca kontrol edildi; görseli bozabilecek agresif CSS silme yaklaşımı alınmadı, güvenli ve düşük riskli iyileştirmeler seçildi.

## Uygulanan RC4 Düzeltmeleri

1. **Müşteri SiteBuilder kaydetme akışı admin rotasından ayrıldı.**
   - Eski müşteri formu `admin/site-builder/page-save` adresine gönderiyordu.
   - RC4 ile müşteri kaydetme işlemi `client/site-builder/page-save` rotasına alındı.
   - Merkezi admin guard artık müşteri builder kaydetmeyi bozmaz.

2. **Müşteri SiteBuilder sahiplik kontrolü güçlendirildi.**
   - Sayfa kaydetme, sayfa oluşturma, proje oluşturma ve export işlemlerinde `customer_id` kontrolü var.
   - Müşteri başka müşterinin `project_id` veya `page_id` değerini değiştirirse işlem reddedilir.

3. **Müşteri panelindeki admin linkleri temizlendi.**
   - `client/site-builder` ekranındaki “Yeni Sayfa / Site Oluştur” aksiyonları artık admin sayfasına gitmez.
   - Müşteri kendi panelinden proje ve sayfa oluşturabilir.

4. **RC3 güvenlik düzeltmeleri korundu.**
   - `admin/security-question` merkezi admin guard istisnasında kalır.
   - `verify_csrf()` çağrıldığı GET aksiyonlarında da token doğrular.
   - `install.php` modül `install.sql` dosyalarını recursive olarak bulur.
   - `schema.sql` ve `ssdhost_nexus.sql` release paketinde yoktur.
   - `fresh-install.sql` içinde hazır bcrypt admin hash sızıntısı yoktur.

5. **Sürüm tutarlılığı RC4’e güncellendi.**
   - README, install.php, config örnekleri, fresh-install.sql, builder ekran başlıkları ve test rehberi RC4 olarak güncellendi.

6. **CSRF hata yönlendirmesi iyileştirildi.**
   - Admin, müşteri ve public formlarda token hatası farklı giriş ekranlarına yanlış düşmez.
   - Public formlarda kullanıcı mümkünse geldiği sayfaya geri yönlendirilir.

7. **Güvenli yardımcı optimizasyon eklendi.**
   - OpenHands paketindeki `scripts/convert-webp.sh` isteğe bağlı yardımcı script olarak eklendi.
   - Bu script otomatik çalışmaz; görsel kırma veya kurulum riski oluşturmaz.

## Bilerek Yapılmayanlar

- CSS dosyaları agresif şekilde silinmedi. Header ve panel görünümünü bozma riski nedeniyle RC4, güvenlik ve sahiplik düzeltmesine odaklanır.
- `index.php` büyük MVC refactor’a bölünmedi. Bu iş v26 mimari refactor için daha güvenlidir.
- `modules/` klasörü kaldırılmadı. `module.json` ve `install.sql` akışı sistem tarafından kullanıldığı için korunur.

## Kurulum Sonrası Zorunlu Testler

1. Boş veritabanında `install.php` ile kurulum yapın.
2. Kurulumda belirlediğiniz admin e-posta/şifre ile giriş yapın.
3. Admin şifre sıfırlama akışını test edin: `forgot-password -> security-question -> reset-password`.
4. Giriş yapmadan `/admin/customers/delete`, `/admin/product-center/product-delete`, `/admin/domain-center/registrar-save` gibi rotaların admin login’e attığını doğrulayın.
5. Müşteri kaydı oluşturup müşteri paneline girin.
6. `client/site-builder` ekranından yeni proje oluşturun.
7. Yeni sayfa oluşturun, düzenleyin ve kaydedin.
8. Farklı müşteriyle URL’de `project_id` / `page_id` değiştirerek erişimin engellendiğini doğrulayın.
9. Site header, admin dashboard, müşteri paneli, Site Builder ve Mobile Builder görsel kontrolünü yapın.
10. Domain Center, Marketplace ve Mobile Builder gerçek entegrasyonlarını ayrıca test edin.

## Statik Paket Kontrolleri

- PHP lint temiz olmalıdır.
- ZIP bütünlük testi temiz olmalıdır.
- `schema.sql` / `ssdhost_nexus.sql` pakette bulunmamalıdır.
- `fresh-install.sql` içinde canlı admin hash, canlı IP, canlı domain veya login event seed verisi bulunmamalıdır.
