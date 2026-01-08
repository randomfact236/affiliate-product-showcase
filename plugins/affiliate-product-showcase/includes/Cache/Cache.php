<?php

namespace AffiliateProductShowcase\Cache;

class Cache {
	public function get( $key ) {
		return get_transient( $key );
	}

	public function set( $key, $value, $ttl = 300 ) {
		return set_transient( $key, $value, (int) $ttl );
	}
}
