CREATE TABLE IF NOT EXISTS module_openai_usage_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  operation VARCHAR(80) NOT NULL,
  model_name VARCHAR(120) NULL,
  prompt_tokens INT DEFAULT 0,
  completion_tokens INT DEFAULT 0,
  status VARCHAR(30) DEFAULT 'success',
  error_message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY operation(operation), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
