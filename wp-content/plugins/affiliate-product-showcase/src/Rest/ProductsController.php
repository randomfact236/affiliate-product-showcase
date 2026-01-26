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

		// Single product routes
		register_rest_route(
			$this->namespace,
			'/products/(?P<id>[\d]+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => WP_REST_Server::CREATABLE | WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => $this->get_update_args(),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		// Product action routes
		register_rest_route(
			$this->namespace,
			'/products/(?P<id>[\d]+)/trash',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'trash' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products/(?P<id>[\d]+)/restore',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'restore' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products/(?P<id>[\d]+)/delete-permanently',
			[
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_permanently' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		// Inline editing routes
		register_rest_route(
			$this->namespace,
			'/products/(?P<id>[\d]+)/field',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_field' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => $this->get_update_field_args(),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products/bulk-status',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'bulk_update_status' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => $this->get_bulk_status_args(),
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
	 * - original_price: Original price before discount (optional)
	 * - discount_percentage: Discount percentage (optional, 0-100)
	 * - currency: Currency code (optional, default USD, enum: USD/EUR/GBP/JPY/CAD/AUD)
	 * - affiliate_url: Affiliate link URL (required, URI format)
	 * - image_url: Image/logo URL (optional, URI format)
	 * - badge: Badge/ribbon text (optional, max 50 chars)
	 * - featured: Whether product is featured (optional, default: false)
	 * - rating: Product rating (optional, 0-5)
	 * - category_ids: Array of category IDs (optional)
	 * - tag_ids: Array of tag IDs (optional)
	 * - platform_requirements: Platform requirements text (optional)
	 * - version_number: Version number string (optional)
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
			'short_description' => [
				'required'          => false,
				'type'              => 'string',
				'maxLength'         => 200,
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'price' => [
				'required'          => true,
				'type'              => 'number',
				'minimum'           => 0,
				'sanitize_callback' => 'floatval',
			],
			'original_price' => [
				'required'          => false,
				'type'              => 'number',
				'minimum'           => 0,
				'sanitize_callback' => 'floatval',
			],
			'discount_percentage' => [
				'required'          => false,
				'type'              => 'number',
				'minimum'           => 0,
				'maximum'           => 100,
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
			'platform_requirements' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'version_number' => [
				'required'          => false,
				'type'              => 'string',
				'maxLength'         => 50,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get validation schema for update endpoint
	 *
	 * @return array<string, mixed> Validation schema for WordPress REST API
	 * @since 1.0.0
	 */
	private function get_update_args(): array {
		$args = $this->get_create_args();
		
		// Make all fields optional for update
		foreach ( $args as $key => $arg ) {
			$args[ $key ]['required'] = false;
		}
		
		return $args;
	}

	/**
	 * Get validation schema for update field endpoint
	 *
	 * @return array<string, mixed> Validation schema for WordPress REST API
	 * @since 1.0.0
	 */
	private function get_update_field_args(): array {
		return [
			'field_name' => [
				'required'          => true,
				'type'              => 'string',
				'enum'              => ['category', 'tags', 'ribbon', 'price', 'status'],
				'sanitize_callback' => 'sanitize_text_field',
			],
			'field_value' => [
				'required'          => true,
				'type'              => 'mixed',
				'sanitize_callback' => function( $value ) {
					if ( is_array( $value ) ) {
						return array_map( 'intval', $value );
					}
					if ( is_numeric( $value ) ) {
						return floatval( $value );
					}
					return sanitize_text_field( $value );
				},
			],
		];
	}

	/**
	 * Get validation schema for bulk status update endpoint
	 *
	 * @return array<string, mixed> Validation schema for WordPress REST API
	 * @since 1.0.0
	 */
	private function get_bulk_status_args(): array {
		return [
			'product_ids' => [
				'required'          => true,
				'type'              => 'array',
				'items'             => [
					'type' => 'integer',
				],
				'sanitize_callback' => function( $value ) {
					return array_map( 'intval', (array) $value );
				},
			],
			'status' => [
				'required'          => true,
				'type'              => 'string',
				'enum'              => ['publish', 'draft'],
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get single product
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with product data or error
	 * @since 1.0.0
	 *
	 * @route GET /affiliate-showcase/v1/products/{id}
	 */
	public function get_item( WP_REST_Request $request ): WP_REST_Response {
		$product_id = $request->get_param( 'id' );
		
		if ( empty( $product_id ) ) {
			return $this->respond( [
				'message' => __( 'Product ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_id',
			], 400 );
		}

		$product = $this->product_service->get_product( (int) $product_id );
		
		if ( null === $product ) {
			return $this->respond( [
				'message' => __( 'Product not found.', 'affiliate-product-showcase' ),
				'code'    => 'product_not_found',
			], 404 );
		}

		return $this->respond( $product->to_array(), 200 );
	}

	/**
	 * Update a product
	 *
	 * @param WP_REST_Request $request Request object containing product data
	 * @return WP_REST_Response Response with updated product or error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/products/{id}
	 */
	public function update( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$product_id = $request->get_param( 'id' );
		
		if ( empty( $product_id ) ) {
			return $this->respond( [
				'message' => __( 'Product ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_id',
			], 400 );
		}

		// Verify product exists
		$existing_product = $this->product_service->get_product( (int) $product_id );
		if ( null === $existing_product ) {
			return $this->respond( [
				'message' => __( 'Product not found.', 'affiliate-product-showcase' ),
				'code'    => 'product_not_found',
			], 404 );
		}

		try {
			// Merge existing product data with updates
			$updates = array_merge( $existing_product->to_array(), $request->get_params() );
			$updates['id'] = (int) $product_id;
			
			$product = $this->product_service->create_or_update( $updates );
			return $this->respond( $product->to_array(), 200 );
			
		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			error_log(sprintf(
				'[APS] Product update failed: %s in %s:%d',
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			));
			
			return $this->respond([
				'message' => __('Failed to update product', 'affiliate-product-showcase'),
				'code' => 'product_update_error',
			], 400);
		}
	}

	/**
	 * Delete a product (move to trash)
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route DELETE /affiliate-showcase/v1/products/{id}
	 */
	public function delete( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$product_id = $request->get_param( 'id' );
		
		if ( empty( $product_id ) ) {
			return $this->respond( [
				'message' => __( 'Product ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_id',
			], 400 );
		}

		$existing_product = $this->product_service->get_product( (int) $product_id );
		if ( null === $existing_product ) {
			return $this->respond( [
				'message' => __( 'Product not found.', 'affiliate-product-showcase' ),
				'code'    => 'product_not_found',
			], 404 );
		}

		try {
			$result = wp_trash_post( (int) $product_id );
			
			if ( ! $result ) {
				return $this->respond( [
					'message' => __( 'Failed to move product to trash.', 'affiliate-product-showcase' ),
					'code'    => 'trash_failed',
				], 500 );
			}
			
			return $this->respond( [
				'message' => __( 'Product moved to trash successfully.', 'affiliate-product-showcase' ),
				'code'    => 'success',
			], 200 );
			
		} catch ( \Throwable $e ) {
			error_log(sprintf('[APS] Product delete failed: %s', $e->getMessage()));
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}

	/**
	 * Restore product from trash
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/products/{id}/restore
	 */
	public function restore( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$product_id = $request->get_param( 'id' );
		
		if ( empty( $product_id ) ) {
			return $this->respond( [
				'message' => __( 'Product ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_id',
			], 400 );
		}

		try {
			$result = wp_untrash_post( (int) $product_id );
			
			if ( ! $result ) {
				return $this->respond( [
					'message' => __( 'Failed to restore product from trash.', 'affiliate-product-showcase' ),
					'code'    => 'restore_failed',
				], 500 );
			}
			
			$product = $this->product_service->get_product( (int) $product_id );
			$product_array = $product ? $product->to_array() : null;
			
			return $this->respond( [
				'message' => __( 'Product restored successfully.', 'affiliate-product-showcase' ),
				'code'    => 'success',
				'product'  => $product_array,
			], 200 );
			
		} catch ( \Throwable $e ) {
			error_log(sprintf('[APS] Product restore failed: %s', $e->getMessage()));
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}

	/**
	 * Delete product permanently
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route DELETE /affiliate-showcase/v1/products/{id}/delete-permanently
	 */
	public function delete_permanently( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$product_id = $request->get_param( 'id' );
		
		if ( empty( $product_id ) ) {
			return $this->respond( [
				'message' => __( 'Product ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_id',
			], 400 );
		}

		$existing_product = $this->product_service->get_product( (int) $product_id );
		if ( null === $existing_product ) {
			return $this->respond( [
				'message' => __( 'Product not found.', 'affiliate-product-showcase' ),
				'code'    => 'product_not_found',
			], 404 );
		}

		try {
			$result = wp_delete_post( (int) $product_id, true );
			
			if ( ! $result ) {
				return $this->respond( [
					'message' => __( 'Failed to delete product permanently.', 'affiliate-product-showcase' ),
					'code'    => 'delete_permanently_failed',
				], 500 );
			}
			
			return $this->respond( [
				'message' => __( 'Product deleted permanently.', 'affiliate-product-showcase' ),
				'code'    => 'success',
			], 200 );
			
		} catch ( \Throwable $e ) {
			error_log(sprintf('[APS] Product permanent delete failed: %s', $e->getMessage()));
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}

	/**
	 * Trash product (move to trash)
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/products/{id}/trash
	 */
	public function trash( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$product_id = $request->get_param( 'id' );
		
		if ( empty( $product_id ) ) {
			return $this->respond( [
				'message' => __( 'Product ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_id',
			], 400 );
		}

		$existing_product = $this->product_service->get_product( (int) $product_id );
		if ( null === $existing_product ) {
			return $this->respond( [
				'message' => __( 'Product not found.', 'affiliate-product-showcase' ),
				'code'    => 'product_not_found',
			], 404 );
		}

		try {
			$result = wp_trash_post( (int) $product_id );
			
			if ( ! $result ) {
				return $this->respond( [
					'message' => __( 'Failed to move product to trash.', 'affiliate-product-showcase' ),
					'code'    => 'trash_failed',
				], 500 );
			}
			
			return $this->respond( [
				'message' => __( 'Product moved to trash successfully.', 'affiliate-product-showcase' ),
				'code'    => 'success',
			], 200 );
			
		} catch ( \Throwable $e ) {
			error_log(sprintf('[APS] Product trash failed: %s', $e->getMessage()));
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
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

	/**
	 * Update a single field of a product
	 *
	 * Handles inline editing of specific fields:
	 * - category: Single category ID
	 * - tags: Array of tag IDs
	 * - ribbon: Ribbon ID or null
	 * - price: Numeric price value
	 * - status: 'publish' or 'draft'
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with updated product or error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/products/{id}/field
	 */
	public function update_field( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$product_id = $request->get_param( 'id' );
		$field_name = $request->get_param( 'field_name' );
		$field_value = $request->get_param( 'field_value' );

		if ( empty( $product_id ) ) {
			return $this->respond( [
				'message' => __( 'Product ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_id',
			], 400 );
		}

		// Verify product exists
		$existing_product = $this->product_service->get_product( (int) $product_id );
		if ( null === $existing_product ) {
			return $this->respond( [
				'message' => __( 'Product not found.', 'affiliate-product-showcase' ),
				'code'    => 'product_not_found',
			], 404 );
		}

		try {
			// Prepare update data based on field type
			$update_data = ['id' => (int) $product_id];

			switch ( $field_name ) {
				case 'category':
					$category_id = ! empty( $field_value ) ? (int) $field_value : null;
					$update_data['category_ids'] = $category_id ? [$category_id] : [];
					break;

				case 'tags':
					$update_data['tag_ids'] = is_array( $field_value ) ? $field_value : [];
					break;

				case 'ribbon':
					$ribbon_id = ! empty( $field_value ) ? (int) $field_value : null;
					$update_data['badge'] = $ribbon_id ? (string) $ribbon_id : '';
					break;

				case 'price':
					$price = floatval( $field_value );
					if ( $price < 0 ) {
						return $this->respond( [
							'message' => __( 'Price must be a positive number.', 'affiliate-product-showcase' ),
							'code'    => 'invalid_price',
						], 400 );
					}
					$update_data['price'] = $price;
					
					// Recalculate discount percentage if original price exists
					if ( ! empty( $existing_product->original_price ) && $existing_product->original_price > 0 ) {
						$discount = ( ( $existing_product->original_price - $price ) / $existing_product->original_price ) * 100;
						$update_data['discount_percentage'] = round( max( 0, $discount ), 2 );
					}
					break;

				case 'status':
					if ( ! in_array( $field_value, ['publish', 'draft'], true ) ) {
						return $this->respond( [
							'message' => __( 'Invalid status. Must be "publish" or "draft".', 'affiliate-product-showcase' ),
							'code'    => 'invalid_status',
						], 400 );
					}
					
					// Update WordPress post status
					$post_data = [
						'ID'          => (int) $product_id,
						'post_status' => $field_value,
					];
					wp_update_post( $post_data );
					break;

				default:
					return $this->respond( [
						'message' => __( 'Invalid field name.', 'affiliate-product-showcase' ),
						'code'    => 'invalid_field',
					], 400 );
			}

			// Update product
			$product = $this->product_service->create_or_update( $update_data );
			
			return $this->respond( [
				'message' => __( 'Product updated successfully.', 'affiliate-product-showcase' ),
				'code'    => 'success',
				'product'  => $product->to_array(),
			], 200 );

		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			error_log( sprintf(
				'[APS] Field update failed: %s in %s:%d',
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			) );

			return $this->respond( [
				'message' => __( 'Failed to update product.', 'affiliate-product-showcase' ),
				'code'    => 'update_error',
			], 400 );

		} catch ( \Throwable $e ) {
			error_log( sprintf( '[APS] Unexpected error in field update: %s', $e->getMessage() ) );

			return $this->respond( [
				'message' => __( 'An unexpected error occurred.', 'affiliate-product-showcase' ),
				'code'    => 'server_error',
			], 500 );
		}
	}

	/**
	 * Bulk update product status
	 *
	 * Updates status for multiple products at once.
	 * Useful for bulk publish/draft operations.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/products/bulk-status
	 */
	public function bulk_update_status( WP_REST_Request $request ): WP_REST_Response {
		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$product_ids = $request->get_param( 'product_ids' );
		$status = $request->get_param( 'status' );

		if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
			return $this->respond( [
				'message' => __( 'Product IDs are required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_product_ids',
			], 400 );
		}

		if ( ! in_array( $status, ['publish', 'draft'], true ) ) {
			return $this->respond( [
				'message' => __( 'Invalid status. Must be "publish" or "draft".', 'affiliate-product-showcase' ),
				'code'    => 'invalid_status',
			], 400 );
		}

		try {
			$success_count = 0;
			$failed_count = 0;
			$failed_ids = [];

			foreach ( $product_ids as $product_id ) {
				// Verify product exists
				$existing_product = $this->product_service->get_product( (int) $product_id );
				if ( null === $existing_product ) {
					$failed_count++;
					$failed_ids[] = $product_id;
					continue;
				}

				// Update WordPress post status
				$post_data = [
					'ID'          => (int) $product_id,
					'post_status' => $status,
				];

				$result = wp_update_post( $post_data, true );

				if ( is_wp_error( $result ) ) {
					$failed_count++;
					$failed_ids[] = $product_id;
				} else {
					$success_count++;
				}
			}

			if ( $failed_count > 0 ) {
				return $this->respond( [
					'message' => sprintf(
						/* translators: %1$d: success count, %2$d: failed count */
						__( 'Updated %1$d products. %2$d failed.', 'affiliate-product-showcase' ),
						$success_count,
						$failed_count
					),
					'code'         => 'partial_success',
					'success_count' => $success_count,
					'failed_count'  => $failed_count,
					'failed_ids'    => $failed_ids,
				], 207 ); // 207 Multi-Status
			}

			return $this->respond( [
				'message'       => sprintf(
					/* translators: %d: success count */
					_n( 'Updated %d product successfully.', 'Updated %d products successfully.', $success_count, 'affiliate-product-showcase' ),
					$success_count
				),
				'code'          => 'success',
				'success_count' => $success_count,
			], 200 );

		} catch ( \Throwable $e ) {
			error_log( sprintf( '[APS] Bulk status update failed: %s', $e->getMessage() ) );

			return $this->respond( [
				'message' => __( 'An unexpected error occurred.', 'affiliate-product-showcase' ),
				'code'    => 'server_error',
			], 500 );
		}
	}
}
