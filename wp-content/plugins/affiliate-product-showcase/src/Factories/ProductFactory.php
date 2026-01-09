<?php

namespace AffiliateProductShowcase\Factories;

use AffiliateProductShowcase\Models\Product;

final class ProductFactory {
	public function from_post( \WP_Post $post ): Product {
		$meta = get_post_meta( $post->ID );

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
