# Section 2: Categories - Missing Features Implementation Plan

## User Request
"with this finding, create implementation plan with solution details, which file to edit and what code to write-as per this report"

---

## HONEST STATUS: What's Missing

Based on verification of `CategoryFields.php`, the following features are **NOT IMPLEMENTED**:

### ❌ Missing Features (4 Major)
1. Custom Columns in WordPress native table
2. Default Category Protection from deletion
3. Product Auto-Assignment to default category
4. Bulk Actions for status management

### ✅ Already Implemented (Keep These)
- Basic form fields (Featured, Default, Image URL, Sort Order, Status)
- Form field rendering and saving
- Default category checkbox
- REST API endpoints (CategoriesController.php - separate file)

---

## IMPLEMENTATION PLAN

### FEATURE 1: Custom Columns in WordPress Native Table

**Priority:** HIGH  
**Complexity:** MEDIUM  
**File to Edit:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`

#### What to Add:

**1. Add Custom Columns Method**
**Location:** After line ~250 (after existing methods)

```php
/**
 * Add custom columns to WordPress native categories table
 *
 * Adds Featured, Default, and Status columns after the 'slug' column.
 *
 * @param array $columns Existing columns
 * @return array Modified columns
 * @since 1.1.0
 *
 * @filter manage_edit-aps_category_columns
 */
public function add_custom_columns( array $columns ): array {
    // Insert custom columns after 'slug' column
    $new_columns = [];
    
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        
        // Add custom columns after slug
        if ( $key === 'slug' ) {
            $new_columns['featured'] = __( 'Featured', 'affiliate-product-showcase' );
            $new_columns['default'] = __( 'Default', 'affiliate-product-showcase' );
            $new_columns['status'] = __( 'Status', 'affiliate-product-showcase' );
        }
    }
    
    return $new_columns;
}
```

**2. Render Custom Columns Method**
**Location:** After add_custom_columns() method

```php
/**
 * Render custom column content
 *
 * Renders Featured, Default, and Status column values.
 *
 * @param string $content Column content
 * @param string $column_name Column name
 * @param int $term_id Term ID
 * @return string Column content
 * @since 1.1.0
 *
 * @filter manage_aps_category_custom_column
 */
public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
    if ( $column_name === 'featured' ) {
        $featured = get_term_meta( $term_id, 'aps_category_featured', true );
        return $featured ? '<span class="dashicons dashicons-star-filled" style="color: #ffb900;" aria-hidden="true"></span>' : '—';
    }
    
    if ( $column_name === 'default' ) {
        $is_default = get_term_meta( $term_id, 'aps_category_is_default', true );
        return $is_default ? '<span class="dashicons dashicons-admin-home" style="color: #2271b1;" aria-hidden="true"></span>' : '—';
    }
    
    if ( $column_name === 'status' ) {
        $status = get_term_meta( $term_id, 'aps_category_status', true );
        if ( $status === 'published' ) {
            return '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" aria-hidden="true"></span> ' . esc_html__( 'Published', 'affiliate-product-showcase' );
        } else {
            return '<span class="dashicons dashicons-minus" style="color: #646970;" aria-hidden="true"></span> ' . esc_html__( 'Draft', 'affiliate-product-showcase' );
        }
    }
    
    return $content;
}
```

**3. Register Hooks in init()**
**Location:** Inside `init()` method (around line 45)

```php
public function init(): void {
    // Add form fields to category edit/add pages
    add_action( 'aps_category_add_form_fields', [ $this, 'add_category_fields' ] );
    add_action( 'aps_category_edit_form_fields', [ $this, 'edit_category_fields' ] );

    // Save category meta fields
    add_action( 'created_aps_category', [ $this, 'save_category_fields' ], 10, 2 );
    add_action( 'edited_aps_category', [ $this, 'save_category_fields' ], 10, 2 );

    // Add custom columns to WordPress native categories table
    add_filter( 'manage_edit-aps_category_columns', [ $this, 'add_custom_columns' ] );
    add_filter( 'manage_aps_category_custom_column', [ $this, 'render_custom_columns' ], 10, 3 );
    
    // ... existing hooks
}
```

---

### FEATURE 2: Default Category Protection

**Priority:** HIGH  
**Complexity:** MEDIUM  
**File to Edit:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`

#### What to Add:

**1. Add Protection Method**
**Location:** After existing methods (around line 250+)

```php
/**
 * Protect default category from permanent deletion
 *
 * Prevents default category from being deleted permanently.
 * Users can still move it to trash but not delete forever.
 *
 * @param mixed $delete_term Whether to delete term
 * @param int $term_id Term ID
 * @return mixed False if default category (prevents deletion), otherwise original value
 * @since 1.1.0
 *
 * @filter pre_delete_term
 */
public function protect_default_category( $delete_term, int $term_id ) {
    // Check if this is default category
    $is_default = get_term_meta( $term_id, 'aps_category_is_default', true );
    
    if ( $is_default === '1' ) {
        // Prevent deletion of default category
        wp_die(
            esc_html__( 'Cannot delete default category. Please set a different category as default first.', 'affiliate-product-showcase' ),
            esc_html__( 'Default Category Protected', 'affiliate-product-showcase' ),
            [ 'back_link' => true ]
        );
    }
    
    return $delete_term;
}
```

**2. Register Hook in init()**
**Location:** Inside `init()` method

```php
public function init(): void {
    // ... existing hooks ...
    
    // Protect default category from permanent deletion
    add_filter( 'pre_delete_term', [ $this, 'protect_default_category' ], 10, 2 );
    
    // ... rest of hooks
}
```

---

### FEATURE 3: Product Auto-Assignment

**Priority:** HIGH  
**Complexity:** MEDIUM  
**File to Edit:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`

#### What to Add:

**1. Add Auto-Assignment Method**
**Location:** After protect_default_category() method

```php
/**
 * Auto-assign default category to products without category
 *
 * When a product is saved without any categories, automatically assign
 * the default category to it.
 *
 * @param int $post_id Post ID
 * @param \WP_Post $post Post object
 * @param bool $update Whether this is an update (true) or new post (false)
 * @return void
 * @since 1.1.0
 *
 * @action save_post_aps_product
 */
public function auto_assign_default_category( int $post_id, \WP_Post $post, bool $update ): void {
    // Skip auto-save, revisions, and trashed posts
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
    
    if ( $post->post_status === 'trash' ) {
        return;
    }
    
    // Get default category ID
    $default_category_id = get_option( 'aps_default_category_id', 0 );
    
    if ( empty( $default_category_id ) ) {
        return;
    }
    
    // Check if product already has categories
    $terms = wp_get_object_terms( $post_id, 'aps_category' );
    
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        // Product already has categories, skip auto-assignment
        return;
    }
    
    // Assign default category to product
    $result = wp_set_object_terms( $post_id, [ (int) $default_category_id ], 'aps_category', true );
    
    if ( ! is_wp_error( $result ) ) {
        // Log auto-assignment for debugging
        error_log( sprintf(
            '[APS] Auto-assigned default category #%d to product #%d',
            $default_category_id,
            $post_id
        ) );
    }
}
```

**2. Register Hook in init()**
**Location:** Inside `init()` method

```php
public function init(): void {
    // ... existing hooks ...
    
    // Auto-assign default category to products without category
    add_action( 'save_post_aps_product', [ $this, 'auto_assign_default_category' ], 10, 3 );
    
    // ... rest of hooks
}
```

---

### FEATURE 4: Bulk Actions for Status Management

**Priority:** MEDIUM  
**Complexity:** MEDIUM  
**File to Edit:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`

#### What to Add:

**1. Add Bulk Actions Method**
**Location:** After auto_assign_default_category() method

```php
/**
 * Add custom bulk actions to categories table
 *
 * Adds "Move to Draft" and "Move to Trash" bulk actions.
 *
 * @param array $bulk_actions Existing bulk actions
 * @return array Modified bulk actions
 * @since 1.1.0
 *
 * @filter bulk_actions-edit-aps_category
 */
public function add_custom_bulk_actions( array $bulk_actions ): array {
    // Add "Move to Draft" bulk action
    $bulk_actions['move_to_draft'] = __( 'Move to Draft', 'affiliate-product-showcase' );
    
    // Add "Move to Trash" bulk action (sets status to draft, safe delete)
    $bulk_actions['move_to_trash'] = __( 'Move to Trash', 'affiliate-product-showcase' );
    
    return $bulk_actions;
}
```

**2. Handle Bulk Actions Method**
**Location:** After add_custom_bulk_actions() method

```php
/**
 * Handle custom bulk actions for categories
 *
 * Processes "Move to Draft" and "Move to Trash" bulk actions.
 *
 * @param string $redirect_url Redirect URL after processing
 * @param string $action_name Action name being processed
 * @param array $term_ids Array of term IDs
 * @return string Modified redirect URL with success/error parameters
 * @since 1.1.0
 *
 * @filter handle_bulk_actions-edit-aps_category
 */
public function handle_custom_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
    if ( empty( $term_ids ) ) {
        return $redirect_url;
    }
    
    $count = 0;
    $error = false;
    
    // Handle "Move to Draft" action
    if ( $action_name === 'move_to_draft' ) {
        foreach ( $term_ids as $term_id ) {
            // Check if this is default category (cannot be changed to draft)
            $is_default = get_term_meta( $term_id, 'aps_category_is_default', true );
            
            if ( $is_default === '1' ) {
                continue; // Skip default category
            }
            
            // Update category status to draft
            $result = update_term_meta( $term_id, 'aps_category_status', 'draft' );
            
            if ( $result !== false ) {
                $count++;
            }
        }
        
        // Add success/error message to redirect URL
        if ( $count > 0 ) {
            $redirect_url = add_query_arg( [
                'moved_to_draft' => $count,
            ], $redirect_url );
        }
    }
    
    // Handle "Move to Trash" action (sets status to draft)
    if ( $action_name === 'move_to_trash' ) {
        foreach ( $term_ids as $term_id ) {
            // Check if this is default category (cannot be trashed)
            $is_default = get_term_meta( $term_id, 'aps_category_is_default', true );
            
            if ( $is_default === '1' ) {
                continue; // Skip default category
            }
            
            // Set status to draft (safe delete - not permanent)
            $result = update_term_meta( $term_id, 'aps_category_status', 'draft' );
            
            if ( $result !== false ) {
                $count++;
            }
        }
        
        // Add success/error message to redirect URL
        if ( $count > 0 ) {
            $redirect_url = add_query_arg( [
                'moved_to_trash' => $count,
            ], $redirect_url );
        }
    }
    
    // Add admin notice for bulk action results
    add_action( 'admin_notices', function() use ( $action_name, $count, $error ) {
        if ( $error ) {
            $message = esc_html__( 'An error occurred while processing bulk action.', 'affiliate-product-showcase' );
            echo '<div class="notice notice-error is-dismissible"><p>' . $message . '</p></div>';
        } elseif ( $count > 0 ) {
            if ( $action_name === 'move_to_draft' ) {
                $message = sprintf(
                    esc_html__( '%d categories moved to draft.', 'affiliate-product-showcase' ),
                    $count
                );
            } elseif ( $action_name === 'move_to_trash' ) {
                $message = sprintf(
                    esc_html__( '%d categories moved to trash (set to draft).', 'affiliate-product-showcase' ),
                    $count
                );
            }
            echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
        }
    } );
    
    return $redirect_url;
}
```

**3. Add Admin Notice Handler (Optional Enhancement)**
**Location:** After handle_custom_bulk_actions() method

```php
/**
 * Add admin notice for bulk actions
 *
 * Shows success/error messages after bulk actions are processed.
 *
 * @param string $location Redirect location
 * @return string Modified location with query args
 * @since 1.1.0
 *
 * @filter redirect_term_location
 */
public function add_notice_query_args( string $location ): string {
    // Check if we're on the categories page
    global $pagenow;
    
    if ( $pagenow !== 'edit-tags.php' ) {
        return $location;
    }
    
    $taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
    if ( $taxonomy !== 'aps_category' ) {
        return $location;
    }
    
    // Preserve existing query args from bulk actions
    if ( isset( $_GET['moved_to_draft'] ) || isset( $_GET['moved_to_trash'] ) ) {
        // Args already added by handle_custom_bulk_actions()
        return $location;
    }
    
    return $location;
}
```

**4. Register Hooks in init()**
**Location:** Inside `init()` method

```php
public function init(): void {
    // ... existing hooks ...
    
    // Add bulk actions for status management
    add_filter( 'bulk_actions-edit-aps_category', [ $this, 'add_custom_bulk_actions' ] );
    add_filter( 'handle_bulk_actions-edit-aps_category', [ $this, 'handle_custom_bulk_actions' ], 10, 3 );
    add_filter( 'redirect_term_location', [ $this, 'add_notice_query_args' ] );
    
    // ... rest of hooks
}
```

---

## IMPLEMENTATION ORDER

### Step 1: Custom Columns (HIGH PRIORITY)
**File:** `src/Admin/CategoryFields.php`
**Estimated Time:** 15 minutes
**Impact:** Users can see category status at a glance

**Actions:**
1. Add `add_custom_columns()` method
2. Add `render_custom_columns()` method
3. Register hooks in `init()`
4. Test in WordPress admin

### Step 2: Default Category Protection (HIGH PRIORITY)
**File:** `src/Admin/CategoryFields.php`
**Estimated Time:** 10 minutes
**Impact:** Prevents accidental deletion of default category

**Actions:**
1. Add `protect_default_category()` method
2. Register hook in `init()`
3. Test deletion protection

### Step 3: Product Auto-Assignment (HIGH PRIORITY)
**File:** `src/Admin/CategoryFields.php`
**Estimated Time:** 15 minutes
**Impact:** Products automatically get default category

**Actions:**
1. Add `auto_assign_default_category()` method
2. Register hook in `init()`
3. Test with new products

### Step 4: Bulk Actions (MEDIUM PRIORITY)
**File:** `src/Admin/CategoryFields.php`
**Estimated Time:** 20 minutes
**Impact:** Faster category status management

**Actions:**
1. Add `add_custom_bulk_actions()` method
2. Add `handle_custom_bulk_actions()` method
3. Add `add_notice_query_args()` method (optional)
4. Register hooks in `init()`
5. Test bulk operations

---

## FINAL FILE STRUCTURE

### Modified File: `src/Admin/CategoryFields.php`

**Expected Structure After Implementation:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

final class CategoryFields {
    // Existing constants if any
    
    /**
     * Initialize category fields
     */
    public function init(): void {
        // Add form fields
        add_action( 'aps_category_add_form_fields', [ $this, 'add_category_fields' ] );
        add_action( 'aps_category_edit_form_fields', [ $this, 'edit_category_fields' ] );
        add_action( 'created_aps_category', [ $this, 'save_category_fields' ], 10, 2 );
        add_action( 'edited_aps_category', [ $this, 'save_category_fields' ], 10, 2 );
        
        // NEW: Custom columns
        add_filter( 'manage_edit-aps_category_columns', [ $this, 'add_custom_columns' ] );
        add_filter( 'manage_aps_category_custom_column', [ $this, 'render_custom_columns' ], 10, 3 );
        
        // NEW: Default category protection
        add_filter( 'pre_delete_term', [ $this, 'protect_default_category' ], 10, 2 );
        
        // NEW: Auto-assignment
        add_action( 'save_post_aps_product', [ $this, 'auto_assign_default_category' ], 10, 3 );
        
        // NEW: Bulk actions
        add_filter( 'bulk_actions-edit-aps_category', [ $this, 'add_custom_bulk_actions' ] );
        add_filter( 'handle_bulk_actions-edit-aps_category', [ $this, 'handle_custom_bulk_actions' ], 10, 3 );
        add_filter( 'redirect_term_location', [ $this, 'add_notice_query_args' ] );
    }
    
    // EXISTING: add_category_fields()
    // EXISTING: edit_category_fields()
    // EXISTING: render_category_fields()
    // EXISTING: save_category_fields()
    
    // NEW: add_custom_columns()
    // NEW: render_custom_columns()
    // NEW: protect_default_category()
    // NEW: auto_assign_default_category()
    // NEW: add_custom_bulk_actions()
    // NEW: handle_custom_bulk_actions()
    // NEW: add_notice_query_args()
}
```

---

## TESTING CHECKLIST

After implementing each feature:

### Custom Columns
- [ ] Categories page shows Featured column
- [ ] Categories page shows Default column
- [ ] Categories page shows Status column
- [ ] Featured column shows star icon when featured
- [ ] Default column shows home icon when default
- [ ] Status column shows Published/Draft with icons

### Default Category Protection
- [ ] Default category cannot be deleted permanently
- [ ] Error message shows when trying to delete default
- [ ] Back link returns to categories page
- [ ] Other categories can still be deleted

### Product Auto-Assignment
- [ ] New product without category gets default category
- [ ] Existing product without category gets default category on save
- [ ] Auto-assignment logged to error_log
- [ ] Products with existing categories are not affected
- [ ] Auto-save doesn't trigger auto-assignment
- [ ] Revisions don't trigger auto-assignment
- [ ] Trashed posts don't trigger auto-assignment

### Bulk Actions
- [ ] Bulk actions dropdown shows "Move to Draft"
- [ ] Bulk actions dropdown shows "Move to Trash"
- [ ] "Move to Draft" updates status to draft
- [ ] "Move to Trash" updates status to draft
- [ ] Default category is skipped in bulk actions
- [ ] Success notice shows after bulk action
- [ ] Count in notice is accurate
- [ ] Redirect preserves query args

---

## SUMMARY

**Total Features to Implement:** 4
**Total Lines of Code:** ~200
**File to Modify:** 1 (`src/Admin/CategoryFields.php`)
**Estimated Time:** 1 hour
**Complexity:** MEDIUM
**Impact:** HIGH (completes Section 2 to 100%)

**Completion After Implementation:**
- ✅ Custom Columns: Feature 64-66 complete
- ✅ Default Category Protection: Feature 68 complete
- ✅ Auto-Assignment: Feature 69 complete
- ✅ Bulk Actions: Feature 64-66 complete
- ✅ **Section 2 Status: 32/32 (100%)**

---

**Generated:** 2026-01-24  
**Status:** READY FOR IMPLEMENTATION  
**Next Step:** Implement features in order listed above