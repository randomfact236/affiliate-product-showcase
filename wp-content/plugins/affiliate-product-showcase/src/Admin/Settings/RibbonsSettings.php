<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Ribbons Settings Section
 *
	 * Handles ribbon settings including position, animations, and limits.
	 * Note: Ribbon colors are controlled per-ribbon in the Ribbon management tab.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class RibbonsSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_ribbons';
	const SECTION_TITLE = 'Ribbon Settings';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'enable_ribbons' => true,
			'ribbon_position' => 'top-right',
			'enable_ribbon_animations' => true,
			'ribbon_animation_type' => 'fade-in',
			'ribbon_size' => 'medium',
			'enable_priority_system' => true,
			'max_ribbons_per_product' => 3,
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
			__('Ribbon Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'ribbons']
		);
		
		\add_settings_field(
			'enable_ribbons',
			__('Enable Ribbons', 'affiliate-product-showcase'),
			[$this, 'render_enable_ribbons_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_ribbons']
		);
		
		\add_settings_field(
			'ribbon_position',
			__('Ribbon Position', 'affiliate-product-showcase'),
			[$this, 'render_ribbon_position_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'ribbon_position']
		);
		
		\add_settings_field(
			'enable_ribbon_animations',
			__('Enable Ribbon Animations', 'affiliate-product-showcase'),
			[$this, 'render_enable_ribbon_animations_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_ribbon_animations']
		);
		
		\add_settings_field(
			'ribbon_animation_type',
			__('Ribbon Animation Type', 'affiliate-product-showcase'),
			[$this, 'render_ribbon_animation_type_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'ribbon_animation_type']
		);
		
		\add_settings_field(
			'ribbon_size',
			__('Ribbon Size', 'affiliate-product-showcase'),
			[$this, 'render_ribbon_size_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'ribbon_size']
		);
		
		\add_settings_field(
			'enable_priority_system',
			__('Enable Priority System', 'affiliate-product-showcase'),
			[$this, 'render_enable_priority_system_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_priority_system']
		);
		
		\add_settings_field(
			'max_ribbons_per_product',
			__('Max Ribbons Per Product', 'affiliate-product-showcase'),
			[$this, 'render_max_ribbons_per_product_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'max_ribbons_per_product']
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
		
		$sanitized['enable_ribbons'] = isset($input['enable_ribbons']);
		$sanitized['ribbon_position'] = in_array($input['ribbon_position'] ?? 'top-right', ['top-left', 'top-right', 'bottom-left', 'bottom-right']) ? $input['ribbon_position'] : 'top-right';
		$sanitized['enable_ribbon_animations'] = isset($input['enable_ribbon_animations']);
		$sanitized['ribbon_animation_type'] = in_array($input['ribbon_animation_type'] ?? 'fade-in', ['fade-in', 'slide-in', 'bounce', 'none']) ? $input['ribbon_animation_type'] : 'fade-in';
		$sanitized['ribbon_size'] = in_array($input['ribbon_size'] ?? 'medium', ['small', 'medium', 'large']) ? $input['ribbon_size'] : 'medium';
		$sanitized['enable_priority_system'] = isset($input['enable_priority_system']);
		$sanitized['max_ribbons_per_product'] = intval($input['max_ribbons_per_product'] ?? 3);
		$sanitized['max_ribbons_per_product'] = max(1, min(5, $sanitized['max_ribbons_per_product']));
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure ribbon position, animations, and display limits.', 'affiliate-product-showcase') . '</p>';
		echo '<p class="description">' . esc_html__('Note: Ribbon colors are controlled per-ribbon in the Ribbon management tab.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable ribbons field
	 *
	 * @return void
	 */
	public function render_enable_ribbons_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_ribbons'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_ribbons]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable product ribbons', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render ribbon position field
	 *
	 * @return void
	 */
	public function render_ribbon_position_field(): void {
		$settings = $this->get_settings();
		$positions = [
			'top-left' => __('Top Left', 'affiliate-product-showcase'),
			'top-right' => __('Top Right', 'affiliate-product-showcase'),
			'bottom-left' => __('Bottom Left', 'affiliate-product-showcase'),
			'bottom-right' => __('Bottom Right', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[ribbon_position]">';
		foreach ($positions as $value => $label) {
			$selected = selected($settings['ribbon_position'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable ribbon animations field
	 *
	 * @return void
	 */
	public function render_enable_ribbon_animations_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_ribbon_animations'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_ribbon_animations]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable ribbon animations', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render ribbon animation type field
	 *
	 * @return void
	 */
	public function render_ribbon_animation_type_field(): void {
		$settings = $this->get_settings();
		$animations = [
			'fade-in' => __('Fade In', 'affiliate-product-showcase'),
			'slide-in' => __('Slide In', 'affiliate-product-showcase'),
			'bounce' => __('Bounce', 'affiliate-product-showcase'),
			'none' => __('None', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[ribbon_animation_type]">';
		foreach ($animations as $value => $label) {
			$selected = selected($settings['ribbon_animation_type'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render ribbon size field
	 *
	 * @return void
	 */
	public function render_ribbon_size_field(): void {
		$settings = $this->get_settings();
		$sizes = [
			'small' => __('Small', 'affiliate-product-showcase'),
			'medium' => __('Medium', 'affiliate-product-showcase'),
			'large' => __('Large', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[ribbon_size]">';
		foreach ($sizes as $value => $label) {
			$selected = selected($settings['ribbon_size'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable priority system field
	 *
	 * @return void
	 */
	public function render_enable_priority_system_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_priority_system'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_priority_system]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable ribbon priority system', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('When enabled, higher priority ribbons are shown first.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render max ribbons per product field
	 *
	 * @return void
	 */
	public function render_max_ribbons_per_product_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[max_ribbons_per_product]">';
		foreach ([1, 2, 3, 4, 5] as $value) {
			$selected = selected($settings['max_ribbons_per_product'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Maximum number of ribbons to show per product.', 'affiliate-product-showcase') . '</p>';
	}
}