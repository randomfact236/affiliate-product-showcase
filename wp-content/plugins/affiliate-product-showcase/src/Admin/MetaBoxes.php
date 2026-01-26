<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Services\ProductService;

final class MetaBoxes {
	public function __construct( private ProductService $product_service ) {
	}

	public function register(): void {
		add_meta_box(
			'aps_product_details',
			__( 'Product Details', Constants::TEXTDOMAIN ),
			[ $this, 'render' ],
			Constants::CPT_PRODUCT,
			'normal',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		// Only show meta box for affiliate_product CPT
		$post_type = $post->post_type;
		
		if ( $post_type !== 'aps_product' ) {
			return;
		}
		
		// Get current meta values
		$meta = [
			// Group 1: Product Information
			'sku'                => get_post_meta( $post->ID, '_aps_sku', true ),
			'brand'              => get_post_meta( $post->ID, '_aps_brand', true ),
			'short_description'   => get_post_meta( $post->ID, '_aps_short_description', true ),
			
			// Group 2: Pricing
			'regular_price'       => get_post_meta( $post->ID, '_aps_regular_price', true ),
			'sale_price'          => get_post_meta( $post->ID, '_aps_sale_price', true ),
			'discount_percentage' => get_post_meta( $post->ID, '_aps_discount_percentage', true ),
			'currency'            => get_post_meta( $post->ID, '_aps_currency', true ),
			
			// Group 3: Product Data
			'stock_status'          => get_post_meta( $post->ID, '_aps_stock_status', true ),
			'availability_date'     => get_post_meta( $post->ID, '_aps_availability_date', true ),
			'rating'               => get_post_meta( $post->ID, '_aps_rating', true ),
			'review_count'         => get_post_meta( $post->ID, '_aps_review_count', true ),
			
			// Group 3b: Digital Product Information
			'version_number'       => get_post_meta( $post->ID, '_aps_version_number', true ),
			'platform_requirements' => get_post_meta( $post->ID, '_aps_platform_requirements', true ),
			
			// Group 4: Product Media
			'video_url'     => get_post_meta( $post->ID, '_aps_video_url', true ),
			
			// Group 5: Shipping & Dimensions
			'weight'  => get_post_meta( $post->ID, '_aps_weight', true ),
			'length'  => get_post_meta( $post->ID, '_aps_length', true ),
			'width'   => get_post_meta( $post->ID, '_aps_width', true ),
			'height'  => get_post_meta( $post->ID, '_aps_height', true ),
			
			// Group 6: Affiliate & Links
			'affiliate_url' => get_post_meta( $post->ID, '_aps_affiliate_url', true ),
			'coupon_url'    => get_post_meta( $post->ID, '_aps_coupon_url', true ),
			
			// Group 7: Product Ribbons
			'featured'    => get_post_meta( $post->ID, '_aps_featured', true ),
			'badge_text'  => get_post_meta( $post->ID, '_aps_badge_text', true ),
			
			// Group 8: Additional Information
			'warranty' => get_post_meta( $post->ID, '_aps_warranty', true ),
			
			// Group 9: Product Scheduling
			'release_date'    => get_post_meta( $post->ID, '_aps_release_date', true ),
			'expiration_date' => get_post_meta( $post->ID, '_aps_expiration_date', true ),
			
			// Group 10: Display Settings
			'display_order'    => get_post_meta( $post->ID, '_aps_display_order', true ),
			'hide_from_home'   => get_post_meta( $post->ID, '_aps_hide_from_home', true ),
		];
		
		// Include meta box template
		require Constants::viewPath( 'src/Admin/partials/product-meta-box.php' );
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		if ( Constants::CPT_PRODUCT !== $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['aps_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aps_meta_box_nonce'] ) ), 'aps_meta_box' ) ) {
			return;
		}

		// Save categories
		if ( isset( $_POST['aps_categories'] ) && is_array( $_POST['aps_categories'] ) ) {
			$category_ids = array_map( 'intval', $_POST['aps_categories'] );
			wp_set_object_terms( $post_id, $category_ids, Constants::TAX_CATEGORY );
		}

		// Save tags
		if ( isset( $_POST['aps_tags'] ) && is_array( $_POST['aps_tags'] ) ) {
			$tag_ids = array_map( 'intval', $_POST['aps_tags'] );
			wp_set_object_terms( $post_id, $tag_ids, Constants::TAX_TAG );
		}

		// Save ribbons (multiple selection)
		if ( isset( $_POST['aps_ribbons'] ) && is_array( $_POST['aps_ribbons'] ) ) {
			$ribbon_ids = array_map( 'intval', $_POST['aps_ribbons'] );
			wp_set_object_terms( $post_id, $ribbon_ids, Constants::TAX_RIBBON );
		} else {
			// Clear all ribbons if none selected
			wp_set_object_terms( $post_id, [], Constants::TAX_RIBBON );
		}

		// Group 1: Product Information
		$sku = sanitize_text_field( wp_unslash( $_POST['aps_sku'] ?? '' ) );
		$brand = isset( $_POST['aps_brand'] ) ? intval( wp_unslash( $_POST['aps_brand'] ) ) : 0;
		$short_description = sanitize_textarea_field( wp_unslash( $_POST['aps_short_description'] ?? '' ) );

		// Group 2: Pricing
		$regular_price = isset( $_POST['aps_regular_price'] ) ? (float) wp_unslash( $_POST['aps_regular_price'] ) : 0;
		$sale_price = isset( $_POST['aps_sale_price'] ) ? (float) wp_unslash( $_POST['aps_sale_price'] ) : 0;
		$discount_percentage = isset( $_POST['aps_discount_percentage'] ) ? (float) wp_unslash( $_POST['aps_discount_percentage'] ) : null;
		$currency = sanitize_text_field( wp_unslash( $_POST['aps_currency'] ?? 'USD' ) );

		// Group 3: Product Data
		$stock_status = sanitize_text_field( wp_unslash( $_POST['aps_stock_status'] ?? 'instock' ) );
		$availability_date = sanitize_text_field( wp_unslash( $_POST['aps_availability_date'] ?? '' ) );
		$rating = isset( $_POST['aps_rating'] ) ? (float) wp_unslash( $_POST['aps_rating'] ) : null;
		$review_count = isset( $_POST['aps_review_count'] ) ? intval( wp_unslash( $_POST['aps_review_count'] ) ) : 0;

		// Group 3b: Digital Product Information
		$version_number = sanitize_text_field( wp_unslash( $_POST['aps_version_number'] ?? '' ) );
		$platform_requirements = sanitize_textarea_field( wp_unslash( $_POST['aps_platform_requirements'] ?? '' ) );

		// Group 4: Product Media
		$video_url = esc_url_raw( wp_unslash( $_POST['aps_video_url'] ?? '' ) );

		// Group 5: Shipping & Dimensions
		$weight = isset( $_POST['aps_weight'] ) ? (float) wp_unslash( $_POST['aps_weight'] ) : null;
		$length = isset( $_POST['aps_length'] ) ? (float) wp_unslash( $_POST['aps_length'] ) : null;
		$width = isset( $_POST['aps_width'] ) ? (float) wp_unslash( $_POST['aps_width'] ) : null;
		$height = isset( $_POST['aps_height'] ) ? (float) wp_unslash( $_POST['aps_height'] ) : null;

		// Group 6: Affiliate & Links
		$affiliate_url = esc_url_raw( wp_unslash( $_POST['aps_affiliate_url'] ?? '' ) );
		$coupon_url = esc_url_raw( wp_unslash( $_POST['aps_coupon_url'] ?? '' ) );

		// Group 7: Product Ribbons
		$featured = isset( $_POST['aps_featured'] );
		// Ribbons are now saved via taxonomy above
		$badge_text = sanitize_text_field( wp_unslash( $_POST['aps_badge_text'] ?? '' ) );

		// Group 8: Additional Information
		$warranty = sanitize_textarea_field( wp_unslash( $_POST['aps_warranty'] ?? '' ) );

		// Group 9: Product Scheduling
		$release_date = sanitize_text_field( wp_unslash( $_POST['aps_release_date'] ?? '' ) );
		$expiration_date = sanitize_text_field( wp_unslash( $_POST['aps_expiration_date'] ?? '' ) );

		// Group 10: Display Settings
		$display_order = isset( $_POST['aps_display_order'] ) ? intval( wp_unslash( $_POST['aps_display_order'] ) ) : 0;
		$hide_from_home = isset( $_POST['aps_hide_from_home'] );

		// Save all meta fields
		update_post_meta( $post_id, '_aps_sku', $sku );
		update_post_meta( $post_id, '_aps_brand', $brand );
		update_post_meta( $post_id, '_aps_short_description', $short_description );
		update_post_meta( $post_id, '_aps_regular_price', $regular_price );
		update_post_meta( $post_id, '_aps_sale_price', $sale_price );
		update_post_meta( $post_id, '_aps_discount_percentage', $discount_percentage );
		update_post_meta( $post_id, '_aps_currency', $currency );
		update_post_meta( $post_id, '_aps_stock_status', $stock_status );
		update_post_meta( $post_id, '_aps_availability_date', $availability_date );
		update_post_meta( $post_id, '_aps_rating', $rating );
		update_post_meta( $post_id, '_aps_review_count', $review_count );
		update_post_meta( $post_id, '_aps_version_number', $version_number );
		update_post_meta( $post_id, '_aps_platform_requirements', $platform_requirements );
		update_post_meta( $post_id, '_aps_video_url', $video_url );
		update_post_meta( $post_id, '_aps_weight', $weight );
		update_post_meta( $post_id, '_aps_length', $length );
		update_post_meta( $post_id, '_aps_width', $width );
		update_post_meta( $post_id, '_aps_height', $height );
		update_post_meta( $post_id, '_aps_affiliate_url', $affiliate_url );
		update_post_meta( $post_id, '_aps_coupon_url', $coupon_url );
		update_post_meta( $post_id, '_aps_featured', $featured );
		update_post_meta( $post_id, '_aps_badge_text', $badge_text );
		update_post_meta( $post_id, '_aps_warranty', $warranty );
		update_post_meta( $post_id, '_aps_release_date', $release_date );
		update_post_meta( $post_id, '_aps_expiration_date', $expiration_date );
		update_post_meta( $post_id, '_aps_display_order', $display_order );
		update_post_meta( $post_id, '_aps_hide_from_home', $hide_from_home );
	}

}
