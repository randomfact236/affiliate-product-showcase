<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Security;

use AffiliateProductShowcase\Helpers\Logger;

/**
 * Sanitizer
 *
 * Provides comprehensive input sanitization and output escaping
 * to prevent XSS, SQL injection, and other security vulnerabilities.
 *
 * @package AffiliateProductShowcase\Security
 * @since 1.0.0
 */
class Sanitizer {

    /**
     * Sanitize a string value
     *
     * @param string $value Value to sanitize
     * @param string $type Sanitization type (text, textarea, url, email, etc.)
     * @return string
     */
    public static function string( string $value, string $type = 'text' ): string {
        switch ( $type ) {
            case 'text':
                return sanitize_text_field( $value );
            case 'textarea':
                return sanitize_textarea_field( $value );
            case 'url':
                return esc_url_raw( $value );
            case 'email':
                return sanitize_email( $value );
            case 'html':
                return wp_kses_post( $value );
            case 'html_class':
                return sanitize_html_class( $value );
            case 'key':
                return sanitize_key( $value );
            case 'title':
                return sanitize_title( $value );
            case 'filename':
                return sanitize_file_name( $value );
            case 'slug':
                return sanitize_title( $value );
            case 'hex_color':
                return sanitize_hex_color( $value );
            case 'mime_type':
                return sanitize_mime_type( $value );
            default:
                return sanitize_text_field( $value );
        }
    }

    /**
     * Sanitize an integer value
     *
     * @param mixed $value Value to sanitize
     * @param int $default Default value if invalid
     * @return int
     */
    public static function integer( $value, int $default = 0 ): int {
        return is_numeric( $value ) ? (int) $value : $default;
    }

    /**
     * Sanitize a float value
     *
     * @param mixed $value Value to sanitize
     * @param float $default Default value if invalid
     * @return float
     */
    public static function float( $value, float $default = 0.0 ): float {
        return is_numeric( $value ) ? (float) $value : $default;
    }

    /**
     * Sanitize a boolean value
     *
     * @param mixed $value Value to sanitize
     * @return bool
     */
    public static function boolean( $value ): bool {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Sanitize an array of values
     *
     * @param array $array Array to sanitize
     * @param string $type Sanitization type for values
     * @return array
     */
    public static function array( array $array, string $type = 'text' ): array {
        return array_map(
            function( $value ) use ( $type ) {
                return is_array( $value ) 
                    ? self::array( $value, $type )
                    : self::string( (string) $value, $type );
            },
            $array
        );
    }

    /**
     * Sanitize product data
     *
     * @param array $data Product data to sanitize
     * @return array
     */
    public static function productData( array $data ): array {
        $sanitized = [];

        if ( isset( $data['title'] ) ) {
            $sanitized['title'] = self::string( $data['title'], 'text' );
        }

        if ( isset( $data['description'] ) ) {
            $sanitized['description'] = self::string( $data['description'], 'textarea' );
        }

        if ( isset( $data['price'] ) ) {
            $sanitized['price'] = self::float( $data['price'] );
        }

        if ( isset( $data['affiliate_url'] ) ) {
            $sanitized['affiliate_url'] = self::string( $data['affiliate_url'], 'url' );
        }

        if ( isset( $data['image_url'] ) ) {
            $sanitized['image_url'] = self::string( $data['image_url'], 'url' );
        }

        if ( isset( $data['sku'] ) ) {
            $sanitized['sku'] = self::string( $data['sku'], 'text' );
        }

        if ( isset( $data['brand'] ) ) {
            $sanitized['brand'] = self::string( $data['brand'], 'text' );
        }

        if ( isset( $data['category'] ) ) {
            $sanitized['category'] = is_array( $data['category'] )
                ? self::array( $data['category'], 'text' )
                : [ self::string( $data['category'], 'text' ) ];
        }

        if ( isset( $data['tags'] ) ) {
            $sanitized['tags'] = is_array( $data['tags'] )
                ? self::array( $data['tags'], 'text' )
                : [ self::string( $data['tags'], 'text' ) ];
        }

        if ( isset( $data['rating'] ) ) {
            $sanitized['rating'] = self::float( $data['rating'], 0.0 );
        }

        if ( isset( $data['in_stock'] ) ) {
            $sanitized['in_stock'] = self::boolean( $data['in_stock'] );
        }

        return $sanitized;
    }

    /**
     * Sanitize settings data
     *
     * @param array $data Settings data to sanitize
     * @return array
     */
    public static function settingsData( array $data ): array {
        $sanitized = [];

        if ( isset( $data['display_mode'] ) ) {
            $sanitized['display_mode'] = in_array( $data['display_mode'], [ 'grid', 'list', 'carousel' ], true )
                ? $data['display_mode']
                : 'grid';
        }

        if ( isset( $data['items_per_page'] ) ) {
            $sanitized['items_per_page'] = max( 1, min( 100, self::integer( $data['items_per_page'], 12 ) ) );
        }

        if ( isset( $data['cache_duration'] ) ) {
            $sanitized['cache_duration'] = max( 0, self::integer( $data['cache_duration'], 3600 ) );
        }

        if ( isset( $data['enable_analytics'] ) ) {
            $sanitized['enable_analytics'] = self::boolean( $data['enable_analytics'] );
        }

        if ( isset( $data['tracking_enabled'] ) ) {
            $sanitized['tracking_enabled'] = self::boolean( $data['tracking_enabled'] );
        }

        if ( isset( $data['custom_css'] ) ) {
            $sanitized['custom_css'] = self::string( $data['custom_css'], 'css' );
        }

        return $sanitized;
    }

    /**
     * Escape output for HTML
     *
     * @param string $value Value to escape
     * @return string
     */
    public static function escapeHtml( string $value ): string {
        return esc_html( $value );
    }

    /**
     * Escape output for HTML attribute
     *
     * @param string $value Value to escape
     * @return string
     */
    public static function escapeAttr( string $value ): string {
        return esc_attr( $value );
    }

    /**
     * Escape output for URL
     *
     * @param string $value Value to escape
     * @return string
     */
    public static function escapeUrl( string $value ): string {
        return esc_url( $value );
    }

    /**
     * Escape output for JavaScript
     *
     * @param string $value Value to escape
     * @return string
     */
    public static function escapeJs( string $value ): string {
        return esc_js( $value );
    }

    /**
     * Sanitize and escape a value for safe output
     *
     * @param mixed $value Value to sanitize and escape
     * @param string $context Context (html, attr, url, js)
     * @return string
     */
    public static function safeOutput( $value, string $context = 'html' ): string {
        $sanitized = is_string( $value ) ? self::string( $value ) : '';

        switch ( $context ) {
            case 'html':
                return self::escapeHtml( $sanitized );
            case 'attr':
                return self::escapeAttr( $sanitized );
            case 'url':
                return self::escapeUrl( $sanitized );
            case 'js':
                return self::escapeJs( $sanitized );
            default:
                return self::escapeHtml( $sanitized );
        }
    }

    /**
     * Validate and sanitize email
     *
     * @param string $email Email to validate
     * @return string|false Valid email or false
     */
    public static function email( string $email ) {
        $sanitized = sanitize_email( $email );
        return is_email( $sanitized ) ? $sanitized : false;
    }

    /**
     * Validate and sanitize URL
     *
     * @param string $url URL to validate
     * @return string|false Valid URL or false
     */
    public static function url( string $url ) {
        $sanitized = esc_url_raw( $url );
        return filter_var( $sanitized, FILTER_VALIDATE_URL ) ? $sanitized : false;
    }

    /**
     * Sanitize JSON input
     *
     * @param string $json JSON string to sanitize
     * @return array|object|false Decoded and sanitized JSON or false
     */
    public static function json( string $json ) {
        $decoded = json_decode( $json, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return false;
        }

        return is_array( $decoded ) ? self::array( $decoded ) : $decoded;
    }

    /**
     * Remove potentially dangerous HTML
     *
     * @param string $html HTML to clean
     * @return string
     */
    public static function stripTags( string $html ): string {
        $allowed = [
            'a'      => [ 'href' => [], 'title' => [], 'target' => [] ],
            'b'      => [],
            'strong' => [],
            'i'      => [],
            'em'     => [],
            'p'      => [],
            'br'     => [],
            'ul'     => [],
            'ol'     => [],
            'li'     => [],
        ];

        return wp_kses( $html, $allowed );
    }

    /**
     * Sanitize a filename for upload
     *
     * @param string $filename Filename to sanitize
     * @return string
     */
    public static function filename( string $filename ): string {
        $filename = sanitize_file_name( $filename );
        
        // Remove dots from beginning and end
        $filename = trim( $filename, '.' );
        
        // Ensure filename is not empty
        if ( empty( $filename ) ) {
            $filename = 'file-' . wp_generate_password( 8, false );
        }

        return $filename;
    }

    /**
     * Clean and normalize an array of input
     *
     * @param array $input Input array to clean
     * @param array $rules Sanitization rules per key
     * @return array
     */
    public static function cleanInput( array $input, array $rules = [] ): array {
        $cleaned = [];

        foreach ( $input as $key => $value ) {
            $rule = $rules[ $key ] ?? 'text';

            if ( is_array( $value ) ) {
                $cleaned[ $key ] = self::array( $value, $rule );
            } else {
                $cleaned[ $key ] = self::string( (string) $value, $rule );
            }
        }

        return $cleaned;
    }
}
