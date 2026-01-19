<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Helpers\Logger;

/**
 * Admin Columns
 *
 * Manages custom columns for the products list table.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class Columns {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter( 'manage_aps_product_posts_columns', [ $this, 'addCustomColumns' ] );
        add_action( 'manage_aps_product_posts_custom_column', [ $this, 'renderCustomColumns' ], 10, 2 );
        add_filter( 'manage_edit-aps_product_sortable_columns', [ $this, 'makeColumnsSortable' ] );
        add_action( 'restrict_manage_posts', [ $this, 'addFilters' ], 10, 2 );
    }

    /**
     * Add custom columns to the products list
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function addCustomColumns( array $columns ): array {
        // Add columns after title
        $new_columns = [];

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;

            // Add custom columns after title
            if ( $key === 'title' ) {
                $new_columns['price'] = __( 'Price', 'affiliate-product-showcase' );
                $new_columns['sku'] = __( 'SKU', 'affiliate-product-showcase' );
                $new_columns['brand'] = __( 'Brand', 'affiliate-product-showcase' );
                $new_columns['rating'] = __( 'Rating', 'affiliate-product-showcase' );
                $new_columns['clicks'] = __( 'Clicks', 'affiliate-product-showcase' );
                $new_columns['conversions'] = __( 'Conversions', 'affiliate-product-showcase' );
            }
        }

        return $new_columns;
    }

    /**
     * Render custom column content
     *
     * @param string $column_name Column name
     * @param int $post_id Post ID
     * @return void
     */
    public function renderCustomColumns( string $column_name, int $post_id ): void {
        switch ( $column_name ) {
            case 'price':
                $this->renderPriceColumn( $post_id );
                break;
            case 'sku':
                $this->renderSkuColumn( $post_id );
                break;
            case 'brand':
                $this->renderBrandColumn( $post_id );
                break;
            case 'rating':
                $this->renderRatingColumn( $post_id );
                break;
            case 'clicks':
                $this->renderClicksColumn( $post_id );
                break;
            case 'conversions':
                $this->renderConversionsColumn( $post_id );
                break;
        }
    }

    /**
     * Render price column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderPriceColumn( int $post_id ): void {
        $price = get_post_meta( $post_id, '_price', true );

        if ( $price ) {
            printf(
                '<span class="product-price">%s</span>',
                esc_html( number_format( (float) $price, 2 ) )
            );
        }
    }

    /**
     * Render SKU column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderSkuColumn( int $post_id ): void {
        $sku = get_post_meta( $post_id, '_sku', true );

        if ( $sku ) {
            printf(
                '<span class="product-sku">%s</span>',
                esc_html( $sku )
            );
        }
    }

    /**
     * Render brand column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderBrandColumn( int $post_id ): void {
        $brand = get_post_meta( $post_id, '_brand', true );

        if ( $brand ) {
            printf(
                '<span class="product-brand">%s</span>',
                esc_html( $brand )
            );
        }
    }

    /**
     * Render rating column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderRatingColumn( int $post_id ): void {
        $rating = get_post_meta( $post_id, '_rating', true );

        if ( $rating ) {
            printf(
                '<div class="product-rating" title="%s">%s ★</div>',
                esc_attr( number_format( (float) $rating, 1 ) ),
                esc_html( number_format( (float) $rating, 1 ) )
            );
        }
    }

    /**
     * Render clicks column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderClicksColumn( int $post_id ): void {
        $clicks = $this->getProductClicks( $post_id );

        printf(
            '<span class="product-clicks">%d</span>',
            esc_html( $clicks )
        );
    }

    /**
     * Render conversions column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderConversionsColumn( int $post_id ): void {
        $conversions = $this->getProductConversions( $post_id );

        printf(
            '<span class="product-conversions">%d</span>',
            esc_html( $conversions )
        );
    }

    /**
     * Get product clicks count
     *
     * @param int $product_id Product ID
     * @return int
     */
    private function getProductClicks( int $product_id ): int {
        global $wpdb;

        $table_name = $wpdb->prefix . 'affiliate_analytics';

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} 
                WHERE product_id = %d 
                AND event_type = 'click'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                $product_id
            )
        );

        return (int) ( $count ?: 0 );
    }

    /**
     * Get product conversions count
     *
     * @param int $product_id Product ID
     * @return int
     */
    private function getProductConversions( int $product_id ): int {
        global $wpdb;

        $table_name = $wpdb->prefix . 'affiliate_analytics';

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} 
                WHERE product_id = %d 
                AND event_type = 'conversion'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                $product_id
            )
        );

        return (int) ( $count ?: 0 );
    }

    /**
     * Make custom columns sortable
     *
     * @param array $columns Existing sortable columns
     * @return array Modified sortable columns
     */
    public function makeColumnsSortable( array $columns ): array {
        $columns['price'] = 'price';
        $columns['rating'] = 'rating';
        $columns['brand'] = 'brand';

        return $columns;
    }

    /**
     * Add filters to the products list
     *
     * @param string $post_type Post type
     * @param string $which Which table (top or bottom)
     * @return void
     */
    public function addFilters( string $post_type, string $which ): void {
        if ( $post_type !== 'aps_product' || $which !== 'top' ) {
            return;
        }

        $brands = $this->getAvailableBrands();

        if ( ! empty( $brands ) ) {
            echo '<select name="brand_filter" id="brand_filter">';
            echo '<option value="">' . esc_html__( 'All Brands', 'affiliate-product-showcase' ) . '</option>';

            foreach ( $brands as $brand ) {
                $selected = isset( $_GET['brand_filter'] ) && $_GET['brand_filter'] === $brand ? 'selected' : '';
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $brand ),
                    esc_attr( $selected ),
                    esc_html( $brand )
                );
            }

            echo '</select>';
        }

        $in_stock = isset( $_GET['in_stock'] ) ? $_GET['in_stock'] : '';

        echo '<select name="in_stock" id="in_stock">';
        echo '<option value="">' . esc_html__( 'All Stock Status', 'affiliate-product-showcase' ) . '</option>';
        echo '<option value="1" ' . selected( $in_stock, '1', false ) . '>' . esc_html__( 'In Stock', 'affiliate-product-showcase' ) . '</option>';
        echo '<option value="0" ' . selected( $in_stock, '0', false ) . '>' . esc_html__( 'Out of Stock', 'affiliate-product-showcase' ) . '</option>';
        echo '</select>';
    }

    /**
     * Get available brands
     *
     * @return array List of brands
     */
    private function getAvailableBrands(): array {
        global $wpdb;

        $brands = $wpdb->get_col(
            "SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_brand' 
            AND meta_value != ''
            ORDER BY meta_value ASC"
        );

        return $brands ?: [];
    }

    /**
     * Handle custom column sorting
     *
     * @param \WP_Query $query WP Query object
     * @return void
     */
    public function handleCustomSorting( \WP_Query $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        if ( in_array( $orderby, [ 'price', 'rating', 'brand' ], true ) ) {
            $query->set( 'meta_key', '_' . $orderby );
            $query->set( 'orderby', 'meta_value_num' );
        }
    }
}
