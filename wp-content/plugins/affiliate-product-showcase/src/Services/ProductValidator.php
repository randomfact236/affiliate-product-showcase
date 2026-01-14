<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Helpers\Logger;
use AffiliateProductShowcase\Security\Validator;

/**
 * Product Validator Service
 *
 * Provides specialized validation for product data.
 * Extends the base Validator with product-specific rules.
 *
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 */
class ProductValidator {

    /**
     * Validate product data before save
     *
     * @param array $data Product data to validate
     * @return array Validation result with 'valid' and 'errors' keys
     */
    public function validate( array $data ): array {
        $result = [
            'valid'  => true,
            'errors' => [],
            'warnings' => [],
        ];

        // Required fields
        $result = $this->validateRequiredFields( $data, $result );

        // Field-specific validation
        $result = $this->validateTitle( $data, $result );
        $result = $this->validatePrice( $data, $result );
        $result = $this->validateUrls( $data, $result );
        $result = $this->validateRating( $data, $result );
        $result = $this->validateStock( $data, $result );

        // Use base validator for additional checks
        $base_validation = Validator::validateProduct( $data );
        
        if ( ! $base_validation['valid'] ) {
            $result['valid'] = false;
            $result['errors'] = array_merge( $result['errors'], $base_validation['errors'] );
        }

        return $result;
    }

    /**
     * Validate required fields
     *
     * @param array $data Product data
     * @param array $result Current validation result
     * @return array Updated validation result
     */
    private function validateRequiredFields( array $data, array $result ): array {
        $required = [ 'title', 'price', 'affiliate_url' ];

        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                $result['valid'] = false;
                $result['errors'][ $field ] = sprintf(
                    '%s is required',
                    ucfirst( $field )
                );
            }
        }

        return $result;
    }

    /**
     * Validate product title
     *
     * @param array $data Product data
     * @param array $result Current validation result
     * @return array Updated validation result
     */
    private function validateTitle( array $data, array $result ): array {
        if ( ! isset( $data['title'] ) ) {
            return $result;
        }

        $title = $data['title'];

        // Check length
        if ( strlen( $title ) < 3 ) {
            $result['valid'] = false;
            $result['errors']['title'] = 'Title must be at least 3 characters';
        } elseif ( strlen( $title ) > 200 ) {
            $result['valid'] = false;
            $result['errors']['title'] = 'Title must not exceed 200 characters';
        }

        // Check for suspicious patterns
        if ( $this->containsSpamKeywords( $title ) ) {
            $result['warnings']['title'] = 'Title contains suspicious keywords';
        }

        return $result;
    }

    /**
     * Validate product price
     *
     * @param array $data Product data
     * @param array $result Current validation result
     * @return array Updated validation result
     */
    private function validatePrice( array $data, array $result ): array {
        if ( ! isset( $data['price'] ) ) {
            return $result;
        }

        $price = floatval( $data['price'] );

        if ( $price < 0 ) {
            $result['valid'] = false;
            $result['errors']['price'] = 'Price cannot be negative';
        }

        if ( $price > 999999.99 ) {
            $result['warnings']['price'] = 'Price seems unusually high';
        }

        return $result;
    }

    /**
     * Validate URLs (affiliate URL and image URL)
     *
     * @param array $data Product data
     * @param array $result Current validation result
     * @return array Updated validation result
     */
    private function validateUrls( array $data, array $result ): array {
        // Validate affiliate URL
        if ( isset( $data['affiliate_url'] ) && ! empty( $data['affiliate_url'] ) ) {
            if ( ! filter_var( $data['affiliate_url'], FILTER_VALIDATE_URL ) ) {
                $result['valid'] = false;
                $result['errors']['affiliate_url'] = 'Invalid affiliate URL';
            }

            // Check if URL is accessible
            if ( ! $this->isUrlAccessible( $data['affiliate_url'] ) ) {
                $result['warnings']['affiliate_url'] = 'Affiliate URL may not be accessible';
            }
        }

        // Validate image URL
        if ( isset( $data['image_url'] ) && ! empty( $data['image_url'] ) ) {
            if ( ! filter_var( $data['image_url'], FILTER_VALIDATE_URL ) ) {
                $result['valid'] = false;
                $result['errors']['image_url'] = 'Invalid image URL';
            }

            // Check if image exists
            if ( ! $this->isImageValid( $data['image_url'] ) ) {
                $result['warnings']['image_url'] = 'Image URL may not point to a valid image';
            }
        }

        return $result;
    }

    /**
     * Validate product rating
     *
     * @param array $data Product data
     * @param array $result Current validation result
     * @return array Updated validation result
     */
    private function validateRating( array $data, array $result ): array {
        if ( ! isset( $data['rating'] ) ) {
            return $result;
        }

        $rating = floatval( $data['rating'] );

        if ( $rating < 0 || $rating > 5 ) {
            $result['valid'] = false;
            $result['errors']['rating'] = 'Rating must be between 0 and 5';
        }

        return $result;
    }

    /**
     * Validate stock status
     *
     * @param array $data Product data
     * @param array $result Current validation result
     * @return array Updated validation result
     */
    private function validateStock( array $data, array $result ): array {
        if ( isset( $data['stock_quantity'] ) ) {
            $quantity = intval( $data['stock_quantity'] );

            if ( $quantity < 0 ) {
                $result['valid'] = false;
                $result['errors']['stock_quantity'] = 'Stock quantity cannot be negative';
            }
        }

        return $result;
    }

    /**
     * Check if string contains spam keywords
     *
     * @param string $text Text to check
     * @return bool
     */
    private function containsSpamKeywords( string $text ): bool {
        $spam_keywords = [
            'free', 'winner', 'congratulations', 'click here',
            'subscribe', 'buy now', 'limited time', 'act now',
        ];

        $text_lower = strtolower( $text );

        foreach ( $spam_keywords as $keyword ) {
            if ( strpos( $text_lower, $keyword ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if URL is accessible
     *
     * @param string $url URL to check
     * @return bool
     */
    private function isUrlAccessible( string $url ): bool {
        // Simple check - in production, you might want to make an actual HTTP request
        $parsed = parse_url( $url );
        
        return isset( $parsed['scheme'] ) 
            && isset( $parsed['host'] ) 
            && in_array( $parsed['scheme'], [ 'http', 'https' ], true );
    }

    /**
     * Check if image URL points to a valid image
     *
     * @param string $url Image URL
     * @return bool
     */
    private function isImageValid( string $url ): bool {
        $path = parse_url( $url, PHP_URL_PATH );
        
        if ( ! $path ) {
            return false;
        }

        $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
        
        $valid_extensions = [ 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' ];
        
        return in_array( $extension, $valid_extensions, true );
    }

    /**
     * Validate product before creation
     *
     * @param array $data Product data
     * @return array Validation result
     */
    public function validateForCreation( array $data ): array {
        $result = $this->validate( $data );

        // Additional checks for creation
        if ( isset( $data['sku'] ) && ! empty( $data['sku'] ) ) {
            if ( $this->skuExists( $data['sku'] ) ) {
                $result['valid'] = false;
                $result['errors']['sku'] = 'SKU already exists';
            }
        }

        return $result;
    }

    /**
     * Validate product before update
     *
     * @param int $product_id Product ID being updated
     * @param array $data Product data
     * @return array Validation result
     */
    public function validateForUpdate( int $product_id, array $data ): array {
        $result = $this->validate( $data );

        // Additional checks for update
        if ( isset( $data['sku'] ) && ! empty( $data['sku'] ) ) {
            if ( $this->skuExists( $data['sku'], $product_id ) ) {
                $result['valid'] = false;
                $result['errors']['sku'] = 'SKU already exists';
            }
        }

        return $result;
    }

    /**
     * Check if SKU exists
     *
     * @param string $sku SKU to check
     * @param int|null $exclude_id Product ID to exclude
     * @return bool
     */
    private function skuExists( string $sku, ?int $exclude_id = null ): bool {
        $args = [
            'post_type'      => 'affiliate_product',
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'     => '_sku',
                    'value'   => $sku,
                    'compare' => '=',
                ],
            ],
        ];

        if ( $exclude_id ) {
            $args['post__not_in'] = [ $exclude_id ];
        }

        $query = new \WP_Query( $args );
        
        return $query->have_posts();
    }

    /**
     * Get validation error messages as HTML
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
}
