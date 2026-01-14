<?php

namespace AffiliateProductShowcase\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use WP_Error;

final class Manifest {
	private const CACHE_GROUP      = 'aps_assets';
	private const CACHE_TTL        = 600;
	private const CACHE_KEY_PREFIX = 'manifest_';

	private static ?self $instance = null;

	/** @var array<string, array<string, mixed>> */
	private array $manifest = [];
	private string $manifest_path;
	private string $dist_path;
	private string $dist_url;
	private ?SRI $sri = null;

	private function __construct() {
		$this->manifest_path = $this->normalize_path( Constants::viewPath( 'assets/dist/manifest.json' ) );
		$this->dist_path     = $this->normalize_path( Constants::viewPath( 'assets/dist/' ) );
		$this->dist_url      = $this->ensure_trailing_slash( Constants::assetUrl( 'assets/dist/' ) );
	}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Internal-only helper for tests to reset the singleton.
	 *
	 * @internal
	 */
	public static function reset_instance(): void {
		self::$instance = null;
	}

	public function set_sri( SRI $sri ): void {
		$this->sri = $sri;
	}

	/**
	 * Load and validate the Vite manifest.
	 *
	 * @return bool|WP_Error
	 */
	public function load_manifest(): bool|WP_Error {
		$path = $this->manifest_path;

		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return new WP_Error(
				'aps_manifest_missing',
				__( 'The asset manifest could not be found or is not readable.', 'affiliate-product-showcase' ),
				[ 'path' => $path ]
			);
		}

		$mtime     = (int) filemtime( $path );
		$cache_key = $this->cache_key( $mtime );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( is_array( $cached ) && ! empty( $cached ) ) {
			$this->manifest = $cached;
			return true;
		}

		$contents = file_get_contents( $path );
		if ( false === $contents ) {
			return new WP_Error(
				'aps_manifest_unreadable',
				__( 'Unable to read the asset manifest.', 'affiliate-product-showcase' ),
				[ 'path' => $path ]
			);
		}

		try {
			$decoded = json_decode( $contents, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $exception ) {
			return new WP_Error(
				'aps_manifest_invalid_json',
				__( 'The asset manifest contains invalid JSON.', 'affiliate-product-showcase' ),
				[ 'message' => $exception->getMessage() ]
			);
		}

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'aps_manifest_invalid_schema',
				__( 'The asset manifest must decode to an associative array.', 'affiliate-product-showcase' )
			);
		}

		$validated = $this->validate_manifest( $decoded );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$this->manifest = $validated;
		wp_cache_set( $cache_key, $validated, self::CACHE_GROUP, self::CACHE_TTL );

		return true;
	}

	/**
	 * Resolve an asset entry to its URL and version.
	 *
	 * @param string $key Manifest key, e.g. admin.js.
	 *
	 * @return array{url:string,version:string}|WP_Error
	 */
	public function get_asset( string $key ): array|WP_Error {
		$key = $this->sanitize_asset_key( $key );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		if ( empty( $this->manifest ) ) {
			$loaded = $this->load_manifest();
			if ( is_wp_error( $loaded ) ) {
				return $loaded;
			}
		}

		$entry = $this->manifest[ $key ] ?? null;
		if ( ! is_array( $entry ) || empty( $entry['file'] ) || ! is_string( $entry['file'] ) ) {
			return new WP_Error(
				'aps_manifest_missing_entry',
				__( 'The requested asset is not present in the manifest.', 'affiliate-product-showcase' ),
				[ 'key' => $key ]
			);
		}

		$relative = ltrim( $entry['file'], '/\\' );
		$path     = $this->normalize_path( $this->dist_path . $relative );

		if ( ! file_exists( $path ) ) {
			return new WP_Error(
				'aps_manifest_file_missing',
				__( 'The asset file referenced in the manifest does not exist.', 'affiliate-product-showcase' ),
				[ 'key' => $key, 'path' => $path ]
			);
		}

		$version = (string) filemtime( $path );
		$url     = esc_url_raw( $this->dist_url . $relative );

		return [
			'url'     => $url,
			'version' => sanitize_text_field( $version ),
		];
	}

	/**
	 * Enqueue a script from the manifest with SRI and caching.
	 *
	 * @hook admin_enqueue_scripts
	 * @hook wp_enqueue_scripts
	 *
	 * @param string   $handle    Script handle.
	 * @param string   $key       Manifest key.
	 * @param string[] $deps      Dependencies.
	 * @param bool     $in_footer Whether to place the script in footer.
	 */
	public function enqueue_script( string $handle, string $key, array $deps = [], bool $in_footer = true ): bool {
		$asset = $this->get_asset( $key );
		if ( is_wp_error( $asset ) ) {
			do_action( 'aps_asset_error', $asset );
			return false;
		}

		$sanitized_handle = $this->sanitize_handle( $handle );
		if ( is_wp_error( $sanitized_handle ) ) {
			do_action( 'aps_asset_error', $sanitized_handle );
			return false;
		}

		$deps = $this->sanitize_dependency_handles( $deps );

		wp_register_script(
			$sanitized_handle,
			$asset['url'],
			$deps,
			$asset['version'],
			$in_footer
		);

		$this->apply_script_integrity( $sanitized_handle, $key );
		wp_enqueue_script( $sanitized_handle );

		return true;
	}

	/**
	 * Enqueue a stylesheet from the manifest with SRI and caching.
	 *
	 * @hook admin_enqueue_scripts
	 * @hook wp_enqueue_scripts
	 *
	 * @param string   $handle Stylesheet handle.
	 * @param string   $key    Manifest key.
	 * @param string[] $deps   Dependencies.
	 * @param string   $media  Media attribute.
	 */
	public function enqueue_style( string $handle, string $key, array $deps = [], string $media = 'all' ): bool {
		$asset = $this->get_asset( $key );
		if ( is_wp_error( $asset ) ) {
			do_action( 'aps_asset_error', $asset );
			return false;
		}

		$sanitized_handle = $this->sanitize_handle( $handle );
		if ( is_wp_error( $sanitized_handle ) ) {
			do_action( 'aps_asset_error', $sanitized_handle );
			return false;
		}

		$deps = $this->sanitize_dependency_handles( $deps );

		wp_register_style(
			$sanitized_handle,
			$asset['url'],
			$deps,
			$asset['version'],
			$media
		);

		$this->apply_style_integrity( $sanitized_handle, $key );
		wp_enqueue_style( $sanitized_handle );

		return true;
	}

	/**
	 * Get the absolute path to an asset defined in the manifest.
	 *
	 * @param string $key Manifest key.
	 *
	 * @return string|WP_Error
	 */
	public function get_asset_path( string $key ): string|WP_Error {
		$key = $this->sanitize_asset_key( $key );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		if ( empty( $this->manifest ) ) {
			$loaded = $this->load_manifest();
			if ( is_wp_error( $loaded ) ) {
				return $loaded;
			}
		}

		$entry = $this->manifest[ $key ] ?? null;
		if ( ! is_array( $entry ) || empty( $entry['file'] ) || ! is_string( $entry['file'] ) ) {
			return new WP_Error(
				'aps_manifest_missing_entry',
				__( 'The requested asset is not present in the manifest.', 'affiliate-product-showcase' ),
				[ 'key' => $key ]
			);
		}

		$relative = ltrim( $entry['file'], '/\\' );
		$path     = $this->normalize_path( $this->dist_path . $relative );

		if ( ! file_exists( $path ) ) {
			return new WP_Error(
				'aps_manifest_file_missing',
				__( 'The asset file referenced in the manifest does not exist.', 'affiliate-product-showcase' ),
				[ 'key' => $key, 'path' => $path ]
			);
		}

		return $path;
	}

	/**
	 * Validate manifest structure and disallow unsafe paths.
	 *
	 * @param array<string, mixed> $manifest Raw manifest.
	 *
	 * @return array<string, array<string, mixed>>|WP_Error
	 */
	private function validate_manifest( array $manifest ): array|WP_Error {
		$clean = [];

		foreach ( $manifest as $entry_key => $payload ) {
			if ( ! is_string( $entry_key ) || ! $this->is_safe_asset_name( $entry_key ) ) {
				return new WP_Error(
					'aps_manifest_invalid_key',
					__( 'Manifest entries must use safe, alphanumeric keys.', 'affiliate-product-showcase' ),
					[ 'key' => $entry_key ]
				);
			}

			if ( ! is_array( $payload ) || empty( $payload['file'] ) || ! is_string( $payload['file'] ) ) {
				return new WP_Error(
					'aps_manifest_invalid_entry',
					__( 'Each manifest entry must contain a file string.', 'affiliate-product-showcase' ),
					[ 'key' => $entry_key ]
				);
			}

			if ( ! $this->is_safe_asset_path( $payload['file'] ) ) {
				return new WP_Error(
					'aps_manifest_invalid_path',
					__( 'Manifest file paths must remain within the dist directory.', 'affiliate-product-showcase' ),
					[ 'file' => $payload['file'] ]
				);
			}

			$clean[ $entry_key ] = $payload;
		}

		return $clean;
	}

	private function cache_key( int $mtime ): string {
		return self::CACHE_KEY_PREFIX . $mtime;
	}

	private function ensure_trailing_slash( string $value ): string {
		if ( function_exists( 'trailingslashit' ) ) {
			return trailingslashit( $value );
		}

		return rtrim( $value, '/\\' ) . '/';
	}

	private function normalize_path( string $path ): string {
		if ( function_exists( 'wp_normalize_path' ) ) {
			return wp_normalize_path( $path );
		}

		return str_replace( '\\', '/', $path );
	}

	/**
	 * @param string $key
	 *
	 * @return string|WP_Error
	 */
	private function sanitize_asset_key( string $key ): string|WP_Error {
		$key = trim( $key );

		if ( '' === $key || ! $this->is_safe_asset_name( $key ) ) {
			return new WP_Error(
				'aps_manifest_invalid_key',
				__( 'The provided manifest key is invalid.', 'affiliate-product-showcase' ),
				[ 'key' => $key ]
			);
		}

		return $key;
	}

	private function is_safe_asset_name( string $key ): bool {
		return (bool) preg_match( '/^[A-Za-z0-9._-]+$/', $key );
	}

	private function is_safe_asset_path( string $path ): bool {
		if ( false !== strpos( $path, '..' ) ) {
			return false;
		}

		if ( preg_match( '/^[a-z]+:\//i', $path ) ) {
			return false;
		}

		if ( false !== strpos( $path, '\\' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string[] $deps
	 *
	 * @return string[]
	 */
	private function sanitize_dependency_handles( array $deps ): array {
		return array_values(
			array_filter(
				array_map(
					function ( $dep ) {
						$dep = is_string( $dep ) ? $dep : '';
						return sanitize_title_with_dashes( $dep );
					},
					$deps
				),
				static fn ( $dep ) => '' !== $dep
			)
		);
	}

	/**
	 * @param string $handle
	 *
	 * @return string|WP_Error
	 */
	private function sanitize_handle( string $handle ): string|WP_Error {
		$handle = sanitize_title_with_dashes( $handle );
		if ( '' === $handle ) {
			return new WP_Error(
				'aps_manifest_invalid_handle',
				__( 'The provided handle is invalid.', 'affiliate-product-showcase' )
			);
		}

		return $handle;
	}

	private function apply_script_integrity( string $handle, string $key ): void {
		if ( null === $this->sri ) {
			return;
		}

		$integrity = $this->sri->get_integrity_attribute( $key );
		$normalized = $this->normalize_integrity_value( $integrity );
		if ( '' === $normalized ) {
			return;
		}

		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( $handle, 'integrity', $normalized );
			wp_script_add_data( $handle, 'crossorigin', 'anonymous' );
		}
	}

	private function apply_style_integrity( string $handle, string $key ): void {
		if ( null === $this->sri ) {
			return;
		}

		$integrity = $this->sri->get_integrity_attribute( $key );
		$normalized = $this->normalize_integrity_value( $integrity );
		if ( '' === $normalized ) {
			return;
		}

		if ( function_exists( 'wp_style_add_data' ) ) {
			wp_style_add_data( $handle, 'integrity', $normalized );
			wp_style_add_data( $handle, 'crossorigin', 'anonymous' );
		}
	}

	private function normalize_integrity_value( string $integrity ): string {
		if ( '' === $integrity ) {
			return '';
		}

		if ( preg_match( '#^integrity="(?P<hash>sha384-[A-Za-z0-9+/=]+)"$#', $integrity, $matches ) ) {
			return $matches['hash'];
		}

		return $integrity;
	}
}
