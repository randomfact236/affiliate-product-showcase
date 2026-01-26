<?php
/**
 * Tag Fields
 *
 * Adds custom fields to tag edit/add forms including:
 * - Icon field
 * - Image URL field
 * - Featured checkbox
 * - Default tag checkbox
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
 * Tag Fields
 *
 * Adds custom fields to tag taxonomy edit/add forms.
 * Extends TaxonomyFieldsAbstract for shared functionality.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */
final class TagFields extends TaxonomyFieldsAbstract {
	/**
	 * Get taxonomy name
	 *
	 * @return string Taxonomy name
	 * @since 2.0.0
	 */
	protected function get_taxonomy(): string {
		return 'aps_tag';
	}
	
	/**
	 * Get taxonomy label
	 *
	 * @return string Human-readable label
	 * @since 2.0.0
	 */
	protected function get_taxonomy_label(): string {
		return 'Tag';
	}
	
	/**
	 * Initialize tag fields
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function init(): void {
		// Call parent to initialize shared functionality
		parent::init();
		
		// Tag doesn't need any additional hooks beyond what parent provides
	}
	
	/**
	 * Render tag-specific fields
	 * 
	 * @param int $tag_id Tag ID (0 for new tag)
	 * @return void
	 * @since 2.0.0
	 */
	protected function render_taxonomy_specific_fields( int $tag_id ): void {
		// Get current values (TRUE HYBRID: use term meta with legacy fallback)
		$image_url = $this->get_tag_meta( $tag_id, 'image_url' );
		$icon = $this->get_tag_meta( $tag_id, 'icon' );
		$featured = $this->get_tag_meta( $tag_id, 'featured' ) === '1';
		$is_default = $this->get_is_default( $tag_id ) === '1';

		?>
		<!-- Featured and Default Checkboxes (side by side) -->
		<div class="aps-tag-checkboxes-wrapper" style="display:none;">
			<!-- Featured Checkbox -->
			<div class="form-field aps-tag-featured">
				<label for="_aps_tag_featured">
					<?php esc_html_e( 'Featured Tag', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="checkbox"
					id="_aps_tag_featured"
					name="_aps_tag_featured"
					value="1"
					<?php checked( $featured, true ); ?>
				/>
				<p class="description">
					<?php esc_html_e( 'Display this tag prominently on frontend.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>
			
			<!-- Default Checkbox -->
			<div class="form-field aps-tag-default">
				<label for="_aps_tag_is_default">
					<?php esc_html_e( 'Default Tag', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="checkbox"
					id="_aps_tag_is_default"
					name="_aps_tag_is_default"
					value="1"
					<?php checked( $is_default, true ); ?>
				/>
				<p class="description">
					<?php esc_html_e( 'Products without a tag will be assigned to this tag automatically.', 'affiliate-product-showcase' ); ?>
				</p>
			</div>
		</div>

		<div class="form-field aps-tag-settings">
			<h3><?php esc_html_e( 'Tag Settings', 'affiliate-product-showcase' ); ?></h3>

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
					value="<?php echo esc_attr( $image_url ); ?>"
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
			// Move Featured and Default checkboxes side by side below slug field
			$('.aps-tag-checkboxes-wrapper').insertAfter($('input[name="slug"]').parent());
			$('.aps-tag-checkboxes-wrapper').show();
		});
		</script>

		<?php
	}
	
	/**
	 * Save tag-specific fields
	 * 
	 * @param int $tag_id Tag ID
	 * @return void
	 * @since 2.0.0
	 */
	protected function save_taxonomy_specific_fields( int $tag_id ): void {
		// Save featured flag (TRUE HYBRID: use term meta)
		$featured = isset( $_POST['_aps_tag_featured'] ) ? '1' : '0';
		update_term_meta( $tag_id, '_aps_tag_featured', $featured );
		// Delete legacy key
		delete_term_meta( $tag_id, 'aps_tag_featured' );

		// Save default flag with exclusive behavior (TRUE HYBRID: use term meta)
		$is_default = isset( $_POST['_aps_tag_is_default'] ) ? '1' : '0';
		
		if ( $is_default === '1' ) {
			// Remove default flag from all other tags
			$this->remove_default_from_all_tags();
			// Set this tag as default
			$this->set_is_default( $tag_id, true );
		} else {
			// Remove default flag from this tag
			$this->set_is_default( $tag_id, false );
		}

		// Sanitize and save icon
		$icon = isset( $_POST['_aps_tag_icon'] ) 
			? sanitize_text_field( wp_unslash( $_POST['_aps_tag_icon'] ) ) 
			: '';
		update_term_meta( $tag_id, '_aps_tag_icon', $icon );
		// Delete legacy key
		delete_term_meta( $tag_id, 'aps_tag_icon' );

		// Sanitize and save image URL
		$image_url = isset( $_POST['_aps_tag_image_url'] ) 
			? esc_url_raw( wp_unslash( $_POST['_aps_tag_image_url'] ) ) 
			: '';
		update_term_meta( $tag_id, '_aps_tag_image_url', $image_url );
		// Delete legacy key
		delete_term_meta( $tag_id, 'aps_tag_image_url' );
	}
	
	/**
	 * Get tag meta with legacy fallback
	 *
	 * @param int $tag_id Tag ID
	 * @param string $meta_key Meta key (without _aps_tag_ prefix)
	 * @return mixed Meta value
	 * @since 2.0.0
	 */
	private function get_tag_meta( int $tag_id, string $meta_key ): mixed {
		// Try new format with underscore prefix
		$value = get_term_meta( $tag_id, '_aps_tag_' . $meta_key, true );
		
		// If empty, try legacy format without underscore
		if ( $value === '' || $value === false ) {
			$value = get_term_meta( $tag_id, 'aps_tag_' . $meta_key, true );
		}
		
		return $value;
	}
	
	/**
	 * Remove default flag from all tags
	 *
	 * @return void
	 * @since 2.0.0
	 */
	private function remove_default_from_all_tags(): void {
		$terms = get_terms( [
			'taxonomy'   => 'aps_tag',
			'hide_empty' => false,
			'fields'     => 'ids',
		] );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $tag_id ) {
				delete_term_meta( $tag_id, '_aps_tag_is_default' );
				delete_term_meta( $tag_id, 'aps_tag_is_default' );
			}
		}
	}
	
	/**
	 * Override add_custom_columns to add icon column
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 * @since 2.0.0
	 */
	public function add_custom_columns( array $columns ): array {
		// Call parent for shared columns
		$columns = parent::add_custom_columns( $columns );
		
		// Insert icon column before status
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add icon column before status
			if ( $key === 'slug' ) {
				$new_columns['icon'] = __( 'Icon', 'affiliate-product-showcase' );
			}
		}
		
		return $new_columns;
	}
	
	/**
	 * Override render_custom_columns to add icon column content
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
		
		return $content;
	}
}