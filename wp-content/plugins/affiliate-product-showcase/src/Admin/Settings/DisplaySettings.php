<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Display Settings Section
 *
 * Handles display settings including layout, colors, and frontend appearance.
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
			'products_per_page' => 12,
			'enable_lazy_loading' => true,
			'enable_product_quick_view' => false,
			'primary_color' => '#007bff',
			'secondary_color' => '#6c757d',
			'accent_color' => '#28a745',
			'enable_dark_mode' => false,
			'enable_responsive_design' => true,
			'product_image_ratio' => '16:9',
			'enable_product_hover_effects' => true,
			'hover_effect_type' => 'zoom',
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
		
		\add_settings_field(
			'product_layout',
			__('Product Layout', 'affiliate-product-showcase'),
			[$this, 'render_product_layout_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'product_layout']
		);
		
		\add_settings_field(
			'products_per_page',
			__('Products Per Page', 'affiliate-product-showcase'),
			[$this, 'render_products_per_page_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'products_per_page']
		);
		
		\add_settings_field(
			'enable_lazy_loading',
			__('Enable Lazy Loading', 'affiliate-product-showcase'),
			[$this, 'render_enable_lazy_loading_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_lazy_loading']
		);
		
		\add_settings_field(
			'enable_product_quick_view',
			__('Enable Product Quick View', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_quick_view_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_product_quick_view']
		);
		
		\add_settings_field(
			'primary_color',
			__('Primary Color', 'affiliate-product-showcase'),
			[$this, 'render_primary_color_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'primary_color']
		);
		
		\add_settings_field(
			'secondary_color',
			__('Secondary Color', 'affiliate-product-showcase'),
			[$this, 'render_secondary_color_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'secondary_color']
		);
		
		\add_settings_field(
			'accent_color',
			__('Accent Color', 'affiliate-product-showcase'),
			[$this, 'render_accent_color_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'accent_color']
		);
		
		\add_settings_field(
			'enable_dark_mode',
			__('Enable Dark Mode', 'affiliate-product-showcase'),
			[$this, 'render_enable_dark_mode_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_dark_mode']
		);
		
		\add_settings_field(
			'enable_responsive_design',
			__('Enable Responsive Design', 'affiliate-product-showcase'),
			[$this, 'render_enable_responsive_design_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_responsive_design']
		);
		
		\add_settings_field(
			'product_image_ratio',
			__('Product Image Ratio', 'affiliate-product-showcase'),
			[$this, 'render_product_image_ratio_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'product_image_ratio']
		);
		
		\add_settings_field(
			'enable_product_hover_effects',
			__('Enable Product Hover Effects', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_hover_effects_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_product_hover_effects']
		);
		
		\add_settings_field(
			'hover_effect_type',
			__('Hover Effect Type', 'affiliate-product-showcase'),
			[$this, 'render_hover_effect_type_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'hover_effect_type']
		);
	}
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$sanitized = [];
		
		$sanitized['product_layout'] = in_array($input['product_layout'] ?? 'grid', ['grid', 'list', 'card']) ? $input['product_layout'] : 'grid';
		$sanitized['products_per_page'] = intval($input['products_per_page'] ?? 12);
		$sanitized['products_per_page'] = max(6, min(48, $sanitized['products_per_page']));
		$sanitized['enable_lazy_loading'] = isset($input['enable_lazy_loading']);
		$sanitized['enable_product_quick_view'] = isset($input['enable_product_quick_view']);
		$sanitized['primary_color'] = sanitize_hex_color($input['primary_color'] ?? $this->get_default('primary_color'));
		$sanitized['secondary_color'] = sanitize_hex_color($input['secondary_color'] ?? $this->get_default('secondary_color'));
		$sanitized['accent_color'] = sanitize_hex_color($input['accent_color'] ?? $this->get_default('accent_color'));
		$sanitized['enable_dark_mode'] = isset($input['enable_dark_mode']);
		$sanitized['enable_responsive_design'] = isset($input['enable_responsive_design']);
		$sanitized['product_image_ratio'] = in_array($input['product_image_ratio'] ?? '16:9', ['1:1', '4:3', '16:9', '21:9']) ? $input['product_image_ratio'] : '16:9';
		$sanitized['enable_product_hover_effects'] = isset($input['enable_product_hover_effects']);
		$sanitized['hover_effect_type'] = in_array($input['hover_effect_type'] ?? 'zoom', ['zoom', 'lift', 'fade', 'shadow']) ? $input['hover_effect_type'] : 'zoom';
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure display settings, layout, colors, and frontend appearance.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render product layout field
	 *
	 * @return void
	 */
	public function render_product_layout_field(): void {
		$settings = $this->get_settings();
		$layouts = [
			'grid' => __('Grid', 'affiliate-product-showcase'),
			'list' => __('List', 'affiliate-product-showcase'),
			'card' => __('Card', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[product_layout]">';
		foreach ($layouts as $value => $label) {
			$selected = selected($settings['product_layout'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render products per page field
	 *
	 * @return void
	 */
	public function render_products_per_page_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[products_per_page]">';
		foreach ([6, 12, 18, 24, 36, 48] as $value) {
			$selected = selected($settings['products_per_page'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable lazy loading field
	 *
	 * @return void
	 */
	public function render_enable_lazy_loading_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_lazy_loading'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_lazy_loading]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable lazy loading for images', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable product quick view field
	 *
	 * @return void
	 */
	public function render_enable_product_quick_view_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_product_quick_view'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_product_quick_view]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable product quick view modal', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render primary color field
	 *
	 * @return void
	 */
	public function render_primary_color_field(): void {
		$settings = $this->get_settings();
		echo '<input type="color" name="' . esc_attr($this->option_name) . '[primary_color]" value="' . esc_attr($settings['primary_color']) . '" class="color-picker">';
		echo '<p class="description">' . esc_html__('Primary color for buttons, links, and accents.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render secondary color field
	 *
	 * @return void
	 */
	public function render_secondary_color_field(): void {
		$settings = $this->get_settings();
		echo '<input type="color" name="' . esc_attr($this->option_name) . '[secondary_color]" value="' . esc_attr($settings['secondary_color']) . '" class="color-picker">';
		echo '<p class="description">' . esc_html__('Secondary color for backgrounds and secondary elements.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render accent color field
	 *
	 * @return void
	 */
	public function render_accent_color_field(): void {
		$settings = $this->get_settings();
		echo '<input type="color" name="' . esc_attr($this->option_name) . '[accent_color]" value="' . esc_attr($settings['accent_color']) . '" class="color-picker">';
		echo '<p class="description">' . esc_html__('Accent color for highlights and call-to-actions.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable dark mode field
	 *
	 * @return void
	 */
	public function render_enable_dark_mode_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_dark_mode'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_dark_mode]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable dark mode support', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable responsive design field
	 *
	 * @return void
	 */
	public function render_enable_responsive_design_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_responsive_design'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_responsive_design]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable responsive design (mobile-friendly)', 'affiliate-product-showcase');
		echo '</label>';
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
			'21:9' => __('21:9 (Ultrawide)', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[product_image_ratio]">';
		foreach ($ratios as $value => $label) {
			$selected = selected($settings['product_image_ratio'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable product hover effects field
	 *
	 * @return void
	 */
	public function render_enable_product_hover_effects_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_product_hover_effects'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_product_hover_effects]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable hover effects on products', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render hover effect type field
	 *
	 * @return void
	 */
	public function render_hover_effect_type_field(): void {
		$settings = $this->get_settings();
		$effects = [
			'zoom' => __('Zoom', 'affiliate-product-showcase'),
			'lift' => __('Lift', 'affiliate-product-showcase'),
			'fade' => __('Fade', 'affiliate-product-showcase'),
			'shadow' => __('Shadow', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[hover_effect_type]">';
		foreach ($effects as $value => $label) {
			$selected = selected($settings['hover_effect_type'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
}