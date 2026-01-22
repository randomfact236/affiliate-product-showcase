<?php
/**
 * Manage Products Admin Page
 *
 * Displays all products in a table with filters, bulk actions, and pagination.
 * Similar to WordPress Posts → All Posts page but with custom columns.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Plugin\Constants;

// Prevent direct access
defined( 'ABSPATH' ) || exit;

/**
 * Render manage products page
 */
function aps_render_manage_products_page(): void {
	// Get current user
	$current_user = wp_get_current_user();
	
	// Check capabilities
	if ( ! current_user_can( 'manage_options', 'aps_product' ) ) {
		wp_die( __( 'Sorry, you are not allowed to manage products.', 'affiliate-product-showcase' ) );
	}
	
	// Get current page, per_page, and search query
	$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$per_page = isset( $_GET['per_page'] ) ? intval( $_GET['per_page'] ) : 20;
	$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
	$category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
	$tag = isset( $_GET['tag'] ) ? sanitize_text_field( $_GET['tag'] ) : '';
	$ribbon = isset( $_GET['ribbon'] ) ? sanitize_text_field( $_GET['ribbon'] ) : '';
	$show_featured = isset( $_GET['featured'] ) && $_GET['featured'] === '1';
	
	// Build query args
	$args = [
		'post_type'      => 'aps_product',
		'post_status'    => [ 'publish', 'draft', 'trash' ],
		'posts_per_page' => $per_page,
		'paged'         => $page,
	];
	
	// Add search
	if ( ! empty( $search ) ) {
		$args['s'] = $search;
	}
	
	// Add category filter
	if ( ! empty( $category ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'aps_category',
				'field'    => 'slug',
				'terms'    => $category,
			],
		];
	}
	
	// Add tag filter
	if ( ! empty( $tag ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'aps_tag',
				'field'    => 'slug',
				'terms'    => $tag,
			],
		];
	}
	
	// Add ribbon filter
	if ( ! empty( $ribbon ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'aps_ribbon',
				'field'    => 'slug',
				'terms'    => $ribbon,
			],
		];
	}
	
	// Add featured filter
	if ( $show_featured ) {
		$args['meta_query'] = [
			'featured' => [
				'key'   => '_featured',
				'value' => '1',
				'compare' => '=',
			],
		];
	}
	
	// Query products
	$query = new \WP_Query( $args );
	
	// Get counts
	$all_count = wp_count_posts( [ 'post_type' => 'aps_product' ] );
	$publish_count = wp_count_posts( [ 'post_type' => 'aps_product', 'post_status' => 'publish' ] );
	$draft_count = wp_count_posts( [ 'post_type' => 'aps_product', 'post_status' => 'draft' ] );
	$trash_count = wp_count_posts( [ 'post_type' => 'aps_product', 'post_status' => 'trash' ] );
	
	// Get product count for current query
	$found_count = $query->found_posts;
	
	// Calculate pagination
	$total_pages = ceil( $found_count / $per_page );
	$current_page = $page;
	$prev_page = $current_page - 1;
	$next_page = $current_page + 1;
	
	// Get products
	$products = $query->posts;
	
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Manage Products' ); ?>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aps_product' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add New Product' ); ?> <span class="dashicons dashicons-plus"></span>
			</a>
		</h1>
		
		<!-- Overview Section -->
		<div class="aps-overview-section">
			<div class="aps-overview-cards">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="aps-overview-card">
					<span class="aps-count-number"><?php echo esc_html( number_format_i18n( $all_count ) ); ?></span>
					<span class="aps-count-label"><?php esc_html_e( 'All' ); ?></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=publish' ) ); ?>" class="aps-overview-card">
					<span class="aps-count-number"><?php echo esc_html( number_format_i18n( $publish_count ) ); ?></span>
					<span class="aps-count-label"><?php esc_html_e( 'Published' ); ?></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=draft' ) ); ?>" class="aps-overview-card">
					<span class="aps-count-number"><?php echo esc_html( number_format_i18n( $draft_count ) ); ?></span>
					<span class="aps-count-label"><?php esc_html_e( 'Draft' ); ?></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=trash' ) ); ?>" class="aps-overview-card">
					<span class="aps-count-number"><?php echo esc_html( number_format_i18n( $trash_count ) ); ?></span>
					<span class="aps-count-label"><?php esc_html_e( 'Trash' ); ?></span>
				</a>
			</div>
		</div>
		
		<!-- Action Buttons Row -->
		<div class="aps-action-buttons">
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aps_product' ) ); ?>" class="button button-primary button-large">
				<span class="dashicons dashicons-plus"></span>
				<?php esc_html_e( 'Add New Product' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&post_status=trash' ) ); ?>" class="button">
				<span class="dashicons dashicons-trash"></span>
				<?php esc_html_e( 'Trash' ); ?>
			</a>
			<button type="button" class="button" onclick="aps_bulk_upload_modal()">
				<span class="dashicons dashicons-upload"></span>
				<?php esc_html_e( 'Bulk Upload' ); ?>
			</button>
			<button type="button" class="button" onclick="aps_check_all_links()">
				<span class="dashicons dashicons-admin-links"></span>
				<?php esc_html_e( 'Check Links' ); ?>
			</button>
		</div>
		
		<!-- Filters Section -->
		<div class="aps-filters-section">
			<!-- Select Action Dropdown -->
			<select name="aps-action" id="aps-action-select" class="aps-filter-select">
				<option value=""><?php esc_html_e( 'Bulk Actions' ); ?></option>
				<option value="publish"><?php esc_html_e( 'Publish' ); ?></option>
				<option value="draft"><?php esc_html_e( 'Set Draft' ); ?></option>
				<option value="in-stock"><?php esc_html_e( 'Set In Stock' ); ?></option>
				<option value="out-of-stock"><?php esc_html_e( 'Set Out of Stock' ); ?></option>
				<option value="reset-clicks"><?php esc_html_e( 'Reset Clicks' ); ?></option>
				<option value="trash"><?php esc_html_e( 'Move to Trash' ); ?></option>
				<option value="restore"><?php esc_html_e( 'Restore from Trash' ); ?></option>
				<option value="delete-permanently"><?php esc_html_e( 'Delete Permanently' ); ?></option>
			</select>
			
			<!-- Search Input -->
			<input type="text" 
				   id="aps-search-input" 
				   class="aps-search-input" 
				   placeholder="<?php esc_attr_e( 'Search products...' ); ?>" 
				   value="<?php echo esc_attr( $search ); ?>">
			
			<!-- Category Filter -->
			<select name="aps-category" id="aps-category-select" class="aps-filter-select">
				<option value=""><?php esc_html_e( 'All Categories' ); ?></option>
				<?php
				$categories = get_terms( [
					'taxonomy' => 'aps_category',
					'hide_empty' => false,
				] );
				foreach ( $categories as $category ) {
					echo '<option value="' . esc_attr( $category->slug ) . '">' . esc_html( $category->name ) . '</option>';
				}
				?>
			</select>
			
			<!-- Sort Dropdown -->
			<select name="aps-sort" id="aps-sort-select" class="aps-filter-select">
				<option value="date-desc"><?php esc_html_e( 'Latest' ); ?></option>
				<option value="date-asc"><?php esc_html_e( 'Oldest' ); ?></option>
				<option value="title-asc"><?php esc_html_e( 'A-Z' ); ?></option>
				<option value="title-desc"><?php esc_html_e( 'Z-A' ); ?></option>
				<option value="price-asc"><?php esc_html_e( 'Price: Low to High' ); ?></option>
				<option value="price-desc"><?php esc_html_e( 'Price: High to Low' ); ?></option>
				<option value="rating-asc"><?php esc_html_e( 'Rating: High to Low' ); ?></option>
				<option value="rating-desc"><?php esc_html_e( 'Rating: Low to High' ); ?></option>
			</select>
			
			<!-- Show Featured Checkbox -->
			<label class="aps-filter-checkbox">
				<input type="checkbox" id="aps-featured-checkbox" <?php checked( $show_featured ) ? 'checked' : ''; ?>>
				<?php esc_html_e( 'Show Featured' ); ?>
			</label>
			
			<!-- Clear Filters Button -->
			<button type="button" class="button button-small" onclick="aps_clear_filters()">
				<?php esc_html_e( 'Clear Filters' ); ?> <span class="dashicons dashicons-dismiss"></span>
			</button>
		</div>
		
		<!-- Active Filters Badge -->
		<?php if ( ! empty( $category ) || ! empty( $tag ) || ! empty( $ribbon ) || ! empty( $search ) || $show_featured ) : ?>
		<div class="aps-active-filters">
			<?php if ( ! empty( $category ) ) : ?>
				<span class="aps-filter-tag">
					<?php esc_html_e( get_term( $category, 'aps_category' )->name ); ?>
					<span class="aps-filter-tag-remove" onclick="aps_remove_filter('category', '<?php echo esc_js( $category ); ?>')">×</span>
				</span>
			<?php endif; ?>
			
			<?php if ( ! empty( $tag ) ) : ?>
				<span class="aps-filter-tag">
					<?php echo esc_html( get_term( $tag, 'aps_tag' )->name ); ?>
					<span class="aps-filter-tag-remove" onclick="aps_remove_filter('tag', '<?php echo esc_js( $tag ); ?>')">×</span>
				</span>
			<?php endif; ?>
			
			<?php if ( ! empty( $ribbon ) ) : ?>
				<span class="aps-filter-tag">
					<?php echo esc_html( get_term( $ribbon, 'aps_ribbon' )->name ); ?>
					<span class="aps-filter-tag-remove" onclick="aps_remove_filter('ribbon', '<?php echo esc_js( $ribbon ); ?>')">×</span>
				</span>
			<?php endif; ?>
			
			<?php if ( $show_featured ) : ?>
				<span class="aps-filter-tag">
					<?php esc_html_e( 'Featured' ); ?>
					<span class="aps-filter-tag-remove" onclick="aps_remove_filter('featured', '1')">×</span>
				</span>
			<?php endif; ?>
			
			<?php if ( ! empty( $search ) ) : ?>
				<span class="aps-filter-tag">
					<?php esc_html_e( 'Search: ' . esc_html( $search ) ); ?>
					<span class="aps-filter-tag-remove" onclick="aps_remove_filter('search', '')">×</span>
				</span>
			<?php endif; ?>
			
			<button type="button" class="button button-small" onclick="aps_clear_all_filters()">
				<?php esc_html_e( 'Clear All' ); ?>
			</button>
		</div>
		<?php endif; ?>
		
		<!-- Products Table -->
		<form id="aps-products-form" method="post">
			<?php wp_nonce_field( 'aps_products_action', 'aps_products_nonce' ); ?>
			<input type="hidden" name="post_type" value="aps_product">
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<td class="manage-column column-cb check-column">
							<input type="checkbox" id="aps-select-all" class="aps-select-all">
						</td>
						<th class="manage-column column-logo">
							<?php esc_html_e( 'Logo' ); ?>
						</th>
						<th class="manage-column column-product">
							<?php esc_html_e( 'Product' ); ?>
						</th>
						<th class="manage-column column-category">
							<?php esc_html_e( 'Category' ); ?>
						</th>
						<th class="manage-column column-tags">
							<?php esc_html_e( 'Tags' ); ?>
						</th>
						<th class="manage-column column-ribbon">
							<?php esc_html_e( 'Ribbon' ); ?>
						</th>
						<th class="manage-column column-featured">
							<?php esc_html_e( 'Featured' ); ?>
						</th>
						<th class="manage-column column-price">
							<?php esc_html_e( 'Price' ); ?>
						</th>
						<th class="manage-column column-status">
							<?php esc_html_e( 'Status' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $products->have_posts() ) : ?>
						<?php foreach ( $products->posts as $product ) : ?>
							<?php
							$product_id = $product->ID;
							$product_title = get_the_title( $product );
							$edit_url = get_edit_post_link( $product_id );
							$delete_url = get_delete_post_link( $product_id );
							$status = get_post_status( $product->ID );
							$featured = get_post_meta( $product_id, '_featured', true ) ? '1' : '0';
							$categories = get_the_terms( $product_id, 'aps_category' );
							$tags = get_the_terms( $product_id, 'aps_tag' );
							$ribbons = get_the_terms( $product_id, 'aps_ribbon' );
							$logo_url = get_post_meta( $product_id, '_logo_image', true );
							$brand_image_url = get_post_meta( $product_id, '_brand_image', true );
							$price = get_post_meta( $product_id, '_current_price', true );
							$original_price = get_post_meta( $product_id, '_original_price', true );
							$affiliate_url = get_post_meta( $product_id, '_affiliate_url', true );
							$views = get_post_meta( $product_id, '_views', true );
							
							// Calculate discount
							$discount = 0;
							if ( $price && $original_price && $original_price > 0 ) {
								$discount = round( ( ( $original_price - $price ) / $original_price ) * 100 );
							}
							
							// Status badge
							if ( $status === 'publish' ) {
								$status_badge_class = 'status-published';
								$status_badge_text = __( 'Published', 'affiliate-product-showcase' );
							} elseif ( $status === 'draft' ) {
								$status_badge_class = 'status-draft';
								$status_badge_text = __( 'Draft', 'affiliate-product-showcase' );
							} else {
								$status_badge_class = 'status-trash';
								$status_badge_text = __( 'Trash', 'affiliate-product-showcase' );
							}
							?>
							<tr class="aps-product-row">
								<td class="manage-column column-cb">
									<input type="checkbox" name="product_ids[]" value="<?php echo esc_attr( $product_id ); ?>" class="aps-product-checkbox">
								</td>
								<td class="manage-column column-logo">
									<?php if ( $logo_url ) : ?>
										<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $product_title ); ?>" class="aps-product-logo">
									<?php else : ?>
										<span class="aps-product-no-logo">No Logo</span>
									<?php endif; ?>
								</td>
								<td class="manage-column column-product">
									<strong>
										<?php echo esc_html( $product_title ); ?>
										<span class="aps-product-id">#<?php echo esc_html( $product_id ); ?></span>
									</strong>
									<div class="row-actions">
										<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small">
											<span class="dashicons dashicons-edit"></span>
											<?php esc_html_e( 'Edit' ); ?>
										</a>
										<a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js( sprintf( __( 'Are you sure you want to delete "%s"?', 'affiliate-product-showcase' ), $product_title ) ); ?>'); ?>">
											<span class="dashicons dashicons-trash"></span>
											<?php esc_html_e( 'Delete' ); ?>
										</a>
									</div>
								</td>
								<td class="manage-column column-category">
									<?php if ( ! empty( $categories ) ) : ?>
										<?php foreach ( $categories as $category ) : ?>
											<span class="aps-category-tag">
												<?php echo esc_html( $category->name ); ?>
												<span class="aps-category-tag-remove" onclick="aps_remove_filter('category', '<?php echo esc_js( $category->slug ); ?>')">×</span>
											</span>
										<?php endforeach; ?>
									<?php else : ?>
										<span class="aps-no-category">—</span>
									<?php endif; ?>
								</td>
								<td class="manage-column column-tags">
									<?php if ( ! empty( $tags ) ) : ?>
										<?php foreach ( $tags as $tag ) : ?>
											<span class="aps-tag-tag"><?php echo esc_html( $tag->name ); ?></span>
										<?php endforeach; ?>
									<?php else : ?>
										<span class="aps-no-tags">—</span>
									<?php endif; ?>
								</td>
								<td class="manage-column column-ribbon">
									<?php if ( ! empty( $ribbons ) ) : ?>
										<?php foreach ( $ribbons as $ribbon ) : ?>
											<span class="aps-ribbon-badge aps-ribbon-<?php echo esc_attr( $ribbon->slug ); ?>">
												<?php echo esc_html( strtoupper( $ribbon->name ) ); ?>
											</span>
										<?php endforeach; ?>
									<?php else : ?>
										<span class="aps-no-ribbon">—</span>
									<?php endif; ?>
								</td>
								<td class="manage-column column-featured">
									<?php if ( $featured ) : ?>
										<span class="dashicons dashicons-star-filled aps-featured"></span>
									<?php else : ?>
										<span class="dashicons dashicons-star-empty"></span>
									<?php endif; ?>
								</td>
								<td class="manage-column column-price">
									<?php if ( $price ) : ?>
										<span class="aps-current-price">
												<?php echo esc_html( '$' . number_format_i18n( $price, 2 ) ); ?>
										</span>
										<?php if ( $original_price && $original_price > $price ) : ?>
												<?php echo esc_html( ' <s>$' . number_format_i18n( $original_price, 2 ) . '</s>' ); ?>
										<?php endif; ?>
										<?php if ( $discount > 0 ) : ?>
												<span class="aps-discount-badge">
													<?php echo esc_html( $discount . '% OFF' ); ?>
												</span>
										<?php endif; ?>
									<?php else : ?>
										<span class="aps-no-price">—</span>
									<?php endif; ?>
								</td>
								<td class="manage-column column-status">
									<span class="aps-status-badge <?php echo esc_attr( $status_badge_class ); ?>">
										<?php echo esc_html( $status_badge_text ); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="9" class="aps-empty-message">
								<?php esc_html_e( 'No products found.' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</form>
		
		<!-- Pagination -->
		<div class="aps-pagination-section">
			<div class="tablenav top">
				<div class="alignleft actions">
					<?php if ( $current_page > 1 ) : ?>
						<a href="<?php echo esc_url( add_query_arg( admin_url( 'edit.php?post_type=aps_product' ), 'paged', $current_page - 1 ) ); ?>" class="button button-small">
							<span class="screen-reader-text"><?php esc_html_e( 'Previous page' ); ?></span>
							<span aria-hidden="true">‹</span>
						</a>
					<?php endif; ?>
					
					<span class="displaying-num">
						<?php echo sprintf( _n( '%1$s of %2$s displayed', 'affiliate-product-showcase' ), number_format_i18n( min( $page, 1 ) * $per_page - $per_page + 1 ), number_format_i18n( min( $page, 1 ) * $per_page ), number_format_i18n( $found_count ) ); ?>
					</span>
				</div>
				
				<div class="alignright">
					<?php if ( $current_page < $total_pages ) : ?>
						<a href="<?php echo esc_url( add_query_arg( admin_url( 'edit.php?post_type=aps_product' ), 'paged', $current_page + 1 ) ); ?>" class="button button-small">
							<span class="screen-reader-text"><?php esc_html_e( 'Next page' ); ?></span>
							<span aria-hidden="true">›</span>
						</a>
					<?php endif; ?>
					
					<select name="per_page" class="aps-per-page-select" onchange="this.form.submit()">
						<?php
						$per_page_options = [ 12, 20, 50, 100 ];
						foreach ( $per_page_options as $option ) {
							$selected = selected( $per_page, $option, false ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $option ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
		
		<!-- Footer -->
		<div class="aps-footer">
			<div class="alignleft">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Return to All Products' ); ?>
				</a>
			</div>
			
			<div class="alignright">
				<span class="aps-total-count">
					<?php echo esc_html( number_format_i18n( $found_count ) . ' ' . _n( 'product total', 'product total', $found_count ) ); ?>
				</span>
			</div>
		</div>
	</div>
</div>

<script>
// AJAX functions for filtering and bulk actions
function aps_apply_filter( filterType, filterValue ) {
	var url = new URL( window.location.href );
	url.searchParams.delete( filterType );
	url.searchParams.set( filterType, filterValue );
	window.location.href = url.toString();
}

function aps_remove_filter( filterType, filterValue ) {
	var url = new URL( window.location.href );
	url.searchParams.delete( filterType );
	
	// Update filter params
	var search = url.searchParams.get( 's' );
	var category = url.searchParams.get( 'category' );
	var tag = url.searchParams.get( 'tag' );
	var ribbon = url.searchParams.get( 'ribbon' );
	var featured = url.searchParams.get( 'featured' );
	
	switch( filterType ) {
		case 'category':
			category = '';
			break;
		case 'tag':
			tag = '';
			break;
		case 'ribbon':
			ribbon = '';
			break;
		case 'search':
			search = '';
			break;
		case 'featured':
			featured = '';
			break;
	}
	
	if ( search ) url.searchParams.set( 's', search );
	if ( category ) url.searchParams.set( 'category', category );
	if ( tag ) url.searchParams.set( 'tag', tag );
	if ( ribbon ) url.searchParams.set( 'ribbon', ribbon );
	if ( featured ) url.searchParams.set( 'featured', featured );
	
	window.location.href = url.toString();
}

function aps_clear_filters() {
	var url = new URL( window.location.href );
	url.searchParams.delete( 's' );
	url.searchParams.delete( 'category' );
	url.searchParams.delete( 'tag' );
	url.searchParams.delete( 'ribbon' );
	url.searchParams.delete( 'featured' );
	window.location.href = url.toString();
}

function aps_clear_all_filters() {
	var url = new URL( window.location.href );
	url.searchParams.delete( 's' );
	url.searchParams.delete( 'category' );
	url.searchParams.delete( 'tag' );
	url.searchParams.delete( 'ribbon' );
	url.searchParams.delete( 'featured' );
	window.location.href = url.toString();
}

function aps_select_all() {
	jQuery( '.aps-product-checkbox' ).prop( 'checked', true );
}

function aps_bulk_upload_modal() {
	alert( 'Bulk Upload modal would open here.' );
}

function aps_check_all_links() {
	alert( 'Checking all affiliate links...' );
}

// Select all functionality
document.addEventListener( 'DOMContentLoaded', function() {
	var selectAllCheckbox = document.getElementById( 'aps-select-all' );
	var productCheckboxes = document.querySelectorAll( '.aps-product-checkbox' );
	
	if ( selectAllCheckbox ) {
		selectAllCheckbox.addEventListener( 'change', function() {
			var isChecked = this.checked;
			productCheckboxes.forEach( function( checkbox ) {
				checkbox.checked = isChecked;
			} );
		} );
	}
} );
</script>
