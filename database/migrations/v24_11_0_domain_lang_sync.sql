
-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_domains`
--

CREATE TABLE IF NOT EXISTS `customer_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `domain_name` varchar(190) NOT NULL,
  `registrar` varchar(120) DEFAULT NULL,
  `registrar_id` int(11) DEFAULT NULL,
  `registrar_status` varchar(80) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `registration_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `lock_status` tinyint(1) DEFAULT 0,
  `transfer_lock` tinyint(1) DEFAULT 0,
  `auth_code` varchar(160) DEFAULT NULL,
  `ns1` varchar(255) DEFAULT NULL,
  `ns2` varchar(255) DEFAULT NULL,
  `ns3` varchar(255) DEFAULT NULL,
  `ns4` varchar(255) DEFAULT NULL,
  `last_synced_at` datetime DEFAULT NULL,
  `sync_status` varchar(50) DEFAULT NULL,
  `sync_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_name` (`domain_name`),
  KEY `customer_id` (`customer_id`),
  KEY `domain_id` (`domain_id`),
  KEY `service_id` (`service_id`),
  KEY `registrar_id` (`registrar_id`),
  KEY `expiry_date` (`expiry_date`),
  KEY `status` (`status`),
  KEY `last_synced_at` (`last_synced_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ahost One v24.11.0 - Domain cron sync + file language system + UI polish
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS last_synced_at DATETIME NULL;
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS registrar_status VARCHAR(80) NULL;
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS lock_status TINYINT(1) DEFAULT 0;
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS auto_renew TINYINT(1) DEFAULT 1;
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS ns1 VARCHAR(255) NULL;
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS ns2 VARCHAR(255) NULL;
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS ns3 VARCHAR(255) NULL;
ALTER TABLE customer_domains ADD COLUMN IF NOT EXISTS ns4 VARCHAR(255) NULL;
CREATE TABLE IF NOT EXISTS domain_sync_logs (id INT AUTO_INCREMENT PRIMARY KEY, domain_id INT NULL, registrar VARCHAR(120) NULL, status VARCHAR(40) NOT NULL DEFAULT 'pending', message TEXT NULL, payload MEDIUMTEXT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX(domain_id), INDEX(status), INDEX(created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS language_translations (id INT AUTO_INCREMENT PRIMARY KEY, lang_code VARCHAR(12) NOT NULL, translation_key VARCHAR(190) NOT NULL, translation_value TEXT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY lang_key (lang_code, translation_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO settings(setting_key,setting_value) VALUES ('enabled_languages','tr,en'),('language_file_sync','1'),('site_header_sticky','0') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
