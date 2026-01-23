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
        add_action( 'admin_print_styles', [ $this, 'printInlineStyles' ] );
        add_action( 'admin_footer', [ $this, 'printRedirectScript' ] );
    }

    /**
     * Enqueue admin styles
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueueStyles( string $hook ): void {
        // Only load on our plugin pages
        if ( ! $this->isPluginPage( $hook ) ) {
            return;
        }

        // Main admin CSS
        wp_enqueue_style(
            'affiliate-product-showcase-admin',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            self::VERSION
        );

        // Dashboard styles
        if ( $this->isDashboardPage( $hook ) ) {
            wp_enqueue_style(
                'affiliate-product-showcase-dashboard',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/dashboard.css',
                [],
                self::VERSION
            );
        }

        // Analytics styles
        if ( $this->isAnalyticsPage( $hook ) ) {
            wp_enqueue_style(
                'affiliate-product-showcase-analytics',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/analytics.css',
                [],
                self::VERSION
            );
        }

        // Settings styles
        if ( $this->isSettingsPage( $hook ) ) {
            wp_enqueue_style(
                'affiliate-product-showcase-settings',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/settings.css',
                [],
                self::VERSION
            );
        }

        // Product edit styles
        if ( $this->isProductEditPage( $hook ) ) {
            wp_enqueue_style(
                'affiliate-product-showcase-product-edit',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/product-edit.css',
                [],
                self::VERSION
            );
        }

        // Products list table styles
        if ( $hook === 'edit-aps_product' ) {
            wp_enqueue_style(
                'affiliate-product-showcase-admin-table',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/admin-table.css',
                [],
                self::VERSION
            );

            // Product table UI styles (custom filters, counts, etc.)
            wp_enqueue_style(
                'affiliate-product-showcase-product-table-ui',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/product-table-ui.css',
                [],
                self::VERSION
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

        // Main admin JS
        wp_enqueue_script(
            'affiliate-product-showcase-admin',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/admin.js',
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
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/dashboard.js',
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
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/analytics.js',
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
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/settings.js',
                [ 'jquery', 'wp-util' ],
                self::VERSION,
                true
            );
        }

        // Products list page scripts (enhanced "All Products" page)
        if ( $this->isProductsListPage( $hook ) ) {
            wp_enqueue_script(
                'affiliate-product-showcase-admin-products-enhancer',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/admin-products-enhancer.js',
                [ 'jquery' ],
                self::VERSION,
                true
            );

            // Product table UI scripts (AJAX filtering, sorting, etc.)
            wp_enqueue_script(
                'affiliate-product-showcase-product-table-ui',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/product-table-ui.js',
                [ 'jquery' ],
                self::VERSION,
                true
            );

            wp_localize_script(
                'affiliate-product-showcase-admin-products-enhancer',
                'affiliateProductShowcaseAdminEnhancer',
                [
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'affiliate-product-showcase-admin-bulk-action' ),
                    'restUrl' => rest_url( 'affiliate-product-showcase/v1/' ),
                    'restNonce' => wp_create_nonce( 'wp_rest' ),
                    'strings' => [
                        'confirmDelete' => __( 'Are you sure you want to delete this item?', 'affiliate-product-showcase' ),
                        'pleaseSelect' => __( 'Please select at least one product.', 'affiliate-product-showcase' ),
                        'selectAction' => __( 'Please select an action.', 'affiliate-product-showcase' ),
                        'processing' => __( 'Processing...', 'affiliate-product-showcase' ),
                        'productDeleted' => __( 'Product deleted successfully.', 'affiliate-product-showcase' ),
                        'deleteFailed' => __( 'Failed to delete product.', 'affiliate-product-showcase' ),
                        'actionFailed' => __( 'Action failed.', 'affiliate-product-showcase' ),
                        'requestFailed' => __( 'Request failed: ', 'affiliate-product-showcase' ),
                        'dismissNotice' => __( 'Dismiss this notice.', 'affiliate-product-showcase' ),
                    ],
                ]
            );

            // Localize product-table-ui.js
            wp_localize_script(
                'affiliate-product-showcase-product-table-ui',
                'apsProductTableUI',
                [
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'aps_table_actions' ),
                    'restUrl' => rest_url( 'affiliate-product-showcase/v1/' ),
                    'restNonce' => wp_create_nonce( 'wp_rest' ),
                    'strings' => [
                        'confirmBulkUpload' => __( 'Are you sure you want to bulk upload products?', 'affiliate-product-showcase' ),
                        'confirmCheckLinks' => __( 'Are you sure you want to check all product links?', 'affiliate-product-showcase' ),
                        'processing' => __( 'Processing...', 'affiliate-product-showcase' ),
                        'noProducts' => __( 'No products found.', 'affiliate-product-showcase' ),
                        'selectAction' => __( 'Please select an action.', 'affiliate-product-showcase' ),
                    ],
                ]
            );
        }

        // Product edit scripts
        if ( $this->isProductEditPage( $hook ) ) {
            wp_enqueue_script(
                'affiliate-product-showcase-product-edit',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/product-edit.js',
                [ 'jquery', 'wp-util', 'media-upload' ],
                self::VERSION,
                true
            );

            // Media uploader
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
     * Print inline styles
     *
     * @return void
     */
    public function printInlineStyles(): void {
        ?>
        <style>
            .affiliate-product-showcase-wrap {
                max-width: 1200px;
                margin: 20px;
            }
            .affiliate-product-showcase-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
                padding: 20px;
                margin-bottom: 20px;
            }
            .affiliate-product-showcase-card h2 {
                margin-top: 0;
            }
            .affiliate-product-showcase-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .affiliate-product-showcase-stat {
                background: #f6f7f7;
                padding: 20px;
                border-radius: 4px;
            }
            .affiliate-product-showcase-stat-value {
                font-size: 2em;
                font-weight: bold;
                color: #2271b1;
            }
            .affiliate-product-showcase-stat-label {
                color: #646970;
                margin-top: 5px;
            }
            
            /* WooCommerce-Style Product Form */
            .aps-product-form {
                display: flex;
                flex-direction: column;
                gap: 24px;
            }
            
            /* Form Sections */
            .aps-form-section {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
                overflow: hidden;
            }
            
            .aps-section-title {
                margin: 0;
                padding: 12px 16px;
                background: #f6f7f7;
                border-bottom: 1px solid #dcdcde;
                font-size: 14px;
                font-weight: 600;
                color: #1d2327;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .aps-section-title .dashicons {
                width: 18px;
                height: 18px;
                font-size: 18px;
                color: #646970;
            }
            
            .aps-section-content {
                padding: 20px;
            }
            
            /* Grid Layout for 2-column sections */
            .aps-grid-2 {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            
            /* Field Styles */
            .aps-field {
                margin-bottom: 16px;
            }
            
            .aps-field:last-child {
                margin-bottom: 0;
            }
            
            .aps-field label {
                display: block;
                margin-bottom: 6px;
                font-size: 14px;
                font-weight: 500;
                color: #1d2327;
                line-height: 1.4;
            }
            
            .aps-field-tip {
                display: block;
                font-size: 12px;
                font-weight: 400;
                color: #646970;
                margin-top: 4px;
                font-style: italic;
            }
            
            .aps-field-required {
                color: #d63638;
                font-weight: 600;
            }
            
            /* Input Styles */
            .aps-input {
                width: 100%;
                padding: 8px 12px;
                font-size: 14px;
                line-height: 1.5;
                color: #1d2327;
                background: #fff;
                border: 1px solid #8c8f94;
                border-radius: 3px;
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
                transition: all .2s;
            }
            
            .aps-input:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 3px rgba(34, 113, 177, .1);
                outline: none;
            }
            
            .aps-input-required:focus {
                border-color: #d63638;
                box-shadow: 0 0 0 3px rgba(214, 99, 56, .1);
            }
            
            /* Input Groups (for currency, percentage, etc.) */
            .aps-input-group {
                display: flex;
                align-items: stretch;
                width: 100%;
            }
            
            .aps-input-group .aps-input {
                flex: 1;
                border-top-left-radius: 0;
                border-bottom-left-radius: 0;
            }
            
            .aps-input-group .aps-input:focus {
                z-index: 1;
            }
            
            .aps-input-prefix,
            .aps-input-suffix {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 12px;
                background: #f6f7f7;
                border: 1px solid #8c8f94;
                color: #646970;
                font-size: 13px;
                font-weight: 500;
                min-width: 50px;
            }
            
            .aps-input-prefix {
                border-top-left-radius: 3px;
                border-bottom-left-radius: 3px;
                border-right: none;
            }
            
            .aps-input-suffix {
                border-top-right-radius: 3px;
                border-bottom-right-radius: 3px;
                border-left: none;
            }
            
            /* Select Styles */
            .aps-select {
                width: 100%;
                padding: 8px 36px 8px 12px;
                font-size: 14px;
                line-height: 1.5;
                color: #1d2327;
                background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"><path fill="%23646970" d="M2 4l4 4 4-4"/></svg>') no-repeat right 12px center;
                border: 1px solid #8c8f94;
                border-radius: 3px;
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
                appearance: none;
                cursor: pointer;
                transition: all .2s;
            }
            
            .aps-select:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 3px rgba(34, 113, 177, .1);
                outline: none;
            }
            
            /* Checkbox Styles */
            .aps-field-checkbox label {
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                user-select: none;
            }
            
            .aps-field-checkbox input[type="checkbox"] {
                width: 18px;
                height: 18px;
                margin: 0;
                cursor: pointer;
            }
            
            /* Textarea Styles */
            .aps-input-textarea {
                min-height: 80px;
                resize: vertical;
                font-family: inherit;
            }
            
            /* Dimensions Grid */
            .aps-dimensions {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 16px;
                margin-top: 16px;
            }
            
            /* Section-specific styles */
            .aps-dimensions .aps-field {
                margin-bottom: 0;
            }
            
            /* Responsive */
            @media (max-width: 782px) {
                .aps-grid-2 {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }
                
                .aps-dimensions {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .aps-form-section {
                    margin-bottom: 16px;
                }
            }
            
            @media (max-width: 480px) {
                .aps-dimensions {
                    grid-template-columns: 1fr;
                }
                
                .aps-input-group {
                    flex-direction: column;
                }
                
                .aps-input-group .aps-input {
                    border-radius: 3px;
                    border-top-left-radius: 3px;
                    border-bottom-left-radius: 3px;
                }
                
                .aps-input-prefix,
                .aps-input-suffix {
                    border-radius: 3px;
                    width: 100%;
                    padding: 6px 12px;
                    border: 1px solid #8c8f94;
                    background: #fff;
                }
            }
            
            /* Print styles */
            @media print {
                .aps-form-section {
                    page-break-inside: avoid;
                    box-shadow: none;
                }
            }
        </style>
        <?php
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
            'affiliate-product-showcase-analytics',
            'affiliate-product-showcase-settings',
            'affiliate-product-showcase-help',
            'toplevel_page_affiliate-product-showcase',
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
        return strpos( $hook, 'affiliate-product-showcase-settings' ) !== false;
    }

    /**
     * Check if current page is product edit
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function isProductEditPage( string $hook ): bool {
        return $hook === 'post.php'
            || $hook === 'post-new.php';
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
                    $button.attr('href', '<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-add-product')); ?>');
                }
            });
            
            // Also redirect top "Add New" link in admin menu
            $('#menu-posts-aps_product .wp-submenu a').each(function() {
                const $link = $(this);
                const href = $link.attr('href');
                if (href && href.includes('post-new.php?post_type=aps_product')) {
                    $link.attr('href', '<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-add-product')); ?>');
                }
            });
        });
        </script>
        <?php
    }
}
