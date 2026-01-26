<?php
/**
 * Ribbon Fields
 *
 * Adds custom fields to ribbon edit/add forms including:
 * - Color field with WordPress color picker
 * - Icon field
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
		$icon = get_term_meta( $ribbon_id, '_aps_ribbon_icon', true ) ?: '';

		?>
		<!-- Color Field (Ribbon-specific) -->
		<div class="form-field">
			<label for="aps_ribbon_color">
				<?php esc_html_e( 'Color', Constants::TEXTDOMAIN ); ?>
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
				<?php esc_html_e( 'Enter hex color code for ribbon (e.g., #ff6b6b).', Constants::TEXTDOMAIN ); ?>
			</p>
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
		// Save color
		if ( isset( $_POST['aps_ribbon_color'] ) ) {
			$color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_color'] ) );
			if ( $color ) {
				update_term_meta( $ribbon_id, '_aps_ribbon_color', $color );
			} else {
				delete_term_meta( $ribbon_id, '_aps_ribbon_color' );
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
		// Call parent for shared columns
		if ( in_array( $column_name, [ 'icon', 'status', 'count' ], true ) ) {
			return parent::render_custom_columns( $content, $column_name, $term_id );
		}
		
		// Render color column (ribbon-specific)
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
		
		return $content;
	}
}