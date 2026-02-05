<?php
/**
 * Dynamic Showcase Template - Enterprise Grade v2.0
 *
 * Renders product showcase with data from WordPress database.
 * Uses strict typing, full escaping, semantic HTML, and zero N+1 queries.
 *
 * @package AffiliateProductShowcase\Templates
 * @since   2.0.0
 * @author  Your Name
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var array<int, \AffiliateProductShowcase\Models\Product> $products
 * @var \AffiliateProductShowcase\Services\AffiliateService|null $affiliate_service
 * @var array<string, mixed> $settings
 * @var array<string, array<int, \WP_Term>> $product_terms_cache Pre-fetched terms
 * @var array<int, array<string, mixed>> $term_meta_cache Pre-fetched term meta
 */
$products = $products ?? [];
$affiliate_service = $affiliate_service ?? null;
$settings = $settings ?? [];
$product_terms_cache = $product_terms_cache ?? [];
$term_meta_cache = $term_meta_cache ?? [];

// Type safety: Ensure affiliate service is valid
if ($affiliate_service !== null && !$affiliate_service instanceof \AffiliateProductShowcase\Services\AffiliateService) {
    error_log('APS: Invalid affiliate service type provided to template');
    $affiliate_service = null;
}

// Early exit if no products
if (empty($products)) : ?>
    <div class="aps-showcase-container">
        <p class="aps-no-products"><?php esc_html_e('No products found.', 'affiliate-product-showcase'); ?></p>
    </div>
    <?php
    return;
endif;

// Settings with strict validation
$per_page = filter_var($settings['per_page'] ?? 12, FILTER_VALIDATE_INT) ?: 12;
$per_page = max(1, min(100, $per_page)); // Clamp between 1-100
$show_filters = filter_var($settings['show_filters'] ?? true, FILTER_VALIDATE_BOOLEAN);
$show_sort = filter_var($settings['show_sort'] ?? true, FILTER_VALIDATE_BOOLEAN);
$current_page = filter_var($settings['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
$total_pages = filter_var($settings['total_pages'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

// Sort options with i18n
$sort_options = [
    'featured'    => __('Featured', 'affiliate-product-showcase'),
    'latest'      => __('Latest', 'affiliate-product-showcase'),
    'oldest'      => __('Oldest', 'affiliate-product-showcase'),
    'random'      => __('Random', 'affiliate-product-showcase'),
    'popularity'  => __('Popularity', 'affiliate-product-showcase'),
    'rating'      => __('Rating', 'affiliate-product-showcase'),
    'price_low'   => __('Price: Low to High', 'affiliate-product-showcase'),
    'price_high'  => __('Price: High to Low', 'affiliate-product-showcase'),
];

// Pre-fetched taxonomies (passed from controller to avoid N+1)
$categories = $settings['categories'] ?? [];
$tags = $settings['tags'] ?? [];

// Current selections
$current_category = sanitize_text_field($_GET['aps_category'] ?? 'all');
$current_sort = sanitize_text_field($_GET['aps_sort'] ?? 'featured');
if (!array_key_exists($current_sort, $sort_options)) {
    $current_sort = 'featured';
}

/**
 * Helper: Get cached terms for product
 *
 * @param int $product_id
 * @param string $taxonomy
 * @return array<int, \WP_Term>
 */
$get_cached_terms = function(int $product_id, string $taxonomy): array {
    global $product_terms_cache;
    return $product_terms_cache[$product_id][$taxonomy] ?? [];
};

/**
 * Helper: Get cached term meta
 *
 * @param int $term_id
 * @param string $key
 * @return mixed
 */
$get_cached_meta = function(int $term_id, string $key) {
    global $term_meta_cache;
    return $term_meta_cache[$term_id][$key] ?? null;
};

// Apply filters for extensibility
$container_classes = apply_filters('aps_showcase_container_classes', ['aps-showcase-container']);
$sidebar_classes = apply_filters('aps_showcase_sidebar_classes', ['aps-sidebar']);
$content_classes = apply_filters('aps_showcase_content_classes', ['aps-main-content']);
?>

<div 
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
    data-per-page="<?php echo esc_attr((string) $per_page); ?>"
    data-current-page="<?php echo esc_attr((string) $current_page); ?>"
    data-total-pages="<?php echo esc_attr((string) $total_pages); ?>"
    data-nonce="<?php echo esc_attr(wp_create_nonce('aps_filter_nonce')); ?>"
    aria-live="polite"
    aria-atomic="false"
>
    <div class="aps-main-layout">
        
        <?php if ($show_filters) : ?>
        <!-- Sidebar Filters -->
        <aside 
            class="<?php echo esc_attr(implode(' ', $sidebar_classes)); ?>" 
            role="complementary" 
            aria-label="<?php esc_attr_e('Filter Products', 'affiliate-product-showcase'); ?>"
        >
            
            <!-- Search -->
            <div class="aps-search-box">
                <label for="aps-search-input" class="screen-reader-text">
                    <?php esc_html_e('Search products', 'affiliate-product-showcase'); ?>
                </label>
                <input 
                    type="search" 
                    id="aps-search-input"
                    class="aps-search-input"
                    placeholder="<?php esc_attr_e('Search products...', 'affiliate-product-showcase'); ?>"
                    aria-label="<?php esc_attr_e('Search products', 'affiliate-product-showcase'); ?>"
                    autocomplete="off"
                    value="<?php echo esc_attr(sanitize_text_field($_GET['aps_search'] ?? '')); ?>"
                />
                <span class="aps-search-spinner" aria-hidden="true"></span>
            </div>

            <!-- Filter Header -->
            <div class="aps-filter-header">
                <span class="aps-filter-title"><?php esc_html_e('Filter Products', 'affiliate-product-showcase'); ?></span>
                <button 
                    type="button" 
                    class="aps-clear-all" 
                    aria-label="<?php esc_attr_e('Clear all filters', 'affiliate-product-showcase'); ?>"
                >
                    <?php esc_html_e('Clear All', 'affiliate-product-showcase'); ?>
                </button>
            </div>

            <!-- Categories -->
            <?php if (!empty($categories) && is_array($categories)) : ?>
                <fieldset class="aps-filter-group">
                    <legend class="aps-section-label"><?php esc_html_e('Category', 'affiliate-product-showcase'); ?></legend>
                    <div 
                        class="aps-category-tabs" 
                        role="tablist" 
                        aria-label="<?php esc_attr_e('Categories', 'affiliate-product-showcase'); ?>"
                    >
                        <button 
                            type="button"
                            class="aps-tab <?php echo $current_category === 'all' ? 'active' : ''; ?>" 
                            role="tab"
                            aria-selected="<?php echo $current_category === 'all' ? 'true' : 'false'; ?>"
                            data-category="all"
                            id="aps-tab-all"
                            aria-controls="aps-panel-all"
                        >
                            <?php esc_html_e('All Products', 'affiliate-product-showcase'); ?>
                        </button>
                        <?php foreach ($categories as $category) : 
                            if (!$category instanceof \WP_Term) continue;
                            $is_active = $current_category === $category->slug;
                        ?>
                            <button 
                                type="button"
                                class="aps-tab <?php echo $is_active ? 'active' : ''; ?>" 
                                role="tab"
                                aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                data-category="<?php echo esc_attr($category->slug); ?>"
                                id="aps-tab-<?php echo esc_attr($category->slug); ?>"
                                aria-controls="aps-panel-<?php echo esc_attr($category->slug); ?>"
                            >
                                <?php echo esc_html($category->name); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endif; ?>

            <!-- Tags -->
            <?php if (!empty($tags) && is_array($tags)) : ?>
                <fieldset class="aps-filter-group">
                    <legend class="aps-section-label"><?php esc_html_e('Tags', 'affiliate-product-showcase'); ?></legend>
                    <div 
                        class="aps-tags-grid" 
                        role="group" 
                        aria-label="<?php esc_attr_e('Tags', 'affiliate-product-showcase'); ?>"
                    >
                        <?php foreach ($tags as $tag) : 
                            if (!$tag instanceof \WP_Term) continue;
                            $tag_icon = $get_cached_meta($tag->term_id, 'icon') ?: '🏷️';
                            $is_selected = in_array($tag->slug, $settings['selected_tags'] ?? [], true);
                        ?>
                            <button 
                                type="button"
                                class="aps-tag <?php echo $is_selected ? 'active' : ''; ?>" 
                                data-tag="<?php echo esc_attr($tag->slug); ?>"
                                aria-pressed="<?php echo $is_selected ? 'true' : 'false'; ?>"
                            >
                                <span class="aps-tag-icon" aria-hidden="true"><?php echo esc_html($tag_icon); ?></span>
                                <span class="aps-tag-name"><?php echo esc_html($tag->name); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endif; ?>
        </aside>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="<?php echo esc_attr(implode(' ', $content_classes)); ?>">
            
            <!-- Sort Dropdown -->
            <?php if ($show_sort) : ?>
                <div class="aps-content-header">
                    <div class="aps-results-info" aria-live="polite">
                        <span class="aps-results-count">
                            <?php 
                            printf(
                                esc_html(_n('%s product', '%s products', count($products), 'affiliate-product-showcase')),
                                number_format_i18n(count($products))
                            ); 
                            ?>
                        </span>
                    </div>
                    <div class="aps-sort-dropdown">
                        <label for="aps-sort-select" class="aps-sort-label">
                            <?php esc_html_e('Sort by', 'affiliate-product-showcase'); ?>
                        </label>
                        <select 
                            id="aps-sort-select" 
                            class="aps-sort-select" 
                            aria-label="<?php esc_attr_e('Sort products by', 'affiliate-product-showcase'); ?>"
                        >
                            <?php foreach ($sort_options as $value => $label) : ?>
                                <option 
                                    value="<?php echo esc_attr($value); ?>" 
                                    <?php selected($value, $current_sort); ?>
                                >
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Products Grid -->
            <div 
                class="aps-cards-grid" 
                role="list" 
                aria-label="<?php esc_attr_e('Products', 'affiliate-product-showcase'); ?>"
                data-loading-text="<?php esc_attr_e('Loading products...', 'affiliate-product-showcase'); ?>"
            >
                <?php 
                foreach ($products as $index => $product) : 
                    // Strict type checking
                    if (!$product instanceof \AffiliateProductShowcase\Models\Product) {
                        error_log('APS: Invalid product type at index ' . $index);
                        continue;
                    }

                    // Safe affiliate URL generation
                    try {
                        $affiliate_url = $affiliate_service ? $affiliate_service->get_tracking_url($product->id) : '#';
                    } catch (\Exception $e) {
                        error_log('APS: Affiliate URL generation failed for product ' . $product->id);
                        $affiliate_url = '#';
                    }

                    // Calculate discount safely
                    $discount_pct = 0;
                    if (is_numeric($product->original_price) && is_numeric($product->price) && $product->original_price > 0) {
                        $discount_pct = (int) round((($product->original_price - $product->price) / $product->original_price) * 100);
                    }

                    // Get cached terms (zero queries)
                    $product_categories = $get_cached_terms($product->id, 'aps_category');
                    $product_tags = $get_cached_terms($product->id, 'aps_tag');
                    
                    $product_category_slugs = wp_list_pluck($product_categories, 'slug');
                    $product_tag_slugs = wp_list_pluck($product_tags, 'slug');

                    // Validate features array - Product model doesn't have features, so we'll use an empty array
                    $features = [];
                    
                    // Apply filters for card customization
                    $card_classes = apply_filters('aps_product_card_classes', ['aps-tool-card'], $product);
                    $card_attrs = apply_filters('aps_product_card_attributes', [
                        'data-id' => (string) $product->id,
                        'data-category' => wp_json_encode($product_category_slugs),
                        'data-tags' => wp_json_encode($product_tag_slugs),
                        'data-rating' => is_numeric($product->rating) ? (string) $product->rating : '0',
                        'data-price' => is_numeric($product->price) ? (string) $product->price : '0',
                    ], $product);
                ?>
                    <article 
                        class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" 
                        role="listitem"
                        <?php 
                        foreach ($card_attrs as $key => $value) {
                            printf('%s="%s" ', esc_attr($key), esc_attr($value));
                        }
                        ?>
                    >
                        <?php if (!empty($product->badge)) : ?>
                            <div class="aps-featured-badge">
                                <span aria-hidden="true">★</span>
                                <?php echo esc_html($product->badge); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Product Image -->
                        <div class="aps-card-image aps-cyan">
                            <?php if (!empty($product->image_url)) : ?>
                                <img 
                                    src="<?php echo esc_url($product->image_url); ?>" 
                                    alt="<?php echo esc_attr($product->title); ?>"
                                    loading="<?php echo $index < 6 ? 'eager' : 'lazy'; ?>"
                                    class="aps-product-image"
                                    width="400"
                                    height="300"
                                />
                            <?php else : ?>
                                <div class="aps-image-placeholder">
                                    <span aria-hidden="true">🖼️</span>
                                    <span class="screen-reader-text">
                                        <?php esc_html_e('Product image placeholder', 'affiliate-product-showcase'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="aps-card-body">
                            <!-- Header: Logo + Name + Price -->
                            <div class="aps-card-header-row">
                                <h3 class="aps-tool-name">
                                    <span class="aps-tool-title-text"><?php echo esc_html($product->title); ?></span>
                                </h3>
                                
                                <div class="aps-price-block">
                                    <?php if ($product->original_price && $product->original_price > $product->price) : ?>
                                        <span class="aps-original-price" aria-label="<?php esc_attr_e('Original price', 'affiliate-product-showcase'); ?>">
                                            <?php echo esc_html(number_format_i18n((float) $product->original_price, 2)); ?>/mo
                                        </span>
                                    <?php endif; ?>
                                    <div class="aps-current-price">
                                        <?php echo esc_html(number_format_i18n((float) $product->price, 2)); ?>
                                        <span class="aps-price-period">/mo</span>
                                    </div>
                                    <?php if ($discount_pct > 0) : ?>
                                        <span class="aps-discount-badge" aria-label="<?php printf(esc_attr__('%d percent discount', 'affiliate-product-showcase'), $discount_pct); ?>">
                                            <?php echo esc_html($discount_pct); ?>% OFF
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <?php if (!empty($product->short_description)) : ?>
                                <p class="aps-tool-description">
                                    <?php 
                                    echo wp_kses_post(
                                        wp_trim_words(
                                            wp_kses($product->short_description, wp_kses_allowed_html('post')), 
                                            20, 
                                            '...'
                                        )
                                    ); 
                                    ?>
                                </p>
                            <?php endif; ?>

                            <!-- Tags -->
                            <?php if (!empty($product_tags)) : ?>
                                <div class="aps-tags-row">
                                    <?php 
                                    $display_tags = array_slice($product_tags, 0, 5);
                                    foreach ($display_tags as $tag) : 
                                        $tag_icon = $get_cached_meta($tag->term_id, 'icon') ?: '🏷️';
                                    ?>
                                        <span class="aps-tag-badge">
                                            <span aria-hidden="true"><?php echo esc_html($tag_icon); ?></span>
                                            <?php echo esc_html($tag->name); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($product_tags) > 5) : ?>
                                        <span class="aps-tag-more">
                                            <?php 
                                            printf(
                                                esc_html__('+%d more', 'affiliate-product-showcase'),
                                                count($product_tags) - 5
                                            ); 
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Footer: Rating + CTA -->
                            <div class="aps-card-footer">
                                <div class="aps-stats-row">
                                    <div class="aps-stats-left">
                                        <?php if (is_numeric($product->rating) && $product->rating > 0) : ?>
                                            <div 
                                                class="aps-rating-stars" 
                                                aria-label="<?php printf(esc_attr__('Rating: %s out of 5', 'affiliate-product-showcase'), number_format_i18n((float) $product->rating, 1)); ?>"
                                            >
                                                <?php 
                                                $rating = (float) $product->rating;
                                                for ($i = 1; $i <= 5; $i++) : 
                                                    $star_class = $i > $rating ? 'aps-empty' : '';
                                                    $star_label = $i <= $rating ? __('Filled star', 'affiliate-product-showcase') : __('Empty star', 'affiliate-product-showcase');
                                                ?>
                                                    <span class="aps-star <?php echo esc_attr($star_class); ?>" aria-label="<?php echo esc_attr($star_label); ?>">★</span>
                                                <?php endfor; ?>
                                                <span class="aps-rating-text"><?php echo esc_html(number_format_i18n($rating, 1)); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- CTA Button -->
                                <a 
                                    href="<?php echo esc_url($affiliate_url); ?>"
                                    class="aps-action-button"
                                    target="_blank"
                                    rel="nofollow sponsored noopener"
                                    aria-label="<?php printf(esc_attr__('Claim discount for %s - opens in new tab', 'affiliate-product-showcase'), esc_attr($product->title)); ?>"
                                >
                                    <?php esc_html_e('Claim Discount', 'affiliate-product-showcase'); ?>
                                    <span aria-hidden="true"></span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1) : ?>
                <nav class="aps-pagination" aria-label="<?php esc_attr_e('Product pagination', 'affiliate-product-showcase'); ?>">
                    <div class="aps-pagination-info">
                        <?php 
                        printf(
                            esc_html__('Page %1$s of %2$s', 'affiliate-product-showcase'),
                            '<span class="aps-current-page">' . esc_html((string) $current_page) . '</span>',
                            '<span class="aps-total-pages">' . esc_html((string) $total_pages) . '</span>'
                        ); 
                        ?>
                    </div>
                    <div class="aps-pagination-controls" role="navigation">
                        <?php if ($current_page > 1) : ?>
                            <button 
                                type="button" 
                                class="aps-pagination-prev" 
                                data-page="<?php echo esc_attr((string) ($current_page - 1)); ?>"
                                aria-label="<?php esc_attr_e('Previous page', 'affiliate-product-showcase'); ?>"
                            >
                                ← <?php esc_html_e('Previous', 'affiliate-product-showcase'); ?>
                            </button>
                        <?php else : ?>
                            <span class="aps-pagination-prev disabled" aria-disabled="true">
                                ← <?php esc_html_e('Previous', 'affiliate-product-showcase'); ?>
                            </span>
                        <?php endif; ?>

                        <div class="aps-pagination-numbers" role="tablist" aria-label="<?php esc_attr_e('Page numbers', 'affiliate-product-showcase'); ?>">
                            <?php
                            // Smart pagination (show max 5 pages)
                            $start = max(1, $current_page - 2);
                            $end = min($total_pages, $start + 4);
                            $start = max(1, $end - 4);
                            
                            for ($i = $start; $i <= $end; $i++) : 
                                $is_current = $i === $current_page;
                            ?>
                                <button 
                                    type="button"
                                    class="aps-pagination-number <?php echo $is_current ? 'active' : ''; ?>"
                                    data-page="<?php echo esc_attr((string) $i); ?>"
                                    role="tab"
                                    aria-selected="<?php echo $is_current ? 'true' : 'false'; ?>"
                                    aria-label="<?php printf(esc_attr__('Page %s', 'affiliate-product-showcase'), (string) $i); ?>"
                                >
                                    <?php echo esc_html((string) $i); ?>
                                </button>
                            <?php endfor; ?>
                        </div>

                        <?php if ($current_page < $total_pages) : ?>
                            <button 
                                type="button" 
                                class="aps-pagination-next" 
                                data-page="<?php echo esc_attr((string) ($current_page + 1)); ?>"
                                aria-label="<?php esc_attr_e('Next page', 'affiliate-product-showcase'); ?>"
                            >
                                <?php esc_html_e('Next', 'affiliate-product-showcase'); ?> →
                            </button>
                        <?php else : ?>
                            <span class="aps-pagination-next disabled" aria-disabled="true">
                                <?php esc_html_e('Next', 'affiliate-product-showcase'); ?> →
                            </span>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>
