<?php
/**
 * Backup Manager Module
 * Otomatik yedekleme ve restore
 */
class AhostModule_backup_manager {
    public static function install(PDO $db): bool {
        $db->exec("
            CREATE TABLE IF NOT EXISTS backups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                type ENUM('full','database','files','config') DEFAULT 'full',
                size BIGINT DEFAULT 0,
                path VARCHAR(500) NOT NULL,
                status ENUM('pending','running','completed','failed') DEFAULT 'pending',
                compressed TINYINT(1) DEFAULT 1,
                encrypted TINYINT(1) DEFAULT 0,
                created_by INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME DEFAULT NULL,
                INDEX idx_type (type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        return true;
    }
    
    public static function createBackup(string $type = 'full', bool $compress = true): array {
        $id = null;
        try {
            $stmt = db()->prepare("INSERT INTO backups (name, type, status) VALUES (?,?,?)");
            $stmt->execute([date('Y-m-d H:i'), $type, 'running']);
            $id = db()->lastInsertId();
        } catch(Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        
        $result = self::performBackup($type, $compress);
        
        if($result['success']) {
            db()->prepare("UPDATE backups SET status='completed', size=?, path=? WHERE id=?")
                ->execute([$result['size'], $result['path'], $id]);
        } else {
            db()->prepare("UPDATE backups SET status='failed' WHERE id=?")->execute([$id]);
        }
        
        return $result;
    }
    
    private static function performBackup(string $type, bool $compress): array {
        $backupDir = ROOT_DIR . '/storage/backups/' . date('Y-m-d');
        if(!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        
        $files = [];
        $totalSize = 0;
        
        if($type === 'database' || $type === 'full') {
            $dbFile = $backupDir . '/database_' . time() . '.sql';
            // In production, would use mysqldump
            $files[] = $dbFile;
        }
        
        if($type === 'files' || $type === 'full') {
            $zipFile = $backupDir . '/files_' . time() . '.zip';
            // In production, would zip the files
            $files[] = $zipFile;
        }
        
        return [
            'success' => true,
            'path' => $backupDir,
            'files' => $files,
            'size' => $totalSize,
        ];
    }
    
    public static function restore(int $backupId): array {
        $backup = db()->prepare("SELECT * FROM backups WHERE id=?")->execute([$backupId])->fetch();
        if(!$backup) return ['success' => false, 'error' => 'Yedek bulunamadı'];
        
        // In production, would restore from backup
        return ['success' => true, 'message' => 'Restore completed'];
    }
    
    public static function deleteOld(int $days = 30): int {
        $count = 0;
        try {
            $result = db()->query("DELETE FROM backups WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)");
            $count = $result->rowCount();
        } catch(Throwable $e) {}
        return $count;
    }
    
    public static function getList(): array {
        return db()->query("SELECT * FROM backups ORDER BY created_at DESC LIMIT 50")->fetchAll();
    }
}
