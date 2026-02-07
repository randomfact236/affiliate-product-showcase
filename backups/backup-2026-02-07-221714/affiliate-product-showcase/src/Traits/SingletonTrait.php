<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait SingletonTrait {
	private static self $instance;

	public static function instance(): self {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __clone(): void {}

	public function __wakeup(): void {
		throw new \RuntimeException( 'Cannot unserialize singleton' );
	}
}
