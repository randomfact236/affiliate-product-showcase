<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Product Table UI
 *
 * Manages UI elements for products list table:
 * - Action buttons (Add New Product, Trash, Bulk Upload, Check Links)
 * - Status counts (All, Published, Draft, Trash)
 * - Enhanced filters (Search, Categories, Sort, Featured toggle, Clear filters)
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class ProductTableUI {

    /**
     * Constructor
     */
    public function __construct() {
        // Add UI elements above product list table
        add_action( 'admin_notices', [ $this, 'renderProductTableUI' ], 5 );
        
        // Add custom styles for UI elements
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueStyles' ] );
    }

    /**
     * Render product table UI elements
     *
     * Renders action buttons, status counts, and filters
     * as shown in backend-all-product-design-diagram.md
     *
     * @return void
     */
    public function renderProductTableUI(): void {
        // Only show on products list page
        if ( ! $this->isProductsPage() ) {
            return;
        }

        $this->renderActionButtons();
        $this->renderStatusCounts();
        $this->renderFilters();
    }

    /**
     * Check if current page is products list
     *
     * @return bool
     */
    private function isProductsPage(): bool {
        return isset( $_GET['post_type'] ) && 
               $_GET['post_type'] === 'aps_product' && 
               ! isset( $_GET['page'] );
    }

    /**
     * Render action buttons section
     *
     * Buttons: Add New Product, Trash, Bulk Upload, Check Links
     *
     * @return void
     */
    private function renderActionButtons(): void {
        $add_product_url = admin_url( 'edit.php?post_type=aps_product&page=add-product' );
        $trash_url = admin_url( 'edit.php?post_type=aps_product&post_status=trash' );
        
        ?>
        <div class="aps-product-table-actions">
            <h2 class="aps-page-title"><?php esc_html_e( 'Manage Products', 'affiliate-product-showcase' ); ?></h2>
            <p class="aps-page-description"><?php esc_html_e( 'Quick overview of your catalog with actions, filters, and bulk selection.', 'affiliate-product-showcase' ); ?></p>
            
            <div class="aps-action-buttons">
                <a href="<?php echo esc_url( $add_product_url ); ?>" class="aps-btn aps-btn-primary">
                    <span class="dashicons dashicons-plus"></span>
                    <?php esc_html_e( 'Add New Product', 'affiliate-product-showcase' ); ?>
                </a>
                
                <a href="<?php echo esc_url( $trash_url ); ?>" class="aps-btn aps-btn-secondary">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e( 'Trash', 'affiliate-product-showcase' ); ?>
                </a>
                
                <button type="button" class="aps-btn aps-btn-secondary" onclick="apsBulkUploadProducts()">
                    <span class="dashicons dashicons-upload"></span>
                    <?php esc_html_e( 'Bulk Upload', 'affiliate-product-showcase' ); ?>
                </button>
                
                <button type="button" class="aps-btn aps-btn-secondary" onclick="apsCheckProductLinks()">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php esc_html_e( 'Check Links', 'affiliate-product-showcase' ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render status counts section
     *
     * Shows: All, Published, Draft, Trash counts
     *
     * @return void
     */
    private function renderStatusCounts(): void {
        $count_all = wp_count_posts( 'aps_product' );
        $total = array_sum( (array) $count_all );
        
        $counts = [
            'all' => [
                'label' => __( 'All', 'affiliate-product-showcase' ),
                'count' => $total,
                'url' => admin_url( 'edit.php?post_type=aps_product' ),
                'class' => 'aps-count-all',
            ],
            'publish' => [
                'label' => __( 'Published', 'affiliate-product-showcase' ),
                'count' => $count_all->publish ?? 0,
                'url' => admin_url( 'edit.php?post_type=aps_product&post_status=publish' ),
                'class' => 'aps-count-published',
            ],
            'draft' => [
                'label' => __( 'Draft', 'affiliate-product-showcase' ),
                'count' => $count_all->draft ?? 0,
                'url' => admin_url( 'edit.php?post_type=aps_product&post_status=draft' ),
                'class' => 'aps-count-draft',
            ],
            'trash' => [
                'label' => __( 'Trash', 'affiliate-product-showcase' ),
                'count' => $count_all->trash ?? 0,
                'url' => admin_url( 'edit.php?post_type=aps_product&post_status=trash' ),
                'class' => 'aps-count-trash',
            ],
        ];

        $current_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'all';

        ?>
        <div class="aps-product-counts">
            <?php foreach ( $counts as $status => $data ): ?>
                <a href="<?php echo esc_url( $data['url'] ); ?>" 
                   class="aps-count-item <?php echo esc_attr( $data['class'] ); ?> <?php echo $current_status === $status ? 'active' : ''; ?>">
                    <span class="aps-count-number"><?php echo esc_html( $data['count'] ); ?></span>
                    <span class="aps-count-label"><?php echo esc_html( $data['label'] ); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render filters section
     *
     * Filters: Select action, Search, Categories dropdown, Sort dropdown, 
     * Show Featured toggle, Clear filters
     *
     * @return void
     */
    private function renderFilters(): void {
        $categories = get_terms( [
            'taxonomy' => 'aps_category',
            'hide_empty' => false,
        ] );

        $featured_filter = isset( $_GET['featured_filter'] ) ? $_GET['featured_filter'] : '';
        $sort_order = isset( $_GET['order'] ) ? $_GET['order'] : 'desc';
        
        ?>
        <div class="aps-product-filters">
            <!-- Select Action Dropdown -->
            <div class="aps-filter-group">
                <label for="aps_bulk_action" class="screen-reader-text"><?php esc_html_e( 'Select action', 'affiliate-product-showcase' ); ?></label>
                <select name="aps_bulk_action" id="aps_bulk_action" class="aps-filter-select">
                    <option value=""><?php esc_html_e( 'Select action', 'affiliate-product-showcase' ); ?></option>
                    <option value="set_in_stock"><?php esc_html_e( 'Set In Stock', 'affiliate-product-showcase' ); ?></option>
                    <option value="set_out_of_stock"><?php esc_html_e( 'Set Out of Stock', 'affiliate-product-showcase' ); ?></option>
                    <option value="set_featured"><?php esc_html_e( 'Set Featured', 'affiliate-product-showcase' ); ?></option>
                    <option value="unset_featured"><?php esc_html_e( 'Unset Featured', 'affiliate-product-showcase' ); ?></option>
                    <option value="reset_clicks"><?php esc_html_e( 'Reset Clicks', 'affiliate-product-showcase' ); ?></option>
                    <option value="export_csv"><?php esc_html_e( 'Export to CSV', 'affiliate-product-showcase' ); ?></option>
                </select>
                <button type="submit" class="aps-btn aps-btn-apply">
                    <?php esc_html_e( 'Apply', 'affiliate-product-showcase' ); ?>
                </button>
            </div>

            <!-- Search -->
            <div class="aps-filter-group aps-filter-search">
                <label for="aps_search_products" class="screen-reader-text"><?php esc_html_e( 'Search products', 'affiliate-product-showcase' ); ?></label>
                <input type="text" 
                       name="aps_search" 
                       id="aps_search_products" 
                       class="aps-filter-input"
                       placeholder="<?php esc_attr_e( 'Search products...', 'affiliate-product-showcase' ); ?>"
                       value="<?php echo isset( $_GET['aps_search'] ) ? esc_attr( $_GET['aps_search'] ) : ''; ?>">
            </div>

            <!-- Categories Dropdown -->
            <div class="aps-filter-group">
                <label for="aps_category_filter" class="screen-reader-text"><?php esc_html_e( 'Filter by category', 'affiliate-product-showcase' ); ?></label>
                <select name="aps_category_filter" id="aps_category_filter" class="aps-filter-select">
                    <option value=""><?php esc_html_e( 'All Categories', 'affiliate-product-showcase' ); ?></option>
                    <?php if ( $categories && ! is_wp_error( $categories ) ): ?>
                        <?php foreach ( $categories as $category ): ?>
                            <option value="<?php echo esc_attr( $category->term_id ); ?>"
                                <?php selected( isset( $_GET['aps_category_filter'] ) ? $_GET['aps_category_filter'] : '', $category->term_id ); ?>>
                                <?php echo esc_html( $category->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Sort Dropdown -->
            <div class="aps-filter-group">
                <label for="aps_sort_order" class="screen-reader-text"><?php esc_html_e( 'Sort products', 'affiliate-product-showcase' ); ?></label>
                <select name="order" id="aps_sort_order" class="aps-filter-select">
                    <option value="desc" <?php selected( $sort_order, 'desc' ); ?>>
                        <?php esc_html_e( 'Latest ← Oldest', 'affiliate-product-showcase' ); ?>
                    </option>
                    <option value="asc" <?php selected( $sort_order, 'asc' ); ?>>
                        <?php esc_html_e( 'Oldest ← Latest', 'affiliate-product-showcase' ); ?>
                    </option>
                </select>
            </div>

            <!-- Show Featured Toggle -->
            <div class="aps-filter-group aps-filter-toggle">
                <label for="aps_show_featured" class="aps-toggle-label">
                    <input type="checkbox" 
                           name="featured_filter" 
                           id="aps_show_featured" 
                           value="1"
                           <?php checked( $featured_filter, '1' ); ?>>
                    <span class="aps-toggle-slider"></span>
                    <span class="aps-toggle-text"><?php esc_html_e( 'Show Featured', 'affiliate-product-showcase' ); ?></span>
                </label>
            </div>

            <!-- Clear Filters Button -->
            <div class="aps-filter-group">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" 
                   class="aps-btn aps-btn-clear">
                    <span class="dashicons dashicons-no"></span>
                    <?php esc_html_e( 'Clear filters', 'affiliate-product-showcase' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue styles for product table UI
     *
     * @param string $hook Current admin hook
     * @return void
     */
    public function enqueueStyles( string $hook ): void {
        // Only enqueue on products list page
        if ( ! $this->isProductsPage() ) {
            return;
        }

        // Enqueue admin table CSS
        wp_enqueue_style(
            'aps-admin-table',
            \AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/admin-table.css',
            [],
            \AffiliateProductShowcase\Plugin\Constants::VERSION
        );

        // Enqueue product table UI CSS
        wp_enqueue_style(
            'aps-product-table-ui',
            \AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/product-table-ui.css',
            ['aps-admin-table'],
            \AffiliateProductShowcase\Plugin\Constants::VERSION
        );

        // Enqueue product table UI JS
        wp_enqueue_script(
            'aps-product-table-ui',
            \AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/js/product-table-ui.js',
            ['jquery'],
            \AffiliateProductShowcase\Plugin\Constants::VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'aps-product-table-ui', 'apsProductTableUI', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'aps_product_table_ui' ),
            'strings' => [
                'confirmBulkUpload' => __( 'Are you sure you want to bulk upload products?', 'affiliate-product-showcase' ),
                'confirmCheckLinks' => __( 'Are you sure you want to check all product links?', 'affiliate-product-showcase' ),
                'processing' => __( 'Processing...', 'affiliate-product-showcase' ),
                'done' => __( 'Done!', 'affiliate-product-showcase' ),
            ],
        ]);
    }
}
