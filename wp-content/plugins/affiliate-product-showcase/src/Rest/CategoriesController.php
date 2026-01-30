<?php
/**
 * Categories REST API Controller
 *
 * Handles REST API endpoints for category management including:
 * - Listing categories with pagination and filtering
 * - Creating new categories with validation
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

use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Security\RateLimiter;
use AffiliateProductShowcase\Factories\CategoryFactory;
use AffiliateProductShowcase\Models\Category;
use AffiliateProductShowcase\Plugin\Constants;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Categories REST API Controller
 *
 * Handles REST API endpoints for category management including:
 * - Listing categories with pagination and filtering
 * - Creating new categories with validation
 * - Rate limiting for API endpoints
 * - CSRF protection via nonce verification
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 * @author Development Team
 */
final class CategoriesController extends RestController {
	/**
	 * Rate limiter instance
	 *
	 * @var RateLimiter
	 * @since 1.0.0
	 */
	private RateLimiter $rate_limiter;

	/**
	 * Category repository
	 *
	 * @var CategoryRepository
	 * @since 1.0.0
	 */
	private CategoryRepository $repository;

	/**
	 * Constructor
	 *
	 * Initializes rate limiter and repository for API endpoint protection.
	 *
	 * @param CategoryRepository $repository Category repository for data access
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct( CategoryRepository $repository ) {
		$this->rate_limiter = new RateLimiter();
		$this->repository = $repository;
	}

	/**
	 * Register REST API routes
	 *
	 * Registers /categories endpoints for:
	 * - GET /categories - List categories with pagination
	 * - POST /categories - Create new category
	 * - GET /categories/{id} - Get single category
	 * - POST /categories/{id} - Update category
	 * - DELETE /categories/{id} - Delete category
	 * - POST /categories/{id}/trash - Trash category
	 * - POST /categories/{id}/restore - Restore category
	 * - DELETE /categories/{id}/delete-permanently - Permanent delete
	 * - POST /categories/trash/empty - Empty trash
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action rest_api_init
	 */
	public function register_routes(): void {
		// Categories list and create
		register_rest_route(
			$this->namespace,
			'/categories',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'list' ],
					'permission_callback' => [ $this, 'permissions_check' ],
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

		// Single category routes
		register_rest_route(
			$this->namespace,
			'/categories/(?P<id>[\d]+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permissions_check' ],
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

		// Category action routes
		register_rest_route(
			$this->namespace,
			'/categories/(?P<id>[\d]+)/trash',
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
			'/categories/(?P<id>[\d]+)/restore',
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
			'/categories/(?P<id>[\d]+)/delete-permanently',
			[
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_permanently' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/categories/trash/empty',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'empty_trash' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);
	}

	/**
	 * Get validation schema for list endpoint
	 *
	 * Defines query parameters for categories list:
	 * - per_page: Number of categories per page (1-100, default 10)
	 * - page: Page number (default 1)
	 * - search: Search term for name/description
	 * - parent: Parent category ID filter
	 * - hide_empty: Hide empty categories (default false)
	 *
	 * @return array<string, mixed> Validation schema for WordPress REST API
	 * @since 1.0.0
	 */
	private function get_list_args(): array {
		return [
			'per_page' => [
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			],
			'page' => [
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			],
			'search' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'parent' => [
				'required'          => false,
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'hide_empty' => [
				'required'          => false,
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
		];
	}

	/**
	 * Get validation schema for create endpoint
	 *
	 * Defines parameters for category creation:
	 * - name: Category name (required, max 200 chars)
	 * - slug: Category slug (optional, auto-generated from name)
	 * - description: Category description (optional)
	 * - parent_id: Parent category ID (optional, default 0)
	 * - featured: Whether category is featured (optional, default false)
	 * - image_url: Category image URL (optional, URI format)
	 * - sort_order: Default sort order (optional, default 'date')
	 *
	 * @return array<string, mixed> Validation schema for WordPress REST API
	 * @since 1.0.0
	 */
	private function get_create_args(): array {
		return [
			'name' => [
				'required'          => true,
				'type'              => 'string',
				'minLength'         => 1,
				'maxLength'         => 200,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'slug' => [
				'required'          => false,
				'type'              => 'string',
				'maxLength'         => 200,
				'sanitize_callback' => 'sanitize_title',
			],
			'description' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'parent_id' => [
				'required'          => false,
				'type'              => 'integer',
				'default'           => 0,
				'minimum'           => 0,
				'sanitize_callback' => 'absint',
			],
			'featured' => [
				'required'          => false,
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'image_url' => [
				'required'          => false,
				'type'              => 'string',
				'format'            => 'uri',
				'sanitize_callback' => 'esc_url_raw',
			],
			'sort_order' => [
				'required'          => false,
				'type'              => 'string',
				'default'           => 'date',
				'enum'              => ['name', 'price', 'date', 'popularity', 'random'],
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
	 * Check if taxonomy exists
	 *
	 * @return WP_REST_Response|null Response if taxonomy doesn't exist, null otherwise
	 * @since 2.1.0
	 */
	private function check_taxonomy_exists(): ?WP_REST_Response {
		if ( ! taxonomy_exists( Constants::TAX_CATEGORY ) ) {
			return $this->respond( [
				'message' => sprintf(
					esc_html__( 'Taxonomy %s is not registered. Please ensure the plugin is properly activated.', 'affiliate-product-showcase' ),
					esc_html( Constants::TAX_CATEGORY )
				),
				'code'    => 'taxonomy_not_registered',
			], 500 );
		}
		return null;
	}

	/**
	 * Verify nonce from request
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|null Response if invalid, null otherwise
	 * @since 2.1.0
	 */
	private function verify_nonce( WP_REST_Request $request ): ?WP_REST_Response {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => esc_html__( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}
		return null;
	}

	/**
	 * Validate category ID parameter
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|null Response if invalid, null otherwise
	 * @since 2.1.0
	 */
	private function validate_category_id( WP_REST_Request $request ): ?WP_REST_Response {
		$category_id = $request->get_param( 'id' );
		if ( empty( $category_id ) ) {
			return $this->respond( [
				'message' => esc_html__( 'Category ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_category_id',
			], 400 );
		}
		return null;
	}

	/**
	 * Get category or return error response
	 *
	 * @param int $category_id Category ID
	 * @return WP_REST_Response|null Response if not found, null otherwise
	 * @since 2.1.0
	 */
	private function get_category_or_error( int $category_id ): ?WP_REST_Response {
		$category = $this->repository->find( $category_id );
		if ( null === $category ) {
			return $this->respond( [
				'message' => __( 'Category not found.', 'affiliate-product-showcase' ),
				'code'    => 'category_not_found',
			], 404 );
		}
		return null;
	}

	/**
	 * Log error with context
	 *
	 * @param string $context Error context description
	 * @param \Throwable $e Exception
	 * @return void
	 * @since 2.1.0
	 */
	private function log_error( string $context, \Throwable $e ): void {
		error_log( sprintf( '[APS] %s: %s', $context, $e->getMessage() ) );
		
		// Debug only - log full details when debug mode is enabled
		if ( defined( 'APS_DEBUG' ) && APS_DEBUG ) {
			error_log( sprintf(
				'[APS] %s: %s in %s:%d',
				$context,
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			) );
		}
	}

	/**
	 * Perform common preflight checks for REST methods
	 *
	 * @param WP_REST_Request $request Request object
	 * @param bool $require_nonce Whether to verify nonce
	 * @param bool $require_id Whether to validate category ID
	 * @return WP_REST_Response|null Error response or null if all checks pass
	 * @since 2.1.0
	 */
	private function perform_preflight_checks( WP_REST_Request $request, bool $require_nonce = true, bool $require_id = false ): ?WP_REST_Response {
		// Check taxonomy exists
		if ( $error = $this->check_taxonomy_exists() ) {
			return $error;
		}
		
		// Verify nonce if required
		if ( $require_nonce ) {
			if ( $error = $this->verify_nonce( $request ) ) {
				return $error;
			}
		}
		
		// Validate category ID if required
		if ( $require_id ) {
			if ( $error = $this->validate_category_id( $request ) ) {
				return $error;
			}
			
			$category_id = (int) $request->get_param( 'id' );
			if ( $error = $this->get_category_or_error( $category_id ) ) {
				return $error;
			}
		}
		
		return null;
	}

	/**
	 * Get single category
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with category data or error
	 * @since 1.0.0
	 *
	 * @route GET /affiliate-showcase/v1/categories/{id}
	 */
	public function get_item( WP_REST_Request $request ): WP_REST_Response {
		// Use helper methods
		if ( $error = $this->check_taxonomy_exists() ) {
			return $error;
		}
		
		if ( $error = $this->validate_category_id( $request ) ) {
			return $error;
		}
		
		$category_id = (int) $request->get_param( 'id' );
		$category    = $this->repository->find( $category_id );
		if ( null === $category ) {
			return $this->respond( [
				'message' => __( 'Category not found.', 'affiliate-product-showcase' ),
				'code'    => 'category_not_found',
			], 404 );
		}

		return $this->respond( $category->to_array(), 200 );
	}

	/**
	 * Update a category
	 *
	 * @param WP_REST_Request $request Request object containing category data
	 * @return WP_REST_Response Response with updated category or error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/categories/{id}
	 */
	public function update( WP_REST_Request $request ): WP_REST_Response {
		// Perform preflight checks
		if ( $error = $this->perform_preflight_checks( $request, true, true ) ) {
			return $error;
		}

		try {
			// Merge existing category data with updates
			$data = $request->get_params();
			$data['id'] = (int) $request->get_param( 'id' );
			
			$category = Category::from_array( $data );
			$updated = $this->repository->update( $category );
			
			return $this->respond( $updated->to_array(), 200 );
			
		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			$this->log_error( 'Category update failed', $e );
			
			return $this->respond([
				'message' => esc_html__('Failed to update category', 'affiliate-product-showcase'),
				'code' => 'category_update_error',
				'errors' => $e->getMessage(),
			], 400);
		}
	}

	/**
	 * Delete a category (move to trash)
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route DELETE /affiliate-showcase/v1/categories/{id}
	 */
	public function delete( WP_REST_Request $request ): WP_REST_Response {
		// Perform preflight checks
		if ( $error = $this->perform_preflight_checks( $request, true, true ) ) {
			return $error;
		}

		try {
			$this->repository->delete( (int) $request->get_param( 'id' ) );
			
			return $this->respond( [
				'message' => esc_html__( 'Category deleted successfully.', 'affiliate-product-showcase' ),
				'code'    => 'success',
			], 200 );
			
		} catch ( \Throwable $e ) {
			$this->log_error( 'Category delete failed', $e );
			
			return $this->respond([
				'message' => esc_html__('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}

	/**
	 * Trash category (move to trash)
	 *
	 * Note: WordPress doesn't have native trash for terms.
	 * This endpoint deletes category permanently.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/categories/{id}/trash
	 */
	public function trash( WP_REST_Request $request ): WP_REST_Response {
		// Perform preflight checks (no taxonomy check needed here)
		if ( $error = $this->verify_nonce( $request ) ) {
			return $error;
		}
		
		if ( $error = $this->validate_category_id( $request ) ) {
			return $error;
		}
		
		if ( $error = $this->get_category_or_error( (int) $request->get_param( 'id' ) ) ) {
			return $error;
		}

		try {
			// WordPress doesn't have native trash for terms
			// We'll delete permanently but notify user
			$this->repository->delete_permanently( (int) $request->get_param( 'id' ) );
			
			return $this->respond( [
				'message' => esc_html__( 'Category deleted. Note: WordPress does not support trash for categories.', 'affiliate-product-showcase' ),
				'code'    => 'success',
			], 200 );
			
		} catch ( \Throwable $e ) {
			$this->log_error( 'Category trash failed', $e );
			
			return $this->respond([
				'message' => esc_html__('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}

	/**
	 * Restore category from trash
	 *
	 * Note: WordPress doesn't have native trash for terms.
	 * This endpoint returns an error.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/categories/{id}/restore
	 */
	public function restore( WP_REST_Request $request ): WP_REST_Response {
		// Use helper methods
		if ( $error = $this->verify_nonce( $request ) ) {
			return $error;
		}

		return $this->respond( [
			'message' => esc_html__( 'Category trash/restore is not supported in WordPress core.', 'affiliate-product-showcase' ),
			'code'    => 'not_supported',
		], 501 );
	}

	/**
	 * Delete category permanently
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route DELETE /affiliate-showcase/v1/categories/{id}/delete-permanently
	 */
	public function delete_permanently( WP_REST_Request $request ): WP_REST_Response {
		// Perform preflight checks (no taxonomy check needed here)
		if ( $error = $this->verify_nonce( $request ) ) {
			return $error;
		}
		
		if ( $error = $this->validate_category_id( $request ) ) {
			return $error;
		}
		
		if ( $error = $this->get_category_or_error( (int) $request->get_param( 'id' ) ) ) {
			return $error;
		}

		try {
			$this->repository->delete_permanently( (int) $request->get_param( 'id' ) );
			
			return $this->respond( [
				'message' => esc_html__( 'Category deleted permanently.', 'affiliate-product-showcase' ),
				'code'    => 'success',
			], 200 );
			
		} catch ( \Throwable $e ) {
			$this->log_error( 'Category permanent delete failed', $e );
			
			return $this->respond([
				'message' => esc_html__('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}

	/**
	 * Empty trash
	 *
	 * Note: WordPress doesn't have native trash for terms.
	 * This endpoint returns an error.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/categories/trash/empty
	 */
	public function empty_trash( WP_REST_Request $request ): WP_REST_Response {
		// Use helper methods
		if ( $error = $this->verify_nonce( $request ) ) {
			return $error;
		}

		return $this->respond( [
			'message' => esc_html__( 'Category trash/empty is not supported in WordPress core.', 'affiliate-product-showcase' ),
			'code'    => 'not_supported',
		], 501 );
	}

	/**
	 * List categories
	 *
	 * Returns paginated list of categories with rate limiting.
	 * Rate limit: 60 requests/minute for public, 120 for authenticated users.
	 *
	 * @param WP_REST_Request $request Request object containing query parameters
	 * @return WP_REST_Response Response with categories list or error
	 * @throws RateLimitException If rate limit is exceeded
	 * @since 1.0.0
	 *
	 * @route GET /affiliate-showcase/v1/categories
	 */
	public function list( WP_REST_Request $request ): WP_REST_Response {
		// Check if taxonomy exists
		if ( ! taxonomy_exists( Constants::TAX_CATEGORY ) ) {
			return $this->respond( [
				'message' => sprintf( 
					esc_html__( 'Taxonomy %s is not registered. Please ensure plugin is properly activated.', 'affiliate-product-showcase' ),
					esc_html( Constants::TAX_CATEGORY )
				),
				'code'    => 'taxonomy_not_registered',
			], 500 );
		}

		// Check rate limit
		if ( ! $this->rate_limiter->check( 'categories_list' ) ) {
			return $this->respond( [
				'message' => esc_html__( 'Too many requests. Please try again later.', 'affiliate-product-showcase' ),
				'code'    => 'rate_limit_exceeded',
			], 429, $this->rate_limiter->get_headers( 'categories_list' ) );
		}

		// Build query arguments
		$args = [];
		$per_page = $request->get_param( 'per_page' );
		$page = $request->get_param( 'page' );
		$search = $request->get_param( 'search' );
		$parent = $request->get_param( 'parent' );
		$hide_empty = $request->get_param( 'hide_empty' );

		if ( ! empty( $per_page ) ) {
			$args['number'] = (int) $per_page;
		}

		if ( ! empty( $page ) && $page > 1 ) {
			$args['offset'] = ( (int) $page - 1 ) * (int) $per_page;
		}

		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		if ( isset( $parent ) ) {
			$args['parent'] = (int) $parent;
		}

		if ( isset( $hide_empty ) ) {
			$args['hide_empty'] = (bool) $hide_empty;
		}

		// Get paginated results
		$result = $this->repository->paginate( $args );

		return $this->respond( [
			'categories' => array_map( fn( $c ) => $c->to_array(), $result['categories'] ),
			'total'      => $result['total'],
			'pages'      => $result['pages'],
		], 200, $this->rate_limiter->get_headers( 'categories_list' ) );
	}

	/**
	 * Create a new category
	 *
	 * Creates a new category with CSRF protection and stricter rate limiting.
	 * Rate limit: 20 requests/minute (stricter than list operations).
	 * Nonce verification required in X-WP-Nonce header.
	 *
	 * @param WP_REST_Request $request Request object containing category data
	 * @return WP_REST_Response Response with created category or error
	 * @throws ValidationException If category data is invalid
	 * @throws RateLimitException If rate limit is exceeded
	 * @throws PluginException If category creation fails
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/categories
	 */
	public function create( WP_REST_Request $request ): WP_REST_Response {
		// Perform preflight checks
		if ( $error = $this->perform_preflight_checks( $request, true, false ) ) {
			return $error;
		}

		// Check rate limit (stricter for create operations)
		if ( ! $this->rate_limiter->check( 'categories_create', 20 ) ) {
			return $this->respond( [
				'message' => esc_html__( 'Too many creation requests. Please try again later.', 'affiliate-product-showcase' ),
				'code'    => 'rate_limit_exceeded',
			], 429, $this->rate_limiter->get_headers( 'categories_create', 20 ) );
		}

		try {
			// Parameters are already validated by REST API args
			$category = Category::from_array( $request->get_params() );
			$created = $this->repository->create( $category );
			
			return $this->respond( $created->to_array(), 201, $this->rate_limiter->get_headers( 'categories_create', 20 ) );
			
		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			$this->log_error( 'Category creation failed', $e );
			
			// Return safe message to client
			return $this->respond([
				'message' => esc_html__('Failed to create category', 'affiliate-product-showcase'),
				'code' => 'category_creation_error',
				'errors' => $e->getMessage(),
			], 400);
			
		} catch ( \Throwable $e ) {
			$this->log_error( 'Unexpected error in category creation', $e );
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}
}
