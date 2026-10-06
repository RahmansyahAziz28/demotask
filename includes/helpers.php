<?php
/**
 * Helper functions for Gotham Crime Records
 */

if (!function_exists('e')) {
    /**
     * Escape HTML output for XSS protection
     *
     * @param mixed $value
     * @return string
     */
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
