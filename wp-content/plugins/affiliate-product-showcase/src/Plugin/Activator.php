<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {
	public static function activate(): void {
		( new \AffiliateProductShowcase\Services\ProductService() )->register_post_type();
		update_option( Constants::PREFIX . 'plugin_version', Constants::VERSION );
		flush_rewrite_rules();
	}
}
