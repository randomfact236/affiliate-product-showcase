<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Services\AnalyticsService;
use AffiliateProductShowcase\Security\RateLimiter;
use WP_REST_Server;

final class AnalyticsController extends RestController {
	private RateLimiter $rate_limiter;

	/**
	 * Constructor
	 *
	 * @param AnalyticsService $analytics_service Analytics service
	 */
	public function __construct( 
		private AnalyticsService $analytics_service 
	) {
		$this->rate_limiter = new RateLimiter();
	}

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
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

	/**
	 * Get analytics summary
	 *
	 * @param \WP_REST_Request $request Request object
	 * @return \WP_REST_Response Response with analytics data
	 */
	public function summary( \WP_REST_Request $request ): \WP_REST_Response {
		// Verify nonce for CSRF protection (authenticated endpoint)
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh the page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		// Check rate limit (admin endpoint, moderate limit)
		if ( ! $this->rate_limiter->check( 'analytics_summary', 60 ) ) {
			return $this->respond( [
				'message' => __( 'Too many requests. Please try again later.', 'affiliate-product-showcase' ),
				'code'    => 'rate_limit_exceeded',
			], 429, $this->rate_limiter->get_headers( 'analytics_summary', 60 ) );
		}

		try {
			$data = $this->analytics_service->summary();
			return $this->respond( $data, 200, $this->rate_limiter->get_headers( 'analytics_summary', 60 ) );
			
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
