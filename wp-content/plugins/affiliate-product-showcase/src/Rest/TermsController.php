<?php
/**
 * Terms REST Controller
 *
 * Provides REST API endpoints for managing product categories, tags, and ribbons.
 * Supports CRUD operations, filtering, and meta field management.
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

use AffiliateProductShowcase\Plugin\Constants;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Terms REST Controller
 *
 * Handles REST API requests for taxonomies.
 * Extends WP_REST_Controller for standard WordPress REST functionality.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 * @author Development Team
 */
class TermsController extends WP_REST_Controller {
	/**
	 * REST API namespace
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public const NAMESPACE = 'affiliate-product-showcase/v1';

	/**
	 * Register routes
	 *
	 * Registers all REST API endpoints for categories, tags, and ribbons.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action rest_api_init
	 */
	public function register_routes(): void {
		// Category endpoints
		register_rest_route(
			self::NAMESPACE . '/categories',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_categories' ],
			]
		);

		register_rest_route(
			self::NAMESPACE . '/categories/(?P<id>\d+)',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_category' ],
			]
		);

		// Tag endpoints
		register_rest_route(
			self::NAMESPACE . '/tags',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_tags' ],
			]
		);

		register_rest_route(
			self::NAMESPACE . '/tags/(?P<id>\d+)',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_tag' ],
			]
		);

		// Ribbon endpoints
		register_rest_route(
			self::NAMESPACE . '/ribbons',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_ribbons' ],
			]
		);

		register_rest_route(
			self::NAMESPACE . '/ribbons/(?P<id>\d+)',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_ribbon' ],
			]
		);
	}

	/**
	 * Get all categories
	 *
	 * Retrieves a paginated list of product categories with optional filtering.
	 * Supports search, parent filtering, and meta field filtering.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - per_page (int): Items per page (default: 10, max: 100)
	 *                               - page (int): Page number (default: 1)
	 *                               - search (string): Search term
	 *                               - parent (int): Parent category ID
	 *                               - hide_empty (bool): Filter empty categories
	 *                               - include (string): Include specific category IDs (comma-separated)
	 * @return WP_REST_Response Response containing success, data array, and total count
	 * @since 1.0.0
	 *
	 * @route GET /v1/categories
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function get_categories( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_params();

		$per_page = isset( $params['per_page'] ) ? min( 100, absint( $params['per_page'] ) ) : 10;
		$page = isset( $params['page'] ) ? absint( $params['page'] ) : 1;

		$args = [
			'taxonomy'   => Constants::TAX_CATEGORY,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => ! empty( $params['hide_empty'] ),
			'number'     => $per_page,
			'offset'     => ( $page - 1 ) * $per_page,
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'category_hide_from_menu',
					'value'   => '1',
					'compare' => '!=',
					'type'    => 'NUMERIC',
				],
			],
		];

		if ( ! empty( $params['search'] ) ) {
			$args['search'] = $params['search'];
		}

		if ( ! empty( $params['include'] ) ) {
			$args['include'] = array_map( 'absint', explode( ',', $params['include'] ) );
		}

		if ( ! empty( $params['parent'] ) ) {
			$args['parent'] = $params['parent'];
		}

		$terms = get_terms( $args );
		$data = [];

		foreach ( $terms as $term ) {
			$data[] = $this->prepare_term_for_response( $term, 'category' );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $data,
				'total'   => is_array( $terms ) ? count( $terms ) : 0,
			],
			200
		);
	}

	/**
	 * Get single category
	 *
	 * Retrieves a single category by ID with all meta fields.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - id (int): Category ID
	 * @return WP_REST_Response|WP_Error Response containing category data or error
	 * @throws WP_Error If category not found
	 * @since 1.0.0
	 *
	 * @route GET /v1/categories/{id}
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function get_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$term_id = (int) $request->get_param( 'id' );
		$term = get_term( $term_id, Constants::TAX_CATEGORY );

		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'aps_category_not_found', 'Category not found', 404 );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_term_for_response( $term, 'category' ),
			],
			200
		);
	}

	/**
	 * Get all tags
	 *
	 * Retrieves a paginated list of product tags with optional filtering.
	 * Supports search and meta field filtering for featured tags.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - per_page (int): Items per page (default: 20, max: 100)
	 *                               - page (int): Page number (default: 1)
	 *                               - search (string): Search term
	 *                               - hide_empty (bool): Filter empty tags
	 *                               - include (string): Include specific tag IDs (comma-separated)
	 * @return WP_REST_Response Response containing success, data array, and total count
	 * @since 1.0.0
	 *
	 * @route GET /v1/tags
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function get_tags( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_params();

		$per_page = isset( $params['per_page'] ) ? min( 100, absint( $params['per_page'] ) ) : 20;
		$page = isset( $params['page'] ) ? absint( $params['page'] ) : 1;

		$args = [
			'taxonomy' => Constants::TAX_TAG,
			'orderby'  => 'count',
			'order'    => 'DESC',
			'hide_empty' => ! empty( $params['hide_empty'] ),
			'number'    => $per_page,
			'offset'    => ( $page - 1 ) * $per_page,
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'tag_featured',
					'value'   => '1',
					'compare' => '=',
					'type'    => 'NUMERIC',
				],
			],
		];

		if ( ! empty( $params['search'] ) ) {
			$args['search'] = $params['search'];
		}

		if ( ! empty( $params['include'] ) ) {
			$args['include'] = array_map( 'absint', explode( ',', $params['include'] ) );
		}

		$terms = get_terms( $args );
		$data = [];

		foreach ( $terms as $term ) {
			$data[] = $this->prepare_term_for_response( $term, 'tag' );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $data,
				'total'   => is_array( $terms ) ? count( $terms ) : 0,
			],
			200
		);
	}

	/**
	 * Get single tag
	 *
	 * Retrieves a single tag by ID with all meta fields.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - id (int): Tag ID
	 * @return WP_REST_Response|WP_Error Response containing tag data or error
	 * @throws WP_Error If tag not found
	 * @since 1.0.0
	 *
	 * @route GET /v1/tags/{id}
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function get_tag( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$term_id = (int) $request->get_param( 'id' );
		$term = get_term( $term_id, Constants::TAX_TAG );

		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'aps_tag_not_found', 'Tag not found', 404 );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_term_for_response( $term, 'tag' ),
			],
			200
		);
	}

	/**
	 * Get all ribbons
	 *
	 * Retrieves a paginated list of ribbons with optional filtering.
	 * Supports search, style filtering, and date filtering for scheduled ribbons.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - per_page (int): Items per page (default: 50, max: 100)
	 *                               - page (int): Page number (default: 1)
	 *                               - search (string): Search term
	 *                               - style (string): Filter by ribbon style (badge, corner, banner, diagonal)
	 * @return WP_REST_Response Response containing success, data array, and total count
	 * @since 1.0.0
	 *
	 * @route GET /v1/ribbons
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function get_ribbons( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_params();

		$per_page = isset( $params['per_page'] ) ? min( 100, absint( $params['per_page'] ) ) : 50;
		$page = isset( $params['page'] ) ? absint( $params['page'] ) : 1;

		$args = [
			'taxonomy' => Constants::TAX_RIBBON,
			'orderby'  => 'name',
			'order'    => 'ASC',
			'hide_empty' => ! empty( $params['hide_empty'] ),
			'number'    => $per_page,
			'offset'    => ( $page - 1 ) * $per_page,
			'meta_query' => [
				'relation' => 'OR',
				[
					'key'     => 'ribbon_start_date',
					'value'   => current_time( 'Y-m-d H:i:s' ),
					'compare' => '<=',
					'type'    => 'DATETIME',
				],
				[
					'key'     => 'ribbon_expiration_date',
					'value'   => current_time( 'Y-m-d H:i:s' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				],
			],
		];

		if ( ! empty( $params['search'] ) ) {
			$args['search'] = $params['search'];
		}

		if ( ! empty( $params['style'] ) ) {
			$valid_styles = [ 'badge', 'corner', 'banner', 'diagonal' ];
			if ( in_array( $params['style'], $valid_styles, true ) ) {
				$args['meta_query'][] = [
					'key'     => 'ribbon_style',
					'value'   => $params['style'],
					'compare' => '=',
					'type'    => 'CHAR',
				];
			}
		}

		$terms = get_terms( $args );
		$data = [];

		foreach ( $terms as $term ) {
			$data[] = $this->prepare_term_for_response( $term, 'ribbon' );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $data,
				'total'   => is_array( $terms ) ? count( $terms ) : 0,
			],
			200
		);
	}

	/**
	 * Get single ribbon
	 *
	 * Retrieves a single ribbon by ID with all meta fields.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - id (int): Ribbon ID
	 * @return WP_REST_Response|WP_Error Response containing ribbon data or error
	 * @throws WP_Error If ribbon not found
	 * @since 1.0.0
	 *
	 * @route GET /v1/ribbons/{id}
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function get_ribbon( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$term_id = (int) $request->get_param( 'id' );
		$term = get_term( $term_id, Constants::TAX_RIBBON );

		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'aps_ribbon_not_found', 'Ribbon not found', 404 );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_term_for_response( $term, 'ribbon' ),
			],
			200
		);
	}

	/**
	 * Create new category
	 *
	 * Creates a new category with the provided data and meta fields.
	 * Checks user permissions before creating.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - name (string, required): Category name
	 *                               - slug (string): URL-friendly slug
	 *                               - description (string): Category description
	 *                               - parent (int): Parent category ID
	 *                               - icon (string): Category icon (SVG or emoji)
	 *                               - color (string): Category color (hex)
	 *                               - image (int): Category image attachment ID
	 *                               - order (int): Display order
	 *                               - featured (bool): Featured flag
	 *                               - hide_from_menu (bool): Hide from menu flag
	 *                               - seo_title (string): SEO title
	 *                               - seo_description (string): SEO description
	 * @return WP_REST_Response|WP_Error Response containing created category data or error
	 * @throws WP_Error If user lacks permission or creation fails
	 * @since 1.0.0
	 *
	 * @route POST /v1/categories
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function create_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			return new WP_Error( 'aps_rest_forbidden', 'You do not have permission to manage categories', 403 );
		}

		$params = $request->get_params();
		$name = sanitize_text_field( $params['name'] ?? '' );
		$slug = sanitize_title( $params['slug'] ?? '' );
		$description = wp_kses_post( $params['description'] ?? '' );
		$parent = isset( $params['parent'] ) ? absint( $params['parent'] ) : 0;

		// Validate required fields
		if ( empty( $name ) ) {
			return new WP_Error( 'aps_category_name_required', 'Category name is required', 400 );
		}

		// Create term
		$result = wp_insert_term( $name, Constants::TAX_CATEGORY, [
			'slug'        => $slug,
			'description' => $description,
			'parent'     => $parent,
		] );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'aps_category_create_failed', $result->get_error_message(), 400 );
		}

		// Add meta fields
		if ( isset( $params['icon'] ) ) {
			update_term_meta( $result['term_id'], 'category_icon', sanitize_text_field( $params['icon'] ) );
		}

		if ( isset( $params['color'] ) ) {
			update_term_meta( $result['term_id'], 'category_color', $this->sanitize_hex_color( $params['color'] ) );
		}

		if ( isset( $params['image'] ) ) {
			update_term_meta( $result['term_id'], 'category_image', absint( $params['image'] ) );
		}

		if ( isset( $params['order'] ) ) {
			update_term_meta( $result['term_id'], 'category_order', absint( $params['order'] ) );
		}

		if ( isset( $params['featured'] ) ) {
			update_term_meta( $result['term_id'], 'category_featured', rest_sanitize_boolean( $params['featured'] ) ? '1' : '0' );
		}

		if ( isset( $params['hide_from_menu'] ) ) {
			update_term_meta( $result['term_id'], 'category_hide_from_menu', rest_sanitize_boolean( $params['hide_from_menu'] ) ? '1' : '0' );
		}

		if ( isset( $params['seo_title'] ) ) {
			update_term_meta( $result['term_id'], 'category_seo_title', sanitize_text_field( $params['seo_title'] ) );
		}

		if ( isset( $params['seo_description'] ) ) {
			update_term_meta( $result['term_id'], 'category_seo_description', sanitize_textarea_field( $params['seo_description'] ) );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_term_for_response( get_term( $result['term_id'], Constants::TAX_CATEGORY ), 'category' ),
			],
			201
		);
	}

	/**
	 * Update category
	 *
	 * Updates an existing category with the provided data and meta fields.
	 * Checks user permissions before updating.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - id (int, required): Category ID to update
	 *                               - name (string): Category name
	 *                               - slug (string): URL-friendly slug
	 *                               - description (string): Category description
	 *                               - parent (int): Parent category ID
	 *                               - icon (string): Category icon (SVG or emoji)
	 *                               - color (string): Category color (hex)
	 *                               - image (int): Category image attachment ID
	 *                               - order (int): Display order
	 *                               - featured (bool): Featured flag
	 *                               - hide_from_menu (bool): Hide from menu flag
	 *                               - seo_title (string): SEO title
	 *                               - seo_description (string): SEO description
	 * @return WP_REST_Response|WP_Error Response containing updated category data or error
	 * @throws WP_Error If user lacks permission, category not found, or update fails
	 * @since 1.0.0
	 *
	 * @route POST /v1/categories/{id}
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function update_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$term_id = (int) $request->get_param( 'id' );

		// Check permissions
		if ( ! current_user_can( 'edit_term', $term_id, Constants::TAX_CATEGORY ) ) {
			return new WP_Error( 'aps_rest_forbidden', 'You do not have permission to edit this category', 403 );
		}

		$params = $request->get_params();
		$term = get_term( $term_id, Constants::TAX_CATEGORY );

		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'aps_category_not_found', 'Category not found', 404 );
		}

		// Update basic fields
		$args = [
			'name'        => isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : $term->name,
			'slug'        => isset( $params['slug'] ) ? sanitize_title( $params['slug'] ) : $term->slug,
			'description' => isset( $params['description'] ) ? wp_kses_post( $params['description'] ) : $term->description,
			'parent'     => isset( $params['parent'] ) ? absint( $params['parent'] ) : $term->parent,
		];

		$result = wp_update_term( $term_id, Constants::TAX_CATEGORY, $args );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'aps_category_update_failed', $result->get_error_message(), 400 );
		}

		// Update meta fields
		if ( isset( $params['icon'] ) ) {
			update_term_meta( $term_id, 'category_icon', sanitize_text_field( $params['icon'] ) );
		}

		if ( isset( $params['color'] ) ) {
			update_term_meta( $term_id, 'category_color', $this->sanitize_hex_color( $params['color'] ) );
		}

		if ( isset( $params['image'] ) ) {
			update_term_meta( $term_id, 'category_image', absint( $params['image'] ) );
		}

		if ( isset( $params['order'] ) ) {
			update_term_meta( $term_id, 'category_order', absint( $params['order'] ) );
		}

		if ( isset( $params['featured'] ) ) {
			update_term_meta( $term_id, 'category_featured', rest_sanitize_boolean( $params['featured'] ) ? '1' : '0' );
		}

		if ( isset( $params['hide_from_menu'] ) ) {
			update_term_meta( $term_id, 'category_hide_from_menu', rest_sanitize_boolean( $params['hide_from_menu'] ) ? '1' : '0' );
		}

		if ( isset( $params['seo_title'] ) ) {
			update_term_meta( $term_id, 'category_seo_title', sanitize_text_field( $params['seo_title'] ) );
		}

		if ( isset( $params['seo_description'] ) ) {
			update_term_meta( $term_id, 'category_seo_description', sanitize_textarea_field( $params['seo_description'] ) );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $this->prepare_term_for_response( $term, 'category' ),
			],
			200
		);
	}

	/**
	 * Delete category
	 *
	 * Deletes a category and all its associated data.
	 * Checks user permissions before deleting.
	 *
	 * @param WP_REST_Request $request REST request object containing:
	 *                               - id (int, required): Category ID to delete
	 * @return WP_REST_Response|WP_Error Response containing success message or error
	 * @throws WP_Error If user lacks permission, category not found, or deletion fails
	 * @since 1.0.0
	 *
	 * @route DELETE /v1/categories/{id}
	 * @see https://developer.wordpress.org/rest-api/reference/
	 */
	public function delete_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$term_id = (int) $request->get_param( 'id' );

		// Check permissions
		if ( ! current_user_can( 'delete_term', $term_id, Constants::TAX_CATEGORY ) ) {
			return new WP_Error( 'aps_rest_forbidden', 'You do not have permission to delete this category', 403 );
		}

		$result = wp_delete_term( $term_id, Constants::TAX_CATEGORY );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'aps_category_delete_failed', $result->get_error_message(), 400 );
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'message' => 'Category deleted successfully',
			],
			200
		);
	}

	/**
	 * Prepare term for response
	 *
	 * Prepares a term object for REST API response including all meta fields.
	 * Includes type-specific data for categories, tags, and ribbons.
	 *
	 * @param \WP_Term $term Term object to prepare
	 * @param string $type Term type (category, tag, ribbon)
	 * @return array<string, mixed> Prepared term data with id, name, slug, count, taxonomy, and type-specific meta
	 * @since 1.0.0
	 */
	private function prepare_term_for_response( \WP_Term $term, string $type ): array {
		$data = [
			'id'       => $term->term_id,
			'name'     => $term->name,
			'slug'      => $term->slug,
			'count'     => $term->count,
			'taxonomy' => $term->taxonomy,
		];

		// Add type-specific data
		if ( 'category' === $type ) {
			$data['icon'] = get_term_meta( $term->term_id, 'category_icon', true );
			$data['color'] = get_term_meta( $term->term_id, 'category_color', true );
			$data['image'] = get_term_meta( $term->term_id, 'category_image', true );
			$data['image_url'] = $data['image'] ? wp_get_attachment_url( $data['image'], 'thumbnail' ) : null;
			$data['order'] = get_term_meta( $term->term_id, 'category_order', true );
			$data['featured'] = get_term_meta( $term->term_id, 'category_featured', true );
			$data['hide_from_menu'] = get_term_meta( $term->term_id, 'category_hide_from_menu', true );
			$data['seo_title'] = get_term_meta( $term->term_id, 'category_seo_title', true );
			$data['seo_description'] = get_term_meta( $term->term_id, 'category_seo_description', true );
		}

		if ( 'tag' === $type ) {
			$data['color'] = get_term_meta( $term->term_id, 'tag_color', true );
			$data['icon'] = get_term_meta( $term->term_id, 'tag_icon', true );
			$data['featured'] = get_term_meta( $term->term_id, 'tag_featured', true );
		}

		if ( 'ribbon' === $type ) {
			$data['text'] = get_term_meta( $term->term_id, 'ribbon_text', true );
			$data['bg_color'] = get_term_meta( $term->term_id, 'ribbon_bg_color', true );
			$data['text_color'] = get_term_meta( $term->term_id, 'ribbon_text_color', true );
			$data['position'] = get_term_meta( $term->term_id, 'ribbon_position', true );
			$data['style'] = get_term_meta( $term->term_id, 'ribbon_style', true );
			$data['icon'] = get_term_meta( $term->term_id, 'ribbon_icon', true );
			$data['priority'] = get_term_meta( $term->term_id, 'ribbon_priority', true );
			$data['start_date'] = get_term_meta( $term->term_id, 'ribbon_start_date', true );
			$data['expiration_date'] = get_term_meta( $term->term_id, 'ribbon_expiration_date', true );
		}

		return $data;
	}

	/**
	 * Get permission check callback
	 *
	 * Checks if current user has specified capability.
	 * Used for REST API permission checks.
	 *
	 * @param string $capability Capability name to check (e.g., 'manage_categories')
	 * @return bool True if user has capability, false otherwise
	 * @since 1.0.0
	 *
	 * @see current_user_can()
	 */
	public function get_permission_check_callback( string $capability ): bool {
		return current_user_can( $capability );
	}

	/**
	 * Get item schema
	 *
	 * Returns JSON schema for term items.
	 * Defines structure for category, tag, and ribbon data.
	 * Includes type-specific properties for each taxonomy.
	 *
	 * @return array JSON schema definition for items
	 * @since 1.0.0
	 *
	 * @see https://json-schema.org/learn/
	 */
	public function get_item_schema(): array {
		// Note: WordPress REST API requires no parameters for get_item_schema()
		// Return combined schema with all possible properties for all taxonomy types
		$properties = [
			'id'       => [
				'description' => 'Unique identifier for the term',
				'type'        => 'integer',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'   => true,
			],
			'name'     => [
				'description' => 'Term name',
				'type'        => 'string',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'   => false,
			],
			'slug'     => [
				'description' => 'URL-friendly slug',
				'type'        => 'string',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'   => true,
			],
			'count'    => [
				'description' => 'Number of associated products',
				'type'        => 'integer',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'   => true,
			],
			'taxonomy' => [
				'description' => 'Taxonomy type',
				'type'        => 'string',
				'enum'        => [ Constants::TAX_CATEGORY, Constants::TAX_TAG, Constants::TAX_RIBBON ],
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'   => true,
			],
		];

		// Add category-specific properties
		$properties['icon'] = [
			'description' => 'Category icon (SVG or emoji)',
			'type'        => 'string',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['color'] = [
			'description' => 'Category/tag/ribbon color (hex format)',
			'type'        => 'string',
			'pattern'     => '^#[0-9a-fA-F]{6}$',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['image'] = [
			'description' => 'Category image ID',
			'type'        => 'integer',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['order'] = [
			'description' => 'Category display order',
			'type'        => 'integer',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['featured'] = [
			'description' => 'Featured flag',
			'type'        => 'boolean',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['hide_from_menu'] = [
			'description' => 'Hide from menu flag',
			'type'        => 'boolean',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['seo_title'] = [
			'description' => 'SEO title',
			'type'        => 'string',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['seo_description'] = [
			'description' => 'SEO description',
			'type'        => 'string',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];

		// Add ribbon-specific properties
		$properties['text'] = [
			'description' => 'Ribbon display text',
			'type'        => 'string',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['bg_color'] = [
			'description' => 'Ribbon background color',
			'type'        => 'string',
			'pattern'     => '^#[0-9a-fA-F]{6}$',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['text_color'] = [
			'description' => 'Ribbon text color',
			'type'        => 'string',
			'pattern'     => '^#[0-9a-fA-F]{6}$',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['position'] = [
			'description' => 'Ribbon position on product image',
			'type'        => 'string',
			'enum'        => [ 'top-left', 'top-right', 'bottom-left', 'bottom-right' ],
			'context'     => [ 'view', 'edit' ],
			'default'     => 'top-left',
			'readonly'   => false,
		];
		$properties['style'] = [
			'description' => 'Ribbon visual style',
			'type'        => 'string',
			'enum'        => [ 'badge', 'corner', 'banner', 'diagonal' ],
			'context'     => [ 'view', 'edit' ],
			'default'     => 'badge',
			'readonly'   => false,
		];
		$properties['priority'] = [
			'description' => 'Ribbon display priority',
			'type'        => 'integer',
			'context'     => [ 'view', 'edit' ],
			'default'     => 0,
			'readonly'   => false,
		];
		$properties['start_date'] = [
			'description' => 'Ribbon start date (scheduled ribbons)',
			'type'        => 'string',
			'format'     => 'date-time',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];
		$properties['expiration_date'] = [
			'description' => 'Ribbon expiration date',
			'type'        => 'string',
			'format'     => 'date-time',
			'context'     => [ 'view', 'edit' ],
			'readonly'   => false,
		];

		return [
			'$schema' => 'http://json-schema.org/draft-07/schema#',
			'title'    => 'Term item',
			'type'     => 'object',
			'properties' => $properties,
		];
	}

	/**
	 * Get collections schema
	 *
	 * Returns JSON schema for term collections.
	 * Defines structure for categories, tags, and ribbons collections.
	 *
	 * @return array JSON schema definition for collections
	 * @since 1.0.0
	 *
	 * @see https://json-schema.org/learn/
	 */
	public function get_collections_schema(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-07/schema#',
			'title'    => 'Terms',
			'type'     => 'object',
			'properties' => [
				'categories' => [
					'description' => 'Collection of categories',
					'type'        => 'array',
					'items'       => [ '$ref' => '#/definitions/category' ],
				],
				'tags' => [
					'description' => 'Collection of tags',
					'type'        => 'array',
					'items'       => [ '$ref' => '#/definitions/tag' ],
				],
				'ribbons' => [
					'description' => 'Collection of ribbons',
					'type'        => 'array',
					'items'       => [ '$ref' => '#/definitions/ribbon' ],
				],
			],
		];
	}

	/**
	 * Sanitize hex color
	 *
	 * Validates and sanitizes a hex color string.
	 * Ensures color is in valid #RRGGBB format.
	 *
	 * @param string $color Hex color string to sanitize
	 * @return string Sanitized hex color in #RRGGBB format
	 * @since 1.0.0
	 */
	private function sanitize_hex_color( string $color ): string {
		$color = trim( $color );
		
		// Remove # if present
		if ( strpos( $color, '#' ) === 0 ) {
			$color = substr( $color, 1 );
		}

		// Validate hex format (without # since we removed it above)
		if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $color ) ) {
			return '#000000';
		}

		// Return with # prefix
		return '#' . $color;
	}
}
