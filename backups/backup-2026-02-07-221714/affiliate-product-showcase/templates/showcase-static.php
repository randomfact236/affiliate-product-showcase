<?php
/**
 * Static Showcase Template
 * 
 * Exact copy of design from plan/frontend-design.md
 * NO database connection - static HTML only
 * 
 * @package AffiliateProductShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="aps-showcase-container">
	<div class="aps-main-layout">
		<!-- Sidebar -->
		<aside class="aps-sidebar">
			<div class="aps-search-box">
				<input type="text" placeholder="Search tools...">
			</div>

			<div class="aps-filter-header">
				<span class="aps-filter-title">Filter Tools</span>
				<a href="#" class="aps-clear-all">Clear All</a>
			</div>

			<span class="aps-section-label">Category</span>
			<div class="aps-category-tabs">
				<div class="aps-tab active">All Tools</div>
				<div class="aps-tab">Hosting</div>
				<div class="aps-tab">AI Tools</div>
				<div class="aps-tab">SEO Tools</div>
				<div class="aps-tab">Marketing Tools</div>
			</div>

			<span class="aps-section-label">Tags</span>
			<div class="aps-tags-grid">
				<div class="aps-tag active">⭐ Featured</div>
				<div class="aps-tag">✍️ Writing</div>
				<div class="aps-tag">🎥 Video</div>
				<div class="aps-tag">🎤 Audio</div>
				<div class="aps-tag">🎨 Design</div>
				<div class="aps-tag">🆓 Free Trial</div>
				<div class="aps-tag">💳 No CC</div>
				<div class="aps-tag">🎁 Free Forever</div>
				<div class="aps-tag active">✅ Verified</div>
			</div>
		</aside>

		<!-- Main Content -->
		<main class="aps-main-content">
			<div class="aps-content-header">
				<div class="aps-sort-dropdown">
					<label class="aps-sort-label" for="aps-sort-select">Sort by</label>
					<select id="aps-sort-select" class="aps-sort-select">
						<option value="featured" selected>Featured</option>
						<option value="all">All</option>
						<option value="latest">Latest</option>
						<option value="oldest">Oldest</option>
						<option value="random">Random</option>
						<option value="popularity">Popularity</option>
						<option value="rating">Rating</option>
					</select>
				</div>
			</div>

			<div class="aps-cards-grid">
				<!-- SEMrush Pro Card -->
				<article class="aps-tool-card">
					<div class="aps-featured-badge">Featured</div>
					<div class="aps-card-image aps-cyan">
						<span>Product Dashboard Preview</span>
					</div>
					<div class="aps-card-body">
						<div class="aps-card-header-row">
							<h3 class="aps-tool-name">
								<span class="aps-tool-icon aps-orange">📤</span>
								SEMrush Pro
							</h3>
							<div class="aps-price-block">
								<span class="aps-original-price">$229.95/mo</span>
								<div class="aps-current-price">
									$119<span class="aps-price-period">/mo</span>
								</div>
								<span class="aps-discount-badge">48% OFF</span>
							</div>
						</div>
						
						<p class="aps-tool-description">
							The most accurate difficulty score in the industry. Find low-competition keywords and spy on competitors' traffic sources easily.
						</p>

						<div class="aps-tags-row">
							<span class="aps-tag">⭐ Featured</span>
							<span class="aps-tag">🔍 SEO</span>
							<span class="aps-tag">✍️ Writing</span>
							<span class="aps-tag">📊 Analytics</span>
							<span class="aps-tag">✅ Verified</span>
						</div>

						<div class="aps-features-list">
							<div class="aps-feature-item">Keyword Research</div>
							<div class="aps-feature-item">Competitor Analysis</div>
							<div class="aps-feature-item">Site Audit</div>
							<div class="aps-feature-item aps-dimmed">Traffic Analytics</div>
						</div>

						<div class="aps-card-footer">
							<div class="aps-stats-row">
								<div class="aps-stats-left">
									<div class="aps-rating-stars">
										<span class="aps-star">★</span>
										<span class="aps-star">★</span>
										<span class="aps-star">★</span>
										<span class="aps-star">★</span>
										<span class="aps-star">★</span>
										<span class="aps-rating-text">5</span>
									</div>
									<span class="aps-reviews-count">3,421 reviews</span>
								</div>
								<div class="aps-users-pill aps-red">10M+ users</div>
							</div>

							<button class="aps-action-button">Claim Discount</button>
							<div class="aps-trial-text">14-day free trial available</div>
						</div>
					</div>
				</article>
			</div>
		</main>
	</div>
</div>

<script>
// Simple tab interaction (no AJAX - static only)
document.addEventListener('DOMContentLoaded', function() {
	// Tab click
	document.querySelectorAll('.aps-tab').forEach(function(tab) {
		tab.addEventListener('click', function() {
			document.querySelectorAll('.aps-tab').forEach(function(t) {
				t.classList.remove('active');
			});
			this.classList.add('active');
		});
	});

	// Tag click
	document.querySelectorAll('.aps-tag').forEach(function(tag) {
		tag.addEventListener('click', function() {
			this.classList.toggle('active');
		});
	});

	// Clear all
	document.querySelector('.aps-clear-all').addEventListener('click', function(e) {
		e.preventDefault();
		document.querySelectorAll('.aps-tab').forEach(function(t) {
			t.classList.remove('active');
		});
		document.querySelectorAll('.aps-tag').forEach(function(t) {
			t.classList.remove('active');
		});
		document.querySelector('.aps-tab').classList.add('active');
	});
});
</script>
