<?php

namespace AffiliateProductShowcase\Plugin;

use AffiliateProductShowcase\Plugin\Loader;

class Plugin {
	/** @var Loader */
	private $loader;

	public function __construct() {
		$this->loader = new Loader();
		$this->define_i18n();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	public function run() {
		$this->loader->run();
	}

	private function define_i18n() {
		$this->loader->add_action(
			'init',
			static function () {
				load_plugin_textdomain( 'affiliate-product-showcase', false, dirname( APS_PLUGIN_BASENAME ) . '/languages' );
			}
		);
	}

	private function define_admin_hooks() {
		$admin = new \AffiliateProductShowcase\Admin\Admin();
		$admin->register( $this->loader );
	}

	private function define_public_hooks() {
		$public = new \AffiliateProductShowcase\PublicSite\PublicSite();
		$public->register( $this->loader );
	}
}
