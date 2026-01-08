<?php
/**
 * Plugin Name: Affiliate Product Showcase
 * Plugin URI:  https://example.com/
 * Description: Display affiliate products using blocks, shortcodes, and widgets.
 * Version:     0.1.0
 * Author:      Your Name
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: affiliate-product-showcase
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'APS_VERSION', '0.1.0' );
define( 'APS_PLUGIN_FILE', __FILE__ );
define( 'APS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'APS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'APS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Prefer Composer autoload when present.
if ( file_exists( APS_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once APS_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	// Minimal PSR-4 autoloader fallback for development without Composer.
	spl_autoload_register(
		static function ( $class ) {
			$prefix = 'AffiliateProductShowcase\\';
			$base_dir = APS_PLUGIN_DIR . 'includes/';

			if ( 0 !== strpos( $class, $prefix ) ) {
				return;
			}

			$relative_class = substr( $class, strlen( $prefix ) );
			$relative_path  = str_replace( '\\', '/', $relative_class );
			$file           = $base_dir . $relative_path . '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

require_once APS_PLUGIN_DIR . 'includes/helpers/functions.php';

register_activation_hook( __FILE__, array( 'AffiliateProductShowcase\\Plugin\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AffiliateProductShowcase\\Plugin\\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'AffiliateProductShowcase\\Plugin\\Plugin' ) ) {
			return;
		}

		$plugin = new AffiliateProductShowcase\Plugin\Plugin();
		$plugin->run();
	}
);
