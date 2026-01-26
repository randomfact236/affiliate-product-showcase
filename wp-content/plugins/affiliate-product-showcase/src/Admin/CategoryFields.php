<?php
/**
 * Category Fields
 *
 * Adds custom fields to category edit/add forms including:
 * - Featured checkbox
 * - Image URL field
 * - Default category checkbox
 * - Auto-assignment to products without category
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Category Fields
 *
 * Adds custom fields to category taxonomy edit/add forms.
 * Extends TaxonomyFieldsAbstract for shared functionality.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */
final class CategoryFields extends TaxonomyFieldsAbstract {
	/**
	 * Get taxonomy name
	 *
	 * @return string Taxonomy name
	 * @since 2.0.0
	 */
	protected function get_taxonomy(): string {
		return 'aps_category';
	}
	
	/**
	 * Get taxonomy label
	 *
	 * @return string Human-readable label
	 * @since 2.0.0
	 */
	protected function get_taxonomy_label(): string {
		return 'Category';
	}
	
	/**
	 * Initialize category fields
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function init(): void {
		// Call parent to initialize shared functionality
		parent::init();
		
		// Add category-specific hooks
		add_action( 'save_post_aps_product', [ $this, 'auto_assign_default_category' ], 10, 3 );
		add_action( 'admin_footer-edit-tags.php', [ $this, 'add_sort_order_html' ] );
	}
	
	/**
	 * Render category-specific fields
	 * 
	 * @param int $category_id Category ID (0 for new category)
	 * @return void
	 * @since 2.0.0
	 */
	protected function render_taxonomy_specific_fields( int $category_id ): void {
		// Get current values with legacy fallback
		$featured = $this->get_category_meta( $category_id, 'featured' );
		$image_url = $this->get_category_meta( $category_id, 'image' );
		$is_default = $this->get_is_default( $category_id ) === '1';

		?>
		<!-- Featured and Default Checkboxes (side by side) -->
		<div class="aps-category-checkboxes-wrapper" style="display:none;">
			<!-- Featured Checkbox -->
			<div class="form-field aps-category-featured">
				<label for="_aps_category_featured">
					<?php esc_html_e( 'Featured Category', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="checkbox"
					id="_aps_category_featured"
					name="_aps_category_featured"
					value="1"
					<?php checked( $featured, true ); ?>
				/>
				<p class="description">
					<?php esc_html_e( 'Display this category prominently on frontend.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>

			<!-- Default Category -->
			<div class="form-field aps-category-default">
				<label for="_aps_category_is_default">
					<?php esc_html_e( 'Default Category', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="checkbox"
					id="_aps_category_is_default"
					name="_aps_category_is_default"
					value="1"
					<?php checked( $is_default, true ); ?>
				/>
				<p class="description">
					<?php esc_html_e( 'Products without a category will be assigned to this category automatically.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>
		</div>

		<div class="form-field aps-category-fields">
			<h3><?php esc_html_e( 'Category Settings', 'affiliate-product-showcase' ); ?></h3>

			<!-- Image URL -->
			<div class="form-field">
				<label for="_aps_category_image">
					<?php esc_html_e( 'Category Image URL', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="url"
					id="_aps_category_image"
					name="_aps_category_image"
					value="<?php echo esc_attr( $image_url ); ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'https://example.com/image.jpg', 'affiliate-product-showcase' ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Enter URL for category image.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Move Featured and Default checkboxes side by side below slug field
			$('.aps-category-checkboxes-wrapper').insertAfter($('input[name="slug"]').parent());
			$('.aps-category-checkboxes-wrapper').show();
		});
		</script>

		<?php
		// Nonce field for security (base class handles saving)
		wp_nonce_field( 'aps_category_fields', 'aps_category_fields_nonce' );
	}
	
	/**
	 * Save category-specific fields
	 * 
	 * @param int $category_id Category ID
	 * @return void
	 * @since 2.0.0
	 */
	protected function save_taxonomy_specific_fields( int $category_id ): void {
		// Sanitize and save featured
		$featured = isset( $_POST['_aps_category_featured'] ) ? '1' : '0';
		update_term_meta( $category_id, '_aps_category_featured', $featured );
		// Delete legacy key
		delete_term_meta( $category_id, 'aps_category_featured' );

		// Sanitize and save image URL
		$image_url = isset( $_POST['_aps_category_image'] ) 
			? esc_url_raw( wp_unslash( $_POST['_aps_category_image'] ) ) 
			: '';
		update_term_meta( $category_id, '_aps_category_image', $image_url );
		// Delete legacy key
		delete_term_meta( $category_id, 'aps_category_image' );

		// Handle default category
		$is_default = isset( $_POST['_aps_category_is_default'] ) ? '1' : '0';
		if ( $is_default === '1' ) {
			// Remove default flag from all other categories
			$this->remove_default_from_all_categories();
			// Set this category as default
			$this->set_is_default( $category_id, true );
			// Update global option
			update_option( 'aps_default_category_id', $category_id );
			
			// Get category name for notice
			$category = get_term( $category_id, 'aps_category' );
			$category_name = $category && ! is_wp_error( $category ) ? $category->name : sprintf( 'Category #%d', $category_id );
			
			// Add admin notice for auto-assignment feedback
			add_action( 'admin_notices', function() use ( $category_name ) {
				$message = sprintf(
					esc_html__( '%s has been set as default category. Products without a category will be automatically assigned to this category.', 'affiliate-product-showcase' ),
					esc_html( $category_name )
				);
				echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
			} );
		} else {
			// Remove default flag from this category
			$this->set_is_default( $category_id, false );
			delete_term_meta( $category_id, '_aps_category_is_default' );
			
			// Clear global option if this was default
			$current_default = get_option( 'aps_default_category_id', 0 );
			if ( (int) $current_default === $category_id ) {
				delete_option( 'aps_default_category_id' );
			}
		}
	}
	
	/**
	 * Get category meta with legacy fallback
	 *
	 * @param int $category_id Category ID
	 * @param string $meta_key Meta key (without _aps_category_ prefix)
	 * @return mixed Meta value
	 * @since 2.0.0
	 */
	private function get_category_meta( int $category_id, string $meta_key ): mixed {
		// Try new format with underscore prefix
		$value = get_term_meta( $category_id, '_aps_category_' . $meta_key, true );
		
		// If empty, try legacy format without underscore
		if ( $value === '' || $value === false ) {
			$value = get_term_meta( $category_id, 'aps_category_' . $meta_key, true );
		}
		
		return $value;
	}
	
	/**
	 * Remove default flag from all categories
	 *
	 * @return void
	 * @since 2.0.0
	 */
	private function remove_default_from_all_categories(): void {
		$terms = get_terms( [
			'taxonomy'   => 'aps_category',
			'hide_empty' => false,
			'fields'     => 'ids',
		] );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term_id ) {
				delete_term_meta( $term_id, '_aps_category_is_default' );
				delete_term_meta( $term_id, 'aps_category_is_default' );
			}
		}
	}
	
	/**
	 * Auto-assign default category to products without category
	 *
	 * When a product is saved without any categories, automatically assign
	 * default category to it.
	 *
	 * @param int $post_id Post ID
	 * @param \WP_Post $post Post object
	 * @param bool $update Whether this is an update (true) or new post (false)
	 * @return void
	 * @since 2.0.0
	 *
	 * @action save_post_aps_product
	 */
	public function auto_assign_default_category( int $post_id, \WP_Post $post, bool $update ): void {
		// Skip auto-save, revisions, and trashed posts
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		
		if ( $post->post_status === 'trash' ) {
			return;
		}
		
		// Get default category ID
		$default_category_id = get_option( 'aps_default_category_id', 0 );
		
		if ( empty( $default_category_id ) ) {
			return;
		}
		
		// Check if product already has categories
		$terms = wp_get_object_terms( $post_id, 'aps_category' );
		
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			// Product already has categories, skip auto-assignment
			return;
		}
		
		// Assign default category to product
		$result = wp_set_object_terms( $post_id, [ (int) $default_category_id ], 'aps_category', true );
		
		if ( ! is_wp_error( $result ) ) {
			// Log auto-assignment
			error_log( sprintf(
				'[APS] Auto-assigned default category #%d to product #%d',
				$default_category_id,
				$post_id
			) );
		}
	}
	
	/**
	 * Add sort order filter HTML above categories table
	 *
	 * Checks if we're on a category management page and adds filter.
	 * Aligns sort filter with bulk action dropdown.
	 *
	 * @return void
	 * @since 2.0.0
	 *
	 * @action admin_footer-edit-tags.php
	 */
	public function add_sort_order_html(): void {
		$screen = get_current_screen();
		
		// Only show on category management page
		if ( ! $screen || $screen->taxonomy !== 'aps_category' ) {
			return;
		}

		// Get current sort order from URL
		$current_sort_order = isset( $_GET['aps_sort_order'] ) ? sanitize_text_field( $_GET['aps_sort_order'] ) : 'date';

		?>
		<style>
			/* Ensure sort filter and bulk actions are side by side */
			.aps-sort-filter {
				display: inline-block;
				margin-right: 10px;
				margin-bottom: 10px;
			}
			.aps-sort-filter .postform {
				margin-right: 5px;
			}
			.bulkactions {
				display: inline-block;
			}
		</style>
		<script>
		jQuery(document).ready(function($) {
			// Find form that contains bulk actions
			var $searchForm = $('form#posts-filter');
			
			if ($searchForm.length) {
				// Insert sort order filter before bulk actions
				$searchForm.find('.tablenav.top').find('.actions').before(`
					<div class="alignleft actions aps-sort-filter">
						<label for="aps_sort_order" class="screen-reader-text">
							<?php esc_html_e( 'Sort Categories By', 'affiliate-product-showcase' ); ?>
						</label>
						<select name="aps_sort_order" id="aps_sort_order" class="postform">
							<option value="date" <?php selected( $current_sort_order, 'date' ); ?>>
								<?php esc_html_e( 'Date (Newest First)', 'affiliate-product-showcase' ); ?>
							</option>
						</select>
					</div>
				`);
				
				// Ensure alignment
				$('.aps-sort-filter').css('float', 'left');
			}
		});
		</script>
		<?php
	}
	
	/**
	 * Override add_custom_columns to add sort order column
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 * @since 2.0.0
	 */
	public function add_custom_columns( array $columns ): array {
		// Call parent for shared columns
		$columns = parent::add_custom_columns( $columns );
		
		// Add sort order column before status
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add sort order column before status
			if ( $key === 'status' ) {
				$new_columns['sort_order'] = __( 'Sort Order', 'affiliate-product-showcase' );
			}
		}
		
		return $new_columns;
	}
	
	/**
	 * Override render_custom_columns to add sort order column content
	 *
	 * @param string $content Column content
	 * @param string $column_name Column name
	 * @param int $term_id Term ID
	 * @return string Column content
	 * @since 2.0.0
	 */
	public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
		// Call parent for shared columns
		if ( in_array( $column_name, [ 'status', 'count' ], true ) ) {
			return parent::render_custom_columns( $content, $column_name, $term_id );
		}
		
		// Render sort order column
		if ( $column_name === 'sort_order' ) {
			return '<span class="aps-category-sort-order">-</span>';
		}
		
		return $content;
	}
}