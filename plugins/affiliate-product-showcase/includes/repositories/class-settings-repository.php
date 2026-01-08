<?php

namespace AffiliateProductShowcase\Repositories;

class SettingsRepository {
	public function get_all() {
		return get_option( 'aps_settings', array() );
	}
}
