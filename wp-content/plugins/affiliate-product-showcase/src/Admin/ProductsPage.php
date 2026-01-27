<?php
/**
 * Products Page
 *
 * Handles products listing page with WP_List_Table.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * ProductsPage class
 *
 * Renders products listing page. Menu registration is handled by Menu.php.
 *
 * @since 1.0.0
 */
final class ProductsPage {
    /**
     * Products table instance
     *
     * @since 1.0.0
     * @var ProductsTable|null
     */
    private ?ProductsTable $products_table = null;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        // No dependencies needed - table handles data retrieval
    }

    /**
     * Initialize products page
     *
     * NOTE: Menu registration is handled by Menu.php
     * This class only handles page rendering
     *
     * @since 1.0.0
     * @return void
     */
    public function init(): void {
        // No menu registration needed - handled by Menu.php
    }

    /**
     * Get status counts
     *
     * Returns the count of products for each status.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_status_counts(): array {
        $counts = [
            'all'       => 0,
            'published' => 0,
            'draft'     => 0,
            'trash'     => 0,
        ];

        // Count published products
        $published = wp_count_posts('aps_product');
        if ($published) {
            $counts['published'] = (int) $published->publish;
            $counts['draft']     = (int) $published->draft;
            $counts['trash']     = (int) $published->trash;
            $counts['all']       = $counts['published'] + $counts['draft'];
        }

        return $counts;
    }

    /**
     * Render products page
     *
     * Called by Menu::renderProductsPage()
     *
     * @since 1.0.0
     * @return void
     */
    public function render_page(): void {
        // Include WordPress list table class if not already loaded
        if (!class_exists('WP_List_Table')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        // Include our table class if not already loaded
        if (!class_exists('AffiliateProductShowcase\\Admin\\ProductsTable')) {
            require_once Constants::get_plugin_path() . 'src/Admin/ProductsTable.php';
        }

        // Get or create table instance
        if ($this->products_table === null) {
            $this->products_table = new ProductsTable();
        }

        // Prepare items for display
        $this->products_table->prepare_items();

        // Include template
        include Constants::dirPath() . 'src/Admin/partials/products-page.php';
    }
}