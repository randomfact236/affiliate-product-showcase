<?php
/**
 * Plugin Constants
 *
 * Centralized constants for the Affiliate Product Showcase plugin.
 * All plugin-wide constants should be defined here for easy reference.
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 * @version 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants Class
 *
 * Defines all plugin-wide constants including version, textdomain, prefixes,
 * custom post types, taxonomies, REST namespace, and path/URL helpers.
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 */
final class Constants {
	/**
	 * Plugin Version
	 *
	 * Current plugin version. Update this on each release.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const VERSION = '1.0.0';

	/**
	 * Plugin Text Domain
	 *
	 * Used for internationalization and translation files.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const TEXTDOMAIN = 'affiliate-product-showcase';

	/**
	 * Plugin Prefix
	 *
	 * Used for option names, meta keys, and database tables.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const PREFIX = 'aps_';

	/**
	 * Plugin Slug
	 *
	 * Used for URLs and directory names.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const SLUG = 'affiliate-manager';

	/**
	 * Custom Post Type: Product
	 *
	 * Post type slug for affiliate products.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const CPT_PRODUCT = 'aps_product';

	/**
	 * Taxonomy: Category
	 *
	 * Taxonomy slug for product categories.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const TAX_CATEGORY = 'aps_category';

	/**
	 * Taxonomy: Tag
	 *
	 * Taxonomy slug for product tags.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const TAX_TAG = 'aps_tag';

	/**
	 * Taxonomy: Ribbon
	 *
	 * Taxonomy slug for product ribbons (badges/labels).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const TAX_RIBBON = 'aps_ribbon';

	/**
	 * Option Prefix
	 *
	 * Prefix for WordPress options stored by the plugin.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const OPTION_PREFIX = 'aps_';

	/**
	 * REST API Namespace
	 *
	 * Namespace for plugin REST API endpoints.
	 * Uses plugin slug to prevent collisions with other plugins.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const REST_NAMESPACE = 'affiliate-product-showcase/v1';

	/**
	 * Admin Menu Slug Prefix
	 *
	 * Prefix for all admin menu pages.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = 'affiliate-manager';

	/**
	 * Main Plugin File Path
	 *
	 * Full filesystem path to the main plugin file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const FILE = __DIR__ . '/../../affiliate-product-showcase.php';

	/**
	 * Menu Capability
	 *
	 * Required capability to access admin menu pages.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const MENU_CAP = 'manage_options';

	/**
	 * Nonce Action
	 *
	 * Base nonce action name for form submissions and AJAX requests.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const NONCE_ACTION = 'aps_action';

	/**
	 * Get Plugin Basename
	 *
	 * Returns the plugin basename (e.g., 'affiliate-product-showcase/affiliate-product-showcase.php').
	 * Used for plugin hooks and settings registration.
	 *
	 * @since  1.0.0
	 * @return string The plugin basename.
	 */
	public static function basename(): string {
		return plugin_basename( self::FILE );
	}

	/**
	 * Get Plugin Directory Path
	 *
	 * Returns the filesystem path to the plugin directory with trailing slash.
	 *
	 * @since  1.0.0
	 * @return string The plugin directory path.
	 */
	public static function dirPath(): string {
		return plugin_dir_path( self::FILE );
	}

	/**
	 * Get Plugin Directory URL
	 *
	 * Returns the URL to the plugin directory with trailing slash.
	 *
	 * @since  1.0.0
	 * @return string The plugin directory URL.
	 */
	public static function dirUrl(): string {
		return plugin_dir_url( self::FILE );
	}

	/**
	 * Get Languages Path
	 *
	 * Returns the relative path to the languages directory.
	 *
	 * @since  1.0.0
	 * @return string The languages directory path.
	 */
	public static function languagesPath(): string {
		return dirname( self::basename() ) . '/languages';
	}

	/**
	 * Get Asset URL
	 *
	 * Helper to get a full URL for an asset file.
	 * Concatenates the plugin directory URL with the relative path.
	 *
	 * @since 1.0.0
	 * @param string $relative The relative path to the asset (e.g., 'assets/css/style.css').
	 * @return string The full asset URL.
	 */
	public static function assetUrl( string $relative ): string {
		$plugin_dir = plugin_dir_path( self::FILE );
		$plugin_url = plugin_dir_url( self::FILE );
		
		// Normalize the relative path
		$relative_path = ltrim( $relative, '/\\' );
		
		// Construct the full asset URL
		return $plugin_url . $relative_path;
	}

	/**
	 * Get View Path
	 *
	 * Helper to get a full filesystem path for a template/view file.
	 * Concatenates the plugin directory path with the relative path.
	 *
	 * @since 1.0.0
	 * @param string $relative The relative path to the view (e.g., 'templates/product-card.php').
	 * @return string The full view file path.
	 */
	public static function viewPath( string $relative ): string {
		return self::dirPath() . ltrim( $relative, '/\\' );
	}
}
