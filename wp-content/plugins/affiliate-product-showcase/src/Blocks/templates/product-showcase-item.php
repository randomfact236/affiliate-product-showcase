<?php
/**
 * Product Showcase Item Template
 *
 * @package AffiliateProductShowcase
 * @since   1.0.0
 */

/** @var AffiliateProductShowcase\Models\Product $product */

use AffiliateProductShowcase\Helpers\FormatHelper;

// Get product data
$id          = $product->get_id();
$title        = $product->get_title();
$description  = $product->get_description();
$image_url    = $product->get_image_url( 'large' );
$link_url     = $product->get_affiliate_link();
$price        = $product->get_price();
$original_price = $product->get_original_price();
$badge        = $product->get_badge();

// Format data
$display_price = FormatHelper::format_price( $price );
$display_original_price = $original_price ? FormatHelper::format_price( $original_price ) : '';

// Global attributes from parent scope
global $show_price, $show_description, $show_button, $button_text, $layout;
?>

<article class="aps-showcase-item" id="aps-product-<?php echo esc_attr( $id ); ?>">
	<?php if ( $image_url ) : ?>
		<img src="<?php echo esc_url( $image_url ); ?>" 
		     alt="<?php echo esc_attr( $title ); ?>" 
		     class="aps-product-image"
		     loading="lazy" />
	<?php endif; ?>

	<?php if ( $badge ) : ?>
		<span class="aps-product-badge"><?php echo esc_html( $badge ); ?></span>
	<?php endif; ?>

	<div class="aps-product-content">
		<h3 class="aps-product-title">
			<a href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="nofollow sponsored">
				<?php echo esc_html( $title ); ?>
			</a>
		</h3>

		<?php if ( $show_price && $price ) : ?>
			<div class="aps-product-price">
				<span class="aps-current-price"><?php echo $display_price; // Already escaped ?></span>
				<?php if ( $display_original_price ) : ?>
					<span class="aps-original-price"><?php echo $display_original_price; // Already escaped ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_description && $description ) : ?>
			<p class="aps-product-description">
				<?php 
				// Show more text in list layout, less in grid
				$word_limit = $layout === 'list' ? 40 : 25;
				echo wp_kses_post( wp_trim_words( $description, $word_limit, '...' ) ); 
				?>
			</p>
		<?php endif; ?>

		<?php if ( $show_button ) : ?>
			<a href="<?php echo esc_url( $link_url ); ?>" 
			   target="_blank" 
			   rel="nofollow sponsored"
			   class="aps-product-button">
				<?php echo esc_html( $button_text ); ?>
			</a>
		<?php endif; ?>
	</div>
</article>
