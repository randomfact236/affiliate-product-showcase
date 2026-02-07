<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Abstract Settings Section
 *
 * Base class for all settings sections. Provides common functionality
 * for getting settings, defaults, and rendering.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
abstract class AbstractSettingsSection {
	
	/**
	 * Option name for the settings
	 *
	 * @var string
	 */
	protected string $option_name;
	
	/**
	 * Constructor
	 *
	 * @param string $option_name
	 */
	public function __construct(string $option_name) {
		$this->option_name = $option_name;
	}
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	abstract public function get_defaults(): array;
	
	/**
	 * Register section and fields
	 *
	 * @return void
	 */
	abstract public function register_section_and_fields(): void;
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	abstract public function sanitize_options(array $input): array;
	
	/**
	 * Get all settings
	 *
	 * @return array
	 */
	protected function get_settings(): array {
		$options = get_option($this->option_name, []);
		return wp_parse_args($options, $this->get_all_defaults());
	}
	
	/**
	 * Get a single setting value
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	protected function get_setting(string $key, $default = null) {
		$settings = $this->get_settings();
		return $settings[$key] ?? ($default ?? $this->get_default($key));
	}
	
	/**
	 * Get a default value
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function get_default(string $key) {
		$defaults = $this->get_defaults();
		return $defaults[$key] ?? null;
	}
	
	/**
	 * Get all defaults from all sections
	 *
	 * @return array
	 */
	private function get_all_defaults(): array {
		// This would be called from the main Settings class
		// For now, return this section's defaults
		return $this->get_defaults();
	}
}