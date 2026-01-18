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
			<p class="aps-card__description"><?php echo wp_kses_post( $product->description ); ?></p>
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
			</div>
			<div class="aps-card__footer">
				<span class="aps-card__price">
					<span class="aps-card__price-currency" aria-label="Currency"><?php echo esc_html( $product->currency ); ?></span>
					<span class="aps-card__price-value" aria-label="Price"><?php echo esc_html( number_format_i18n( $product->price, 2 ) ); ?></span>
				</span>
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
