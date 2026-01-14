<?php

namespace AffiliateProductShowcase\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Models\Product;

final class ProductFactory {
	/**
	 * Create a Product from a WP_Post object
	 *
	 * @param \WP_Post $post WordPress post object
	 * @param array<string, array<string, mixed>>|null $meta_cache Optional pre-fetched meta data to avoid N+1 queries
	 * @return Product Product instance
	 */
	public function from_post( \WP_Post $post, ?array $meta_cache = null ): Product {
		// Use provided cache if available (for batch operations), otherwise fetch
		$meta = $meta_cache ?? get_post_meta( $post->ID );

		return new Product(
			$post->ID,
			$post->post_title,
			$post->post_name,
			wp_kses_post( $post->post_content ),
			$meta['aps_currency'][0] ?? 'USD',
			(float) ( $meta['aps_price'][0] ?? 0 ),
			esc_url_raw( $meta['aps_affiliate_url'][0] ?? '' ),
			esc_url_raw( $meta['aps_image_url'][0] ?? '' ) ?: null,
			isset( $meta['aps_rating'][0] ) ? (float) $meta['aps_rating'][0] : null,
			sanitize_text_field( $meta['aps_badge'][0] ?? '' ) ?: null,
			array_map( 'sanitize_text_field', $meta['aps_categories'] ?? [] )
		);
	}

	/**
	 * Create a Product from an array
	 *
	 * @param array<string, mixed> $data Product data
	 * @return Product Product instance
	 */
	public function from_array( array $data ): Product {
		return new Product(
			(int) ( $data['id'] ?? 0 ),
			sanitize_text_field( $data['title'] ?? '' ),
			sanitize_title( $data['slug'] ?? ( $data['title'] ?? '' ) ),
			wp_kses_post( $data['description'] ?? '' ),
			sanitize_text_field( $data['currency'] ?? 'USD' ),
			(float) ( $data['price'] ?? 0 ),
			esc_url_raw( $data['affiliate_url'] ?? '' ),
			esc_url_raw( $data['image_url'] ?? '' ) ?: null,
			isset( $data['rating'] ) ? (float) $data['rating'] : null,
			sanitize_text_field( $data['badge'] ?? '' ) ?: null,
			array_map( 'sanitize_text_field', $data['categories'] ?? [] )
		);
	}
}
