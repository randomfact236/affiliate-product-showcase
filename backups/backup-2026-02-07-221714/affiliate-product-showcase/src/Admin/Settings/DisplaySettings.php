<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Display Settings Section (Minimal)
 *
 * Frontend display configuration only.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class DisplaySettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_display';
	const SECTION_TITLE = 'Display Settings';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'product_layout' => 'grid',
			'product_image_ratio' => '16:9',
			'products_per_row' => 3,
		];
	}
	
	/**
	 * Register section and fields
	 *
	 * @return void
	 */
	public function register_section_and_fields(): void {
		\add_settings_section(
			self::SECTION_ID,
			__('Display Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'display']
		);
		
		// Product layout - Controls archive view
		\add_settings_field(
			'product_layout',
			__('Product Layout', 'affiliate-product-showcase'),
			[$this, 'render_product_layout_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'product_layout']
		);
		
		// Products per row - Controls grid density
		\add_settings_field(
			'products_per_row',
			__('Products Per Row', 'affiliate-product-showcase'),
			[$this, 'render_products_per_row_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'products_per_row']
		);
		
		// Image ratio - Controls product image aspect
		\add_settings_field(
			'product_image_ratio',
			__('Product Image Ratio', 'affiliate-product-showcase'),
			[$this, 'render_product_image_ratio_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'product_image_ratio']
		);
	}
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		return [
			'product_layout' => in_array($input['product_layout'] ?? 'grid', ['grid', 'list']) 
				? $input['product_layout'] 
				: 'grid',
			'products_per_row' => max(2, min(4, intval($input['products_per_row'] ?? 3))),
			'product_image_ratio' => in_array($input['product_image_ratio'] ?? '16:9', ['1:1', '4:3', '16:9']) 
				? $input['product_image_ratio'] 
				: '16:9',
		];
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure frontend product display.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render product layout field
	 *
	 * @return void
	 */
	public function render_product_layout_field(): void {
		$settings = $this->get_settings();
		$layouts = [
			'grid' => __('Grid (Card View)', 'affiliate-product-showcase'),
			'list' => __('List (Detailed View)', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[product_layout]">';
		foreach ($layouts as $value => $label) {
			$selected = selected($settings['product_layout'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Layout for product archive pages.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render products per row field
	 *
	 * @return void
	 */
	public function render_products_per_row_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[products_per_row]">';
		foreach ([2, 3, 4] as $value) {
			$selected = selected($settings['products_per_row'], $value, false);
			$label = sprintf(__('%d products', 'affiliate-product-showcase'), $value);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Number of products per row in grid layout.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render product image ratio field
	 *
	 * @return void
	 */
	public function render_product_image_ratio_field(): void {
		$settings = $this->get_settings();
		$ratios = [
			'1:1' => __('1:1 (Square)', 'affiliate-product-showcase'),
			'4:3' => __('4:3 (Standard)', 'affiliate-product-showcase'),
			'16:9' => __('16:9 (Widescreen)', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[product_image_ratio]">';
		foreach ($ratios as $value => $label) {
			$selected = selected($settings['product_image_ratio'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Aspect ratio for product images.', 'affiliate-product-showcase') . '</p>';
	}
}
