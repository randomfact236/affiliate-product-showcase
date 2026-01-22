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
	 * Menu slug
	 *
	 * @var string
	 */
	public const MENU_SLUG = 'affiliate-manager';

	/**
	 * Constructor
	 */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'addMenuPages' ] );
        add_action( 'admin_head', [ $this, 'addMenuIcons' ] );
        add_filter( 'custom_menu_order', '__return_true' );
        add_filter( 'menu_order', [ $this, 'reorderMenus' ], 999 );
        
        // Remove WordPress default "Add New" menu (we have custom "Add Product" instead)
        add_action( 'admin_menu', [ $this, 'removeDefaultAddNewMenu' ], 999 );
    }

	/**
	 * Add menu pages
	 *
	 * @return void
	 */
	public function addMenuPages(): void {
		// Add Product submenu under Affiliate Products CPT
		add_submenu_page(
			'edit.php?post_type=aps_product',
			__( 'Add Product', 'affiliate-product-showcase' ),
			__( 'Add Product', 'affiliate-product-showcase' ),
			'manage_options',
			'add-product',
			[ $this, 'renderAddProductPage' ]
		);

		// Main menu page - Affiliate Manager (plugin settings)
		add_menu_page(
			__( 'Affiliate Manager', 'affiliate-product-showcase' ),
			__( 'Affiliate Manager', 'affiliate-product-showcase' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this, 'renderDashboardPage' ],
			'dashicons-admin-generic',
			55.1
		);

		// Dashboard submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'affiliate-product-showcase' ),
			__( 'Dashboard', 'affiliate-product-showcase' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this, 'renderDashboardPage' ]
		);

		// Settings submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'affiliate-product-showcase' ),
			__( 'Settings', 'affiliate-product-showcase' ),
			'manage_options',
			self::MENU_SLUG . '-settings',
			[ $this, 'renderSettingsPage' ]
		);

		// Help submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Help', 'affiliate-product-showcase' ),
			__( 'Help', 'affiliate-product-showcase' ),
			'manage_options',
			self::MENU_SLUG . '-help',
			[ $this, 'renderHelpPage' ]
		);
	}

    /**
     * Render dashboard page
     *
     * @return void
     */
    public function renderDashboardPage(): void {
        include \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/dashboard-page.php' );
    }

    /**
     * Render analytics page
     *
     * @return void
     */
    public function renderAnalyticsPage(): void {
        include \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/analytics-page.php' );
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
