<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Security;

use AffiliateProductShowcase\Helpers\Logger;
use AffiliateProductShowcase\Database\Database;

/**
 * Audit Logger
 *
 * Logs security-related events and user actions
 * for audit trail and compliance purposes.
 *
 * @package AffiliateProductShowcase\Security
 * @since 1.0.0
 */
class AuditLogger {

    /**
     * Table name for audit logs
     *
     * @var string
     */
    private string $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'affiliate_audit_log';
    }

    /**
     * Log a security event
     *
     * @param string $event Event type (login, logout, permission_denied, etc.)
     * @param string $message Event message
     * @param array $context Additional context data
     * @param int|null $user_id User ID (defaults to current user)
     * @return bool Success status
     */
    public function logEvent(
        string $event,
        string $message,
        array $context = [],
        ?int $user_id = null
    ): bool {
        global $wpdb;

        $user_id = $user_id ?? get_current_user_id();
        $ip      = $this->getClientIp();
        $ua      = $this->getUserAgent();

        $data = [
            'event_type'      => sanitize_text_field( $event ),
            'user_id'         => $user_id,
            'message'         => sanitize_textarea_field( $message ),
            'ip_address'      => $ip,
            'user_agent'      => $ua,
            'context'         => maybe_serialize( $context ),
            'created_at'      => current_time( 'mysql' ),
        ];

        $result = $wpdb->insert( $this->table_name, $data );

        if ( $result === false ) {
            Logger::error( 'Failed to log audit event: ' . $wpdb->last_error );
            return false;
        }

        // Also log to standard logger
        Logger::info( "Audit: [{$event}] {$message}", $context );

        return true;
    }

    /**
     * Log login attempt
     *
     * @param string $username Username
     * @param bool $success Whether login was successful
     * @return bool
     */
    public function logLogin( string $username, bool $success ): bool {
        $event = $success ? 'login_success' : 'login_failed';
        return $this->logEvent(
            $event,
            sprintf( 'Login attempt for user: %s', $username ),
            [ 'username' => $username ]
        );
    }

    /**
     * Log permission check
     *
     * @param string $capability Capability checked
     * @param bool $granted Whether permission was granted
     * @param array $context Additional context
     * @return bool
     */
    public function logPermissionCheck( string $capability, bool $granted, array $context = [] ): bool {
        $event = $granted ? 'permission_granted' : 'permission_denied';
        return $this->logEvent(
            $event,
            sprintf( 'Permission check: %s', $capability ),
            array_merge( $context, [ 'capability' => $capability ] )
        );
    }

    /**
     * Log product modification
     *
     * @param int $product_id Product ID
     * @param string $action Action (created, updated, deleted)
     * @param array $changes Changes made
     * @return bool
     */
    public function logProductChange( int $product_id, string $action, array $changes = [] ): bool {
        return $this->logEvent(
            "product_{$action}",
            sprintf( 'Product %s: ID %d', $action, $product_id ),
            array_merge( [ 'product_id' => $product_id ], $changes )
        );
    }

    /**
     * Log settings change
     *
     * @param array $old_settings Old settings
     * @param array $new_settings New settings
     * @return bool
     */
    public function logSettingsChange( array $old_settings, array $new_settings ): bool {
        $changes = array_diff_assoc( $new_settings, $old_settings );
        
        return $this->logEvent(
            'settings_changed',
            'Plugin settings updated',
            [ 'changes' => $changes ]
        );
    }

    /**
     * Log security alert
     *
     * @param string $alert_type Type of alert
     * @param string $message Alert message
     * @param array $context Additional context
     * @return bool
     */
    public function logSecurityAlert( string $alert_type, string $message, array $context = [] ): bool {
        return $this->logEvent(
            "security_alert_{$alert_type}",
            $message,
            $context
        );
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
     * Get user agent string
     *
     * @return string
     */
    private function getUserAgent(): string {
        return ! empty( $_SERVER['HTTP_USER_AGENT'] ) 
            ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
            : 'unknown';
    }

    /**
     * Get audit logs for a specific user
     *
     * @param int $user_id User ID
     * @param int $limit Number of logs to retrieve
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getUserLogs( int $user_id, int $limit = 50, int $offset = 0 ): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            WHERE user_id = %d 
            ORDER BY created_at DESC 
            LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        );

        return $wpdb->get_results( $query, ARRAY_A ) ?: [];
    }

    /**
     * Get audit logs by event type
     *
     * @param string $event_type Event type
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function getLogsByEventType( string $event_type, int $limit = 50 ): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            WHERE event_type = %s 
            ORDER BY created_at DESC 
            LIMIT %d",
            $event_type,
            $limit
        );

        return $wpdb->get_results( $query, ARRAY_A ) ?: [];
    }

    /**
     * Get recent audit logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function getRecentLogs( int $limit = 50 ): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            ORDER BY created_at DESC 
            LIMIT %d",
            $limit
        );

        return $wpdb->get_results( $query, ARRAY_A ) ?: [];
    }

    /**
     * Get log statistics
     *
     * @param int $days Number of days to look back
     * @return array
     */
    public function getStatistics( int $days = 30 ): array {
        global $wpdb;

        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        // Total events
        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} 
                WHERE created_at >= %s",
                $date
            )
        );

        // Events by type
        $by_type = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT event_type, COUNT(*) as count 
                FROM {$this->table_name} 
                WHERE created_at >= %s 
                GROUP BY event_type 
                ORDER BY count DESC",
                $date
            ),
            ARRAY_A
        );

        // Failed logins
        $failed_logins = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} 
                WHERE event_type = 'login_failed' 
                AND created_at >= %s",
                $date
            )
        );

        return [
            'total_events'   => (int) $total,
            'by_type'        => $by_type,
            'failed_logins'  => (int) $failed_logins,
            'period_days'    => $days,
        ];
    }

    /**
     * Create audit log table
     *
     * @return void
     */
    public function createTable(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type varchar(100) NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            message text NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(500) DEFAULT NULL,
            context longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Clean up old audit logs
     *
     * @param int $days Number of days to retain logs
     * @return int Number of rows deleted
     */
    public function cleanupOldLogs( int $days = 90 ): int {
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
}
