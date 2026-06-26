-- SiteBuilder Pro - Database Tables

-- Projeler tablosu
CREATE TABLE IF NOT EXISTS site_builder_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL DEFAULT 'Yeni Site',
    template VARCHAR(50) NOT NULL DEFAULT 'corporate',
    settings JSON NOT NULL DEFAULT '{}',
    pages JSON NOT NULL DEFAULT '{}',
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Şablonlar tablosu
CREATE TABLE IF NOT EXISTS site_builder_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_key VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    preview_image VARCHAR(500) NULL,
    settings JSON NOT NULL DEFAULT '{}',
    default_pages JSON NOT NULL DEFAULT '{}',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bloklar tablosu
CREATE TABLE IF NOT EXISTS site_builder_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    block_key VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    icon VARCHAR(50) NULL,
    template_html TEXT NOT NULL,
    settings JSON NOT NULL DEFAULT '{}',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Export geçmişi
CREATE TABLE IF NOT EXISTS site_builder_exports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    export_type ENUM('zip', 'pwa', 'hosting') NOT NULL,
    file_count INT DEFAULT 0,
    file_path VARCHAR(500) NULL,
    license_type VARCHAR(50) NULL,
    license_hash VARCHAR(64) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lisanslar tablosu
CREATE TABLE IF NOT EXISTS site_builder_licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    license_type ENUM('single_domain', 'open_source') NOT NULL,
    licensed_domain VARCHAR(255) NULL,
    license_key VARCHAR(255) NOT NULL,
    license_hash VARCHAR(64) NOT NULL,
    expires_at DATE NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_id (project_id),
    INDEX idx_license_key (license_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Event logları
CREATE TABLE IF NOT EXISTS module_sitebuilder_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page_id INT NULL,
    event_type VARCHAR(80) NOT NULL,
    status VARCHAR(30) DEFAULT 'info',
    message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY page_id(page_id),
    KEY event_type(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Varsayılan şablonları ekle
INSERT IGNORE INTO site_builder_templates (template_key, name, category, description) VALUES
('hosting', 'Hosting Firması', 'business', 'Hosting ve domain satışı için ideal'),
('corporate', 'Kurumsal Şirket', 'business', 'Profesyonel kurumsal web sitesi'),
('software', 'Yazılım Firması', 'business', 'Yazılım şirketleri için'),
('agency', 'Ajans', 'business', 'Dijital pazarlama ajansları için'),
('radio', 'Radyo', 'media', 'Radyo ve medya siteleri için'),
('news', 'Haber', 'media', 'Haber ve blog siteleri için'),
('ecommerce', 'E-Ticaret Ön Site', 'ecommerce', 'E-ticaret giriş sayfası'),
('landing', 'Landing Page', 'marketing', 'Tek sayfa satış sitesi'),
('portfolio', 'Portfolyo', 'personal', 'Kişisel portfolyo sitesi'),
('blog', 'Blog', 'personal', 'Kişisel blog sitesi'),
('restaurant', 'Restoran', 'business', 'Restoran ve cafe siteleri için'),
('realestate', 'Emlak', 'business', 'Emlak ve gayrimenkul siteleri için');

-- Varsayılan blokları ekle
INSERT IGNORE INTO site_builder_blocks (block_key, name, category, icon, template_html) VALUES
('hero', 'Hero Section', 'layout', 'fa-image', '<section class="hero"><h1>{{title}}</h1><p>{{subtitle}}</p></section>'),
('text', 'Metin Bloğu', 'content', 'fa-font', '<section class="text-block"><h2>{{title}}</h2><p>{{body}}</p></section>'),
('image', 'Görsel', 'media', 'fa-photo', '<figure class="image-block"><img src="{{src}}" alt="{{alt}}"></figure>'),
('video', 'Video', 'media', 'fa-video', '<div class="video-block"><video src="{{src}}"></video></div>'),
('button', 'Buton', 'interactive', 'fa-hand-pointer', '<a href="{{url}}" class="btn btn-{{style}}">{{text}}</a>'),
('features', 'Özellikler', 'content', 'fa-star', '<section class="features"><h2>{{title}}</h2>{{#items}}<div class="feature">{{{item}}}</div>{{/items}}</section>'),
('pricing', 'Fiyatlandırma', 'business', 'fa-tags', '<section class="pricing">{{#plans}}<div class="plan">{{name}} - {{price}}</div>{{/plans}}</section>'),
('testimonials', 'Referanslar', 'social', 'fa-comments', '<section class="testimonials">{{#reviews}}<blockquote>{{text}}</blockquote>{{/reviews}}</section>'),
('faq', 'SSS', 'content', 'fa-question-circle', '<section class="faq">{{#items}}<div class="faq-item"><h3>{{question}}</h3><p>{{answer}}</p></div>{{/items}}</section>'),
('cta', 'CTA', 'interactive', 'fa-bullhorn', '<section class="cta"><h2>{{title}}</h2><a href="{{button_url}}">{{button_text}}</a></section>'),
('form', 'Form', 'interactive', 'fa-edit', '<form class="builder-form">{{{fields}}}</form>'),
('map', 'Harita', 'media', 'fa-map-marker-alt', '<div class="map-block">{{{embed_code}}}</div>'),
('counter', 'Sayaç', 'interactive', 'fa-calculator', '<section class="counter">{{#stats}}<span>{{value}}</span>{{/stats}}</section>'),
('gallery', 'Galeri', 'media', 'fa-images', '<div class="gallery">{{#images}}<img src="{{src}}">{{/images}}</div>'),
('team', 'Ekip', 'content', 'fa-users', '<section class="team">{{#members}}<div class="member"><h3>{{name}}</h3><p>{{role}}</p></div>{{/members}}</section>'),
('blog-grid', 'Blog Grid', 'content', 'fa-newspaper', '<div class="blog-grid">{{#posts}}<article><h3>{{title}}</h3></article>{{/posts}}</div>'),
('separator', 'Ayırıcı', 'layout', 'fa-minus', '<hr class="separator">'),
('spacer', 'Boşluk', 'layout', 'fa-arrows-alt-v', '<div class="spacer"></div>');
