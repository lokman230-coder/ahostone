# Ahost One v25.0.0 RC14 — Admin/Public Component Rebuild

## Amaç
RC13 sonrası header büyük ölçüde korunurken site ve admin body/content katmanlarında kalan bozulmalar düzeltildi.

## Yapılanlar
- Admin sidebar inceltildi ve daha zarif/premium SaaS görünüm verildi.
- Admin topbar'a Hızlı Erişim menüsü eklendi.
- Admin body component sistemi stabilize edildi: page head, stat card, tab, form, table, empty state, mini card.
- Hosting & Server Center yeniden düzenlendi.
- SiteBuilder ana sayfası yeniden düzenlendi.
- MobileBuilder boş görünüm sorunu için güvenli, veritabanı tablosu olmasa da içerik gösteren dashboard yazıldı.
- Theme Center kart sistemi yeniden düzenlendi.
- Notification Center yeniden düzenlendi.
- AI Center daha anlaşılır premium kart düzenine alındı.
- QA & Scan Center raw HTML görünümü yerine tab/kart sistemiyle yenilendi.
- API Entegrasyonları sekmeli premium kart/form yapısına alındı.
- Settings Center aktif sekme mantığıyla sade premium tab sistemine taşındı.
- Help Center doğru menü yolları ve premium kart yapısıyla düzenlendi.
- Public hosting/vps/sitebuilder/mobilebuilder sayfaları tek shared service renderer ile düzenlendi.
- Domain sayfasındaki raw button'lar premium domain tool grid yapısına alındı.
- Marketplace filtre, istatistik, kategori ve ilan kartları yeniden düzenlendi.
- /blog route/view mapping hatası düzeltildi; public blog artık site view olarak açılır.
- SiteMap içindeki bazı hatalı `site/...` view pathleri düzeltildi.

## Korunanlar
- Header yapısı büyük ölçüde korunmuştur.
- Backend/controller/model/DB güvenlik mantığına dokunulmamıştır.
- Fresh install yapısı korunmuştur.
- Eski dosyalar `_backup/rc14-before-admin-public-component-rebuild/` altında yedeklendi.
