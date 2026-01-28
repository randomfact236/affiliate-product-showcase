<?php
/**
 * Plugin Name:       Affiliate Product Showcase
 * Plugin URI:        https://example.com/affiliate-product-showcase
 * Description:       Display affiliate products with shortcodes and blocks. Built with modern standards for security, performance, and scalability.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Author:            Affiliate Product Showcase Team
 * Author URI:        https://example.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       affiliate-product-showcase
 * Domain Path:       /languages
 * Update URI:        https://example.com/updates/affiliate-product-showcase
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 */

declare(strict_types=1);

// ==============================================================================
// CRITICAL: PHP Version Check BEFORE declare(strict_types=1)
// Prevents parse errors on PHP < 7.0 where strict_types is not supported.
// ==============================================================================

if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
	if ( defined( 'WP_ADMIN' ) && WP_ADMIN ) {
		add_action(
			'admin_notices',
			static function (): void {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					wp_kses_post(
						sprintf(
							/* translators: 1: Plugin name, 2: Current PHP version, 3: Required PHP version */
							__( '<strong>%1$s</strong> requires PHP %3$s or higher. Your site is running PHP %2$s. Please upgrade PHP or deactivate the plugin.', 'affiliate-product-showcase' ),
							'Affiliate Product Showcase',
							PHP_VERSION,
							'8.1'
						)
					)
				);
			}
		);
	}
	return;
	}


// ==============================================================================
// Security: Exit if accessed directly
// ==============================================================================

if ( ! defined( 'ABSPATH' ) ) {
	http_response_code( 403 );
	exit;
}

// ==============================================================================
// Plugin Constants (Fail Fast - No defensive checks)
// If these conflict, it's a plugin naming collision that SHOULD error.
// ==============================================================================

define( 'AFFILIATE_PRODUCT_SHOWCASE_VERSION', '1.0.0' );
define( 'AFFILIATE_PRODUCT_SHOWCASE_FILE', __FILE__ );
define( 'AFFILIATE_PRODUCT_SHOWCASE_DIR', __DIR__ );
define( 'AFFILIATE_PRODUCT_SHOWCASE_BASENAME', plugin_basename( __FILE__ ) );
define( 'AFFILIATE_PRODUCT_SHOWCASE_URL', plugin_dir_url( __FILE__ ) );
define( 'AFFILIATE_PRODUCT_SHOWCASE_PATH', plugin_dir_path( __FILE__ ) );

// ==============================================================================
// Error Handling Utilities
// ==============================================================================

/**
 * Display a single admin error notice and prevent duplicates using static scope.
 * (Avoids global variables).
 *
 * @since 1.0.0
 * @param string $message Error message to display.
 * @return void
 */
function affiliate_product_showcase_show_error( string $message ): void {
	static $error_shown = false;

	if ( $error_shown ) {
		return; // Prevent duplicate notices.
	}

	add_action(
		'admin_notices',
		static function () use ( $message ): void {
			printf(
				'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
				wp_kses_post( $message )
			);
		}
	);

	$error_shown = true;
}

/**
 * Log errors with context and stack traces in debug mode.
 *
 * @since 1.0.0
 * @param string              $message Error message.
 * @param Throwable|null      $exception Optional exception object.
 * @param array<string,mixed> $context Additional context data.
 * @return void
 */
function affiliate_product_showcase_log_error( string $message, ?Throwable $exception = null, array $context = [] ): void {
	$log_entry = sprintf( '[Affiliate Product Showcase] %s', $message );

	if ( $exception ) {
		$log_entry .= sprintf(
			' | Exception: %s in %s:%d',
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine()
		);

		// Include stack trace in debug mode.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$log_entry .= "\nStack trace:\n" . $exception->getTraceAsString();
		}
	}

	if ( ! empty( $context ) ) {
		$log_entry .= ' | Context: ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES );
	}

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( $log_entry );

	// Hook for external logging services (Sentry, Bugsnag, etc.).
	do_action( 'affiliate_product_showcase_log_error', $message, $exception, $context );
}

// ==============================================================================
// Composer Autoloader Check
// ==============================================================================

$autoload = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'vendor/autoload.php';

if ( ! is_readable( $autoload ) ) {
	if ( is_admin() ) {
		affiliate_product_showcase_show_error(
			sprintf(
				/* translators: 1: Plugin name, 2: Command to run */
				__( '<strong>%1$s</strong> installation is incomplete. Please run %2$s in the plugin directory.', 'affiliate-product-showcase' ),
				'Affiliate Product Showcase',
				'<code>composer install --no-dev --optimize-autoloader</code>'
			)
		);
	}

	affiliate_product_showcase_log_error(
		'Autoloader not found',
		null,
		[ 'expected_path' => $autoload ]
	);

	return;
}

require_once $autoload;

// Verify critical internal classes exist (detects autoloader misconfiguration).
if ( ! class_exists( 'AffiliateProductShowcase\\Plugin\\Plugin' ) ) {
	if ( is_admin() ) {
		affiliate_product_showcase_show_error(
			sprintf(
				/* translators: %s: Plugin name */
				__( '<strong>%s</strong> core class not found. Autoloader may be misconfigured. Try running %s again.', 'affiliate-product-showcase' ),
				'Affiliate Product Showcase',
				'<code>composer dump-autoload -o</code>'
			)
		);
	}

	affiliate_product_showcase_log_error( 'Core class AffiliateProductShowcase\\Plugin\\Plugin not found after autoload' );
	return;
}

// ==============================================================================
// Activation/Deactivation Hooks
// ==============================================================================

if ( class_exists( 'AffiliateProductShowcase\\Plugin\\Activator' ) ) {
	register_activation_hook(
		AFFILIATE_PRODUCT_SHOWCASE_FILE,
		[ 'AffiliateProductShowcase\\Plugin\\Activator', 'activate' ]
	);
}

if ( class_exists( 'AffiliateProductShowcase\\Plugin\\Deactivator' ) ) {
	register_deactivation_hook(
		AFFILIATE_PRODUCT_SHOWCASE_FILE,
		[ 'AffiliateProductShowcase\\Plugin\\Deactivator', 'deactivate' ]
	);
}

// Tag taxonomy activation/deactivation hooks
if ( class_exists( 'AffiliateProductShowcase\\TagActivator' ) ) {
	register_activation_hook(
		AFFILIATE_PRODUCT_SHOWCASE_FILE,
		[ 'AffiliateProductShowcase\\TagActivator', 'activate' ]
	);
}

if ( class_exists( 'AffiliateProductShowcase\\TagActivator' ) ) {
	register_deactivation_hook(
		AFFILIATE_PRODUCT_SHOWCASE_FILE,
		[ 'AffiliateProductShowcase\\TagActivator', 'deactivate' ]
	);
}

// Ribbon taxonomy activation/deactivation hooks
if ( class_exists( 'AffiliateProductShowcase\\RibbonActivator' ) ) {
	register_activation_hook(
		AFFILIATE_PRODUCT_SHOWCASE_FILE,
		[ 'AffiliateProductShowcase\\RibbonActivator', 'activate' ]
	);
}

if ( class_exists( 'AffiliateProductShowcase\\RibbonActivator' ) ) {
	register_deactivation_hook(
		AFFILIATE_PRODUCT_SHOWCASE_FILE,
		[ 'AffiliateProductShowcase\\RibbonActivator', 'deactivate' ]
	);
}

// ==============================================================================
// Plugin Initialization
// ==============================================================================

add_action( 'plugins_loaded', 'affiliate_product_showcase_init', 10 );

// Load textdomain at init hook (after plugins_loaded)
add_action( 'init', 'affiliate_product_showcase_load_textdomain', 10 );

/**
 * Initialize plugin after WordPress is fully loaded.
 *
 * Uses singleton pattern and graceful error handling to prevent site breakage.
 *
 * @since 1.0.0
 * @return void
 */
function affiliate_product_showcase_init(): void {
	try {
		// Get singleton instance.
		$plugin = \AffiliateProductShowcase\Plugin\Plugin::instance();

		// Initialize plugin hooks and services.
		$plugin->init();

		// Hook for extensions and integrations.
		do_action( 'affiliate_product_showcase_loaded', $plugin );

		// Performance monitoring in debug mode.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			add_action(
				'shutdown',
				static function (): void {
					$peak_memory = size_format( memory_get_peak_usage( true ) );
					$query_count = isset( $GLOBALS['wpdb']->queries ) && is_array( $GLOBALS['wpdb']->queries )
						? count( $GLOBALS['wpdb']->queries )
						: 0;

					affiliate_product_showcase_log_error(
						'Performance metrics',
						null,
						[
							'peak_memory' => $peak_memory,
							'db_queries'  => $query_count,
						]
					);
				},
				PHP_INT_MAX
			);
		}
	} catch ( Throwable $e ) {
		// Log detailed error information.
		affiliate_product_showcase_log_error( 'Plugin initialization failed', $e );

		// Show admin notice only to users who can fix the issue.
		if ( is_admin() && ! wp_doing_ajax() && current_user_can( 'activate_plugins' ) ) {
			affiliate_product_showcase_show_error(
				sprintf(
					/* translators: 1: Plugin name, 2: Error message */
					__( '<strong>%1$s</strong> failed to initialize: %2$s', 'affiliate-product-showcase' ),
					'Affiliate Product Showcase',
					esc_html( $e->getMessage() )
				)
			);
		}
	}
}

/**
 * Load textdomain at proper time
 *
 * Loads translations on init hook to avoid "_load_textdomain_just_in_time" warning.
 *
 * @since 1.0.0
 * @return void
 */
function affiliate_product_showcase_load_textdomain(): void {
	load_plugin_textdomain(
		'affiliate-product-showcase',
		false,
		dirname( AFFILIATE_PRODUCT_SHOWCASE_BASENAME ) . '/languages'
	);
}

// ==============================================================================
// Version Migration Check (Admin Only)
// ==============================================================================

if ( is_admin() && ! wp_doing_ajax() ) {
	add_action(
		'admin_init',
		static function (): void {
			$installed_version = get_option( 'affiliate_product_showcase_version', '0.0.0' );

			if ( version_compare( $installed_version, AFFILIATE_PRODUCT_SHOWCASE_VERSION, '<' ) ) {
				// Trigger migration hook for version-specific upgrades.
				do_action(
					'affiliate_product_showcase_upgrade',
					$installed_version,
					AFFILIATE_PRODUCT_SHOWCASE_VERSION
				);

				// Update stored version.
				update_option(
					'affiliate_product_showcase_version',
					AFFILIATE_PRODUCT_SHOWCASE_VERSION,
					false
				);

				affiliate_product_showcase_log_error(
					'Plugin upgraded',
					null,
					[
						'from' => $installed_version,
						'to'   => AFFILIATE_PRODUCT_SHOWCASE_VERSION,
					]
				);
			}
		}
	);
}
