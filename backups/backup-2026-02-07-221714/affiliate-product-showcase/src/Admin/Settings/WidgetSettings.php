<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

use AffiliateProductShowcase\Admin\Settings\AbstractSettingsSection;

/**
 * Widget Settings Section
 *
 * Handles settings for widget functionality including widget availability,
 * display options, and lazy loading configurations.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 * @author Development Team
 */
final class WidgetSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_widgets';
	const SECTION_TITLE = 'Widgets';
	
	/**
	 * @var array
	 */
	private array $widget_layout_options = [
		'grid' => 'Grid',
		'list' => 'List',
		'compact' => 'Compact',
	];
	
	/**
	 * @var array
	 */
	private array $image_size_options = [
		'thumbnail' => 'Thumbnail',
		'medium' => 'Medium',
		'large' => 'Large',
		'full' => 'Full Size',
	];
	
	/**
	 * Get default settings values
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'enable_product_widget' => true,
			'enable_featured_widget' => true,
			'enable_category_widget' => true,
			'enable_sale_widget' => true,
			'widget_default_layout' => 'grid',
			'widget_image_size' => 'thumbnail',
			'widget_lazy_loading' => true,
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
			__('Widgets', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'widgets']
		);
		
		\add_settings_field(
			'enable_product_widget',
			__('Enable Product Widget', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_widget_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_product_widget']
		);
		
		\add_settings_field(
			'enable_featured_widget',
			__('Enable Featured Products Widget', 'affiliate-product-showcase'),
			[$this, 'render_enable_featured_widget_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_featured_widget']
		);
		
		\add_settings_field(
			'enable_category_widget',
			__('Enable Category Widget', 'affiliate-product-showcase'),
			[$this, 'render_enable_category_widget_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_category_widget']
		);
		
		\add_settings_field(
			'enable_sale_widget',
			__('Enable Sale Products Widget', 'affiliate-product-showcase'),
			[$this, 'render_enable_sale_widget_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_sale_widget']
		);
		
		\add_settings_field(
			'widget_default_layout',
			__('Default Widget Layout', 'affiliate-product-showcase'),
			[$this, 'render_widget_default_layout_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'widget_default_layout']
		);
		
		\add_settings_field(
			'widget_image_size',
			__('Widget Image Size', 'affiliate-product-showcase'),
			[$this, 'render_widget_image_size_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'widget_image_size']
		);
		
		\add_settings_field(
			'widget_lazy_loading',
			__('Enable Lazy Loading', 'affiliate-product-showcase'),
			[$this, 'render_widget_lazy_loading_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'widget_lazy_loading']
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
		
		// Widget Availability
		$sanitized['enable_product_widget'] = isset($input['enable_product_widget']) ? (bool) $input['enable_product_widget'] : true;
		$sanitized['enable_featured_widget'] = isset($input['enable_featured_widget']) ? (bool) $input['enable_featured_widget'] : true;
		$sanitized['enable_category_widget'] = isset($input['enable_category_widget']) ? (bool) $input['enable_category_widget'] : true;
		$sanitized['enable_sale_widget'] = isset($input['enable_sale_widget']) ? (bool) $input['enable_sale_widget'] : true;
		
		// Layout Settings
		if (isset($input['widget_default_layout'])) {
			$sanitized['widget_default_layout'] = in_array($input['widget_default_layout'], array_keys($this->widget_layout_options))
				? sanitize_text_field($input['widget_default_layout'])
				: 'grid';
		}
		
		// Image Settings
		if (isset($input['widget_image_size'])) {
			$sanitized['widget_image_size'] = in_array($input['widget_image_size'], array_keys($this->image_size_options))
				? sanitize_text_field($input['widget_image_size'])
				: 'thumbnail';
		}
		
		// Lazy Loading
		$sanitized['widget_lazy_loading'] = isset($input['widget_lazy_loading']) ? (bool) $input['widget_lazy_loading'] : true;
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure widget settings and display options for sidebar widgets.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable product widget field
	 *
	 * @return void
	 */
	public function render_enable_product_widget_field(): void {
		$settings = $this->get_settings();
		$value = $settings['enable_product_widget'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_product_widget]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Enable the general product list widget.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable featured widget field
	 *
	 * @return void
	 */
	public function render_enable_featured_widget_field(): void {
		$settings = $this->get_settings();
		$value = $settings['enable_featured_widget'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_featured_widget]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Enable the featured products widget.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable category widget field
	 *
	 * @return void
	 */
	public function render_enable_category_widget_field(): void {
		$settings = $this->get_settings();
		$value = $settings['enable_category_widget'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_category_widget]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Enable the product category widget.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable sale widget field
	 *
	 * @return void
	 */
	public function render_enable_sale_widget_field(): void {
		$settings = $this->get_settings();
		$value = $settings['enable_sale_widget'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_sale_widget]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Enable the sale products widget.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render widget default layout field
	 *
	 * @return void
	 */
	public function render_widget_default_layout_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[widget_default_layout]">';
		foreach ($this->widget_layout_options as $value => $label) {
			$selected = selected($settings['widget_default_layout'] ?? 'grid', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Default layout for product widgets. Can be overridden per widget instance.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render widget image size field
	 *
	 * @return void
	 */
	public function render_widget_image_size_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[widget_image_size]">';
		foreach ($this->image_size_options as $value => $label) {
			$selected = selected($settings['widget_image_size'] ?? 'thumbnail', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Default image size for product images in widgets.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render widget lazy loading field
	 *
	 * @return void
	 */
	public function render_widget_lazy_loading_field(): void {
		$settings = $this->get_settings();
		$value = $settings['widget_lazy_loading'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[widget_lazy_loading]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Enable lazy loading for widget images to improve page load performance.', 'affiliate-product-showcase') . '</p>';
	}
}