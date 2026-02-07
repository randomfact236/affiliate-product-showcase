<?php
/**
 * URL Validator
 *
 * Provides consistent URL validation across the plugin, particularly
 * for image URLs and external resources.
 *
 * @package AffiliateProductShowcase\Validators
 * @since 2.1.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Validators;

/**
 * URL Validator
 *
 * Centralized URL validation to ensure consistency across different
 * components of the plugin.
 *
 * @package AffiliateProductShowcase\Validators
 * @since 2.1.0
 */
final class UrlValidator {
	/**
	 * Allowed URL schemes
	 *
	 * @var array<string>
	 */
	private const ALLOWED_SCHEMES = [ 'http', 'https' ];

	/**
	 * Validate and sanitize image URL
	 *
	 * Performs comprehensive validation including:
	 * - URL sanitization
	 * - Structure validation
	 * - Protocol validation (HTTP/HTTPS only)
	 * - Host validation
	 *
	 * @param string $url URL to validate
	 * @return string|null Sanitized URL or null if invalid
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * $url = UrlValidator::validate_image_url( 'https://example.com/image.jpg' );
	 * if ( $url ) {
	 *     // Valid URL
	 * }
	 * ```
	 */
	public static function validate_image_url( string $url ): ?string {
		if ( empty( $url ) ) {
			return null;
		}

		// Sanitize first
		$clean_url = esc_url_raw( $url );

		if ( empty( $clean_url ) ) {
			return null;
		}

		// Parse URL
		$parsed = wp_parse_url( $clean_url );

		// Check structure
		if ( ! $parsed || empty( $parsed['scheme'] ) || empty( $parsed['host'] ) ) {
			return null;
		}

		// Check protocol
		if ( ! in_array( $parsed['scheme'], self::ALLOWED_SCHEMES, true ) ) {
			return null;
		}

		return $clean_url;
	}

	/**
	 * Validate URL with error message
	 *
	 * Same as validate_image_url() but sets an error message
	 * when validation fails.
	 *
	 * @param string $url URL to validate
	 * @param string &$error_message Error message (passed by reference)
	 * @return string|null Validated URL or null with error message set
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * $error = '';
	 * $url = UrlValidator::validate_with_error( $_POST['url'], $error );
	 * if ( ! $url && ! empty( $error ) ) {
	 *     // Display error message
	 * }
	 * ```
	 */
	public static function validate_with_error( string $url, string &$error_message ): ?string {
		$validated = self::validate_image_url( $url );

		if ( null === $validated && ! empty( $url ) ) {
			$error_message = __( 'Invalid image URL. Please enter a valid HTTP or HTTPS URL.', 'affiliate-product-showcase' );
		}

		return $validated;
	}

	/**
	 * Validate URL and get result object
	 *
	 * Returns an array with validation result and error message.
	 *
	 * @param string $url URL to validate
	 * @return array{valid: bool, url: string|null, error: string} Validation result
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * $result = UrlValidator::validate_with_result( $_POST['url'] );
	 * if ( $result['valid'] ) {
	 *     $clean_url = $result['url'];
	 * } else {
	 *     echo $result['error'];
	 * }
	 * ```
	 */
	public static function validate_with_result( string $url ): array {
		$error_message = '';
		$validated = self::validate_with_error( $url, $error_message );

		return [
			'valid' => null !== $validated,
			'url'   => $validated,
			'error' => $error_message,
		];
	}

	/**
	 * Check if URL is valid without sanitizing
	 *
	 * Useful for checking if a URL is already valid.
	 *
	 * @param string $url URL to check
	 * @return bool True if valid, false otherwise
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * if ( UrlValidator::is_valid( $url ) ) {
	 *     // URL is valid
	 * }
	 * ```
	 */
	public static function is_valid( string $url ): bool {
		return null !== self::validate_image_url( $url );
	}

	/**
	 * Validate multiple URLs
	 *
	 * @param array<string> $urls Array of URLs to validate
	 * @return array<string> Array of valid URLs only
	 * @since 2.1.0
	 *
	 * @example
	 * ```php
	 * $urls = [ 'https://example.com/1.jpg', 'invalid', 'https://example.com/2.jpg' ];
	 * $valid = UrlValidator::validate_multiple( $urls );
	 * // Returns: [ 'https://example.com/1.jpg', 'https://example.com/2.jpg' ]
	 * ```
	 */
	public static function validate_multiple( array $urls ): array {
		$valid_urls = [];

		foreach ( $urls as $url ) {
			$validated = self::validate_image_url( $url );
			if ( null !== $validated ) {
				$valid_urls[] = $validated;
			}
		}

		return $valid_urls;
	}
}
