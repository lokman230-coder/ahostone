-- API Gateway Tables
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    api_key VARCHAR(64) NOT NULL UNIQUE,
    secret_key VARCHAR(128) NOT NULL,
    permissions JSON,
    rate_limit INT DEFAULT 100,
    is_active TINYINT(1) DEFAULT 1,
    customer_id INT DEFAULT NULL,
    last_used_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key (api_key),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT DEFAULT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    status_code INT DEFAULT 200,
    request_body LONGTEXT,
    response_body LONGTEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    execution_time_ms INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key (api_key_id),
    INDEX idx_endpoint (endpoint),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS webhooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    events JSON NOT NULL,
    secret VARCHAR(128) DEFAULT NULL,
    headers JSON,
    is_active TINYINT(1) DEFAULT 1,
    retry_count INT DEFAULT 3,
    timeout INT DEFAULT 30,
    customer_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_events (events(100)),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS webhook_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    webhook_id INT NOT NULL,
    event VARCHAR(50) NOT NULL,
    payload LONGTEXT,
    response_code INT DEFAULT NULL,
    response_body LONGTEXT,
    attempts INT DEFAULT 1,
    status ENUM('pending','success','failed') DEFAULT 'pending',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhook (webhook_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
