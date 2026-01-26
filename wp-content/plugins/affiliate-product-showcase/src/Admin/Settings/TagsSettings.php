<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Tags Settings Section
 *
 * Handles tag settings including display styles, colors, icons, and filtering options.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class TagsSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_tags';
	const SECTION_TITLE = 'Tag Settings';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'tag_display_style' => 'pills',
			'enable_tag_colors' => true,
			'enable_tag_icons' => true,
			'tag_cloud_limit' => 20,
			'tag_cloud_orderby' => 'count',
			'tag_cloud_order' => 'DESC',
			'show_tag_description' => false,
			'show_tag_count' => true,
			'enable_tag_filtering' => true,
			'tag_filter_display_mode' => 'checkboxes',
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
		
		\add_settings_field(
			'tag_display_style',
			__('Tag Display Style', 'affiliate-product-showcase'),
			[$this, 'render_tag_display_style_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'tag_display_style']
		);
		
		\add_settings_field(
			'enable_tag_colors',
			__('Enable Tag Colors', 'affiliate-product-showcase'),
			[$this, 'render_enable_tag_colors_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_tag_colors']
		);
		
		\add_settings_field(
			'enable_tag_icons',
			__('Enable Tag Icons', 'affiliate-product-showcase'),
			[$this, 'render_enable_tag_icons_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_tag_icons']
		);
		
		\add_settings_field(
			'tag_cloud_limit',
			__('Tag Cloud Limit', 'affiliate-product-showcase'),
			[$this, 'render_tag_cloud_limit_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'tag_cloud_limit']
		);
		
		\add_settings_field(
			'tag_cloud_orderby',
			__('Tag Cloud Order By', 'affiliate-product-showcase'),
			[$this, 'render_tag_cloud_orderby_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'tag_cloud_orderby']
		);
		
		\add_settings_field(
			'tag_cloud_order',
			__('Tag Cloud Order', 'affiliate-product-showcase'),
			[$this, 'render_tag_cloud_order_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'tag_cloud_order']
		);
		
		\add_settings_field(
			'show_tag_description',
			__('Show Tag Description', 'affiliate-product-showcase'),
			[$this, 'render_show_tag_description_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_tag_description']
		);
		
		\add_settings_field(
			'show_tag_count',
			__('Show Tag Count', 'affiliate-product-showcase'),
			[$this, 'render_show_tag_count_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_tag_count']
		);
		
		\add_settings_field(
			'enable_tag_filtering',
			__('Enable Tag Filtering', 'affiliate-product-showcase'),
			[$this, 'render_enable_tag_filtering_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_tag_filtering']
		);
		
		\add_settings_field(
			'tag_filter_display_mode',
			__('Tag Filter Display Mode', 'affiliate-product-showcase'),
			[$this, 'render_tag_filter_display_mode_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'tag_filter_display_mode']
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
		
		$sanitized['tag_display_style'] = in_array($input['tag_display_style'] ?? 'pills', ['pills', 'badges', 'links', 'dropdown']) ? $input['tag_display_style'] : 'pills';
		$sanitized['enable_tag_colors'] = isset($input['enable_tag_colors']);
		$sanitized['enable_tag_icons'] = isset($input['enable_tag_icons']);
		$sanitized['tag_cloud_limit'] = intval($input['tag_cloud_limit'] ?? 20);
		$sanitized['tag_cloud_limit'] = max(10, min(50, $sanitized['tag_cloud_limit']));
		$sanitized['tag_cloud_orderby'] = in_array($input['tag_cloud_orderby'] ?? 'count', ['name', 'count', 'slug', 'random']) ? $input['tag_cloud_orderby'] : 'count';
		$sanitized['tag_cloud_order'] = in_array($input['tag_cloud_order'] ?? 'DESC', ['ASC', 'DESC']) ? $input['tag_cloud_order'] : 'DESC';
		$sanitized['show_tag_description'] = isset($input['show_tag_description']);
		$sanitized['show_tag_count'] = isset($input['show_tag_count']);
		$sanitized['enable_tag_filtering'] = isset($input['enable_tag_filtering']);
		$sanitized['tag_filter_display_mode'] = in_array($input['tag_filter_display_mode'] ?? 'checkboxes', ['checkboxes', 'links', 'dropdown']) ? $input['tag_filter_display_mode'] : 'checkboxes';
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure tag display styles, colors, icons, and filtering options.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render tag display style field
	 *
	 * @return void
	 */
	public function render_tag_display_style_field(): void {
		$settings = $this->get_settings();
		$styles = [
			'pills' => __('Pills', 'affiliate-product-showcase'),
			'badges' => __('Badges', 'affiliate-product-showcase'),
			'links' => __('Links', 'affiliate-product-showcase'),
			'dropdown' => __('Dropdown', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[tag_display_style]">';
		foreach ($styles as $value => $label) {
			$selected = selected($settings['tag_display_style'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable tag colors field
	 *
	 * @return void
	 */
	public function render_enable_tag_colors_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_tag_colors'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_tag_colors]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable custom tag colors', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable tag icons field
	 *
	 * @return void
	 */
	public function render_enable_tag_icons_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_tag_icons'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_tag_icons]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable tag icons (emoji/SVG)', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render tag cloud limit field
	 *
	 * @return void
	 */
	public function render_tag_cloud_limit_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[tag_cloud_limit]">';
		foreach ([10, 20, 30, 40, 50] as $value) {
			$selected = selected($settings['tag_cloud_limit'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Number of tags in tag cloud.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render tag cloud orderby field
	 *
	 * @return void
	 */
	public function render_tag_cloud_orderby_field(): void {
		$settings = $this->get_settings();
		$options = [
			'name' => __('Name', 'affiliate-product-showcase'),
			'count' => __('Count', 'affiliate-product-showcase'),
			'slug' => __('Slug', 'affiliate-product-showcase'),
			'random' => __('Random', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[tag_cloud_orderby]">';
		foreach ($options as $value => $label) {
			$selected = selected($settings['tag_cloud_orderby'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render tag cloud order field
	 *
	 * @return void
	 */
	public function render_tag_cloud_order_field(): void {
		$settings = $this->get_settings();
		$options = [
			'ASC' => __('Ascending', 'affiliate-product-showcase'),
			'DESC' => __('Descending', 'affiliate-product-showcase'),
		];
		
		foreach ($options as $value => $label) {
			$checked = checked($settings['tag_cloud_order'], $value, false);
			echo '<label>';
			echo '<input type="radio" name="' . esc_attr($this->option_name) . '[tag_cloud_order]" value="' . esc_attr($value) . '" ' . $checked . '> ';
			echo esc_html($label);
			echo '</label><br>';
		}
	}
	
	/**
	 * Render show tag description field
	 *
	 * @return void
	 */
	public function render_show_tag_description_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_tag_description'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[show_tag_description]" value="1" ' . $checked . '> ';
		echo esc_html__('Show tag description', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show tag count field
	 *
	 * @return void
	 */
	public function render_show_tag_count_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_tag_count'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[show_tag_count]" value="1" ' . $checked . '> ';
		echo esc_html__('Show product count per tag', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable tag filtering field
	 *
	 * @return void
	 */
	public function render_enable_tag_filtering_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_tag_filtering'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_tag_filtering]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable tag filtering on product pages', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render tag filter display mode field
	 *
	 * @return void
	 */
	public function render_tag_filter_display_mode_field(): void {
		$settings = $this->get_settings();
		$modes = [
			'checkboxes' => __('Checkboxes', 'affiliate-product-showcase'),
			'links' => __('Links', 'affiliate-product-showcase'),
			'dropdown' => __('Dropdown', 'affiliate-product-showcase'),
		];
		
		foreach ($modes as $value => $label) {
			$checked = checked($settings['tag_filter_display_mode'], $value, false);
			echo '<label>';
			echo '<input type="radio" name="' . esc_attr($this->option_name) . '[tag_filter_display_mode]" value="' . esc_attr($value) . '" ' . $checked . '> ';
			echo esc_html($label);
			echo '</label><br>';
		}
	}
}