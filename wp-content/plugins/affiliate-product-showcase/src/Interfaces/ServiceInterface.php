<?php

namespace AffiliateProductShowcase\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ServiceInterface {
	public function boot(): void;
}
