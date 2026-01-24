<?php
/**
 * Tag Factory
 *
 * Factory class for creating Tag instances from various sources.
 * Handles conversion between different data formats and Tag model.
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Factories;

use AffiliateProductShowcase\Models\Tag;

/**
 * Tag Factory
 *
 * Factory class for creating Tag instances from various sources.
 * Handles conversion between different data formats and Tag model.
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 * @author Development Team
 */
final class TagFactory {
	/**
	 * Create Tag from WP_Term
	 *
	 * @param \WP_Term $term WordPress term object
	 * @return Tag Tag instance
	 * @throws \InvalidArgumentException If term is not a tag
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $term = get_term(1, 'aps_tag');
	 * $tag = TagFactory::from_wp_term($term);
	 * ```
	 */
	public static function from_wp_term( \WP_Term $term ): Tag {
		return Tag::from_wp_term( $term );
	}

	/**
	 * Create Tag from array
	 *
	 * @param array<string, mixed> $data Tag data
	 * @return Tag Tag instance
	 * @throws \InvalidArgumentException If required fields are missing
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tag = TagFactory::from_array([
	 *     'name' => 'Sale',
	 *     'slug' => 'sale',
	 *     'color' => '#ff0000',
	 * ]);
	 * ```
	 */
	public static function from_array( array $data ): Tag {
		return Tag::from_array( $data );
	}

	/**
	 * Create Tags from array of arrays
	 *
	 * @param array<int, array<string, mixed>> $tags_data Array of tag data
	 * @return array<int, Tag> Array of Tag instances
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tags = TagFactory::from_arrays([
	 *     ['name' => 'Sale', 'slug' => 'sale'],
	 *     ['name' => 'New', 'slug' => 'new'],
	 * ]);
	 * ```
	 */
	public static function from_arrays( array $tags_data ): array {
		$tags = [];

		foreach ( $tags_data as $tag_data ) {
			try {
				$tags[] = self::from_array( $tag_data );
			} catch ( \InvalidArgumentException $e ) {
				// Skip invalid tags
				continue;
			}
		}

		return $tags;
	}

	/**
	 * Create Tags from array of WP_Term objects
	 *
	 * @param array<int, \WP_Term> $terms Array of WordPress term objects
	 * @return array<int, Tag> Array of Tag instances
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $terms = get_terms(['taxonomy' => 'aps_tag']);
	 * $tags = TagFactory::from_wp_terms($terms);
	 * ```
	 */
	public static function from_wp_terms( array $terms ): array {
		$tags = [];

		foreach ( $terms as $term ) {
			try {
				$tags[] = self::from_wp_term( $term );
			} catch ( \InvalidArgumentException $e ) {
				// Skip invalid terms
				continue;
			}
		}

		return $tags;
	}

	/**
	 * Create Tag from database row
	 *
	 * @param object $row Database row object
	 * @return Tag Tag instance
	 * @throws \InvalidArgumentException If required fields are missing
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * global $wpdb;
	 * $row = $wpdb->get_row("SELECT * FROM $wpdb->terms WHERE term_id = 1");
	 * $tag = TagFactory::from_db_row($row);
	 * ```
	 */
	public static function from_db_row( object $row ): Tag {
		if ( empty( $row->term_id ) || empty( $row->name ) ) {
			throw new \InvalidArgumentException( 'Database row must contain term_id and name.' );
		}

		// Get tag metadata
		$color = get_term_meta( (int) $row->term_id, '_aps_tag_color', true ) ?: null;
		$icon = get_term_meta( (int) $row->term_id, '_aps_tag_icon', true ) ?: null;

		return new Tag(
			(int) $row->term_id,
			$row->name,
			$row->slug ?? sanitize_title( $row->name ),
			$row->description ?? '',
			(int) ( $row->count ?? 0 ),
			$color,
			$icon,
			$row->term_group ? date( 'Y-m-d H:i:s', (int) $row->term_group ) : current_time( 'mysql' )
		);
	}

	/**
	 * Sort tags by name (alphabetical)
	 *
	 * @param array<int, Tag> $tags Tags to sort
	 * @param string $order 'ASC' or 'DESC'
	 * @return array<int, Tag> Sorted tags
	 * @since 1.0.0
	 */
	public static function sort_by_name( array $tags, string $order = 'ASC' ): array {
		$tags = $tags;
		
		usort( $tags, function( $a, $b ) use ( $order ) {
			$compare = strcasecmp( $a->name, $b->name );
			return $order === 'ASC' ? $compare : -$compare;
		} );

		return $tags;
	}

	/**
	 * Sort tags by count (most products first)
	 *
	 * @param array<int, Tag> $tags Tags to sort
	 * @param string $order 'ASC' or 'DESC'
	 * @return array<int, Tag> Sorted tags
	 * @since 1.0.0
	 */
	public static function sort_by_count( array $tags, string $order = 'DESC' ): array {
		$tags = $tags;
		
		usort( $tags, function( $a, $b ) use ( $order ) {
			$compare = $a->count <=> $b->count;
			return $order === 'DESC' ? -$compare : $compare;
		} );

		return $tags;
	}
}