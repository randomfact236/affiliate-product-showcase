<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}/** @var array<\AffiliateProductShowcase\Models\Product> $products */
?>
<div class="aps-grid">
	<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}foreach ( $products as $product ) : ?>
		<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}echo aps_view( 'src/Public/partials/product-card.php', [ 'product' => $product ] ); ?>
	<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}endforeach; ?>
</div>
