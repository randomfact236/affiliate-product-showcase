<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use AffiliateProductShowcase\Helpers\Logger;
use AffiliateProductShowcase\Services\SettingsValidator;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Settings REST Controller
 *
 * Handles REST API endpoints for plugin settings.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 */
class SettingsController extends RestController {

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
    protected $rest_base = 'settings';

    /**
     * Settings validator
     *
     * @var SettingsValidator
     */
    private SettingsValidator $validator;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->validator = new SettingsValidator();
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
                    'callback'            => [ $this, 'getSettings' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'updateSettings' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                    'args'                => $this->get_endpoint_args_for_item_schema(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<key>[a-zA-Z0-9_-]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'getSetting' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                    'args'                => [
                        'key' => [
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'updateSetting' ],
                    'permission_callback' => [ $this, 'checkPermission' ],
                    'args'                => [
                        'key'   => [
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'value' => [
                            'required'          => false,
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get all settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function getSettings( WP_REST_Request $request ): WP_REST_Response {
        $settings_repository = new \AffiliateProductShowcase\Repositories\SettingsRepository();
        $settings = $settings_repository->getAll();

        return new WP_REST_Response( $settings, 200 );
    }

    /**
     * Get a single setting
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function getSetting( WP_REST_Request $request ) {
        $key = $request->get_param( 'key' );
        $settings_repository = new \AffiliateProductShowcase\Repositories\SettingsRepository();
        $value = $settings_repository->get( $key );

        if ( $value === null ) {
            return new WP_Error(
                'setting_not_found',
                __( 'Setting not found.', 'affiliate-product-showcase' ),
                [ 'status' => 404 ]
            );
        }

        return new WP_REST_Response(
            [ 'key' => $key, 'value' => $value ],
            200
        );
    }

    /**
     * Update all settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function updateSettings( WP_REST_Request $request ) {
        $new_settings = $request->get_json_params();

        if ( empty( $new_settings ) ) {
            return new WP_Error(
                'no_settings',
                __( 'No settings provided.', 'affiliate-product-showcase' ),
                [ 'status' => 400 ]
            );
        }

        $settings_repository = new \AffiliateProductShowcase\Repositories\SettingsRepository();
        $current_settings = $settings_repository->getAll();

        // Validate settings
        $validation = $this->validator->mergeSettings( $new_settings, $current_settings );

        if ( ! $validation['valid'] ) {
            return new WP_Error(
                'validation_failed',
                __( 'Settings validation failed.', 'affiliate-product-showcase' ),
                [ 'status' => 400, 'errors' => $validation['errors'] ]
            );
        }

        // Save settings
        $result = $settings_repository->saveAll( $validation['sanitized'] );

        if ( $result ) {
            return new WP_REST_Response(
                [
                    'message'  => __( 'Settings updated successfully.', 'affiliate-product-showcase' ),
                    'settings' => $validation['sanitized'],
                ],
                200
            );
        }

        return new WP_Error(
            'save_failed',
            __( 'Failed to save settings.', 'affiliate-product-showcase' ),
            [ 'status' => 500 ]
        );
    }

    /**
     * Update a single setting
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function updateSetting( WP_REST_Request $request ) {
        $key = $request->get_param( 'key' );
        $value = $request->get_param( 'value' );

        $settings_repository = new \AffiliateProductShowcase\Repositories\SettingsRepository();

        // Validate single setting
        $validation = $this->validator->validate( [ $key => $value ] );

        if ( ! $validation['valid'] ) {
            return new WP_Error(
                'validation_failed',
                __( 'Setting validation failed.', 'affiliate-product-showcase' ),
                [ 'status' => 400, 'errors' => $validation['errors'] ]
            );
        }

        // Save setting
        $result = $settings_repository->save( $key, $validation['sanitized'][ $key ] );

        if ( $result ) {
            return new WP_REST_Response(
                [
                    'message' => __( 'Setting updated successfully.', 'affiliate-product-showcase' ),
                    'key'     => $key,
                    'value'   => $validation['sanitized'][ $key ],
                ],
                200
            );
        }

        return new WP_Error(
            'save_failed',
            __( 'Failed to save setting.', 'affiliate-product-showcase' ),
            [ 'status' => 500 ]
        );
    }

    /**
     * Check permissions
     *
     * @return bool
     */
    public function checkPermission(): bool {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get item schema
     *
     * @return array
     */
    public function get_item_schema(): array {
        return [
            '$schema'     => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'settings',
            'type'       => 'object',
            'properties' => [
                'display_mode'     => [
                    'type'    => 'string',
                    'enum'    => [ 'grid', 'list', 'carousel' ],
                    'default'  => 'grid',
                ],
                'items_per_page'   => [
                    'type'    => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'default'  => 12,
                ],
                'cache_duration'   => [
                    'type'    => 'integer',
                    'minimum' => 0,
                    'default'  => 3600,
                ],
                'enable_analytics' => [
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'tracking_enabled' => [
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'custom_css'        => [
                    'type'    => 'string',
                    'default' => '',
                ],
            ],
        ],
        ];
    }
}
