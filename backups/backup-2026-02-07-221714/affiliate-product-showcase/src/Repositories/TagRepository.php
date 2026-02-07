<?php
/**
 * Tag Repository
 *
 * Handles database operations for tag taxonomy including
 * CRUD operations, querying, and metadata management.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Factories\TagFactory;
use AffiliateProductShowcase\Models\Tag;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Exceptions\PluginException;
use WP_Error;

/**
 * Tag Repository
 *
 * Handles database operations for tag taxonomy including
 * CRUD operations, querying, and metadata management.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 * @author Development Team
 */
final class TagRepository {
	/**
	 * Get a tag by ID
	 *
	 * @param int $tag_id Tag ID (term_id)
	 * @return Tag|null Tag instance or null if not found
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tag = $repository->find(1);
	 * if ($tag) {
	 *     echo $tag->name;
	 * }
	 * ```
	 */
	public function find( int $tag_id ): ?Tag {
		if ( $tag_id <= 0 ) {
			return null;
		}

		// Ensure taxonomy is registered
		if ( ! taxonomy_exists( Constants::TAX_TAG ) ) {
			error_log( sprintf( '[APS] Taxonomy %s not registered', Constants::TAX_TAG ) );
			return null;
		}

		$term = get_term( $tag_id, Constants::TAX_TAG );

		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		return TagFactory::from_wp_term( $term );
	}

	/**
	 * Get a tag by slug
	 *
	 * @param string $slug Tag slug
	 * @return Tag|null Tag instance or null if not found
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tag = $repository->find_by_slug('sale');
	 * ```
	 */
	public function find_by_slug( string $slug ): ?Tag {
		$term = get_term_by( 'slug', $slug, Constants::TAX_TAG );

		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		return TagFactory::from_wp_term( $term );
	}

	/**
	 * Get all tags
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array<int, Tag> Array of Tag instances
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tags = $repository->all(['hide_empty' => false]);
	 * ```
	 */
	public function all( array $args = [] ): array {
		$default_args = [
			'taxonomy'   => Constants::TAX_TAG,
			'hide_empty' => false,
		];

		$args = wp_parse_args( $args, $default_args );

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		return TagFactory::from_wp_terms( $terms );
	}

	/**
	 * Get tags with pagination
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array{tags: array<int, Tag>, total: int, pages: int} Paginated result
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
			'taxonomy'   => Constants::TAX_TAG,
			'hide_empty' => false,
			'number'     => 10,
			'offset'     => 0,
		];

		$args = wp_parse_args( $args, $default_args );

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [
				'tags'  => [],
				'total'  => 0,
				'pages'   => 0,
			];
		}

		// Get total count
		$count_args = $args;
		unset( $count_args['number'], $count_args['offset'] );
		$total = wp_count_terms( Constants::TAX_TAG, $count_args );

		if ( is_wp_error( $total ) ) {
			$total = 0;
		}

		$per_page = (int) $args['number'];
		$pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 0;

		return [
			'tags'  => TagFactory::from_wp_terms( $terms ),
			'total'  => $total,
			'pages'   => $pages,
		];
	}

	/**
	 * Create a new tag
	 *
	 * @param Tag $tag Tag instance to create
	 * @return Tag Created tag instance
	 * @throws PluginException If tag creation fails
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tag = new Tag(
	 *     0,
	 *     'Sale',
	 *     'sale',
	 *     'Products on sale',
	 *     0,
	 *     'dashicons-tag',
	 *     'published',
	 *     false
	 * );
	 * $created = $repository->create($tag);
	 * ```
	 */
	public function create( Tag $tag ): Tag {
		$result = wp_insert_term(
			$tag->name,
			Constants::TAX_TAG,
			[
				'slug'        => $tag->slug,
				'description' => $tag->description,
			]
		);

		if ( is_wp_error( $result ) ) {
			throw new PluginException(
				sprintf(
					'Failed to create tag: %s',
					$result->get_error_message()
				)
			);
		}

		$term_id = (int) $result['term_id'];

		// Save metadata
		$this->save_metadata( $term_id, $tag );

		// Return tag with ID
		return $this->find( $term_id );
	}

	/**
	 * Update an existing tag
	 *
	 * @param Tag $tag Tag instance to update
	 * @return Tag Updated tag instance
	 * @throws PluginException If tag update fails
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $tag = $repository->find(1);
	 * $tag = new Tag(
	 *     1,
	 *     'Sale Updated',
	 *     'sale-updated',
	 *     'Updated description',
	 *     0,
	 *     'dashicons-tag-updated',
	 *     'published',
	 *     true
	 * );
	 * $updated = $repository->update($tag);
	 * ```
	 */
	public function update( Tag $tag ): Tag {
		if ( $tag->id <= 0 ) {
			throw new PluginException( 'Tag ID is required for update.' );
		}

		$result = wp_update_term(
			$tag->id,
			Constants::TAX_TAG,
			[
				'name'        => $tag->name,
				'slug'        => $tag->slug,
				'description' => $tag->description,
			]
		);

		if ( is_wp_error( $result ) ) {
			throw new PluginException(
				sprintf(
					'Failed to update tag: %s',
					$result->get_error_message()
				)
			);
		}

		// Save metadata
		$this->save_metadata( $tag->id, $tag );

		return $this->find( $tag->id );
	}

	/**
	 * Delete a tag
	 *
	 * @param int $tag_id Tag ID to delete
	 * @return bool True on success
	 * @throws PluginException If deletion fails
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $repository->delete(1);
	 * ```
	 */
	public function delete( int $tag_id ): bool {
		if ( $tag_id <= 0 ) {
			throw new PluginException( 'Tag ID is required.' );
		}

		$tag = $this->find( $tag_id );
		if ( ! $tag ) {
			throw new PluginException( 'Tag not found.' );
		}

		$result = wp_delete_term( $tag_id, Constants::TAX_TAG );

		if ( is_wp_error( $result ) ) {
			throw new PluginException(
				sprintf(
					'Failed to delete tag: %s',
					$result->get_error_message()
				)
			);
		}

		// Delete metadata
		$this->delete_metadata( $tag_id );

		return true;
	}

	/**
	 * Count tags
	 *
	 * @return int Total number of tags
	 * @since 1.0.0
	 */
	public function count(): int {
		$count = wp_count_terms( Constants::TAX_TAG, [ 'hide_empty' => false ] );

		return is_wp_error( $count ) ? 0 : (int) $count;
	}

	/**
	 * Search tags by name or description
	 *
	 * @param string $search Search term
	 * @return array<int, Tag> Matching tags
	 * @since 1.0.0
	 *
	 * @example
	 * ```php
	 * $results = $repository->search('sale');
	 * ```
	 */
	public function search( string $search ): array {
		return $this->all( [
			'search' => $search,
		] );
	}

	/**
	 * Save tag metadata
	 *
	 * @param int $term_id Term ID
	 * @param Tag $tag Tag instance
	 * @return void
	 * @since 1.0.0
	 */
	private function save_metadata( int $term_id, Tag $tag ): void {
		// Icon
		if ( $tag->icon ) {
			update_term_meta( $term_id, '_aps_tag_icon', $tag->icon );
		} else {
			delete_term_meta( $term_id, '_aps_tag_icon' );
		}

		// Status (TRUE HYBRID: use term meta)
		update_term_meta( $term_id, '_aps_tag_status', $tag->status );

		// Featured (TRUE HYBRID: use term meta)
		update_term_meta( $term_id, '_aps_tag_featured', $tag->featured ? '1' : '0' );
	}

	/**
	 * Delete tag metadata
	 *
	 * @param int $term_id Term ID
	 * @return void
	 * @since 1.0.0
	 */
	private function delete_metadata( int $term_id ): void {
		delete_term_meta( $term_id, '_aps_tag_icon' );
		delete_term_meta( $term_id, '_aps_tag_status' );
		delete_term_meta( $term_id, '_aps_tag_featured' );
	}

	/**
	 * Set tag visibility status (TRUE HYBRID: use term meta)
	 *
	 * @param int $tag_id Tag ID
	 * @param string $status Status: 'published', 'draft', or 'trash'
	 * @return bool True if status set successfully
	 * @since 1.0.0
	 */
	public function set_visibility( int $tag_id, string $status ): bool {
		return update_term_meta( $tag_id, '_aps_tag_status', $status ) !== false;
	}

	/**
	 * Set tag featured flag (TRUE HYBRID: use term meta)
	 *
	 * @param int $tag_id Tag ID
	 * @param bool $featured Whether tag is featured
	 * @return bool True if flag set successfully
	 * @since 1.0.0
	 */
	public function set_featured( int $tag_id, bool $featured ): bool {
		return update_term_meta( $tag_id, '_aps_tag_featured', $featured ? '1' : '0' ) !== false;
	}

	/**
	 * Change status for multiple tags (TRUE HYBRID: use term meta)
	 *
	 * @param array<int, int> $tag_ids Array of tag IDs
	 * @param string $status Status: 'published', 'draft', or 'trash'
	 * @return int Number of tags updated
	 * @since 1.0.0
	 */
	public function change_status( array $tag_ids, string $status ): int {
		$count = 0;
		foreach ( $tag_ids as $tag_id ) {
			if ( $this->set_visibility( $tag_id, $status ) ) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Change featured flag for multiple tags (TRUE HYBRID: use term meta)
	 *
	 * @param array<int, int> $tag_ids Array of tag IDs
	 * @param bool $featured Whether tags are featured
	 * @return int Number of tags updated
	 * @since 1.0.0
	 */
	public function change_featured( array $tag_ids, bool $featured ): int {
		$count = 0;
		foreach ( $tag_ids as $tag_id ) {
			if ( $this->set_featured( $tag_id, $featured ) ) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Get tags by status (TRUE HYBRID: use term meta)
	 *
	 * @param string $status Status: 'published', 'draft', or 'trash'
	 * @return array<int, Tag> Tags with specified status
	 * @since 1.0.0
	 */
	public function get_by_status( string $status ): array {
		$args = [
			'taxonomy'   => Constants::TAX_TAG,
			'hide_empty' => false,
			'meta_query' => [
				[
					'key'     => '_aps_tag_status',
					'value'   => $status,
					'compare' => '=',
				],
			],
		];

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		return TagFactory::from_wp_terms( $terms );
	}
}
