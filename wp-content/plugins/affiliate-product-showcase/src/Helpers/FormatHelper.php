<?php
/**
 * Format Helper Class
 *
 * Provides utility functions for formatting data
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 */

declare( strict_types = 1 );

namespace AffiliateProductShowcase\Helpers;

class FormatHelper {
	/**
	 * Format price with currency symbol
	 *
	 * @since 1.0.0
	 * @param float $price Price value
	 * @param string $currency Currency code (default: USD)
	 * @return string Formatted price
	 */
	public static function format_price( float $price, string $currency = 'USD' ): string {
		$symbols = [
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'CAD' => 'C$',
			'AUD' => 'A$',
		];

		$symbol = $symbols[ $currency ] ?? '$';
		return esc_html( $symbol . number_format( $price, 2 ) );
	}

	/**
	 * Format rating as stars
	 *
	 * @since 1.0.0
	 * @param float|null $rating Rating value (0-5)
	 * @return string HTML for star rating
	 */
	public static function format_rating( ?float $rating ): string {
		if ( null === $rating || $rating < 0 || $rating > 5 ) {
			return '';
		}

		$full_stars  = floor( $rating );
		$half_star   = ( $rating - $full_stars ) >= 0.5 ? 1 : 0;
		$empty_stars = 5 - $full_stars - $half_star;

		$output  = '<div class="aps-stars">';
		$output .= self::render_stars( 'full', (int) $full_stars );

		if ( $half_star ) {
			$output .= self::render_star( 'half' );
		}

		$output .= self::render_stars( 'empty', (int) $empty_stars );
		$output .= '</div>';

		return $output;
	}

	/**
	 * Calculate discount percentage
	 *
	 * @since 1.0.0
	 * @param float $original_price Original price
	 * @param float $sale_price Sale price
	 * @return string Discount percentage
	 */
	public static function calculate_discount( float $original_price, float $sale_price ): string {
		if ( $original_price <= 0 || $sale_price >= $original_price ) {
			return '';
		}

		$discount = ( ( $original_price - $sale_price ) / $original_price ) * 100;
		return number_format( $discount, 0 );
	}

	/**
	 * Format date
	 *
	 * @since 1.0.0
	 * @param string $date_date Date string
	 * @param string $format Date format
	 * @return string Formatted date
	 */
	public static function format_date( string $date_date, string $format = 'F j, Y' ): string {
		$timestamp = strtotime( $date_date );
		return date_i18n( $format, $timestamp );
	}

	/**
	 * Truncate text
	 *
	 * @since 1.0.0
	 * @param string $text Text to truncate
	 * @param int $length Maximum length
	 * @param string $suffix Suffix to add
	 * @return string Truncated text
	 */
	public static function truncate_text( string $text, int $length = 100, string $suffix = '...' ): string {
		if ( mb_strlen( $text ) <= $length ) {
			return $text;
		}

		return mb_substr( $text, 0, $length ) . $suffix;
	}

	/**
	 * Render multiple stars
	 *
	 * @since 1.0.0
	 * @param string $type Star type (full or empty)
	 * @param int $count Number of stars
	 * @return string HTML for stars
	 */
	private static function render_stars( string $type, int $count ): string {
		if ( $count <= 0 ) {
			return '';
		}

		$output = '';
		for ( $i = 0; $i < $count; $i++ ) {
			$output .= self::render_star( $type );
		}

		return $output;
	}

	/**
	 * Render single star
	 *
	 * @since 1.0.0
	 * @param string $type Star type (full, half, or empty)
	 * @return string HTML for star
	 */
	private static function render_star( string $type ): string {
		$classes = 'aps-star';

		if ( 'empty' === $type ) {
			$classes .= ' empty';
		}

		return sprintf(
			'<span class="%s" aria-hidden="true">★</span>',
			esc_attr( $classes )
		);
	}

	/**
	 * Sanitize and format product title
	 *
	 * @since 1.0.0
	 * @param string $title Product title
	 * @param int $max_length Maximum length
	 * @return string Formatted title
	 */
	public static function format_title( string $title, int $max_length = 60 ): string {
		$title = wp_strip_all_tags( $title );
		$title = trim( $title );

		if ( mb_strlen( $title ) > $max_length ) {
			$title = self::truncate_text( $title, $max_length, '...' );
		}

		return $title;
	}

	/**
	 * Format percentage
	 *
	 * @since 1.0.0
	 * @param float $value Percentage value
	 * @param int $decimals Decimal places
	 * @return string Formatted percentage
	 */
	public static function format_percentage( float $value, int $decimals = 0 ): string {
		return number_format( $value, $decimals ) . '%';
	}
}
