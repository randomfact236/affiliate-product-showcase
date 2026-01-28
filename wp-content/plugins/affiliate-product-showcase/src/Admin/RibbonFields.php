<?php
/**
 * Ribbon Fields
 *
 * Adds custom fields to ribbon edit/add forms including:
 * - Color field with WordPress color picker
 * - Icon field
 * - Background color field with presets
 * - Live preview area
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
 * Ribbon Fields
 *
 * Adds custom fields to ribbon taxonomy edit/add forms.
 * Extends TaxonomyFieldsAbstract for shared functionality.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */
final class RibbonFields extends TaxonomyFieldsAbstract {
	/**
	 * Get taxonomy name
	 *
	 * @return string Taxonomy name
	 * @since 2.0.0
	 */
	protected function get_taxonomy(): string {
		return 'aps_ribbon';
	}
	
	/**
	 * Get taxonomy label
	 *
	 * @return string Human-readable label
	 * @since 2.0.0
	 */
	protected function get_taxonomy_label(): string {
		return 'Ribbon';
	}
	
	/**
	 * Enqueue admin assets (ribbon-specific: color picker)
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
			
			// Add inline script for color picker
			wp_add_inline_script( 'wp-color-picker', $this->get_color_picker_script() );

			// Enqueue ribbon-specific JavaScript
			wp_enqueue_script(
				'aps-admin-ribbon-js',
				Constants::assetUrl( 'assets/js/admin-ribbon.js' ),
				[ 'jquery' ],
				Constants::VERSION,
				true
			);

			wp_localize_script( 'aps-admin-ribbon-js', 'aps_admin_vars', [
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( $this->get_nonce_action( 'toggle_status' ) ),
				'row_action_nonce'=> wp_create_nonce( $this->get_nonce_action( 'row_action' ) ),
				'success_text'     => esc_html__( $this->get_taxonomy_label() . ' status updated successfully.', 'affiliate-product-showcase' ),
				'error_text'       => esc_html__( 'An error occurred. Please try again.', 'affiliate-product-showcase' ),
			] );
			
			// Add ribbon-specific hook to hide description field
			add_action( 'admin_head', [ $this, 'hide_description_field' ] );
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
			// Initialize color picker
			if ( $('.aps-color-picker').length ) {
				$('.aps-color-picker').wpColorPicker({
					change: function(event, ui) {
						// Update value when color changes
						$(this).val(ui.color.toString());
					},
					clear: function() {
						// Clear value when clear button clicked
						$(this).val('');
					}
				});
			}
		});
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Hide description field from ribbon taxonomy
	 *
	 * The description field is a built-in WordPress taxonomy field.
	 * We hide it via CSS since it cannot be completely removed.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function hide_description_field(): void {
		$screen = get_current_screen();
		
		// Check if we're on a ribbon taxonomy page
		if ( ! $screen || ! in_array( $screen->base, [ 'edit-tags', 'term' ] ) ) {
			return;
		}
		
		if ( ! isset( $screen->taxonomy ) || $screen->taxonomy !== $this->get_taxonomy() ) {
			return;
		}
		
		?>
		<style>
			/* Hide description field in ribbon taxonomy forms */
			.tag-description,
			.form-field.term-description-wrap,
			tr.form-field.term-description-wrap {
				display: none !important;
			}
			
			/* Hide description column in ribbon table */
			.column-description {
				display: none !important;
			}
			
			/* Hide "Description" table header */
			th.manage-column.column-description {
				display: none !important;
			}
			
			/* Ribbon name badge styling */
			.aps-ribbon-name-badge {
				display: inline-block;
				padding: 4px 12px;
				border-radius: 4px;
				font-weight: 600;
				font-size: 12px;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
			}
		</style>
		<?php
	}
	
	/**
	 * Render ribbon-specific fields
	 * 
	 * @param int $ribbon_id Ribbon ID (0 for new ribbon)
	 * @return void
	 * @since 2.0.0
	 */
	protected function render_taxonomy_specific_fields( int $ribbon_id ): void {
		// Get current values
		$color = get_term_meta( $ribbon_id, '_aps_ribbon_color', true ) ?: '#ff6b6b';
		$bg_color = get_term_meta( $ribbon_id, '_aps_ribbon_bg_color', true ) ?: '#ff0000';
		$icon = get_term_meta( $ribbon_id, '_aps_ribbon_icon', true ) ?: '';

		?>
		<!-- Color Field (Ribbon text color) -->
		<div class="form-field">
			<label for="aps_ribbon_color">
				<?php esc_html_e( 'Text Color', Constants::TEXTDOMAIN ); ?>
			</label>
			<input 
				type="text" 
				name="aps_ribbon_color" 
				id="aps_ribbon_color" 
				value="<?php echo esc_attr( $color ); ?>" 
				class="aps-color-picker regular-text"
				placeholder="#ff6b6b"
				pattern="^#[0-9a-fA-F]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Enter hex color code for ribbon text (e.g., #ff6b6b).', Constants::TEXTDOMAIN ); ?>
			</p>
		</div>

		<!-- Background Color Field (Ribbon background) -->
		<div class="form-field">
			<label for="aps_ribbon_bg_color">
				<?php esc_html_e( 'Background Color', Constants::TEXTDOMAIN ); ?>
			</label>
			<input 
				type="text" 
				name="aps_ribbon_bg_color" 
				id="aps_ribbon_bg_color" 
				value="<?php echo esc_attr( $bg_color ); ?>" 
				class="aps-color-picker regular-text"
				placeholder="#ff0000"
				pattern="^#[0-9a-fA-F]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Enter hex color code for ribbon background (e.g., #ff0000).', Constants::TEXTDOMAIN ); ?>
			</p>
			
			<!-- Color Presets -->
			<div class="color-presets">
				<button type="button" class="preset-color" data-color="#ff0000" title="Red">
					<span class="color-swatch" style="background-color: #ff0000;"></span>
				</button>
				<button type="button" class="preset-color" data-color="#00ff00" title="Green">
					<span class="color-swatch" style="background-color: #00ff00;"></span>
				</button>
				<button type="button" class="preset-color" data-color="#0000ff" title="Blue">
					<span class="color-swatch" style="background-color: #0000ff;"></span>
				</button>
				<button type="button" class="preset-color" data-color="#ffff00" title="Yellow">
					<span class="color-swatch" style="background-color: #ffff00;"></span>
				</button>
				<button type="button" class="preset-color" data-color="#ff00ff" title="Purple">
					<span class="color-swatch" style="background-color: #ff00ff;"></span>
				</button>
				<button type="button" class="preset-color" data-color="#000000" title="Black">
					<span class="color-swatch" style="background-color: #000000;"></span>
				</button>
				<button type="button" class="preset-color" data-color="#ff6600" title="Orange">
					<span class="color-swatch" style="background-color: #ff6600;"></span>
				</button>
			</div>
			
		<!-- Live Preview -->
			<div class="ribbon-live-preview" id="ribbon-preview-container">
				<span class="preview-label">Preview:</span>
				<div class="ribbon-preview-badge" id="ribbon-preview">
					<?php esc_html_e( 'SALE', Constants::TEXTDOMAIN ); ?>
				</div>
			</div>
		</div>

		<!-- Icon Field -->
		<div class="form-field">
			<label for="aps_ribbon_icon">
				<?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?>
			</label>
			<input 
				type="text" 
				name="aps_ribbon_icon" 
				id="aps_ribbon_icon" 
				value="<?php echo esc_attr( $icon ); ?>" 
				class="regular-text" 
			/>
			<p class="description">
				<?php esc_html_e( 'Enter an icon class or identifier (e.g., "star", "badge").', Constants::TEXTDOMAIN ); ?>
			</p>
		</div>
		<?php
	}
	
	/**
	 * Save ribbon-specific fields
	 * 
	 * @param int $ribbon_id Ribbon ID
	 * @return void
	 * @since 2.0.0
	 */
	protected function save_taxonomy_specific_fields( int $ribbon_id ): void {
		// Save text color
		if ( isset( $_POST['aps_ribbon_color'] ) ) {
			$color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_color'] ) );
			if ( $color ) {
				update_term_meta( $ribbon_id, '_aps_ribbon_color', $color );
			} else {
				delete_term_meta( $ribbon_id, '_aps_ribbon_color' );
			}
		}

		// Save background color
		if ( isset( $_POST['aps_ribbon_bg_color'] ) ) {
			$bg_color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_bg_color'] ) );
			if ( $bg_color ) {
				update_term_meta( $ribbon_id, '_aps_ribbon_bg_color', $bg_color );
			} else {
				delete_term_meta( $ribbon_id, '_aps_ribbon_bg_color' );
			}
		}

		// Save icon
		if ( isset( $_POST['aps_ribbon_icon'] ) ) {
			$icon = sanitize_text_field( wp_unslash( $_POST['aps_ribbon_icon'] ) );
			if ( $icon ) {
				update_term_meta( $ribbon_id, '_aps_ribbon_icon', $icon );
			} else {
				delete_term_meta( $ribbon_id, '_aps_ribbon_icon' );
			}
		}
	}
	
	/**
	 * Override add_custom_columns to add color column
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 * @since 2.0.0
	 */
	public function add_custom_columns( array $columns ): array {
		// Call parent for shared columns
		$columns = parent::add_custom_columns( $columns );
		
		// Insert color column before icon
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add color column before icon
			if ( $key === 'slug' ) {
				$new_columns['color'] = __( 'Color', 'affiliate-product-showcase' );
				$new_columns['bg_color'] = __( 'Background', 'affiliate-product-showcase' );
			}
		}
		
		return $new_columns;
	}
	
	/**
	 * Override render_custom_columns to add color column content
	 *
	 * @param string $content Column content
	 * @param string $column_name Column name
	 * @param int $term_id Term ID
	 * @return string Column content
	 * @since 2.0.0
	 */
	public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
		// Render ribbon name with background color
		if ( $column_name === 'name' ) {
			$term = get_term( $term_id );
			$bg_color = get_term_meta( $term_id, '_aps_ribbon_bg_color', true );
			$text_color = get_term_meta( $term_id, '_aps_ribbon_color', true );
			
			if ( $term && ! is_wp_error( $term ) ) {
				$styles = [];
				
				if ( ! empty( $bg_color ) ) {
					$styles[] = 'background-color: ' . esc_attr( $bg_color );
				}
				
				if ( ! empty( $text_color ) ) {
					$styles[] = 'color: ' . esc_attr( $text_color );
				}
				
				$style_attr = ! empty( $styles ) ? ' style="' . implode( '; ', $styles ) . '"' : '';
				
				return sprintf(
					'<span class="aps-ribbon-name-badge"%s>%s</span>',
					$style_attr,
					esc_html( $term->name )
				);
			}
			
			return $content;
		}
		
		// Call parent for shared columns
		if ( in_array( $column_name, [ 'icon', 'status', 'count' ], true ) ) {
			return parent::render_custom_columns( $content, $column_name, $term_id );
		}
		
		// Render color column (text color)
		if ( $column_name === 'color' ) {
			$color = get_term_meta( $term_id, '_aps_ribbon_color', true );
			
			if ( ! empty( $color ) ) {
				return sprintf(
					'<span class="aps-ribbon-color-swatch" style="background-color: %s; display:inline-block; width:20px; height:20px; border-radius:4px;" title="%s"></span>',
					esc_attr( $color ),
					esc_attr( $color )
				);
			}
			
			return '<span class="aps-ribbon-color-empty">-</span>';
		}
		
		// Render background color column
		if ( $column_name === 'bg_color' ) {
			$bg_color = get_term_meta( $term_id, '_aps_ribbon_bg_color', true );
			
			if ( ! empty( $bg_color ) ) {
				return sprintf(
					'<span class="aps-ribbon-bg-color-swatch" style="background-color: %s; display:inline-block; width:20px; height:20px; border-radius:4px; border:2px solid #ccc;" title="%s"></span>',
					esc_attr( $bg_color ),
					esc_attr( $bg_color )
				);
			}
			
			return '<span class="aps-ribbon-bg-color-empty">-</span>';
		}
		
		return $content;
	}
}