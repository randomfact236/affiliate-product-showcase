<?php
/**
 * Product Showcase Template - Pure Tailwind
 * 
 * Full layout with sidebar filters and product grid.
 * 
 * @package AffiliateProductShowcase
 * @since 1.0.0
 * 
 * @var array $products
 * @var AffiliateService $affiliate_service
 * @var array $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$disclosure_text = $settings['disclosure_text'] ?? __( 'We may earn a commission when you purchase through our links.', 'affiliate-product-showcase' );
?>

<!-- Affiliate Product Showcase -->
<div class="aps-root">
	<div class="aps-showcase">
		<!-- Disclosure Notice -->
		<?php if ( $settings['enable_disclosure'] ?? true ) : ?>
			<div class="aps-disclosure aps-disclosure--top" role="note">
				<?php echo wp_kses_post( $disclosure_text ); ?>
			</div>
		<?php endif; ?>

		<div class="aps-showcase__layout">
			
			<!-- Sidebar Filters -->
			<aside class="aps-showcase__sidebar">
				
				<!-- Search -->
				<div class="aps-search">
					<svg class="aps-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="8"/>
						<path d="m21 21-4.35-4.35"/>
					</svg>
					<input 
						type="text" 
						placeholder="<?php esc_attr_e( 'Search tools...', 'affiliate-product-showcase' ); ?>"
						class="aps-search__input"
					>
				</div>

				<!-- Filter Header -->
				<div class="aps-flex aps-justify-between aps-items-center aps-pb-4 aps-border-b aps-border-gray-200">
					<span class="aps-text-sm aps-font-semibold aps-text-gray-700 aps-uppercase aps-tracking-wider">
						<?php esc_html_e( 'Filter Tools', 'affiliate-product-showcase' ); ?>
					</span>
					<button id="clearAll" class="aps-text-sm aps-font-medium aps-text-blue-500 hover:aps-text-blue-600 aps-transition-colors">
						<?php esc_html_e( 'Clear All', 'affiliate-product-showcase' ); ?>
					</button>
				</div>

				<!-- Categories -->
				<div class="aps-filter-group">
					<span class="aps-filter-label"><?php esc_html_e( 'Category', 'affiliate-product-showcase' ); ?></span>
					<div class="aps-filter-buttons">
						<button class="aps-filter-btn aps-filter-btn--active tab-active" data-category="all">
							<?php esc_html_e( 'All Tools', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-category="hosting">
							<?php esc_html_e( 'Hosting', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-category="ai">
							<?php esc_html_e( 'AI Tools', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-category="seo">
							<?php esc_html_e( 'SEO Tools', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-category="marketing">
							<?php esc_html_e( 'Marketing', 'affiliate-product-showcase' ); ?>
						</button>
					</div>
				</div>

				<!-- Tags -->
				<div class="aps-filter-group">
					<span class="aps-filter-label"><?php esc_html_e( 'Tags', 'affiliate-product-showcase' ); ?></span>
					<div class="aps-filter-buttons">
						<button class="aps-filter-btn aps-filter-btn--active tag-active" data-tag="featured">
							<span>⭐</span> <?php esc_html_e( 'Featured', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-tag="writing">
							<span>✍️</span> <?php esc_html_e( 'Writing', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-tag="video">
							<span>🎥</span> <?php esc_html_e( 'Video', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-tag="design">
							<span>🎨</span> <?php esc_html_e( 'Design', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn" data-tag="freetrial">
							<span>🆓</span> <?php esc_html_e( 'Free Trial', 'affiliate-product-showcase' ); ?>
						</button>
						<button class="aps-filter-btn aps-filter-btn--active tag-active" data-tag="verified">
							<span>✅</span> <?php esc_html_e( 'Verified', 'affiliate-product-showcase' ); ?>
						</button>
					</div>
				</div>

			</aside>

			<!-- Main Content -->
			<main class="aps-showcase__content">
				
				<!-- Header -->
				<div class="aps-flex aps-justify-end aps-mb-6">
					<div class="aps-sort">
						<button class="aps-sort__btn">
							<div class="aps-flex aps-items-center aps-gap-2">
								<span class="aps-text-gray-400 aps-font-normal"><?php esc_html_e( 'Sort by', 'affiliate-product-showcase' ); ?></span>
								<span class="sort-value"><?php esc_html_e( 'Featured', 'affiliate-product-showcase' ); ?></span>
							</div>
							<svg class="aps-w-4 aps-h-4 aps-text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<polyline points="6 9 12 15 18 9"/>
							</svg>
						</button>
					</div>
				</div>

				<!-- Products Grid -->
				<?php if ( ! empty( $products ) ) : ?>
					<div class="aps-grid-products">
						<?php foreach ( $products as $product ) : ?>
							<?php include __DIR__ . '/product-card.php'; ?>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="aps-text-gray-600 aps-text-center aps-py-12">
						<?php esc_html_e( 'No products found.', 'affiliate-product-showcase' ); ?>
					</p>
				<?php endif; ?>

			</main>

		</div>
	</div>
</div>
<!-- End Affiliate Product Showcase -->
