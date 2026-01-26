<?php
/**
 * Taxonomy Fields Abstract Base Class
 *
 * Provides shared functionality for Category, Tag, and Ribbon taxonomies.
 * Child classes only need to implement taxonomy-specific features.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Abstract Base Class for Taxonomy Fields
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */
abstract class TaxonomyFieldsAbstract {
	/**
	 * Get taxonomy name (must be implemented by child classes)
	 *
	 * @return string Taxonomy name (e.g., 'aps_category')
	 */
	abstract protected function get_taxonomy(): string;
	
	/**
	 * Get taxonomy label (must be implemented by child classes)
	 *
	 * @return string Human-readable label (e.g., 'Category')
	 */
	abstract protected function get_taxonomy_label(): string;
	
	/**
	 * Get meta key prefix
	 *
	 * @return string Meta key prefix (e.g., '_aps_category_')
	 */
	protected function get_meta_prefix(): string {
		$taxonomy = $this->get_taxonomy();
		// Remove 'aps_' prefix from taxonomy name to avoid double prefix
		// e.g., 'aps_category' becomes 'category', so '_aps_category_'
		$clean_taxonomy = str_replace( 'aps_', '', $taxonomy );
		return '_aps_' . $clean_taxonomy . '_';
	}
	
	/**
	 * Get nonce action name
	 *
	 * @param string $action Action suffix
	 * @return string Nonce action name
	 */
	protected function get_nonce_action( string $action ): string {
		return 'aps_' . $this->get_taxonomy() . '_' . $action;
	}
	
	/**
	 * Render taxonomy-specific fields
	 * 
	 * Child classes implement their custom fields here.
	 *
	 * @param int $term_id Term ID
	 * @return void
	 */
	abstract protected function render_taxonomy_specific_fields( int $term_id ): void;
	
	/**
	 * Save taxonomy-specific fields
	 * 
	 * Child classes implement their custom save logic here.
	 *
	 * @param int $term_id Term ID
	 * @return void
	 */
	abstract protected function save_taxonomy_specific_fields( int $term_id ): void;
	
	/**
	 * Initialize all hooks
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function init(): void {
		// Form hooks
		add_action( $this->get_taxonomy() . '_add_form_fields', [ $this, 'render_add_fields' ] );
		add_action( $this->get_taxonomy() . '_edit_form_fields', [ $this, 'render_edit_fields' ] );
		add_action( 'created_' . $this->get_taxonomy(), [ $this, 'save_fields' ], 10, 2 );
		add_action( 'edited_' . $this->get_taxonomy(), [ $this, 'save_fields' ], 10, 2 );
		
		// Table hooks
		add_filter( 'manage_edit-' . $this->get_taxonomy() . '_columns', [ $this, 'add_custom_columns' ] );
		add_filter( 'manage_' . $this->get_taxonomy() . '_custom_column', [ $this, 'render_custom_columns' ], 10, 3 );
		
		// Status hooks
		add_filter( 'views_edit-' . $this->get_taxonomy(), [ $this, 'add_status_view_tabs' ] );
		add_filter( 'get_terms', [ $this, 'filter_terms_by_status' ], 10, 3 );
		
		// Bulk action hooks
		add_filter( 'bulk_actions-edit-' . $this->get_taxonomy(), [ $this, 'add_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-edit-' . $this->get_taxonomy(), [ $this, 'handle_bulk_actions' ], 10, 3 );
		add_action( 'admin_notices', [ $this, 'display_bulk_action_notices' ] );
		
		// AJAX hooks
		add_action( 'wp_ajax_aps_toggle_' . $this->get_taxonomy() . '_status', [ $this, 'ajax_toggle_term_status' ] );
		add_action( 'wp_ajax_aps_' . $this->get_taxonomy() . '_row_action', [ $this, 'ajax_term_row_action' ] );
		
		// Row actions
		add_filter( 'tag_row_actions', [ $this, 'add_term_row_actions' ], 10, 2 );
		add_action( 'admin_post_aps_' . $this->get_taxonomy() . '_row_action', [ $this, 'handle_term_row_action' ] );
		
		// Default protection
		add_action( 'pre_delete_term', [ $this, 'protect_default_term' ], 10, 2 );
		
		// Assets
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_footer-term.php', [ $this, 'add_cancel_button_to_term_edit_screen' ] );
	}
	
	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook_suffix Current admin page hook
	 * @return void
	 * @since 2.0.0
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		$screen = get_current_screen();
		
		if ( $screen && $screen->taxonomy === $this->get_taxonomy() ) {
			// Enqueue styles
			$css_file = 'assets/css/admin-' . $this->get_taxonomy() . '.css';
			if ( file_exists( Constants::dirPath() . $css_file ) ) {
				wp_enqueue_style(
					'aps-admin-' . $this->get_taxonomy(),
					Constants::assetUrl( $css_file ),
					[],
					Constants::VERSION
				);
			}
			
			// Enqueue custom JavaScript for taxonomy management
			$js_file = 'assets/js/admin-' . $this->get_taxonomy() . '.js';
			if ( file_exists( Constants::dirPath() . $js_file ) ) {
				wp_enqueue_script(
					'aps-admin-' . $this->get_taxonomy() . '-js',
					Constants::assetUrl( $js_file ),
					[ 'jquery' ],
					Constants::VERSION,
					true
				);

				wp_localize_script( 'aps-admin-' . $this->get_taxonomy() . '-js', 'aps_admin_vars', [
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'nonce'           => wp_create_nonce( $this->get_nonce_action( 'toggle_status' ) ),
					'row_action_nonce'=> wp_create_nonce( $this->get_nonce_action( 'row_action' ) ),
					'success_text'     => esc_html__( $this->get_taxonomy_label() . ' status updated successfully.', 'affiliate-product-showcase' ),
					'error_text'       => esc_html__( 'An error occurred. Please try again.', 'affiliate-product-showcase' ),
				] );
			}
		}
	}
	
	/**
	 * Render fields in add form
	 *
	 * @param string $taxonomy Taxonomy name
	 */
	public function render_add_fields( string $taxonomy ): void {
		$this->render_taxonomy_specific_fields( 0 );
		wp_nonce_field( $this->get_nonce_action( 'fields' ), $this->get_nonce_action( 'fields_nonce' ) );
	}
	
	/**
	 * Render fields in edit form
	 *
	 * @param \WP_Term $term Term object
	 */
	public function render_edit_fields( \WP_Term $term ): void {
		$this->render_taxonomy_specific_fields( $term->term_id );
		wp_nonce_field( $this->get_nonce_action( 'fields' ), $this->get_nonce_action( 'fields_nonce' ) );
	}
	
	/**
	 * Save fields on term creation/update
	 *
	 * @param int $term_id Term ID
	 * @param int $tt_id Term taxonomy ID
	 * @return void
	 * @since 2.0.0
	 */
	final public function save_fields( int $term_id, int $tt_id ): void {
		// Check nonce
		if ( ! isset( $_POST[ $this->get_nonce_action( 'fields_nonce' ) ] ) || 
		     ! wp_verify_nonce( $_POST[ $this->get_nonce_action( 'fields_nonce' ) ], $this->get_nonce_action( 'fields' ) ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		// Set default status to published for new terms
		$status = $this->get_term_status( $term_id );
		if ( empty( $status ) || $status === 'published' ) {
			update_term_meta( $term_id, $this->get_meta_prefix() . 'status', 'published' );
		}

		// Save taxonomy-specific fields
		$this->save_taxonomy_specific_fields( $term_id );

		// Update timestamp
		update_term_meta( $term_id, $this->get_meta_prefix() . 'updated_at', current_time( 'mysql' ) );
	}
	
	/**
	 * Add status view tabs to taxonomy page
	 *
	 * Adds "All | Published | Draft | Trash" tabs similar to WordPress posts.
	 *
	 * @param array $views Existing views
	 * @return array Modified views
	 * @since 2.0.0
	 */
	final public function add_status_view_tabs( array $views ): array {
		// Only filter on current taxonomy
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== $this->get_taxonomy() ) {
			return $views;
		}

		// Count terms by status
		$all_count = $this->count_terms_by_status( 'all' );
		$published_count = $this->count_terms_by_status( 'published' );
		$draft_count = $this->count_terms_by_status( 'draft' );
		$trash_count = $this->count_terms_by_status( 'trashed' );

		// Get current status from URL
		$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		// Build new views
		$new_views = [];

		// All tab
		$all_class = $current_status === 'all' ? 'class="current"' : '';
		$all_url = admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product' );
		$new_views['all'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $all_url ),
			$all_class,
			esc_html__( 'All', 'affiliate-product-showcase' ),
			$all_count
		);

		// Published tab
		$published_class = $current_status === 'published' ? 'class="current"' : '';
		$published_url = admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product&status=published' );
		$new_views['published'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $published_url ),
			$published_class,
			esc_html__( 'Published', 'affiliate-product-showcase' ),
			$published_count
		);

		// Draft tab
		$draft_class = $current_status === 'draft' ? 'class="current"' : '';
		$draft_url = admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product&status=draft' );
		$new_views['draft'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $draft_url ),
			$draft_class,
			esc_html__( 'Draft', 'affiliate-product-showcase' ),
			$draft_count
		);

		// Trash tab
		$trash_class = $current_status === 'trashed' ? 'class="current"' : '';
		$trash_url = admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product&status=trashed' );
		$new_views['trash'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			esc_url( $trash_url ),
			$trash_class,
			esc_html__( 'Trash', 'affiliate-product-showcase' ),
			$trash_count
		);

		return $new_views;
	}
	
	/**
	 * Filter terms by status
	 *
	 * Filters terms based on status parameter in URL.
	 *
	 * @param array $terms Terms array
	 * @param array $taxonomies Taxonomies
	 * @param array $args Query arguments
	 * @return array Filtered terms
	 * @since 2.0.0
	 */
	final public function filter_terms_by_status( array $terms, array $taxonomies, array $args ): array {
		// Only filter for current taxonomy
		if ( ! in_array( $this->get_taxonomy(), $taxonomies, true ) ) {
			return $terms;
		}

		// Only filter on admin taxonomy list page
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== $this->get_taxonomy() || $screen->base !== 'edit-tags' ) {
			return $terms;
		}

		// Only filter when terms are objects (list table). Avoid breaking calls using fields=ids.
		if ( isset( $args['fields'] ) && $args['fields'] !== 'all' ) {
			return $terms;
		}

		// Get status from URL
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		// Filter terms by status
		$filtered_terms = [];
		foreach ( $terms as $term ) {
			if ( ! is_object( $term ) ) {
				continue;
			}

			$term_id = is_numeric( $term ) ? (int) $term : (int) $term->term_id;
			$term_status = $this->get_term_status( $term_id );

			// Default view (no status filter): behave like posts list and exclude trashed.
			if ( $status === 'all' ) {
				if ( $term_status !== 'trashed' ) {
					$filtered_terms[] = $term;
				}
				continue;
			}

			// Include term if status matches
			if ( $term_status === $status ) {
				$filtered_terms[] = $term;
			}
		}

		return $filtered_terms;
	}
	
	/**
	 * Count terms by status
	 *
	 * @param string $status Status to count ('all', 'published', 'draft', 'trashed')
	 * @return int Count of terms
	 * @since 2.0.0
	 */
	final protected function count_terms_by_status( string $status ): int {
		$terms = get_terms( [
			'taxonomy'   => $this->get_taxonomy(),
			'hide_empty' => false,
			'fields'     => 'ids',
		] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return 0;
		}

		$count = 0;
		foreach ( $terms as $term_id ) {
			$term_status = $this->get_term_status( (int) $term_id );

			// Default view excludes trashed (matches WP posts behavior).
			if ( $status === 'all' ) {
				if ( $term_status !== 'trashed' ) {
					$count++;
				}
				continue;
			}

			if ( $term_status === $status ) {
				$count++;
			}
		}

		return $count;
	}
	
	/**
	 * Get term status with default fallback
	 *
	 * @param int $term_id Term ID
	 * @return string Term status
	 * @since 2.0.0
	 */
	final protected function get_term_status( int $term_id ): string {
		$status = get_term_meta( $term_id, $this->get_meta_prefix() . 'status', true );
		if ( empty( $status ) || ! in_array( $status, [ 'published', 'draft', 'trashed' ], true ) ) {
			return 'published';
		}
		return $status;
	}
	
	/**
	 * Update term status
	 *
	 * @param int $term_id Term ID
	 * @param string $status New status
	 * @return bool Success
	 * @since 2.0.0
	 */
	final protected function update_term_status( int $term_id, string $status ): bool {
		return update_term_meta( $term_id, $this->get_meta_prefix() . 'status', $status ) !== false;
	}
	
	/**
	 * Add custom columns to WordPress native taxonomy table
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 * @since 2.0.0
	 */
	public function add_custom_columns( array $columns ): array {
		// Remove WordPress native count column to avoid duplicate
		unset( $columns['posts'] );
		
		// Insert custom columns after 'slug' column
		$new_columns = [];
		
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add custom columns after slug
			if ( $key === 'slug' ) {
				$new_columns['status'] = __( 'Status', 'affiliate-product-showcase' );
				$new_columns['count'] = __( 'Count', 'affiliate-product-showcase' );
			}
		}
		
		return $new_columns;
	}
	
	/**
	 * Render custom column content
	 *
	 * @param string $content Column content
	 * @param string $column_name Column name
	 * @param int $term_id Term ID
	 * @return string Column content
	 * @since 2.0.0
	 */
	public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
		// Render status column
		if ( $column_name === 'status' ) {
			$status = $this->get_term_status( $term_id );
			$is_default = $this->get_is_default( $term_id );

			if ( $is_default === '1' ) {
				// Default term - read-only status
				$icon = $status === 'published' 
					? '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" aria-hidden="true"></span>' 
					: '<span class="dashicons dashicons-minus" style="color: #646970;" aria-hidden="true"></span>';
				$status_text = $status === 'published' 
					? esc_html__( 'Published', 'affiliate-product-showcase' ) 
					: esc_html__( 'Draft', 'affiliate-product-showcase' );
				
				return sprintf(
					'<span class="aps-term-status-readonly">%s %s <span class="aps-status-note">(%s)</span></span>',
					$icon,
					$status_text,
					esc_html__( 'Default', 'affiliate-product-showcase' )
				);
			} else {
				// Non-default term - editable dropdown
				$selected_published = $status === 'published' ? 'selected' : '';
				$selected_draft = $status === 'draft' ? 'selected' : '';
				
				return sprintf(
					'<select class="aps-term-status-select" data-term-id="%d" data-original-status="%s" aria-label="%s">
						<option value="published" %s>%s</option>
						<option value="draft" %s>%s</option>
					</select>',
					$term_id,
					esc_attr( $status ),
					esc_attr__( 'Change ' . strtolower( $this->get_taxonomy_label() ) . ' status', 'affiliate-product-showcase' ),
					$selected_published,
					esc_html__( 'Published', 'affiliate-product-showcase' ),
					$selected_draft,
					esc_html__( 'Draft', 'affiliate-product-showcase' )
				);
			}
		}

		// Render count column (native WordPress count)
		if ( $column_name === 'count' ) {
			$term = get_term( $term_id, $this->get_taxonomy() );
			$count = $term ? $term->count : 0;
			return '<span class="aps-term-count">' . esc_html( (string) $count ) . '</span>';
		}
		
		return $content;
	}
	
	/**
	 * Get is default meta value
	 *
	 * @param int $term_id Term ID
	 * @return string Default flag ('1' or '0')
	 * @since 2.0.0
	 */
	protected function get_is_default( int $term_id ): string {
		$value = get_term_meta( $term_id, $this->get_meta_prefix() . 'is_default', true );
		if ( $value === '' || $value === false ) {
			$value = get_term_meta( $term_id, str_replace( '_aps_', 'aps_', $this->get_meta_prefix() ) . 'is_default', true );
		}
		return $value === '1' ? '1' : '0';
	}
	
	/**
	 * Set is default
	 *
	 * @param int $term_id Term ID
	 * @param bool $is_default Is default
	 * @return void
	 * @since 2.0.0
	 */
	protected function set_is_default( int $term_id, bool $is_default ): void {
		$value = $is_default ? '1' : '0';
		update_term_meta( $term_id, $this->get_meta_prefix() . 'is_default', $value );
		delete_term_meta( $term_id, str_replace( '_aps_', 'aps_', $this->get_meta_prefix() ) . 'is_default' );
	}
	
	/**
	 * Add custom bulk actions to taxonomy table
	 *
	 * Adds bulk actions based on current view (Draft, Trash, Restore, etc.).
	 *
	 * @param array $bulk_actions Existing bulk actions
	 * @return array Modified bulk actions
	 * @since 2.0.0
	 */
	final public function add_bulk_actions( array $bulk_actions ): array {
		// Get current status from URL
		$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		// If in Trash view, add Restore and Permanently Delete
		if ( $current_status === 'trashed' ) {
			$bulk_actions['restore'] = __( 'Restore', 'affiliate-product-showcase' );
			$bulk_actions['delete_permanently'] = __( 'Delete Permanently', 'affiliate-product-showcase' );
			return $bulk_actions;
		}

		// If not in Trash view, add Move to Draft and Move to Trash
		$bulk_actions['move_to_draft'] = __( 'Move to Draft', 'affiliate-product-showcase' );
		$bulk_actions['move_to_trash'] = __( 'Move to Trash', 'affiliate-product-showcase' );
		
		return $bulk_actions;
	}
	
	/**
	 * Handle custom bulk actions for taxonomy terms
	 *
	 * Processes bulk actions: Move to Draft, Move to Trash, Restore, Delete Permanently.
	 *
	 * @param string $redirect_url Redirect URL after processing
	 * @param string $action_name Action name being processed
	 * @param array $term_ids Array of term IDs
	 * @return string Modified redirect URL (with query parameters for notices)
	 * @since 2.0.0
	 */
	final public function handle_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
		if ( empty( $term_ids ) ) {
			return $redirect_url;
		}
		
		$count = 0;
		
		// Handle "Move to Draft" action
		if ( $action_name === 'move_to_draft' ) {
			foreach ( $term_ids as $term_id ) {
				// Check if this is default term (cannot be changed to draft)
				$is_default = $this->get_is_default( (int) $term_id );
				
				if ( $is_default === '1' ) {
					continue;
				}
				
				// Update term status to draft
				$result = $this->update_term_status( (int) $term_id, 'draft' );
				
				if ( $result ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'moved_to_draft' => $count,
				], $redirect_url );
			}
		}
		
		// Handle "Move to Trash" action (sets status to trashed)
		if ( $action_name === 'move_to_trash' ) {
			foreach ( $term_ids as $term_id ) {
				// Check if this is default term (cannot be trashed)
				$is_default = $this->get_is_default( (int) $term_id );
				
				if ( $is_default === '1' ) {
					continue;
				}
				
				// Set status to trashed
				$result = $this->update_term_status( (int) $term_id, 'trashed' );
				
				if ( $result ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'moved_to_trash' => $count,
				], $redirect_url );
			}
		}

		// Handle "Restore" action
		if ( $action_name === 'restore' ) {
			foreach ( $term_ids as $term_id ) {
				// Restore by setting status to published
				$result = $this->update_term_status( (int) $term_id, 'published' );
				
				if ( $result ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'restored_from_trash' => $count,
				], $redirect_url );
			}
		}

		// Handle "Delete Permanently" action
		if ( $action_name === 'delete_permanently' ) {
			foreach ( $term_ids as $term_id ) {
				// Check if this is default term (cannot be permanently deleted)
				$is_default = $this->get_is_default( (int) $term_id );
				
				if ( $is_default === '1' ) {
					continue;
				}
				
				// Permanently delete term
				$result = wp_delete_term( (int) $term_id, $this->get_taxonomy() );
				
				if ( $result && ! is_wp_error( $result ) ) {
					$count++;
				}
			}
			
			// Add success message to redirect URL
			if ( $count > 0 ) {
				$redirect_url = add_query_arg( [
					'permanently_deleted' => $count,
				], $redirect_url );
			}
		}
		
		return $redirect_url;
	}
	
	/**
	 * Display bulk action notices
	 *
	 * @return void
	 * @since 2.0.0
	 */
	final public function display_bulk_action_notices(): void {
		if ( isset( $_GET['moved_to_draft'] ) ) {
			$count = intval( $_GET['moved_to_draft'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d ' . strtolower( $this->get_taxonomy_label() ) . '(s) moved to draft.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}
		
		if ( isset( $_GET['moved_to_trash'] ) ) {
			$count = intval( $_GET['moved_to_trash'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d ' . strtolower( $this->get_taxonomy_label() ) . '(s) moved to trash.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}

		if ( isset( $_GET['restored_from_trash'] ) ) {
			$count = intval( $_GET['restored_from_trash'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d ' . strtolower( $this->get_taxonomy_label() ) . '(s) restored from trash.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}

		if ( isset( $_GET['permanently_deleted'] ) ) {
			$count = intval( $_GET['permanently_deleted'] );
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf( esc_html__( '%d ' . strtolower( $this->get_taxonomy_label() ) . '(s) permanently deleted.', 'affiliate-product-showcase' ), $count );
			echo '</p></div>';
		}
	}
	
	/**
	 * AJAX handler for inline status toggle
	 *
	 * @return void
	 * @since 2.0.0
	 */
	final public function ajax_toggle_term_status(): void {
		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], $this->get_nonce_action( 'toggle_status' ) ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'affiliate-product-showcase' ) ] );
		}
		
		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action.', 'affiliate-product-showcase' ) ] );
		}
		
		// Get term ID and new status
		$term_id = isset( $_POST['term_id'] ) ? (int) $_POST['term_id'] : 0;
		$new_status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'published';
		
		if ( empty( $term_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid ' . strtolower( $this->get_taxonomy_label() ) . ' ID.', 'affiliate-product-showcase' ) ] );
		}
		
		// Check if this is default term (cannot change status)
		$is_default = $this->get_is_default( $term_id );
		if ( $is_default === '1' ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Cannot change status of default ' . strtolower( $this->get_taxonomy_label() ) . '.', 'affiliate-product-showcase' ) ] );
		}
		
		// Update term status
		if ( $this->update_term_status( $term_id, $new_status ) ) {
			wp_send_json_success( [
				'status'  => $new_status,
				'message' => esc_html__( $this->get_taxonomy_label() . ' status updated successfully.', 'affiliate-product-showcase' ),
			] );
		}
		
		wp_send_json_error( [ 'message' => esc_html__( 'Failed to update ' . strtolower( $this->get_taxonomy_label() ) . ' status.', 'affiliate-product-showcase' ) ] );
	}
	
	/**
	 * AJAX handler for row actions
	 *
	 * @return void
	 * @since 2.0.0
	 */
	final public function ajax_term_row_action(): void {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], $this->get_nonce_action( 'row_action' ) ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'affiliate-product-showcase' ) ] );
		}
		
		if ( ! current_user_can( 'manage_categories' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission.', 'affiliate-product-showcase' ) ] );
		}

		$term_id = isset( $_POST['term_id'] ) ? (int) $_POST['term_id'] : 0;
		$do = isset( $_POST['do'] ) ? sanitize_text_field( $_POST['do'] ) : '';
		if ( $term_id <= 0 || $do === '' ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'affiliate-product-showcase' ) ] );
		}

		$is_default = $this->get_is_default( $term_id );
		if ( $is_default === '1' && in_array( $do, [ 'trash', 'draft', 'delete_permanently' ], true ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Default ' . strtolower( $this->get_taxonomy_label() ) . ' cannot be moved to draft/trash or deleted.', 'affiliate-product-showcase' ) ] );
		}

		$ok = false;
		$new_status = null;
		$message = '';
		
		switch ( $do ) {
			case 'draft':
				$ok = $this->update_term_status( $term_id, 'draft' );
				$new_status = 'draft';
				$message = esc_html__( $this->get_taxonomy_label() . ' moved to draft.', 'affiliate-product-showcase' );
				break;
			case 'trash':
				$ok = $this->update_term_status( $term_id, 'trashed' );
				$new_status = 'trashed';
				$message = esc_html__( $this->get_taxonomy_label() . ' moved to trash.', 'affiliate-product-showcase' );
				break;
			case 'restore':
				$ok = $this->update_term_status( $term_id, 'published' );
				$new_status = 'published';
				$message = esc_html__( $this->get_taxonomy_label() . ' restored.', 'affiliate-product-showcase' );
				break;
			case 'delete_permanently':
				$result = wp_delete_term( $term_id, $this->get_taxonomy() );
				$ok = ( $result && ! is_wp_error( $result ) );
				$message = esc_html__( $this->get_taxonomy_label() . ' permanently deleted.', 'affiliate-product-showcase' );
				break;
		}

		if ( $ok ) {
			wp_send_json_success( [
				'status'  => $new_status,
				'message' => $message,
			] );
		}
		
		wp_send_json_error( [ 'message' => esc_html__( 'Action failed. Please try again.', 'affiliate-product-showcase' ) ] );
	}
	
	/**
	 * Add per-row actions to taxonomy list table.
	 *
	 * @param array $actions Existing actions
	 * @param \WP_Term $term Term object
	 * @return array Modified actions
	 * @since 2.0.0
	 */
	final public function add_term_row_actions( array $actions, \WP_Term $term ): array {
		if ( $term->taxonomy !== $this->get_taxonomy() ) {
			return $actions;
		}

		$term_id = (int) $term->term_id;
		$status = $this->get_term_status( $term_id );
		$is_default = $this->get_is_default( $term_id );

		$current_view = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';
		$base = admin_url( 'admin-post.php' );

		if ( $current_view === 'trashed' || $status === 'trashed' ) {
			$restore_url = wp_nonce_url(
				add_query_arg( [
					'action'     => 'aps_' . $this->get_taxonomy() . '_row_action',
					'term_id'    => $term_id,
					'do'         => 'restore',
				], $base ),
				$this->get_nonce_action( 'row_action' )
			);
			$actions['aps_restore'] = '<a href="' . esc_url( $restore_url ) . '">' . esc_html__( 'Restore', 'affiliate-product-showcase' ) . '</a>';

			if ( $is_default !== '1' ) {
				$delete_url = wp_nonce_url(
					add_query_arg( [
						'action'     => 'aps_' . $this->get_taxonomy() . '_row_action',
						'term_id'    => $term_id,
						'do'         => 'delete_permanently',
					], $base ),
					$this->get_nonce_action( 'row_action' )
				);
				$actions['aps_delete_permanently'] = '<a href="' . esc_url( $delete_url ) . '" class="submitdelete">' . esc_html__( 'Delete Permanently', 'affiliate-product-showcase' ) . '</a>';
			}
			return $actions;
		}

		unset( $actions['delete'] );

		if ( $is_default !== '1' ) {
			$draft_url = wp_nonce_url(
				add_query_arg( [
					'action'     => 'aps_' . $this->get_taxonomy() . '_row_action',
					'term_id'    => $term_id,
					'do'         => 'draft',
				], $base ),
				$this->get_nonce_action( 'row_action' )
			);
			$actions['aps_move_to_draft'] = '<a href="' . esc_url( $draft_url ) . '">' . esc_html__( 'Move to Draft', 'affiliate-product-showcase' ) . '</a>';

			$trash_url = wp_nonce_url(
				add_query_arg( [
					'action'     => 'aps_' . $this->get_taxonomy() . '_row_action',
					'term_id'    => $term_id,
					'do'         => 'trash',
				], $base ),
				$this->get_nonce_action( 'row_action' )
			);
			$actions['aps_move_to_trash'] = '<a href="' . esc_url( $trash_url ) . '" class="submitdelete">' . esc_html__( 'Move to Trash', 'affiliate-product-showcase' ) . '</a>';
		}

		return $actions;
	}
	
	/**
	 * Non-AJAX fallback for row actions
	 *
	 * @return void
	 * @since 2.0.0
	 */
	final public function handle_term_row_action(): void {
		if ( ! current_user_can( 'manage_categories' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'affiliate-product-showcase' ) );
		}
		
		check_admin_referer( $this->get_nonce_action( 'row_action' ) );

		$term_id = isset( $_GET['term_id'] ) ? (int) $_GET['term_id'] : 0;
		$do = isset( $_GET['do'] ) ? sanitize_text_field( $_GET['do'] ) : '';
		if ( $term_id <= 0 || $do === '' ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product' ) );
			exit;
		}

		$is_default = $this->get_is_default( $term_id );
		$redirect = wp_get_referer() ?: admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product' );
		
		if ( $is_default === '1' && in_array( $do, [ 'trash', 'draft', 'delete_permanently' ], true ) ) {
			wp_safe_redirect( add_query_arg( [
				'aps_' . $this->get_taxonomy() . '_notice' => 'default_protected',
			], $redirect ) );
			exit;
		}

		$ok = false;
		$msg = '';
		
		switch ( $do ) {
			case 'draft':
				$ok = $this->update_term_status( $term_id, 'draft' );
				$msg = esc_html__( $this->get_taxonomy_label() . ' moved to draft.', 'affiliate-product-showcase' );
				break;
			case 'trash':
				$ok = $this->update_term_status( $term_id, 'trashed' );
				$msg = esc_html__( $this->get_taxonomy_label() . ' moved to trash.', 'affiliate-product-showcase' );
				break;
			case 'restore':
				$ok = $this->update_term_status( $term_id, 'published' );
				$msg = esc_html__( $this->get_taxonomy_label() . ' restored.', 'affiliate-product-showcase' );
				break;
			case 'delete_permanently':
				$result = wp_delete_term( $term_id, $this->get_taxonomy() );
				$ok = ( $result && ! is_wp_error( $result ) );
				$msg = esc_html__( $this->get_taxonomy_label() . ' deleted permanently.', 'affiliate-product-showcase' );
				break;
		}

		wp_safe_redirect( add_query_arg( [
			'aps_' . $this->get_taxonomy() . '_notice' => $ok ? 'success' : 'error',
			'aps_' . $this->get_taxonomy() . '_msg'    => rawurlencode( $msg ),
		], $redirect ) );
		exit;
	}
	
	/**
	 * Protect default term from permanent deletion
	 *
	 * @param mixed $term Term to delete
	 * @param string $taxonomy Taxonomy name
	 * @return void
	 * @since 2.0.0
	 */
	final public function protect_default_term( $term, string $taxonomy ): void {
		if ( $taxonomy !== $this->get_taxonomy() ) {
			return;
		}
		
		$term_id = (int) $term;
		if ( $term_id <= 0 ) {
			return;
		}

		$is_default = $this->get_is_default( $term_id );
		if ( $is_default === '1' ) {
			wp_die(
				esc_html__( 'Cannot delete default ' . strtolower( $this->get_taxonomy_label() ) . '. Please set a different ' . strtolower( $this->get_taxonomy_label() ) . ' as default first.', 'affiliate-product-showcase' ),
				esc_html__( 'Default ' . $this->get_taxonomy_label() . ' Protected', 'affiliate-product-showcase' ),
				[ 'back_link' => true ]
			);
		}
	}
	
	/**
	 * Add a Cancel button on term edit screen.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	final public function add_cancel_button_to_term_edit_screen(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== $this->get_taxonomy() || $screen->base !== 'term' ) {
			return;
		}

		$cancel_url = admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product' );
		?>
		<script>
		jQuery(function($){
			var $submit = $('#edittag .submit');
			if ($submit.length && !$submit.find('.aps-cancel-term-edit').length) {
				$submit.prepend('<a class="button button-secondary aps-cancel-term-edit" style="margin-right:8px" href="<?php echo esc_js( esc_url( $cancel_url ) ); ?>"><?php echo esc_js( esc_html__( 'Cancel', 'affiliate-product-showcase' ) ); ?></a>');
			}
		});
		</script>
		<?php
	}
}