<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Helpers\Logger;

/**
 * Bulk Actions
 *
 * Handles bulk actions for products list.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class BulkActions {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter( 'bulk_actions-edit-aps_product', [ $this, 'registerBulkActions' ] );
        add_filter( 'handle_bulk_actions-edit-aps_product', [ $this, 'handleBulkActions' ], 10, 3 );
        add_action( 'admin_notices', [ $this, 'bulkActionNotices' ] );
    }

    /**
     * Register custom bulk actions
     *
     * @param array $bulk_actions Existing bulk actions
     * @return array Modified bulk actions
     */
    public function registerBulkActions( array $bulk_actions ): array {
        $bulk_actions['set_in_stock'] = __( 'Set In Stock', 'affiliate-product-showcase' );
        $bulk_actions['set_out_of_stock'] = __( 'Set Out of Stock', 'affiliate-product-showcase' );
        $bulk_actions['reset_clicks'] = __( 'Reset Click Count', 'affiliate-product-showcase' );
        $bulk_actions['export_products'] = __( 'Export Products', 'affiliate-product-showcase' );

        return $bulk_actions;
    }

    /**
     * Handle bulk action execution
     *
     * @param string $redirect_to Redirect URL
     * @param string $doaction Action to perform
     * @param array $post_ids Post IDs
     * @return string Redirect URL
     */
    public function handleBulkActions( string $redirect_to, string $doaction, array $post_ids ): string {
        if ( empty( $post_ids ) ) {
            return $redirect_to;
        }

        $count = 0;
        $error = false;

        switch ( $doaction ) {
            case 'set_in_stock':
                $count = $this->setStockStatus( $post_ids, true );
                break;
            case 'set_out_of_stock':
                $count = $this->setStockStatus( $post_ids, false );
                break;
            case 'reset_clicks':
                $count = $this->resetClickCounts( $post_ids );
                break;
            case 'export_products':
                $count = $this->exportProducts( $post_ids );
                break;
        }

        if ( $count > 0 && ! $error ) {
            $redirect_to = add_query_arg( [
                'bulk_action' => $doaction,
                'processed'  => $count,
                'ids'        => implode( ',', $post_ids ),
            ], $redirect_to );
        }

        return $redirect_to;
    }

    /**
     * Set stock status for products
     *
     * @param array $post_ids Post IDs
     * @param bool $in_stock Stock status
     * @return int Number of updated products
     */
    private function setStockStatus( array $post_ids, bool $in_stock ): int {
        $count = 0;

        foreach ( $post_ids as $post_id ) {
            $result = update_post_meta( $post_id, '_in_stock', $in_stock );

            if ( $result ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Reset click counts for products
     *
     * @param array $post_ids Post IDs
     * @return int Number of reset products
     */
    private function resetClickCounts( array $post_ids ): int {
        global $wpdb;

        $placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}affiliate_analytics 
                WHERE product_id IN ({$placeholders}) 
                AND event_type = 'click'",
                ...$post_ids
            )
        );

        return (int) $result ?: 0;
    }

    /**
     * Export products to CSV
     *
     * @param array $post_ids Post IDs
     * @return int Number of exported products
     */
    private function exportProducts( array $post_ids ): int {
        $filename = 'products-export-' . date( 'Y-m-d-His' ) . '.csv';
        $filepath = wp_upload_dir()['basedir'] . '/' . $filename;

        $file = fopen( $filepath, 'w' );

        if ( ! $file ) {
            Logger::error( 'Failed to create export file', [ 'filename' => $filename ] );
            return 0;
        }

        // CSV headers
        fputcsv( $file, [
            __( 'ID', 'affiliate-product-showcase' ),
            __( 'Title', 'affiliate-product-showcase' ),
            __( 'SKU', 'affiliate-product-showcase' ),
            __( 'Brand', 'affiliate-product-showcase' ),
            __( 'Price', 'affiliate-product-showcase' ),
            __( 'Rating', 'affiliate-product-showcase' ),
            __( 'In Stock', 'affiliate-product-showcase' ),
            __( 'Affiliate URL', 'affiliate-product-showcase' ),
            __( 'Image URL', 'affiliate-product-showcase' ),
        ] );

        $count = 0;

        // Use generator for memory-efficient processing
        foreach ( $this->getProductsForExport( $post_ids ) as $product_data ) {
            fputcsv( $file, $product_data );
            $count++;
        }

        fclose( $file );

        // Generate download URL
        $download_url = admin_url( 'admin-post.php?action=download_product_export&file=' . urlencode( $filename ) );

        // Store download URL in transient
        set_transient( 'product_export_url_' . get_current_user_id(), $download_url, HOUR_IN_SECONDS );

        return $count;
    }

    /**
     * Get products for export using generator pattern
     * Yields product data one at a time to reduce memory usage
     *
     * @param array<int> $post_ids Post IDs to export
     * @return \Generator<array<string,mixed>> Generator yielding product data arrays
     */
    private function getProductsForExport( array $post_ids ): \Generator {
        $batch_size = 50; // Process 50 products at a time for memory efficiency

        // Process in batches to reduce memory usage
        foreach ( array_chunk( $post_ids, $batch_size ) as $chunk ) {
            $args = [
                'post__in'     => $chunk,
                'post_type'    => 'aps_product',
                'post_status'  => 'any',
                'posts_per_page' => -1,
                'fields'       => 'ids',
            ];

            $product_post_ids = get_posts( $args );

            // Pre-fetch all meta data for batch to reduce queries
            $all_meta = [];
            foreach ( $product_post_ids as $product_post_id ) {
                $all_meta[ $product_post_id ] = get_post_meta( $product_post_id );
            }

            // Process posts with pre-fetched meta
            foreach ( $product_post_ids as $product_post_id ) {
                $post = get_post( $product_post_id );

                if (!$post) {
                    continue;
                }

                $meta = $all_meta[$product_post_id] ?? [];

                yield $this->buildProductExportData($post, $meta);
            }

            // Clear meta array to free memory after each batch
            $all_meta = [];
        }
    }

    /**
     * Build product export data array
     *
     * @param \WP_Post $post Post object
     * @param array $meta Meta data
     * @return array Product export data
     */
    private function buildProductExportData(\WP_Post $post, array $meta): array
    {
        return [
            $post->ID,
            $post->post_title,
            $meta['_sku'][0] ?? '',
            $meta['_brand'][0] ?? '',
            $meta['_price'][0] ?? '',
            $meta['_rating'][0] ?? '',
            $meta['_in_stock'][0] ?? '',
            $meta['_affiliate_url'][0] ?? '',
            $meta['_image_url'][0] ?? '',
        ];
    }

    /**
     * Display bulk action notices
     *
     * @return void
     */
    public function bulkActionNotices(): void {
        if (!isset($_GET['bulk_action']) || !isset($_GET['processed'])) {
            return;
        }

        $action = sanitize_text_field(wp_unslash($_GET['bulk_action']));
        $processed = intval($_GET['processed']);

        $message = '';

        switch ($action) {
            case 'set_in_stock':
                $message = $this->getInStockMessage($processed);
                break;
            case 'set_out_of_stock':
                $message = $this->getOutOfStockMessage($processed);
                break;
            case 'reset_clicks':
                $message = $this->getResetClicksMessage($processed);
                break;
            case 'export_products':
                $message = $this->getExportMessage($processed);
                break;
        }

        if ($message) {
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                wp_kses_post($message)
            );
        }
    }

    /**
     * Get bulk action message for set in stock
     *
     * @param int $processed Number of products processed
     * @return string Message
     */
    private function getInStockMessage(int $processed): string
    {
        return sprintf(
            _n('%d product set as in stock.', '%d products set as in stock.', $processed, 'affiliate-product-showcase'),
            $processed
        );
    }

    /**
     * Get bulk action message for set out of stock
     *
     * @param int $processed Number of products processed
     * @return string Message
     */
    private function getOutOfStockMessage(int $processed): string
    {
        return sprintf(
            _n('%d product set as out of stock.', '%d products set as out of stock.', $processed, 'affiliate-product-showcase'),
            $processed
        );
    }

    /**
     * Get bulk action message for reset clicks
     *
     * @param int $processed Number of products processed
     * @return string Message
     */
    private function getResetClicksMessage(int $processed): string
    {
        return sprintf(
            _n('Click count reset for %d product.', 'Click count reset for %d products.', $processed, 'affiliate-product-showcase'),
            $processed
        );
    }

    /**
     * Get bulk action message for export products
     *
     * @param int $processed Number of products processed
     * @return string Message
     */
    private function getExportMessage(int $processed): string
    {
        $message = sprintf(
            _n('%d product exported.', '%d products exported.', $processed, 'affiliate-product-showcase'),
            $processed
        );

        // Add download link
        $download_url = get_transient('product_export_url_' . get_current_user_id());
        if ($download_url) {
            $message .= ' <a href="' . esc_url($download_url) . '" class="button button-small">' . esc_html__('Download Export', 'affiliate-product-showcase') . '</a>';
        }

        return $message;
    }

    /**
     * Handle product export download
     *
     * @return void
     */
    public function handleExportDownload(): void {
        if ( ! isset( $_GET['file'] ) ) {
            return;
        }

        $filename = sanitize_file_name( wp_unslash( $_GET['file'] ) );
        $filepath = wp_upload_dir()['basedir'] . '/' . $filename;

        if ( ! file_exists( $filepath ) ) {
            wp_die( __( 'Export file not found.', 'affiliate-product-showcase' ), 404 );
        }

        // Download file
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . filesize( $filepath ) );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        readfile( $filepath );
        exit;
    }
}
