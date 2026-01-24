<?php
/**
 * Tag Activation Handler
 *
 * Creates default terms for tag taxonomies during plugin activation.
 * Ensures required status and flag terms exist.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Tag Activation Handler
 *
 * Handles creation of default taxonomy terms for tags during plugin activation.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 * @author Development Team
 */
final class TagActivator {
	/**
	 * Activate tag taxonomies
	 *
	 * Creates default terms for both aps_tag_visibility and
	 * aps_tag_flags taxonomies. Should be called during plugin activation.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function activate(): void {
		self::create_visibility_terms();
		self::create_flag_terms();
	}

	/**
	 * Create visibility status terms
	 *
	 * Creates default terms for aps_tag_visibility taxonomy:
	 * - published: Tag is visible and active
	 * - draft: Tag is not visible
	 * - trash: Tag is marked for deletion
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private static function create_visibility_terms(): void {
		$statuses = [
			'published' => 'Published',
			'draft' => 'Draft',
			'trash' => 'Trash',
		];

		foreach ($statuses as $slug => $name) {
			$existing = get_term_by('slug', $slug, 'aps_tag_visibility');
			
			if (!$existing) {
				wp_insert_term(
					$name,
					'aps_tag_visibility',
					[
						'slug' => $slug,
						'description' => sprintf(
							'Tag status: %s',
							$name
						),
					]
				);
			}
		}
	}

	/**
	 * Create flag terms
	 *
	 * Creates default terms for aps_tag_flags taxonomy:
	 * - featured: Tag is marked as featured
	 * - none: Tag has no special flags
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private static function create_flag_terms(): void {
		$flags = [
			'featured' => 'Featured',
			'none' => 'None',
		];

		foreach ($flags as $slug => $name) {
			$existing = get_term_by('slug', $slug, 'aps_tag_flags');
			
			if (!$existing) {
				wp_insert_term(
					$name,
					'aps_tag_flags',
					[
						'slug' => $slug,
						'description' => sprintf(
							'Tag flag: %s',
							$name
						),
					]
				);
			}
		}
	}
}