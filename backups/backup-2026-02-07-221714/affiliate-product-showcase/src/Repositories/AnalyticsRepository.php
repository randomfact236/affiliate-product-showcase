<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Abstracts\AbstractRepository;
use AffiliateProductShowcase\Helpers\Logger;

/**
 * Analytics Repository
 *
 * Handles data access for analytics and tracking data.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 */
class AnalyticsRepository extends AbstractRepository {

    /**
     * Table name for analytics
     *
     * @var string
     */
    private string $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'affiliate_analytics';
    }

    /**
     * Record a click event
     *
     * @param int $product_id Product ID
     * @param int|null $user_id User ID (optional)
     * @param string|null $ip IP address
     * @return int|false Click ID or false on failure
     */
    public function recordClick( int $product_id, ?int $user_id = null, ?string $ip = null ) {
        global $wpdb;

        $data = [
            'product_id' => $product_id,
            'user_id'    => $user_id ?? get_current_user_id(),
            'event_type' => 'click',
            'ip_address'  => $ip ?? $this->getClientIp(),
            'created_at'  => current_time( 'mysql' ),
        ];

        $result = $wpdb->insert( $this->table_name, $data );

        if ( $result === false ) {
            Logger::error( 'Failed to record click', [
                'product_id' => $product_id,
                'error'      => $wpdb->last_error,
            ] );
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Record a conversion event
     *
     * @param int $product_id Product ID
     * @param float $revenue Revenue amount
     * @param int|null $user_id User ID (optional)
     * @return int|false Conversion ID or false on failure
     */
    public function recordConversion( int $product_id, float $revenue, ?int $user_id = null ) {
        global $wpdb;

        $data = [
            'product_id' => $product_id,
            'user_id'    => $user_id ?? get_current_user_id(),
            'event_type' => 'conversion',
            'revenue'    => $revenue,
            'ip_address'  => $this->getClientIp(),
            'created_at'  => current_time( 'mysql' ),
        ];

        $result = $wpdb->insert( $this->table_name, $data );

        if ( $result === false ) {
            Logger::error( 'Failed to record conversion', [
                'product_id' => $product_id,
                'error'      => $wpdb->last_error,
            ] );
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Get click statistics for a product
     *
     * @param int $product_id Product ID
     * @param int $days Number of days to look back
     * @return array Statistics
     */
    public function getProductStats( int $product_id, int $days = 30 ): array {
        $cache_key = "aps_stats_product_{$product_id}_{$days}";
        $cached = get_transient( $cache_key );
        
        if ( false !== $cached ) {
            return $cached;
        }

        global $wpdb;

        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(CASE WHEN event_type = 'click' THEN 1 END) as clicks,
                    COUNT(CASE WHEN event_type = 'conversion' THEN 1 END) as conversions,
                    SUM(CASE WHEN event_type = 'conversion' THEN revenue ELSE 0 END) as revenue
                FROM {$this->table_name} 
                WHERE product_id = %d 
                AND created_at >= %s",
                $product_id,
                $date
            )
        );

        $total_clicks      = (int) ( $row->clicks ?? 0 );
        $total_conversions = (int) ( $row->conversions ?? 0 );
        $total_revenue     = (float) ( $row->revenue ?? 0 );

        $conversion_rate = $total_clicks > 0 
            ? ( $total_conversions / $total_clicks ) * 100 
            : 0;

        $stats = [
            'product_id'       => $product_id,
            'total_clicks'      => $total_clicks,
            'total_conversions' => $total_conversions,
            'total_revenue'     => $total_revenue,
            'conversion_rate'   => round( $conversion_rate, 2 ),
            'period_days'       => $days,
        ];

        set_transient( $cache_key, $stats, 15 * MINUTE_IN_SECONDS );

        return $stats;
    }

    /**
     * Get overall analytics statistics
     *
     * @param int $days Number of days to look back
     * @return array Statistics
     */
    public function getOverallStats( int $days = 30 ): array {
        $cache_key = "aps_stats_overall_{$days}";
        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        global $wpdb;

        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(CASE WHEN event_type = 'click' THEN 1 END) as clicks,
                    COUNT(CASE WHEN event_type = 'conversion' THEN 1 END) as conversions,
                    SUM(CASE WHEN event_type = 'conversion' THEN revenue ELSE 0 END) as revenue
                FROM {$this->table_name} 
                WHERE created_at >= %s",
                $date
            )
        );

        $total_clicks      = (int) ( $row->clicks ?? 0 );
        $total_conversions = (int) ( $row->conversions ?? 0 );
        $total_revenue     = (float) ( $row->revenue ?? 0 );

        $conversion_rate = $total_clicks > 0 
            ? ( $total_conversions / $total_clicks ) * 100 
            : 0;

        $stats = [
            'total_clicks'      => $total_clicks,
            'total_conversions' => $total_conversions,
            'total_revenue'     => $total_revenue,
            'conversion_rate'   => round( $conversion_rate, 2 ),
            'period_days'       => $days,
        ];

        set_transient( $cache_key, $stats, 15 * MINUTE_IN_SECONDS );

        return $stats;
    }

    /**
     * Get top performing products
     *
     * @param int $limit Number of products to return
     * @param int $days Number of days to look back
     * @return array List of products with stats
     */
    public function getTopProducts( int $limit = 10, int $days = 30 ): array {
        global $wpdb;

        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    product_id,
                    COUNT(CASE WHEN event_type = 'click' THEN 1 END) as clicks,
                    COUNT(CASE WHEN event_type = 'conversion' THEN 1 END) as conversions,
                    SUM(CASE WHEN event_type = 'conversion' THEN revenue ELSE 0 END) as revenue
                FROM {$this->table_name}
                WHERE created_at >= %s
                GROUP BY product_id
                ORDER BY clicks DESC
                LIMIT %d",
                $date,
                $limit
            ),
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Get click data for a date range
     *
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Daily click data
     */
    public function getClicksByDate( string $start_date, string $end_date ): array {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(CASE WHEN event_type = 'click' THEN 1 END) as clicks,
                    COUNT(CASE WHEN event_type = 'conversion' THEN 1 END) as conversions
                FROM {$this->table_name}
                WHERE created_at BETWEEN %s AND %s
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function getClientIp(): string {
        $ip = '';

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        return $ip ?: 'unknown';
    }

    /**
     * Clean up old analytics data
     *
     * @param int $days Number of days to retain
     * @return int Number of rows deleted
     */
    public function cleanupOldData( int $days = 90 ): int {
        global $wpdb;

        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        return (int) $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} 
                WHERE created_at < %s",
                $date
            )
        );
    }

    /**
     * Create analytics table
     *
     * @return void
     */
    public function createTable(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            event_type varchar(50) NOT NULL,
            revenue decimal(10,2) DEFAULT 0.00,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(500) DEFAULT NULL,
            referer varchar(500) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY event_type (event_type),
            KEY created_at (created_at),
            KEY product_created (product_id, created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
