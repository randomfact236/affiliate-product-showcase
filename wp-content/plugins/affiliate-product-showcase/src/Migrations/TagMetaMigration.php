<?php
/**
 * Tag Metadata Migration
 *
 * Migrates tag data from auxiliary taxonomies (aps_tag_visibility, aps_tag_flags)
 * to term meta (_aps_tag_status, _aps_tag_featured).
 * This migration implements the TRUE HYBRID approach.
 *
 * @package AffiliateProductShowcase\Migrations
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Migrations;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Tag Metadata Migration
 *
 * Migrates tag data from auxiliary taxonomies to term meta.
 * After migration, auxiliary taxonomies can be safely removed.
 *
 * @package AffiliateProductShowcase\Migrations
 * @since 1.0.0
 * @author Development Team
 */
final class TagMetaMigration {
	/**
	 * Migration version
	 */
	public const VERSION = '1.0.0';

	/**
	 * Migration option key
	 */
	public const MIGRATION_OPTION = 'aps_tag_meta_migration_version';

	/**
	 * Run migration
	 *
	 * Migrates all tags from auxiliary taxonomies to term meta.
	 * Should only be run once per version.
	 *
	 * @return bool True if migration was performed
	 * @since 1.0.0
	 */
	public static function run(): bool {
		// Check if migration already ran
		$migrated_version = get_option(self::MIGRATION_OPTION);
		
		if (version_compare($migrated_version, self::VERSION, '>=')) {
			// Migration already performed
			return false;
		}

		// Migrate status from aps_tag_visibility taxonomy
		$status_migrated = self::migrate_status();
		
		// Migrate featured flag from aps_tag_flags taxonomy
		$featured_migrated = self::migrate_featured();
		
		// Mark migration as complete
		if ($status_migrated || $featured_migrated) {
			update_option(self::MIGRATION_OPTION, self::VERSION);
			
			// Log migration
			error_log(sprintf(
				'[APS] Tag meta migration completed at %s (version %s)',
				current_time('mysql'),
				self::VERSION
			));
			
			return true;
		}

		return false;
	}

	/**
	 * Migrate status from auxiliary taxonomy to term meta
	 *
	 * Reads aps_tag_visibility taxonomy terms and sets term meta.
	 * Status mapping: 'published' → 'published', 'draft' → 'draft', 'trash' → 'trash'
	 *
	 * @return bool True if any tags were migrated
	 * @since 1.0.0
	 */
	private static function migrate_status(): bool {
		if (!taxonomy_exists('aps_tag_visibility')) {
			// Auxiliary taxonomy doesn't exist, nothing to migrate
			return false;
		}

		// Get all visibility terms
		$visibility_terms = get_terms([
			'taxonomy' => 'aps_tag_visibility',
			'hide_empty' => false,
		]);

		if (is_wp_error($visibility_terms) || empty($visibility_terms)) {
			// No visibility terms to migrate
			return false;
		}

		$migrated_count = 0;

		// For each visibility term, find all tags with that visibility
		foreach ($visibility_terms as $visibility_term) {
			// Get all tags with this visibility term
			// Use term relationship query via term_taxonomy_id
			global $wpdb;
			$tag_ids = $wpdb->get_col($wpdb->prepare(
				"SELECT t.term_id 
				FROM {$wpdb->terms} t
				INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
				INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
				INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr.term_taxonomy_id = tt2.term_taxonomy_id
				WHERE tt2.taxonomy = 'aps_tag'
				AND tt.taxonomy = 'aps_tag_visibility'
				AND t.term_id = %d",
				$visibility_term->term_id
			));

			if (empty($tag_ids)) {
				continue;
			}

			$tags = [];
			foreach ($tag_ids as $tag_id) {
				$tag = get_term($tag_id, 'aps_tag');
				if ($tag && !is_wp_error($tag)) {
					$tags[] = $tag;
				}
			}

			// Set status for each tag
			foreach ($tags as $tag) {
				// Skip if already has term meta status
				$existing_status = get_term_meta($tag->term_id, '_aps_tag_status', true);
				
				if ($existing_status) {
					// Already migrated or manually set
					continue;
				}

				// Set status from visibility term slug
				$status = $visibility_term->slug;
				
				// Validate status
				if (in_array($status, ['published', 'draft', 'trash'], true)) {
					update_term_meta($tag->term_id, '_aps_tag_status', $status);
					$migrated_count++;
					
					error_log(sprintf(
						'[APS] Migrated status for tag ID %d: %s',
						$tag->term_id,
						$status
					));
				}
			}
		}

		return $migrated_count > 0;
	}

	/**
	 * Migrate featured flag from auxiliary taxonomy to term meta
	 *
	 * Reads aps_tag_flags taxonomy terms and sets term meta.
	 * Featured mapping: 'featured' → true, 'none' → false
	 *
	 * @return bool True if any tags were migrated
	 * @since 1.0.0
	 */
	private static function migrate_featured(): bool {
		if (!taxonomy_exists('aps_tag_flags')) {
			// Auxiliary taxonomy doesn't exist, nothing to migrate
			return false;
		}

		// Get all flag terms
		$flag_terms = get_terms([
			'taxonomy' => 'aps_tag_flags',
			'hide_empty' => false,
		]);

		if (is_wp_error($flag_terms) || empty($flag_terms)) {
			// No flag terms to migrate
			return false;
		}

		$migrated_count = 0;

		// For each flag term, find all tags with that flag
		foreach ($flag_terms as $flag_term) {
			// Get all tags with this flag term
			// Use term relationship query via term_taxonomy_id
			global $wpdb;
			$tag_ids = $wpdb->get_col($wpdb->prepare(
				"SELECT t.term_id 
				FROM {$wpdb->terms} t
				INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
				INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
				INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr.term_taxonomy_id = tt2.term_taxonomy_id
				WHERE tt2.taxonomy = 'aps_tag'
				AND tt.taxonomy = 'aps_tag_flags'
				AND t.term_id = %d",
				$flag_term->term_id
			));

			if (empty($tag_ids)) {
				continue;
			}

			$tags = [];
			foreach ($tag_ids as $tag_id) {
				$tag = get_term($tag_id, 'aps_tag');
				if ($tag && !is_wp_error($tag)) {
					$tags[] = $tag;
				}
			}

			// Set featured flag for each tag
			foreach ($tags as $tag) {
				// Skip if already has term meta featured
				$existing_featured = get_term_meta($tag->term_id, '_aps_tag_featured', true);
				
				if ($existing_featured !== '') {
					// Already migrated or manually set
					continue;
				}

				// Set featured from flag term slug
				$is_featured = ($flag_term->slug === 'featured');
				update_term_meta($tag->term_id, '_aps_tag_featured', $is_featured ? '1' : '0');
				$migrated_count++;
				
				error_log(sprintf(
					'[APS] Migrated featured for tag ID %d: %s',
					$tag->term_id,
					$is_featured ? 'true' : 'false'
				));
			}
		}

		return $migrated_count > 0;
	}

	/**
	 * Rollback migration
	 *
	 * Removes term meta for status and featured.
	 * WARNING: This will lose all status/featured data!
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function rollback(): void {
		// Get all tags
		$tags = get_terms([
			'taxonomy' => 'aps_tag',
			'hide_empty' => false,
		]);

		if (is_wp_error($tags) || empty($tags)) {
			return;
		}

		// Remove term meta for all tags
		foreach ($tags as $tag) {
			delete_term_meta($tag->term_id, '_aps_tag_status');
			delete_term_meta($tag->term_id, '_aps_tag_featured');
		}

		// Remove migration marker
		delete_option(self::MIGRATION_OPTION);
		
		error_log('[APS] Tag meta migration rolled back');
	}

	/**
	 * Get migration status
	 *
	 * @return array{migrated: bool, version: string|null}
	 * @since 1.0.0
	 */
	public static function get_status(): array {
		$version = get_option(self::MIGRATION_OPTION);
		
		return [
			'migrated' => version !== null && version_compare($version, self::VERSION, '>='),
			'version' => $version,
		];
	}
}