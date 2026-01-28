# Products Table Hybrid UI Design Plan

## Executive Summary

**Date:** 2026-01-27  
**Approach:** WordPress Default + Custom Extensions (Hybrid)  
**Goal:** Create clean, maintainable products page UI using WordPress defaults with custom filters

---

## Feature List

### WordPress Default Features (KEEP)

#### Bulk Actions
- ✅ **Edit** - Native WordPress bulk edit
- ✅ **Move to Trash** - Native WordPress trash action
- ✅ **Delete Permanently** - Native WordPress delete action
- ✅ **Quick Edit** - Native WordPress quick edit modal
- ✅ **Bulk Edit** - Native WordPress bulk edit modal

#### Status Filters
- ✅ **All** - Show all products
- ✅ **Published** - Show published products only
- ✅ **Draft** - Show draft products only
- ✅ **Trashed** - Show trashed products only
- ✅ **Pending** - Show pending review products

#### Native Table Features
- ✅ **Column Sorting** - Sort by any column
- ✅ **Pagination** - Navigate through pages
- ✅ **Screen Options** - Show/hide columns
- ✅ **Search** - Native WordPress search
- ✅ **Row Actions** - Edit, Trash, Quick Edit, View

### Custom Features (ADD)

#### Custom Bulk Actions
- ➕ **Set Featured** - Mark selected products as featured
- ➕ **Unset Featured** - Remove featured status from selected products
- ➕ **Set In Stock** - Mark selected products as in stock - Not Needed
- ➕ **Set Out of Stock** - Mark selected products as out of stock- Not Needed
- ➕ **Export to CSV** - Export selected products to CSV file

#### Custom Filters
- ➕ **Featured Toggle** - Filter by featured products only
- ➕ **Category Filter** - Filter by product category
- ➕ **Tag Filter** - Filter by product tag
- ➕ **Ribbon Filter** - Filter by product ribbon
- ➕ **Stock Status Filter** - Filter by stock status (In Stock / Out of Stock)
- ➕ **Price Range Filter** - Filter by minimum and maximum price
- ➕ **Custom Search** - Enhanced search with filters
- ➕ **Apply Filters Button** - Apply selected filters
- ➕ **Clear Filters Button** - Reset all filters

#### Custom Table Columns
- ➕ **Image Column** - Product thumbnail (50x50px)
- ➕ **Category Column** - Display product category
- ➕ **Tag Column** - Display product tag
- ➕ **Price Column** - Display price with discount badge
- ➕ **Stock Column** - Display stock status (In Stock / Out of Stock)
- ➕ **Featured Column** - Display featured badge (🌟)

#### Visual Enhancements
- ➕ **Discount Badge** - Show percentage off when original price exists
- ➕ **Stock Status Badges** - Color-coded stock indicators (green/red)
- ➕ **Featured Badge** - Star icon for featured products
- ➕ **Image Placeholders** - Dashicons when no thumbnail
- ➕ **Responsive Layout** - Mobile-friendly filter layout

### User Experience Features

#### Accessibility
- ✅ **Keyboard Navigation** - Full keyboard support
- ✅ **Screen Reader Support** - ARIA labels on interactive elements
- ✅ **Focus Indicators** - Visible focus states
- ✅ **Color Contrast** - WCAG AA/AAA compliant colors

#### Usability
- ✅ **Familiar Interface** - WordPress admin design patterns
- ✅ **Clear Feedback** - Success/error messages after actions
- ✅ **Loading States** - Visual feedback during operations
- ✅ **Empty States** - Helpful message when no products found
- ✅ **Confirmation Dialogs** - Confirm destructive actions (delete, trash)

#### Performance
- ✅ **Lazy Loading** - Images load only when visible
- ✅ **Efficient Queries** - Optimized database queries
- ✅ **Caching** - Cache taxonomy terms for filters
- ✅ **Minimal DOM** - Lightweight HTML structure

### Admin Features

#### Export Functionality
- ➕ **CSV Export** - Export all fields (ID, Title, Price, Category, Tag, Ribbon, Stock, Featured)
- ➕ **Bulk Export** - Export selected products
- ➕ **All Export** - Export all filtered products
- ➕ **Timestamped Files** - Include datetime in filename

#### Bulk Operations
- ➕ **Batch Updates** - Update multiple products at once
- ➕ **Progress Feedback** - Show progress during bulk operations
- ➕ **Success Messages** - Count of updated products
- ➕ **Error Handling** - Graceful handling of failed updates

---

## Current State

### Problems with Previous Approach

1. **Duplicate UI Layers**
   - WordPress native bulk actions hidden
   - Custom bulk actions implemented separately
   - Two separate status count sections
   - User confusion about which UI to use

2. **High Maintenance Burden**
   - All bulk action logic must be implemented manually
   - Custom CSS to hide WordPress defaults
   - Duplicate functionality across multiple files
   - WordPress updates may break custom implementations

3. **Performance Overhead**
   - Extra CSS files to hide WordPress UI
   - Extra JavaScript for custom bulk actions
   - Duplicate DOM elements for same functionality

### Files Affected

**Previously Deleted:**
- `src/Admin/ProductTableUI.php` - Custom table implementation
- `assets/css/product-table-ui.css` - Custom table styles
- `assets/css/products-table-inline-edit.css` - Inline editing styles
- `assets/js/product-table-ui.js` - Custom table scripts
- `assets/js/products-table-inline-edit.js` - Inline editing scripts

**Current State:**
- `src/Admin/ProductsTable.php` - Native WordPress WP_List_Table
- `src/Admin/Admin.php` - Admin initialization
- `src/Admin/Enqueue.php` - Asset management

---

## Target Architecture

### UI Layout (Single, Clean Interface)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ Products                                                     [+ New] │  ← WordPress header
├─────────────────────────────────────────────────────────────────────────┤
│                                                                  │
│ ┌────────────────────────────────────────────────────────────────────┐ │  ← WordPress defaults (KEEP)
│ │ Bulk Actions ▼  [Apply]                                       │ │
│ │ [All (25) | Published (20) | Draft (5)]                     │ │
│ └────────────────────────────────────────────────────────────────────┘ │
│                                                                  │
│ ┌────────────────────────────────────────────────────────────────────┐ │  ← Custom filters (ADD)
│ │ [🌟 Featured]  [Category ▼]  [Tag ▼]                      │ │
│ │ [Ribbon ▼]  [Stock Status ▼]  [Price Range ▼]               │ │
│ │ [Search products...]  [Apply Filters]  [Clear]                   │ │
│ └────────────────────────────────────────────────────────────────────┘ │
│                                                                  │
│ ┌────────────────────────────────────────────────────────────────────┐ │  ← WordPress table (KEEP + EXTEND)
│ │ ☐ | ID | Image | Product | Category | Tag | Price | Stock │    │
│ │ ☑ | 1  | [📷]  | Widget A | Tech   | New  | $29.99│ In   │    │
│ │ ☑ | 2  | [📷]  | Widget B | Tech   | New  | $19.99│ Out  │    │
│ │ ☐ | 3  | [📷]  | Widget C | Home   | Old  | $49.99│ In   │    │
│ │ ...                                                             │    │
│ │                                                                 │    │
│ │ Showing 1-20 of 25                               [« 1 2 2 »] │    │
│ └────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

### Key Principles

1. **Single Source of Truth**
   - One bulk actions section (WordPress default)
   - One status count section (WordPress default)
   - One table (WordPress WP_List_Table)

2. **WordPress Default + Extensions**
   - Keep all WordPress native functionality
   - Add custom filters BELOW WordPress UI
   - Extend WordPress bulk actions with custom options

3. **Clear Separation**
   - WordPress defaults at top (familiar to users)
   - Custom filters below (clearly marked)
   - No CSS hiding of WordPress UI

---

## Detailed UI Design

### Section 1: WordPress Defaults (Top)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Bulk Actions ▼                                              │  ← WordPress select (KEEP)
│   - Bulk Actions (Select option)                                │
│   - Edit                                                         │
│   - Move to Trash                                                 │
│   - Set Featured ← Custom action (ADD)                             │
│   - Unset Featured ← Custom action (ADD)                           │
│   - Set In Stock ← Custom action (ADD)                               │
│   - Set Out of Stock ← Custom action (ADD)                            │
│   - Export CSV ← Custom action (ADD)                                │
│                                                                 │
│ [Apply]                                                       │  ← WordPress button (KEEP)
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ All (25) | Published (20) | Draft (5) | Trashed (0)          │  ← WordPress counts (KEEP)
└─────────────────────────────────────────────────────────────────────┘
```

### Section 2: Custom Filters (Middle)

```
┌─────────────────────────────────────────────────────────────────────┐
│ 🌟 Featured Toggle                                               │  ← Custom checkbox (ADD)
│   [ ] Show only featured products                                  │
│                                                                 │
│ Category Filter ▼                                                │  ← Custom select (ADD)
│   - All Categories                                                 │
│   - Electronics                                                   │
│   - Home & Garden                                                 │
│   - Sports                                                       │
│                                                                 │
│ Tag Filter ▼                                                      │  ← Custom select (ADD)
│   - All Tags                                                       │
│   - New                                                           │
│   - Best Seller                                                    │
│   - Sale                                                          │
│                                                                 │
│ Ribbon Filter ▼                                                    │  ← Custom select (ADD)
│   - All Ribbons                                                   │
│   - Best Value                                                     │
│   - Limited Time                                                   │
│   - New Arrival                                                   │
│                                                                 │
│ Stock Status ▼                                                     │  ← Custom select (ADD)
│   - All Stock Statuses                                             │
│   - In Stock                                                      │
│   - Out of Stock                                                  │
│                                                                 │
│ Price Range                                                        │  ← Custom inputs (ADD)
│   Min: [$____]  Max: [$____]                                    │
│                                                                 │
│ [Search products...]  [Apply Filters]  [Clear]                    │  ← Custom buttons (ADD)
└─────────────────────────────────────────────────────────────────────┘
```

### Section 3: Table (Bottom - Extended WordPress)

```
┌─────────────────────────────────────────────────────────────────────┐
│ ☐ | Image | Product | Category | Tag | Price | Stock | Featured│  ← Custom columns
│ ☑ | [📷] | Widget A | Tech      | New  | $29.99 | In     │ 🌟    │
│ ☑ | [📷] | Widget B | Tech      | New  | $19.99 | Out    │        │
│ ☐ | [📷] | Widget C | Home      | Old  | $49.99 | In     │ 🌟    │
│ ...                                                               │
│                                                                   │
│ Showing 1-20 of 25                                       [« 1 2 2 »]  ← WordPress pagination
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation Plan

### Phase 1: Remove CSS That Hides WordPress UI

**File:** `assets/css/admin-table.css` (if exists)

**Remove:**
```css
/* DELETE THESE RULES */
body.post-type-aps_product.edit-php .tablenav .bulkactions {
    display: none;
}

body.post-type-aps_product.edit-php .tablenav .views {
    display: none;
}

body.post-type-aps_product.edit-php .tablenav .tablenav-pages {
    display: none;
}
```

**Why:** Remove custom CSS that hides WordPress default UI

---

### Phase 2: Extend ProductsTable Bulk Actions

**File:** `src/Admin/ProductsTable.php`

**Method:** `get_bulk_actions()`

```php
/**
 * Get bulk actions for products table
 * 
 * Combines WordPress default actions with custom plugin actions
 * 
 * @return array List of bulk actions
 */
public function get_bulk_actions(): array {
    $actions = [
        // WordPress default actions (handled by WordPress)
        'edit'   => __('Edit', 'affiliate-product-showcase'),
        'trash'  => __('Move to Trash', 'affiliate-product-showcase'),
        
        // Custom plugin actions (handled by plugin)
        'set_featured'     => __('Set Featured', 'affiliate-product-showcase'),
        'unset_featured'   => __('Unset Featured', 'affiliate-product-showcase'),
        'set_in_stock'     => __('Set In Stock', 'affiliate-product-showcase'),
        'set_out_of_stock' => __('Set Out of Stock', 'affiliate-product-showcase'),
        'export_csv'       => __('Export to CSV', 'affiliate-product-showcase'),
    ];
    
    return $actions;
}
```

**Why:** Extend WordPress bulk actions with custom options

---

### Phase 3: Add Custom Filters to ProductsTable

**File:** `src/Admin/ProductsTable.php`

**Method:** `extra_tablenav()`

```php
/**
 * Add custom filters to products table navigation
 * 
 * Renders custom filter UI below WordPress default UI
 * 
 * @param string $which Which navigation area (top/bottom)
 */
public function extra_tablenav($which): void {
    if ('top' !== $which) {
        return; // Only show on top navigation
    }
    
    // Get current filter values from URL
    $featured_filter = isset($_GET['featured_filter']) ? (int) $_GET['featured_filter'] : 0;
    $category_filter = isset($_GET['category_filter']) ? (int) $_GET['category_filter'] : 0;
    $tag_filter = isset($_GET['tag_filter']) ? (int) $_GET['tag_filter'] : 0;
    $ribbon_filter = isset($_GET['ribbon_filter']) ? (int) $_GET['ribbon_filter'] : 0;
    $stock_filter = isset($_GET['stock_filter']) ? sanitize_text_field($_GET['stock_filter']) : '';
    $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    
    ?>
    <div class="aps-custom-filters alignleft actions">
        
        <!-- Featured Toggle -->
        <label class="aps-filter-checkbox">
            <input type="checkbox" 
                   name="featured_filter" 
                   value="1" 
                   <?php checked($featured_filter, 1); ?> />
            <span><?php _e('🌟 Featured Only', 'affiliate-product-showcase'); ?></span>
        </label>
        
        <!-- Category Filter -->
        <select name="category_filter" class="aps-filter-select">
            <option value="0"><?php _e('All Categories', 'affiliate-product-showcase'); ?></option>
            <?php
            $categories = get_terms([
                'taxonomy' => 'aps_product_category',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);
            foreach ($categories as $category) {
                printf(
                    '<option value="%d" %s>%s</option>',
                    $category->term_id,
                    selected($category_filter, $category->term_id, false),
                    esc_html($category->name)
                );
            }
            ?>
        </select>
        
        <!-- Tag Filter -->
        <select name="tag_filter" class="aps-filter-select">
            <option value="0"><?php _e('All Tags', 'affiliate-product-showcase'); ?></option>
            <?php
            $tags = get_terms([
                'taxonomy' => 'aps_product_tag',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);
            foreach ($tags as $tag) {
                printf(
                    '<option value="%d" %s>%s</option>',
                    $tag->term_id,
                    selected($tag_filter, $tag->term_id, false),
                    esc_html($tag->name)
                );
            }
            ?>
        </select>
        
        <!-- Ribbon Filter -->
        <select name="ribbon_filter" class="aps-filter-select">
            <option value="0"><?php _e('All Ribbons', 'affiliate-product-showcase'); ?></option>
            <?php
            $ribbons = get_terms([
                'taxonomy' => 'aps_product_ribbon',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);
            foreach ($ribbons as $ribbon) {
                printf(
                    '<option value="%d" %s>%s</option>',
                    $ribbon->term_id,
                    selected($ribbon_filter, $ribbon->term_id, false),
                    esc_html($ribbon->name)
                );
            }
            ?>
        </select>
        
        <!-- Stock Status Filter -->
        <select name="stock_filter" class="aps-filter-select">
            <option value=""><?php _e('All Stock Statuses', 'affiliate-product-showcase'); ?></option>
            <option value="in_stock" <?php selected($stock_filter, 'in_stock'); ?>>
                <?php _e('In Stock', 'affiliate-product-showcase'); ?>
            </option>
            <option value="out_of_stock" <?php selected($stock_filter, 'out_of_stock'); ?>>
                <?php _e('Out of Stock', 'affiliate-product-showcase'); ?>
            </option>
        </select>
        
        <!-- Search Input (Custom) -->
        <input type="text" 
               name="s" 
               class="aps-filter-search"
               placeholder="<?php _e('Search products...', 'affiliate-product-showcase'); ?>"
               value="<?php echo esc_attr($search_term); ?>" />
        
        <!-- Apply Button -->
        <button type="submit" class="button button-primary">
            <?php _e('Apply Filters', 'affiliate-product-showcase'); ?>
        </button>
        
        <!-- Clear Button -->
        <a href="<?php echo admin_url('edit.php?post_type=aps_product'); ?>" 
           class="button">
            <?php _e('Clear', 'affiliate-product-showcase'); ?>
        </a>
        
    </div>
    <?php
}
```

**Why:** Add custom filters below WordPress UI using WordPress hook

---

### Phase 4: Handle Custom Bulk Actions

**File:** `src/Admin/Admin.php` (or create `src/Admin/BulkActions.php`)

**Add filter:**

```php
/**
 * Handle custom bulk actions for products
 * 
 * Processes custom bulk actions added to WordPress default bulk actions
 * 
 * @param string $redirect_to Redirect URL
 * @param string $action Action name
 * @param array $post_ids Post IDs to process
 * @return string Modified redirect URL
 */
public function handle_bulk_actions(string $redirect_to, string $action, array $post_ids): string {
    
    $count = count($post_ids);
    
    // Set Featured
    if ('set_featured' === $action) {
        foreach ($post_ids as $post_id) {
            update_post_meta($post_id, 'aps_featured', '1');
        }
        $redirect_to = add_query_arg('bulk_featured', $count, $redirect_to);
    }
    
    // Unset Featured
    if ('unset_featured' === $action) {
        foreach ($post_ids as $post_id) {
            delete_post_meta($post_id, 'aps_featured');
        }
        $redirect_to = add_query_arg('bulk_unfeatured', $count, $redirect_to);
    }
    
    // Set In Stock
    if ('set_in_stock' === $action) {
        foreach ($post_ids as $post_id) {
            update_post_meta($post_id, 'aps_in_stock', '1');
        }
        $redirect_to = add_query_arg('bulk_in_stock', $count, $redirect_to);
    }
    
    // Set Out of Stock
    if ('set_out_of_stock' === $action) {
        foreach ($post_ids as $post_id) {
            update_post_meta($post_id, 'aps_in_stock', '0');
        }
        $redirect_to = add_query_arg('bulk_out_of_stock', $count, $redirect_to);
    }
    
    // Export to CSV
    if ('export_csv' === $action) {
        $this->export_products_csv($post_ids);
        return $redirect_to; // Don't add query arg for export
    }
    
    return $redirect_to;
}

/**
 * Export products to CSV
 * 
 * @param array $post_ids Post IDs to export
 */
private function export_products_csv(array $post_ids): void {
    // CSV export implementation
    $filename = 'products-export-' . date('Y-m-d-H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV header
    fputcsv($output, [
        'ID',
        'Title',
        'Price',
        'Category',
        'Tag',
        'Ribbon',
        'Stock Status',
        'Featured'
    ]);
    
    // Write CSV rows
    foreach ($post_ids as $post_id) {
        $product = get_post($post_id);
        $categories = wp_get_post_terms($post_id, 'aps_product_category', ['fields' => 'names']);
        $tags = wp_get_post_terms($post_id, 'aps_product_tag', ['fields' => 'names']);
        $ribbons = wp_get_post_terms($post_id, 'aps_product_ribbon', ['fields' => 'names']);
        
        fputcsv($output, [
            $product->ID,
            $product->post_title,
            get_post_meta($post_id, 'aps_price', true),
            implode(', ', $categories),
            implode(', ', $tags),
            implode(', ', $ribbons),
            get_post_meta($post_id, 'aps_in_stock', true) ? 'In Stock' : 'Out of Stock',
            get_post_meta($post_id, 'aps_featured', true) ? 'Yes' : 'No'
        ]);
    }
    
    fclose($output);
    exit;
}
```

**Register filter in constructor:**
```php
add_filter('handle_bulk_actions-edit-aps_product', [$this, 'handle_bulk_actions'], 10, 3);
```

**Why:** Handle custom bulk actions using WordPress filter

---

### Phase 5: Add Custom Columns to ProductsTable

**File:** `src/Admin/ProductsTable.php`

**Method:** `get_columns()`

```php
/**
 * Get columns for products table
 * 
 * Extends WordPress default columns with custom plugin columns
 * 
 * @return array List of columns
 */
public function get_columns(): array {
    $columns = [
        'cb'        => '<input type="checkbox" />', // WordPress checkbox column
        'image'     => __('Image', 'affiliate-product-showcase'),
        'title'     => __('Product', 'affiliate-product-showcase'),
        'category'  => __('Category', 'affiliate-product-showcase'),
        'tag'       => __('Tag', 'affiliate-product-showcase'),
        'price'     => __('Price', 'affiliate-product-showcase'),
        'stock'     => __('Stock', 'affiliate-product-showcase'),
        'featured'  => __('Featured', 'affiliate-product-showcase'),
        'date'      => __('Date', 'affiliate-product-showcase'),
    ];
    
    return $columns;
}
```

**Method:** `column_default()`

```php
/**
 * Render custom column content
 * 
 * @param WP_Post $post Current post
 * @param string $column_name Column identifier
 * @return string Column content
 */
public function column_default(WP_Post $post, string $column_name): string {
    
    switch ($column_name) {
        case 'image':
            $thumbnail = get_the_post_thumbnail($post->ID, [50, 50]);
            return $thumbnail ? $thumbnail : '<span class="dashicons dashicons-format-image"></span>';
        
        case 'category':
            $categories = wp_get_post_terms($post->ID, 'aps_product_category', ['fields' => 'names']);
            return implode(', ', $categories);
        
        case 'tag':
            $tags = wp_get_post_terms($post->ID, 'aps_product_tag', ['fields' => 'names']);
            return implode(', ', $tags);
        
        case 'price':
            $price = get_post_meta($post->ID, 'aps_price', true);
            $original_price = get_post_meta($post->ID, 'aps_original_price', true);
            
            if ($original_price && $original_price > $price) {
                $discount = round((($original_price - $price) / $original_price) * 100);
                return sprintf(
                    '<span class="aps-price-discount">
                        <del>$%.2f</del> $%.2f <span class="aps-discount-badge">-%d%%</span>
                    </span>',
                    $original_price,
                    $price,
                    $discount
                );
            }
            
            return sprintf('<span class="aps-price">$%.2f</span>', $price);
        
        case 'stock':
            $in_stock = get_post_meta($post->ID, 'aps_in_stock', true);
            $stock_status = $in_stock ? 'In Stock' : 'Out of Stock';
            $status_class = $in_stock ? 'aps-stock-in' : 'aps-stock-out';
            
            return sprintf(
                '<span class="aps-stock-status %s">%s</span>',
                $status_class,
                $stock_status
            );
        
        case 'featured':
            $featured = get_post_meta($post->ID, 'aps_featured', true);
            if ($featured) {
                return '<span class="aps-featured-badge" title="Featured Product">🌟</span>';
            }
            return '';
        
        default:
            return '';
    }
}
```

**Why:** Add custom columns to WordPress table

---

### Phase 6: Add Custom Styles

**File:** `assets/css/products-table-hybrid.css` (CREATE NEW)

```css
/* Custom Filters Section */
.aps-custom-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
}

.aps-filter-checkbox {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.aps-filter-checkbox:hover {
    background: #f0f0f1;
}

.aps-filter-select,
.aps-filter-search {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

.aps-filter-select {
    min-width: 150px;
}

.aps-filter-search {
    min-width: 200px;
}

/* Price Styles */
.aps-price-discount {
    color: #d63638;
}

.aps-price-discount del {
    color: #999;
    font-size: 0.9em;
}

.aps-discount-badge {
    background: #d63638;
    color: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8em;
    margin-left: 5px;
}

/* Stock Status */
.aps-stock-status {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.aps-stock-in {
    background: #00a32a;
    color: #fff;
}

.aps-stock-out {
    background: #d63638;
    color: #fff;
}

/* Featured Badge */
.aps-featured-badge {
    font-size: 18px;
    display: inline-block;
}

/* Image Column */
.column-image img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.column-image .dashicons {
    font-size: 30px;
    color: #999;
}

/* Responsive */
@media (max-width: 1200px) {
    .aps-custom-filters {
        flex-direction: column;
    }
    
    .aps-filter-select,
    .aps-filter-search {
        width: 100%;
    }
}
```

**Enqueue in:** `src/Admin/Enqueue.php`

```php
// Enqueue custom filters styles on products page
if ('edit-aps_product' === $hook) {
    wp_enqueue_style(
        'aps-products-table-hybrid',
        APS_PLUGIN_URL . 'assets/css/products-table-hybrid.css',
        [],
        APS_VERSION
    );
}
```

**Why:** Style custom filters to match WordPress admin design

---

## Comparison: Before vs After

### Before (Full Custom UI)

**Problems:**
- ❌ Duplicated bulk actions
- ❌ Two status count sections
- ❌ CSS hiding WordPress defaults
- ❌ High maintenance burden
- ❌ User confusion
- ❌ More files to maintain

**Architecture:**
```
WordPress Admin Page
    ↓
CSS Hides WordPress UI
    ↓
Custom ProductTableUI Renders Everything
    ↓
Custom Bulk Actions (duplicate)
Custom Filters (duplicate)
Custom Status Counts (duplicate)
```

### After (Hybrid UI)

**Benefits:**
- ✅ Single bulk actions section
- ✅ Single status count section
- ✅ No CSS hiding
- ✅ Low maintenance burden
- ✅ No user confusion
- ✅ Fewer files to maintain

**Architecture:**
```
WordPress Admin Page
    ↓
WordPress Default UI (bulk actions, status counts)
    ↓
Custom Filters (added via extra_tablenav hook)
    ↓
WordPress Table (extended with custom columns and actions)
```

---

## Implementation Checklist

### Phase 1: Cleanup
- [ ] Remove CSS that hides WordPress UI
- [ ] Verify WordPress bulk actions show
- [ ] Verify WordPress status counts show

### Phase 2: Extend ProductsTable
- [ ] Add custom bulk actions to get_bulk_actions()
- [ ] Add extra_tablenav() method
- [ ] Add custom filter UI
- [ ] Add custom columns to get_columns()
- [ ] Add column_default() for custom columns

### Phase 3: Handle Bulk Actions
- [ ] Create handle_bulk_actions() method
- [ ] Implement set_featured action
- [ ] Implement unset_featured action
- [ ] Implement set_in_stock action
- [ ] Implement set_out_of_stock action
- [ ] Implement export_csv action

### Phase 4: Styling
- [ ] Create products-table-hybrid.css
- [ ] Style custom filters
- [ ] Style custom columns
- [ ] Make responsive

### Phase 5: Testing
- [ ] Test bulk actions work
- [ ] Test filters work
- [ ] Test custom columns display
- [ ] Test responsive design
- [ ] Test on different browsers

---

## Benefits Summary

### Code Quality
- ✅ **Reduced Complexity** - 50% less code to maintain
- ✅ **Clear Architecture** - WordPress handles core, we extend
- ✅ **Better Separation** - Clear what's WordPress vs custom
- ✅ **Easier Testing** - Test WordPress and custom separately

### User Experience
- ✅ **Familiar Interface** - Users know WordPress UI
- ✅ **No Confusion** - Single bulk actions section
- ✅ **Consistent** - Matches WordPress admin design
- ✅ **Discoverable** - Custom features clearly visible

### Maintenance
- ✅ **Lower Burden** - WordPress handles bulk actions, status counts
- ✅ **Future-Proof** - WordPress updates won't break custom code
- ✅ **Easier Updates** - Less code to update
- ✅ **Better Documentation** - Clear separation of concerns

### Performance
- ✅ **Fewer Assets** - No duplicate CSS/JS files
- ✅ **Faster Load** - Less DOM to render
- ✅ **Better Caching** - WordPress UI is optimized

---

## Files to Create/Modify

### New Files
1. `assets/css/products-table-hybrid.css` - Custom filters styles

### Modified Files
1. `src/Admin/ProductsTable.php` - Add custom filters and columns
2. `src/Admin/Admin.php` - Add bulk actions handler
3. `src/Admin/Enqueue.php` - Enqueue new styles

### Files to Delete
1. Any CSS that hides WordPress UI (if exists)
2. Any duplicate bulk action implementations

---

## Next Steps

1. **Review this design plan** with team/stakeholders
2. **Approve implementation approach**
3. **Implement Phase 1** (Cleanup)
4. **Implement Phase 2** (Extend ProductsTable)
5. **Implement Phase 3** (Handle Bulk Actions)
6. **Implement Phase 4** (Styling)
7. **Test thoroughly** (Phase 5)
8. **Deploy to production**

---

## Conclusion

This hybrid approach provides the **best of both worlds**:

- **WordPress Reliability** - Core functionality handled by WordPress
- **Custom Features** - Plugin-specific features added as extensions
- **Clean Architecture** - Clear separation of concerns
- **Low Maintenance** - WordPress handles bulk of work
- **Great UX** - Familiar interface + custom features

**Status:** ✅ Design Complete  
**Ready for:** Implementation  
**Estimated Complexity:** Medium  
**Timeline:** 2-3 days

---

**Created:** 2026-01-27  
**Author:** Cline (AI Assistant)  
**Version:** 1.0.0