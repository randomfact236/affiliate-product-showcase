<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Migrations;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Ribbon data migration
 *
 * Migrates ribbon data from post meta to taxonomy relationships.
 * This ensures true hybrid compliance.
 *
 * @package AffiliateProductShowcase\Migrations
 * @since 1.0.0
 */
final class RibbonMigration {
    /**
     * Migration option name
     */
    private const MIGRATION_OPTION = 'aps_ribbon_migration_complete';

    /**
     * Check if migration is needed
     *
     * @return bool True if migration needed
     */
    public static function is_needed(): bool {
        return get_option( self::MIGRATION_OPTION ) !== '1.0';
    }

    /**
     * Run migration
     *
     * Migrates all ribbon data from post meta to taxonomy relationships.
     */
    public static function run(): void {
        if ( ! self::is_needed() ) {
            return;
        }

        // Get all products with _aps_ribbon post meta
        $products = get_posts( [
            'post_type'      => Constants::POST_TYPE,
            'numberposts'    => -1,
            'meta_key'       => '_aps_ribbon',
            'meta_compare'   => 'EXISTS',
        ] );

        $migrated_count = 0;
        $error_count = 0;

        foreach ( $products as $product ) {
            $ribbon_term_id = (int) get_post_meta( $product->ID, '_aps_ribbon', true );

            if ( $ribbon_term_id > 0 ) {
                // Check if ribbon term exists
                $term = get_term( $ribbon_term_id, Constants::TAX_RIBBON );

                if ( $term && ! is_wp_error( $term ) ) {
                    // Establish taxonomy relationship
                    $result = wp_set_object_terms( $product->ID, [ $ribbon_term_id ], Constants::TAX_RIBBON );

                    if ( ! is_wp_error( $result ) ) {
                        // Remove old post meta
                        delete_post_meta( $product->ID, '_aps_ribbon' );
                        $migrated_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    // Ribbon term doesn't exist, remove invalid post meta
                    delete_post_meta( $product->ID, '_aps_ribbon' );
                    $error_count++;
                }
            }
        }

        // Mark migration as complete
        update_option( self::MIGRATION_OPTION, '1.0' );

        // Log migration results
        error_log( sprintf(
            'Ribbon migration complete: %d migrated, %d errors',
            $migrated_count,
            $error_count
        ) );

        // Display admin notice
        add_action( 'admin_notices', function() use ( $migrated_count, $error_count ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: %1$d: migrated count, %2$d: error count */
                        esc_html__( 'Ribbon migration complete: %1$d products migrated, %2$d errors.', 'affiliate-product-showcase' ),
                        esc_html( $migrated_count ),
                        esc_html( $error_count )
                    );
                    ?>
                </p>
            </div>
            <?php
        } );
    }

    /**
     * Register migration hook
     *
     * Run migration on admin_init if needed.
     *
     * @hook admin_init
     */
    public static function register(): void {
        add_action( 'admin_init', [ self::class, 'run' ] );
    }
}

// Register migration
RibbonMigration::register();