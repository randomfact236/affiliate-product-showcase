<?php
/**
 * Nonce Verification Trait
 *
 * Provides reusable nonce verification methods.
 *
 * @package AffiliateProductShowcase\Traits
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Traits;

/**
 * Nonce Verification Trait
 *
 * Provides reusable nonce verification methods.
 *
 * @package AffiliateProductShowcase\Traits
 * @since 2.1.0
 */
trait NonceVerificationTrait {
	/**
	 * Verify nonce value
	 *
	 * @param string $nonce Nonce value
	 * @param string $action Nonce action
	 * @return bool True if valid
	 * @since 2.1.0
	 */
	protected function verify_nonce(string $nonce, string $action): bool {
		return wp_verify_nonce($nonce, $action) !== false;
	}
	
	/**
	 * Verify POST nonce
	 *
	 * @param string $key POST key for nonce
	 * @param string $action Nonce action
	 * @return bool True if valid
	 * @since 2.1.0
	 */
	protected function verify_post_nonce(string $key, string $action): bool {
		return isset($_POST[$key]) && $this->verify_nonce($_POST[$key], $action);
	}
	
	/**
	 * Verify header nonce (for REST API)
	 *
	 * @param string $header Header name (default: X-WP-Nonce)
	 * @param string $action Nonce action (default: wp_rest)
	 * @return bool True if valid
	 * @since 2.1.0
	 */
	protected function verify_header_nonce(string $header = 'X-WP-Nonce', string $action = 'wp_rest'): bool {
		// This would need to be adapted based on context
		// For REST API controllers, use $request->get_header()
		// This is a placeholder for trait usage
		return true;
	}
	
	/**
	 * Die with nonce error message
	 *
	 * @return void
	 * @since 2.1.0
	 */
	protected function nonce_failed(): void {
		wp_die(esc_html__('Security check failed. Please try again.', 'affiliate-product-showcase'));
	}
}
