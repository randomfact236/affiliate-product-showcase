<?php
declare(strict_types=1);

namespace AffiliateProductShowcase;

use AffiliateProductShowcase\Plugin\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ribbon Activator
 *
 * Handles activation and deactivation of ribbon taxonomy.
 * Ensures proper cleanup when taxonomy is removed.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 * @author Development Team
 */
final class RibbonActivator {
	/**
	 * Activate ribbon taxonomy
	 *
	 * Registers the ribbon taxonomy and flushes rewrite rules.
	 * Called on plugin activation.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function activate(): void {
		// Register taxonomy
		$labels = [
			'name'                       => _x( 'Ribbons', 'taxonomy general name', 'affiliate-product-showcase' ),
			'singular_name'              => _x( 'Ribbon', 'taxonomy singular name', 'affiliate-product-showcase' ),
			'search_items'               => __( 'Search Ribbons', 'affiliate-product-showcase' ),
			'popular_items'              => __( 'Popular Ribbons', 'affiliate-product-showcase' ),
			'all_items'                  => __( 'All Ribbons', 'affiliate-product-showcase' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Ribbon', 'affiliate-product-showcase' ),
			'update_item'                => __( 'Update Ribbon', 'affiliate-product-showcase' ),
			'add_new_item'               => __( 'Add New Ribbon', 'affiliate-product-showcase' ),
			'new_item_name'              => __( 'New Ribbon Name', 'affiliate-product-showcase' ),
			'separate_items_with_commas'  => __( 'Separate ribbons with commas', 'affiliate-product-showcase' ),
			'add_or_remove_items'         => __( 'Add or remove ribbons', 'affiliate-product-showcase' ),
			'choose_from_most_used'      => __( 'Choose from the most used ribbons', 'affiliate-product-showcase' ),
			'not_found'                  => __( 'No ribbons found.', 'affiliate-product-showcase' ),
			'menu_name'                  => __( 'Ribbons', 'affiliate-product-showcase' ),
		];

		register_taxonomy(
			Constants::TAX_RIBBON,
			Constants::CPT_PRODUCT,
			[
				'hierarchical'       => false,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column'   => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'product-ribbon' ],
				'public'            => false,
				'show_in_rest'       => true,
				'rest_base'          => 'product-ribbons',
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
			]
		);

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Deactivate ribbon taxonomy
	 *
	 * Unregisters the ribbon taxonomy and flushes rewrite rules.
	 * Called on plugin deactivation.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function deactivate(): void {
		// Unregister taxonomy
		unregister_taxonomy( Constants::TAX_RIBBON );

		// Flush rewrite rules
		flush_rewrite_rules();
	}
}