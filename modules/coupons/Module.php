<?php
class AhostModule_coupons {
    public static function install(PDO $db): bool {
        $db->exec("CREATE TABLE IF NOT EXISTS coupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            type ENUM('percent','fixed') DEFAULT 'percent',
            value DECIMAL(10,2) NOT NULL,
            min_amount DECIMAL(12,2) DEFAULT 0,
            max_discount DECIMAL(12,2) DEFAULT NULL,
            max_uses INT DEFAULT NULL,
            used_count INT DEFAULT 0,
            valid_from DATETIME,
            valid_until DATETIME,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }
    public static function validate($code, $amount): array {
        $coupon = db()->prepare("SELECT * FROM coupons WHERE code=? AND is_active=1")->execute([$code])->fetch();
        if(!$coupon) return ['valid'=>false,'error'=>'Geçersiz kupon'];
        if($coupon['max_uses'] && $coupon['used_count'] >= $coupon['max_uses']) return ['valid'=>false,'error'=>'Kupon kullanım limiti doldu'];
        if($coupon['valid_until'] && strtotime($coupon['valid_until']) < time()) return ['valid'=>false,'error'=>'Kupon süresi doldu'];
        if($amount < $coupon['min_amount']) return ['valid'=>false,'error'=>'Minimum tutar: '.$coupon['min_amount'].' ₺'];
        $discount = $coupon['type'] === 'percent' ? $amount * $coupon['value'] / 100 : $coupon['value'];
        if($coupon['max_discount']) $discount = min($discount, $coupon['max_discount']);
        return ['valid'=>true,'discount'=>$discount,'coupon'=>$coupon];
    }
    public static function use($code): bool {
        try { db()->prepare("UPDATE coupons SET used_count=used_count+1 WHERE code=?")->execute([$code]); return true; } catch(Throwable $e) { return false; }
    }
}
