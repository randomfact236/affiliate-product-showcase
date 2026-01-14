<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RateLimiter - Protects REST API endpoints from abuse.
 * 
 * Implements rate limiting using WordPress transients.
 * Limits requests per IP address within a time window.
 * 
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 */
final class RateLimiter {
	private const DEFAULT_LIMIT = 100; // 100 requests per hour
	private const WINDOW = 3600; // 1 hour in seconds

	/**
	 * Check if a request is allowed under rate limits.
	 *
	 * @param string $identifier Unique identifier (typically IP address)
	 * @param int $limit Maximum requests allowed in time window
	 * @return bool True if request allowed, false if rate limited
	 */
	public function check( string $identifier, int $limit = self::DEFAULT_LIMIT ): bool {
		$key = 'aps_ratelimit_' . md5( $identifier );
		$count = (int) get_transient( $key );

		if ( $count >= $limit ) {
			return false;
		}

		set_transient( $key, $count + 1, self::WINDOW );
		return true;
	}

	/**
	 * Get remaining requests allowed.
	 *
	 * @param string $identifier Unique identifier
	 * @param int $limit Maximum requests allowed
	 * @return int Number of requests remaining
	 */
	public function get_remaining( string $identifier, int $limit = self::DEFAULT_LIMIT ): int {
		$key = 'aps_ratelimit_' . md5( $identifier );
		$count = (int) get_transient( $key );
		return max( 0, $limit - $count );
	}

	/**
	 * Reset rate limit for an identifier.
	 *
	 * @param string $identifier Unique identifier
	 * @return void
	 */
	public function reset( string $identifier ): void {
		$key = 'aps_ratelimit_' . md5( $identifier );
		delete_transient( $key );
	}
}
