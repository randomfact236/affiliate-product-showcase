<?php
/**
 * Simple WordPress-compatible Logger
 *
 * Replaces Monolog with lightweight WordPress error_log based logging.
 * Suitable for WordPress plugins and VIP/Enterprise environments.
 * Fully PSR-3 compliant for enterprise compatibility.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

declare( strict_types=1 );

namespace AffiliateProductShowcase\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

/**
 * Logger Class
 *
 * Provides simple logging functionality using WordPress error_log.
 * Compatible with WordPress VIP and Enterprise environments.
 * Fully PSR-3 compliant for enterprise integration.
 *
 * @since 1.0.0
 * @implements LoggerInterface
 */
class Logger implements LoggerInterface {
    /**
     * Logger prefix for log entries
     *
     * @var string
     */
    private const PREFIX = '[APS]';

    /**
     * PSR-3: Log an emergency message
     *
     * @param string|\Stringable $message Emergency message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function emergency( string|\Stringable $message, array $context = [] ): void {
        $this->log( LogLevel::EMERGENCY, $this->normalizeMessage( $message ), $context );
    }

    /**
     * PSR-3: Log an alert message
     *
     * @param string|\Stringable $message Alert message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function alert( string|\Stringable $message, array $context = [] ): void {
        $this->log( LogLevel::ALERT, $this->normalizeMessage( $message ), $context );
    }

    /**
     * PSR-3: Log a critical message
     *
     * @param string|\Stringable $message Critical message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function critical( string|\Stringable $message, array $context = [] ): void {
        $this->log( LogLevel::CRITICAL, $this->normalizeMessage( $message ), $context );
    }

    /**
     * PSR-3: Log an error message
     *
     * @param string|\Stringable $message Error message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function error( string|\Stringable $message, array $context = [] ): void {
        $this->log( LogLevel::ERROR, $this->normalizeMessage( $message ), $context );
    }

    /**
     * PSR-3: Log a warning message
     *
     * @param string|\Stringable $message Warning message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function warning( string|\Stringable $message, array $context = [] ): void {
        $this->log( LogLevel::WARNING, $this->normalizeMessage( $message ), $context );
    }

    /**
     * PSR-3: Log a notice message
     *
     * @param string|\Stringable $message Notice message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function notice( string|\Stringable $message, array $context = [] ): void {
        $this->log( LogLevel::NOTICE, $this->normalizeMessage( $message ), $context );
    }

    /**
     * PSR-3: Log an info message
     *
     * @param string|\Stringable $message Info message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function info( string|\Stringable $message, array $context = [] ): void {
        $this->log( LogLevel::INFO, $this->normalizeMessage( $message ), $context );
    }

    /**
     * PSR-3: Log a debug message (only if WP_DEBUG is enabled)
     *
     * @param string|\Stringable $message Debug message to log
     * @param array<string,mixed> $context Additional context data
     * @return void
     */
    public function debug( string|\Stringable $message, array $context = [] ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $this->log( LogLevel::DEBUG, $this->normalizeMessage( $message ), $context );
        }
    }

    /**
     * PSR-3: Log with arbitrary level
     *
     * @param mixed $level Log level
     * @param string|\Stringable $message Message to log
     * @param array<string,mixed> $context Additional context
     * @return void
     * @throws InvalidArgumentException If log level is invalid
     */
    public function log( $level, string|\Stringable $message, array $context = [] ): void {
        $validLevels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];

        if ( ! in_array( $level, $validLevels, true ) ) {
            throw new InvalidArgumentException( sprintf( 'Invalid log level: %s', $level ) );
        }

        $logEntry = sprintf(
            '%s %s: %s',
            self::PREFIX,
            strtoupper( $level ),
            $this->normalizeMessage( $message )
        );

        if ( ! empty( $context ) ) {
            $logEntry .= ' | Context: ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        }

        // Use WordPress error_log
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log( $logEntry );

        // Hook for external logging services (Sentry, Bugsnag, New Relic, etc.)
        do_action(
            'affiliate_product_showcase_log',
            strtoupper( $level ),
            (string) $message,
            $context
        );
    }

    /**
     * Normalize message to string
     *
     * @param string|\Stringable $message Message to normalize
     * @return string Normalized message
     */
    private function normalizeMessage( string|\Stringable $message ): string {
        if ( $message instanceof \Stringable ) {
            return (string) $message;
        }
        return $message;
    }

    /**
     * Log an exception with stack trace (convenience method)
     *
     * @param \Throwable $exception Exception to log
     * @param string $message Optional additional message
     * @return void
     */
    public function exception( \Throwable $exception, string $message = '' ): void {
        $logMessage = $message ?: $exception->getMessage();

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

        $this->error( $logMessage, $context );
    }

    /**
     * Log performance metrics (convenience method)
     *
     * @param string $operation Operation being measured
     * @param float $time Time taken in seconds
     * @param array<string,mixed> $context Additional context
     * @return void
     */
    public function performance( string $operation, float $time, array $context = [] ): void {
        $context['time_seconds'] = $time;
        $context['time_formatted'] = number_format_i18n( $time * 1000, 2 ) . 'ms';

        $this->info( sprintf( 'Performance: %s', $operation ), $context );
    }
}
