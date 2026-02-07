<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * General Settings Section (Minimal)
 *
 * Core plugin settings only - currency and localization.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class GeneralSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_general';
	const SECTION_TITLE = 'General Settings';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'default_currency' => 'USD',
			'date_format' => get_option('date_format', 'F j, Y'),
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
			__('General Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'general']
		);
		
		// Currency - Core setting used in pricing display
		\add_settings_field(
			'default_currency',
			__('Default Currency', 'affiliate-product-showcase'),
			[$this, 'render_currency_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'default_currency']
		);
		
		// Date format - Used in product display
		\add_settings_field(
			'date_format',
			__('Date Format', 'affiliate-product-showcase'),
			[$this, 'render_date_format_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'date_format']
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
			'default_currency' => sanitize_text_field($input['default_currency'] ?? 'USD'),
			'date_format' => sanitize_text_field($input['date_format'] ?? $this->get_default('date_format')),
		];
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure general plugin settings.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render currency field
	 *
	 * @return void
	 */
	public function render_currency_field(): void {
		$settings = $this->get_settings();
		$currencies = [
			'USD' => __('US Dollar ($)', 'affiliate-product-showcase'),
			'EUR' => __('Euro (€)', 'affiliate-product-showcase'),
			'GBP' => __('British Pound (£)', 'affiliate-product-showcase'),
			'JPY' => __('Japanese Yen (¥)', 'affiliate-product-showcase'),
			'AUD' => __('Australian Dollar ($)', 'affiliate-product-showcase'),
			'CAD' => __('Canadian Dollar ($)', 'affiliate-product-showcase'),
			'INR' => __('Indian Rupee (₹)', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[default_currency]">';
		foreach ($currencies as $code => $label) {
			$selected = selected($settings['default_currency'], $code, false);
			echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Used for product pricing display.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render date format field
	 *
	 * @return void
	 */
	public function render_date_format_field(): void {
		$settings = $this->get_settings();
		$formats = [
			'F j, Y' => date('F j, Y'),
			'Y-m-d' => date('Y-m-d'),
			'm/d/Y' => date('m/d/Y'),
			'd/m/Y' => date('d/m/Y'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[date_format]">';
		foreach ($formats as $format => $example) {
			$selected = selected($settings['date_format'], $format, false);
			echo '<option value="' . esc_attr($format) . '" ' . $selected . '>';
			echo esc_html($format) . ' <span class="example">' . esc_html('(' . $example . ')') . '</span>';
			echo '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Used for product release dates.', 'affiliate-product-showcase') . '</p>';
	}
}
