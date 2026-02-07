<?php
/**
 * Product Showcase Template
 * 
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="aps-showcase-wrapper">
	<div class="container">
		<div class="main-layout">
			<aside class="sidebar">
				<div class="search-box">
					<input type="text" placeholder="Search tools...">
				</div>

				<div class="filter-header">
					<span class="filter-title">Filter Tools</span>
					<a href="#" class="clear-all">Clear All</a>
				</div>

				<div class="filter-section">
					<span class="section-label">Category</span>
					<div class="category-tabs">
						<div class="tab active">All Tools</div>
						<div class="tab">Hosting</div>
						<div class="tab">AI Tools</div>
						<div class="tab">SEO Tools</div>
						<div class="tab">Marketing Tools</div>
					</div>
				</div>

				<div class="filter-section">
					<span class="section-label">Tags</span>
					<div class="tags-grid">
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

			<main class="main-content">
				<div class="content-header">
					<div class="sort-dropdown">
						<button class="sort-btn" type="button">
							<span class="sort-label">Sort by</span>
							<span>Featured</span>
							<span>▼</span>
						</button>
					</div>
				</div>

				<div class="cards-grid">
					<?php
					$cards_template = __DIR__ . '/product-card.php';
					if ( file_exists( $cards_template ) ) {
						include $cards_template;
					} else {
						echo '<p>Cards template not found.</p>';
					}
					?>
				</div>
			</main>
		</div>
	</div>
</div>
