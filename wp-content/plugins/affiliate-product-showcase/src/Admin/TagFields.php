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
		
		// Add filter to modify name column display
		add_filter( $this->get_taxonomy() . '_row_actions', [ $this, 'modify_name_column' ], 5, 2 );
		
		// Remove description field from tag form
		add_filter( $this->get_taxonomy() . '_add_form_fields', [ $this, 'remove_description_field' ], 9 );
		add_filter( $this->get_taxonomy() . '_edit_form_fields', [ $this, 'remove_description_field' ], 9 );
	}

	/**
	 * Enqueue admin assets (color picker for tags)
	 *
	 * @param string $hook_suffix Current admin page hook
	 * @return void
	 * @since 2.0.0
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		$screen = get_current_screen();

		if ( $screen && $screen->taxonomy === $this->get_taxonomy() ) {
			// Enqueue WordPress color picker
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			// Add inline script for initializing color picker
			wp_add_inline_script( 'wp-color-picker', $this->get_color_picker_script() );

			// Enqueue taxonomy-specific JS/CSS if present (parent handles that too)
			parent::enqueue_admin_assets( $hook_suffix );
		}
	}

	/**
	 * Get inline JavaScript for color picker
	 *
	 * @return string Inline script
	 * @since 2.0.0
	 */
	private function get_color_picker_script(): string {
		ob_start();
		?>
		jQuery(document).ready(function($) {
			if ( $('.aps-color-picker').length ) {
				$('.aps-color-picker').wpColorPicker({
					change: function(event, ui) {
						$(this).val(ui.color.toString());
						// Update preview if present
						var text = $('#_aps_tag_color').val() || '';
						var bg = $('#_aps_tag_bg_color').val() || '';
						if ( $('#tag-preview').length ) {
							$('#tag-preview').css({ 'color': text, 'background-color': bg });
						}
					},
					clear: function() {
						$(this).val('');
						var text = $('#_aps_tag_color').val() || '';
						var bg = $('#_aps_tag_bg_color').val() || '';
						if ( $('#tag-preview').length ) {
							$('#tag-preview').css({ 'color': text, 'background-color': bg });
						}
					}
				});
			}

			// Initialize preview on load
			var initText = $('#_aps_tag_color').val() || '';
			var initBg = $('#_aps_tag_bg_color').val() || '';
			if ( $('#tag-preview').length ) {
				$('#tag-preview').css({ 'color': initText, 'background-color': initBg });
			}
		});
		<?php
		return ob_get_clean();
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
		<div class="aps-tag-checkboxes-wrapper">
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

			<!-- Text Color -->
			<div class="form-field">
				<label for="_aps_tag_color">
					<?php esc_html_e( 'Text Color', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="text"
					id="_aps_tag_color"
					name="_aps_tag_color"
					value="<?php echo esc_attr( $this->get_tag_meta( $tag_id, 'color' ) ?: '#ffffff' ); ?>"
					class="aps-color-picker regular-text"
					placeholder="#ffffff"
					pattern="^#[0-9a-fA-F]{6}$"
					maxlength="7"
				/>
				<p class="description">
					<?php esc_html_e( 'Enter hex color code for tag text (e.g., #ffffff).', 'affiliate-product-showcase' ); ?>
				</p>
			</div>

			<!-- Background Color -->
			<div class="form-field">
				<label for="_aps_tag_bg_color">
					<?php esc_html_e( 'Background Color', 'affiliate-product-showcase' ); ?>
				</label>
				<input
					type="text"
					id="_aps_tag_bg_color"
					name="_aps_tag_bg_color"
					value="<?php echo esc_attr( $this->get_tag_meta( $tag_id, 'bg_color' ) ?: '#ff6b6b' ); ?>"
					class="aps-color-picker regular-text"
					placeholder="#ff6b6b"
					pattern="^#[0-9a-fA-F]{6}$"
					maxlength="7"
				/>
				<p class="description">
					<?php esc_html_e( 'Enter hex color code for tag background (e.g., #ff6b6b).', 'affiliate-product-showcase' ); ?>
				</p>

				<div class="ribbon-live-preview" id="tag-preview-container">
					<span class="preview-label">Preview:</span>
					<div class="ribbon-preview-badge" id="tag-preview">
						<?php esc_html_e( 'Tag', 'affiliate-product-showcase' ); ?>
					</div>
				</div>
				</div>
			</div>

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

		// Save text color
		if ( isset( $_POST['_aps_tag_color'] ) ) {
			$color = sanitize_hex_color( wp_unslash( $_POST['_aps_tag_color'] ) );
			if ( $color ) {
				update_term_meta( $tag_id, '_aps_tag_color', $color );
			} else {
				delete_term_meta( $tag_id, '_aps_tag_color' );
			}
		}

		// Save background color
		if ( isset( $_POST['_aps_tag_bg_color'] ) ) {
			$bg = sanitize_hex_color( wp_unslash( $_POST['_aps_tag_bg_color'] ) );
			if ( $bg ) {
				update_term_meta( $tag_id, '_aps_tag_bg_color', $bg );
			} else {
				delete_term_meta( $tag_id, '_aps_tag_bg_color' );
			}
		}
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
	 * Remove description field from tag form
	 *
	 * Removes the WordPress native description field from add/edit forms.
	 *
	 * @param string $taxonomy Taxonomy name (for edit form) or empty (for add form)
	 * @return void
	 * @since 2.0.0
	 */
	public function remove_description_field( string $taxonomy = '' ): void {
		ob_start();
		?>
		<script>
		jQuery(document).ready(function($) {
			// Remove description field from add form
			$('#tag-description').closest('.form-field').remove();
			
			// Remove description field from edit form
			$('#description').closest('.form-wrap').remove();
		});
		</script>
		<?php
		echo ob_get_clean();
	}
	
	/**
	 * Modify name column to show icon and colored badge
	 *
	 * @param array $actions Row actions
	 * @param \WP_Term $term Term object
	 * @return array Row actions (unchanged)
	 * @since 2.0.0
	 */
	public function modify_name_column( array $actions, \WP_Term $term ): array {
		// This filter is called after the name is rendered, but we can use it to inject CSS
		// that modifies the already-rendered name column via JavaScript
		// Add inline style to make name display as badge
		static $script_added = false;
		if ( ! $script_added ) {
			$script_added = true;
			add_action( 'admin_footer', function() {
				?>
				<script>
				jQuery(document).ready(function($) {
					// Modify tag name column to show as badge with colors
					$('.column-name a.row-title').each(function() {
						var $link = $(this);
						var termId = $link.closest('tr').attr('id');
						if (termId && termId.indexOf('tag-') === 0) {
							var $row = $link.closest('tr');
							// Get colors from the color columns
							var textColor = $row.find('.column-color .aps-tag-color-swatch').attr('title') || '';
							var bgColor = $row.find('.column-bg_color .aps-tag-bg-color-swatch').attr('title') || '';
							var icon = $row.find('.column-icon .aps-tag-icon-display').clone();
							
							if (textColor || bgColor) {
								var $badge = $('<span class="aps-ribbon-name-badge"></span>');
								if (textColor) $badge.css('color', textColor);
								if (bgColor) $badge.css('background-color', bgColor);
								if (icon.length) {
									icon.css({'vertical-align': 'middle', 'margin-right': '6px'});
									$badge.append(icon);
								}
								$badge.append($link.text());
								$link.html($badge);
							}
						}
					});
				});
				</script>
				<?php
			} );
		}
		return $actions;
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
		// Remove description column from table
		unset( $columns['description'] );
		
		// Call parent for shared columns
		$columns = parent::add_custom_columns( $columns );
		
		// Insert icon and color columns after slug (like ribbons)
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add tag-specific columns after slug
			if ( $key === 'slug' ) {
				$new_columns['color'] = __( 'Text Color', 'affiliate-product-showcase' );
				$new_columns['bg_color'] = __( 'Background', 'affiliate-product-showcase' );
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
		
		// Render text color column
		if ( $column_name === 'color' ) {
			$color = get_term_meta( $term_id, '_aps_tag_color', true );
			
			if ( ! empty( $color ) ) {
				return sprintf(
					'<span class="aps-tag-color-swatch" style="background-color: %s; display:inline-block; width:20px; height:20px; border-radius:4px;" title="%s"></span>',
					esc_attr( $color ),
					esc_attr( $color )
				);
			}
			
			return '<span class="aps-tag-color-empty">-</span>';
		}
		
		// Render background color column
		if ( $column_name === 'bg_color' ) {
			$bg_color = get_term_meta( $term_id, '_aps_tag_bg_color', true );
			
			if ( ! empty( $bg_color ) ) {
				return sprintf(
					'<span class="aps-tag-bg-color-swatch" style="background-color: %s; display:inline-block; width:20px; height:20px; border-radius:4px; border:2px solid #ccc;" title="%s"></span>',
					esc_attr( $bg_color ),
					esc_attr( $bg_color )
				);
			}
			
			return '<span class="aps-tag-bg-color-empty">-</span>';
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