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
					'args'                => $this->get_list_args(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => $this->get_create_args(),
				],
			]
		);
	}

	/**
	 * Get validation schema for list endpoint
	 */
	private function get_list_args(): array {
		return [
			'per_page' => [
				'type'              => 'integer',
				'default'           => 12,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			],
		];
	}

	/**
	 * Get validation schema for create endpoint
	 */
	private function get_create_args(): array {
		return [
			'title'       => [
				'required'          => true,
				'type'              => 'string',
				'minLength'         => 1,
				'maxLength'         => 200,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'description' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			],
			'price'       => [
				'required'          => true,
				'type'              => 'number',
				'minimum'           => 0,
				'sanitize_callback' => 'floatval',
			],
			'currency'    => [
				'required'          => true,
				'type'              => 'string',
				'default'           => 'USD',
				'enum'              => ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD'],
				'sanitize_callback' => 'sanitize_text_field',
			],
			'affiliate_url' => [
				'required'          => true,
				'type'              => 'string',
				'format'            => 'uri',
				'sanitize_callback' => 'esc_url_raw',
			],
			'image_url'   => [
				'required'          => false,
				'type'              => 'string',
				'format'            => 'uri',
				'sanitize_callback' => 'esc_url_raw',
			],
			'badge'       => [
				'required'          => false,
				'type'              => 'string',
				'maxLength'         => 50,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'rating'      => [
				'required'          => false,
				'type'              => 'number',
				'minimum'           => 0,
				'maximum'           => 5,
				'sanitize_callback' => 'floatval',
			],
		];
	}

	public function list( \WP_REST_Request $request ): \WP_REST_Response {
		// Parameters are already validated by REST API args
		$per_page = $request->get_param( 'per_page' );

		$products = $this->product_service->get_products( [
			'per_page' => $per_page,
		] );

		return $this->respond( array_map( fn( $p ) => $p->to_array(), $products ) );
	}

	public function create( \WP_REST_Request $request ): \WP_REST_Response {
		try {
			// Parameters are already validated by REST API args
			$product = $this->product_service->create_or_update( $request->get_params() );
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
