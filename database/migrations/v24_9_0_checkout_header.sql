-- Ahost One v24.9.0 checkout/header enhancements
-- Mevcut SQL verilerini silmez; sadece ek paket/ek özellik bağlantısı için opsiyonel tablo ekler.
CREATE TABLE IF NOT EXISTS product_checkout_addons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  addon_key VARCHAR(80) NOT NULL,
  name VARCHAR(190) NOT NULL,
  description TEXT NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  currency VARCHAR(10) NOT NULL DEFAULT 'TRY',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_product_addon_key (product_id, addon_key),
  KEY idx_product_checkout_addons_product (product_id),
  KEY idx_product_checkout_addons_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO settings(setting_key, setting_value) VALUES
('checkout_theme_mode','site'),
('checkout_guest_until_payment','1'),
('frontend_admin_button_visible','0'),
('header_account_dropdown_enabled','1'),
('checkout_domain_step_enabled','1'),
('checkout_addons_step_enabled','1');
