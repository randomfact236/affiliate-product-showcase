<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

final class Cache {
	private string $group = 'aps';

	public function get( string $key ) {
		return wp_cache_get( $key, $this->group );
	}

	public function set( string $key, $value, int $ttl = 300 ): bool {
		return wp_cache_set( $key, $value, $this->group, $ttl );
	}

	public function delete( string $key ): bool {
		return wp_cache_delete( $key, $this->group );
	}

	/**
	 * Remember value with cache stampede protection
	 * Uses locking to prevent multiple concurrent requests from regenerating the same cache
	 */
	public function remember( string $key, callable $resolver, int $ttl = 300 ) {
		$cached = $this->get( $key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Check if another process is already regenerating this cache
		$lock_key = $key . '_lock';
		$lock_timeout = 30; // Maximum 30 seconds to hold the lock
		
		// Try to acquire lock using transients (atomic operation)
		$lock_acquired = set_transient( $lock_key, 1, $lock_timeout );
		
		if ( $lock_acquired ) {
			// We got the lock, regenerate the cache
			try {
				$value = $resolver();
				$this->set( $key, $value, $ttl );
				
				// Release the lock
				delete_transient( $lock_key );
				
				return $value;
			} catch ( \Throwable $e ) {
				// Release lock even if resolver fails
				delete_transient( $lock_key );
				throw $e;
			}
		} else {
			// Another process is regenerating, wait briefly and retry
			usleep( 500000 ); // Wait 0.5 seconds
			
			// Try to get cached value again
			$cached = $this->get( $key );
			if ( false !== $cached ) {
				return $cached;
			}
			
			// Lock still held, wait a bit more and try once more
			usleep( 1000000 ); // Wait 1 second
			
			$cached = $this->get( $key );
			if ( false !== $cached ) {
				return $cached;
			}
			
			// Lock held too long, regenerate anyway
			$value = $resolver();
			$this->set( $key, $value, $ttl );
			
			return $value;
		}
	}

	public function flush(): void {
		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( $this->group );
			return;
		}

		wp_cache_flush();
	}
}
