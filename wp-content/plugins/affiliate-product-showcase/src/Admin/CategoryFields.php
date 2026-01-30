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
use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Helpers\TermMetaHelper;

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
	 * Category repository instance
	 *
	 * @var CategoryRepository
	 * @since 2.0.0
	 */
	private CategoryRepository $repository;

	/**
	 * Constructor
	 *
	 * @param CategoryRepository|null $repository Optional repository instance
	 * @since 2.0.0
	 */
	public function __construct( ?CategoryRepository $repository = null ) {
		$this->repository = $repository ?? new CategoryRepository();
	}

	/**
	 * Get taxonomy name
	 *
	 * @return string Taxonomy name
	 * @since 2.0.0
	 */
	protected function get_taxonomy(): string {
		return Constants::TAX_CATEGORY;
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
		$featured = TermMetaHelper::get_with_fallback( $category_id, 'featured', 'aps_category_' );
		$image_url = TermMetaHelper::get_with_fallback( $category_id, 'image', 'aps_category_' );
		$is_default = $this->get_is_default( $category_id ) === '1';

		?>
	<!-- Featured and Default Checkboxes (side by side) -->
	<fieldset class="aps-category-checkboxes-wrapper aps-hidden" aria-label="<?php esc_attr_e( 'Category options', 'affiliate-product-showcase' ); ?>">
		<legend><?php esc_html_e( 'Category Options', 'affiliate-product-showcase' ); ?></legend>
		
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
				aria-describedby="_aps_category_featured_description"
				<?php checked( $featured, true ); ?>
			/>
			<p class="description" id="_aps_category_featured_description">
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
				aria-describedby="_aps_category_is_default_description"
				<?php checked( $is_default, true ); ?>
			/>
			<p class="description" id="_aps_category_is_default_description">
				<?php esc_html_e( 'Products without a category will be assigned to this category automatically.', 'affiliate-product-showcase' ); ?>
			</p>
		</div>
	</fieldset>

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
					aria-describedby="_aps_category_image_description"
					aria-label="<?php esc_attr_e( 'Category image URL input field', 'affiliate-product-showcase' ); ?>"
				/>
				<p class="description" id="_aps_category_image_description">
					<?php esc_html_e( 'Enter URL for category image.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>
		</div>

		<?php
		// Note: Nonce field is handled by the base class TaxonomyFieldsAbstract
		// No need to add it here as it would be redundant
	}
	
	/**
	 * Save category-specific fields
	 * 
	 * @param int $category_id Category ID
	 * @return void
	 * @since 2.0.0
	 */
	protected function save_taxonomy_specific_fields( int $category_id ): void {
		$this->save_featured_field( $category_id );
		$this->save_image_field( $category_id );
		$this->save_default_field( $category_id );
	}
	
	/**
	 * Save featured field
	 *
	 * @param int $category_id Category ID
	 * @return void
	 * @since 2.1.0
	 */
	private function save_featured_field( int $category_id ): void {
		$featured = isset( $_POST['_aps_category_featured'] ) && '1' === $_POST['_aps_category_featured'] ? '1' : '0';
		update_term_meta( $category_id, '_aps_category_featured', $featured );
		$this->delete_legacy_meta( $category_id, 'featured' );
	}
	
	/**
	 * Save and validate image URL field
	 *
	 * @param int $category_id Category ID
	 * @return void
	 * @since 2.1.0
	 */
	private function save_image_field( int $category_id ): void {
		$image_url_input = isset( $_POST['_aps_category_image'] )
			? wp_unslash( $_POST['_aps_category_image'] )
			: '';
		
		$error_message = '';
		$image_url = \AffiliateProductShowcase\Validators\UrlValidator::validate_with_error( $image_url_input, $error_message );
		
		if ( ! empty( $error_message ) ) {
			$this->add_invalid_url_notice();
			$image_url = '';
		}
		
		update_term_meta( $category_id, '_aps_category_image', $image_url ?? '' );
		$this->delete_legacy_meta( $category_id, 'image' );
	}
	
	/**
	 * Save default category field
	 *
	 * @param int $category_id Category ID
	 * @return void
	 * @since 2.1.0
	 */
	private function save_default_field( int $category_id ): void {
		$is_default = isset( $_POST['_aps_category_is_default'] ) && '1' === $_POST['_aps_category_is_default'];
		
		if ( $is_default ) {
			$this->set_as_default_category( $category_id );
		} else {
			$this->unset_as_default_category( $category_id );
		}
	}
	
	/**
	 * Set category as default
	 *
	 * @param int $category_id Category ID
	 * @return void
	 * @since 2.1.0
	 */
	private function set_as_default_category( int $category_id ): void {
		// Remove default flag from all other categories
		$this->repository->remove_default_from_all_categories();
		
		// Set this category as default
		$this->set_is_default( $category_id, true );
		
		// Update global option
		update_option( 'aps_default_category_id', $category_id );
		
		// Get category name for notice
		$category = get_term( $category_id, 'aps_category' );
		$category_name = $category && ! is_wp_error( $category ) ? $category->name : sprintf( 'Category #%d', $category_id );
		
		// Add admin notice for auto-assignment feedback
		add_action( 'admin_notices', function() use ( $category_name ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				wp_kses_post(
					sprintf(
						__( '%s has been set as default category. Products without a category will be automatically assigned to this category.', 'affiliate-product-showcase' ),
						esc_html( $category_name )
					)
				)
			);
		} );
	}
	
	/**
	 * Unset category as default
	 *
	 * @param int $category_id Category ID
	 * @return void
	 * @since 2.1.0
	 */
	private function unset_as_default_category( int $category_id ): void {
		// Remove default flag from this category
		$this->set_is_default( $category_id, false );
		$this->delete_legacy_meta( $category_id, 'is_default' );
		
		// Clear global option if this was default
		$current_default = get_option( 'aps_default_category_id', 0 );
		if ( (int) $current_default === $category_id ) {
			delete_option( 'aps_default_category_id' );
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
	 * Get sort order filter HTML template
	 *
	 * Returns the HTML template for the sort order filter.
	 * Extracted to a constant for better maintainability.
	 *
	 * @param string $current_sort_order Current sort order
	 * @return string HTML template
	 * @since 2.0.0
	 */
	private function get_sort_filter_template( string $current_sort_order ): string {
		ob_start();
		?>
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
		<?php
		return ob_get_clean();
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

		// Get current sort order from URL with validation
		$valid_sort_orders = ['date', 'name', 'count'];
		$current_sort_order = isset( $_GET['aps_sort_order'] ) &&
		                  in_array( $_GET['aps_sort_order'], $valid_sort_orders, true )
		                  ? sanitize_text_field( $_GET['aps_sort_order'] )
		                  : 'date';

		// Get HTML template
		$filter_html = $this->get_sort_filter_template( $current_sort_order );

		?>
		<script>
		jQuery(document).ready(function($) {
			// Find form that contains bulk actions
			var $searchForm = $('form#posts-filter');
			
			if ($searchForm.length) {
				// Insert sort order filter before bulk actions
				$searchForm.find('.tablenav.top').find('.actions').before(
					<?php echo wp_json_encode( $filter_html ); ?>
				);
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
	 * Delete legacy term meta key
	 *
	 * @param int    $term_id  Term ID
	 * @param string $meta_key Meta key name (without prefix)
	 * @return void
	 * @since 2.1.0
	 */
	private function delete_legacy_meta( int $term_id, string $meta_key ): void {
		delete_term_meta( $term_id, '_aps_category_' . $meta_key );
		delete_term_meta( $term_id, 'aps_category_' . $meta_key );
	}
	
	/**
	 * Add invalid URL admin notice
	 *
	 * @return void
	 * @since 2.1.0
	 */
	private function add_invalid_url_notice(): void {
		add_action( 'admin_notices', function() {
			printf(
				'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
				esc_html__( 'Invalid image URL. Please enter a valid HTTP or HTTPS URL.', 'affiliate-product-showcase' )
			);
		} );
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