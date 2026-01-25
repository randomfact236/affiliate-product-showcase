<?php
/**
 * Tag Activation Handler
 *
 * Placeholder for future tag-related activation tasks.
 * Since tags now use TRUE HYBRID approach with term meta for
 * status and flags, no auxiliary taxonomy terms are needed.
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
 * Placeholder for future tag-related activation tasks.
 * TRUE HYBRID approach uses term meta for status and flags,
 * so no auxiliary taxonomy terms are needed.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 * @author Development Team
 */
final class TagActivator {
	/**
	 * Activate tag taxonomies
	 *
	 * Placeholder for future activation tasks.
	 * TRUE HYBRID approach: Status and flags are stored in term meta,
	 * so no auxiliary taxonomy terms are needed.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function activate(): void {
		// TRUE HYBRID: No auxiliary taxonomy terms needed
		// Status and flags are stored in term meta:
		// - _aps_tag_status: 'published', 'draft', 'trash'
		// - _aps_tag_featured: '1' or '0'
		
		// Future activation tasks can be added here
		do_action('aps_tag_activated');
	}
}