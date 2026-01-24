<?php
/**
 * Tag Fields
 *
 * Adds custom fields to tag edit/add forms including:
 * - Color field (hex color picker)
 * - Icon field (emoji or SVG)
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
 * Adds custom fields to tag edit/add forms.
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

		// Add bulk actions
		add_filter( 'bulk_actions-edit-aps_tag', [ $this, 'add_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-edit-aps_tag', [ $this, 'handle_bulk_actions' ], 10, 3 );

		// Add status links above table
		add_action( 'admin_notices', [ $this, 'render_status_links' ] );

		// Enqueue admin scripts and styles
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_admin_assets(): void {
		$screen = get_current_screen();
		if ( $screen && $screen->taxonomy === 'aps_tag' ) {
			wp_enqueue_style(
				'aps-admin-tag',
				Constants::assetUrl( 'assets/css/admin-tag.css' ),
				[],
				Constants::VERSION
			);
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
		// Get current values
		$color = get_term_meta( $tag_id, '_aps_tag_color', true );
		$icon = get_term_meta( $tag_id, '_aps_tag_icon', true );

		// Get current status and featured flag
		$status = 'published';
		$featured = false;
		
		if ( $tag_id > 0 ) {
			$status_terms = wp_get_object_terms( $tag_id, 'aps_tag_visibility' );
			$status = ! empty( $status_terms ) ? $status_terms[0]->slug : 'published';
			
			$flag_terms = wp_get_object_terms( $tag_id, 'aps_tag_flags' );
			$featured = ! empty( $flag_terms ) && $flag_terms[0]->slug === 'featured';
		}

		?>
		<div class="form-field aps-tag-status">
			<label for="aps_tag_status">
				<?php esc_html_e( 'Status', 'affiliate-product-showcase' ); ?>
			</label>
			<select
				id="aps_tag_status"
				name="aps_tag_status"
				class="postform"
			>
				<option value="published" <?php selected( $status, 'published' ); ?>>
					<?php esc_html_e( 'Published', 'affiliate-product-showcase' ); ?>
				</option>
				<option value="draft" <?php selected( $status, 'draft' ); ?>>
					<?php esc_html_e( 'Draft', 'affiliate-product-showcase' ); ?>
				</option>
			</select>
			<p class="description">
				<?php esc_html_e( 'Choose whether this tag is visible on the frontend.', 'affiliate-product-showcase' ); ?>
			</p>
		</div>

		<div class="form-field aps-tag-featured">
			<label for="aps_tag_featured">
				<?php esc_html_e( 'Featured', 'affiliate-product-showcase' ); ?>
			</label>
			<input
				type="checkbox"
				id="aps_tag_featured"
				name="aps_tag_featured"
				value="1"
				<?php checked( $featured, true ); ?>
			/>
			<p class="description">
				<?php esc_html_e( 'Mark this tag as featured.', 'affiliate-product-showcase' ); ?>
			</p>
		</div>

		<div class="form-field aps-tag-color">
			<label for="_aps_tag_color">
				<?php esc_html_e( 'Tag Color', 'affiliate-product-showcase' ); ?>
			</label>
			<input
				type="text"
				id="_aps_tag_color"
				name="_aps_tag_color"
				value="<?php echo esc_attr( $color ); ?>"
				class="color-picker"
				data-default-color="#0073aa"
			/>
			<p class="description">
				<?php esc_html_e( 'Choose a color for this tag (hex format).', 'affiliate-product-showcase' ); ?>
			</p>
		</div>

		<div class="form-field aps-tag-icon">
			<label for="_aps_tag_icon">
				<?php esc_html_e( 'Tag Icon', 'affiliate-product-showcase' ); ?>
			</label>
			<input
				type="text"
				id="_aps_tag_icon"
				name="_aps_tag_icon"
				value="<?php echo esc_attr( $icon ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( '🏷️ or <svg>...', 'affiliate-product-showcase' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Enter an emoji or SVG icon for this tag.', 'affiliate-product-showcase' ); ?>
			</p>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Initialize color picker
			if ($.fn.wpColorPicker) {
				$('#_aps_tag_color').wpColorPicker({
					change: function(event, ui) {
						// Real-time preview could be added here
					},
					clear: function() {
						// Handle clear color
					}
				});
			}
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

		// Save status
		$status = isset( $_POST['aps_tag_status'] ) 
			? sanitize_text_field( wp_unslash( $_POST['aps_tag_status'] ) ) 
			: 'published';
		
		// Validate status
		$valid_statuses = [ 'published', 'draft' ];
		if ( ! in_array( $status, $valid_statuses, true ) ) {
			$status = 'published';
		}
		
		TagStatus::set_visibility( $tag_id, $status );

		// Save featured flag
		$featured = isset( $_POST['aps_tag_featured'] ) && $_POST['aps_tag_featured'] === '1';
		$flag_slug = $featured ? 'featured' : 'none';
		TagFlags::set_featured( $tag_id, $flag_slug );

		// Sanitize and save color (hex color)
		$color = isset( $_POST['_aps_tag_color'] ) 
			? sanitize_hex_color( wp_unslash( $_POST['_aps_tag_color'] ) ) 
			: '';
		update_term_meta( $tag_id, '_aps_tag_color', $color );

		// Sanitize and save icon (emoji or SVG)
		$icon = isset( $_POST['_aps_tag_icon'] ) 
			? wp_kses_post( wp_unslash( $_POST['_aps_tag_icon'] ) ) 
			: '';
		update_term_meta( $tag_id, '_aps_tag_icon', $icon );
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
				$new_columns['status'] = __( 'Status', 'affiliate-product-showcase' );
				$new_columns['featured'] = __( 'Featured', 'affiliate-product-showcase' );
				$new_columns['color'] = __( 'Color', 'affiliate-product-showcase' );
				$new_columns['icon'] = __( 'Icon', 'affiliate-product-showcase' );
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
		// Render status column
		if ( $column_name === 'status' ) {
			$status_terms = wp_get_object_terms( $term_id, 'aps_tag_visibility' );
			$status = ! empty( $status_terms ) ? $status_terms[0]->slug : 'published';
			
			$status_labels = [
				'published' => __( 'Published', 'affiliate-product-showcase' ),
				'draft' => __( 'Draft', 'affiliate-product-showcase' ),
				'trash' => __( 'Trash', 'affiliate-product-showcase' ),
			];
			
			$status_label = $status_labels[ $status ] ?? $status;
			
			return sprintf(
				'<span class="aps-tag-status aps-tag-status-%s">%s</span>',
				esc_attr( $status ),
				esc_html( $status_label )
			);
		}

		// Render featured column
		if ( $column_name === 'featured' ) {
			$flag_terms = wp_get_object_terms( $term_id, 'aps_tag_flags' );
			$featured = ! empty( $flag_terms ) && $flag_terms[0]->slug === 'featured';
			
			if ( $featured ) {
				return '<span class="aps-tag-featured-badge">★ ' . esc_html__( 'Featured', 'affiliate-product-showcase' ) . '</span>';
			}
			
			return '&mdash;';
		}

		// Render color column
		if ( $column_name === 'color' ) {
			$color = get_term_meta( $term_id, '_aps_tag_color', true );
			
			if ( ! empty( $color ) ) {
				// Display color badge
				return sprintf(
					'<span class="aps-tag-color-badge" style="background-color: %s;" aria-label="%s"></span> <code>%s</code>',
					esc_attr( $color ),
					esc_attr__( 'Tag color', 'affiliate-product-showcase' ),
					esc_html( $color )
				);
			}
			
			return '&mdash;';
		}
		
		// Render icon column
		if ( $column_name === 'icon' ) {
			$icon = get_term_meta( $term_id, '_aps_tag_icon', true );
			
			if ( ! empty( $icon ) ) {
				// Display icon (emoji or SVG)
				return sprintf(
					'<span class="aps-tag-icon-display">%s</span>',
					wp_kses_post( $icon )
				);
			}
			
			return '&mdash;';
		}
		
		return $content;
	}

	/**
	 * Render status links (above table)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_status_links(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== 'aps_tag' ) {
			return;
		}

		// Get visibility terms
		$published_term = get_term_by( 'slug', 'published', 'aps_tag_visibility' );
		$draft_term = get_term_by( 'slug', 'draft', 'aps_tag_visibility' );
		$trash_term = get_term_by( 'slug', 'trash', 'aps_tag_visibility' );

		// Get counts for each status
		$all_count = wp_count_terms( 'aps_tag', [ 'hide_empty' => false ] );
		$published_count = wp_count_terms( 'aps_tag', [ 
			'hide_empty' => false,
			'meta_query' => $published_term ? [
				[
					'key' => 'term_id',
					'value' => $published_term->term_id,
					'taxonomy' => 'aps_tag_visibility',
				],
			] : [],
		] );
		$draft_count = wp_count_terms( 'aps_tag', [
			'hide_empty' => false,
			'meta_query' => $draft_term ? [
				[
					'key' => 'term_id',
					'value' => $draft_term->term_id,
					'taxonomy' => 'aps_tag_visibility',
				],
			] : [],
		] );
		$trash_count = wp_count_terms( 'aps_tag', [
			'hide_empty' => false,
			'meta_query' => $trash_term ? [
				[
					'key' => 'term_id',
					'value' => $trash_term->term_id,
					'taxonomy' => 'aps_tag_visibility',
				],
			] : [],
		] );

		$current_status = isset( $_GET['tag_status'] ) ? sanitize_text_field( $_GET['tag_status'] ) : 'all';
		
		?>
		<div class="aps-tag-status-links-wrapper">
			<ul class="aps-tag-status-links">
				<li class="<?php echo $current_status === 'all' ? 'current' : ''; ?>">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=aps_tag&tag_status=all' ) ); ?>">
						<?php echo esc_html( sprintf( __( 'All (%d)', 'affiliate-product-showcase' ), $all_count ) ); ?>
					</a>
				</li>
				<li class="<?php echo $current_status === 'published' ? 'current' : ''; ?>">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=aps_tag&tag_status=published' ) ); ?>">
						<?php echo esc_html( sprintf( __( 'Published (%d)', 'affiliate-product-showcase' ), $published_count ) ); ?>
					</a>
				</li>
				<li class="<?php echo $current_status === 'draft' ? 'current' : ''; ?>">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=aps_tag&tag_status=draft' ) ); ?>">
						<?php echo esc_html( sprintf( __( 'Draft (%d)', 'affiliate-product-showcase' ), $draft_count ) ); ?>
					</a>
				</li>
				<li class="<?php echo $current_status === 'trash' ? 'current' : ''; ?>">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=aps_tag&tag_status=trash' ) ); ?>">
						<?php echo esc_html( sprintf( __( 'Trash (%d)', 'affiliate-product-showcase' ), $trash_count ) ); ?>
					</a>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Add bulk actions to tags table
	 *
	 * @param array $actions Existing bulk actions
	 * @return array Modified bulk actions
	 * @since 1.0.0
	 *
	 * @filter bulk_actions-edit-aps_tag
	 */
	public function add_bulk_actions( array $actions ): array {
		// Remove default delete action
		unset( $actions['delete'] );

		// Add custom bulk actions
		$actions['set_published'] = __( 'Move to Published', 'affiliate-product-showcase' );
		$actions['set_draft'] = __( 'Move to Draft', 'affiliate-product-showcase' );
		$actions['set_trash'] = __( 'Move to Trash', 'affiliate-product-showcase' );
		$actions['delete_permanently'] = __( 'Delete Permanently', 'affiliate-product-showcase' );

		return $actions;
	}

	/**
	 * Handle bulk actions for tags
	 *
	 * @param string $redirect_to Redirect URL
	 * @param string $doaction Action to perform
	 * @param array $tag_ids Tag IDs to process
	 * @return string Redirect URL
	 * @since 1.0.0
	 *
	 * @filter handle_bulk_actions-edit-aps_tag
	 */
	public function handle_bulk_actions( string $redirect_to, string $doaction, array $tag_ids ): string {
		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			return $redirect_to;
		}

		$processed = 0;
		$message = '';

		switch ( $doaction ) {
			case 'set_published':
				$processed = $this->bulk_set_status( $tag_ids, 'published' );
				$message = sprintf( __( '%d tags moved to published.', 'affiliate-product-showcase' ), $processed );
				break;

			case 'set_draft':
				$processed = $this->bulk_set_status( $tag_ids, 'draft' );
				$message = sprintf( __( '%d tags moved to draft.', 'affiliate-product-showcase' ), $processed );
				break;

			case 'set_trash':
				$processed = $this->bulk_set_status( $tag_ids, 'trash' );
				$message = sprintf( __( '%d tags moved to trash.', 'affiliate-product-showcase' ), $processed );
				break;

			case 'delete_permanently':
				$processed = $this->bulk_delete_permanently( $tag_ids );
				$message = sprintf( __( '%d tags permanently deleted.', 'affiliate-product-showcase' ), $processed );
				break;
		}

		if ( $processed > 0 ) {
			// Add success message to redirect URL
			$redirect_to = add_query_arg( [
				'aps_bulk_updated' => '1',
				'aps_bulk_message' => urlencode( $message ),
				'aps_bulk_count' => $processed,
			], $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Bulk set status for tags
	 *
	 * @param array<int, int> $tag_ids Tag IDs
	 * @param string $status Status to set
	 * @return int Number of tags processed
	 * @since 1.0.0
	 */
	private function bulk_set_status( array $tag_ids, string $status ): int {
		$count = 0;
		
		foreach ( $tag_ids as $tag_id ) {
			$result = TagStatus::set_visibility( $tag_id, $status );
			if ( $result ) {
				$count++;
			}
		}
		
		return $count;
	}

	/**
	 * Bulk delete tags permanently
	 *
	 * @param array<int, int> $tag_ids Tag IDs
	 * @return int Number of tags deleted
	 * @since 1.0.0
	 */
	private function bulk_delete_permanently( array $tag_ids ): int {
		$count = 0;
		
		foreach ( $tag_ids as $tag_id ) {
			$result = wp_delete_term( $tag_id, 'aps_tag', [ 'force_delete' => true ] );
			if ( $result && ! is_wp_error( $result ) ) {
				$count++;
			}
		}
		
		return $count;
	}
}
