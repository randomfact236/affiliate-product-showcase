<?php
/**
 * Ribbon Fields
 *
 * Adds custom fields to ribbon edit/add forms including:
 * - Color field with WordPress color picker
 * - Icon field
 * - Status field (Published/Draft)
 * - Status view tabs (All | Published | Draft | Trash)
 * - Bulk actions (Move to Draft, Move to Trash, Restore, Delete Permanently)
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Ribbon Fields
 *
 * Adds custom fields to ribbon taxonomy edit form.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class RibbonFields {
    /**
     * Initialize ribbon fields
     *
     * @return void
     * @since 1.0.0
     */
    public function init(): void {
        // Hide description field (built-in WordPress field)
        add_action( 'admin_head', [ $this, 'hide_description_field' ] );
        
        // Add fields to add term form
        add_action( 'aps_ribbon_add_form_fields', [ $this, 'render_add_fields' ] );
        
        // Add fields to edit term form
        add_action( 'aps_ribbon_edit_form_fields', [ $this, 'render_edit_fields' ] );
        
        // Save fields on term creation
        add_action( 'created_aps_ribbon', [ $this, 'save_fields' ], 10, 2 );
        
        // Save fields on term update
        add_action( 'edited_aps_ribbon', [ $this, 'save_fields' ], 10, 2 );

        // Add custom columns to WordPress native ribbons table
        add_filter( 'manage_edit-aps_ribbon_columns', [ $this, 'add_custom_columns' ] );
        add_filter( 'manage_aps_ribbon_custom_column', [ $this, 'render_custom_columns' ], 10, 3 );
        
        // Make count column non-sortable
        add_filter( 'manage_edit-aps_ribbon_sortable_columns', [ $this, 'make_count_non_sortable' ] );

        // Add view tabs (All | Published | Draft | Trash) - WordPress native
        add_filter( 'views_edit-aps_ribbon', [ $this, 'add_status_view_tabs' ] );

        // Filter ribbons by status
        add_filter( 'get_terms', [ $this, 'filter_ribbons_by_status' ], 10, 3 );

        // Add bulk actions
        add_filter( 'bulk_actions-edit-aps_ribbon', [ $this, 'add_bulk_actions' ] );
        add_filter( 'handle_bulk_actions-edit-aps_ribbon', [ $this, 'handle_bulk_actions' ], 10, 3 );

        // Add admin notices for bulk actions
        add_action( 'admin_notices', [ $this, 'display_bulk_action_notices' ] );

        // Enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook_suffix Current admin page hook
     * @return void
     * @since 1.0.0
     */
    public function enqueue_admin_assets( string $hook_suffix ): void {
        $screen = get_current_screen();
        if ( $screen && $screen->taxonomy === 'aps_ribbon' ) {
            // Enqueue WordPress color picker
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            
            // Add inline script for color picker
            wp_add_inline_script( 'wp-color-picker', $this->get_color_picker_script() );
        }
    }

    /**
     * Get inline JavaScript for color picker
     *
     * @return string Inline script
     * @since 1.4.0
     */
    private function get_color_picker_script(): string {
        ob_start();
        ?>
        jQuery(document).ready(function($) {
            // Initialize color picker
            if ( $('.aps-color-picker').length ) {
                $('.aps-color-picker').wpColorPicker({
                    change: function(event, ui) {
                        // Update value when color changes
                        $(this).val(ui.color.toString());
                    },
                    clear: function() {
                        // Clear value when clear button clicked
                        $(this).val('');
                    }
                });
            }
        });
        <?php
        return ob_get_clean();
    }

    /**
     * Render fields in add form
     *
     * @param string $taxonomy Taxonomy name
     */
    public function render_add_fields( string $taxonomy ): void {
        ?>
        <div class="form-field">
            <label for="aps_ribbon_color"><?php esc_html_e( 'Color', Constants::TEXTDOMAIN ); ?></label>
            <input 
                type="text" 
                name="aps_ribbon_color" 
                id="aps_ribbon_color" 
                value="#ff6b6b" 
                class="aps-color-picker regular-text"
                placeholder="#ff6b6b"
                pattern="^#[0-9a-fA-F]{6}$"
                maxlength="7"
            />
            <p class="description"><?php esc_html_e( 'Enter hex color code for ribbon (e.g., #ff6b6b).', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <div class="form-field">
            <label for="aps_ribbon_icon"><?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?></label>
            <input type="text" name="aps_ribbon_icon" id="aps_ribbon_icon" value="" class="regular-text" />
            <p class="description"><?php esc_html_e( 'Enter an icon class or identifier (e.g., "star", "badge").', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <?php wp_nonce_field( 'aps_ribbon_fields', 'aps_ribbon_fields_nonce' ); ?>
        <?php
    }

    /**
     * Render fields in edit form
     *
     * @param WP_Term $term Term object
     */
    public function render_edit_fields( \WP_Term $term ): void {
        $color = get_term_meta( $term->term_id, '_aps_ribbon_color', true ) ?: '#ff6b6b';
        $icon = get_term_meta( $term->term_id, '_aps_ribbon_icon', true ) ?: '';

        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_color"><?php esc_html_e( 'Color', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input 
                    type="text" 
                    name="aps_ribbon_color" 
                    id="aps_ribbon_color" 
                    value="<?php echo esc_attr( $color ); ?>" 
                    class="aps-color-picker regular-text"
                    placeholder="#ff6b6b"
                    pattern="^#[0-9a-fA-F]{6}$"
                    maxlength="7"
                />
                <p class="description"><?php esc_html_e( 'Enter hex color code for ribbon (e.g., #ff6b6b).', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_icon"><?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input type="text" name="aps_ribbon_icon" id="aps_ribbon_icon" value="<?php echo esc_attr( $icon ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'Enter an icon class or identifier (e.g., "star", "badge").', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <?php wp_nonce_field( 'aps_ribbon_fields', 'aps_ribbon_fields_nonce' ); ?>
        <?php
    }

    /**
     * Save ribbon fields
     *
     * @param int $term_id Term ID
     * @param int $tt_id Term taxonomy ID
     * @return void
     * @since 1.0.0
     *
     * @action created_aps_ribbon
     * @action edited_aps_ribbon
     */
    public function save_fields( int $term_id, int $tt_id ): void {
        // Check nonce
        if ( ! isset( $_POST['aps_ribbon_fields_nonce'] ) || 
             ! wp_verify_nonce( $_POST['aps_ribbon_fields_nonce'], 'aps_ribbon_fields' ) ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_categories' ) ) {
            return;
        }

        // Set default status to published for new ribbons
        $status = get_term_meta( $term_id, '_aps_ribbon_status', true );
        if ( empty( $status ) ) {
            update_term_meta( $term_id, '_aps_ribbon_status', 'published' );
        }

        // TRUE HYBRID: Save to term meta with underscore prefix
        if ( isset( $_POST['aps_ribbon_color'] ) ) {
            $color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_color'] ) );
            if ( $color ) {
                update_term_meta( $term_id, '_aps_ribbon_color', $color );
            } else {
                delete_term_meta( $term_id, '_aps_ribbon_color' );
            }
        }

        if ( isset( $_POST['aps_ribbon_icon'] ) ) {
            $icon = sanitize_text_field( wp_unslash( $_POST['aps_ribbon_icon'] ) );
            if ( $icon ) {
                update_term_meta( $term_id, '_aps_ribbon_icon', $icon );
            } else {
                delete_term_meta( $term_id, '_aps_ribbon_icon' );
            }
        }

        // Update timestamp
        update_term_meta( $term_id, '_aps_ribbon_updated_at', current_time( 'mysql' ) );
    }

    /**
     * Hide description field from ribbon taxonomy
     *
     * The description field is a built-in WordPress taxonomy field.
     * We hide it via CSS since it cannot be completely removed.
     *
     * @hook admin_head
     * @return void
     */
    public function hide_description_field(): void {
        $screen = get_current_screen();
        
        // Check if we're on the ribbon taxonomy page
        if ( ! $screen || ! in_array( $screen->base, [ 'edit-tags', 'term' ] ) ) {
            return;
        }
        
        if ( ! isset( $screen->taxonomy ) || $screen->taxonomy !== 'aps_ribbon' ) {
            return;
        }
        
        ?>
        <style>
            /* Hide description field in ribbon taxonomy forms */
            .tag-description,
            .form-field.term-description-wrap,
            tr.form-field.term-description-wrap {
                display: none !important;
            }
            
            /* Hide description column in ribbon table */
            .column-description {
                display: none !important;
            }
            
            /* Hide "Description" table header */
            th.manage-column.column-description {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Add custom columns to WordPress native ribbons table
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     * @since 1.0.0
     *
     * @filter manage_edit-aps_ribbon_columns
     */
    public function add_custom_columns( array $columns ): array {
        // Remove WordPress native count column to avoid duplicate
        unset( $columns['posts'] ); // Native count column
        
        // Insert custom columns after 'slug' column
        $new_columns = [];
        
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            
            // Add custom columns after slug
            if ( $key === 'slug' ) {
                $new_columns['color'] = __( 'Color', 'affiliate-product-showcase' );
                $new_columns['icon'] = __( 'Icon', 'affiliate-product-showcase' );
                $new_columns['status'] = __( 'Status', 'affiliate-product-showcase' );
                $new_columns['count'] = __( 'Count', 'affiliate-product-showcase' );
            }
        }
        
        return $new_columns;
    }

    /**
     * Make count column non-sortable
     *
     * @param array $sortable_columns Existing sortable columns
     * @return array Modified sortable columns
     * @since 1.4.0
     *
     * @filter manage_edit-aps_ribbon_sortable_columns
     */
    public function make_count_non_sortable( array $sortable_columns ): array {
        // Remove count from sortable columns
        if ( isset( $sortable_columns['count'] ) ) {
            unset( $sortable_columns['count'] );
        }
        
        return $sortable_columns;
    }

    /**
     * Render custom column content
     *
     * @param string $content Column content
     * @param string $column_name Column name
     * @param int $term_id Term ID
     * @return string Column content
     * @since 1.0.0
     *
     * @filter manage_aps_ribbon_custom_column
     */
    public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
        // Render color column
        if ( $column_name === 'color' ) {
            $color = get_term_meta( $term_id, '_aps_ribbon_color', true );
            
            if ( ! empty( $color ) ) {
                return sprintf(
                    '<span class="aps-ribbon-color-swatch" style="background-color: %s;" title="%s"></span>',
                    esc_attr( $color ),
                    esc_attr( $color )
                );
            }
            
            return '<span class="aps-ribbon-color-empty">-</span>';
        }

        // Render icon column
        if ( $column_name === 'icon' ) {
            $icon = get_term_meta( $term_id, '_aps_ribbon_icon', true );
            
            if ( ! empty( $icon ) ) {
                // Check if it's a dashicon
                if ( strpos( $icon, 'dashicons-' ) === 0 ) {
                    return sprintf(
                        '<span class="dashicons %s aps-ribbon-icon-display"></span>',
                        esc_attr( $icon )
                    );
                }
                
                // Check if it's an emoji
                $icon_length = mb_strlen( $icon );
                if ( $icon_length <= 4 ) {
                    return '<span class="aps-ribbon-icon-display">' . esc_html( $icon ) . '</span>';
                }
                
                // Default to dashicon
                return '<span class="dashicons dashicons-award aps-ribbon-icon-display"></span>';
            }
            
            return '<span class="aps-ribbon-icon-empty">-</span>';
        }

        // Render status column
        if ( $column_name === 'status' ) {
            $status = get_term_meta( $term_id, '_aps_ribbon_status', true ) ?: 'published';
            
            return sprintf(
                '<span class="aps-ribbon-status aps-ribbon-status-%s">%s</span>',
                esc_attr( $status ),
                esc_html( $status === 'published' ? 'Published' : 'Draft' )
            );
        }

        // Render count column (native WordPress count)
        if ( $column_name === 'count' ) {
            $term = get_term( $term_id, 'aps_ribbon' );
            $count = $term ? $term->count : 0;
            return '<span class="aps-ribbon-count">' . esc_html( (string) $count ) . '</span>';
        }
        
        return $content;
    }

    /**
     * Add status view tabs to ribbons page
     *
     * Adds "All | Published | Draft | Trash" tabs similar to WordPress posts.
     *
     * @param array $views Existing views
     * @return array Modified views
     * @since 1.3.0
     *
     * @filter views_edit-aps_ribbon
     */
    public function add_status_view_tabs( array $views ): array {
        // Only filter on aps_ribbon taxonomy
        $screen = get_current_screen();
        if ( ! $screen || $screen->taxonomy !== 'aps_ribbon' ) {
            return $views;
        }

        // Count ribbons by status
        $all_count = $this->count_ribbons_by_status( 'all' );
        $published_count = $this->count_ribbons_by_status( 'published' );
        $draft_count = $this->count_ribbons_by_status( 'draft' );
        $trash_count = $this->count_ribbons_by_status( 'trashed' );

        // Get current status from URL
        $current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        // Build new views
        $new_views = [];

        // All tab
        $all_class = $current_status === 'all' ? 'class="current"' : '';
        $all_url = admin_url( 'edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product' );
        $new_views['all'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            esc_url( $all_url ),
            $all_class,
            esc_html__( 'All', 'affiliate-product-showcase' ),
            $all_count
        );

        // Published tab
        $published_class = $current_status === 'published' ? 'class="current"' : '';
        $published_url = admin_url( 'edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product&status=published' );
        $new_views['published'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            esc_url( $published_url ),
            $published_class,
            esc_html__( 'Published', 'affiliate-product-showcase' ),
            $published_count
        );

        // Draft tab
        $draft_class = $current_status === 'draft' ? 'class="current"' : '';
        $draft_url = admin_url( 'edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product&status=draft' );
        $new_views['draft'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            esc_url( $draft_url ),
            $draft_class,
            esc_html__( 'Draft', 'affiliate-product-showcase' ),
            $draft_count
        );

        // Trash tab
        $trash_class = $current_status === 'trashed' ? 'class="current"' : '';
        $trash_url = admin_url( 'edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product&status=trashed' );
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
     * Filter ribbons by status
     *
     * Filters ribbons based on status parameter in URL.
     *
     * @param array $terms Terms array
     * @param array $taxonomies Taxonomies
     * @param array $args Query arguments
     * @return array Filtered terms
     * @since 1.3.0
     *
     * @filter get_terms
     */
    public function filter_ribbons_by_status( array $terms, array $taxonomies, array $args ): array {
        // Only filter for aps_ribbon taxonomy
        if ( ! in_array( 'aps_ribbon', $taxonomies, true ) ) {
            return $terms;
        }

        // Only filter on admin ribbon list page
        $screen = get_current_screen();
        if ( ! $screen || $screen->taxonomy !== 'aps_ribbon' || $screen->base !== 'edit-tags' ) {
            return $terms;
        }

        // Get status from URL
        $status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        // If showing all, no filtering
        if ( $status === 'all' ) {
            return $terms;
        }

        // Filter terms by status
        $filtered_terms = [];
        foreach ( $terms as $term ) {
            if ( ! is_object( $term ) ) {
                continue;
            }

            $term_id = is_numeric( $term ) ? $term : $term->term_id;
            $term_status = get_term_meta( $term_id, '_aps_ribbon_status', true );

            // Default to published if not set
            if ( empty( $term_status ) || ! in_array( $term_status, [ 'published', 'draft', 'trashed' ], true ) ) {
                $term_status = 'published';
            }

            // Include term if status matches
            if ( $term_status === $status ) {
                $filtered_terms[] = $term;
            }
        }

        return $filtered_terms;
    }

    /**
     * Count ribbons by status
     *
     * @param string $status Status to count ('all', 'published', 'draft', 'trashed')
     * @return int Count of ribbons
     * @since 1.3.0
     */
    private function count_ribbons_by_status( string $status ): int {
        $terms = get_terms( [
            'taxonomy'   => 'aps_ribbon',
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return 0;
        }

        $count = 0;
        foreach ( $terms as $term_id ) {
            $term_status = get_term_meta( $term_id, '_aps_ribbon_status', true );

            // Default to published if not set
            if ( empty( $term_status ) || ! in_array( $term_status, [ 'published', 'draft', 'trashed' ], true ) ) {
                $term_status = 'published';
            }

            if ( $status === 'all' || $term_status === $status ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Display bulk action notices
     *
     * @return void
     * @since 1.3.0
     */
    public function display_bulk_action_notices(): void {
        if ( isset( $_GET['moved_to_draft'] ) ) {
            $count = intval( $_GET['moved_to_draft'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf( esc_html__( '%d ribbons moved to draft.', 'affiliate-product-showcase' ), $count );
            echo '</p></div>';
        }
        
        if ( isset( $_GET['moved_to_trash'] ) ) {
            $count = intval( $_GET['moved_to_trash'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf( esc_html__( '%d ribbons moved to trash.', 'affiliate-product-showcase' ), $count );
            echo '</p></div>';
        }

        if ( isset( $_GET['restored_from_trash'] ) ) {
            $count = intval( $_GET['restored_from_trash'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf( esc_html__( '%d ribbons restored from trash.', 'affiliate-product-showcase' ), $count );
            echo '</p></div>';
        }

        if ( isset( $_GET['permanently_deleted'] ) ) {
            $count = intval( $_GET['permanently_deleted'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf( esc_html__( '%d ribbons permanently deleted.', 'affiliate-product-showcase' ), $count );
            echo '</p></div>';
        }
    }

    /**
     * Add custom bulk actions to ribbons table
     *
     * Adds bulk actions based on current view (Draft, Trash, Restore, etc.).
     *
     * @param array $bulk_actions Existing bulk actions
     * @return array Modified bulk actions
     * @since 1.3.0
     *
     * @filter bulk_actions-edit-aps_ribbon
     */
    public function add_bulk_actions( array $bulk_actions ): array {
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
     * Handle custom bulk actions for ribbons
     *
     * Processes bulk actions: Move to Draft, Move to Trash, Restore, Delete Permanently.
     *
     * @param string $redirect_url Redirect URL after processing
     * @param string $action_name Action name being processed
     * @param array $term_ids Array of term IDs
     * @return string Modified redirect URL (with query parameters for notices)
     * @since 1.3.0
     *
     * @filter handle_bulk_actions-edit-aps_ribbon
     */
    public function handle_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
        if ( empty( $term_ids ) ) {
            return $redirect_url;
        }
        
        $count = 0;
        
        // Handle "Move to Draft" action
        if ( $action_name === 'move_to_draft' ) {
            foreach ( $term_ids as $term_id ) {
                // Update ribbon status to draft
                $result = update_term_meta( $term_id, '_aps_ribbon_status', 'draft' );
                
                if ( $result !== false ) {
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
                // Set status to trashed
                $result = update_term_meta( $term_id, '_aps_ribbon_status', 'trashed' );
                
                if ( $result !== false ) {
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
                $result = update_term_meta( $term_id, '_aps_ribbon_status', 'published' );
                
                if ( $result !== false ) {
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
                // Permanently delete term
                $result = wp_delete_term( $term_id, 'aps_ribbon' );
                
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
}
