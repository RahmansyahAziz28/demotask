<?php
$request_script = $_GET['path'] ?? $_SERVER['PHP_SELF'];
$current_page = basename(dirname($request_script));
if ($current_page === 'gotham-crime-records' || basename($request_script) === 'index.php' && $current_page !== 'cases' && $current_page !== 'suspects' && $current_page !== 'investigators') {
    $current_page = 'dashboard';
}
$page_title = $page_title ?? 'Gotham Crime Records';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Gotham Crime Records</title>
    <link rel="stylesheet" href="<?= $base_path ?? '' ?>assets/css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">&#9776;</button>
                <div class="breadcrumb"><?= $page_title ?></div>
            </div>
            <div class="topbar-right">
                <span class="badge-status badge-open">GPD System Online</span>
            </div>
        </header>
        <div class="content">
