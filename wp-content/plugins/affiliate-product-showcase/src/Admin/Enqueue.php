<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Admin Enqueue
 *
 * Handles enqueuing of scripts and styles for admin area.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class Enqueue {

    /**
     * Plugin version
     *
     * @var string
     */
    private const VERSION = '1.0.0';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueStyles' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'debugHook' ], 1 );
        add_action( 'admin_footer', [ $this, 'printRedirectScript' ] );
    }
    
    /**
     * Debug hook to log current admin page
     * 
     * @param string $hook Current admin page hook
     * @return void
     */
    public function debugHook( string $hook ): void {
        if ( strpos( $hook, 'add-product' ) !== false || strpos( $hook, 'aps_product' ) !== false ) {
            error_log( 'APS Debug - Current admin hook: ' . $hook );
        }
    }

    /**
     * Enqueue admin styles
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueueStyles( string $hook ): void {
        wp_enqueue_style(
            'affiliate-product-showcase-tokens',
            \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/tokens.css' ),
            [],
            self::VERSION
        );

        // Load products page CSS regardless of plugin page check
        global $typenow;
        if ( $hook === 'edit.php' && $typenow === 'aps_product' ) {
            // Table filters CSS
            wp_enqueue_style(
                'affiliate-product-showcase-table-filters',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/admin-table-filters.css' ),
                [],
                self::VERSION
            );
            
            // Products table CSS for custom columns (Logo, Ribbon, Status, etc.)
            wp_enqueue_style(
                'affiliate-product-showcase-products',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/affiliate-product-showcase.css' ),
                [ 'affiliate-product-showcase-tokens' ],
                self::VERSION
            );
        }
        
        // Load settings CSS on settings page (before plugin page check)
        if ( $this->isSettingsPage( $hook ) ) {
            wp_enqueue_style(
                'affiliate-product-showcase-settings',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/settings.css' ),
                [],
                self::VERSION . '.' . time() // Cache buster for development
            );
        }
        
        // Only load other styles on our plugin pages
        if ( ! $this->isPluginPage( $hook ) ) {
            return;
        }

        // Main admin CSS
        wp_enqueue_style(
            'affiliate-product-showcase-admin',
            \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/admin.css' ),
            [ 'affiliate-product-showcase-tokens' ],
            self::VERSION
        );

        // Form styles (WooCommerce-style product form)
        wp_enqueue_style(
            'affiliate-product-showcase-form',
            \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/affiliate-product-showcase.css' ),
            [ 'affiliate-product-showcase-tokens' ],
            self::VERSION
        );

        // Dashboard styles
        if ( $this->isDashboardPage( $hook ) ) {
            wp_enqueue_style(
                'affiliate-product-showcase-dashboard',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/dashboard.css' ),
                [],
                self::VERSION
            );
        }

        // Analytics styles
        if ( $this->isAnalyticsPage( $hook ) ) {
            wp_enqueue_style(
                'affiliate-product-showcase-analytics',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/analytics.css' ),
                [],
                self::VERSION
            );
        }

        // Settings styles already enqueued earlier

        // Note: Product edit styles are inline in add-product-page.php
        // No separate CSS file needed for add-product page

        // Custom products page
        if ( $hook === 'aps_product_page_aps-products' ) {
            wp_enqueue_style(
                'affiliate-product-showcase-products',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/css/affiliate-product-showcase.css' ),
                [ 'affiliate-product-showcase-tokens' ],
                self::VERSION
            );
            
            wp_enqueue_script(
                'affiliate-product-showcase-products',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/js/admin-products.js' ),
                [ 'jquery' ],
                self::VERSION,
                true
            );
            
            wp_localize_script(
                'affiliate-product-showcase-products',
                'apsProductsData',
                $this->getProductsPageScriptData()
            );
        }
    }

    /**
     * Enqueue admin scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueueScripts( string $hook ): void {
        // Load on our plugin pages
        if ( ! $this->isPluginPage( $hook ) ) {
            return;
        }

        // Ensure jQuery is available in admin pages we control
        wp_enqueue_script( 'jquery' );

        // Main admin JS
        wp_enqueue_script(
            'affiliate-product-showcase-admin',
            \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/js/admin.js' ),
            [ 'jquery' ],
            self::VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'affiliate-product-showcase-admin',
            'affiliateProductShowcaseAdmin',
            $this->getScriptData()
        );

        // Dashboard scripts
        if ( $this->isDashboardPage( $hook ) ) {
            wp_register_script(
                'affiliate-product-showcase-dashboard',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/js/dashboard.js' ),
                [ 'jquery', 'wp-util' ],
                self::VERSION,
                true
            );
            
            // Add defer attribute for non-critical script (WordPress 6.3+)
            wp_script_add_data( 'affiliate-product-showcase-dashboard', 'defer', true );
            
            wp_enqueue_script( 'affiliate-product-showcase-dashboard' );
        }

        // Analytics scripts
        if ( $this->isAnalyticsPage( $hook ) ) {
            wp_register_script(
                'affiliate-product-showcase-analytics',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/js/analytics.js' ),
                [ 'jquery', 'wp-util', 'chart.js' ],
                self::VERSION,
                true
            );
            
            // Add defer attribute for non-critical script (WordPress 6.3+)
            wp_script_add_data( 'affiliate-product-showcase-analytics', 'defer', true );
            
            wp_enqueue_script( 'affiliate-product-showcase-analytics' );
        }

        // Settings scripts
        if ( $this->isSettingsPage( $hook ) ) {
            wp_enqueue_script(
                'affiliate-product-showcase-settings',
                \AffiliateProductShowcase\Plugin\Constants::assetUrl( 'assets/js/settings.js' ),
                [ 'jquery', 'wp-util' ],
                self::VERSION,
                true
            );
        }

        // Products list page scripts - WordPress default table only
        if ( $this->isProductsListPage( $hook ) ) {
            // No custom scripts needed - using native WordPress UI
        }

        // Custom products page scripts
        if ( $this->isCustomProductsPage( $hook ) ) {
            // Scripts already enqueued in enqueueScripts
        }

        // Product edit scripts
        if ( $this->isProductEditPage( $hook ) ) {
            // Media uploader - required for wp.media() functionality
            wp_enqueue_media();
        }

        // Color picker
        if ( $this->isSettingsPage( $hook ) ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        }

        // Select2 for dropdowns
        if ( $this->isProductEditPage( $hook ) ) {
            wp_enqueue_style( 'select2' );
            wp_enqueue_script( 'select2' );
        }
    }


    /**
     * Check if current page is a plugin page
     *
     * @param string $hook Current page hook
     * @return bool
     */
    private function isPluginPage( string $hook ): bool {
        $plugin_pages = [
            'affiliate-product-showcase',
            'affiliate-manager',
            'affiliate-manager-settings',
            'affiliate-manager-help',
            'affiliate-product-showcase-analytics',
            'affiliate-product-showcase-settings',
            'affiliate-product-showcase-help',
            'toplevel_page_affiliate-product-showcase',
            'toplevel_page_affiliate-manager',
            // Add Product page hook (under aps_product)
            'aps_product_page_add-product',
        ];

        foreach ( $plugin_pages as $page ) {
            if ( strpos( $hook, $page ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if current page is dashboard
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function isDashboardPage( string $hook ): bool {
        return strpos( $hook, 'affiliate-product-showcase' ) !== false
            || strpos( $hook, 'toplevel_page_affiliate-product-showcase' ) !== false;
    }

    /**
     * Check if current page is analytics
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function isAnalyticsPage( string $hook ): bool {
        return strpos( $hook, 'affiliate-product-showcase-analytics' ) !== false;
    }

    /**
     * Check if current page is settings
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function isSettingsPage( string $hook ): bool {
        return strpos( $hook, 'affiliate-manager-settings' ) !== false;
    }

    /**
     * Check if current page is product edit
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function isProductEditPage( string $hook ): bool {
        return $hook === 'post.php'
            || $hook === 'post-new.php'
            || $hook === 'aps_product_page_add-product';
    }

    /**
     * Check if current page is products list (for enhanced "All Products")
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function isProductsListPage( string $hook ): bool {
        return $hook === 'edit-aps_product';
    }

    /**
     * Check if current page is custom products page
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function isCustomProductsPage( string $hook ): bool {
        return $hook === 'aps_product_page_aps-products';
    }

    /**
     * Get script data for localization
     *
     * @return array
     */
    private function getScriptData(): array {
        return [
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'affiliate-product-showcase-admin' ),
            'restUrl'   => rest_url( 'affiliate-product-showcase/v1/' ),
            'restNonce' => wp_create_nonce( 'wp_rest' ),
            'strings'   => [
                'confirmDelete' => __( 'Are you sure you want to delete this item?', 'affiliate-product-showcase' ),
                'saving'       => __( 'Saving...', 'affiliate-product-showcase' ),
                'saved'        => __( 'Saved successfully!', 'affiliate-product-showcase' ),
                'error'         => __( 'An error occurred. Please try again.', 'affiliate-product-showcase' ),
                'uploadImage'   => __( 'Upload Image', 'affiliate-product-showcase' ),
                'selectImage'   => __( 'Select Image', 'affiliate-product-showcase' ),
                'removeImage'   => __( 'Remove Image', 'affiliate-product-showcase' ),
            ],
            'settings'  => [
                'restBase' => 'affiliate-product-showcase/v1',
            ],
            'products'  => [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aps_products_nonce'),
                'strings' => [
                    'bulkActionRequired' => __('Please select an action.', 'affiliate-product-showcase'),
                    'noItemsSelected' => __('Please select at least one product.', 'affiliate-product-showcase'),
                    'bulkDeleteConfirm' => __('Are you sure you want to move %d products to trash?', 'affiliate-product-showcase'),
                    'bulkDeleteSuccess' => __('%d products moved to trash.', 'affiliate-product-showcase'),
                    'deleteConfirm' => __('Are you sure you want to move this product to trash?', 'affiliate-product-showcase'),
                    'deleteSuccess' => __('Product moved to trash.', 'affiliate-product-showcase'),
                    'saveError' => __('Failed to save changes. Please try again.', 'affiliate-product-showcase'),
                    'saveSuccess' => __('Changes saved successfully.', 'affiliate-product-showcase'),
                    'validationError' => __('Please fix the errors before saving.', 'affiliate-product-showcase'),
                ],
            ],
        ];
    }

    /**
     * Get script data for products page
     *
     * @return array
     */
    private function getProductsPageScriptData(): array {
        return [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aps_products_nonce'),
            'restUrl' => rest_url('affiliate-product-showcase/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'strings' => [
                'bulkActionRequired' => __('Please select an action.', 'affiliate-product-showcase'),
                'noItemsSelected' => __('Please select at least one product.', 'affiliate-product-showcase'),
                'bulkDeleteConfirm' => __('Are you sure you want to move %d products to trash?', 'affiliate-product-showcase'),
                'bulkDeleteSuccess' => __('%d products moved to trash.', 'affiliate-product-showcase'),
                'deleteConfirm' => __('Are you sure you want to move this product to trash?', 'affiliate-product-showcase'),
                'deleteSuccess' => __('Product moved to trash.', 'affiliate-product-showcase'),
                'saveError' => __('Failed to save changes. Please try again.', 'affiliate-product-showcase'),
                'saveSuccess' => __('Changes saved successfully.', 'affiliate-product-showcase'),
                'validationError' => __('Please fix the errors before saving.', 'affiliate-product-showcase'),
            ],
        ];
    }
    
    /**
     * Print inline JavaScript to redirect "Add New" button
     *
     * @return void
     */
    public function printRedirectScript(): void {
        global $pagenow;
        
        // Only on products list page
        if ( $pagenow !== 'edit.php' || ! isset( $_GET['post_type'] ) || $_GET['post_type'] !== 'aps_product' ) {
            return;
        }
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Redirect "Add New" button to custom WooCommerce-style page
            $('.page-title-action').each(function() {
                const $button = $(this);
                const href = $button.attr('href');
                if (href && href.includes('post-new.php?post_type=aps_product')) {
                    $button.attr('href', '<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>');
                }
            });
            
            // Also redirect top "Add New" link in admin menu
            $('#menu-posts-aps_product .wp-submenu a').each(function() {
                const $link = $(this);
                const href = $link.attr('href');
                if (href && href.includes('post-new.php?post_type=aps_product')) {
                    $link.attr('href', '<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>');
                }
            });
        });
        </script>
        <?php
    }
}
