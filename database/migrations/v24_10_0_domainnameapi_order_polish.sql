-- Ahost One v24.10.0 - DomainNameAPI yeni API alanları, manuel sipariş akışı ve marka temizliği

-- DomainNameAPI artık username/password yerine Reseller ID + API Key + OTE/Test API Key ile yapılandırılır.
INSERT INTO registrar_configs(registrar_id, config_key, config_value, is_secret)
SELECT id, 'auth_mode', 'apikey', 0 FROM domain_registrars WHERE slug='domainnameapi'
ON DUPLICATE KEY UPDATE config_value='apikey', is_secret=0;

INSERT INTO registrar_configs(registrar_id, config_key, config_value, is_secret)
SELECT id, 'ote_api_key', '', 1 FROM domain_registrars WHERE slug='domainnameapi'
ON DUPLICATE KEY UPDATE is_secret=1;

INSERT INTO registrar_configs(registrar_id, config_key, config_value, is_secret)
SELECT id, 'reseller_id', '', 0 FROM domain_registrars WHERE slug='domainnameapi'
ON DUPLICATE KEY UPDATE is_secret=0;

-- Eski kullanıcı/şifre anahtarları fresh install yeni ekranda kullanılmaz.
DELETE rc FROM registrar_configs rc
JOIN domain_registrars dr ON dr.id=rc.registrar_id
WHERE dr.slug='domainnameapi' AND rc.config_key IN ('api_username','api_password','username','password');

-- Görünen sipariş numarası ve import izlerini Ahost One markasına çevir.
UPDATE orders SET order_number = REPLACE(order_number, 'AHOST-ORDER', 'AHOST-ORDER') WHERE order_number LIKE 'AHOST-ORDER%';
UPDATE invoices SET invoice_number = REPLACE(invoice_number, 'Ahost One-', 'AHOST-') WHERE invoice_number LIKE 'Ahost One-%';
UPDATE products SET slug = REPLACE(slug, 'whmcs-product', 'ahost-product') WHERE slug LIKE 'whmcs-product%';
UPDATE product_groups SET slug = REPLACE(slug, 'whmcs-', 'ahost-') WHERE slug LIKE 'whmcs-%';
