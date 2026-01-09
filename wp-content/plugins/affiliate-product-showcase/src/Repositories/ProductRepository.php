<?php

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

		$items = [];
		foreach ( $query->posts as $post ) {
			$items[] = $this->factory->from_post( $post );
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
}
