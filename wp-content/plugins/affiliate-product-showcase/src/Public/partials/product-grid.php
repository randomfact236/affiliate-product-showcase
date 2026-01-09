<?php
/** @var array<\AffiliateProductShowcase\Models\Product> $products */
?>
<div class="aps-grid">
	<?php foreach ( $products as $product ) : ?>
		<?php echo aps_view( 'src/Public/partials/product-card.php', [ 'product' => $product ] ); ?>
	<?php endforeach; ?>
</div>
