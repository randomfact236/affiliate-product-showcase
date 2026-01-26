<?php
/**
 * Tag Activation Handler
 *
 * Handles plugin activation/deactivation for the Tag taxonomy.
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
 * Handles activation and deactivation of the Tag taxonomy.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 * @author Development Team
 */
final class TagActivator {
	/**
	 * Activate tag taxonomies
	 *
	 * Registers the Tag taxonomy during plugin activation.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function activate(): void {
		// Register the Tag taxonomy
		\AffiliateProductShowcase\Services\ProductService::register_taxonomies_static();
		
		// Run any migration tasks
		do_action('aps_tag_activated');
	}

	/**
	 * Deactivate tag taxonomies
	 *
	 * Cleans up tag-related data during plugin deactivation.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function deactivate(): void {
		// Clean up any transient data or options
		// Note: We don't unregister taxonomies on deactivation
		// as WordPress handles this automatically
		
		do_action('aps_tag_deactivated');
	}
}