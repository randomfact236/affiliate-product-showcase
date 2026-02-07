<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Security;

use AffiliateProductShowcase\Helpers\Logger;

/**
 * CSRF Protection
 *
 * Provides Cross-Site Request Forgery protection
 * using WordPress nonces and additional security measures.
 *
 * @package AffiliateProductShowcase\Security
 * @since 1.0.0
 */
class CSRFProtection {

    /**
     * Default nonce action name
     *
     * @var string
     */
    private const DEFAULT_ACTION = 'affiliate_product_showcase_nonce';

    /**
     * Nonce lifetime in seconds (12 hours)
     *
     * @var int
     */
    private const NONCE_LIFETIME = 43200;

    /**
     * Generate a nonce for a specific action
     *
     * @param string $action Action name
     * @return string Nonce token
     */
    public static function generateNonce( string $action = self::DEFAULT_ACTION ): string {
        return wp_create_nonce( $action );
    }

    /**
     * Verify a nonce for a specific action
     *
     * @param string $nonce Nonce token to verify
     * @param string $action Action name
     * @return bool True if valid, false otherwise
     */
    public static function verifyNonce( string $nonce, string $action = self::DEFAULT_ACTION ): bool {
        return wp_verify_nonce( $nonce, $action ) !== false;
    }

    /**
     * Verify nonce from request
     *
     * @param string $nonce_field Name of nonce field in request
     * @param string $action Action name
     * @return bool True if valid, false otherwise
     */
    public static function verifyRequest( string $nonce_field = '_wpnonce', string $action = self::DEFAULT_ACTION ): bool {
        $nonce = $_POST[ $nonce_field ] ?? $_REQUEST[ $nonce_field ] ?? '';

        if ( empty( $nonce ) ) {
            return false;
        }

        return self::verifyNonce( $nonce, $action );
    }

    /**
     * Output nonce field for forms
     *
     * @param string $action Action name
     * @param string $name Field name
     * @param bool $referer Whether to referer field
     * @return void
     */
    public static function nonceField( string $action = self::DEFAULT_ACTION, string $name = '_wpnonce', bool $referer = true ): void {
        wp_nonce_field( $action, $name, $referer, false );
    }

    /**
     * Get nonce URL with embedded nonce
     *
     * @param string $actionurl URL to add nonce to
     * @param string $action Action name
     * @param string $name Nonce name
     * @return string URL with nonce
     */
    public static function nonceUrl( string $actionurl, string $action = self::DEFAULT_ACTION, string $name = '_wpnonce' ): string {
        return wp_nonce_url( $actionurl, $action, $name );
    }

    /**
     * Check if current request is a POST request
     *
     * @return bool
     */
    public static function isPost(): bool {
        return isset( $_SERVER['REQUEST_METHOD'] ) && strtoupper( $_SERVER['REQUEST_METHOD'] ) === 'POST';
    }

    /**
     * Check if current request is a GET request
     *
     * @return bool
     */
    public static function isGet(): bool {
        return isset( $_SERVER['REQUEST_METHOD'] ) && strtoupper( $_SERVER['REQUEST_METHOD'] ) === 'GET';
    }

    /**
     * Validate AJAX request with nonce
     *
     * @param string $action Action name
     * @param string $nonce_field Nonce field name
     * @return bool True if valid, false otherwise
     */
    public static function validateAjaxRequest( string $action = self::DEFAULT_ACTION, string $nonce_field = 'nonce' ): bool {
        if ( ! wp_doing_ajax() ) {
            return false;
        }

        $nonce = $_POST[ $nonce_field ] ?? $_GET[ $nonce_field ] ?? '';

        if ( empty( $nonce ) ) {
            return false;
        }

        return self::verifyNonce( $nonce, $action );
    }

    /**
     * Validate REST API request
     *
     * @param \WP_REST_Request $request REST request object
     * @param string $action Action name
     * @param string $nonce_field Nonce field name
     * @return bool True if valid, false otherwise
     */
    public static function validateRestRequest( \WP_REST_Request $request, string $action = self::DEFAULT_ACTION, string $nonce_field = '_wpnonce' ): bool {
        $nonce = $request->get_param( $nonce_field );

        if ( empty( $nonce ) ) {
            return false;
        }

        return self::verifyNonce( $nonce, $action );
    }

    /**
     * Get AJAX error response for invalid nonce
     *
     * @return array
     */
    public static function getAjaxErrorResponse(): array {
        return [
            'success' => false,
            'message' => __( 'Security check failed. Please refresh the page and try again.', 'affiliate-product-showcase' ),
            'code'    => 'invalid_nonce',
        ];
    }

    /**
     * Send AJAX error response for invalid nonce
     *
     * @return void
     */
    public static function sendAjaxErrorResponse(): void {
        wp_send_json_error( self::getAjaxErrorResponse(), 403 );
    }

    /**
     * Add nonce to form data
     *
     * @param array $data Form data
     * @param string $action Action name
     * @param string $name Nonce field name
     * @return array Form data with nonce
     */
    public static function addNonceToData( array $data, string $action = self::DEFAULT_ACTION, string $name = '_wpnonce' ): array {
        $data[ $name ] = self::generateNonce( $action );
        return $data;
    }

    /**
     * Get nonce from request
     *
     * @param string $field Nonce field name
     * @return string|null
     */
    public static function getNonceFromRequest( string $field = '_wpnonce' ): ?string {
        $nonce = $_POST[ $field ] ?? $_GET[ $field ] ?? $_REQUEST[ $field ] ?? null;

        return is_string( $nonce ) ? $nonce : null;
    }

    /**
     * Check and verify nonce with automatic error handling
     *
     * @param string $action Action name
     * @param string $nonce_field Nonce field name
     * @param bool $die_on_fail Whether to die on failure
     * @return bool True if valid, false otherwise
     */
    public static function check( string $action = self::DEFAULT_ACTION, string $nonce_field = '_wpnonce', bool $die_on_fail = true ): bool {
        $nonce = self::getNonceFromRequest( $nonce_field );

        if ( empty( $nonce ) ) {
            if ( $die_on_fail ) {
                wp_die( __( 'Security check failed. No nonce found.', 'affiliate-product-showcase' ), 403 );
            }
            return false;
        }

        if ( ! self::verifyNonce( $nonce, $action ) ) {
            Logger::warning( 'CSRF protection: Invalid nonce detected', [ 'action' => $action ] );
            
            if ( $die_on_fail ) {
                wp_die( __( 'Security check failed. Invalid nonce.', 'affiliate-product-showcase' ), 403 );
            }
            return false;
        }

        return true;
    }

    /**
     * Generate a time-limited nonce
     *
     * @param string $action Action name
     * @param int $lifetime Lifetime in seconds
     * @return string Nonce token
     */
    public static function generateTimedNonce( string $action = self::DEFAULT_ACTION, int $lifetime = self::NONCE_LIFETIME ): string {
        $action .= '_' . floor( time() / $lifetime );
        return wp_create_nonce( $action );
    }

    /**
     * Verify a time-limited nonce
     *
     * @param string $nonce Nonce token
     * @param string $action Action name
     * @param int $lifetime Lifetime in seconds
     * @return bool True if valid, false otherwise
     */
    public static function verifyTimedNonce( string $nonce, string $action = self::DEFAULT_ACTION, int $lifetime = self::NONCE_LIFETIME ): bool {
        $current_tick = floor( time() / $lifetime );
        
        // Check current and previous tick
        for ( $i = 0; $i <= 1; $i++ ) {
            $tick = $current_tick - $i;
            $test_action = $action . '_' . $tick;
            
            if ( wp_verify_nonce( $nonce, $test_action ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate nonce for specific user
     *
     * @param int $user_id User ID
     * @param string $action Action name
     * @return string Nonce token
     */
    public static function generateUserNonce( int $user_id, string $action = self::DEFAULT_ACTION ): string {
        $action .= '_user_' . $user_id;
        return wp_create_nonce( $action );
    }

    /**
     * Verify nonce for specific user
     *
     * @param string $nonce Nonce token
     * @param int $user_id User ID
     * @param string $action Action name
     * @return bool True if valid, false otherwise
     */
    public static function verifyUserNonce( string $nonce, int $user_id, string $action = self::DEFAULT_ACTION ): bool {
        $action .= '_user_' . $user_id;
        return wp_verify_nonce( $nonce, $action ) !== false;
    }

    /**
     * Add nonce verification to form submission handler
     *
     * @param callable $callback Original callback
     * @param string $action Action name
     * @param string $nonce_field Nonce field name
     * @return callable Wrapped callback
     */
    public static function wrapFormSubmission( callable $callback, string $action = self::DEFAULT_ACTION, string $nonce_field = '_wpnonce' ): callable {
        return function() use ( $callback, $action, $nonce_field ) {
            if ( ! self::check( $action, $nonce_field, false ) ) {
                return;
            }

            return call_user_func_array( $callback, func_get_args() );
        };
    }
}
