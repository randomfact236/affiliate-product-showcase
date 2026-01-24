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

		?>
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
}