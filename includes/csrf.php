<?php
/**
 * CSRF Protection Helper Functions
 */

require_once __DIR__ . '/helpers.php';

if (!function_exists('csrf_token')) {
    /**
     * Generate or return existing 32-byte hex random CSRF token in session
     */
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Output hidden input tag with current CSRF token
     */
    function csrf_field(): string {
        $token = csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify incoming POST CSRF token using hash_equals
     * Halts with HTTP 403 Forbidden if invalid or missing
     */
    function csrf_verify(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $postToken    = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($postToken) || empty($sessionToken) || !hash_equals($sessionToken, $postToken)) {
            http_response_code(403);
            die('<!DOCTYPE html><html><head><title>403 Forbidden</title><style>body{background:#0B0F14;color:#E8EDF2;font-family:monospace;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}.box{background:#18212B;border:1px solid #D9534F;border-radius:6px;padding:2rem;max-width:480px;text-align:center;}h2{color:#D9534F;margin:0 0 1rem;}p{color:#8FA0B3;line-height:1.6;}</style></head><body><div class="box"><h2>403 Forbidden</h2><p>Invalid or missing CSRF token. Request verification failed.</p></div></body></html>');
        }
    }
}
