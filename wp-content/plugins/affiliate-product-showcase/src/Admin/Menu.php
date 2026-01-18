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
    public const MENU_SLUG = 'affiliate-product-showcase';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'addMenuPages' ] );
        add_action( 'admin_head', [ $this, 'addMenuIcons' ] );
    }

    /**
     * Add menu pages
     *
     * @return void
     */
    public function addMenuPages(): void {
        // Main menu page
        add_menu_page(
            __( 'Affiliate Products', 'affiliate-product-showcase' ),
            __( 'Affiliate Products', 'affiliate-product-showcase' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'renderDashboardPage' ],
            'dashicons-products',
            30
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

        // Products submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'All Products', 'affiliate-product-showcase' ),
            __( 'All Products', 'affiliate-product-showcase' ),
            'edit_affiliate_products',
            'edit.php?post_type=aps_product'
        );

        // Add Product submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Add Product', 'affiliate-product-showcase' ),
            __( 'Add Product', 'affiliate-product-showcase' ),
            'edit_affiliate_products',
            'post-new.php?post_type=aps_product'
        );

        // Analytics submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Analytics', 'affiliate-product-showcase' ),
            __( 'Analytics', 'affiliate-product-showcase' ),
            'manage_options',
            self::MENU_SLUG . '-analytics',
            [ $this, 'renderAnalyticsPage' ]
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
        include AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_DIR . 'src/Admin/partials/dashboard-page.php';
    }

    /**
     * Render analytics page
     *
     * @return void
     */
    public function renderAnalyticsPage(): void {
        include AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_DIR . 'src/Admin/partials/analytics-page.php';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function renderSettingsPage(): void {
        include AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_DIR . 'src/Admin/partials/settings-page.php';
    }

    /**
     * Render help page
     *
     * @return void
     */
    public function renderHelpPage(): void {
        include AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_DIR . 'src/Admin/partials/help-page.php';
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
     * Get analytics URL
     *
     * @return string
     */
    public static function getAnalyticsUrl(): string {
        return self::getPageUrl( 'analytics' );
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
}
