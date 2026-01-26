<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Admin\Settings\GeneralSettings;
use AffiliateProductShowcase\Admin\Settings\ProductsSettings;
use AffiliateProductShowcase\Admin\Settings\CategoriesSettings;
use AffiliateProductShowcase\Admin\Settings\TagsSettings;
use AffiliateProductShowcase\Admin\Settings\RibbonsSettings;
use AffiliateProductShowcase\Admin\Settings\DisplaySettings;
use AffiliateProductShowcase\Admin\Settings\SecuritySettings;
use AffiliateProductShowcase\Admin\Settings\ImportExportSettings;
use AffiliateProductShowcase\Admin\Settings\ShortcodeSettings;
use AffiliateProductShowcase\Admin\Settings\WidgetSettings;

/**
 * Settings Manager
 *
 * Coordinates all settings sections, handles registration,
 * sanitization, and retrieval with caching support.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class Settings {
	
	const OPTION_GROUP = 'affiliate_product_showcase_settings';
	const OPTION_NAME = 'affiliate_product_showcase_options';
	
	// Section constants for backward compatibility with settings-page.php
	const SECTION_GENERAL = 'affiliate_product_showcase_general';
	const SECTION_DISPLAY = 'affiliate_product_showcase_display';
	const SECTION_PRODUCTS = 'affiliate_product_showcase_products';
	const SECTION_CATEGORIES = 'affiliate_product_showcase_categories';
	const SECTION_TAGS = 'affiliate_product_showcase_tags';
	const SECTION_RIBBONS = 'affiliate_product_showcase_ribbons';
	const SECTION_SECURITY = 'affiliate_product_showcase_security';
	const SECTION_IMPORT_EXPORT = 'affiliate_product_showcase_import_export';
	const SECTION_SHORTCODES = 'affiliate_product_showcase_shortcodes';
	const SECTION_WIDGETS = 'affiliate_product_showcase_widgets';
	
	/**
	 * @var GeneralSettings
	 */
	private GeneralSettings $general_settings;
	
	/**
	 * @var ProductsSettings
	 */
	private ProductsSettings $products_settings;
	
	/**
	 * @var CategoriesSettings
	 */
	private CategoriesSettings $categories_settings;
	
	/**
	 * @var TagsSettings
	 */
	private TagsSettings $tags_settings;
	
	/**
	 * @var RibbonsSettings
	 */
	private RibbonsSettings $ribbons_settings;
	
	/**
	 * @var DisplaySettings
	 */
	private DisplaySettings $display_settings;
	
	/**
	 * @var SecuritySettings
	 */
	private SecuritySettings $security_settings;
	
	/**
	 * @var ImportExportSettings
	 */
	private ImportExportSettings $import_export_settings;
	
	/**
	 * @var ShortcodeSettings
	 */
	private ShortcodeSettings $shortcode_settings;
	
	/**
	 * @var WidgetSettings
	 */
	private WidgetSettings $widget_settings;
	
	/**
	 * @var array
	 */
	private array $defaults;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_sections();
		$this->init_defaults();
	}
	
	/**
	 * Initialize settings sections
	 *
	 * @return void
	 */
	private function init_sections(): void {
		$this->general_settings = new GeneralSettings(self::OPTION_NAME);
		$this->products_settings = new ProductsSettings(self::OPTION_NAME);
		$this->categories_settings = new CategoriesSettings(self::OPTION_NAME);
		$this->tags_settings = new TagsSettings(self::OPTION_NAME);
		$this->ribbons_settings = new RibbonsSettings(self::OPTION_NAME);
		$this->display_settings = new DisplaySettings(self::OPTION_NAME);
		$this->security_settings = new SecuritySettings(self::OPTION_NAME);
		$this->import_export_settings = new ImportExportSettings(self::OPTION_NAME);
		$this->shortcode_settings = new ShortcodeSettings(self::OPTION_NAME);
		$this->widget_settings = new WidgetSettings(self::OPTION_NAME);
	}
	
	/**
	 * Initialize default settings values
	 *
	 * @return void
	 */
	private function init_defaults(): void {
		$this->defaults = array_merge(
			$this->general_settings->get_defaults(),
			$this->products_settings->get_defaults(),
			$this->categories_settings->get_defaults(),
			$this->tags_settings->get_defaults(),
			$this->ribbons_settings->get_defaults(),
			$this->display_settings->get_defaults(),
			$this->security_settings->get_defaults(),
			$this->import_export_settings->get_defaults(),
			$this->shortcode_settings->get_defaults(),
			$this->widget_settings->get_defaults()
		);
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
		
		// Register all sections and fields
		$this->general_settings->register_section_and_fields();
		$this->products_settings->register_section_and_fields();
		$this->categories_settings->register_section_and_fields();
		$this->tags_settings->register_section_and_fields();
		$this->ribbons_settings->register_section_and_fields();
		$this->display_settings->register_section_and_fields();
		$this->security_settings->register_section_and_fields();
		$this->import_export_settings->register_section_and_fields();
		$this->shortcode_settings->register_section_and_fields();
		$this->widget_settings->register_section_and_fields();
	}
	
	/**
	 * Get all settings
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
		
		// Sanitize each section's options
		$sanitized = array_merge($sanitized, $this->general_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->products_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->categories_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->tags_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->ribbons_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->display_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->security_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->import_export_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->shortcode_settings->sanitize_options($input));
		$sanitized = array_merge($sanitized, $this->widget_settings->sanitize_options($input));
		
		return $sanitized;
	}
	
	/**
	 * Get all available sections
	 *
	 * @return array
	 */
	public function get_sections(): array {
		return [
			GeneralSettings::SECTION_ID => GeneralSettings::SECTION_TITLE,
			ProductsSettings::SECTION_ID => ProductsSettings::SECTION_TITLE,
			CategoriesSettings::SECTION_ID => CategoriesSettings::SECTION_TITLE,
			TagsSettings::SECTION_ID => TagsSettings::SECTION_TITLE,
			RibbonsSettings::SECTION_ID => RibbonsSettings::SECTION_TITLE,
			DisplaySettings::SECTION_ID => DisplaySettings::SECTION_TITLE,
			SecuritySettings::SECTION_ID => SecuritySettings::SECTION_TITLE,
			ImportExportSettings::SECTION_ID => ImportExportSettings::SECTION_TITLE,
			ShortcodeSettings::SECTION_ID => ShortcodeSettings::SECTION_TITLE,
			WidgetSettings::SECTION_ID => WidgetSettings::SECTION_TITLE,
		];
	}
}