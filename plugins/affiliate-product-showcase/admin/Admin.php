<?php

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Plugin\Loader;

class Admin {
	public function register( Loader $loader ) {
		$loader->add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	public function register_menu() {
		add_options_page(
			__( 'Affiliate Product Showcase', 'affiliate-product-showcase' ),
			__( 'Affiliate Products', 'affiliate-product-showcase' ),
			'manage_options',
			'affiliate-product-showcase',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page() {
		require APS_PLUGIN_DIR . 'admin/partials/settings-page.php';
	}
}
