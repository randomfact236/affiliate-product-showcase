<?php

namespace AffiliateProductShowcase\PublicSite;

use AffiliateProductShowcase\Plugin\Loader;

class PublicSite {
	public function register( Loader $loader ) {
		$shortcodes = new Shortcodes();
		$shortcodes->register( $loader );
	}
}
