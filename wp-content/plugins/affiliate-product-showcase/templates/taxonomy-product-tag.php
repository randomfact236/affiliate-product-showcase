<?php
/**
 * Product Tag Archive Template
 *
 * Displays archive page for product tags (taxonomy: product-tag).
 * Shows all products associated with a specific tag.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header('affiliate-product-showcase');

$tag = get_queried_object();
$tag_name = $tag->name ?? '';
$tag_description = $tag->description ?? '';
$tag_slug = $tag->slug ?? '';
$tag_color = get_term_meta($tag->term_id, 'tag_color', true);
?>

<div class="aps-container aps-tag-archive">
	<header class="aps-tag-archive-header">
		<div class="aps-tag-archive-title-wrapper">
			<h1 class="aps-tag-archive-title">
				<?php if ($tag_color): ?>
					<span class="aps-tag-name-badge" style="background-color: <?php echo esc_attr($tag_color); ?>;">
						<?php echo esc_html($tag_name); ?>
					</span>
				<?php else: ?>
					<span class="aps-tag-name-badge">
						<?php echo esc_html($tag_name); ?>
					</span>
				<?php endif; ?>
			</h1>
			
			<?php if ($tag_description): ?>
				<p class="aps-tag-archive-description"><?php echo esc_html($tag_description); ?></p>
			<?php endif; ?>
		</div>
		
		<div class="aps-tag-archive-meta">
			<?php
			$tag_count = $tag->count ?? 0;
			printf(
				/* translators: %s: number of products */
				_n('%s product', '%s products', $tag_count, 'affiliate-product-showcase'),
				number_format_i18n($tag_count)
			);
			?>
		</div>
	</header>

	<?php if (have_posts()): ?>
		<div class="aps-products-grid" role="list" aria-label="<?php echo esc_attr(sprintf(__('Products tagged with "%s"', 'affiliate-product-showcase'), $tag_name)); ?>">
			<?php
			while (have_posts()):
				the_post();
				?>
				<div class="aps-product-card" role="listitem">
					<?php
					$product_id = get_the_ID();
					$title = get_the_title();
					$excerpt = get_the_excerpt();
					$thumbnail = get_the_post_thumbnail($product_id, 'medium', ['loading' => 'lazy', 'alt' => esc_attr($title)]);
					$price = get_post_meta($product_id, 'product_price', true);
					$sale_price = get_post_meta($product_id, 'product_sale_price', true);
					$rating = get_post_meta($product_id, 'product_rating', true);
					$affiliate_url = get_post_meta($product_id, 'product_affiliate_url', true);
					?>

					<?php if ($thumbnail): ?>
						<div class="aps-product-image">
							<a href="<?php echo esc_url($affiliate_url); ?>" rel="nofollow sponsored" target="_blank" aria-label="<?php echo esc_attr(sprintf(__('View %s', 'affiliate-product-showcase'), $title)); ?>">
								<?php echo $thumbnail; ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="aps-product-content">
						<h2 class="aps-product-title">
							<a href="<?php echo esc_url($affiliate_url); ?>" rel="nofollow sponsored" target="_blank" aria-label="<?php echo esc_attr(sprintf(__('View %s', 'affiliate-product-showcase'), $title)); ?>">
								<?php echo esc_html($title); ?>
							</a>
						</h2>

						<?php if ($excerpt): ?>
							<p class="aps-product-excerpt"><?php echo esc_html($excerpt); ?></p>
						<?php endif; ?>

						<?php if ($rating): ?>
							<div class="aps-product-rating">
								<span class="aps-stars" style="--rating: <?php echo esc_attr($rating); ?>;" aria-label="<?php echo esc_attr(sprintf(__('%d out of 5 stars', 'affiliate-product-showcase'), $rating)); ?>">★★★★★</span>
								<span class="aps-rating-value"><?php echo number_format(floatval($rating), 1); ?></span>
							</div>
						<?php endif; ?>

						<div class="aps-product-price">
							<?php
							if ($sale_price && floatval($sale_price) > 0):
								$discount = round(((floatval($price) - floatval($sale_price)) / floatval($price) * 100);
								?>
								<span class="aps-price-original"><?php echo number_format(floatval($price), 2); ?></span>
								<span class="aps-price-sale"><?php echo number_format(floatval($sale_price), 2); ?></span>
								<span class="aps-discount-badge">-<?php echo absint($discount); ?>%</span>
							<?php else: ?>
								<span class="aps-price-current"><?php echo number_format(floatval($price), 2); ?></span>
							<?php endif; ?>
						</div>

						<div class="aps-product-cta">
							<a href="<?php echo esc_url($affiliate_url); ?>" class="aps-cta-button" rel="nofollow sponsored" target="_blank" aria-label="<?php echo esc_attr(__('View Deal', 'affiliate-product-showcase')); ?>">
								<?php esc_html_e('View Deal', 'affiliate-product-showcase'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
		</div>

		<?php
		// Pagination
		$pagination = paginate_links([
			'type'      => 'list',
			'prev_text' => __('&laquo; Previous', 'affiliate-product-showcase'),
			'next_text' => __('Next &raquo;', 'affiliate-product-showcase'),
		]);

		if ($pagination):
			?>
			<nav class="aps-pagination" aria-label="<?php esc_attr_e('Pagination', 'affiliate-product-showcase'); ?>">
				<?php echo $pagination; ?>
			</nav>
		<?php endif; ?>

	<?php else: ?>
		<div class="aps-no-products">
			<h2><?php esc_html_e('No products found', 'affiliate-product-showcase'); ?></h2>
			<p><?php printf(esc_html__('There are currently no products tagged with "%s".', 'affiliate-product-showcase'), esc_html($tag_name)); ?></p>
			<a href="<?php echo esc_url(home_url()); ?>" class="aps-button aps-button-primary">
				<?php esc_html_e('Back to Home', 'affiliate-product-showcase'); ?>
			</a>
		</div>
	<?php endif; ?>

	<?php
	// Display related tags
	$related_tags = get_terms([
		'taxonomy'   => 'aps_tag',
		'exclude'     => $tag->term_id,
		'number'      => 10,
		'orderby'     => 'count',
		'order'       => 'DESC',
		'hide_empty' => true,
	]);

	if (!empty($related_tags) && !is_wp_error($related_tags)):
		?>
		<aside class="aps-related-tags" aria-label="<?php esc_attr_e('Related tags', 'affiliate-product-showcase'); ?>">
			<h3><?php esc_html_e('Related Tags', 'affiliate-product-showcase'); ?></h3>
			<div class="aps-related-tags-cloud">
				<?php foreach ($related_tags as $related_tag): ?>
					<a href="<?php echo esc_url(get_term_link($related_tag)); ?>" 
					   class="aps-related-tag-link"
					   rel="tag"
					   aria-label="<?php echo esc_attr(sprintf(__('View products tagged with %s', 'affiliate-product-showcase'), $related_tag->name)); ?>">
						<?php echo esc_html($related_tag->name); ?>
						<span class="aps-tag-count"><?php echo absint($related_tag->count); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</aside>
	<?php endif; ?>
</div>

<?php
get_footer('affiliate-product-showcase');
