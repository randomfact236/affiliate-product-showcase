<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 *
	 * @param SettingsRepository $settings_repository Settings repository
	 */
	public function __construct( SettingsRepository $settings_repository ) {
		$this->settings_repository = $settings_repository;
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
		$parsed = wp_parse_url( $url );
		$parts = $this->sanitizeUrlParts( $parsed );
		return $this->rebuildSanitizedUrl( $parts );
	}

	/**
	 * Sanitize individual URL parts
	 *
	 * @param array<string, mixed> $parsed Parsed URL components
	 * @return array<string, string> Sanitized URL parts
	 */
	private function sanitizeUrlParts( array $parsed ): array {
		return [
			'scheme'   => $this->sanitizeScheme( $parsed['scheme'] ?? null ),
			'host'     => $this->sanitizeHost( $parsed['host'] ?? '' ),
			'path'     => $this->sanitizePath( $parsed['path'] ?? '' ),
			'query'    => $this->sanitizeQuery( $parsed['query'] ?? '' ),
			'fragment' => $this->sanitizeFragment( $parsed['fragment'] ?? '' ),
		];
	}

	/**
	 * Rebuild sanitized URL from parts
	 *
	 * @param array<string, string> $parts Sanitized URL parts
	 * @return string Reconstructed URL
	 */
	private function rebuildSanitizedUrl( array $parts ): string {
		$sanitized = $parts['scheme'] . $parts['host'] . $parts['path'];
		
		if ( ! empty( $parts['query'] ) ) {
			$sanitized .= '?' . $parts['query'];
		}
		
		if ( ! empty( $parts['fragment'] ) ) {
			$sanitized .= '#' . $parts['fragment'];
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize URL scheme
	 *
	 * @param string|null $scheme URL scheme
	 * @return string Sanitized scheme with protocol separator
	 */
	private function sanitizeScheme( ?string $scheme ): string {
		return $scheme ? esc_url_raw( $scheme . '://' ) : 'https://';
	}

	/**
	 * Sanitize URL host
	 *
	 * @param string $host URL host
	 * @return string Sanitized host
	 */
	private function sanitizeHost( string $host ): string {
		return sanitize_text_field( $host );
	}

	/**
	 * Sanitize URL path
	 *
	 * @param string $path URL path
	 * @return string Sanitized path
	 */
	private function sanitizePath( string $path ): string {
		return sanitize_text_field( $path );
	}

	/**
	 * Sanitize URL query string
	 *
	 * @param string $query URL query string
	 * @return string Sanitized query string
	 */
	private function sanitizeQuery( string $query ): string {
		return $this->sanitize_query_string( $query );
	}

	/**
	 * Sanitize URL fragment
	 *
	 * @param string $fragment URL fragment
	 * @return string Sanitized fragment
	 */
	private function sanitizeFragment( string $fragment ): string {
		return sanitize_text_field( $fragment );
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

	/**
	 * Get tracking URL for a product.
	 *
	 * @param int $product_id Product ID
	 * @return string Tracking URL
	 * @throws \InvalidArgumentException If product not found or URL is invalid
	 */
	public function get_tracking_url( int $product_id ): string {
		// Get product from database
		$product = get_post( $product_id );
		
		if ( ! $product || 'aps_product' !== $product->post_type ) {
			throw new \InvalidArgumentException( __( 'Product not found.', 'affiliate-product-showcase' ) );
		}

		// Get affiliate URL from post meta
		$affiliate_url = get_post_meta( $product_id, 'affiliate_url', true );
		
		if ( empty( $affiliate_url ) ) {
			throw new \InvalidArgumentException( __( 'Product affiliate URL is not set.', 'affiliate-product-showcase' ) );
		}

		// Build and return tracking URL
		$affiliate_link = $this->build_link( $affiliate_url );
		
		return $affiliate_link->get_url();
	}
}
