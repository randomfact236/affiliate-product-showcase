<?php
/**
 * Product Grid Template - Pure Tailwind
 * 
 * @package AffiliateProductShowcase
 * @since 1.0.0
 * 
 * @var array $products
 * @var AffiliateService $affiliate_service
 * @var array $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $products ) ) {
	return '<p class="aps-text-gray-600">' . esc_html__( 'No products found.', 'affiliate-product-showcase' ) . '</p>';
}
?>

<div 
	class="aps-grid-products"
	role="list"
	aria-label="<?php echo esc_attr( sprintf( _n( '%d product', '%d products', count( $products ), 'affiliate-product-showcase' ), count( $products ) ) ); ?>"
>
	<?php foreach ( $products as $product ) : ?>
		<div role="listitem">
			<?php include __DIR__ . '/product-card.php'; ?>
		</div>
	<?php endforeach; ?>
</div>
