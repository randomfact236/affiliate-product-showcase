<?php

namespace AffiliateProductShowcase\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AffiliateLink {
	public function __construct(
		public string $url,
		public ?string $merchant = null,
		public ?string $tracking_id = null
	) {}
}
