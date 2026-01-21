<?php
/**
 * Product Card Renderer
 *
 * Provides shared product card rendering logic for shortcodes and widgets.
 * Eliminates code duplication across display components.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Product Card Renderer
 *
 * Handles consistent product card rendering across shortcodes and widgets.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class ProductCardRenderer {
	/**
	 * Render product card
	 *
	 * Generates HTML for a single product card with configurable display options.
	 * Supports images, features, ratings, prices, and CTA buttons.
	 *
	 * @param \WP_Post $product Post object
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	public function render( \WP_Post $product, array $atts ): string {
		$product_id = $product->ID;
		$title = get_the_title( $product );
		$price = get_post_meta( $product_id, 'product_price', true );
		$sale_price = get_post_meta( $product_id, 'product_sale_price', true );
		$rating = get_post_meta( $product_id, 'product_rating', true );
		$features = get_post_meta( $product_id, 'product_features', true );
		$affiliate_url = get_post_meta( $product_id, 'product_affiliate_url', true );

		// Format price
		$price_html = $this->format_price( $price, $sale_price );

		ob_start();

		if ( $atts['show_image'] && has_post_thumbnail( $product ) ) {
			echo '<div class="aps-product-image">';
			echo '<a href="' . esc_url( $affiliate_url ) . '" rel="nofollow sponsored" target="_blank">';
			the_post_thumbnail( $product, 'medium', [ 'loading' => 'lazy' ] );
			echo '</a>';
			echo '</div>';
		}

		echo '<div class="aps-product-content">';

		if ( $atts['show_features'] && ! empty( $features ) ) {
			$features_array = is_array( $features ) ? $features : [ $features ];
			echo '<ul class="aps-product-features">';
			foreach ( array_slice( $features_array, 0, 5 ) as $index => $feature ) {
				echo '<li class="aps-feature-item">' . esc_html( $feature ) . '</li>';
			}
			echo '</ul>';
		}

		echo '<h3 class="aps-product-title"><a href="' . esc_url( $affiliate_url ) . '" rel="nofollow sponsored" target="_blank">' . esc_html( $title ) . '</a></h3>';

		if ( $atts['show_rating'] && $rating ) {
			echo '<div class="aps-product-rating">';
			echo '<span class="aps-stars" style="--rating: ' . esc_attr( $rating ) . ';">★★★★★</span>';
			echo '<span class="aps-rating-value">' . number_format( $rating, 1 ) . '</span>';
			echo '</div>';
		}

		if ( $atts['show_price'] ) {
			echo '<div class="aps-product-price">' . $price_html . '</div>';
		}

		if ( $atts['show_cta'] && $affiliate_url ) {
			echo '<div class="aps-product-cta">';
			$cta_text = $atts['cta_text'] ?? __( 'View Deal', Constants::TEXTDOMAIN );
			echo '<a href="' . esc_url( $affiliate_url ) . '" class="aps-cta-button" rel="nofollow sponsored" target="_blank">' . esc_html( $cta_text ) . '</a>';
			echo '</div>';
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Format product price
	 *
	 * Formats price display with sale price support.
	 *
	 * @param string $price Regular price
	 * @param string $sale_price Sale price (optional)
	 * @return string Formatted HTML
	 * @since 1.0.0
	 */
	private function format_price( string $price, string $sale_price ): string {
		if ( $sale_price && floatval( $sale_price ) > 0 ) {
			$html = '<span class="aps-price-original">' . number_format( floatval( $price ), 2 ) . '</span> ';
			$html .= '<span class="aps-price-sale">' . number_format( floatval( $sale_price ), 2 ) . '</span>';
			return $html;
		}

		return '<span class="aps-price-current">' . number_format( floatval( $price ), 2 ) . '</span>';
	}

	/**
	 * Get default attributes
	 *
	 * Returns default display attributes for product cards.
	 *
	 * @return array<string, mixed> Default attributes
	 * @since 1.0.0
	 */
	public function get_defaults(): array {
		return [
			'show_image' => true,
			'show_price' => true,
			'show_rating' => true,
			'show_features' => true,
			'show_cta' => true,
			'cta_text' => __( 'View Deal', Constants::TEXTDOMAIN ),
		];
	}

	/**
	 * Sanitize display attributes
	 *
	 * Validates and sanitizes product card display attributes.
	 *
	 * @param array<string, mixed> $atts Raw attributes
	 * @return array<string, mixed> Sanitized attributes
	 * @since 1.0.0
	 */
	public function sanitize_attributes( array $atts ): array {
		return [
			'show_image' => isset( $atts['show_image'] ) ? rest_sanitize_boolean( $atts['show_image'] ) : true,
			'show_price' => isset( $atts['show_price'] ) ? rest_sanitize_boolean( $atts['show_price'] ) : true,
			'show_rating' => isset( $atts['show_rating'] ) ? rest_sanitize_boolean( $atts['show_rating'] ) : true,
			'show_features' => isset( $atts['show_features'] ) ? rest_sanitize_boolean( $atts['show_features'] ) : true,
			'show_cta' => isset( $atts['show_cta'] ) ? rest_sanitize_boolean( $atts['show_cta'] ) : true,
			'cta_text' => isset( $atts['cta_text'] ) ? sanitize_text_field( $atts['cta_text'] ) : __( 'View Deal', Constants::TEXTDOMAIN ),
		];
	}
}
