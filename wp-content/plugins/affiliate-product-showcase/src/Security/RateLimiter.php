<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rate Limiter
 *
 * Implements rate limiting for REST API endpoints to prevent abuse.
 * Uses transients to track request counts per IP.
 *
 * @package AffiliateProductShowcase
 * @subpackage Security
 * @since 1.0.0
 */
final class RateLimiter {
	/**
	 * @var string Rate limit option key
	 */
	private const OPTION_KEY = 'aps_rate_limit_settings';
	
	/**
	 * @var int Default rate limit (requests per hour)
	 */
	private const DEFAULT_LIMIT = 100;
	
	/**
	 * @var int Rate limit window in seconds
	 */
	private const WINDOW = 3600; // 1 hour

	/**
	 * Check if request is allowed.
	 *
	 * @param string $endpoint Endpoint identifier
	 * @param int $limit Maximum requests per window
	 * @return bool True if allowed, false if rate limited
	 */
	public function check( string $endpoint, int $limit = self::DEFAULT_LIMIT ): bool {
		$ip = $this->get_client_ip();
		$key = $this->get_key( $endpoint, $ip );
		
		// Get current count
		$count = get_transient( $key );
		
		if ( false === $count ) {
			// First request in window
			set_transient( $key, 1, self::WINDOW );
			return true;
		}
		
		if ( $count >= $limit ) {
			// Rate limit exceeded
			return false;
		}
		
		// Increment count
		set_transient( $key, $count + 1, self::WINDOW );
		
		return true;
	}

	/**
	 * Get remaining requests for current IP.
	 *
	 * @param string $endpoint Endpoint identifier
	 * @param int $limit Maximum requests per window
	 * @return int Remaining requests
	 */
	public function get_remaining( string $endpoint, int $limit = self::DEFAULT_LIMIT ): int {
		$ip = $this->get_client_ip();
		$key = $this->get_key( $endpoint, $ip );
		
		$count = get_transient( $key );
		
		if ( false === $count ) {
			return $limit;
		}
		
		return max( 0, $limit - $count );
	}

	/**
	 * Get rate limit reset time.
	 *
	 * @param string $endpoint Endpoint identifier
	 * @return int Unix timestamp of reset time
	 */
	public function get_reset_time( string $endpoint ): int {
		$ip = $this->get_client_ip();
		$key = $this->get_key( $endpoint, $ip );
		
		$transient_timeout = get_option( '_transient_timeout_' . $key );
		
		return $transient_timeout ? (int) $transient_timeout : time() + self::WINDOW;
	}

	/**
	 * Reset rate limit for IP.
	 *
	 * Useful for testing or manual intervention.
	 *
	 * @param string $endpoint Endpoint identifier
	 * @return bool True if reset successful
	 */
	public function reset( string $endpoint ): bool {
		$ip = $this->get_client_ip();
		$key = $this->get_key( $endpoint, $ip );
		
		return delete_transient( $key );
	}

	/**
	 * Generate rate limit key.
	 *
	 * @param string $endpoint Endpoint identifier
	 * @param string $ip Client IP address
	 * @return string Rate limit key
	 */
	private function get_key( string $endpoint, string $ip ): string {
		return 'aps_rate_limit_' . md5( $endpoint . $ip );
	}

	/**
	 * Get client IP address.
	 *
	 * Handles various proxy configurations and returns the real client IP.
	 *
	 * @return string Client IP address
	 */
	private function get_client_ip(): string {
		$ip = '';
		
		// Check for forwarded IP (behind proxy)
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		// Handle multiple IPs in X-Forwarded-For
		if ( false !== strpos( $ip, ',' ) ) {
			$ip = trim( explode( ',', $ip )[0] );
		}
		
		// Validate IP
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}
		
		return '0.0.0.0'; // Fallback
	}

	/**
	 * Get rate limit headers for response.
	 *
	 * @param string $endpoint Endpoint identifier
	 * @param int $limit Maximum requests per window
	 * @return array Headers for rate limiting
	 */
	public function get_headers( string $endpoint, int $limit = self::DEFAULT_LIMIT ): array {
		return [
			'X-RateLimit-Limit'     => (string) $limit,
			'X-RateLimit-Remaining' => (string) $this->get_remaining( $endpoint, $limit ),
			'X-RateLimit-Reset'     => (string) $this->get_reset_time( $endpoint ),
		];
	}
}
