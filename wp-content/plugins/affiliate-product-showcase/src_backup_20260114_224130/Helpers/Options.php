<?php
/**
 * Options API Wrapper
 *
 * Provides centralized methods for retrieving and updating plugin options
 * with environment variable fallback for development mode.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 1.0.0
 * @version 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Options Helper Class
 *
 * Centralized wrapper for WordPress Options API with environment
 * variable fallback for development settings.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 1.0.0
 */
class Options {

	/**
	 * Get plugin option with environment variable fallback
	 *
	 * Checks environment variable first (dev mode), then falls back to
	 * WordPress options table (production mode).
	 *
	 * @param string $key Option key.
	 * @param mixed  $default Default value if option not found.
	 * @return mixed Option value or default.
	 * @since 1.0.0
	 */
	public static function get_plugin_option( string $key, $default = null ) {
		// Check environment variable first (for development)
		$env_value = Env::get( $key );

		if ( null !== $env_value ) {
			return $env_value;
		}

		// Fall back to WordPress options (production)
		$option_name = Constants::OPTION_PREFIX . $key;
		$value        = get_option( $option_name, $default );

		/**
		 * Filter the retrieved option value
		 *
		 * @param mixed  $value   The option value.
		 * @param string $key     The option key.
		 * @param mixed  $default The default value.
		 * @since 1.0.0
		 */
		return apply_filters( 'affiliate_product_showcase_get_option', $value, $key, $default );
	}

	/**
	 * Update plugin option
	 *
	 * Updates option in WordPress options table only. Does not modify
	 * environment variables (which are read-only at runtime).
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value New value.
	 * @return bool True if updated successfully, false otherwise.
	 * @since 1.0.0
	 */
	public static function update_plugin_option( string $key, $value ): bool {
		$option_name = Constants::OPTION_PREFIX . $key;

		/**
		 * Filter the value before updating
		 *
		 * @param mixed  $value The new value.
		 * @param string $key   The option key.
		 * @since 1.0.0
		 */
		$value = apply_filters( 'affiliate_product_showcase_update_option_value', $value, $key );

		$result = update_option( $option_name, $value );

		if ( $result ) {
			/**
			 * Action fired after option is updated
			 *
			 * @param string $key   The option key.
			 * @param mixed  $value The new value.
			 * @since 1.0.0
			 */
			do_action( 'affiliate_product_showcase_after_update_option', $key, $value );
		}

		return $result;
	}

	/**
	 * Delete plugin option
	 *
	 * Deletes option from WordPress options table.
	 *
	 * @param string $key Option key.
	 * @return bool True if deleted successfully, false otherwise.
	 * @since 1.0.0
	 */
	public static function delete_plugin_option( string $key ): bool {
		$option_name = Constants::OPTION_PREFIX . $key;

		/**
		 * Action fired before option is deleted
		 *
		 * @param string $key The option key.
		 * @since 1.0.0
		 */
		do_action( 'affiliate_product_showcase_before_delete_option', $key );

		$result = delete_option( $option_name );

		if ( $result ) {
			/**
			 * Action fired after option is deleted
			 *
			 * @param string $key The option key.
			 * @since 1.0.0
			 */
			do_action( 'affiliate_product_showcase_after_delete_option', $key );
		}

		return $result;
	}

	/**
	 * Check if plugin option exists
	 *
	 * @param string $key Option key.
	 * @return bool True if option exists, false otherwise.
	 * @since 1.0.0
	 */
	public static function has_plugin_option( string $key ): bool {
		// Check environment variable first
		$env_value = Env::get( $key );
		if ( null !== $env_value ) {
			return true;
		}

		// Check WordPress options
		$option_name = Constants::OPTION_PREFIX . $key;
		return false !== get_option( $option_name );
	}

	/**
	 * Get multiple plugin options at once
	 *
	 * @param array $keys Array of option keys.
	 * @param array $defaults Array of default values keyed by option name.
	 * @return array Associative array of option values.
	 * @since 1.0.0
	 */
	public static function get_plugin_options( array $keys, array $defaults = [] ): array {
		$options = [];

		foreach ( $keys as $key ) {
			$default = $defaults[ $key ] ?? null;
			$options[ $key ] = self::get_plugin_option( $key, $default );
		}

		return $options;
	}

	/**
	 * Get development mode status
	 *
	 * Checks if plugin is in development mode via environment variable
	 * or WordPress option.
	 *
	 * @return bool True if in development mode, false otherwise.
	 * @since 1.0.0
	 */
	public static function is_dev_mode(): bool {
		return (bool) self::get_plugin_option( 'dev_mode', false );
	}

	/**
	 * Get debug mode status
	 *
	 * Checks if plugin debug mode is enabled via environment variable
	 * or WordPress option.
	 *
	 * @return bool True if debug mode is enabled, false otherwise.
	 * @since 1.0.0
	 */
	public static function is_debug_mode(): bool {
		return (bool) self::get_plugin_option( 'debug_mode', false );
	}
}
