<?php
// Basic bootstrap for lightweight PHPUnit tests in this repo.
// For full WP integration tests, install WP test suite separately.

require_once __DIR__ . '/../vendor/autoload.php';

// Simple helper: define plugin path if needed
if (!defined('APS_PLUGIN_DIR')) {
    define('APS_PLUGIN_DIR', __DIR__ . '/../');
}

// If a DB seeder exists, run it so tests have the expected fixtures.
$seeder = __DIR__ . '/db-seed.php';
// This is intentionally opt-in so CI/local runs don't require MySQL.
// Enable with APS_RUN_DB_SEED=1. The seeder is defensive and will create a
// lightweight marker when MySQL isn't available so tests can proceed.
$runSeeder = getenv('APS_RUN_DB_SEED');
if ($runSeeder === '1' && file_exists($seeder)) {
    require_once $seeder;
}

// Minimal WP_Error polyfill for unit tests outside WordPress.
if (! class_exists('WP_Error')) {
    class WP_Error {
        private $code;
        private $message;
        private $data;

        public function __construct($code = '', $message = '', $data = []) {
            $this->code = $code;
            $this->message = $message;
            $this->data = $data;
        }

        public function get_error_code() { return $this->code; }
        public function get_error_message() { return $this->message; }
        public function get_error_data() { return $this->data; }
    }

    function is_wp_error($thing) { return $thing instanceof WP_Error; }
}

// Minimal translation function used by plugin code
if (! function_exists('__')) {
    function __($text, $domain = '') {
        return $text;
    }
}

// Minimal transient functions used by SRI class
if (! function_exists('set_transient')) {
    $GLOBALS['aps_transients'] = $GLOBALS['aps_transients'] ?? [];
    function set_transient($key, $value, $ttl = 0) {
        $GLOBALS['aps_transients'][$key] = $value;
        return true;
    }
}

if (! function_exists('get_transient')) {
    function get_transient($key) {
        return $GLOBALS['aps_transients'][$key] ?? false;
    }
}
