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
    }

    /**
     * Enqueue admin scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueueScripts( string $hook ): void {
        // Only load on our plugin pages
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
            wp_enqueue_script(
                'affiliate-product-showcase-dashboard',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/dashboard.js',
                [ 'jquery', 'wp-util' ],
                self::VERSION,
                true
            );
        }

        // Analytics scripts
        if ( $this->isAnalyticsPage( $hook ) ) {
            wp_enqueue_script(
                'affiliate-product-showcase-analytics',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/analytics.js',
                [ 'jquery', 'wp-util', 'chart.js' ],
                self::VERSION,
                true
            );
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
     * @param string $hook Current page hook
     * @return bool
     */
    private function isDashboardPage( string $hook ): bool {
        return strpos( $hook, 'affiliate-product-showcase' ) !== false
            || strpos( $hook, 'toplevel_page_affiliate-product-showcase' ) !== false;
    }

    /**
     * Check if current page is analytics
     *
     * @param string $hook Current page hook
     * @return bool
     */
    private function isAnalyticsPage( string $hook ): bool {
        return strpos( $hook, 'affiliate-product-showcase-analytics' ) !== false;
    }

    /**
     * Check if current page is settings
     *
     * @param string $hook Current page hook
     * @return bool
     */
    private function isSettingsPage( string $hook ): bool {
        return strpos( $hook, 'affiliate-product-showcase-settings' ) !== false;
    }

    /**
     * Check if current page is product edit
     *
     * @param string $hook Current page hook
     * @return bool
     */
    private function isProductEditPage( string $hook ): bool {
        return $hook === 'post.php'
            || $hook === 'post-new.php';
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
                'confirmDelete'   => __( 'Are you sure you want to delete this item?', 'affiliate-product-showcase' ),
                'saving'         => __( 'Saving...', 'affiliate-product-showcase' ),
                'saved'          => __( 'Saved successfully!', 'affiliate-product-showcase' ),
                'error'          => __( 'An error occurred. Please try again.', 'affiliate-product-showcase' ),
                'uploadImage'    => __( 'Upload Image', 'affiliate-product-showcase' ),
                'selectImage'    => __( 'Select Image', 'affiliate-product-showcase' ),
                'removeImage'    => __( 'Remove Image', 'affiliate-product-showcase' ),
            ],
            'settings'  => [
                'restBase' => 'affiliate-product-showcase/v1',
            ],
        ];
    }
}
