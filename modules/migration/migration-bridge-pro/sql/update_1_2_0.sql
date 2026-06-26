CREATE TABLE IF NOT EXISTS module_migration_bridge_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO module_migration_bridge_settings (setting_key, setting_value) VALUES
('target_currency','TRY'),
('fallback_usd_try','40'),
('fallback_eur_try','43'),
('margin_percent','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Ürün ve domain fiyat kolonları farklı Ahost One kurulumlarında değişebildiği için
-- v1.2.0 bu kolonları import sırasında güvenli şekilde runtime ALTER TABLE ile ekler.
-- Bu SQL yalnızca modül ayar tablosunu kurar.
