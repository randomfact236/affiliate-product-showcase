<?php
/**
 * Tag Fields
 *
 * Adds custom fields to tag edit/add forms including:
 * - Featured checkbox
 * - Tag Settings section
 * - Image URL field
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
 * Tag Fields
 *
 * Adds custom fields to tag edit/add forms and manages tag list table.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class TagFields {
	/**
	 * Initialize tag fields
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init(): void {
		// Add form fields to tag edit/add pages
		add_action( 'aps_tag_add_form_fields', [ $this, 'add_tag_fields' ] );
		add_action( 'aps_tag_edit_form_fields', [ $this, 'edit_tag_fields' ] );

		// Save tag meta fields
		add_action( 'created_aps_tag', [ $this, 'save_tag_fields' ], 10, 2 );
		add_action( 'edited_aps_tag', [ $this, 'save_tag_fields' ], 10, 2 );

		// Add custom columns to WordPress native tags table
		add_filter( 'manage_edit-aps_tag_columns', [ $this, 'add_custom_columns' ] );
		add_filter( 'manage_aps_tag_custom_column', [ $this, 'render_custom_columns' ], 10, 3 );

		// Add sort order filter above tags table
		add_action( 'admin_footer-edit-tags.php', [ $this, 'add_sort_order_html' ] );

		// Add view tabs (All | Published | Draft | Trash) - WordPress native
		add_filter( 'views_edit-aps_tag', [ $this, 'add_status_view_tabs' ] );

		// Filter tags by status
		add_filter( 'get_terms', [ $this, 'filter_tags_by_status' ], 10, 3 );

		// Add bulk actions
		add_filter( 'bulk_actions-edit-aps_tag', [ $this, 'add_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-edit-aps_tag', [ $this, 'handle_bulk_actions' ], 10, 3 );

		// Add admin notices for bulk actions
		add_action( 'admin_notices', [ $this, 'display_bulk_action_notices' ] );

		// Register AJAX handler for inline status toggle
		add_action( 'wp_ajax_aps_toggle_tag_status', [ $this, 'ajax_toggle_tag_status' ] );

		// Enqueue admin scripts and styles
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		
		// Localize script with needed variables
		add_action( 'admin_head-edit-tags.php', [ $this, 'localize_admin_script' ] );
		add_action( 'admin_head-term.php', [ $this, 'localize_admin_script' ] );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook_suffix Current admin page hook
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		$screen = get_current_screen();
		if ( $screen && $screen->taxonomy === 'aps_tag' ) {
			wp_enqueue_style(
				'aps-admin-tag',
				Constants::assetUrl( 'assets/css/admin-tag.css' ),
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
	 * @since 1.3.0
	 */
	private function get_inline_script(): string {
		ob_start();
		?>
		jQuery(document).ready(function($) {
			// Handle status dropdown changes in table
			$(document).on('change', '.aps-tag-status-select', function() {
				var $this = $(this);
				var termId = $this.data('term-id');
				var newStatus = $this.val();
				var originalStatus = $this.find('option:selected').text();

				// Update status via AJAX
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'aps_toggle_tag_status',
						nonce: aps_admin_vars.nonce,
						term_id: termId,
						status: newStatus
					},
					beforeSend: function() {
						$this.prop('disabled', true);
					},
					success: function(response) {
						if (response.success) {
							$this.prop('disabled', false);
							// Show success notice
							if ($('.notice-success.aps-status-notice').length) {
								$('.notice-success.aps-status-notice').remove();
							}
							$('.wrap h1').after('<div class="notice notice-success is-dismissible aps-status-notice"><p>' + aps_admin_vars.success_text + '</p></div>');
							setTimeout(function() {
								$('.aps-status-notice').fadeOut();
							}, 3000);
						} else if (response.data && response.data.message) {
							$this.prop('disabled', false);
							// Revert to original status
							$this.val(originalStatus === 'Published' ? 'published' : 'draft');
							alert(response.data.message);
						}
					},
					error: function() {
						$this.prop('disabled', false);
						// Revert to original status
						$this.val(originalStatus === 'Published' ? 'published' : 'draft');
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
	 * @since 1.3.0
	 */
	public function localize_admin_script(): void {
		wp_localize_script( 'jquery', 'aps_admin_vars', [
			'nonce' => wp_create_nonce( 'aps_toggle_tag_status' ),
			'published_text' => esc_html__( 'Published', 'affiliate-product-showcase' ),
			'draft_text' => esc_html__( 'Draft', 'affiliate-product-showcase' ),
			'success_text' => esc_html__( 'Tag status updated successfully.', 'affiliate-product-showcase' ),
			'error_text' => esc_html__( 'An error occurred. Please try again.', 'affiliate-product-showcase' ),
		] );
	}

	/**
	 * Add status view tabs to tags page
	 *
	 * Adds "All | Published | Draft | Trash" tabs similar to WordPress posts.
	 *
	 * @param array $views Existing views
	 * @return array Modified views
	 * @since 1.3.0
	 *
	 * @filter views_edit-aps_tag
	 */
	public function add_status_view_tabs( array $views ): array {
		// Only filter on aps_tag taxonomy
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== 'aps_tag' ) {
			return $views;
		}

		// Count tags by status
		$all_count = $this->count_tags_by_status( 'all' );
		$published_count = $this->count_tags_by_status( 'published' );
		$draft_count = $this->count_tags_by_status( 'draft' );
		$trash_count = $this->count_tags_by_status( 'trashed' );

		// Get current status from URL (use consistent parameter name)
		$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		// Build new views
		$new_views = [];

		// All tab
		$all_class = $current_status === 'all' ? 'class="current"' : '';
		$all_url = admin_url( 'edit-tags.php?taxonomy=aps_tag&post_type=aps_product' );
		$new_views['all'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $all_url ),
			$all_class,
			esc_html__( 'All', 'affiliate-product-showcase' ),
			$all_count
		);

		// Published tab
		$published_class = $current_status === 'published' ? 'class="current"' : '';
		$published_url = admin_url( 'edit-tags.php?taxonomy=aps_tag&post_type=aps_product&status=published' );
		$new_views['published'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $published_url ),
			$published_class,
			esc_html__( 'Published', 'affiliate-product-showcase' ),
			$published_count
		);

		// Draft tab
		$draft_class = $current_status === 'draft' ? 'class="current"' : '';
		$draft_url = admin_url( 'edit-tags.php?taxonomy=aps_tag&post_type=aps_product&status=draft' );
		$new_views['draft'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $draft_url ),
			$draft_class,
			esc_html__( 'Draft', 'affiliate-product-showcase' ),
			$draft_count
		);

		// Trash tab
		$trash_class = $current_status === 'trashed' ? 'class="current"' : '';
		$trash_url = admin_url( 'edit-tags.php?taxonomy=aps_tag&post_type=aps_product&status=trashed' );
		$new_views['trash'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $trash_url ),
			$trash_class,
			esc_html__( 'Trash', 'affiliate-product-showcase' ),
			$trash_count
		);

		return $new_views;
	}

	/**
	 * Filter tags by status
	 *
	 * Filters tags based on status parameter in URL.
	 *
	 * @param array $terms Terms array
	 * @param array $taxonomies Taxonomies
	 * @param array $args Query arguments
	 * @return array Filtered terms
	 * @since 1.3.0
	 *
	 * @filter get_terms
	 */
	public function filter_tags_by_status( array $terms, array $taxonomies, array $args ): array {
		// Only filter for aps_tag taxonomy
		if ( ! in_array( 'aps_tag', $taxonomies, true ) ) {
			return $terms;
		}

		// Only filter on admin tag list page
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== 'aps_tag' || $screen->base !== 'edit-tags' ) {
			return $terms;
		}

		// Get status from URL (use consistent parameter name)
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		// If showing all, no filtering
		if ( $status === 'all' ) {
			return $terms;
		}

		// Filter terms by status
		$filtered_terms = [];
		foreach ( $terms as $term ) {
			if ( ! is_object( $term ) ) {
				continue;
			}

			$term_id = is_numeric( $term ) ? $term : $term->term_id;
			$term_status = get_term_meta( $term_id, '_aps_tag_status', true );

			// Default to published if not set
			if ( empty( $term_status ) || ! in_array( $term_status, [ 'published', 'draft', 'trashed' ], true ) ) {
				$term_status = 'published';
			}

			// Include term if status matches
			if ( $term_status === $status ) {
				$filtered_terms[] = $term;
			}
		}

		return $filtered_terms;
	}

	/**
	 * Count tags by status
	 *
	 * @param string $status Status to count ('all', 'published', 'draft', 'trashed')
	 * @return int Count of tags
	 * @since 1.3.0
	 */
	private function count_tags_by_status( string $status ): int {
		$terms = get_terms( [
			'taxonomy'   => 'aps_tag',
			'hide_empty' => false,
			'fields'     => 'ids',
		] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return 0;
		}

		$count = 0;
		foreach ( $terms as $term_id ) {
			$term_status = get_term_meta( $term_id, '_aps_tag_status', true );

			// Default to published if not set
			if ( empty( $term_status ) || ! in_array( $term_status, [ 'published', 'draft', 'trashed' ], true ) ) {
				$term_status = 'published';
			}

			if ( $status === 'all' || $term_status === $status ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Display bulk action notices
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function display_bulk_action_notices(): void {
		if ( isset( $_GET['moved_to_draft'] ) ) {
			$count = intval( $_GET['moved_to_draft'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d tags moved to draft.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}
		
		if ( isset( $_GET['moved_to_trash'] ) ) {
			$count = intval( $_GET['moved_to_trash'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d tags moved to trash.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}

		if ( isset( $_GET['restored_from_trash'] ) ) {
			$count = intval( $_GET['restored_from_trash'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d tags restored from trash.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}

		if ( isset( $_GET['permanently_deleted'] ) ) {
			$count = intval( $_GET['permanently_deleted'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d tags permanently deleted.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}
	}

	/**
	 * Add sort order filter HTML above tags table
	 *
	 * Checks if we're on tag management page and adds filter.
	 * Aligns sort filter with bulk action dropdown.
	 *
	 * @return void
	 * @since 1.3.0
	 *
	 * @action admin_footer-edit-tags.php
	 */
	public function add_sort_order_html(): void {
		$screen = get_current_screen();
		
		// Only show on tag management page
		if ( ! $screen || $screen->taxonomy !== 'aps_tag' ) {
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
			// Find: bulk actions container
			var $bulkActions = $('.bulkactions');
			
			if ($bulkActions.length) {
				// Insert sort order filter before bulk actions
				$bulkActions.before(`
					<div class="alignleft actions aps-sort-filter">
						<label for="aps_sort_order" class="screen-reader-text">
							<?php esc_html_e( 'Sort By', 'affiliate-product-showcase' ); ?>
						</label>
						<select name="aps_sort_order" id="aps_sort_order" class="postform">
							<option value="date" <?php selected( $current_sort_order, 'date' ); ?>>
								<?php esc_html_e( 'Date (Newest First)', 'affiliate-product-showcase' ); ?>
							</option>
						</select>
					</div>
				`);
				
				// Ensure both are aligned properly
				$('.aps-sort-filter').css('float', 'left');
				$bulkActions.css('float', 'left');
			}
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for inline status toggle
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function ajax_toggle_tag_status(): void {
		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'aps_toggle_tag_status' ) ) {
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
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid tag ID.', 'affiliate-product-showcase' ) ] );
		}
		
		// Update tag status
		$result = update_term_meta( $term_id, '_aps_tag_status', $new_status );
		
		if ( $result !== false ) {
			wp_send_json_success( [ 'status' => $new_status ] );
		} else {
			wp_send_json_error( [ 'message' => esc_html__( 'Failed to update tag status.', 'affiliate-product-showcase' ) ] );
		}
	}

	/**
	 * Add fields to tag add form
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action aps_tag_add_form_fields
	 */
	public function add_tag_fields(): void {
		$this->render_tag_fields( 0 );
	}

	/**
	 * Add fields to tag edit form
	 *
	 * @param \WP_Term $tag Tag term object
	 * @return void
	 * @since 1.0.0
	 *
	 * @action aps_tag_edit_form_fields
	 */
	public function edit_tag_fields( \WP_Term $tag ): void {
		$this->render_tag_fields( $tag->term_id );
	}

	/**
	 * Render tag fields
	 *
	 * @param int $tag_id Tag ID (0 for new tag)
	 * @return void
	 * @since 1.0.0
	 */
	private function render_tag_fields( int $tag_id ): void {
		// Get current values (TRUE HYBRID: use term meta)
		$image_url = get_term_meta( $tag_id, '_aps_tag_image_url', true );
		$featured = get_term_meta( $tag_id, '_aps_tag_featured', true ) === '1';
		$is_default = get_term_meta( $tag_id, '_aps_tag_is_default', true ) === '1';
		$icon = get_term_meta( $tag_id, '_aps_tag_icon', true );

		?>
		<!-- Featured and Default Checkboxes (will be moved below slug via JavaScript) -->
		<div class="aps-tag-checkbox-wrapper" style="display:none;">
			<div class="form-field">
				<label><?php esc_html_e( 'Tag Options', 'affiliate-product-showcase' ); ?></label>
				
				<div class="aps-tag-checkboxes-inner">
					<!-- Featured Checkbox -->
					<div class="aps-tag-checkbox-item">
						<label for="aps_tag_featured">
							<?php esc_html_e( 'Featured Tag', 'affiliate-product-showcase' ); ?>
						</label>
						<input
							type="checkbox"
							id="aps_tag_featured"
							name="aps_tag_featured"
							value="1"
							<?php checked( $featured, true ); ?>
						/>
						<span class="description">
							<?php esc_html_e( 'Display this tag prominently on frontend.', 'affiliate-product-showcase' ); ?>
						</span>
					</div>
					
					<!-- Default Checkbox -->
					<div class="aps-tag-checkbox-item">
						<label for="aps_tag_is_default">
							<?php esc_html_e( 'Default Tag', 'affiliate-product-showcase' ); ?>
						</label>
						<input
							type="checkbox"
							id="aps_tag_is_default"
							name="aps_tag_is_default"
							value="1"
							<?php checked( $is_default, true ); ?>
						/>
						<span class="description">
							<?php esc_html_e( 'Products without a tag will be assigned to this tag automatically.', 'affiliate-product-showcase' ); ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="form-field aps-tag-settings">
			<h3><?php esc_html_e( '=== Tag Settings ===', 'affiliate-product-showcase' ); ?></h3>

			<!-- Tag Icon -->
			<div class="form-field">
				<label for="_aps_tag_icon">
					<?php esc_html_e( 'Tag Icon', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="text"
					id="_aps_tag_icon"
					name="_aps_tag_icon"
					value="<?php echo esc_attr( $icon ); ?>"
					class="regular-text"
					placeholder="dashicons-tag"
				/>
				<p class="description">
					<?php esc_html_e( 'Enter icon class name (e.g., dashicons-tag).', 'affiliate-product-showcase' ); ?>
				</p>
			</div>

			<!-- Image URL -->
			<div class="form-field">
				<label for="_aps_tag_image_url">
					<?php esc_html_e( 'Tag Image URL', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="url"
					id="_aps_tag_image_url"
					name="_aps_tag_image_url"
					value="<?php echo esc_url( $image_url ); ?>"
					class="regular-text"
					placeholder="https://example.com/tag-image.jpg"
				/>
				<p class="description">
					<?php esc_html_e( 'Enter URL for tag image.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>
		</div>

		<style>
			/* Side-by-side checkbox layout */
			.aps-tag-checkboxes-inner {
				display: flex;
				gap: 20px;
				margin-top: 10px;
			}
			
			.aps-tag-checkbox-item {
				flex: 1;
				display: flex;
				flex-direction: column;
				gap: 5px;
			}
			
			.aps-tag-checkbox-item label {
				font-weight: 600;
				margin-bottom: 5px;
			}
			
			.aps-tag-checkbox-item input[type="checkbox"] {
				margin-right: 8px;
			}
			
			.aps-tag-checkbox-item .description {
				font-size: 12px;
				color: #646970;
				margin-top: 5px;
			}
			
			/* Section divider styling */
			.aps-tag-settings h3 {
				margin: 20px 0 15px 0;
				padding-bottom: 10px;
				border-bottom: 1px solid #dcdcde;
				font-size: 14px;
				font-weight: 600;
				text-align: center;
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			// Move Featured checkbox wrapper below slug field
			$('.aps-tag-checkbox-wrapper').insertAfter($('input[name="slug"]').parent());
			$('.aps-tag-checkbox-wrapper').show();
		});
		</script>

		<?php
		// Nonce field for security
		wp_nonce_field( 'aps_tag_fields', 'aps_tag_fields_nonce' );
	}

	/**
	 * Save tag fields
	 *
	 * @param int $tag_id Tag ID
	 * @param int $term_id Term ID (same as tag_id)
	 * @return void
	 * @since 1.0.0
	 *
	 * @action created_aps_tag
	 * @action edited_aps_tag
	 */
	public function save_tag_fields( int $tag_id, int $term_id ): void {
		// Check nonce
		if ( ! isset( $_POST['aps_tag_fields_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['aps_tag_fields_nonce'], 'aps_tag_fields' ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		// Set default status to published for new tags
		$status = get_term_meta( $tag_id, '_aps_tag_status', true );
		if ( empty( $status ) ) {
			update_term_meta( $tag_id, '_aps_tag_status', 'published' );
		}

		// Save featured flag (TRUE HYBRID: use term meta)
		$featured = isset( $_POST['aps_tag_featured'] ) && $_POST['aps_tag_featured'] === '1';
		update_term_meta( $tag_id, '_aps_tag_featured', $featured ? '1' : '0' );

		// Save default flag with exclusive behavior (TRUE HYBRID: use term meta)
		$is_default = isset( $_POST['aps_tag_is_default'] ) && $_POST['aps_tag_is_default'] === '1';
		
		if ( $is_default ) {
			// Remove default flag from all other tags (exclusive behavior)
			$all_tags = get_terms( [
				'taxonomy'   => 'aps_tag',
				'hide_empty' => false,
				'fields'     => 'ids',
			] );

			if ( ! is_wp_error( $all_tags ) && ! empty( $all_tags ) ) {
				foreach ( $all_tags as $other_tag_id ) {
					if ( intval( $other_tag_id ) !== intval( $tag_id ) ) {
						update_term_meta( $other_tag_id, '_aps_tag_is_default', '0' );
					}
				}
			}

			// Set this tag as default
			update_term_meta( $tag_id, '_aps_tag_is_default', '1' );
		} else {
			// Remove default flag from this tag
			update_term_meta( $tag_id, '_aps_tag_is_default', '0' );
		}


		// Sanitize and save icon
		$icon = isset( $_POST['_aps_tag_icon'] ) 
			? sanitize_text_field( wp_unslash( $_POST['_aps_tag_icon'] ) ) 
			: '';
		update_term_meta( $tag_id, '_aps_tag_icon', $icon );

		// Sanitize and save image URL
		$image_url = isset( $_POST['_aps_tag_image_url'] ) 
			? esc_url_raw( wp_unslash( $_POST['_aps_tag_image_url'] ) ) 
			: '';
		update_term_meta( $tag_id, '_aps_tag_image_url', $image_url );
	}


	/**
	 * Add custom columns to WordPress native tags table
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 * @since 1.0.0
	 *
	 * @filter manage_edit-aps_tag_columns
	 */
	public function add_custom_columns( array $columns ): array {
		// Insert custom columns after 'slug' column
		$new_columns = [];
		
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add custom columns after slug
			if ( $key === 'slug' ) {
				$new_columns['icon'] = __( 'Icon', 'affiliate-product-showcase' );
				$new_columns['status'] = __( 'Status', 'affiliate-product-showcase' );
				$new_columns['count'] = __( 'Count', 'affiliate-product-showcase' );
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
	 * @since 1.0.0
	 *
	 * @filter manage_aps_tag_custom_column
	 */
	public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
		// Render icon column
		if ( $column_name === 'icon' ) {
			$icon = get_term_meta( $term_id, '_aps_tag_icon', true );
			
			if ( ! empty( $icon ) ) {
				// Check if it's a dashicon
				if ( strpos( $icon, 'dashicons-' ) === 0 ) {
					return sprintf(
						'<span class="dashicons %s aps-tag-icon-display"></span>',
						esc_attr( $icon )
					);
				}
				
				// Check if it's an emoji
				$icon_length = mb_strlen( $icon );
				if ( $icon_length <= 4 ) {
					return '<span class="aps-tag-icon-display">' . esc_html( $icon ) . '</span>';
				}
				
				// Default to dashicon
				return '<span class="dashicons dashicons-tag aps-tag-icon-display"></span>';
			}
			
			return '<span class="aps-tag-icon-empty">-</span>';
		}

		// Render status column (TRUE HYBRID: use term meta, inline editable)
		if ( $column_name === 'status' ) {
			$status = get_term_meta( $term_id, '_aps_tag_status', true ) ?: 'published';
			
			return sprintf(
				'<select name="tag_status_%d" class="aps-tag-status-select" data-term-id="%d" aria-label="%s">
					<option value="published" %s>%s</option>
					<option value="draft" %s>%s</option>
				</select>',
				intval( $term_id ),
				intval( $term_id ),
				esc_attr__( 'Change tag status', 'affiliate-product-showcase' ),
				selected( $status, 'published', false ),
				esc_html__( 'Published', 'affiliate-product-showcase' ),
				selected( $status, 'draft', false ),
				esc_html__( 'Draft', 'affiliate-product-showcase' )
			);
		}

		// Render count column (native WordPress count)
		if ( $column_name === 'count' ) {
			$term = get_term( $term_id, 'aps_tag' );
			$count = $term ? $term->count : 0;
			return '<span class="aps-tag-count">' . esc_html( (string) $count ) . '</span>';
		}
		
		return $content;
	}


	/**
	 * Add custom bulk actions to tags table
	 *
	 * Adds bulk actions based on current view (Draft, Trash, Restore, etc.).
	 *
	 * @param array $bulk_actions Existing bulk actions
	 * @return array Modified bulk actions
	 * @since 1.3.0
	 *
	 * @filter bulk_actions-edit-aps_tag
	 */
	public function add_bulk_actions( array $bulk_actions ): array {
		// Get current status from URL
		$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		// If in Trash view, add Restore and Permanently Delete
		if ( $current_status === 'trashed' ) {
			$bulk_actions['restore'] = __( 'Restore', 'affiliate-product-showcase' );
			$bulk_actions['delete_permanently'] = __( 'Delete Permanently', 'affiliate-product-showcase' );
			return $bulk_actions;
		}

		// If not in Trash view, add Move to Draft and Move to Trash
		$bulk_actions['move_to_draft'] = __( 'Move to Draft', 'affiliate-product-showcase' );
		$bulk_actions['move_to_trash'] = __( 'Move to Trash', 'affiliate-product-showcase' );
		
		return $bulk_actions;
	}

	/**
	 * Handle custom bulk actions for tags
	 *
	 * Processes bulk actions: Move to Draft, Move to Trash, Restore, Delete Permanently.
	 *
	 * @param string $redirect_url Redirect URL after processing
	 * @param string $action_name Action name being processed
	 * @param array $term_ids Array of term IDs
	 * @return string Modified redirect URL (with query parameters for notices)
	 * @since 1.3.0
	 *
	 * @filter handle_bulk_actions-edit-aps_tag
	 */
	public function handle_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
		if ( empty( $term_ids ) ) {
			return $redirect_url;
		}
		
		$count = 0;
		
		// Handle "Move to Draft" action
		if ( $action_name === 'move_to_draft' ) {
			foreach ( $term_ids as $term_id ) {
				// Update tag status to draft
				$result = update_term_meta( $term_id, '_aps_tag_status', 'draft' );
				
				if ( $result !== false ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'moved_to_draft' => $count,
				], $redirect_url );
			}
		}
		
		// Handle "Move to Trash" action (sets status to trashed)
		if ( $action_name === 'move_to_trash' ) {
			foreach ( $term_ids as $term_id ) {
				// Set status to trashed
				$result = update_term_meta( $term_id, '_aps_tag_status', 'trashed' );
				
				if ( $result !== false ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'moved_to_trash' => $count,
				], $redirect_url );
			}
		}

		// Handle "Restore" action
		if ( $action_name === 'restore' ) {
			foreach ( $term_ids as $term_id ) {
				// Restore by setting status to published
				$result = update_term_meta( $term_id, '_aps_tag_status', 'published' );
				
				if ( $result !== false ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'restored_from_trash' => $count,
				], $redirect_url );
			}
		}

		// Handle "Delete Permanently" action
		if ( $action_name === 'delete_permanently' ) {
			foreach ( $term_ids as $term_id ) {
				// Permanently delete term
				$result = wp_delete_term( $term_id, 'aps_tag' );
				
				if ( $result && ! is_wp_error( $result ) ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'permanently_deleted' => $count,
				], $redirect_url );
			}
		}
		
		return $redirect_url;
	}
}
