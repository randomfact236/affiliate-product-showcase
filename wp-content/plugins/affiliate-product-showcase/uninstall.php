<?php
/**
 * Pragmatic Plugin Uninstaller - Affiliate Product Showcase
 * Philosophy: Delete and Forget – fast, robust, zero drama
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Configurable constants (can be defined in wp-config.php)
defined( 'APS_UNINSTALL_REMOVE_ALL_DATA' )     or define( 'APS_UNINSTALL_REMOVE_ALL_DATA', false );
defined( 'APS_UNINSTALL_FORCE_DELETE_CONTENT' ) or define( 'APS_UNINSTALL_FORCE_DELETE_CONTENT', false );
defined( 'APS_UNINSTALL_BATCH_SIZE' )           or define( 'APS_UNINSTALL_BATCH_SIZE', 500 );

// Resource limits for large sites/networks
@set_time_limit( 600 );
@ini_set( 'memory_limit', '512M' );

// ============================================================================
// Minimal debug logging (only active when WP_DEBUG is true)
// ============================================================================
function aps_uninstall_log( $message ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( '[APS Uninstall] ' . $message );
	}
}

// ============================================================================
// Cleanup functions
// ============================================================================

function aps_cleanup_options() {
	global $wpdb;
	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $wpdb->options 
			 WHERE option_name LIKE %s 
				OR option_name LIKE %s 
				OR option_name LIKE %s",
			'aps_%',
			'_transient_aps_%',
			'_transient_timeout_aps_%'
		)
	);
	aps_uninstall_log( "Removed {$deleted} options/transients." );
}

function aps_cleanup_tables() {
	global $wpdb;
	$tables = [
		$wpdb->prefix . 'aps_products',
		$wpdb->prefix . 'aps_categories',
		$wpdb->prefix . 'aps_affiliates',
		$wpdb->prefix . 'aps_stats',
	];
	foreach ( $tables as $table ) {
		$result = $wpdb->query( "DROP TABLE IF EXISTS `$table`" );
		if ( $result === false ) {
			aps_uninstall_log( "Failed to drop table: {$table}" );
		}
	}
	aps_uninstall_log( 'Tables dropped.' );
}

function aps_cleanup_content() {
	global $wpdb;

	$post_types = [ 'aps_product', 'aps_affiliate' ];
	$taxonomies = [ 'aps_category', 'aps_tag' ];

	// Safety re-registration (multisite context)
	foreach ( $post_types as $pt ) {
		register_post_type( $pt, [ 'public' => false ] );
	}
	foreach ( $taxonomies as $tax ) {
		register_taxonomy( $tax, $post_types, [ 'public' => false ] );
	}

	// Delete terms first (before deleting posts)
	foreach ( $taxonomies as $tax ) {
		$terms = get_terms( [
			'taxonomy'   => $tax,
			'hide_empty' => false,
			'fields'     => 'ids',
		] );

		if ( is_wp_error( $terms ) ) continue;

		foreach ( $terms as $term_id ) {
			wp_delete_term( $term_id, $tax );
		}

		aps_uninstall_log( "Taxonomy '{$tax}': " . count( $terms ) . ' terms deleted.' );
	}

	// Batch delete posts
	foreach ( $post_types as $pt ) {
		$offset = 0;
		$total = 0;
		$limit = absint( APS_UNINSTALL_BATCH_SIZE );

		while ( true ) {
			$safe_offset = absint( $offset );

			// Properly escape LIMIT and OFFSET using sprintf() with absint()
			$ids = $wpdb->get_col( $wpdb->prepare(
				sprintf(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s LIMIT %d OFFSET %d",
					absint( $limit ),
					absint( $safe_offset )
				),
				$pt
			));

			if ( empty( $ids ) ) break;

			foreach ( $ids as $id ) {
				$result = wp_delete_post( (int) $id, APS_UNINSTALL_FORCE_DELETE_CONTENT );
				if ( $result ) {
					$total++;
				}
			}

			$offset += $limit;

			if ( function_exists( 'gc_collect_cycles' ) ) {
				gc_collect_cycles();
			}
		}

		$action = APS_UNINSTALL_FORCE_DELETE_CONTENT ? 'deleted' : 'trashed';
		aps_uninstall_log( "Post type '{$pt}': {$total} posts {$action}." );
	}

	// Clean up old 'aps_categories' post meta (migration cleanup)
	$meta_deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
			'aps_categories'
		)
	);
	aps_uninstall_log( "Deleted {$meta_deleted} 'aps_categories' meta entries (migration cleanup)." );
}

function aps_cleanup_user_data() {
	global $wpdb;

	$deleted = $wpdb->query(
		$wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s", 'aps_%' )
	);

	$caps = [
		'manage_aps_products',
		'edit_aps_products',
		'delete_aps_products',
	];

	foreach ( wp_roles()->roles as $role_name => $_ ) {
		$role = get_role( $role_name );
		if ( $role ) {
			foreach ( $caps as $cap ) {
				$role->remove_cap( $cap );
			}
		}
	}

	aps_uninstall_log( "Removed {$deleted} user meta entries and capabilities." );
}

function aps_cleanup_files() {
	$base = wp_upload_dir()['basedir'];
	$dirs = [
		trailingslashit( $base ) . 'affiliate-product-showcase',
		WP_CONTENT_DIR . '/cache/aps/',
	];

	// Prefer WP_Filesystem for portability; fall back to direct removal.
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	WP_Filesystem();
	global $wp_filesystem;

	foreach ( $dirs as $dir ) {
		if ( is_dir( $dir ) ) {
			// Try WP_Filesystem recursive removal first
			if ( isset( $wp_filesystem ) && method_exists( $wp_filesystem, 'rmdir' ) ) {
				$result = $wp_filesystem->rmdir( untrailingslashit( $dir ), true );
				if ( $result ) {
					aps_uninstall_log( "Removed directory: {$dir}" );
					continue;
				}
				aps_uninstall_log( "WP_Filesystem failed to remove directory: {$dir}, falling back." );
			}

			// Fallback: recursive iterator (best-effort)
			try {
				$it = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
					RecursiveIteratorIterator::CHILD_FIRST
				);
				foreach ( $it as $file ) {
					if ( $file->isDir() ) {
						@rmdir( $file->getPathname() );
					} else {
						@unlink( $file->getPathname() );
					}
				}
				@rmdir( $dir );
				aps_uninstall_log( "Removed directory (fallback): {$dir}" );
			} catch ( \Exception $e ) {
				aps_uninstall_log( "Failed to delete directory {$dir}: " . $e->getMessage() );
			}
		}
	}
}

function aps_cleanup_cron() {
	wp_clear_scheduled_hook( 'aps_daily_sync' );
	wp_clear_scheduled_hook( 'aps_hourly_cleanup' );
	wp_clear_scheduled_hook( 'aps_weekly_report' );
	aps_uninstall_log( 'Cron jobs cleared.' );
}

function aps_verify_cleanup() {
	global $wpdb;
    
	$remaining_options = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s", 'aps_%' )
	);
    
	$remaining_posts = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type LIKE %s", 'aps_%' ) );
    
	aps_uninstall_log( "Verification: {$remaining_options} options, {$remaining_posts} posts remaining." );
    
	if ( $remaining_options > 0 || $remaining_posts > 0 ) {
		aps_uninstall_log( 'WARNING: Some data may not have been removed completely.' );
	}
}

// ============================================================================
// Main flow
// ============================================================================

if ( APS_UNINSTALL_REMOVE_ALL_DATA ) {
	aps_uninstall_log( 'Starting uninstall...' );

	try {
		if ( is_multisite() ) {
			$sites = get_sites( [ 'fields' => 'ids' ] );
			aps_uninstall_log( 'Processing ' . count( $sites ) . ' sites...' );
            
			foreach ( $sites as $site_id ) {
				switch_to_blog( $site_id );
                
				aps_cleanup_options();
				aps_cleanup_tables();
				aps_cleanup_content();
				aps_cleanup_user_data();
				aps_cleanup_files();
				aps_cleanup_cron();
                
				restore_current_blog();
			}
            
			delete_site_option( 'aps_network_settings' );
		} else {
			aps_cleanup_options();
			aps_cleanup_tables();
			aps_cleanup_content();
			aps_cleanup_user_data();
			aps_cleanup_files();
			aps_cleanup_cron();
		}

		// Verify cleanup
		aps_verify_cleanup();

		flush_rewrite_rules( false );
		wp_cache_flush();

		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( 'options' );
		}

		aps_uninstall_log( 'Uninstall completed successfully.' );
        
	} catch ( \Throwable $e ) {
		aps_uninstall_log( 'FATAL ERROR: ' . $e->getMessage() );
	}
} else {
	aps_uninstall_log( 'Data preservation enabled. Cleanup skipped.' );
}
