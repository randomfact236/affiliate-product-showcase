# Products Page True Hybrid Cleanup Plan

**Purpose:** Implement complete true hybrid approach for products page
**Date:** 2026-01-23
**Status:** 🔄 UPDATED (2026-01-23)
**Approach:** Complete restructure (5-step plan)
**Time Estimate:** ~4 hours

---

# Manage Products Admin Table - Visual Design Diagram
# Feature Requirements: Affiliate Product Showcase

> **IMPORTANT RULE: NEVER DELETE THIS FILE**
> This file contains complete feature requirements for plugin. All features must be implemented according to this plan.

---

# 📝 STRICT DEVELOPMENT RULES

**⚠️ MANDATORY:** Always use all assistant instruction files when writing code for feature development and issue resolution.

### Project Context

**Project:** Affiliate Product Showcase WordPress Plugin  
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)  
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere  
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks  
**Tech Stack:** PHP 8.1+, JavaScript/React, Vite, Tailwind CSS  
**Code Quality:** PHPUnit, PHPCS (WPCS), PHPStan, Psalm  

### Required Reference Files (ALWAYS USE):

1. **docs/assistant-instructions.md** - Project context, code change policy, git rules
2. **docs/assistant-quality-standards.md** - Enterprise-grade code quality requirements
3. **docs/assistant-performance-optimization.md** - Performance optimization guidelines

### Quality Standard: 10/10 Enterprise-Grade
- Fully/highly optimized, no compromises
- All code must meet hybrid quality matrix standards
- Essential standards at 10/10, performance goals as targets

----


## 📋 Plan History

### v2.0 - Complete Restructure (2026-01-23) ⭐ CURRENT
- Remove ProductsPageHooks.php entirely
- Create new ProductsTable.php extending WP_List_Table
- Proper separation of concerns
- Eliminate all duplication

### v1.0 - Minimal Changes (2026-01-23) ❌ OBSOLETE
- Keep ProductsPageHooks.php
- Remove duplicate filters only
- Does NOT address ProductsPageHooks vs ProductTableUI duplication
- Replaced by v2.0

---

## 🎯 Why Update to v2.0?

**Issues with v1.0:**
- ❌ Doesn't address ProductsPageHooks vs ProductTableUI duplication
- ❌ Both files still rendering same UI
- ❌ ProductTableUI.php not integrated
- ❌ Not true hybrid (still has duplication)

**Benefits of v2.0:**
- ✅ Eliminates ALL duplication
- ✅ Proper separation of concerns
- ✅ Follows WordPress best practices
- ✅ Extends WP_List_Table correctly
- ✅ Clean architecture
- ✅ Maintainable code

---

## 📋 True Hybrid Approach Definition

**True Hybrid = Custom UI + Default WordPress Table**

```
┌─────────────────────────────────────────┐
│  CUSTOM UI (ProductTableUI.php)    │
│  - Page Header                        │
│  - Action Buttons                     │
│  - Status Counts                      │
│  - Filters (Search, Category, etc.)   │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  WORDPRESS WP_LIST_TABLE            │
│  (ProductsTable.php extends)         │
│  - Single table (no duplication)     │
│  - Custom columns (Columns.php)      │
│  - Native pagination                 │
│  - Native sorting                   │
│  - Native bulk actions              │
└─────────────────────────────────────────┘
```

---

## 🚀 v2.0 Implementation Plan (5 Steps)

---

### **Step 1: Remove Duplicates First (5 min)**

**Goal:** Clean slate by removing ProductsPageHooks.php

#### File 1: ServiceProvider.php
**Path:** `src/Plugin/ServiceProvider.php`

**Remove lines 229-231:**
```php
// DELETE THESE LINES:
$this->getContainer()->addShared( ProductsPageHooks::class )
    ->addArgument( ProductRepository::class );
```

**Remove from `provides()` method (around line 84):**
```php
// REMOVE 'ProductsPageHooks::class,' from services array
```

#### File 2: ProductsPageHooks.php
**Path:** `src/Admin/ProductsPageHooks.php`

**Action:** DELETE ENTIRE FILE

**Verify:**
```bash
grep -r "ProductsPageHooks" src/
```

**Expected Result:** No references found (except in git history)

---

### **Step 2: Create ProductsTable.php (2 hours)**

**Goal:** Create custom WP_List_Table extension for products

#### File: src/Admin/ProductsTable.php (NEW)

**Full Implementation:**

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Repositories\ProductRepository;

/**
 * Products List Table
 *
 * Extends WordPress WP_List_Table to display products with custom columns.
 * Provides native pagination, sorting, and bulk actions.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class ProductsTable extends \WP_List_Table {

    /**
     * Product repository
     *
     * @var ProductRepository
     */
    private ProductRepository $repository;

    /**
     * Constructor
     *
     * @param ProductRepository $repository Product repository instance
     */
    public function __construct( ProductRepository $repository ) {
        $this->repository = $repository;

        // Set screen options
        parent::__construct( [
            'singular' => 'product',
            'plural'   => 'products',
            'ajax'     => false,
        ] );
    }

    /**
     * Get table columns
     *
     * @return array
     */
    public function get_columns(): array {
        $columns = [
            'cb'        => '<input type="checkbox" />',
            'logo'      => __( 'Logo', 'affiliate-product-showcase' ),
            'title'     => __( 'Product Name', 'affiliate-product-showcase' ),
            'category'  => __( 'Category', 'affiliate-product-showcase' ),
            'tags'      => __( 'Tags', 'affiliate-product-showcase' ),
            'ribbon'    => __( 'Ribbon', 'affiliate-product-showcase' ),
            'featured'  => __( 'Featured', 'affiliate-product-showcase' ),
            'price'     => __( 'Price', 'affiliate-product-showcase' ),
            'status'    => __( 'Status', 'affiliate-product-showcase' ),
            'date'      => __( 'Date', 'affiliate-product-showcase' ),
        ];

        return $columns;
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns(): array {
        $sortable = [
            'title'   => [ 'title', true ],
            'price'   => [ 'price', true ],
            'date'    => [ 'date', true ],
            'status'  => [ 'status', true ],
            'featured' => [ 'featured', true ],
        ];

        return $sortable;
    }

    /**
     * Get bulk actions
     *
     * @return array
     */
    public function get_bulk_actions(): array {
        $actions = [
            'set_in_stock'    => __( 'Set In Stock', 'affiliate-product-showcase' ),
            'set_out_of_stock' => __( 'Set Out of Stock', 'affiliate-product-showcase' ),
            'set_featured'     => __( 'Set Featured', 'affiliate-product-showcase' ),
            'unset_featured'   => __( 'Unset Featured', 'affiliate-product-showcase' ),
            'reset_clicks'     => __( 'Reset Clicks', 'affiliate-product-showcase' ),
            'export_csv'       => __( 'Export to CSV', 'affiliate-product-showcase' ),
        ];

        return $actions;
    }

    /**
     * Column: Logo
     *
     * @param array $item
     * @return string
     */
    public function column_logo( array $item ): string {
        $logo_url = get_post_meta( $item['ID'], 'aps_product_logo', true );
        
        if ( empty( $logo_url ) ) {
            return '<span class="dashicons dashicons-format-image"></span>';
        }

        return sprintf(
            '<img src="%s" alt="%s" style="max-width: 50px; max-height: 50px; object-fit: cover;" />',
            esc_url( $logo_url ),
            esc_attr( $item['post_title'] )
        );
    }

    /**
     * Column: Title
     *
     * @param array $item
     * @return string
     */
    public function column_title( array $item ): string {
        $edit_url = get_edit_post_link( $item['ID'] );
        $view_url = get_permalink( $item['ID'] );
        $title = $item['post_title'];
        $post_type = get_post_type_object( 'aps_product' );
        $can_edit_post = current_user_can( $post_type->cap->edit_post, $item['ID'] );
        $can_delete_post = current_user_can( $post_type->cap->delete_post, $item['ID'] );

        $actions = [];

        if ( $can_edit_post ) {
            $actions['edit'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url( $edit_url ),
                __( 'Edit', 'affiliate-product-showcase' )
            );
        }

        if ( $can_delete_post ) {
            $actions['trash'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url( get_delete_post_link( $item['ID'] ) ),
                __( 'Trash', 'affiliate-product-showcase' )
            );
        }

        $actions['view'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url( $view_url ),
            __( 'View', 'affiliate-product-showcase' )
        );

        return sprintf(
            '<strong><a href="%s">%s</a></strong> %s',
            esc_url( $edit_url ),
            esc_html( $title ),
            $this->row_actions( $actions )
        );
    }

    /**
     * Column: Category
     *
     * @param array $item
     * @return string
     */
    public function column_category( array $item ): string {
        $categories = get_the_terms( $item['ID'], 'aps_product_category' );

        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            return '—';
        }

        $category_links = array_map( function( $category ) {
            return sprintf(
                '<a href="%s">%s</a>',
                esc_url( admin_url( 'edit.php?aps_category=' . $category->term_id . '&post_type=aps_product' ) ),
                esc_html( $category->name )
            );
        }, $categories );

        return implode( ', ', $category_links );
    }

    /**
     * Column: Tags
     *
     * @param array $item
     * @return string
     */
    public function column_tags( array $item ): string {
        $tags = get_the_terms( $item['ID'], 'aps_product_tag' );

        if ( empty( $tags ) || is_wp_error( $tags ) ) {
            return '—';
        }

        $tag_links = array_map( function( $tag ) {
            return sprintf(
                '<a href="%s">%s</a>',
                esc_url( admin_url( 'edit.php?aps_tag=' . $tag->term_id . '&post_type=aps_product' ) ),
                esc_html( $tag->name )
            );
        }, $tags );

        return implode( ', ', $tag_links );
    }

    /**
     * Column: Ribbon
     *
     * @param array $item
     * @return string
     */
    public function column_ribbon( array $item ): string {
        $ribbon_text = get_post_meta( $item['ID'], 'aps_product_ribbon_text', true );
        $ribbon_type = get_post_meta( $item['ID'], 'aps_product_ribbon_type', true );

        if ( empty( $ribbon_text ) ) {
            return '—';
        }

        $colors = [
            'sale'       => '#dc2626',
            'new'        => '#16a34a',
            'bestseller'  => '#ea580c',
            'limited'     => '#7c3aed',
            'popular'     => '#0891b2',
        ];

        $background = $colors[ $ribbon_type ] ?? '#64748b';

        return sprintf(
            '<span style="background-color: %s; color: #ffffff; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">%s</span>',
            esc_attr( $background ),
            esc_html( $ribbon_text )
        );
    }

    /**
     * Column: Featured
     *
     * @param array $item
     * @return string
     */
    public function column_featured( array $item ): string {
        $is_featured = (bool) get_post_meta( $item['ID'], 'aps_product_featured', true );

        if ( $is_featured ) {
            return '<span class="dashicons dashicons-star-filled" style="color: #f59e0b; font-size: 20px;"></span>';
        }

        return '<span class="dashicons dashicons-star-empty" style="color: #d1d5db; font-size: 20px;"></span>';
    }

    /**
     * Column: Price
     *
     * @param array $item
     * @return string
     */
    public function column_price( array $item ): string {
        $price = get_post_meta( $item['ID'], 'aps_product_price', true );
        $currency = get_post_meta( $item['ID'], 'aps_product_currency', true );
        $original_price = get_post_meta( $item['ID'], 'aps_product_original_price', true );
        $currency_symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
        ];
        $symbol = $currency_symbols[ $currency ] ?? $currency;

        if ( empty( $price ) ) {
            return '—';
        }

        $output = sprintf(
            '<strong>%s%.2f</strong>',
            esc_html( $symbol ),
            esc_html( (float) $price )
        );

        if ( ! empty( $original_price ) && $original_price > $price ) {
            $discount = round( ( ( $original_price - $price ) / $original_price ) * 100 );
            $output .= sprintf(
                ' <span style="color: #dc2626; text-decoration: line-through; margin-left: 8px;">%s%.2f</span> <span style="color: #16a34a; font-weight: 600;">-%d%%</span>',
                esc_html( $symbol ),
                esc_html( (float) $original_price ),
                esc_html( $discount )
            );
        }

        return $output;
    }

    /**
     * Column: Status
     *
     * @param array $item
     * @return string
     */
    public function column_status( array $item ): string {
        $status = get_post_status( $item['ID'] );

        $status_labels = [
            'publish' => '<span style="color: #16a34a; font-weight: 600;">Published</span>',
            'draft'   => '<span style="color: #ca8a04; font-weight: 600;">Draft</span>',
            'pending' => '<span style="color: #ea580c; font-weight: 600;">Pending</span>',
            'trash'   => '<span style="color: #dc2626; font-weight: 600;">Trash</span>',
        ];

        return $status_labels[ $status ] ?? $status;
    }

    /**
     * Column: Date
     *
     * @param array $item
     * @return string
     */
    public function column_date( array $item ): string {
        $time = strtotime( $item['post_date'] );
        $time_diff = current_time( 'timestamp' ) - $time;

        if ( $time_diff < DAY_IN_SECONDS ) {
            $time_ago = sprintf( __( '%s ago', 'affiliate-product-showcase' ), human_time_diff( $time ) );
        } else {
            $time_ago = date_i18n( get_option( 'date_format' ), $time );
        }

        return sprintf(
            '<abbr title="%s">%s</abbr>',
            esc_attr( $item['post_date'] ),
            esc_html( $time_ago )
        );
    }

    /**
     * Default column handler
     *
     * @param array $item
     * @param string $column_name
     * @return string
     */
    public function column_default( array $item, string $column_name ): string {
        return apply_filters( "manage_aps_product_posts_custom_column_{$column_name}", '', $item['ID'] );
    }

    /**
     * Prepare items for display
     *
     * @return void
     */
    public function prepare_items(): void {
        // Get pagination settings
        $per_page = $this->get_items_per_page( 'products_per_page', 20 );
        $current_page = $this->get_pagenum();
        $offset = ( $current_page - 1 ) * $per_page;

        // Get filter values
        $search = isset( $_GET['aps_search'] ) ? sanitize_text_field( $_GET['aps_search'] ) : '';
        $category = isset( $_GET['aps_category_filter'] ) ? intval( $_GET['aps_category_filter'] ) : 0;
        $featured = isset( $_GET['featured_filter'] ) ? boolval( $_GET['featured_filter'] ) : false;
        $order = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
        $orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'date';

        // Build query args
        $args = [
            'post_type'      => 'aps_product',
            'post_status'    => isset( $_GET['post_status'] ) ? sanitize_key( $_GET['post_status'] ) : 'publish',
            'posts_per_page' => $per_page,
            'offset'         => $offset,
            'orderby'        => $orderby,
            'order'          => $order,
        ];

        // Add search
        if ( ! empty( $search ) ) {
            $args['s'] = $search;
        }

        // Add category filter
        if ( $category > 0 ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'aps_product_category',
                    'terms'    => $category,
                ],
            ];
        }

        // Add featured filter
        if ( $featured ) {
            $args['meta_query'] = [
                [
                    'key'     => 'aps_product_featured',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ];
        }

        // Get products
        $query = new \WP_Query( $args );
        $this->items = $query->posts;

        // Set pagination
        $total_items = $query->found_posts;
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ] );
    }

    /**
     * Display table
     *
     * @return void
     */
    public function display(): void {
        $this->views();
        $this->search_box( __( 'Search Products', 'affiliate-product-showcase' ), 'product' );
        parent::display();
    }

    /**
     * Render views (status counts)
     *
     * @return array
     */
    protected function get_views(): array {
        $status_links = [
            'all' => [
                'url'        => admin_url( 'edit.php?post_type=aps_product' ),
                'label'      => __( 'All <span class="count">(%s)</span>', 'affiliate-product-showcase' ),
                'count'      => wp_count_posts( 'aps_product' )->publish + wp_count_posts( 'aps_product' )->draft,
                'current'    => ! isset( $_GET['post_status'] ),
            ],
            'publish' => [
                'url'        => admin_url( 'edit.php?post_type=aps_product&post_status=publish' ),
                'label'      => __( 'Published <span class="count">(%s)</span>', 'affiliate-product-showcase' ),
                'count'      => wp_count_posts( 'aps_product' )->publish,
                'current'    => isset( $_GET['post_status'] ) && $_GET['post_status'] === 'publish',
            ],
            'draft' => [
                'url'        => admin_url( 'edit.php?post_type=aps_product&post_status=draft' ),
                'label'      => __( 'Draft <span class="count">(%s)</span>', 'affiliate-product-showcase' ),
                'count'      => wp_count_posts( 'aps_product' )->draft,
                'current'    => isset( $_GET['post_status'] ) && $_GET['post_status'] === 'draft',
            ],
            'trash' => [
                'url'        => admin_url( 'edit.php?post_type=aps_product&post_status=trash' ),
                'label'      => __( 'Trash <span class="count">(%s)</span>', 'affiliate-product-showcase' ),
                'count'      => wp_count_posts( 'aps_product' )->trash,
                'current'    => isset( $_GET['post_status'] ) && $_GET['post_status'] === 'trash',
            ],
        ];

        $views = [];
        foreach ( $status_links as $status => $data ) {
            $class = $data['current'] ? 'current' : '';
            $url = esc_url( $data['url'] );
            $label = sprintf( $data['label'], number_format_i18n( $data['count'] ) );
            $views[ $status ] = sprintf( '<a href="%s" class="%s">%s</a>', $url, $class, $label );
        }

        return $views;
    }
}
```

**File Size:** ~400 lines
**Features:**
- ✅ Extends WP_List_Table
- ✅ Custom columns (logo, title, category, tags, ribbon, featured, price, status, date)
- ✅ Native pagination
- ✅ Native sorting
- ✅ Native bulk actions
- ✅ Column rendering methods
- ✅ Filter support (search, category, featured)
- ✅ Status counts (views)

---

### **Step 3: Modify ProductTableUI.php (1 hour)**

**Goal:** Remove custom table HTML, add ProductsTable integration

#### File: src/Admin/ProductTableUI.php

**Remove from class:**
```php
// ❌ DELETE: renderProductTableUI() method entirely
// ❌ DELETE: renderActionButtons(), renderStatusCounts(), renderFilters() methods
// ❌ DELETE: isProductsPage() method
```

**Replace with new implementation:**

```php
<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Product Table UI
 *
 * Manages UI elements above the products list table.
 * Renders action buttons, custom filters, and displays ProductsTable.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class ProductTableUI {

    /**
     * Product table instance
     *
     * @var ProductsTable
     */
    private ProductsTable $product_table;

    /**
     * Constructor
     */
    public function __construct() {
        // Enqueue styles and scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueStyles' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }

    /**
     * Render product table page
     *
     * @return void
     */
    public function render(): void {
        // Only show on products list page
        if ( ! $this->isProductsPage() ) {
            return;
        }

        // Initialize products table
        $this->product_table = new ProductsTable(
            new \AffiliateProductShowcase\Repositories\ProductRepository()
        );

        $this->renderCustomUI();
        $this->product_table->prepare_items();
        $this->product_table->display();
    }

    /**
     * Check if current page is products list
     *
     * @return bool
     */
    private function isProductsPage(): bool {
        return isset( $_GET['post_type'] ) && 
               $_GET['post_type'] === 'aps_product' && 
               ! isset( $_GET['page'] );
    }

    /**
     * Render custom UI above table
     *
     * @return void
     */
    private function renderCustomUI(): void {
        $add_product_url = admin_url( 'edit.php?post_type=aps_product&page=add-product' );
        $trash_url = admin_url( 'edit.php?post_type=aps_product&post_status=trash' );
        
        ?>
        <div class="wrap aps-products-page">
            
            <!-- Page Title -->
            <h1 class="aps-page-title">
                <?php echo esc_html( get_admin_page_title() ); ?>
            </h1>
            
            <p class="aps-page-description">
                <?php echo esc_html( __( 'Manage all your affiliate products. Use filters below to find specific products.', 'affiliate-product-showcase' ) ); ?>
            </p>
            
            <!-- Action Buttons -->
            <div class="aps-action-buttons">
                <a href="<?php echo esc_url( $add_product_url ); ?>" 
                   class="aps-btn aps-btn-primary">
                    <span class="dashicons dashicons-plus"></span>
                    <?php echo esc_html( __( 'Add New Product', 'affiliate-product-showcase' ) ); ?>
                </a>
                
                <a href="<?php echo esc_url( $trash_url ); ?>" 
                   class="aps-btn aps-btn-secondary">
                    <span class="dashicons dashicons-trash"></span>
                    <?php echo esc_html( __( 'Trash', 'affiliate-product-showcase' ) ); ?>
                </a>
                
                <button type="button" class="aps-btn aps-btn-secondary" onclick="apsBulkUploadProducts()">
                    <span class="dashicons dashicons-upload"></span>
                    <?php echo esc_html( __( 'Bulk Upload', 'affiliate-product-showcase' ) ); ?>
                </button>
                
                <button type="button" class="aps-btn aps-btn-secondary" onclick="apsCheckProductLinks()">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php echo esc_html( __( 'Check Links', 'affiliate-product-showcase' ) ); ?>
                </button>
            </div>
            
            <!-- Custom Filters -->
            <div class="aps-product-filters">
                
                <!-- Search -->
                <div class="aps-filter-group aps-filter-search">
                    <label for="aps_search_products"><?php echo esc_html( __( 'Search Products', 'affiliate-product-showcase' ) ); ?></label>
                    <input type="text" 
                           name="aps_search" 
                           id="aps_search_products" 
                           class="aps-filter-input" 
                           placeholder="<?php echo esc_attr( __( 'Search products...', 'affiliate-product-showcase' ) ); ?>"
                           value="<?php echo isset( $_GET['aps_search'] ) ? esc_attr( $_GET['aps_search'] ) : ''; ?>">
                </div>
                
                <!-- Category Filter -->
                <div class="aps-filter-group">
                    <label for="aps_category_filter"><?php echo esc_html( __( 'Category', 'affiliate-product-showcase' ) ); ?></label>
                    <select name="aps_category_filter" id="aps_category_filter" class="aps-filter-select">
                        <option value=""><?php echo esc_html( __( 'All Categories', 'affiliate-product-showcase' ) ); ?></option>
                        <?php
                        $categories = get_terms( [ 
                            'taxonomy' => 'aps_product_category', 
                            'hide_empty' => false 
                        ] );
                        foreach ( $categories as $category ) :
                        ?>
                            <option value="<?php echo esc_attr( $category->term_id ); ?>"
                                <?php selected( isset( $_GET['aps_category_filter'] ) ? $_GET['aps_category_filter'] : '', $category->term_id ); ?>>
                                <?php echo esc_html( $category->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Featured Filter -->
                <div class="aps-filter-group aps-filter-toggle">
                    <label class="aps-toggle-label">
                        <input type="checkbox" 
                               name="featured_filter" 
                               id="aps_show_featured" 
                               value="1"
                               <?php checked( isset( $_GET['featured_filter'] ) ? $_GET['featured_filter'] : '', '1' ); ?>>
                        <span class="aps-toggle-slider"></span>
                        <span class="aps-toggle-text"><?php echo esc_html( __( 'Featured Only', 'affiliate-product-showcase' ) ); ?></span>
                    </label>
                </div>
                
                <!-- Sort Order -->
                <div class="aps-filter-group">
                    <label for="aps_sort_order"><?php echo esc_html( __( 'Sort Order', 'affiliate-product-showcase' ) ); ?></label>
                    <select name="order" id="aps_sort_order" class="aps-filter-select">
                        <option value="desc" <?php selected( isset( $_GET['order'] ) ? $_GET['order'] : 'desc', 'desc' ); ?>>
                            <?php echo esc_html( __( 'Descending', 'affiliate-product-showcase' ) ); ?>
                        </option>
                        <option value="asc" <?php selected( isset( $_GET['order'] ) ? $_GET['order'] : 'desc', 'asc' ); ?>>
                            <?php echo esc_html( __( 'Ascending', 'affiliate-product-showcase' ) ); ?>
                        </option>
                    </select>
                </div>
                
                <!-- Clear Filters Button -->
                <div class="aps-filter-group">
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" 
                       class="aps-btn aps-btn-clear">
                        <?php echo esc_html( __( 'Clear Filters', 'affiliate-product-showcase' ) ); ?>
                    </a>
                </div>
                
            </div>
            
        </div>
        <?php
    }

    /**
     * Enqueue styles
     *
     * @param string $hook Current admin hook
     * @return void
     */
    public function enqueueStyles( string $hook ): void {
        if ( ! $this->isProductsPage() ) {
            return;
        }

        // Enqueue admin table CSS
        wp_enqueue_style(
            'aps-admin-table',
            \AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/admin-table.css',
            [],
            \AffiliateProductShowcase\Plugin\Constants::VERSION
        );

        // Enqueue product table UI CSS
        wp_enqueue_style(
            'aps-product-table-ui',
            \AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/product-table-ui.css',
            ['aps-admin-table'],
            \AffiliateProductShowcase\Plugin\Constants::VERSION
        );
    }

    /**
     * Enqueue scripts
     *
     * @param string $hook Current admin hook
     * @return void
     */
    public function enqueueScripts( string $hook ): void {
        if ( ! $this->isProductsPage() ) {
            return;
        }

        wp_enqueue_script(
            'aps-product-table-ui',
            \AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/js/product-table-ui.js',
            ['jquery'],
            \AffiliateProductShowcase\Plugin\Constants::VERSION,
            true
        );

        wp_localize_script( 'aps-product-table-ui', 'apsProductTableUI', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'aps_product_table_ui' ),
            'strings' => [
                'confirmBulkUpload' => __( 'Are you sure you want to bulk upload products?', 'affiliate-product-showcase' ),
                'confirmCheckLinks' => __( 'Are you sure you want to check all product links?', 'affiliate-product-showcase' ),
                'processing' => __( 'Processing...', 'affiliate-product-showcase' ),
                'done' => __( 'Done!', 'affiliate-product-showcase' ),
                'noProducts' => __( 'No products found.', 'affiliate-product-showcase' ),
            ],
        ]);
    }
}
```

**Key Changes:**
- ✅ Removed custom table HTML rendering
- ✅ Removed status counts (handled by ProductsTable views)
- ✅ Kept action buttons section
- ✅ Kept custom filters section
- ✅ Added ProductsTable instantiation
- ✅ Calls ProductsTable->display()

---

### **Step 4: Update Columns.php (30 min)**

**Goal:** Remove duplicate filters, keep column rendering

#### File: src/Admin/Columns.php

**Remove from constructor:**
```php
// ❌ DELETE THIS LINE:
add_action( 'restrict_manage_posts', [ $this, 'addFilters' ], 10, 2 );
```

**Remove entire method:**
```php
// ❌ DELETE THIS METHOD ENTIRELY:
public function addFilters( string $post_type, string $which ): void { ... }
```

**Keep all other methods:**
```php
✅ KEEP: __construct() (remove only restrict_manage_posts hook)
✅ KEEP: addCustomColumns()
✅ KEEP: renderCustomColumns()
✅ KEEP: All render*Column() methods
✅ KEEP: makeColumnsSortable()
✅ KEEP: handleCustomSorting()
```

**Updated constructor:**
```php
public function __construct() {
    add_filter( 'manage_aps_product_posts_columns', [ $this, 'addCustomColumns' ] );
    add_action( 'manage_aps_product_posts_custom_column', [ $this, 'renderCustomColumns' ], 10, 2 );
    add_filter( 'manage_edit-aps_product_sortable_columns', [ $this, 'makeColumnsSortable' ] );
    add_action( 'pre_get_posts', [ $this, 'handleCustomSorting' ] );
    // ❌ REMOVED: add_action( 'restrict_manage_posts', ... )
}
```

**Note:** ProductsTable.php now handles column rendering directly. Columns.php may be deprecated or repurposed later.

---

### **Step 5: Update Admin.php to Call ProductTableUI (10 min)**

**Goal:** Ensure ProductTableUI->render() is called

#### File: src/Admin/Admin.php

**Update constructor:**
```php
public function __construct(
    private Assets $assets,
    private ProductService $product_service,
    private Headers $headers,
    Menu $menu,
    ProductFormHandler $form_handler
) {
    $this->settings = new Settings();
    $this->metaboxes = new MetaBoxes( $this->product_service );
    $this->form_handler = $form_handler;
    $this->menu = $menu;
    $this->columns = new Columns();
    $this->product_table_ui = new ProductTableUI(); // ✅ ALREADY EXISTS
    
    // ✅ ADD: Hook to render ProductTableUI
    add_action( 'all_admin_notices', [ $this->product_table_ui, 'render' ], 10 );
}
```

**Note:** This may already exist. Check if `product_table_ui` is already hooked.

---

### **Step 6: Testing (30 min)**

**Goal:** Verify all functionality works correctly

#### Test Checklist:

**UI Rendering:**
- [ ] Custom UI above table renders (buttons, filters)
- [ ] ProductsTable renders below custom UI
- [ ] No duplicate UI elements
- [ ] No WordPress default UI showing

**Features:**
- [ ] Action buttons work (Add, Trash, Bulk Upload, Check Links)
- [ ] Custom filters work (Search, Category, Featured, Sort)
- [ ] Clear filters button works
- [ ] Status counts (views) display correctly
- [ ] Pagination works
- [ ] Sorting works (click column headers)
- [ ] Bulk actions dropdown shows

**Table Display:**
- [ ] All columns render (logo, title, category, tags, ribbon, featured, price, status, date)
- [ ] Logo images display correctly
- [ ] Featured star displays correctly
- [ ] Ribbon badges display with correct colors
- [ ] Prices display with discount calculation
- [ ] Status badges display with correct colors
- [ ] Row actions (Edit, Trash, View) work

**Responsive:**
- [ ] Works on desktop
- [ ] Works on tablet
- [ ] Works on mobile

---

## 📊 Summary Table

| Step | File | Action | Lines Changed | Time |
|-------|-------|--------|---------------|-------|
| 1 | ServiceProvider.php | Remove ProductsPageHooks registration | -3 | 5 min |
| 1 | ProductsPageHooks.php | DELETE FILE | -244 | 5 min |
| 2 | ProductsTable.php | CREATE NEW FILE | +400 | 2 hours |
| 3 | ProductTableUI.php | Remove table HTML, add ProductsTable | -150 | 1 hour |
| 4 | Columns.php | Remove addFilters() method | -40 | 30 min |
| 5 | Admin.php | Add render hook (if needed) | +2 | 10 min |
| 6 | Testing | Manual testing | 0 | 30 min |

**Total:** ~4 hours

---

## 🎯 Expected Result

**Before:**
```
[ProductsPageHooks UI] → Duplicates ProductTableUI ❌
[ProductTableUI UI]   → Renders custom UI
[WordPress Default UI] → Shows through (not hidden)
```

**After:**
```
┌─────────────────────────────────────┐
│ ProductTableUI (Custom UI)       │
│ - Action buttons                  │
│ - Custom filters                 │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ ProductsTable (WP_List_Table)      │
│ - Custom columns                 │
│ - Native pagination              │
│ - Native sorting                │
│ - Native bulk actions           │
└─────────────────────────────────────┘
```

**Status:** ✅ True Hybrid Approach - No Duplication

---

## 📝 Notes

1. **Single Source of Truth:** All filters and UI from ProductTableUI
2. **No Duplication:** WordPress default UI is properly hidden
3. **True Hybrid:** Custom UI above + WordPress table below
4. **Maintainable:** Clear separation of concerns
5. **Extensible:** Easy to add new columns or features
6. **WordPress Standards:** Follows WP_List_Table best practices

---

## 🔧 Additional Considerations

### 1. Columns.php Deprecation
After implementing ProductsTable, Columns.php may no longer be needed since ProductsTable handles column rendering. Consider:
- Deprecating Columns.php entirely
- Or repurposing it for other admin column needs

### 2. Asset Enqueuing
Ensure CSS/JS files are properly enqueued:
- `assets/css/admin-table.css`
- `assets/css/product-table-ui.css`
- `assets/js/product-table-ui.js`

### 3. AJAX Handlers
Verify AJAX handlers work with new structure:
- Bulk upload
- Check links
- Bulk actions

### 4. Permissions
Check all user capabilities:
- Can edit products?
- Can delete products?
- Can view products?

---

**Status:** 📋 Plan v2.0 Complete - Ready for Implementation
**Files to Modify/Create:** 6 files
**Estimated Time:** ~4 hours
**Result:** Professional true hybrid architecture with no duplication
