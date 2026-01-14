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
		} catch ( \Throwable $e ) {
			return $this->respond( [ 'message' => $e->getMessage() ], 400 );
		}
	}
}
