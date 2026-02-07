<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<\AffiliateProductShowcase\Models\Product> $products */
/** @var \AffiliateProductShowcase\Services\AffiliateService $affiliate_service */
?>
<div class="aps-cards-grid" role="list" aria-label="<?php echo esc_attr( sprintf( _n( 'List of %d product', 'List of %d products', count( $products ), 'affiliate-product-showcase' ), count( $products ), 'affiliate-product-showcase' ) ); ?>">
	<?php foreach ( $products as $product ) : ?>
		<article role="listitem">
			<?php echo aps_view( 'src/Public/partials/product-card.php', [ 
				'product' => $product,
				'affiliate_service' => $affiliate_service
			] ); ?>
		</article>
	<?php endforeach; ?>
</div>
