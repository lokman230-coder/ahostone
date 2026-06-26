-- Ahost One v25.0.0 RC20 Fresh Install SQL
-- Temiz dağıtım şemasıdır; canlı admin hash, IP logu, gerçek URL veya üretim DB dump verisi içermez.
-- Admin hesabı install.php kurulum formunda girilen bilgilerle dinamik oluşturulur.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `ahost_one`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `abandoned_carts`
--

CREATE TABLE `abandoned_carts` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(190) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `items_json` longtext DEFAULT NULL,
  `total` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `status` varchar(40) DEFAULT 'open',
  `last_reminder_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_email` varchar(190) DEFAULT NULL,
  `type` varchar(40) DEFAULT 'admin',
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `security_question` varchar(255) DEFAULT 'İlk güvenlik cevabınız nedir?',
  `security_answer_hash` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'super_admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `admins`
--

-- Admin hesabı install.php kurulum formunda dinamik oluşturulur. Hazır admin seed verisi güvenlik için kaldırıldı.

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin_preferences`
--

CREATE TABLE `admin_preferences` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `pref_key` varchar(120) NOT NULL,
  `pref_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `admin_preferences`
--

INSERT INTO `admin_preferences` (`id`, `admin_id`, `pref_key`, `pref_value`, `updated_at`) VALUES
(2, 1, 'setup_wizard_popup_dismissed', '1', '2026-06-23 20:35:35');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin_search_index`
--

CREATE TABLE `admin_search_index` (
  `id` int(11) NOT NULL,
  `title` varchar(160) NOT NULL,
  `route` varchar(190) NOT NULL,
  `keywords` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `admin_search_index`
--

INSERT INTO `admin_search_index` (`id`, `title`, `route`, `keywords`, `category`, `module`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(367, 'Kredi Kartı Ayarları', 'admin/accounting/payment-fees', 'kredi kartı, kart komisyonu, sanal pos, iyzico, paytr, stripe, ödeme api, taksit, komisyon', 'Muhasebe', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(368, 'Sanal POS Yönetimi', 'admin/accounting/payment-fees', 'sanal pos, ödeme, kredi kartı, paytr, iyzico, shopier, param, sipay', 'Muhasebe', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(369, 'API Entegrasyonları', 'admin/api-integrations', 'api, entegrasyon, servis bağlantıları, webhook', 'API & Entegrasyonlar', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(370, 'Registrarlar', 'admin/domain-center/registrars', 'domainnameapi, registrar, epp, domain kayıt, transfer, yenileme', 'Domain', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(371, 'İletiMerkezi SMS', 'admin/notifications', 'sms, iletimerkezi, whatsapp, mail, bildirim, bakiye, özel mesaj', 'Bildirim', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(372, 'Theme Center', 'admin/theme-center/themes', 'tema, görünüm, site teması, admin teması, müşteri paneli teması, önizleme', 'Görünüm', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(373, 'Marketplace', 'admin/marketplace', 'marketplace, domain satışı, web tasarım, seo, logo, dijital içerik, öne çıkarma', 'Marketplace', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(374, 'Ürünler', 'admin/product-center/products', 'ürün, paket, hosting, vps, hizmet, sil, düzenle', 'Ürün', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(375, 'Scan & Report Center', 'admin/scan-report', 'tarama, rapor, pdf, çalışmayan, demo, health', 'Sistem', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(376, 'Sunucu API', 'admin/hosting-server/servers', 'whm, cpanel, directadmin, plesk, sunucu, hosting api', 'Hosting', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(377, 'Build Center', 'admin/build-center', 'android sdk gradle jdk apk aab build merkezi mobilebuilder', 'Sistem', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(378, 'APK AAB Build Kuyruğu', 'admin/build-center/queue', 'apk aab kuyruk gradle build log', 'Sistem', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(379, 'Commerce Complete', 'admin/commerce-complete', 'domain hosting marketplace tamamlandı commerce complete üretim kontrol', 'Ticaret', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(380, 'Marketplace Teklifleri', 'admin/marketplace/offers', 'teklif karşı teklif kabul red marketplace', 'Marketplace', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(381, 'Marketplace Escrow', 'admin/marketplace/escrow', 'escrow emanet ödeme iş teslim alıcı onay', 'Marketplace', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(382, 'Marketplace Açık Artırma', 'admin/marketplace/auctions', 'açık artırma auction teklif minimum artış', 'Marketplace', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(383, 'Hosting Sağlık Kontrolü', 'admin/hosting-server/health', 'hosting sağlık disk cpu ram load sunucu kontrol', 'Hosting', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(384, 'Domain Operasyon Logları', 'admin/domain-center/operations', 'domain kayıt yenileme transfer epp whois dns operasyon log', 'Domain', NULL, NULL, 1, '2026-06-23 20:35:28', NULL),
(511, 'SiteBuilder Pro', 'admin/site-builder', 'sitebuilder site builder sayfa oluştur zip export elementor sürükle bırak', 'Builder', NULL, NULL, 1, '2026-06-23 21:12:57', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `affiliates`
--

CREATE TABLE `affiliates` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `code` varchar(40) NOT NULL,
  `commission_rate` decimal(5,2) DEFAULT 10.00,
  `total_referrals` int(11) DEFAULT 0,
  `total_earned` decimal(12,2) DEFAULT 0.00,
  `pending_payout` decimal(12,2) DEFAULT 0.00,
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ai_copilot_threads`
--

CREATE TABLE `ai_copilot_threads` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `context_area` varchar(80) DEFAULT 'admin',
  `title` varchar(190) DEFAULT NULL,
  `messages_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ai_seo_audits`
--

CREATE TABLE `ai_seo_audits` (
  `id` int(11) NOT NULL,
  `target_type` varchar(80) DEFAULT 'product',
  `target_id` int(11) DEFAULT NULL,
  `keyword` varchar(190) DEFAULT NULL,
  `title_score` int(11) DEFAULT 0,
  `content_score` int(11) DEFAULT 0,
  `technical_score` int(11) DEFAULT 0,
  `competitor_score` int(11) DEFAULT 0,
  `overall_score` int(11) DEFAULT 0,
  `warnings_json` longtext DEFAULT NULL,
  `suggestions_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `type` varchar(40) DEFAULT 'info',
  `target` varchar(80) DEFAULT 'all',
  `channel` varchar(80) DEFAULT 'panel',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `api_integrations`
--

CREATE TABLE `api_integrations` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `provider` varchar(80) NOT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `username` varchar(190) DEFAULT NULL,
  `secret` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'inactive',
  `test_mode` tinyint(1) DEFAULT 1,
  `last_test_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `api_logs`
--

CREATE TABLE `api_logs` (
  `id` int(11) NOT NULL,
  `provider` varchar(120) NOT NULL,
  `action` varchar(120) NOT NULL,
  `status` varchar(40) DEFAULT 'info',
  `message` text DEFAULT NULL,
  `payload` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `asset_version_registry`
--

CREATE TABLE `asset_version_registry` (
  `id` int(11) NOT NULL,
  `area` varchar(80) NOT NULL,
  `asset_group` varchar(120) NOT NULL,
  `version` varchar(40) NOT NULL DEFAULT '24.3.1',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `auth_login_events`
--

CREATE TABLE `auth_login_events` (
  `id` int(11) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `event_type` varchar(80) NOT NULL,
  `method` varchar(30) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'info',
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `auth_login_events`
-- Fresh install paketinde login geçmişi seed verisi bulunmaz.

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `auth_mfa_profiles`
--

CREATE TABLE `auth_mfa_profiles` (
  `id` int(11) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `enabled` tinyint(1) DEFAULT 0,
  `preferred_method` varchar(30) DEFAULT 'mail',
  `totp_secret` varchar(80) DEFAULT NULL,
  `recovery_codes` text DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `auth_otp_tokens`
--

CREATE TABLE `auth_otp_tokens` (
  `id` int(11) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method` varchar(30) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `destination` varchar(190) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `automation_logs`
--

CREATE TABLE `automation_logs` (
  `id` int(11) NOT NULL,
  `rule_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'success',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `automation_rules`
--

CREATE TABLE `automation_rules` (
  `id` int(11) NOT NULL,
  `rule_key` varchar(120) DEFAULT NULL,
  `name` varchar(190) NOT NULL,
  `trigger_event` varchar(120) DEFAULT NULL,
  `action_type` varchar(80) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `config_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bridge_connections`
--

CREATE TABLE `bridge_connections` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `source_type` varchar(40) DEFAULT 'migration',
  `source_mode` varchar(40) DEFAULT 'database',
  `source_host` varchar(190) NOT NULL,
  `source_port` int(11) DEFAULT NULL,
  `source_ssl` tinyint(1) DEFAULT 0,
  `source_database` varchar(190) NOT NULL,
  `source_username` varchar(190) NOT NULL,
  `source_password` text DEFAULT NULL,
  `source_sql_path` varchar(255) DEFAULT NULL,
  `source_charset` varchar(40) DEFAULT 'utf8mb4',
  `table_prefix` varchar(40) DEFAULT 'tbl',
  `status` varchar(40) DEFAULT 'ready',
  `last_test_status` varchar(40) DEFAULT NULL,
  `last_test_message` text DEFAULT NULL,
  `last_test_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bridge_import_maps`
--

CREATE TABLE `bridge_import_maps` (
  `id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `entity_type` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `target_table` varchar(120) NOT NULL,
  `target_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bridge_import_selections`
--

CREATE TABLE `bridge_import_selections` (
  `id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `entity_type` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `source_label` varchar(255) DEFAULT NULL,
  `selected` tinyint(1) DEFAULT 1,
  `payload_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bridge_items`
--

CREATE TABLE `bridge_items` (
  `id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `entity_type` varchar(80) NOT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `source_label` varchar(255) DEFAULT NULL,
  `target_table` varchar(120) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `action_name` varchar(80) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'ok',
  `message` text DEFAULT NULL,
  `payload_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bridge_runs`
--

CREATE TABLE `bridge_runs` (
  `id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `run_type` varchar(40) NOT NULL,
  `status` varchar(40) DEFAULT 'running',
  `summary_json` longtext DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bridge_sql_uploads`
--

CREATE TABLE `bridge_sql_uploads` (
  `id` int(11) NOT NULL,
  `connection_id` int(11) DEFAULT NULL,
  `source_type` varchar(40) DEFAULT 'migration',
  `original_name` varchar(255) DEFAULT NULL,
  `stored_path` varchar(255) NOT NULL,
  `sql_file_name` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'uploaded',
  `message` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `build_repository_files`
--

CREATE TABLE `build_repository_files` (
  `id` int(11) NOT NULL,
  `project_name` varchar(190) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT 'apk',
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT 0,
  `status` varchar(40) DEFAULT 'ready',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `client_preferences`
--

CREATE TABLE `client_preferences` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `site_theme_id` int(11) DEFAULT NULL,
  `client_theme_id` int(11) DEFAULT NULL,
  `builder_layout_json` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `client_security_questions`
--

CREATE TABLE `client_security_questions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `question` varchar(190) NOT NULL,
  `answer_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `commerce_completion_checks`
--

CREATE TABLE `commerce_completion_checks` (
  `id` int(11) NOT NULL,
  `module_key` varchar(120) NOT NULL,
  `check_key` varchar(120) NOT NULL,
  `title` varchar(190) NOT NULL,
  `status` enum('pass','warning','fail') DEFAULT 'warning',
  `detail` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `commerce_completion_checks`
--

INSERT INTO `commerce_completion_checks` (`id`, `module_key`, `check_key`, `title`, `status`, `detail`, `updated_at`) VALUES
(141, 'domain', 'registrars', 'Registrar operasyonları', 'warning', 'DomainNameAPI aktif; diğer registrarlar test bekliyor.', '2026-06-23 20:35:28'),
(142, 'domain', 'intelligence', 'Domain Intelligence', 'pass', 'SSL/DNS/WHOIS/SEO/değerleme ekranları mevcut.', '2026-06-23 20:35:28'),
(143, 'hosting', 'operations', 'Hosting operasyonları', 'warning', 'Create/suspend/unsuspend/terminate kuyruk ve butonları mevcut; canlı panel testi gerekir.', '2026-06-23 20:35:28'),
(144, 'hosting', 'health', 'Hosting sağlık kontrolü', 'pass', 'Sunucu ve hizmet sağlık kayıt tablosu hazır.', '2026-06-23 20:35:28'),
(145, 'marketplace', 'categories', 'Çoklu marketplace kategori', 'pass', 'Domain, hosting, web tasarım, SEO, logo, mobil uygulama, script ve dijital ürün kategorileri eklendi.', '2026-06-23 20:35:28'),
(146, 'marketplace', 'escrow', 'Escrow iş akışı', 'pass', 'Escrow kayıt altyapısı hazır.', '2026-06-23 20:35:28'),
(147, 'marketplace', 'featured', 'Öne çıkarma paketleri', 'pass', '7/15/30/60 gün paketleri tekil.', '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `configurable_options`
--

CREATE TABLE `configurable_options` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `option_type` varchar(40) DEFAULT 'select',
  `is_required` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `configurable_option_values`
--

CREATE TABLE `configurable_option_values` (
  `id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `label` varchar(160) NOT NULL,
  `value` varchar(160) DEFAULT NULL,
  `price_delta` decimal(14,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `contents`
--

CREATE TABLE `contents` (
  `id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(220) NOT NULL,
  `slug` varchar(240) NOT NULL,
  `summary` text DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `cover_image_url` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'published',
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `content_categories`
--

CREATE TABLE `content_categories` (
  `id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(190) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `content_media`
--

CREATE TABLE `content_media` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `media_type` varchar(40) DEFAULT 'image',
  `file_url` varchar(255) NOT NULL,
  `title` varchar(190) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `credit_transactions`
--

CREATE TABLE `credit_transactions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `type` varchar(40) DEFAULT 'credit',
  `amount` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `first_name` varchar(120) DEFAULT NULL,
  `last_name` varchar(120) DEFAULT NULL,
  `company_name` varchar(190) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` varchar(120) DEFAULT NULL,
  `postcode` varchar(40) DEFAULT NULL,
  `country` varchar(80) DEFAULT NULL,
  `tax_number` varchar(80) DEFAULT NULL,
  `tc_identity_no` varchar(11) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `tc_no` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `identity_verified` tinyint(1) DEFAULT 0,
  `identity_verified_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `credit_balance` decimal(14,2) DEFAULT 0.00,
  `balance` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `language` varchar(20) DEFAULT 'tr',
  `notes` text DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer_hash` varchar(255) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `restored_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_account_users`
--

CREATE TABLE `customer_account_users` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `name` varchar(190) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `role_key` varchar(80) DEFAULT 'viewer',
  `permissions_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'invited',
  `invite_token_hash` varchar(190) DEFAULT NULL,
  `invited_at` datetime DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_activity_logs`
--

CREATE TABLE `customer_activity_logs` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_connected_sites`
--

CREATE TABLE `customer_connected_sites` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `site_name` varchar(190) DEFAULT NULL,
  `site_url` varchar(255) NOT NULL,
  `domain` varchar(190) DEFAULT NULL,
  `connection_type` varchar(40) DEFAULT 'sftp',
  `host` varchar(190) DEFAULT NULL,
  `port` int(11) DEFAULT 22,
  `username` varchar(190) DEFAULT NULL,
  `encrypted_secret` longtext DEFAULT NULL,
  `panel_type` varchar(80) DEFAULT NULL,
  `panel_url` varchar(255) DEFAULT NULL,
  `db_host` varchar(190) DEFAULT NULL,
  `db_name` varchar(190) DEFAULT NULL,
  `db_user` varchar(190) DEFAULT NULL,
  `encrypted_db_pass` longtext DEFAULT NULL,
  `install_path` varchar(255) DEFAULT NULL,
  `license_key` varchar(160) DEFAULT NULL,
  `last_backup_at` datetime DEFAULT NULL,
  `last_update_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_domains`
--

CREATE TABLE `customer_domains` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_groups`
--

CREATE TABLE `customer_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `discount_percent` decimal(6,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_group_members`
--

CREATE TABLE `customer_group_members` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_notifications`
--

CREATE TABLE `customer_notifications` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `title` varchar(190) NOT NULL,
  `message` text DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_payment_methods`
--

CREATE TABLE `customer_payment_methods` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider` varchar(80) NOT NULL,
  `provider_customer_token` varchar(190) DEFAULT NULL,
  `card_token` varchar(190) NOT NULL,
  `card_brand` varchar(40) DEFAULT NULL,
  `masked_card` varchar(40) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_product_update_status`
--

CREATE TABLE `customer_product_update_status` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `licensed_domain` varchar(190) DEFAULT NULL,
  `current_version` varchar(60) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'available',
  `message` text DEFAULT NULL,
  `applied_at` datetime DEFAULT NULL,
  `dismissed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_site_backups`
--

CREATE TABLE `customer_site_backups` (
  `id` int(11) NOT NULL,
  `connected_site_id` int(11) NOT NULL,
  `backup_type` varchar(40) DEFAULT 'full',
  `file_backup_path` varchar(255) DEFAULT NULL,
  `sql_backup_path` varchar(255) DEFAULT NULL,
  `rollback_token` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'created',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_user_activity_logs`
--

CREATE TABLE `customer_user_activity_logs` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `account_user_id` int(11) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_user_sessions`
--

CREATE TABLE `customer_user_sessions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `account_user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_seen_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domains`
--

CREATE TABLE `domains` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `domain_name` varchar(190) NOT NULL,
  `registrar_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `registration_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 0,
  `lock_status` tinyint(1) DEFAULT 0,
  `auth_code` varchar(160) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_bulk_operation_queue`
--

CREATE TABLE `domain_bulk_operation_queue` (
  `id` int(11) NOT NULL,
  `domain_name` varchar(190) NOT NULL,
  `operation` varchar(80) NOT NULL,
  `payload` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_contacts`
--

CREATE TABLE `domain_contacts` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `registrant_name` varchar(190) DEFAULT NULL,
  `registrant_email` varchar(190) DEFAULT NULL,
  `registrant_phone` varchar(80) DEFAULT NULL,
  `raw_json` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_dns_records`
--

CREATE TABLE `domain_dns_records` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `record_type` varchar(20) DEFAULT 'A',
  `host` varchar(190) DEFAULT '@',
  `record_value` text DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `ttl` int(11) DEFAULT 3600,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_document_rules`
--

CREATE TABLE `domain_document_rules` (
  `id` int(11) NOT NULL,
  `tld` varchar(40) NOT NULL,
  `required_docs` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_intelligence_reports`
--

CREATE TABLE `domain_intelligence_reports` (
  `id` int(11) NOT NULL,
  `domain_name` varchar(190) NOT NULL,
  `ssl_score` int(11) DEFAULT 0,
  `dns_score` int(11) DEFAULT 0,
  `seo_score` int(11) DEFAULT 0,
  `traffic_score` int(11) DEFAULT 0,
  `valuation_score` int(11) DEFAULT 0,
  `estimated_value` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `summary` text DEFAULT NULL,
  `raw_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_investment_watchlist`
--

CREATE TABLE `domain_investment_watchlist` (
  `id` int(11) NOT NULL,
  `domain` varchar(190) NOT NULL,
  `keyword` varchar(190) DEFAULT NULL,
  `tld` varchar(40) DEFAULT NULL,
  `estimated_value` decimal(14,2) DEFAULT 0.00,
  `saleability_score` int(11) DEFAULT 0,
  `drop_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'watching',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_nameservers`
--

CREATE TABLE `domain_nameservers` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `ns1` varchar(190) DEFAULT NULL,
  `ns2` varchar(190) DEFAULT NULL,
  `ns3` varchar(190) DEFAULT NULL,
  `ns4` varchar(190) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_operation_logs`
--

CREATE TABLE `domain_operation_logs` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `domain_name` varchar(190) NOT NULL,
  `operation` varchar(80) NOT NULL,
  `registrar` varchar(120) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `raw_response` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_order_routes`
--

CREATE TABLE `domain_order_routes` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `domain` varchar(190) NOT NULL,
  `tld` varchar(40) NOT NULL,
  `selected_registrar` varchar(140) NOT NULL,
  `registrar_cost` decimal(12,4) DEFAULT 0.0000,
  `sale_price` decimal(12,4) DEFAULT 0.0000,
  `currency` varchar(10) DEFAULT 'USD',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_price_cache`
--

CREATE TABLE `domain_price_cache` (
  `id` int(11) NOT NULL,
  `tld` varchar(40) NOT NULL,
  `registrar` varchar(80) DEFAULT 'domainnameapi',
  `cost_usd` decimal(12,4) DEFAULT 0.0000,
  `commission_percent` decimal(6,2) DEFAULT 20.00,
  `sale_usd` decimal(12,4) DEFAULT 0.0000,
  `sale_try` decimal(12,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `domain_price_cache`
--

INSERT INTO `domain_price_cache` (`id`, `tld`, `registrar`, `cost_usd`, `commission_percent`, `sale_usd`, `sale_try`, `updated_at`) VALUES
(131, 'com', 'domainnameapi', 10.0000, 20.00, 12.0000, 567.00, '2026-06-23 20:35:28'),
(132, 'net', 'domainnameapi', 12.0000, 20.00, 14.4000, 680.40, '2026-06-23 20:35:28'),
(133, 'org', 'domainnameapi', 11.0000, 20.00, 13.2000, 623.70, '2026-06-23 20:35:28'),
(134, 'com.tr', 'domainnameapi', 8.5000, 20.00, 10.2000, 481.95, '2026-06-23 20:35:28'),
(135, 'net.tr', 'domainnameapi', 8.0000, 20.00, 9.6000, 453.60, '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_price_import_logs`
--

CREATE TABLE `domain_price_import_logs` (
  `id` int(11) NOT NULL,
  `registrar_slug` varchar(120) DEFAULT NULL,
  `source` varchar(120) DEFAULT 'manual',
  `imported_count` int(11) DEFAULT 0,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_pricing_rules`
--

CREATE TABLE `domain_pricing_rules` (
  `id` int(11) NOT NULL,
  `tld` varchar(40) NOT NULL,
  `mode` varchar(30) DEFAULT 'percent',
  `markup_percent` decimal(8,2) DEFAULT 30.00,
  `markup_fixed` decimal(12,2) DEFAULT 0.00,
  `min_profit` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'USD',
  `registrar_override` varchar(140) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `domain_pricing_rules`
--

INSERT INTO `domain_pricing_rules` (`id`, `tld`, `mode`, `markup_percent`, `markup_fixed`, `min_profit`, `currency`, `registrar_override`, `is_active`, `created_at`) VALUES
(5, '.com', 'percent', 30.00, 0.00, 3.00, 'USD', NULL, 1, '2026-06-23 20:35:28'),
(6, '.net', 'percent', 30.00, 0.00, 3.00, 'USD', NULL, 1, '2026-06-23 20:35:28'),
(7, '.org', 'percent', 30.00, 0.00, 3.00, 'USD', NULL, 1, '2026-06-23 20:35:28'),
(8, '.com.tr', 'percent', 35.00, 0.00, 2.00, 'USD', NULL, 1, '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_registrars`
--

CREATE TABLE `domain_registrars` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `module_name` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'inactive',
  `test_mode` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_ssl_checks`
--

CREATE TABLE `domain_ssl_checks` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `domain_name` varchar(190) NOT NULL,
  `issuer` varchar(190) DEFAULT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'unknown',
  `checked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_sync_logs`
--

CREATE TABLE `domain_sync_logs` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `registrar` varchar(120) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `payload` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `domain_whois_records`
--

CREATE TABLE `domain_whois_records` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `domain_name` varchar(190) NOT NULL,
  `whois_text` longtext DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `checked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hosting_accounts`
--

CREATE TABLE `hosting_accounts` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `server_name` varchar(190) DEFAULT NULL,
  `server_ip` varchar(80) DEFAULT NULL,
  `username` varchar(120) DEFAULT NULL,
  `whm_username` varchar(120) DEFAULT NULL,
  `panel_password` text DEFAULT NULL,
  `package_name` varchar(160) DEFAULT NULL,
  `disk_mb` int(11) DEFAULT 0,
  `disk_used_mb` int(11) DEFAULT 0,
  `bandwidth_mb` int(11) DEFAULT 0,
  `bandwidth_used_mb` int(11) DEFAULT 0,
  `mail_limit` int(11) DEFAULT 0,
  `mail_used` int(11) DEFAULT 0,
  `mysql_limit` int(11) DEFAULT 0,
  `mysql_used` int(11) DEFAULT 0,
  `cpu_percent` int(11) DEFAULT 0,
  `ram_mb` int(11) DEFAULT 0,
  `ram_used_mb` int(11) DEFAULT 0,
  `inode_limit` int(11) DEFAULT 0,
  `inode_used` int(11) DEFAULT 0,
  `ftp_limit` int(11) DEFAULT 0,
  `ftp_used` int(11) DEFAULT 0,
  `cron_limit` int(11) DEFAULT 0,
  `cron_used` int(11) DEFAULT 0,
  `addon_domain_limit` int(11) DEFAULT 0,
  `addon_domain_used` int(11) DEFAULT 0,
  `subdomain_limit` int(11) DEFAULT 0,
  `subdomain_used` int(11) DEFAULT 0,
  `cpanel_url` varchar(255) DEFAULT NULL,
  `directadmin_url` varchar(255) DEFAULT NULL,
  `plesk_url` varchar(255) DEFAULT NULL,
  `webmail_url` varchar(255) DEFAULT NULL,
  `whm_url` varchar(255) DEFAULT NULL,
  `vps_panel_url` varchar(255) DEFAULT NULL,
  `ns1` varchar(190) DEFAULT NULL,
  `ns2` varchar(190) DEFAULT NULL,
  `disk_used` decimal(14,2) DEFAULT 0.00,
  `disk_limit` decimal(14,2) DEFAULT 0.00,
  `bandwidth_used` decimal(14,2) DEFAULT 0.00,
  `bandwidth_limit` decimal(14,2) DEFAULT 0.00,
  `suspended_at` datetime DEFAULT NULL,
  `terminated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hosting_account_logs`
--

CREATE TABLE `hosting_account_logs` (
  `id` int(11) NOT NULL,
  `hosting_account_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hosting_automation_settings`
--

CREATE TABLE `hosting_automation_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(120) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `hosting_automation_settings`
--

INSERT INTO `hosting_automation_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(153, 'hosting_suspend_day', '1', '2026-06-23 20:35:28'),
(154, 'hosting_terminate_day', '16', '2026-06-23 20:35:28'),
(155, 'hosting_reminder_days', '1,3,7,10,15', '2026-06-23 20:35:28'),
(156, 'notify_mail', '1', '2026-06-23 20:35:28'),
(157, 'notify_sms', '1', '2026-06-23 20:35:28'),
(158, 'notify_whatsapp', '1', '2026-06-23 20:35:28'),
(159, 'auto_renew_credit_first', '1', '2026-06-23 20:35:28'),
(160, 'stored_card_mode', 'token_only', '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hosting_health_checks`
--

CREATE TABLE `hosting_health_checks` (
  `id` int(11) NOT NULL,
  `server_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `check_type` varchar(80) DEFAULT 'server',
  `status` enum('pass','warning','fail') DEFAULT 'pass',
  `load_avg` varchar(80) DEFAULT NULL,
  `disk_percent` decimal(6,2) DEFAULT NULL,
  `memory_percent` decimal(6,2) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `checked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hosting_operation_queue`
--

CREATE TABLE `hosting_operation_queue` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `operation` varchar(80) NOT NULL,
  `status` enum('pending','running','done','failed') DEFAULT 'pending',
  `request_payload` longtext DEFAULT NULL,
  `response_payload` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `executed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hosting_password_change_logs`
--

CREATE TABLE `hosting_password_change_logs` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `hosting_account_id` int(11) DEFAULT NULL,
  `actor_type` varchar(40) DEFAULT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `sync_status` varchar(40) DEFAULT 'queued',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(80) NOT NULL,
  `status` varchar(40) DEFAULT 'unpaid',
  `subtotal` decimal(14,2) DEFAULT 0.00,
  `tax` decimal(14,2) DEFAULT 0.00,
  `total` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `due_date` date DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `invoice_activity_logs`
--

CREATE TABLE `invoice_activity_logs` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `invoice_email_logs`
--

CREATE TABLE `invoice_email_logs` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `recipient_email` varchar(190) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `language_translations`
--

CREATE TABLE `language_translations` (
  `id` int(11) NOT NULL,
  `lang_code` varchar(12) NOT NULL,
  `translation_key` varchar(190) NOT NULL,
  `translation_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `license_injection_jobs`
--

CREATE TABLE `license_injection_jobs` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_type` varchar(80) DEFAULT 'web',
  `original_zip_path` varchar(255) DEFAULT NULL,
  `licensed_zip_path` varchar(255) DEFAULT NULL,
  `license_key` varchar(160) DEFAULT NULL,
  `target_domain` varchar(190) DEFAULT NULL,
  `target_package_name` varchar(190) DEFAULT NULL,
  `license_type` varchar(40) DEFAULT 'subscription',
  `expires_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'pending',
  `scan_report` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `license_private_keys`
--

CREATE TABLE `license_private_keys` (
  `id` int(11) NOT NULL,
  `key_name` varchar(120) NOT NULL,
  `algorithm` varchar(80) DEFAULT 'RSA-4096-SHA256',
  `encrypted_private_key` longtext DEFAULT NULL,
  `public_key` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_auctions`
--

CREATE TABLE `marketplace_auctions` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `start_price` decimal(14,2) DEFAULT 0.00,
  `min_increment` decimal(14,2) DEFAULT 10.00,
  `buy_now_price` decimal(14,2) DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `status` enum('draft','active','ended','cancelled') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_categories`
--

CREATE TABLE `marketplace_categories` (
  `id` int(11) NOT NULL,
  `slug` varchar(90) NOT NULL,
  `name` varchar(160) NOT NULL,
  `listing_type` varchar(60) DEFAULT 'service',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `marketplace_categories`
--

INSERT INTO `marketplace_categories` (`id`, `slug`, `name`, `listing_type`, `is_active`, `sort_order`) VALUES
(341, 'domain', 'Domain', 'domain', 1, 10),
(342, 'web-design', 'Web Tasarım', 'web_design', 1, 2),
(343, 'seo', 'SEO Paketi', 'service', 1, 40),
(344, 'logo-design', 'Logo Tasarımı', 'logo_design', 1, 4),
(345, 'digital-content', 'Dijital İçerikler', 'digital_content', 1, 5),
(346, 'mobile-app', 'Mobil Uygulama', 'mobile_app', 1, 6),
(347, 'hosting-service', 'Hosting Hizmeti', 'hosting', 1, 7),
(348, 'software', 'Yazılım / Script', 'software', 1, 8),
(350, 'hosting', 'Hosting', 'hosting', 1, 20),
(351, 'web-tasarim', 'Web Tasarım', 'service', 1, 30),
(353, 'logo-tasarim', 'Logo Tasarımı', 'service', 1, 50),
(354, 'mobil-uygulama', 'Mobil Uygulama', 'service', 1, 60),
(355, 'script-yazilim', 'Script / Yazılım', 'digital', 1, 70),
(356, 'dijital-urun', 'Dijital Ürün', 'digital', 1, 80),
(357, 'freelancer-hizmet', 'Freelancer Hizmeti', 'service', 1, 90);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_escrow`
--

CREATE TABLE `marketplace_escrow` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `buyer_customer_id` int(11) DEFAULT NULL,
  `seller_customer_id` int(11) DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `status` enum('pending','funded','delivered','approved','released','disputed','refunded') DEFAULT 'pending',
  `release_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_escrow_transactions`
--

CREATE TABLE `marketplace_escrow_transactions` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `offer_id` int(11) DEFAULT NULL,
  `buyer_customer_id` int(11) DEFAULT NULL,
  `seller_customer_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `commission_percent` decimal(8,2) DEFAULT 5.00,
  `status` varchar(40) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_feature_packages`
--

CREATE TABLE `marketplace_feature_packages` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `days` int(11) NOT NULL,
  `price` decimal(14,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'TRY',
  `badge` varchar(80) DEFAULT 'Öne Çıkan',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `marketplace_feature_packages`
--

INSERT INTO `marketplace_feature_packages` (`id`, `name`, `days`, `price`, `currency`, `badge`, `is_active`, `created_at`) VALUES
(241, 'Öne Çıkarma 7 Gün', 7, 99.00, 'TRY', 'Öne Çıkan', 1, '2026-06-23 20:35:28'),
(242, 'Öne Çıkarma 15 Gün', 15, 179.00, 'TRY', 'Öne Çıkan', 1, '2026-06-23 20:35:28'),
(243, 'Öne Çıkarma 30 Gün', 30, 299.00, 'TRY', 'Öne Çıkan', 1, '2026-06-23 20:35:28'),
(244, 'Öne Çıkarma 60 Gün', 60, 499.00, 'TRY', 'Öne Çıkan', 1, '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_listings`
--

CREATE TABLE `marketplace_listings` (
  `id` int(11) NOT NULL,
  `seller_type` enum('admin','customer') DEFAULT 'admin',
  `seller_customer_id` int(11) DEFAULT NULL,
  `listing_type` varchar(60) DEFAULT 'domain',
  `title` varchar(190) NOT NULL,
  `domain_name` varchar(190) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `price` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `status` enum('draft','active','pending','sold','passive') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `featured_until` datetime DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `is_urgent` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sale_model` enum('fixed','offer','auction') DEFAULT 'fixed',
  `commission_percent` decimal(8,2) DEFAULT 5.00,
  `delivery_days` int(11) DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_offers`
--

CREATE TABLE `marketplace_offers` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `name` varchar(160) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `offer_amount` decimal(14,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'TRY',
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','countered') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `counter_amount` decimal(14,2) DEFAULT NULL,
  `admin_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_revenue`
--

CREATE TABLE `marketplace_revenue` (
  `id` int(11) NOT NULL,
  `source_type` varchar(80) NOT NULL,
  `source_id` int(11) DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `marketplace_seller_profiles`
--

CREATE TABLE `marketplace_seller_profiles` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `display_name` varchar(190) NOT NULL,
  `rating` decimal(3,2) DEFAULT 5.00,
  `sales_count` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `badges` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `menu_key` varchar(120) NOT NULL,
  `title` varchar(190) NOT NULL,
  `location` varchar(60) NOT NULL DEFAULT 'site',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `label` varchar(190) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(80) DEFAULT NULL,
  `badge` varchar(60) DEFAULT NULL,
  `target` varchar(30) DEFAULT '_self',
  `visibility` varchar(60) DEFAULT 'public',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `module_update_logs`
--

CREATE TABLE `module_update_logs` (
  `id` int(11) NOT NULL,
  `module_key` varchar(120) DEFAULT NULL,
  `action` varchar(80) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'success',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `module_visibility`
--

CREATE TABLE `module_visibility` (
  `id` int(11) NOT NULL,
  `module_key` varchar(120) NOT NULL,
  `title` varchar(190) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `route` varchar(190) DEFAULT NULL,
  `category` varchar(80) DEFAULT 'core',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `module_visibility`
--

INSERT INTO `module_visibility` (`id`, `module_key`, `title`, `is_enabled`, `route`, `category`, `updated_at`) VALUES
(12, 'domain', 'Domain Center', 1, 'admin/domain-center', 'commerce', '2026-06-23 20:35:35'),
(13, 'hosting', 'Hosting & Server', 1, 'admin/hosting-server', 'commerce', '2026-06-23 20:35:35'),
(14, 'marketplace', 'Marketplace', 1, 'admin/marketplace', 'commerce', '2026-06-23 20:35:35'),
(15, 'sitebuilder', 'SiteBuilder', 1, 'admin/site-builder', 'builder', '2026-06-23 20:35:35'),
(16, 'mobilebuilder', 'MobileBuilder', 1, 'admin/mobile-builder', 'builder', '2026-06-23 20:35:35'),
(17, 'buildcenter', 'Build Center', 1, 'admin/build-center', 'builder', '2026-06-23 20:35:35'),
(18, 'license', 'License Center', 1, 'admin/license-center', 'system', '2026-06-23 20:35:35'),
(19, 'notification', 'Notification Center', 1, 'admin/notification-center', 'system', '2026-06-23 20:35:35'),
(20, 'backup', 'Backup Center', 1, 'admin/backup-center', 'system', '2026-06-23 20:35:35'),
(21, 'scan', 'Scan & Report', 1, 'admin/scan-report', 'system', '2026-06-23 20:35:35'),
(22, 'ai', 'AI Center', 1, 'admin/ai-center', 'ai', '2026-06-23 20:35:35');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notification_channels`
--

CREATE TABLE `notification_channels` (
  `id` int(11) NOT NULL,
  `channel_type` varchar(40) DEFAULT NULL,
  `channel` varchar(40) DEFAULT NULL,
  `provider` varchar(120) DEFAULT NULL,
  `name` varchar(190) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `config_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'inactive',
  `test_mode` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 10,
  `sender_name` varchar(190) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `channel_type` varchar(40) DEFAULT NULL,
  `provider` varchar(120) DEFAULT NULL,
  `channel` varchar(40) DEFAULT NULL,
  `recipient` varchar(190) DEFAULT NULL,
  `subject` varchar(190) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `provider_response` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_key` varchar(120) DEFAULT NULL,
  `response_code` varchar(20) DEFAULT NULL,
  `response_body` longtext DEFAULT NULL,
  `payload_json` longtext DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `event_key` varchar(120) DEFAULT NULL,
  `template_key` varchar(120) NOT NULL,
  `channel` varchar(40) DEFAULT 'email',
  `subject` varchar(190) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `title` varchar(190) DEFAULT NULL,
  `sms_body` text DEFAULT NULL,
  `whatsapp_body` text DEFAULT NULL,
  `email_subject` varchar(190) DEFAULT NULL,
  `email_body` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `event_key`, `template_key`, `channel`, `subject`, `body`, `is_active`, `title`, `sms_body`, `whatsapp_body`, `email_subject`, `email_body`) VALUES
(161, 'domain_epp_code', '', 'email', NULL, NULL, 1, 'Domain EPP Kodu', 'Sayın {customer_name}, {domain} transfer kodunuz: {epp_code}.', 'Sayın {customer_name}, {domain} transfer kodunuz: {epp_code}.', 'Domain EPP Kodu', 'Sayın {customer_name}, {domain} transfer kodunuz: {epp_code}.');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_number` varchar(80) NOT NULL,
  `status` varchar(40) DEFAULT 'pending',
  `total` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `payment_method` varchar(80) DEFAULT 'manual',
  `fraud_score` int(11) DEFAULT 0,
  `provision_status` varchar(40) DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `domain` varchar(190) DEFAULT NULL,
  `billing_cycle` varchar(40) DEFAULT 'monthly',
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(14,2) DEFAULT 0.00,
  `total` decimal(14,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `order_status_logs`
--

CREATE TABLE `order_status_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `old_status` varchar(60) DEFAULT NULL,
  `new_status` varchar(60) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `package_builders`
--

CREATE TABLE `package_builders` (
  `id` int(11) NOT NULL,
  `name` varchar(190) NOT NULL,
  `base_price` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `disk_price` decimal(14,2) DEFAULT 0.00,
  `traffic_price` decimal(14,2) DEFAULT 0.00,
  `email_price` decimal(14,2) DEFAULT 0.00,
  `requires_admin_approval` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `token_hash` varchar(190) NOT NULL,
  `channel` varchar(40) DEFAULT 'email',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `gateway` varchar(80) DEFAULT 'manual',
  `amount` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `status` varchar(40) DEFAULT 'completed',
  `transaction_id` varchar(160) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payment_fee_rules`
--

CREATE TABLE `payment_fee_rules` (
  `id` int(11) NOT NULL,
  `gateway` varchar(120) NOT NULL,
  `label` varchar(160) NOT NULL,
  `invoice_line_label` varchar(160) DEFAULT 'Kart İşlem Komisyonu',
  `fee_percent` decimal(8,3) DEFAULT 0.000,
  `last_known_fee_percent` decimal(8,3) DEFAULT 0.000,
  `fee_fixed` decimal(12,4) DEFAULT 0.0000,
  `last_known_fee_fixed` decimal(12,4) DEFAULT 0.0000,
  `currency` varchar(10) DEFAULT 'TRY',
  `rate_source` varchar(30) DEFAULT 'manual',
  `api_enabled` tinyint(1) DEFAULT 0,
  `api_endpoint` varchar(255) DEFAULT NULL,
  `api_auth_json` longtext DEFAULT NULL,
  `last_synced_at` datetime DEFAULT NULL,
  `last_sync_status` varchar(40) DEFAULT NULL,
  `last_sync_message` text DEFAULT NULL,
  `payer_mode` varchar(40) DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `payment_fee_rules`
--

INSERT INTO `payment_fee_rules` (`id`, `gateway`, `label`, `invoice_line_label`, `fee_percent`, `last_known_fee_percent`, `fee_fixed`, `last_known_fee_fixed`, `currency`, `rate_source`, `api_enabled`, `api_endpoint`, `api_auth_json`, `last_synced_at`, `last_sync_status`, `last_sync_message`, `payer_mode`, `is_active`, `created_at`) VALUES
(5, 'paytr', 'PayTR Kredi Kartı', 'Kart İşlem Komisyonu', 2.990, 2.990, 0.0000, 0.0000, 'TRY', 'manual', 0, NULL, NULL, NULL, NULL, NULL, 'customer', 1, '2026-06-23 20:35:28'),
(6, 'iyzico', 'İyzico Kredi Kartı', 'Kart İşlem Komisyonu', 3.250, 3.250, 0.0000, 0.0000, 'TRY', 'manual', 0, NULL, NULL, NULL, NULL, NULL, 'customer', 1, '2026-06-23 20:35:28'),
(7, 'stripe', 'Stripe', 'Kart İşlem Komisyonu', 3.490, 3.490, 0.4900, 0.0000, 'USD', 'manual', 0, NULL, NULL, NULL, NULL, NULL, 'customer', 1, '2026-06-23 20:35:28'),
(8, 'manual', 'Havale/EFT', 'Kart İşlem Komisyonu', 0.000, 0.000, 0.0000, 0.0000, 'TRY', 'manual', 0, NULL, NULL, NULL, NULL, NULL, 'customer', 1, '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payment_fee_sync_logs`
--

CREATE TABLE `payment_fee_sync_logs` (
  `id` int(11) NOT NULL,
  `gateway` varchar(120) NOT NULL,
  `status` varchar(40) NOT NULL,
  `message` text DEFAULT NULL,
  `old_percent` decimal(8,3) DEFAULT NULL,
  `new_percent` decimal(8,3) DEFAULT NULL,
  `old_fixed` decimal(12,4) DEFAULT NULL,
  `new_fixed` decimal(12,4) DEFAULT NULL,
  `raw_response` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `portfolio_references`
--

CREATE TABLE `portfolio_references` (
  `id` int(11) NOT NULL,
  `title` varchar(190) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `reference_type` varchar(40) DEFAULT 'website',
  `sector` varchar(120) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `cover_image_url` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `technologies` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `portfolio_references`
--

INSERT INTO `portfolio_references` (`id`, `title`, `slug`, `reference_type`, `sector`, `short_description`, `description`, `image_url`, `cover_image_url`, `logo_url`, `project_url`, `technologies`, `is_featured`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(11, 'Nova Kurumsal', 'nova-kurumsal', 'website', 'Teknoloji', 'B2B hizmetlerini sade bir satış akışıyla sunan kurumsal web deneyimi.', '<p>B2B hizmetlerini sade bir satış akışıyla sunan kurumsal web deneyimi.</p>', 'public/assets/img/reference-web-1.svg', 'public/assets/img/reference-web-1.svg', 'public/assets/img/reference-logo-1.svg', NULL, 'Next.js, UI/UX, SEO', 1, 1, 10, '2026-06-23 20:35:28', NULL),
(12, 'Mira Restaurant', 'mira-restaurant', 'website', 'Yeme İçme', 'Menü, rezervasyon ve şube içeriklerini bir araya getiren mobil uyumlu site.', '<p>Menü, rezervasyon ve şube içeriklerini bir araya getiren mobil uyumlu site.</p>', 'public/assets/img/reference-web-2.svg', 'public/assets/img/reference-web-2.svg', 'public/assets/img/reference-logo-2.svg', NULL, 'PHP, Responsive UI, CMS', 1, 1, 20, '2026-06-23 20:35:28', NULL),
(13, 'Arven Mimarlık', 'arven-mimarlik', 'website', 'Mimarlık', 'Projeleri büyük görseller ve güçlü tipografiyle öne çıkaran portföy sitesi.', '<p>Projeleri büyük görseller ve güçlü tipografiyle öne çıkaran portföy sitesi.</p>', 'public/assets/img/reference-web-3.svg', 'public/assets/img/reference-web-3.svg', 'public/assets/img/reference-logo-3.svg', NULL, 'Portfolio, WebP, SEO', 1, 1, 30, '2026-06-23 20:35:28', NULL),
(14, 'Lina Beauty', 'lina-beauty', 'website', 'Güzellik', 'Hizmet, uzman ve randevu akışlarını merkezileştiren premium marka sitesi.', '<p>Hizmet, uzman ve randevu akışlarını merkezileştiren premium marka sitesi.</p>', 'public/assets/img/reference-web-4.svg', 'public/assets/img/reference-web-4.svg', 'public/assets/img/reference-logo-4.svg', NULL, 'Booking, CMS, Analytics', 0, 1, 40, '2026-06-23 20:35:28', NULL),
(15, 'Atlas Lojistik', 'atlas-lojistik', 'website', 'Lojistik', 'Teklif toplama ve operasyon kabiliyetlerini anlatan çok dilli kurumsal site.', '<p>Teklif toplama ve operasyon kabiliyetlerini anlatan çok dilli kurumsal site.</p>', 'public/assets/img/reference-web-5.svg', 'public/assets/img/reference-web-5.svg', 'public/assets/img/reference-logo-5.svg', NULL, 'Multilanguage, Forms, SEO', 0, 1, 50, '2026-06-23 20:35:28', NULL),
(16, 'RotaGo Android', 'rotago-android', 'android', 'Seyahat', 'Rota planlama, favoriler ve anlık bildirimlerle seyahat asistanı.', '<p>Rota planlama, favoriler ve anlık bildirimlerle seyahat asistanı.</p>', 'public/assets/img/reference-android-1.svg', 'public/assets/img/reference-android-1.svg', 'public/assets/img/reference-app-logo-1.svg', NULL, 'Kotlin, Maps, Push', 1, 1, 120, '2026-06-23 20:35:28', NULL),
(17, 'SiparişJet Android', 'siparisjet-android', 'android', 'E-Ticaret', 'Ürün keşfi, hızlı sepet ve sipariş takibi sunan mobil ticaret uygulaması.', '<p>Ürün keşfi, hızlı sepet ve sipariş takibi sunan mobil ticaret uygulaması.</p>', 'public/assets/img/reference-android-2.svg', 'public/assets/img/reference-android-2.svg', 'public/assets/img/reference-app-logo-2.svg', NULL, 'Kotlin, REST API, Payment', 1, 1, 130, '2026-06-23 20:35:28', NULL),
(18, 'FitLife Android', 'fitlife-android', 'android', 'Sağlık & Spor', 'Antrenman planı, ilerleme takibi ve üyelik yönetimini birleştiren uygulama.', '<p>Antrenman planı, ilerleme takibi ve üyelik yönetimini birleştiren uygulama.</p>', 'public/assets/img/reference-android-3.svg', 'public/assets/img/reference-android-3.svg', 'public/assets/img/reference-app-logo-3.svg', NULL, 'Kotlin, Charts, Notifications', 1, 1, 140, '2026-06-23 20:35:28', NULL),
(19, 'RadyoMix Android', 'radyomix-android', 'android', 'Medya', 'Canlı yayın, program akışı ve favori istasyon deneyimi.', '<p>Canlı yayın, program akışı ve favori istasyon deneyimi.</p>', 'public/assets/img/reference-android-4.svg', 'public/assets/img/reference-android-4.svg', 'public/assets/img/reference-app-logo-4.svg', NULL, 'ExoPlayer, Media Session, Push', 0, 1, 150, '2026-06-23 20:35:28', NULL),
(20, 'UstaBul Android', 'ustabul-android', 'android', 'Hizmet Pazarı', 'Konuma göre hizmet sağlayıcı keşfi, teklif ve mesajlaşma akışı.', '<p>Konuma göre hizmet sağlayıcı keşfi, teklif ve mesajlaşma akışı.</p>', 'public/assets/img/reference-android-5.svg', 'public/assets/img/reference-android-5.svg', 'public/assets/img/reference-app-logo-5.svg', NULL, 'Kotlin, Maps, Chat', 0, 1, 160, '2026-06-23 20:35:28', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `name` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `type` varchar(80) DEFAULT 'service',
  `module_name` varchar(120) DEFAULT NULL,
  `server_group_id` int(11) DEFAULT NULL,
  `whm_package` varchar(160) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `billing_cycle` varchar(40) DEFAULT 'monthly',
  `is_custom_build_enabled` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `visibility` varchar(40) DEFAULT 'visible',
  `seo_title` varchar(190) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `source_type` varchar(40) DEFAULT NULL,
  `external_id` varchar(80) DEFAULT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `currency_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `products`
--

INSERT INTO `products` (`id`, `group_id`, `name`, `slug`, `type`, `module_name`, `server_group_id`, `whm_package`, `short_description`, `description`, `price`, `currency`, `billing_cycle`, `is_custom_build_enabled`, `is_active`, `visibility`, `seo_title`, `meta_description`, `sort_order`, `created_at`, `updated_at`, `source_type`, `external_id`, `source_id`, `currency_code`) VALUES
(81, 225, 'Başlangıç Hosting', 'baslangic-hosting-1', 'hosting', 'whm', NULL, NULL, 'Başlangıç Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Başlangıç Hosting</h2><p>Başlangıç Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 149.00, 'TRY', 'monthly', 0, 1, 'visible', 'Başlangıç Hosting', 'Başlangıç Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(82, 225, 'Kurumsal Hosting', 'baslangic-hosting-2', 'hosting', 'whm', NULL, NULL, 'Kurumsal Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Kurumsal Hosting</h2><p>Kurumsal Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 249.00, 'TRY', 'monthly', 0, 1, 'visible', 'Kurumsal Hosting', 'Kurumsal Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(83, 225, 'WordPress Hosting', 'baslangic-hosting-3', 'hosting', 'whm', NULL, NULL, 'WordPress Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>WordPress Hosting</h2><p>WordPress Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 349.00, 'TRY', 'monthly', 0, 1, 'visible', 'WordPress Hosting', 'WordPress Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(84, 225, 'E-Ticaret Hosting', 'baslangic-hosting-4', 'hosting', 'whm', NULL, NULL, 'E-Ticaret Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>E-Ticaret Hosting</h2><p>E-Ticaret Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 549.00, 'TRY', 'monthly', 0, 1, 'visible', 'E-Ticaret Hosting', 'E-Ticaret Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(85, 225, 'Ajans Hosting', 'baslangic-hosting-5', 'hosting', 'whm', NULL, NULL, 'Ajans Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Ajans Hosting</h2><p>Ajans Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 899.00, 'TRY', 'monthly', 0, 1, 'visible', 'Ajans Hosting', 'Ajans Hosting, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(86, 226, 'Cloud VPS S', 'baslangic-vps-sunucu-1', 'server', 'manual', NULL, NULL, 'Cloud VPS S, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Cloud VPS S</h2><p>Cloud VPS S, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 499.00, 'TRY', 'monthly', 0, 1, 'visible', 'Cloud VPS S', 'Cloud VPS S, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(87, 226, 'Cloud VPS M', 'baslangic-vps-sunucu-2', 'server', 'manual', NULL, NULL, 'Cloud VPS M, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Cloud VPS M</h2><p>Cloud VPS M, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 799.00, 'TRY', 'monthly', 0, 1, 'visible', 'Cloud VPS M', 'Cloud VPS M, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(88, 226, 'Cloud VPS L', 'baslangic-vps-sunucu-3', 'server', 'manual', NULL, NULL, 'Cloud VPS L, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Cloud VPS L</h2><p>Cloud VPS L, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1299.00, 'TRY', 'monthly', 0, 1, 'visible', 'Cloud VPS L', 'Cloud VPS L, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(89, 226, 'Yönetilen VPS', 'baslangic-vps-sunucu-4', 'server', 'manual', NULL, NULL, 'Yönetilen VPS, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Yönetilen VPS</h2><p>Yönetilen VPS, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Yönetilen VPS', 'Yönetilen VPS, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(90, 226, 'Dedicated Pro', 'baslangic-vps-sunucu-5', 'server', 'manual', NULL, NULL, 'Dedicated Pro, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Dedicated Pro</h2><p>Dedicated Pro, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 3499.00, 'TRY', 'monthly', 0, 1, 'visible', 'Dedicated Pro', 'Dedicated Pro, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(91, 228, 'DV SSL', 'baslangic-ssl-1', 'ssl', 'manual', NULL, NULL, 'DV SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>DV SSL</h2><p>DV SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 299.00, 'TRY', 'monthly', 0, 1, 'visible', 'DV SSL', 'DV SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(92, 228, 'Wildcard SSL', 'baslangic-ssl-2', 'ssl', 'manual', NULL, NULL, 'Wildcard SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Wildcard SSL</h2><p>Wildcard SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 799.00, 'TRY', 'monthly', 0, 1, 'visible', 'Wildcard SSL', 'Wildcard SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(93, 228, 'Business SSL', 'baslangic-ssl-3', 'ssl', 'manual', NULL, NULL, 'Business SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Business SSL</h2><p>Business SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1299.00, 'TRY', 'monthly', 0, 1, 'visible', 'Business SSL', 'Business SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(94, 228, 'E-Ticaret SSL', 'baslangic-ssl-4', 'ssl', 'manual', NULL, NULL, 'E-Ticaret SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>E-Ticaret SSL</h2><p>E-Ticaret SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1899.00, 'TRY', 'monthly', 0, 1, 'visible', 'E-Ticaret SSL', 'E-Ticaret SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(95, 228, 'Enterprise SSL', 'baslangic-ssl-5', 'ssl', 'manual', NULL, NULL, 'Enterprise SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Enterprise SSL</h2><p>Enterprise SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 2999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Enterprise SSL', 'Enterprise SSL, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(96, 229, 'SiteBuilder Başlangıç', 'baslangic-sitebuilder-1', 'service', 'manual', NULL, NULL, 'SiteBuilder Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SiteBuilder Başlangıç</h2><p>SiteBuilder Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 249.00, 'TRY', 'monthly', 0, 1, 'visible', 'SiteBuilder Başlangıç', 'SiteBuilder Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(97, 229, 'SiteBuilder Kurumsal', 'baslangic-sitebuilder-2', 'service', 'manual', NULL, NULL, 'SiteBuilder Kurumsal, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SiteBuilder Kurumsal</h2><p>SiteBuilder Kurumsal, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 449.00, 'TRY', 'monthly', 0, 1, 'visible', 'SiteBuilder Kurumsal', 'SiteBuilder Kurumsal, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(98, 229, 'SiteBuilder E-Ticaret', 'baslangic-sitebuilder-3', 'service', 'manual', NULL, NULL, 'SiteBuilder E-Ticaret, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SiteBuilder E-Ticaret</h2><p>SiteBuilder E-Ticaret, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 749.00, 'TRY', 'monthly', 0, 1, 'visible', 'SiteBuilder E-Ticaret', 'SiteBuilder E-Ticaret, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(99, 229, 'SiteBuilder Ajans', 'baslangic-sitebuilder-4', 'service', 'manual', NULL, NULL, 'SiteBuilder Ajans, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SiteBuilder Ajans</h2><p>SiteBuilder Ajans, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1199.00, 'TRY', 'monthly', 0, 1, 'visible', 'SiteBuilder Ajans', 'SiteBuilder Ajans, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(100, 229, 'SiteBuilder Enterprise', 'baslangic-sitebuilder-5', 'service', 'manual', NULL, NULL, 'SiteBuilder Enterprise, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SiteBuilder Enterprise</h2><p>SiteBuilder Enterprise, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1999.00, 'TRY', 'monthly', 0, 1, 'visible', 'SiteBuilder Enterprise', 'SiteBuilder Enterprise, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(101, 230, 'MobileBuilder PWA', 'baslangic-mobilebuilder-1', 'service', 'manual', NULL, NULL, 'MobileBuilder PWA, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>MobileBuilder PWA</h2><p>MobileBuilder PWA, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 499.00, 'TRY', 'monthly', 0, 1, 'visible', 'MobileBuilder PWA', 'MobileBuilder PWA, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(102, 230, 'MobileBuilder Android', 'baslangic-mobilebuilder-2', 'service', 'manual', NULL, NULL, 'MobileBuilder Android, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>MobileBuilder Android</h2><p>MobileBuilder Android, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 999.00, 'TRY', 'monthly', 0, 1, 'visible', 'MobileBuilder Android', 'MobileBuilder Android, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(103, 230, 'MobileBuilder Business', 'baslangic-mobilebuilder-3', 'service', 'manual', NULL, NULL, 'MobileBuilder Business, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>MobileBuilder Business</h2><p>MobileBuilder Business, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1499.00, 'TRY', 'monthly', 0, 1, 'visible', 'MobileBuilder Business', 'MobileBuilder Business, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(104, 230, 'MobileBuilder Commerce', 'baslangic-mobilebuilder-4', 'service', 'manual', NULL, NULL, 'MobileBuilder Commerce, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>MobileBuilder Commerce</h2><p>MobileBuilder Commerce, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 2499.00, 'TRY', 'monthly', 0, 1, 'visible', 'MobileBuilder Commerce', 'MobileBuilder Commerce, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(105, 230, 'MobileBuilder Agency', 'baslangic-mobilebuilder-5', 'service', 'manual', NULL, NULL, 'MobileBuilder Agency, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>MobileBuilder Agency</h2><p>MobileBuilder Agency, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 3999.00, 'TRY', 'monthly', 0, 1, 'visible', 'MobileBuilder Agency', 'MobileBuilder Agency, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(106, 231, 'Tek Sayfa Web', 'baslangic-web-tasarim-1', 'service', 'manual', NULL, NULL, 'Tek Sayfa Web, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Tek Sayfa Web</h2><p>Tek Sayfa Web, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 4990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Tek Sayfa Web', 'Tek Sayfa Web, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(107, 231, 'Kurumsal Web', 'baslangic-web-tasarim-2', 'service', 'manual', NULL, NULL, 'Kurumsal Web, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Kurumsal Web</h2><p>Kurumsal Web, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 8990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Kurumsal Web', 'Kurumsal Web, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(108, 231, 'E-Ticaret Sitesi', 'baslangic-web-tasarim-3', 'service', 'manual', NULL, NULL, 'E-Ticaret Sitesi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>E-Ticaret Sitesi</h2><p>E-Ticaret Sitesi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 14990.00, 'TRY', 'monthly', 0, 1, 'visible', 'E-Ticaret Sitesi', 'E-Ticaret Sitesi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(109, 231, 'Özel Web Portalı', 'baslangic-web-tasarim-4', 'service', 'manual', NULL, NULL, 'Özel Web Portalı, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Özel Web Portalı</h2><p>Özel Web Portalı, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 24990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Özel Web Portalı', 'Özel Web Portalı, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(110, 231, 'Enterprise Web Projesi', 'baslangic-web-tasarim-5', 'service', 'manual', NULL, NULL, 'Enterprise Web Projesi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Enterprise Web Projesi</h2><p>Enterprise Web Projesi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 44990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Enterprise Web Projesi', 'Enterprise Web Projesi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(111, 232, 'Android Başlangıç', 'baslangic-mobil-uygulama-1', 'service', 'manual', NULL, NULL, 'Android Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Android Başlangıç</h2><p>Android Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 9990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Android Başlangıç', 'Android Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(112, 232, 'Kurumsal Android', 'baslangic-mobil-uygulama-2', 'service', 'manual', NULL, NULL, 'Kurumsal Android, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Kurumsal Android</h2><p>Kurumsal Android, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 14990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Kurumsal Android', 'Kurumsal Android, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(113, 232, 'E-Ticaret Uygulaması', 'baslangic-mobil-uygulama-3', 'service', 'manual', NULL, NULL, 'E-Ticaret Uygulaması, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>E-Ticaret Uygulaması</h2><p>E-Ticaret Uygulaması, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 24990.00, 'TRY', 'monthly', 0, 1, 'visible', 'E-Ticaret Uygulaması', 'E-Ticaret Uygulaması, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(114, 232, 'Randevu Uygulaması', 'baslangic-mobil-uygulama-4', 'service', 'manual', NULL, NULL, 'Randevu Uygulaması, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Randevu Uygulaması</h2><p>Randevu Uygulaması, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 34990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Randevu Uygulaması', 'Randevu Uygulaması, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(115, 232, 'Özel Mobil Platform', 'baslangic-mobil-uygulama-5', 'service', 'manual', NULL, NULL, 'Özel Mobil Platform, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Özel Mobil Platform</h2><p>Özel Mobil Platform, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 59990.00, 'TRY', 'monthly', 0, 1, 'visible', 'Özel Mobil Platform', 'Özel Mobil Platform, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(116, 233, 'SEO Başlangıç', 'baslangic-seo-1', 'service', 'manual', NULL, NULL, 'SEO Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SEO Başlangıç</h2><p>SEO Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1499.00, 'TRY', 'monthly', 0, 1, 'visible', 'SEO Başlangıç', 'SEO Başlangıç, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(117, 233, 'Yerel SEO', 'baslangic-seo-2', 'service', 'manual', NULL, NULL, 'Yerel SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Yerel SEO</h2><p>Yerel SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 2499.00, 'TRY', 'monthly', 0, 1, 'visible', 'Yerel SEO', 'Yerel SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(118, 233, 'Kurumsal SEO', 'baslangic-seo-3', 'service', 'manual', NULL, NULL, 'Kurumsal SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Kurumsal SEO</h2><p>Kurumsal SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 3999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Kurumsal SEO', 'Kurumsal SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(119, 233, 'E-Ticaret SEO', 'baslangic-seo-4', 'service', 'manual', NULL, NULL, 'E-Ticaret SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>E-Ticaret SEO</h2><p>E-Ticaret SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 5999.00, 'TRY', 'monthly', 0, 1, 'visible', 'E-Ticaret SEO', 'E-Ticaret SEO, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(120, 233, 'SEO Growth', 'baslangic-seo-5', 'service', 'manual', NULL, NULL, 'SEO Growth, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SEO Growth</h2><p>SEO Growth, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 8999.00, 'TRY', 'monthly', 0, 1, 'visible', 'SEO Growth', 'SEO Growth, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(121, 234, 'Logo ve Marka Kiti', 'baslangic-dijital-hizmetler-1', 'service', 'manual', NULL, NULL, 'Logo ve Marka Kiti, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Logo ve Marka Kiti</h2><p>Logo ve Marka Kiti, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1499.00, 'TRY', 'monthly', 0, 1, 'visible', 'Logo ve Marka Kiti', 'Logo ve Marka Kiti, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(122, 234, 'Sosyal Medya Paketi', 'baslangic-dijital-hizmetler-2', 'service', 'manual', NULL, NULL, 'Sosyal Medya Paketi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Sosyal Medya Paketi</h2><p>Sosyal Medya Paketi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 2499.00, 'TRY', 'monthly', 0, 1, 'visible', 'Sosyal Medya Paketi', 'Sosyal Medya Paketi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(123, 234, 'İçerik Üretimi', 'baslangic-dijital-hizmetler-3', 'service', 'manual', NULL, NULL, 'İçerik Üretimi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>İçerik Üretimi</h2><p>İçerik Üretimi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 3499.00, 'TRY', 'monthly', 0, 1, 'visible', 'İçerik Üretimi', 'İçerik Üretimi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(124, 234, 'Dijital Reklam Yönetimi', 'baslangic-dijital-hizmetler-4', 'service', 'manual', NULL, NULL, 'Dijital Reklam Yönetimi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Dijital Reklam Yönetimi</h2><p>Dijital Reklam Yönetimi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 4999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Dijital Reklam Yönetimi', 'Dijital Reklam Yönetimi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(125, 234, 'Dijital Dönüşüm Paketi', 'baslangic-dijital-hizmetler-5', 'service', 'manual', NULL, NULL, 'Dijital Dönüşüm Paketi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Dijital Dönüşüm Paketi</h2><p>Dijital Dönüşüm Paketi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 9999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Dijital Dönüşüm Paketi', 'Dijital Dönüşüm Paketi, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(126, 235, 'Premium Tema', 'baslangic-marketplace-1', 'marketplace', 'marketplace', NULL, NULL, 'Premium Tema, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Premium Tema</h2><p>Premium Tema, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Premium Tema', 'Premium Tema, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 10, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(127, 235, 'Kurumsal Script', 'baslangic-marketplace-2', 'marketplace', 'marketplace', NULL, NULL, 'Kurumsal Script, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Kurumsal Script</h2><p>Kurumsal Script, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 1999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Kurumsal Script', 'Kurumsal Script, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 20, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(128, 235, 'E-Ticaret Arayüzü', 'baslangic-marketplace-3', 'marketplace', 'marketplace', NULL, NULL, 'E-Ticaret Arayüzü, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>E-Ticaret Arayüzü</h2><p>E-Ticaret Arayüzü, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 2999.00, 'TRY', 'monthly', 0, 1, 'visible', 'E-Ticaret Arayüzü', 'E-Ticaret Arayüzü, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 30, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(129, 235, 'Mobil Uygulama Kaynak Kodu', 'baslangic-marketplace-4', 'marketplace', 'marketplace', NULL, NULL, 'Mobil Uygulama Kaynak Kodu, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>Mobil Uygulama Kaynak Kodu</h2><p>Mobil Uygulama Kaynak Kodu, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 3999.00, 'TRY', 'monthly', 0, 1, 'visible', 'Mobil Uygulama Kaynak Kodu', 'Mobil Uygulama Kaynak Kodu, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 40, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL),
(130, 235, 'SaaS Başlangıç Kiti', 'baslangic-marketplace-5', 'marketplace', 'marketplace', NULL, NULL, 'SaaS Başlangıç Kiti, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', '<h2>SaaS Başlangıç Kiti</h2><p>SaaS Başlangıç Kiti, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.</p><ul><li>Premium SaaS deneyimi</li><li>Merkezi yönetim ve raporlama</li><li>İhtiyaca göre geliştirilebilir yapı</li></ul>', 5999.00, 'TRY', 'monthly', 0, 1, 'visible', 'SaaS Başlangıç Kiti', 'SaaS Başlangıç Kiti, modern işletmeler için ölçeklenebilir ve yönetilebilir bir çözümdür.', 50, '2026-06-23 20:35:28', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_checkout_addons`
--

CREATE TABLE `product_checkout_addons` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `addon_key` varchar(80) NOT NULL,
  `name` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'TRY',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_custom_fields`
--

CREATE TABLE `product_custom_fields` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT 0,
  `product_id` int(11) DEFAULT 0,
  `field_key` varchar(120) NOT NULL,
  `label` varchar(190) NOT NULL,
  `field_type` varchar(40) DEFAULT 'text',
  `options` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_groups`
--

CREATE TABLE `product_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(80) DEFAULT 'hosting',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `product_groups`
--

INSERT INTO `product_groups` (`id`, `name`, `slug`, `description`, `type`, `sort_order`, `is_active`, `created_at`) VALUES
(225, 'Hosting', 'hosting', 'Web hosting ve reseller paketleri', 'hosting', 10, 1, '2026-06-23 20:35:28'),
(226, 'VPS / Sunucu', 'vps-sunucu', 'VPS, dedicated ve yönetilebilir sunucular', 'server', 20, 1, '2026-06-23 20:35:28'),
(227, 'Domain', 'domain', 'Domain kayıt, transfer ve DNS ürünleri', 'domain', 30, 1, '2026-06-23 20:35:28'),
(228, 'SSL', 'ssl', 'SSL sertifikaları ve güvenlik ürünleri', 'ssl', 40, 1, '2026-06-23 20:35:28'),
(229, 'SiteBuilder', 'sitebuilder', 'Hazır site, şablon ve builder paketleri', 'sitebuilder', 50, 1, '2026-06-23 20:35:28'),
(230, 'MobileBuilder', 'mobilebuilder', 'Mobil uygulama builder paketleri', 'mobilebuilder', 60, 1, '2026-06-23 20:35:28'),
(231, 'Web Tasarım', 'web-tasarim', 'Kurumsal site, e-ticaret ve özel tasarım', 'web', 70, 1, '2026-06-23 20:35:28'),
(232, 'Mobil Uygulama', 'mobil-uygulama', 'Android/iOS proje hizmetleri', 'mobile', 80, 1, '2026-06-23 20:35:28'),
(233, 'SEO', 'seo', 'SEO ve dijital pazarlama paketleri', 'seo', 90, 1, '2026-06-23 20:35:28'),
(234, 'Dijital Hizmetler', 'dijital-hizmetler', 'Ahost tarafından sunulan dijital hizmetler', 'digital', 100, 1, '2026-06-23 20:35:28'),
(235, 'Marketplace', 'marketplace', 'Tema, script, domain ve dijital ürün ilan altyapısı', 'marketplace', 110, 1, '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_price_update_logs`
--

CREATE TABLE `product_price_update_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(80) DEFAULT 'quick_update',
  `cycle` varchar(40) DEFAULT 'monthly',
  `old_snapshot` longtext DEFAULT NULL,
  `new_snapshot` longtext DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_pricing`
--

CREATE TABLE `product_pricing` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cycle` varchar(40) DEFAULT 'monthly',
  `price` decimal(14,2) DEFAULT 0.00,
  `setup_fee` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'TRY',
  `price_usd` decimal(14,2) DEFAULT 0.00,
  `price_try` decimal(14,2) DEFAULT 0.00,
  `setup_fee_usd` decimal(14,2) DEFAULT 0.00,
  `setup_fee_try` decimal(14,2) DEFAULT 0.00,
  `base_currency` varchar(10) DEFAULT 'USD',
  `exchange_rate` decimal(16,6) DEFAULT 0.000000,
  `margin_percent` decimal(8,2) DEFAULT 0.00,
  `auto_convert` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 0,
  `source_type` varchar(40) DEFAULT NULL,
  `external_id` varchar(80) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `product_pricing`
--

INSERT INTO `product_pricing` (`id`, `product_id`, `cycle`, `price`, `setup_fee`, `currency`, `price_usd`, `price_try`, `setup_fee_usd`, `setup_fee_try`, `base_currency`, `exchange_rate`, `margin_percent`, `auto_convert`, `is_active`, `source_type`, `external_id`, `updated_at`) VALUES
(291, 81, 'monthly', 149.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(292, 82, 'monthly', 249.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(293, 83, 'monthly', 349.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(294, 84, 'monthly', 549.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(295, 85, 'monthly', 899.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(296, 86, 'monthly', 499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(297, 87, 'monthly', 799.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(298, 88, 'monthly', 1299.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(299, 89, 'monthly', 1999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(300, 90, 'monthly', 3499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(301, 91, 'monthly', 299.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(302, 92, 'monthly', 799.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(303, 93, 'monthly', 1299.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(304, 94, 'monthly', 1899.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(305, 95, 'monthly', 2999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(306, 96, 'monthly', 249.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(307, 97, 'monthly', 449.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(308, 98, 'monthly', 749.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(309, 99, 'monthly', 1199.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(310, 100, 'monthly', 1999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(311, 101, 'monthly', 499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(312, 102, 'monthly', 999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(313, 103, 'monthly', 1499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(314, 104, 'monthly', 2499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(315, 105, 'monthly', 3999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(316, 106, 'monthly', 4990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(317, 107, 'monthly', 8990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(318, 108, 'monthly', 14990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(319, 109, 'monthly', 24990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(320, 110, 'monthly', 44990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(321, 111, 'monthly', 9990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(322, 112, 'monthly', 14990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(323, 113, 'monthly', 24990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(324, 114, 'monthly', 34990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(325, 115, 'monthly', 59990.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(326, 116, 'monthly', 1499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(327, 117, 'monthly', 2499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(328, 118, 'monthly', 3999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(329, 119, 'monthly', 5999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(330, 120, 'monthly', 8999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(331, 121, 'monthly', 1499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(332, 122, 'monthly', 2499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(333, 123, 'monthly', 3499.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(334, 124, 'monthly', 4999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(335, 125, 'monthly', 9999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(336, 126, 'monthly', 999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(337, 127, 'monthly', 1999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(338, 128, 'monthly', 2999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(339, 129, 'monthly', 3999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL),
(340, 130, 'monthly', 5999.00, 0.00, 'TRY', 0.00, 0.00, 0.00, 0.00, 'USD', 0.000000, 0.00, 1, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_promotions`
--

CREATE TABLE `product_promotions` (
  `id` int(11) NOT NULL,
  `code` varchar(80) NOT NULL,
  `title` varchar(190) DEFAULT NULL,
  `discount_type` varchar(20) DEFAULT 'percent',
  `discount_value` decimal(14,2) DEFAULT 0.00,
  `min_total` decimal(14,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_revision_logs`
--

CREATE TABLE `product_revision_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(80) DEFAULT 'update',
  `snapshot_json` longtext DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_update_packages`
--

CREATE TABLE `product_update_packages` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_type` varchar(80) DEFAULT 'theme',
  `product_slug` varchar(160) DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `version` varchar(60) NOT NULL,
  `changelog` longtext DEFAULT NULL,
  `zip_path` varchar(255) DEFAULT NULL,
  `is_mandatory` tinyint(1) DEFAULT 0,
  `status` varchar(40) DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `registrar_capability_matrix`
--

CREATE TABLE `registrar_capability_matrix` (
  `id` int(11) NOT NULL,
  `registrar_slug` varchar(120) NOT NULL,
  `operation` varchar(80) NOT NULL,
  `is_supported` tinyint(1) DEFAULT 1,
  `test_status` enum('unknown','pass','fail') DEFAULT 'unknown',
  `last_message` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `registrar_capability_matrix`
--

INSERT INTO `registrar_capability_matrix` (`id`, `registrar_slug`, `operation`, `is_supported`, `test_status`, `last_message`, `updated_at`) VALUES
(901, 'domainnameapi', 'register', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(902, 'domainnameapi', 'renew', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(903, 'domainnameapi', 'transfer', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(904, 'domainnameapi', 'epp', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(905, 'domainnameapi', 'whois', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(906, 'domainnameapi', 'dns', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(907, 'domainnameapi', 'nameserver', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(908, 'domainnameapi', 'lock', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(909, 'domainnameapi', 'privacy', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(910, 'resellerclub', 'register', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(911, 'resellerclub', 'renew', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(912, 'resellerclub', 'transfer', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(913, 'resellerclub', 'epp', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(914, 'resellerclub', 'whois', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(915, 'resellerclub', 'dns', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(916, 'resellerclub', 'nameserver', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(917, 'resellerclub', 'lock', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(918, 'resellerclub', 'privacy', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(919, 'enom', 'register', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(920, 'enom', 'renew', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(921, 'enom', 'transfer', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(922, 'enom', 'epp', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(923, 'enom', 'whois', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(924, 'enom', 'dns', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(925, 'enom', 'nameserver', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(926, 'enom', 'lock', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(927, 'enom', 'privacy', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(928, 'natro', 'register', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(929, 'natro', 'renew', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(930, 'natro', 'transfer', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(931, 'natro', 'epp', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(932, 'natro', 'whois', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(933, 'natro', 'dns', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(934, 'natro', 'nameserver', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(935, 'natro', 'lock', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(936, 'natro', 'privacy', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(937, 'isimtescil', 'register', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(938, 'isimtescil', 'renew', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(939, 'isimtescil', 'transfer', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(940, 'isimtescil', 'epp', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(941, 'isimtescil', 'whois', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(942, 'isimtescil', 'dns', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(943, 'isimtescil', 'nameserver', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(944, 'isimtescil', 'lock', 1, 'unknown', NULL, '2026-06-23 20:35:28'),
(945, 'isimtescil', 'privacy', 1, 'unknown', NULL, '2026-06-23 20:35:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `registrar_configs`
--

CREATE TABLE `registrar_configs` (
  `id` int(11) NOT NULL,
  `registrar_id` int(11) NOT NULL,
  `config_key` varchar(120) NOT NULL,
  `config_value` longtext DEFAULT NULL,
  `is_secret` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `registrar_price_cache`
--

CREATE TABLE `registrar_price_cache` (
  `id` int(11) NOT NULL,
  `registrar_slug` varchar(140) NOT NULL,
  `tld` varchar(40) NOT NULL,
  `action` varchar(40) DEFAULT 'register',
  `cost` decimal(12,4) DEFAULT 0.0000,
  `currency` varchar(10) DEFAULT 'USD',
  `source` varchar(40) DEFAULT 'manual',
  `raw_response` longtext DEFAULT NULL,
  `last_checked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `remote_update_jobs`
--

CREATE TABLE `remote_update_jobs` (
  `id` int(11) NOT NULL,
  `connected_site_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `current_step` varchar(120) DEFAULT NULL,
  `log_json` longtext DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `renewal_automation_logs`
--

CREATE TABLE `renewal_automation_logs` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `channel` varchar(40) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `route_aliases`
--

CREATE TABLE `route_aliases` (
  `id` int(11) NOT NULL,
  `alias_path` varchar(190) NOT NULL,
  `target_path` varchar(190) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `panel_type` varchar(80) DEFAULT 'whm',
  `hostname` varchar(190) DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `username` varchar(160) DEFAULT NULL,
  `api_token` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'inactive',
  `test_mode` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_groups`
--

CREATE TABLE `server_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(190) NOT NULL,
  `strategy` varchar(80) DEFAULT 'least_used',
  `location` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `server_groups`
--

INSERT INTO `server_groups` (`id`, `name`, `strategy`, `location`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(58, 'Türkiye Hosting', 'least_used', 'TR', 'active', 'Yeni hosting hesapları en az dolu Türkiye sunucusuna atanır.', '2026-06-23 20:35:28', NULL),
(59, 'Almanya Hosting', 'least_used', 'DE', 'active', 'Avrupa lokasyonlu hosting ve VPS ürünleri için sunucu grubu.', '2026-06-23 20:35:28', NULL),
(60, 'Manuel Teslimat', 'manual', NULL, 'active', 'Otomasyon kullanılmayan ürünlerde manuel teslimat grubu.', '2026-06-23 20:35:28', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_nodes`
--

CREATE TABLE `server_nodes` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `panel_type` varchar(80) DEFAULT 'whm',
  `hostname` varchar(190) DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `username` varchar(160) DEFAULT NULL,
  `api_token` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'inactive',
  `test_mode` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `server_group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `domain` varchar(190) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `billing_cycle` varchar(40) DEFAULT 'monthly',
  `next_due_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `suspend_at` datetime DEFAULT NULL,
  `terminate_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(160) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(433, 'checkout_theme_mode', 'site', '2026-06-23 20:35:26'),
(434, 'checkout_guest_until_payment', '1', '2026-06-23 20:35:26'),
(435, 'frontend_admin_button_visible', '0', '2026-06-23 20:35:26'),
(436, 'header_account_dropdown_enabled', '1', '2026-06-23 20:35:26'),
(437, 'checkout_domain_step_enabled', '1', '2026-06-23 20:35:26'),
(438, 'checkout_addons_step_enabled', '1', '2026-06-23 20:35:26'),
(439, 'site_url', '', '2026-06-23 20:35:26'),
(440, 'base_url', '', '2026-06-23 20:35:26'),
(441, 'ahost_version', '25.0.0-rc24', '2026-06-23 21:13:10'),
(442, 'setup_wizard_completed', '0', '2026-06-23 20:35:26'),
(443, 'setup_wizard_dismissed', '0', '2026-06-23 20:35:26'),
(445, 'css_isolation_app_shell', '1', '2026-06-23 20:35:28'),
(446, 'inline_builder_enabled', '1', '2026-06-23 20:35:28'),
(447, 'client_layout_rebuild', '1', '2026-06-23 20:35:28'),
(448, 'theme_studio_pro', '1', '2026-06-23 20:35:28'),
(449, 'client_builder_pro', '1', '2026-06-23 20:35:28'),
(450, 'real_theme_preview', '1', '2026-06-23 20:35:28'),
(451, 'currency_margin_percent', '5.00', '2026-06-23 20:35:28'),
(452, 'domain_default_commission_percent', '20.00', '2026-06-23 20:35:28'),
(457, 'starter_catalog_v2450_seeded', '1', '2026-06-23 20:35:28'),
(458, 'portfolio_v2464_seeded', '1', '2026-06-23 20:35:28'),
(459, 'admin_mfa_policy', 'optional', '2026-06-23 20:35:30'),
(460, 'customer_mfa_policy', 'optional', '2026-06-23 20:35:30'),
(461, 'mfa_mail_enabled', '1', '2026-06-23 20:35:30'),
(462, 'mfa_totp_enabled', '1', '2026-06-23 20:35:30'),
(463, 'mfa_sms_enabled', '0', '2026-06-23 20:35:30'),
(464, 'mfa_default_method', 'mail', '2026-06-23 20:35:30'),
(465, 'mfa_otp_ttl_minutes', '5', '2026-06-23 20:35:30'),
(466, 'mfa_max_attempts', '5', '2026-06-23 20:35:30'),
(467, 'mfa_sms_sender', 'AhostOne', '2026-06-23 20:35:30');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `setup_wizard_runs`
--

CREATE TABLE `setup_wizard_runs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `payload` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `setup_wizard_runs`
--

INSERT INTO `setup_wizard_runs` (`id`, `admin_id`, `action`, `payload`, `created_at`) VALUES
(2, 1, 'dismiss', '{}', '2026-06-23 20:35:35');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `setup_wizard_steps`
--

CREATE TABLE `setup_wizard_steps` (
  `id` int(11) NOT NULL,
  `step_key` varchar(120) NOT NULL,
  `title` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(80) DEFAULT 'general',
  `route` varchar(190) DEFAULT NULL,
  `status` enum('pending','done','skipped') DEFAULT 'pending',
  `required` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `setup_wizard_steps`
--

INSERT INTO `setup_wizard_steps` (`id`, `step_key`, `title`, `description`, `category`, `route`, `status`, `required`, `sort_order`, `updated_at`) VALUES
(18, 'site_identity', 'Logo, Site Adı ve Site Linki', 'Marka adı, logo, site URL ve iletişim bilgilerini tamamlayın.', 'Başlangıç', 'admin/settings', 'pending', 1, 10, '2026-06-23 20:35:35'),
(19, 'theme', 'Tema Seçimi ve Önizleme', 'Ön yüz, admin ve müşteri paneli temasını seçip uygulayın.', 'Görünüm', 'admin/theme-center', 'pending', 1, 20, '2026-06-23 20:35:35'),
(20, 'domain_registrar', 'DomainNameAPI / Registrar Ayarları', 'Domain sorgulama, kayıt, yenileme, EPP ve transfer için registrar ayarlarını yapın.', 'Domain', 'admin/domain-center/registrars', 'pending', 1, 30, '2026-06-23 20:35:35'),
(21, 'server', 'Sunucu / WHM / DirectAdmin / Plesk', 'Hosting otomasyonu için sunucu ekleyin ve bağlantı testini yapın.', 'Hosting', 'admin/hosting-server/servers', 'pending', 1, 40, '2026-06-23 20:35:35'),
(22, 'products', 'Ürün Grupları ve Paketler', 'Hosting, domain, SSL, web tasarım, SEO ve diğer ürünlerinizi tanımlayın.', 'Ürün', 'admin/product-center/groups', 'pending', 1, 50, '2026-06-23 20:35:35'),
(23, 'payment', 'Ödeme Yöntemleri ve Kart Komisyonu', 'Shopier, sanal POS ve ödeme komisyonlarını yapılandırın.', 'Ödeme', 'admin/accounting/payment-fees', 'pending', 1, 60, '2026-06-23 20:35:35'),
(24, 'smtp', 'SMTP Mail Ayarları', 'Fatura, ticket, şifre sıfırlama ve sistem bildirimleri için SMTP ayarlarını girin.', 'Bildirim', 'admin/notification-center', 'pending', 1, 70, '2026-06-23 20:35:35'),
(25, 'sms', 'SMS / İletiMerkezi Ayarları', 'İletiMerkezi veya diğer SMS sağlayıcılarını bağlayın, bakiye sorgulayın ve test SMS gönderin.', 'Bildirim', 'admin/notifications', 'pending', 0, 80, '2026-06-23 20:35:35'),
(26, 'whatsapp', 'WhatsApp Bildirimleri', 'WhatsApp API veya webhook sağlayıcınızı bağlayın.', 'Bildirim', 'admin/notification-center', 'pending', 0, 90, '2026-06-23 20:35:35'),
(27, 'ai', 'Yapay Zeka API Ayarları', 'OpenAI/Gemini/Claude gibi AI sağlayıcı API anahtarlarını girin.', 'AI', 'admin/ai-center', 'pending', 0, 100, '2026-06-23 20:35:35'),
(28, 'sitebuilder', 'SiteBuilder Ayarları', 'SiteBuilder, export ZIP ve tema entegrasyonunu kontrol edin.', 'Builder', 'admin/site-builder', 'pending', 0, 110, '2026-06-23 20:35:35'),
(29, 'mobilebuilder', 'MobileBuilder ve Build Center', 'PWA/Flutter/Android export, SDK, Gradle ve build kuyruğu ayarlarını kontrol edin.', 'Builder', 'admin/mobile-builder', 'pending', 0, 120, '2026-06-23 20:35:35'),
(30, 'license', 'Lisans Merkezi', 'SiteBuilder, MobileBuilder, tema, marketplace ve kaynak kod lisans kurallarını tanımlayın.', 'Lisans', 'admin/license-center', 'pending', 0, 130, '2026-06-23 20:35:35'),
(31, 'marketplace', 'Marketplace Ayarları', 'Domain, hosting, web tasarım, SEO, logo ve dijital ürün satış kurallarını ayarlayın.', 'Marketplace', 'admin/marketplace', 'pending', 0, 140, '2026-06-23 20:35:35'),
(32, 'security', 'Güvenlik ve Yetkiler', 'Admin rolleri, 2FA, IP kısıtlama, oturum süresi ve CSRF ayarlarını kontrol edin.', 'Güvenlik', 'admin/security', 'pending', 1, 150, '2026-06-23 20:35:35'),
(33, 'backup', 'Backup / Restore', 'Veritabanı ve dosya yedekleme planını oluşturun.', 'Sistem', 'admin/backup-center', 'pending', 1, 160, '2026-06-23 20:35:35'),
(34, 'scan', 'Sistem Taraması', 'Kurulum sonunda Scan & Report Center ile PDF rapor alın.', 'Sistem', 'admin/scan-report', 'pending', 1, 170, '2026-06-23 20:35:35');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sitebuilder_exports`
--

CREATE TABLE `sitebuilder_exports` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` varchar(30) DEFAULT 'ready',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sitebuilder_pages`
--

CREATE TABLE `sitebuilder_pages` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(180) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `page_type` varchar(40) DEFAULT 'page',
  `builder_json` longtext DEFAULT NULL,
  `html_cache` longtext DEFAULT NULL,
  `status` varchar(30) DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `sitebuilder_pages`
--

INSERT INTO `sitebuilder_pages` (`id`, `project_id`, `title`, `slug`, `page_type`, `builder_json`, `html_cache`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ana Sayfa', 'index', 'home', '[{\"id\":\"hero\",\"type\":\"hero\",\"cols\":1,\"content\":[{\"id\":\"h1\",\"type\":\"heading\",\"text\":\"Ahost One ile dijital işinizi büyütün\",\"props\":[]},{\"id\":\"p1\",\"type\":\"text\",\"text\":\"Domain, hosting, marketplace ve site builder çözümleri tek platformda.\",\"props\":[]},{\"id\":\"b1\",\"type\":\"button\",\"text\":\"Hemen Başla\",\"props\":[]}]}]', NULL, 'published', '2026-06-23 21:12:57', '2026-06-23 21:12:57');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sitebuilder_projects`
--

CREATE TABLE `sitebuilder_projects` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `type` varchar(40) DEFAULT 'site',
  `theme_slug` varchar(80) DEFAULT 'default',
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `sitebuilder_projects`
--

INSERT INTO `sitebuilder_projects` (`id`, `customer_id`, `name`, `type`, `theme_slug`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Ahost Demo Site', 'site', 'default', 'active', '2026-06-23 21:12:57', '2026-06-23 21:12:57');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sitebuilder_revisions`
--

CREATE TABLE `sitebuilder_revisions` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `builder_json` longtext DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sitebuilder_templates`
--

CREATE TABLE `sitebuilder_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `category` varchar(80) DEFAULT 'general',
  `builder_json` longtext DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms_balance_checks`
--

CREATE TABLE `sms_balance_checks` (
  `id` int(11) NOT NULL,
  `provider` varchar(80) NOT NULL,
  `balance_text` varchar(190) DEFAULT NULL,
  `raw_response` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'unknown',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `support_departments`
--

CREATE TABLE `support_departments` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `support_live_chats`
--

CREATE TABLE `support_live_chats` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `visitor_name` varchar(190) DEFAULT NULL,
  `visitor_email` varchar(190) DEFAULT NULL,
  `department` varchar(120) DEFAULT 'Teknik Destek',
  `subject` varchar(255) DEFAULT 'Canlı Sohbet',
  `status` varchar(40) DEFAULT 'waiting',
  `assigned_admin_id` int(11) DEFAULT NULL,
  `source_url` varchar(255) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `support_live_messages`
--

CREATE TABLE `support_live_messages` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_type` varchar(40) DEFAULT 'visitor',
  `sender_id` int(11) DEFAULT NULL,
  `sender_name` varchar(190) DEFAULT NULL,
  `message` longtext NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `support_widget_events`
--

CREATE TABLE `support_widget_events` (
  `id` int(11) NOT NULL,
  `event_type` varchar(80) NOT NULL,
  `name` varchar(190) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `query_text` text DEFAULT NULL,
  `response_text` longtext DEFAULT NULL,
  `source_url` varchar(255) DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `support_widget_settings`
--

CREATE TABLE `support_widget_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(160) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `templates`
--

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `template_key` varchar(160) NOT NULL,
  `template_type` varchar(40) NOT NULL DEFAULT 'email',
  `title` varchar(190) DEFAULT NULL,
  `subject` varchar(220) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tenant_accounts`
--

CREATE TABLE `tenant_accounts` (
  `id` int(11) NOT NULL,
  `company_name` varchar(190) NOT NULL,
  `domain` varchar(190) DEFAULT NULL,
  `plan` varchar(80) DEFAULT 'standard',
  `status` varchar(40) DEFAULT 'inactive',
  `settings_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `themes`
--

CREATE TABLE `themes` (
  `id` int(11) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `name` varchar(160) NOT NULL,
  `area` varchar(40) DEFAULT 'site',
  `description` text DEFAULT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `preview_url` varchar(255) DEFAULT NULL,
  `primary_color` varchar(20) DEFAULT '#2563eb',
  `secondary_color` varchar(20) DEFAULT '#7c3aed',
  `font_family` varchar(120) DEFAULT 'Inter, Arial, sans-serif',
  `custom_css` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `status` varchar(30) DEFAULT 'installed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `radius` varchar(24) DEFAULT '24px',
  `button_radius` varchar(24) DEFAULT '16px',
  `button_style` varchar(40) DEFAULT 'gradient',
  `background_color` varchar(24) DEFAULT '#f8fbff',
  `background_gradient` varchar(190) DEFAULT NULL,
  `header_mode` varchar(40) DEFAULT 'sticky',
  `mobile_bottom_nav` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `themes`
--

INSERT INTO `themes` (`id`, `slug`, `name`, `area`, `description`, `preview_image`, `preview_url`, `primary_color`, `secondary_color`, `font_family`, `custom_css`, `is_active`, `status`, `created_at`, `updated_at`, `radius`, `button_radius`, `button_style`, `background_color`, `background_gradient`, `header_mode`, `mobile_bottom_nav`) VALUES
(341, 'ahost-default', 'Ahost Default', 'site', 'Ahost Default site ön yüz teması.', NULL, NULL, '#2563eb', '#0f172a', 'Inter, Arial, sans-serif', NULL, 1, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(342, 'modern-hosting', 'Modern Hosting', 'site', 'Modern Hosting site ön yüz teması.', NULL, NULL, '#7c3aed', '#06b6d4', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(343, 'cloud-pro', 'Cloud Pro', 'site', 'Cloud Pro site ön yüz teması.', NULL, NULL, '#0284c7', '#22c55e', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(344, 'dark-server', 'Dark Server', 'site', 'Dark Server site ön yüz teması.', NULL, NULL, '#111827', '#38bdf8', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(345, 'corporate-business', 'Corporate Business', 'site', 'Corporate Business site ön yüz teması.', NULL, NULL, '#334155', '#c59f45', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(346, 'domain-marketplace', 'Domain Marketplace', 'site', 'Domain Marketplace site ön yüz teması.', NULL, NULL, '#f97316', '#111827', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(347, 'ai-company', 'AI Company', 'site', 'AI Company site ön yüz teması.', NULL, NULL, '#8b5cf6', '#14b8a6', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(348, 'web-agency', 'Web Agency', 'site', 'Web Agency site ön yüz teması.', NULL, NULL, '#ec4899', '#3b82f6', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(349, 'ecommerce-services', 'E-Commerce Services', 'site', 'E-Commerce Services site ön yüz teması.', NULL, NULL, '#16a34a', '#f59e0b', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(350, 'ultra-premium', 'Ultra Premium', 'site', 'Ultra Premium site ön yüz teması.', NULL, NULL, '#0ea5e9', '#a855f7', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(351, 'admin-default', 'Admin Default', 'admin', 'Admin Default admin panel teması.', NULL, NULL, '#2563eb', '#0f172a', 'Inter, Arial, sans-serif', NULL, 1, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(352, 'admin-dark', 'Admin Dark', 'admin', 'Admin Dark admin panel teması.', NULL, NULL, '#111827', '#38bdf8', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(353, 'admin-glass', 'Admin Glass', 'admin', 'Admin Glass admin panel teması.', NULL, NULL, '#7c3aed', '#06b6d4', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(354, 'client-default', 'Client Default', 'client', 'Client Default müşteri paneli teması.', NULL, NULL, '#2563eb', '#0f172a', 'Inter, Arial, sans-serif', NULL, 1, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(355, 'client-modern', 'Client Modern', 'client', 'Client Modern müşteri paneli teması.', NULL, NULL, '#7c3aed', '#06b6d4', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(356, 'client-dark', 'Client Dark', 'client', 'Client Dark müşteri paneli teması.', NULL, NULL, '#111827', '#38bdf8', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1),
(357, 'client-corporate', 'Client Corporate', 'client', 'Client Corporate müşteri paneli teması.', NULL, NULL, '#334155', '#c59f45', 'Inter, Arial, sans-serif', NULL, 0, 'installed', '2026-06-23 20:35:28', '2026-06-23 20:35:28', '24px', '16px', 'gradient', '#f8fbff', NULL, 'sticky', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `theme_apply_logs`
--

CREATE TABLE `theme_apply_logs` (
  `id` int(11) NOT NULL,
  `theme_id` int(11) DEFAULT NULL,
  `area` varchar(40) DEFAULT 'site',
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(80) DEFAULT 'apply',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(40) DEFAULT 'open',
  `priority` varchar(40) DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `message` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tld_pricing`
--

CREATE TABLE `tld_pricing` (
  `id` int(11) NOT NULL,
  `tld` varchar(40) NOT NULL,
  `registrar_slug` varchar(120) DEFAULT NULL,
  `register_price` decimal(14,2) DEFAULT 0.00,
  `renew_price` decimal(14,2) DEFAULT 0.00,
  `transfer_price` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'USD',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `abandoned_carts`
--
ALTER TABLE `abandoned_carts`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `admin_preferences`
--
ALTER TABLE `admin_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_admin_pref` (`admin_id`,`pref_key`);

--
-- Tablo için indeksler `admin_search_index`
--
ALTER TABLE `admin_search_index`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_route_title` (`route`,`title`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_title` (`title`);

--
-- Tablo için indeksler `ai_copilot_threads`
--
ALTER TABLE `ai_copilot_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `ai_seo_audits`
--
ALTER TABLE `ai_seo_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `target_lookup` (`target_type`,`target_id`),
  ADD KEY `overall_score` (`overall_score`);

--
-- Tablo için indeksler `api_logs`
--
ALTER TABLE `api_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider` (`provider`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `asset_version_registry`
--
ALTER TABLE `asset_version_registry`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `area_group` (`area`,`asset_group`);

--
-- Tablo için indeksler `auth_login_events`
--
ALTER TABLE `auth_login_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_lookup` (`user_type`,`user_id`),
  ADD KEY `event_type` (`event_type`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `auth_mfa_profiles`
--
ALTER TABLE `auth_mfa_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_mfa_user` (`user_type`,`user_id`);

--
-- Tablo için indeksler `auth_otp_tokens`
--
ALTER TABLE `auth_otp_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_lookup` (`user_type`,`user_id`),
  ADD KEY `method` (`method`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Tablo için indeksler `automation_logs`
--
ALTER TABLE `automation_logs`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `automation_rules`
--
ALTER TABLE `automation_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trigger_event` (`trigger_event`),
  ADD KEY `is_active` (`is_active`);

--
-- Tablo için indeksler `bridge_connections`
--
ALTER TABLE `bridge_connections`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `bridge_import_maps`
--
ALTER TABLE `bridge_import_maps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_bridge_map` (`connection_id`,`entity_type`,`source_id`,`target_table`),
  ADD KEY `target_lookup` (`target_table`,`target_id`);

--
-- Tablo için indeksler `bridge_import_selections`
--
ALTER TABLE `bridge_import_selections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_bridge_selection` (`connection_id`,`entity_type`,`source_id`),
  ADD KEY `entity_lookup` (`entity_type`,`selected`);

--
-- Tablo için indeksler `bridge_items`
--
ALTER TABLE `bridge_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `run_id` (`run_id`),
  ADD KEY `entity_type` (`entity_type`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `bridge_runs`
--
ALTER TABLE `bridge_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `connection_id` (`connection_id`);

--
-- Tablo için indeksler `bridge_sql_uploads`
--
ALTER TABLE `bridge_sql_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `connection_id` (`connection_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `build_repository_files`
--
ALTER TABLE `build_repository_files`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `client_preferences`
--
ALTER TABLE `client_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_client_pref` (`client_id`);

--
-- Tablo için indeksler `client_security_questions`
--
ALTER TABLE `client_security_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_customer_question` (`customer_id`);

--
-- Tablo için indeksler `commerce_completion_checks`
--
ALTER TABLE `commerce_completion_checks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_check` (`module_key`,`check_key`);

--
-- Tablo için indeksler `configurable_options`
--
ALTER TABLE `configurable_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Tablo için indeksler `configurable_option_values`
--
ALTER TABLE `configurable_option_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `option_id` (`option_id`);

--
-- Tablo için indeksler `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_slug` (`type`,`slug`),
  ADD KEY `type` (`type`),
  ADD KEY `category_id` (`category_id`);

--
-- Tablo için indeksler `content_categories`
--
ALTER TABLE `content_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_slug` (`type`,`slug`);

--
-- Tablo için indeksler `content_media`
--
ALTER TABLE `content_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`);

--
-- Tablo için indeksler `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `customer_account_users`
--
ALTER TABLE `customer_account_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_customer_user_email` (`customer_id`,`email`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `customer_activity_logs`
--
ALTER TABLE `customer_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `action` (`action`);

--
-- Tablo için indeksler `customer_connected_sites`
--
ALTER TABLE `customer_connected_sites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `domain` (`domain`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `customer_domains`
--
ALTER TABLE `customer_domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_name` (`domain_name`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `domain_id` (`domain_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `registrar_id` (`registrar_id`),
  ADD KEY `expiry_date` (`expiry_date`),
  ADD KEY `status` (`status`),
  ADD KEY `last_synced_at` (`last_synced_at`);

--
-- Tablo için indeksler `customer_groups`
--
ALTER TABLE `customer_groups`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `customer_group_members`
--
ALTER TABLE `customer_group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_group_unique` (`customer_id`,`group_id`);

--
-- Tablo için indeksler `customer_notifications`
--
ALTER TABLE `customer_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `read_at` (`read_at`);

--
-- Tablo için indeksler `customer_payment_methods`
--
ALTER TABLE `customer_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `customer_product_update_status`
--
ALTER TABLE `customer_product_update_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_customer_package` (`package_id`,`customer_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `customer_site_backups`
--
ALTER TABLE `customer_site_backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `connected_site_id` (`connected_site_id`),
  ADD KEY `rollback_token` (`rollback_token`);

--
-- Tablo için indeksler `customer_user_activity_logs`
--
ALTER TABLE `customer_user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `account_user_id` (`account_user_id`),
  ADD KEY `action` (`action`);

--
-- Tablo için indeksler `customer_user_sessions`
--
ALTER TABLE `customer_user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `account_user_id` (`account_user_id`);

--
-- Tablo için indeksler `domains`
--
ALTER TABLE `domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_name` (`domain_name`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `expiry_date` (`expiry_date`);

--
-- Tablo için indeksler `domain_bulk_operation_queue`
--
ALTER TABLE `domain_bulk_operation_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operation` (`operation`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `domain_contacts`
--
ALTER TABLE `domain_contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_id` (`domain_id`);

--
-- Tablo için indeksler `domain_dns_records`
--
ALTER TABLE `domain_dns_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_id` (`domain_id`);

--
-- Tablo için indeksler `domain_document_rules`
--
ALTER TABLE `domain_document_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_tld` (`tld`);

--
-- Tablo için indeksler `domain_intelligence_reports`
--
ALTER TABLE `domain_intelligence_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_name` (`domain_name`);

--
-- Tablo için indeksler `domain_investment_watchlist`
--
ALTER TABLE `domain_investment_watchlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_unique` (`domain`),
  ADD KEY `status` (`status`),
  ADD KEY `drop_date` (`drop_date`);

--
-- Tablo için indeksler `domain_nameservers`
--
ALTER TABLE `domain_nameservers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_id` (`domain_id`);

--
-- Tablo için indeksler `domain_operation_logs`
--
ALTER TABLE `domain_operation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_name` (`domain_name`),
  ADD KEY `operation` (`operation`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `domain_order_routes`
--
ALTER TABLE `domain_order_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain` (`domain`),
  ADD KEY `order_id` (`order_id`);

--
-- Tablo için indeksler `domain_price_cache`
--
ALTER TABLE `domain_price_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_tld_registrar` (`tld`,`registrar`);

--
-- Tablo için indeksler `domain_price_import_logs`
--
ALTER TABLE `domain_price_import_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registrar_slug` (`registrar_slug`);

--
-- Tablo için indeksler `domain_pricing_rules`
--
ALTER TABLE `domain_pricing_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_tld` (`tld`);

--
-- Tablo için indeksler `domain_registrars`
--
ALTER TABLE `domain_registrars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `domain_ssl_checks`
--
ALTER TABLE `domain_ssl_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_id` (`domain_id`),
  ADD KEY `domain_name` (`domain_name`);

--
-- Tablo için indeksler `domain_sync_logs`
--
ALTER TABLE `domain_sync_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_id` (`domain_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Tablo için indeksler `domain_whois_records`
--
ALTER TABLE `domain_whois_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_id` (`domain_id`),
  ADD KEY `domain_name` (`domain_name`);

--
-- Tablo için indeksler `hosting_accounts`
--
ALTER TABLE `hosting_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `server_id` (`server_id`),
  ADD KEY `username` (`username`);

--
-- Tablo için indeksler `hosting_account_logs`
--
ALTER TABLE `hosting_account_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hosting_account_id` (`hosting_account_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `action` (`action`);

--
-- Tablo için indeksler `hosting_automation_settings`
--
ALTER TABLE `hosting_automation_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `hosting_health_checks`
--
ALTER TABLE `hosting_health_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `hosting_operation_queue`
--
ALTER TABLE `hosting_operation_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `operation` (`operation`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `hosting_password_change_logs`
--
ALTER TABLE `hosting_password_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Tablo için indeksler `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `invoice_activity_logs`
--
ALTER TABLE `invoice_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `action` (`action`);

--
-- Tablo için indeksler `invoice_email_logs`
--
ALTER TABLE `invoice_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Tablo için indeksler `language_translations`
--
ALTER TABLE `language_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lang_key` (`lang_code`,`translation_key`);

--
-- Tablo için indeksler `license_injection_jobs`
--
ALTER TABLE `license_injection_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `license_key` (`license_key`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `license_private_keys`
--
ALTER TABLE `license_private_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Tablo için indeksler `marketplace_auctions`
--
ALTER TABLE `marketplace_auctions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `marketplace_categories`
--
ALTER TABLE `marketplace_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `marketplace_escrow`
--
ALTER TABLE `marketplace_escrow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `marketplace_escrow_transactions`
--
ALTER TABLE `marketplace_escrow_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `marketplace_feature_packages`
--
ALTER TABLE `marketplace_feature_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_feature_days` (`days`);

--
-- Tablo için indeksler `marketplace_listings`
--
ALTER TABLE `marketplace_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `listing_type` (`listing_type`),
  ADD KEY `domain_name` (`domain_name`);

--
-- Tablo için indeksler `marketplace_offers`
--
ALTER TABLE `marketplace_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `marketplace_revenue`
--
ALTER TABLE `marketplace_revenue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `source_type` (`source_type`);

--
-- Tablo için indeksler `marketplace_seller_profiles`
--
ALTER TABLE `marketplace_seller_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menu_key` (`menu_key`),
  ADD KEY `location` (`location`);

--
-- Tablo için indeksler `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `sort_order` (`sort_order`);

--
-- Tablo için indeksler `module_update_logs`
--
ALTER TABLE `module_update_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_key` (`module_key`);

--
-- Tablo için indeksler `module_visibility`
--
ALTER TABLE `module_visibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_key` (`module_key`);

--
-- Tablo için indeksler `notification_channels`
--
ALTER TABLE `notification_channels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `channel_provider` (`channel`,`provider`);

--
-- Tablo için indeksler `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `channel` (`channel`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_channel` (`template_key`,`channel`);

--
-- Tablo için indeksler `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Tablo için indeksler `order_status_logs`
--
ALTER TABLE `order_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `action` (`action`);

--
-- Tablo için indeksler `package_builders`
--
ALTER TABLE `package_builders`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_hash` (`token_hash`),
  ADD KEY `email` (`email`);

--
-- Tablo için indeksler `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `payment_fee_rules`
--
ALTER TABLE `payment_fee_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_gateway` (`gateway`);

--
-- Tablo için indeksler `payment_fee_sync_logs`
--
ALTER TABLE `payment_fee_sync_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gateway` (`gateway`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `portfolio_references`
--
ALTER TABLE `portfolio_references`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `reference_type` (`reference_type`),
  ADD KEY `is_active` (`is_active`);

--
-- Tablo için indeksler `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `server_group_id` (`server_group_id`),
  ADD KEY `visibility` (`visibility`),
  ADD KEY `type` (`type`);

--
-- Tablo için indeksler `product_checkout_addons`
--
ALTER TABLE `product_checkout_addons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_addon_key` (`product_id`,`addon_key`),
  ADD KEY `idx_product_checkout_addons_product` (`product_id`),
  ADD KEY `idx_product_checkout_addons_active` (`is_active`);

--
-- Tablo için indeksler `product_custom_fields`
--
ALTER TABLE `product_custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_field_key` (`field_key`);

--
-- Tablo için indeksler `product_groups`
--
ALTER TABLE `product_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `product_price_update_logs`
--
ALTER TABLE `product_price_update_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `action` (`action`),
  ADD KEY `cycle` (`cycle`);

--
-- Tablo için indeksler `product_pricing`
--
ALTER TABLE `product_pricing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_product_cycle` (`product_id`,`cycle`);

--
-- Tablo için indeksler `product_promotions`
--
ALTER TABLE `product_promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_promo_code` (`code`);

--
-- Tablo için indeksler `product_revision_logs`
--
ALTER TABLE `product_revision_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `action` (`action`);

--
-- Tablo için indeksler `product_update_packages`
--
ALTER TABLE `product_update_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_lookup` (`product_type`,`product_slug`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `registrar_capability_matrix`
--
ALTER TABLE `registrar_capability_matrix`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_reg_operation` (`registrar_slug`,`operation`);

--
-- Tablo için indeksler `registrar_configs`
--
ALTER TABLE `registrar_configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_reg_key` (`registrar_id`,`config_key`);

--
-- Tablo için indeksler `registrar_price_cache`
--
ALTER TABLE `registrar_price_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_reg_tld_action` (`registrar_slug`,`tld`,`action`);

--
-- Tablo için indeksler `remote_update_jobs`
--
ALTER TABLE `remote_update_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `connected_site_id` (`connected_site_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `renewal_automation_logs`
--
ALTER TABLE `renewal_automation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `action` (`action`);

--
-- Tablo için indeksler `route_aliases`
--
ALTER TABLE `route_aliases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alias_path` (`alias_path`);

--
-- Tablo için indeksler `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `panel_type` (`panel_type`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `server_groups`
--
ALTER TABLE `server_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_server_group_name` (`name`);

--
-- Tablo için indeksler `server_nodes`
--
ALTER TABLE `server_nodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `panel_type` (`panel_type`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `status` (`status`),
  ADD KEY `next_due_date` (`next_due_date`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `setup_wizard_runs`
--
ALTER TABLE `setup_wizard_runs`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `setup_wizard_steps`
--
ALTER TABLE `setup_wizard_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `step_key` (`step_key`);

--
-- Tablo için indeksler `sitebuilder_exports`
--
ALTER TABLE `sitebuilder_exports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Tablo için indeksler `sitebuilder_pages`
--
ALTER TABLE `sitebuilder_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_project_slug` (`project_id`,`slug`),
  ADD KEY `project_id` (`project_id`);

--
-- Tablo için indeksler `sitebuilder_projects`
--
ALTER TABLE `sitebuilder_projects`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sitebuilder_revisions`
--
ALTER TABLE `sitebuilder_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`);

--
-- Tablo için indeksler `sitebuilder_templates`
--
ALTER TABLE `sitebuilder_templates`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sms_balance_checks`
--
ALTER TABLE `sms_balance_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider` (`provider`);

--
-- Tablo için indeksler `support_departments`
--
ALTER TABLE `support_departments`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `support_live_chats`
--
ALTER TABLE `support_live_chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `support_live_messages`
--
ALTER TABLE `support_live_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `sender_type` (`sender_type`);

--
-- Tablo için indeksler `support_widget_events`
--
ALTER TABLE `support_widget_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_type` (`event_type`),
  ADD KEY `email` (`email`);

--
-- Tablo için indeksler `support_widget_settings`
--
ALTER TABLE `support_widget_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_key_type` (`template_key`,`template_type`);

--
-- Tablo için indeksler `tenant_accounts`
--
ALTER TABLE `tenant_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain` (`domain`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug_area` (`slug`,`area`);

--
-- Tablo için indeksler `theme_apply_logs`
--
ALTER TABLE `theme_apply_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area` (`area`);

--
-- Tablo için indeksler `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status` (`status`);

--
-- Tablo için indeksler `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Tablo için indeksler `tld_pricing`
--
ALTER TABLE `tld_pricing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_tld_reg` (`tld`,`registrar_slug`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `abandoned_carts`
--
ALTER TABLE `abandoned_carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `admin_preferences`
--
ALTER TABLE `admin_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `admin_search_index`
--
ALTER TABLE `admin_search_index`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=550;

--
-- Tablo için AUTO_INCREMENT değeri `ai_copilot_threads`
--
ALTER TABLE `ai_copilot_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `ai_seo_audits`
--
ALTER TABLE `ai_seo_audits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `api_logs`
--
ALTER TABLE `api_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `asset_version_registry`
--
ALTER TABLE `asset_version_registry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `auth_login_events`
--
ALTER TABLE `auth_login_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `auth_mfa_profiles`
--
ALTER TABLE `auth_mfa_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `auth_otp_tokens`
--
ALTER TABLE `auth_otp_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `automation_logs`
--
ALTER TABLE `automation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `automation_rules`
--
ALTER TABLE `automation_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `bridge_connections`
--
ALTER TABLE `bridge_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `bridge_import_maps`
--
ALTER TABLE `bridge_import_maps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=271;

--
-- Tablo için AUTO_INCREMENT değeri `bridge_import_selections`
--
ALTER TABLE `bridge_import_selections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2117;

--
-- Tablo için AUTO_INCREMENT değeri `bridge_items`
--
ALTER TABLE `bridge_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=734;

--
-- Tablo için AUTO_INCREMENT değeri `bridge_runs`
--
ALTER TABLE `bridge_runs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `bridge_sql_uploads`
--
ALTER TABLE `bridge_sql_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `build_repository_files`
--
ALTER TABLE `build_repository_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `client_preferences`
--
ALTER TABLE `client_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `client_security_questions`
--
ALTER TABLE `client_security_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `commerce_completion_checks`
--
ALTER TABLE `commerce_completion_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- Tablo için AUTO_INCREMENT değeri `configurable_options`
--
ALTER TABLE `configurable_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `configurable_option_values`
--
ALTER TABLE `configurable_option_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `contents`
--
ALTER TABLE `contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `content_categories`
--
ALTER TABLE `content_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `content_media`
--
ALTER TABLE `content_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `credit_transactions`
--
ALTER TABLE `credit_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `customer_account_users`
--
ALTER TABLE `customer_account_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_activity_logs`
--
ALTER TABLE `customer_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_connected_sites`
--
ALTER TABLE `customer_connected_sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_domains`
--
ALTER TABLE `customer_domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_groups`
--
ALTER TABLE `customer_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_group_members`
--
ALTER TABLE `customer_group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_notifications`
--
ALTER TABLE `customer_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_payment_methods`
--
ALTER TABLE `customer_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_product_update_status`
--
ALTER TABLE `customer_product_update_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_site_backups`
--
ALTER TABLE `customer_site_backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_user_activity_logs`
--
ALTER TABLE `customer_user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_user_sessions`
--
ALTER TABLE `customer_user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domains`
--
ALTER TABLE `domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Tablo için AUTO_INCREMENT değeri `domain_bulk_operation_queue`
--
ALTER TABLE `domain_bulk_operation_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_contacts`
--
ALTER TABLE `domain_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_dns_records`
--
ALTER TABLE `domain_dns_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_document_rules`
--
ALTER TABLE `domain_document_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `domain_intelligence_reports`
--
ALTER TABLE `domain_intelligence_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_investment_watchlist`
--
ALTER TABLE `domain_investment_watchlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_nameservers`
--
ALTER TABLE `domain_nameservers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_operation_logs`
--
ALTER TABLE `domain_operation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_order_routes`
--
ALTER TABLE `domain_order_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_price_cache`
--
ALTER TABLE `domain_price_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- Tablo için AUTO_INCREMENT değeri `domain_price_import_logs`
--
ALTER TABLE `domain_price_import_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_pricing_rules`
--
ALTER TABLE `domain_pricing_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `domain_registrars`
--
ALTER TABLE `domain_registrars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `domain_ssl_checks`
--
ALTER TABLE `domain_ssl_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_sync_logs`
--
ALTER TABLE `domain_sync_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `domain_whois_records`
--
ALTER TABLE `domain_whois_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `hosting_accounts`
--
ALTER TABLE `hosting_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `hosting_account_logs`
--
ALTER TABLE `hosting_account_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `hosting_automation_settings`
--
ALTER TABLE `hosting_automation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- Tablo için AUTO_INCREMENT değeri `hosting_health_checks`
--
ALTER TABLE `hosting_health_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `hosting_operation_queue`
--
ALTER TABLE `hosting_operation_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `hosting_password_change_logs`
--
ALTER TABLE `hosting_password_change_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Tablo için AUTO_INCREMENT değeri `invoice_activity_logs`
--
ALTER TABLE `invoice_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `invoice_email_logs`
--
ALTER TABLE `invoice_email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- Tablo için AUTO_INCREMENT değeri `language_translations`
--
ALTER TABLE `language_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `license_injection_jobs`
--
ALTER TABLE `license_injection_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `license_private_keys`
--
ALTER TABLE `license_private_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_auctions`
--
ALTER TABLE `marketplace_auctions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_categories`
--
ALTER TABLE `marketplace_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=511;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_escrow`
--
ALTER TABLE `marketplace_escrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_escrow_transactions`
--
ALTER TABLE `marketplace_escrow_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_feature_packages`
--
ALTER TABLE `marketplace_feature_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=361;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_listings`
--
ALTER TABLE `marketplace_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_offers`
--
ALTER TABLE `marketplace_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_revenue`
--
ALTER TABLE `marketplace_revenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `marketplace_seller_profiles`
--
ALTER TABLE `marketplace_seller_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `module_update_logs`
--
ALTER TABLE `module_update_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `module_visibility`
--
ALTER TABLE `module_visibility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Tablo için AUTO_INCREMENT değeri `notification_channels`
--
ALTER TABLE `notification_channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- Tablo için AUTO_INCREMENT değeri `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Tablo için AUTO_INCREMENT değeri `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `order_status_logs`
--
ALTER TABLE `order_status_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `package_builders`
--
ALTER TABLE `package_builders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `payment_fee_rules`
--
ALTER TABLE `payment_fee_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `payment_fee_sync_logs`
--
ALTER TABLE `payment_fee_sync_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `portfolio_references`
--
ALTER TABLE `portfolio_references`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Tablo için AUTO_INCREMENT değeri `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- Tablo için AUTO_INCREMENT değeri `product_checkout_addons`
--
ALTER TABLE `product_checkout_addons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `product_custom_fields`
--
ALTER TABLE `product_custom_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `product_groups`
--
ALTER TABLE `product_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313;

--
-- Tablo için AUTO_INCREMENT değeri `product_price_update_logs`
--
ALTER TABLE `product_price_update_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `product_pricing`
--
ALTER TABLE `product_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;

--
-- Tablo için AUTO_INCREMENT değeri `product_promotions`
--
ALTER TABLE `product_promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `product_revision_logs`
--
ALTER TABLE `product_revision_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `product_update_packages`
--
ALTER TABLE `product_update_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `registrar_capability_matrix`
--
ALTER TABLE `registrar_capability_matrix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1351;

--
-- Tablo için AUTO_INCREMENT değeri `registrar_configs`
--
ALTER TABLE `registrar_configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `registrar_price_cache`
--
ALTER TABLE `registrar_price_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `remote_update_jobs`
--
ALTER TABLE `remote_update_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `renewal_automation_logs`
--
ALTER TABLE `renewal_automation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `route_aliases`
--
ALTER TABLE `route_aliases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `server_groups`
--
ALTER TABLE `server_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- Tablo için AUTO_INCREMENT değeri `server_nodes`
--
ALTER TABLE `server_nodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=652;

--
-- Tablo için AUTO_INCREMENT değeri `setup_wizard_runs`
--
ALTER TABLE `setup_wizard_runs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `setup_wizard_steps`
--
ALTER TABLE `setup_wizard_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Tablo için AUTO_INCREMENT değeri `sitebuilder_exports`
--
ALTER TABLE `sitebuilder_exports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sitebuilder_pages`
--
ALTER TABLE `sitebuilder_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `sitebuilder_projects`
--
ALTER TABLE `sitebuilder_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `sitebuilder_revisions`
--
ALTER TABLE `sitebuilder_revisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sitebuilder_templates`
--
ALTER TABLE `sitebuilder_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sms_balance_checks`
--
ALTER TABLE `sms_balance_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `support_departments`
--
ALTER TABLE `support_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `support_live_chats`
--
ALTER TABLE `support_live_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `support_live_messages`
--
ALTER TABLE `support_live_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `support_widget_events`
--
ALTER TABLE `support_widget_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `support_widget_settings`
--
ALTER TABLE `support_widget_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tenant_accounts`
--
ALTER TABLE `tenant_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=511;

--
-- Tablo için AUTO_INCREMENT değeri `theme_apply_logs`
--
ALTER TABLE `theme_apply_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tld_pricing`
--
ALTER TABLE `tld_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



-- =====================================================
-- MODULE INSTALL SQL BUNDLE
-- =====================================================


-- Module: modules/affiliate/install.sql

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


-- Module: modules/ai/openai/install.sql

CREATE TABLE IF NOT EXISTS module_openai_usage_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  operation VARCHAR(80) NOT NULL,
  model_name VARCHAR(120) NULL,
  prompt_tokens INT DEFAULT 0,
  completion_tokens INT DEFAULT 0,
  status VARCHAR(30) DEFAULT 'success',
  error_message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY operation(operation), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/api-gateway/install.sql

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


-- Module: modules/blog/install.sql

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


-- Module: modules/builder/mobilebuilder/install.sql

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


-- Module: modules/builder/sitebuilder/install.sql

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


-- Module: modules/commerce/marketplace/install.sql

CREATE TABLE IF NOT EXISTS module_marketplace_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NULL,
  event_type VARCHAR(80) NOT NULL,
  amount DECIMAL(14,2) DEFAULT 0,
  currency VARCHAR(10) DEFAULT 'TRY',
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY listing_id(listing_id), KEY event_type(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/e-invoice/install.sql

-- E-Invoice Tables
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('invoice','proforma','receipt') DEFAULT 'invoice',
    customer_id INT,
    customer_name VARCHAR(200) NOT NULL,
    customer_email VARCHAR(150),
    customer_tax_number VARCHAR(20),
    customer_tax_office VARCHAR(100),
    customer_address TEXT,
    subtotal DECIMAL(12,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 18.00,
    tax_amount DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TRY',
    status ENUM('draft','sent','paid','cancelled','refunded') DEFAULT 'draft',
    due_date DATE,
    paid_at DATETIME DEFAULT NULL,
    notes TEXT,
    pdf_path VARCHAR(500),
    gib_status VARCHAR(50),
    gib_uuid VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_number (invoice_number),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1.00,
    unit_price DECIMAL(12,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 18.00,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proforma_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proforma_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT,
    customer_name VARCHAR(200) NOT NULL,
    customer_email VARCHAR(150),
    subtotal DECIMAL(12,2) NOT NULL,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TRY',
    valid_until DATE,
    converted_to_invoice_id INT DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_number (proforma_number),
    FOREIGN KEY (converted_to_invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/email-templates/install.sql

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


-- Module: modules/kanban/install.sql

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


-- Module: modules/knowledge/knowledge-academy-pro/install.sql

CREATE TABLE IF NOT EXISTS module_knowledge_academy_pro_meta (id INT AUTO_INCREMENT PRIMARY KEY, meta_key VARCHAR(120), meta_value LONGTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/license/license-center/install.sql

CREATE TABLE IF NOT EXISTS module_license_center_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  license_id INT NULL,
  event_type VARCHAR(80) NOT NULL,
  status VARCHAR(30) DEFAULT 'info',
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY license_id(license_id), KEY event_type(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/live-chat/install.sql

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


-- Module: modules/migration/migration-bridge-pro/install.sql

CREATE TABLE IF NOT EXISTS module_migration_bridge_connections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  source_type VARCHAR(32) NOT NULL DEFAULT 'Kaynak Sistem',
  title VARCHAR(160) NULL,
  host VARCHAR(190) NULL,
  port INT DEFAULT 3306,
  database_name VARCHAR(190) NULL,
  username VARCHAR(190) NULL,
  password_encrypted TEXT NULL,
  charset_name VARCHAR(32) DEFAULT 'utf8mb4',
  status VARCHAR(30) DEFAULT 'draft',
  last_error TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_scans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  connection_id INT NULL,
  source_type VARCHAR(32) NOT NULL DEFAULT 'Kaynak Sistem',
  summary_json LONGTEXT NULL,
  status VARCHAR(30) DEFAULT 'scanned',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  scan_id INT NOT NULL,
  item_type VARCHAR(40) NOT NULL,
  source_id VARCHAR(80) NOT NULL,
  title VARCHAR(255) NULL,
  subtitle VARCHAR(255) NULL,
  payload_json LONGTEXT NULL,
  action VARCHAR(30) DEFAULT 'import',
  conflict_status VARCHAR(30) DEFAULT 'new',
  mapped_id VARCHAR(80) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_scan_type (scan_id,item_type),
  INDEX idx_source (item_type,source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_maps (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  item_type VARCHAR(40) NOT NULL,
  source_id VARCHAR(80) NOT NULL,
  target_table VARCHAR(120) NULL,
  target_id VARCHAR(80) NULL,
  payload_hash VARCHAR(80) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_item_map (item_type,source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  scan_id INT NULL,
  level VARCHAR(20) DEFAULT 'info',
  message TEXT NULL,
  context_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS module_migration_bridge_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO module_migration_bridge_settings (setting_key, setting_value) VALUES
('target_currency','TRY'),
('fallback_usd_try','40'),
('fallback_eur_try','43'),
('margin_percent','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);


-- Module: modules/payment/paytr/install.sql

CREATE TABLE IF NOT EXISTS module_paytr_callbacks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_oid VARCHAR(190) NULL,
  status VARCHAR(30) DEFAULT 'received',
  amount DECIMAL(14,2) DEFAULT 0,
  payload_json LONGTEXT NULL,
  processed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY merchant_oid(merchant_oid), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/payment/shopier/install.sql

CREATE TABLE IF NOT EXISTS module_shopier_callbacks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  platform_order_id VARCHAR(190) NULL,
  status VARCHAR(30) DEFAULT 'received',
  amount DECIMAL(14,2) DEFAULT 0,
  payload_json LONGTEXT NULL,
  processed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY platform_order_id(platform_order_id), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/provider/provider-center-pro/install.sql

CREATE TABLE IF NOT EXISTS module_provider_center_pro_meta (id INT AUTO_INCREMENT PRIMARY KEY, meta_key VARCHAR(120), meta_value LONGTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/registrar/domainnameapi/install.sql

CREATE TABLE IF NOT EXISTS module_domainnameapi_operations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  domain_name VARCHAR(190) NULL,
  operation VARCHAR(80) NOT NULL,
  status VARCHAR(30) DEFAULT 'pending',
  request_json LONGTEXT NULL,
  response_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY domain_name(domain_name), KEY operation(operation), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/security/install.sql

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


-- Module: modules/sms/iletimerkezi/install.sql

CREATE TABLE IF NOT EXISTS module_iletimerkezi_messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  recipient VARCHAR(80) NOT NULL,
  event_key VARCHAR(120) NULL,
  status VARCHAR(30) DEFAULT 'pending',
  provider_response LONGTEXT NULL,
  sent_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY recipient(recipient), KEY event_key(event_key), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/support/support-widget-pro/install.sql

CREATE TABLE IF NOT EXISTS module_support_widget_pro_meta (id INT AUTO_INCREMENT PRIMARY KEY, meta_key VARCHAR(120), meta_value LONGTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/system/build-center/install.sql

CREATE TABLE IF NOT EXISTS module_build_center_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  build_id INT NULL,
  event_type VARCHAR(80) NOT NULL,
  status VARCHAR(30) DEFAULT 'info',
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY build_id(build_id), KEY event_type(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/system/currency-translation-pro/install.sql

CREATE TABLE IF NOT EXISTS module_currency_translation_pro_meta (id INT AUTO_INCREMENT PRIMARY KEY, meta_key VARCHAR(120), meta_value LONGTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Module: modules/system/module-center-pro/install.sql

CREATE TABLE IF NOT EXISTS module_module_center_pro_meta (id INT AUTO_INCREMENT PRIMARY KEY, meta_key VARCHAR(120), meta_value LONGTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================
-- SITE BUILDER NAMING COMPATIBILITY
-- Project contains both sitebuilder_* (core) and site_builder_* (module) naming.
-- Fresh install includes both table families to prevent missing table errors while modules are consolidated.
-- =====================================================
CREATE TABLE IF NOT EXISTS sitebuilder_projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NULL,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NULL,
  template_key VARCHAR(120) NULL,
  status VARCHAR(40) DEFAULT 'draft',
  settings_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY customer_id(customer_id), KEY slug(slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS sitebuilder_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  content_json LONGTEXT NULL,
  seo_json LONGTEXT NULL,
  status VARCHAR(40) DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY project_id(project_id), KEY slug(slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS sitebuilder_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  category VARCHAR(120) NULL,
  preview_image VARCHAR(255) NULL,
  template_json LONGTEXT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS sitebuilder_exports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  export_type VARCHAR(40) DEFAULT 'zip',
  file_path VARCHAR(255) NULL,
  status VARCHAR(40) DEFAULT 'ready',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY project_id(project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS sitebuilder_revisions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  page_id INT NULL,
  revision_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY project_id(project_id), KEY page_id(page_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- =====================================================
-- QUOTATION SYSTEM - v25.0.0-rc24
-- Teklif talepleri ve yönetimi
-- =====================================================
CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NULL,
    customer_company VARCHAR(255) NULL,
    service_type ENUM('website', 'mobile_app', 'web_app', 'custom_software', 'other') NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    project_description TEXT NULL,
    features JSON NULL,
    budget_min DECIMAL(10,2) NULL,
    budget_max DECIMAL(10,2) NULL,
    budget_currency VARCHAR(3) DEFAULT 'TRY',
    target_completion DATE NULL,
    urgency ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('pending', 'reviewed', 'quoted', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending',
    quoted_price DECIMAL(10,2) NULL,
    quoted_currency VARCHAR(3) DEFAULT 'TRY',
    quoted_notes TEXT NULL,
    quoted_at TIMESTAMP NULL,
    assigned_to INT NULL,
    source VARCHAR(50) DEFAULT 'website',
    referer_url VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_email (customer_email),
    INDEX idx_status (status),
    INDEX idx_service_type (service_type),
    INDEX idx_created_at (created_at),
    INDEX idx_assigned_to (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    sender_type ENUM('customer', 'admin', 'system') NOT NULL,
    sender_id INT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_quotation_id (quotation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NULL,
    mime_type VARCHAR(100) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_quotation_id (quotation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- MOBILE BUILDER - v25.0.0-rc24
-- MobileBuilder projeleri ve build geçmişi
-- =====================================================
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
    INDEX idx_build_type (build_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

INSERT IGNORE INTO module_mobilebuilder_templates (template_key, name, category, description, features) VALUES
('blank', 'Boş Uygulama', 'general', 'Sıfırdan başlayın', '["Özel tasarım", "Sınırsız sayfa", "Tüm özellikler"]'),
('realestate', 'Emlak Uygulaması', 'business', 'Gayrimenkul satış ve kiralama', '["İlan yönetimi", "Harita entegrasyonu", "Filtreleme", "Favoriler"]'),
('restaurant', 'Restoran Uygulaması', 'business', 'Restoran ve cafe için', '["Menü sistemi", "Rezervasyon", "Sipariş", "Promosyonlar"]'),
('radio', 'Radyo Uygulaması', 'media', 'Radyo ve podcast için', '["Canlı dinleme", "Podcast", "Program rehberi", "Bildirimler"]'),
('corporate', 'Kurumsal Uygulama', 'business', 'Şirketler için profesyonel uygulama', '["Hakkımızda", "Hizmetler", "Blog", "İletişim formu"]'),
('ecommerce', 'E-Ticaret Uygulaması', 'business', 'Online alışveriş için', '["Ürün kataloğu", "Sepet", "Ödeme", "Sipariş takibi"]'),
('news', 'Haber Uygulaması', 'media', 'Haber ve medya için', '["Kategori sistemi", "Bildirimler", "Video haber", "Arşiv"]'),
('education', 'Eğitim Uygulaması', 'education', 'Kurs ve eğitim platformu', '["Kurslar", "Video içerik", "Sınav sistemi", "Sertifika"]');

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

