<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Helpers\Logger;
use AffiliateProductShowcase\Security\Validator;

/**
 * Settings Validator Service
 *
 * Provides specialized validation for plugin settings.
 * Ensures settings are valid before saving.
 *
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 */
class SettingsValidator {

    /**
     * Allowed display modes
     *
     * @var array
     */
    private const ALLOWED_DISPLAY_MODES = [
        'grid',
        'list',
        'carousel',
    ];

    /**
     * Default settings with validation rules
     *
     * @var array
     */
    private const DEFAULT_SETTINGS = [
        'display_mode'       => 'grid',
        'items_per_page'     => 12,
        'cache_duration'     => 3600,
        'enable_analytics'   => true,
        'tracking_enabled'   => true,
        'custom_css'        => '',
    ];

    /**
     * Validate settings data
     *
     * @param array $settings Settings to validate
     * @return array Validation result
     */
    public function validate( array $settings ): array {
        $result = [
            'valid'   => true,
            'errors'  => [],
            'sanitized' => [],
        ];

        // Use base validator for common checks
        $base_validation = Validator::validateSettings( $settings );
        
        if ( ! $base_validation['valid'] ) {
            $result['valid'] = false;
            $result['errors'] = array_merge( $result['errors'], $base_validation['errors'] );
        }

        // Sanitize and validate each setting
        $result['sanitized']['display_mode'] = $this->validateDisplayMode( $settings['display_mode'] ?? null );
        $result['sanitized']['items_per_page'] = $this->validateItemsPerPage( $settings['items_per_page'] ?? null );
        $result['sanitized']['cache_duration'] = $this->validateCacheDuration( $settings['cache_duration'] ?? null );
        $result['sanitized']['enable_analytics'] = $this->validateBoolean( $settings['enable_analytics'] ?? null );
        $result['sanitized']['tracking_enabled'] = $this->validateBoolean( $settings['tracking_enabled'] ?? null );
        $result['sanitized']['custom_css'] = $this->validateCustomCss( $settings['custom_css'] ?? '' );

        return $result;
    }

    /**
     * Validate display mode
     *
     * @param mixed $value Display mode value
     * @return string Validated display mode
     */
    private function validateDisplayMode( $value ): string {
        if ( ! in_array( $value, self::ALLOWED_DISPLAY_MODES, true ) ) {
            return self::DEFAULT_SETTINGS['display_mode'];
        }

        return $value;
    }

    /**
     * Validate items per page
     *
     * @param mixed $value Items per page value
     * @return int Validated items per page
     */
    private function validateItemsPerPage( $value ): int {
        $int_value = intval( $value );

        // Must be between 1 and 100
        if ( $int_value < 1 || $int_value > 100 ) {
            return self::DEFAULT_SETTINGS['items_per_page'];
        }

        return $int_value;
    }

    /**
     * Validate cache duration
     *
     * @param mixed $value Cache duration value
     * @return int Validated cache duration
     */
    private function validateCacheDuration( $value ): int {
        $int_value = intval( $value );

        // Must be non-negative
        if ( $int_value < 0 ) {
            return self::DEFAULT_SETTINGS['cache_duration'];
        }

        return $int_value;
    }

    /**
     * Validate boolean setting
     *
     * @param mixed $value Boolean value
     * @return bool Validated boolean
     */
    private function validateBoolean( $value ): bool {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Validate custom CSS
     *
     * @param string $css CSS string
     * @return string Sanitized CSS
     */
    private function validateCustomCss( string $css ): string {
        // Remove potentially dangerous content
        $css = $this->sanitizeCss( $css );

        return $css;
    }

    /**
     * Sanitize CSS content
     *
     * @param string $css CSS to sanitize
     * @return string Sanitized CSS
     */
    private function sanitizeCss( string $css ): string {
        // Remove expressions (XSS risk)
        $css = preg_replace( '/expression\s*\(.*?\)/i', '', $css );

        // Remove @import and @charset (potential security risks)
        $css = preg_replace( '/@import\s+[^;]+;/i', '', $css );
        $css = preg_replace( '/@charset\s+[^;]+;/i', '', $css );

        // Remove javascript: URLs
        $css = preg_replace( '/javascript:/i', '', $css );

        // Remove behavior property
        $css = preg_replace( '/behavior\s*:/i', '', $css );

        // Remove url() with javascript:
        $css = preg_replace( '/url\s*\(\s*["\']?javascript:/i', '', $css );

        return trim( $css );
    }

    /**
     * Get default settings
     *
     * @return array
     */
    public function getDefaults(): array {
        return self::DEFAULT_SETTINGS;
    }

    /**
     * Get setting with fallback to default
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public function getSetting( string $key, $default = null ) {
        $defaults = self::getDefaults();
        
        return $defaults[ $key ] ?? $default;
    }

    /**
     * Validate and merge settings
     *
     * @param array $new_settings New settings to apply
     * @param array $current_settings Current settings
     * @return array Validated and merged settings
     */
    public function mergeSettings( array $new_settings, array $current_settings = [] ): array {
        $validation = $this->validate( $new_settings );

        if ( ! $validation['valid'] ) {
            Logger::warning( 'Settings validation failed', [
                'errors' => $validation['errors'],
            ] );
        }

        // Merge with defaults first
        $settings = array_merge(
            self::getDefaults(),
            $current_settings,
            $validation['sanitized']
        );

        return $settings;
    }

    /**
     * Validate array of settings
     *
     * @param array $settings_array Array of settings to validate
     * @return array Array of validation results
     */
    public function validateArray( array $settings_array ): array {
        $results = [];

        foreach ( $settings_array as $key => $settings ) {
            $results[ $key ] = $this->validate( $settings );
        }

        return $results;
    }

    /**
     * Check if settings are valid
     *
     * @param array $settings Settings to check
     * @return bool
     */
    public function isValid( array $settings ): bool {
        $validation = $this->validate( $settings );
        return $validation['valid'];
    }

    /**
     * Get validation errors as HTML
     *
     * @param array $errors Validation errors
     * @return string HTML formatted errors
     */
    public function getErrorsHtml( array $errors ): string {
        if ( empty( $errors ) ) {
            return '';
        }

        $html = '<div class="notice notice-error"><ul>';

        foreach ( $errors as $field => $messages ) {
            foreach ( (array) $messages as $message ) {
                $html .= sprintf( '<li><strong>%s:</strong> %s</li>', ucfirst( $field ), esc_html( $message ) );
            }
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Get allowed display modes
     *
     * @return array
     */
    public static function getAllowedDisplayModes(): array {
        return self::ALLOWED_DISPLAY_MODES;
    }
}
