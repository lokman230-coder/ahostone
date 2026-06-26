CREATE TABLE IF NOT EXISTS module_iletimerkezi_messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  recipient VARCHAR(80) NOT NULL,
  event_key VARCHAR(120) NULL,
  status VARCHAR(30) DEFAULT 'pending',
  provider_response LONGTEXT NULL,
  sent_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY recipient(recipient), KEY event_key(event_key), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
