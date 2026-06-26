-- Ahost One v24.8.0
-- Ürünler listesinden hızlı fiyat düzeltme ve toplu fiyat güncelleme işlem geçmişi.
-- Mevcut ürün/fiyat verilerini silmez; sadece log tablosu ekler.
CREATE TABLE IF NOT EXISTS `product_price_update_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(80) DEFAULT 'quick_update',
  `cycle` varchar(40) DEFAULT 'monthly',
  `old_snapshot` longtext DEFAULT NULL,
  `new_snapshot` longtext DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `action` (`action`),
  KEY `cycle` (`cycle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
