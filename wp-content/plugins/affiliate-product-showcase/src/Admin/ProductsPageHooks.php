<?php
/**
 * Products Page Hooks
 *
 * Hooks into default WordPress products listing to add custom UI above
 * the WordPress WP_List_Table. This creates the true hybrid approach.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Repositories\ProductRepository;

/**
 * Products Page Hooks Class
 *
 * Adds custom UI elements above the default WordPress products table.
 *
 * @since 1.0.0
 */
class ProductsPageHooks {

    /**
     * Product repository
     *
     * @var ProductRepository
     */
    private ProductRepository $repository;

    /**
     * Constructor
     *
     * @param ProductRepository $repository Product repository instance
     */
    public function __construct( ProductRepository $repository ) {
        $this->repository = $repository;

        // Add custom UI above WordPress table
        add_action( 'all_admin_notices', [ $this, 'renderCustomUI' ], 10 );
        
        // Enqueue custom CSS/JS (not needed for simple hybrid - using default WP table)
        // add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ], 10, 1 );
        
        // Hide WordPress search and filters (we have custom ones)
        add_action( 'admin_head', [ $this, 'hideWordPressUI' ], 10 );
    }

    /**
     * Check if we're on the products page
     *
     * @return bool
     */
    private function isProductsPage(): bool {
        return isset( $_GET['post_type'] ) && 
               $_GET['post_type'] === 'aps_product' && 
               ! isset( $_GET['page'] ) &&
               ! isset( $_GET['action'] );
    }

    /**
     * Render custom UI above WordPress table
     *
     * @return void
     */
    public function renderCustomUI(): void {
        if ( ! $this->isProductsPage() ) {
            return;
        }
        
        // Get counts
        $counts = [
            'all'     => $this->repository->count_products( [ 'status' => 'all' ] ),
            'publish'  => $this->repository->count_products( [ 'status' => 'publish' ] ),
            'draft'    => $this->repository->count_products( [ 'status' => 'draft' ] ),
            'trash'    => $this->repository->count_products( [ 'status' => 'trash' ] ),
        ];
        
        ?>
        <div class="wrap" id="aps-products-page">
            
            <!-- Custom UI Above Table -->
            <div class="aps-product-table-actions">
                
                <!-- Page Title -->
                <h1 class="aps-page-title">
                    <?php echo esc_html( get_admin_page_title() ); ?>
                </h1>
                
                <p class="aps-page-description">
                    <?php echo esc_html( __( 'Manage all your affiliate products. Use the filters below to find specific products.', 'affiliate-product-showcase' ) ); ?>
                </p>
                
                <!-- Action Buttons -->
                <div class="aps-action-buttons">
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&page=add-product' ) ); ?>" 
                       class="aps-btn aps-btn-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php echo esc_html( __( 'Add New Product', 'affiliate-product-showcase' ) ); ?>
                    </a>
                    <button type="button" class="aps-btn aps-btn-secondary" onclick="apsBulkUploadProducts()">
                        <span class="dashicons dashicons-upload"></span>
                        <?php echo esc_html( __( 'Bulk Upload', 'affiliate-product-showcase' ) ); ?>
                    </button>
                    <button type="button" class="aps-btn aps-btn-secondary" onclick="apsCheckProductLinks()">
                        <span class="dashicons dashicons-admin-links"></span>
                        <?php echo esc_html( __( 'Check Links', 'affiliate-product-showcase' ) ); ?>
                    </button>
                </div>
                
                <!-- Status Counts -->
                <div class="aps-product-counts">
                    <a href="#" class="aps-count-item active" data-status="all">
                        <span class="aps-count-number"><?php echo esc_html( $counts['all'] ); ?></span>
                        <span class="aps-count-label"><?php echo esc_html( __( 'All', 'affiliate-product-showcase' ) ); ?></span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=publish' ) ); ?>" 
                       class="aps-count-item" data-status="publish">
                        <span class="aps-count-number"><?php echo esc_html( $counts['publish'] ); ?></span>
                        <span class="aps-count-label"><?php echo esc_html( __( 'Published', 'affiliate-product-showcase' ) ); ?></span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=draft' ) ); ?>" 
                       class="aps-count-item" data-status="draft">
                        <span class="aps-count-number"><?php echo esc_html( $counts['draft'] ); ?></span>
                        <span class="aps-count-label"><?php echo esc_html( __( 'Draft', 'affiliate-product-showcase' ) ); ?></span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=trash' ) ); ?>" 
                       class="aps-count-item" data-status="trash">
                        <span class="aps-count-number"><?php echo esc_html( $counts['trash'] ); ?></span>
                        <span class="aps-count-label"><?php echo esc_html( __( 'Trash', 'affiliate-product-showcase' ) ); ?></span>
                    </a>
                </div>
                
            </div>
            
            <!-- Filters Section -->
            <div class="aps-product-filters">
                
                <!-- Search -->
                <div class="aps-filter-group aps-filter-search">
                    <label for="aps_search_products"><?php echo esc_html( __( 'Search Products', 'affiliate-product-showcase' ) ); ?></label>
                    <input type="text" 
                           id="aps_search_products" 
                           class="aps-filter-input" 
                           placeholder="<?php echo esc_attr( __( 'Search products...', 'affiliate-product-showcase' ) ); ?>" 
                           autocomplete="off" />
                </div>
                
                <!-- Category Filter -->
                <div class="aps-filter-group">
                    <label for="aps_category_filter"><?php echo esc_html( __( 'Category', 'affiliate-product-showcase' ) ); ?></label>
                    <select id="aps_category_filter" class="aps-filter-select">
                        <option value="0"><?php echo esc_html( __( 'All Categories', 'affiliate-product-showcase' ) ); ?></option>
                        <?php
                        $categories = get_terms( [ 
                            'taxonomy' => 'aps_product_category', 
                            'hide_empty' => false 
                        ] );
                        foreach ( $categories as $category ) :
                        ?>
                            <option value="<?php echo esc_attr( $category->term_id ); ?>">
                                <?php echo esc_html( $category->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Featured Filter -->
                <div class="aps-filter-group aps-filter-toggle">
                    <label class="aps-toggle-label">
                        <input type="checkbox" id="aps_show_featured" />
                        <span class="aps-toggle-slider"></span>
                        <span class="aps-toggle-text"><?php echo esc_html( __( 'Featured Only', 'affiliate-product-showcase' ) ); ?></span>
                    </label>
                </div>
                
                <!-- Sort Order -->
                <div class="aps-filter-group">
                    <label for="aps_sort_order"><?php echo esc_html( __( 'Sort Order', 'affiliate-product-showcase' ) ); ?></label>
                    <select id="aps_sort_order" class="aps-filter-select">
                        <option value="asc"><?php echo esc_html( __( 'Ascending', 'affiliate-product-showcase' ) ); ?></option>
                        <option value="desc" selected><?php echo esc_html( __( 'Descending', 'affiliate-product-showcase' ) ); ?></option>
                    </select>
                </div>
                
                <!-- Clear Filters Button -->
                <div class="aps-filter-group">
                    <button type="button" class="aps-btn aps-btn-clear" id="aps_clear_filters">
                        <?php echo esc_html( __( 'Clear Filters', 'affiliate-product-showcase' ) ); ?>
                    </button>
                </div>
                
            </div>
            
            <!-- Hidden inputs for AJAX -->
            <input type="hidden" id="aps_nonce" value="<?php echo esc_attr( wp_create_nonce( 'aps_table_actions' ) ); ?>" />
            <input type="hidden" id="aps_ajax_url" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" />
            
        </div>
        <?php
    }


    /**
     * Hide WordPress default UI elements
     *
     * Hides WordPress search and filters since we have custom ones.
     *
     * @return void
     */
    public function hideWordPressUI(): void {
        if ( ! $this->isProductsPage() ) {
            return;
        }
        ?>
        <style>
            /* Hide WordPress search */
            .wrap.search-box {
                display: none !important;
            }
            
            /* Hide WordPress view filters (screen options) */
            .screen-options {
                display: none !important;
            }
            
            /* Hide WordPress bulk actions (we have custom) */
            .tablenav.top .bulkactions {
                display: none !important;
            }
            
            /* Hide WordPress pagination top (keep bottom) */
            .tablenav.top .tablenav-pages {
                display: none !important;
            }
            
            /* Style WordPress table to match our design */
            .wp-list-table {
                margin-top: 20px;
            }
            
            .wp-list-table thead th {
                background: #f8f9fa;
                color: #1e293b;
                font-weight: 600;
            }
            
            .wp-list-table tbody tr:hover {
                background: #f1f5f9;
            }
        </style>
        <?php
    }
}
