<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Activator;

final class Migrations {
	/**
	 * Run database migrations
	 *
	 * @return void
	 */
	public static function run(): void {
		$current_version = get_option( 'aps_db_version', '1.0.0' );

		// Add original_price field support (v1.1.0)
		if ( version_compare( $current_version, '1.1.0', '<' ) ) {
			self::migrate_to_v110();
		}

		// Future migrations can be added here
		// if ( version_compare( $current_version, '1.2.0', '<' ) ) {
		//     self::migrate_to_v120();
		// }
	}

	/**
	 * Migration to version 1.1.0 - Add original_price field
	 *
	 * This migration doesn't require database schema changes
	 * since we're using post_meta. We just update the version
	 * to indicate the feature is available.
	 *
	 * @return void
	 */
	private static function migrate_to_v110(): void {
		// No schema changes needed - using post_meta
		// The original_price field is stored in aps_original_price meta key
		
		// Update version
		update_option( 'aps_db_version', '1.1.0' );
		
		// Log migration
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[APS] Database migrated to version 1.1.0 - Added original_price field support' );
		}
	}

	/**
	 * Get current database version
	 *
	 * @return string Current version
	 */
	public static function get_version(): string {
		return get_option( 'aps_db_version', '1.0.0' );
	}

	/**
	 * Check if migration is needed
	 *
	 * @return bool True if migration is needed
	 */
	public static function needs_migration(): bool {
		return version_compare( self::get_version(), '1.1.0', '<' );
	}
}
