<?php
/**
 * Paths Helper
 *
 * Canonical wrapper helpers for WordPress path/URL functions.
 * Provides consistent asset URLs/paths, multisite/subdirectory/custom wp-content support,
 * and prevents hardcoded paths/domains.
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 * @version 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Helpers;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Paths Helper Class
 *
 * Provides static methods for getting plugin paths and URLs in a consistent way.
 * All methods use WordPress native functions to ensure compatibility with
 * multisite, subdirectory installations, and custom wp-content locations.
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 */
class Paths {
	/**
	 * Get the plugin base path
	 *
	 * Returns the filesystem path to the plugin directory.
	 * Uses plugin_dir_path() for WordPress compatibility.
	 *
	 * @since  1.0.0
	 * @return string The plugin base path with trailing slash.
	 */
	public static function plugin_path(): string {
		return plugin_dir_path( AFFILIATE_PRODUCT_SHOWCASE_FILE );
	}

	/**
	 * Get a specific file/directory path relative to plugin
	 *
	 * Concatenates a relative path to the plugin base path.
	 *
	 * @since  1.0.0
	 * @param  string $relative Path relative to plugin directory.
	 * @return string Full filesystem path.
	 */
	public static function plugin_file_path( string $relative ): string {
		return self::plugin_path() . ltrim( $relative, '/\\' );
	}

	/**
	 * Get the plugin base URL
	 *
	 * Returns the URL to the plugin directory.
	 * Uses plugins_url() for WordPress compatibility.
	 *
	 * @since  1.0.0
	 * @return string The plugin base URL with trailing slash.
	 */
	public static function plugin_url(): string {
		return plugins_url( '/', AFFILIATE_PRODUCT_SHOWCASE_FILE );
	}

	/**
	 * Get a specific file URL relative to plugin
	 *
	 * Concatenates a relative path to the plugin base URL.
	 *
	 * @since  1.0.0
	 * @param  string $relative Path relative to plugin directory.
	 * @return string Full URL.
	 */
	public static function plugin_file_url( string $relative ): string {
		return self::plugin_url() . ltrim( $relative, '/\\' );
	}

	/**
	 * Get the assets URL
	 *
	 * Returns the URL to the assets directory.
	 * Typically assets are stored in the plugin's 'assets' or 'dist' folder.
	 *
	 * @since  1.0.0
	 * @return string The assets URL with trailing slash.
	 */
	public static function assets_url(): string {
		return self::plugin_file_url( 'assets/' );
	}

	/**
	 * Get a specific asset file URL
	 *
	 * Used for enqueuing CSS, JS, images, and other static assets.
	 *
	 * @since  1.0.0
	 * @param  string $relative Path relative to assets directory.
	 * @return string Full asset URL.
	 */
	public static function asset_file_url( string $relative ): string {
		return self::assets_url() . ltrim( $relative, '/\\' );
	}

	/**
	 * Get the compiled assets URL (dist/ folder)
	 *
	 * Returns the URL to the compiled/production assets directory.
	 * Used for enqueuing built CSS/JS files.
	 *
	 * @since  1.0.0
	 * @return string The dist assets URL with trailing slash.
	 */
	public static function dist_url(): string {
		return self::plugin_file_url( 'assets/dist/' );
	}

	/**
	 * Get a specific compiled asset file URL
	 *
	 * Used for enqueuing compiled CSS, JS, and other production assets.
	 *
	 * @since  1.0.0
	 * @param  string $filename The asset filename (e.g., 'frontend.abc123.css').
	 * @return string Full asset URL.
	 */
	public static function dist_file_url( string $filename ): string {
		return self::dist_url() . ltrim( $filename, '/\\' );
	}

	/**
	 * Get the images URL
	 *
	 * Returns the URL to the images directory.
	 *
	 * @since  1.0.0
	 * @return string The images URL with trailing slash.
	 */
	public static function images_url(): string {
		return self::plugin_file_url( 'assets/images/' );
	}

	/**
	 * Get a specific image file URL
	 *
	 * @since  1.0.0
	 * @param  string $filename The image filename.
	 * @return string Full image URL.
	 */
	public static function image_file_url( string $filename ): string {
		return self::images_url() . ltrim( $filename, '/\\' );
	}

	/**
	 * Get the views/templates path
	 *
	 * Returns the filesystem path to the views/templates directory.
	 * Used for including PHP template files.
	 *
	 * @since  1.0.0
	 * @return string The views path with trailing slash.
	 */
	public static function views_path(): string {
		return self::plugin_file_path( 'templates/' );
	}

	/**
	 * Get a specific view/template file path
	 *
	 * @since  1.0.0
	 * @param  string $template The template filename.
	 * @return string Full template file path.
	 */
	public static function view_file_path( string $template ): string {
		return self::views_path() . ltrim( $template, '/\\' );
	}

	/**
	 * Get the REST API namespace base URL
	 *
	 * Returns the base URL for the plugin's REST API endpoints.
	 *
	 * @since  1.0.0
	 * @return string The REST API base URL.
	 */
	public static function rest_url(): string {
		return rest_url( Constants::REST_NAMESPACE . '/' );
	}

	/**
	 * Get a specific REST API endpoint URL
	 *
	 * @since  1.0.0
	 * @param  string $endpoint The endpoint path (e.g., 'products').
	 * @return string Full REST API endpoint URL.
	 */
	public static function rest_endpoint_url( string $endpoint ): string {
		return rest_url( trailingslashit( Constants::REST_NAMESPACE ) . ltrim( $endpoint, '/\\' ) );
	}

	/**
	 * Get the admin URL for a specific plugin page
	 *
	 * @since  1.0.0
	 * @param  string $page    The plugin admin page slug.
	 * @param  array  $args    Optional query arguments.
	 * @return string Full admin URL.
	 */
	public static function admin_url( string $page, array $args = [] ): string {
		$base_url = admin_url( 'admin.php' );
		$url       = add_query_arg(
			[
				'page' => Constants::ADMIN_MENU_SLUG . '-' . $page,
			],
			$base_url
		);

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * Get the site home URL
	 *
	 * Wrapper for get_home_url() for consistency.
	 *
	 * @since  1.0.0
	 * @param  string|null $blog_id Optional blog ID for multisite.
	 * @param  string      $path   Optional path.
	 * @return string The home URL.
	 */
	public static function home_url( ?string $blog_id = null, string $path = '' ): string {
		return get_home_url( $blog_id, $path );
	}

	/**
	 * Get uploads directory information
	 *
	 * Returns array with paths and URLs for uploads directory.
	 * Compatible with custom upload directories.
	 *
	 * @since  1.0.0
	 * @param  string|null $sub Optional subdirectory within uploads.
	 * @return array Array with 'path', 'url', 'subdir', 'basedir', 'baseurl', 'error'.
	 */
	public static function uploads_dir( ?string $sub = null ): array {
		$upload_dir = wp_upload_dir( null, false );

		if ( ! empty( $sub ) ) {
			$upload_dir['path']   = $upload_dir['path'] . '/' . ltrim( $sub, '/\\' );
			$upload_dir['url']    = $upload_dir['url'] . '/' . ltrim( $sub, '/\\' );
			$upload_dir['subdir'] = $upload_dir['subdir'] . '/' . ltrim( $sub, '/\\' );
		}

		return $upload_dir;
	}

	/**
	 * Get the plugin basename
	 *
	 * Returns the plugin basename (e.g., 'affiliate-product-showcase/affiliate-product-showcase.php').
	 * Used for plugin hooks and settings.
	 *
	 * @since  1.0.0
	 * @return string The plugin basename.
	 */
	public static function plugin_basename(): string {
		return plugin_basename( AFFILIATE_PRODUCT_SHOWCASE_FILE );
	}

	/**
	 * Get a versioned asset URL
	 *
	 * Adds the plugin version as a query parameter for cache busting.
	 * Useful when not using file hash-based versioning.
	 *
	 * @since  1.0.0
	 * @param  string $url The asset URL.
	 * @return string The versioned asset URL.
	 */
	public static function versioned_url( string $url ): string {
		return add_query_arg( 'ver', Constants::VERSION, $url );
	}

	/**
	 * Verify if a URL is local to this site
	 *
	 * Checks if a URL belongs to the current site.
	 * Useful for validating that assets are local (for standalone mode).
	 *
	 * @since  1.0.0
	 * @param  string $url The URL to check.
	 * @return bool True if URL is local, false otherwise.
	 */
	public static function is_local_url( string $url ): bool {
		$home_url = home_url( '/' );
		$parsed_url = wp_parse_url( $url );
		$home_parsed = wp_parse_url( $home_url );

		if ( ! isset( $parsed_url['host'] ) || ! isset( $home_parsed['host'] ) ) {
			return false;
		}

		return $parsed_url['host'] === $home_parsed['host'];
	}
}
