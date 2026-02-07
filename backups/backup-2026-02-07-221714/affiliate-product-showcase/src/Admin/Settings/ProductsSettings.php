<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Products Settings Section (Minimal)
 *
 * Core product functionality settings only.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class ProductsSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_products';
	const SECTION_TITLE = 'Product Settings';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'default_product_status' => 'publish',
			'products_per_page' => 12,
			'enable_click_tracking' => true,
			'auto_generate_slugs' => true,
			'show_product_version' => true,
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
			__('Product Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'products']
		);
		
		// Default status - Controls new product publish state
		\add_settings_field(
			'default_product_status',
			__('Default Product Status', 'affiliate-product-showcase'),
			[$this, 'render_default_product_status_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'default_product_status']
		);
		
		// Products per page - Controls archive pagination
		\add_settings_field(
			'products_per_page',
			__('Products Per Page', 'affiliate-product-showcase'),
			[$this, 'render_products_per_page_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'products_per_page']
		);
		
		// Click tracking - Controls analytics functionality
		\add_settings_field(
			'enable_click_tracking',
			__('Enable Click Tracking', 'affiliate-product-showcase'),
			[$this, 'render_enable_click_tracking_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_click_tracking']
		);
		
		// Auto slugs - Controls URL generation
		\add_settings_field(
			'auto_generate_slugs',
			__('Auto Generate Slugs', 'affiliate-product-showcase'),
			[$this, 'render_auto_generate_slugs_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'auto_generate_slugs']
		);
		
		// Show version - Controls product display
		\add_settings_field(
			'show_product_version',
			__('Show Product Version', 'affiliate-product-showcase'),
			[$this, 'render_show_product_version_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_product_version']
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
			'default_product_status' => in_array($input['default_product_status'] ?? 'publish', ['draft', 'publish', 'pending']) 
				? $input['default_product_status'] 
				: 'publish',
			'products_per_page' => max(6, min(48, intval($input['products_per_page'] ?? 12))),
			'enable_click_tracking' => isset($input['enable_click_tracking']),
			'auto_generate_slugs' => isset($input['auto_generate_slugs']),
			'show_product_version' => isset($input['show_product_version']),
		];
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure core product functionality.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render default product status field
	 *
	 * @return void
	 */
	public function render_default_product_status_field(): void {
		$settings = $this->get_settings();
		$statuses = [
			'draft' => __('Draft', 'affiliate-product-showcase'),
			'publish' => __('Published', 'affiliate-product-showcase'),
			'pending' => __('Pending Review', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[default_product_status]">';
		foreach ($statuses as $value => $label) {
			$selected = selected($settings['default_product_status'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Default status for new products.', 'affiliate-product-showcase') . '</p>';
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
		echo '<p class="description">' . esc_html__('Number of products per page on archive pages.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable click tracking field
	 *
	 * @return void
	 */
	public function render_enable_click_tracking_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_click_tracking'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_click_tracking]" value="1" ' . $checked . '> ';
		echo esc_html__('Track affiliate link clicks for analytics', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render auto generate slugs field
	 *
	 * @return void
	 */
	public function render_auto_generate_slugs_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['auto_generate_slugs'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[auto_generate_slugs]" value="1" ' . $checked . '> ';
		echo esc_html__('Auto-generate URL slugs from product titles', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show product version field
	 *
	 * @return void
	 */
	public function render_show_product_version_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_product_version'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[show_product_version]" value="1" ' . $checked . '> ';
		echo esc_html__('Display version number on product cards', 'affiliate-product-showcase');
		echo '</label>';
	}
}
