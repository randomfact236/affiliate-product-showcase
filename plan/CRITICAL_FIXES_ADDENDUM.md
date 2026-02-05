# Critical Fixes Addendum - v1.2

This file contains the **4 critical missing pieces** that should be added before production use.

---

## 🔧 FIX 1: Search Functionality (JavaScript)

**Add to Section 3 (showcase-frontend.js) after line ~120, inside the init() function:**

```javascript
/**
 * Initialize search functionality with debounce
 * @param {HTMLElement} container
 */
function initSearch(container) {
    const searchInput = container.querySelector('#aps-search-input');
    if (!searchInput) {
        return;
    }
    
    let debounceTimer = null;
    const DEBOUNCE_DELAY = 300; // ms
    
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        
        const searchTerm = this.value.trim();
        
        debounceTimer = setTimeout(() => {
            filterProducts({
                category: getActiveCategory(container),
                tags: getSelectedTags(container),
                sort: getCurrentSort(container),
                search: searchTerm
            });
        }, DEBOUNCE_DELAY);
    });
    
    // Clear search on 'x' button click (if browser supports)
    searchInput.addEventListener('search', function() {
        if (this.value === '') {
            filterProducts({
                category: getActiveCategory(container),
                tags: getSelectedTags(container),
                sort: getCurrentSort(container),
                search: ''
            });
        }
    });
}

/**
 * Get current sort value
 * @param {HTMLElement} container
 * @returns {string}
 */
function getCurrentSort(container) {
    const sortSelect = container.querySelector('#aps-sort-select');
    return sortSelect ? sortSelect.value : 'featured';
}
```

**Then add `initSearch(container);` call inside the init() function near `initFilters(container);`**

---

## 🔧 FIX 2: Type Validation (Template)

**Add to Section 1 (showcase-dynamic.php) after line ~36:**

```php
<?php
/**
 * Dynamic Showcase Template
 * ...
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Type validation for injected dependencies
use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Services\AffiliateService;

/**
 * @var array<Product> $products
 * @var AffiliateService|null $affiliate_service
 * @var array<string, mixed> $settings
 */
$products = $products ?? [];
$affiliate_service = $affiliate_service ?? null;
$settings = $settings ?? [];

// Validate products array contains Product instances
$validated_products = [];
foreach ($products as $product) {
    if ($product instanceof Product) {
        $validated_products[] = $product;
    }
}
$products = $validated_products;

// Early exit if affiliate service is not available
if ($affiliate_service === null || !$affiliate_service instanceof AffiliateService) {
    error_log('APS: Affiliate service not available in showcase template');
    ?>
    <div class="aps-showcase-container">
        <p class="aps-error-message">
            <?php esc_html_e('Configuration error. Please contact support.', 'affiliate-product-showcase'); ?>
        </p>
    </div>
    <?php
    return;
}

// Early exit if no products
if (empty($products)) : ?>
```

---

## 🔧 FIX 3: Product Card Partial

**Create New File:** `wp-content/plugins/affiliate-product-showcase/templates/partials/product-card.php`

```php
<?php
/**
 * Product Card Partial Template
 *
 * @package AffiliateProductShowcase\Templates\Partials
 * @since   1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var \AffiliateProductShowcase\Models\Product $product
 * @var \AffiliateProductShowcase\Services\AffiliateService $affiliate_service
 * @var string $currency_symbol
 */
$product = $product ?? null;
$affiliate_service = $affiliate_service ?? null;
$currency_symbol = $currency_symbol ?? '$';

// Type validation
if (!$product instanceof \AffiliateProductShowcase\Models\Product) {
    return;
}
if (!$affiliate_service instanceof \AffiliateProductShowcase\Services\AffiliateService) {
    return;
}

// Get product data with null coalescing
$product_id = $product->id ?? 0;
$product_name = $product->name ?? '';
$product_slug = $product->slug ?? '';
$product_logo = $product->logo_url ?? '';
$product_description = $product->short_description ?? '';
$product_rating = (float) ($product->rating ?? 0);
$product_review_count = (int) ($product->review_count ?? 0);
$original_price = (float) ($product->original_price ?? 0);
$current_price = (float) ($product->current_price ?? 0);
$product_features = $product->features ?? [];
$is_featured = !empty($product->is_featured);

// Calculate discount
$discount_percentage = 0;
if ($original_price > 0 && $current_price > 0 && $current_price < $original_price) {
    $discount_percentage = round((($original_price - $current_price) / $original_price) * 100);
}

// Get tracking URL
$tracking_url = '#';
try {
    $tracking_url = $affiliate_service->get_tracking_url($product_id);
} catch (Exception $e) {
    error_log('APS: Error getting tracking URL for product ' . $product_id . ': ' . $e->getMessage());
}

// Get categories and tags with error handling
$product_categories = wp_get_object_terms($product_id, 'aps_category');
$product_tags = wp_get_object_terms($product_id, 'aps_tag');

$category_slugs = !is_wp_error($product_categories) ? wp_list_pluck($product_categories, 'slug') : [];
$tag_slugs = !is_wp_error($product_tags) ? wp_list_pluck($product_tags, 'slug') : [];

// Format rating display
$formatted_rating = $product_rating > 0 ? number_format_i18n($product_rating, 1) : '0.0';
?>

<article 
    class="aps-tool-card <?php echo $is_featured ? 'aps-featured' : ''; ?>"
    data-category='<?php echo esc_attr(wp_json_encode($category_slugs)); ?>'
    data-tags='<?php echo esc_attr(wp_json_encode($tag_slugs)); ?>'
    data-rating="<?php echo esc_attr((string) $product_rating); ?>"
    data-date="<?php echo esc_attr((string) ($product->created_at ?? time())); ?>"
    data-popularity="<?php echo esc_attr((string) ($product->view_count ?? 0)); ?>"
    data-name="<?php echo esc_attr($product_name); ?>"
>
    <?php if ($is_featured) : ?>
        <div class="aps-featured-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
            <?php esc_html_e('Featured', 'affiliate-product-showcase'); ?>
        </div>
    <?php endif; ?>

    <div class="aps-card-header">
        <div class="aps-logo-wrapper">
            <?php if ($product_logo) : ?>
                <img src="<?php echo esc_url($product_logo); ?>" 
                     alt="<?php echo esc_attr(sprintf(__('%s logo', 'affiliate-product-showcase'), $product_name)); ?>"
                     class="aps-tool-logo"
                     loading="lazy"
                     width="50"
                     height="50">
            <?php else : ?>
                <div class="aps-logo-placeholder">
                    <?php echo esc_html(mb_substr($product_name, 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
        <h3 class="aps-tool-name"><?php echo esc_html($product_name); ?></h3>
    </div>

    <div class="aps-card-body">
        <p class="aps-description"><?php echo esc_html($product_description); ?></p>
        
        <?php if (!empty($product_features) && is_array($product_features)) : ?>
            <ul class="aps-feature-list">
                <?php foreach (array_slice($product_features, 0, 3) as $feature) : ?>
                    <li class="aps-feature-item">
                        <svg class="aps-check-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo esc_html($feature); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($tag_slugs)) : ?>
            <div class="aps-tags-row">
                <?php foreach (array_slice($tag_slugs, 0, 5) as $tag_slug) : ?>
                    <?php
                    $tag = get_term_by('slug', $tag_slug, 'aps_tag');
                    $tag_name = $tag && !is_wp_error($tag) ? $tag->name : $tag_slug;
                    ?>
                    <span class="aps-tag"><?php echo esc_html($tag_name); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="aps-rating-row">
            <div class="aps-stars" aria-label="<?php echo esc_attr(sprintf(__('Rating: %s out of 5', 'affiliate-product-showcase'), $formatted_rating)); ?>">
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <svg class="aps-star <?php echo $i <= round($product_rating) ? 'filled' : ''; ?>" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                <?php endfor; ?>
            </div>
            <span class="aps-rating-text"><?php echo esc_html($formatted_rating); ?></span>
            <?php if ($product_review_count > 0) : ?>
                <span class="aps-review-count">(<?php echo esc_html(number_format_i18n($product_review_count)); ?>)</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="aps-card-footer">
        <div class="aps-price-block">
            <?php if ($original_price > 0) : ?>
                <span class="aps-original-price"><?php echo esc_html($currency_symbol . number_format($original_price, 2)); ?></span>
            <?php endif; ?>
            
            <?php if ($current_price > 0) : ?>
                <span class="aps-current-price"><?php echo esc_html($currency_symbol . number_format($current_price, 2)); ?></span>
            <?php else : ?>
                <span class="aps-current-price"><?php esc_html_e('Free', 'affiliate-product-showcase'); ?></span>
            <?php endif; ?>
            
            <?php if ($discount_percentage > 0) : ?>
                <span class="aps-discount-badge">-<?php echo esc_html($discount_percentage); ?>%</span>
            <?php endif; ?>
        </div>

        <a href="<?php echo esc_url($tracking_url); ?>" 
           class="aps-cta-button"
           target="_blank"
           rel="nofollow noopener sponsored"
           data-product-id="<?php echo esc_attr((string) $product_id); ?>"
           data-product-name="<?php echo esc_attr($product_name); ?>">
            <?php esc_html_e('Get Deal', 'affiliate-product-showcase'); ?>
            <svg class="aps-arrow-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </a>
    </div>
</article>
```

**Update Section 1 to use the partial (replace the inner loop content):**

```php
<?php foreach ($products as $product) : ?>
    <?php
    // Pass variables to partial
    set_query_var('product', $product);
    set_query_var('affiliate_service', $affiliate_service);
    set_query_var('currency_symbol', $currency_symbol);
    
    // Load partial template
    $partial_path = __DIR__ . '/partials/product-card.php';
    if (file_exists($partial_path)) {
        load_template($partial_path, false);
    } else {
        // Fallback: inline rendering
        ?>
        <!-- Inline fallback if partial missing -->
        <article class="aps-tool-card">
            <p><?php esc_html_e('Product card template missing.', 'affiliate-product-showcase'); ?></p>
        </article>
        <?php
    }
    ?>
<?php endforeach; ?>
```

---

## 🔧 FIX 4: CSS filemtime (Shortcode)

**Update Section 4 (Shortcodes.php) enqueueStyles method:**

```php
/**
 * Enqueue showcase styles
 *
 * @return void
 */
private function enqueueStyles(): void {
    // Prevent duplicate enqueue
    if (wp_style_is('aps-showcase', 'enqueued')) {
        return;
    }
    
    $css_file = APS_PLUGIN_DIR . 'assets/css/showcase-frontend-isolated.css';
    $css_url  = APS_PLUGIN_URL . 'assets/css/showcase-frontend-isolated.css';
    $version  = '1.0.0';
    
    // Use filemtime for cache busting if file exists
    if (file_exists($css_file)) {
        $version = (string) filemtime($css_file);
    }
    
    wp_enqueue_style(
        'aps-showcase',
        $css_url,
        [],
        $version,
        'all'
    );
}
```

---

## 🔧 FIX 5: Search in AJAX Handler

**Update Section 5 (AjaxHandler.php) handleFilter method:**

```php
/**
 * Handle filter AJAX request
 *
 * @return void
 */
public function handleFilter(): void {
    // Verify nonce
    if (!check_ajax_referer('aps_filter_nonce', 'nonce', false)) {
        wp_send_json_error([
            'message' => __('Security check failed.', 'affiliate-product-showcase')
        ], 403);
    }
    
    // Sanitize inputs
    $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : 'all';
    $tags = isset($_POST['tags']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['tags'])) : [];
    $sort = isset($_POST['sort']) ? sanitize_text_field(wp_unslash($_POST['sort'])) : 'featured';
    $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
    
    // Build query args
    $query_args = [
        'post_type'      => 'aps_product',
        'posts_per_page' => 12,
        'post_status'    => 'publish',
    ];
    
    // Category filter
    if ($category !== 'all') {
        $query_args['tax_query'][] = [
            'taxonomy' => 'aps_category',
            'field'    => 'slug',
            'terms'    => $category,
        ];
    }
    
    // Tags filter
    if (!empty($tags)) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'aps_tag',
            'field'    => 'slug',
            'terms'    => $tags,
            'operator' => 'AND',
        ];
    }
    
    // Search filter
    if (!empty($search)) {
        $query_args['s'] = $search;
    }
    
    // Sort handling
    switch ($sort) {
        case 'latest':
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            break;
        case 'oldest':
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'ASC';
            break;
        case 'rating':
            $query_args['meta_key'] = '_aps_rating';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        case 'popularity':
            $query_args['meta_key'] = '_aps_view_count';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        case 'price_low':
            $query_args['meta_key'] = '_aps_current_price';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'ASC';
            break;
        case 'price_high':
            $query_args['meta_key'] = '_aps_current_price';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        case 'name_asc':
            $query_args['orderby'] = 'title';
            $query_args['order'] = 'ASC';
            break;
        case 'name_desc':
            $query_args['orderby'] = 'title';
            $query_args['order'] = 'DESC';
            break;
        case 'random':
            $query_args['orderby'] = 'rand';
            break;
        case 'featured':
        default:
            $query_args['meta_key'] = '_aps_is_featured';
            $query_args['orderby'] = 'meta_value_num date';
            $query_args['order'] = 'DESC';
            break;
    }
    
    // Allow filtering of query args
    $query_args = apply_filters('aps_filter_query_args', $query_args, [
        'category' => $category,
        'tags'     => $tags,
        'sort'     => $sort,
        'search'   => $search,
    ]);
    
    // Execute query
    $query = new \WP_Query($query_args);
    
    if (!$query->have_posts()) {
        wp_send_json_success([
            'html'     => '<p class="aps-no-products">' . esc_html__('No products found.', 'affiliate-product-showcase') . '</p>',
            'count'    => 0,
            'category' => $category,
        ]);
    }
    
    // Start output buffering
    ob_start();
    
    // Get affiliate service from container
    $container = Container::get_instance();
    $affiliate_service = $container->get(AffiliateService::class);
    
    while ($query->have_posts()) {
        $query->the_post();
        $product = $this->product_service->get_product(get_the_ID());
        
        if (!$product instanceof Product) {
            continue;
        }
        
        // Use partial template
        set_query_var('product', $product);
        set_query_var('affiliate_service', $affiliate_service);
        set_query_var('currency_symbol', '$');
        
        $partial_path = APS_PLUGIN_DIR . 'templates/partials/product-card.php';
        if (file_exists($partial_path)) {
            load_template($partial_path, false);
        }
    }
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html'     => $html,
        'count'    => $query->found_posts,
        'category' => $category,
    ]);
}
```

---

## Summary

These 5 fixes address the **critical gaps**:

| Fix | Issue | Impact |
|-----|-------|--------|
| 1 | Search not functional | **High** - Feature promised but broken |
| 2 | No type validation | **High** - Could cause fatals in production |
| 3 | No partial template | **Medium** - Code duplication risk |
| 4 | CSS no filemtime | **Low** - Cache issues during development |
| 5 | Search in AJAX | **High** - Required for Fix #1 |

**After applying these fixes: Quality = 9.0/10** ✅
