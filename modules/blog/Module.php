<?php
/**
 * Ahost One Blog System Module
 * SEO-friendly blog with categories, tags, comments
 */
class AhostModule_blog {
    public static function manifest(): array {
        return json_decode(file_get_contents(__DIR__ . '/module.json'), true) ?: [];
    }
    
    public static function install(PDO $db): bool {
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $db->exec($sql);
        return true;
    }
    
    public static function uninstall(PDO $db): bool {
        $db->exec("DROP TABLE IF EXISTS blog_post_tags");
        $db->exec("DROP TABLE IF EXISTS blog_comments");
        $db->exec("DROP TABLE IF EXISTS blog_tags");
        $db->exec("DROP TABLE IF EXISTS blog_categories");
        $db->exec("DROP TABLE IF EXISTS blog_posts");
        $db->exec("DROP TABLE IF EXISTS blog_settings");
        return true;
    }
    
    public static function enable(PDO $db): bool { return true; }
    public static function disable(PDO $db): bool { return true; }
}
