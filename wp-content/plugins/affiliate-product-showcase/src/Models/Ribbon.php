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
     * Ribbon description
     */
    public readonly string $description;

    /**
     * Number of products with this ribbon
     */
    public readonly int $count;

    /**
     * Display color (hex code)
     */
    public readonly ?string $color;

    /**
     * Icon identifier/class
     */
    public readonly ?string $icon;

    /**
     * Display priority (higher/lower affects ordering)
     */
    public readonly int $priority;

    /**
     * Ribbon visibility status (published/draft/trashed)
     */
    public readonly string $status;

    /**
     * Featured flag
     */
    public readonly bool $featured;

    /**
     * Default flag (exclusive - only one default ribbon)
     */
    public readonly bool $is_default;

    /**
     * Ribbon image URL
     */
    public readonly ?string $image_url;

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
     * @param string $description Ribbon description
     * @param int $count Product count
     * @param string|null $color Display color
     * @param string|null $icon Icon identifier
     * @param int $priority Display priority
     * @param string $status Visibility status
     * @param bool $featured Featured flag
     * @param bool $is_default Default flag
     * @param string|null $image_url Image URL
     * @param string|null $created_at Creation timestamp
     * @param string|null $updated_at Update timestamp
     */
    public function __construct(
        int $id,
        string $name,
        string $slug,
        string $description,
        int $count,
        ?string $color = null,
        ?string $icon = null,
        int $priority = 10,
        string $status = 'published',
        bool $featured = false,
        bool $is_default = false,
        ?string $image_url = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->count = $count;
        $this->color = $color;
        $this->icon = $icon;
        $this->priority = $priority;
        $this->status = $status;
        $this->featured = $featured;
        $this->is_default = $is_default;
        $this->image_url = $image_url;
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
        $color = self::get_ribbon_meta( $term->term_id, 'color' );
        $icon = self::get_ribbon_meta( $term->term_id, 'icon' );
        $priority = (int) self::get_ribbon_meta( $term->term_id, 'priority' );
        
        // Tag Pattern: Get status from term meta
        $status = self::get_ribbon_meta( $term->term_id, 'status' ) ?: 'published';
        
        // Tag Pattern: Get featured flag from term meta
        $featured = (bool) self::get_ribbon_meta( $term->term_id, 'featured' );
        
        // Tag Pattern: Get default flag from term meta
        $is_default = (bool) self::get_ribbon_meta( $term->term_id, 'is_default' );
        
        // Tag Pattern: Get image URL from term meta
        $image_url = self::get_ribbon_meta( $term->term_id, 'image_url' );
        
        $created_at = self::get_ribbon_meta( $term->term_id, 'created_at' );
        $updated_at = self::get_ribbon_meta( $term->term_id, 'updated_at' );

        return new self(
            id: $term->term_id,
            name: $term->name,
            slug: $term->slug,
            description: $term->description ?: '',
            count: $term->count,
            color: $color ?: null,
            icon: $icon ?: null,
            priority: $priority ?: 10,
            status: $status,
            featured: $featured,
            is_default: $is_default,
            image_url: $image_url ?: null,
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
            'description' => $this->description,
            'count' => $this->count,
            'color' => $this->color,
            'icon' => $this->icon,
            'priority' => $this->priority,
            'status' => $this->status,
            'featured' => $this->featured,
            'is_default' => $this->is_default,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}