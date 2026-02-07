<?php
/**
 * Product Helpers
 *
 * Utility functions for product-related operations.
 *
 * @package AffiliateProductShowcase\Admin\Helpers
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Helpers;

use AffiliateProductShowcase\Admin\Config\ProductConfig;

/**
 * ProductHelpers class
 *
 * Provides utility methods for product data retrieval and formatting.
 *
 * @since 1.0.0
 */
class ProductHelpers {
    /**
     * Get product ribbon
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return string Ribbon name
     */
    public static function getRibbon(int $product_id): string {
        $terms = \wp_get_object_terms(
            $product_id,
            ProductConfig::getTaxonomy('ribbon'),
            ['fields' => 'names']
        );
        return is_array($terms) && !empty($terms) ? $terms[0] : '';
    }

    /**
     * Get product categories
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return array Category names
     */
    public static function getCategories(int $product_id): array {
        $terms = \wp_get_object_terms(
            $product_id,
            ProductConfig::getTaxonomy('category'),
            ['fields' => 'names']
        );
        return is_array($terms) ? $terms : [];
    }

    /**
     * Get product tags
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return array Tag names
     */
    public static function getTags(int $product_id): array {
        $terms = \wp_get_object_terms(
            $product_id,
            ProductConfig::getTaxonomy('tag'),
            ['fields' => 'names']
        );
        return is_array($terms) ? $terms : [];
    }

    /**
     * Get product meta value
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @param string $meta_key Meta key type
     * @param mixed $default Default value if not found
     * @return mixed Meta value
     */
    public static function getMeta(int $product_id, string $meta_key, $default = '') {
        return \get_post_meta(
            $product_id,
            ProductConfig::getMetaKey($meta_key),
            true
        ) ?: $default;
    }

    /**
     * Get product price
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return float Product price
     */
    public static function getPrice(int $product_id): float {
        return (float) self::getMeta($product_id, 'price', 0);
    }

    /**
     * Get product original price
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return float Original price
     */
    public static function getOriginalPrice(int $product_id): float {
        return (float) self::getMeta($product_id, 'original_price', 0);
    }

    /**
     * Get product currency
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return string Currency code
     */
    public static function getCurrency(int $product_id): string {
        return self::getMeta($product_id, 'currency', ProductConfig::DEFAULT_CURRENCY);
    }

    /**
     * Check if product is featured
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return bool True if featured
     */
    public static function isFeatured(int $product_id): bool {
        return (bool) self::getMeta($product_id, 'featured', false);
    }

    /**
     * Get product affiliate URL
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return string Affiliate URL
     */
    public static function getAffiliateUrl(int $product_id): string {
        return self::getMeta($product_id, 'affiliate_url', '');
    }

    /**
     * Get product logo URL
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return string Logo URL
     */
    public static function getLogoUrl(int $product_id): string {
        return \get_the_post_thumbnail_url($product_id, 'thumbnail') ?: '';
    }

    /**
     * Format price with currency symbol
     *
     * @since 1.0.0
     * @param float $price Price value
     * @param string $currency Currency code
     * @return string Formatted price
     */
    public static function formatPrice(float $price, string $currency = ''): string {
        $currency = $currency ?: ProductConfig::DEFAULT_CURRENCY;
        $symbol = ProductConfig::getCurrencySymbol($currency);
        return $symbol . number_format($price, 2);
    }

    /**
     * Calculate discount percentage
     *
     * @since 1.0.0
     * @param float $original_price Original price
     * @param float $current_price Current price
     * @return int Discount percentage
     */
    public static function calculateDiscount(float $original_price, float $current_price): int {
        if ($original_price <= 0 || $current_price >= $original_price) {
            return 0;
        }
        return (int) round(($original_price - $current_price) / $original_price * 100);
    }

    /**
     * Get ribbon colors
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return array Background and text colors
     */
    public static function getRibbonColors(int $product_id): array {
        $terms = \wp_get_object_terms(
            $product_id,
            ProductConfig::getTaxonomy('ribbon')
        );

        if (is_wp_error($terms) || empty($terms)) {
            return ['bg' => '', 'text' => ''];
        }

        $ribbon_term = $terms[0];
        return [
            'bg'   => \get_term_meta($ribbon_term->term_id, ProductConfig::getMetaKey('ribbon_bg'), true),
            'text' => \get_term_meta($ribbon_term->term_id, ProductConfig::getMetaKey('ribbon_color'), true),
        ];
    }

    /**
     * Build product edit URL
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return string Edit URL
     */
    public static function getEditUrl(int $product_id): string {
        return \admin_url(sprintf('admin.php?page=aps-edit-product&id=%d', $product_id));
    }

    /**
     * Build product view URL
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return string View URL
     */
    public static function getViewUrl(int $product_id): string {
        return \get_permalink($product_id);
    }
}
