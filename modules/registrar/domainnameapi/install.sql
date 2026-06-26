CREATE TABLE IF NOT EXISTS module_domainnameapi_operations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  domain_name VARCHAR(190) NULL,
  operation VARCHAR(80) NOT NULL,
  status VARCHAR(30) DEFAULT 'pending',
  request_json LONGTEXT NULL,
  response_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY domain_name(domain_name), KEY operation(operation), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
