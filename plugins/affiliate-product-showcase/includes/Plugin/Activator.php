<?php

namespace AffiliateProductShowcase\Plugin;

class Activator {
	public static function activate() {
		if ( false === get_option( 'aps_settings', false ) ) {
			add_option( 'aps_settings', array() );
		}
	}
}
