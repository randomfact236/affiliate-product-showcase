# Custom "Add New Product" Button Analysis Report

**Generated:** 2026-01-31T17:04:11.000000  
**Plugin:** Affiliate Product Showcase  
**Directory:** `wp-content/plugins/affiliate-product-showcase`

---

## Executive Summary

This report documents all custom implementations of "Add New Product" buttons that override WordPress's native functionality. The plugin uses a multi-layered approach to redirect users from native WordPress editor to a custom WooCommerce-style single-page form.

**Total Custom Button Implementations Found:** 4

---

## Instance #1: Products Page Header Button

**File Path:** `src/Admin/partials/products-page.php`  
**Line Range:** 26-29

### Hook Registration
No specific hook - This is a static HTML element rendered in the template.

### Complete Code Block
```php
<a href="<?php echo esc_url(admin_url('admin.php?page=aps-add-product')); ?>" 
   class="page-title-action">
    <?php esc_html_e('Add New Product', 'affiliate-product-showcase'); ?>
</a>
```

### Purpose
This button appears in the page header of the products listing page (`edit.php?post_type=aps_product`). It uses WordPress's native `.page-title-action` class to position the button next to the page title, mimicking WordPress core UI.

### Associated CSS
The `.page-title-action` class is styled by WordPress core. However, the plugin has a CSS override in `assets/css/admin-table-filters.css`:

```css
/* Hide native WordPress "Add New" button on products list page */
body.post-type-aps_product.edit-php .page-title-action,
body.post-type-aps_product.edit-php .wrap .page-title-action {
    display: none !important;
}
```

**Note:** The CSS hides WordPress's native button, but this custom button still renders because it's in a custom template file, not the native WordPress page.

### Conditional Logic
Always displayed on the products page (no conditional logic).

---

## Instance #2: JavaScript Redirect Script

**File Path:** `src/Admin/Enqueue.php`  
**Line Range:** 417-447

### Hook Registration
```php
add_action( 'admin_footer', [ $this, 'printRedirectScript' ] );
```

**Callback Function:** `printRedirectScript()` (Lines 417-447)

### Complete Code Block
```php
/**
 * Print inline JavaScript to redirect "Add New" button
 *
 * @return void
 */
public function printRedirectScript(): void {
    global $pagenow;
    
    // Only on products list page
    if ( $pagenow !== 'edit.php' || ! isset( $_GET['post_type'] ) || $_GET['post_type'] !== 'aps_product' ) {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Redirect "Add New" button to custom WooCommerce-style page
        $('.page-title-action').each(function() {
            const $button = $(this);
            const href = $button.attr('href');
            if (href && href.includes('post-new.php?post_type=aps_product')) {
                $button.attr('href', '<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>');
            }
        });
        
        // Also redirect top "Add New" link in admin menu
        $('#menu-posts-aps_product .wp-submenu a').each(function() {
            const $link = $(this);
            const href = $link.attr('href');
            if (href && href.includes('post-new.php?post_type=aps_product')) {
                $link.attr('href', '<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>');
            }
        });
    });
    </script>
    <?php
}
```

### Purpose
This JavaScript intercepts any remaining WordPress native "Add New" buttons that might appear and redirects their href to the custom add-product page. It targets:
1. The `.page-title-action` button in the page header
2. The "Add New" link in the admin submenu

### Conditional Logic
Only executes on `edit.php` page when `post_type=aps_product`.

### Associated JavaScript Event Handlers
- jQuery `.ready()` event to ensure DOM is loaded
- `.each()` loop to handle multiple button instances
- `href.includes()` check to verify it's the correct button

---

## Instance #3: Dashboard Page Welcome Banner Button

**File Path:** `src/Admin/partials/dashboard-page.php`  
**Line Range:** 36-39

### Hook Registration
No specific hook - This is a static HTML element rendered in the template.

### Complete Code Block
```php
<a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="button button-primary button-large">
    <?php esc_html_e('Add New Product', 'affiliate-product-showcase'); ?>
</a>
```

### Purpose
This button appears in the welcome banner of the plugin's dashboard page. It's a prominent call-to-action button styled with WordPress button classes (`.button`, `.button-primary`, `.button-large`).

### Associated CSS
Inline styles in the same file (Lines 208-254):
```css
.aps-welcome-actions .button {
    background: #ffffff;
    color: #667eea;
    border: none;
    font-weight: 600;
    transition: all 0.2s ease;
}

.aps-welcome-actions .button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
```

### Conditional Logic
Always displayed on the dashboard page (no conditional logic).

---

## Instance #4: Dashboard Page Empty State Button

**File Path:** `src/Admin/partials/dashboard-page.php`  
**Line Range:** 168-170

### Hook Registration
No specific hook - This is a static HTML element rendered in the template.

### Complete Code Block
```php
<a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="button button-primary">
    <?php esc_html_e('Add First Product', 'affiliate-product-showcase'); ?>
</a>
```

### Purpose
This button appears in the "empty state" when no products exist. It encourages users to add their first product.

### Conditional Logic
Only displayed when `$recent_products->have_posts()` returns false (no products found).

---

## Instance #5: Dashboard Page Quick Actions Button

**File Path:** `src/Admin/partials/dashboard-page.php`  
**Line Range:** 91-94

### Hook Registration
No specific hook - This is a static HTML element rendered in the template.

### Complete Code Block
```php
<a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="aps-quick-action">
    <span class="aps-action-icon">➕</span>
    <span class="aps-action-text"><?php esc_html_e('Add Product', 'affiliate-product-showcase'); ?></span>
</a>
```

### Purpose
This button appears in the "Quick Actions" section of the dashboard, providing easy access to add a new product.

### Associated CSS
Inline styles in the same file (Lines 318-343):
```css
.aps-quick-action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #374151;
    transition: all 0.2s ease;
}

.aps-quick-action:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
    transform: translateY(-2px);
}
```

### Conditional Logic
Always displayed in the Quick Actions section (no conditional logic).

---

## Supporting Infrastructure: Menu Override

**File Path:** `src/Admin/Menu.php`  
**Line Range:** 39-40, 554-580

### Hook Registration
```php
// Remove default Add New - run VERY late after admin_menu
add_action( 'admin_menu', [ $this, 'removeDefaultAddNewMenu' ], PHP_INT_MAX );
```

**Callback Function:** `removeDefaultAddNewMenu()` (Lines 554-580)

### Complete Code Block
```php
/**
 * Remove WordPress default "Add New" menu
 *
 * Removes the default WordPress "Add New" submenu that's automatically
 * created for custom post types. We have our custom "Add Product"
 * submenu instead (just like WooCommerce does).
 *
 * Uses triple approach: WordPress helper + manual array cleanup + late execution
 *
 * @return void
 */
public function removeDefaultAddNewMenu(): void {
    global $submenu;

    $parent_slug = 'edit.php?post_type=aps_product';
    $old_add_new_slug = 'post-new.php?post_type=aps_product';

    // Remove using WordPress helper (most reliable)
    remove_submenu_page( $parent_slug, $old_add_new_slug );

    // Also manually clean submenu array (fallback)
    if ( isset( $submenu[ $parent_slug ] ) ) {
        foreach ( $submenu[ $parent_slug ] as $index => $item ) {
            if ( isset( $item[2] ) && $item[2] === $old_add_new_slug ) {
                unset( $submenu[ $parent_slug ][ $index ] );
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( '[APS] Default "Add New" submenu removed successfully' );
                }
                break;
            }
        }
    }
    
    // Re-index array to prevent gaps
    if ( isset( $submenu[ $parent_slug ] ) ) {
        $submenu[ $parent_slug ] = array_values( $submenu[ $parent_slug ] );
    }
}
```

### Purpose
This function removes WordPress's automatically-generated "Add New" submenu item from the admin menu. It uses a triple-layer approach:
1. `remove_submenu_page()` - WordPress helper function
2. Manual array cleanup - Direct manipulation of `$submenu` global
3. Late execution (PHP_INT_MAX priority) - Ensures it runs after all menus are registered

### Conditional Logic
Always executes on `admin_menu` hook (no page-specific conditions).

---

## Supporting Infrastructure: Native Editor Redirect

**File Path:** `src/Admin/Menu.php`  
**Line Range:** 20-22, 124-147

### Hook Registration
```php
// Redirect native editor to custom page (using load hook - more reliable)
add_action( 'load-post.php', [ $this, 'redirectNativeEditor' ] );
add_action( 'load-post-new.php', [ $this, 'redirectNativeEditor' ] );
```

**Callback Function:** `redirectNativeEditor()` (Lines 124-147)

### Complete Code Block
```php
/**
 * Redirect native editor to custom Add Product page
 *
 * Redirects both post-new.php (Add New) and post.php (Edit)
 * to our custom single-page form.
 *
 * Uses load-post.php and load-post-new.php hooks for more reliable detection.
 *
 * @return void
 */
public function redirectNativeEditor(): void {
    // Check if we're editing an aps_product
    if ( ! isset( $_GET['post'] ) && ! isset( $_GET['post_type'] ) ) {
        return;
    }
    
    // Handle edit existing product
    $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
    if ( $post_id > 0 ) {
        $post_type = get_post_type( $post_id );
        if ( $post_type === 'aps_product' ) {
            // Redirect to custom form with post ID
            wp_safe_redirect( admin_url( 'edit.php?post_type=aps_product&page=add-product&post=' . $post_id ) );
            exit;
        }
    }
    
    // Handle add new product
    if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'aps_product' ) {
        // Redirect to custom add form
        wp_safe_redirect( self::getAddProductUrl() );
        exit;
    }
}
```

### Purpose
This function intercepts direct access to WordPress's native editor pages and redirects users to the custom add-product form. It handles both:
1. `post-new.php?post_type=aps_product` - Add New page
2. `post.php?post=ID` - Edit existing product page

### Conditional Logic
- Only redirects when `post_type=aps_product` or when editing an `aps_product` post
- Uses `load-post.php` and `load-post-new.php` hooks which fire before the page renders

---

## Supporting Infrastructure: Edit Link Filter

**File Path:** `src/Admin/Menu.php`  
**Line Range:** 25, 160-169

### Hook Registration
```php
// Filter all edit post links to point to custom form
add_filter( 'get_edit_post_link', [ $this, 'filterEditPostLink' ], 10, 3 );
```

**Callback Function:** `filterEditPostLink()` (Lines 160-169)

### Complete Code Block
```php
/**
 * Filter edit post link to use custom form
 *
 * Redirects all "Edit" links throughout the admin area to use
 * our custom add-product form instead of the native WordPress editor.
 *
 * @param string $url     The edit post link
 * @param int    $post_id  The post ID
 * @param string $context  The link context
 * @return string Modified URL pointing to custom form
 */
public function filterEditPostLink( string $url, int $post_id, string $context ): string {
    $post_type = get_post_type( $post_id );
    
    // Only redirect for aps_product post type
    if ( $post_type === 'aps_product' ) {
        return admin_url( 'edit.php?post_type=aps_product&page=add-product&post=' . $post_id );
    }
    
    return $url;
}
```

### Purpose
This filter modifies all "Edit" links throughout the WordPress admin (including in tables, lists, and other plugins) to point to the custom add-product form instead of the native editor.

### Conditional Logic
Only modifies links for `aps_product` post type.

---

## Removal Instructions

### Critical: Understanding the Architecture

The plugin uses a **custom WooCommerce-style single-page form** for adding/editing products. This is an intentional design, not a bug. Removing these components would break the entire product management workflow.

**DO NOT REMOVE** these components unless you want to:
1. Use WordPress's native block editor for products
2. Lose all custom product fields (logo, price, ribbon, etc.)
3. Lose the custom single-page form UX

### If You MUST Remove (Not Recommended)

#### Option 1: Remove Custom Button from Products Page Header

**File:** `src/Admin/partials/products-page.php`  
**Action:** Delete lines 26-29

```php
// DELETE THESE LINES (26-29):
<a href="<?php echo esc_url(admin_url('admin.php?page=aps-add-product')); ?>" 
   class="page-title-action">
    <?php esc_html_e('Add New Product', 'affiliate-product-showcase'); ?>
</a>
```

**Impact:** Users will see WordPress's native "Add New" button (if not hidden by CSS).

---

#### Option 2: Remove JavaScript Redirect Script

**File:** `src/Admin/Enqueue.php`  
**Action:** Delete the hook registration (line 31) AND the entire function (lines 417-447)

```php
// DELETE LINE 31:
add_action( 'admin_footer', [ $this, 'printRedirectScript' ] );

// DELETE LINES 417-447 (entire printRedirectScript function)
```

**Impact:** Any remaining WordPress native buttons will point to the native editor.

---

#### Option 3: Remove Dashboard Buttons

**File:** `src/Admin/partials/dashboard-page.php`  
**Action:** Delete specific button instances

**Welcome Banner Button (Lines 36-39):**
```php
// DELETE THESE LINES (36-39):
<a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="button button-primary button-large">
    <?php esc_html_e('Add New Product', 'affiliate-product-showcase'); ?>
</a>
```

**Empty State Button (Lines 168-170):**
```php
// DELETE THESE LINES (168-170):
<a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="button button-primary">
    <?php esc_html_e('Add First Product', 'affiliate-product-showcase'); ?>
</a>
```

**Quick Actions Button (Lines 91-94):**
```php
// DELETE THESE LINES (91-94):
<a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="aps-quick-action">
    <span class="aps-action-icon">➕</span>
    <span class="aps-action-text"><?php esc_html_e('Add Product', 'affiliate-product-showcase'); ?></span>
</a>
```

**Impact:** Dashboard will have fewer quick action buttons.

---

#### Option 4: Remove Menu Override (NOT RECOMMENDED)

**File:** `src/Admin/Menu.php`  
**Action:** Delete the hook registration (line 40) AND the entire function (lines 554-580)

```php
// DELETE LINE 40:
add_action( 'admin_menu', [ $this, 'removeDefaultAddNewMenu' ], PHP_INT_MAX );

// DELETE LINES 554-580 (entire removeDefaultAddNewMenu function)
```

**Impact:** WordPress's native "Add New" submenu will appear in the admin menu.

---

#### Option 5: Remove Native Editor Redirect (NOT RECOMMENDED)

**File:** `src/Admin/Menu.php`  
**Action:** Delete the hook registrations (lines 21-22) AND the entire function (lines 124-147)

```php
// DELETE LINES 21-22:
add_action( 'load-post.php', [ $this, 'redirectNativeEditor' ] );
add_action( 'load-post-new.php', [ $this, 'redirectNativeEditor' ] );

// DELETE LINES 124-147 (entire redirectNativeEditor function)
```

**Impact:** Users accessing `post-new.php?post_type=aps_product` will see WordPress's native editor instead of the custom form.

---

#### Option 6: Remove Edit Link Filter (NOT RECOMMENDED)

**File:** `src/Admin/Menu.php`  
**Action:** Delete the hook registration (line 25) AND the entire function (lines 160-169)

```php
// DELETE LINE 25:
add_filter( 'get_edit_post_link', [ $this, 'filterEditPostLink' ], 10, 3 );

// DELETE LINES 160-169 (entire filterEditPostLink function)
```

**Impact:** "Edit" links will point to WordPress's native editor.

---

## Core WordPress Functionality Impact Assessment

### What Will NOT Break

Removing these components will **NOT** affect:
- WordPress core functionality for other post types
- Other plugins' functionality
- WordPress admin menu structure (for other plugins)
- User authentication and permissions
- Database operations

### What WILL Break

Removing these components **WILL** break:
1. **Custom Product Form:** Users will not be able to access the custom WooCommerce-style form
2. **Product Editing:** The custom single-page edit form will be inaccessible
3. **Custom Fields:** Logo, price, ribbon, category, tag, and featured fields will not be available in the native editor
4. **Custom UX:** The streamlined product management experience will be lost
5. **Product Creation:** Users will need to use WordPress's block editor which doesn't support the plugin's custom fields

### Recommended Approach

**DO NOT REMOVE** these components. Instead:

1. **If you want to restore the native button:** Remove the CSS hiding it in `assets/css/admin-table-filters.css` (lines 11-14)

2. **If you want to keep both:** Modify the custom button to point to the native editor:
   ```php
   <a href="<?php echo esc_url(admin_url('post-new.php?post_type=aps_product')); ?>" 
      class="page-title-action">
       <?php esc_html_e('Add New Product', 'affiliate-product-showcase'); ?>
   </a>
   ```

3. **If you want to improve the custom form:** Enhance `src/Admin/partials/add-product-page.php` instead of removing the redirect logic.

---

## Summary Table

| # | File | Lines | Type | Purpose | Recommended Action |
|---|-------|--------|---------|-------------------|
| 1 | `src/Admin/partials/products-page.php` | 26-29 | HTML | Keep - Part of custom UI |
| 2 | `src/Admin/Enqueue.php` | 417-447 | JavaScript | Keep - Ensures consistent UX |
| 3 | `src/Admin/partials/dashboard-page.php` | 36-39 | HTML | Keep - Dashboard CTA |
| 4 | `src/Admin/partials/dashboard-page.php` | 168-170 | HTML | Keep - Empty state UX |
| 5 | `src/Admin/partials/dashboard-page.php` | 91-94 | HTML | Keep - Quick action |
| 6 | `src/Admin/Menu.php` | 554-580 | PHP | Keep - Menu customization |
| 7 | `src/Admin/Menu.php` | 124-147 | PHP | Keep - Redirect to custom form |
| 8 | `src/Admin/Menu.php` | 160-169 | PHP | Keep - Link consistency |

---

## Conclusion

The Affiliate Product Showcase plugin intentionally uses a custom product management interface that overrides WordPress's native editor. All identified "Add New Product" button implementations are part of this intentional design and should **NOT** be removed unless you want to revert to using WordPress's native block editor for products.

If you choose to remove these components, you must also:
1. Update the custom post type registration to use the block editor
2. Migrate all custom fields to WordPress meta boxes
3. Update all product-related templates to work with the native editor
4. Test thoroughly to ensure no data loss occurs

**Recommendation:** Keep all components as-is. They provide a better UX for affiliate product management.
