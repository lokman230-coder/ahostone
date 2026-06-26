-- Email Templates Table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    type ENUM('customer','order','invoice','domain','support','system','affiliate') DEFAULT 'customer',
    subject VARCHAR(255) NOT NULL,
    content LONGTEXT,
    variables JSON,
    is_active TINYINT(1) DEFAULT 1,
    is_html TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
