<?php

namespace AffiliateProductShowcase\Cache;

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

	public function remember( string $key, callable $resolver, int $ttl = 300 ) {
		$cached = $this->get( $key );
		if ( false !== $cached ) {
			return $cached;
		}

		$value = $resolver();
		$this->set( $key, $value, $ttl );

		return $value;
	}

	public function flush(): void {
		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( $this->group );
			return;
		}

		wp_cache_flush();
	}
}
