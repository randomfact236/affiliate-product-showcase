<?php
/**
 * Products REST API Controller
 *
 * Handles REST API endpoints for product management including:
 * - Listing products with pagination and filtering
 * - Creating new products with validation
 * - Rate limiting for API endpoints
 * - CSRF protection via nonce verification
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Security\RateLimiter;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Products REST API Controller
 *
 * Handles REST API endpoints for product management including:
 * - Listing products with pagination and filtering
 * - Creating new products with validation
 * - Rate limiting for API endpoints
 * - CSRF protection via nonce verification
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 * @author Development Team
 */
final class ProductsController extends RestController {
	/**
	 * Rate limiter instance
	 *
	 * @var RateLimiter
	 * @since 1.0.0
	 */
	private RateLimiter $rate_limiter;

	/**
	 * Constructor
	 *
	 * Initializes rate limiter for API endpoint protection.
	 *
	 * @param ProductService $product_service Product service for business logic
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct(
		private ProductService $product_service
	) {
		$this->rate_limiter = new RateLimiter();
	}

	/**
	 * Register REST API routes
	 *
	 * Registers /products endpoints for:
	 * - GET /products - List products with pagination
	 * - POST /products - Create new product
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action rest_api_init
	 */
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
	 *
	 * Defines query parameters for products list:
	 * - per_page: Number of products per page (1-100, default 12)
	 *
	 * @return array<string, mixed> Validation schema for WordPress REST API
	 * @since 1.0.0
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
	 *
	 * Defines parameters for product creation:
	 * - title: Product title (required, max 200 chars)
	 * - description: Product description (optional)
	 * - price: Product price (required, min 0)
	 * - currency: Currency code (optional, default USD, enum: USD/EUR/GBP/JPY/CAD/AUD)
	 * - affiliate_url: Affiliate link URL (required, URI format)
	 * - image_url: Image/logo URL (optional, URI format)
	 * - badge: Badge/ribbon text (optional, max 50 chars)
	 * - featured: Whether product is featured (optional, default: false)
	 * - rating: Product rating (optional, 0-5)
	 * - category_ids: Array of category IDs (optional)
	 * - tag_ids: Array of tag IDs (optional)
	 *
	 * @return array<string, mixed> Validation schema for WordPress REST API
	 * @since 1.0.0
	 */
	private function get_create_args(): array {
		return [
			'title' => [
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
			'price' => [
				'required'          => true,
				'type'              => 'number',
				'minimum'           => 0,
				'sanitize_callback' => 'floatval',
			],
			'currency' => [
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
			'image_url' => [
				'required'          => false,
				'type'              => 'string',
				'format'            => 'uri',
				'sanitize_callback' => 'esc_url_raw',
			],
			'badge' => [
				'required'          => false,
				'type'              => 'string',
				'maxLength'         => 50,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'featured' => [
				'required'          => false,
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => function( $value ) {
					return (bool) rest_sanitize_boolean( $value );
				},
			],
			'rating' => [
				'required'          => false,
				'type'              => 'number',
				'minimum'           => 0,
				'maximum'           => 5,
				'sanitize_callback' => 'floatval',
			],
			'category_ids' => [
				'required'          => false,
				'type'              => 'array',
				'items'             => [
					'type' => 'integer',
				],
				'sanitize_callback' => function( $value ) {
					return array_map( 'intval', (array) $value );
				},
			],
			'tag_ids' => [
				'required'          => false,
				'type'              => 'array',
				'items'             => [
					'type' => 'integer',
				],
				'sanitize_callback' => function( $value ) {
					return array_map( 'intval', (array) $value );
				},
			],
		];
	}

	/**
	 * List products
	 *
	 * Returns paginated list of products with rate limiting.
	 * Rate limit: 60 requests/minute for public, 120 for authenticated users.
	 *
	 * @param WP_REST_Request $request Request object containing query parameters
	 * @return WP_REST_Response Response with products list or error
	 * @throws RateLimitException If rate limit is exceeded
	 * @since 1.0.0
	 *
	 * @route GET /affiliate-showcase/v1/products
	 */
	public function list( WP_REST_Request $request ): WP_REST_Response {
		// Check rate limit
		if ( ! $this->rate_limiter->check( 'products_list' ) ) {
			return $this->respond( [
				'message' => __( 'Too many requests. Please try again later.', 'affiliate-product-showcase' ),
				'code'    => 'rate_limit_exceeded',
			], 429, $this->rate_limiter->get_headers( 'products_list' ) );
		}

		// Parameters are already validated by REST API args
		$per_page = $request->get_param( 'per_page' );

		$products = $this->product_service->get_products( [
			'per_page' => $per_page,
		] );

		return $this->respond( array_map( fn( $p ) => $p->to_array(), $products ), 200, $this->rate_limiter->get_headers( 'products_list' ) );
	}

	/**
	 * Create a new product
	 *
	 * Creates a new product with CSRF protection and stricter rate limiting.
	 * Rate limit: 20 requests/minute (stricter than list operations).
	 * Nonce verification required in X-WP-Nonce header.
	 *
	 * @param WP_REST_Request $request Request object containing product data
	 * @return WP_REST_Response Response with created product or error
	 * @throws ValidationException If product data is invalid
	 * @throws RateLimitException If rate limit is exceeded
	 * @throws PluginException If product creation fails
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/products
	 */
	public function create( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		// Check rate limit (stricter for create operations)
		if ( ! $this->rate_limiter->check( 'products_create', 20 ) ) {
			return $this->respond( [
				'message' => __( 'Too many creation requests. Please try again later.', 'affiliate-product-showcase' ),
				'code'    => 'rate_limit_exceeded',
			], 429, $this->rate_limiter->get_headers( 'products_create', 20 ) );
		}

		try {
			// Parameters are already validated by REST API args
			$product = $this->product_service->create_or_update( $request->get_params() );
			return $this->respond( $product->to_array(), 201, $this->rate_limiter->get_headers( 'products_create', 20 ) );
			
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
			error_log(sprintf('[APS] Unexpected error in product creation: %s', $e->getMessage()));
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}
}
