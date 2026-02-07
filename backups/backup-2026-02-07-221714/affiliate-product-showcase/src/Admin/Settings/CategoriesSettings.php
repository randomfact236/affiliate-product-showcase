<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Categories Settings Section
 *
 * Default category background color using wpColorPicker.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class CategoriesSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_categories';
	const SECTION_TITLE = 'Category Settings';
	
	const DEFAULT_BACKGROUND_COLOR = '#f0f0f1';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'category_background_color' => self::DEFAULT_BACKGROUND_COLOR,
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
			__('Category Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'categories']
		);
		
		\add_settings_field(
			'category_background_color',
			__('Default Background Color', 'affiliate-product-showcase'),
			[$this, 'render_category_background_color_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'category_background_color']
		);
	}
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$color = sanitize_hex_color($input['category_background_color'] ?? self::DEFAULT_BACKGROUND_COLOR);
		
		return [
			'category_background_color' => $color ?: self::DEFAULT_BACKGROUND_COLOR,
		];
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure default category appearance.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render category background color field
	 * Uses WordPress wpColorPicker (same as tag add form)
	 *
	 * @return void
	 */
	public function render_category_background_color_field(): void {
		$settings = $this->get_settings();
		$color = $settings['category_background_color'];
		
		echo '<input type="text" 
			name="' . esc_attr($this->option_name) . '[category_background_color]" 
			id="category_background_color" 
			value="' . esc_attr($color) . '" 
			class="aps-color-picker regular-text"
			placeholder="#f0f0f1"
			pattern="^#[0-9a-fA-F]{6}$"
			maxlength="7">';
		
		echo '<p class="description">' . 
			esc_html__('Default background color for category badges. Individual categories can override this.', 'affiliate-product-showcase') . 
			'</p>';
	}
}
