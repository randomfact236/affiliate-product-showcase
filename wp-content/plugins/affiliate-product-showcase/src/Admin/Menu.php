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
	
	const MENU_SLUG = 'affiliate-manager';

	public function __construct() {
		// Add top-level Affiliate Manager menu (priority 10)
		add_action( 'admin_menu', [ $this, 'addMenuPages' ], 10 );
		
		// Add custom "Add Product" submenu to Affiliate Products CPT (priority 10)
		add_action( 'admin_menu', [ $this, 'addCustomSubmenus' ], 10 );
		
		// Redirect old form to new form
		add_action( 'admin_init', [ $this, 'redirectOldAddNewForm' ] );
		
		// Remove default Add New - run VERY late after admin_menu
		add_action( 'admin_menu', [ $this, 'removeDefaultAddNewMenu' ], PHP_INT_MAX );
		
		// Also remove on submenu filter (extra protection)
		add_filter( 'submenu_file', [ $this, 'removeDefaultAddNewFromSubmenu' ], 999 );
		
		// Reorder submenus - run last (after all menus registered)
		add_action( 'admin_menu', [ $this, 'reorderSubmenus' ], PHP_INT_MAX );
		
		// Add menu styling
		add_action( 'admin_head', [ $this, 'addMenuIcons' ] );
		
		// Enable custom menu ordering
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', [ $this, 'reorderMenus' ], 999 );
	}

	/**
	 * Add top-level Affiliate Manager menu with subpages
	 *
	 * Creates separate "Affiliate Manager" menu for plugin management
	 * separate from "Affiliate Products" (CPT) menu for content.
	 *
	 * Structure:
	 * - Affiliate Manager (top-level)
	 *   - Dashboard
	 *   - Settings
	 *   - Help
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function addMenuPages(): void {
		// Main top-level menu: Affiliate Manager
		add_menu_page(
			__( 'Affiliate Manager', 'affiliate-product-showcase' ),
			__( 'Affiliate Manager', 'affiliate-product-showcase' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this, 'renderDashboardPage' ],
			'dashicons-admin-generic',
			56
		);

		// Dashboard submenu (same slug as parent = no duplicate)
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
	 * Redirect old default Add New form to our custom Add Product page
	 *
	 * @return void
	 */
	public function redirectOldAddNewForm(): void {
		global $pagenow, $typenow;
		
		if ( $pagenow === 'post-new.php' && $typenow === 'aps_product' ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=aps_product&page=add-product' ) );
			exit;
		}
	}

	/**
	 * Add custom submenus to Affiliate Products CPT
	 *
	 * Registers custom "Add Product" submenu under Affiliate Products.
	 * Called during admin_menu hook (priority 10) before reordering.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function addCustomSubmenus(): void {
		// Add custom "Add Product" submenu to Affiliate Products CPT
		add_submenu_page(
			'edit.php?post_type=aps_product',
			__( 'Add Product', 'affiliate-product-showcase' ),
			__( 'Add Product', 'affiliate-product-showcase' ),
			'edit_posts',
			'add-product',
			[ $this, 'renderAddProductPage' ]
		);
	}

	/**
	 * Reorder submenus under Affiliate Products CPT
	 * 
	 * Desired order: All Products, Add Product, Categories, Tags, Ribbons
	 * Keeps all items, just reorders them (no adding/removing).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reorderSubmenus(): void {
		global $submenu;
		$parent = 'edit.php?post_type=aps_product';
		
		if ( ! isset( $submenu[ $parent ] ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'MENU REORDER: Parent submenu not found!' );
			}
			return;
		}
		
		// Get existing submenu items (all already registered by this point)
		$existing_items = $submenu[ $parent ];
		
		// Debug: log current items
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$current_items = [];
			foreach ( $existing_items as $item ) {
				$current_items[] = isset( $item[2] ) ? $item[2] : 'unknown';
			}
			error_log( 'MENU REORDER: Current items: ' . implode( ', ', $current_items ) );
		}
		
		// Keep ALL existing items, just reorder them
		$reordered_items = [];
		$used_indices = [];
		
		// 1. Keep "All Products" (edit.php?post_type=aps_product) - it's the main listing
		foreach ( $existing_items as $index => $item ) {
			$slug = isset( $item[2] ) ? $item[2] : '';
			if ( $slug === 'edit.php?post_type=aps_product' ) {
				$reordered_items[] = $item;
				$used_indices[] = $index;
				break;
			}
		}
		
		// 2. Add custom "Add Product" menu (now already registered)
		foreach ( $existing_items as $index => $item ) {
			$slug = isset( $item[2] ) ? $item[2] : '';
			if ( $slug === 'add-product' && ! in_array( $index, $used_indices, true ) ) {
				$reordered_items[] = $item;
				$used_indices[] = $index;
				break;
			}
		}
		
		// 3. Add remaining items in desired order (Categories, Tags, Ribbons)
		$desired_taxonomy_order = [
			'edit-tags.php?taxonomy=aps_category&post_type=aps_product', // Categories
			'edit-tags.php?taxonomy=aps_tag&post_type=aps_product',      // Tags
			'edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product',    // Ribbons
		];
		
		foreach ( $desired_taxonomy_order as $desired_slug ) {
			foreach ( $existing_items as $index => $item ) {
				$slug = isset( $item[2] ) ? $item[2] : '';
				// Match if slug starts with desired slug (handles query string variations)
				if ( $slug === $desired_slug && ! in_array( $index, $used_indices, true ) ) {
					$reordered_items[] = $item;
					$used_indices[] = $index;
					break;
				}
			}
		}
		
		// 4. Add any remaining items that weren't in desired order (don't lose anything)
		foreach ( $existing_items as $index => $item ) {
			if ( ! in_array( $index, $used_indices, true ) ) {
				$reordered_items[] = $item;
			}
		}
		
		// Update submenu with reordered items
		$submenu[ $parent ] = $reordered_items;
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$new_items = [];
			foreach ( $reordered_items as $item ) {
				$new_items[] = isset( $item[2] ) ? $item[2] : 'unknown';
			}
			error_log( 'MENU REORDER: Reordered items: ' . implode( ', ', $new_items ) );
		}
	}

    /**
     * Render dashboard page
     *
     * @since 1.0.0
     * @return void
     */
    public function renderDashboardPage(): void {
        include \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/dashboard-page.php' );
    }

    /**
     * Render settings page
     *
     * @since 1.0.0
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
     * Add custom menu icons styling
     *
     * @since 1.0.0
     * @return void
     */
    public function addMenuIcons(): void {
        ?>
        <style>
            #adminmenu .toplevel_page_affiliate-manager .wp-menu-image img {
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
     * Uses triple approach: WordPress helper + manual array cleanup + late execution
     *
     * @return void
     */
    public function removeDefaultAddNewMenu(): void {
        global $submenu;

        $parent_slug = 'edit.php?post_type=aps_product';
        $old_add_new_slug = 'post-new.php?post_type=aps_product';

        // Remove using WordPress helper (most reliable)
        remove_submenu_page( $parent_slug, $old_add_new_slug );

        // Also manually clean submenu array (fallback)
        if ( isset( $submenu[ $parent_slug ] ) ) {
            foreach ( $submenu[ $parent_slug ] as $index => $item ) {
                if ( isset( $item[2] ) && $item[2] === $old_add_new_slug ) {
                    unset( $submenu[ $parent_slug ][ $index ] );
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( '[APS] Default "Add New" submenu removed successfully' );
					}
                    break;
                }
            }
        }
        
        // Re-index array to prevent gaps
        if ( isset( $submenu[ $parent_slug ] ) ) {
            $submenu[ $parent_slug ] = array_values( $submenu[ $parent_slug ] );
        }
    }
    
    /**
     * Remove default "Add New" from submenu filter (extra protection)
     *
     * Additional layer to prevent default "Add New" from showing
     * when WordPress renders the menu.
     *
     * @param string $submenu_file Current submenu file
     * @return string Modified submenu file
     */
    public function removeDefaultAddNewFromSubmenu( $submenu_file ) {
        // Check if we're on the Add New page
        if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'aps_product' ) {
            if ( isset( $_GET['page'] ) && $_GET['page'] === 'add-product' ) {
                return 'add-product'; // Force our custom Add Product
            }
        }
        return $submenu_file;
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
