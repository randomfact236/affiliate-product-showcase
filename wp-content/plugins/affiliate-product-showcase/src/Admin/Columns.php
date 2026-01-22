<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Admin Columns
 *
 * Manages custom columns for products list table.
 * Columns match backend design diagram: Logo, Category, Tags, Ribbon, Featured, Price, Status
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
        add_action( 'pre_get_posts', [ $this, 'handleCustomSorting' ] );
        add_action( 'restrict_manage_posts', [ $this, 'addFilters' ], 10, 2 );
    }

    /**
     * Add custom columns to products list
     *
     * Adds columns matching backend design diagram:
     * - Logo, Category, Tags, Ribbon, Featured, Price, Status
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function addCustomColumns( array $columns ): array {
        // Remove default columns we don't need
        unset( $columns['date'] );
        
        // Add custom columns
        $new_columns = [];

        foreach ( $columns as $key => $value ) {
            // Add logo column first (after checkbox)
            if ( $key === 'cb' ) {
                $new_columns[ $key ] = $value;
                $new_columns['logo'] = __( 'Logo', 'affiliate-product-showcase' );
            } 
            // Add other custom columns after title
            elseif ( $key === 'title' ) {
                $new_columns[ $key ] = $value;
                $new_columns['category'] = __( 'Category', 'affiliate-product-showcase' );
                $new_columns['tags'] = __( 'Tags', 'affiliate-product-showcase' );
                $new_columns['ribbon'] = __( 'Ribbon', 'affiliate-product-showcase' );
                $new_columns['featured'] = __( 'Featured', 'affiliate-product-showcase' );
                $new_columns['price'] = __( 'Price', 'affiliate-product-showcase' );
                $new_columns['status'] = __( 'Status', 'affiliate-product-showcase' );
            } 
            else {
                $new_columns[ $key ] = $value;
            }
        }

        return $new_columns;
    }

    /**
     * Render custom column content
     *
     * Renders columns matching backend design diagram:
     * - Logo, Category, Tags, Ribbon, Featured, Price, Status
     *
     * @param string $column_name Column name
     * @param int $post_id Post ID
     * @return void
     */
    public function renderCustomColumns( string $column_name, int $post_id ): void {
        switch ( $column_name ) {
            case 'logo':
                $this->renderLogoColumn( $post_id );
                break;
            case 'category':
                $this->renderCategoryColumn( $post_id );
                break;
            case 'tags':
                $this->renderTagsColumn( $post_id );
                break;
            case 'ribbon':
                $this->renderRibbonColumn( $post_id );
                break;
            case 'featured':
                $this->renderFeaturedColumn( $post_id );
                break;
            case 'price':
                $this->renderPriceColumn( $post_id );
                break;
            case 'status':
                $this->renderStatusColumn( $post_id );
                break;
        }
    }

    /**
     * Render logo column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderLogoColumn( int $post_id ): void {
        $image_url = get_post_meta( $post_id, 'aps_image_url', true );
        $title = get_the_title( $post_id );

        if ( $image_url ) {
            printf(
                '<img src="%s" alt="%s" class="product-logo" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />',
                esc_url( $image_url ),
                esc_attr( $title )
            );
        } else {
            printf(
                '<div class="product-logo-placeholder" style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #666;">%s</div>',
                esc_html( substr( $title, 0, 1 ) )
            );
        }
    }

    /**
     * Render category column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderCategoryColumn( int $post_id ): void {
        $categories = get_the_terms( $post_id, 'aps_category' );
        
        if ( $categories && ! is_wp_error( $categories ) ) {
            $output = '';
            foreach ( $categories as $category ) {
                $output .= sprintf(
                    '<span class="product-category">%s</span><br>',
                    esc_html( $category->name )
                );
            }
            echo $output;
        }
    }

    /**
     * Render tags column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderTagsColumn( int $post_id ): void {
        $tags = get_the_terms( $post_id, 'aps_tag' );
        
        if ( $tags && ! is_wp_error( $tags ) ) {
            $output = '';
            foreach ( $tags as $tag ) {
                $output .= sprintf(
                    '<span class="product-tag">%s</span> ',
                    esc_html( $tag->name )
                );
            }
            echo $output;
        }
    }

    /**
     * Render ribbon column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderRibbonColumn( int $post_id ): void {
        $badge = get_post_meta( $post_id, 'aps_badge', true );

        if ( $badge ) {
            printf(
                '<span class="product-badge">%s</span>',
                esc_html( $badge )
            );
        }
    }

    /**
     * Render featured column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderFeaturedColumn( int $post_id ): void {
        $featured = get_post_meta( $post_id, 'aps_featured', true );
        $is_featured = ! empty( $featured ) && $featured === '1';

        if ( $is_featured ) {
            echo '<span class="product-featured" style="color: #f59e0b; font-size: 18px;">★</span>';
        }
    }

    /**
     * Render price column with discount percentage
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderPriceColumn( int $post_id ): void {
        $price = get_post_meta( $post_id, 'aps_price', true );
        $original_price = get_post_meta( $post_id, 'aps_original_price', true );
        $currency = get_post_meta( $post_id, 'aps_currency', true ) ?: 'USD';

        if ( $price ) {
            $formatted_price = number_format( (float) $price, 2 );
            echo '<span class="product-price">';
            
            // Show original price and discount if available
            if ( $original_price && $original_price > $price ) {
                $formatted_original = number_format( (float) $original_price, 2 );
                $discount = round( ( ( $original_price - $price ) / $original_price ) * 100 );
                
                printf(
                    '%s<br><span style="text-decoration: line-through; color: #999;">%s</span><br><span style="color: #ef4444; font-weight: bold;">%d%% OFF</span>',
                    esc_html( $currency . ' ' . $formatted_price ),
                    esc_html( $currency . ' ' . $formatted_original ),
                    esc_html( $discount )
                );
            } else {
                printf(
                    '%s',
                    esc_html( $currency . ' ' . $formatted_price )
                );
            }
            
            echo '</span>';
        }
    }

    /**
     * Render status column
     *
     * @param int $post_id Post ID
     * @return void
     */
    private function renderStatusColumn( int $post_id ): void {
        $status = get_post_status( $post_id );
        $status_map = [
            'publish' => __( 'PUBLISHED', 'affiliate-product-showcase' ),
            'draft' => __( 'DRAFT', 'affiliate-product-showcase' ),
            'trash' => __( 'TRASH', 'affiliate-product-showcase' ),
        ];

        $status_text = $status_map[ $status ] ?? strtoupper( $status );
        $color_map = [
            'publish' => '#10b981', // Green
            'draft' => '#6b7280', // Gray
            'trash' => '#ef4444', // Red
        ];
        $color = $color_map[ $status ] ?? '#6b7280';

        printf(
            '<span class="product-status" style="color: %s; font-weight: 500;">%s</span>',
            esc_attr( $color ),
            esc_html( $status_text )
        );
    }

    /**
     * Make custom columns sortable
     *
     * @param array $columns Existing sortable columns
     * @return array Modified sortable columns
     */
    public function makeColumnsSortable( array $columns ): array {
        $columns['price'] = 'price';
        $columns['featured'] = 'featured';

        return $columns;
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

        if ( $orderby === 'price' ) {
            $query->set( 'meta_key', 'aps_price' );
            $query->set( 'orderby', 'meta_value_num' );
        }
        
        if ( $orderby === 'featured' ) {
            $query->set( 'meta_key', 'aps_featured' );
            $query->set( 'orderby', 'meta_value' );
        }
    }

    /**
     * Add filters to products list
     *
     * @param string $post_type Post type
     * @param string $which Which table (top or bottom)
     * @return void
     */
    public function addFilters( string $post_type, string $which ): void {
        if ( $post_type !== 'aps_product' || $which !== 'top' ) {
            return;
        }

        // Category filter
        $categories = get_terms( [
            'taxonomy' => 'aps_category',
            'hide_empty' => false,
        ] );

        if ( $categories && ! is_wp_error( $categories ) ) {
            echo '<select name="aps_category_filter" id="aps_category_filter">';
            echo '<option value="">' . esc_html__( 'All Categories', 'affiliate-product-showcase' ) . '</option>';
            
            foreach ( $categories as $category ) {
                $selected = isset( $_GET['aps_category_filter'] ) && $_GET['aps_category_filter'] == $category->term_id ? 'selected' : '';
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $category->term_id ),
                    esc_attr( $selected ),
                    esc_html( $category->name )
                );
            }
            
            echo '</select>';
        }

        // Featured filter
        $featured_filter = isset( $_GET['featured_filter'] ) ? $_GET['featured_filter'] : '';
        echo '<select name="featured_filter" id="featured_filter">';
        echo '<option value="">' . esc_html__( 'All Products', 'affiliate-product-showcase' ) . '</option>';
        echo '<option value="1" ' . selected( $featured_filter, '1', false ) . '>' . esc_html__( 'Featured Only', 'affiliate-product-showcase' ) . '</option>';
        echo '</select>';
    }
}
