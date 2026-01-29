<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Repositories\ProductRepository;

/**
 * Product Form Handler
 *
 * Handles form submission for the custom product editor.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class ProductFormHandler {

	/**
	 * Product repository
	 *
	 * @var ProductRepository
	 */
	private ProductRepository $repository;

	/**
	 * Product factory
	 *
	 * @var ProductFactory
	 */
	private ProductFactory $factory;

	/**
	 * Constructor
	 *
	 * @param ProductRepository $repository Product repository
	 * @param ProductFactory     $factory     Product factory
	 */
	public function __construct(
		ProductRepository $repository,
		ProductFactory $factory
	) {
		$this->repository = $repository;
		$this->factory    = $factory;

		// Use admin_post_{action} hook for form submissions
		add_action( 'admin_post_aps_save_product', [ $this, 'handle_form_submission' ] );
		add_action( 'admin_post_aps_update_product', [ $this, 'handle_form_submission' ] );
	}

	/**
	 * Handle form submission
	 *
	 * Handles both creating new products and updating existing products.
	 *
	 * @return void
	 */
	public function handle_form_submission(): void {
		// Check if this is our form submission
		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		
		if ( ! in_array( $action, [ 'aps_save_product', 'aps_update_product' ], true ) ) {
			return;
		}

		// Determine nonce action based on form action
		$nonce_action = $action === 'aps_update_product' ? 'aps_update_product' : 'aps_save_product';

		// Verify nonce
		if ( ! isset( $_POST['aps_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aps_product_nonce'] ) ), $nonce_action ) ) {
			wp_die( esc_html__( 'Security check failed. Please try again.', 'affiliate-product-showcase' ) );
		}

		// Check user permissions
		if ( ! current_user_can( 'publish_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to save products.', 'affiliate-product-showcase' ) );
		}

		// Sanitize and validate input
		$data = $this->sanitize_form_data( $_POST );

		// Validate required fields
		$errors = $this->validate_data( $data );
		if ( ! empty( $errors ) ) {
			$this->handle_errors( $errors );
			return;
		}

		// Check if we're updating an existing product
		$is_update = isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] );
		
		if ( $is_update ) {
			$data['post_id'] = (int) $_POST['post_id'];
			$product = $this->update_product( $data );
			$message = 2; // Updated
		} else {
			$product = $this->create_product( $data );
			$message = 1; // Created
		}

		if ( is_wp_error( $product ) ) {
			wp_die( esc_html( $product->get_error_message() ) );
		}

		// Redirect to product list with success message
		wp_safe_redirect(
			admin_url( 'edit.php?post_type=aps_product&message=' . $message )
		);
		exit;
	}

	/**
	 * Sanitize form data
	 *
	 * @param array<string,mixed> $raw_data Raw form data
	 * @return array<string,mixed> Sanitized data
	 */
	private function sanitize_form_data( array $raw_data ): array {
		$data = [];

		// Basic fields
		$data['title']             = isset( $raw_data['aps_title'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_title'] ) ) : '';
		$data['description']       = isset( $raw_data['aps_short_description'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_short_description'] ) ) : '';
		$data['short_description'] = isset( $raw_data['aps_short_description'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_short_description'] ) ) : '';
		$data['affiliate_url']     = isset( $raw_data['aps_affiliate_url'] ) ? esc_url_raw( wp_unslash( $raw_data['aps_affiliate_url'] ) ) : '';
		$data['image_url']     = isset( $raw_data['aps_image_url'] ) ? esc_url_raw( wp_unslash( $raw_data['aps_image_url'] ) ) : '';
		$data['video_url']     = isset( $raw_data['aps_video_url'] ) ? esc_url_raw( wp_unslash( $raw_data['aps_video_url'] ) ) : '';
		$data['logo']          = isset( $raw_data['aps_image_url'] ) ? esc_url_raw( wp_unslash( $raw_data['aps_image_url'] ) ) : '';

		// Brand image
		$data['brand_image'] = isset( $raw_data['aps_brand_image_url'] ) ? esc_url_raw( wp_unslash( $raw_data['aps_brand_image_url'] ) ) : '';

		// Button name
		$data['button_name'] = isset( $raw_data['aps_button_name'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_button_name'] ) ) : 'Buy Now';

		// User count
		$data['user_count'] = isset( $raw_data['aps_user_count'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_user_count'] ) ) : '';

		// Views count
		$data['views'] = isset( $raw_data['aps_views'] ) ? intval( $raw_data['aps_views'] ) : 0;

		// Reviews count
		$data['reviews'] = isset( $raw_data['aps_reviews'] ) ? intval( $raw_data['aps_reviews'] ) : 0;

		// Features (JSON array)
		$data['features'] = isset( $raw_data['aps_features'] ) ? $this->sanitize_features_array( wp_unslash( $raw_data['aps_features'] ) ) : [];

		// Ribbons (taxonomy)
		$data['ribbons'] = isset( $raw_data['aps_ribbons'] ) ? $this->sanitize_comma_list( wp_unslash( $raw_data['aps_ribbons'] ) ) : [];

		// Pricing
		$data['regular_price']     = isset( $raw_data['aps_regular_price'] ) ? floatval( $raw_data['aps_regular_price'] ) : 0.0;
		$data['sale_price']        = isset( $raw_data['aps_sale_price'] ) && ! empty( $raw_data['aps_sale_price'] ) ? floatval( $raw_data['aps_sale_price'] ) : null;
		$data['discount_percentage'] = isset( $raw_data['aps_discount_percentage'] ) && ! empty( $raw_data['aps_discount_percentage'] ) ? floatval( $raw_data['aps_discount_percentage'] ) : null;
		$data['currency']           = isset( $raw_data['aps_currency'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_currency'] ) ) : 'USD';

		// Digital product info
		$data['platform_requirements'] = isset( $raw_data['aps_platform_requirements'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_platform_requirements'] ) ) : '';
		$data['version_number']       = isset( $raw_data['aps_version_number'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_version_number'] ) ) : '';

		// Rating
		$data['rating'] = isset( $raw_data['aps_rating'] ) && ! empty( $raw_data['aps_rating'] ) ? floatval( $raw_data['aps_rating'] ) : null;

		// Gallery
		$data['gallery'] = isset( $raw_data['aps_gallery'] ) ? $this->sanitize_gallery_urls( wp_unslash( $raw_data['aps_gallery'] ) ) : [];

		// Status
		$data['status']       = isset( $raw_data['aps_status'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_status'] ) ) : 'draft';
		$data['stock_status'] = isset( $raw_data['aps_stock_status'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_stock_status'] ) ) : 'instock';

		// Featured
		$data['featured'] = isset( $raw_data['aps_featured'] ) ? (bool) $raw_data['aps_featured'] : false;

		// Categories and tags
		$data['categories'] = isset( $raw_data['aps_categories'] ) ? $this->sanitize_comma_list( wp_unslash( $raw_data['aps_categories'] ) ) : [];
		$data['tags']       = isset( $raw_data['aps_tags'] ) ? $this->sanitize_comma_list( wp_unslash( $raw_data['aps_tags'] ) ) : [];

		// SEO
		$data['seo_title']       = isset( $raw_data['aps_seo_title'] ) ? sanitize_text_field( wp_unslash( $raw_data['aps_seo_title'] ) ) : '';
		$data['seo_description'] = isset( $raw_data['aps_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_seo_description'] ) ) : '';

		// Draft vs Publish
		$data['is_draft'] = isset( $_POST['draft'] );

		return $data;
	}

	/**
	 * Sanitize gallery URLs
	 *
	 * @param string $gallery_text Gallery text (one URL per line)
	 * @return array<string> Sanitized URLs
	 */
	private function sanitize_gallery_urls( string $gallery_text ): array {
		$urls = array_filter(
			array_map( 'trim', explode( "\n", $gallery_text ) ),
			fn( $url ) => ! empty( $url )
		);

		return array_map( 'esc_url_raw', $urls );
	}

	/**
	 * Sanitize comma-separated list or array
	 *
	 * @param string|array $list_text List text or array of items
	 * @return array<string> Sanitized items
	 */
	private function sanitize_comma_list( $list_text ): array {
		// If it's already an array, sanitize each item
		if ( is_array( $list_text ) ) {
			return array_map( 'sanitize_text_field', array_filter( $list_text ) );
		}
		
		// If it's a string, split by comma
		if ( is_string( $list_text ) ) {
			$items = array_filter(
				array_map( 'trim', explode( ',', $list_text ) ),
				fn( $item ) => ! empty( $item )
			);

			return array_map( 'sanitize_text_field', $items );
		}
		
		// Return empty array for any other type
		return [];
	}

	/**
	 * Sanitize features JSON array
	 *
	 * @param string $features_json JSON string of features
	 * @return array<array<string, mixed>> Sanitized features array
	 */
	private function sanitize_features_array( string $features_json ): array {
		// Decode JSON
		$features = json_decode( $features_json, true );
		
		// Validate it's an array
		if ( ! is_array( $features ) ) {
			return [];
		}
		
		// Sanitize each feature
		$sanitized_features = [];
		foreach ( $features as $feature ) {
			// Ensure feature is an array with required fields
			if ( ! is_array( $feature ) ) {
				continue;
			}
			
			$sanitized_features[] = [
				'text'       => isset( $feature['text'] ) ? sanitize_text_field( $feature['text'] ) : '',
				'highlighted' => isset( $feature['highlighted'] ) ? (bool) $feature['highlighted'] : false,
			];
		}
		
		return $sanitized_features;
	}

	/**
	 * Validate form data
	 *
	 * @param array<string,mixed> $data Sanitized data
	 * @return array<string> Validation errors
	 */
	private function validate_data( array $data ): array {
		$errors = [];

		// Required fields
		if ( empty( $data['title'] ) ) {
			$errors['title'] = __( 'Product name is required.', 'affiliate-product-showcase' );
		}

		if ( empty( $data['affiliate_url'] ) ) {
			$errors['affiliate_url'] = __( 'Affiliate URL is required.', 'affiliate-product-showcase' );
		}

		// Validate URL format
		if ( ! empty( $data['affiliate_url'] ) && ! filter_var( $data['affiliate_url'], FILTER_VALIDATE_URL ) ) {
			$errors['affiliate_url'] = __( 'Invalid affiliate URL format.', 'affiliate-product-showcase' );
		}

		// Validate price
		if ( $data['regular_price'] < 0 ) {
			$errors['regular_price'] = __( 'Price must be greater than or equal to 0.', 'affiliate-product-showcase' );
		}

		// Validate sale price
		if ( null !== $data['sale_price'] && $data['sale_price'] > $data['regular_price'] ) {
			$errors['sale_price'] = __( 'Sale price must be less than regular price.', 'affiliate-product-showcase' );
		}

		// Validate rating
		if ( null !== $data['rating'] && ( $data['rating'] < 0 || $data['rating'] > 5 ) ) {
			$errors['rating'] = __( 'Rating must be between 0 and 5.', 'affiliate-product-showcase' );
		}

		// Validate short description length
		if ( strlen( $data['short_description'] ) > 200 ) {
			$errors['short_description'] = __( 'Short description must be less than 200 characters.', 'affiliate-product-showcase' );
		}

		return $errors;
	}

	/**
	 * Handle validation errors
	 *
	 * @param array<string> $errors Validation errors
	 * @return void
	 */
	private function handle_errors( array $errors ): void {
		$error_message = implode( "\n", $errors );

		add_action(
			'admin_notices',
			static function () use ( $error_message ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					wp_kses_post( nl2br( $error_message ) )
				);
			}
		);

		wp_die( esc_html( $error_message ) );
	}

	/**
	 * Create product from form data
	 *
	 * @param array<string,mixed> $data Form data
	 * @return int|WP_Error Product ID or error
	 */
	private function create_product( array $data ) {
		// Create product post
		$post_id = wp_insert_post(
			[
				'post_title'    => $data['title'],
				'post_content'  => $data['description'],
				'post_excerpt'   => $data['short_description'],
				'post_status'   => $data['is_draft'] ? 'draft' : $data['status'],
				'post_type'     => 'aps_product',
				'post_author'   => get_current_user_id(),
			],
			true // Return WP_Error on failure
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Save all meta data
		$this->save_product_meta( $post_id, $data );

		return $post_id;
	}

	/**
	 * Update existing product
	 *
	 * @param array<string,mixed> $data Form data including post_id
	 * @return int|WP_Error Updated product ID or error
	 */
	private function update_product( array $data ) {
		$post_id = $data['post_id'];
		
		// Verify the post exists and is a product
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'aps_product' ) {
			return new \WP_Error( 'invalid_product', __( 'Invalid product ID.', 'affiliate-product-showcase' ) );
		}

		// Update product post
		$result = wp_update_post(
			[
				'ID'           => $post_id,
				'post_title'   => $data['title'],
				'post_content' => $data['description'],
				'post_excerpt' => $data['short_description'],
				'post_status'  => $data['status'],
			],
			true // Return WP_Error on failure
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Save all meta data
		$this->save_product_meta( $post_id, $data );

		return $post_id;
	}

	/**
	 * Save product meta data
	 *
	 * @param int $post_id Product ID
	 * @param array<string,mixed> $data Form data
	 * @return void
	 */
	private function save_product_meta( int $post_id, array $data ): void {
		// Save basic meta fields
		update_post_meta( $post_id, '_aps_price', $data['regular_price'] );
		update_post_meta( $post_id, '_aps_currency', $data['currency'] );
		update_post_meta( $post_id, '_aps_affiliate_url', $data['affiliate_url'] );
		update_post_meta( $post_id, '_aps_image_url', $data['image_url'] );
		update_post_meta( $post_id, '_aps_video_url', $data['video_url'] );
		update_post_meta( $post_id, '_aps_rating', $data['rating'] );
		update_post_meta( $post_id, '_aps_featured', $data['featured'] );
		update_post_meta( $post_id, '_aps_stock_status', $data['stock_status'] );
		update_post_meta( $post_id, '_aps_seo_title', $data['seo_title'] );
		update_post_meta( $post_id, '_aps_seo_description', $data['seo_description'] );
		
		// Save logo
		update_post_meta( $post_id, '_aps_logo', $data['logo'] );

		// Handle sale price logic - preserve existing values if not provided
		$existing_sale_price = get_post_meta( $post_id, '_aps_sale_price', true );
		$existing_original_price = get_post_meta( $post_id, '_aps_original_price', true );
		
		if ( null !== $data['sale_price'] && ! empty( $data['sale_price'] ) ) {
			// Sale price provided - update both
			update_post_meta( $post_id, '_aps_original_price', $data['regular_price'] );
			update_post_meta( $post_id, '_aps_price', $data['sale_price'] );
			update_post_meta( $post_id, '_aps_sale_price', $data['sale_price'] );
		} elseif ( ! isset( $raw_data['aps_sale_price'] ) ) {
			// Sale price field not submitted - preserve existing values
			if ( $existing_original_price ) {
				update_post_meta( $post_id, '_aps_original_price', $existing_original_price );
			}
			if ( $existing_sale_price ) {
				update_post_meta( $post_id, '_aps_price', $existing_sale_price );
				update_post_meta( $post_id, '_aps_sale_price', $existing_sale_price );
			} else {
				// No existing sale price - use regular price
				update_post_meta( $post_id, '_aps_price', $data['regular_price'] );
				delete_post_meta( $post_id, '_aps_sale_price' );
			}
		} else {
			// Sale price is empty/null - remove sale pricing
			delete_post_meta( $post_id, '_aps_original_price' );
			update_post_meta( $post_id, '_aps_price', $data['regular_price'] );
			delete_post_meta( $post_id, '_aps_sale_price' );
		}

		// Save discount percentage if provided
		if ( null !== $data['discount_percentage'] && ! empty( $data['discount_percentage'] ) ) {
			update_post_meta( $post_id, '_aps_discount_percentage', $data['discount_percentage'] );
		} else {
			delete_post_meta( $post_id, '_aps_discount_percentage' );
		}

		// Save digital product info
		update_post_meta( $post_id, '_aps_platform_requirements', $data['platform_requirements'] );
		update_post_meta( $post_id, '_aps_version_number', $data['version_number'] );

		// Save gallery
		update_post_meta( $post_id, '_aps_gallery', $data['gallery'] );

		// Save brand image
		update_post_meta( $post_id, '_aps_brand_image', $data['brand_image'] );

		// Save button name
		update_post_meta( $post_id, '_aps_button_name', ! empty( $data['button_name'] ) ? $data['button_name'] : 'Buy Now' );

		// Save user count
		update_post_meta( $post_id, '_aps_user_count', $data['user_count'] );

		// Save views count
		update_post_meta( $post_id, '_aps_views', $data['views'] );

		// Save reviews count
		update_post_meta( $post_id, '_aps_reviews', $data['reviews'] );

		// Save features (JSON array)
		if ( ! empty( $data['features'] ) ) {
			update_post_meta( $post_id, '_aps_features', json_encode( $data['features'] ) );
		} else {
			delete_post_meta( $post_id, '_aps_features' );
		}

		// Save categories
		if ( ! empty( $data['categories'] ) ) {
			wp_set_object_terms( $post_id, $data['categories'], 'aps_category', false );
		} else {
			// Auto-assign default category if no categories specified
			$default_category_id = get_option( 'aps_default_category_id', 0 );
			if ( $default_category_id > 0 ) {
				wp_set_object_terms( $post_id, [ (int) $default_category_id ], 'aps_category', false );
			}
		}

		// Save tags
		if ( ! empty( $data['tags'] ) ) {
			wp_set_object_terms( $post_id, $data['tags'], 'aps_tag', false );
		} else {
			wp_delete_object_term_relationships( $post_id, 'aps_tag' );
		}

		// Save ribbons
		if ( ! empty( $data['ribbons'] ) ) {
			wp_set_object_terms( $post_id, $data['ribbons'], 'aps_ribbon', false );
		} else {
			wp_delete_object_term_relationships( $post_id, 'aps_ribbon' );
		}

		// Set featured image if URL provided
		if ( ! empty( $data['image_url'] ) ) {
			$this->set_featured_image_from_url( $post_id, $data['image_url'] );
		}
	}

	/**
	 * Set featured image from URL
	 *
	 * @param int    $post_id Post ID
	 * @param string $image_url Image URL
	 * @return void
	 */
	private function set_featured_image_from_url( int $post_id, string $image_url ): void {
		// Check if image is already in media library
		$existing_attachment = $this->get_attachment_by_url( $image_url );
		if ( $existing_attachment ) {
			set_post_thumbnail( $post_id, $existing_attachment );
			return;
		}

		// Download and save image
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			return;
		}

		$file_array = [
			'name'     => basename( parse_url( $image_url, PHP_URL_PATH ) ),
			'tmp_name' => $tmp,
		];

		$id = media_handle_sideload( $file_array );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
			return;
		}

		set_post_thumbnail( $post_id, $id );
		@unlink( $tmp );
	}

	/**
	 * Get attachment ID by URL
	 *
	 * @param string $url Image URL
	 * @return int|null Attachment ID or null
	 */
	private function get_attachment_by_url( string $url ): ?int {
		global $wpdb;

		$attachment_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
				'%' . basename( parse_url( $url, PHP_URL_PATH ) ) . '%'
			)
		);

		return $attachment_id ? (int) $attachment_id : null;
	}
}
