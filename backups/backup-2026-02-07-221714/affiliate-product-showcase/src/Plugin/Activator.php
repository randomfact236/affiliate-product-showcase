<?php
/**
 * Plugin Activator
 *
 * Handles plugin activation tasks including:
 * - Registering custom post types
 * - Registering taxonomies
 * - Setting plugin version
 * - Flushing rewrite rules
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

use AffiliateProductShowcase\Plugin\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Activator
 *
 * Handles plugin activation tasks including:
 * - Registering custom post types
 * - Registering taxonomies
 * - Setting plugin version
 * - Flushing rewrite rules
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 * @author Development Team
 */
final class Activator {
	/**
	 * Activate plugin
	 *
	 * Registers all custom post types and taxonomies,
	 * sets plugin version option, and flushes rewrite rules.
	 * Called on plugin activation via register_activation_hook().
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action register_activation_hook
	 */
	public static function activate(): void {
		// Register CPT and taxonomies using static helper from ProductService
		// This eliminates code duplication and ensures single source of truth
		// while still working during activation (before DI container is ready)
		\AffiliateProductShowcase\Services\ProductService::register_all();

		// Update plugin version
		update_option( Constants::PREFIX . 'plugin_version', Constants::VERSION );
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
