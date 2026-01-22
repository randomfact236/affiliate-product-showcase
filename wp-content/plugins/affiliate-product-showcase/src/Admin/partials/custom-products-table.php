<?php
/**
 * Custom Products Table Page
 *
 * Fully replaces WordPress default "All Products" table with custom implementation.
 * Includes filters, bulk actions, pagination, and all features from design diagram.
 *
 * @package AffiliateProductShowcase\Admin\Partials
 * @since 1.0.0
 */

// Prevent direct access
defined( 'ABSPATH' ) || exit;

// Get products
$per_page = isset( $_GET['per_page'] ) ? intval( $_GET['per_page'] ) : 20;
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$featured = isset( $_GET['featured'] ) && $_GET['featured'] === '1';
$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

// Get counts
$total_products = wp_count_posts( [ 'post_type' => 'aps_product', 'post_status' => 'any' ] );
$published_count = wp_count_posts( [ 'post_type' => 'aps_product', 'post_status' => 'publish' ] );
$draft_count = wp_count_posts( [ 'post_type' => 'aps_product', 'post_status' => 'draft' ] );
$trash_count = wp_count_posts( [ 'post_type' => 'aps_product', 'post_status' => 'trash' ] );

// Build query args
$args = [
    'post_type' => 'aps_product',
    'posts_per_page' => $per_page,
    'paged' => $current_page,
    'orderby' => 'date',
    'order' => 'DESC',
];

// Add filters
if ( $search ) {
    $args['s'] = $search;
}

if ( $category ) {
    $tax_query = [
        [
            'taxonomy' => 'aps_category',
            'field' => 'slug',
            'terms' => $category,
        ]
    ];
    $args['tax_query'] = $tax_query;
}

if ( $featured ) {
    $args['meta_query'] = [
        [
            'key' => '_aps_featured',
            'value' => '1',
            'compare' => '=',
        ]
    ];
}

if ( $status && in_array( $status, [ 'publish', 'draft', 'trash' ] ) ) {
    $args['post_status'] = $status;
}

// Get products
$products = new WP_Query( $args );

// Calculate pagination
$total_pages = $products->max_num_pages;
$prev_page = $current_page > 1 ? $current_page - 1 : 0;
$next_page = $current_page < $total_pages ? $current_page + 1 : 0;
?>
<div class="wrap affiliate-products-wrapper">
    <!-- Page Header -->
    <h1 class="wp-heading-inline">
        <?php echo esc_html__( 'All Products', 'affiliate-product-showcase' ); ?>
        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aps_product' ) ); ?>" class="page-title-action">
            <?php echo esc_html__( 'Add New', 'affiliate-product-showcase' ); ?>
        </a>
    </h1>

    <!-- Overview Stats Cards -->
    <div class="aps-overview-section">
        <div class="aps-overview-cards">
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="aps-overview-card">
                <span class="aps-count-number"><?php echo esc_html( $total_products ); ?></span>
                <span class="aps-count-label"><?php echo esc_html__( 'All', 'affiliate-product-showcase' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=publish' ) ); ?>" class="aps-overview-card">
                <span class="aps-count-number"><?php echo esc_html( $published_count ); ?></span>
                <span class="aps-count-label"><?php echo esc_html__( 'Published', 'affiliate-product-showcase' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=draft' ) ); ?>" class="aps-overview-card">
                <span class="aps-count-number"><?php echo esc_html( $draft_count ); ?></span>
                <span class="aps-count-label"><?php echo esc_html__( 'Draft', 'affiliate-product-showcase' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=trash' ) ); ?>" class="aps-overview-card">
                <span class="aps-count-number"><?php echo esc_html( $trash_count ); ?></span>
                <span class="aps-count-label"><?php echo esc_html__( 'Trash', 'affiliate-product-showcase' ); ?></span>
            </a>
        </div>
    </div>

    <!-- Active Filters Badge (if any filters applied) -->
    <?php if ( $search || $category || $featured || $status ) : ?>
    <div class="aps-active-filters">
        <strong><?php echo esc_html__( 'Active Filters:', 'affiliate-product-showcase' ); ?></strong>
        <?php if ( $search ) : ?>
            <span class="aps-filter-tag">
                <?php echo esc_html( 'Search' ); ?>: "<?php echo esc_html( $search ); ?>"
                <span class="aps-filter-tag-remove" data-filter="search">&times;</span>
            </span>
        <?php endif; ?>
        
        <?php if ( $category ) : ?>
            <span class="aps-filter-tag">
                <?php echo esc_html( 'Category' ); ?>: "<?php echo esc_html( $category ); ?>"
                <span class="aps-filter-tag-remove" data-filter="category">&times;</span>
            </span>
        <?php endif; ?>
        
        <?php if ( $featured ) : ?>
            <span class="aps-filter-tag">
                <?php echo esc_html__( 'Featured' ); ?>
                <span class="aps-filter-tag-remove" data-filter="featured">&times;</span>
            </span>
        <?php endif; ?>
        
        <?php if ( $status ) : ?>
            <span class="aps-filter-tag">
                <?php echo esc_html( 'Status' ); ?>: "<?php echo esc_html( $status ); ?>"
                <span class="aps-filter-tag-remove" data-filter="status">&times;</span>
            </span>
        <?php endif; ?>
        
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="button button-secondary">
            <?php echo esc_html__( 'Clear All Filters', 'affiliate-product-showcase' ); ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Filters Section -->
    <div class="aps-filters-section">
        <h2><?php echo esc_html__( 'Filters', 'affiliate-product-showcase' ); ?></h2>
        
        <div class="aps-filters-row">
            <!-- Search Input -->
            <div class="aps-filter-group">
                <label for="aps-search"><?php echo esc_html__( 'Search', 'affiliate-product-showcase' ); ?>:</label>
                <input type="text" id="aps-search" class="aps-filter-input" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr__( 'Search products...', 'affiliate-product-showcase' ); ?>">
            </div>
            
            <!-- Category Dropdown -->
            <div class="aps-filter-group">
                <label for="aps-category"><?php echo esc_html__( 'Category', 'affiliate-product-showcase' ); ?>:</label>
                <select id="aps-category" class="aps-filter-select">
                    <option value=""><?php echo esc_html__( 'All Categories', 'affiliate-product-showcase' ); ?></option>
                    <?php 
                    $categories = get_terms( [ 'taxonomy' => 'aps_category', 'hide_empty' => false ] );
                    foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $category === $cat->slug ); ?>><?php echo esc_html( $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Featured Checkbox -->
            <div class="aps-filter-group">
                <label class="aps-filter-checkbox">
                    <input type="checkbox" id="aps-featured" class="aps-filter-checkbox" <?php checked( $featured ); ?>>
                    <?php echo esc_html__( 'Show Featured Only', 'affiliate-product-showcase' ); ?>
                </label>
            </div>
            
            <!-- Status Dropdown -->
            <div class="aps-filter-group">
                <label for="aps-status"><?php echo esc_html__( 'Status', 'affiliate-product-showcase' ); ?>:</label>
                <select id="aps-status" class="aps-filter-select">
                    <option value=""><?php echo esc_html__( 'All Status', 'affiliate-product-showcase' ); ?></option>
                    <option value="publish" <?php selected( $status === 'publish' ); ?>><?php echo esc_html__( 'Published', 'affiliate-product-showcase' ); ?></option>
                    <option value="draft" <?php selected( $status === 'draft' ); ?>><?php echo esc_html__( 'Draft', 'affiliate-product-showcase' ); ?></option>
                    <option value="trash" <?php selected( $status === 'trash' ); ?>><?php echo esc_html__( 'Trash', 'affiliate-product-showcase' ); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <form id="aps-bulk-actions-form" class="aps-bulk-actions-form">
        <div class="aps-bulk-actions-bar">
            <h2><?php echo esc_html__( 'Bulk Actions', 'affiliate-product-showcase' ); ?></h2>
            
            <div class="aps-bulk-row">
                <div class="aps-bulk-action-label">
                    <label for="aps-action-select"><?php echo esc_html__( 'Select Action', 'affiliate-product-showcase' ); ?>:</label>
                    <select id="aps-action-select" class="aps-bulk-action-select">
                        <option value=""><?php echo esc_html__( 'Bulk actions', 'affiliate-product-showcase' ); ?></option>
                        <option value="publish"><?php echo esc_html__( 'Publish', 'affiliate-product-showcase' ); ?></option>
                        <option value="draft"><?php echo esc_html__( 'Set to Draft', 'affiliate-product-showcase' ); ?></option>
                        <option value="in-stock"><?php echo esc_html__( 'Set In Stock', 'affiliate-product-showcase' ); ?></option>
                        <option value="out-of-stock"><?php echo esc_html__( 'Set Out of Stock', 'affiliate-product-showcase' ); ?></option>
                        <option value="reset-clicks"><?php echo esc_html__( 'Reset Click Counts', 'affiliate-product-showcase' ); ?></option>
                        <option value="trash"><?php echo esc_html__( 'Move to Trash', 'affiliate-product-showcase' ); ?></option>
                        <option value="restore"><?php echo esc_html__( 'Restore from Trash', 'affiliate-product-showcase' ); ?></option>
                        <option value="delete-permanent"><?php echo esc_html__( 'Delete Permanently', 'affiliate-product-showcase' ); ?></option>
                    </select>
                </div>
                
                <button type="submit" class="aps-bulk-apply button-primary" disabled>
                    <span class="dashicons dashicons-yes"></span>
                    <?php echo esc_html__( 'Apply', 'affiliate-product-showcase' ); ?>
                </button>
            </div>
            
            <div class="aps-bulk-selected-count">
                <span class="aps-selected-count-text">
                    <?php echo sprintf( esc_html__( '%1$s selected', 'affiliate-product-showcase' ), '<span id="aps-selected-count">0</span>' ); ?>
                </span>
            </div>
        </div>
    </form>

    <!-- Products Table -->
    <table class="wp-list-table widefat fixed striped aps-products-table">
        <thead>
            <tr>
                <th class="manage-column column-cb check-column">
                    <input type="checkbox" id="aps-select-all" class="aps-product-checkbox">
                </th>
                <th class="manage-column column-logo">
                    <?php echo esc_html__( 'Image', 'affiliate-product-showcase' ); ?>
                </th>
                <th class="manage-column column-title sortable" data-sort="title">
                    <?php echo esc_html__( 'Product', 'affiliate-product-showcase' ); ?>
                    <span class="aps-sort-icon"></span>
                </th>
                <th class="manage-column column-category">
                    <?php echo esc_html__( 'Category', 'affiliate-product-showcase' ); ?>
                </th>
                <th class="manage-column column-tags">
                    <?php echo esc_html__( 'Tags', 'affiliate-product-showcase' ); ?>
                </th>
                <th class="manage-column column-ribbon">
                    <?php echo esc_html__( 'Ribbon', 'affiliate-product-showcase' ); ?>
                </th>
                <th class="manage-column column-featured">
                    <?php echo esc_html__( 'Featured', 'affiliate-product-showcase' ); ?>
                </th>
                <th class="manage-column column-price sortable" data-sort="price">
                    <?php echo esc_html__( 'Price', 'affiliate-product-showcase' ); ?>
                    <span class="aps-sort-icon"></span>
                </th>
                <th class="manage-column column-status">
                    <?php echo esc_html__( 'Status', 'affiliate-product-showcase' ); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $products->have_posts() ) : ?>
                <?php 
                while ( $products->have_posts() ) : 
                    $products->the_post();
                    
                    $product_id = $products->post->ID;
                    $title = get_the_title();
                    $excerpt = get_the_excerpt();
                    $thumbnail = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
                    $product_link = get_edit_post_link( $product_id );
                    $delete_link = get_delete_post_link( $product_id );
                    
                    // Get product meta
                    $categories = wp_get_post_terms( $product_id, 'aps_category' );
                    $tags = wp_get_post_terms( $product_id, 'aps_tag' );
                    $ribbon = get_post_meta( $product_id, '_aps_ribbon', true );
                    $is_featured = get_post_meta( $product_id, '_aps_featured', true );
                    $current_price = get_post_meta( $product_id, '_aps_current_price', true );
                    $original_price = get_post_meta( $product_id, '_aps_original_price', true );
                    $product_status = get_post_status( $product->post->ID );
                    
                    // Calculate discount
                    $discount = 0;
                    if ( $current_price && $original_price && $original_price > $current_price ) {
                        $discount = round( ( ( $original_price - $current_price ) / $original_price ) * 100 );
                    }
                ?>
                    <tr class="aps-product-row" data-product-id="<?php echo esc_attr( $product_id ); ?>">
                        <td class="column-cb">
                            <input type="checkbox" class="aps-product-checkbox" value="<?php echo esc_attr( $product_id ); ?>">
                        </td>
                        <td class="column-logo">
                            <?php if ( $thumbnail ) : ?>
                                <img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="aps-product-logo">
                            <?php else : ?>
                                <div class="aps-product-no-logo"><?php echo esc_html__( 'No Image', 'affiliate-product-showcase' ); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="column-title">
                            <a href="<?php echo esc_url( $product_link ); ?>" class="aps-product-link">
                                <strong><?php echo esc_html( $title ); ?></strong>
                                <span class="aps-product-id">#<?php echo esc_html( $product_id ); ?></span>
                            </a>
                            <div class="aps-row-actions">
                                <a href="<?php echo esc_url( $product_link ); ?>" class="aps-quick-action button-small">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php echo esc_html__( 'Edit', 'affiliate-product-showcase' ); ?>
                                </a>
                                <a href="<?php echo esc_url( $delete_link ); ?>" class="aps-quick-delete button-small" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-product-name="<?php echo esc_attr( $title ); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                    <?php echo esc_html__( 'Delete', 'affiliate-product-showcase' ); ?>
                                </a>
                            </div>
                        </td>
                        <td class="column-category">
                            <?php 
                            if ( ! empty( $categories ) ) {
                                foreach ( $categories as $cat ) : ?>
                                    <span class="aps-category-tag">
                                        <?php echo esc_html( $cat->name ); ?>
                                        <span class="aps-category-tag-remove" data-category="<?php echo esc_attr( $cat->slug ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">&times;</span>
                                    </span>
                                <?php endforeach; 
                            } else {
                                echo '<span class="aps-no-category">' . esc_html__( 'No category', 'affiliate-product-showcase' ) . '</span>';
                            }
                            ?>
                        </td>
                        <td class="column-tags">
                            <?php 
                            if ( ! empty( $tags ) ) {
                                foreach ( $tags as $tag ) : ?>
                                    <span class="aps-tag-tag">
                                        <?php echo esc_html( $tag->name ); ?>
                                    </span>
                                <?php endforeach; 
                            } else {
                                echo '<span class="aps-no-tags">' . esc_html__( 'No tags', 'affiliate-product-showcase' ) . '</span>';
                            }
                            ?>
                        </td>
                        <td class="column-ribbon">
                            <?php 
                            if ( $ribbon ) {
                                $ribbon_class = 'aps-ribbon-badge aps-ribbon-' . sanitize_title( $ribbon );
                                $ribbon_text = ucfirst( $ribbon );
                                echo '<span class="' . esc_attr( $ribbon_class ) . '">' . esc_html( $ribbon_text ) . '</span>';
                            } else {
                                echo '<span class="aps-no-ribbon">' . esc_html__( 'No ribbon', 'affiliate-product-showcase' ) . '</span>';
                            }
                            ?>
                        </td>
                        <td class="column-featured">
                            <?php if ( $is_featured ) : ?>
                                <span class="aps-featured"><span class="dashicons dashicons-star-filled"></span></span>
                            <?php else : ?>
                                <span class="aps-not-featured">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="column-price">
                            <?php 
                            if ( $current_price ) {
                                echo '<span class="aps-current-price">' . esc_html__( '$', 'affiliate-product-showcase' ) . esc_html( number_format( $current_price, 2 ) ) . '</span>';
                                
                                if ( $original_price && $original_price > $current_price ) {
                                    echo '<span class="aps-original-price">' . esc_html__( '$', 'affiliate-product-showcase' ) . esc_html( number_format( $original_price, 2 ) ) . '</span>';
                                    
                                    if ( $discount > 0 ) {
                                        echo '<span class="aps-discount-badge">-' . esc_html( $discount ) . '%</span>';
                                    }
                                }
                            } else {
                                echo '<span class="aps-no-price">' . esc_html__( 'No price', 'affiliate-product-showcase' ) . '</span>';
                            }
                            ?>
                        </td>
                        <td class="column-status">
                            <span class="aps-status-badge aps-status-<?php echo esc_attr( $product_status ); ?>">
                                <?php echo ucfirst( $product_status ); ?>
                            </span>
                        </td>
                    </tr>
                <?php 
                endwhile; 
                wp_reset_postdata();
                ?>
            <?php else : ?>
                <tr>
                    <td colspan="10" class="aps-empty-message">
                        <?php echo esc_html__( 'No products found.', 'affiliate-product-showcase' ); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="aps-pagination-section">
        <div class="aps-pagination-controls">
            <div class="aps-per-page-select-wrapper">
                <label for="aps-per-page"><?php echo esc_html__( 'Items per page', 'affiliate-product-showcase' ); ?>:</label>
                <select id="aps-per-page" class="aps-per-page-select">
                    <option value="12"<?php selected( $per_page === 12 ); ?>><?php echo esc_html( $per_page ); ?></option>
                    <option value="20"<?php selected( $per_page === 20 ); ?>><?php echo esc_html( $per_page ); ?></option>
                    <option value="50"<?php selected( $per_page === 50 ); ?>><?php echo esc_html( $per_page ); ?></option>
                    <option value="100"<?php selected( $per_page === 100 ); ?>><?php echo esc_html( $per_page ); ?></option>
                </select>
            </div>
            
            <div class="aps-pagination-info">
                <?php 
                $start = ( $current_page - 1 ) * $per_page + 1;
                $end = min( $products->found_posts, $current_page * $per_page );
                echo sprintf( esc_html__( 'Showing %1$s-%2$s of %3$s products', 'affiliate-product-showcase' ), number_format_i18n( $start ), number_format_i18n( $end ), number_format_i18n( $products->found_posts ) );
                ?>
            </div>
        </div>
        
        <div class="aps-page-links">
            <button type="button" class="aps-page-link <?php echo $prev_page === 0 ? 'disabled' : ''; ?>" data-page="<?php echo $prev_page; ?>">
                &laquo; <?php echo esc_html__( 'Previous', 'affiliate-product-showcase' ); ?>
            </button>
            
            <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                <?php 
                $active = $i === $current_page ? 'current' : '';
                echo '<button type="button" class="aps-page-link ' . $active . '" data-page="' . esc_attr( $i ) . '">' . esc_html( $i ) . '</button>';
                ?>
            <?php endfor; ?>
            
            <button type="button" class="aps-page-link <?php echo $next_page === 0 ? 'disabled' : ''; ?>" data-page="<?php echo $next_page; ?>">
                <?php echo esc_html__( 'Next', 'affiliate-product-showcase' ); ?> &raquo;
            </button>
        </div>
    </div>

    <!-- Footer -->
    <div class="aps-footer">
        <p class="aps-total-count">
            <?php echo sprintf( esc_html__( 'Total: %1$s products', 'affiliate-product-showcase' ), number_format_i18n( $total_products ) ); ?>
        </p>
    </div>

    <!-- JavaScript -->
    <script>
        jQuery(document).ready(function($) {
            // Select all checkbox
            $('#aps-select-all').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.aps-product-checkbox').prop('checked', isChecked);
            });

            // Filter change handlers
            $('#aps-search').on('keypress', function(e) {
                if (e.which === 13) {
                    const search = $(this).val();
                    const url = new URL(window.location.href);
                    if (search) {
                        url.searchParams.set('s', search);
                    } else {
                        url.searchParams.delete('s');
                    }
                    window.location.href = url.toString();
                }
            });

            $('#aps-category').on('change', function() {
                const category = $(this).val();
                const url = new URL(window.location.href);
                if (category) {
                    url.searchParams.set('category', category);
                } else {
                    url.searchParams.delete('category');
                }
                window.location.href = url.toString();
            });

            $('#aps-featured').on('change', function() {
                const featured = $(this).prop('checked') ? '1' : '';
                const url = new URL(window.location.href);
                if (featured) {
                    url.searchParams.set('featured', featured);
                } else {
                    url.searchParams.delete('featured');
                }
                window.location.href = url.toString();
            });

            $('#aps-status').on('change', function() {
                const status = $(this).val();
                const url = new URL(window.location.href);
                if (status) {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }
                window.location.href = url.toString();
            });

            // Clear filters
            $('.aps-filter-clear').on('click', function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('s');
                url.searchParams.delete('category');
                url.searchParams.delete('featured');
                url.searchParams.delete('status');
                window.location.href = url.toString();
            });

            // Remove individual filters
            $('.aps-filter-tag-remove').on('click', function() {
                const filter = $(this).data('filter');
                const url = new URL(window.location.href);
                url.searchParams.delete(filter);
                window.location.href = url.toString();
            });

            // Update selected count
            $(document).on('change', '.aps-product-checkbox', function() {
                const selectedCount = $('.aps-product-checkbox:checked').length;
                $('#aps-selected-count').text(selectedCount);
                
                if (selectedCount > 0) {
                    $('.aps-bulk-apply').prop('disabled', false).removeClass('disabled');
                } else {
                    $('.aps-bulk-apply').prop('disabled', true).addClass('disabled');
                }
            });

            // Sort headers
            $('.sortable').on('click', function() {
                const sort = $(this).data('sort');
                const currentSort = new URL(window.location.href).searchParams.get('sort') || '';
                const newSort = currentSort === sort + '-asc' ? sort + '-desc' : sort + '-asc';
                
                const url = new URL(window.location.href);
                url.searchParams.set('sort', newSort);
                url.searchParams.set('paged', '1');
                window.location.href = url.toString();
            });

            // Bulk actions
            $('#aps-bulk-actions-form').on('submit', function(e) {
                e.preventDefault();
                
                const action = $('#aps-action-select').val();
                const selectedIds = [];
                
                $('.aps-product-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });
                
                if (selectedIds.length === 0) {
                    alert('<?php echo esc_js__( 'Please select at least one product.', 'affiliate-product-showcase' ); ?>');
                    return;
                }
                
                if (!action) {
                    alert('<?php echo esc_js__( 'Please select an action.', 'affiliate-product-showcase' ); ?>');
                    return;
                }
                
                if (confirm('<?php echo esc_js__( 'Are you sure you want to apply this bulk action to ' . selectedIds.length . ' products?', 'affiliate-product-showcase' ); ?>')) {
                    // Show loading
                    $('.aps-bulk-actions-form').append('<div class="aps-bulk-loading"><div class="aps-bulk-loading-spinner"></div><p><?php echo esc_html__( 'Processing...', 'affiliate-product-showcase' ); ?></p></div>');
                    
                    $.ajax({
                        url: '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>',
                        type: 'POST',
                        data: {
                            action: 'aps_bulk_action',
                            nonce: '<?php echo wp_create_nonce('aps_bulk_action'); ?>',
                            action_type: action,
                            product_ids: selectedIds
                        },
                        success: function(response) {
                            $('.aps-bulk-loading').remove();
                            
                            if (response.success) {
                                alert(response.data.message);
                                window.location.reload();
                            } else {
                                alert(response.data.message || '<?php echo esc_js__( 'Action failed. Please try again.', 'affiliate-product-showcase' ); ?>');
                            }
                        },
                        error: function(xhr, status, error) {
                            $('.aps-bulk-loading').remove();
                            alert('<?php echo esc_js__( 'Request failed: ', 'affiliate-product-showcase' ) . error + '\'');
                        }
                    });
                }
            });
        });

        // Quick delete
        $('.aps-quick-delete').on('click', function(e) {
            e.preventDefault();
            
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            
            if (confirm('<?php echo esc_js__( 'Are you sure you want to delete "%1$s"? This action cannot be undone.', 'affiliate-product-showcase' ); ?>', productName))) {
                const row = $(this).closest('tr');
                row.fadeOut(function() {
                    window.location.reload();
                });
            }
        });
        });
    </script>

    <!-- Styles -->
    <style>
        .affiliate-products-wrapper {
            max-width: 1400px;
            margin: 20px 0;
        }

        .wp-heading-inline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .wp-heading-inline .page-title-action {
            margin: 0;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            color: #fff;
            background: #2271b1;
            border: none;
            border-radius: 3px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .wp-heading-inline .page-title-action:hover {
            background: #135e96;
            box-shadow: 0 2px 4px rgba(19, 94, 234, 0.3);
        }

        /* Overview Cards */
        .aps-overview-section {
            margin: 20px 0;
            padding: 0;
        }

        .aps-overview-cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .aps-overview-card {
            flex: 1;
            min-width: 150px;
            max-width: 200px;
            background: #fff;
            border: 1px solid #dcdcdb;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .aps-overview-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
            border-color: #2271b1;
        }

        .aps-count-number {
            display: block;
            font-size: 28px;
            font-weight: 700;
            color: #2271b1;
            line-height: 1.2;
        }

        .aps-count-label {
            display: block;
            font-size: 13px;
            color: #646970;
            margin-top: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Filters Section */
        .aps-filters-section {
            background: #f9f9f9;
            border: 1px solid #dcdcdb;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }

        .aps-filters-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .aps-filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 150px;
        }

        .aps-filter-label {
            font-size: 13px;
            font-weight: 500;
            color: #1d2327;
        }

        .aps-filter-input {
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            background: #fff;
            width: 100%;
            box-sizing: border-box;
        }

        .aps-filter-input:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.2);
            outline: none;
        }

        .aps-filter-select {
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            background: #fff;
            width: 100%;
            box-sizing: border-box;
            cursor: pointer;
        }

        .aps-filter-select:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.2);
            outline: none;
        }

        .aps-filter-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #1d2327;
            cursor: pointer;
        }

        /* Active Filters Badge */
        .aps-active-filters {
            background: #e7f3ff;
            border: 1px solid #c6e1ff;
            border-radius: 4px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .aps-filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #2271b1;
            color: #fff;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .aps-filter-tag-remove {
            cursor: pointer;
            font-weight: bold;
            opacity: 0.8;
            margin-left: 5px;
        }

        .aps-filter-tag-remove:hover {
            opacity: 1;
        }

        /* Bulk Actions Bar */
        .aps-bulk-actions-bar {
            background: #fff;
            border: 1px solid #dcdcdb;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }

        .aps-bulk-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .aps-bulk-action-label {
            flex: 1;
            min-width: 200px;
        }

        .aps-bulk-action-select {
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            background: #fff;
            width: 100%;
            cursor: pointer;
        }

        .aps-bulk-action-select:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.2);
            outline: none;
        }

        .aps-bulk-apply {
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            background: #2271b1;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .aps-bulk-apply:hover {
            background: #135e96;
            box-shadow: 0 2px 4px rgba(19, 94, 234, 0.3);
        }

        .aps-bulk-apply:disabled {
            background: #94a3a8;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .aps-bulk-selected-count {
            font-size: 13px;
            color: #646970;
        }

        .aps-selected-count-text span {
            font-weight: bold;
            color: #2271b1;
        }

        /* Products Table */
        .aps-products-table {
            border-collapse: collapse;
            table-layout: fixed;
            background: #fff;
            border: 1px solid #dcdcdb;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            margin: 20px 0;
        }

        .aps-products-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f6f7f7;
        }

        .aps-products-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #1d2327;
            border-bottom: 2px solid #dcdcdb;
            background: #f6f7f7;
        }

        .aps-products-table .check-column {
            width: 40px;
        }

        .aps-products-table .column-logo {
            width: 60px;
        }

        .aps-products-table .column-title {
            min-width: 200px;
        }

        .aps-products-table .column-category {
            width: 120px;
        }

        .aps-products-table .column-tags {
            width: 150px;
        }

        .aps-products-table .column-ribbon {
            width: 80px;
        }

        .aps-products-table .column-featured {
            width: 60px;
            text-align: center;
        }

        .aps-products-table .column-price {
            width: 120px;
        }

        .aps-products-table .column-status {
            width: 80px;
            text-align: center;
        }

        .sortable {
            cursor: pointer;
            position: relative;
            user-select: none;
        }

        .sortable:hover {
            background: #e7f3ff;
        }

        .aps-sort-icon {
            font-size: 12px;
            color: #646970;
            margin-left: 5px;
        }

        .aps-products-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .aps-products-table tbody tr:hover {
            background-color: #f0f0f1;
        }

        /* Product Columns */
        .aps-product-logo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            display: block;
        }

        .aps-product-no-logo {
            width: 40px;
            height: 40px;
            background: #f0f0f1;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #646970;
        }

        .aps-product-link {
            display: block;
            color: #1d2327;
            text-decoration: none;
            font-weight: 500;
        }

        .aps-product-link:hover {
            color: #2271b1;
            text-decoration: underline;
        }

        .aps-product-id {
            color: #646970;
            font-size: 11px;
            font-weight: 400;
            margin-left: 8px;
        }

        .aps-row-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .aps-quick-action {
            padding: 6px 12px;
            border: 1px solid transparent;
            border-radius: 3px;
            background: transparent;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            color: #1d2327;
            transition: all 0.2s ease;
        }

        .aps-quick-action:hover {
            transform: translateY(-1px);
        }

        .aps-quick-edit {
            color: #2271b1;
            background: #e7f3ff;
            border-color: #2271b1;
        }

        .aps-quick-delete {
            color: #d63638;
            background: #fff0f0;
            border-color: #d63638;
        }

        /* Category Tags */
        .aps-category-tag {
            display: inline-block;
            padding: 4px 8px;
            background: #f0f0f1;
            color: #1d2327;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin: 0 4px;
        }

        .aps-no-category,
        .aps-no-tags,
        .aps-no-ribbon {
            color: #646970;
            font-style: italic;
            font-size: 12px;
        }

        /* Ribbons */
        .aps-ribbon-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .aps-ribbon-hot {
            background: #dc3545;
            color: #fff;
        }

        .aps-ribbon-new {
            background: #2271b1;
            color: #fff;
        }

        .aps-ribbon-sale {
            background: #9333ea;
            color: #fff;
        }

        .aps-no-ribbon {
            background: #f0f0f1;
            color: #646970;
        }

        /* Featured */
        .aps-featured {
            color: #2271b1;
            font-size: 16px;
        }

        .aps-not-featured {
            color: #ccc;
            font-size: 16px;
        }

        /* Price */
        .aps-current-price {
            color: #1d2327;
            font-weight: 600;
            font-size: 14px;
        }

        .aps-original-price {
            color: #646970;
            text-decoration: line-through;
            margin-left: 8px;
            font-size: 13px;
        }

        .aps-discount-badge {
            display: inline-block;
            margin-left: 8px;
            padding: 2px 6px;
            background: #00a32a;
            color: #fff;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }

        .aps-no-price {
            color: #646970;
            font-style: italic;
            font-size: 14px;
        }

        /* Status */
        .aps-status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .aps-status-published {
            background: #00a32a;
            color: #fff;
        }

        .aps-status-draft {
            background: #6c757d;
            color: #fff;
        }

        .aps-status-trash {
            background: #dc3545;
            color: #fff;
        }

        /* Tags */
        .aps-tag-tag {
            display: inline-block;
            padding: 2px 8px;
            background: #f0f0f1;
            color: #1d2327;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 400;
            margin: 0 4px;
        }

        /* Empty Message */
        .aps-empty-message {
            text-align: center;
            padding: 60px 20px;
            color: #646970;
            font-size: 14px;
        }

        /* Pagination */
        .aps-pagination-section {
            background: #fff;
            border: 1px solid #dcdcdb;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }

        .aps-pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .aps-per-page-select-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .aps-per-page-select {
            padding: 8px 12px;
            font-size: 13px;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            background: #fff;
            cursor: pointer;
        }

        .aps-per-page-select:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.2);
            outline: none;
        }

        .aps-pagination-info {
            font-size: 13px;
            color: #646970;
        }

        .aps-page-links {
            display: flex;
            gap: 5px;
        }

        .aps-page-link {
            padding: 8px 16px;
            font-size: 13px;
            border: 1px solid #dcdcdb;
            border-radius: 3px;
            background: #fff;
            color: #2271b1;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .aps-page-link:hover {
            background: #2271b1;
            color: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .aps-page-link.disabled {
            background: #f6f7f7;
            color: #646970;
            cursor: not-allowed;
            opacity: 0.7;
            border-color: #dcdcdb;
        }

        .aps-page-link.current {
            background: #2271b1;
            color: #fff;
            cursor: default;
            font-weight: 600;
        }

        /* Footer */
        .aps-footer {
            border-top: 1px solid #dcdcdb;
            padding: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0;
        }

        .aps-total-count {
            font-size: 13px;
            color: #646970;
        }

        /* Bulk Loading */
        .aps-bulk-loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100000;
            padding: 20px;
        }

        .aps-bulk-loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #fff;
            border-top: 5px solid #fff;
            border-radius: 50%;
            animation: aps-spin 0.8s linear infinite;
        }

        @keyframes aps-spin {
            0% {
                transform: rotate(0deg);
                border-top-color: #2271b1;
            }
            100% {
                transform: rotate(360deg);
                border-top-color: #2271b1;
            }
        }

        .aps-bulk-loading-text {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            margin-left: 15px;
        }

        /* Responsive */
        @media (max-width: 782px) {
            .aps-overview-cards {
                justify-content: center;
            }

            .aps-overview-card {
                flex: 1;
                min-width: 120px;
                max-width: 150px;
            }

            .aps-count-number {
                font-size: 24px;
            }

            .aps-filters-section,
            .aps-bulk-actions-bar,
            .aps-pagination-section {
                padding: 15px;
                margin: 15px 0;
            }

            .aps-filters-row,
            .aps-bulk-row,
            .aps-pagination-controls {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .aps-filter-group {
                width: 100%;
            }

            .aps-bulk-action-label {
                width: 100%;
                margin-bottom: 10px;
            }

            .aps-filter-select,
            .aps-filter-input,
            .aps-bulk-action-select,
            .aps-per-page-select {
                width: 100%;
            }

            .aps-bulk-apply {
                width: 100%;
                text-align: center;
            }

            .aps-page-links {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .aps-pagination-controls {
                flex-direction: column-reverse;
                align-items: stretch;
                gap: 10px;
            }

            .aps-products-table {
                display: block;
                overflow-x: auto;
            }

            .aps-products-table thead {
                position: relative;
            }

            .aps-products-table .check-column,
            .aps-products-table .column-logo {
                display: none;
            }

            .aps-products-table .column-title {
                min-width: 150px;
            }

            .aps-products-table .column-category,
            .aps-products-table .column-tags,
            .aps-products-table .column-ribbon {
                display: none;
            }

            .aps-products-table .column-price {
                min-width: 100px;
            }

            .aps-products-table .column-status {
                display: none;
            }

            .aps-products-table td {
                padding: 12px 8px;
            }

            .aps-products-table .column-cb {
                display: none;
            }

            .aps-products-table .column-logo {
                display: none;
            }

            .aps-product-no-logo {
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
            }

            .aps-footer {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .aps-overview-cards {
                flex-direction: column;
                gap: 10px;
            }

            .aps-filters-section h2,
            .aps-bulk-actions-bar h2 {
                font-size: 14px;
            }
        }
    </style>
</div>
