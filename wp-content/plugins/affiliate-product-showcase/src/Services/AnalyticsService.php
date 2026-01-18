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
	private string $queue_key = 'analytics_events_queue';
	private int $batch_size = 50;

	/**
	 * Constructor
	 *
	 * @param Cache $cache Cache instance
	 */
	public function __construct( Cache $cache ) {
		$this->cache = $cache;
		
		// Register hook for background processing
		add_action( 'process_analytics_queue', [ $this, 'process_queue' ] );
	}

	/**
	 * Record a product view
	 *
	 * @param int $product_id Product ID
	 * @return void
	 */
	public function record_view( int $product_id ): void {
		$event = [
			'product_id' => $product_id,
			'metric'     => 'views',
			'timestamp'  => current_time( 'mysql' ),
		];
		
		$this->queue_event( $event );
	}

	/**
	 * Record a product click
	 *
	 * @param int $product_id Product ID
	 * @return void
	 */
	public function record_click( int $product_id ): void {
		$event = [
			'product_id' => $product_id,
			'metric'     => 'clicks',
			'timestamp'  => current_time( 'mysql' ),
		];
		
		$this->queue_event( $event );
	}

	/**
	 * Queue analytics event for background processing
	 *
	 * @param array<string,mixed> $event Event data
	 * @return void
	 */
	private function queue_event( array $event ): void {
		$queue = get_transient( $this->queue_key, [] );
		
		if ( ! is_array( $queue ) ) {
			$queue = [];
		}

		$queue[] = $event;
		
		// Process queue if batch size reached
		if ( count( $queue ) >= $this->batch_size ) {
			$this->process_queue();
		} else {
			// Store in transient for 1 hour
			set_transient( $this->queue_key, $queue, HOUR_IN_SECONDS );
			
			// Schedule background processing if not already scheduled
			if ( ! wp_next_scheduled( 'process_analytics_queue' ) ) {
				wp_schedule_single_event( time() + 60, 'process_analytics_queue' );
			}
		}
	}

	/**
	 * Process queued analytics events
	 * Called by WordPress cron or when batch size is reached
	 *
	 * @return void
	 */
	public function process_queue(): void {
		$queue = get_transient( $this->queue_key, [] );
		
		if ( empty( $queue ) || ! is_array( $queue ) ) {
			return;
		}

		// Get existing analytics data
		$data = get_option( $this->option_key, [] );
		
		if ( ! is_array( $data ) ) {
			$data = [];
		}

		// Process queued events in batch
		foreach ( $queue as $event ) {
			$product_id = $event['product_id'];
			$metric = $event['metric'];
			
			if ( ! isset( $data[ $product_id ] ) ) {
				$data[ $product_id ] = [ 'views' => 0, 'clicks' => 0 ];
			}

			// Increment metric
			$data[ $product_id ][ $metric ]++;
		}

		// Update with no autoload for performance
		update_option( $this->option_key, $data, false );
		
		// Clear queue
		delete_transient( $this->queue_key );
		
		// Invalidate summary cache
		$this->cache->delete( 'analytics_summary' );
		
		// Clear scheduled event
		$timestamp = wp_next_scheduled( 'process_analytics_queue' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'process_analytics_queue' );
		}
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

}
