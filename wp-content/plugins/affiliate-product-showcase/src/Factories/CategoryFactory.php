<?php
/**
 * Category Factory
 *
 * Factory class for creating Category instances from various sources.
 * Handles conversion between different data formats and Category model.
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Factories;

use AffiliateProductShowcase\Models\Category;

/**
 * Category Factory
 *
 * Factory class for creating Category instances from various sources.
 * Handles conversion between different data formats and Category model.
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 * @author Development Team
 */
final class CategoryFactory {
	/**
	 * Create Category from WP_Term
	 *
	 * @param \WP_Term $term WordPress term object
	 * @return Category Category instance
	 * @throws \InvalidArgumentException If term is not a category
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $term = get_term(1, 'aps_category');
	 * $category = CategoryFactory::from_wp_term($term);
	 * ```
	 */
	public static function from_wp_term( \WP_Term $term ): Category {
		return Category::from_wp_term( $term );
	}

	/**
	 * Create Category from array
	 *
	 * @param array<string, mixed> $data Category data
	 * @return Category Category instance
	 * @throws \InvalidArgumentException If required fields are missing
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $category = CategoryFactory::from_array([
	 *     'name' => 'Electronics',
	 *     'slug' => 'electronics',
	 *     'description' => 'Electronic products',
	 * ]);
	 * ```
	 */
	public static function from_array( array $data ): Category {
		return Category::from_array( $data );
	}

	/**
	 * Create Categories from array of arrays
	 *
	 * @param array<int, array<string, mixed>> $categories_data Array of category data
	 * @return array<int, Category> Array of Category instances
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $categories = CategoryFactory::from_arrays([
	 *     ['name' => 'Electronics', 'slug' => 'electronics'],
	 *     ['name' => 'Books', 'slug' => 'books'],
	 * ]);
	 * ```
	 */
	public static function from_arrays( array $categories_data ): array {
		$categories = [];

		foreach ( $categories_data as $category_data ) {
			try {
				$categories[] = self::from_array( $category_data );
			} catch ( \InvalidArgumentException $e ) {
				// Skip invalid categories
				continue;
			}
		}

		return $categories;
	}

	/**
	 * Create Categories from array of WP_Term objects
	 *
	 * @param array<int, \WP_Term> $terms Array of WordPress term objects
	 * @return array<int, Category> Array of Category instances
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $terms = get_terms(['taxonomy' => 'aps_category']);
	 * $categories = CategoryFactory::from_wp_terms($terms);
	 * ```
	 */
	public static function from_wp_terms( array $terms ): array {
		$categories = [];

		foreach ( $terms as $term ) {
			try {
				$categories[] = self::from_wp_term( $term );
			} catch ( \InvalidArgumentException $e ) {
				// Skip invalid terms
				continue;
			}
		}

		return $categories;
	}

	/**
	 * Create Category from database row
	 *
	 * @param object $row Database row object
	 * @return Category Category instance
	 * @throws \InvalidArgumentException If required fields are missing
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * global $wpdb;
	 * $row = $wpdb->get_row("SELECT * FROM $wpdb->terms WHERE term_id = 1");
	 * $category = CategoryFactory::from_db_row($row);
	 * ```
	 */
	public static function from_db_row( object $row ): Category {
		if ( empty( $row->term_id ) || empty( $row->name ) ) {
			throw new \InvalidArgumentException( 'Database row must contain term_id and name.' );
		}

		return new Category(
			(int) $row->term_id,
			$row->name,
			$row->slug ?? sanitize_title( $row->name ),
			$row->description ?? '',
			(int) ( $row->parent ?? 0 ),
			(int) ( $row->count ?? 0 ),
			false,
			null,
			'date',
			$row->term_group ? date( 'Y-m-d H:i:s', (int) $row->term_group ) : current_time( 'mysql' )
		);
	}

	/**
	 * Build hierarchical tree from flat category list
	 *
	 * @param array<int, Category> $categories Flat array of categories
	 * @return array<int, Category> Hierarchical tree of categories
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $categories = get_terms(['taxonomy' => 'aps_category', 'get' => 'all']);
	 * $flat_categories = CategoryFactory::from_wp_terms($categories);
	 * $tree = CategoryFactory::build_tree($flat_categories);
	 * ```
	 */
	public static function build_tree( array $categories ): array {
		$tree = [];
		$lookup = [];

		// Build lookup table
		foreach ( $categories as $category ) {
			$lookup[ $category->id ] = $category;
		}

		// Build tree structure
		foreach ( $categories as $category ) {
			if ( $category->parent_id === 0 ) {
				// Top-level category
				$tree[] = $category;
			} elseif ( isset( $lookup[ $category->parent_id ] ) ) {
				// Child category - add to parent's children
				$parent = $lookup[ $category->parent_id ];
				// Note: We'd need to modify Category model to store children
				// For now, this is a placeholder for future enhancement
			}
		}

		return $tree;
	}

	/**
	 * Sort categories by name (alphabetical)
	 *
	 * @param array<int, Category> $categories Categories to sort
	 * @param string $order 'ASC' or 'DESC'
	 * @return array<int, Category> Sorted categories
	 * @since 1.0.0
	 */
	public static function sort_by_name( array $categories, string $order = 'ASC' ): array {
		$categories = $categories;
		
		usort( $categories, function( $a, $b ) use ( $order ) {
			$compare = strcasecmp( $a->name, $b->name );
			return $order === 'ASC' ? $compare : -$compare;
		} );

		return $categories;
	}

	/**
	 * Sort categories by count (most products first)
	 *
	 * @param array<int, Category> $categories Categories to sort
	 * @param string $order 'ASC' or 'DESC'
	 * @return array<int, Category> Sorted categories
	 * @since 1.0.0
	 */
	public static function sort_by_count( array $categories, string $order = 'DESC' ): array {
		$categories = $categories;
		
		usort( $categories, function( $a, $b ) use ( $order ) {
			$compare = $a->count <=> $b->count;
			return $order === 'DESC' ? -$compare : $compare;
		} );

		return $categories;
	}

	/**
	 * Filter categories by featured status
	 *
	 * @param array<int, Category> $categories Categories to filter
	 * @param bool $featured Featured status to filter by
	 * @return array<int, Category> Filtered categories
	 * @since 1.0.0
	 */
	public static function filter_by_featured( array $categories, bool $featured = true ): array {
		return array_filter( $categories, fn( $category ) => $category->featured === $featured );
	}

	/**
	 * Filter categories by parent
	 *
	 * @param array<int, Category> $categories Categories to filter
	 * @param int $parent_id Parent category ID (0 for top-level)
	 * @return array<int, Category> Filtered categories
	 * @since 1.0.0
	 */
	public static function filter_by_parent( array $categories, int $parent_id = 0 ): array {
		return array_filter( $categories, fn( $category ) => $category->parent_id === $parent_id );
	}
}