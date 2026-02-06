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
		// Redirect native editor to custom page (using load hook - more reliable)
		add_action( 'load-post.php', [ $this, 'redirectNativeEditor' ] );
		add_action( 'load-post-new.php', [ $this, 'redirectNativeEditor' ] );
		
		// Filter all edit post links to point to custom form
		add_filter( 'get_edit_post_link', [ $this, 'filterEditPostLink' ], 10, 3 );
		
		// Add custom columns to products table (register early)
		add_filter( 'manage_aps_product_posts_columns', [ $this, 'addCustomColumns' ] );
		add_action( 'manage_aps_product_posts_custom_column', [ $this, 'renderCustomColumns' ], 10, 2 );
		add_filter( 'manage_edit-aps_product_sortable_columns', [ $this, 'makeColumnsSortable' ] );
		add_filter( 'pre_get_posts', [ $this, 'setDefaultSorting' ] );
		
		// Add top-level Affiliate Manager menu (priority 10)
		add_action( 'admin_menu', [ $this, 'addMenuPages' ], 10 );
		
		// Add custom "Add Product" submenu to Affiliate Products CPT (priority 10)
		add_action( 'admin_menu', [ $this, 'addCustomSubmenus' ], 10 );
		
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
	 * Redirect native editor to custom Add Product page
	 *
	 * Redirects both post-new.php (Add New) and post.php (Edit)
	 * to our custom single-page form.
	 *
	 * Uses load-post.php and load-post-new.php hooks for more reliable detection.
	 *
	 * @return void
	 */
	public function redirectNativeEditor(): void {
		// Check if we're editing an aps_product
		if ( ! isset( $_GET['post'] ) && ! isset( $_GET['post_type'] ) ) {
			return;
		}
		
		// Handle edit existing product
		$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
		if ( $post_id > 0 ) {
			$post_type = get_post_type( $post_id );
			if ( $post_type === 'aps_product' ) {
				// Redirect to custom form with post ID
				wp_safe_redirect( admin_url( 'edit.php?post_type=aps_product&page=add-product&post=' . $post_id ) );
				exit;
			}
		}
		
		// Handle add new product
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'aps_product' ) {
			// Redirect to custom add form
			wp_safe_redirect( self::getAddProductUrl() );
			exit;
		}
	}
	
	/**
	 * Filter edit post link to use custom form
	 *
	 * Redirects all "Edit" links throughout the admin area to use
	 * our custom add-product form instead of native WordPress editor.
	 *
	 * @param string $url     The edit post link
	 * @param int    $post_id  The post ID
	 * @param string $context  The link context
	 * @return string Modified URL pointing to custom form
	 */
	public function filterEditPostLink( string $url, int $post_id, string $context ): string {
		$post_type = get_post_type( $post_id );
		
		// Only redirect for aps_product post type
		if ( $post_type === 'aps_product' ) {
			return admin_url( 'edit.php?post_type=aps_product&page=add-product&post=' . $post_id );
		}
		
		return $url;
	}
	
	/**
	 * Add custom columns to products table
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns with custom ones
	 */
	public function addCustomColumns(array $columns): array {
		// Ensure ID column is present and positioned
		if (!isset($columns['id'])) {
			// Insert ID column at position 1 (after checkbox)
			$id_column = ['id' => __('ID', 'affiliate-product-showcase')];
			$columns = array_slice($columns, 0, 1, true) + $id_column + array_slice($columns, 1, null, true);
		}
		
		// Insert Logo column at the beginning (after checkbox and ID)
		$logo_column = ['logo' => __('Logo', 'affiliate-product-showcase')];
		$columns = array_slice($columns, 0, 2, true) + $logo_column + array_slice($columns, 2, null, true);
		
		// Add other custom columns
		// Note: Ribbon uses WordPress native taxonomy column (taxonomy-aps_ribbon)
		// Custom styling applied via CSS in affiliate-product-showcase.css
		$columns['price'] = __('Price', 'affiliate-product-showcase');
		$columns['featured'] = __('Featured', 'affiliate-product-showcase');
		$columns['status'] = __('Status', 'affiliate-product-showcase');
		
		return $columns;
	}
	
	/**
	 * Render custom column content
	 *
	 * @param string $column  Column name
	 * @param int    $post_id Post ID
	 * @return void
	 */
	public function renderCustomColumns(string $column, int $post_id): void {
		switch ($column) {
			case 'id':
				echo '<span class="aps-product-id">' . esc_html($post_id) . '</span>';
				break;
				
			case 'logo':
				$logo_value = get_post_meta($post_id, '_aps_logo', true);
				if ($logo_value) {
					// Check if it's an attachment ID or URL
					if (is_numeric($logo_value)) {
						$logo_url = wp_get_attachment_image_url($logo_value, 'thumbnail');
					} else {
						// It's a URL string
						$logo_url = $logo_value;
					}
					
					if ($logo_url) {
						echo '<div class="aps-logo-container">';
						echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" class="aps-product-logo">';
						echo '</div>';
					}
				}
				break;
				
			case 'price':
				$price = get_post_meta($post_id, '_aps_price', true);
				$currency = get_post_meta($post_id, '_aps_currency', true) ?: 'USD';
				$symbol = $this->getCurrencySymbol($currency);
				if ($price) {
					echo '<span class="aps-price">' . esc_html($symbol) . esc_html(number_format(floatval($price), 2)) . '</span>';
				}
				break;
				
			case 'featured':
				$featured = get_post_meta($post_id, '_aps_featured', true);
				echo $featured ? '<span class="aps-featured-star">★</span>' : '';
				break;
				
			case 'status':
				$post_status = get_post_status($post_id);
				$status_labels = [
					'publish' => __('Published', 'affiliate-product-showcase'),
					'draft' => __('Draft', 'affiliate-product-showcase'),
					'trash' => __('Trash', 'affiliate-product-showcase'),
					'pending' => __('Pending', 'affiliate-product-showcase'),
				];
				$label = $status_labels[$post_status] ?? ucfirst($post_status);
				$status_class = 'aps-product-status aps-product-status-' . $post_status;
				echo '<span class="' . esc_attr($status_class) . '">' . esc_html($label) . '</span>';
				break;
		}
	}
	
	/**
	 * Make custom columns sortable
	 *
	 * @param array $columns Existing sortable columns
	 * @return array Modified sortable columns
	 */
	public function makeColumnsSortable(array $columns): array {
		$columns['price'] = 'aps_price';
		$columns['featured'] = 'aps_featured';
		return $columns;
	}
	
	/**
	 * Set default sorting for products table
	 *
	 * @param WP_Query $query Current query
	 * @return void
	 */
	public function setDefaultSorting(\WP_Query $query): void {
		if (!is_admin() || !$query->is_main_query()) {
			return;
		}
		
		if (isset($_GET['post_type']) && $_GET['post_type'] === 'aps_product') {
			// Default sort by date descending
			if (!isset($_GET['orderby'])) {
				$query->set('orderby', 'date');
				$query->set('order', 'DESC');
			}
		}
	}
	
	/**
	 * Get currency symbol
	 *
	 * @param string $currency Currency code
	 * @return string Currency symbol
	 */
	private function getCurrencySymbol(string $currency): string {
		$symbols = [
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'CAD' => 'C$',
			'AUD' => 'A$',
		];
		return $symbols[$currency] ?? $currency;
	}

	/**
	 * Add custom submenus to Affiliate Products CPT
	 *
	 * Registers custom "Add Product" submenu under Affiliate Products.
	 * "All Products" uses WordPress native listing (automatically created).
	 * Called during admin_menu hook (priority 10) before reordering.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function addCustomSubmenus(): void {
		// NOTE: "All Products" uses WordPress native submenu (edit.php?post_type=aps_product)
		// which is automatically created for the custom post type
		// We don't add a custom one here to avoid duplicates

		// Add custom "Add Product" submenu to Affiliate Products CPT
		// Use 'manage_options' capability for admin access
		add_submenu_page(
			'edit.php?post_type=aps_product',
			__( 'Add Product', 'affiliate-product-showcase' ),
			__( 'Add Product', 'affiliate-product-showcase' ),
			'manage_options', // Use manage_options for admin access
			'add-product',
			[ $this, 'renderAddProductPage' ]
		);
		
		// Note: Using WordPress native Categories menu (auto-created for taxonomy)
		// No custom submenu needed - WordPress handles all category management
	}

	/**
	 * Reorder submenus under Affiliate Products CPT
	 * 
	 * Desired order: All Products, Add Product, Categories, Tags, Ribbons
	 * Uses WordPress native Categories/Tabs/Ribbons menus (auto-created for taxonomies).
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
		
		// 1. Keep "All Products" (edit.php?post_type=aps_product) - WordPress native listing
		foreach ( $existing_items as $index => $item ) {
			$slug = isset( $item[2] ) ? $item[2] : '';
			if ( $slug === 'edit.php?post_type=aps_product' ) {
				// Keep the native WordPress menu item as-is
				$reordered_items[] = $item;
				$used_indices[] = $index;
				break;
			}
		}
		
		// 2. Add custom "Add Product" menu (already registered)
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
    /**
     * Render add product page (custom WooCommerce-style editor)
     *
     * @since 1.0.0
     * @return void
     */
    public function renderAddProductPage(): void {
        global $post_id, $is_editing, $product_data;
        
        // Get product data if editing (before enqueue to have data available for localization)
        $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
        $is_editing = $post_id > 0;
        $product_data = [];
        
        if ( $is_editing ) {
            $post = get_post( $post_id );
            if ( $post && $post->post_type === 'aps_product' ) {
                $product_data = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'status' => $post->post_status,
                    'content' => $post->post_content,
                    'short_description' => $post->post_excerpt,
                    'logo' => get_post_meta( $post->ID, '_aps_logo', true ),
                    'brand_image' => get_post_meta( $post->ID, '_aps_brand_image', true ),
                    'affiliate_url' => get_post_meta( $post->ID, '_aps_affiliate_url', true ),
                    'button_name' => get_post_meta( $post->ID, '_aps_button_name', true ),
                    'regular_price' => get_post_meta( $post->ID, '_aps_price', true ),
                    'original_price' => get_post_meta( $post->ID, '_aps_original_price', true ),
                    'featured' => get_post_meta( $post->ID, '_aps_featured', true ) === '1',
                    'rating' => get_post_meta( $post->ID, '_aps_rating', true ),
                    'views' => get_post_meta( $post->ID, '_aps_views', true ),
                    'user_count' => get_post_meta( $post->ID, '_aps_user_count', true ),
                    'reviews' => get_post_meta( $post->ID, '_aps_reviews', true ),
                    'features' => json_decode( get_post_meta( $post->ID, '_aps_features', true ) ?: '[]', true ),
                    'categories' => wp_get_object_terms( $post->ID, 'aps_category', [ 'fields' => 'slugs' ] ),
                    'tags' => wp_get_object_terms( $post->ID, 'aps_tag', [ 'fields' => 'slugs' ] ),
                    'ribbons' => wp_get_object_terms( $post->ID, 'aps_ribbon', [ 'fields' => 'slugs' ] ),
                ];
            }
        }
        
        // Enqueue WordPress media library scripts
        wp_enqueue_media();
        
        // Enqueue admin add product styles
        wp_enqueue_style(
            'aps-admin-add-product',
            plugins_url( 'assets/css/affiliate-product-showcase.css', \AffiliateProductShowcase\Plugin\Constants::FILE ),
            [],
            \AffiliateProductShowcase\Plugin\Constants::VERSION
        );
        
        // Enqueue admin add product script
        wp_enqueue_script(
            'aps-admin-add-product',
            plugins_url( 'assets/js/admin-add-product.js', \AffiliateProductShowcase\Plugin\Constants::FILE ),
            ['jquery', 'media-editor'],
            \AffiliateProductShowcase\Plugin\Constants::VERSION,
            true
        );
        
        // Localize script with PHP data (now includes categories and ribbons)
        wp_localize_script('aps-admin-add-product', 'apsAddProductData', [
            'productData' => $product_data,
            'isEditing' => $is_editing,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aps_product_nonce')
        ]);
        
        include \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/add-product-page.php' );
    }

    /**
     * Render help page
     *
     * @since 1.0.0
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
            /* Logo column sizing - auto-resize to fit in table */
            .aps-product-logo {
                width: 50px;
                height: 50px;
                object-fit: contain;
                object-position: center;
            }
            .aps-logo-container {
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            /* Table column width adjustment for logo */
            .wp-list-table.posts .column-logo {
                width: 80px;
                vertical-align: middle;
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
	 * Get products URL (under Affiliate Products CPT menu)
	 *
	 * Uses native WordPress URL for the CPT listing.
	 * Our custom ProductsPage will be rendered via load-edit.php hook.
	 *
	 * @return string
	 */
	public static function getProductsUrl(): string {
		return admin_url( 'edit.php?post_type=aps_product' );
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
