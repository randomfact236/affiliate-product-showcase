<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<\AffiliateProductShowcase\Models\Product> $products */
/** @var \AffiliateProductShowcase\Services\AffiliateService $affiliate_service */
/** @var array $settings Optional settings */
?>
<div class="aps-showcase-wrapper">
	<div class="aps-container">
		<div class="aps-main-layout">
			<!-- Sidebar -->
			<aside class="aps-sidebar">
				<div class="aps-search-box">
					<input type="text" placeholder="Search tools..." aria-label="<?php esc_attr_e( 'Search tools', 'affiliate-product-showcase' ); ?>" />
				</div>

				<div class="aps-filter-header">
					<span class="aps-filter-title">Filter Tools</span>
					<a href="#" class="aps-clear-all">Clear All</a>
				</div>

				<div class="aps-filter-section">
					<span class="aps-section-label">Category</span>
					<div class="aps-category-tabs">
						<div class="tab active">All Tools</div>
						<div class="tab">Hosting</div>
						<div class="tab">AI Tools</div>
						<div class="tab">SEO Tools</div>
						<div class="tab">Marketing Tools</div>
					</div>
				</div>

				<div class="aps-filter-section">
					<span class="aps-section-label">Tags</span>
					<div class="aps-tags-grid">
						<div class="tag active">⭐ Featured</div>
						<div class="tag">✍️ Writing</div>
						<div class="tag">🎥 Video</div>
						<div class="tag">🎤 Audio</div>
						<div class="tag">🎨 Design</div>
						<div class="tag">🆓 Free Trial</div>
						<div class="tag">💳 No CC</div>
						<div class="tag">🎁 Free Forever</div>
						<div class="tag active">✅ Verified</div>
					</div>
				</div>
			</aside>

			<!-- Main Content -->
			<main class="aps-main-content">
				<div class="aps-content-header">
					<div class="aps-sort-dropdown">
						<button class="aps-sort-btn">
							<span class="aps-sort-label">Sort by</span>
							<span>Featured</span>
							<span class="aps-sort-arrow">▼</span>
						</button>
					</div>
				</div>

				<?php echo aps_view( 'src/Public/partials/product-grid.php', [ 
					'products' => $products,
					'affiliate_service' => $affiliate_service
				] ); ?>
			</main>
		</div>
	</div>
</div>
