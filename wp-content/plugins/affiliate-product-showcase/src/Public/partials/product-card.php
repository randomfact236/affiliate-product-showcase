<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}/** @var \AffiliateProductShowcase\Models\Product $product */
/** @var array $settings Optional settings */
$settings = $settings ?? [];
$cta_label = $settings['cta_label'] ?? __( 'View Deal', 'affiliate-product-showcase' );
?>
<article class="aps-card">
	<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}if ( $product->image_url ) : ?>
		<div class="aps-card__media">
			<img src="<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_url( $product->image_url ); ?>" alt="<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_attr( $product->title ); ?>" loading="lazy" />
		</div>
	<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}endif; ?>
	<div class="aps-card__body">
		<h3 class="aps-card__title"><?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_html( $product->title ); ?></h3>
		<p class="aps-card__description"><?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo wp_kses_post( $product->description ); ?></p>
		<div class="aps-card__meta">
			<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}if ( $product->badge ) : ?>
				<span class="aps-card__badge"><?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_html( $product->badge ); ?></span>
			<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}endif; ?>
			<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}if ( $product->rating ) : ?>
				<span class="aps-card__rating">★ <?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_html( number_format_i18n( $product->rating, 1 ) ); ?></span>
			<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}endif; ?>
		</div>
		<div class="aps-card__footer">
			<span class="aps-card__price"><?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_html( $product->currency ); ?> <?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_html( number_format_i18n( $product->price, 2 ) ); ?></span>
			<a class="aps-card__cta" href="<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_url( $product->affiliate_url ); ?>" target="_blank" rel="nofollow sponsored noopener">
				<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo esc_html( $cta_label ); ?>
			</a>
		</div>
	</div>
</article>
