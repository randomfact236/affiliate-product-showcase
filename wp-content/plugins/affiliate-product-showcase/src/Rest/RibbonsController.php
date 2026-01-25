<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use AffiliateProductShowcase\Models\Ribbon;
use AffiliateProductShowcase\Repositories\RibbonRepository;
use AffiliateProductShowcase\Factories\RibbonFactory;
use AffiliateProductShowcase\Plugin\Constants;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Ribbons REST API Controller
 *
 * Provides REST API endpoints for ribbon management.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 */
final class RibbonsController {
    /**
     * Namespace
     */
    private const NAMESPACE = 'affiliate-product-showcase/v1';

    /**
     * Route base
     */
    private const REST_BASE = 'ribbons';

    /**
     * Ribbon repository
     */
    private RibbonRepository $repository;

    /**
     * Constructor
     *
     * @param RibbonRepository $repository Ribbon repository
     */
    public function __construct( RibbonRepository $repository ) {
        $this->repository = $repository;
    }

    /**
     * Register routes
     *
     * @hook rest_api_init
     */
    public function register_routes(): void {
        register_rest_route(
            self::NAMESPACE,
            '/' . self::REST_BASE,
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => $this->get_collection_params(),
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema(),
                ],
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/' . self::REST_BASE . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'get_item_permissions_check' ],
                    'args'                => [
                        'id' => [
                            'description' => __( 'Unique identifier for the ribbon.', 'affiliate-product-showcase' ),
                            'type'        => 'integer',
                            'required'    => true,
                        ],
                    ],
                ],
                [
                    'methods'             => 'PUT',
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'update_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( false ),
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                    'args'                => [
                        'id' => [
                            'description' => __( 'Unique identifier for the ribbon.', 'affiliate-product-showcase' ),
                            'type'        => 'integer',
                            'required'    => true,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get a collection of ribbons
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object or WP_Error.
     */
    public function get_items( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $params = $request->get_params();
        $args = [];

        // Search
        if ( ! empty( $params['search'] ) ) {
            $args['search'] = $params['search'];
        }

        // Status filter
        if ( ! empty( $params['status'] ) ) {
            // TRUE HYBRID: Filter by status from term meta
            $args['meta_key'] = '_aps_ribbon_status';
            $args['meta_value'] = $params['status'];
        }

        // Featured filter
        if ( isset( $params['featured'] ) ) {
            // TRUE HYBRID: Filter by featured from term meta
            $args['meta_query'] = [
                [
                    'key'   => '_aps_ribbon_featured',
                    'value' => $params['featured'] ? '1' : '0',
                ],
            ];
        }

        // Order
        if ( ! empty( $params['orderby'] ) ) {
            if ( 'priority' === $params['orderby'] ) {
                // TRUE HYBRID: Order by priority from term meta
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_aps_ribbon_priority';
            } else {
                $args['orderby'] = $params['orderby'];
            }
        }

        if ( ! empty( $params['order'] ) ) {
            $args['order'] = $params['order'];
        }

        // Pagination
        if ( ! empty( $params['per_page'] ) ) {
            $args['number'] = min( 100, max( 1, (int) $params['per_page'] ) );
        }

        if ( ! empty( $params['page'] ) ) {
            $args['offset'] = ( (int) $params['page'] - 1 ) * $args['number'];
        }

        $ribbons = $this->repository->all( $args );

        $data = array_map( fn( $ribbon ) => $this->prepare_item_for_response( $ribbon ), $ribbons );

        return rest_ensure_response( $data );
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object or WP_Error.
     */
    public function get_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request['id'];
        $ribbon = $this->repository->find( $id );

        if ( ! $ribbon ) {
            return new WP_Error(
                'ribbon_not_found',
                __( 'Ribbon not found.', 'affiliate-product-showcase' ),
                [ 'status' => 404 ]
            );
        }

        $data = $this->prepare_item_for_response( $ribbon );

        return rest_ensure_response( $data );
    }

    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object or WP_Error.
     */
    public function create_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $params = $request->get_params();

        // Validate required fields
        if ( empty( $params['name'] ) ) {
            return new WP_Error(
                'missing_name',
                __( 'Ribbon name is required.', 'affiliate-product-showcase' ),
                [ 'status' => 400 ]
            );
        }

        // Create ribbon
        $ribbon = RibbonFactory::from_array( $params );

        try {
            $created = $this->repository->create( $ribbon );
        } catch ( \Exception $e ) {
            return new WP_Error(
                'creation_failed',
                $e->getMessage(),
                [ 'status' => 500 ]
            );
        }

        $data = $this->prepare_item_for_response( $created );

        return rest_ensure_response( $data, 201 );
    }

    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object or WP_Error.
     */
    public function update_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request['id'];
        $params = $request->get_params();

        $existing = $this->repository->find( $id );
        if ( ! $existing ) {
            return new WP_Error(
                'ribbon_not_found',
                __( 'Ribbon not found.', 'affiliate-product-showcase' ),
                [ 'status' => 404 ]
            );
        }

        // Update ribbon
        $ribbon = RibbonFactory::from_array( array_merge( $existing->to_array(), $params ) );

        try {
            $updated = $this->repository->update( $id, $ribbon );
        } catch ( \Exception $e ) {
            return new WP_Error(
                'update_failed',
                $e->getMessage(),
                [ 'status' => 500 ]
            );
        }

        $data = $this->prepare_item_for_response( $updated );

        return rest_ensure_response( $data );
    }

    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object or WP_Error.
     */
    public function delete_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request['id'];

        $existing = $this->repository->find( $id );
        if ( ! $existing ) {
            return new WP_Error(
                'ribbon_not_found',
                __( 'Ribbon not found.', 'affiliate-product-showcase' ),
                [ 'status' => 404 ]
            );
        }

        try {
            $this->repository->delete( $id );
        } catch ( \Exception $e ) {
            return new WP_Error(
                'deletion_failed',
                $e->getMessage(),
                [ 'status' => 500 ]
            );
        }

        $data = [
            'deleted' => true,
            'previous' => $this->prepare_item_for_response( $existing ),
        ];

        return rest_ensure_response( $data );
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        return current_user_can( 'edit_posts' );
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_item_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        return current_user_can( 'edit_posts' );
    }

    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool|WP_Error True if the request has access to create items, WP_Error object otherwise.
     */
    public function create_item_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        return current_user_can( 'manage_categories' );
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool|WP_Error True if the request has access to update the item, WP_Error object otherwise.
     */
    public function update_item_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        return current_user_can( 'manage_categories' );
    }

    /**
     * Check if a given request has access to delete a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
     */
    public function delete_item_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        return current_user_can( 'manage_categories' );
    }

    /**
     * Retrieves the query params for the collections.
     *
     * @return array Query parameters for the collection.
     */
    public function get_collection_params(): array {
        return [
            'context' => [
                'description' => __( 'Scope under which the request is made; determines fields present in response.', 'affiliate-product-showcase' ),
                'type'        => 'string',
                'default'     => 'view',
                'enum'        => [ 'view', 'edit' ],
            ],
            'search' => [
                'description' => __( 'Limit results to those matching a string.', 'affiliate-product-showcase' ),
                'type'        => 'string',
            ],
            'status' => [
                'description' => __( 'Limit results to ribbons with a specific status.', 'affiliate-product-showcase' ),
                'type'        => 'string',
                'enum'        => [ 'published', 'draft' ],
            ],
            'featured' => [
                'description' => __( 'Limit results to featured ribbons.', 'affiliate-product-showcase' ),
                'type'        => 'boolean',
            ],
            'orderby' => [
                'description' => __( 'Sort collection by object attribute.', 'affiliate-product-showcase' ),
                'type'        => 'string',
                'default'     => 'name',
                'enum'        => [ 'id', 'name', 'count', 'priority' ],
            ],
            'order' => [
                'description' => __( 'Order sort attribute ascending or descending.', 'affiliate-product-showcase' ),
                'type'        => 'string',
                'default'     => 'asc',
                'enum'        => [ 'asc', 'desc' ],
            ],
            'page' => [
                'description' => __( 'Current page of the collection.', 'affiliate-product-showcase' ),
                'type'        => 'integer',
                'default'     => 1,
                'minimum'     => 1,
            ],
            'per_page' => [
                'description' => __( 'Maximum number of items to be returned in result set.', 'affiliate-product-showcase' ),
                'type'        => 'integer',
                'default'     => 20,
                'minimum'     => 1,
                'maximum'     => 100,
            ],
        ];
    }

    /**
     * Retrieves the item's schema for display / management.
     *
     * @return array Item schema data.
     */
    public function get_item_schema(): array {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'ribbon',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the ribbon.', 'affiliate-product-showcase' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'name'        => [
                    'description' => __( 'Ribbon name.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'required'    => true,
                ],
                'slug'        => [
                    'description' => __( 'URL-friendly slug.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'description' => [
                    'description' => __( 'Ribbon description.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'count'       => [
                    'description' => __( 'Number of products with this ribbon.', 'affiliate-product-showcase' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'color'       => [
                    'description' => __( 'Display color (hex code).', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'icon'        => [
                    'description' => __( 'Icon identifier/class.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'priority'    => [
                    'description' => __( 'Display priority.', 'affiliate-product-showcase' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                ],
                'status'      => [
                    'description' => __( 'Visibility status.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'enum'        => [ 'published', 'draft' ],
                ],
                'featured'    => [
                    'description' => __( 'Featured flag.', 'affiliate-product-showcase' ),
                    'type'        => 'boolean',
                    'context'     => [ 'view', 'edit' ],
                ],
                'is_default'  => [
                    'description' => __( 'Default ribbon flag.', 'affiliate-product-showcase' ),
                    'type'        => 'boolean',
                    'context'     => [ 'view', 'edit' ],
                ],
                'image_url'   => [
                    'description' => __( 'Image URL.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'created_at'  => [
                    'description' => __( 'Creation timestamp.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'updated_at'  => [
                    'description' => __( 'Last update timestamp.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
            ],
        ];
    }

    /**
     * Prepares a single ribbon output for response.
     *
     * @param Ribbon $ribbon Ribbon object.
     * @return array Response data.
     */
    private function prepare_item_for_response( Ribbon $ribbon ): array {
        return $ribbon->to_array();
    }
}