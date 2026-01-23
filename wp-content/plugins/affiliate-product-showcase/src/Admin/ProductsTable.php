<?php
/**
 * Products Table (WordPress WP_List_Table)
 *
 * Uses WordPress WP_List_Table class for displaying products
 * in a standard WordPress table format with custom UI above.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\ProductRepository;

// Make sure WP_List_Table is available
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Products Table Class
 *
 * Extends WP_List_Table to provide standard WordPress table functionality
 * while allowing custom UI elements above the table.
 *
 * @since 1.0.0
 */
class ProductsTable extends \WP_List_Table {

    /**
     * Product service
     *
     * @var ProductService
     */
    private ProductService $product_service;

    /**
     * Product repository
     *
     * @var ProductRepository
     */
    private ProductRepository $repository;

    /**
     * Current page number
     *
     * @var int
     */
    private int $current_page = 1;

    /**
     * Items per page
     *
     * @var int
     */
    private int $per_page = 20;

    /**
     * Total items count
     *
     * @var int
     */
    private int $total_items = 0;

    /**
     * Constructor
     *
     * @param ProductService $product_service Product service instance
     * @param ProductRepository $repository Product repository instance
     */
    public function __construct(
        ProductService $product_service,
        ProductRepository $repository
    ) {
        $this->product_service = $product_service;
        $this->repository = $repository;

        parent::__construct( [
            'singular' => 'product',
            'plural'   => 'products',
            'ajax'      => true,
        ] );
    }

    /**
     * Prepare items for the table
     *
     * @return void
     */
    public function prepare_items(): void {
        // Set pagination
        $this->current_page = $this->get_pagenum();
        
        // Get items from service
        $products = $this->repository->get_products( [
            'per_page' => $this->per_page,
            'page'     => $this->current_page,
            'orderby'   => isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'date',
            'order'     => isset( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : 'DESC',
        ] );

        $this->items = $products;
        $this->total_items = $this->repository->count_products();

        // Set pagination
        $this->set_pagination_args( [
            'total_items' => $this->total_items,
            'per_page'    => $this->per_page,
            'total_pages' => ceil( $this->total_items / $this->per_page ),
        ] );
    }

    /**
     * Get table columns
     *
     * @return array Columns
     */
    public function get_columns(): array {
        return [
            'cb'        => '<input type="checkbox" />',
            'logo'      => __( 'Logo', 'affiliate-product-showcase' ),
            'title'     => __( 'Title', 'affiliate-product-showcase' ),
            'categories' => __( 'Categories', 'affiliate-product-showcase' ),
            'tags'      => __( 'Tags', 'affiliate-product-showcase' ),
            'ribbon'     => __( 'Ribbon', 'affiliate-product-showcase' ),
            'featured'   => __( 'Featured', 'affiliate-product-showcase' ),
            'price'     => __( 'Price', 'affiliate-product-showcase' ),
            'status'     => __( 'Status', 'affiliate-product-showcase' ),
        ];
    }

    /**
     * Get sortable columns
     *
     * @return array Sortable columns
     */
    public function get_sortable_columns(): array {
        return [
            'title'   => [ 'title', true ],
            'price'    => [ 'price', true ],
            'status'   => [ 'status', true ],
            'featured' => [ 'featured', true ],
        ];
    }

    /**
     * Get bulk actions
     *
     * @return array Bulk actions
     */
    public function get_bulk_actions(): array {
        return [
            'publish' => __( 'Publish', 'affiliate-product-showcase' ),
            'draft'   => __( 'Draft', 'affiliate-product-showcase' ),
            'trash'   => __( 'Move to Trash', 'affiliate-product-showcase' ),
        ];
    }

    /**
     * Column default
     *
     * @param Product $item Product item
     * @param string $column_name Column name
     * @return mixed Column value
     */
    protected function column_default( $item, $column_name ) {
        if ( ! $item instanceof Product ) {
            return '';
        }

        switch ( $column_name ) {
            case 'logo':
                return $this->get_logo_column( $item );

            case 'title':
                return $this->get_title_column( $item );

            case 'categories':
                return $this->get_categories_column( $item );

            case 'tags':
                return $this->get_tags_column( $item );

            case 'ribbon':
                return $this->get_ribbon_column( $item );

            case 'featured':
                return $this->get_featured_column( $item );

            case 'price':
                return $this->get_price_column( $item );

            case 'status':
                return $this->get_status_column( $item );

            default:
                return '';
        }
    }

    /**
     * Checkbox column
     *
     * @param Product $item Product item
     * @return string Checkbox HTML
     */
    protected function column_cb( $item ): string {
        return sprintf(
            '<input type="checkbox" name="product[]" value="%s" />',
            $item->get_id()
        );
    }

    /**
     * Logo column
     *
     * @param Product $item Product item
     * @return string Logo HTML
     */
    protected function get_logo_column( Product $item ): string {
        $logo = $item->get_logo();
        
        if ( ! empty( $logo ) ) {
            return sprintf(
                '<img src="%s" alt="%s" class="aps-product-logo" width="40" height="40" />',
                esc_url( $logo ),
                esc_attr( $item->get_title() )
            );
        }

        $first_char = strtoupper( substr( $item->get_title(), 0, 1 ) );
        return sprintf(
            '<div class="aps-product-logo-placeholder">%s</div>',
            esc_html( $first_char )
        );
    }

    /**
     * Title column
     *
     * @param Product $item Product item
     * @return string Title HTML
     */
    protected function get_title_column( Product $item ): string {
        $edit_url = admin_url(
            sprintf(
                'post.php?post_type=aps_product&action=edit&post=%d',
                $item->get_id()
            )
        );

        $title = sprintf(
            '<strong><a href="%s">%s</a></strong>',
            esc_url( $edit_url ),
            esc_html( $item->get_title() )
        );

        $actions = $this->get_row_actions( $item );

        return $title . $this->row_actions( $actions );
    }

    /**
     * Get row actions
     *
     * @param Product $item Product item
     * @return array Row actions
     */
    private function get_row_actions( Product $item ): array {
        $edit_url = admin_url(
            sprintf(
                'post.php?post_type=aps_product&action=edit&post=%d',
                $item->get_id()
            )
        );

        $delete_url = wp_nonce_url(
            admin_url(
                sprintf(
                    'post.php?post_type=aps_product&action=delete&post=%d',
                    $item->get_id()
                )
            ),
            'delete-product_' . $item->get_id()
        );

        return [
            'edit' => sprintf(
                '<a href="%s">%s</a>',
                esc_url( $edit_url ),
                __( 'Edit', 'affiliate-product-showcase' )
            ),
            'trash' => sprintf(
                '<a href="%s" class="submitdelete">%s</a>',
                esc_url( $delete_url ),
                __( 'Trash', 'affiliate-product-showcase' )
            ),
        ];
    }

    /**
     * Categories column
     *
     * @param Product $item Product item
     * @return string Categories HTML
     */
    protected function get_categories_column( Product $item ): string {
        $categories = $item->get_categories();
        
        if ( empty( $categories ) ) {
            return '-';
        }

        $category_links = [];
        foreach ( $categories as $category ) {
            $category_links[] = sprintf(
                '<span class="aps-product-category">%s</span>',
                esc_html( $category )
            );
        }

        return implode( ' ', $category_links );
    }

    /**
     * Tags column
     *
     * @param Product $item Product item
     * @return string Tags HTML
     */
    protected function get_tags_column( Product $item ): string {
        $tags = $item->get_tags();
        
        if ( empty( $tags ) ) {
            return '-';
        }

        $tag_links = [];
        foreach ( $tags as $tag ) {
            $tag_links[] = sprintf(
                '<span class="aps-product-tag">%s</span>',
                esc_html( $tag )
            );
        }

        return implode( ' ', $tag_links );
    }

    /**
     * Ribbon column
     *
     * @param Product $item Product item
     * @return string Ribbon HTML
     */
    protected function get_ribbon_column( Product $item ): string {
        $ribbon = $item->get_ribbon();
        
        if ( empty( $ribbon ) ) {
            return '-';
        }

        return sprintf(
            '<span class="aps-product-badge">%s</span>',
            esc_html( $ribbon )
        );
    }

    /**
     * Featured column
     *
     * @param Product $item Product item
     * @return string Featured HTML
     */
    protected function get_featured_column( Product $item ): string {
        return $item->is_featured()
            ? '<span class="aps-product-featured">★</span>'
            : '-';
    }

    /**
     * Price column
     *
     * @param Product $item Product item
     * @return string Price HTML
     */
    protected function get_price_column( Product $item ): string {
        $price = $item->get_price();
        $original_price = $item->get_original_price();
        $discount_percent = $item->get_discount_percentage();

        $price_html = sprintf(
            '<span class="aps-product-price">$%s</span>',
            number_format( $price, 2 )
        );

        if ( $original_price > 0 && $original_price > $price ) {
            $price_html .= sprintf(
                ' <span class="aps-product-price-original">$%s</span>',
                number_format( $original_price, 2 )
            );
            
            if ( $discount_percent > 0 ) {
                $price_html .= sprintf(
                    ' <span class="aps-product-price-discount">%d%% OFF</span>',
                    $discount_percent
                );
            }
        }

        return $price_html;
    }

    /**
     * Status column
     *
     * @param Product $item Product item
     * @return string Status HTML
     */
    protected function get_status_column( Product $item ): string {
        $status = get_post_status( $item->get_id() );
        $status_labels = [
            'publish' => 'PUBLISHED',
            'draft'   => 'DRAFT',
            'trash'   => 'TRASH',
            'pending' => 'PENDING',
        ];
        
        $status_label = $status_labels[ $status ] ?? strtoupper( $status );
        
        return sprintf(
            '<span class="aps-product-status aps-product-status-%s">%s</span>',
            esc_attr( $status ),
            esc_html( $status_label )
        );
    }

    /**
     * No items found
     *
     * @return string No items message
     */
    public function no_items(): string {
        return __( 'No products found.', 'affiliate-product-showcase' );
    }

    /**
     * Display the table
     *
     * @return void
     */
    public function display(): void {
        $this->prepare_items();
        
        // Output nonce and other data needed for AJAX
        echo '<input type="hidden" id="aps_nonce" value="' . esc_attr( wp_create_nonce( 'aps_table_actions' ) ) . '" />';
        echo '<input type="hidden" id="aps_ajax_url" value="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" />';
        
        parent::display();
    }

    /**
     * Extra controls for table top
     *
     * @param string $which Top or bottom
     * @return void
     */
    protected function extra_tablenav( $which ): void {
        if ( 'top' === $which ) {
            // Render custom UI above table
            $this->render_custom_ui_above();
        }
        
        parent::extra_tablenav( $which );
    }

    /**
     * Render custom UI above table
     *
     * This renders status counts, action buttons, and filters
     * above the WordPress table.
     *
     * @return void
     */
    private function render_custom_ui_above(): void {
        // Get counts
        $counts = [
            'all'     => $this->repository->count_products( [ 'status' => 'all' ] ),
            'publish'  => $this->repository->count_products( [ 'status' => 'publish' ] ),
            'draft'    => $this->repository->count_products( [ 'status' => 'draft' ] ),
            'trash'    => $this->repository->count_products( [ 'status' => 'trash' ] ),
        ];
        ?>
        
        <!-- Custom UI Above Table -->
        <div class="aps-products-ui-header">
            
            <!-- Status Counts -->
            <div class="aps-status-counts">
                <a href="#" class="aps-count-item active" data-status="all">
                    <span class="aps-count-label">All</span>
                    <span class="aps-count-number"><?php echo esc_html( $counts['all'] ); ?></span>
                </a>
                <a href="#" class="aps-count-item" data-status="publish">
                    <span class="aps-count-label">Published</span>
                    <span class="aps-count-number"><?php echo esc_html( $counts['publish'] ); ?></span>
                </a>
                <a href="#" class="aps-count-item" data-status="draft">
                    <span class="aps-count-label">Draft</span>
                    <span class="aps-count-number"><?php echo esc_html( $counts['draft'] ); ?></span>
                </a>
                <a href="#" class="aps-count-item" data-status="trash">
                    <span class="aps-count-label">Trash</span>
                    <span class="aps-count-number"><?php echo esc_html( $counts['trash'] ); ?></span>
                </a>
            </div>
            
            <!-- Action Buttons -->
            <div class="aps-actions">
                <button type="button" class="button button-primary" onclick="apsBulkUploadProducts()">
                    <span class="dashicons dashicons-upload"></span>
                    <?php echo esc_html( __( 'Bulk Upload', 'affiliate-product-showcase' ) ); ?>
                </button>
                <button type="button" class="button" onclick="apsCheckProductLinks()">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php echo esc_html( __( 'Check Links', 'affiliate-product-showcase' ) ); ?>
                </button>
            </div>
            
        </div>
        
        <!-- Filters Section -->
        <div class="aps-products-filters">
            
            <!-- Search -->
            <div class="aps-filter-group">
                <label for="aps_search_products"><?php echo esc_html( __( 'Search:', 'affiliate-product-showcase' ) ); ?></label>
                <input type="text" id="aps_search_products" class="regular-text" placeholder="<?php echo esc_attr( __( 'Search products...', 'affiliate-product-showcase' ) ); ?>" />
            </div>
            
            <!-- Category Filter -->
            <div class="aps-filter-group">
                <label for="aps_category_filter"><?php echo esc_html( __( 'Category:', 'affiliate-product-showcase' ) ); ?></label>
                <select id="aps_category_filter" class="regular-text">
                    <option value="0"><?php echo esc_html( __( 'All Categories', 'affiliate-product-showcase' ) ); ?></option>
                    <?php
                    $categories = get_terms( [ 'taxonomy' => 'aps_product_category', 'hide_empty' => false ] );
                    foreach ( $categories as $category ) :
                    ?>
                        <option value="<?php echo esc_attr( $category->term_id ); ?>">
                            <?php echo esc_html( $category->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Featured Filter -->
            <div class="aps-filter-group">
                <label>
                    <input type="checkbox" id="aps_show_featured" />
                    <?php echo esc_html( __( 'Show Featured Only', 'affiliate-product-showcase' ) ); ?>
                </label>
            </div>
            
            <!-- Sort Order -->
            <div class="aps-filter-group">
                <label for="aps_sort_order"><?php echo esc_html( __( 'Sort Order:', 'affiliate-product-showcase' ) ); ?></label>
                <select id="aps_sort_order" class="regular-text">
                    <option value="asc"><?php echo esc_html( __( 'Ascending', 'affiliate-product-showcase' ) ); ?></option>
                    <option value="desc" selected><?php echo esc_html( __( 'Descending', 'affiliate-product-showcase' ) ); ?></option>
                </select>
            </div>
            
        </div>
        <?php
    }
}
