<?php
/**
 * Categories Table Template
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Categories', 'affiliate-product-showcase' ); ?>
	</h1>
	<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=aps_category&post_type=aps_product' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New Category', 'affiliate-product-showcase' ); ?>
	</a>
	<hr class="wp-header-end">
</div>

<!-- Search Form -->
<form method="get" class="wp-filter">
	<input type="hidden" name="page" value="aps-categories">
	
	<p class="search-box">
		<label class="screen-reader-text" for="category-search-input">
			<?php esc_html_e( 'Search Categories', 'affiliate-product-showcase' ); ?>
		</label>
		<input
			type="search"
			id="category-search-input"
			name="s"
			value="<?php echo esc_attr( $this->search ); ?>"
			class="wp-filter-search"
			placeholder="<?php esc_attr_e( 'Search categories...', 'affiliate-product-showcase' ); ?>"
		>
		<input
			type="submit"
			id="search-submit"
			class="button"
			value="<?php esc_attr_e( 'Search Categories', 'affiliate-product-showcase' ); ?>"
		>
	</p>
</form>

<!-- Categories Table Form -->
<form method="post" id="aps-categories-form">
	<?php wp_nonce_field( 'aps_category_bulk', 'aps_category_bulk_nonce' ); ?>
	
	<div class="tablenav top">
		<!-- Bulk Actions -->
		<div class="alignleft actions bulkactions">
			<select name="aps_category_bulk_action" id="bulk-action-selector-top">
				<option value="-1"><?php esc_html_e( 'Bulk actions', 'affiliate-product-showcase' ); ?></option>
				<option value="delete"><?php esc_html_e( 'Delete', 'affiliate-product-showcase' ); ?></option>
				<option value="delete_permanently"><?php esc_html_e( 'Delete Permanently', 'affiliate-product-showcase' ); ?></option>
				<option value="toggle_featured"><?php esc_html_e( 'Toggle Featured', 'affiliate-product-showcase' ); ?></option>
			</select>
			<input type="submit" name="doaction" id="doaction" class="button action" value="<?php esc_attr_e( 'Apply', 'affiliate-product-showcase' ); ?>">
		</div>
		
		<!-- Pagination (top) -->
		<div class="tablenav-pages">
			<span class="displaying-num">
				<?php
				printf(
					_n( '%s item', '%s items', $this->get_total_items(), 'affiliate-product-showcase' ),
					number_format_i18n( $this->get_total_items() )
				);
				?>
			</span>
			<span class="pagination-links">
				<?php
				$total_pages = ceil( $this->get_total_items() / $this->per_page );
				if ( $total_pages > 1 ) {
					$current_url = admin_url( 'admin.php' );
					$query_args = [
						'page'    => 'aps-categories',
						'paged'    => 1,
						's'        => $this->search,
						'orderby'  => $this->orderby,
						'order'    => $this->order,
					];
					
					// First page
					$query_args['paged'] = 1;
					echo '<a class="first-page button" href="' . esc_url( add_query_arg( $query_args, $current_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'First page', 'affiliate-product-showcase' ) . '</span><span aria-hidden="true">««</span></a>';
					
					// Previous page
					if ( $this->page > 1 ) {
						$query_args['paged'] = $this->page - 1;
						echo '<a class="prev-page button" href="' . esc_url( add_query_arg( $query_args, $current_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Previous page', 'affiliate-product-showcase' ) . '</span><span aria-hidden="true">‹</span></a>';
					} else {
						echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
					}
					
					// Current page info
					echo '<span class="paging-input">';
					printf(
						esc_html_x( '%1$s of %2$s', 'paging', 'affiliate-product-showcase' ),
						'<span class="total-pages">' . number_format_i18n( $this->page ) . '</span>',
						'<span class="total-pages">' . number_format_i18n( $total_pages ) . '</span>'
					);
					echo '</span>';
					
					// Next page
					if ( $this->page < $total_pages ) {
						$query_args['paged'] = $this->page + 1;
						echo '<a class="next-page button" href="' . esc_url( add_query_arg( $query_args, $current_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Next page', 'affiliate-product-showcase' ) . '</span><span aria-hidden="true">›</span></a>';
					} else {
						echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
					}
					
					// Last page
					$query_args['paged'] = $total_pages;
					echo '<a class="last-page button" href="' . esc_url( add_query_arg( $query_args, $current_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Last page', 'affiliate-product-showcase' ) . '</span><span aria-hidden="true">»»</span></a>';
				}
				?>
			</span>
		</div>
		
		<br class="clear">
	</div>

	<!-- Categories Table -->
	<table class="wp-list-table widefat fixed striped table-view-list" id="aps-categories-table">
		<thead>
			<tr>
				<td class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-1">
						<?php esc_html_e( 'Select All', 'affiliate-product-showcase' ); ?>
					</label>
					<input id="cb-select-all-1" type="checkbox">
				</td>
				<th scope="col" class="manage-column column-name column-primary sortable <?php echo esc_attr( $this->orderby === 'name' ? 'sorted ' . esc_attr( strtolower( $this->order ) ) : 'sortable desc' ); ?>">
					<a href="<?php echo esc_url( add_query_arg( [ 'orderby' => 'name', 'order' => $this->orderby === 'name' && $this->order === 'ASC' ? 'DESC' : 'ASC' ] ) ); ?>">
						<span><?php esc_html_e( 'Name', 'affiliate-product-showcase' ); ?></span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th scope="col" class="manage-column column-slug">
					<?php esc_html_e( 'Slug', 'affiliate-product-showcase' ); ?>
				</th>
				<th scope="col" class="manage-column column-description">
					<?php esc_html_e( 'Description', 'affiliate-product-showcase' ); ?>
				</th>
				<th scope="col" class="manage-column column-count">
					<?php esc_html_e( 'Count', 'affiliate-product-showcase' ); ?>
				</th>
				<th scope="col" class="manage-column column-featured">
					<?php esc_html_e( 'Featured', 'affiliate-product-showcase' ); ?>
				</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			<?php if ( empty( $categories ) ) : ?>
				<tr class="no-items">
					<td class="colspanchange" colspan="6">
						<?php esc_html_e( 'No categories found.', 'affiliate-product-showcase' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php
					$edit_url = admin_url( 'edit-tags.php?taxonomy=aps_category&post_type=aps_product&tag_ID=' . $category->id . '&action=edit' );
					$parent_category = $category->parent_id > 0 ? get_term( $category->parent_id, 'aps_category' ) : null;
					?>
					<tr id="category-<?php echo esc_attr( $category->id ); ?>">
						<th scope="row" class="check-column">
							<label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $category->id ); ?>">
								<?php printf( esc_html__( 'Select %s', 'affiliate-product-showcase' ), esc_html( $category->name ) ); ?>
							</label>
							<input id="cb-select-<?php echo esc_attr( $category->id ); ?>" type="checkbox" name="category_ids[]" value="<?php echo esc_attr( $category->id ); ?>">
						</th>
						<td class="column-name column-primary" data-colname="<?php esc_attr_e( 'Name', 'affiliate-product-showcase' ); ?>">
							<strong>
								<a class="row-title" href="<?php echo esc_url( $edit_url ); ?>">
									<?php echo esc_html( $category->name ); ?>
								</a>
								<?php if ( $category->featured ) : ?>
									<span class="dashicons dashicons-star-filled aps-featured-indicator" aria-hidden="true"></span>
								<?php endif; ?>
							</strong>
							<br>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'affiliate-product-showcase' ); ?></a>
								</span>
								<span class="inline hide-if-no-js"> | </span>
								<span class="trash">
									<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=aps_delete_category&category_id=' . $category->id ), 'aps_delete_category_' . $category->id ); ?>" class="submitdelete"><?php esc_html_e( 'Delete', 'affiliate-product-showcase' ); ?></a>
								</span>
								<span class="inline hide-if-no-js"> | </span>
								<span class="view">
									<a href="<?php echo esc_url( get_term_link( $category->id, 'aps_category' ) ); ?>"><?php esc_html_e( 'View', 'affiliate-product-showcase' ); ?></a>
								</span>
							</div>
							<?php if ( $parent_category ) : ?>
								<div class="parent-category">
									<?php
									printf(
										esc_html__( 'Parent: %s', 'affiliate-product-showcase' ),
										esc_html( $parent_category->name )
									);
									?>
								</div>
							<?php endif; ?>
						</td>
						<td class="column-slug" data-colname="<?php esc_attr_e( 'Slug', 'affiliate-product-showcase' ); ?>">
							<?php echo esc_html( $category->slug ); ?>
						</td>
						<td class="column-description" data-colname="<?php esc_attr_e( 'Description', 'affiliate-product-showcase' ); ?>">
							<?php echo esc_html( $category->description ); ?>
						</td>
						<td class="column-count" data-colname="<?php esc_attr_e( 'Count', 'affiliate-product-showcase' ); ?>">
							<?php echo esc_html( $category->count ); ?>
						</td>
						<td class="column-featured" data-colname="<?php esc_attr_e( 'Featured', 'affiliate-product-showcase' ); ?>">
							<?php if ( $category->featured ) : ?>
								<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
							<?php else : ?>
								<span class="dashicons dashicons-minus" aria-hidden="true"></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		
		<tfoot>
			<tr>
				<td class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-2">
						<?php esc_html_e( 'Select All', 'affiliate-product-showcase' ); ?>
					</label>
					<input id="cb-select-all-2" type="checkbox">
				</td>
				<th scope="col" class="manage-column column-name column-primary sortable <?php echo esc_attr( $this->orderby === 'name' ? 'sorted ' . esc_attr( strtolower( $this->order ) ) : 'sortable desc' ); ?>">
					<a href="<?php echo esc_url( add_query_arg( [ 'orderby' => 'name', 'order' => $this->orderby === 'name' && $this->order === 'ASC' ? 'DESC' : 'ASC' ] ) ); ?>">
						<span><?php esc_html_e( 'Name', 'affiliate-product-showcase' ); ?></span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th scope="col" class="manage-column column-slug">
					<?php esc_html_e( 'Slug', 'affiliate-product-showcase' ); ?>
				</th>
				<th scope="col" class="manage-column column-description">
					<?php esc_html_e( 'Description', 'affiliate-product-showcase' ); ?>
				</th>
				<th scope="col" class="manage-column column-count">
					<?php esc_html_e( 'Count', 'affiliate-product-showcase' ); ?>
				</th>
				<th scope="col" class="manage-column column-featured">
					<?php esc_html_e( 'Featured', 'affiliate-product-showcase' ); ?>
				</th>
			</tr>
		</tfoot>
	</table>
	
	<!-- Pagination (bottom) -->
	<div class="tablenav bottom">
		<div class="alignleft actions bulkactions">
			<!-- Bulk actions repeated at bottom -->
		</div>
		<div class="tablenav-pages">
			<!-- Pagination repeated at bottom -->
		</div>
		<br class="clear">
	</div>
</form>

<style>
.aps-featured-indicator {
	color: #ffb900;
	margin-left: 5px;
}

.aps-featured-indicator.dashicons-star-filled:before {
	font-size: 16px;
}
</style>