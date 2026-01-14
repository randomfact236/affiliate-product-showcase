<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<\AffiliateProductShowcase\Models\Product> $products */
/** @var \AffiliateProductShowcase\Services\AffiliateService $affiliate_service */
?>
<div class="aps-grid">
	<?php foreach ( $products as $product ) : ?>
		<?php echo aps_view( 'src/Public/partials/product-card.php', [ 
			'product' => $product,
			'affiliate_service' => $affiliate_service
		] ); ?>
	<?php endforeach; ?>
</div>
