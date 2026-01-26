<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

use AffiliateProductShowcase\Admin\Settings\AbstractSettingsSection;

/**
 * Shortcode Settings Section
 *
 * Handles settings for shortcode configuration including
 * shortcode IDs and caching options.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 * @author Development Team
 */
final class ShortcodeSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_shortcodes';
	const SECTION_TITLE = 'Shortcodes';
	const SECTION_DESCRIPTION = 'Configure shortcode IDs and caching options for dynamic content.';
	
	/**
	 * @var array
	 */
	private array $cache_duration_options = [
		300 => '5 minutes',
		600 => '10 minutes',
		1800 => '30 minutes',
		3600 => '1 hour',
		7200 => '2 hours',
		86400 => '1 day',
	];
	
	/**
	 * Get default settings values
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'products_shortcode_id' => 'products',
			'product_shortcode_id' => 'product',
			'category_shortcode_id' => 'category',
			'tag_shortcode_id' => 'tag',
			'ribbon_shortcode_id' => 'ribbon',
			'enable_shortcode_cache' => true,
			'shortcode_cache_duration' => 3600,
		];
	}
	
	/**
	 * Register settings section and fields
	 *
	 * @return void
	 */
	public function register_section_and_fields(): void {
		// Register section
		\add_settings_section(
			self::SECTION_ID,
			self::SECTION_TITLE,
			[$this, 'render_section_description'],
			'affiliate-product-showcase'
		);
		
		// Shortcode IDs
		$this->add_field(
			'products_shortcode_id',
			'Products Shortcode',
			'Shortcode ID for displaying product lists. Default: [products]',
			'text'
		);
		
		$this->add_field(
			'product_shortcode_id',
			'Single Product Shortcode',
			'Shortcode ID for displaying a single product. Default: [product id="123"]',
			'text'
		);
		
		$this->add_field(
			'category_shortcode_id',
			'Category Shortcode',
			'Shortcode ID for displaying products by category. Default: [category slug="electronics"]',
			'text'
		);
		
		$this->add_field(
			'tag_shortcode_id',
			'Tag Shortcode',
			'Shortcode ID for displaying products by tag. Default: [tag slug="sale"]',
			'text'
		);
		
		$this->add_field(
			'ribbon_shortcode_id',
			'Ribbon Shortcode',
			'Shortcode ID for displaying ribbons/badges. Default: [ribbon]',
			'text'
		);
		
		// Cache Settings
		$this->add_field(
			'enable_shortcode_cache',
			'Enable Shortcode Caching',
			'Cache shortcode output to improve performance. Disable during development.',
			'checkbox'
		);
		
		$this->add_field(
			'shortcode_cache_duration',
			'Cache Duration',
			'How long to cache shortcode output. Shorter duration means more frequent updates.',
			'select',
			$this->cache_duration_options
		);
	}
	
	/**
	 * Sanitize settings options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$sanitized = [];
		
		// Shortcode IDs
		if (isset($input['products_shortcode_id'])) {
			$sanitized['products_shortcode_id'] = $this->sanitize_shortcode_id($input['products_shortcode_id'], 'products');
		}
		
		if (isset($input['product_shortcode_id'])) {
			$sanitized['product_shortcode_id'] = $this->sanitize_shortcode_id($input['product_shortcode_id'], 'product');
		}
		
		if (isset($input['category_shortcode_id'])) {
			$sanitized['category_shortcode_id'] = $this->sanitize_shortcode_id($input['category_shortcode_id'], 'category');
		}
		
		if (isset($input['tag_shortcode_id'])) {
			$sanitized['tag_shortcode_id'] = $this->sanitize_shortcode_id($input['tag_shortcode_id'], 'tag');
		}
		
		if (isset($input['ribbon_shortcode_id'])) {
			$sanitized['ribbon_shortcode_id'] = $this->sanitize_shortcode_id($input['ribbon_shortcode_id'], 'ribbon');
		}
		
		// Cache Settings
		$sanitized['enable_shortcode_cache'] = isset($input['enable_shortcode_cache']) ? (bool) $input['enable_shortcode_cache'] : true;
		
		if (isset($input['shortcode_cache_duration'])) {
			$duration = intval($input['shortcode_cache_duration']);
			$sanitized['shortcode_cache_duration'] = in_array($duration, array_keys($this->cache_duration_options))
				? $duration
				: 3600;
		}
		
		return $sanitized;
	}
	
	/**
	 * Sanitize shortcode ID
	 *
	 * Ensures shortcode ID is valid: lowercase, alphanumeric, underscores, hyphens only
	 *
	 * @param string $id
	 * @param string $default
	 * @return string
	 */
	private function sanitize_shortcode_id(string $id, string $default): string {
		// Remove square brackets if present
		$id = str_replace(['[', ']'], '', $id);
		
		// Sanitize and validate format
		$sanitized = sanitize_text_field($id);
		
		// Only allow lowercase letters, numbers, underscores, and hyphens
		$sanitized = preg_replace('/[^a-z0-9_-]/', '', strtolower($sanitized));
		
		// Ensure it's not empty
		return empty($sanitized) ? $default : $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Customize shortcode IDs for embedding products in posts/pages. Configure caching to improve performance on high-traffic sites.', 'affiliate-product-showcase') . '</p>';
	}
}