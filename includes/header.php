<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$request_script = $_GET['path'] ?? $_SERVER['PHP_SELF'];
$current_page = basename(dirname($request_script));
if ($current_page === 'gotham-crime-records' || basename($request_script) === 'index.php' && $current_page !== 'cases' && $current_page !== 'suspects' && $current_page !== 'investigators') {
    $current_page = 'dashboard';
}
$page_title = $page_title ?? 'Gotham Crime Records';
$base = $base_path ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> &mdash; Gotham Crime Records</title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="gpd-body">
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="main-wrapper">
        <main class="page-container">
