CREATE TABLE IF NOT EXISTS module_paytr_callbacks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_oid VARCHAR(190) NULL,
  status VARCHAR(30) DEFAULT 'received',
  amount DECIMAL(14,2) DEFAULT 0,
  payload_json LONGTEXT NULL,
  processed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY merchant_oid(merchant_oid), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
