<?php
$rootInstall = dirname(__DIR__) . '/install.php';
if (file_exists($rootInstall)) {
    require $rootInstall;
    exit;
}
?><!doctype html><html lang="tr"><head><meta charset="utf-8"><title>Ahost One Install</title></head><body><h1>Kurulum dosyası bulunamadı</h1><p>Güvenlik için install.php kaldırılmış olabilir.</p></body></html>
