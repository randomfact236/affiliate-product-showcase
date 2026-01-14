<?php

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Services\ProductService;

final class MetaBoxes {
	public function __construct( private ProductService $product_service ) {}

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
		$meta = [
			'price'         => get_post_meta( $post->ID, 'aps_price', true ),
			'currency'      => get_post_meta( $post->ID, 'aps_currency', true ),
			'affiliate_url' => get_post_meta( $post->ID, 'aps_affiliate_url', true ),
			'image_url'     => get_post_meta( $post->ID, 'aps_image_url', true ),
			'rating'        => get_post_meta( $post->ID, 'aps_rating', true ),
			'badge'         => get_post_meta( $post->ID, 'aps_badge', true ),
		];

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

		$price         = isset( $_POST['aps_price'] ) ? (float) wp_unslash( $_POST['aps_price'] ) : 0;
		$currency      = sanitize_text_field( wp_unslash( $_POST['aps_currency'] ?? 'USD' ) );
		$affiliate_url = esc_url_raw( wp_unslash( $_POST['aps_affiliate_url'] ?? '' ) );
		$image_url     = esc_url_raw( wp_unslash( $_POST['aps_image_url'] ?? '' ) );
		$rating        = isset( $_POST['aps_rating'] ) ? (float) wp_unslash( $_POST['aps_rating'] ) : null;
		$badge         = sanitize_text_field( wp_unslash( $_POST['aps_badge'] ?? '' ) );

		update_post_meta( $post_id, 'aps_price', $price );
		update_post_meta( $post_id, 'aps_currency', $currency );
		update_post_meta( $post_id, 'aps_affiliate_url', $affiliate_url );
		update_post_meta( $post_id, 'aps_image_url', $image_url );
		update_post_meta( $post_id, 'aps_rating', $rating );
		update_post_meta( $post_id, 'aps_badge', $badge );
	}
}
