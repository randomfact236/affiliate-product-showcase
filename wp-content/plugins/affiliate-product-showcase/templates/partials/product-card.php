<?php
/**
 * Product Card Partial - For AJAX rendering
 * 
 * @package AffiliateProductShowcase\Templates\Partials
 * @var Product $product
 * @var array $product_categories
 * @var array $product_tags
 * @var \AffiliateProductShowcase\Services\AffiliateService|null $affiliate_service
 */

if (!isset($product) || !$product instanceof \AffiliateProductShowcase\Models\Product) {
    return;
}

// Calculate discount
$discount_pct = 0;
if ($product->original_price && $product->original_price > 0 && $product->price < $product->original_price) {
    $discount_pct = (int) round((($product->original_price - $product->price) / $product->original_price) * 100);
}

// Safe URL
try {
    $affiliate_url = $affiliate_service ? $affiliate_service->get_tracking_url($product->id) : '#';
} catch (\Exception $e) {
    $affiliate_url = '#';
}
?>

<article class="aps-tool-card" 
         data-id="<?php echo esc_attr($product->id); ?>"
         data-category="<?php echo esc_attr(wp_json_encode(wp_list_pluck($product_categories, 'slug'))); ?>"
         data-tags="<?php echo esc_attr(wp_json_encode(wp_list_pluck($product_tags, 'slug'))); ?>"
         data-rating="<?php echo esc_attr($product->rating ?? '0'); ?>"
         data-price="<?php echo esc_attr($product->price); ?>">
    
    <?php if ($product->badge) : ?>
        <div class="aps-featured-badge">
            <span aria-hidden="true">★</span>
            <?php echo esc_html($product->badge); ?>
        </div>
    <?php endif; ?>

    <div class="aps-card-image aps-cyan">
        <?php if ($product->image_url) : ?>
            <img src="<?php echo esc_url($product->image_url); ?>" 
                 alt="<?php echo esc_attr($product->title); ?>" 
                 loading="lazy" />
        <?php else : ?>
            <div class="aps-image-placeholder">
                <span aria-hidden="true">🖼️</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="aps-card-body">
        <div class="aps-card-header-row">
            <h3 class="aps-tool-name">
                <?php echo esc_html($product->title); ?>
            </h3>
            
            <div class="aps-price-block">
                <?php if ($product->original_price && $product->original_price > $product->price) : ?>
                    <span class="aps-original-price">
                        <?php echo esc_html(number_format_i18n($product->original_price, 2)); ?>
                    </span>
                <?php endif; ?>
                <div class="aps-current-price">
                    <?php echo esc_html(number_format_i18n($product->price, 2)); ?>
                    <span class="aps-price-period">/mo</span>
                </div>
                <?php if ($discount_pct > 0) : ?>
                    <span class="aps-discount-badge"><?php echo esc_html($discount_pct); ?>% OFF</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($product->short_description) : ?>
            <p class="aps-tool-description">
                <?php echo wp_kses_post(wp_trim_words($product->short_description, 20)); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($product_tags)) : ?>
            <div class="aps-tags-row">
                <?php foreach (array_slice($product_tags, 0, 5) as $tag) : ?>
                    <span class="aps-tag-badge"><?php echo esc_html($tag->name); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="aps-card-footer">
            <?php if (is_numeric($product->rating) && $product->rating > 0) : ?>
                <div class="aps-rating-stars">
                    <?php 
                    $rating = (float) $product->rating;
                    for ($i = 1; $i <= 5; $i++) : 
                        $star_class = $i > $rating ? 'aps-empty' : '';
                    ?>
                        <span class="aps-star <?php echo esc_attr($star_class); ?>">★</span>
                    <?php endfor; ?>
                    <span class="aps-rating-text"><?php echo esc_html(number_format_i18n($rating, 1)); ?></span>
                </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($affiliate_url); ?>" 
               class="aps-action-button" 
               target="_blank" 
               rel="nofollow sponsored noopener">
                <?php esc_html_e('Claim Discount', 'affiliate-product-showcase'); ?>
            </a>
        </div>
    </div>
</article>
