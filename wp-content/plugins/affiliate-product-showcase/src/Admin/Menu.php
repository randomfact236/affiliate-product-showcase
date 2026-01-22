<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Admin Menu
 *
 * Manages admin menu pages and submenus.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class Menu {

	/**
	 * Reorder submenus under Affiliate Products CPT
	 * 
	 * Desired order: All Products, Add Product, Categories, Tags, Ribbons
	 * Uses remove/add approach for guaranteed ordering
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reorderSubmenus(): void {
		global $submenu;
		$parent = 'edit.php?post_type=aps_product';
		
		if ( ! isset( $submenu[ $parent ] ) ) {
			error_log('MENU REORDER: Parent submenu not found!');
			return;
		}
		
		// Remove custom submenu items
		remove_submenu_page( $parent, 'add-product' );
		remove_submenu_page( $parent, 'edit-tags.php?taxonomy=aps_category&post_type=aps_product' );
		remove_submenu_page( $parent, 'edit-tags.php?taxonomy=aps_tag&post_type=aps_product' );
		remove_submenu_page( $parent, 'edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product' );
		
		// Re-add in desired order (All Products stays at top - it's core)
		
		// 1. Add Product
		add_submenu_page(
			$parent,
			__( 'Add Product', 'affiliate-product-showcase' ),
			__( 'Add Product', 'affiliate-product-showcase' ),
			'manage_options',
			'add-product',
			[ $this, 'renderAddProductPage' ]
		);
		
		// 2. Categories
		add_submenu_page(
			$parent,
			__( 'Categories', 'affiliate-product-showcase' ),
			__( 'Categories', 'affiliate-product-showcase' ),
			'manage_categories',
			'edit-tags.php?taxonomy=aps_category&post_type=aps_product'
		);
		
		// 3. Tags
		add_submenu_page(
			$parent,
			__( 'Tags', 'affiliate-product-showcase' ),
			__( 'Tags', 'affiliate-product-showcase' ),
			'manage_categories',
			'edit-tags.php?taxonomy=aps_tag&post_type=aps_product'
		);
		
		// 4. Ribbons
		add_submenu_page(
			$parent,
			__( 'Ribbons', 'affiliate-product-showcase' ),
			__( 'Ribbons', 'affiliate-product-showcase' ),
			'manage_categories',
			'edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product'
		);
		
		error_log('MENU REORDER: Submenu reordered successfully');
	}

    /**
     * Render settings page
     *
     * @return void
     */
    public function renderSettingsPage(): void {
        include \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/settings-page.php' );
    }

    /**
     * Render add product page (custom WooCommerce-style editor)
     *
     * @return void
     */
    public function renderAddProductPage(): void {
        include \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/add-product-page.php' );
    }

    /**
     * Render help page
     *
     * @return void
     */
    public function renderHelpPage(): void {
        include \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/help-page.php' );
    }

    /**
     * Add custom menu icons
     *
     * @return void
     */
    public function addMenuIcons(): void {
        ?>
        <style>
            #adminmenu .toplevel_page_affiliate-product-showcase .wp-menu-image img {
                width: 20px;
                height: 20px;
                padding: 5px 0;
            }
        </style>
        <?php
    }

    /**
     * Remove WordPress default "Add New" menu
     *
     * Removes the default WordPress "Add New" submenu that's automatically
     * created for custom post types. We have our custom "Add Product"
     * submenu instead (just like WooCommerce does).
     *
     * @return void
     */
    public function removeDefaultAddNewMenu(): void {
        remove_submenu_page( 'edit.php?post_type=aps_product', 'post-new.php?post_type=aps_product' );
    }

    /**
     * Get menu page URL
     *
     * @param string $page Page slug
     * @return string Page URL
     */
    public static function getPageUrl( string $page ): string {
        return admin_url( 'admin.php?page=' . self::MENU_SLUG . '-' . $page );
    }

    /**
     * Get dashboard URL
     *
     * @return string
     */
    public static function getDashboardUrl(): string {
        return self::getPageUrl( '' );
    }

    /**
     * Get settings URL
     *
     * @return string
     */
    public static function getSettingsUrl(): string {
        return self::getPageUrl( 'settings' );
    }

    /**
     * Get help URL
     *
     * @return string
     */
    public static function getHelpUrl(): string {
        return self::getPageUrl( 'help' );
    }

    /**
     * Get add product URL (under Affiliate Products CPT menu)
     *
     * @return string
     */
    public static function getAddProductUrl(): string {
        return admin_url( 'edit.php?post_type=aps_product&page=add-product' );
    }

    /**
     * Reorder menus to position Affiliate Manager right after Affiliate Products
     *
     * @param array $menu_order Current menu order
     * @return array Modified menu order
     */
    public function reorderMenus( $menu_order ) {
        // Find positions of our menus
        $products_key = array_search( 'edit.php?post_type=aps_product', $menu_order );
        $manager_key = array_search( self::MENU_SLUG, $menu_order );
        
        // If either menu not found, return unchanged
        if ( $products_key === false || $manager_key === false ) {
            return $menu_order;
        }
        
        // Remove Affiliate Manager from current position
        unset( $menu_order[$manager_key] );
        
        // Insert Affiliate Manager right after Affiliate Products
        array_splice( $menu_order, $products_key + 1, 0, [ self::MENU_SLUG ] );
        
        return $menu_order;
    }

}
