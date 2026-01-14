<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use AffiliateProductShowcase\Services\RateLimiter;
use AffiliateProductShowcase\Services\ProductService;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProductsController - Handles REST API product endpoints.
 * 
 * Provides CRUD operations for affiliate products.
 * Includes rate limiting to prevent abuse.
 * 
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 */
final class ProductsController extends RestController {
	private RateLimiter $rate_limiter;

	public function __construct(
		private ProductService $product_service,
		RateLimiter $rate_limiter
	) {
		$this->rate_limiter = $rate_limiter;
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/products',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'list' ],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);
	}

	/**
	 * List all products.
	 * 
	 * Rate limited to 100 requests per hour per IP.
	 *
	 * @param \WP_REST_Request $request The REST request
	 * @return \WP_REST_Response Response with products array
	 */
	public function list( \WP_REST_Request $request ): \WP_REST_Response {
		$ip = $this->get_client_ip();

		if ( ! $this->rate_limiter->check( $ip, 100 ) ) {
			return $this->respond( [
				'message' => __( 'Rate limit exceeded', 'affiliate-product-showcase' ),
				'retry_after' => 3600,
				'remaining' => $this->rate_limiter->get_remaining( $ip, 100 ),
			], 429 );
		}

		$products = $this->product_service->get_products( [
			'per_page' => (int) $request->get_param( 'per_page' ) ?: 12,
		] );

		return $this->respond( array_map( fn( $p ) => $p->to_array(), $products ) );
	}

	/**
	 * Create a new product.
	 * 
	 * Rate limited to 50 requests per hour per IP.
	 * Requires authentication.
	 *
	 * @param \WP_REST_Request $request The REST request
	 * @return \WP_REST_Response Response with created product or error
	 */
	public function create( \WP_REST_Request $request ): \WP_REST_Response {
		$ip = $this->get_client_ip();

		if ( ! $this->rate_limiter->check( $ip, 50 ) ) {
			return $this->respond( [
				'message' => __( 'Rate limit exceeded', 'affiliate-product-showcase' ),
				'retry_after' => 3600,
				'remaining' => $this->rate_limiter->get_remaining( $ip, 50 ),
			], 429 );
		}

		try {
			$product = $this->product_service->create_or_update( $request->get_json_params() ?? [] );
			return $this->respond( $product->to_array(), 201 );
		} catch ( \Throwable $e ) {
			return $this->respond( [ 'message' => $e->getMessage() ], 400 );
		}
	}

	/**
	 * Get client IP address.
	 * 
	 * Handles proxy headers for accurate IP detection.
	 *
	 * @return string Client IP address
	 */
	private function get_client_ip(): string {
		$headers = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = $_SERVER[ $header ];
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				return sanitize_text_field( $ip );
			}
		}

		return sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
	}
}
