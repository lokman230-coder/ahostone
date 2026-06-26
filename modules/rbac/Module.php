<?php
/**
 * RBAC Module - Role Based Access Control
 * Kullanıcı rolleri ve yetkilendirme
 */
class AhostModule_rbac {
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';
    const ROLE_RESELLER = 'reseller';
    const ROLE_CUSTOMER = 'customer';
    
    public static function install(PDO $db): bool {
        $db->exec("
            CREATE TABLE IF NOT EXISTS rbac_roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                description VARCHAR(200),
                permissions JSON,
                is_system TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS rbac_user_roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                role_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_role (user_id, role_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS rbac_permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                description VARCHAR(200),
                category VARCHAR(50)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Insert default roles
        self::seedRoles($db);
        return true;
    }
    
    private static function seedRoles(PDO $db): void {
        $roles = [
            ['admin', 'Yönetici', '{"all":true}', 1],
            ['staff', 'Personel', '{"view":true,"edit":true,"orders":true}', 1],
            ['reseller', 'Bayi', '{"view":true,"orders":true,"customers":true}', 1],
            ['customer', 'Müşteri', '{"view":true,"own_orders":true}', 1],
        ];
        
        foreach($roles as $r) {
            try {
                $db->prepare("INSERT IGNORE INTO rbac_roles (name, description, permissions, is_system) VALUES (?,?,?,?)")
                    ->execute($r);
            } catch(Throwable $e) {}
        }
    }
    
    public static function hasPermission(int $userId, string $permission): bool {
        $user = db()->query("SELECT is_admin FROM users WHERE id=$userId")->fetch();
        if($user['is_admin']) return true;
        
        $roles = db()->prepare("SELECT r.permissions FROM rbac_user_roles ur JOIN rbac_roles r ON r.id=ur.role_id WHERE ur.user_id=?")
            ->execute([$userId])->fetchAll();
        
        foreach($roles as $role) {
            $perms = json_decode($role['permissions'], true) ?: [];
            if(!empty($perms['all']) || !empty($perms[$permission])) {
                return true;
            }
        }
        return false;
    }
    
    public static function assignRole(int $userId, string $roleName): bool {
        try {
            $role = db()->prepare("SELECT id FROM rbac_roles WHERE name=?")->execute([$roleName])->fetch();
            if($role) {
                db()->prepare("INSERT IGNORE INTO rbac_user_roles (user_id, role_id) VALUES (?,?)")
                    ->execute([$userId, $role['id']]);
                return true;
            }
        } catch(Throwable $e) {}
        return false;
    }
    
    public static function getUserRoles(int $userId): array {
        return db()->prepare("SELECT r.* FROM rbac_roles r JOIN rbac_user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?")
            ->execute([$userId])->fetchAll() ?: [];
    }
}
