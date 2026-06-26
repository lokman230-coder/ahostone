CREATE TABLE IF NOT EXISTS module_shopier_callbacks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  platform_order_id VARCHAR(190) NULL,
  status VARCHAR(30) DEFAULT 'received',
  amount DECIMAL(14,2) DEFAULT 0,
  payload_json LONGTEXT NULL,
  processed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY platform_order_id(platform_order_id), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
