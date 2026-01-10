<?php

namespace AffiliateProductShowcase\Assets;

use WP_Error;

final class SRI {
	private const TRANSIENT_PREFIX = 'aps_sri_';

	private Manifest $manifest;
	private int $ttl;

	public function __construct( Manifest $manifest, int $ttl = 0 ) {
		$this->manifest = $manifest;
		$this->ttl      = $ttl > 0 ? $ttl : ( defined( 'DAY_IN_SECONDS' ) ? (int) DAY_IN_SECONDS : 86400 );
	}

	/**
	 * Generate a SHA-384 hash for a file on disk.
	 *
	 * @param string $filepath Absolute path to the asset.
	 *
	 * @return string|WP_Error
	 */
	public function generate_hash( string $filepath ): string|WP_Error {
		$filepath = $this->normalize_path( $filepath );

		if ( '' === $filepath || ! file_exists( $filepath ) || ! is_readable( $filepath ) ) {
			return new WP_Error(
				'aps_sri_missing_file',
				__( 'Cannot generate SRI hash because the file is missing or unreadable.', 'affiliate-product-showcase' ),
				[ 'path' => $filepath ]
			);
		}

		$hash = hash_file( 'sha384', $filepath, true );
		if ( false === $hash ) {
			return new WP_Error(
				'aps_sri_hash_failed',
				__( 'Failed to generate the SRI hash for the requested asset.', 'affiliate-product-showcase' ),
				[ 'path' => $filepath ]
			);
		}

		return 'sha384-' . base64_encode( $hash );
	}

	/**
	 * Return a fully formatted integrity attribute string or empty on error.
	 *
	 * @param string $asset_key Manifest key for the asset.
	 */
	public function get_integrity_attribute( string $asset_key ): string {
		$path = $this->manifest->get_asset_path( $asset_key );
		if ( is_wp_error( $path ) ) {
			do_action( 'aps_asset_error', $path );
			return '';
		}

		$mtime = (int) filemtime( $path );
		$key   = $this->cache_key( $path, $mtime );

		$cached = get_transient( $key );
		if ( is_string( $cached ) && $this->is_valid_integrity_string( $cached ) ) {
			return $cached;
		}

		$hash = $this->generate_hash( $path );
		if ( is_wp_error( $hash ) || ! is_string( $hash ) ) {
			do_action( 'aps_asset_error', $hash );
			return '';
		}

		$attribute = sprintf( 'integrity="%s"', $hash );
		set_transient( $key, $attribute, $this->ttl );

		return $attribute;
	}

	/**
	 * Verify a stored hash against the on-disk file.
	 *
	 * @param string $filepath      Absolute path to the file.
	 * @param string $expected_hash Expected SRI hash (sha384-...).
	 */
	public function verify_hash( string $filepath, string $expected_hash ): bool {
		if ( ! $this->is_valid_hash_value( $expected_hash ) ) {
			return false;
		}

		$current = $this->generate_hash( $filepath );
		if ( is_wp_error( $current ) ) {
			return false;
		}

		return hash_equals( $expected_hash, $current );
	}

	private function cache_key( string $path, int $mtime ): string {
		return self::TRANSIENT_PREFIX . md5( $this->normalize_path( $path ) . '|' . $mtime );
	}

	private function normalize_path( string $path ): string {
		if ( function_exists( 'wp_normalize_path' ) ) {
			return wp_normalize_path( $path );
		}

		return str_replace( '\\', '/', $path );
	}

	private function is_valid_hash_value( string $hash ): bool {
		return (bool) preg_match( '#^sha384-[A-Za-z0-9+/=]+$#', $hash );
	}

	private function is_valid_integrity_string( string $value ): bool {
		return (bool) preg_match( '#^integrity="sha384-[A-Za-z0-9+/=]+"$#', $value );
	}
}
