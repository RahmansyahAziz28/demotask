<?php
$project_root = dirname(__DIR__);
$requested_path = $_GET['path'] ?? '';

$relative_path = ltrim(parse_url($requested_path, PHP_URL_PATH) ?? '', '/');

if ($relative_path === '' || $relative_path === 'index.php') {
    chdir($project_root);
    require $project_root . '/index.php';
    exit;
}

$allowed_pattern = '~^((cases|suspects|investigators)/[A-Za-z0-9_-]+\.php|(login|proses_login|register|proses_register|logout)\.php)$~';

if (!preg_match($allowed_pattern, $relative_path)) {
    http_response_code(404);
    exit('Page not found');
}

$page_path = realpath($project_root . '/' . $relative_path);
$root_prefix = $project_root . DIRECTORY_SEPARATOR;

if ($page_path === false || !str_starts_with($page_path, $root_prefix) || !is_file($page_path)) {
    http_response_code(404);
    exit('Page not found');
}

chdir(dirname($page_path));
require $page_path;
exit;
