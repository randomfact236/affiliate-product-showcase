<?php
/**
 * Category Model
 *
 * Represents a product category with strict typing and immutability.
 * Categories are hierarchical taxonomies for organizing products.
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Models;

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Helpers\TermMetaHelper;
use AffiliateProductShowcase\Validators\StatusValidator;
use AffiliateProductShowcase\Plugin\StatusConstants;
use AffiliateProductShowcase\Plugin\SortOrderConstants;

/**
 * Category Model
 *
 * Represents a product category with strict typing and immutability.
 * Categories are hierarchical taxonomies for organizing products.
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 * @author Development Team
 */
final class Category {
	/**
	 * Category ID (term_id)
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public readonly int $id;

	/**
	 * Category name
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $name;

	/**
	 * Category slug
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $slug;

	/**
	 * Category description
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $description;

	/**
	 * Parent category ID (0 for top-level)
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public readonly int $parent_id;

	/**
	 * Product count in this category
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public readonly int $count;

	/**
	 * Whether category is featured
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	public readonly bool $featured;

	/**
	 * Category image URL
	 *
	 * @var string|null
	 * @since 1.0.0
	 */
	public readonly ?string $image_url;

	/**
	 * Default sort order for products in this category
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $sort_order;

	/**
	 * Category creation date
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $created_at;

	/**
	 * Category status (published, draft)
	 *
	 * @var string
	 * @since 1.1.0
	 */
	public readonly string $status;

	/**
	 * Whether category is the default category
	 *
	 * @var bool
	 * @since 1.1.0
	 */
	public readonly bool $is_default;

	/**
	 * Constructor
	 *
	 * @param int    $id           Category ID (term_id)
	 * @param string $name         Category name
	 * @param string $slug         Category slug
	 * @param string $description  Category description
	 * @param int    $parent_id    Parent category ID (0 for top-level)
	 * @param int    $count        Product count in this category
	 * @param bool   $featured     Whether category is featured
	 * @param string|null $image_url   Category image URL (optional)
	 * @param string $sort_order   Default sort order for products
	 * @param string $created_at   Category creation date
	 * @param string $status       Category status (published, draft)
	 * @param bool   $is_default   Whether category is default
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		int $id,
		string $name,
		string $slug,
		string $description = '',
		int $parent_id = 0,
		int $count = 0,
		bool $featured = false,
		?string $image_url = null,
		string $sort_order = 'date',
		string $created_at = '',
		string $status = 'published',
		bool $is_default = false
	) {
		$this->id = $id;
		$this->name = $name;
		$this->slug = $slug;
		$this->description = $description;
		$this->parent_id = $parent_id;
		$this->count = $count;
		$this->featured = $featured;
		$this->image_url = $image_url;
		$this->sort_order = $sort_order;
		$this->created_at = $created_at ?: current_time( 'mysql' );
		$this->status = $status;
		$this->is_default = $is_default;
	}

	/**
	 * Convert category to array
	 *
	 * Returns array representation of category for API responses.
	 *
	 * @return array<string, mixed> Category data as array
	 * @since 1.0.0
	 */
	public function to_array(): array {
		return [
			'id'           => $this->id,
			'name'         => $this->name,
			'slug'         => $this->slug,
			'description'  => $this->description,
			'parent_id'    => $this->parent_id,
			'count'        => $this->count,
			'featured'     => $this->featured,
			'image_url'    => $this->image_url,
			'sort_order'   => $this->sort_order,
			'created_at'   => $this->created_at,
			'status'       => $this->status,
			'is_default'   => $this->is_default,
			'taxonomy'     => Constants::TAX_CATEGORY,
		];
	}

	/**
	 * Create Category from WP_Term
	 *
	 * Factory method to create Category instance from WP_Term object.
	 *
	 * @param \WP_Term $term WordPress term object
	 * @return self Category instance
	 * @throws \InvalidArgumentException If term is not a category
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $term = get_term(1, 'aps_category');
	 * $category = Category::from_wp_term($term);
	 * ```
	 */
	public static function from_wp_term( \WP_Term $term ): self {
		if ( $term->taxonomy !== Constants::TAX_CATEGORY ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Term must be a category, got taxonomy: %s',
					$term->taxonomy
				)
			);
		}

		// Get category metadata with legacy fallback
		$featured = (bool) TermMetaHelper::get_with_fallback( $term->term_id, 'featured', 'aps_category_' );
		$image_url = TermMetaHelper::get_with_fallback( $term->term_id, 'image', 'aps_category_' ) ?: null;
		$sort_order = TermMetaHelper::get_with_fallback( $term->term_id, 'sort_order', 'aps_category_' ) ?: 'date';
		$status = StatusValidator::validate(TermMetaHelper::get_with_fallback( $term->term_id, 'status', 'aps_category_' ));
		$is_default = (bool) TermMetaHelper::get_with_fallback( $term->term_id, 'is_default', 'aps_category_' );

		// Check if this is the global default category
		$global_default_id = get_option( 'aps_default_category_id', 0 );
		$is_default = $is_default || ( (int) $global_default_id === (int) $term->term_id );

		return new self(
			(int) $term->term_id,
			$term->name,
			$term->slug,
			$term->description ?? '',
			(int) $term->parent,
			(int) $term->count,
			$featured,
			$image_url,
			$sort_order,
			$term->term_group ? date( 'Y-m-d H:i:s', $term->term_group ) : current_time( 'mysql' ),
			$status,
			$is_default
		);
	}

	/**
	 * Create Category from array
	 *
	 * Factory method to create Category instance from array data.
	 * Useful for API requests and data imports.
	 *
	 * @param array<string, mixed> $data Category data
	 * @return self Category instance
	 * @throws \InvalidArgumentException If required fields are missing
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $category = Category::from_array([
	 *     'name' => 'Electronics',
	 *     'slug' => 'electronics',
	 *     'description' => 'Electronic products',
	 * ]);
	 * ```
	 */
	public static function from_array( array $data ): self {
		if ( empty( $data['name'] ) ) {
			throw new \InvalidArgumentException( 'Category name is required.' );
		}

		// Generate slug from name if not provided
		$slug = $data['slug'] ?? sanitize_title( $data['name'] );

		// Ensure unique slug by creating a temporary term object
		$term_object = (object) [
			'taxonomy' => Constants::TAX_CATEGORY,
			'parent'   => (int) ( $data['parent_id'] ?? 0 ),
			'term_id'  => (int) ( $data['id'] ?? 0 ),
		];
		$slug = wp_unique_term_slug( $slug, $term_object );

		return new self(
			(int) ( $data['id'] ?? 0 ),
			sanitize_text_field( $data['name'] ),
			sanitize_title( $slug ),
			sanitize_textarea_field( $data['description'] ?? '' ),
			(int) ( $data['parent_id'] ?? 0 ),
			(int) ( $data['count'] ?? 0 ),
			(bool) ( $data['featured'] ?? false ),
			! empty( $data['image_url'] ) ? esc_url_raw( $data['image_url'] ) : null,
			SortOrderConstants::isValid($data['sort_order'] ?? SortOrderConstants::DATE)
				? $data['sort_order']
				: SortOrderConstants::DATE,
			$data['created_at'] ?? '',
			StatusValidator::isValid($data['status'] ?? StatusConstants::PUBLISHED)
				? $data['status']
				: 'published',
			(bool) ( $data['is_default'] ?? false )
		);
	}

	/**
	 * Check if category has parent
	 *
	 * @return bool True if category has parent, false otherwise
	 * @since 1.0.0
	 */
	public function has_parent(): bool {
		return $this->parent_id > 0;
	}

	/**
	 * Get parent category
	 *
	 * @return self|null Parent category or null if no parent
	 * @since 1.0.0
	 */
	public function get_parent(): ?self {
		if ( ! $this->has_parent() ) {
			return null;
		}

		$term = get_term( $this->parent_id, Constants::TAX_CATEGORY );

		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		return self::from_wp_term( $term );
	}

	/**
	 * Get child categories
	 *
	 * @return array<int, self> Array of child categories
	 * @since 1.0.0
	 */
	public function get_children(): array {
		$children = get_terms( [
			'taxonomy'   => Constants::TAX_CATEGORY,
			'parent'     => $this->id,
			'hide_empty' => false,
		] );

		if ( is_wp_error( $children ) || empty( $children ) ) {
			return [];
		}

		return array_map( fn( $term ) => self::from_wp_term( $term ), $children );
	}

	/**
	 * Get products in category
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array<int, \AffiliateProductShowcase\Models\Product> Products in category
	 * @since 1.0.0
	 */
	public function get_products( array $args = [] ): array {
		$default_args = [
			'post_type'      => Constants::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'tax_query'      => [
				[
					'taxonomy' => Constants::TAX_CATEGORY,
					'field'    => 'term_id',
					'terms'    => $this->id,
				],
			],
		];

		$args = wp_parse_args( $args, $default_args );

		$query = new \WP_Query( $args );

		$products = [];
		foreach ( $query->posts as $post ) {
			$products[] = Product::from_post( $post );
		}

		return $products;
	}
}