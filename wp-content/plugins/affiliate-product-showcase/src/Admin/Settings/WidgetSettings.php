<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

use AffiliateProductShowcase\Admin\Settings\AbstractSettingsSection;

/**
 * Widget Settings Section
 *
 * Handles settings for widget configuration including
 * widget availability, display limits, and lazy loading.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 * @author Development Team
 */
final class WidgetSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_widgets';
	const SECTION_TITLE = 'Widgets';
	const SECTION_DESCRIPTION = 'Configure widget settings and display options.';
	
	/**
	 * @var array
	 */
	private array $limit_options = [
		3 => '3 items',
		6 => '6 items',
		9 => '9 items',
		12 => '12 items',
	];
	
	/**
	 * Get default settings values
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'enable_product_slider_widget' => true,
			'enable_category_widget' => true,
			'enable_tag_cloud_widget' => true,
			'enable_featured_products_widget' => true,
			'enable_filter_widget' => true,
			'widget_default_limit' => 6,
			'enable_widget_lazy_loading' => true,
		];
	}
	
	/**
	 * Register settings section and fields
	 *
	 * @return void
	 */
	public function register_section_and_fields(): void {
		// Register section
		\add_settings_section(
			self::SECTION_ID,
			self::SECTION_TITLE,
			[$this, 'render_section_description'],
			'affiliate-product-showcase'
		);
		
		// Widget Availability
		$this->add_field(
			'enable_product_slider_widget',
			'Product Slider Widget',
			'Enable carousel/slider widget for displaying products.',
			'checkbox'
		);
		
		$this->add_field(
			'enable_category_widget',
			'Category Widget',
			'Enable widget for displaying product categories with hierarchy support.',
			'checkbox'
		);
		
		$this->add_field(
			'enable_tag_cloud_widget',
			'Tag Cloud Widget',
			'Enable tag cloud widget for displaying popular product tags.',
			'checkbox'
		);
		
		$this->add_field(
			'enable_featured_products_widget',
			'Featured Products Widget',
			'Enable widget for displaying featured/featured products.',
			'checkbox'
		);
		
		$this->add_field(
			'enable_filter_widget',
			'Filter Widget',
			'Enable sidebar filter widget with category, tag, and price filters.',
			'checkbox'
		);
		
		// Display Settings
		$this->add_field(
			'widget_default_limit',
			'Default Item Limit',
			'Default number of items to display in widgets. Can be overridden per widget.',
			'select',
			$this->limit_options
		);
		
		$this->add_field(
			'enable_widget_lazy_loading',
			'Widget Lazy Loading',
			'Lazy load widget content to improve page load performance.',
			'checkbox'
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
		$sanitized['enable_product_slider_widget'] = isset($input['enable_product_slider_widget'])
			? (bool) $input['enable_product_slider_widget']
			: true;
		
		$sanitized['enable_category_widget'] = isset($input['enable_category_widget'])
			? (bool) $input['enable_category_widget']
			: true;
		
		$sanitized['enable_tag_cloud_widget'] = isset($input['enable_tag_cloud_widget'])
			? (bool) $input['enable_tag_cloud_widget']
			: true;
		
		$sanitized['enable_featured_products_widget'] = isset($input['enable_featured_products_widget'])
			? (bool) $input['enable_featured_products_widget']
			: true;
		
		$sanitized['enable_filter_widget'] = isset($input['enable_filter_widget'])
			? (bool) $input['enable_filter_widget']
			: true;
		
		// Display Settings
		if (isset($input['widget_default_limit'])) {
			$limit = intval($input['widget_default_limit']);
			$sanitized['widget_default_limit'] = in_array($limit, array_keys($this->limit_options))
				? $limit
				: 6;
		}
		
		$sanitized['enable_widget_lazy_loading'] = isset($input['enable_widget_lazy_loading'])
			? (bool) $input['enable_widget_lazy_loading']
			: true;
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure which widgets are available and their default display settings. Widgets can be added to sidebars and widget areas.', 'affiliate-product-showcase') . '</p>';
	}
}