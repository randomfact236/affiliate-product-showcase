<?php
/**
 * Blocks Registration and Rendering
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 */

declare( strict_types = 1 );

namespace AffiliateProductShowcase\Blocks;

use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Services\ProductService;

// Block rendering functions
function render_product_grid( array $attributes ): string {
	// Parse attributes with defaults
	$per_page    = $attributes['perPage'] ?? 6;
	$columns      = $attributes['columns'] ?? 3;
	$gap          = $attributes['gap'] ?? 16;
	$show_price   = $attributes['showPrice'] ?? true;
	$show_rating  = $attributes['showRating'] ?? true;
	$show_badge   = $attributes['showBadge'] ?? true;
	$hover_effect = $attributes['hoverEffect'] ?? 'lift';

	// Get products
	$repository = new ProductRepository();
	$products   = $repository->get_published_products( $per_page );

	if ( empty( $products ) ) {
		return '<div class="aps-block--grid is-empty"><p>No products found.</p></div>';
	}

	// Build output
	ob_start();
	?>
	<div class="aps-block aps-block--grid" 
	     data-hover-effect="<?php echo esc_attr( $hover_effect ); ?>"
	     style="--aps-grid-columns: <?php echo esc_attr( $columns ); ?>; --aps-grid-gap: <?php echo esc_attr( $gap ); ?>px;">
		<?php
		foreach ( $products as $product ) {
			include __DIR__ . '/../Blocks/templates/product-grid-item.php';
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}

function render_product_showcase( array $attributes ): string {
	// Parse attributes with defaults
	$layout          = $attributes['layout'] ?? 'grid';
	$columns         = $attributes['columns'] ?? 3;
	$gap             = $attributes['gap'] ?? 16;
	$show_price     = $attributes['showPrice'] ?? true;
	$show_description = $attributes['showDescription'] ?? true;
	$show_button     = $attributes['showButton'] ?? true;
	$button_text     = $attributes['buttonText'] ?? 'View Details';

	// Get products
	$repository = new ProductRepository();
	$products   = $repository->get_published_products( 6 ); // Default 6 for showcase

	if ( empty( $products ) ) {
		return '<div class="aps-block--showcase is-empty"><p>No products found.</p></div>';
	}

	// Build output
	ob_start();
	?>
	<div class="aps-block aps-block--showcase" 
	     data-layout="<?php echo esc_attr( $layout ); ?>"
	     data-show-price="<?php echo $show_price ? 'true' : 'false'; ?>"
	     data-show-description="<?php echo $show_description ? 'true' : 'false'; ?>"
	     data-show-button="<?php echo $show_button ? 'true' : 'false'; ?>"
	     style="--aps-showcase-columns: <?php echo esc_attr( $columns ); ?>; --aps-showcase-gap: <?php echo esc_attr( $gap ); ?>px;">
		<?php
		foreach ( $products as $product ) {
			include __DIR__ . '/../Blocks/templates/product-showcase-item.php';
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}

// Register blocks
function register_blocks(): void {
	// Register Product Grid block
	register_block_type_from_metadata(
		AFFILIATE_PRODUCT_SHOWCASE_PATH . 'blocks/product-grid',
		[
			'render_callback' => __NAMESPACE__ . '\\render_product_grid',
		]
	);

	// Register Product Showcase block
	register_block_type_from_metadata(
		AFFILIATE_PRODUCT_SHOWCASE_PATH . 'blocks/product-showcase',
		[
			'render_callback' => __NAMESPACE__ . '\\render_product_showcase',
		]
	);
}

// Initialize
add_action( 'init', __NAMESPACE__ . '\\register_blocks', 10 );
