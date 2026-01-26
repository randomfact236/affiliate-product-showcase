<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

use AffiliateProductShowcase\Admin\Settings\AbstractSettingsSection;

/**
 * Shortcode Settings Section
 *
 * Handles settings for shortcode functionality including shortcode IDs,
 * caching options, and display configurations.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 * @author Development Team
 */
final class ShortcodeSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_shortcodes';
	const SECTION_TITLE = 'Shortcodes';
	
	/**
	 * @var array
	 */
	private array $cache_duration_options = [
		'60' => '1 Minute',
		'300' => '5 Minutes',
		'900' => '15 Minutes',
		'1800' => '30 Minutes',
		'3600' => '1 Hour',
		'86400' => '1 Day',
		'0' => 'No Cache',
	];
	
	/**
	 * @var array
	 */
	private array $button_style_options = [
		'default' => 'Default (Theme)',
		'primary' => 'Primary',
		'secondary' => 'Secondary',
		'flat' => 'Flat',
	];
	
	/**
	 * Get default settings values
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'product_grid_shortcode_id' => 'affiliate_product_grid',
			'featured_products_shortcode_id' => 'affiliate_featured_products',
			'product_slider_shortcode_id' => 'affiliate_product_slider',
			'enable_shortcode_cache' => true,
			'shortcode_cache_duration' => 300,
			'add_to_cart_button_style' => 'default',
			'enable_quick_view_shortcode' => true,
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
			__('Shortcodes', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'shortcodes']
		);
		
		\add_settings_field(
			'product_grid_shortcode_id',
			__('Product Grid Shortcode ID', 'affiliate-product-showcase'),
			[$this, 'render_product_grid_shortcode_id_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'product_grid_shortcode_id']
		);
		
		\add_settings_field(
			'featured_products_shortcode_id',
			__('Featured Products Shortcode ID', 'affiliate-product-showcase'),
			[$this, 'render_featured_products_shortcode_id_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'featured_products_shortcode_id']
		);
		
		\add_settings_field(
			'product_slider_shortcode_id',
			__('Product Slider Shortcode ID', 'affiliate-product-showcase'),
			[$this, 'render_product_slider_shortcode_id_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'product_slider_shortcode_id']
		);
		
		\add_settings_field(
			'enable_shortcode_cache',
			__('Enable Shortcode Caching', 'affiliate-product-showcase'),
			[$this, 'render_enable_shortcode_cache_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_shortcode_cache']
		);
		
		\add_settings_field(
			'shortcode_cache_duration',
			__('Cache Duration', 'affiliate-product-showcase'),
			[$this, 'render_shortcode_cache_duration_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'shortcode_cache_duration']
		);
		
		\add_settings_field(
			'add_to_cart_button_style',
			__('Add to Cart Button Style', 'affiliate-product-showcase'),
			[$this, 'render_add_to_cart_button_style_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'add_to_cart_button_style']
		);
		
		\add_settings_field(
			'enable_quick_view_shortcode',
			__('Enable Quick View Shortcode', 'affiliate-product-showcase'),
			[$this, 'render_enable_quick_view_shortcode_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_quick_view_shortcode']
		);
	}
	
	/**
	 * Sanitize settings options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$sanitized = [];
		
		// Shortcode IDs
		$sanitized['product_grid_shortcode_id'] = sanitize_text_field($input['product_grid_shortcode_id'] ?? 'affiliate_product_grid');
		$sanitized['featured_products_shortcode_id'] = sanitize_text_field($input['featured_products_shortcode_id'] ?? 'affiliate_featured_products');
		$sanitized['product_slider_shortcode_id'] = sanitize_text_field($input['product_slider_shortcode_id'] ?? 'affiliate_product_slider');
		
		// Cache Settings
		$sanitized['enable_shortcode_cache'] = isset($input['enable_shortcode_cache']) ? (bool) $input['enable_shortcode_cache'] : true;
		
		if (isset($input['shortcode_cache_duration'])) {
			$sanitized['shortcode_cache_duration'] = in_array($input['shortcode_cache_duration'], array_keys($this->cache_duration_options))
				? intval($input['shortcode_cache_duration'])
				: 300;
		}
		
		// Button Style
		if (isset($input['add_to_cart_button_style'])) {
			$sanitized['add_to_cart_button_style'] = in_array($input['add_to_cart_button_style'], array_keys($this->button_style_options))
				? sanitize_text_field($input['add_to_cart_button_style'])
				: 'default';
		}
		
		// Quick View
		$sanitized['enable_quick_view_shortcode'] = isset($input['enable_quick_view_shortcode']) ? (bool) $input['enable_quick_view_shortcode'] : true;
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure shortcode IDs and caching options for displaying products on your site.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render product grid shortcode ID field
	 *
	 * @return void
	 */
	public function render_product_grid_shortcode_id_field(): void {
		$settings = $this->get_settings();
		echo '<input type="text" name="' . esc_attr($this->option_name) . '[product_grid_shortcode_id]" value="' . esc_attr($settings['product_grid_shortcode_id'] ?? 'affiliate_product_grid') . '" class="regular-text">';
		echo '<p class="description">' . esc_html__('Default: [affiliate_product_grid]. Change this to avoid conflicts with other plugins.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render featured products shortcode ID field
	 *
	 * @return void
	 */
	public function render_featured_products_shortcode_id_field(): void {
		$settings = $this->get_settings();
		echo '<input type="text" name="' . esc_attr($this->option_name) . '[featured_products_shortcode_id]" value="' . esc_attr($settings['featured_products_shortcode_id'] ?? 'affiliate_featured_products') . '" class="regular-text">';
		echo '<p class="description">' . esc_html__('Default: [affiliate_featured_products]. Display featured products grid.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render product slider shortcode ID field
	 *
	 * @return void
	 */
	public function render_product_slider_shortcode_id_field(): void {
		$settings = $this->get_settings();
		echo '<input type="text" name="' . esc_attr($this->option_name) . '[product_slider_shortcode_id]" value="' . esc_attr($settings['product_slider_shortcode_id'] ?? 'affiliate_product_slider') . '" class="regular-text">';
		echo '<p class="description">' . esc_html__('Default: [affiliate_product_slider]. Display products in a carousel/slider.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable shortcode cache field
	 *
	 * @return void
	 */
	public function render_enable_shortcode_cache_field(): void {
		$settings = $this->get_settings();
		$value = $settings['enable_shortcode_cache'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_shortcode_cache]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Cache shortcode output to improve performance. Disable for dynamic content.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render shortcode cache duration field
	 *
	 * @return void
	 */
	public function render_shortcode_cache_duration_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[shortcode_cache_duration]">';
		foreach ($this->cache_duration_options as $value => $label) {
			$selected = selected($settings['shortcode_cache_duration'] ?? 300, $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('How long to cache shortcode output. Longer durations improve performance but may show outdated content.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render add to cart button style field
	 *
	 * @return void
	 */
	public function render_add_to_cart_button_style_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[add_to_cart_button_style]">';
		foreach ($this->button_style_options as $value => $label) {
			$selected = selected($settings['add_to_cart_button_style'] ?? 'default', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Style for "Add to Cart" buttons in shortcodes.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable quick view shortcode field
	 *
	 * @return void
	 */
	public function render_enable_quick_view_shortcode_field(): void {
		$settings = $this->get_settings();
		$value = $settings['enable_quick_view_shortcode'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_quick_view_shortcode]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Enable quick view functionality for products in shortcodes.', 'affiliate-product-showcase') . '</p>';
	}
}