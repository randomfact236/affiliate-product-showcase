# Section 5: Cross-Features - True Hybrid Implementation Plan

**Created:** January 24, 2026  
**Priority:** 🟠 HIGH - Implement remaining features and ensure true hybrid compliance  
**Scope:** Basic Level Features Only (66 features total, 18 already implemented ~27%)

---

## Executive Summary

Section 5 (Cross-Features) is **PARTIALLY IMPLEMENTED** - needs remaining features and true hybrid compliance.

**Current Status:**
- ✅ 18/66 features implemented (~27%)
- ❌ 48/66 features need implementation
- ⚠️ Some implemented features may need true hybrid fixes
- ❌ Missing key integration features

**Impact:** Cross-features need completion and true hybrid compliance.

---

## Understanding True Hybrid for Cross-Features

**True Hybrid Means:**
1. ✅ All features use consistent patterns (Model, Factory, Repository, Controller)
2. ✅ All meta keys use underscore prefix where applicable
3. ✅ All models have readonly properties
4. ✅ All factories have from_* methods
5. ✅ All repositories have full CRUD operations
6. ✅ All admin components use proper forms and nonce verification
7. ✅ All REST controllers have proper permission checks
8. ✅ Consistent with Product/Category/Ribbon/Tag patterns

---

## Basic Level Features to Implement (48 Remaining Features)

### 18. Category Product Integration (6 features)
- [ ] 115. Filter products by category in shortcode
- [ ] 116. Display category count on products
- [ ] 117. Category breadcrumbs on product page
- [ ] 118. Category sub-menu in product list
- [ ] 119. Quick add product to category
- [ ] 120. Product count per category widget

### 19. Tag Product Integration (6 features)
- [ ] 121. Filter products by tag in shortcode
- [ ] 122. Display tag count on products
- [ ] 123. Tag cloud widget
- [ ] 124. Tag filter in product list
- [ ] 125. Quick add product to tag
- [ ] 126. Product count per tag widget

### 20. Ribbon Product Integration (2 features)
- [ ] 127. Ribbon selector in product form
- [ ] 128. Ribbon preview on product card

### 21. Product Display (8 features)
- [ ] 129. Product grid layout
- [ ] 130. Product list layout
- [ ] 131. Responsive design
- [ ] 132. Product search
- [ ] 133. Product sorting
- [ ] 134. Load more button
- [ ] 135. Quick view modal
- [ ] 136. Empty state display

### 22. Product Filtering (6 features)
- [ ] 137. Category filter sidebar
- [ ] 138. Tag filter sidebar
- [ ] 139. Price range filter
- [ ] 140. Availability filter
- [ ] 141. Clear all filters
- [ ] 142. Active filters display

### 23. Product Shortcodes (6 features)
- [ ] 143. Product grid shortcode
- [ ] 144. Product list shortcode
- [ ] 145. Featured products shortcode
- [ ] 146. Category products shortcode
- [ ] 147. Tag products shortcode
- [ ] 148. Search products shortcode

### 24. Frontend Assets (4 features)
- [ ] 149. Frontend CSS
- [ ] 150. Frontend JavaScript
- [ ] 151. Image lazy loading
- [ ] 152. Mobile optimization

### 25. AJAX/AJAX-Like Features (10 features)
- [ ] 153. AJAX product filtering
- [ ] 154. AJAX load more
- [ ] 155. AJAX quick view
- [ ] 156. AJAX add to cart
- [ ] 157. AJAX remove from cart
- [ ] 158. AJAX update cart
- [ ] 159. AJAX cart count
- [ ] 160. AJAX search suggestions
- [ ] 161. AJAX category/tag counts
- [ ] 162. Nonce verification

---

## Phase 1: Create Shortcode Service (HIGH)

**Priority:** 🟠 HIGH  
**Files to Create:** `src/Services/ShortcodeService.php`

### Service Structure

```php
<?php
/**
 * Shortcode Service
 *
 * Handles all shortcodes for products, categories and tags.
 *
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Repositories\TagRepository;

/**
 * Shortcode Service
 *
 * Handles all shortcodes for products, categories, and tags.
 *
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 * @author Development Team
 */
final class ShortcodeService {
    /**
     * Product repository instance
     *
     * @var ProductRepository
     * @since 1.0.0
     */
    private ProductRepository $product_repository;

    /**
     * Category repository instance
     *
     * @var CategoryRepository
     * @since 1.0.0
     */
    private CategoryRepository $category_repository;

    /**
     * Tag repository instance
     *
     * @var TagRepository
     * @since 1.0.0
     */
    private TagRepository $tag_repository;

    /**
     * Constructor
     *
     * @param ProductRepository $product_repository Product repository instance
     * @param CategoryRepository $category_repository Category repository instance
     * @param TagRepository $tag_repository Tag repository instance
     * @since 1.0.0
     */
    public function __construct(
        ProductRepository $product_repository,
        CategoryRepository $category_repository,
        TagRepository $tag_repository
    ) {
        $this->product_repository = $product_repository;
        $this->category_repository = $category_repository;
        $this->tag_repository = $tag_repository;
    }

    /**
     * Register all shortcodes
     *
     * @return void
     * @since 1.0.0
     */
    public function register_shortcodes(): void {
        add_shortcode('aps_products', [$this, 'render_products']);
        add_shortcode('aps_product_grid', [$this, 'render_product_grid']);
        add_shortcode('aps_product_list', [$this, 'render_product_list']);
        add_shortcode('aps_featured_products', [$this, 'render_featured_products']);
        add_shortcode('aps_category_products', [$this, 'render_category_products']);
        add_shortcode('aps_tag_products', [$this, 'render_tag_products']);
        add_shortcode('aps_search_products', [$this, 'render_search_products']);
    }

    /**
     * Render products shortcode
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered HTML
     * @since 1.0.0
     */
    public function render_products(array $atts = [], ?string $content = null): string {
        $atts = shortcode_atts([
            'category' => '',
            'tag'      => '',
            'limit'    => 10,
            'orderby'  => 'date',
            'order'    => 'DESC',
        ], $atts);

        $args = [
            'posts_per_page' => (int) $atts['limit'],
            'orderby'          => sanitize_text_field($atts['orderby']),
            'order'            => sanitize_text_field($atts['order']),
        ];

        // Add category filter
        if (!empty($atts['category'])) {
            $category = $this->category_repository->find_by_slug($atts['category']);
            if ($category) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'aps_category',
                        'field'    => 'term_id',
                        'terms'    => $category->id,
                    ],
                ];
            }
        }

        // Add tag filter
        if (!empty($atts['tag'])) {
            $tag = $this->tag_repository->find_by_slug($atts['tag']);
            if ($tag) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'aps_tag',
                        'field'    => 'term_id',
                        'terms'    => $tag->id,
                    ],
                ];
            }
        }

        $args['post_type'] = 'aps_product';
        $args['post_status'] = 'publish';

        $query = new \WP_Query($args);

        ob_start();
        include plugin_dir_path('../public/partials/product-grid.php');
        return ob_get_clean();
    }

    /**
     * Render product grid shortcode
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered HTML
     * @since 1.0.0
     */
    public function render_product_grid(array $atts = [], ?string $content = null): string {
        return $this->render_products(
            array_merge($atts, ['layout' => 'grid']),
            $content
        );
    }

    /**
     * Render product list shortcode
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered HTML
     * @since 1.0.0
     */
    public function render_product_list(array $atts = [], ?string $content = null): string {
        return $this->render_products(
            array_merge($atts, ['layout' => 'list']),
            $content
        );
    }

    /**
     * Render featured products shortcode
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered HTML
     * @since 1.0.0
     */
    public function render_featured_products(array $atts = [], ?string $content = null): string {
        $atts = shortcode_atts([
            'limit' => 6,
        ], $atts);

        $args = [
            'posts_per_page' => (int) $atts['limit'],
            'post_type'      => 'aps_product',
            'post_status'    => 'publish',
            'meta_query'      => [
                [
                    'key'     => '_aps_featured',
                    'value'   => 1,
                    'compare' => '=',
                ],
            ],
        ];

        $query = new \WP_Query($args);

        ob_start();
        include plugin_dir_path('../public/partials/product-grid.php');
        return ob_get_clean();
    }

    /**
     * Render category products shortcode
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered HTML
     * @since 1.0.0
     */
    public function render_category_products(array $atts = [], ?string $content = null): string {
        if (empty($atts['category'])) {
            return '<p>' . esc_html__('Please provide a category.', 'affiliate-product-showcase') . '</p>';
        }

        $category = $this->category_repository->find_by_slug($atts['category']);
        if (!$category) {
            return '<p>' . esc_html__('Category not found.', 'affiliate-product-showcase') . '</p>';
        }

        $args = shortcode_atts([
            'limit' => 10,
        ], $atts);

        $query = new \WP_Query([
            'post_type'      => 'aps_product',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $args['limit'],
            'tax_query'      => [
                [
                    'taxonomy' => 'aps_category',
                    'field'    => 'term_id',
                    'terms'    => $category->id,
                ],
            ],
        ]);

        ob_start();
        include plugin_dir_path('../public/partials/product-grid.php');
        return ob_get_clean();
    }

    /**
     * Render tag products shortcode
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered HTML
     * @since 1.0.0
     */
    public function render_tag_products(array $atts = [], ?string $content = null): string {
        if (empty($atts['tag'])) {
            return '<p>' . esc_html__('Please provide a tag.', 'affiliate-product-showcase') . '</p>';
        }

        $tag = $this->tag_repository->find_by_slug($atts['tag']);
        if (!$tag) {
            return '<p>' . esc_html__('Tag not found.', 'affiliate-product-showcase') . '</p>';
        }

        $args = shortcode_atts([
            'limit' => 10,
        ], $atts);

        $query = new \WP_Query([
            'post_type'      => 'aps_product',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $args['limit'],
            'tax_query'      => [
                [
                    'taxonomy' => 'aps_tag',
                    'field'    => 'term_id',
                    'terms'    => $tag->id,
                ],
            ],
        ]);

        ob_start();
        include plugin_dir_path('../public/partials/product-grid.php');
        return ob_get_clean();
    }

    /**
     * Render search products shortcode
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered HTML
     * @since 1.0.0
     */
    public function render_search_products(array $atts = [], ?string $content = null): string {
        $search_term = sanitize_text_field($atts['search'] ?? '');

        if (empty($search_term)) {
            return '<p>' . esc_html__('Please provide a search term.', 'affiliate-product-showcase') . '</p>';
        }

        $args = shortcode_atts([
            'limit' => 10,
        ], $atts);

        $query = new \WP_Query([
            'post_type'      => 'aps_product',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $args['limit'],
            's'              => $search_term,
        ]);

        ob_start();
        include plugin_dir_path('../public/partials/product-grid.php');
        return ob_get_clean();
    }
}
```

### Implementation Steps

1. **Create file** `src/Services/ShortcodeService.php`
2. **Copy code from template above**
3. **Verify all shortcode methods**
4. **Test shortcodes**

### Verification Checklist
- [ ] ShortcodeService created
- [ ] All shortcode methods implemented
- [ ] Repositories injected
- [ ] Shortcodes registered
- [ ] PHPStan analysis passes

---

## Phase 2: Create Product Display Component (HIGH)

**Priority:** 🟠 HIGH  
**Files to Create:** `src/Public/ProductDisplay.php`, `assets/js/product-display.js`

### Component Structure

```php
<?php
/**
 * Product Display
 *
 * Handles product grid/list layouts, filtering, and pagination.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Repositories\TagRepository;

/**
 * Product Display
 *
 * Handles product grid/list layouts, filtering, and pagination.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class ProductDisplay {
    /**
     * Product repository instance
     *
     * @var ProductRepository
     * @since 1.0.0
     */
    private ProductRepository $product_repository;

    /**
     * Category repository instance
     *
     * @var CategoryRepository
     * @since 1.0.0
     */
    private CategoryRepository $category_repository;

    /**
     * Tag repository instance
     *
     * @var TagRepository
     * @since 1.0.0
     */
    private TagRepository $tag_repository;

    /**
     * Constructor
     *
     * @param ProductRepository $product_repository Product repository instance
     * @param CategoryRepository $category_repository Category repository instance
     * @param TagRepository $tag_repository Tag repository instance
     * @since 1.0.0
     */
    public function __construct(
        ProductRepository $product_repository,
        CategoryRepository $category_repository,
        TagRepository $tag_repository
    ) {
        $this->product_repository = $product_repository;
        $this->category_repository = $category_repository;
        $this->tag_repository = $tag_repository;
    }

    /**
     * Render products
     *
     * @param array<string, mixed> $args Query arguments
     * @return void
     * @since 1.0.0
     */
    public function render_products(array $args = []): void {
        $layout = $args['layout'] ?? 'grid';
        $category_slug = $args['category'] ?? '';
        $tag_slug = $args['tag'] ?? '';
        $search = $args['search'] ?? '';
        $page = (int) ($args['page'] ?? 1);
        $per_page = (int) ($args['per_page'] ?? 12);
        $orderby = $args['orderby'] ?? 'date';
        $order = $args['order'] ?? 'DESC';

        $query_args = [
            'post_type'      => 'aps_product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => $orderby,
            'order'          => $order,
        ];

        // Add search
        if (!empty($search)) {
            $query_args['s'] = $search;
        }

        // Add category filter
        if (!empty($category_slug)) {
            $category = $this->category_repository->find_by_slug($category_slug);
            if ($category) {
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => 'aps_category',
                        'field'    => 'term_id',
                        'terms'    => $category->id,
                    ],
                ];
            }
        }

        // Add tag filter
        if (!empty($tag_slug)) {
            $tag = $this->tag_repository->find_by_slug($tag_slug);
            if ($tag) {
                $query_args['tax_query'][] = [
                    [
                        'taxonomy' => 'aps_tag',
                        'field'    => 'term_id',
                        'terms'    => $tag->id,
                    ],
                ];
            }
        }

        $query = new \WP_Query($query_args);
        $total_pages = $query->max_num_pages;

        // Load products
        $products = [];
        foreach ($query->posts as $post) {
            $products[] = \AffiliateProductShowcase\Factories\ProductFactory::from_post($post);
        }

        // Load template
        $template = $layout === 'grid' 
            ? 'product-grid.php' 
            : 'product-list.php';

        include plugin_dir_path('../public/partials/' . $template);
    }
}
```

### JavaScript File

```javascript
/**
 * Product Display JavaScript
 *
 * Handles AJAX requests for product filtering, pagination, and quick view.
 *
 * @since 1.0.0
 */

(function($) {
    'use strict';

    const apsProductDisplay = {
        init: function() {
            this.setupFilters();
            this.setupPagination();
            this.setupQuickView();
        },

        setupFilters: function() {
            // Category filter change
            $(document).on('change', '.aps-filter-category', function() {
                apsProductDisplay.filterProducts();
            });

            // Tag filter change
            $(document).on('change', '.aps-filter-tag', function() {
                apsProductDisplay.filterProducts();
            });

            // Search input
            $(document).on('input', '.aps-search-input', apsProductDisplay.debounce(function() {
                apsProductDisplay.filterProducts();
            }, 300));

            // Clear filters
            $(document).on('click', '.aps-clear-filters', function(e) {
                e.preventDefault();
                $('.aps-filter-category').val('');
                $('.aps-filter-tag').val('');
                $('.aps-search-input').val('');
                apsProductDisplay.filterProducts();
            });
        },

        filterProducts: function() {
            const category = $('.aps-filter-category').val();
            const tag = $('.aps-filter-tag').val();
            const search = $('.aps-search-input').val();

            $.ajax({
                url: apsData.ajax_url,
                type: 'POST',
                data: {
                    action: 'aps_filter_products',
                    nonce: apsData.nonce,
                    category: category,
                    tag: tag,
                    search: search,
                    page: 1
                },
                success: function(response) {
                    $('.aps-product-container').html(response.html);
                    apsProductDisplay.updatePagination(response.pagination);
                },
                error: function() {
                    console.error('Failed to filter products');
                }
            });
        },

        setupPagination: function() {
            $(document).on('click', '.aps-pagination-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                apsProductDisplay.loadPage(page);
            });
        },

        loadPage: function(page) {
            const category = $('.aps-filter-category').val();
            const tag = $('.aps-filter-tag').val();
            const search = $('.aps-search-input').val();

            $.ajax({
                url: apsData.ajax_url,
                type: 'POST',
                data: {
                    action: 'aps_filter_products',
                    nonce: apsData.nonce,
                    category: category,
                    tag: tag,
                    search: search,
                    page: page
                },
                success: function(response) {
                    $('.aps-product-container').html(response.html);
                    apsProductDisplay.updatePagination(response.pagination);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                error: function() {
                    console.error('Failed to load products');
                }
            });
        },

        updatePagination: function(pagination) {
            if (!pagination || pagination.total_pages <= 1) {
                $('.aps-pagination').hide();
                return;
            }

            let html = '<div class="aps-pagination">';
            html += '<span class="aps-pagination-info">Page ' + pagination.current_page + ' of ' + pagination.total_pages + '</span>';

            if (pagination.current_page > 1) {
                html += '<a href="#" class="aps-pagination-link" data-page="' + (pagination.current_page - 1) + '">Previous</a>';
            }

            html += '<span class="aps-pagination-ellipsis">...</span>';

            if (pagination.current_page < pagination.total_pages) {
                html += '<a href="#" class="aps-pagination-link" data-page="' + (pagination.current_page + 1) + '">Next</a>';
            }

            html += '</div>';
            $('.aps-pagination').html(html).show();
        },

        setupQuickView: function() {
            $(document).on('click', '.aps-quick-view', function(e) {
                e.preventDefault();
                const productId = $(this).data('product-id');
                apsProductDisplay.showQuickView(productId);
            });

            $(document).on('click', '.aps-modal-close, .aps-modal-overlay', function() {
                $('.aps-modal-overlay').hide();
                $('.aps-modal-content').hide();
            });
        },

        showQuickView: function(productId) {
            $.ajax({
                url: apsData.ajax_url,
                type: 'POST',
                data: {
                    action: 'aps_quick_view',
                    nonce: apsData.nonce,
                    product_id: productId
                },
                success: function(response) {
                    $('.aps-modal-content').html(response.html);
                    $('.aps-modal-overlay').show();
                    $('.aps-modal-content').show();
                },
                error: function() {
                    console.error('Failed to load product quick view');
                }
            });
        },

        debounce: function(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        apsProductDisplay.init();
    });

})(jQuery);
```

### Implementation Steps

1. **Create ProductDisplay.php**
2. **Create product-display.js**
3. **Verify filtering works**
4. **Verify pagination works**
5. **Verify quick view works**

### Verification Checklist
- [ ] ProductDisplay component created
- [ ] JavaScript created
- [ ] Filtering works
- [ ] Pagination works
- [ ] Quick view modal works
- [ ] AJAX handlers implemented
- [ ] Nonce verification in AJAX

---

## Phase 3: Register Components (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Modify:** `src/Plugin/Loader.php`, `src/Plugin/ServiceProvider.php`

### Add to ServiceProvider

```php
// Add to $this->getContainer()->addShared() calls:
$this->getContainer()->addShared(ShortcodeService::class)
    ->addArgument(ProductRepository::class)
    ->addArgument(CategoryRepository::class)
    ->addArgument(TagRepository::class);
$this->getContainer()->addShared(ProductDisplay::class)
    ->addArgument(ProductRepository::class)
    ->addArgument(CategoryRepository::class)
    ->addArgument(TagRepository::class);
```

### Add to Loader

```php
use AffiliateProductShowcase\Services\ShortcodeService;
use AffiliateProductShowcase\Public\ProductDisplay;

// In constructor:
$this->shortcode_service = $container->get(ShortcodeService::class);
$this->product_display = $container->get(ProductDisplay::class);

// In register() method:
$this->shortcode_service->register_shortcodes();

// Add AJAX handlers
add_action('wp_ajax_aps_filter_products', [$this, 'handle_ajax_filter']);
add_action('wp_ajax_nopriv_aps_filter_products', [$this, 'handle_ajax_filter']);
add_action('wp_ajax_aps_quick_view', [$this, 'handle_ajax_quick_view']);
add_action('wp_ajax_nopriv_aps_quick_view', [$this, 'handle_ajax_quick_view']);
```

### Implementation Steps

1. **Backup files**
2. **Add registrations to ServiceProvider**
3. **Add registrations to Loader**
4. **Add AJAX handlers**

### Verification Checklist
- [ ] ShortcodeService registered
- [ ] ProductDisplay registered
- [ ] AJAX handlers registered
- [ ] Components load without errors

---

## Phase 4: Create Templates (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `public/partials/product-grid.php`, `public/partials/product-list.php`

### Product Grid Template

```php
<?php
/**
 * Product Grid Template
 *
 * Displays products in a responsive grid layout.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */

if (!defined('ABSPATH')) {
    exit;
}

if (empty($products)) {
    return;
}
?>

<div class="aps-products-container" data-layout="grid">
    <?php foreach ($products as $product): ?>
        <article class="aps-product-item aps-product-grid-item" id="aps-product-<?php echo esc_attr($product->id); ?>">
            <?php if ($product->image_url): ?>
                <img src="<?php echo esc_url($product->image_url); ?>" 
                     alt="<?php echo esc_attr($product->title); ?>" 
                     class="aps-product-image"
                     loading="lazy" />
            <?php endif; ?>
            
            <?php if ($product->ribbon): ?>
                <div class="aps-product-ribbon" style="background-color: <?php echo esc_attr($product->ribbon->background_color); ?>; color: <?php echo esc_attr($product->ribbon->text_color); ?>;">
                    <?php echo esc_html($product->ribbon->name); ?>
                </div>
            <?php endif; ?>

            <div class="aps-product-info">
                <h2 class="aps-product-title">
                    <a href="<?php echo esc_url($product->affiliate_url); ?>" 
                       target="_blank" 
                       rel="nofollow sponsored">
                        <?php echo esc_html($product->title); ?>
                    </a>
                </h2>

                <div class="aps-product-meta">
                    <?php if ($product->sale_price): ?>
                        <span class="aps-price-original"><?php echo esc_html($product->regular_price); ?></span>
                        <span class="aps-price-current"><?php echo esc_html($product->sale_price); ?></span>
                        <span class="aps-discount-badge">-<?php echo esc_html($product->get_calculated_discount()); ?>%</span>
                    <?php else: ?>
                        <span class="aps-price-current"><?php echo esc_html($product->regular_price); ?></span>
                    <?php endif; ?>

                    <?php if ($product->rating): ?>
                        <div class="aps-rating">
                            <?php echo str_repeat('★', (int) $product->rating); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product->category_ids)): ?>
                        <div class="aps-categories">
                            <?php foreach (array_slice($product->category_ids, 0, 2) as $cat_id): ?>
                                <?php $cat = get_term($cat_id, 'aps_category'); ?>
                                <a href="<?php echo esc_url(get_term_link($cat_id)); ?>" class="aps-category-tag">
                                    <?php echo esc_html($cat->name); ?>
                                </a>
                            <?php endforeach; ?>
                            <?php if (count($product->category_ids) > 2): ?>
                                <span class="aps-more-categories">+<?php echo count($product->category_ids) - 2; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product->tag_ids)): ?>
                        <div class="aps-tags">
                            <?php foreach (array_slice($product->tag_ids, 0, 3) as $tag_id): ?>
                                <?php $tag = get_term($tag_id, 'aps_tag'); ?>
                                <a href="<?php echo esc_url(get_term_link($tag_id)); ?>" class="aps-tag-badge" style="background-color: <?php echo esc_attr($tag->color); ?>;">
                                    <?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                            <?php if (count($product->tag_ids) > 3): ?>
                                <span class="aps-more-tags">+<?php echo count($product->tag_ids) - 3; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <a href="<?php echo esc_url($product->affiliate_url); ?>" 
                   target="_blank" 
                   rel="nofollow sponsored"
                   class="aps-product-button">
                    <?php esc_html_e('View Deal', 'affiliate-product-showcase'); ?>
                </a>
            </div>

            <button class="aps-quick-view" data-product-id="<?php echo esc_attr($product->id); ?>">
                <?php esc_html_e('Quick View', 'affiliate-product-showcase'); ?>
            </button>
        </article>
    <?php endforeach; ?>
</div>

<?php if (isset($pagination) && $pagination->total_pages > 1): ?>
    <div class="aps-pagination">
        <span class="aps-pagination-info">
            <?php printf(
                esc_html__('Page %d of %d', 'affiliate-product-showcase'),
                $pagination->current_page,
                $pagination->total_pages
            ); ?>
        </span>
        <?php if ($pagination->current_page > 1): ?>
            <a href="#" class="aps-pagination-link" data-page="<?php echo esc_attr($pagination->current_page - 1); ?>">
                <?php esc_html_e('Previous', 'affiliate-product-showcase'); ?>
            </a>
        <?php endif; ?>
        <?php if ($pagination->current_page < $pagination->total_pages): ?>
            <a href="#" class="aps-pagination-link" data-page="<?php echo esc_attr($pagination->current_page + 1); ?>">
                <?php esc_html_e('Next', 'affiliate-product-showcase'); ?>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
```

### Product List Template

```php
<?php
/**
 * Product List Template
 *
 * Displays products in a responsive list layout.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */

if (!defined('ABSPATH')) {
    exit;
}

if (empty($products)) {
    return;
}
?>

<div class="aps-products-container" data-layout="list">
    <?php foreach ($products as $product): ?>
        <article class="aps-product-item aps-product-list-item" id="aps-product-<?php echo esc_attr($product->id); ?>">
            <div class="aps-list-content">
                <div class="aps-list-image">
                    <?php if ($product->image_url): ?>
                        <img src="<?php echo esc_url($product->image_url); ?>" 
                             alt="<?php echo esc_attr($product->title); ?>" 
                             class="aps-product-image"
                             loading="lazy" />
                    <?php endif; ?>
                </div>

                <div class="aps-list-details">
                    <h2 class="aps-product-title">
                        <a href="<?php echo esc_url($product->affiliate_url); ?>" 
                           target="_blank" 
                           rel="nofollow sponsored">
                            <?php echo esc_html($product->title); ?>
                        </a>
                    </h2>

                    <div class="aps-list-meta">
                        <?php if ($product->sale_price): ?>
                            <span class="aps-price-original"><?php echo esc_html($product->regular_price); ?></span>
                            <span class="aps-price-current"><?php echo esc_html($product->sale_price); ?></span>
                        <?php else: ?>
                            <span class="aps-price-current"><?php echo esc_html($product->regular_price); ?></span>
                        <?php endif; ?>

                        <?php if ($product->rating): ?>
                            <span class="aps-rating">
                                <?php echo str_repeat('★', (int) $product->rating); ?>
                            </span>
                        <?php endif; ?>

                        <div class="aps-categories-tags">
                            <?php if (!empty($product->category_ids)): ?>
                                <span class="aps-meta-label"><?php esc_html_e('Categories:', 'affiliate-product-showcase'); ?></span>
                                <?php foreach ($product->category_ids as $cat_id): ?>
                                    <?php $cat = get_term($cat_id, 'aps_category'); ?>
                                    <a href="<?php echo esc_url(get_term_link($cat_id)); ?>" class="aps-category-tag">
                                        <?php echo esc_html($cat->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($product->tag_ids)): ?>
                                <span class="aps-meta-label"><?php esc_html_e('Tags:', 'affiliate-product-showcase'); ?></span>
                                <?php foreach ($product->tag_ids as $tag_id): ?>
                                    <?php $tag = get_term($tag_id, 'aps_tag'); ?>
                                    <a href="<?php echo esc_url(get_term_link($tag_id)); ?>" class="aps-tag-badge" style="background-color: <?php echo esc_attr($tag->color); ?>;">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a href="<?php echo esc_url($product->affiliate_url); ?>" 
                       target="_blank" 
                       rel="nofollow sponsored"
                       class="aps-product-button">
                        <?php esc_html_e('View Deal', 'affiliate-product-showcase'); ?>
                    </a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
```

### Implementation Steps

1. **Create product-grid.php**
2. **Create product-list.php**
3. **Verify templates render correctly**
4. **Verify responsive design**

### Verification Checklist
- [ ] Product grid template created
- [ ] Product list template created
- [ ] Ribbon display works
- [ ] Price display works
- [ ] Category display works
- [ ] Tag display works
- [ ] Responsive design implemented

---

## Phase 5: Add Frontend Assets (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `assets/css/product-display.css`

### CSS Structure

```css
/* Product Display Styles */
.aps-products-container {
    display: grid;
    gap: 20px;
    margin: 20px 0;
}

.aps-products-container[data-layout="grid"] {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}

.aps-products-container[data-layout="list"] {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.aps-product-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.aps-product-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.aps-product-grid-item {
    display: flex;
    flex-direction: column;
}

.aps-product-list-item {
    display: flex;
    flex-direction: row;
    gap: 15px;
}

.aps-product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
}

.aps-product-info {
    padding: 15px;
    flex: 1;
}

.aps-list-image {
    width: 150px;
    height: 150px;
    flex-shrink: 0;
}

.aps-list-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.aps-product-title {
    margin: 0 0 10px;
    font-size: 1.25rem;
    font-weight: 600;
}

.aps-product-title a {
    text-decoration: none;
    color: inherit;
    transition: color 0.2s ease;
}

.aps-product-title a:hover {
    color: #007bff;
}

.aps-product-meta {
    margin: 10px 0;
}

.aps-categories-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 10px 0;
}

.aps-category-tag {
    display: inline-block;
    padding: 4px 10px;
    background: #f0f0f0;
    border-radius: 20px;
    color: #ffffff;
    text-decoration: none;
    font-size: 0.875rem;
}

.aps-tag-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 15px;
    color: #ffffff;
    text-decoration: none;
    font-size: 0.875rem;
}

.aps-price-current {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
}

.aps-price-original {
    font-size: 1rem;
    color: #6c757d;
    text-decoration: line-through;
    margin-right: 10px;
}

.aps-discount-badge {
    background: #dc3545;
    color: #ffffff;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    margin-left: 8px;
}

.aps-rating {
    color: #ffc107;
    letter-spacing: 2px;
}

.aps-product-ribbon {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 10;
}

.aps-product-button {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: #ffffff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s ease;
}

.aps-product-button:hover {
    background: #0056b3;
}

.aps-quick-view {
    background: transparent;
    border: 1px solid #007bff;
    color: #007bff;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    margin-top: auto;
}

.aps-quick-view:hover {
    background: #007bff;
    color: #ffffff;
}

.aps-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
}

.aps-pagination-link {
    padding: 8px 16px;
    background: #007bff;
    color: #ffffff;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.2s ease;
}

.aps-pagination-link:hover {
    background: #0056b3;
}

.aps-pagination-ellipsis {
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .aps-products-container[data-layout="grid"] {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }

    .aps-product-list-item {
        flex-direction: column;
    }

    .aps-list-image {
        width: 100%;
        height: 200px;
    }

    .aps-list-details {
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .aps-products-container {
        margin: 10px 0;
    }

    .aps-product-item {
        border-width: 0;
        box-shadow: none;
    }
}
```

### Implementation Steps

1. **Create product-display.css**
2. **Verify responsive breakpoints**
3. **Test on multiple devices**

### Verification Checklist
- [ ] CSS file created
- [ ] Grid layout styles defined
- [ ] List layout styles defined
- [ ] Responsive breakpoints defined
- [ ] Mobile-first approach
- [ ] Touch-friendly styles

---

## Phase 6: Testing & Verification (REQUIRED)

**Priority:** 🟡 REQUIRED

### Unit Tests

**File:** `tests/Unit/Services/ShortcodeServiceTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Services\ShortcodeService;

final class ShortcodeServiceTest extends TestCase {
    public function test_render_products(): void {
        $service = new ShortcodeService(
            $this->createMockProductRepository(),
            $this->createMockCategoryRepository(),
            $this->createMockTagRepository()
        );

        $html = $service->render_products([
            'limit' => 5,
            'orderby' => 'date'
        ]);

        $this->assertStringContainsString('<div class="aps-products-container"', $html);
    }

    public function test_render_category_products(): void {
        // Test category filter
    }

    private function createMockProductRepository() {
        return $this->createMock(\AffiliateProductShowcase\Repositories\ProductRepository::class);
    }

    private function createMockCategoryRepository() {
        return $this->createMock(\AffiliateProductShowcase\Repositories\CategoryRepository::class);
    }

    private function createMockTagRepository() {
        return $this->createMock(\AffiliateProductShowcase\Repositories\TagRepository::class);
    }
}
```

### Integration Tests

**File:** `tests/Integration/Services/ShortcodeServiceTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration\Services;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Services\ShortcodeService;

final class ShortcodeServiceIntegrationTest extends TestCase {
    public function test_shortcode_registration(): void {
        // Verify shortcodes are registered
        $this->assertTrue(shortcode_exists('aps_products'));
        $this->assertTrue(shortcode_exists('aps_product_grid'));
        $this->assertTrue(shortcode_exists('aps_product_list'));
    }

    public function test_shortcode_output(): void {
        // Test shortcode rendering with real data
    }
}
```

### Static Analysis

```bash
# Run PHPStan
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpstan

# Run Psalm
composer --working-dir=wp-content/plugins/affiliate-product-showcase psalm

# Run PHPCS
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs
```

### Manual Testing Checklist

**Shortcodes:**
- [ ] [aps_products] shortcode works
- [ ] [aps_product_grid] shortcode works
- [ ] [aps_product_list] shortcode works
- [ ] [aps_featured_products] shortcode works
- [ ] [aps_category_products] shortcode works
- [ ] [aps_tag_products] shortcode works
- [ ] [aps_search_products] shortcode works

**Product Display:**
- [ ] Grid layout renders correctly
- [ ] List layout renders correctly
- [ ] Category filtering works
- [ ] Tag filtering works
- [ ] Search functionality works
- [ ] Pagination works
- [ ] Quick view modal works

**Responsive Design:**
- [ ] Works on desktop (1200px+)
- [ ] Works on tablet (768-1200px)
- [ ] Works on mobile (480-768px)
- [ ] Works on small mobile (<480px)

**Integration:**
- [ ] Categories integrate with products
- [ ] Tags integrate with products
- [ ] Ribbons display on products
- [ ] All filters work together

---

## Summary

### Phase Overview

| Phase | Priority | Files Created/Modified |
|--------|----------|----------------------|
| Phase 1: Shortcode Service | 🟠 HIGH | ShortcodeService.php (new) |
| Phase 2: Product Display | 🟠 HIGH | ProductDisplay.php (new), product-display.js (new) |
| Phase 3: Register Components | 🟡 MEDIUM | ServiceProvider.php (modify), Loader.php (modify) |
| Phase 4: Create Templates | 🟡 MEDIUM | product-grid.php (new), product-list.php (new) |
| Phase 5: Frontend Assets | 🟡 MEDIUM | product-display.css (new) |
| Phase 6: Testing & Verification | 🟡 REQUIRED | Test files |

### Dependencies

- Phase 1 must be completed before Phase 2
- Phase 2 must be completed before Phase 3
- Phase 3-4 can be done in parallel
- Phase 5 depends on Phase 4
- Phase 6 depends on all previous phases

### Risk Mitigation

**Backup Strategy:**
- Create backups before each phase
- Keep backups for at least 1 week
- Test on staging environment first

**Rollback Plan:**
```bash
# If issues occur, delete new files and restore from backup
rm src/Services/ShortcodeService.php
rm src/Public/ProductDisplay.php
# Restore ServiceProvider.php from backup
cp backups/ServiceProvider.php.backup-YYYYMMDD-HHMMSS src/Plugin/ServiceProvider.php
```

**Testing Strategy:**
- Run unit tests after each phase
- Run integration tests after each phase
- Manual testing after each phase
- Static analysis before committing

---

## Next Steps

1. **Start with Phase 1** (Shortcode Service - most critical)
2. Complete phases in order
3. Test thoroughly after each phase
4. Commit changes with proper messages
5. Update feature-requirements.md with completion status

**Note:** All implementation plans (Sections 2-5) are now complete. Start with Section 2 and proceed sequentially.

---

## Expected Outcome

After completing all phases, Section 5 (Cross-Features) will be **100% true hybrid compliant**:
- ✅ All 48 remaining features implemented
- ✅ Shortcode service with all shortcodes
- ✅ Product display with filtering and pagination
- ✅ Templates for grid and list layouts
- ✅ Frontend CSS with responsive design
- ✅ AJAX handlers with nonce verification
- ✅ Category/tag/ribbon integration working
- ✅ Consistent with Product/Category/Tag/Ribbon patterns
- ✅ All meta keys use underscore prefix where applicable
- ✅ All models have readonly properties
- ✅ All factories have from_* methods
- ✅ All repositories have full CRUD operations
- ✅ Ready for production use

---

**Overall Implementation Summary:**

| Section | Total Features | Implemented | Remaining | Status |
|---------|----------------|------------|---------|
| Section 2 (Categories) | 32 | 0 | ✅ 100% (Meta key fix only) |
| Section 3 (Tags) | 24 | 0 | ❌ 0% (Needs full implementation) |
| Section 4 (Ribbons) | 23 | 0 | ❌ 0% (Needs full implementation) |
| Section 5 (Cross-Features) | 66 | 18 | ⚠️ ~27% (48 remaining) |