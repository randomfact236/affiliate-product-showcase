<?php
/**
 * Term Meta Helper
 *
 * Provides utility methods for term meta operations with legacy fallback support.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Helpers;

/**
 * Term Meta Helper
 *
 * Provides utility methods for term meta operations with legacy fallback support.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 2.1.0
 */
final class TermMetaHelper {
	/**
	 * Get meta value with legacy fallback
	 *
	 * @param int $term_id Term ID
	 * @param string $meta_key Meta key (without prefix)
	 * @param string $prefix Meta key prefix (e.g., 'aps_category_')
	 * @return mixed Meta value
	 * @since 2.1.0
	 */
	public static function get_with_fallback(int $term_id, string $meta_key, string $prefix): mixed {
		// Try new format with underscore prefix
		$value = get_term_meta($term_id, '_' . $prefix . $meta_key, true);
		
		// If empty, try legacy format without underscore
		if ($value === '' || $value === false) {
			$value = get_term_meta($term_id, $prefix . $meta_key, true);
		}
		
		return $value;
	}
	
	/**
	 * Delete both new and legacy meta keys
	 *
	 * @param int $term_id Term ID
	 * @param string $meta_key Meta key (without prefix)
	 * @param string $prefix Meta key prefix (e.g., 'aps_category_')
	 * @return void
	 * @since 2.1.0
	 */
	public static function delete_legacy(int $term_id, string $meta_key, string $prefix): void {
		// Delete new format key
		delete_term_meta($term_id, '_' . $prefix . $meta_key);
		// Delete legacy format key
		delete_term_meta($term_id, $prefix . $meta_key);
	}
	
	/**
	 * Update meta and delete legacy key
	 *
	 * @param int $term_id Term ID
	 * @param string $meta_key Meta key (without prefix)
	 * @param mixed $value Meta value
	 * @param string $prefix Meta key prefix (e.g., 'aps_category_')
	 * @return bool Success
	 * @since 2.1.0
	 */
	public static function update_with_legacy_cleanup(int $term_id, string $meta_key, mixed $value, string $prefix): bool {
		$result = update_term_meta($term_id, '_' . $prefix . $meta_key, $value);
		// Delete legacy key
		delete_term_meta($term_id, $prefix . $meta_key);
		return $result !== false;
	}
}
