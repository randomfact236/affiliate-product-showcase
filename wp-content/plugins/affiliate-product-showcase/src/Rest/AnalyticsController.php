<?php

namespace AffiliateProductShowcase\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Services\AnalyticsService;
use WP_REST_Server;

final class AnalyticsController extends RestController {
	public function __construct( private AnalyticsService $analytics_service ) {}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/analytics',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'summary' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);
	}

	public function summary(): \WP_REST_Response {
		return $this->respond( $this->analytics_service->summary() );
	}
}
