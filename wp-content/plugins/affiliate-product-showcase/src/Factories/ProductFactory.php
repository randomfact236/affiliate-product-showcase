<?php
/**
 * Product Factory
 *
 * Factory for creating Product objects from:
 * - WordPress post objects
 * - Associative arrays
 * - Supports N+1 query prevention with meta cache
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Models\Product;

/**
 * Product Factory
 *
 * Factory for creating Product objects from:
 * - WordPress post objects
 * - Associative arrays
 * - Supports N+1 query prevention with meta cache
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 * @author Development Team
 */
final class ProductFactory {
	/**
	 * Create a Product from a WP_Post object
	 *
	 * Creates a Product instance from WordPress post object.
	 * Supports optional meta cache to prevent N+1 queries.
	 *
	 * @param \WP_Post $post WordPress post object
	 * @param array<string, array<string, mixed>>|null $meta_cache Optional pre-fetched meta data to avoid N+1 queries
	 * @return Product Product instance
	 * @since 1.0.0
	 */
	public function from_post( \WP_Post $post, ?array $meta_cache = null ): Product {
		// Use provided cache if available (for batch operations), otherwise fetch
		$meta = $meta_cache ?? get_post_meta( $post->ID );

		// Get category taxonomy terms
		$category_terms = wp_get_object_terms( $post->ID, \AffiliateProductShowcase\Plugin\Constants::TAX_CATEGORY, [ 'fields' => 'ids' ] );
		$category_ids = ! is_wp_error( $category_terms ) ? array_map( 'intval', $category_terms ) : [];

		// Get tag taxonomy terms
		$tag_terms = wp_get_object_terms( $post->ID, \AffiliateProductShowcase\Plugin\Constants::TAX_TAG, [ 'fields' => 'ids' ] );
		$tag_ids = ! is_wp_error( $tag_terms ) ? array_map( 'intval', $tag_terms ) : [];

		// Get ribbon taxonomy terms
		$ribbon_terms = wp_get_object_terms( $post->ID, \AffiliateProductShowcase\Plugin\Constants::TAX_RIBBON, [ 'fields' => 'ids' ] );
		$ribbon_ids = ! is_wp_error( $ribbon_terms ) ? array_map( 'intval', $ribbon_terms ) : [];

		// Get featured status
		$featured_meta = $meta['aps_featured'][0] ?? '';
		$is_featured = ! empty( $featured_meta ) && $featured_meta === '1';

		return new Product(
			$post->ID,
			$post->post_title,
			$post->post_name,
			wp_kses_post( $post->post_content ),
			wp_kses_post( $post->post_excerpt ?? '' ),
			$meta['aps_currency'][0] ?? 'USD',
			(float) ( $meta['aps_price'][0] ?? 0 ),
			isset( $meta['aps_original_price'][0] ) ? (float) $meta['aps_original_price'][0] : null,
			isset( $meta['aps_discount_percentage'][0] ) ? (float) $meta['aps_discount_percentage'][0] : null,
			esc_url_raw( $meta['aps_affiliate_url'][0] ?? '' ),
			esc_url_raw( $meta['aps_image_url'][0] ?? '' ) ?: null,
			isset( $meta['aps_rating'][0] ) ? (float) $meta['aps_rating'][0] : null,
			sanitize_text_field( $meta['aps_badge'][0] ?? '' ) ?: null,
			$is_featured,
			$post->post_status,
			$category_ids,
			$tag_ids,
			$ribbon_ids,
			sanitize_text_field( $meta['aps_platform_requirements'][0] ?? '' ) ?: null,
			sanitize_text_field( $meta['aps_version_number'][0] ?? '' ) ?: null
		);
	}

	/**
	 * Create a Product from an array
	 *
	 * Creates a Product instance from associative array.
	 * Supports backward compatibility with legacy field names.
	 *
	 * @param array<string, mixed> $data Product data
	 * @return Product Product instance
	 * @since 1.0.0
	 */
	public function from_array( array $data ): Product {
		// Support both 'category_ids' and 'categories' for backward compatibility
		$category_ids = $data['category_ids'] ?? $data['categories'] ?? [];
		if ( ! empty( $category_ids ) ) {
			$category_ids = array_map( 'intval', (array) $category_ids );
		}

		// Support both 'tag_ids' and 'tags' for backward compatibility
		$tag_ids = $data['tag_ids'] ?? $data['tags'] ?? [];
		if ( ! empty( $tag_ids ) ) {
			$tag_ids = array_map( 'intval', (array) $tag_ids );
		}

		// Support both 'ribbon_ids' and 'ribbons' for backward compatibility
		$ribbon_ids = $data['ribbon_ids'] ?? $data['ribbons'] ?? [];
		if ( ! empty( $ribbon_ids ) ) {
			$ribbon_ids = array_map( 'intval', (array) $ribbon_ids );
		}

		// Get featured status with proper type conversion
		$is_featured = isset( $data['featured'] ) ? (bool) $data['featured'] : false;

		return new Product(
			(int) ( $data['id'] ?? 0 ),
			sanitize_text_field( $data['title'] ?? '' ),
			sanitize_title( $data['slug'] ?? ( $data['title'] ?? '' ) ),
			wp_kses_post( $data['description'] ?? '' ),
			sanitize_textarea_field( $data['short_description'] ?? '' ),
			sanitize_text_field( $data['currency'] ?? 'USD' ),
			(float) ( $data['price'] ?? 0 ),
			isset( $data['original_price'] ) ? (float) $data['original_price'] : null,
			isset( $data['discount_percentage'] ) ? (float) $data['discount_percentage'] : null,
			esc_url_raw( $data['affiliate_url'] ?? '' ),
			esc_url_raw( $data['image_url'] ?? '' ) ?: null,
			isset( $data['rating'] ) ? (float) $data['rating'] : null,
			sanitize_text_field( $data['badge'] ?? '' ) ?: null,
			$is_featured,
			sanitize_text_field( $data['status'] ?? 'publish' ),
			$category_ids,
			$tag_ids,
			$ribbon_ids,
			sanitize_text_field( $data['platform_requirements'] ?? '' ) ?: null,
			sanitize_text_field( $data['version_number'] ?? '' ) ?: null
		);
	}
}
