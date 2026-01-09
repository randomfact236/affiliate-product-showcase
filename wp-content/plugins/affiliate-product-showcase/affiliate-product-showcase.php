<?php
/**
 * Plugin Name:       Affiliate Product Showcase
 * Plugin URI:        https://example.com/
 * Description:       Display affiliate products with shortcodes and blocks.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Affiliate Product Showcase
 * Author URI:        https://example.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       affiliate-product-showcase
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'APS_PLUGIN_VERSION' ) ) {
	define( 'APS_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'APS_TEXTDOMAIN' ) ) {
	define( 'APS_TEXTDOMAIN', 'affiliate-product-showcase' );
}

if ( ! defined( 'APS_PLUGIN_FILE' ) ) {
	define( 'APS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'APS_PLUGIN_BASENAME' ) ) {
	define( 'APS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'APS_PLUGIN_DIR' ) ) {
	define( 'APS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'APS_PLUGIN_URL' ) ) {
	define( 'APS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! function_exists( 'aps_load_textdomain' ) ) {
	function aps_load_textdomain() {
		load_plugin_textdomain(
			APS_TEXTDOMAIN,
			false,
			dirname( APS_PLUGIN_BASENAME ) . '/languages'
		);
	}
}
add_action( 'plugins_loaded', 'aps_load_textdomain' );

if ( ! function_exists( 'aps_activate' ) ) {
	function aps_activate() {
		update_option( 'aps_plugin_version', APS_PLUGIN_VERSION );
		flush_rewrite_rules();
	}
}
register_activation_hook( __FILE__, 'aps_activate' );

if ( ! function_exists( 'aps_deactivate' ) ) {
	function aps_deactivate() {
		flush_rewrite_rules();
	}
}
register_deactivation_hook( __FILE__, 'aps_deactivate' );

if ( ! function_exists( 'aps_bootstrap' ) ) {
	function aps_bootstrap() {
		$autoload = APS_PLUGIN_DIR . 'vendor/autoload.php';
		if ( file_exists( $autoload ) ) {
			require_once $autoload;
		}

		$main_class = '\\AffiliateProductShowcase\\Plugin\\Plugin';
		if ( class_exists( $main_class ) && is_callable( array( $main_class, 'instance' ) ) ) {
			$plugin = call_user_func( array( $main_class, 'instance' ) );
			if ( is_object( $plugin ) && method_exists( $plugin, 'init' ) ) {
				$plugin->init();
			}
		}
	}
}
add_action( 'plugins_loaded', 'aps_bootstrap', 20 );
