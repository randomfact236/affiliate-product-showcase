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

		// Add custom columns to WordPress native categories table
		add_filter( 'manage_edit-aps_category_columns', [ $this, 'add_custom_columns' ] );
		add_filter( 'manage_aps_category_custom_column', [ $this, 'render_custom_columns' ], 10, 3 );

		// Add sort order filter above categories table
		add_action( 'restrict_manage_posts', [ $this, 'add_sort_order_filter' ] );

		// Protect default category from permanent deletion
		add_filter( 'pre_delete_term', [ $this, 'protect_default_category' ], 10, 2 );

		// Auto-assign default category to products without category
		add_action( 'save_post_aps_product', [ $this, 'auto_assign_default_category' ], 10, 3 );

		// Add bulk actions for status management
		add_filter( 'bulk_actions-edit-aps_category', [ $this, 'add_custom_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-edit-aps_category', [ $this, 'handle_custom_bulk_actions' ], 10, 3 );
		
		// Add admin notices for bulk actions
		add_action( 'admin_notices', [ $this, 'display_bulk_action_notices' ] );
		
		// Enqueue admin scripts and styles
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		
		// Localize script with needed variables
		add_action( 'admin_head-edit-tags.php', [ $this, 'localize_admin_script' ] );
		add_action( 'admin_head-term.php', [ $this, 'localize_admin_script' ] );
		
		// AJAX handler for inline status toggle
		add_action( 'wp_ajax_aps_toggle_category_status', [ $this, 'ajax_toggle_category_status' ] );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function enqueue_admin_assets(): void {
		$screen = get_current_screen();
		if ( $screen && $screen->taxonomy === 'aps_category' ) {
			wp_enqueue_style(
				'aps-admin-category',
				Constants::assetUrl( 'assets/css/admin-category.css' ),
				[],
				Constants::VERSION
			);
			
			// Add inline script for status toggle
			wp_add_inline_script( 'jquery', $this->get_inline_script() );
		}
	}

	/**
	 * Get inline JavaScript for status toggle
	 *
	 * @return string Inline script
	 * @since 1.2.0
	 */
	private function get_inline_script(): string {
		ob_start();
		?>
		jQuery(document).ready(function($) {
			// Handle status toggle clicks in table
			$(document).on('click', '.aps-category-status-toggle', function(e) {
				e.preventDefault();
				var $this = $(this);
				var termId = $this.data('term-id');
				var currentStatus = $this.data('current-status');
				var newStatus = currentStatus === 'published' ? 'draft' : 'published';

				// Toggle status via AJAX
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'aps_toggle_category_status',
						nonce: aps_admin_vars.nonce,
						term_id: termId,
						status: newStatus
					},
					beforeSend: function() {
						$this.addClass('updating');
					},
					success: function(response) {
						if (response.success) {
							// Update status display
							if (newStatus === 'published') {
								$this.html('<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" aria-hidden="true"></span> ' + aps_admin_vars.published_text);
								$this.attr('data-current-status', 'published');
								$this.removeClass('status-draft').addClass('status-published');
							} else {
								$this.html('<span class="dashicons dashicons-minus" style="color: #646970;" aria-hidden="true"></span> ' + aps_admin_vars.draft_text);
								$this.attr('data-current-status', 'draft');
								$this.removeClass('status-published').addClass('status-draft');
							}
							$this.removeClass('updating');
						} else if (response.data && response.data.message) {
							$this.removeClass('updating');
							alert(response.data.message);
						}
					},
					error: function() {
						$this.removeClass('updating');
						alert(aps_admin_vars.error_text);
					}
				});
			});
		});
		<?php
		return ob_get_clean();
	}

	/**
	 * Localize admin script with needed variables
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function localize_admin_script(): void {
		wp_localize_script( 'jquery', 'aps_admin_vars', [
			'nonce' => wp_create_nonce( 'aps_toggle_category_status' ),
			'published_text' => esc_html__( 'Published', 'affiliate-product-showcase' ),
			'draft_text' => esc_html__( 'Draft', 'affiliate-product-showcase' ),
			'error_text' => esc_html__( 'An error occurred. Please try again.', 'affiliate-product-showcase' ),
		] );
	}

	/**
	 * Display bulk action notices
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function display_bulk_action_notices(): void {
		if ( isset( $_GET['moved_to_draft'] ) ) {
			$count = intval( $_GET['moved_to_draft'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d categories moved to draft.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}
		
		if ( isset( $_GET['moved_to_trash'] ) ) {
			$count = intval( $_GET['moved_to_trash'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d categories moved to trash (set to draft).', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}
	}

	/**
	 * AJAX handler for inline status toggle
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function ajax_toggle_category_status(): void {
		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'aps_toggle_category_status' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'affiliate-product-showcase' ) ] );
		}
		
		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action.', 'affiliate-product-showcase' ) ] );
		}
		
		// Get term ID and new status
		$term_id = isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;
		$new_status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'published';
		
		if ( empty( $term_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid category ID.', 'affiliate-product-showcase' ) ] );
		}
		
		// Check if this is default category (cannot change status)
		$is_default = get_term_meta( $term_id, '_aps_category_is_default', true );
		if ( $is_default === '1' ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Cannot change status of default category.', 'affiliate-product-showcase' ) ] );
		}
		
		// Update category status
		$result = update_term_meta( $term_id, '_aps_category_status', $new_status );
		
		if ( $result !== false ) {
			wp_send_json_success( [ 'status' => $new_status ] );
		} else {
			wp_send_json_error( [ 'message' => esc_html__( 'Failed to update category status.', 'affiliate-product-showcase' ) ] );
		}
	}

	/**
	 * Add sort order filter above categories table
	 *
	 * @return void
	 * @since 1.2.0
	 *
	 * @action restrict_manage_posts
	 */
	public function add_sort_order_filter(): void {
		// Only show on category management page
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== 'aps_category' ) {
			return;
		}

		// Get current sort order from URL
		$current_sort_order = isset( $_GET['aps_sort_order'] ) ? sanitize_text_field( $_GET['aps_sort_order'] ) : 'date';

		?>
		<div class="alignleft actions">
			<label for="aps_sort_order" class="screen-reader-text">
				<?php esc_html_e( 'Sort Categories By', 'affiliate-product-showcase' ); ?>
			</label>
			<select name="aps_sort_order" id="aps_sort_order" class="postform">
				<option value="date" <?php selected( $current_sort_order, 'date' ); ?>>
					<?php esc_html_e( 'Date (Newest First)', 'affiliate-product-showcase' ); ?>
				</option>
			</select>
			<input type="submit" id="query-submit" class="button" value="<?php esc_attr_e( 'Filter', 'affiliate-product-showcase' ); ?>" />
		</div>
		<?php
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
	 * Get category meta with legacy fallback
	 *
	 * Retrieves meta value with fallback to old key format.
	 *
	 * @param int $term_id Term ID
	 * @param string $meta_key Meta key (without _aps_category_ prefix)
	 * @return mixed Meta value
	 * @since 1.2.0
	 */
	private function get_category_meta( int $term_id, string $meta_key ) {
		// Try new format with underscore prefix
		$value = get_term_meta( $term_id, '_aps_category_' . $meta_key, true );
		
		// If empty, try legacy format without underscore
		if ( $value === '' || $value === false ) {
			$value = get_term_meta( $term_id, 'aps_category_' . $meta_key, true );
		}
		
		return $value;
	}

	/**
	 * Render category fields
	 *
	 * @param int $category_id Category ID (0 for new category)
	 * @return void
	 * @since 1.0.0
	 */
	private function render_category_fields( int $category_id ): void {
		// Get current values with legacy fallback
		$featured    = $this->get_category_meta( $category_id, 'featured' );
		$image_url   = $this->get_category_meta( $category_id, 'image' );
		$sort_order  = $this->get_category_meta( $category_id, 'sort_order' );
		$status      = $this->get_category_meta( $category_id, 'status' );
		$is_default  = $this->get_category_meta( $category_id, 'is_default' );

		if ( empty( $sort_order ) ) {
			$sort_order = 'date';
		}

		if ( empty( $status ) ) {
			$status = 'published';
		}

		?>
		<!-- Featured and Default Checkbox (side by side) -->
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
					<?php checked( $featured, '1' ); ?>
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
					<?php checked( $is_default, '1' ); ?>
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

			<!-- Sort Order -->
			<div class="form-field">
				<label for="_aps_category_sort_order">
					<?php esc_html_e( 'Default Sort Order', 'affiliate-product-showcase' ); ?>
				</label>
				<select
					id="_aps_category_sort_order"
					name="_aps_category_sort_order"
					class="postform"
				>
					<option value="date" <?php selected( $sort_order, 'date' ); ?>>
						<?php esc_html_e( 'Date (Newest First)', 'affiliate-product-showcase' ); ?>
					</option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Default sort order for products in this category. Categories are sorted by date created.', 'affiliate-product-showcase' ); ?>
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

		// Sanitize and save sort order
		$sort_order = isset( $_POST['_aps_category_sort_order'] )
			? sanitize_text_field( wp_unslash( $_POST['_aps_category_sort_order'] ) )
			: 'date';
		update_term_meta( $category_id, '_aps_category_sort_order', $sort_order );
		// Delete legacy key
		delete_term_meta( $category_id, 'aps_category_sort_order' );

		// Sanitize and save status (default to published if not set)
		$status = isset( $_POST['_aps_category_status'] )
			? sanitize_text_field( wp_unslash( $_POST['_aps_category_status'] ) )
			: 'published';
		update_term_meta( $category_id, '_aps_category_status', $status );
		// Delete legacy key
		delete_term_meta( $category_id, 'aps_category_status' );

		// Handle default category
		$is_default = isset( $_POST['_aps_category_is_default'] ) ? '1' : '0';
		if ( $is_default === '1' ) {
			// Remove default flag from all other categories
			$this->remove_default_from_all_categories();
			// Set this category as default
			update_term_meta( $category_id, '_aps_category_is_default', '1' );
			// Delete legacy key
			delete_term_meta( $category_id, 'aps_category_is_default' );
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
			update_term_meta( $category_id, '_aps_category_is_default', '0' );
			delete_term_meta( $category_id, 'aps_category_is_default' );
			// Delete legacy key
			delete_term_meta( $category_id, 'aps_category_is_default' );
			// Clear global option if this was default
			$current_default = get_option( 'aps_default_category_id', 0 );
			if ( (int) $current_default === $category_id ) {
				delete_option( 'aps_default_category_id' );
			}
		}
	}

	/**
	 * Add custom columns to WordPress native categories table
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 * @since 1.1.0
	 *
	 * @filter manage_edit-aps_category_columns
	 */
	public function add_custom_columns( array $columns ): array {
		// Insert custom columns after 'slug' column
		$new_columns = [];
		
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add custom columns after slug (status only)
			if ( $key === 'slug' ) {
				$new_columns['status'] = __( 'Status', 'affiliate-product-showcase' );
			}
		}
		
		return $new_columns;
	}

	/**
	 * Render custom column content
	 *
	 * @param string $content Column content
	 * @param string $column_name Column name
	 * @param int $term_id Term ID
	 * @return string Column content
	 * @since 1.1.0
	 *
	 * @filter manage_aps_category_custom_column
	 */
	public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
		if ( $column_name === 'status' ) {
			$status = $this->get_category_meta( $term_id, 'status' );
			$is_default = $this->get_category_meta( $term_id, 'is_default' );
			
			// Make status clickable for inline toggle
			if ( $is_default === '1' ) {
				// Default category - read-only status
				if ( $status === 'published' ) {
					return '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" aria-hidden="true"></span> ' . esc_html__( 'Published', 'affiliate-product-showcase' ) . ' <span class="aps-status-note">(' . esc_html__( 'Default', 'affiliate-product-showcase' ) . ')</span>';
				} else {
					return '<span class="dashicons dashicons-minus" style="color: #646970;" aria-hidden="true"></span> ' . esc_html__( 'Draft', 'affiliate-product-showcase' ) . ' <span class="aps-status-note">(' . esc_html__( 'Default', 'affiliate-product-showcase' ) . ')</span>';
				}
			} else {
				// Non-default category - clickable toggle
				$toggle_class = $status === 'published' ? 'status-published' : 'status-draft';
				$status_text = $status === 'published' ? esc_html__( 'Published', 'affiliate-product-showcase' ) : esc_html__( 'Draft', 'affiliate-product-showcase' );
				$icon = $status === 'published' ? '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" aria-hidden="true"></span>' : '<span class="dashicons dashicons-minus" style="color: #646970;" aria-hidden="true"></span>';
				
				return sprintf(
					'<a href="#" class="aps-category-status-toggle %s" data-term-id="%d" data-current-status="%s">%s %s</a>',
					esc_attr( $toggle_class ),
					$term_id,
					esc_attr( $status ),
					$icon,
					$status_text
				);
			}
		}
		
		return $content;
	}

	/**
	 * Remove default flag from all categories
	 *
	 * @return void
	 * @since 1.1.0
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
	 * Protect default category from permanent deletion
	 *
	 * Prevents default category from being deleted permanently.
	 * Users can still move it to trash but not delete forever.
	 *
	 * @param mixed $delete_term Whether to delete term
	 * @param int $term_id Term ID
	 * @return mixed False if default category (prevents deletion), otherwise original value
	 * @since 1.1.0
	 *
	 * @filter pre_delete_term
	 */
	public function protect_default_category( $delete_term, int $term_id ) {
		// Check if this is default category
		$is_default = $this->get_category_meta( $term_id, 'is_default' );
		
		if ( $is_default === '1' ) {
			// Prevent deletion of default category
			wp_die(
				sprintf(
					esc_html__( 'Cannot delete default category. Please set a different category as default first.', 'affiliate-product-showcase' ),
					esc_html( get_term( $term_id )->name ?? '#' . $term_id )
				),
				esc_html__( 'Default Category Protected', 'affiliate-product-showcase' ),
				[ 'back_link' => true ]
			);
		}
		
		return $delete_term;
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
	 * @since 1.1.0
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
	 * Add custom bulk actions to categories table
	 *
	 * Adds "Move to Draft" and "Move to Trash" bulk actions.
	 *
	 * @param array $bulk_actions Existing bulk actions
	 * @return array Modified bulk actions
	 * @since 1.1.0
	 *
	 * @filter bulk_actions-edit-aps_category
	 */
	public function add_custom_bulk_actions( array $bulk_actions ): array {
		// Add "Move to Draft" bulk action
		$bulk_actions['move_to_draft'] = __( 'Move to Draft', 'affiliate-product-showcase' );
		
		// Add "Move to Trash" bulk action (sets status to draft, safe delete)
		$bulk_actions['move_to_trash'] = __( 'Move to Trash', 'affiliate-product-showcase' );
		
		return $bulk_actions;
	}

	/**
	 * Handle custom bulk actions for categories
	 *
	 * Processes "Move to Draft" and "Move to Trash" bulk actions.
	 * Displays success/error notices inline instead of redirecting.
	 *
	 * @param string $redirect_url Redirect URL after processing
	 * @param string $action_name Action name being processed
	 * @param array $term_ids Array of term IDs
	 * @return string Modified redirect URL (with query parameters for notices)
	 * @since 1.1.0
	 *
	 * @filter handle_bulk_actions-edit-aps_category
	 */
	public function handle_custom_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
		if ( empty( $term_ids ) ) {
			return $redirect_url;
		}
		
		$count = 0;
		$error = false;
		
		// Handle "Move to Draft" action
		if ( $action_name === 'move_to_draft' ) {
			foreach ( $term_ids as $term_id ) {
				// Check if this is default category (cannot be changed to draft)
				$is_default = $this->get_category_meta( $term_id, 'is_default' );
				
				if ( $is_default === '1' ) {
					continue; // Skip default category
				}
				
				// Update category status to draft
				$result = update_term_meta( $term_id, '_aps_category_status', 'draft' );
				
				if ( $result !== false ) {
					$count++;
				}
			}
			
			// Add success/error message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'moved_to_draft' => $count,
				], $redirect_url );
			}
		}
		
		// Handle "Move to Trash" action (sets status to draft)
		if ( $action_name === 'move_to_trash' ) {
			foreach ( $term_ids as $term_id ) {
				// Check if this is default category (cannot be trashed)
				$is_default = $this->get_category_meta( $term_id, 'is_default' );
				
				if ( $is_default === '1' ) {
					continue; // Skip default category
				}
				
				// Set status to draft (safe delete - not permanent)
				$result = update_term_meta( $term_id, '_aps_category_status', 'draft' );
				
				if ( $result !== false ) {
					$count++;
				}
			}
			
			// Add success/error message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'moved_to_trash' => $count,
				], $redirect_url );
			}
		}
		
		return $redirect_url;
	}
}