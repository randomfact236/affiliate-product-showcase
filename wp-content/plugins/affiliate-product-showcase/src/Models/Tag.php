<?php
/**
 * Tag Model
 *
 * Represents a product tag with strict typing and immutability.
 * Tags are non-hierarchical taxonomies for organizing products.
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Models;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Tag Model
 *
 * Represents a product tag with strict typing and immutability.
 * Tags are non-hierarchical (flat structure) for organizing products.
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 * @author Development Team
 */
final class Tag {
	/**
	 * Tag ID (term_id)
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public readonly int $id;

	/**
	 * Tag name
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $name;

	/**
	 * Tag slug
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $slug;

	/**
	 * Tag description
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $description;

	/**
	 * Product count with this tag
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public readonly int $count;

	/**
	 * Display color (hex color code)
	 *
	 * @var string|null
	 * @since 1.0.0
	 */
	public readonly ?string $color;

	/**
	 * Icon identifier/class
	 *
	 * @var string|null
	 * @since 1.0.0
	 */
	public readonly ?string $icon;

	/**
	 * Tag creation date
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $created_at;

	/**
	 * Tag visibility status
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public readonly string $status;

	/**
	 * Featured flag
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	public readonly bool $featured;

	/**
	 * Constructor
	 *
	 * @param int    $id          Tag ID (term_id)
	 * @param string $name        Tag name
	 * @param string $slug        Tag slug
	 * @param string $description Tag description
	 * @param int    $count       Product count with this tag
	 * @param string|null $color  Display color (hex code)
	 * @param string|null $icon   Icon identifier/class
	 * @param string $created_at  Tag creation date
	 * @param string $status      Tag visibility status
	 * @param bool   $featured    Featured flag
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		int $id,
		string $name,
		string $slug,
		string $description = '',
		int $count = 0,
		?string $color = null,
		?string $icon = null,
		string $created_at = '',
		string $status = 'published',
		bool $featured = false
	) {
		$this->id = $id;
		$this->name = $name;
		$this->slug = $slug;
		$this->description = $description;
		$this->count = $count;
		$this->color = $color;
		$this->icon = $icon;
		$this->created_at = $created_at ?: current_time( 'mysql' );
		$this->status = $status;
		$this->featured = $featured;
	}

	/**
	 * Convert tag to array
	 *
	 * Returns array representation of tag for API responses.
	 *
	 * @return array<string, mixed> Tag data as array
	 * @since 1.0.0
	 */
	public function to_array(): array {
		return [
			'id'          => $this->id,
			'name'        => $this->name,
			'slug'        => $this->slug,
			'description' => $this->description,
			'count'       => $this->count,
			'color'       => $this->color,
			'icon'        => $this->icon,
			'created_at'  => $this->created_at,
			'taxonomy'    => Constants::TAX_TAG,
			'status'      => $this->status,
			'featured'    => $this->featured,
		];
	}

	/**
	 * Get tag meta
	 *
	 * Retrieves tag metadata with underscore prefix.
	 *
	 * @param int $term_id Term ID
	 * @param string $meta_key Meta key (without _aps_tag_ prefix)
	 * @return mixed Meta value
	 * @since 1.0.0
	 */
	private static function get_tag_meta( int $term_id, string $meta_key ) {
		return get_term_meta( $term_id, '_aps_tag_' . $meta_key, true );
	}

	/**
	 * Create Tag from WP_Term
	 *
	 * Factory method to create Tag instance from WP_Term object.
	 * Includes status and featured flag from taxonomies.
	 *
	 * @param \WP_Term $term WordPress term object
	 * @return self Tag instance
	 * @throws \InvalidArgumentException If term is not a tag
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $term = get_term(1, 'aps_tag');
	 * $tag = Tag::from_wp_term($term);
	 * ```
	 */
	public static function from_wp_term( \WP_Term $term ): self {
		if ( $term->taxonomy !== Constants::TAX_TAG ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Term must be a tag, got taxonomy: %s',
					$term->taxonomy
				)
			);
		}

		// Get tag metadata
		$color = self::get_tag_meta( $term->term_id, 'color' );
		$icon = self::get_tag_meta( $term->term_id, 'icon' );

		// Get status from aps_tag_visibility taxonomy
		$visibility_terms = wp_get_object_terms( $term->term_id, 'aps_tag_visibility' );
		$status = ! empty( $visibility_terms ) ? $visibility_terms[0]->slug : 'published';

		// Get featured flag from aps_tag_flags taxonomy
		$flag_terms = wp_get_object_terms( $term->term_id, 'aps_tag_flags' );
		$featured = ! empty( $flag_terms ) && $flag_terms[0]->slug === 'featured';

		return new self(
			(int) $term->term_id,
			$term->name,
			$term->slug,
			$term->description ?? '',
			(int) $term->count,
			$color ?: null,
			$icon ?: null,
			$term->term_group ? date( 'Y-m-d H:i:s', $term->term_group ) : current_time( 'mysql' ),
			$status,
			$featured
		);
	}

	/**
	 * Create Tag from array
	 *
	 * Factory method to create Tag instance from array data.
	 * Useful for API requests and data imports.
	 *
	 * @param array<string, mixed> $data Tag data
	 * @return self Tag instance
	 * @throws \InvalidArgumentException If required fields are missing
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tag = Tag::from_array([
	 *     'name' => 'Sale',
	 *     'slug' => 'sale',
	 *     'color' => '#ff0000',
	 *     'icon' => 'dashicons-tag',
	 *     'status' => 'published',
	 *     'featured' => true,
	 * ]);
	 * ```
	 */
	public static function from_array( array $data ): self {
		if ( empty( $data['name'] ) ) {
			throw new \InvalidArgumentException( 'Tag name is required.' );
		}

		// Generate slug from name if not provided
		$slug = $data['slug'] ?? sanitize_title( $data['name'] );

		// Ensure unique slug
		$slug = wp_unique_term_slug( $slug, Constants::TAX_TAG );

		// Validate status
		$status = $data['status'] ?? 'published';
		if ( ! in_array( $status, [ 'published', 'draft', 'trash' ], true ) ) {
			$status = 'published';
		}

		return new self(
			(int) ( $data['id'] ?? 0 ),
			sanitize_text_field( $data['name'] ),
			sanitize_title( $slug ),
			sanitize_textarea_field( $data['description'] ?? '' ),
			(int) ( $data['count'] ?? 0 ),
			! empty( $data['color'] ) ? sanitize_hex_color( $data['color'] ) : null,
			! empty( $data['icon'] ) ? sanitize_text_field( $data['icon'] ) : null,
			$data['created_at'] ?? '',
			$status,
			(bool) ( $data['featured'] ?? false )
		);
	}

	/**
	 * Get products with this tag
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array<int, \AffiliateProductShowcase\Models\Product> Products with this tag
	 * @since 1.0.0
	 */
	public function get_products( array $args = [] ): array {
		$default_args = [
			'post_type'      => Constants::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'tax_query'      => [
				[
					'taxonomy' => Constants::TAX_TAG,
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