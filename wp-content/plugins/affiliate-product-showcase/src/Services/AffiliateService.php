<?php

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Models\AffiliateLink;
use AffiliateProductShowcase\Repositories\SettingsRepository;

/**
 * AffiliateService - Handles affiliate link building with strict security.
 * 
 * SECURITY GUARANTEES:
 * - No phone-home behavior
 * - No external update checks
 * - No telemetry or data collection
 * - Strict URL validation and sanitization
 * - Prevents XSS and injection attacks
 */
final class AffiliateService {
	private SettingsRepository $settings_repository;

	/**
	 * Allowed URL schemes for affiliate links
	 */
	private const ALLOWED_SCHEMES = [ 'http', 'https' ];

	/**
	 * Blocked external domains (prevent data exfiltration)
	 */
	private const BLOCKED_DOMAINS = [
		'google-analytics.com',
		'googletagmanager.com',
		'facebook.com',
		'facebook.net',
		'connect.facebook.net',
		'doubleclick.net',
		'googlesyndication.com',
		'adsystem.amazon.com',
		'c.amazon-adsystem.com',
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings_repository = new SettingsRepository();
	}

	/**
	 * Build an affiliate link with strict validation and sanitization.
	 *
	 * @param string $url The affiliate URL to process
	 * @return AffiliateLink Sanitized affiliate link object
	 * @throws \InvalidArgumentException If URL is invalid or malicious
	 */
	public function build_link( string $url ): AffiliateLink {
		// Validate input is not empty
		$url = trim( $url );
		if ( empty( $url ) ) {
			throw new \InvalidArgumentException( 'Affiliate URL cannot be empty.' );
		}

		// Strict URL validation
		$this->validate_url( $url );

		// Sanitize URL
		$sanitizedUrl = $this->sanitize_url( $url );

		// Get tracking ID and append if needed
		$settings   = $this->settings_repository->get_settings();
		$trackingId = $this->sanitize_tracking_id( $settings['affiliate_id'] ?? '' );

		$finalUrl = $sanitizedUrl;
		if ( $trackingId && false === strpos( $sanitizedUrl, $trackingId ) ) {
			$separator = false !== strpos( $sanitizedUrl, '?' ) ? '&' : '?';
			$finalUrl  = $sanitizedUrl . $separator . 'aff_id=' . rawurlencode( $trackingId );
		}

		return new AffiliateLink( $finalUrl, null, $trackingId ?: null );
	}

	/**
	 * Validate an image URL for security.
	 *
	 * @param string $url Image URL to validate
	 * @return bool True if URL is safe
	 * @throws \InvalidArgumentException If URL is external or malicious
	 */
	public function validate_image_url( string $url ): bool {
		$url = trim( $url );
		
		if ( empty( $url ) ) {
			return false;
		}

		// Must be a valid URL
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new \InvalidArgumentException( 'Invalid image URL format.' );
		}

		$parsed = wp_parse_url( $url );

		// Reject external image URLs - only allow local uploads
		if ( isset( $parsed['scheme'] ) && isset( $parsed['host'] ) ) {
			$siteHost = wp_parse_url( site_url(), PHP_URL_HOST );
			if ( $parsed['host'] !== $siteHost ) {
				throw new \InvalidArgumentException(
					'External image URLs are not allowed for security and privacy reasons. ' .
					'Please upload images to your media library.'
				);
			}
		}

		// Check for blocked domains
		$this->check_blocked_domains( $url );

		return true;
	}

	/**
	 * Validate JavaScript URL - MUST reject all external JS.
	 *
	 * @param string $url JavaScript URL to validate
	 * @return bool True if safe (only local URLs allowed)
	 * @throws \InvalidArgumentException If URL is external
	 */
	public function validate_js_url( string $url ): bool {
		$url = trim( $url );

		if ( empty( $url ) ) {
			return false;
		}

		// External JS is NEVER allowed
		if ( preg_match( '#^https?://#', $url ) ) {
			throw new \InvalidArgumentException(
				'External JavaScript URLs are strictly prohibited. ' .
				'All JavaScript must be bundled with the plugin.'
			);
		}

		// Only allow relative URLs or data URLs (inline)
		if ( ! preg_match( '#^(/|\.|data:)#', $url ) ) {
			throw new \InvalidArgumentException(
				'Invalid JavaScript URL. Only local paths are allowed.'
			);
		}

		return true;
	}

	/**
	 * Strict URL validation with multiple security checks.
	 *
	 * @param string $url URL to validate
	 * @throws \InvalidArgumentException If URL fails any security check
	 */
	private function validate_url( string $url ): void {
		// Must be a valid URL
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new \InvalidArgumentException( 'Invalid URL format.' );
		}

		$parsed = wp_parse_url( $url );

		// Check scheme
		if ( ! isset( $parsed['scheme'] ) || ! in_array( strtolower( $parsed['scheme'] ), self::ALLOWED_SCHEMES, true ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Invalid URL scheme. Only %s are allowed.',
					implode( ', ', self::ALLOWED_SCHEMES )
				)
			);
		}

		// Must have a host
		if ( ! isset( $parsed['host'] ) || empty( $parsed['host'] ) ) {
			throw new \InvalidArgumentException( 'URL must have a valid host.' );
		}

		// Check for blocked domains
		$this->check_blocked_domains( $url );

		// Prevent protocol-relative URLs (can be hijacked)
		if ( 0 === strpos( $url, '//' ) ) {
			throw new \InvalidArgumentException( 'Protocol-relative URLs are not allowed.' );
		}
	}

	/**
	 * Check if URL contains blocked domains.
	 *
	 * @param string $url URL to check
	 * @throws \InvalidArgumentException If URL contains blocked domain
	 */
	private function check_blocked_domains( string $url ): void {
		$urlLower = strtolower( $url );

		foreach ( self::BLOCKED_DOMAINS as $blockedDomain ) {
			if ( false !== strpos( $urlLower, $blockedDomain ) ) {
				throw new \InvalidArgumentException(
					sprintf(
						'URL contains blocked domain: %s. ' .
						'This domain is blocked for privacy and security reasons.',
						$blockedDomain
					)
				);
			}
		}
	}

	/**
	 * Sanitize URL to prevent XSS and injection.
	 *
	 * @param string $url URL to sanitize
	 * @return string Sanitized URL
	 */
	private function sanitize_url( string $url ): string {
		// Parse and rebuild URL safely
		$parsed = wp_parse_url( $url );

		$scheme   = isset( $parsed['scheme'] ) ? esc_url_raw( $parsed['scheme'] . '://' ) : 'https://';
		$host     = isset( $parsed['host'] ) ? sanitize_text_field( $parsed['host'] ) : '';
		$path     = isset( $parsed['path'] ) ? sanitize_text_field( $parsed['path'] ) : '';
		$query    = isset( $parsed['query'] ) ? $this->sanitize_query_string( $parsed['query'] ) : '';
		$fragment = isset( $parsed['fragment'] ) ? sanitize_text_field( $parsed['fragment'] ) : '';

		$sanitized = $scheme . $host . $path;

		if ( ! empty( $query ) ) {
			$sanitized .= '?' . $query;
		}

		if ( ! empty( $fragment ) ) {
			$sanitized .= '#' . $fragment;
		}

		return $sanitized;
	}

	/**
	 * Sanitize query string parameters.
	 *
	 * @param string $queryString Query string
	 * @return string Sanitized query string
	 */
	private function sanitize_query_string( string $queryString ): string {
		parse_str( $queryString, $params );
		
		$sanitizedParams = [];
		foreach ( $params as $key => $value ) {
			$sanitizedKey   = sanitize_key( $key );
			$sanitizedValue = is_array( $value ) 
				? array_map( 'sanitize_text_field', $value )
				: sanitize_text_field( $value );
			$sanitizedParams[ $sanitizedKey ] = $sanitizedValue;
		}

		return http_build_query( $sanitizedParams );
	}

	/**
	 * Sanitize tracking ID.
	 *
	 * @param string $trackingId Tracking ID to sanitize
	 * @return string Sanitized tracking ID
	 */
	private function sanitize_tracking_id( string $trackingId ): string {
		return sanitize_text_field( $trackingId );
	}
}
