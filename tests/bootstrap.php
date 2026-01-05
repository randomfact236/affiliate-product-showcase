<?php
// Basic bootstrap for lightweight PHPUnit tests in this repo.
// For full WP integration tests, install WP test suite separately.

require_once __DIR__ . '/../vendor/autoload.php';

// Simple helper: define plugin path if needed
if (!defined('APS_PLUGIN_DIR')) {
    define('APS_PLUGIN_DIR', __DIR__ . '/../');
}
