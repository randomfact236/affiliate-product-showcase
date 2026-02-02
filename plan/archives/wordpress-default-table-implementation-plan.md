# WordPress Default Table Implementation Plan

**Status:** Ready for Implementation
**Created:** 2026-01-27
**Approach:** Pure WordPress Default Table + Extensions via Hooks

---

## 📋 Overview

Transform products list from custom `WP_List_Table` extension to pure WordPress default table, using only hooks for extensions.

**Goal:** Eliminate all custom table features, use WordPress native interface, add filters via hooks only.

---

## 🎯 Objectives

### Primary Goals
- ✅ Remove all custom table rendering logic
- ✅ Enable WordPress default status views (All/Published/Draft/Trash)
- ✅ Use WordPress default bulk actions
- ✅ Add custom filters via hooks only
- ✅ Reduce code maintenance burden
- ✅ Provide familiar WordPress interface

### Non-Goals (What We're NOT Doing)
- ❌ Custom column rendering (logo, category, tags, ribbon)
- ❌ Custom bulk actions (Set Featured, Reset Clicks, etc.)
- ❌ Custom table sorting logic
- ❌ Custom pagination logic
- ❌ Any UI customizations beyond filters

---

## 📊 Current State Analysis

### What We Have Now

**ProductsTable.php** (Will be DELETED):
- Extends `WP_List_Table`
- Custom columns: cb, id, logo, title, category, tags, ribbon, featured, price, status
- Custom bulk actions: Publish, Move to Draft, Set In/Out of Stock, Set/Unset Featured, Reset Clicks, Export CSV
- Custom filters: featured, category, tag, search
- `views()` method override (disables status views)
- Custom query building logic
- Custom pagination handling
- Custom sorting logic

**Enqueue.php** (Will be MODIFIED):
- Enqueues assets for `edit-aps_product` hook
- References `isProductsListPage()` method
- Currently handles custom table styles

### What WordPress Provides Natively

**Default Columns:**
- cb (checkbox)
- title (post title with edit link)
- date (post date)
- author (post author)
- categories (post categories)
- tags (post tags)
- comments (comment count)

**Default Bulk Actions:**
- Edit
- Trash
- Move to Trash
- Delete Permanently

**Default Status Views:**
- All (count)
- Published (count)
- Draft (count)
- Trash (count)
- Pending (count)

**Default Features:**
- Pagination
- Sorting (title, date, author)
- Search
- Per-page options
- Screen options

---

## 🚧 Implementation Phases

### Phase 1: Remove ProductsTable Class

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`

**Action:** DELETE this file entirely

**Reasoning:**
- All custom table logic is being removed
- WordPress provides native table handling
- No need for custom WP_List_Table extension

**Impact:**
- Remove ~400 lines of custom code
- Eliminate maintenance burden
- Use WordPress native table

---

### Phase 2: Remove ProductsTable Usage

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Admin.php`

**Current Code:**
```php
final class Admin {
    // No ProductsTable reference found in __construct
    // ProductsTable is not instantiated in init()
}
```

**Action:** NO CHANGES NEEDED

**Reasoning:**
- ProductsTable is not currently instantiated in Admin.php
- May be used elsewhere (need to search)

**Check Required:**
- Search all PHP files for `ProductsTable` usage
- Remove any instantiation or references

---

### Phase 3: Create Filter Extensions via Hooks

**New File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php`

**Purpose:** Add custom filters to WordPress default table using hooks

**Implementation:**

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Product Filters
 *
 * Adds custom filters to WordPress default products list table.
 * Uses hooks to extend WordPress native table interface.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class ProductFilters {
    
    /**
     * Initialize filters
     *
     * @return void
     */
    public function init(): void {
        // Add filters to top of table
        add_action('restrict_manage_posts', [$this, 'add_category_filter'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'add_tag_filter'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'add_featured_filter'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'add_search_filter'], 10, 2);
        
        // Handle filter queries
        add_action('pre_get_posts', [$this, 'handle_filters']);
    }
    
    /**
     * Add category filter dropdown
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_category_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $categories = get_terms([
            'taxonomy' => Constants::TAX_CATEGORY,
            'hide_empty' => false
        ]);
        
        if (empty($categories) || is_wp_error($categories)) {
            return;
        }
        
        $selected = isset($_GET['aps_category_filter']) ? (int) $_GET['aps_category_filter'] : 0;
        
        echo '<select name="aps_category_filter" id="aps_category_filter">';
        echo '<option value="0">' . esc_html__('All Categories', 'affiliate-product-showcase') . '</option>';
        
        foreach ($categories as $category) {
            $selected_attr = selected($selected, $category->term_id, false);
            echo sprintf(
                '<option value="%d" %s>%s</option>',
                $category->term_id,
                $selected_attr,
                esc_html($category->name)
            );
        }
        
        echo '</select>';
    }
    
    /**
     * Add tag filter dropdown
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_tag_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $tags = get_terms([
            'taxonomy' => Constants::TAX_TAG,
            'hide_empty' => false
        ]);
        
        if (empty($tags) || is_wp_error($tags)) {
            return;
        }
        
        $selected = isset($_GET['aps_tag_filter']) ? (int) $_GET['aps_tag_filter'] : 0;
        
        echo '<select name="aps_tag_filter" id="aps_tag_filter">';
        echo '<option value="0">' . esc_html__('All Tags', 'affiliate-product-showcase') . '</option>';
        
        foreach ($tags as $tag) {
            $selected_attr = selected($selected, $tag->term_id, false);
            echo sprintf(
                '<option value="%d" %s>%s</option>',
                $tag->term_id,
                $selected_attr,
                esc_html($tag->name)
            );
        }
        
        echo '</select>';
    }
    
    /**
     * Add featured filter checkbox
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_featured_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $is_checked = isset($_GET['featured_filter']) ? checked('1', $_GET['featured_filter'], false) : '';
        
        echo '<label class="aps-featured-filter-label">';
        echo '<input type="checkbox" name="featured_filter" value="1" ' . $is_checked . ' />';
        echo esc_html__('Featured Only', 'affiliate-product-showcase');
        echo '</label>';
    }
    
    /**
     * Add custom search input
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_search_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $search_value = isset($_GET['aps_search']) ? esc_attr($_GET['aps_search']) : '';
        
        echo '<input type="text" name="aps_search" id="aps_search" ';
        echo 'placeholder="' . esc_attr__('Search products...', 'affiliate-product-showcase') . '" ';
        echo 'value="' . $search_value . '" />';
    }
    
    /**
     * Handle custom filters in query
     *
     * @param \WP_Query $query WordPress query object
     * @return void
     */
    public function handle_filters(\WP_Query $query): void {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || 'edit-aps_product' !== $screen->id) {
            return;
        }
        
        // Category filter
        if (isset($_GET['aps_category_filter']) && !empty($_GET['aps_category_filter'])) {
            $category_id = (int) $_GET['aps_category_filter'];
            if ($category_id > 0) {
                $tax_query = $query->get('tax_query');
                if (empty($tax_query)) {
                    $tax_query = [];
                }
                
                $tax_query[] = [
                    'taxonomy' => Constants::TAX_CATEGORY,
                    'terms' => $category_id,
                ];
                
                $tax_query['relation'] = 'AND';
                $query->set('tax_query', $tax_query);
            }
        }
        
        // Tag filter
        if (isset($_GET['aps_tag_filter']) && !empty($_GET['aps_tag_filter'])) {
            $tag_id = (int) $_GET['aps_tag_filter'];
            if ($tag_id > 0) {
                $tax_query = $query->get('tax_query');
                if (empty($tax_query)) {
                    $tax_query = [];
                }
                
                $tax_query[] = [
                    'taxonomy' => Constants::TAX_TAG,
                    'terms' => $tag_id,
                ];
                
                $tax_query['relation'] = 'AND';
                $query->set('tax_query', $tax_query);
            }
        }
        
        // Featured filter
        if (isset($_GET['featured_filter']) && '1' === $_GET['featured_filter']) {
            $meta_query = $query->get('meta_query');
            if (empty($meta_query)) {
                $meta_query = [];
            }
            
            $meta_query[] = [
                'key' => 'aps_featured',
                'value' => '1',
                'compare' => '=',
            ];
            
            $query->set('meta_query', $meta_query);
        }
        
        // Custom search
        if (isset($_GET['aps_search']) && !empty($_GET['aps_search'])) {
            $search_term = sanitize_text_field($_GET['aps_search']);
            $query->set('s', $search_term);
        }
    }
}
```

---

### Phase 4: Register ProductFilters in Admin

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Admin.php`

**Changes:**

```php
final class Admin {
    private ProductFilters $product_filters;
    
    public function __construct(
        private Assets $assets,
        private ProductService $product_service,
        private Headers $headers,
        Menu $menu,
        ProductFormHandler $form_handler,
        RibbonFields $ribbon_fields,
        Settings $settings
    ) {
        $this->settings = $settings;
        $this->form_handler = $form_handler;
        $this->menu = $menu;
        $this->category_fields = new CategoryFields();
        $this->tag_fields = new TagFields();
        $this->ribbon_fields = $ribbon_fields;
        $this->product_filters = new ProductFilters();
    }

    public function init(): void {
        // Initialize settings
        $this->settings->init();
        
        // Initialize category components (WordPress native + custom enhancements)
        $this->category_fields->init();
        
        // Initialize tag components (WordPress native + custom enhancements)
        $this->tag_fields->init();
        
        // Initialize ribbon components (WordPress native + custom enhancements)
        $this->ribbon_fields->init();
        
        // Initialize product filters for WordPress default table
        $this->product_filters->init();
        
        $this->headers->init();
    }
}
```

---

### Phase 5: Update Enqueue.php

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Enqueue.php`

**Changes:**

```php
public function enqueue_admin_assets(string $hook): void {
    // Products list page - WordPress default table
    if ($hook === 'edit-aps_product') {
        // Enqueue filter styles (for custom filters added via hooks)
        wp_enqueue_style(
            'aps-admin-table-filters',
            $this->assets->get_asset_url('css/admin-table-filters.css'),
            [],
            $this->assets->get_version()
        );
        
        // No custom table JS needed - using WordPress native
    }
    
    // Other admin pages...
}
```

---

### Phase 6: Create Filter Styles

**New File:** `wp-content/plugins/affiliate-product-showcase/assets/css/admin-table-filters.css`

**Implementation:**

```css
/**
 * Product Filters Styles
 *
 * Styles for custom filters added to WordPress default table.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

/* Filter container in tablenav */
.aps-featured-filter-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: 1rem;
}

.aps-featured-filter-label input[type="checkbox"] {
    margin: 0;
}

/* Filter dropdowns */
#aps_category_filter,
#aps_tag_filter {
    margin-right: 0.5rem;
}

/* Custom search */
#aps_search {
    margin-left: 0.5rem;
    padding: 6px 8px;
}
```

---

### Phase 7: Update ServiceProvider

**File:** `wp-content/plugins/affiliate-product-showcase/src/Plugin/ServiceProvider.php`

**Current Code:**
```php
$this->container->register(
    ProductTable::class,
    static fn (ContainerInterface $c): ProductTable => new ProductTable(
        $c->get(ProductRepository::class)
    )
);
```

**Action:** REMOVE this registration

**Reasoning:**
- ProductsTable class is being deleted
- No longer needed in DI container
- WordPress handles table natively

---

## 📝 Implementation Checklist

### Phase 1: Remove Custom Table
- [ ] Delete `ProductsTable.php` file
- [ ] Search for `ProductsTable` references in codebase
- [ ] Remove all `ProductsTable` usage
- [ ] Remove `ProductsTable` from DI container

### Phase 2: Create Filter Extensions
- [ ] Create `ProductFilters.php` class
- [ ] Implement `add_category_filter()` method
- [ ] Implement `add_tag_filter()` method
- [ ] Implement `add_featured_filter()` method
- [ ] Implement `add_search_filter()` method
- [ ] Implement `handle_filters()` method

### Phase 3: Integrate Filters
- [ ] Register `ProductFilters` in `Admin.php`
- [ ] Add to constructor
- [ ] Initialize in `init()` method

### Phase 4: Update Enqueue
- [ ] Remove custom table references
- [ ] Add filter styles enqueue
- [ ] Remove custom table JS enqueue
- [ ] Update `isProductsListPage()` method (if needed)

### Phase 5: Create Styles
- [ ] Create `admin-table-filters.css`
- [ ] Add filter dropdown styles
- [ ] Add checkbox styles
- [ ] Add search input styles

### Phase 6: Update DI Container
- [ ] Remove `ProductsTable` registration from `ServiceProvider.php`
- [ ] Remove `ProductRepository` dependency (if only used by ProductsTable)

### Phase 7: Testing
- [ ] Test products list page loads
- [ ] Test category filter works
- [ ] Test tag filter works
- [ ] Test featured filter works
- [ ] Test search filter works
- [ ] Test status views appear (All/Published/Draft)
- [ ] Test WordPress bulk actions work
- [ ] Test pagination works
- [ ] Test sorting works

---

## 🔄 Migration Impact

### Code Changes Summary
- **Files Deleted:** 1 (`ProductsTable.php`)
- **Files Created:** 2 (`ProductFilters.php`, `admin-table-filters.css`)
- **Files Modified:** 3 (`Admin.php`, `Enqueue.php`, `ServiceProvider.php`)
- **Lines Removed:** ~400 lines (custom table logic)
- **Lines Added:** ~200 lines (filter extensions)

### Functional Changes
- **Removed Features:**
  - Custom columns (logo, category, tags, ribbon, featured, price, status)
  - Custom bulk actions (Set Featured, Reset Clicks, etc.)
  - Custom pagination
  - Custom sorting

- **Added Features:**
  - WordPress status views (All/Published/Draft/Trash)
  - WordPress bulk actions (Edit, Trash, Delete)
  - Custom filters (category, tag, featured, search)
  - Familiar WordPress interface

### User Impact
- **Positive:**
  - Familiar WordPress interface
  - Built-in status views
  - Less maintenance overhead
  - Better accessibility (WordPress native)

- **Negative:**
  - No product logo in table
  - No category/tags/ribbon badges in table
  - No featured star in table
  - No price display in table
  - No custom bulk actions

---

## ⚠️ Risks & Considerations

### Risks
1. **Loss of Information Display**
   - Products won't show logo, price, category, tags, ribbon, status
   - Users must click through to see product details
   - May impact productivity for power users

2. **Loss of Custom Bulk Actions**
   - No "Set Featured" bulk action
   - No "Reset Clicks" bulk action
   - No "Export to CSV" bulk action
   - Must use WordPress defaults (Edit, Trash, Delete)

3. **User Adaptation**
   - Users familiar with current interface may need training
   - Loss of "at-a-glance" product information
   - More clicks to access product details

### Mitigations
1. **Provide Documentation**
   - Create user guide for new interface
   - Document filter usage
   - Explain status views benefits

2. **Monitor User Feedback**
   - Gather feedback after deployment
   - Track usage patterns
   - Be prepared to iterate

3. **Consider Future Enhancements**
   - Can add custom columns via hooks later if needed
   - Can add custom bulk actions via hooks later if needed
   - Maintain flexibility to extend

---

## 📚 WordPress Hooks Reference

### Used Hooks

**`restrict_manage_posts`**
- **Purpose:** Add custom filters to table
- **Parameters:** `$post_type`, `$which` (top/bottom)
- **Usage:** Add dropdowns, checkboxes, inputs

**`pre_get_posts`**
- **Purpose:** Modify query before execution
- **Parameters:** `\WP_Query $query`
- **Usage:** Apply filters to query

### Available (Not Used)

**`manage_{$post_type}_posts_columns`**
- **Purpose:** Add custom columns to table
- **Parameters:** `$columns` array
- **Usage:** Would need to add custom columns back

**`manage_{$post_type}_posts_custom_column`**
- **Purpose:** Render custom column content
- **Parameters:** `$column_name`, `$post_id`
- **Usage:** Would need to render column content

**`bulk_actions-{$post_type}`**
- **Purpose:** Add custom bulk actions
- **Parameters:** `$actions` array
- **Usage:** Would need to add custom bulk actions back

**`handle_bulk_actions-{$post_type}`**
- **Purpose:** Handle custom bulk action execution
- **Parameters:** `$redirect_to`, `$action`, `$post_ids`
- **Usage:** Would need to handle bulk action logic

---

## ✅ Success Criteria

### Functional Requirements
- [ ] Products list page displays with WordPress default table
- [ ] Status views appear (All/Published/Draft/Trash with counts)
- [ ] WordPress bulk actions work (Edit, Trash, Delete)
- [ ] Category filter dropdown appears and works
- [ ] Tag filter dropdown appears and works
- [ ] Featured filter checkbox appears and works
- [ ] Search filter input appears and works
- [ ] Pagination works correctly
- [ ] Sorting works correctly
- [ ] All filters work together (category + tag + featured)

### Code Quality Requirements
- [ ] No `ProductsTable.php` file exists
- [ ] No `ProductsTable` references in codebase
- [ ] All custom table logic removed
- [ ] Filters implemented via hooks only
- [ ] Code follows PSR-12 standards
- [ ] Code has proper PHPDoc
- [ ] No CSS hiding of WordPress elements

### Performance Requirements
- [ ] Page loads quickly (no custom query building)
- [ ] Filters execute efficiently
- [ ] No N+1 query issues
- [ ] Proper caching in place

### Accessibility Requirements
- [ ] All filters keyboard accessible
- [ ] All filters have proper labels
- [ ] Screen reader compatible
- [ ] Focus management correct

---

## 📖 Post-Implementation Tasks

### Documentation
- [ ] Update user guide with new interface
- [ ] Update admin documentation
- [ ] Create filter usage guide
- [ ] Document status views

### Testing
- [ ] Test on different browsers
- [ ] Test on different screen sizes
- [ ] Test with accessibility tools
- [ ] Test with large datasets

### Monitoring
- [ ] Track filter usage
- [ ] Monitor page load times
- [ ] Collect user feedback
- [ ] Identify issues early

---

## 🎓 Lessons Learned

### What Worked
- WordPress hooks provide powerful extension capabilities
- Filters can be added without custom table
- Native interface reduces maintenance

### What to Avoid
- Don't hide WordPress elements via CSS
- Don't duplicate WordPress functionality
- Don't over-customize native interface

### Best Practices
- Use hooks for extensions
- Keep WordPress native behavior
- Document all changes
- Test thoroughly

---

**Version:** 1.0.0  
**Status:** Ready for Implementation  
**Estimated Complexity:** Medium  
**Risk Level:** Medium (user interface changes)