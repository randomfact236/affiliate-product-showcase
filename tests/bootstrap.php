<?php
/**
 * PHPUnit Bootstrap
 * 
 * This file is loaded by PHPUnit before running any tests.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Define test constants
define('TESTS_DIR', __DIR__);
define('PLUGIN_DIR', __DIR__ . '/..');

// Mock WordPress functions if not available
if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = []) {
        throw new \Exception($message);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}
