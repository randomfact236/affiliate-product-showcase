<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Products Settings Section
 *
 * Handles product-specific settings including slugs, tracking, and display options.
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
			'auto_generate_slugs' => true,
			'enable_click_tracking' => true,
			'enable_conversion_tracking' => true,
			'default_product_status' => 'publish',
			'enable_product_sharing' => false,
			'sharing_platforms' => ['facebook', 'twitter', 'linkedin'],
			'show_product_version' => true,
			'show_platform_requirements' => true,
			'enable_product_tabs' => true,
			'product_tabs_order' => 'description,specs,faq,requirements',
			'enable_product_ratings' => false,
			'enable_product_reviews' => false,
			'enable_wishlist' => false,
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
		
		\add_settings_field(
			'auto_generate_slugs',
			__('Auto Generate Slugs', 'affiliate-product-showcase'),
			[$this, 'render_auto_generate_slugs_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'auto_generate_slugs']
		);
		
		\add_settings_field(
			'enable_click_tracking',
			__('Enable Click Tracking', 'affiliate-product-showcase'),
			[$this, 'render_enable_click_tracking_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_click_tracking']
		);
		
		\add_settings_field(
			'enable_conversion_tracking',
			__('Enable Conversion Tracking', 'affiliate-product-showcase'),
			[$this, 'render_enable_conversion_tracking_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_conversion_tracking']
		);
		
		\add_settings_field(
			'default_product_status',
			__('Default Product Status', 'affiliate-product-showcase'),
			[$this, 'render_default_product_status_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'default_product_status']
		);
		
		\add_settings_field(
			'enable_product_sharing',
			__('Enable Product Sharing', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_sharing_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_product_sharing']
		);
		
		\add_settings_field(
			'sharing_platforms',
			__('Sharing Platforms', 'affiliate-product-showcase'),
			[$this, 'render_sharing_platforms_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'sharing_platforms']
		);
		
		\add_settings_field(
			'show_product_version',
			__('Show Product Version', 'affiliate-product-showcase'),
			[$this, 'render_show_product_version_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_product_version']
		);
		
		\add_settings_field(
			'show_platform_requirements',
			__('Show Platform Requirements', 'affiliate-product-showcase'),
			[$this, 'render_show_platform_requirements_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_platform_requirements']
		);
		
		\add_settings_field(
			'enable_product_tabs',
			__('Enable Product Tabs', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_tabs_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_product_tabs']
		);
		
		\add_settings_field(
			'product_tabs_order',
			__('Product Tabs Order', 'affiliate-product-showcase'),
			[$this, 'render_product_tabs_order_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'product_tabs_order']
		);
		
		\add_settings_field(
			'enable_product_ratings',
			__('Enable Product Ratings', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_ratings_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_product_ratings']
		);
		
		\add_settings_field(
			'enable_product_reviews',
			__('Enable Product Reviews', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_reviews_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_product_reviews']
		);
		
		\add_settings_field(
			'enable_wishlist',
			__('Enable Wishlist', 'affiliate-product-showcase'),
			[$this, 'render_enable_wishlist_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_wishlist']
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
		
		$sanitized['auto_generate_slugs'] = isset($input['auto_generate_slugs']);
		$sanitized['enable_click_tracking'] = isset($input['enable_click_tracking']);
		$sanitized['enable_conversion_tracking'] = isset($input['enable_conversion_tracking']);
		$sanitized['default_product_status'] = in_array($input['default_product_status'] ?? 'publish', ['draft', 'publish', 'pending', 'private']) ? $input['default_product_status'] : 'publish';
		$sanitized['enable_product_sharing'] = isset($input['enable_product_sharing']);
		$sanitized['sharing_platforms'] = is_array($input['sharing_platforms'] ?? null) ? array_intersect($input['sharing_platforms'], ['facebook', 'twitter', 'linkedin', 'pinterest', 'whatsapp']) : [];
		$sanitized['show_product_version'] = isset($input['show_product_version']);
		$sanitized['show_platform_requirements'] = isset($input['show_platform_requirements']);
		$sanitized['enable_product_tabs'] = isset($input['enable_product_tabs']);
		$sanitized['product_tabs_order'] = sanitize_text_field($input['product_tabs_order'] ?? $this->get_default('product_tabs_order'));
		$sanitized['enable_product_ratings'] = isset($input['enable_product_ratings']);
		$sanitized['enable_product_reviews'] = isset($input['enable_product_reviews']);
		$sanitized['enable_wishlist'] = isset($input['enable_wishlist']);
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure product-specific settings including slugs, tracking, and display options.', 'affiliate-product-showcase') . '</p>';
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
		echo esc_html__('Automatically generate slugs from product titles', 'affiliate-product-showcase');
		echo '</label>';
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
		echo esc_html__('Track affiliate link clicks', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable conversion tracking field
	 *
	 * @return void
	 */
	public function render_enable_conversion_tracking_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_conversion_tracking'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_conversion_tracking]" value="1" ' . $checked . '> ';
		echo esc_html__('Track product conversions', 'affiliate-product-showcase');
		echo '</label>';
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
			'private' => __('Private', 'affiliate-product-showcase'),
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
	 * Render enable product sharing field
	 *
	 * @return void
	 */
	public function render_enable_product_sharing_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_product_sharing'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_product_sharing]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable social sharing buttons', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render sharing platforms field
	 *
	 * @return void
	 */
	public function render_sharing_platforms_field(): void {
		$settings = $this->get_settings();
		$platforms = [
			'facebook' => __('Facebook', 'affiliate-product-showcase'),
			'twitter' => __('Twitter', 'affiliate-product-showcase'),
			'linkedin' => __('LinkedIn', 'affiliate-product-showcase'),
			'pinterest' => __('Pinterest', 'affiliate-product-showcase'),
			'whatsapp' => __('WhatsApp', 'affiliate-product-showcase'),
		];
		
		foreach ($platforms as $value => $label) {
			$checked = in_array($value, $settings['sharing_platforms']) ? 'checked="checked"' : '';
			echo '<label style="display:inline-block; margin-right:15px;">';
			echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[sharing_platforms][]" value="' . esc_attr($value) . '" ' . $checked . '> ';
			echo esc_html($label);
			echo '</label>';
		}
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
		echo esc_html__('Display product version number', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show platform requirements field
	 *
	 * @return void
	 */
	public function render_show_platform_requirements_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_platform_requirements'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[show_platform_requirements]" value="1" ' . $checked . '> ';
		echo esc_html__('Display platform requirements', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable product tabs field
	 *
	 * @return void
	 */
	public function render_enable_product_tabs_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_product_tabs'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_product_tabs]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable tabbed product display', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render product tabs order field
	 *
	 * @return void
	 */
	public function render_product_tabs_order_field(): void {
		$settings = $this->get_settings();
		echo '<input type="text" name="' . esc_attr($this->option_name) . '[product_tabs_order]" value="' . esc_attr($settings['product_tabs_order']) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__('Order of product tabs (comma-separated). Options: description, specs, faq, requirements', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable product ratings field
	 *
	 * @return void
	 */
	public function render_enable_product_ratings_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_product_ratings'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_product_ratings]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable product ratings system', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable product reviews field
	 *
	 * @return void
	 */
	public function render_enable_product_reviews_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_product_reviews'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_product_reviews]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable product reviews', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render enable wishlist field
	 *
	 * @return void
	 */
	public function render_enable_wishlist_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_wishlist'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_wishlist]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable wishlist functionality', 'affiliate-product-showcase');
		echo '</label>';
	}
}