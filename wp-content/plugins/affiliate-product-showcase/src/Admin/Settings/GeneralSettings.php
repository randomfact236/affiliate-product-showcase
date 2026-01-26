<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * General Settings Section
 *
 * Handles general plugin settings including version, currency, and date/time formats.
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
			'plugin_version' => '1.0.0',
			'default_currency' => 'USD',
			'date_format' => get_option('date_format', 'F j, Y'),
			'time_format' => get_option('time_format', 'g:i a'),
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
		
		\add_settings_field(
			'plugin_version',
			__('Plugin Version', 'affiliate-product-showcase'),
			[$this, 'render_plugin_version_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'plugin_version']
		);
		
		\add_settings_field(
			'default_currency',
			__('Default Currency', 'affiliate-product-showcase'),
			[$this, 'render_currency_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'default_currency']
		);
		
		\add_settings_field(
			'date_format',
			__('Date Format', 'affiliate-product-showcase'),
			[$this, 'render_date_format_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'date_format']
		);
		
		\add_settings_field(
			'time_format',
			__('Time Format', 'affiliate-product-showcase'),
			[$this, 'render_time_format_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'time_format']
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
		
		$sanitized['plugin_version'] = $this->get_default('plugin_version'); // Read-only
		$sanitized['default_currency'] = sanitize_text_field($input['default_currency'] ?? 'USD');
		$sanitized['date_format'] = sanitize_text_field($input['date_format'] ?? $this->get_default('date_format'));
		$sanitized['time_format'] = sanitize_text_field($input['time_format'] ?? $this->get_default('time_format'));
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure general plugin settings and preferences.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render plugin version field
	 *
	 * @return void
	 */
	public function render_plugin_version_field(): void {
		$settings = $this->get_settings();
		echo '<input type="text" value="' . esc_attr($settings['plugin_version']) . '" readonly class="regular-text">';
		echo '<p class="description">' . esc_html__('Current plugin version (read-only).', 'affiliate-product-showcase') . '</p>';
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
			'CHF' => __('Swiss Franc (CHF)', 'affiliate-product-showcase'),
			'CNY' => __('Chinese Yuan (¥)', 'affiliate-product-showcase'),
			'INR' => __('Indian Rupee (₹)', 'affiliate-product-showcase'),
			'NPR' => __('Nepali Rupee (₨)', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[default_currency]">';
		foreach ($currencies as $code => $label) {
			$selected = selected($settings['default_currency'], $code, false);
			echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
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
	}
	
	/**
	 * Render time format field
	 *
	 * @return void
	 */
	public function render_time_format_field(): void {
		$settings = $this->get_settings();
		$formats = [
			'g:i a' => date('g:i a'),
			'g:i A' => date('g:i A'),
			'H:i' => date('H:i'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[time_format]">';
		foreach ($formats as $format => $example) {
			$selected = selected($settings['time_format'], $format, false);
			echo '<option value="' . esc_attr($format) . '" ' . $selected . '>';
			echo esc_html($format) . ' <span class="example">' . esc_html('(' . $example . ')') . '</span>';
			echo '</option>';
		}
		echo '</select>';
	}
}