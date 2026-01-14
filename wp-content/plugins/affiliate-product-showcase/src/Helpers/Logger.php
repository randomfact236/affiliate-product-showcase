<?php
/**
 * Simple WordPress-compatible Logger
 *
 * Replaces Monolog with lightweight WordPress error_log based logging.
 * Suitable for WordPress plugins and VIP/Enterprise environments.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

declare( strict_types=1 );

namespace AffiliateProductShowcase\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger Class
 *
 * Provides simple logging functionality using WordPress error_log.
 * Compatible with WordPress VIP and Enterprise environments.
 *
 * @since 1.0.0
 */
class Logger {
    /**
     * Logger prefix for log entries
     *
     * @var string
     */
    private const PREFIX = '[APS]';

    /**
     * Log an error message
     *
     * @param string $message Error message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public static function error( string $message, array $context = [] ): void {
        self::log( 'ERROR', $message, $context );
    }

    /**
     * Log a warning message
     *
     * @param string $message Warning message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public static function warning( string $message, array $context = [] ): void {
        self::log( 'WARNING', $message, $context );
    }

    /**
     * Log an info message
     *
     * @param string $message Info message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public static function info( string $message, array $context = [] ): void {
        self::log( 'INFO', $message, $context );
    }

    /**
     * Log a debug message (only if WP_DEBUG is enabled)
     *
     * @param string $message Debug message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public static function debug( string $message, array $context = [] ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            self::log( 'DEBUG', $message, $context );
        }
    }

    /**
     * Internal log method
     *
     * @param string $level Log level (ERROR, WARNING, INFO, DEBUG)
     * @param string $message Message to log
     * @param array<string,mixed> $context Additional context
     * @return void
     */
    private static function log( string $level, string $message, array $context = [] ): void {
        $log_entry = sprintf(
            '%s %s: %s',
            self::PREFIX,
            $level,
            $message
        );

        if ( ! empty( $context ) ) {
            $log_entry .= ' | Context: ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        }

        // Use WordPress error_log
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log( $log_entry );

        // Hook for external logging services (Sentry, Bugsnag, New Relic, etc.)
        do_action(
            'affiliate_product_showcase_log',
            $level,
            $message,
            $context
        );
    }

    /**
     * Log an exception with stack trace
     *
     * @param \Throwable $exception Exception to log
     * @param string $message Optional additional message
     * @return void
     */
    public static function exception( \Throwable $exception, string $message = '' ): void {
        $log_message = $message ?: $exception->getMessage();

        $context = [
            'exception' => get_class( $exception ),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ];

        // Add stack trace in debug mode
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $context['stack_trace'] = $exception->getTraceAsString();
        }

        self::error( $log_message, $context );
    }

    /**
     * Log performance metrics
     *
     * @param string $operation Operation being measured
     * @param float $time Time taken in seconds
     * @param array<string,mixed> $context Additional context
     * @return void
     */
    public static function performance( string $operation, float $time, array $context = [] ): void {
        $context['time_seconds'] = $time;
        $context['time_formatted'] = number_format_i18n( $time * 1000, 2 ) . 'ms';

        self::info( sprintf( 'Performance: %s', $operation ), $context );
    }
}
