<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \AffiliateProductShowcase\Models\Product $product */
/** @var \AffiliateProductShowcase\Services\AffiliateService $affiliate_service */
echo aps_view( 'src/Public/partials/product-card.php', [ 
	'product' => $product,
	'affiliate_service' => $affiliate_service
] );
