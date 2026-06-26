CREATE TABLE IF NOT EXISTS module_build_center_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  build_id INT NULL,
  event_type VARCHAR(80) NOT NULL,
  status VARCHAR(30) DEFAULT 'info',
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY build_id(build_id), KEY event_type(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
