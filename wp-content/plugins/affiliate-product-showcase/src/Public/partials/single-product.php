<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}/** @var \AffiliateProductShowcase\Models\Product $product */
echo aps_view( 'src/Public/partials/product-card.php', [ 'product' => $product ] );
