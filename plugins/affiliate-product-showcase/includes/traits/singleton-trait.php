<?php

namespace AffiliateProductShowcase\Traits;

trait SingletonTrait {
	/** @var static|null */
	protected static $instance;

	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}
}
