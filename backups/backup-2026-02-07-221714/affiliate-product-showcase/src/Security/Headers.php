<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Security Headers Manager
 *
 * Implements comprehensive security headers following OWASP recommendations
 * and WordPress best practices. Uses wp_headers filter for reliable header injection.
 *
 * @package AffiliateProductShowcase\Security
 * @since 1.0.0
 */
class Headers {
	/**
	 * Initialize security headers.
	 *
	 * Registers the wp_headers filter to inject security headers
	 * into all WordPress HTTP responses.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'wp_headers', [ $this, 'add_security_headers' ] );
	}

	/**
	 * Add security headers to HTTP responses.
	 *
	 * Implements OWASP-recommended security headers:
	 * - Content-Security-Policy: Restricts resource sources to prevent XSS
	 * - X-Content-Type-Options: Prevents MIME type sniffing
	 * - X-Frame-Options: Prevents clickjacking attacks
	 * - X-XSS-Protection: Enables browser XSS filters
	 * - Referrer-Policy: Controls referrer information leakage
	 * - Permissions-Policy: Restricts browser features
	 * - Strict-Transport-Security: Enforces HTTPS
	 * - Expect-CT: Prevents MIME confusion attacks
	 * - Cross-Origin-Opener-Policy: Controls cross-origin behavior
	 * - Cross-Origin-Resource-Policy: Controls resource loading
	 *
	 * @param array $headers Existing HTTP headers
	 * @return array Modified headers with security additions
	 */
	public function add_security_headers( array $headers ): array {
		// Add headers to admin pages
		if ( is_admin() ) {
			$headers = $this->add_admin_headers( $headers );
		}

		// Add headers to frontend pages
		$headers = $this->add_frontend_headers( $headers );

		// Add headers to REST API endpoints
		$headers = $this->add_rest_headers( $headers );

		// Add global security headers
		$headers = $this->add_global_security_headers( $headers );

		return $headers;
	}

	/**
	 * Add global security headers
	 *
	 * Adds security headers that apply to all requests.
	 *
	 * @param array $headers Existing HTTP headers
	 * @return array Headers with global security additions
	 * @since 1.0.0
	 */
	private function add_global_security_headers( array $headers ): array {
		// Strict-Transport-Security (HSTS) - only on HTTPS
		if ( is_ssl() ) {
			$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
		}

		// Expect-CT - prevent MIME confusion attacks
		$headers['Expect-CT'] = 'enforce';

		// Cross-Origin-Opener-Policy - control window.opener access
		$headers['Cross-Origin-Opener-Policy'] = 'same-origin';

		// Cross-Origin-Resource-Policy - control resource loading
		$headers['Cross-Origin-Resource-Policy'] = "same-origin";

		return $headers;
	}

	/**
	 * Add security headers for admin pages.
	 *
	 * Admin pages require more permissive CSP directives to allow
	 * WordPress admin interface functionality (inline scripts, styles, etc.).
	 *
	 * @param array $headers Existing HTTP headers
	 * @return array Headers with admin-specific security additions
	 */
	private function add_admin_headers( array $headers ): array {
		// Content-Security-Policy for admin
		$csp_directives = [
			"default-src 'self'",
			"script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Required for WP admin
			"style-src 'self' 'unsafe-inline'", // Required for WP admin
			"img-src 'self' data: https:",
			"connect-src 'self'",
			"frame-src 'self'",
			"font-src 'self' data:",
			"object-src 'none'", // Block plugins
		];

		$headers['Content-Security-Policy'] = implode( '; ', $csp_directives );
		$headers['X-Content-Type-Options'] = 'nosniff';
		$headers['X-Frame-Options'] = 'SAMEORIGIN';
		$headers['X-XSS-Protection'] = '1; mode=block';
		$headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';

		// Permissions-Policy (formerly Feature-Policy)
		$permissions_directives = [
			"geolocation=()",
			"microphone=()",
			"camera=()",
			"payment=()",
		];
		$headers['Permissions-Policy'] = implode( ', ', $permissions_directives );

		return $headers;
	}

	/**
	 * Add security headers for frontend pages.
	 *
	 * Frontend pages use stricter CSP for better security.
	 *
	 * @param array $headers Existing HTTP headers
	 * @return array Headers with frontend-specific security additions
	 */
	private function add_frontend_headers( array $headers ): array {
		// Skip if headers already set (admin takes precedence)
		if ( isset( $headers['Content-Security-Policy'] ) ) {
			return $headers;
		}

		// Content-Security-Policy for frontend
		$csp_directives = [
			"default-src 'self'",
			"script-src 'self' 'unsafe-inline'", // May be needed for inline scripts
			"style-src 'self' 'unsafe-inline'", // May be needed for inline styles
			"img-src 'self' data: https:",
			"connect-src 'self'",
			"frame-src 'self'",
			"font-src 'self' data:",
			"object-src 'none'",
		];

		$headers['Content-Security-Policy'] = implode( '; ', $csp_directives );
		$headers['X-Content-Type-Options'] = 'nosniff';
		$headers['X-Frame-Options'] = 'SAMEORIGIN';
		$headers['X-XSS-Protection'] = '1; mode=block';
		$headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';

		return $headers;
	}

	/**
	 * Add security headers for REST API endpoints.
	 *
	 * REST API uses minimal headers to ensure JSON responses work correctly.
	 *
	 * @param array $headers Existing HTTP headers
	 * @return array Headers with REST-specific security additions
	 */
	private function add_rest_headers( array $headers ): array {
		// Only add to REST API requests
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
			return $headers;
		}

		// REST API doesn't need CSP, but needs other security headers
		$headers['X-Content-Type-Options'] = 'nosniff';
		$headers['X-Frame-Options'] = 'SAMEORIGIN';
		$headers['X-XSS-Protection'] = '1; mode=block';

		return $headers;
	}

	/**
	 * Verify security headers are being sent.
	 *
	 * Utility method for testing and verification.
	 * Can be used in development to ensure headers are properly configured.
	 *
	 * @return array List of security headers being sent
	 */
	public function verify_headers(): array {
		$headers = [];
		$headers_list = headers_list();

		$security_headers = [
			'Content-Security-Policy',
			'X-Content-Type-Options',
			'X-Frame-Options',
			'X-XSS-Protection',
			'Referrer-Policy',
			'Permissions-Policy',
		];

		foreach ( $headers_list as $header ) {
			foreach ( $security_headers as $security_header ) {
				if ( stripos( $header, $security_header ) === 0 ) {
					$headers[] = $header;
					break;
				}
			}
		}

		return $headers;
	}
}
