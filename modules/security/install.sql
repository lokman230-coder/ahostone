-- 2FA Security Tables
CREATE TABLE IF NOT EXISTS user_2fa_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin','customer') NOT NULL,
    user_id INT NOT NULL,
    enabled TINYINT(1) DEFAULT 0,
    secret VARCHAR(64) DEFAULT NULL,
    backup_codes TEXT,
    method ENUM('totp','email','sms') DEFAULT 'totp',
    email_verify TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_type, user_id),
    INDEX idx_user (user_type, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_2fa_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin','customer') NOT NULL,
    user_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    type ENUM('login','password','change') NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_code (user_type, user_id, code),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
