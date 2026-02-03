<?php
/**
 * Product Card Template - Pure Tailwind
 * 
 * @package AffiliateProductShowcase
 * @since 1.0.0
 * 
 * @var Product $product
 * @var AffiliateService $affiliate_service
 * @var array $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_label = $settings['cta_label'] ?? __( 'View Deal', 'affiliate-product-showcase' );
$tracking_url = $affiliate_service->get_tracking_url( $product->id );
?>

<article 
	class="aps-card aps-card-hover"
	data-product-id="<?php echo esc_attr( $product->id ); ?>"
	data-category="<?php echo esc_attr( $product->category ?? 'all' ); ?>"
	data-tags="<?php echo esc_attr( implode( ',', $product->tags ?? [] ) ); ?>"
>
	<!-- Featured Badge -->
	<?php if ( $product->is_featured ?? false ) : ?>
		<div class="aps-absolute aps-top-3 aps-left-3 aps-z-20">
			<span class="aps-badge aps-badge--featured">
				<svg class="aps-w-3 aps-h-3" viewBox="0 0 24 24" fill="currentColor">
					<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
				</svg>
				<?php esc_html_e( 'Featured', 'affiliate-product-showcase' ); ?>
			</span>
		</div>
	<?php endif; ?>

	<!-- Bookmark Button -->
	<button class="aps-bookmark aps-top-3 aps-left-3">
		<svg class="aps-bookmark__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
		</svg>
	</button>

	<!-- Product Image -->
	<?php if ( $product->image_url ) : ?>
		<div class="aps-card__media aps-gradient-blue">
			<img 
				src="<?php echo esc_url( $product->image_url ); ?>" 
				alt="<?php echo esc_attr( $product->title ); ?>"
				class="aps-w-full aps-h-full aps-object-cover"
				loading="lazy"
			>
		</div>
	<?php else : ?>
		<div class="aps-card__media aps-gradient-purple">
			<span class="aps-text-white/90 aps-font-medium aps-text-sm"><?php esc_html_e( 'Preview', 'affiliate-product-showcase' ); ?></span>
		</div>
	<?php endif; ?>

	<div class="aps-card__body">
		<!-- Title & Price Row -->
		<div class="aps-flex aps-justify-between aps-items-start aps-mb-3">
			<h3 class="aps-card__title aps-line-clamp-2">
				<?php echo esc_html( $product->title ); ?>
			</h3>
			<div class="aps-text-right aps-flex-shrink-0 aps-ml-2">
				<?php if ( $product->original_price && $product->original_price > $product->price ) : ?>
					<span class="aps-price--original">$<?php echo esc_html( number_format( $product->original_price, 2 ) ); ?>/mo</span>
				<?php endif; ?>
				<div class="aps-price">
					<span class="aps-price--current">$<?php echo esc_html( number_format( $product->price, 2 ) ); ?></span>
					<span class="aps-price--currency">/mo</span>
				</div>
				<?php if ( $product->discount_percentage ?? false ) : ?>
					<span class="aps-discount-badge">-<?php echo esc_html( $product->discount_percentage ); ?>%</span>
				<?php endif; ?>
			</div>
		</div>

		<!-- Description -->
		<?php if ( $product->short_description ) : ?>
			<p class="aps-card__description aps-line-clamp-2">
				<?php echo esc_html( $product->short_description ); ?>
			</p>
		<?php elseif ( $product->description ) : ?>
			<p class="aps-card__description aps-line-clamp-2">
				<?php echo esc_html( wp_trim_words( $product->description, 15 ) ); ?>
			</p>
		<?php endif; ?>

		<!-- Tags/Badges -->
		<?php if ( $product->badge ) : ?>
			<div class="aps-flex aps-items-center aps-gap-1.5 aps-mb-3">
				<svg class="aps-w-3.5 aps-h-3.5 aps-text-amber-400" viewBox="0 0 24 24" fill="currentColor">
					<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
				</svg>
				<span class="aps-text-sm aps-text-gray-600"><?php echo esc_html( $product->badge ); ?></span>
			</div>
		<?php endif; ?>

		<!-- Features -->
		<?php if ( ! empty( $product->features ) ) : ?>
			<div class="aps-feature-list">
				<?php foreach ( array_slice( $product->features, 0, 4 ) as $feature ) : ?>
					<div class="aps-feature-item">
						<svg class="aps-feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
							<polyline points="20 6 9 17 4 12"/>
						</svg>
						<span><?php echo esc_html( $feature ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Footer -->
		<div class="aps-card__footer">
			<!-- Rating & Reviews -->
			<div class="aps-flex aps-items-center aps-justify-between aps-mb-4">
				<?php if ( $product->rating ?? false ) : ?>
					<div class="aps-rating">
						<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
							<svg 
								class="aps-rating__star <?php echo $i > $product->rating ? 'aps-rating__star--empty' : ''; ?>" 
								viewBox="0 0 24 24" 
								fill="currentColor"
							>
								<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
							</svg>
						<?php endfor; ?>
						<span class="aps-rating__value"><?php echo esc_html( number_format( $product->rating, 1 ) ); ?>/5</span>
					</div>
				<?php endif; ?>
				
				<?php if ( $product->user_count ?? false ) : ?>
					<div class="aps-user-count <?php echo $product->user_count > 1000000 ? 'aps-user-count--large' : ''; ?>">
						<svg class="aps-w-3 aps-h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
							<circle cx="9" cy="7" r="4"/>
							<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
							<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
						</svg>
						<span><?php echo esc_html( $product->user_count_formatted ?? $product->user_count ); ?>+ users</span>
					</div>
				<?php endif; ?>
			</div>

			<!-- CTA Button -->
			<a 
				href="<?php echo esc_url( $tracking_url ); ?>"
				target="_blank"
				rel="nofollow sponsored noopener"
				class="aps-btn"
			>
				<?php echo esc_html( $cta_label ); ?>
				<svg class="aps-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
					<polyline points="15 3 21 3 21 9"/>
					<line x1="10" y1="14" x2="21" y2="3"/>
				</svg>
			</a>
		</div>
	</div>
</article>
