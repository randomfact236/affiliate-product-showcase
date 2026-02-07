<?php
/**
 * Category Meta Keys Manager
 *
 * Centralized management of category meta keys to eliminate magic strings
 * and provide consistent access to both new and legacy meta key formats.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 2.1.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Helpers;

/**
 * Category Meta Keys Manager
 *
 * Provides centralized meta key management for categories, handling both
 * new format (_aps_category_) and legacy format (aps_category_) keys.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 2.1.0
 */
final class CategoryMetaKeys {
	/**
	 * Meta key prefixes
	 */
	private const PREFIX_NEW = '_aps_category_';
	private const PREFIX_LEGACY = 'aps_category_';

	/**
	 * Meta key names (without prefix)
	 */
	public const FEATURED = 'featured';
	public const IMAGE = 'image';
	public const SORT_ORDER = 'sort_order';
	public const STATUS = 'status';
	public const IS_DEFAULT = 'is_default';
	public const UPDATED_AT = 'updated_at';

	/**
	 * Get new format meta key
	 *
	 * @param string $key Meta key name
	 * @return string Full meta key with new prefix
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * $key = CategoryMetaKeys::new( CategoryMetaKeys::FEATURED );
	 * // Returns: '_aps_category_featured'
	 * ```
	 */
	public static function new( string $key ): string {
		return self::PREFIX_NEW . $key;
	}

	/**
	 * Get legacy format meta key
	 *
	 * @param string $key Meta key name
	 * @return string Full meta key with legacy prefix
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * $key = CategoryMetaKeys::legacy( CategoryMetaKeys::FEATURED );
	 * // Returns: 'aps_category_featured'
	 * ```
	 */
	public static function legacy( string $key ): string {
		return self::PREFIX_LEGACY . $key;
	}

	/**
	 * Clean up legacy meta for a key
	 *
	 * Removes legacy format meta key from term metadata.
	 *
	 * @param int $term_id Term ID
	 * @param string $key Meta key name
	 * @return void
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * CategoryMetaKeys::cleanup_legacy( 1, CategoryMetaKeys::FEATURED );
	 * // Deletes: 'aps_category_featured'
	 * ```
	 */
	public static function cleanup_legacy( int $term_id, string $key ): void {
		delete_term_meta( $term_id, self::legacy( $key ) );
	}

	/**
	 * Clean up multiple legacy meta keys
	 *
	 * @param int $term_id Term ID
	 * @param array<string> $keys Array of meta key names
	 * @return void
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * CategoryMetaKeys::cleanup_legacy_batch( 1, [
	 *     CategoryMetaKeys::FEATURED,
	 *     CategoryMetaKeys::IMAGE
	 * ] );
	 * ```
	 */
	public static function cleanup_legacy_batch( int $term_id, array $keys ): void {
		foreach ( $keys as $key ) {
			self::cleanup_legacy( $term_id, $key );
		}
	}

	/**
	 * Get meta with fallback to legacy format
	 *
	 * Attempts to retrieve meta using new format first,
	 * falls back to legacy format if not found.
	 *
	 * @param int $term_id Term ID
	 * @param string $key Meta key name
	 * @param mixed $default Default value if not found
	 * @return mixed Meta value or default
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * $featured = CategoryMetaKeys::get_with_fallback(
	 *     1,
	 *     CategoryMetaKeys::FEATURED,
	 *     false
	 * );
	 * ```
	 */
	public static function get_with_fallback( int $term_id, string $key, $default = '' ) {
		$value = get_term_meta( $term_id, self::new( $key ), true );
		if ( empty( $value ) ) {
			$value = get_term_meta( $term_id, self::legacy( $key ), true );
		}
		return $value ?: $default;
	}

	/**
	 * Update meta and clean up legacy
	 *
	 * Updates meta using new format and removes legacy format.
	 * This is the recommended method for updating category meta.
	 *
	 * @param int $term_id Term ID
	 * @param string $key Meta key name
	 * @param mixed $value Meta value
	 * @return void
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * CategoryMetaKeys::update( 1, CategoryMetaKeys::FEATURED, true );
	 * // Updates: '_aps_category_featured'
	 * // Deletes: 'aps_category_featured'
	 * ```
	 */
	public static function update( int $term_id, string $key, $value ): void {
		update_term_meta( $term_id, self::new( $key ), $value );
		self::cleanup_legacy( $term_id, $key );
	}

	/**
	 * Delete meta in both formats
	 *
	 * Removes both new and legacy format meta keys.
	 *
	 * @param int $term_id Term ID
	 * @param string $key Meta key name
	 * @return void
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * CategoryMetaKeys::delete( 1, CategoryMetaKeys::FEATURED );
	 * // Deletes both: '_aps_category_featured' and 'aps_category_featured'
	 * ```
	 */
	public static function delete( int $term_id, string $key ): void {
		delete_term_meta( $term_id, self::new( $key ) );
		delete_term_meta( $term_id, self::legacy( $key ) );
	}

	/**
	 * Delete all category meta keys
	 *
	 * Removes all category-related meta in both formats.
	 *
	 * @param int $term_id Term ID
	 * @return void
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * CategoryMetaKeys::delete_all( 1 );
	 * ```
	 */
	public static function delete_all( int $term_id ): void {
		$all_keys = [
			self::FEATURED,
			self::IMAGE,
			self::SORT_ORDER,
			self::STATUS,
			self::IS_DEFAULT,
			self::UPDATED_AT,
		];

		foreach ( $all_keys as $key ) {
			self::delete( $term_id, $key );
		}
	}
}
