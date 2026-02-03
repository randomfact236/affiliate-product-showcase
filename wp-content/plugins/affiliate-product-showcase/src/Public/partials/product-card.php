<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \AffiliateProductShowcase\Models\Product $product */
/** @var \AffiliateProductShowcase\Services\AffiliateService $affiliate_service */
/** @var array $settings Optional settings */
$settings = $settings ?? [];
$cta_label = $settings['cta_label'] ?? __( 'View Deal', 'affiliate-product-showcase' );
$enable_disclosure = $settings['enable_disclosure'] ?? true;
$disclosure_text = $settings['disclosure_text'] ?? __( 'We may earn a commission when you purchase through our links.', 'affiliate-product-showcase' );
$disclosure_position = $settings['disclosure_position'] ?? 'top';
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

	<article 
		class="aps-card aps-card-wp"
		aria-labelledby="product-title-<?php echo esc_attr( $product->id ); ?>"
	>
		<?php if ( $product->image_url ) : ?>
			<div class="aps-card__media">
				<img src="<?php echo esc_url( $product->image_url ); ?>" alt="<?php echo esc_attr( $product->title ); ?>" loading="lazy" />
			</div>
		<?php endif; ?>
		<div class="aps-card__body">
			<h3 id="product-title-<?php echo esc_attr( $product->id ); ?>" class="aps-card__title"><?php echo esc_html( $product->title ); ?></h3>
			<?php if ( ! empty( $product->short_description ) ) : ?>
				<p class="aps-card__short-description"><?php echo wp_kses_post( $product->short_description ); ?></p>
			<?php elseif ( ! empty( $product->description ) ) : ?>
				<p class="aps-card__short-description"><?php echo wp_kses_post( wp_trim_words( $product->description, 20, '...' ) ); ?></p>
			<?php endif; ?>
			<div class="aps-card__meta">
				<?php if ( $product->badge ) : ?>
					<span class="aps-card__badge" role="status" aria-label="<?php echo esc_attr( $product->badge ); ?>"><?php echo esc_html( $product->badge ); ?></span>
				<?php endif; ?>
				<?php if ( $product->rating ) : ?>
					<span class="aps-card__rating" aria-label="<?php echo esc_attr( sprintf( __( 'Rating: %1$.1f out of 5 stars', 'affiliate-product-showcase' ), $product->rating ) ); ?>">
						<span aria-hidden="true">★</span>
						<?php echo esc_html( number_format_i18n( $product->rating, 1 ) ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $product->version_number ) : ?>
					<span class="aps-card__version" aria-label="<?php echo esc_attr( sprintf( __( 'Version: %s', 'affiliate-product-showcase' ), $product->version_number ) ); ?>">
						<span class="dashicons dashicons-download" aria-hidden="true"></span>
						<?php echo esc_html( $product->version_number ); ?>
					</span>
				<?php endif; ?>
			</div>
			<div class="aps-card__footer">
				<?php if ( $product->original_price && $product->original_price > $product->price ) : ?>
					<div class="aps-card__price-container">
						<span class="aps-card__price aps-card__price--original">
							<span class="aps-card__price-currency" aria-label="<?php echo esc_attr( __( 'Currency', 'affiliate-product-showcase' ) ); ?>"><?php echo esc_html( $product->currency ); ?></span>
							<span class="aps-card__price-value" aria-label="<?php echo esc_attr( __( 'Original price', 'affiliate-product-showcase' ) ); ?>"><?php echo esc_html( number_format_i18n( $product->original_price, 2 ) ); ?></span>
						</span>
						<span class="aps-card__price">
							<span class="aps-card__price-currency" aria-label="<?php echo esc_attr( __( 'Currency', 'affiliate-product-showcase' ) ); ?>"><?php echo esc_html( $product->currency ); ?></span>
							<span class="aps-card__price-value" aria-label="<?php echo esc_attr( __( 'Sale price', 'affiliate-product-showcase' ) ); ?>"><?php echo esc_html( number_format_i18n( $product->price, 2 ) ); ?></span>
						</span>
						<?php if ( $product->discount_percentage ) : ?>
							<span class="aps-card__discount" aria-label="<?php echo esc_attr( sprintf( __( '%d%% discount', 'affiliate-product-showcase' ), $product->discount_percentage ) ); ?>">
								<?php echo esc_html( sprintf( __( '-%d%%', 'affiliate-product-showcase' ), $product->discount_percentage ) ); ?>
							</span>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<span class="aps-card__price">
						<span class="aps-card__price-currency" aria-label="<?php echo esc_attr( __( 'Currency', 'affiliate-product-showcase' ) ); ?>"><?php echo esc_html( $product->currency ); ?></span>
						<span class="aps-card__price-value" aria-label="<?php echo esc_attr( __( 'Price', 'affiliate-product-showcase' ) ); ?>"><?php echo esc_html( number_format_i18n( $product->price, 2 ) ); ?></span>
					</span>
				<?php endif; ?>
				<a 
					class="aps-card__cta aps-btn-wp" 
					href="<?php echo esc_url( $affiliate_service->get_tracking_url( $product->id ) ); ?>" 
					target="_blank" 
					rel="nofollow sponsored noopener"
					aria-label="<?php echo esc_attr( sprintf( __( '%1$s - opens in new tab', 'affiliate-product-showcase' ), $cta_label ) ); ?>"
				>
					<?php echo esc_html( $cta_label ); ?>
				</a>
			</div>
			<?php if ( $product->platform_requirements ) : ?>
				<div class="aps-card__platform" aria-label="<?php echo esc_attr_e( 'Platform requirements', 'affiliate-product-showcase' ); ?>">
					<details class="aps-card__platform-details">
						<summary class="aps-card__platform-toggle">
							<span class="dashicons dashicons-info" aria-hidden="true"></span>
							<?php esc_html_e( 'Requirements', 'affiliate-product-showcase' ); ?>
						</summary>
						<div class="aps-card__platform-content">
							<?php echo wp_kses_post( $product->platform_requirements ); ?>
						</div>
					</details>
				</div>
			<?php endif; ?>
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
