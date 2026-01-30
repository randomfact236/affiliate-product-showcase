<?php
/**
 * Sort Order Constants
 *
 * Defines constant values for sort order options.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

/**
 * Sort Order Constants
 *
 * Defines constant values for sort order options across the plugin.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 2.1.0
 */
final class SortOrderConstants {
	/**
	 * Date sort order (newest first)
	 *
	 * @var string
	 */
	public const DATE = 'date';
	
	/**
	 * Name sort order (alphabetical)
	 *
	 * @var string
	 */
	public const NAME = 'name';
	
	/**
	 * Price sort order
	 *
	 * @var string
	 */
	public const PRICE = 'price';
	
	/**
	 * Popularity sort order
	 *
	 * @var string
	 */
	public const POPULARITY = 'popularity';
	
	/**
	 * Random sort order
	 *
	 * @var string
	 */
	public const RANDOM = 'random';
	
	/**
	 * All valid sort order values
	 *
	 * @var array<string>
	 */
	public const ALL = [
		self::DATE,
		self::NAME,
		self::PRICE,
		self::POPULARITY,
		self::RANDOM,
	];
	
	/**
	 * Get all valid sort orders
	 *
	 * @return array<string> Valid sort order values
	 * @since 2.1.0
	 */
	public static function getAll(): array {
		return self::ALL;
	}
	
	/**
	 * Check if sort order is valid
	 *
	 * @param string $sort_order Sort order to check
	 * @return bool True if valid
	 * @since 2.1.0
	 */
	public static function isValid(string $sort_order): bool {
		return in_array($sort_order, self::ALL, true);
	}
}
