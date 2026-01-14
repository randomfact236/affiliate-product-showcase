<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Security;

use AffiliateProductShowcase\Helpers\Logger;

/**
 * Validator
 *
 * Provides comprehensive data validation for various input types.
 * Used to ensure data integrity and prevent invalid data submission.
 *
 * @package AffiliateProductShowcase\Security
 * @since 1.0.0
 */
class Validator {

    /**
     * Validation errors
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Validation rules
     *
     * @var array
     */
    private array $rules = [];

    /**
     * Data to validate
     *
     * @var array
     */
    private array $data = [];

    /**
     * Constructor
     *
     * @param array $data Data to validate
     */
    public function __construct( array $data = [] ) {
        $this->data = $data;
    }

    /**
     * Set validation rules
     *
     * @param array $rules Validation rules
     * @return self
     */
    public function setRules( array $rules ): self {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Set data to validate
     *
     * @param array $data Data to validate
     * @return self
     */
    public function setData( array $data ): self {
        $this->data = $data;
        return $this;
    }

    /**
     * Validate data against rules
     *
     * @return bool
     */
    public function validate(): bool {
        $this->errors = [];

        foreach ( $this->rules as $field => $rule ) {
            $value = $this->data[ $field ] ?? null;

            if ( ! $this->validateField( $field, $value, $rule ) ) {
                // Error added in validateField
            }
        }

        return empty( $this->errors );
    }

    /**
     * Validate a single field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string|array $rule Validation rule(s)
     * @return bool
     */
    private function validateField( string $field, $value, $rule ): bool {
        if ( is_string( $rule ) ) {
            $rule = [ $rule ];
        }

        foreach ( $rule as $single_rule ) {
            $params = [];

            if ( strpos( $single_rule, ':' ) !== false ) {
                [ $single_rule, $param_string ] = explode( ':', $single_rule, 2 );
                $params = explode( ',', $param_string );
            }

            $method = 'validate' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $single_rule ) ) );

            if ( method_exists( $this, $method ) ) {
                if ( ! $this->$method( $field, $value, $params ) ) {
                    return false;
                }
            } else {
                Logger::warning( "Unknown validation rule: {$single_rule}" );
            }
        }

        return true;
    }

    /**
     * Validate required field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters
     * @return bool
     */
    private function validateRequired( string $field, $value, array $params ): bool {
        if ( is_null( $value ) || $value === '' ) {
            $this->addError( $field, sprintf( '%s is required', ucfirst( $field ) ) );
            return false;
        }
        return true;
    }

    /**
     * Validate email
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters
     * @return bool
     */
    private function validateEmail( string $field, $value, array $params ): bool {
        if ( ! is_email( $value ) ) {
            $this->addError( $field, sprintf( '%s must be a valid email', ucfirst( $field ) ) );
            return false;
        }
        return true;
    }

    /**
     * Validate URL
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters
     * @return bool
     */
    private function validateUrl( string $field, $value, array $params ): bool {
        if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
            $this->addError( $field, sprintf( '%s must be a valid URL', ucfirst( $field ) ) );
            return false;
        }
        return true;
    }

    /**
     * Validate minimum length
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters (min length)
     * @return bool
     */
    private function validateMinLength( string $field, $value, array $params ): bool {
        $min = (int) ( $params[0] ?? 0 );

        if ( strlen( (string) $value ) < $min ) {
            $this->addError( $field, sprintf( '%s must be at least %d characters', ucfirst( $field ), $min ) );
            return false;
        }
        return true;
    }

    /**
     * Validate maximum length
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters (max length)
     * @return bool
     */
    private function validateMaxLength( string $field, $value, array $params ): bool {
        $max = (int) ( $params[0] ?? 0 );

        if ( strlen( (string) $value ) > $max ) {
            $this->addError( $field, sprintf( '%s must not exceed %d characters', ucfirst( $field ), $max ) );
            return false;
        }
        return true;
    }

    /**
     * Validate numeric value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters
     * @return bool
     */
    private function validateNumeric( string $field, $value, array $params ): bool {
        if ( ! is_numeric( $value ) ) {
            $this->addError( $field, sprintf( '%s must be numeric', ucfirst( $field ) ) );
            return false;
        }
        return true;
    }

    /**
     * Validate integer
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters
     * @return bool
     */
    private function validateInteger( string $field, $value, array $params ): bool {
        if ( ! is_int( $value ) && ! ( is_numeric( $value ) && (int) $value == $value ) ) {
            $this->addError( $field, sprintf( '%s must be an integer', ucfirst( $field ) ) );
            return false;
        }
        return true;
    }

    /**
     * Validate minimum value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters (min value)
     * @return bool
     */
    private function validateMin( string $field, $value, array $params ): bool {
        $min = (float) ( $params[0] ?? 0 );

        if ( (float) $value < $min ) {
            $this->addError( $field, sprintf( '%s must be at least %s', ucfirst( $field ), $min ) );
            return false;
        }
        return true;
    }

    /**
     * Validate maximum value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters (max value)
     * @return bool
     */
    private function validateMax( string $field, $value, array $params ): bool {
        $max = (float) ( $params[0] ?? 0 );

        if ( (float) $value > $max ) {
            $this->addError( $field, sprintf( '%s must not exceed %s', ucfirst( $field ), $max ) );
            return false;
        }
        return true;
    }

    /**
     * Validate in array
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters (allowed values)
     * @return bool
     */
    private function validateIn( string $field, $value, array $params ): bool {
        if ( ! in_array( $value, $params, true ) ) {
            $this->addError( $field, sprintf( '%s must be one of: %s', ucfirst( $field ), implode( ', ', $params ) ) );
            return false;
        }
        return true;
    }

    /**
     * Validate regex pattern
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters (pattern)
     * @return bool
     */
    private function validateRegex( string $field, $value, array $params ): bool {
        $pattern = $params[0] ?? '';

        if ( ! preg_match( $pattern, (string) $value ) ) {
            $this->addError( $field, sprintf( '%s format is invalid', ucfirst( $field ) ) );
            return false;
        }
        return true;
    }

    /**
     * Validate boolean
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Additional parameters
     * @return bool
     */
    private function validateBoolean( string $field, $value, array $params ): bool {
        if ( ! is_bool( $value ) && ! in_array( $value, [ '0', '1', 0, 1, 'true', 'false', 'yes', 'no' ], true ) ) {
            $this->addError( $field, sprintf( '%s must be true or false', ucfirst( $field ) ) );
            return false;
        }
        return true;
    }

    /**
     * Add validation error
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return void
     */
    private function addError( string $field, string $message ): void {
        $this->errors[ $field ][] = $message;
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Get first error for a field
     *
     * @param string $field Field name
     * @return string|null
     */
    public function getFirstError( string $field ): ?string {
        return $this->errors[ $field ][0] ?? null;
    }

    /**
     * Check if field has errors
     *
     * @param string $field Field name
     * @return bool
     */
    public function hasError( string $field ): bool {
        return isset( $this->errors[ $field ] );
    }

    /**
     * Get all errors as a single string
     *
     * @return string
     */
    public function getErrorString(): string {
        $messages = [];

        foreach ( $this->errors as $field => $errors ) {
            $messages = array_merge( $messages, $errors );
        }

        return implode( "\n", $messages );
    }

    /**
     * Validate product data
     *
     * @param array $data Product data
     * @return array Validation result with 'valid' and 'errors' keys
     */
    public static function validateProduct( array $data ): array {
        $validator = new self( $data );

        $validator->setRules( [
            'title'         => [ 'required', 'min_length:3', 'max_length:200' ],
            'description'   => [ 'max_length:5000' ],
            'price'         => [ 'required', 'numeric', 'min:0' ],
            'affiliate_url' => [ 'required', 'url' ],
            'image_url'     => [ 'url' ],
            'sku'           => [ 'max_length:100' ],
            'brand'         => [ 'max_length:100' ],
            'rating'        => [ 'numeric', 'min:0', 'max:5' ],
            'in_stock'      => [ 'boolean' ],
        ] );

        return [
            'valid'  => $validator->validate(),
            'errors' => $validator->getErrors(),
        ];
    }

    /**
     * Validate settings data
     *
     * @param array $data Settings data
     * @return array Validation result with 'valid' and 'errors' keys
     */
    public static function validateSettings( array $data ): array {
        $validator = new self( $data );

        $validator->setRules( [
            'display_mode'     => [ 'in:grid,list,carousel' ],
            'items_per_page'   => [ 'integer', 'min:1', 'max:100' ],
            'cache_duration'   => [ 'integer', 'min:0' ],
            'enable_analytics' => [ 'boolean' ],
            'tracking_enabled' => [ 'boolean' ],
        ] );

        return [
            'valid'  => $validator->validate(),
            'errors' => $validator->getErrors(),
        ];
    }
}
