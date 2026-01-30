<?php
/**
 * Category Repository
 *
 * Handles database operations for category taxonomy including
 * CRUD operations, querying, and metadata management.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Factories\CategoryFactory;
use AffiliateProductShowcase\Models\Category;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Exceptions\PluginException;
use WP_Error;

/**
 * Category Repository
 *
 * Handles database operations for category taxonomy including
 * CRUD operations, querying, and metadata management.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 * @author Development Team
 */
final class CategoryRepository {
	/**
	 * Get a category by ID
	 *
	 * @param int $category_id Category ID (term_id)
	 * @return Category|null Category instance or null if not found
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $category = $repository->find(1);
	 * if ($category) {
	 *     echo $category->name;
	 * }
	 * ```
	 */
	public function find( int $category_id ): ?Category {
		if ( $category_id <= 0 ) {
			return null;
		}

		// Ensure taxonomy is registered
		if ( ! taxonomy_exists( Constants::TAX_CATEGORY ) ) {
			error_log( sprintf( '[APS] Taxonomy %s not registered', Constants::TAX_CATEGORY ) );
			return null;
		}

		$term = get_term( $category_id, Constants::TAX_CATEGORY );

		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		return CategoryFactory::from_wp_term( $term );
	}

	/**
	 * Get a category by slug
	 *
	 * @param string $slug Category slug
	 * @return Category|null Category instance or null if not found
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $category = $repository->find_by_slug('electronics');
	 * ```
	 */
	public function find_by_slug( string $slug ): ?Category {
		$term = get_term_by( 'slug', $slug, Constants::TAX_CATEGORY );

		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		return CategoryFactory::from_wp_term( $term );
	}

	/**
	 * Get all categories
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array<int, Category> Array of Category instances
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $categories = $repository->all(['hide_empty' => false]);
	 * ```
	 */
	public function all( array $args = [] ): array {
		$default_args = [
			'taxonomy'   => Constants::TAX_CATEGORY,
			'hide_empty' => false,
		];

		$args = wp_parse_args( $args, $default_args );

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		return CategoryFactory::from_wp_terms( $terms );
	}

	/**
	 * Get categories with pagination
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array{categories: array<int, Category>, total: int, pages: int} Paginated result
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $result = $repository->paginate([
	 *     'number' => 10,
	 *     'offset' => 0,
	 * ]);
	 * ```
	 */
	public function paginate( array $args = [] ): array {
		$default_args = [
			'taxonomy'   => Constants::TAX_CATEGORY,
			'hide_empty' => false,
			'number'     => 10,
			'offset'     => 0,
		];

		$args = wp_parse_args( $args, $default_args );

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [
				'categories' => [],
				'total'      => 0,
				'pages'      => 0,
			];
		}

		// Get total count
		$count_args = $args;
		unset( $count_args['number'], $count_args['offset'] );
		$total = wp_count_terms( Constants::TAX_CATEGORY, $count_args );

		if ( is_wp_error( $total ) ) {
			$total = 0;
		}

		$per_page = (int) $args['number'];
		$pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 0;

		return [
			'categories' => CategoryFactory::from_wp_terms( $terms ),
			'total'      => $total,
			'pages'      => $pages,
		];
	}

	/**
	 * Create a new category
	 *
	 * @param Category $category Category instance to create
	 * @return Category Created category instance
	 * @throws PluginException If category creation fails
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $category = new Category(
	 *     0,
	 *     'Electronics',
	 *     'electronics',
	 *     'Electronic products'
	 * );
	 * $created = $repository->create($category);
	 * ```
	 */
	public function create( Category $category ): Category {
		$result = wp_insert_term(
			$category->name,
			Constants::TAX_CATEGORY,
			[
				'slug'        => $category->slug,
				'description' => $category->description,
				'parent'      => $category->parent_id,
			]
		);

		if ( is_wp_error( $result ) ) {
			throw new PluginException(
				sprintf(
					'Failed to create category: %s',
					$result->get_error_message()
				)
			);
		}

		$term_id = (int) $result['term_id'];

		// Save metadata
		$this->save_metadata( $term_id, $category );

		// Return category with ID
		$created_category = $this->find( $term_id );
		
		if ( ! $created_category ) {
			throw new PluginException(
				sprintf(
					'Category created successfully but could not be retrieved. Term ID: %d',
					$term_id
				)
			);
		}
		
		return $created_category;
	}

	/**
	 * Update an existing category
	 *
	 * @param Category $category Category instance to update
	 * @return Category Updated category instance
	 * @throws PluginException If category update fails
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $category = $repository->find(1);
	 * $category = new Category(
	 *     1,
	 *     'Electronics Updated',
	 *     'electronics-updated',
	 *     'Updated description'
	 * );
	 * $updated = $repository->update($category);
	 * ```
	 */
	public function update( Category $category ): Category {
		if ( $category->id <= 0 ) {
			throw new PluginException( 'Category ID is required for update.' );
		}

		$result = wp_update_term(
			$category->id,
			Constants::TAX_CATEGORY,
			[
				'name'        => $category->name,
				'slug'        => $category->slug,
				'description' => $category->description,
				'parent'      => $category->parent_id,
			]
		);

		if ( is_wp_error( $result ) ) {
			throw new PluginException(
				sprintf(
					'Failed to update category: %s',
					$result->get_error_message()
				)
			);
		}

		// Save metadata
		$this->save_metadata( $category->id, $category );

		// Return updated category
		$updated_category = $this->find( $category->id );
		
		if ( ! $updated_category ) {
			throw new PluginException(
				sprintf(
					'Category updated successfully but could not be retrieved. Term ID: %d',
					$category->id
				)
			);
		}
		
		return $updated_category;
	}

	/**
	 * Delete a category permanently
	 *
	 * Note: WordPress core does not support trash/restore functionality for taxonomy terms.
	 * This method immediately deletes the category permanently. It is kept as an alias
	 * to delete_permanently() for API consistency and backward compatibility.
	 *
	 * @param int $category_id Category ID to delete
	 * @return bool True on success
	 * @throws PluginException If deletion fails
	 * @since 1.0.0
	 * @see delete_permanently() The actual implementation
	 *
	 * @example
	 * ```php
	 * $repository->delete(1);
	 * ```
	 */
	final public function delete( int $category_id ): bool {
		// WordPress doesn't have native trash for terms
		// This is an alias for delete_permanently() for API consistency
		return $this->delete_permanently( $category_id );
	}

	/**
	 * Restore a category from trash
	 *
	 * Note: WordPress doesn't have native trash for terms.
	 * This is a placeholder for future enhancement.
	 *
	 * @param int $category_id Category ID to restore
	 * @return Category Restored category instance
	 * @throws PluginException If restore fails
	 * @since 1.0.0
	 */
	public function restore( int $category_id ): Category {
		// WordPress doesn't have native trash for terms
		// This is a placeholder for future enhancement
		throw new PluginException( 'Category trash/restore is not supported in WordPress core.' );
	}

	/**
	 * Delete a category permanently
	 *
	 * This method performs the actual deletion. The delete() method is an alias
	 * to this method since WordPress does not support trash for taxonomy terms.
	 *
	 * @param int $category_id Category ID to delete permanently
	 * @return bool True on success
	 * @throws PluginException If deletion fails
	 * @since 1.0.0
	 * @see delete() Alias method for API consistency
	 *
	 * @example
	 * ```php
	 * $repository->delete_permanently(1);
	 * ```
	 */
	public function delete_permanently( int $category_id ): bool {
		if ( $category_id <= 0 ) {
			throw new PluginException( 'Category ID is required.' );
		}

		$category = $this->find( $category_id );
		if ( ! $category ) {
			throw new PluginException( 'Category not found.' );
		}

		// Prevent deleting default category
		if ( $category->is_default ) {
			throw new PluginException( 'Cannot delete default category. Please select another default category first.' );
		}

		$result = wp_delete_term( $category_id, Constants::TAX_CATEGORY );

		if ( is_wp_error( $result ) ) {
			throw new PluginException(
				sprintf(
					'Failed to delete category permanently: %s',
					$result->get_error_message()
				)
			);
		}

		// Delete metadata
		$this->delete_metadata( $category_id );

		return true;
	}

	/**
	 * Get featured categories
	 *
	 * @return array<int, Category> Featured categories
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $featured = $repository->get_featured();
	 * ```
	 */
	public function get_featured(): array {
		return $this->all( [
			'meta_query' => [
				[
					'key'   => '_aps_category_featured',
					'value' => '1',
				],
			],
		] );
	}

	/**
	 * Get child categories
	 *
	 * @param int $parent_id Parent category ID
	 * @return array<int, Category> Child categories
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $children = $repository->get_children(1);
	 * ```
	 */
	public function get_children( int $parent_id ): array {
		return $this->all( [
			'parent' => $parent_id,
		] );
	}

	/**
	 * Get top-level categories
	 *
	 * @return array<int, Category> Top-level categories
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $top_level = $repository->get_top_level();
	 * ```
	 */
	public function get_top_level(): array {
		return $this->all( [
			'parent' => 0,
		] );
	}

	/**
	 * Count categories
	 *
	 * @return int Total number of categories
	 * @since 1.0.0
	 */
	public function count(): int {
		$count = wp_count_terms( Constants::TAX_CATEGORY, [ 'hide_empty' => false ] );

		return is_wp_error( $count ) ? 0 : (int) $count;
	}

	/**
	 * Search categories by name or description
	 *
	 * @param string $search Search term
	 * @return array<int, Category> Matching categories
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $results = $repository->search('electron');
	 * ```
	 */
	public function search( string $search ): array {
		return $this->all( [
			'search' => $search,
		] );
	}

	/**
	 * Save category metadata
	 *
	 * @param int $term_id Term ID
	 * @param Category $category Category instance
	 * @return void
	 * @since 1.0.0
	 */
	private function save_metadata( int $term_id, Category $category ): void {
		// Featured
		update_term_meta( $term_id, '_aps_category_featured', $category->featured ? 1 : 0 );
		// Delete legacy key
		delete_term_meta( $term_id, 'aps_category_featured' );

		// Image URL
		if ( $category->image_url ) {
			update_term_meta( $term_id, '_aps_category_image', $category->image_url );
			// Delete legacy key
			delete_term_meta( $term_id, 'aps_category_image' );
		} else {
			delete_term_meta( $term_id, '_aps_category_image' );
			delete_term_meta( $term_id, 'aps_category_image' );
		}

		// Sort order
		update_term_meta( $term_id, '_aps_category_sort_order', $category->sort_order );
		// Delete legacy key
		delete_term_meta( $term_id, 'aps_category_sort_order' );

		// Status (published/draft)
		update_term_meta( $term_id, '_aps_category_status', $category->status );
		// Delete legacy key
		delete_term_meta( $term_id, 'aps_category_status' );

		// Is default category
		if ( $category->is_default ) {
			// Remove default flag from other categories
			$this->remove_default_from_all_categories();
			// Set this category as default
			update_term_meta( $term_id, '_aps_category_is_default', 1 );
			// Delete legacy key
			delete_term_meta( $term_id, 'aps_category_is_default' );
			// Update global default category option
			update_option( 'aps_default_category_id', $term_id );
		}
	}

	/**
	 * Remove default flag from all categories
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function remove_default_from_all_categories(): void {
		$categories = $this->all();
		foreach ( $categories as $category ) {
			delete_term_meta( $category->id, '_aps_category_is_default' );
			delete_term_meta( $category->id, 'aps_category_is_default' );
		}
	}

	/**
	 * Get default category
	 *
	 * @return Category|null Default category or null if not set
	 * @since 1.1.0
	 */
	public function get_default(): ?Category {
		$default_id = get_option( 'aps_default_category_id', 0 );
		if ( $default_id > 0 ) {
			return $this->find( $default_id );
		}
		return null;
	}

	/**
	 * Set category status to draft
	 *
	 * @param int $category_id Category ID
	 * @return bool True on success
	 * @throws PluginException If update fails
	 * @since 1.1.0
	 */
	public function set_draft( int $category_id ): bool {
		$category = $this->find( $category_id );
		if ( ! $category ) {
			throw new PluginException( 'Category not found.' );
		}

		$updated = new Category(
			$category->id,
			$category->name,
			$category->slug,
			$category->description,
			$category->parent_id,
			$category->count,
			$category->featured,
			$category->image_url,
			$category->sort_order,
			$category->created_at,
			'draft',
			$category->is_default
		);

		$this->update( $updated );
		return true;
	}

	/**
	 * Delete category metadata
	 *
	 * @param int $term_id Term ID
	 * @return void
	 * @since 1.0.0
	 */
	private function delete_metadata( int $term_id ): void {
		// Delete new format keys
		delete_term_meta( $term_id, '_aps_category_featured' );
		delete_term_meta( $term_id, '_aps_category_image' );
		delete_term_meta( $term_id, '_aps_category_sort_order' );
		delete_term_meta( $term_id, '_aps_category_status' );
		delete_term_meta( $term_id, '_aps_category_is_default' );
		
		// Delete legacy format keys
		delete_term_meta( $term_id, 'aps_category_featured' );
		delete_term_meta( $term_id, 'aps_category_image' );
		delete_term_meta( $term_id, 'aps_category_sort_order' );
		delete_term_meta( $term_id, 'aps_category_status' );
		delete_term_meta( $term_id, 'aps_category_is_default' );
	}
}
