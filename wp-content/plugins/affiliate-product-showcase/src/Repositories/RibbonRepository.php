<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Models\Ribbon;
use AffiliateProductShowcase\Factories\RibbonFactory;
use AffiliateProductShowcase\Plugin\Constants;
use WP_Error;

/**
 * Ribbon repository
 *
 * Handles CRUD operations for ribbons.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 */
final class RibbonRepository {
    /**
     * Create a new ribbon
     *
     * @param Ribbon $ribbon Ribbon to create
     * @return Ribbon Created ribbon
     * @throws WP_Error If creation fails
     */
    public function create( Ribbon $ribbon ): Ribbon {
        $result = wp_insert_term(
            $ribbon->name,
            Constants::TAX_RIBBON,
            [
                'slug' => $ribbon->slug,
                'description' => $ribbon->description,
            ]
        );

        if ( is_wp_error( $result ) ) {
            throw $result;
        }

        $term_id = $result['term_id'];
        $this->save_metadata( $term_id, $ribbon );

        return $this->find( $term_id );
    }

    /**
     * Find a ribbon by ID
     *
     * @param int $id Ribbon ID
     * @return Ribbon|null Ribbon instance or null if not found
     */
    public function find( int $id ): ?Ribbon {
        $term = get_term( $id, Constants::TAX_RIBBON );

        if ( ! $term || is_wp_error( $term ) ) {
            return null;
        }

        return RibbonFactory::from_wp_term( $term );
    }

    /**
     * Update an existing ribbon
     *
     * @param int $id Ribbon ID
     * @param Ribbon $ribbon Ribbon data to update
     * @return Ribbon Updated ribbon
     * @throws WP_Error If update fails
     */
    public function update( int $id, Ribbon $ribbon ): Ribbon {
        $result = wp_update_term(
            $id,
            Constants::TAX_RIBBON,
            [
                'name' => $ribbon->name,
                'slug' => $ribbon->slug,
                'description' => $ribbon->description,
            ]
        );

        if ( is_wp_error( $result ) ) {
            throw $result;
        }

        $this->save_metadata( $id, $ribbon );

        return $this->find( $id );
    }

    /**
     * Delete a ribbon
     *
     * @param int $id Ribbon ID
     * @return bool True if deleted
     * @throws WP_Error If deletion fails
     */
    public function delete( int $id ): bool {
        $result = wp_delete_term( $id, Constants::TAX_RIBBON );

        if ( is_wp_error( $result ) ) {
            throw $result;
        }

        $this->delete_metadata( $id );

        return $result;
    }

    /**
     * Get all ribbons
     *
     * @param array $args Query arguments
     * @return array<Ribbon> Array of ribbons
     */
    public function all( array $args = [] ): array {
        $defaults = [
            'taxonomy' => Constants::TAX_RIBBON,
            'hide_empty' => false,
            'orderby' => 'meta_value_num',
            'meta_key' => '_aps_ribbon_priority',
            'order' => 'ASC',
        ];

        $args = wp_parse_args( $args, $defaults );
        $terms = get_terms( $args );

        if ( is_wp_error( $terms ) ) {
            return [];
        }

        return RibbonFactory::from_array_many(
            array_map( fn( $term ) => (array) $term, $terms )
        );
    }

    /**
     * Search ribbons by name
     *
     * @param string $search Search term
     * @return array<Ribbon> Matching ribbons
     */
    public function search( string $search ): array {
        return $this->all( [
            'search' => $search,
        ] );
    }

    /**
     * Get default ribbon
     *
     * @return Ribbon|null Default ribbon or null
     */
    public function get_default(): ?Ribbon {
        $terms = get_terms( [
            'taxonomy' => Constants::TAX_RIBBON,
            'hide_empty' => false,
            'meta_key' => '_aps_ribbon_is_default',
            'meta_value' => '1',
            'number' => 1,
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return null;
        }

        return RibbonFactory::from_wp_term( $terms[0] );
    }

    /**
     * Save ribbon metadata
     *
     * @param int $term_id Term ID
     * @param Ribbon $ribbon Ribbon instance
     */
    private function save_metadata( int $term_id, Ribbon $ribbon ): void {
        // Color (with underscore prefix)
        if ( $ribbon->color ) {
            update_term_meta( $term_id, '_aps_ribbon_color', $ribbon->color );
        } else {
            delete_term_meta( $term_id, '_aps_ribbon_color' );
        }

        // Icon (with underscore prefix)
        if ( $ribbon->icon ) {
            update_term_meta( $term_id, '_aps_ribbon_icon', $ribbon->icon );
        } else {
            delete_term_meta( $term_id, '_aps_ribbon_icon' );
        }

        // Priority (with underscore prefix)
        update_term_meta( $term_id, '_aps_ribbon_priority', $ribbon->priority );

        // Tag Pattern: Status (with underscore prefix)
        update_term_meta( $term_id, '_aps_ribbon_status', $ribbon->status );

        // Tag Pattern: Featured (with underscore prefix)
        update_term_meta( $term_id, '_aps_ribbon_featured', $ribbon->featured ? '1' : '0' );

        // Tag Pattern: Default (with underscore prefix, exclusive)
        if ( $ribbon->is_default ) {
            // Remove default flag from all other ribbons (exclusive behavior)
            $all_ribbons = get_terms( [
                'taxonomy' => Constants::TAX_RIBBON,
                'hide_empty' => false,
                'fields' => 'ids',
            ] );

            if ( ! is_wp_error( $all_ribbons ) && ! empty( $all_ribbons ) ) {
                foreach ( $all_ribbons as $other_ribbon_id ) {
                    if ( intval( $other_ribbon_id ) !== $term_id ) {
                        update_term_meta( $other_ribbon_id, '_aps_ribbon_is_default', '0' );
                    }
                }
            }
            update_term_meta( $term_id, '_aps_ribbon_is_default', '1' );
        } else {
            update_term_meta( $term_id, '_aps_ribbon_is_default', '0' );
        }

        // Tag Pattern: Image URL (with underscore prefix)
        if ( $ribbon->image_url ) {
            update_term_meta( $term_id, '_aps_ribbon_image_url', $ribbon->image_url );
        } else {
            delete_term_meta( $term_id, '_aps_ribbon_image_url' );
        }

        // Timestamps
        $existing_created = get_term_meta( $term_id, '_aps_ribbon_created_at', true );
        if ( ! $existing_created ) {
            update_term_meta( $term_id, '_aps_ribbon_created_at', current_time( 'mysql' ) );
        }
        update_term_meta( $term_id, '_aps_ribbon_updated_at', current_time( 'mysql' ) );
    }

    /**
     * Delete ribbon metadata
     *
     * @param int $term_id Term ID
     */
    private function delete_metadata( int $term_id ): void {
        delete_term_meta( $term_id, '_aps_ribbon_color' );
        delete_term_meta( $term_id, '_aps_ribbon_icon' );
        delete_term_meta( $term_id, '_aps_ribbon_priority' );
        delete_term_meta( $term_id, '_aps_ribbon_status' );
        delete_term_meta( $term_id, '_aps_ribbon_featured' );
        delete_term_meta( $term_id, '_aps_ribbon_is_default' );
        delete_term_meta( $term_id, '_aps_ribbon_image_url' );
        delete_term_meta( $term_id, '_aps_ribbon_created_at' );
        delete_term_meta( $term_id, '_aps_ribbon_updated_at' );
    }
}