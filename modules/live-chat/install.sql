-- Live Chat Tables
CREATE TABLE IF NOT EXISTS chat_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    avatar VARCHAR(500),
    status ENUM('online','offline','busy') DEFAULT 'offline',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(100),
    visitor_email VARCHAR(150),
    visitor_ip VARCHAR(45),
    department_id INT DEFAULT NULL,
    agent_id INT DEFAULT NULL,
    status ENUM('pending','active','closed') DEFAULT 'pending',
    source ENUM('chat','whatsapp','widget') DEFAULT 'chat',
    unread_admin INT DEFAULT 0,
    unread_visitor INT DEFAULT 0,
    last_message_at DATETIME DEFAULT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_agent (agent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_type ENUM('visitor','agent','system','ai') DEFAULT 'visitor',
    sender_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    attachments JSON,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Department
INSERT IGNORE INTO chat_departments (id, name, description, is_active) VALUES (1, 'Genel Destek', 'Genel sorular ve destek', 1);
INSERT IGNORE INTO chat_departments (id, name, description, is_active) VALUES (2, 'Teknik Destek', 'Hosting ve domain teknik konular', 1);
INSERT IGNORE INTO chat_departments (id, name, description, is_active) VALUES (3, 'Satış', 'Satış ve fiyatlandırma', 1);
