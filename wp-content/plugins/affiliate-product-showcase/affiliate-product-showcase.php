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

declare( strict_types=1 );

use AffiliateProductShowcase\Plugin\Activator;
use AffiliateProductShowcase\Plugin\Deactivator;
use AffiliateProductShowcase\Plugin\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
}

register_activation_hook( __FILE__, static function (): void {
	Activator::activate();
} );

register_deactivation_hook( __FILE__, static function (): void {
	Deactivator::deactivate();
} );

add_action( 'plugins_loaded', static function (): void {
	Plugin::instance()->init();
}, 20 );
