<?php
/**
 * Tab: Details - Images and Media
 *
 * @package AffiliateProductShowcase\Admin\Partials\Tabs
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Product Image -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">🖼️</span>
		<span><?php esc_html_e( 'Product Image', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_image_url"><?php esc_html_e( 'Image URL', 'affiliate-product-showcase' ); ?></label>
			<input type="url" id="aps_image_url" name="aps_image_url" class="woocommerce-input" placeholder="<?php esc_attr_e( 'https://example.com/product-image.jpg', 'affiliate-product-showcase' ); ?>">
			<span class="description"><?php esc_html_e( 'Direct link to product image (will download to media library)', 'affiliate-product-showcase' ); ?></span>
		</div>
		
		<div id="aps_image_preview" style="display:none; margin-top:15px; text-align:center;">
			<img src="" alt="<?php esc_attr_e( 'Product Preview', 'affiliate-product-showcase' ); ?>" style="max-width:300px; max-height:300px; border:1px solid #dcdcde; border-radius:4px;">
		</div>
	</div>
</div>

<!-- Product Gallery -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">🖼️</span>
		<span><?php esc_html_e( 'Product Gallery', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_gallery"><?php esc_html_e( 'Gallery Images (one per line)', 'affiliate-product-showcase' ); ?></label>
			<textarea id="aps_gallery" name="aps_gallery" class="woocommerce-input woocommerce-textarea" rows="5" placeholder="<?php esc_attr_e( 'https://example.com/image1.jpg', 'affiliate-product-showcase' ); ?>"></textarea>
			<span class="description"><?php esc_html_e( 'Enter gallery image URLs (one per line)', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<!-- Product Video -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">🎬</span>
		<span><?php esc_html_e( 'Product Video', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_video_url"><?php esc_html_e( 'Video URL', 'affiliate-product-showcase' ); ?></label>
			<input type="url" id="aps_video_url" name="aps_video_url" class="woocommerce-input" placeholder="<?php esc_attr_e( 'https://youtube.com/watch?v=xxxxx', 'affiliate-product-showcase' ); ?>">
			<span class="description"><?php esc_html_e( 'YouTube or Vimeo video URL (optional)', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<!-- Product Rating -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">⭐</span>
		<span><?php esc_html_e( 'Product Rating', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_rating"><?php esc_html_e( 'Rating (0-5 stars)', 'affiliate-product-showcase' ); ?></label>
			<input type="number" id="aps_rating" name="aps_rating" class="woocommerce-input" step="0.1" min="0" max="5" placeholder="4.5">
			<span class="description"><?php esc_html_e( 'Average customer rating', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<style>
	/* Panel Icons */
	.panel-icon {
		font-size: 16px;
		margin-right: 8px;
	}
</style>

<script>
jQuery(document).ready(function($) {
	// Image preview on URL change
	$('#aps_image_url').on('change', function() {
		var url = $(this).val();
		if (url) {
			$('#aps_image_preview').show();
			$('#aps_image_preview img').attr('src', url);
		} else {
			$('#aps_image_preview').hide();
		}
	});
	
	// Load initial image preview
	if ($('#aps_image_url').val()) {
		$('#aps_image_preview').show();
		$('#aps_image_preview img').attr('src', $('#aps_image_url').val());
	}
});
</script>
