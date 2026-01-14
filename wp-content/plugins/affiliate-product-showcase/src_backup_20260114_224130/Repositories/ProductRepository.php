<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Abstracts\AbstractRepository;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Exceptions\RepositoryException;
use WP_Error;

final class ProductRepository extends AbstractRepository {
	private ProductFactory $factory;

	public function __construct() {
		$this->factory = new ProductFactory();
	}

	/**
	 * Find a product by ID
	 *
	 * @param int $id Product ID
	 * @return Product|null
	 * @throws RepositoryException If ID is invalid
	 */
	public function find( int $id ): ?Product {
		if ( $id <= 0 ) {
			throw RepositoryException::validationError('id', 'ID must be a positive integer');
		}

		// Check cache first
		$cache_key = 'aps_product_' . $id;
		$cached_product = wp_cache_get( $cache_key, 'aps_products' );
		
		if ( false !== $cached_product ) {
			return $cached_product;
		}

		$post = get_post( $id );
		
		if ( ! $post || is_wp_error( $post ) ) {
			return null;
		}

		if ( Constants::CPT_PRODUCT !== $post->post_type ) {
			return null;
		}

		try {
			$product = $this->factory->from_post( $post );
			// Cache for 1 hour
			wp_cache_set( $cache_key, $product, 'aps_products', HOUR_IN_SECONDS );
			return $product;
		} catch ( \Exception $e ) {
			throw RepositoryException::queryError('Product', $e->getMessage(), 0, $e);
		}
	}

	/**
	 * List products with optional filtering
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array<int, Product>
	 * @throws RepositoryException If query fails
	 */
	public function list( array $args = [] ): array {
		$query_args = wp_parse_args(
			$args,
			[
				'post_type'      => Constants::CPT_PRODUCT,
				'post_status'    => 'publish',
				'posts_per_page' => $args['per_page'] ?? 20,
				'orderby'        => $args['orderby'] ?? 'date',
				'order'          => $args['order'] ?? 'DESC',
			]
		);

		// Validate posts_per_page
		if ( isset( $query_args['posts_per_page'] ) && $query_args['posts_per_page'] < -1 ) {
			throw RepositoryException::validationError('posts_per_page', 'Must be -1 or a positive integer');
		}

		// Create cache key from query args
		$cache_key = 'aps_product_list_' . md5( serialize( $query_args ) );
		
		// Check cache first
		$cached_items = wp_cache_get( $cache_key, 'aps_products' );
		
		if ( false !== $cached_items ) {
			return $cached_items;
		}

		try {
			$query = new \WP_Query( $query_args );
		} catch ( \Exception $e ) {
			throw RepositoryException::queryError('Product', $e->getMessage(), 0, $e);
		}

		// OPTIMIZATION: Fetch all meta data at once to prevent N+1 queries
		// Instead of calling get_post_meta() for each post individually,
		// we fetch all meta data in a single query using get_post_meta()
		// with the third parameter set to false (returns all meta for all posts)
		$post_ids = wp_list_pluck( $query->posts, 'ID' );
		$all_meta = [];
		
		if ( ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$all_meta[ $post_id ] = get_post_meta( $post_id );
			}
		}

		$items = [];
		foreach ( $query->posts as $post ) {
			try {
				// Pass pre-fetched meta to factory to avoid additional queries
				$items[] = $this->factory->from_post( $post, $all_meta[ $post->ID ] ?? [] );
			} catch ( \Exception $e ) {
				// Log error but continue with other posts
				error_log(sprintf(
					'ProductRepository: Failed to create product from post %d: %s',
					$post->ID,
					$e->getMessage()
				));
			}
		}
		
		// Cache for 5 minutes (shorter for lists as they change more frequently)
		wp_cache_set( $cache_key, $items, 'aps_products', 5 * MINUTE_IN_SECONDS );

		return $items;
	}

	/**
	 * Save a product
	 *
	 * @param object $model Product model
	 * @return int Product ID
	 * @throws RepositoryException If save fails
	 */
	public function save( object $model ): int {
		if ( ! $model instanceof Product ) {
			throw RepositoryException::invalidModelType(Product::class, get_class($model));
		}

		// Validate required fields
		$this->validateProduct( $model );

		$post_id = $model->id > 0 ? $model->id : 0;
		$postarr = [
			'ID'           => $post_id,
			'post_title'   => $model->title,
			'post_name'    => $model->slug ?? sanitize_title( $model->title ),
			'post_content' => $model->description ?? '',
			'post_type'    => Constants::CPT_PRODUCT,
			'post_status'  => 'publish',
		];

		$stored_id = wp_insert_post( $postarr, true );
		
		if ( is_wp_error( $stored_id ) ) {
			$error_message = $stored_id instanceof WP_Error 
				? implode( ', ', $stored_id->get_error_messages() ) 
				: 'Unknown error';
			throw RepositoryException::saveFailed('Product', $error_message);
		}

		$stored_id = (int) $stored_id;
		
		// Save meta data
		$this->saveMeta( $stored_id, $model );

		return $stored_id;
	}

	/**
	 * Validate product data before saving
	 *
	 * @param Product $product Product to validate
	 * @return void
	 * @throws RepositoryException If validation fails
	 */
	private function validateProduct( Product $product ): void {
		if ( empty( $product->title ) ) {
			throw RepositoryException::validationError('title', 'Title is required');
		}

		if ( empty( $product->affiliate_url ) ) {
			throw RepositoryException::validationError('affiliate_url', 'Affiliate URL is required');
		}

		if ( ! filter_var( $product->affiliate_url, FILTER_VALIDATE_URL ) ) {
			throw RepositoryException::validationError('affiliate_url', 'Must be a valid URL');
		}

		if ( null !== $product->price && $product->price < 0 ) {
			throw RepositoryException::validationError('price', 'Cannot be negative');
		}
	}

	/**
	 * Save product meta data
	 *
	 * @param int $post_id Post ID
	 * @param Product $product Product to save
	 * @return void
	 * @throws RepositoryException If meta save fails
	 */
	private function saveMeta( int $post_id, Product $product ): void {
		$meta_fields = [
			'aps_price'        => $product->price,
			'aps_currency'     => $product->currency,
			'aps_affiliate_url' => $product->affiliate_url,
			'aps_image_url'    => $product->image_url,
			'aps_rating'       => $product->rating,
			'aps_badge'        => $product->badge,
			'aps_categories'    => $product->categories,
		];

		foreach ( $meta_fields as $key => $value ) {
			// Only update if value is actually changed
			$current = get_post_meta( $post_id, $key, true );
			
			if ($value !== $current) {
				$result = update_post_meta( $post_id, $key, $value );
				
				// update_post_meta returns false on FAILURE, not when value === false
				// It returns the old value on success (which might be false)
				if ($result === false && !in_array($value, [false, '', null], true)) {
					throw RepositoryException::saveFailed(
						'Product Meta',
						sprintf('Failed to update meta field "%s"', $key)
					);
				}
			}
		}
	}

	/**
	 * Delete a product
	 *
	 * @param int $id Product ID
	 * @return bool True if deleted
	 * @throws RepositoryException If delete fails
	 */
	public function delete( int $id ): bool {
		if ( $id <= 0 ) {
			throw RepositoryException::validationError('id', 'ID must be a positive integer');
		}

		// Check if product exists and is correct type
		$product = $this->find( $id );
		if ( ! $product ) {
			throw RepositoryException::notFound('Product', $id);
		}

		$deleted = wp_delete_post( $id, true );
		
		if ( false === $deleted ) {
			throw RepositoryException::deleteFailed('Product', 'wp_delete_post returned false');
		}
		
		// Clear product cache
		wp_cache_delete( 'aps_product_' . $id, 'aps_products' );
		// Clear all product list caches
		wp_cache_flush_group( 'aps_products' );

		return true;
	}
}
