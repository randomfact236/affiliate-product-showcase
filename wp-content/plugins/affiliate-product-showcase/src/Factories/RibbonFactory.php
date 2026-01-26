<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Factories;

use AffiliateProductShowcase\Models\Ribbon;
use WP_Term;

/**
 * Ribbon factory
 *
 * Creates Ribbon instances from various data sources.
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 */
final class RibbonFactory {
    /**
     * Create Ribbon from WP_Term
     *
     * @param WP_Term $term WordPress term object
     * @return Ribbon Ribbon instance
     */
    public static function from_wp_term( WP_Term $term ): Ribbon {
        return Ribbon::from_wp_term( $term );
    }

    /**
     * Create Ribbon from array
     *
     * @param array $data Ribbon data
     * @return Ribbon Ribbon instance
     */
    public static function from_array( array $data ): Ribbon {
        return new Ribbon(
            id: isset( $data['id'] ) ? (int) $data['id'] : 0,
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            count: $data['count'] ?? 0,
            priority: (int) ( $data['priority'] ?? 10 ),
            color: $data['color'] ?? null,
            icon: $data['icon'] ?? null,
            status: $data['status'] ?? 'published',
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }

    /**
     * Create multiple Ribbons from array
     *
     * @param array<array> $data_array Array of ribbon data
     * @return array<Ribbon> Array of Ribbon instances
     */
    public static function from_array_many( array $data_array ): array {
        return array_map(
            fn( $data ) => self::from_array( $data ),
            $data_array
        );
    }
}