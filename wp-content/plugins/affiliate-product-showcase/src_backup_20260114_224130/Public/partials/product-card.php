<?php
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
		<div class="aps-disclosure aps-disclosure--top aps-notice-wp aps-notice-info">
			<?php echo wp_kses_post( $disclosure_text ); ?>
		</div>
	<?php endif; ?>

	<article class="aps-card aps-card-wp">
		<?php if ( $product->image_url ) : ?>
			<div class="aps-card__media">
				<img src="<?php echo esc_url( $product->image_url ); ?>" alt="<?php echo esc_attr( $product->title ); ?>" loading="lazy" />
			</div>
		<?php endif; ?>
		<div class="aps-card__body">
			<h3 class="aps-card__title"><?php echo esc_html( $product->title ); ?></h3>
			<p class="aps-card__description"><?php echo wp_kses_post( $product->description ); ?></p>
			<div class="aps-card__meta">
				<?php if ( $product->badge ) : ?>
					<span class="aps-card__badge"><?php echo esc_html( $product->badge ); ?></span>
				<?php endif; ?>
				<?php if ( $product->rating ) : ?>
					<span class="aps-card__rating">★ <?php echo esc_html( number_format_i18n( $product->rating, 1 ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="aps-card__footer">
				<span class="aps-card__price"><?php echo esc_html( $product->currency ); ?> <?php echo esc_html( number_format_i18n( $product->price, 2 ) ); ?></span>
				<a class="aps-card__cta aps-btn-wp" href="<?php echo esc_url( $affiliate_service->get_tracking_url( $product->id ) ); ?>" target="_blank" rel="nofollow sponsored noopener">
					<?php echo esc_html( $cta_label ); ?>
				</a>
			</div>
		</div>
	</article>

	<?php if ( $enable_disclosure && 'bottom' === $disclosure_position ) : ?>
		<div class="aps-disclosure aps-disclosure--bottom aps-notice-wp aps-notice-info">
			<?php echo wp_kses_post( $disclosure_text ); ?>
		</div>
	<?php endif; ?>
</div>
