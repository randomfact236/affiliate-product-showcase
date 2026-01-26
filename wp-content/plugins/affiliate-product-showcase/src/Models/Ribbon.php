<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Models;

use WP_Term;

/**
 * Ribbon model
 *
 * Represents a product ribbon with all metadata.
 * Ribbons are non-hierarchical (flat structure, like tags).
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 */
final class Ribbon {
    /**
     * Ribbon ID (term_id)
     */
    public readonly int $id;

    /**
     * Ribbon name
     */
    public readonly string $name;

    /**
     * Ribbon slug (URL-friendly identifier)
     */
    public readonly string $slug;

    /**
     * Number of products with this ribbon
     */
    public readonly int $count;

    /**
     * Display priority (lower = higher priority)
     */
    public readonly int $priority;

    /**
     * Display color (hex code)
     */
    public readonly ?string $color;

    /**
     * Icon identifier/class
     */
    public readonly ?string $icon;

    /**
     * Status (published/draft/trashed)
     */
    public readonly string $status;

    /**
     * Creation timestamp
     */
    public readonly string $created_at;

    /**
     * Last update timestamp
     */
    public readonly string $updated_at;

    /**
     * Constructor
     *
     * @param int $id Ribbon ID
     * @param string $name Ribbon name
     * @param string $slug Ribbon slug
     * @param int $count Product count
     * @param int $priority Display priority (lower = higher priority)
     * @param string|null $color Display color
     * @param string|null $icon Icon identifier
     * @param string $status Status (published/draft/trashed)
     * @param string|null $created_at Creation timestamp
     * @param string|null $updated_at Update timestamp
     */
    public function __construct(
        int $id,
        string $name,
        string $slug,
        int $count,
        int $priority = 10,
        ?string $color = null,
        ?string $icon = null,
        string $status = 'published',
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->count = $count;
        $this->priority = $priority;
        $this->color = $color;
        $this->icon = $icon;
        $this->status = $status;
        $this->created_at = $created_at ?? current_time( 'mysql' );
        $this->updated_at = $updated_at ?? current_time( 'mysql' );
    }

    /**
     * Get ribbon meta
     *
     * Retrieves ribbon metadata with underscore prefix.
     *
     * @param int $term_id Term ID
     * @param string $meta_key Meta key (without _aps_ribbon_ prefix)
     * @return mixed Meta value
     */
    private static function get_ribbon_meta( int $term_id, string $meta_key ) {
        return get_term_meta( $term_id, '_aps_ribbon_' . $meta_key, true );
    }

    /**
     * Create Ribbon from WP_Term
     *
     * @param WP_Term $term WordPress term object
     * @return self Ribbon instance
     */
    public static function from_wp_term( WP_Term $term ): self {
        // Get ribbon metadata (with underscore prefix)
        $priority = (int) self::get_ribbon_meta( $term->term_id, 'priority' ) ?: 10;
        $color = self::get_ribbon_meta( $term->term_id, 'color' );
        $icon = self::get_ribbon_meta( $term->term_id, 'icon' );
        $status = self::get_ribbon_meta( $term->term_id, 'status' ) ?: 'published';
        $created_at = self::get_ribbon_meta( $term->term_id, 'created_at' );
        $updated_at = self::get_ribbon_meta( $term->term_id, 'updated_at' );

        return new self(
            id: $term->term_id,
            name: $term->name,
            slug: $term->slug,
            count: $term->count,
            priority: $priority,
            color: $color ?: null,
            icon: $icon ?: null,
            status: $status,
            created_at: $created_at ?: current_time( 'mysql' ),
            updated_at: $updated_at ?: current_time( 'mysql' )
        );
    }

    /**
     * Convert Ribbon to array
     *
     * @return array Ribbon data as array
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'count' => $this->count,
            'priority' => $this->priority,
            'color' => $this->color,
            'icon' => $this->icon,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}