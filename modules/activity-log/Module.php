<?php
/**
 * Activity Log Module
 * Tüm işlemlerin loglanması
 */
class AhostModule_activity_log {
    public static function install(PDO $db): bool {
        $db->exec("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                user_type ENUM('admin','staff','customer','api') DEFAULT 'admin',
                action VARCHAR(100) NOT NULL,
                entity_type VARCHAR(50),
                entity_id INT DEFAULT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent VARCHAR(500),
                old_data JSON,
                new_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_action (action),
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        return true;
    }
    
    public static function log(string $action, ?int $userId = null, ?string $description = null, ?array $entity = null, ?array $oldData = null, ?array $newData = null): void {
        try {
            db()->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, old_data, new_data) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([
                    $userId,
                    $action,
                    $entity['type'] ?? null,
                    $entity['id'] ?? null,
                    $description,
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $oldData ? json_encode($oldData) : null,
                    $newData ? json_encode($newData) : null,
                ]);
        } catch(Throwable $e) {}
    }
    
    public static function getRecent(int $limit = 50): array {
        return db()->query("SELECT l.*, u.name as user_name 
            FROM activity_logs l 
            LEFT JOIN users u ON u.id=l.user_id 
            ORDER BY l.created_at DESC LIMIT $limit")->fetchAll();
    }
    
    public static function getByEntity(string $type, int $id): array {
        return db()->prepare("SELECT * FROM activity_logs WHERE entity_type=? AND entity_id=? ORDER BY created_at DESC")
            ->execute([$type, $id])->fetchAll();
    }
}
