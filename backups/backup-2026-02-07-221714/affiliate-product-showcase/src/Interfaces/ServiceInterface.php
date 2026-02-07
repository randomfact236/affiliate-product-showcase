<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ServiceInterface {
	public function boot(): void;
}
