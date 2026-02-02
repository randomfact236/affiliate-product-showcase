<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Tags Settings Section
 *
 * Enable tag colors and default background color using wpColorPicker.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class TagsSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_tags';
	const SECTION_TITLE = 'Tag Settings';
	
	const DEFAULT_BACKGROUND_COLOR = '#dbeafe';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'enable_tag_colors' => true,
			'tag_background_color' => self::DEFAULT_BACKGROUND_COLOR,
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
			__('Tag Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'tags']
		);
		
		// Enable tag colors toggle
		\add_settings_field(
			'enable_tag_colors',
			__('Enable Tag Colors', 'affiliate-product-showcase'),
			[$this, 'render_enable_tag_colors_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_tag_colors']
		);
		
		// Default background color
		\add_settings_field(
			'tag_background_color',
			__('Default Background Color', 'affiliate-product-showcase'),
			[$this, 'render_tag_background_color_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'tag_background_color']
		);
	}
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$color = sanitize_hex_color($input['tag_background_color'] ?? self::DEFAULT_BACKGROUND_COLOR);
		
		return [
			'enable_tag_colors' => isset($input['enable_tag_colors']),
			'tag_background_color' => $color ?: self::DEFAULT_BACKGROUND_COLOR,
		];
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure tag appearance. Individual tags can override the default color.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable tag colors field
	 *
	 * @return void
	 */
	public function render_enable_tag_colors_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_tag_colors'], true, false);
		
		echo '<label class="aps-toggle-label">';
		echo '<input type="checkbox" 
			name="' . esc_attr($this->option_name) . '[enable_tag_colors]" 
			id="enable_tag_colors" 
			value="1" ' . $checked . ' 
			class="aps-toggle"> ';
		echo '<span class="aps-toggle-slider"></span>';
		echo '</label>';
		echo '<p class="description">' . esc_html__('Allow custom background colors for tags.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render tag background color field
	 * Uses WordPress wpColorPicker (same as tag add form)
	 *
	 * @return void
	 */
	public function render_tag_background_color_field(): void {
		$settings = $this->get_settings();
		$color = $settings['tag_background_color'];
		
		echo '<input type="text" 
			name="' . esc_attr($this->option_name) . '[tag_background_color]" 
			id="tag_background_color" 
			value="' . esc_attr($color) . '" 
			class="aps-color-picker regular-text"
			placeholder="#dbeafe"
			pattern="^#[0-9a-fA-F]{6}$"
			maxlength="7"
			' . disabled($settings['enable_tag_colors'], false, false) . '>';
		
		echo '<p class="description">' . 
			esc_html__('Default background color for tag badges. Individual tags can override this when creating/editing.', 'affiliate-product-showcase') . 
			'</p>';
	}
}
