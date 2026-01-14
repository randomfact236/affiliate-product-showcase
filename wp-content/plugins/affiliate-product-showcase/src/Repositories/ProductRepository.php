<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Abstracts\AbstractRepository;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Plugin\Constants;

final class ProductRepository extends AbstractRepository {
	private ProductFactory $factory;

	public function __construct() {
		$this->factory = new ProductFactory();
	}

	public function find( int $id ): ?Product {
		$post = get_post( $id );
		if ( ! $post || Constants::CPT_PRODUCT !== $post->post_type ) {
			return null;
		}

		return $this->factory->from_post( $post );
	}

	public function list( array $args = [] ): array {
		$query = new \WP_Query(
			wp_parse_args(
				$args,
				[
					'post_type'      => Constants::CPT_PRODUCT,
					'post_status'    => 'publish',
					'posts_per_page' => $args['per_page'] ?? 20,
					'orderby'        => $args['orderby'] ?? 'date',
					'order'          => $args['order'] ?? 'DESC',
				]
			)
		);

		if ( empty( $query->posts ) ) {
			return [];
		}

		// Batch fetch all meta data for all posts at once
		$post_ids = wp_list_pluck( $query->posts, 'ID' );
		$meta_values = $this->batch_fetch_meta( $post_ids );

		$items = [];
		foreach ( $query->posts as $post ) {
			$product = $this->factory->from_post( $post );
			if ( $product && isset( $meta_values[ $post->ID ] ) ) {
				$this->populate_product_meta( $product, $meta_values[ $post->ID ] );
			}
			$items[] = $product;
		}

		return $items;
	}

	public function save( object $model ): int {
		if ( ! $model instanceof Product ) {
			return 0;
		}

		$post_id = $model->id > 0 ? $model->id : 0;
		$postarr = [
			'ID'           => $post_id,
			'post_title'   => $model->title,
			'post_name'    => $model->slug,
			'post_content' => $model->description,
			'post_type'    => Constants::CPT_PRODUCT,
			'post_status'  => 'publish',
		];

		$stored_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $stored_id ) ) {
			return 0;
		}

		$stored_id = (int) $stored_id;
		update_post_meta( $stored_id, 'aps_price', $model->price );
		update_post_meta( $stored_id, 'aps_currency', $model->currency );
		update_post_meta( $stored_id, 'aps_affiliate_url', $model->affiliate_url );
		update_post_meta( $stored_id, 'aps_image_url', $model->image_url );
		update_post_meta( $stored_id, 'aps_rating', $model->rating );
		update_post_meta( $stored_id, 'aps_badge', $model->badge );
		update_post_meta( $stored_id, 'aps_categories', $model->categories );

		return $stored_id;
	}

	public function delete( int $id ): bool {
		$deleted = wp_delete_post( $id, true );
		return (bool) $deleted;
	}

	/**
	 * Batch fetch meta data for multiple posts.
	 * 
	 * Fetches all product-related meta data in a single query
	 * to avoid N+1 query performance issues.
	 *
	 * @param int[] $post_ids Array of post IDs.
	 * @return array<int, array<string, mixed>> Meta data indexed by post ID.
	 */
	private function batch_fetch_meta( array $post_ids ): array {
		global $wpdb;

		if ( empty( $post_ids ) ) {
			return [];
		}

		$ids_placeholder = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
		$meta_keys = ['aps_price', 'aps_currency', 'aps_affiliate_url', 'aps_image_url', 'aps_rating', 'aps_badge', 'aps_categories'];

		$query = $wpdb->prepare(
			"SELECT post_id, meta_key, meta_value 
			 FROM {$wpdb->postmeta} 
			 WHERE post_id IN ({$ids_placeholder}) 
			 AND meta_key IN ('aps_price', 'aps_currency', 'aps_affiliate_url', 'aps_image_url', 'aps_rating', 'aps_badge', 'aps_categories')",
			...$post_ids
		);

		$results = $wpdb->get_results( $query );
		if ( ! $results ) {
			return [];
		}

		$meta_values = [];
		foreach ( $results as $row ) {
			$post_id = (int) $row->post_id;
			if ( ! isset( $meta_values[ $post_id ] ) ) {
				$meta_values[ $post_id ] = [];
			}
			$meta_values[ $post_id ][ $row->meta_key ] = maybe_unserialize( $row->meta_value );
		}

		return $meta_values;
	}

	/**
	 * Populate product with batch-fetched meta data.
	 *
	 * @param Product $product Product instance.
	 * @param array<string, mixed> $meta Meta data array.
	 * @return void
	 */
	private function populate_product_meta( Product $product, array $meta ): void {
		$product->price = $meta['aps_price'] ?? 0;
		$product->currency = $meta['aps_currency'] ?? 'USD';
		$product->affiliate_url = $meta['aps_affiliate_url'] ?? '';
		$product->image_url = $meta['aps_image_url'] ?? '';
		$product->rating = $meta['aps_rating'] ? (float) $meta['aps_rating'] : 0;
		$product->badge = $meta['aps_badge'] ?? '';
		$product->categories = $meta['aps_categories'] ?? [];
	}
}
