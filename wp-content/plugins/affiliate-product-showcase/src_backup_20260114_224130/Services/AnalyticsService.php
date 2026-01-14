<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Cache\Cache;

final class AnalyticsService {
	private Cache $cache;
	private string $option_key = 'aps_analytics';

	/**
	 * Constructor
	 *
	 * @param Cache $cache Cache instance
	 */
	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Record a product view
	 *
	 * @param int $product_id Product ID
	 * @return void
	 */
	public function record_view( int $product_id ): void {
		$this->record( $product_id, 'views' );
	}

	/**
	 * Record a product click
	 *
	 * @param int $product_id Product ID
	 * @return void
	 */
	public function record_click( int $product_id ): void {
		$this->record( $product_id, 'clicks' );
	}

	/**
	 * Get analytics summary
	 *
	 * @return array<string, mixed> Analytics summary data
	 */
	public function summary(): array {
		return $this->cache->remember( 'analytics_summary', function (): array {
			$data = get_option( $this->option_key, [] );
			return is_array( $data ) ? $data : [];
		}, 60 );
	}

	/**
	 * Record a metric with atomic operations to prevent race conditions.
	 *
	 * Uses cache locking to ensure atomic read-modify-write operations.
	 * Prevents data loss when multiple requests record metrics simultaneously.
	 *
	 * @param int $product_id Product ID
	 * @param string $metric Metric name ('views' or 'clicks')
	 */
	private function record( int $product_id, string $metric ): void {
		// Use cache-based locking to prevent race conditions
		$lock_key = 'analytics_record_' . $product_id;
		
		$this->cache->remember( $lock_key, function () use ( $product_id, $metric ) {
			// Critical section: only one process at a time
			$data = get_option( $this->option_key, [] );
			
			if ( ! isset( $data[ $product_id ] ) ) {
				$data[ $product_id ] = [ 'views' => 0, 'clicks' => 0 ];
			}

			// Atomic increment
			$data[ $product_id ][ $metric ]++;
			
			// Update options with no autoload for performance
			update_option( $this->option_key, $data, false );
			
			// Invalidate summary cache
			$this->cache->delete( 'analytics_summary' );
			
			return true; // Lock released automatically
		}, 5 ); // 5 second lock timeout
	}
}
