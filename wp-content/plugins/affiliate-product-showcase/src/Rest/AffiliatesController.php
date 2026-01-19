<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use AffiliateProductShowcase\Helpers\Logger;
use AffiliateProductShowcase\Services\AffiliateService;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Affiliates REST Controller
 *
 * Handles REST API endpoints for affiliate management.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 */
class AffiliatesController extends RestController {

    /**
     * Namespace
     *
     * @var string
     */
    protected $namespace = 'affiliate-product-showcase/v1';

    /**
     * Route base
     *
     * @var string
     */
    protected $rest_base = 'affiliates';

    /**
     * Affiliate service
     *
     * @var AffiliateService
     */
    private AffiliateService $affiliate_service;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->affiliate_service = new AffiliateService();
    }

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes(): void {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'getAffiliates' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                    'args'                => $this->get_collection_params(),
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'getAffiliate' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                    'args'                => [
                        'id' => [
                            'required'          => true,
                            'validate_callback' => 'rest_validate_request_arg',
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'updateAffiliate' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'deleteAffiliate' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                    'args'                => [
                        'id' => [
                            'required'          => true,
                            'validate_callback' => 'rest_validate_request_arg',
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/track',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'trackClick' ],
                    'permission_callback' => '__return_true',
                    'args'                => [
                        'product_id' => [
                            'required'          => true,
                            'validate_callback' => 'rest_validate_request_arg',
                            'sanitize_callback' => 'absint',
                        ],
                        'affiliate_id' => [
                            'required'          => false,
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * Get all affiliates
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function getAffiliates( WP_REST_Request $request ): WP_REST_Response {
        $params = $request->get_params();
        
        $args = [
            'post_type'      => 'aps_product',
            'posts_per_page' => $params['per_page'] ?? 10,
            'paged'          => $params['page'] ?? 1,
            'orderby'        => $params['orderby'] ?? 'date',
            'order'          => $params['order'] ?? 'DESC',
        ];

        if ( ! empty( $params['search'] ) ) {
            $args['s'] = $params['search'];
        }

        $query = new \WP_Query( $args );
        $affiliates = [];

        foreach ( $query->posts as $post ) {
            $affiliates[] = $this->affiliate_service->prepareAffiliateForApi( $post->ID );
        }

        return new WP_REST_Response( [
            'data'  => $affiliates,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ], 200 );
    }

    /**
     * Get single affiliate
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function getAffiliate( WP_REST_Request $request ) {
        $id = $request->get_param( 'id' );
        
        $affiliate = $this->affiliate_service->prepareAffiliateForApi( $id );

        if ( ! $affiliate ) {
            return new WP_Error(
                'affiliate_not_found',
                __( 'Affiliate product not found.', 'affiliate-product-showcase' ),
                [ 'status' => 404 ]
            );
        }

        return new WP_REST_Response( $affiliate, 200 );
    }

    /**
     * Update affiliate
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function updateAffiliate( WP_REST_Request $request ) {
        $id = $request->get_param( 'id' );
        $data = $request->get_json_params();

        $result = $this->affiliate_service->updateAffiliate( $id, $data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( [
            'message' => __( 'Affiliate updated successfully.', 'affiliate-product-showcase' ),
            'data'    => $this->affiliate_service->prepareAffiliateForApi( $id ),
        ], 200 );
    }

    /**
     * Delete affiliate
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function deleteAffiliate( WP_REST_Request $request ) {
        $id = $request->get_param( 'id' );
        $force = $request->get_param( 'force' ) ?? false;

        $result = wp_delete_post( $id, $force );

        if ( ! $result ) {
            return new WP_Error(
                'delete_failed',
                __( 'Failed to delete affiliate product.', 'affiliate-product-showcase' ),
                [ 'status' => 500 ]
            );
        }

        return new WP_REST_Response( [
            'message' => __( 'Affiliate deleted successfully.', 'affiliate-product-showcase' ),
        ], 200 );
    }

    /**
     * Track affiliate click
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function trackClick( WP_REST_Request $request ) {
        $product_id = $request->get_param( 'product_id' );
        $affiliate_id = $request->get_param( 'affiliate_id' );

        $result = $this->affiliate_service->trackClick( $product_id, $affiliate_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( [
            'message' => __( 'Click tracked successfully.', 'affiliate-product-showcase' ),
            'tracked' => true,
        ], 200 );
    }

    /**
     * Check permissions
     *
     * @return bool
     */
    public function checkPermission(): bool {
        return current_user_can( 'edit_affiliate_products' );
    }

    /**
     * Get collection params
     *
     * @return array
     */
    public function get_collection_params(): array {
        return [
            'page'     => [
                'description'       => __( 'Current page of the collection.', 'affiliate-product-showcase' ),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback'  => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'per_page' => [
                'description'       => __( 'Maximum number of items to be returned in result set.', 'affiliate-product-showcase' ),
                'type'              => 'integer',
                'default'           => 10,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback'  => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'search'   => [
                'description'       => __( 'Limit results to those matching a string.', 'affiliate-product-showcase' ),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'orderby'  => [
                'description'       => __( 'Sort collection by object attribute.', 'affiliate-product-showcase' ),
                'type'              => 'string',
                'default'           => 'date',
                'enum'              => [ 'date', 'title', 'price', 'rating' ],
                'sanitize_callback' => 'sanitize_key',
            ],
            'order'    => [
                'description'       => __( 'Order sort attribute ascending or descending.', 'affiliate-product-showcase' ),
                'type'              => 'string',
                'default'           => 'DESC',
                'enum'              => [ 'ASC', 'DESC' ],
                'sanitize_callback' => 'strtoupper',
            ],
        ];
    }

    /**
     * Get item schema
     *
     * @return array
     */
    public function get_item_schema(): array {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'affiliate',
            'type'       => 'object',
            'properties' => [
                'id'            => [
                    'description' => __( 'Unique identifier for the affiliate.', 'affiliate-product-showcase' ),
                    'type'        => 'integer',
                ],
                'title'         => [
                    'description' => __( 'Affiliate title.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                ],
                'content'       => [
                    'description' => __( 'Affiliate content.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                ],
                'price'         => [
                    'description' => __( 'Product price.', 'affiliate-product-showcase' ),
                    'type'        => 'number',
                ],
                'affiliate_url' => [
                    'description' => __( 'Affiliate URL.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                ],
                'image_url'     => [
                    'description' => __( 'Product image URL.', 'affiliate-product-showcase' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                ],
                'rating'        => [
                    'description' => __( 'Product rating.', 'affiliate-product-showcase' ),
                    'type'        => 'number',
                    'minimum'     => 0,
                    'maximum'     => 5,
                ],
            ],
        ];
    }
}
