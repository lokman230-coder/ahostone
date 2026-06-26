CREATE TABLE IF NOT EXISTS module_marketplace_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NULL,
  event_type VARCHAR(80) NOT NULL,
  amount DECIMAL(14,2) DEFAULT 0,
  currency VARCHAR(10) DEFAULT 'TRY',
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY listing_id(listing_id), KEY event_type(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
