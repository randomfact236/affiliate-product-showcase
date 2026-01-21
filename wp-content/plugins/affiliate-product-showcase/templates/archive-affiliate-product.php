<?php
/**
 * Archive Template: Affiliate Products
 *
 * Displays archive page for all products.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

get_header();

/**
 * Output breadcrumb navigation
 */
if ( function_exists( '\AffiliateProductShowcase\Public\Templates' ) ) {
	$templates = new \AffiliateProductShowcase\Public\Templates();
	echo $templates->render_breadcrumbs();
}
?>

<div class="aps-archive-wrapper">
	<header class="aps-archive-header">
		<h1 class="aps-archive-title">
			<?php
			if ( is_post_type_archive( 'affiliate_product' ) ) {
				esc_html_e( 'All Products', 'affiliate-product-showcase' );
			} else {
				the_archive_title();
			}
			?>
		</h1>
		
		<?php
		// Output filter/sort/search if available
		if ( shortcode_exists( 'affiliate_products' ) ) {
			echo do_shortcode( 'affiliate_products', 'search=true&sort=true&filter=true&pagination=true&limit=12' );
		}
		?>
	</header>

	<main class="aps-archive-content">
		<?php
		if ( have_posts() ) {
			?>
			<div class="aps-products-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					
					// Get product data
					$product_id = get_the_ID();
					$title = get_the_title();
					$price = get_post_meta( $product_id, 'product_price', true );
					$sale_price = get_post_meta( $product_id, 'product_sale_price', true );
					$affiliate_url = get_post_meta( $product_id, 'product_affiliate_url', true );
					$brand = get_post_meta( $product_id, 'product_brand', true );
					$featured = get_post_meta( $product_id, 'product_featured', true );
					
					// Format price
					if ( $sale_price && floatval( $sale_price ) > 0 ) {
						$price_html = '<span class="aps-price-original">' . number_format( floatval( $price ), 2 ) . '</span> ';
						$price_html .= '<span class="aps-price-sale">' . number_format( floatval( $sale_price ), 2 ) . '</span>';
					} else {
						$price_html = '<span class="aps-price-current">' . number_format( floatval( $price ), 2 ) . '</span>';
					}
					?>
					
					<article class="aps-product-card <?php echo $featured ? 'aps-product-featured' : ''; ?>">
						<?php
						if ( has_post_thumbnail( $product_id ) ) {
							echo '<div class="aps-product-image">';
							echo '<a href="' . esc_url( $affiliate_url ) . '" rel="nofollow sponsored" target="_blank" aria-label="' . esc_attr( $title ) . '">';
							the_post_thumbnail( $product_id, 'medium', [ 'loading' => 'lazy' ] );
							echo '</a>';
							echo '</div>';
						}
						?>
						
						<div class="aps-product-content">
							<?php
							if ( $brand ) {
								echo '<div class="aps-product-brand">' . esc_html( $brand ) . '</div>';
							}
							?>
							
							<h2 class="aps-product-title">
								<a href="<?php the_permalink(); ?>" rel="nofollow">
									<?php the_title(); ?>
								</a>
							</h2>
							
							<?php
							// Rating
							$rating = get_post_meta( $product_id, 'product_rating', true );
							if ( $rating ) {
								echo '<div class="aps-product-rating">';
								echo '<span class="aps-stars" style="--rating: ' . esc_attr( $rating ) . ';">★★★★★</span>';
								echo '<span class="aps-rating-value">' . number_format( $rating, 1 ) . '</span>';
								echo '</div>';
							}
							?>
							
							<div class="aps-product-price">
								<?php echo $price_html; ?>
							</div>
							
							<div class="aps-product-cta">
								<a href="<?php echo esc_url( $affiliate_url ); ?>" class="aps-cta-button" rel="nofollow sponsored" target="_blank">
									<?php esc_html_e( 'View Deal', 'affiliate-product-showcase' ); ?>
								</a>
							</div>
						</div>
					</article>
					<?php
				} // End while
				?>
			</div>
			<?php
			
			// Pagination
			if ( shortcode_exists( 'affiliate_products' ) ) {
				echo do_shortcode( 'affiliate_products', 'pagination=true' );
			}
			?>
			
		<?php
		} else {
			?>
			<div class="aps-empty-state">
				<p><?php esc_html_e( 'No products found', 'affiliate-product-showcase' ); ?></p>
			</div>
			<?php
		}
		?>
	</main>
</div>

<?php
get_footer();
