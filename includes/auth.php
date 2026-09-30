<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$redirect_base = isset($base_path) ? $base_path : '';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $redirect_base . "login.php", true, 302);
    exit;
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('currentUser')) {
    function currentUser(): ?array {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id'           => $_SESSION['user_id'],
            'full_name'    => $_SESSION['user_name'] ?? 'Investigator',
            'badge_number' => $_SESSION['badge_number'] ?? '',
            'rank'         => $_SESSION['rank'] ?? '',
            'department'   => $_SESSION['department'] ?? '',
            'email'        => $_SESSION['email'] ?? '',
        ];
    }
}
