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
		try {
			$data = $this->analytics_service->summary();
			return $this->respond($data);
			
		} catch (\Throwable $e) {
			// Log full error internally
			error_log('[APS] Analytics summary failed: ' . $e->getMessage());
			
			// Return safe message to client
			return $this->respond([
				'message' => __('Failed to retrieve analytics', 'affiliate-product-showcase'),
				'code' => 'analytics_error',
			], 500);
		}
	}
}
