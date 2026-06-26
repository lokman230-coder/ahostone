# RC10 Öncesi Premium SaaS UI Rebuild Yedeği

Bu klasör RC10 Premium SaaS Design System Rebuild öncesinde aktif header, layout ve CSS dosyalarının yedeğini içerir.

Amaç:
- Eski site/auth/customer/admin header dosyalarına geri dönüş imkânı sağlamak.
- Eski CSS dosyaları silinmeden önce güvenli referans tutmak.
- RC10'da eklenen yeni design token, component ve shell dosyaları eski sistemi ezmeden kademeli geçiş yapar.

Geri dönüşte öncelikli dosyalar:
- app/Views/shared/layout-head.php
- app/Views/shared/unified-header.php
- app/Views/admin/partials/header.php
- app/Views/customer/partials/header.php
- app/Views/auth/partials/header.php
- app/Views/site/partials/header.php
