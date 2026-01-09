<?php
/**
 * Sample configuration for the WordPress PHPUnit test suite.
 *
 * Copy this file to `wp-tests-config.php` and adjust values for your local setup.
 *
 * This repository includes multiple ways to run WordPress locally (for example via Docker).
 * The required database name/user/password/host depend on your environment.
 */

// Database settings for the WordPress tests database.
define('DB_NAME', 'wordpress_test');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

// Authentication unique keys and salts.
// You can use any random strings for local testing.
define('AUTH_KEY', 'put your unique phrase here');
define('SECURE_AUTH_KEY', 'put your unique phrase here');
define('LOGGED_IN_KEY', 'put your unique phrase here');
define('NONCE_KEY', 'put your unique phrase here');
define('AUTH_SALT', 'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT', 'put your unique phrase here');
define('NONCE_SALT', 'put your unique phrase here');

// Path to the WordPress tests library.
// Common values are a checkout of `wordpress-develop/tests/phpunit` or a location
// provided by a tooling script.
if (!defined('ABSPATH')) {
	define('ABSPATH', dirname(__FILE__) . '/');
}

// Example: define('WP_TESTS_DIR', 'C:/path/to/wordpress-develop/tests/phpunit');
define('WP_TESTS_DIR', getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib');

// WordPress debug settings for tests.
define('WP_DEBUG', true);
