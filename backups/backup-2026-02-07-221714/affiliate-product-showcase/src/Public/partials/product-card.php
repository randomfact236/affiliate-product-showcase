<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \AffiliateProductShowcase\Models\Product $product */
/** @var \AffiliateProductShowcase\Services\AffiliateService $affiliate_service */
/** @var array $settings Optional settings */
$settings = $settings ?? [];
$cta_label = $settings['cta_label'] ?? __( 'Explore Now', 'affiliate-product-showcase' );
$enable_disclosure = $settings['enable_disclosure'] ?? true;
$disclosure_text = $settings['disclosure_text'] ?? __( 'We may earn a commission when you purchase through our links.', 'affiliate-product-showcase' );
$disclosure_position = $settings['disclosure_position'] ?? 'top';

// Calculate discount percentage
$discount_percentage = 0;
if ( $product->original_price && $product->original_price > $product->price ) {
	$discount_percentage = round( ( ( $product->original_price - $product->price ) / $product->original_price ) * 100 );
}

// Generate gradient class based on product ID for variety
$gradient_classes = ['pink', 'cyan', 'purple', 'orange', 'green', 'blue'];
$gradient_class = $gradient_classes[ $product->id % count( $gradient_classes ) ];

// Generate icon class based on product ID
$icon_classes = ['orange', 'blue', 'green', 'purple', 'pink'];
$icon_class = $icon_classes[ $product->id % count( $icon_classes ) ];

// Generate users pill class based on rating
$users_pill_class = $product->rating >= 4.5 ? 'green' : 'red';
?>

<div class="aps-root">
	<?php if ( $enable_disclosure && 'top' === $disclosure_position ) : ?>
		<div 
			class="aps-disclosure aps-disclosure--top aps-notice-wp aps-notice-info" 
			role="note"
			aria-label="<?php esc_attr_e( 'Affiliate Disclosure', 'affiliate-product-showcase' ); ?>"
		>
			<?php echo wp_kses_post( $disclosure_text ); ?>
		</div>
	<?php endif; ?>

	<article class="aps-tool-card">
		<?php if ( $product->badge ) : ?>
			<div class="aps-featured-badge"><?php echo esc_html( $product->badge ); ?></div>
		<?php endif; ?>

		<?php if ( $product->view_count ) : ?>
			<div class="aps-view-count"><?php echo esc_html( number_format_i18n( $product->view_count ) ); ?> viewed</div>
		<?php endif; ?>

		<div class="aps-card-image <?php echo esc_attr( $gradient_class ); ?>">
			<button class="aps-bookmark-icon" aria-label="<?php esc_attr_e( 'Bookmark this product', 'affiliate-product-showcase' ); ?>"></button>
			<?php if ( $product->image_url ) : ?>
				<img src="<?php echo esc_url( $product->image_url ); ?>" alt="<?php echo esc_attr( $product->title ); ?>" loading="lazy" />
			<?php else : ?>
				<span>Preview</span>
			<?php endif; ?>
		</div>

		<div class="aps-card-body">
			<div class="aps-card-header-row">
				<h3 class="aps-tool-name">
					<?php if ( $product->icon_emoji ) : ?>
						<span class="aps-tool-icon <?php echo esc_attr( $icon_class ); ?>"><?php echo esc_html( $product->icon_emoji ); ?></span>
					<?php endif; ?>
					<?php echo esc_html( $product->title ); ?>
				</h3>
				<div class="aps-price-block">
					<?php if ( $product->original_price && $product->original_price > $product->price ) : ?>
						<span class="aps-original-price">
							<?php echo esc_html( $product->currency ); ?><?php echo esc_html( number_format_i18n( $product->original_price, 2 ) ); ?>/mo
						</span>
					<?php endif; ?>
					<div class="aps-current-price">
						<?php echo esc_html( $product->currency ); ?><?php echo esc_html( number_format_i18n( $product->price, 2 ) ); ?>
						<span class="aps-price-period">/<?php echo esc_html( $product->billing_period ?? 'mo' ); ?></span>
					</div>
					<?php if ( $discount_percentage > 0 ) : ?>
						<span class="aps-discount-badge"><?php echo esc_html( $discount_percentage ); ?>% OFF</span>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( ! empty( $product->short_description ) ) : ?>
				<p class="aps-tool-description"><?php echo wp_kses_post( $product->short_description ); ?></p>
			<?php elseif ( ! empty( $product->description ) ) : ?>
				<p class="aps-tool-description"><?php echo wp_kses_post( wp_trim_words( $product->description, 20, '...' ) ); ?></p>
			<?php endif; ?>

			<?php if ( $product->badge ) : ?>
				<div class="aps-inline-tag"><?php echo esc_html( $product->badge ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $product->features ) ) : ?>
				<div class="aps-features-list">
					<?php foreach ( $product->features as $index => $feature ) : ?>
						<div class="aps-feature-item <?php echo $index >= 2 ? 'dimmed' : ''; ?>"><?php echo esc_html( $feature ); ?></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="aps-card-footer">
				<div class="aps-stats-row">
					<div class="aps-stats-left">
						<?php if ( $product->rating ) : ?>
							<div class="aps-rating-stars">
								<?php
								$full_stars = floor( $product->rating );
								$has_half_star = ( $product->rating - $full_stars ) >= 0.5;
								$empty_stars = 5 - $full_stars - ( $has_half_star ? 1 : 0 );
								for ( $i = 0; $i < $full_stars; $i++ ) : ?>
									<span class="aps-star">★</span>
								<?php endfor; ?>
								<?php if ( $has_half_star ) : ?>
									<span class="aps-star">★</span>
								<?php endif; ?>
								<?php for ( $i = 0; $i < $empty_stars; $i++ ) : ?>
									<span class="aps-star empty">★</span>
								<?php endfor; ?>
								<span class="aps-rating-text"><?php echo esc_html( number_format_i18n( $product->rating, 1 ) ); ?>/5</span>
							</div>
						<?php endif; ?>
						<?php if ( $product->reviews_count ) : ?>
							<span class="aps-reviews-count"><?php echo esc_html( number_format_i18n( $product->reviews_count ) ); ?> reviews</span>
						<?php endif; ?>
					</div>
					<?php if ( $product->users_count ) : ?>
						<div class="aps-users-pill <?php echo esc_attr( $users_pill_class ); ?>">
							<?php echo esc_html( $product->users_count ); ?>+ users
						</div>
					<?php endif; ?>
				</div>

				<a 
					class="aps-action-button" 
					href="<?php echo esc_url( $affiliate_service->get_tracking_url( $product->id ) ); ?>" 
					target="_blank" 
					rel="nofollow sponsored noopener"
					aria-label="<?php echo esc_attr( sprintf( __( '%1$s - opens in new tab', 'affiliate-product-showcase' ), $cta_label ) ); ?>"
				>
					<?php echo esc_html( $cta_label ); ?>
				</a>
				<?php if ( $product->trial_days ) : ?>
					<div class="aps-trial-text"><?php printf( esc_html__( '%d-day free trial available', 'affiliate-product-showcase' ), $product->trial_days ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</article>

	<?php if ( $enable_disclosure && 'bottom' === $disclosure_position ) : ?>
		<div 
			class="aps-disclosure aps-disclosure--bottom aps-notice-wp aps-notice-info" 
			role="note"
			aria-label="<?php esc_attr_e( 'Affiliate Disclosure', 'affiliate-product-showcase' ); ?>"
		>
			<?php echo wp_kses_post( $disclosure_text ); ?>
		</div>
	<?php endif; ?>
</div>
