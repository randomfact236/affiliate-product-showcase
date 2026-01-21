<?php
/**
 * Single Product Template
 *
 * Displays individual product with full details,
 * images, features, and related products.
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

// Get current product
$product_id = get_the_ID();
$title = get_the_title();
$price = get_post_meta( $product_id, 'product_price', true );
$sale_price = get_post_meta( $product_id, 'product_sale_price', true );
$affiliate_url = get_post_meta( $product_id, 'product_affiliate_url', true );
$brand = get_post_meta( $product_id, 'product_brand', true );
$sku = get_post_meta( $product_id, 'product_sku', true );
$stock_status = get_post_meta( $product_id, 'product_stock_status', true );
$rating = get_post_meta( $product_id, 'product_rating', true );
$features = get_post_meta( $product_id, 'product_features', true );
$description = get_the_content();
$product_url = get_permalink( $product_id );

// Format price
if ( $sale_price && floatval( $sale_price ) > 0 ) {
	$price_html = '<span class="aps-single-price-original">' . number_format( floatval( $price ), 2 ) . '</span> ';
	$price_html .= '<span class="aps-single-price-sale">' . number_format( floatval( $sale_price ), 2 ) . '</span>';
	$sale_badge = '<span class="aps-sale-badge">' . esc_html__( 'On Sale', 'affiliate-product-showcase' ) . '</span>';
} else {
	$price_html = '<span class="aps-single-price-current">' . number_format( floatval( $price ), 2 ) . '</span>';
	$sale_badge = '';
}

// Format rating
$rating_html = '';
if ( $rating ) {
	$rating_html = '<div class="aps-single-rating">';
	$rating_html .= '<span class="aps-single-stars" style="--rating: ' . esc_attr( $rating ) . ';">★★★★★</span>';
	$rating_html .= '<span class="aps-single-rating-value">' . number_format( $rating, 1 ) . '</span>';
	$rating_html .= '</div>';
}

// Format features
$features_html = '';
if ( $features ) {
	$features_array = is_array( $features ) ? $features : [ $features ];
	$features_html .= '<ul class="aps-single-features">';
	foreach ( $features_array as $index => $feature ) {
		if ( $index < 10 ) { // Limit to 10 features
			$features_html .= '<li class="aps-single-feature">' . esc_html( $feature ) . '</li>';
		}
	}
	$features_html .= '</ul>';
}

// Get categories
$categories = get_the_terms( $product_id, 'product_category' );

// Get tags
$tags = get_the_terms( $product_id, 'product_tag' );

// Get ribbons
$ribbons = get_the_terms( $product_id, 'product_ribbon' );
?>

<div class="aps-single-wrapper">
	<?php
	// Left sidebar for categories/tags
	if ( $categories || $tags ) {
		?>
		<aside class="aps-single-sidebar">
			<?php
			if ( $categories && ! is_wp_error( $categories ) ) {
				?>
				<div class="aps-single-sidebar-section">
					<h3 class="aps-single-sidebar-title">
						<?php esc_html_e( 'Categories', 'affiliate-product-showcase' ); ?>
					</h3>
					<ul class="aps-single-sidebar-list">
						<?php
						foreach ( $categories as $category ) {
							$link = get_term_link( $category );
							?>
							<li class="aps-single-sidebar-item">
								<a href="<?php echo esc_url( $link ); ?>" class="aps-single-sidebar-link">
									<?php echo esc_html( $category->name ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<?php
			}
			?>

			<?php
			if ( $tags && ! is_wp_error( $tags ) ) {
				?>
				<div class="aps-single-sidebar-section">
					<h3 class="aps-single-sidebar-title">
						<?php esc_html_e( 'Tags', 'affiliate-product-showcase' ); ?>
					</h3>
					<ul class="aps-single-sidebar-list">
						<?php
						foreach ( $tags as $tag ) {
							$link = get_term_link( $tag );
							?>
							<li class="aps-single-sidebar-item">
								<a href="<?php echo esc_url( $link ); ?>" class="aps-single-sidebar-link">
									<?php echo esc_html( $tag->name ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<?php
			}
			?>
		</aside>
		<?php
	}
	?>

	<article class="aps-single-product">
		<?php
		// Product gallery
		if ( has_post_thumbnail( $product_id ) ) {
			?>
			<div class="aps-single-gallery">
				<?php
				$images = []; // Could implement multiple images
				$images[] = get_post_thumbnail_id( $product_id );

				foreach ( $images as $image_id ) {
					$full_url = wp_get_attachment_image_url( $image_id, 'large' );
					$alt_text = get_post_meta( $product_id, 'post_title', true );
					?>
					<div class="aps-single-gallery-item">
						<img src="<?php echo esc_url( $full_url ); ?>" 
							 alt="<?php echo esc_attr( $alt_text ); ?>" 
								class="aps-single-gallery-image" 
								loading="lazy">
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>

		<div class="aps-single-header">
			<div class="aps-single-badges">
				<?php
				// Ribbons
				if ( $ribbons && ! is_wp_error( $ribbons ) ) {
					foreach ( $ribbons as $ribbon ) {
						$ribbon_text = get_term_meta( $ribbon->term_id, 'ribbon_text', true );
						$bg_color = get_term_meta( $ribbon->term_id, 'ribbon_bg_color', true );
						$text_color = get_term_meta( $ribbon->term_id, 'ribbon_text_color', true );
						$position = get_term_meta( $ribbon->term_id, 'ribbon_position', true );
						$style = get_term_meta( $ribbon->term_id, 'ribbon_style', true );

						$ribbon_style = 'position: absolute; ';
						$ribbon_style .= $position . ': 10px; ';
						$ribbon_style .= 'background-color: ' . esc_attr( $bg_color ) . '; ';
						$ribbon_style .= 'color: ' . esc_attr( $text_color ) . '; ';
						$ribbon_style .= 'padding: 5px 15px; ';
						$ribbon_style .= 'border-radius: 3px; ';
						$ribbon_style .= 'font-size: 12px; ';
						$ribbon_style .= 'font-weight: bold;';

						if ( 'corner' === $style ) {
							$ribbon_style .= 'clip-path: polygon(0 0, 100% 0, 0 20%); ';
						}
						?>
						<span class="aps-ribbon" style="<?php echo esc_attr( $ribbon_style ); ?>">
							<?php echo esc_html( $ribbon_text ?: $ribbon->name ); ?>
						</span>
						<?php
					}
				}
				?>

				<?php echo $sale_badge; ?>
			</div>

			<h1 class="aps-single-title">
				<?php the_title(); ?>
			</h1>

			<?php
			// Brand
			if ( $brand ) {
				?>
				<div class="aps-single-brand">
					<?php echo esc_html( $brand ); ?>
				</div>
				<?php
			}
			?>

			<?php
			// SKU
			if ( $sku ) {
				?>
				<div class="aps-single-sku">
					<span class="aps-single-sku-label">
						<?php esc_html_e( 'SKU:', 'affiliate-product-showcase' ); ?>
					</span>
					<span class="aps-single-sku-value">
						<?php echo esc_html( $sku ); ?>
					</span>
				</div>
				<?php
			}
			?>

			<?php
			// Stock status
			if ( $stock_status ) {
				$stock_class = 'in_stock' === $stock_status ? 'aps-stock-in' : 'aps-stock-out';
				$stock_label = 'in_stock' === $stock_status ? esc_html__( 'In Stock', 'affiliate-product-showcase' ) : esc_html__( 'Out of Stock', 'affiliate-product-showcase' );
				?>
				<div class="aps-single-stock <?php echo esc_attr( $stock_class ); ?>">
					<?php echo esc_html( $stock_label ); ?>
				</div>
				<?php
			}
			?>
		</div>

		<div class="aps-single-content">
			<?php
			// Description
			if ( $description ) {
				?>
				<div class="aps-single-description">
					<?php
					// Check if it's a block editor content
					if ( has_blocks( $product_id ) ) {
						the_content();
					} else {
						echo wp_kses_post( $description );
					}
					?>
				</div>
				<?php
			}
			?>

			<?php
			// Features
			if ( $features_html ) {
				?>
				<div class="aps-single-features-wrapper">
					<h2 class="aps-single-features-title">
						<?php esc_html_e( 'Key Features', 'affiliate-product-showcase' ); ?>
					</h2>
					<?php echo $features_html; ?>
				</div>
				<?php
			}
			?>

			<?php
			// Rating
			if ( $rating_html ) {
				?>
				<div class="aps-single-rating-wrapper">
					<h2 class="aps-single-rating-title">
						<?php esc_html_e( 'Customer Rating', 'affiliate-product-showcase' ); ?>
					</h2>
					<?php echo $rating_html; ?>
				</div>
				<?php
			}
			?>
		</div>

		<div class="aps-single-actions">
			<div class="aps-single-price">
				<h2 class="aps-single-price-title">
					<?php esc_html_e( 'Price', 'affiliate-product-showcase' ); ?>
				</h2>
				<?php echo $price_html; ?>
			</div>

			<?php
			// CTA Button
			if ( $affiliate_url ) {
				?>
				<div class="aps-single-cta">
					<a href="<?php echo esc_url( $affiliate_url ); ?>" 
					   class="aps-single-cta-button" 
					   rel="nofollow sponsored" 
					   target="_blank">
						<?php
						// Get CTA text from settings or use default
						$cta_text = apply_filters( 'affiliate_showcase_cta_text', __( 'View Deal', 'affiliate-product-showcase' ) );
						echo esc_html( $cta_text );
						?>
					</a>
				</div>
				<?php
			}
			?>

			<div class="aps-single-share">
				<?php
				// Share buttons could be added here
				if ( function_exists( '\AffiliateProductShowcase\Public\Shortcodes' ) ) {
					// Could implement social sharing
				}
				?>
			</div>
		</div>

		<?php
		// Related products
		$args = [
			'post_type'      => 'affiliate_product',
			'post_status'    => 'publish',
			'posts_per_page' => 4,
			'post__not_in'   => [ $product_id ],
			'orderby'        => 'rand',
			];

		$related_products = new \WP_Query( $args );

		if ( $related_products->have_posts() ) {
			?>
			<div class="aps-single-related">
				<h2 class="aps-single-related-title">
					<?php esc_html_e( 'You May Also Like', 'affiliate-product-showcase' ); ?>
				</h2>
				<div class="aps-single-related-grid">
					<?php
					while ( $related_products->have_posts() ) {
						$related_products->the_post();
						?>
						<div class="aps-related-product-card">
							<?php
							// Product image
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'medium', [ 'loading' => 'lazy' ] );
							}
							?>

							<div class="aps-related-product-content">
								<h3 class="aps-related-product-title">
									<a href="<?php the_permalink(); ?>" rel="nofollow">
										<?php the_title(); ?>
									</a>
								</h3>

								<?php
								// Product price
								$related_price = get_post_meta( get_the_ID(), 'product_price', true );
								$related_sale = get_post_meta( get_the_ID(), 'product_sale_price', true );
								
								if ( $related_sale && floatval( $related_sale ) > 0 ) {
									echo '<div class="aps-related-price">';
									echo '<span class="aps-price-original">' . number_format( floatval( $related_price ), 2 ) . '</span> ';
									echo '<span class="aps-price-sale">' . number_format( floatval( $related_sale ), 2 ) . '</span>';
									echo '</div>';
								} else {
									echo '<div class="aps-related-price">';
									echo '<span class="aps-price-current">' . number_format( floatval( $related_price ), 2 ) . '</span>';
									echo '</div>';
								}
								?>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
			wp_reset_postdata();
		}
		?>
	</article>

	<?php
get_footer();
