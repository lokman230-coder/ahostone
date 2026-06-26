-- MobileBuilder Pro - Database Tables

-- Projeler tablosu
CREATE TABLE IF NOT EXISTS module_mobilebuilder_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL DEFAULT 'Yeni Uygulama',
    template VARCHAR(50) NOT NULL DEFAULT 'blank',
    package_name VARCHAR(255) NOT NULL,
    settings JSON NOT NULL DEFAULT '{}',
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Build geçmişi tablosu
CREATE TABLE IF NOT EXISTS module_mobilebuilder_builds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    build_type ENUM('apk', 'aab', 'pwa', 'source') NOT NULL,
    status ENUM('pending', 'building', 'completed', 'failed') DEFAULT 'pending',
    build_log JSON NULL,
    download_path VARCHAR(500) NULL,
    file_size BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_id (project_id),
    INDEX idx_build_type (build_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Şablonlar tablosu
CREATE TABLE IF NOT EXISTS module_mobilebuilder_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_key VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    features JSON NOT NULL DEFAULT '[]',
    preview_image VARCHAR(500) NULL,
    settings JSON NOT NULL DEFAULT '{}',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lisanslar tablosu
CREATE TABLE IF NOT EXISTS module_mobilebuilder_licenses (
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

-- Event logları tablosu
CREATE TABLE IF NOT EXISTS module_mobilebuilder_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NULL,
    event_type VARCHAR(80) NOT NULL,
    status VARCHAR(30) DEFAULT 'info',
    message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY project_id(project_id),
    KEY event_type(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Varsayılan şablonları ekle
INSERT IGNORE INTO module_mobilebuilder_templates (template_key, name, category, description, features) VALUES
('blank', 'Boş Uygulama', 'general', 'Sıfırdan başlayın', '["Özel tasarım", "Sınırsız sayfa", "Tüm özellikler"]'),
('realestate', 'Emlak Uygulaması', 'business', 'Gayrimenkul satış ve kiralama', '["İlan yönetimi", "Harita entegrasyonu", "Filtreleme", "Favoriler"]'),
('restaurant', 'Restoran Uygulaması', 'business', 'Restoran ve cafe için', '["Menü sistemi", "Rezervasyon", "Sipariş", "Promosyonlar"]'),
('radio', 'Radyo Uygulaması', 'media', 'Radyo ve podcast için', '["Canlı dinleme", "Podcast", "Program rehberi", "Bildirimler"]'),
('corporate', 'Kurumsal Uygulama', 'business', 'Şirketler için profesyonel uygulama', '["Hakkımızda", "Hizmetler", "Blog", "İletişim formu"]'),
('ecommerce', 'E-Ticaret Uygulaması', 'business', 'Online alışveriş için', '["Ürün kataloğu", "Sepet", "Ödeme", "Sipariş takibi"]'),
('news', 'Haber Uygulaması', 'media', 'Haber ve medya için', '["Kategori sistemi", "Bildirimler", "Video haber", "Arşiv"]'),
('education', 'Eğitim Uygulaması', 'education', 'Kurs ve eğitim platformu', '["Kurslar", "Video içerik", "Sınav sistemi", "Sertifika"]');
