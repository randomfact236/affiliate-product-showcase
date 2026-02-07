<?php
/**
 * Product Configuration
 *
 * Centralized configuration for product-related settings.
 *
 * @package AffiliateProductShowcase\Admin\Config
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Config;

/**
 * ProductConfig class
 *
 * Provides centralized configuration for product post type,
 * taxonomies, meta keys, and other product-related settings.
 *
 * @since 1.0.0
 */
class ProductConfig {
    /**
     * Post type for products
     *
     * @since 1.0.0
     * @var string
     */
    public const POST_TYPE = 'aps_product';

    /**
     * Taxonomy slugs
     *
     * @since 1.0.0
     * @var array
     */
    public const TAXONOMIES = [
        'category' => 'aps_category',
        'tag'      => 'aps_tag',
        'ribbon'   => 'aps_ribbon',
    ];

    /**
     * Meta key prefixes
     *
     * @since 1.0.0
     * @var array
     */
    public const META_KEYS = [
        'price'          => '_aps_price',
        'currency'       => '_aps_currency',
        'affiliate_url'  => '_aps_affiliate_url',
        'featured'       => '_aps_featured',
        'original_price' => '_aps_original_price',
        'ribbon_bg'      => '_aps_ribbon_bg_color',
        'ribbon_color'   => '_aps_ribbon_color',
    ];

    /**
     * Currency symbols mapping
     *
     * @since 1.0.0
     * @var array
     */
    public const CURRENCY_SYMBOLS = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'AUD' => 'A$',
        'CAD' => 'C$',
    ];

    /**
     * Default currency code
     *
     * @since 1.0.0
     * @var string
     */
    public const DEFAULT_CURRENCY = 'USD';

    /**
     * Status labels mapping
     *
     * @since 1.0.0
     * @var array
     */
    public const STATUS_LABELS = [
        'published' => 'Published',
        'draft'     => 'Draft',
        'trash'     => 'Trash',
        'pending'   => 'Pending',
    ];

    /**
     * Default items per page
     *
     * @since 1.0.0
     * @var int
     */
    public const DEFAULT_PER_PAGE = 20;

    /**
     * Product logo dimensions
     *
     * @since 1.0.0
     * @var array
     */
    public const LOGO_DIMENSIONS = [
        'width'  => 48,
        'height' => 48,
    ];

    /**
     * Get taxonomy slug by type
     *
     * @since 1.0.0
     * @param string $type Taxonomy type (category, tag, ribbon)
     * @return string Taxonomy slug
     */
    public static function getTaxonomy(string $type): string {
        return self::TAXONOMIES[$type] ?? '';
    }

    /**
     * Get meta key by type
     *
     * @since 1.0.0
     * @param string $type Meta key type
     * @return string Meta key
     */
    public static function getMetaKey(string $type): string {
        return self::META_KEYS[$type] ?? '';
    }

    /**
     * Get currency symbol for currency code
     *
     * @since 1.0.0
     * @param string $currency Currency code
     * @return string Currency symbol
     */
    public static function getCurrencySymbol(string $currency): string {
        return self::CURRENCY_SYMBOLS[$currency] ?? self::CURRENCY_SYMBOLS['USD'];
    }

    /**
     * Get status label for status code
     *
     * @since 1.0.0
     * @param string $status Status code
     * @return string Status label
     */
    public static function getStatusLabel(string $status): string {
        return self::STATUS_LABELS[$status] ?? ucfirst($status);
    }

    /**
     * Get all available currency codes
     *
     * @since 1.0.0
     * @return array Currency codes
     */
    public static function getAvailableCurrencies(): array {
        return array_keys(self::CURRENCY_SYMBOLS);
    }

    /**
     * Get all available status codes
     *
     * @since 1.0.0
     * @return array Status codes
     */
    public static function getAvailableStatuses(): array {
        return array_keys(self::STATUS_LABELS);
    }
}
