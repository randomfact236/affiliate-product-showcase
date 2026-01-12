<?php
/**
 * Environment Variables Helper
 *
 * Provides safe methods for reading environment variables with
 * proper type casting, defaults, and normalization.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 1.0.0
 * @version 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Helpers;

/**
 * Environment Helper Class
 *
 * Safe environment variable reader with type casting helpers.
 * All environment variables are prefixed with 'PLUGIN_' for consistency.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 1.0.0
 */
class Env {

	/**
	 * Get environment variable value
	 *
	 * Safely retrieves environment variable with support for default values.
	 * Automatically prepends 'PLUGIN_' prefix if not present.
	 *
	 * @param string $key     Environment variable key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed|null Environment value or default.
	 * @since 1.0.0
	 */
	public static function get( string $key, $default = null ) {
		// Automatically prepend PLUGIN_ prefix if not present
		if ( 0 !== strpos( $key, 'PLUGIN_' ) ) {
			$key = 'PLUGIN_' . $key;
		}

		$value = getenv( $key );

		if ( false === $value ) {
			return $default;
		}

		/**
		 * Filter environment variable value
		 *
		 * @param mixed  $value   The environment value.
		 * @param string $key     The environment key.
		 * @param mixed  $default The default value.
		 * @since 1.0.0
		 */
		return apply_filters( 'affiliate_product_showcase_get_env', $value, $key, $default );
	}

	/**
	 * Get environment variable as boolean
	 *
	 * Converts string values 'true', '1', 'yes', 'on' to true,
	 * 'false', '0', 'no', 'off', '' to false.
	 *
	 * @param string $key     Environment variable key.
	 * @param bool   $default Default value (default false).
	 * @return bool Boolean value.
	 * @since 1.0.0
	 */
	public static function get_bool( string $key, bool $default = false ): bool {
		$value = self::get( $key, (string) $default );

		if ( is_bool( $value ) ) {
			return $value;
		}

		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get environment variable as integer
	 *
	 * @param string $key     Environment variable key.
	 * @param int    $default Default value.
	 * @return int Integer value.
	 * @since 1.0.0
	 */
	public static function get_int( string $key, int $default = 0 ): int {
		$value = self::get( $key, (string) $default );

		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		return $default;
	}

	/**
	 * Get environment variable as float
	 *
	 * @param string $key     Environment variable key.
	 * @param float  $default Default value.
	 * @return float Float value.
	 * @since 1.0.0
	 */
	public static function get_float( string $key, float $default = 0.0 ): float {
		$value = self::get( $key, (string) $default );

		if ( is_numeric( $value ) ) {
			return (float) $value;
		}

		return $default;
	}

	/**
	 * Get environment variable as string
	 *
	 * @param string $key     Environment variable key.
	 * @param string $default Default value.
	 * @return string String value.
	 * @since 1.0.0
	 */
	public static function get_string( string $key, string $default = '' ): string {
		$value = self::get( $key, $default );

		if ( is_string( $value ) ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Get environment variable as array
	 *
	 * Supports comma-separated, pipe-separated, or JSON-encoded arrays.
	 *
	 * @param string $key     Environment variable key.
	 * @param array  $default Default value.
	 * @return array Array value.
	 * @since 1.0.0
	 */
	public static function get_array( string $key, array $default = [] ): array {
		$value = self::get( $key, '' );

		if ( empty( $value ) ) {
			return $default;
		}

		// Try JSON decode first
		$json = json_decode( $value, true );
		if ( json_last_error() === JSON_ERROR_NONE && is_array( $json ) ) {
			return $json;
		}

		// Try comma-separated
		if ( strpos( $value, ',' ) !== false ) {
			return array_map( 'trim', explode( ',', $value ) );
		}

		// Try pipe-separated
		if ( strpos( $value, '|' ) !== false ) {
			return array_map( 'trim', explode( '|', $value ) );
		}

		return $default;
	}

	/**
	 * Get environment variable as associative array (key=value pairs)
	 *
	 * Supports comma-separated key=value pairs.
	 *
	 * @param string $key     Environment variable key.
	 * @param array  $default Default value.
	 * @return array Associative array.
	 * @since 1.0.0
	 */
	public static function get_assoc( string $key, array $default = [] ): array {
		$value = self::get( $key, '' );

		if ( empty( $value ) ) {
			return $default;
		}

		$result = [];
		$pairs  = explode( ',', $value );

		foreach ( $pairs as $pair ) {
			$parts = explode( '=', trim( $pair ), 2 );
			if ( count( $parts ) === 2 ) {
				$result[ trim( $parts[0] ) ] = trim( $parts[1] );
			}
		}

		return $result;
	}

	/**
	 * Check if environment variable is set
	 *
	 * @param string $key Environment variable key.
	 * @return bool True if set and not empty, false otherwise.
	 * @since 1.0.0
	 */
	public static function has( string $key ): bool {
		$value = self::get( $key );

		return null !== $value && '' !== $value;
	}

	/**
	 * Get development mode from environment
	 *
	 * @return bool True if development mode is enabled.
	 * @since 1.0.0
	 */
	public static function is_dev_mode(): bool {
		return self::get_bool( 'DEV_MODE', false ) || self::get_bool( 'development', false );
	}

	/**
	 * Get debug mode from environment
	 *
	 * @return bool True if debug mode is enabled.
	 * @since 1.0.0
	 */
	public static function is_debug_mode(): bool {
		return self::get_bool( 'DEBUG_MODE', false ) || self::get_bool( 'debug', false );
	}

	/**
	 * Get database configuration from environment
	 *
	 * Returns associative array with db_host, db_name, db_user, db_password, db_prefix.
	 *
	 * @return array Database configuration or empty array if not set.
	 * @since 1.0.0
	 */
	public static function get_db_config(): array {
		return [
			'host'     => self::get_string( 'DB_HOST', '' ),
			'name'     => self::get_string( 'DB_NAME', '' ),
			'user'     => self::get_string( 'DB_USER', '' ),
			'password' => self::get_string( 'DB_PASSWORD', '' ),
			'prefix'   => self::get_string( 'DB_PREFIX', '' ),
		];
	}

	/**
	 * Get Redis configuration from environment
	 *
	 * Returns associative array with host, port, password, database, ttl.
	 *
	 * @return array Redis configuration or empty array if not set.
	 * @since 1.0.0
	 */
	public static function get_redis_config(): array {
		return [
			'host'     => self::get_string( 'REDIS_HOST', '127.0.0.1' ),
			'port'     => self::get_int( 'REDIS_PORT', 6379 ),
			'password' => self::get_string( 'REDIS_PASSWORD', '' ),
			'database' => self::get_int( 'REDIS_DATABASE', 0 ),
			'ttl'      => self::get_int( 'REDIS_TTL', 3600 ),
		];
	}

	/**
	 * Get all plugin environment variables
	 *
	 * Returns all environment variables that start with 'PLUGIN_'.
	 *
	 * @return array Associative array of plugin environment variables.
	 * @since 1.0.0
	 */
	public static function get_all(): array {
		$all_env = $_ENV; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$plugin_env = [];

		foreach ( $all_env as $key => $value ) {
			if ( 0 === strpos( $key, 'PLUGIN_' ) ) {
				$plugin_env[ $key ] = $value;
			}
		}

		return $plugin_env;
	}

	/**
	 * Normalize environment variable key
	 *
	 * Converts keys to uppercase and replaces dots/hyphens with underscores.
	 *
	 * @param string $key The key to normalize.
	 * @return string Normalized key.
	 * @since 1.0.0
	 */
	public static function normalize_key( string $key ): string {
		$key = strtoupper( $key );
		$key = str_replace( [ '.', '-' ], '_', $key );

		return $key;
	}

	/**
	 * Get environment variable with validation callback
	 *
	 * Retrieves value and validates it using a custom callback function.
	 *
	 * @param string   $key     Environment variable key.
	 * @param callable $validate Validation callback (receives value, returns bool).
	 * @param mixed    $default Default value if validation fails.
	 * @return mixed Validated value or default.
	 * @since 1.0.0
	 */
	public static function get_validated( string $key, callable $validate, $default = null ) {
		$value = self::get( $key, $default );

		if ( $validate( $value ) ) {
			return $value;
		}

		return $default;
	}
}
