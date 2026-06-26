-- Kanban Board Tables
CREATE TABLE IF NOT EXISTS kanban_boards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(20) DEFAULT '#2563eb',
    is_public TINYINT(1) DEFAULT 0,
    owner_type ENUM('admin','customer') DEFAULT 'admin',
    owner_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kanban_columns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(20) DEFAULT '#64748b',
    sort_order INT DEFAULT 0,
    wip_limit INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (board_id) REFERENCES kanban_boards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kanban_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    column_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
    status ENUM('todo','in_progress','done','blocked') DEFAULT 'todo',
    assignee_type ENUM('admin','customer') DEFAULT 'admin',
    assignee_id INT DEFAULT NULL,
    due_date DATE DEFAULT NULL,
    estimated_hours DECIMAL(10,2) DEFAULT 0,
    logged_hours DECIMAL(10,2) DEFAULT 0,
    tags VARCHAR(255) DEFAULT '',
    attachments INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    position INT DEFAULT 0,
    created_by INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (column_id) REFERENCES kanban_columns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Board
INSERT IGNORE INTO kanban_boards (id, name, description, color) VALUES (1, 'Proje Yönetimi', 'Varsayılan proje panosu', '#2563eb');

-- Default Columns
INSERT IGNORE INTO kanban_columns (id, board_id, name, color, sort_order) VALUES 
(1, 1, 'Yapılacak', '#ef4444', 1),
(2, 1, 'Devam Eden', '#f59e0b', 2),
(3, 1, 'İnceleme', '#3b82f6', 3),
(4, 1, 'Tamamlanan', '#10b981', 4);
