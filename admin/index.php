<?php
// Ahost One admin physical entrypoint.
// Keeps /admin and /admin/* working even when a hosting panel expects a real admin folder.
$uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH), '/');
$pos = strpos($uri, 'admin');
$route = 'admin';
if ($pos !== false) {
    $tail = trim(substr($uri, $pos + strlen('admin')), '/');
    if ($tail !== '') { $route = 'admin/' . $tail; }
}
if (!empty($_GET['ao_route'])) { $route = trim((string)$_GET['ao_route'], '/'); }
putenv('AH_CUSTOM_ROUTES=1');
$_GET['ao_route'] = $route;
require dirname(__DIR__) . '/index.php';
