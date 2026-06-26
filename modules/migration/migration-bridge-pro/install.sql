CREATE TABLE IF NOT EXISTS module_migration_bridge_connections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  source_type VARCHAR(32) NOT NULL DEFAULT 'Kaynak Sistem',
  title VARCHAR(160) NULL,
  host VARCHAR(190) NULL,
  port INT DEFAULT 3306,
  database_name VARCHAR(190) NULL,
  username VARCHAR(190) NULL,
  password_encrypted TEXT NULL,
  charset_name VARCHAR(32) DEFAULT 'utf8mb4',
  status VARCHAR(30) DEFAULT 'draft',
  last_error TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_scans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  connection_id INT NULL,
  source_type VARCHAR(32) NOT NULL DEFAULT 'Kaynak Sistem',
  summary_json LONGTEXT NULL,
  status VARCHAR(30) DEFAULT 'scanned',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  scan_id INT NOT NULL,
  item_type VARCHAR(40) NOT NULL,
  source_id VARCHAR(80) NOT NULL,
  title VARCHAR(255) NULL,
  subtitle VARCHAR(255) NULL,
  payload_json LONGTEXT NULL,
  action VARCHAR(30) DEFAULT 'import',
  conflict_status VARCHAR(30) DEFAULT 'new',
  mapped_id VARCHAR(80) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_scan_type (scan_id,item_type),
  INDEX idx_source (item_type,source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_maps (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  item_type VARCHAR(40) NOT NULL,
  source_id VARCHAR(80) NOT NULL,
  target_table VARCHAR(120) NULL,
  target_id VARCHAR(80) NULL,
  payload_hash VARCHAR(80) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_item_map (item_type,source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  scan_id INT NULL,
  level VARCHAR(20) DEFAULT 'info',
  message TEXT NULL,
  context_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
