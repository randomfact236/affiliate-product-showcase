<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Helpers\Logger;

/**
 * Notification Service
 *
 * Handles sending notifications for various plugin events
 * including email notifications and admin notices.
 *
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 */
class NotificationService {

    /**
     * Notification types
     *
     * @var array
     */
    private const NOTIFICATION_TYPES = [
        'info'    => 'info',
        'success' => 'success',
        'warning' => 'warning',
        'error'   => 'error',
    ];

    /**
     * Admin notices
     *
     * @var array
     */
    private static array $admin_notices = [];

    /**
     * Add admin notice
     *
     * @param string $message Notice message
     * @param string $type Notice type
     * @param bool $dismissible Whether notice is dismissible
     * @return void
     */
    public static function addAdminNotice( string $message, string $type = 'info', bool $dismissible = true ): void {
        $type = in_array( $type, self::NOTIFICATION_TYPES, true ) ? $type : 'info';

        self::$admin_notices[] = [
            'message'     => $message,
            'type'        => $type,
            'dismissible' => $dismissible,
        ];
    }

    /**
     * Display admin notices
     *
     * @return void
     */
    public static function displayAdminNotices(): void {
        if ( empty( self::$admin_notices ) ) {
            return;
        }

        foreach ( self::$admin_notices as $notice ) {
            self::renderNotice( $notice['message'], $notice['type'], $notice['dismissible'] );
        }

        // Clear notices after displaying
        self::$admin_notices = [];
    }

    /**
     * Render a single notice
     *
     * @param string $message Notice message
     * @param string $type Notice type
     * @param bool $dismissible Whether notice is dismissible
     * @return void
     */
    private static function renderNotice( string $message, string $type, bool $dismissible ): void {
        $class = "notice notice-{$type}";

        if ( $dismissible ) {
            $class .= ' is-dismissible';
        }

        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr( $class ),
            wp_kses_post( $message )
        );
    }

    /**
     * Send email notification
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email message
     * @param array $headers Additional headers
     * @return bool
     */
    public static function sendEmail( string $to, string $subject, string $message, array $headers = [] ): bool {
        $headers = array_merge(
            [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
            ],
            $headers
        );

        $sent = wp_mail( $to, $subject, $message, $headers );

        if ( ! $sent ) {
            Logger::error( 'Failed to send email notification', [
                'to'      => $to,
                'subject' => $subject,
            ] );
        }

        return $sent;
    }

    /**
     * Send notification to admin
     *
     * @param string $subject Subject line
     * @param string $message Message content
     * @return bool
     */
    public static function notifyAdmin( string $subject, string $message ): bool {
        return self::sendEmail(
            get_option( 'admin_email' ),
            $subject,
            $message
        );
    }

    /**
     * Send product update notification
     *
     * @param int $product_id Product ID
     * @param string $action Action performed (created, updated, deleted)
     * @return bool
     */
    public static function notifyProductUpdate( int $product_id, string $action ): bool {
        $subject = sprintf(
            '[%s] Product %s',
            get_bloginfo( 'name' ),
            ucfirst( $action )
        );

        $product = get_post( $product_id );
        if ( ! $product ) {
            return false;
        }

        $message = sprintf(
            '<h2>%s</h2>
            <p><strong>Product:</strong> %s</p>
            <p><strong>Action:</strong> %s</p>
            <p><strong>Time:</strong> %s</p>
            <p><strong>User:</strong> %s</p>',
            esc_html( $subject ),
            esc_html( $product->post_title ),
            esc_html( ucfirst( $action ) ),
            current_time( 'mysql' ),
            get_the_author_meta( 'display_name', get_current_user_id() )
        );

        return self::notifyAdmin( $subject, $message );
    }

    /**
     * Send analytics report notification
     *
     * @param array $stats Statistics data
     * @return bool
     */
    public static function sendAnalyticsReport( array $stats ): bool {
        $subject = sprintf(
            '[%s] Affiliate Analytics Report',
            get_bloginfo( 'name' )
        );

        $message = sprintf(
            '<h2>Analytics Report</h2>
            <p><strong>Period:</strong> Last 30 days</p>
            <hr>
            <h3>Statistics</h3>
            <ul>
                <li>Total Clicks: %d</li>
                <li>Total Conversions: %d</li>
                <li>Conversion Rate: %.2f%%</li>
                <li>Total Revenue: $%.2f</li>
            </ul>
            <p><em>Generated on: %s</em></p>',
            $stats['total_clicks'] ?? 0,
            $stats['total_conversions'] ?? 0,
            $stats['conversion_rate'] ?? 0,
            $stats['total_revenue'] ?? 0,
            current_time( 'mysql' )
        );

        return self::notifyAdmin( $subject, $message );
    }

    /**
     * Send error notification
     *
     * @param string $error_message Error message
     * @param array $context Additional context
     * @return bool
     */
    public static function notifyError( string $error_message, array $context = [] ): bool {
        $subject = sprintf(
            '[%s] Error Notification',
            get_bloginfo( 'name' )
        );

        $message = sprintf(
            '<h2>Error Occurred</h2>
            <p><strong>Message:</strong> %s</p>
            <p><strong>Time:</strong> %s</p>
            <p><strong>URL:</strong> %s</p>
            %s',
            esc_html( $error_message ),
            current_time( 'mysql' ),
            esc_url( $_SERVER['REQUEST_URI'] ?? 'N/A' ),
            ! empty( $context ) ? '<pre>' . esc_html( print_r( $context, true ) ) . '</pre>' : ''
        );

        return self::notifyAdmin( $subject, $message );
    }

    /**
     * Send low stock notification
     *
     * @param int $product_id Product ID
     * @param int $current_stock Current stock level
     * @return bool
     */
    public static function notifyLowStock( int $product_id, int $current_stock ): bool {
        $product = get_post( $product_id );
        if ( ! $product ) {
            return false;
        }

        $subject = sprintf(
            '[%s] Low Stock Alert: %s',
            get_bloginfo( 'name' ),
            $product->post_title
        );

        $message = sprintf(
            '<h2>Low Stock Alert</h2>
            <p><strong>Product:</strong> %s</p>
            <p><strong>Current Stock:</strong> %d</p>
            <p><strong>Time:</strong> %s</p>
            <p><a href="%s">View Product</a></p>',
            esc_html( $product->post_title ),
            $current_stock,
            current_time( 'mysql' ),
            get_edit_post_link( $product_id )
        );

        return self::notifyAdmin( $subject, $message );
    }

    /**
     * Initialize hooks for admin notices
     *
     * @return void
     */
    public static function init(): void {
        add_action( 'admin_notices', [ __CLASS__, 'displayAdminNotices' ] );
    }

    /**
     * Get notification types
     *
     * @return array
     */
    public static function getNotificationTypes(): array {
        return self::NOTIFICATION_TYPES;
    }
}
