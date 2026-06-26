# RC11 Site System Unification Backup

Bu klasör, site/public/auth header ve tekrarlı frontend CSS temizliği öncesi alınan yedektir.

Amaç:
- Aktif header HTML yapısını tek dosyaya indirmek.
- Site, admin login, müşteri login ve müşteri panel üst header görünümünü tek header CSS üzerinden yönetmek.
- Blog, bilgi bankası, sayfa ve diğer site içeriklerinde ortak renderer + ortak CSS kullanmak.
- Eski tema/header CSS çakışmalarını aktif yüklemeden çıkarmak.

Geri alma:
- `files/` altındaki dosyalar eski konumlarına kopyalanabilir.
- `css/` altındaki CSS dosyaları eski konumlarına geri alınabilir.
- `themes/themes-original` klasörü eski tema dosyalarını içerir.
