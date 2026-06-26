-- Affiliate System Tables
CREATE TABLE IF NOT EXISTS affiliate_affiliates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    referral_code VARCHAR(20) NOT NULL UNIQUE,
    commission_rate DECIMAL(5,2) DEFAULT 10.00,
    total_earnings DECIMAL(12,2) DEFAULT 0.00,
    pending_earnings DECIMAL(12,2) DEFAULT 0.00,
    paid_earnings DECIMAL(12,2) DEFAULT 0.00,
    total_referrals INT DEFAULT 0,
    active_referrals INT DEFAULT 0,
    status ENUM('active','pending','suspended') DEFAULT 'active',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (referral_code),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS affiliate_referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT NOT NULL,
    referred_customer_id INT NOT NULL,
    referral_code_used VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    converted TINYINT(1) DEFAULT 0,
    converted_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (affiliate_id) REFERENCES affiliate_affiliates(id) ON DELETE CASCADE,
    INDEX idx_affiliate (affiliate_id),
    INDEX idx_customer (referred_customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS affiliate_commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT NOT NULL,
    referral_id INT DEFAULT NULL,
    order_id INT DEFAULT NULL,
    customer_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    commission DECIMAL(12,2) NOT NULL,
    status ENUM('pending','approved','paid','cancelled') DEFAULT 'pending',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (affiliate_id) REFERENCES affiliate_affiliates(id) ON DELETE CASCADE,
    INDEX idx_affiliate (affiliate_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS affiliate_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    fee DECIMAL(12,2) DEFAULT 0.00,
    net_amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('bank','paypal','crypto') DEFAULT 'bank',
    bank_account VARCHAR(255),
    status ENUM('pending','processing','completed','failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    paid_at DATETIME DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (affiliate_id) REFERENCES affiliate_affiliates(id) ON DELETE CASCADE,
    INDEX idx_affiliate (affiliate_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
