<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Settings Manager
 *
 * Handles plugin settings including registration, validation,
 * sanitization, and retrieval with caching support.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class Settings {
	
	const OPTION_GROUP = 'affiliate_product_showcase_settings';
	const OPTION_NAME = 'affiliate_product_showcase_options';
	
	// Settings sections
	const SECTION_GENERAL = 'general';
	const SECTION_DISPLAY = 'display';
	
	/**
	 * Default settings values
	 */
	private array $defaults;
	
	public function __construct() {
		$this->init_defaults();
	}
	
	/**
	 * Initialize default settings values
	 */
	private function init_defaults(): void {
		$this->defaults = [
			// General Settings
			'plugin_version' => '1.0.0',
			'default_currency' => 'USD',
			'date_format' => get_option('date_format', 'F j, Y'),
			'time_format' => get_option('time_format', 'g:i a'),
			
			// Display Settings
			'products_per_page' => 12,
			'default_view_mode' => 'grid',
			'enable_view_mode_toggle' => true,
			'grid_columns' => 3,
			'list_columns' => 1,
			'enable_lazy_loading' => true,
			'lazy_load_threshold' => 50,
			'show_product_price' => true,
			'show_original_price' => true,
			'show_discount_percentage' => true,
			'price_display_format' => '{symbol}{price}',
			'show_currency_symbol' => true,
			'show_product_sku' => false,
			'show_product_brand' => true,
			'show_product_rating' => false,
			'show_product_clicks' => false,
			'enable_product_quick_view' => false,
			'quick_view_animation' => 'fade',
			'enable_product_comparison' => false,
			'max_comparison_items' => 4,
		];
	}
	
	/**
	 * Initialize settings
	 *
	 * Registers settings, sections, and fields.
	 *
	 * @return void
	 */
	public function init(): void {
		\add_action('admin_init', [$this, 'register_settings']);
	}
	
	/**
	 * Register settings
	 *
	 * Called on admin_init hook to register settings with WordPress.
	 * All sections and fields must be registered regardless of active tab.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		// Register setting
		\register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'sanitize_callback' => [$this, 'sanitize_options'],
				'show_in_rest' => false,
				'default' => $this->defaults
			]
		);
		
		// Register General Settings Section
		\add_settings_section(
			self::SECTION_GENERAL,
			__('General Settings', 'affiliate-product-showcase'),
			[$this, 'render_general_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'general']
		);
		
		// General Settings Fields
		\add_settings_field(
			'plugin_version',
			__('Plugin Version', 'affiliate-product-showcase'),
			[$this, 'render_plugin_version_field'],
			'affiliate-product-showcase',
			self::SECTION_GENERAL,
			['label_for' => 'plugin_version']
		);
		
		\add_settings_field(
			'default_currency',
			__('Default Currency', 'affiliate-product-showcase'),
			[$this, 'render_currency_field'],
			'affiliate-product-showcase',
			self::SECTION_GENERAL,
			['label_for' => 'default_currency']
		);
		
		\add_settings_field(
			'date_format',
			__('Date Format', 'affiliate-product-showcase'),
			[$this, 'render_date_format_field'],
			'affiliate-product-showcase',
			self::SECTION_GENERAL,
			['label_for' => 'date_format']
		);
		
		\add_settings_field(
			'time_format',
			__('Time Format', 'affiliate-product-showcase'),
			[$this, 'render_time_format_field'],
			'affiliate-product-showcase',
			self::SECTION_GENERAL,
			['label_for' => 'time_format']
		);
		
		// Register Display Settings Section
		\add_settings_section(
			self::SECTION_DISPLAY,
			__('Display Settings', 'affiliate-product-showcase'),
			[$this, 'render_display_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'display']
		);
		
		// Display Settings Fields
		\add_settings_field(
			'products_per_page',
			__('Products Per Page', 'affiliate-product-showcase'),
			[$this, 'render_products_per_page_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'products_per_page']
		);
		
		\add_settings_field(
			'default_view_mode',
			__('Default View Mode', 'affiliate-product-showcase'),
			[$this, 'render_default_view_mode_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'default_view_mode']
		);
		
		\add_settings_field(
			'enable_view_mode_toggle',
			__('Enable View Mode Toggle', 'affiliate-product-showcase'),
			[$this, 'render_enable_view_mode_toggle_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'enable_view_mode_toggle']
		);
		
		\add_settings_field(
			'grid_columns',
			__('Grid Columns', 'affiliate-product-showcase'),
			[$this, 'render_grid_columns_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'grid_columns']
		);
		
		\add_settings_field(
			'list_columns',
			__('List Columns', 'affiliate-product-showcase'),
			[$this, 'render_list_columns_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'list_columns']
		);
		
		\add_settings_field(
			'enable_lazy_loading',
			__('Enable Lazy Loading', 'affiliate-product-showcase'),
			[$this, 'render_enable_lazy_loading_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'enable_lazy_loading']
		);
		
		\add_settings_field(
			'lazy_load_threshold',
			__('Lazy Load Threshold', 'affiliate-product-showcase'),
			[$this, 'render_lazy_load_threshold_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'lazy_load_threshold']
		);
		
		\add_settings_field(
			'show_product_price',
			__('Show Product Price', 'affiliate-product-showcase'),
			[$this, 'render_show_product_price_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_product_price']
		);
		
		\add_settings_field(
			'show_original_price',
			__('Show Original Price', 'affiliate-product-showcase'),
			[$this, 'render_show_original_price_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_original_price']
		);
		
		\add_settings_field(
			'show_discount_percentage',
			__('Show Discount Percentage', 'affiliate-product-showcase'),
			[$this, 'render_show_discount_percentage_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_discount_percentage']
		);
		
		\add_settings_field(
			'price_display_format',
			__('Price Display Format', 'affiliate-product-showcase'),
			[$this, 'render_price_display_format_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'price_display_format']
		);
		
		\add_settings_field(
			'show_currency_symbol',
			__('Show Currency Symbol', 'affiliate-product-showcase'),
			[$this, 'render_show_currency_symbol_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_currency_symbol']
		);
		
		\add_settings_field(
			'show_product_sku',
			__('Show Product SKU', 'affiliate-product-showcase'),
			[$this, 'render_show_product_sku_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_product_sku']
		);
		
		\add_settings_field(
			'show_product_brand',
			__('Show Product Brand', 'affiliate-product-showcase'),
			[$this, 'render_show_product_brand_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_product_brand']
		);
		
		\add_settings_field(
			'show_product_rating',
			__('Show Product Rating', 'affiliate-product-showcase'),
			[$this, 'render_show_product_rating_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_product_rating']
		);
		
		\add_settings_field(
			'show_product_clicks',
			__('Show Product Clicks', 'affiliate-product-showcase'),
			[$this, 'render_show_product_clicks_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'show_product_clicks']
		);
		
		\add_settings_field(
			'enable_product_quick_view',
			__('Enable Product Quick View', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_quick_view_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'enable_product_quick_view']
		);
		
		\add_settings_field(
			'quick_view_animation',
			__('Quick View Animation', 'affiliate-product-showcase'),
			[$this, 'render_quick_view_animation_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'quick_view_animation']
		);
		
		\add_settings_field(
			'enable_product_comparison',
			__('Enable Product Comparison', 'affiliate-product-showcase'),
			[$this, 'render_enable_product_comparison_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'enable_product_comparison']
		);
		
		\add_settings_field(
			'max_comparison_items',
			__('Max Comparison Items', 'affiliate-product-showcase'),
			[$this, 'render_max_comparison_items_field'],
			'affiliate-product-showcase',
			self::SECTION_DISPLAY,
			['label_for' => 'max_comparison_items']
		);
	}
	
	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings(): array {
		$options = get_option(self::OPTION_NAME, []);
		return wp_parse_args($options, $this->defaults);
	}
	
	/**
	 * Get a single setting value
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null) {
		$settings = $this->get_settings();
		return $settings[$key] ?? ($default ?? $this->defaults[$key] ?? null);
	}
	
	/**
	 * Sanitize options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$sanitized = [];
		
		// General Settings
		$sanitized['plugin_version'] = $this->defaults['plugin_version']; // Read-only
		$sanitized['default_currency'] = sanitize_text_field($input['default_currency'] ?? 'USD');
		$sanitized['date_format'] = sanitize_text_field($input['date_format'] ?? $this->defaults['date_format']);
		$sanitized['time_format'] = sanitize_text_field($input['time_format'] ?? $this->defaults['time_format']);
		
		// Display Settings
		$sanitized['products_per_page'] = intval($input['products_per_page'] ?? 12);
		$sanitized['products_per_page'] = max(6, min(48, $sanitized['products_per_page']));
		$sanitized['default_view_mode'] = in_array($input['default_view_mode'] ?? 'grid', ['grid', 'list']) ? $input['default_view_mode'] : 'grid';
		$sanitized['enable_view_mode_toggle'] = isset($input['enable_view_mode_toggle']);
		$sanitized['grid_columns'] = intval($input['grid_columns'] ?? 3);
		$sanitized['grid_columns'] = max(2, min(5, $sanitized['grid_columns']));
		$sanitized['list_columns'] = intval($input['list_columns'] ?? 1);
		$sanitized['list_columns'] = max(1, min(2, $sanitized['list_columns']));
		$sanitized['enable_lazy_loading'] = isset($input['enable_lazy_loading']);
		$sanitized['lazy_load_threshold'] = intval($input['lazy_load_threshold'] ?? 50);
		$sanitized['lazy_load_threshold'] = max(20, min(100, $sanitized['lazy_load_threshold']));
		$sanitized['show_product_price'] = isset($input['show_product_price']);
		$sanitized['show_original_price'] = isset($input['show_original_price']);
		$sanitized['show_discount_percentage'] = isset($input['show_discount_percentage']);
		$sanitized['price_display_format'] = sanitize_text_field($input['price_display_format'] ?? '{symbol}{price}');
		$sanitized['show_currency_symbol'] = isset($input['show_currency_symbol']);
		$sanitized['show_product_sku'] = isset($input['show_product_sku']);
		$sanitized['show_product_brand'] = isset($input['show_product_brand']);
		$sanitized['show_product_rating'] = isset($input['show_product_rating']);
		$sanitized['show_product_clicks'] = isset($input['show_product_clicks']);
		$sanitized['enable_product_quick_view'] = isset($input['enable_product_quick_view']);
		$sanitized['quick_view_animation'] = in_array($input['quick_view_animation'] ?? 'fade', ['fade', 'slide', 'zoom']) ? $input['quick_view_animation'] : 'fade';
		$sanitized['enable_product_comparison'] = isset($input['enable_product_comparison']);
		$sanitized['max_comparison_items'] = intval($input['max_comparison_items'] ?? 4);
		$sanitized['max_comparison_items'] = max(2, min(5, $sanitized['max_comparison_items']));
		
		return $sanitized;
	}
	
	// ============== SECTION DESCRIPTIONS ==============
	
	/**
	 * Render general section description
	 *
	 * @return void
	 */
	public function render_general_section_description(): void {
		echo '<p>' . esc_html__('Configure general plugin settings and preferences.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render display section description
	 *
	 * @return void
	 */
	public function render_display_section_description(): void {
		echo '<p>' . esc_html__('Control how products are displayed on frontend.', 'affiliate-product-showcase') . '</p>';
	}
	
	// ============== GENERAL SETTINGS FIELDS ==============
	
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
		
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[default_currency]">';
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
		
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[date_format]">';
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
		
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[time_format]">';
		foreach ($formats as $format => $example) {
			$selected = selected($settings['time_format'], $format, false);
			echo '<option value="' . esc_attr($format) . '" ' . $selected . '>';
			echo esc_html($format) . ' <span class="example">' . esc_html('(' . $example . ')') . '</span>';
			echo '</option>';
		}
		echo '</select>';
	}
	
	// ============== DISPLAY SETTINGS FIELDS ==============
	
	/**
	 * Render products per page field
	 *
	 * @return void
	 */
	public function render_products_per_page_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[products_per_page]">';
		foreach ([6, 12, 18, 24, 36, 48] as $value) {
			$selected = selected($settings['products_per_page'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render default view mode field
	 *
	 * @return void
	 */
	public function render_default_view_mode_field(): void {
		$settings = $this->get_settings();
		$modes = [
			'grid' => __('Grid', 'affiliate-product-showcase'),
			'list' => __('List', 'affiliate-product-showcase'),
		];
		
		foreach ($modes as $value => $label) {
			$checked = checked($settings['default_view_mode'], $value, false);
			echo '<label>';
			echo '<input type="radio" name="' . esc_attr(self::OPTION_NAME) . '[default_view_mode]" value="' . esc_attr($value) . '" ' . $checked . '> ';
			echo esc_html($label);
			echo '</label><br>';
		}
	}
	
	/**
	 * Render enable view mode toggle field
	 *
	 * @return void
	 */
	public function render_enable_view_mode_toggle_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_view_mode_toggle'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[enable_view_mode_toggle]" value="1" ' . $checked . '> ';
		echo esc_html__('Allow users to toggle between grid and list view', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render grid columns field
	 *
	 * @return void
	 */
	public function render_grid_columns_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[grid_columns]">';
		foreach ([2, 3, 4, 5] as $value) {
			$selected = selected($settings['grid_columns'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Number of columns in grid view.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render list columns field
	 *
	 * @return void
	 */
	public function render_list_columns_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[list_columns]">';
		foreach ([1, 2] as $value) {
			$selected = selected($settings['list_columns'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Number of columns in list view.', 'affiliate-product-showcase') . '</p>';
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
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[enable_lazy_loading]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable lazy loading for images', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render lazy load threshold field
	 *
	 * @return void
	 */
	public function render_lazy_load_threshold_field(): void {
		$settings = $this->get_settings();
		echo '<input type="number" name="' . esc_attr(self::OPTION_NAME) . '[lazy_load_threshold]" value="' . esc_attr($settings['lazy_load_threshold']) . '" min="20" max="100" step="10">';
		echo '<p class="description">' . esc_html__('Number of products before lazy loading starts (20-100).', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render show product price field
	 *
	 * @return void
	 */
	public function render_show_product_price_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_product_price'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_product_price]" value="1" ' . $checked . '> ';
		echo esc_html__('Show product price', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show original price field
	 *
	 * @return void
	 */
	public function render_show_original_price_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_original_price'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_original_price]" value="1" ' . $checked . '> ';
		echo esc_html__('Show original price (if discount)', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show discount percentage field
	 *
	 * @return void
	 */
	public function render_show_discount_percentage_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_discount_percentage'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_discount_percentage]" value="1" ' . $checked . '> ';
		echo esc_html__('Show discount percentage', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render price display format field
	 *
	 * @return void
	 */
	public function render_price_display_format_field(): void {
		$settings = $this->get_settings();
		echo '<input type="text" name="' . esc_attr(self::OPTION_NAME) . '[price_display_format]" value="' . esc_attr($settings['price_display_format']) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__('Use {symbol} for currency symbol, {price} for price. Example: {symbol}{price} or {price}{symbol}', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render show currency symbol field
	 *
	 * @return void
	 */
	public function render_show_currency_symbol_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_currency_symbol'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_currency_symbol]" value="1" ' . $checked . '> ';
		echo esc_html__('Show currency symbol', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show product SKU field
	 *
	 * @return void
	 */
	public function render_show_product_sku_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_product_sku'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_product_sku]" value="1" ' . $checked . '> ';
		echo esc_html__('Show product SKU', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show product brand field
	 *
	 * @return void
	 */
	public function render_show_product_brand_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_product_brand'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_product_brand]" value="1" ' . $checked . '> ';
		echo esc_html__('Show product brand', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show product rating field
	 *
	 * @return void
	 */
	public function render_show_product_rating_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_product_rating'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_product_rating]" value="1" ' . $checked . '> ';
		echo esc_html__('Show product rating', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render show product clicks field
	 *
	 * @return void
	 */
	public function render_show_product_clicks_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_product_clicks'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[show_product_clicks]" value="1" ' . $checked . '> ';
		echo esc_html__('Show product click count', 'affiliate-product-showcase');
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
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[enable_product_quick_view]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable product quick view modal', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render quick view animation field
	 *
	 * @return void
	 */
	public function render_quick_view_animation_field(): void {
		$settings = $this->get_settings();
		$animations = [
			'fade' => __('Fade', 'affiliate-product-showcase'),
			'slide' => __('Slide', 'affiliate-product-showcase'),
			'zoom' => __('Zoom', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[quick_view_animation]">';
		foreach ($animations as $value => $label) {
			$selected = selected($settings['quick_view_animation'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable product comparison field
	 *
	 * @return void
	 */
	public function render_enable_product_comparison_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_product_comparison'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[enable_product_comparison]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable product comparison', 'affiliate-product-showcase');
		echo '</label>';
	}
	
	/**
	 * Render max comparison items field
	 *
	 * @return void
	 */
	public function render_max_comparison_items_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[max_comparison_items]">';
		foreach ([2, 3, 4, 5] as $value) {
			$selected = selected($settings['max_comparison_items'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Maximum items for comparison.', 'affiliate-product-showcase') . '</p>';
	}
}