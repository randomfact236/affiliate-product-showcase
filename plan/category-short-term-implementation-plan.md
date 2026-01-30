# Category Files: Short-Term Implementation Plan

**Generated:** 2026-01-30  
**Duration:** 2-3 days  
**Focus:** Code quality improvements and refactoring

---

## OVERVIEW

This plan addresses the 3 major refactoring tasks needed to improve code quality:

1. ✅ Split large `TaxonomyFieldsAbstract.php` into 5 focused classes
2. ✅ Implement Content Security Policy headers
3. ✅ Extract helper methods for duplicate patterns

**Estimated Time:** 16-24 hours (2-3 days)  
**Files to Create:** 6  
**Files to Modify:** 3  
**Lines of Code:** ~1,200

---

## TASK 1: Split TaxonomyFieldsAbstract.php (8-10 hours)

**Current State:** 850 lines in single abstract class  
**Problem:** Violates Single Responsibility Principle  
**Solution:** Split into 5 focused classes

### 1.1 Create TaxonomyFieldsAbstract.php (Core) [2 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`

**Purpose:** Core hooks and initialization

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AffiliateProductShowcase\Admin\Traits\DefaultCategoryProtectionTrait;
use AffiliateProductShowcase\Plugin\Constants;

/**
 * Taxonomy Fields Abstract Base Class
 *
 * Core hooks and initialization for taxonomy field management.
 * Child classes handle taxonomy-specific functionality.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */
abstract class TaxonomyFieldsAbstract {
    
    /**
     * @var TaxonomyColumnRenderer Column renderer instance
     */
    protected TaxonomyColumnRenderer $column_renderer;
    
    /**
     * @var TaxonomyStatusManager Status manager instance
     */
    protected TaxonomyStatusManager $status_manager;
    
    /**
     * @var TaxonomyBulkActions Bulk actions instance
     */
    protected TaxonomyBulkActions $bulk_actions;
    
    /**
     * @var TaxonomyAjaxHandler AJAX handler instance
     */
    protected TaxonomyAjaxHandler $ajax_handler;
    
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
     * Get meta key prefix
     *
     * @return string Meta key prefix (e.g., '_aps_category_')
     */
    protected function get_meta_prefix(): string {
        $taxonomy = $this->get_taxonomy();
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
     * Initialize all hooks
     *
     * @return void
     * @since 2.0.0
     */
    public function init(): void {
        // Initialize sub-components
        $this->column_renderer = new TaxonomyColumnRenderer( $this );
        $this->status_manager = new TaxonomyStatusManager( $this );
        $this->bulk_actions = new TaxonomyBulkActions( $this );
        $this->ajax_handler = new TaxonomyAjaxHandler( $this );
        
        // Initialize all sub-components
        $this->column_renderer->init();
        $this->status_manager->init();
        $this->bulk_actions->init();
        $this->ajax_handler->init();
        
        // Form hooks
        add_action( $this->get_taxonomy() . '_add_form_fields', [ $this, 'render_add_fields' ] );
        add_action( $this->get_taxonomy() . '_edit_form_fields', [ $this, 'render_edit_fields' ] );
        add_action( 'created_' . $this->get_taxonomy(), [ $this, 'save_fields' ], 10, 2 );
        add_action( 'edited_' . $this->get_taxonomy(), [ $this, 'save_fields' ], 10, 2 );
        
        // Default protection
        add_action( 'pre_delete_term', [ $this, 'protect_default_term' ], 10, 2 );
        
        // Assets
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'admin_footer-term.php', [ $this, 'add_cancel_button_to_term_edit_screen' ] );
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
        $status = $this->status_manager->get_term_status( $term_id );
        if ( empty( $status ) || $status === 'published' ) {
            update_term_meta( $term_id, $this->get_meta_prefix() . 'status', 'published' );
        }

        // Save taxonomy-specific fields
        $this->save_taxonomy_specific_fields( $term_id );

        // Update timestamp
        update_term_meta( $term_id, $this->get_meta_prefix() . 'updated_at', current_time( 'mysql' ) );
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
                    'cancel_url'       => admin_url( 'edit-tags.php?taxonomy=' . $this->get_taxonomy() . '&post_type=aps_product' ),
                    'cancel_text'      => esc_html__( 'Cancel', 'affiliate-product-showcase' ),
                ] );
            }
        }
    }
    
    /**
     * Add a Cancel button on term edit screen
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
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#submit').before(
                '<a href="<?php echo esc_url( $cancel_url ); ?>" class="button">' +
                '<?php esc_html_e( 'Cancel', 'affiliate-product-showcase' ); ?>' +
                '</a>'
            );
        });
        </script>
        <?php
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

        $is_default = $this->status_manager->get_is_default( $term_id );
        if ( $is_default === '1' ) {
            wp_die(
                esc_html__( 'Cannot delete default ' . strtolower( $this->get_taxonomy_label() ) . '. Please set a different ' . strtolower( $this->get_taxonomy_label() ) . ' as default first.', 'affiliate-product-showcase' ),
                esc_html__( 'Default ' . $this->get_taxonomy_label() . ' Protected', 'affiliate-product-showcase' ),
                [ 'back_link' => true ]
            );
        }
    }
}
```

---

### 1.2 Create TaxonomyColumnRenderer.php [1.5 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Traits/TaxonomyColumnRenderer.php`

**Purpose:** Handle column rendering in taxonomy tables

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AffiliateProductShowcase\Admin\TaxonomyFieldsAbstract;

/**
 * Taxonomy Column Renderer Trait
 *
 * Handles custom column rendering for taxonomy list tables.
 *
 * @package AffiliateProductShowcase\Admin\Traits
 * @since 2.0.0
 */
trait TaxonomyColumnRenderer {
    
    /**
     * @var TaxonomyFieldsAbstract Parent taxonomy fields instance
     */
    protected TaxonomyFieldsAbstract $taxonomy_fields;
    
    /**
     * Constructor
     *
     * @param TaxonomyFieldsAbstract $taxonomy_fields Parent instance
     */
    public function __construct( TaxonomyFieldsAbstract $taxonomy_fields ) {
        $this->taxonomy_fields = $taxonomy_fields;
    }
    
    /**
     * Initialize column hooks
     *
     * @return void
     */
    public function init(): void {
        add_filter( 
            'manage_edit-' . $this->taxonomy_fields->get_taxonomy() . '_columns',
            [ $this, 'add_custom_columns' ]
        );
        add_filter(
            'manage_' . $this->taxonomy_fields->get_taxonomy() . '_custom_column',
            [ $this, 'render_custom_columns' ],
            10,
            3
        );
    }
    
    /**
     * Add custom columns to WordPress native taxonomy table
     *
     * @param array $columns Existing columns
     * @return array Modified columns
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
     */
    public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
        // Render status column
        if ( $column_name === 'status' ) {
            return $this->render_status_column( $term_id );
        }

        // Render count column (native WordPress count)
        if ( $column_name === 'count' ) {
            $term = get_term( $term_id, $this->taxonomy_fields->get_taxonomy() );
            $count = $term ? $term->count : 0;
            return '<span class="aps-term-count">' . esc_html( (string) $count ) . '</span>';
        }
        
        return $content;
    }
    
    /**
     * Render status column
     *
     * @param int $term_id Term ID
     * @return string Status column HTML
     */
    private function render_status_column( int $term_id ): string {
        $status = $this->taxonomy_fields->get_status_manager()->get_term_status( $term_id );
        $is_default = $this->taxonomy_fields->get_status_manager()->get_is_default( $term_id );

        if ( $is_default === '1' ) {
            // Default term - read-only status
            $icon = $status === 'published'
                ? '<span class="dashicons dashicons-yes-alt aps-status-icon-success" aria-hidden="true"></span>'
                : '<span class="dashicons dashicons-minus aps-status-icon-neutral" aria-hidden="true"></span>';
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
                esc_attr__( 'Change ' . strtolower( $this->taxonomy_fields->get_taxonomy_label() ) . ' status: Published or Draft', 'affiliate-product-showcase' ),
                $selected_published,
                esc_html__( 'Published', 'affiliate-product-showcase' ),
                $selected_draft,
                esc_html__( 'Draft', 'affiliate-product-showcase' )
            );
        }
    }
}
```

---

### 1.3 Create TaxonomyStatusManager.php [2 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Traits/TaxonomyStatusManager.php`

**Purpose:** Handle status management (views, filtering, counting)

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AffiliateProductShowcase\Admin\TaxonomyFieldsAbstract;

/**
 * Taxonomy Status Manager Trait
 *
 * Handles status views, filtering, and counting for taxonomies.
 *
 * @package AffiliateProductShowcase\Admin\Traits
 * @since 2.0.0
 */
trait TaxonomyStatusManager {
    
    /**
     * @var TaxonomyFieldsAbstract Parent taxonomy fields instance
     */
    protected TaxonomyFieldsAbstract $taxonomy_fields;
    
    /**
     * Constructor
     *
     * @param TaxonomyFieldsAbstract $taxonomy_fields Parent instance
     */
    public function __construct( TaxonomyFieldsAbstract $taxonomy_fields ) {
        $this->taxonomy_fields = $taxonomy_fields;
    }
    
    /**
     * Initialize status hooks
     *
     * @return void
     */
    public function init(): void {
        add_filter(
            'views_edit-' . $this->taxonomy_fields->get_taxonomy(),
            [ $this, 'add_status_view_tabs' ]
        );
        add_filter(
            'get_terms',
            [ $this, 'filter_terms_by_status' ],
            10,
            3
        );
    }
    
    /**
     * Add status view tabs to taxonomy page
     *
     * @param array $views Existing views
     * @return array Modified views
     */
    public function add_status_view_tabs( array $views ): array {
        // Only filter on current taxonomy
        $screen = get_current_screen();
        if ( ! $screen || $screen->taxonomy !== $this->taxonomy_fields->get_taxonomy() ) {
            return $views;
        }

        // Count terms by status
        $all_count = $this->count_terms_by_status( 'all' );
        $published_count = $this->count_terms_by_status( 'published' );
        $draft_count = $this->count_terms_by_status( 'draft' );
        $trash_count = $this->count_terms_by_status( 'trashed' );

        // Get current status from URL
        $current_status = $this->get_valid_status_from_url();

        // Build new views
        $new_views = [];

        // All tab
        $all_class = $current_status === 'all' ? 'class="current"' : '';
        $all_url = admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy_fields->get_taxonomy() . '&post_type=aps_product' );
        $new_views['all'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            esc_url( $all_url ),
            $all_class,
            esc_html__( 'All', 'affiliate-product-showcase' ),
            $all_count
        );

        // Published tab
        $published_class = $current_status === 'published' ? 'class="current"' : '';
        $published_url = admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy_fields->get_taxonomy() . '&post_type=aps_product&status=published' );
        $new_views['published'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            esc_url( $published_url ),
            $published_class,
            esc_html__( 'Published', 'affiliate-product-showcase' ),
            $published_count
        );

        // Draft tab
        $draft_class = $current_status === 'draft' ? 'class="current"' : '';
        $draft_url = admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy_fields->get_taxonomy() . '&post_type=aps_product&status=draft' );
        $new_views['draft'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            esc_url( $draft_url ),
            $draft_class,
            esc_html__( 'Draft', 'affiliate-product-showcase' ),
            $draft_count
        );

        // Trash tab
        $trash_class = $current_status === 'trashed' ? 'class="current"' : '';
        $trash_url = admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy_fields->get_taxonomy() . '&post_type=aps_product&status=trashed' );
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
     * @param array $terms Terms array
     * @param array $taxonomies Taxonomies
     * @param array $args Query arguments
     * @return array Filtered terms
     */
    public function filter_terms_by_status( array $terms, array $taxonomies, array $args ): array {
        // Only filter for current taxonomy
        if ( ! in_array( $this->taxonomy_fields->get_taxonomy(), $taxonomies, true ) ) {
            return $terms;
        }

        // Only filter on admin taxonomy list page
        $screen = get_current_screen();
        if ( ! $screen || $screen->taxonomy !== $this->taxonomy_fields->get_taxonomy() || $screen->base !== 'edit-tags' ) {
            return $terms;
        }

        // Only filter when terms are objects (list table)
        if ( isset( $args['fields'] ) && $args['fields'] !== 'all' ) {
            return $terms;
        }

        $status = $this->get_valid_status_from_url();

        // Filter terms by status
        $filtered_terms = [];
        foreach ( $terms as $term ) {
            if ( ! is_object( $term ) ) {
                continue;
            }

            $term_id = is_numeric( $term ) ? (int) $term : (int) $term->term_id;
            $term_status = $this->get_term_status( $term_id );

            // Default view (no status filter): exclude trashed
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
     * @param string $status Status to count
     * @return int Count of terms
     */
    protected function count_terms_by_status( string $status ): int {
        $terms = get_terms( [
            'taxonomy'   => $this->taxonomy_fields->get_taxonomy(),
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return 0;
        }

        $count = 0;
        foreach ( $terms as $term_id ) {
            $term_status = $this->get_term_status( (int) $term_id );

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
     */
    protected function get_term_status( int $term_id ): string {
        $status = get_term_meta( $term_id, $this->taxonomy_fields->get_meta_prefix() . 'status', true );
        if ( empty( $status ) || ! in_array( $status, [ 'published', 'draft', 'trashed' ], true ) ) {
            return 'published';
        }
        return $status;
    }
    
    /**
     * Get is default meta value
     *
     * @param int $term_id Term ID
     * @return string Default flag ('1' or '0')
     */
    protected function get_is_default( int $term_id ): string {
        $value = get_term_meta( $term_id, $this->taxonomy_fields->get_meta_prefix() . 'is_default', true );
        if ( $value === '' || $value === false ) {
            $value = get_term_meta( $term_id, str_replace( '_aps_', 'aps_', $this->taxonomy_fields->get_meta_prefix() ) . 'is_default', true );
        }
        return $value === '1' ? '1' : '0';
    }
    
    /**
     * Get and validate status from URL
     *
     * @return string Valid status
     */
    protected function get_valid_status_from_url(): string {
        $valid_statuses = ['all', 'published', 'draft', 'trashed'];
        return isset( $_GET['status'] ) &&
               in_array( $_GET['status'], $valid_statuses, true )
               ? sanitize_text_field( $_GET['status'] )
               : 'all';
    }
}
```

---

### 1.4 Create TaxonomyBulkActions.php [1.5 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Traits/TaxonomyBulkActions.php`

**Purpose:** Handle bulk actions for taxonomy terms

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AffiliateProductShowcase\Admin\TaxonomyFieldsAbstract;
use AffiliateProductShowcase\Traits\DefaultCategoryProtectionTrait;

/**
 * Taxonomy Bulk Actions Trait
 *
 * Handles bulk actions for taxonomy terms (move to draft/trash, restore, delete).
 *
 * @package AffiliateProductShowcase\Admin\Traits
 * @since 2.0.0
 */
trait TaxonomyBulkActions {
    use DefaultCategoryProtectionTrait;
    
    /**
     * @var TaxonomyFieldsAbstract Parent taxonomy fields instance
     */
    protected TaxonomyFieldsAbstract $taxonomy_fields;
    
    /**
     * Constructor
     *
     * @param TaxonomyFieldsAbstract $taxonomy_fields Parent instance
     */
    public function __construct( TaxonomyFieldsAbstract $taxonomy_fields ) {
        $this->taxonomy_fields = $taxonomy_fields;
    }
    
    /**
     * Initialize bulk action hooks
     *
     * @return void
     */
    public function init(): void {
        add_filter(
            'bulk_actions-edit-' . $this->taxonomy_fields->get_taxonomy(),
            [ $this, 'add_bulk_actions' ]
        );
        add_filter(
            'handle_bulk_actions-edit-' . $this->taxonomy_fields->get_taxonomy(),
            [ $this, 'handle_bulk_actions' ],
            10,
            3
        );
        add_action( 'admin_notices', [ $this, 'display_bulk_action_notices' ] );
    }
    
    /**
     * Add custom bulk actions to taxonomy table
     *
     * @param array $bulk_actions Existing bulk actions
     * @return array Modified bulk actions
     */
    public function add_bulk_actions( array $bulk_actions ): array {
        $current_status = $this->get_valid_status_from_url();

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
     * @param string $redirect_url Redirect URL after processing
     * @param string $action_name Action name being processed
     * @param array $term_ids Array of term IDs
     * @return string Modified redirect URL
     */
    public function handle_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
        if ( empty( $term_ids ) ) {
            return $redirect_url;
        }
        
        $count = 0;
        $meta_prefix = $this->taxonomy_fields->get_meta_prefix();
        
        switch ( $action_name ) {
            case 'move_to_draft':
                $count = $this->bulk_move_to_draft( $term_ids, $meta_prefix );
                if ( $count > 0 ) {
                    $redirect_url = add_query_arg( ['moved_to_draft' => $count], $redirect_url );
                }
                break;
                
            case 'move_to_trash':
                $count = $this->bulk_move_to_trash( $term_ids, $meta_prefix );
                if ( $count > 0 ) {
                    $redirect_url = add_query_arg( ['moved_to_trash' => $count], $redirect_url );
                }
                break;
                
            case 'restore':
                $count = $this->bulk_restore( $term_ids, $meta_prefix );
                if ( $count > 0 ) {
                    $redirect_url = add_query_arg( ['restored_from_trash' => $count], $redirect_url );
                }
                break;
                
            case 'delete_permanently':
                $count = $this->bulk_delete_permanently( $term_ids );
                if ( $count > 0 ) {
                    $redirect_url = add_query_arg( ['permanently_deleted' => $count], $redirect_url );
                }
                break;
        }
        
        return $redirect_url;
    }
    
    /**
     * Bulk move to draft
     *
     * @param array $term_ids Term IDs
     * @param string $meta_prefix Meta key prefix
     * @return int Number of terms updated
     */
    private function bulk_move_to_draft( array $term_ids, string $meta_prefix ): int {
        $count = 0;
        foreach ( $term_ids as $term_id ) {
            if ( ! $this->can_delete_term( (int) $term_id, $meta_prefix . 'is_default' ) ) {
                continue;
            }
            
            if ( update_term_meta( (int) $term_id, $meta_prefix . 'status', 'draft' ) !== false ) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Bulk move to trash
     *
     * @param array $term_ids Term IDs
     * @param string $meta_prefix Meta key prefix
     * @return int Number of terms updated
     */
    private function bulk_move_to_trash( array $term_ids, string $meta_prefix ): int {
        $count = 0;
        foreach ( $term_ids as $term_id ) {
            if ( ! $this->can_delete_term( (int) $term_id, $meta_prefix . 'is_default' ) ) {
                continue;
            }
            
            if ( update_term_meta( (int) $term_id, $meta_prefix . 'status', 'trashed' ) !== false ) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Bulk restore
     *
     * @param array $term_ids Term IDs
     * @param string $meta_prefix Meta key prefix
     * @return int Number of terms updated
     */
    private function bulk_restore( array $term_ids, string $meta_prefix ): int {
        $count = 0;
        foreach ( $term_ids as $term_id ) {
            if ( update_term_meta( (int) $term_id, $meta_prefix . 'status', 'published' ) !== false ) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Bulk delete permanently
     *
     * @param array $term_ids Term IDs
     * @return int Number of terms deleted
     */
    private function bulk_delete_permanently( array $term_ids ): int {
        $count = 0;
        foreach ( $term_ids as $term_id ) {
            if ( ! $this->can_delete_term( (int) $term_id, $this->taxonomy_fields->get_meta_prefix() . 'is_default' ) ) {
                continue;
            }
            
            $result = wp_delete_term( (int) $term_id, $this->taxonomy_fields->get_taxonomy() );
            if ( $result && ! is_wp_error( $result ) ) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Display bulk action notices
     *
     * @return void
     */
    public function display_bulk_action_notices(): void {
        if ( isset( $_GET['moved_to_draft'] ) ) {
            $count = intval( $_GET['moved_to_draft'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf(
                esc_html__( '%d %s(s) moved to draft.', 'affiliate-product-showcase' ),
                $count,
                esc_html( strtolower( $this->taxonomy_fields->get_taxonomy_label() ) )
            );
            echo '</p></div>';
        }
        
        if ( isset( $_GET['moved_to_trash'] ) ) {
            $count = intval( $_GET['moved_to_trash'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf(
                esc_html__( '%d %s(s) moved to trash.', 'affiliate-product-showcase' ),
                $count,
                esc_html( strtolower( $this->taxonomy_fields->get_taxonomy_label() ) )
            );
            echo '</p></div>';
        }

        if ( isset( $_GET['restored_from_trash'] ) ) {
            $count = intval( $_GET['restored_from_trash'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf(
                esc_html__( '%d %s(s) restored from trash.', 'affiliate-product-showcase' ),
                $count,
                esc_html( strtolower( $this->taxonomy_fields->get_taxonomy_label() ) )
            );
            echo '</p></div>';
        }

        if ( isset( $_GET['permanently_deleted'] ) ) {
            $count = intval( $_GET['permanently_deleted'] );
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf(
                esc_html__( '%d %s(s) permanently deleted.', 'affiliate-product-showcase' ),
                $count,
                esc_html( strtolower( $this->taxonomy_fields->get_taxonomy_label() ) )
            );
            echo '</p></div>';
        }
    }
    
    /**
     * Get and validate status from URL
     *
     * @return string Valid status
     */
    protected function get_valid_status_from_url(): string {
        $valid_statuses = ['all', 'published', 'draft', 'trashed'];
        return isset( $_GET['status'] ) &&
               in_array( $_GET['status'], $valid_statuses, true )
               ? sanitize_text_field( $_GET['status'] )
               : 'all';
    }
}
```

---

### 1.5 Create TaxonomyAjaxHandler.php [2 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Traits/TaxonomyAjaxHandler.php`

**Purpose:** Handle AJAX requests for taxonomy operations

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AffiliateProductShowcase\Admin\TaxonomyFieldsAbstract;
use AffiliateProductShowcase\Traits\DefaultCategoryProtectionTrait;

/**
 * Taxonomy AJAX Handler Trait
 *
 * Handles AJAX requests for taxonomy operations (status toggle, row actions).
 *
 * @package AffiliateProductShowcase\Admin\Traits
 * @since 2.0.0
 */
trait TaxonomyAjaxHandler {
    use DefaultCategoryProtectionTrait;
    
    /**
     * @var TaxonomyFieldsAbstract Parent taxonomy fields instance
     */
    protected TaxonomyFieldsAbstract $taxonomy_fields;
    
    /**
     * Constructor
     *
     * @param TaxonomyFieldsAbstract $taxonomy_fields Parent instance
     */
    public function __construct( TaxonomyFieldsAbstract $taxonomy_fields ) {
        $this->taxonomy_fields = $taxonomy_fields;
    }
    
    /**
     * Initialize AJAX hooks
     *
     * @return void
     */
    public function init(): void {
        add_action(
            'wp_ajax_aps_toggle_' . $this->taxonomy_fields->get_taxonomy() . '_status',
            [ $this, 'ajax_toggle_term_status' ]
        );
        add_action(
            'wp_ajax_aps_' . $this->taxonomy_fields->get_taxonomy() . '_row_action',
            [ $this, 'ajax_term_row_action' ]
        );
        
        // Row actions
        add_filter( 'tag_row_actions', [ $this, 'add_term_row_actions' ], 10, 2 );
        add_action(
            'admin_post_aps_' . $this->taxonomy_fields->get_taxonomy() . '_row_action',
            [ $this, 'handle_term_row_action' ]
        );
    }
    
    /**
     * AJAX handler for inline status toggle
     *
     * @return void
     */
    public function ajax_toggle_term_status(): void {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || 
             ! wp_verify_nonce( $_POST['nonce'], $this->taxonomy_fields->get_nonce_action( 'toggle_status' ) ) ) {
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
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid ' . strtolower( $this->taxonomy_fields->get_taxonomy_label() ) . ' ID.', 'affiliate-product-showcase' ) ] );
        }
        
        // Check if this is default term (cannot change status)
        $meta_prefix = $this->taxonomy_fields->get_meta_prefix();
        if ( ! $this->can_delete_term( $term_id, $meta_prefix . 'is_default' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Cannot change status of default ' . strtolower( $this->taxonomy_fields->get_taxonomy_label() ) . '.', 'affiliate-product-showcase' ) ] );
        }
        
        // Update term status
        if ( update_term_meta( $term_id, $meta_prefix . 'status', $new_status ) !== false ) {
            wp_send_json_success( [
                'status'  => $new_status,
                'message' => esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' status updated successfully.', 'affiliate-product-showcase' ),
            ] );
        }
        
        wp_send_json_error( [ 'message' => esc_html__( 'Failed to update ' . strtolower( $this->taxonomy_fields->get_taxonomy_label() ) . ' status.', 'affiliate-product-showcase' ) ] );
    }
    
    /**
     * AJAX handler for row actions
     *
     * @return void
     */
    public function ajax_term_row_action(): void {
        if ( ! isset( $_POST['nonce'] ) || 
             ! wp_verify_nonce( $_POST['nonce'], $this->taxonomy_fields->get_nonce_action( 'row_action' ) ) ) {
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

        $meta_prefix = $this->taxonomy_fields->get_meta_prefix();
        if ( ! $this->can_delete_term( $term_id, $meta_prefix . 'is_default' ) && 
             in_array( $do, [ 'trash', 'draft', 'delete_permanently' ], true ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Default ' . strtolower( $this->taxonomy_fields->get_taxonomy_label() ) . ' cannot be moved to draft/trash or deleted.', 'affiliate-product-showcase' ) ] );
        }

        $ok = false;
        $new_status = null;
        $message = '';
        
        switch ( $do ) {
            case 'draft':
                $ok = update_term_meta( $term_id, $meta_prefix . 'status', 'draft' ) !== false;
                $new_status = 'draft';
                $message = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' moved to draft.', 'affiliate-product-showcase' );
                break;
            case 'trash':
                $ok = update_term_meta( $term_id, $meta_prefix . 'status', 'trashed' ) !== false;
                $new_status = 'trashed';
                $message = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' moved to trash.', 'affiliate-product-showcase' );
                break;
            case 'restore':
                $ok = update_term_meta( $term_id, $meta_prefix . 'status', 'published' ) !== false;
                $new_status = 'published';
                $message = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' restored.', 'affiliate-product-showcase' );
                break;
            case 'delete_permanently':
                $result = wp_delete_term( $term_id, $this->taxonomy_fields->get_taxonomy() );
                $ok = ( $result && ! is_wp_error( $result ) );
                $message = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' permanently deleted.', 'affiliate-product-showcase' );
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
     * Add per-row actions to taxonomy list table
     *
     * @param array $actions Existing actions
     * @param \WP_Term $term Term object
     * @return array Modified actions
     */
    public function add_term_row_actions( array $actions, \WP_Term $term ): array {
        if ( $term->taxonomy !== $this->taxonomy_fields->get_taxonomy() ) {
            return $actions;
        }

        $term_id = (int) $term->term_id;
        $status = get_term_meta( $term_id, $this->taxonomy_fields->get_meta_prefix() . 'status', true );
        $is_default = get_term_meta( $term_id, $this->taxonomy_fields->get_meta_prefix() . 'is_default', true );

        $current_status = $this->get_valid_status_from_url();
        $base = admin_url( 'admin-post.php' );

        if ( $current_status === 'trashed' || $status === 'trashed' ) {
            $restore_url = wp_nonce_url(
                add_query_arg( [
                    'action'  => 'aps_' . $this->taxonomy_fields->get_taxonomy() . '_row_action',
                    'term_id' => $term_id,
                    'do'       => 'restore',
                ], $base ),
                $this->taxonomy_fields->get_nonce_action( 'row_action' )
            );
            $actions['aps_restore'] = '<a href="' . esc_url( $restore_url ) . '">' . esc_html__( 'Restore', 'affiliate-product-showcase' ) . '</a>';

            if ( $is_default !== '1' ) {
                $delete_url = wp_nonce_url(
                    add_query_arg( [
                        'action'  => 'aps_' . $this->taxonomy_fields->get_taxonomy() . '_row_action',
                        'term_id' => $term_id,
                        'do'       => 'delete_permanently',
                    ], $base ),
                    $this->taxonomy_fields->get_nonce_action( 'row_action' )
                );
                $actions['aps_delete_permanently'] = '<a href="' . esc_url( $delete_url ) . '" class="submitdelete">' . esc_html__( 'Delete Permanently', 'affiliate-product-showcase' ) . '</a>';
            }
            return $actions;
        }

        unset( $actions['delete'] );

        if ( $is_default !== '1' ) {
            $draft_url = wp_nonce_url(
                add_query_arg( [
                    'action'  => 'aps_' . $this->taxonomy_fields->get_taxonomy() . '_row_action',
                    'term_id' => $term_id,
                    'do'       => 'draft',
                ], $base ),
                $this->taxonomy_fields->get_nonce_action( 'row_action' )
            );
            $actions['aps_move_to_draft'] = '<a href="' . esc_url( $draft_url ) . '">' . esc_html__( 'Move to Draft', 'affiliate-product-showcase' ) . '</a>';

            $trash_url = wp_nonce_url(
                add_query_arg( [
                    'action'  => 'aps_' . $this->taxonomy_fields->get_taxonomy() . '_row_action',
                    'term_id' => $term_id,
                    'do'       => 'trash',
                ], $base ),
                $this->taxonomy_fields->get_nonce_action( 'row_action' )
            );
            $actions['aps_move_to_trash'] = '<a href="' . esc_url( $trash_url ) . '" class="submitdelete">' . esc_html__( 'Move to Trash', 'affiliate-product-showcase' ) . '</a>';
        }

        return $actions;
    }
    
    /**
     * Non-AJAX fallback for row actions
     *
     * @return void
     */
    public function handle_term_row_action(): void {
        if ( ! current_user_can( 'manage_categories' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'affiliate-product-showcase' ) );
        }
        
        check_admin_referer( $this->taxonomy_fields->get_nonce_action( 'row_action' ) );

        $term_id = isset( $_GET['term_id'] ) ? (int) $_GET['term_id'] : 0;
        $do = $this->get_valid_action_from_url();
        
        if ( $term_id <= 0 || $do === '' ) {
            wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy_fields->get_taxonomy() . '&post_type=aps_product' ) );
            exit;
        }

        $meta_prefix = $this->taxonomy_fields->get_meta_prefix();
        $redirect = wp_get_referer() ?: admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy_fields->get_taxonomy() . '&post_type=aps_product' );
        
        if ( ! $this->can_delete_term( $term_id, $meta_prefix . 'is_default' ) && 
             in_array( $do, [ 'trash', 'draft', 'delete_permanently' ], true ) ) {
            wp_safe_redirect( add_query_arg( [
                'aps_' . $this->taxonomy_fields->get_taxonomy() . '_notice' => 'default_protected',
            ], $redirect ) );
            exit;
        }

        $ok = false;
        $msg = '';
        
        switch ( $do ) {
            case 'draft':
                $ok = update_term_meta( $term_id, $meta_prefix . 'status', 'draft' ) !== false;
                $msg = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' moved to draft.', 'affiliate-product-showcase' );
                break;
            case 'trash':
                $ok = update_term_meta( $term_id, $meta_prefix . 'status', 'trashed' ) !== false;
                $msg = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' moved to trash.', 'affiliate-product-showcase' );
                break;
            case 'restore':
                $ok = update_term_meta( $term_id, $meta_prefix . 'status', 'published' ) !== false;
                $msg = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' restored.', 'affiliate-product-showcase' );
                break;
            case 'delete_permanently':
                $result = wp_delete_term( $term_id, $this->taxonomy_fields->get_taxonomy() );
                $ok = ( $result && ! is_wp_error( $result ) );
                $msg = esc_html__( $this->taxonomy_fields->get_taxonomy_label() . ' deleted permanently.', 'affiliate-product-showcase' );
                break;
        }

        wp_safe_redirect( add_query_arg( [
            'aps_' . $this->taxonomy_fields->get_taxonomy() . '_notice' => $ok ? 'success' : 'error',
            'aps_' . $this->taxonomy_fields->get_taxonomy() . '_msg'    => rawurlencode( $msg ),
        ], $redirect ) );
        exit;
    }
    
    /**
     * Get and validate status from URL
     *
     * @return string Valid status
     */
    protected function get_valid_status_from_url(): string {
        $valid_statuses = ['all', 'published', 'draft', 'trashed'];
        return isset( $_GET['status'] ) &&
               in_array( $_GET['status'], $valid_statuses, true )
               ? sanitize_text_field( $_GET['status'] )
               : 'all';
    }
    
    /**
     * Get and validate action from URL
     *
     * @return string Valid action
     */
    protected function get_valid_action_from_url(): string {
        $valid_actions = ['draft', 'trash', 'restore', 'delete_permanently'];
        return isset( $_GET['do'] ) &&
               in_array( $_GET['do'], $valid_actions, true )
               ? sanitize_text_field( $_GET['do'] )
               : '';
    }
}
```

---

### 1.6 Update CategoryFields.php to Use New Structure [1 hour]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`

**Changes Required:**
1. Remove all methods moved to traits
2. Keep only taxonomy-specific methods:
   - `get_taxonomy()` - returns 'aps_category'
   - `get_taxonomy_label()` - returns 'Category'
   - `render_taxonomy_specific_fields()` - renders category fields
   - `save_taxonomy_specific_fields()` - saves category fields

---

## TASK 2: Implement Content Security Policy (3-4 hours)

### 2.1 Create SecurityHeaders.php [1.5 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Security/SecurityHeaders.php`

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Security;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Security Headers Manager
 *
 * Manages Content Security Policy and other security headers.
 *
 * @package AffiliateProductShowcase\Admin\Security
 * @since 2.0.0
 */
class SecurityHeaders {
    
    /**
     * Initialize security headers
     *
     * @return void
     */
    public function init(): void {
        // Add CSP headers for admin pages
        add_action( 'admin_head', [ $this, 'add_csp_header' ] );
        
        // Add CSP headers for frontend
        add_action( 'wp_head', [ $this, 'add_frontend_csp_header' ] );
    }
    
    /**
     * Add CSP header to admin pages
     *
     * @return void
     */
    public function add_csp_header(): void {
        $screen = get_current_screen();
        
        // Only add to plugin admin pages
        if ( ! $screen ) {
            return;
        }
        
        $is_plugin_page = $this->is_plugin_admin_page( $screen );
        if ( ! $is_plugin_page ) {
            return;
        }
        
        // Get CSP policy for admin
        $csp = $this->get_admin_csp_policy();
        
        printf(
            '<meta http-equiv="Content-Security-Policy" content="%s">',
            esc_attr( $csp )
        );
    }
    
    /**
     * Add CSP header to frontend
     *
     * @return void
     */
    public function add_frontend_csp_header(): void {
        // Get CSP policy for frontend
        $csp = $this->get_frontend_csp_policy();
        
        printf(
            '<meta http-equiv="Content-Security-Policy" content="%s">',
            esc_attr( $csp )
        );
    }
    
    /**
     * Get CSP policy for admin pages
     *
     * @return string CSP policy
     */
    private function get_admin_csp_policy(): string {
        $directives = [
            'default-src' => "'self'",
            'script-src'  => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            'style-src'   => "'self' 'unsafe-inline' https://fonts.googleapis.com",
            'img-src'     => "'self' data: https: *.wp.com *.gravatar.com",
            'font-src'    => "'self' https://fonts.gstatic.com",
            'connect-src' => "'self' https://api.example.com",
            'object-src'  => "'none'",
            'frame-src'   => "'self'",
            'base-uri'    => "'self'",
            'form-action'  => "'self'",
        ];
        
        // Apply filters for extensibility
        $directives = apply_filters( 'aps_admin_csp_directives', $directives );
        
        return $this->build_csp_string( $directives );
    }
    
    /**
     * Get CSP policy for frontend
     *
     * @return string CSP policy
     */
    private function get_frontend_csp_policy(): string {
        $directives = [
            'default-src' => "'self'",
            'script-src'  => "'self' 'unsafe-inline'",
            'style-src'   => "'self' 'unsafe-inline'",
            'img-src'     => "'self' data: https:",
            'font-src'    => "'self' data:",
            'connect-src' => "'self'",
            'object-src'  => "'none'",
            'frame-src'   => "'self'",
        ];
        
        // Apply filters for extensibility
        $directives = apply_filters( 'aps_frontend_csp_directives', $directives );
        
        return $this->build_csp_string( $directives );
    }
    
    /**
     * Build CSP string from directives
     *
     * @param array $directives CSP directives
     * @return string CSP policy string
     */
    private function build_csp_string( array $directives ): string {
        $parts = [];
        
        foreach ( $directives as $directive => $value ) {
            $parts[] = sprintf( '%s %s', $directive, $value );
        }
        
        return implode( '; ', $parts );
    }
    
    /**
     * Check if current screen is a plugin admin page
     *
     * @param \WP_Screen|null $screen Current screen
     * @return bool True if plugin admin page
     */
    private function is_plugin_admin_page( ?\WP_Screen $screen ): bool {
        if ( ! $screen ) {
            return false;
        }
        
        // Check for plugin taxonomies
        $plugin_taxonomies = ['aps_category', 'aps_tag', 'aps_ribbon'];
        if ( in_array( $screen->taxonomy ?? '', $plugin_taxonomies, true ) ) {
            return true;
        }
        
        // Check for plugin post types
        $plugin_post_types = ['aps_product'];
        if ( in_array( $screen->post_type ?? '', $plugin_post_types, true ) ) {
            return true;
        }
        
        // Check for plugin admin pages
        $plugin_pages = [
            'aps-product-showcase',
            'aps_products',
            'aps_categories',
            'aps_settings',
        ];
        
        if ( in_array( $screen->id ?? '', $plugin_pages, true ) ) {
            return true;
        }
        
        return false;
    }
}
```

### 2.2 Initialize SecurityHeaders in Main Plugin File [0.5 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php`

**Add to initialization:**
```php
// Initialize security headers
$security_headers = new \AffiliateProductShowcase\Admin\Security\SecurityHeaders();
$security_headers->init();
```

---

## TASK 3: Extract Helper Methods (4-5 hours)

### 3.1 Create MetadataHelper.php [2 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Helpers/MetadataHelper.php`

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metadata Helper
 *
 * Provides utility methods for metadata operations.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 2.0.0
 */
class MetadataHelper {
    
    /**
     * Clean up both new and legacy metadata keys
     *
     * @param int $term_id Term ID
     * @param array $meta_keys Metadata keys to clean
     * @return void
     */
    public static function cleanup_metadata_keys( int $term_id, array $meta_keys ): void {
        foreach ( $meta_keys as $key ) {
            // Delete new format
            delete_term_meta( $term_id, '_aps_category_' . $key );
            // Delete legacy format
            delete_term_meta( $term_id, 'aps_category_' . $key );
        }
    }
    
    /**
     * Save metadata with legacy cleanup
     *
     * @param int $term_id Term ID
     * @param array $metadata Metadata to save
     * @return void
     */
    public static function save_metadata_with_cleanup( int $term_id, array $metadata ): void {
        foreach ( $metadata as $key => $value ) {
            // Save new format
            update_term_meta( $term_id, '_aps_category_' . $key, $value );
            
            // Delete legacy format if exists
            delete_term_meta( $term_id, 'aps_category_' . $key );
        }
    }
    
    /**
     * Get metadata with legacy fallback
     *
     * @param int $term_id Term ID
     * @param string $key Metadata key
     * @param mixed $default Default value
     * @return mixed Metadata value
     */
    public static function get_metadata_with_fallback( int $term_id, string $key, $default = '' ) {
        // Try new format first
        $value = get_term_meta( $term_id, '_aps_category_' . $key, true );
        
        // Fallback to legacy format
        if ( $value === '' || $value === false ) {
            $value = get_term_meta( $term_id, 'aps_category_' . $key, true );
        }
        
        return $value !== '' && $value !== false ? $value : $default;
    }
}
```

### 3.2 Update CategoryRepository.php to Use MetadataHelper [1.5 hours]

**Changes:**
```php
use AffiliateProductShowcase\Helpers\MetadataHelper;

// Update save_metadata():
private function save_metadata( int $term_id, Category $category ): void {
    MetadataHelper::save_metadata_with_cleanup( $term_id, [
        'featured'    => $category->featured ? '1' : '0',
        'image'       => $category->image,
        'sort_order'  => $category->sort_order,
        'status'      => $category->status,
        'is_default'  => $category->is_default ? '1' : '0',
    ] );
}

// Update delete_metadata():
private function delete_metadata( int $term_id ): void {
    MetadataHelper::cleanup_metadata_keys( $term_id, [
        'featured',
        'image',
        'sort_order',
        'status',
        'is_default',
    ] );
}

// Update get_is_default():
private function get_is_default( int $term_id ): string {
    $value = MetadataHelper::get_metadata_with_fallback( $term_id, 'is_default', '0' );
    return $value === '1' ? '1' : '0';
}
```

---

## TESTING PLAN

### Unit Tests to Write

1. **TaxonomyColumnRendererTest**
   - Test custom columns are added
   - Test status column rendering
   - Test count column rendering

2. **TaxonomyStatusManagerTest**
   - Test status views are added
   - Test term filtering by status
   - Test term counting by status

3. **TaxonomyBulkActionsTest**
   - Test bulk actions are added
   - Test bulk move to draft
   - Test bulk move to trash
   - Test bulk restore
   - Test bulk delete permanently

4. **TaxonomyAjaxHandlerTest**
   - Test status toggle AJAX
   - Test row action AJAX
   - Test nonce verification
   - Test permission checks

5. **SecurityHeadersTest**
   - Test CSP headers are added
   - Test admin CSP policy
   - Test frontend CSP policy

6. **MetadataHelperTest**
   - Test metadata cleanup
   - Test metadata saving
   - Test legacy fallback

---

## SUMMARY

**Files to Create:** 6
1. `src/Admin/TaxonomyFieldsAbstract.php` (refactored)
2. `src/Admin/Traits/TaxonomyColumnRenderer.php`
3. `src/Admin/Traits/TaxonomyStatusManager.php`
4. `src/Admin/Traits/TaxonomyBulkActions.php`
5. `src/Admin/Traits/TaxonomyAjaxHandler.php`
6. `src/Admin/Security/SecurityHeaders.php`
7. `src/Helpers/MetadataHelper.php`

**Files to Modify:** 2
1. `src/Admin/CategoryFields.php`
2. `src/Repositories/CategoryRepository.php`

**Estimated Time:** 16-24 hours (2-3 days)

**Commits:**
- refactor(category): Split TaxonomyFieldsAbstract into focused classes
- feat(category): Implement Content Security Policy headers
- refactor(category): Extract MetadataHelper for metadata operations

---

**Next Steps:** Proceed to long-term plan (testing, optimization)