<?php

namespace AffiliateProductShowcase\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

abstract class RestController {
	protected string $namespace = Constants::REST_NAMESPACE;

	abstract public function register_routes(): void;

	protected function permissions_check(): bool {
		return current_user_can( 'manage_options' );
	}

	protected function respond( $data, int $status = 200 ): \WP_REST_Response {
		return new \WP_REST_Response( $data, $status );
	}
}
