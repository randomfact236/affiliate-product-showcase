<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {
	public static function activate(): void {
		// Register post type
		register_post_type(
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'               => __( 'Products', Constants::TEXTDOMAIN ),
					'singular_name'      => __( 'Product', Constants::TEXTDOMAIN ),
					'menu_name'          => __( 'Affiliate Products', Constants::TEXTDOMAIN ),
					'all_items'          => __( 'All Products', Constants::TEXTDOMAIN ),
					'add_new_item'       => __( 'Add Product', Constants::TEXTDOMAIN ),
					'edit_item'          => __( 'Edit Product', Constants::TEXTDOMAIN ),
					'new_item'           => __( 'New Product', Constants::TEXTDOMAIN ),
					'view_item'          => __( 'View Product', Constants::TEXTDOMAIN ),
					'search_items'       => __( 'Search Products', Constants::TEXTDOMAIN ),
					'not_found'          => __( 'No products found', Constants::TEXTDOMAIN ),
					'not_found_in_trash' => __( 'No products found in trash', Constants::TEXTDOMAIN ),
				],
				'public'              => true,
				'show_in_rest'        => true,
				'supports'            => [ 'title', 'editor', 'thumbnail' ],
				'rewrite'             => [ 'slug' => 'affiliate-product' ],
				'has_archive'         => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => true,
				'capability_type'     => 'post',
				'show_in_menu'        => true,
				'menu_position'       => 55,
				'taxonomies'          => [ Constants::TAX_CATEGORY, Constants::TAX_TAG, Constants::TAX_RIBBON ],
			]
		);

		// Register Category taxonomy
		register_taxonomy(
			Constants::TAX_CATEGORY,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                       => __( 'Categories', Constants::TEXTDOMAIN ),
					'singular_name'              => __( 'Category', Constants::TEXTDOMAIN ),
					'search_items'               => __( 'Search Categories', Constants::TEXTDOMAIN ),
					'all_items'                  => __( 'All Categories', Constants::TEXTDOMAIN ),
					'parent_item'                => __( 'Parent Category', Constants::TEXTDOMAIN ),
					'parent_item_colon'          => __( 'Parent Category:', Constants::TEXTDOMAIN ),
					'edit_item'                  => __( 'Edit Category', Constants::TEXTDOMAIN ),
					'update_item'                => __( 'Update Category', Constants::TEXTDOMAIN ),
					'add_new_item'               => __( 'Add New Category', Constants::TEXTDOMAIN ),
					'new_item_name'              => __( 'New Category Name', Constants::TEXTDOMAIN ),
					'menu_name'                  => __( 'Categories', Constants::TEXTDOMAIN ),
				],
				'hierarchical'               => true,
				'public'                     => true,
				'show_in_rest'               => true,
				'show_ui'                    => true,
				'show_admin_column'           => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'rewrite'                    => [ 'slug' => 'product-category' ],
			]
		);

		// Register Tag taxonomy
		register_taxonomy(
			Constants::TAX_TAG,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                       => __( 'Tags', Constants::TEXTDOMAIN ),
					'singular_name'              => __( 'Tag', Constants::TEXTDOMAIN ),
					'search_items'               => __( 'Search Tags', Constants::TEXTDOMAIN ),
					'all_items'                  => __( 'All Tags', Constants::TEXTDOMAIN ),
					'edit_item'                  => __( 'Edit Tag', Constants::TEXTDOMAIN ),
					'update_item'                => __( 'Update Tag', Constants::TEXTDOMAIN ),
					'add_new_item'               => __( 'Add New Tag', Constants::TEXTDOMAIN ),
					'new_item_name'              => __( 'New Tag Name', Constants::TEXTDOMAIN ),
					'menu_name'                  => __( 'Tags', Constants::TEXTDOMAIN ),
				],
				'hierarchical'               => false,
				'public'                     => true,
				'show_in_rest'               => true,
				'show_ui'                    => true,
				'show_admin_column'           => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'rewrite'                    => [ 'slug' => 'product-tag' ],
			]
		);

		// Register Ribbon taxonomy (non-hierarchical, for badges/labels)
		register_taxonomy(
			Constants::TAX_RIBBON,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                       => __( 'Ribbons', Constants::TEXTDOMAIN ),
					'singular_name'              => __( 'Ribbon', Constants::TEXTDOMAIN ),
					'search_items'               => __( 'Search Ribbons', Constants::TEXTDOMAIN ),
					'all_items'                  => __( 'All Ribbons', Constants::TEXTDOMAIN ),
					'edit_item'                  => __( 'Edit Ribbon', Constants::TEXTDOMAIN ),
					'update_item'                => __( 'Update Ribbon', Constants::TEXTDOMAIN ),
					'add_new_item'               => __( 'Add New Ribbon', Constants::TEXTDOMAIN ),
					'new_item_name'              => __( 'New Ribbon Name', Constants::TEXTDOMAIN ),
					'menu_name'                  => __( 'Ribbons', Constants::TEXTDOMAIN ),
				],
				'hierarchical'               => false,
				'public'                     => true,
				'show_in_rest'               => true,
				'show_ui'                    => true,
				'show_admin_column'           => true,
				'show_in_nav_menus'          => false,
				'show_tagcloud'              => false,
				'rewrite'                    => [ 'slug' => 'product-ribbon' ],
			]
		);

		// Update plugin version
		update_option( Constants::PREFIX . 'plugin_version', Constants::VERSION );
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
