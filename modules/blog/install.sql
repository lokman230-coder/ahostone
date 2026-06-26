-- Blog System Tables - Ahost One v24.11.3
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    featured_image VARCHAR(500),
    author_id INT DEFAULT 1,
    category_id INT DEFAULT 1,
    status ENUM('draft','published','scheduled','archived') DEFAULT 'draft',
    visibility ENUM('public','private','password','subscribers') DEFAULT 'public',
    password_protected VARCHAR(255) DEFAULT NULL,
    published_at DATETIME DEFAULT NULL,
    scheduled_at DATETIME DEFAULT NULL,
    view_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(255),
    og_image VARCHAR(500),
    featured TINYINT(1) DEFAULT 0,
    sticky TINYINT(1) DEFAULT 0,
    allow_comments TINYINT(1) DEFAULT 1,
    allow_rating TINYINT(1) DEFAULT 1,
    language VARCHAR(10) DEFAULT 'tr',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_slug (slug),
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    parent_id INT DEFAULT 0,
    image VARCHAR(500),
    post_count INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    post_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_post_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    parent_id INT DEFAULT 0,
    author_name VARCHAR(100),
    author_email VARCHAR(150),
    author_url VARCHAR(255),
    content TEXT NOT NULL,
    status ENUM('pending','approved','spam','trash') DEFAULT 'pending',
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0,
    customer_id INT DEFAULT NULL,
    admin_reply TINYINT(1) DEFAULT 0,
    replied_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_post (post_id),
    INDEX idx_status (status),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Categories
INSERT IGNORE INTO blog_categories (id, name, slug, description, is_active) VALUES (1, 'Genel', 'genel', 'Genel konular', 1);
INSERT IGNORE INTO blog_categories (id, name, slug, description, is_active) VALUES (2, 'Hosting', 'hosting', 'Hosting rehberleri', 1);
INSERT IGNORE INTO blog_categories (id, name, slug, description, is_active) VALUES (3, 'Domain', 'domain', 'Domain yönetimi', 1);
INSERT IGNORE INTO blog_categories (id, name, slug, description, is_active) VALUES (4, 'Güvenlik', 'guvenlik', 'Güvenlik ipuçları', 1);
INSERT IGNORE INTO blog_categories (id, name, slug, description, is_active) VALUES (5, 'Teknoloji', 'teknoloji', 'Teknoloji haberleri', 1);

-- Default Settings
INSERT IGNORE INTO blog_settings (setting_key, setting_value) VALUES 
('blog_name', 'Ahost One Blog'),
('blog_tagline', 'Hosting, domain ve teknoloji rehberleri'),
('posts_per_page', '10'),
('comments_moderation', '1'),
('auto_publish', '0'),
('featured_posts', '1'),
('share_buttons', '1'),
('related_posts', '1'),
('author_bio', '1'),
('read_time', '1'),
('social_links', '{\"twitter\":\"\",\"facebook\":\"\",\"linkedin\":\"\",\"instagram\":\"\"}');
