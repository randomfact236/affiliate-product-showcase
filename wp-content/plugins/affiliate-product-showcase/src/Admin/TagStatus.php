<?php
/**
 * Tag Status Helper
 *
 * Helper class for managing tag visibility status using
 * aps_tag_visibility taxonomy. Provides methods for
 * getting and setting tag status (published, draft, trash).
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
 * Tag Status Helper
 *
 * Provides helper methods for managing tag visibility status
 * using the aps_tag_visibility taxonomy.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class TagStatus {
	/**
	 * Get tag visibility status
	 *
	 * Retrieves the visibility status of a tag from the
	 * aps_tag_visibility taxonomy. Returns 'published' if
	 * no status is set.
	 *
	 * @param int $tag_id Tag ID
	 * @return string Status: 'published', 'draft', or 'trash'
	 * @since 1.0.0
	 */
	public static function get_visibility(int $tag_id): string {
		$terms = wp_get_object_terms($tag_id, 'aps_tag_visibility');
		return !empty($terms) ? $terms[0]->slug : 'published';
	}

	/**
	 * Set tag visibility status
	 *
	 * Sets the visibility status of a tag by assigning
	 * the appropriate term from aps_tag_visibility taxonomy.
	 *
	 * @param int $tag_id Tag ID
	 * @param string $status Status: 'published', 'draft', or 'trash'
	 * @return bool True if status set successfully, false otherwise
	 * @since 1.0.0
	 */
	public static function set_visibility(int $tag_id, string $status): bool {
		$term = get_term_by('slug', $status, 'aps_tag_visibility');
		if (!$term) {
			return false;
		}

		$result = wp_set_object_terms($tag_id, [$term->term_id], 'aps_tag_visibility');
		return !is_wp_error($result);
	}

	/**
	 * Get visibility term (cached)
	 *
	 * Retrieves a visibility taxonomy term with caching.
	 * Reduces database queries by caching for 1 hour.
	 *
	 * @param string $slug Status slug: 'published', 'draft', or 'trash'
	 * @return \WP_Term|null Term object or null if not found
	 * @since 1.0.0
	 */
	public static function get_term_cached(string $slug): ?\WP_Term {
		$cache_key = "aps_visibility_term_{$slug}";
		$term = wp_cache_get($cache_key, 'aps_tag_visibility');
		
		if ($term === false) {
			$term = get_term_by('slug', $slug, 'aps_tag_visibility');
			wp_cache_set($cache_key, $term, 'aps_tag_visibility', HOUR_IN_SECONDS);
		}
		
		return $term ?: null;
	}

	/**
	 * Check if tag is published
	 *
	 * @param int $tag_id Tag ID
	 * @return bool True if published, false otherwise
	 * @since 1.0.0
	 */
	public static function is_published(int $tag_id): bool {
		return self::get_visibility($tag_id) === 'published';
	}

	/**
	 * Check if tag is draft
	 *
	 * @param int $tag_id Tag ID
	 * @return bool True if draft, false otherwise
	 * @since 1.0.0
	 */
	public static function is_draft(int $tag_id): bool {
		return self::get_visibility($tag_id) === 'draft';
	}

	/**
	 * Check if tag is trash
	 *
	 * @param int $tag_id Tag ID
	 * @return bool True if trash, false otherwise
	 * @since 1.0.0
	 */
	public static function is_trash(int $tag_id): bool {
		return self::get_visibility($tag_id) === 'trash';
	}
}