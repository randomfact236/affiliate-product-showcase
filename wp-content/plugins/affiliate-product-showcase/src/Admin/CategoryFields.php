<?php
/**
 * Category Fields
 *
 * Adds custom fields to category edit/add forms including:
 * - Featured checkbox
 * - Image URL field
 * - Sort order dropdown
 * - Parent category dropdown
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
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
 * Adds custom fields to category edit/add forms.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class CategoryFields {
	/**
	 * Initialize category fields
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init(): void {
		// Add form fields to category edit/add pages
		add_action( 'aps_category_add_form_fields', [ $this, 'add_category_fields' ] );
		add_action( 'aps_category_edit_form_fields', [ $this, 'edit_category_fields' ] );

		// Save category meta fields
		add_action( 'created_aps_category', [ $this, 'save_category_fields' ], 10, 2 );
		add_action( 'edited_aps_category', [ $this, 'save_category_fields' ], 10, 2 );
	}

	/**
	 * Add fields to category add form
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action aps_category_add_form_fields
	 */
	public function add_category_fields(): void {
		$this->render_category_fields( 0 );
	}

	/**
	 * Add fields to category edit form
	 *
	 * @param \WP_Term $category Category term object
	 * @return void
	 * @since 1.0.0
	 *
	 * @action aps_category_edit_form_fields
	 */
	public function edit_category_fields( \WP_Term $category ): void {
		$this->render_category_fields( $category->term_id );
	}

	/**
	 * Render category fields
	 *
	 * @param int $category_id Category ID (0 for new category)
	 * @return void
	 * @since 1.0.0
	 */
	private function render_category_fields( int $category_id ): void {
		// Get current values
		$featured   = get_term_meta( $category_id, 'aps_category_featured', true );
		$image_url  = get_term_meta( $category_id, 'aps_category_image', true );
		$sort_order = get_term_meta( $category_id, 'aps_category_sort_order', true );

		if ( empty( $sort_order ) ) {
			$sort_order = 'date';
		}

		?>
		<div class="form-field aps-category-fields">
			<h3><?php esc_html_e( 'Category Settings', 'affiliate-product-showcase' ); ?></h3>

			<!-- Featured Checkbox -->
			<div class="form-field">
				<label for="aps_category_featured">
					<?php esc_html_e( 'Featured Category', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="checkbox"
					id="aps_category_featured"
					name="aps_category_featured"
					value="1"
					<?php checked( $featured, '1' ); ?>
				/>
				<p class="description">
					<?php esc_html_e( 'Display this category prominently on the frontend.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>

			<!-- Image URL -->
			<div class="form-field">
				<label for="aps_category_image">
					<?php esc_html_e( 'Category Image URL', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="url"
					id="aps_category_image"
					name="aps_category_image"
					value="<?php echo esc_attr( $image_url ); ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'https://example.com/image.jpg', 'affiliate-product-showcase' ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Enter URL for category image.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>

			<!-- Sort Order -->
			<div class="form-field">
				<label for="aps_category_sort_order">
					<?php esc_html_e( 'Default Sort Order', 'affiliate-product-showcase' ); ?>
				</label>
				<select
					id="aps_category_sort_order"
					name="aps_category_sort_order"
					class="postform"
				>
					<option value="date" <?php selected( $sort_order, 'date' ); ?>>
						<?php esc_html_e( 'Date (Newest First)', 'affiliate-product-showcase' ); ?>
					</option>
					<option value="date_asc" <?php selected( $sort_order, 'date_asc' ); ?>>
						<?php esc_html_e( 'Date (Oldest First)', 'affiliate-product-showcase' ); ?>
					</option>
					<option value="name" <?php selected( $sort_order, 'name' ); ?>>
						<?php esc_html_e( 'Name (A-Z)', 'affiliate-product-showcase' ); ?>
					</option>
					<option value="name_desc" <?php selected( $sort_order, 'name_desc' ); ?>>
						<?php esc_html_e( 'Name (Z-A)', 'affiliate-product-showcase' ); ?>
					</option>
					<option value="price" <?php selected( $sort_order, 'price' ); ?>>
						<?php esc_html_e( 'Price (Low to High)', 'affiliate-product-showcase' ); ?>
					</option>
					<option value="price_desc" <?php selected( $sort_order, 'price_desc' ); ?>>
						<?php esc_html_e( 'Price (High to Low)', 'affiliate-product-showcase' ); ?>
					</option>
					<option value="popularity" <?php selected( $sort_order, 'popularity' ); ?>>
						<?php esc_html_e( 'Popularity (Most Clicked)', 'affiliate-product-showcase' ); ?>
					</option>
					<option value="random" <?php selected( $sort_order, 'random' ); ?>>
						<?php esc_html_e( 'Random', 'affiliate-product-showcase' ); ?>
					</option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Default sort order for products in this category.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>
		</div>

		<?php
		// Nonce field for security
		wp_nonce_field( 'aps_category_fields', 'aps_category_fields_nonce' );
	}

	/**
	 * Save category fields
	 *
	 * @param int $category_id Category ID
	 * @param int $term_id Term ID (same as category_id)
	 * @return void
	 * @since 1.0.0
	 *
	 * @action created_aps_category
	 * @action edited_aps_category
	 */
	public function save_category_fields( int $category_id, int $term_id ): void {
		// Check nonce
		if ( ! isset( $_POST['aps_category_fields_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['aps_category_fields_nonce'], 'aps_category_fields' ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		// Sanitize and save featured
		$featured = isset( $_POST['aps_category_featured'] ) ? '1' : '0';
		update_term_meta( $category_id, 'aps_category_featured', $featured );

		// Sanitize and save image URL
		$image_url = isset( $_POST['aps_category_image'] ) 
			? esc_url_raw( wp_unslash( $_POST['aps_category_image'] ) ) 
			: '';
		update_term_meta( $category_id, 'aps_category_image', $image_url );

		// Sanitize and save sort order
		$sort_order = isset( $_POST['aps_category_sort_order'] )
			? sanitize_text_field( wp_unslash( $_POST['aps_category_sort_order'] ) )
			: 'date';
		update_term_meta( $category_id, 'aps_category_sort_order', $sort_order );
	}
}