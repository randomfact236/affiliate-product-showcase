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
// Enable with APS_RUN_DB_SEED=1 and ensure the mysqli extension is available.
$runSeeder = getenv('APS_RUN_DB_SEED');
if ($runSeeder === '1' && extension_loaded('mysqli') && file_exists($seeder)) {
    require_once $seeder;
}
