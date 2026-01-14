<?php

namespace AffiliateProductShowcase\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Services\ProductService;
use WP_REST_Server;

final class ProductsController extends RestController {
	public function __construct( private ProductService $product_service ) {}

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

	public function list( \WP_REST_Request $request ): \WP_REST_Response {
		$products = $this->product_service->get_products( [
			'per_page' => (int) $request->get_param( 'per_page' ) ?: 12,
		] );

		return $this->respond( array_map( fn( $p ) => $p->to_array(), $products ) );
	}

	public function create( \WP_REST_Request $request ): \WP_REST_Response {
		try {
			$product = $this->product_service->create_or_update( $request->get_json_params() ?? [] );
			return $this->respond( $product->to_array(), 201 );
			
		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			// Log full error internally (includes details)
			error_log(sprintf(
				'[APS] Product creation failed: %s in %s:%d',
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			));
			
			// Return safe message to client
			return $this->respond([
				'message' => __('Failed to create product', 'affiliate-product-showcase'),
				'code' => 'product_creation_error',
			], 400);
			
		} catch ( \Throwable $e ) {
			// Catch-all for unexpected errors
			error_log('[APS] Unexpected error in product creation: ' . $e->getMessage());
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}
}
