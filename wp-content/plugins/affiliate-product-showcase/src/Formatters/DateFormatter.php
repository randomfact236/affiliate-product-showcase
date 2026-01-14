<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Formatters;

/**
 * Date Formatter
 *
 * Provides consistent date formatting throughout the plugin.
 * Supports various date formats and time zones.
 *
 * @package AffiliateProductShowcase\Formatters
 * @since 1.0.0
 */
class DateFormatter {

    /**
     * Default date format
     *
     * @var string
     */
    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    /**
     * Date format constants
     *
     * @var array
     */
    public const FORMATS = [
        'datetime'    => 'Y-m-d H:i:s',
        'date'        => 'Y-m-d',
        'time'        => 'H:i:s',
        'readable'    => 'F j, Y',
        'readable_time' => 'g:i A',
        'full'        => 'F j, Y \a\t g:i A',
        'short'       => 'M j, Y',
        'iso8601'     => 'c',
        'rfc2822'     => 'r',
    ];

    /**
     * Format a date string or timestamp
     *
     * @param string|int|null $date Date to format (timestamp or string)
     * @param string $format Format to use
     * @param bool $gmt Whether to use GMT time
     * @return string Formatted date
     */
    public static function format( $date, string $format = self::DEFAULT_FORMAT, bool $gmt = false ): string {
        if ( empty( $date ) ) {
            return '';
        }

        $timestamp = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );

        if ( $timestamp === false ) {
            return '';
        }

        return $gmt 
            ? gmdate( $format, $timestamp )
            : date( $format, $timestamp );
    }

    /**
     * Format date for display
     *
     * @param string|int|null $date Date to format
     * @return string Formatted date
     */
    public static function display( $date ): string {
        return self::format( $date, self::FORMATS['readable'] );
    }

    /**
     * Format date and time for display
     *
     * @param string|int|null $date Date to format
     * @return string Formatted date and time
     */
    public static function displayDateTime( $date ): string {
        return self::format( $date, self::FORMATS['full'] );
    }

    /**
     * Format date for input fields
     *
     * @param string|int|null $date Date to format
     * @return string Formatted date
     */
    public static function input( $date ): string {
        return self::format( $date, self::FORMATS['date'] );
    }

    /**
     * Format date for database storage
     *
     * @param string|int|null $date Date to format
     * @return string Formatted date
     */
    public static function database( $date ): string {
        return self::format( $date, self::FORMATS['datetime'] );
    }

    /**
     * Get time ago string
     *
     * @param string|int|null $date Date to calculate from
     * @return string Time ago string (e.g., "2 hours ago")
     */
    public static function timeAgo( $date ): string {
        if ( empty( $date ) ) {
            return '';
        }

        $timestamp = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );

        if ( $timestamp === false ) {
            return '';
        }

        $diff = time() - $timestamp;

        if ( $diff < 60 ) {
            return 'just now';
        }

        $intervals = [
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
        ];

        foreach ( $intervals as $seconds => $label ) {
            $value = floor( $diff / $seconds );

            if ( $value >= 1 ) {
                return sprintf(
                    '%d %s%s ago',
                    $value,
                    $label,
                    $value > 1 ? 's' : ''
                );
            }
        }

        return 'just now';
    }

    /**
     * Format date range
     *
     * @param string|int|null $start_date Start date
     * @param string|int|null $end_date End date
     * @param string $format Format to use
     * @return string Formatted date range
     */
    public static function range( $start_date, $end_date, string $format = self::FORMATS['short'] ): string {
        $start = self::format( $start_date, $format );
        $end = self::format( $end_date, $format );

        if ( $start === $end ) {
            return $start;
        }

        return sprintf( '%s - %s', $start, $end );
    }

    /**
     * Get WordPress date format
     *
     * @return string
     */
    public static function getWordPressFormat(): string {
        return get_option( 'date_format' ) ?: self::FORMATS['readable'];
    }

    /**
     * Get WordPress time format
     *
     * @return string
     */
    public static function getWordPressTimeFormat(): string {
        return get_option( 'time_format' ) ?: self::FORMATS['readable_time'];
    }

    /**
     * Format date using WordPress settings
     *
     * @param string|int|null $date Date to format
     * @return string Formatted date
     */
    public static function wpFormat( $date ): string {
        $format = self::getWordPressFormat() . ' ' . self::getWordPressTimeFormat();
        return self::format( $date, trim( $format ) );
    }

    /**
     * Check if date is today
     *
     * @param string|int|null $date Date to check
     * @return bool
     */
    public static function isToday( $date ): bool {
        if ( empty( $date ) ) {
            return false;
        }

        $timestamp = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );

        if ( $timestamp === false ) {
            return false;
        }

        $today = date( 'Y-m-d' );
        $check_date = date( 'Y-m-d', $timestamp );

        return $today === $check_date;
    }

    /**
     * Check if date is in the future
     *
     * @param string|int|null $date Date to check
     * @return bool
     */
    public static function isFuture( $date ): bool {
        if ( empty( $date ) ) {
            return false;
        }

        $timestamp = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );

        if ( $timestamp === false ) {
            return false;
        }

        return $timestamp > time();
    }

    /**
     * Check if date is in the past
     *
     * @param string|int|null $date Date to check
     * @return bool
     */
    public static function isPast( $date ): bool {
        if ( empty( $date ) ) {
            return false;
        }

        $timestamp = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );

        if ( $timestamp === false ) {
            return false;
        }

        return $timestamp < time();
    }

    /**
     * Get current date in specified format
     *
     * @param string $format Format to use
     * @param bool $gmt Whether to use GMT time
     * @return string Current date
     */
    public static function now( string $format = self::DEFAULT_FORMAT, bool $gmt = false ): string {
        return $gmt 
            ? gmdate( $format )
            : date( $format );
    }

    /**
     * Add time to a date
     *
     * @param string|int|null $date Date to modify
     * @param string $interval Interval string (e.g., "+1 day", "-2 weeks")
     * @return string Modified date in default format
     */
    public static function add( $date, string $interval ): string {
        if ( empty( $date ) ) {
            return '';
        }

        $timestamp = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );

        if ( $timestamp === false ) {
            return '';
        }

        $new_timestamp = strtotime( $interval, $timestamp );

        if ( $new_timestamp === false ) {
            return '';
        }

        return date( self::DEFAULT_FORMAT, $new_timestamp );
    }

    /**
     * Get available formats
     *
     * @return array
     */
    public static function getFormats(): array {
        return self::FORMATS;
    }

    /**
     * Format date using a named format
     *
     * @param string|int|null $date Date to format
     * @param string $format_name Named format key
     * @return string Formatted date
     */
    public static function namedFormat( $date, string $format_name ): string {
        $format = self::FORMATS[ $format_name ] ?? self::DEFAULT_FORMAT;
        return self::format( $date, $format );
    }
}
