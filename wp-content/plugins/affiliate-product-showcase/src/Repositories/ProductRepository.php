<?php
/**
 * Product Repository
 *
 * Database repository for managing affiliate products including:
 * - Product CRUD operations (Create, Read, Update, Delete)
 * - Query execution with caching and N+1 prevention
 * - Meta data management
 * - Taxonomy term management
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 */

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

/**
 * Product Repository
 *
 * Database repository for managing affiliate products including:
 * - Product CRUD operations (Create, Read, Update, Delete)
 * - Query execution with caching and N+1 prevention
 * - Meta data management
 * - Taxonomy term management
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 * @author Development Team
 */
final class ProductRepository extends AbstractRepository {
	/**
	 * Product factory instance
	 *
	 * Creates Product objects from post data and arrays.
	 *
	 * @var ProductFactory
	 * @since 1.0.0
	 */
	private ProductFactory $factory;

	/**
	 * Constructor
	 *
	 * Initializes the repository with required dependencies.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->factory = new ProductFactory();
	}

	/**
	 * Get published products (alias for list method)
	 *
	 * Convenience method to retrieve only published products.
	 *
	 * @param int $per_page Number of products to retrieve (default: 10)
	 * @return array<int, Product> Array of published products
	 * @since 1.0.0
	 */
	public function get_published_products( int $per_page = 10 ): array {
		return $this->list( [
			'per_page'   => $per_page,
			'post_status' => 'publish',
		] );
	}

	/**
	 * Find a product by ID
	 *
	 * Retrieves a single product by its unique identifier.
	 * Uses caching to improve performance.
	 *
	 * @param int $id Unique product identifier
	 * @return Product|null Product object or null if not found
	 * @throws RepositoryException If ID is invalid
	 * @since 1.0.0
	 */
	public function find( int $id ): ?Product {
		if ( $id <= 0 ) {
			throw RepositoryException::validationError('id', __( 'ID must be a positive integer', 'affiliate-product-showcase' ));
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
			throw RepositoryException::queryError(__( 'Product', 'affiliate-product-showcase' ), $e->getMessage(), 0, $e);
		}
	}

	/**
	 * List products with optional filtering
	 *
	 * Retrieves products with optional filtering, sorting, and pagination.
	 * Implements caching and N+1 query prevention.
	 *
	 * @param array<string, mixed> $args Query arguments for filtering products
	 * @return array<int, Product> Array of product objects
	 * @throws RepositoryException If query fails
	 * @since 1.0.0
	 */
	public function list( array $args = [] ): array {
		$query_args = $this->prepareQueryArgs( $args );
		
		// Check cache first
		$cache_key = $this->generateCacheKey( $query_args );
		$cached_items = $this->getCachedItems( $cache_key );
		
		if ( false !== $cached_items ) {
			return $cached_items;
		}
		
		// Execute query and process results
		$query = $this->executeQuery( $query_args );
		$items = $this->processQueryResults( $query );
		
		// Cache results
		$this->cacheItems( $cache_key, $items );
		
		return $items;
	}

	/**
	 * Prepare and validate query arguments
	 *
	 * Merges provided arguments with defaults and validates.
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array<string, mixed> Prepared query arguments
	 * @throws RepositoryException If validation fails
	 * @since 1.0.0
	 */
	private function prepareQueryArgs( array $args ): array {
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

		$this->validateQueryArgs( $query_args );
		
		return $query_args;
	}

	/**
	 * Validate query arguments
	 *
	 * Ensures query arguments are valid before execution.
	 *
	 * @param array<string, mixed> $args Query arguments to validate
	 * @throws RepositoryException If validation fails
	 * @since 1.0.0
	 */
	private function validateQueryArgs( array $args ): void {
		if ( isset( $args['posts_per_page'] ) && $args['posts_per_page'] < -1 ) {
			throw RepositoryException::validationError( 'posts_per_page', 'Must be -1 or a positive integer' );
		}
	}

	/**
	 * Generate cache key from query arguments
	 *
	 * Creates unique cache key from query arguments.
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return string Cache key
	 * @since 1.0.0
	 */
	private function generateCacheKey( array $args ): string {
		return 'aps_product_list_' . md5( serialize( $args ) );
	}

	/**
	 * Get cached items if available
	 *
	 * Retrieves cached items from WordPress object cache.
	 *
	 * @param string $cache_key Cache key
	 * @return array<int, Product>|false Cached items or false if not cached
	 * @since 1.0.0
	 */
	private function getCachedItems( string $cache_key ) {
		return wp_cache_get( $cache_key, 'aps_products' );
	}

	/**
	 * Execute WP_Query with error handling
	 *
	 * Executes WordPress query and wraps exceptions.
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return \WP_Query Query object
	 * @throws RepositoryException If query fails
	 * @since 1.0.0
	 */
	private function executeQuery( array $args ): \WP_Query {
		try {
			return new \WP_Query( $args );
		} catch ( \Exception $e ) {
			throw RepositoryException::queryError(
				__( 'Product', 'affiliate-product-showcase' ),
				$e->getMessage(),
				0,
				$e
			);
		}
	}

	/**
	 * Process query results with N+1 query optimization
	 *
	 * Converts query results to Product objects with
	 * pre-fetched meta data for performance.
	 *
	 * @param \WP_Query $query Query object
	 * @return array<int, Product> Processed products
	 * @since 1.0.0
	 */
	private function processQueryResults( \WP_Query $query ): array {
		$post_ids = wp_list_pluck( $query->posts, 'ID' );
		$all_meta = $this->fetchAllMeta( $post_ids );
		
		return $this->buildProductsFromQuery( $query->posts, $all_meta );
	}

	/**
	 * Fetch all meta data for given post IDs (N+1 query prevention)
	 *
	 * Pre-fetches all meta data to prevent N+1 query problem.
	 *
	 * @param array<int, int> $post_ids Array of post IDs
	 * @return array<int, array<string, mixed>> Meta data indexed by post ID
	 * @since 1.0.0
	 */
	private function fetchAllMeta( array $post_ids ): array {
		$all_meta = [];
		
		if ( ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$all_meta[ $post_id ] = get_post_meta( $post_id );
			}
		}
		
		return $all_meta;
	}

	/**
	 * Build product objects from query results
	 *
	 * Creates Product objects from posts and pre-fetched meta data.
	 *
	 * @param array<int, \WP_Post> $posts Array of post objects
	 * @param array<int, array<string, mixed>> $all_meta Pre-fetched meta data
	 * @return array<int, Product> Array of product objects
	 * @since 1.0.0
	 */
	private function buildProductsFromQuery( array $posts, array $all_meta ): array {
		$items = [];
		
		foreach ( $posts as $post ) {
			try {
				$items[] = $this->factory->from_post( $post, $all_meta[ $post->ID ] ?? [] );
			} catch ( \Exception $e ) {
				$this->logProductCreationError( $post->ID, $e );
			}
		}
		
		return $items;
	}

	/**
	 * Log product creation error
	 *
	 * Logs errors that occur during Product object creation.
	 *
	 * @param int $post_id Post ID
	 * @param \Exception $exception Exception that occurred
	 * @return void
	 * @since 1.0.0
	 */
	private function logProductCreationError( int $post_id, \Exception $exception ): void {
		error_log( sprintf(
			'ProductRepository: Failed to create product from post %d: %s',
			$post_id,
			$exception->getMessage()
		) );
	}

	/**
	 * Cache items for future queries
	 *
	 * Stores query results in WordPress object cache.
	 *
	 * @param string $cache_key Cache key
	 * @param array<int, Product> $items Items to cache
	 * @return void
	 * @since 1.0.0
	 */
	private function cacheItems( string $cache_key, array $items ): void {
		// Cache for 5 minutes (shorter for lists as they change more frequently)
		wp_cache_set( $cache_key, $items, 'aps_products', 5 * MINUTE_IN_SECONDS );
	}

	/**
	 * Save a product
	 *
	 * Creates or updates a product in the database.
	 * Includes validation and meta data handling.
	 *
	 * @param object $model Product model to save
	 * @return int Product ID
	 * @throws RepositoryException If save fails or model is invalid type
	 * @since 1.0.0
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
			'post_status'  => $model->status ?? 'publish',
		];

		$stored_id = wp_insert_post( $postarr, true );
		
		if ( is_wp_error( $stored_id ) ) {
			$error_message = $stored_id instanceof WP_Error 
				? implode( ', ', $stored_id->get_error_messages() ) 
				: __( 'Unknown error', 'affiliate-product-showcase' );
			throw RepositoryException::saveFailed(__( 'Product', 'affiliate-product-showcase' ), $error_message);
		}

		$stored_id = (int) $stored_id;
		
		// Save meta data
		$this->saveMeta( $stored_id, $model );

		return $stored_id;
	}

	/**
	 * Validate product data before saving
	 *
	 * Ensures required fields are present and valid.
	 *
	 * @param Product $product Product to validate
	 * @return void
	 * @throws RepositoryException If validation fails
	 * @since 1.0.0
	 */
	private function validateProduct( Product $product ): void {
		if ( empty( $product->title ) ) {
			throw RepositoryException::validationError('title', __( 'Title is required', 'affiliate-product-showcase' ));
		}

		if ( empty( $product->affiliate_url ) ) {
			throw RepositoryException::validationError('affiliate_url', __( 'Affiliate URL is required', 'affiliate-product-showcase' ));
		}

		if ( ! filter_var( $product->affiliate_url, FILTER_VALIDATE_URL ) ) {
			throw RepositoryException::validationError('affiliate_url', __( 'Must be a valid URL', 'affiliate-product-showcase' ));
		}

		if ( null !== $product->price && $product->price < 0 ) {
			throw RepositoryException::validationError('price', __( 'Cannot be negative', 'affiliate-product-showcase' ));
		}
	}

	/**
	 * Save product meta data
	 *
	 * Saves all meta fields and taxonomy terms for a product.
	 *
	 * @param int $post_id Post ID
	 * @param Product $product Product to save
	 * @return void
	 * @throws RepositoryException If meta save fails
	 * @since 1.0.0
	 */
	private function saveMeta( int $post_id, Product $product ): void {
		$meta_fields = $this->getProductMetaFields( $product );
		
		foreach ( $meta_fields as $key => $value ) {
			if ( $this->shouldUpdateMeta( $post_id, $key, $value ) ) {
				$this->updateMetaField( $post_id, $key, $value );
			}
		}

		// Save category taxonomies
		$this->saveCategories( $post_id, $product );
		
		// Save tag taxonomies
		$this->saveTags( $post_id, $product );
	}

	/**
	 * Get product meta fields as associative array
	 *
	 * Extracts meta fields from Product object.
	 *
	 * @param Product $product Product object
	 * @return array<string, mixed> Meta fields keyed by meta key
	 * @since 1.0.0
	 */
	private function getProductMetaFields( Product $product ): array {
		return [
			'aps_price'         => $product->price,
			'aps_original_price' => $product->original_price,
			'aps_currency'      => $product->currency,
			'aps_affiliate_url' => $product->affiliate_url,
			'aps_image_url'    => $product->image_url,
			'aps_rating'       => $product->rating,
			'aps_badge'        => $product->badge,
			'aps_featured'      => $product->featured ? '1' : '',
		];
	}

	/**
	 * Save category taxonomies for a product
	 *
	 * Sets category taxonomy terms for the product.
	 *
	 * @param int $post_id Post ID
	 * @param Product $product Product object
	 * @return void
	 * @since 1.0.0
	 */
	private function saveCategories( int $post_id, Product $product ): void {
		// Remove old 'aps_categories' meta if it exists (migration cleanup)
		delete_post_meta( $post_id, 'aps_categories' );
		
		// Set taxonomy terms
		if ( ! empty( $product->category_ids ) ) {
			wp_set_object_terms( $post_id, $product->category_ids, Constants::TAX_CATEGORY );
		} else {
			// Remove all category terms if empty array provided
			wp_set_object_terms( $post_id, [], Constants::TAX_CATEGORY );
		}
	}

	/**
	 * Save tag taxonomies for a product
	 *
	 * Sets tag taxonomy terms for the product.
	 *
	 * @param int $post_id Post ID
	 * @param Product $product Product object
	 * @return void
	 * @since 1.0.0
	 */
	private function saveTags( int $post_id, Product $product ): void {
		// Remove old 'aps_tags' meta if it exists (migration cleanup)
		delete_post_meta( $post_id, 'aps_tags' );
		
		// Set taxonomy terms
		if ( ! empty( $product->tag_ids ) ) {
			wp_set_object_terms( $post_id, $product->tag_ids, Constants::TAX_TAG );
		} else {
			// Remove all tag terms if empty array provided
			wp_set_object_terms( $post_id, [], Constants::TAX_TAG );
		}
	}

	/**
	 * Check if meta field should be updated
	 *
	 * Compares new value with current value to avoid unnecessary updates.
	 *
	 * @param int $post_id Post ID
	 * @param string $key Meta key
	 * @param mixed $value New value
	 * @return bool True if field should be updated
	 * @since 1.0.0
	 */
	private function shouldUpdateMeta( int $post_id, string $key, $value ): bool {
		$current = get_post_meta( $post_id, $key, true );
		return $value !== $current;
	}

	/**
	 * Update a single meta field with error handling
	 *
	 * Updates meta field and handles errors appropriately.
	 *
	 * @param int $post_id Post ID
	 * @param string $key Meta key
	 * @param mixed $value Meta value
	 * @throws RepositoryException If meta update fails
	 * @since 1.0.0
	 */
	private function updateMetaField( int $post_id, string $key, $value ): void {
		$result = update_post_meta( $post_id, $key, $value );
		
		if ( $this->isMetaUpdateFailed( $result, $value ) ) {
			throw RepositoryException::saveFailed(
				__( 'Product Meta', 'affiliate-product-showcase' ),
				sprintf( __( 'Failed to update meta field "%s"', 'affiliate-product-showcase' ), $key )
			);
		}
	}

	/**
	 * Check if meta update operation failed
	 *
	 * Determines if update_post_meta operation failed.
	 *
	 * @param mixed $result Result from update_post_meta
	 * @param mixed $value Value that was being updated
	 * @return bool True if update failed
	 * @since 1.0.0
	 */
	private function isMetaUpdateFailed( $result, $value ): bool {
		// update_post_meta returns false on FAILURE, not when value === false
		// It returns the old value on success (which might be false)
		return $result === false && ! in_array( $value, [ false, '', null ], true );
	}

	/**
	 * Delete a product
	 *
	 * Permanently deletes a product from the database.
	 * Clears related caches after deletion.
	 *
	 * @param int $id Product ID
	 * @return bool True if deleted successfully
	 * @throws RepositoryException If delete fails or product not found
	 * @since 1.0.0
	 */
	public function delete( int $id ): bool {
		if ( $id <= 0 ) {
			throw RepositoryException::validationError('id', __( 'ID must be a positive integer', 'affiliate-product-showcase' ));
		}

		// Check if product exists and is correct type
		$product = $this->find( $id );
		if ( ! $product ) {
			throw RepositoryException::notFound(__( 'Product', 'affiliate-product-showcase' ), $id);
		}

		$deleted = wp_delete_post( $id, true );
		
		if ( false === $deleted ) {
			throw RepositoryException::deleteFailed(__( 'Product', 'affiliate-product-showcase' ), __( 'wp_delete_post returned false', 'affiliate-product-showcase' ));
		}
		
		// Clear product cache
		wp_cache_delete( 'aps_product_' . $id, 'aps_products' );
		// Clear all product list caches
		wp_cache_flush_group( 'aps_products' );

		return true;
	}
}
