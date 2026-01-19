<?php
/**
 * Sanitizer
 *
 * Provides comprehensive input sanitization and output escaping
 * to prevent XSS, SQL injection, and other security vulnerabilities.
 *
 * @package AffiliateProductShowcase\Security
 * @since 1.0.0
 */

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
 * @author Development Team
 */
class Sanitizer {

    /**
     * Sanitize a string value
     *
     * Sanitizes string input based on specified type.
     * Supports multiple sanitization types: text, textarea, url, email, etc.
     *
     * @param string $value Value to sanitize
     * @param string $type Sanitization type (text, textarea, url, email, etc.)
     * @return string Sanitized string
     * @since 1.0.0
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
     * Validates and converts value to integer.
     * Returns default value if input is not numeric.
     *
     * @param mixed $value Value to sanitize
     * @param int $default Default value if invalid
     * @return int Sanitized integer
     * @since 1.0.0
     */
    public static function integer( $value, int $default = 0 ): int {
        return is_numeric( $value ) ? (int) $value : $default;
    }

    /**
     * Sanitize a float value
     *
     * Validates and converts value to float.
     * Returns default value if input is not numeric.
     *
     * @param mixed $value Value to sanitize
     * @param float $default Default value if invalid
     * @return float Sanitized float
     * @since 1.0.0
     */
    public static function float( $value, float $default = 0.0 ): float {
        return is_numeric( $value ) ? (float) $value : $default;
    }

    /**
     * Sanitize a boolean value
     *
     * Converts value to boolean using PHP filter.
     * Handles various input formats (strings, integers, etc.).
     *
     * @param mixed $value Value to sanitize
     * @return bool Sanitized boolean
     * @since 1.0.0
     */
    public static function boolean( $value ): bool {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Sanitize an array of values
     *
     * Recursively sanitizes all values in an array.
     * Supports nested arrays for complex data structures.
     *
     * @param array $array Array to sanitize
     * @param string $type Sanitization type for values
     * @return array Sanitized array
     * @since 1.0.0
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
     * Sanitizes all product-related fields including
     * title, description, price, URLs, and taxonomy IDs.
     *
     * @param array<string, mixed> $data Product data to sanitize
     * @return array<string, mixed> Sanitized product data
     * @since 1.0.0
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

		if ( isset( $data['category_ids'] ) ) {
			$sanitized['category_ids'] = is_array( $data['category_ids'] )
				? array_map( 'intval', $data['category_ids'] )
				: [ intval( $data['category_ids'] ) ];
		}

		if ( isset( $data['tag_ids'] ) ) {
			$sanitized['tag_ids'] = is_array( $data['tag_ids'] )
				? array_map( 'intval', $data['tag_ids'] )
				: [ intval( $data['tag_ids'] ) ];
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
     * Sanitizes plugin settings including display mode,
     * cache duration, and feature toggles.
     *
     * @param array<string, mixed> $data Settings data to sanitize
     * @return array<string, mixed> Sanitized settings data
     * @since 1.0.0
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
     * Escapes special characters for safe HTML output.
     * Prevents XSS attacks when displaying user content.
     *
     * @param string $value Value to escape
     * @return string Escaped HTML
     * @since 1.0.0
     */
    public static function escapeHtml( string $value ): string {
        return esc_html( $value );
    }

    /**
     * Escape output for HTML attribute
     *
     * Escapes special characters for safe use in HTML attributes.
     * Prevents XSS attacks in attribute values.
     *
     * @param string $value Value to escape
     * @return string Escaped attribute value
     * @since 1.0.0
     */
    public static function escapeAttr( string $value ): string {
        return esc_attr( $value );
    }

    /**
     * Escape output for URL
     *
     * Escapes special characters for safe URL display.
     * Prevents XSS attacks in URLs.
     *
     * @param string $value Value to escape
     * @return string Escaped URL
     * @since 1.0.0
     */
    public static function escapeUrl( string $value ): string {
        return esc_url( $value );
    }

    /**
     * Escape output for JavaScript
     *
     * Escapes special characters for safe JavaScript output.
     * Prevents XSS attacks in JavaScript code.
     *
     * @param string $value Value to escape
     * @return string Escaped JavaScript string
     * @since 1.0.0
     */
    public static function escapeJs( string $value ): string {
        return esc_js( $value );
    }

    /**
     * Sanitize and escape a value for safe output
     *
     * Combines sanitization and escaping for safe output.
     * Automatically selects appropriate escaping based on context.
     *
     * @param mixed $value Value to sanitize and escape
     * @param string $context Context (html, attr, url, js)
     * @return string Sanitized and escaped string
     * @since 1.0.0
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
     * Sanitizes and validates email address.
     * Returns false if email is invalid.
     *
     * @param string $email Email to validate
     * @return string|false Valid email or false if invalid
     * @since 1.0.0
     */
    public static function email( string $email ) {
        $sanitized = sanitize_email( $email );
        return is_email( $sanitized ) ? $sanitized : false;
    }

    /**
     * Validate and sanitize URL
     *
     * Sanitizes and validates URL.
     * Returns false if URL is invalid.
     *
     * @param string $url URL to validate
     * @return string|false Valid URL or false if invalid
     * @since 1.0.0
     */
    public static function url( string $url ) {
        $sanitized = esc_url_raw( $url );
        return filter_var( $sanitized, FILTER_VALIDATE_URL ) ? $sanitized : false;
    }

    /**
     * Sanitize JSON input
     *
     * Decodes JSON string and sanitizes all values.
     * Returns false if JSON is invalid.
     *
     * @param string $json JSON string to sanitize
     * @return array|object|false Decoded and sanitized JSON or false
     * @since 1.0.0
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
     * Removes all HTML except for safe tags.
     * Allows basic formatting tags: a, b, strong, i, em, p, br, ul, ol, li.
     *
     * @param string $html HTML to clean
     * @return string Sanitized HTML
     * @since 1.0.0
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
     * Sanitizes filename for secure file uploads.
     * Removes special characters and ensures valid filename.
     *
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     * @since 1.0.0
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
     * Sanitizes input array with custom rules per key.
     * Useful for form submissions and API requests.
     *
     * @param array<string, mixed> $input Input array to clean
     * @param array<string, string> $rules Sanitization rules per key
     * @return array<string, mixed> Cleaned and sanitized array
     * @since 1.0.0
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
