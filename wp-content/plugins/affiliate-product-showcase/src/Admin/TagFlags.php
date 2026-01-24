<?php
/**
 * Tag Flags Helper
 *
 * Helper class for managing tag flags using
 * aps_tag_flags taxonomy. Provides methods for
 * getting and setting tag flags (featured, none).
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Tag Flags Helper
 *
 * Provides helper methods for managing tag flags
 * using aps_tag_flags taxonomy.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class TagFlags {
	/**
	 * Get tag featured flag
	 *
	 * Retrieves featured flag status of a tag from
	 * aps_tag_flags taxonomy. Returns 'none' if
	 * no flag is set.
	 *
	 * @param int $tag_id Tag ID
	 * @return string Flag: 'featured' or 'none'
	 * @since 1.0.0
	 */
	public static function get_featured(int $tag_id): string {
		$terms = wp_get_object_terms($tag_id, 'aps_tag_flags');
		return !empty($terms) ? $terms[0]->slug : 'none';
	}

	/**
	 * Set tag featured flag
	 *
	 * Sets featured flag of a tag by assigning
	 * appropriate term from aps_tag_flags taxonomy.
	 *
	 * @param int $tag_id Tag ID
	 * @param string $flag Flag: 'featured' or 'none'
	 * @return bool True if flag set successfully, false otherwise
	 * @since 1.0.0
	 */
	public static function set_featured(int $tag_id, string $flag): bool {
		$term = get_term_by('slug', $flag, 'aps_tag_flags');
		if (!$term) {
			return false;
		}

		$result = wp_set_object_terms($tag_id, [$term->term_id], 'aps_tag_flags');
		return !is_wp_error($result);
	}

	/**
	 * Get flag term (cached)
	 *
	 * Retrieves a flag taxonomy term with caching.
	 * Reduces database queries by caching for 1 hour.
	 *
	 * @param string $slug Flag slug: 'featured' or 'none'
	 * @return \WP_Term|null Term object or null if not found
	 * @since 1.0.0
	 */
	public static function get_term_cached(string $slug): ?\WP_Term {
		$cache_key = "aps_flag_term_{$slug}";
		$term = wp_cache_get($cache_key, 'aps_tag_flags');
		
		if ($term === false) {
			$term = get_term_by('slug', $slug, 'aps_tag_flags');
			wp_cache_set($cache_key, $term, 'aps_tag_flags', HOUR_IN_SECONDS);
		}
		
		return $term ?: null;
	}

	/**
	 * Check if tag is featured
	 *
	 * @param int $tag_id Tag ID
	 * @return bool True if featured, false otherwise
	 * @since 1.0.0
	 */
	public static function is_featured(int $tag_id): bool {
		return self::get_featured($tag_id) === 'featured';
	}
}