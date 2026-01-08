<?php

namespace AffiliateProductShowcase\PublicSite;

use AffiliateProductShowcase\Plugin\Loader;

class Shortcodes {
	public function register( Loader $loader ) {
		$loader->add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	public function register_shortcodes() {
		add_shortcode( 'affiliate_product_showcase', array( $this, 'render_showcase' ) );
	}

	public function render_showcase() {
		ob_start();
		require APS_PLUGIN_DIR . 'public/partials/product-grid.php';
		return (string) ob_get_clean();
	}
}
