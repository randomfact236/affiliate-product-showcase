<?php

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Cache\Cache;

final class AnalyticsService {
	private Cache $cache;
	private string $option_key = 'aps_analytics';

	public function __construct() {
		$this->cache = new Cache();
	}

	public function record_view( int $product_id ): void {
		$this->record( $product_id, 'views' );
	}

	public function record_click( int $product_id ): void {
		$this->record( $product_id, 'clicks' );
	}

	public function summary(): array {
		return $this->cache->remember( 'analytics_summary', function (): array {
			$data = get_option( $this->option_key, [] );
			return is_array( $data ) ? $data : [];
		}, 60 );
	}

	private function record( int $product_id, string $metric ): void {
		$data = get_option( $this->option_key, [] );
		if ( ! isset( $data[ $product_id ] ) ) {
			$data[ $product_id ] = [ 'views' => 0, 'clicks' => 0 ];
		}

		$data[ $product_id ][ $metric ]++;
		update_option( $this->option_key, $data, false );
		$this->cache->delete( 'analytics_summary' );
	}
}
