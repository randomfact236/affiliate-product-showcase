<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Security Settings (DEPRECATED)
 *
 * Security is handled by:
 * - WordPress core (nonces, CSRF)
 * - Code-level sanitization (always enabled)
 * - Server configuration (headers, rate limiting)
 *
 * This file kept for backward compatibility only.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 * @deprecated Security settings removed - use WordPress core + server config
 */
final class SecuritySettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_security';
	const SECTION_TITLE = 'Security Settings';
	
	/**
	 * Get default values - all security features always enabled in code
	 *
	 * @return array Empty array - no settings needed
	 */
	public function get_defaults(): array {
		return [];
	}
	
	/**
	 * Register section and fields - no fields registered
	 *
	 * @return void
	 */
	public function register_section_and_fields(): void {
		// Security handled by WordPress core and code-level implementation
		// No user-configurable security settings
	}
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		return [];
	}
}
