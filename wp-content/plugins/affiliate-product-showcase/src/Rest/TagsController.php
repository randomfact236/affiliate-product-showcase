<?php
/**
 * Tags REST API Controller
 *
 * Handles REST API endpoints for tag management including:
 * - Listing tags with pagination and filtering
 * - Creating new tags with validation
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

use AffiliateProductShowcase\Repositories\TagRepository;
use AffiliateProductShowcase\Security\RateLimiter;
use AffiliateProductShowcase\Factories\TagFactory;
use AffiliateProductShowcase\Models\Tag;
use AffiliateProductShowcase\Plugin\Constants;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Tags REST API Controller
 *
 * Handles REST API endpoints for tag management including:
 * - Listing tags with pagination and filtering
 * - Creating new tags with validation
 * - Rate limiting for API endpoints
 * - CSRF protection via nonce verification
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 * @author Development Team
 */
final class TagsController extends RestController {
	/**
	 * Rate limiter instance
	 *
	 * @var RateLimiter
	 * @since 1.0.0
	 */
	private RateLimiter $rate_limiter;

	/**
	 * Tag repository
	 *
	 * @var TagRepository
	 * @since 1.0.0
	 */
	private TagRepository $repository;

	/**
	 * Constructor
	 *
	 * Initializes rate limiter and repository for API endpoint protection.
	 *
	 * @param TagRepository $repository Tag repository for data access
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct( TagRepository $repository ) {
		$this->rate_limiter = new RateLimiter();
		$this->repository = $repository;
	}

	/**
	 * Register REST API routes
	 *
	 * Registers /tags endpoints for:
	 * - GET /tags - List tags with pagination
	 * - POST /tags - Create new tag
	 * - GET /tags/{id} - Get single tag
	 * - POST /tags/{id} - Update tag
	 * - DELETE /tags/{id} - Delete tag
	 *
	 * Note: Tags don't support trash/restore (flat taxonomy, no hierarchy)
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action rest_api_init
	 */
	public function register_routes(): void {
		// Tags list and create
		register_rest_route(
			$this->namespace,
			'/tags',
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

		// Single tag routes
		register_rest_route(
			$this->namespace,
			'/tags/(?P<id>[\d]+)',
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
	}

	/**
	 * Get validation schema for list endpoint
	 *
	 * Defines query parameters for tags list:
	 * - per_page: Number of tags per page (1-100, default 10)
	 * - page: Page number (default 1)
	 * - search: Search term for name/description
	 * - hide_empty: Hide empty tags (default false)
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
	 * Defines parameters for tag creation:
	 * - name: Tag name (required, max 200 chars)
	 * - slug: Tag slug (optional, auto-generated from name)
	 * - description: Tag description (optional)
	 * - color: Tag color (hex format, optional)
	 * - icon: Tag icon (emoji or SVG, optional)
	 * - status: Tag status (published/draft, default published)
	 * - featured: Tag featured flag (boolean, default false)
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
			'color' => [
				'required'          => false,
				'type'              => 'string',
				'format'            => 'hex-color',
				'sanitize_callback' => 'sanitize_hex_color',
			],
			'icon' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'status' => [
				'required'          => false,
				'type'              => 'string',
				'default'           => 'published',
				'enum'              => ['published', 'draft'],
				'sanitize_callback' => 'sanitize_text_field',
			],
			'featured' => [
				'required'          => false,
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
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
	 * Get single tag
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with tag data or error
	 * @since 1.0.0
	 *
	 * @route GET /affiliate-showcase/v1/tags/{id}
	 */
	public function get_item( WP_REST_Request $request ): WP_REST_Response {
		// Check if taxonomy exists
		if ( ! taxonomy_exists( Constants::TAX_TAG ) ) {
			return $this->respond( [
				'message' => sprintf( 
					__( 'Taxonomy %s is not registered. Please ensure the plugin is properly activated.', 'affiliate-product-showcase' ),
					Constants::TAX_TAG
				),
				'code'    => 'taxonomy_not_registered',
			], 500 );
		}

		$tag_id = $request->get_param( 'id' );
		
		if ( empty( $tag_id ) ) {
			return $this->respond( [
				'message' => __( 'Tag ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_tag_id',
			], 400 );
		}

		$tag = $this->repository->find( (int) $tag_id );
		
		if ( null === $tag ) {
			return $this->respond( [
				'message' => __( 'Tag not found.', 'affiliate-product-showcase' ),
				'code'    => 'tag_not_found',
			], 404 );
		}

		return $this->respond( $tag->to_array(), 200 );
	}

	/**
	 * Update a tag
	 *
	 * @param WP_REST_Request $request Request object containing tag data
	 * @return WP_REST_Response Response with updated tag or error
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/tags/{id}
	 */
	public function update( WP_REST_Request $request ): WP_REST_Response {
		// Check if taxonomy exists
		if ( ! taxonomy_exists( Constants::TAX_TAG ) ) {
			return $this->respond( [
				'message' => sprintf( 
					__( 'Taxonomy %s is not registered. Please ensure the plugin is properly activated.', 'affiliate-product-showcase' ),
					Constants::TAX_TAG
				),
				'code'    => 'taxonomy_not_registered',
			], 500 );
		}

		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$tag_id = $request->get_param( 'id' );
		
		if ( empty( $tag_id ) ) {
			return $this->respond( [
				'message' => __( 'Tag ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_tag_id',
			], 400 );
		}

		// Verify tag exists
		$existing_tag = $this->repository->find( (int) $tag_id );
		if ( null === $existing_tag ) {
			return $this->respond( [
				'message' => __( 'Tag not found.', 'affiliate-product-showcase' ),
				'code'    => 'tag_not_found',
			], 404 );
		}

		try {
			// Merge existing tag data with updates
			$data = $request->get_params();
			$data['id'] = (int) $tag_id;
			
			$tag = Tag::from_array( $data );
			$updated = $this->repository->update( $tag );
			
			return $this->respond( $updated->to_array(), 200 );
			
		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			error_log(sprintf(
				'[APS] Tag update failed: %s in %s:%d',
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			));
			
			return $this->respond([
				'message' => __('Failed to update tag', 'affiliate-product-showcase'),
				'code' => 'tag_update_error',
				'errors' => $e->getMessage(),
			], 400);
		}
	}

	/**
	 * Delete a tag
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response with success/error
	 * @since 1.0.0
	 *
	 * @route DELETE /affiliate-showcase/v1/tags/{id}
	 */
	public function delete( WP_REST_Request $request ): WP_REST_Response {
		// Check if taxonomy exists
		if ( ! taxonomy_exists( Constants::TAX_TAG ) ) {
			return $this->respond( [
				'message' => sprintf( 
					__( 'Taxonomy %s is not registered. Please ensure the plugin is properly activated.', 'affiliate-product-showcase' ),
					Constants::TAX_TAG
				),
				'code'    => 'taxonomy_not_registered',
			], 500 );
		}

		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		$tag_id = $request->get_param( 'id' );
		
		if ( empty( $tag_id ) ) {
			return $this->respond( [
				'message' => __( 'Tag ID is required.', 'affiliate-product-showcase' ),
				'code'    => 'missing_tag_id',
			], 400 );
		}

		$existing_tag = $this->repository->find( (int) $tag_id );
		if ( null === $existing_tag ) {
			return $this->respond( [
				'message' => __( 'Tag not found.', 'affiliate-product-showcase' ),
				'code'    => 'tag_not_found',
			], 404 );
		}

		try {
			$this->repository->delete( (int) $tag_id );
			
			return $this->respond( [
				'message' => __( 'Tag deleted successfully.', 'affiliate-product-showcase' ),
				'code'    => 'success',
			], 200 );
			
		} catch ( \Throwable $e ) {
			error_log(sprintf('[APS] Tag delete failed: %s', $e->getMessage()));
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}

	/**
	 * List tags
	 *
	 * Returns paginated list of tags with rate limiting.
	 * Rate limit: 60 requests/minute for public, 120 for authenticated users.
	 *
	 * @param WP_REST_Request $request Request object containing query parameters
	 * @return WP_REST_Response Response with tags list or error
	 * @throws RateLimitException If rate limit is exceeded
	 * @since 1.0.0
	 *
	 * @route GET /affiliate-showcase/v1/tags
	 */
	public function list( WP_REST_Request $request ): WP_REST_Response {
		// Check if taxonomy exists
		if ( ! taxonomy_exists( Constants::TAX_TAG ) ) {
			return $this->respond( [
				'message' => sprintf( 
					__( 'Taxonomy %s is not registered. Please ensure the plugin is properly activated.', 'affiliate-product-showcase' ),
					Constants::TAX_TAG
				),
				'code'    => 'taxonomy_not_registered',
			], 500 );
		}

		// Check rate limit
		if ( ! $this->rate_limiter->check( 'tags_list' ) ) {
			return $this->respond( [
				'message' => __( 'Too many requests. Please try again later.', 'affiliate-product-showcase' ),
				'code'    => 'rate_limit_exceeded',
			], 429, $this->rate_limiter->get_headers( 'tags_list' ) );
		}

		// Build query arguments
		$args = [];
		$per_page = $request->get_param( 'per_page' );
		$page = $request->get_param( 'page' );
		$search = $request->get_param( 'search' );
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

		if ( isset( $hide_empty ) ) {
			$args['hide_empty'] = (bool) $hide_empty;
		}

		// Get paginated results
		$result = $this->repository->paginate( $args );

		return $this->respond( [
			'tags'   => array_map( fn( $t ) => $t->to_array(), $result['tags'] ),
			'total'  => $result['total'],
			'pages'   => $result['pages'],
		], 200, $this->rate_limiter->get_headers( 'tags_list' ) );
	}

	/**
	 * Create a new tag
	 *
	 * Creates a new tag with CSRF protection and stricter rate limiting.
	 * Rate limit: 20 requests/minute (stricter than list operations).
	 * Nonce verification required in X-WP-Nonce header.
	 *
	 * @param WP_REST_Request $request Request object containing tag data
	 * @return WP_REST_Response Response with created tag or error
	 * @throws ValidationException If tag data is invalid
	 * @throws RateLimitException If rate limit is exceeded
	 * @throws PluginException If tag creation fails
	 * @since 1.0.0
	 *
	 * @route POST /affiliate-showcase/v1/tags
	 */
	public function create( WP_REST_Request $request ): WP_REST_Response {
		// Check if taxonomy exists
		if ( ! taxonomy_exists( Constants::TAX_TAG ) ) {
			return $this->respond( [
				'message' => sprintf( 
					__( 'Taxonomy %s is not registered. Please ensure the plugin is properly activated.', 'affiliate-product-showcase' ),
					Constants::TAX_TAG
				),
				'code'    => 'taxonomy_not_registered',
			], 500 );
		}

		// Verify nonce for CSRF protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return $this->respond( [
				'message' => __( 'Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase' ),
				'code'    => 'invalid_nonce',
			], 403 );
		}

		// Check rate limit (stricter for create operations)
		if ( ! $this->rate_limiter->check( 'tags_create', 20 ) ) {
			return $this->respond( [
				'message' => __( 'Too many creation requests. Please try again later.', 'affiliate-product-showcase' ),
				'code'    => 'rate_limit_exceeded',
			], 429, $this->rate_limiter->get_headers( 'tags_create', 20 ) );
		}

		try {
			// Parameters are already validated by REST API args
			$tag = Tag::from_array( $request->get_params() );
			$created = $this->repository->create( $tag );
			
			return $this->respond( $created->to_array(), 201, $this->rate_limiter->get_headers( 'tags_create', 20 ) );
			
		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			// Log full error internally (includes details)
			error_log(sprintf(
				'[APS] Tag creation failed: %s in %s:%d',
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			));
			
			// Return safe message to client
			return $this->respond([
				'message' => __('Failed to create tag', 'affiliate-product-showcase'),
				'code' => 'tag_creation_error',
				'errors' => $e->getMessage(),
			], 400);
			
		} catch ( \Throwable $e ) {
			// Catch-all for unexpected errors
			error_log(sprintf('[APS] Unexpected error in tag creation: %s', $e->getMessage()));
			
			return $this->respond([
				'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
				'code' => 'server_error',
			], 500);
		}
	}
}