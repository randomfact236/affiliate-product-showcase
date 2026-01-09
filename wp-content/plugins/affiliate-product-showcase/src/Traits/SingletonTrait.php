<?php

namespace AffiliateProductShowcase\Traits;

trait SingletonTrait {
	private static $instance;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __clone() {}
	public function __wakeup() {
		throw new \RuntimeException( 'Cannot unserialize singleton' );
	}
}
