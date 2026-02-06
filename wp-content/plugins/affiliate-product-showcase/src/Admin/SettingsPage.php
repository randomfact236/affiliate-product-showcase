<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Settings Page
 * 
 * Clean, simple architecture for settings page with pill tabs.
 * Each tab has its own method for easy customization.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class SettingsPage {

	/**
	 * Tab definitions
	 */
	private array $tabs = [
		'general' => [
			'label' => 'General',
			'icon' => '⚙️',
			'description' => 'Configure general plugin settings and preferences.',
		],
		'display' => [
			'label' => 'Display',
			'icon' => '🎨',
			'description' => 'Control how products are displayed on frontend.',
		],
		'products' => [
			'label' => 'Products',
			'icon' => '📦',
			'description' => 'Configure product-specific settings.',
		],
		'categories' => [
			'label' => 'Categories',
			'icon' => '📁',
			'description' => 'Configure default category appearance.',
		],
		'tags' => [
			'label' => 'Tags',
			'icon' => '🏷️',
			'description' => 'Configure tag appearance. Individual tags can override the default color.',
		],
		'ribbons' => [
			'label' => 'Ribbons',
			'icon' => '🎀',
			'description' => 'Configure ribbon/badge settings.',
		],
		'import_export' => [
			'label' => 'Import/Export',
			'icon' => '📥',
			'description' => 'Import or export your product data.',
		],
		'shortcodes' => [
			'label' => 'Shortcodes',
			'icon' => '🎬',
			'description' => 'Configure shortcode defaults.',
		],
		'widgets' => [
			'label' => 'Widgets',
			'icon' => '🧩',
			'description' => 'Configure widget defaults.',
		],
	];

	/**
	 * Coming soon tabs (disabled)
	 */
	private array $coming_soon = [
		'performance' => [
			'label' => 'Performance',
			'icon' => '⚡',
			'badge' => 'Soon',
		],
	];

	/**
	 * Current active tab
	 */
	private string $active_tab = 'general';

	/**
	 * Settings instance
	 */
	private Settings $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = new Settings();
	}

	/**
	 * Initialize settings page
	 */
	public function init(): void {
		add_action('admin_menu', [$this, 'addMenuPage']);
		add_action('admin_init', [$this, 'registerSettings']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
	}

	/**
	 * Add submenu page
	 */
	public function addMenuPage(): void {
		add_submenu_page(
			'affiliate-manager',
			__('Settings', 'affiliate-product-showcase'),
			__('Settings', 'affiliate-product-showcase'),
			'manage_options',
			'affiliate-manager-settings',
			[$this, 'renderPage']
		);
	}

	/**
	 * Register settings
	 */
	public function registerSettings(): void {
		$this->settings->register_settings();
	}

	/**
	 * Enqueue assets
	 */
	public function enqueueAssets(string $hook): void {
		if ($hook !== 'affiliate-manager_page_affiliate-manager-settings') {
			return;
		}

		// Core + Admin CSS (compiled from SCSS)
		wp_enqueue_style(
			'aps-core',
			\AffiliateProductShowcase\Plugin\Constants::assetUrl('assets/css/core.css'),
			[],
			'2.0.0'
		);
		wp_enqueue_style(
			'aps-settings',
			\AffiliateProductShowcase\Plugin\Constants::assetUrl('assets/css/admin.css'),
			[ 'aps-core' ],
			'2.0.0'
		);

		// WordPress color picker (same as tag add form)
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');

		// Initialize color picker
		wp_add_inline_script('wp-color-picker', $this->getColorPickerScript());
	}

	/**
	 * Get color picker initialization script
	 */
	private function getColorPickerScript(): string {
		return 'jQuery(document).ready(function($) {
			if ($(".aps-color-picker").length) {
				$(".aps-color-picker").wpColorPicker();
			}
		});';
	}

	/**
	 * Render settings page
	 */
	public function renderPage(): void {
		$this->active_tab = sanitize_text_field($_GET['tab'] ?? 'general');
		
		if (!array_key_exists($this->active_tab, $this->tabs)) {
			$this->active_tab = 'general';
		}

		$this->renderHeader();
		$this->renderTabs();
		$this->renderContent();
		$this->renderFooter();
	}

	/**
	 * Render page header
	 */
	private function renderHeader(): void {
		echo '<div class="wrap aps-settings-wrap">';
		echo '<h1>' . esc_html__('Affiliate Product Showcase Settings', 'affiliate-product-showcase') . '</h1>';
	}

	/**
	 * Render tab navigation
	 */
	private function renderTabs(): void {
		echo '<nav class="aps-settings-tabs">';

		// Active tabs
		foreach ($this->tabs as $slug => $tab) {
			$url = admin_url('admin.php?page=affiliate-manager-settings&tab=' . $slug);
			$class = ($this->active_tab === $slug) ? 'aps-tab is-active' : 'aps-tab';
			
			echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">';
			echo '<span class="aps-tab__icon">' . esc_html($tab['icon']) . '</span>';
			echo '<span class="aps-tab__text">' . esc_html($tab['label']) . '</span>';
			echo '</a>';
		}

		// Coming soon tabs
		foreach ($this->coming_soon as $slug => $tab) {
			echo '<span class="aps-tab is-disabled">';
			echo '<span class="aps-tab__icon">' . esc_html($tab['icon']) . '</span>';
			echo '<span class="aps-tab__text">' . esc_html($tab['label']) . '</span>';
			echo '<span class="aps-tab__badge">' . esc_html($tab['badge']) . '</span>';
			echo '</span>';
		}

		echo '</nav>';
	}

	/**
	 * Render content area
	 */
	private function renderContent(): void {
		echo '<div class="aps-settings-content">';
		echo '<form action="options.php" method="post">';
		
		settings_fields(Settings::OPTION_GROUP);
		
		$tab = $this->tabs[$this->active_tab];
		echo '<h2>' . esc_html($tab['label'] . ' ' . __('Settings', 'affiliate-product-showcase')) . '</h2>';
		echo '<p>' . esc_html($tab['description']) . '</p>';
		
		echo '<table class="form-table">';
		do_settings_fields('affiliate-product-showcase', $this->getSectionId($this->active_tab));
		echo '</table>';
		
		submit_button();
		
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render page footer
	 */
	private function renderFooter(): void {
		echo '</div>';
	}

	/**
	 * Get section ID from tab slug
	 */
	private function getSectionId(string $tab): string {
		$map = [
			'general' => Settings::SECTION_GENERAL,
			'display' => Settings::SECTION_DISPLAY,
			'products' => Settings::SECTION_PRODUCTS,
			'categories' => Settings::SECTION_CATEGORIES,
			'tags' => Settings::SECTION_TAGS,
			'ribbons' => Settings::SECTION_RIBBONS,
			'import_export' => Settings::SECTION_IMPORT_EXPORT,
			'shortcodes' => Settings::SECTION_SHORTCODES,
			'widgets' => Settings::SECTION_WIDGETS,
		];

		return $map[$tab] ?? Settings::SECTION_GENERAL;
	}
}
