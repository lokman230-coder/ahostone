-- v23.3.0 Dual Currency Product Pricing
CREATE TABLE IF NOT EXISTS product_pricing (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  cycle VARCHAR(40) DEFAULT 'monthly',
  price DECIMAL(14,2) DEFAULT 0.00,
  setup_fee DECIMAL(14,2) DEFAULT 0.00,
  currency VARCHAR(10) DEFAULT 'TRY',
  UNIQUE KEY uniq_product_cycle (product_id,cycle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS price_usd DECIMAL(14,2) DEFAULT 0.00;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS price_try DECIMAL(14,2) DEFAULT 0.00;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS setup_fee_usd DECIMAL(14,2) DEFAULT 0.00;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS setup_fee_try DECIMAL(14,2) DEFAULT 0.00;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS base_currency VARCHAR(10) DEFAULT 'USD';
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS exchange_rate DECIMAL(16,6) DEFAULT 0.000000;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS margin_percent DECIMAL(8,2) DEFAULT 0.00;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS auto_convert TINYINT(1) DEFAULT 1;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 0;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS source_type VARCHAR(40) NULL;
ALTER TABLE product_pricing ADD COLUMN IF NOT EXISTS external_id VARCHAR(80) NULL;

CREATE TABLE IF NOT EXISTS currency_rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  currency_code VARCHAR(10) UNIQUE,
  base_code VARCHAR(10) DEFAULT 'TRY',
  tcmb_rate DECIMAL(16,6) DEFAULT 0,
  margin_percent DECIMAL(8,2) DEFAULT 0,
  final_rate DECIMAL(16,6) DEFAULT 0,
  source VARCHAR(80) DEFAULT 'TCMB',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO currency_rates(currency_code,base_code,tcmb_rate,margin_percent,final_rate,source)
VALUES('USD','TRY',45,5,47.25,'TCMB')
ON DUPLICATE KEY UPDATE currency_code=VALUES(currency_code);
