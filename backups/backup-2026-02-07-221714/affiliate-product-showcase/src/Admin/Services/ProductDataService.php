<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Services;

/**
 * Product Data Service
 *
 * Handles all product data retrieval and manipulation operations.
 * Separates data access logic from presentation layer.
 *
 * @package AffiliateProductShowcase\Admin\Services
 * @since 1.0.0
 */
class ProductDataService {

	/**
	 * Get complete product data for editing
	 *
	 * @param int $post_id The product post ID
	 * @return array|null Product data array or null if not found
	 */
	public function getProductData(int $post_id): ?array {
		$post = get_post($post_id);

		if (!$post || $post->post_type !== 'aps_product') {
			return null;
		}

		return [
			'id' => $post->ID,
			'title' => $post->post_title,
			'status' => $post->post_status,
			'content' => $post->post_content,
			'short_description' => $post->post_excerpt,
			// Meta fields
			'logo' => get_post_meta($post->ID, '_aps_logo', true),
			'brand_image' => get_post_meta($post->ID, '_aps_brand_image', true),
			'affiliate_url' => get_post_meta($post->ID, '_aps_affiliate_url', true),
			'button_name' => get_post_meta($post->ID, '_aps_button_name', true),
			'regular_price' => get_post_meta($post->ID, '_aps_price', true),
			'original_price' => get_post_meta($post->ID, '_aps_original_price', true),
			'currency' => get_post_meta($post->ID, '_aps_currency', true) ?: 'USD',
			'featured' => get_post_meta($post->ID, '_aps_featured', true) === '1',
			'rating' => get_post_meta($post->ID, '_aps_rating', true),
			'views' => get_post_meta($post->ID, '_aps_views', true),
			'user_count' => get_post_meta($post->ID, '_aps_user_count', true),
			'reviews' => get_post_meta($post->ID, '_aps_reviews', true),
			'features' => json_decode(get_post_meta($post->ID, '_aps_features', true) ?: '[]', true),
			'video_url' => get_post_meta($post->ID, '_aps_video_url', true),
			'platform_requirements' => get_post_meta($post->ID, '_aps_platform_requirements', true),
			'version_number' => get_post_meta($post->ID, '_aps_version_number', true),
			'stock_status' => get_post_meta($post->ID, '_aps_stock_status', true) ?: 'instock',
			'seo_title' => get_post_meta($post->ID, '_aps_seo_title', true),
			'seo_description' => get_post_meta($post->ID, '_aps_seo_description', true),
		];
	}

	/**
	 * Get product taxonomies (categories, tags, ribbons)
	 *
	 * @param int $post_id The product post ID
	 * @return array Taxonomy data
	 */
	public function getProductTaxonomies(int $post_id): array {
		return [
			'categories' => wp_get_object_terms($post_id, 'aps_category', ['fields' => 'slugs']),
			'tags' => wp_get_object_terms($post_id, 'aps_tag', ['fields' => 'slugs']),
			'ribbons' => wp_get_object_terms($post_id, 'aps_ribbon', ['fields' => 'slugs']),
		];
	}

	/**
	 * Get complete product data including taxonomies
	 *
	 * @param int $post_id The product post ID
	 * @return array|null Complete product data or null if not found
	 */
	public function getCompleteProductData(int $post_id): ?array {
		$productData = $this->getProductData($post_id);

		if (!$productData) {
			return null;
		}

		$taxonomies = $this->getProductTaxonomies($post_id);

		return array_merge($productData, $taxonomies);
	}

	/**
	 * Get all categories
	 *
	 * @return array List of category terms
	 */
	public function getCategories(): array {
		return get_terms(['taxonomy' => 'aps_category', 'hide_empty' => false]);
	}

	/**
	 * Get all ribbons
	 *
	 * @return array List of ribbon terms
	 */
	public function getRibbons(): array {
		return get_terms(['taxonomy' => 'aps_ribbon', 'hide_empty' => false]);
	}

	/**
	 * Get all tags
	 *
	 * @return array List of tag terms
	 */
	public function getTags(): array {
		return get_terms(['taxonomy' => 'aps_tag', 'hide_empty' => false]);
	}

	/**
	 * Get category metadata
	 *
	 * @param int $term_id The term ID
	 * @return array Category metadata
	 */
	public function getCategoryMetadata(int $term_id): array {
		return [
			'image' => get_term_meta($term_id, '_aps_category_image', true),
			'featured' => get_term_meta($term_id, '_aps_category_featured', true) === '1',
		];
	}

	/**
	 * Get ribbon metadata
	 *
	 * @param int $term_id The term ID
	 * @return array Ribbon metadata
	 */
	public function getRibbonMetadata(int $term_id): array {
		return [
			'color' => get_term_meta($term_id, '_aps_ribbon_color', true) ?: '#ff6b6b',
			'bg_color' => get_term_meta($term_id, '_aps_ribbon_bg_color', true) ?: '#ff0000',
			'icon' => get_term_meta($term_id, '_aps_ribbon_icon', true) ?: '',
		];
	}

	/**
	 * Validate product data
	 *
	 * @param array $data Product data to validate
	 * @return array Validation errors
	 */
	public function validateProductData(array $data): array {
		$errors = [];

		// Required fields
		if (empty($data['title'])) {
			$errors['title'] = __('Product title is required.', 'affiliate-product-showcase');
		}

		if (empty($data['short_description'])) {
			$errors['short_description'] = __('Short description is required.', 'affiliate-product-showcase');
		}

		if (empty($data['regular_price'])) {
			$errors['regular_price'] = __('Current price is required.', 'affiliate-product-showcase');
		}

		// Validate price format
		if (!empty($data['regular_price']) && !is_numeric($data['regular_price'])) {
			$errors['regular_price'] = __('Current price must be a number.', 'affiliate-product-showcase');
		}

		if (!empty($data['original_price']) && !is_numeric($data['original_price'])) {
			$errors['original_price'] = __('Original price must be a number.', 'affiliate-product-showcase');
		}

		// Validate rating
		if (!empty($data['rating'])) {
			$rating = floatval($data['rating']);
			if ($rating < 0 || $rating > 5) {
				$errors['rating'] = __('Rating must be between 0 and 5.', 'affiliate-product-showcase');
			}
		}

		// Validate URL fields
		if (!empty($data['affiliate_url']) && !filter_var($data['affiliate_url'], FILTER_VALIDATE_URL)) {
			$errors['affiliate_url'] = __('Affiliate URL must be a valid URL.', 'affiliate-product-showcase');
		}

		if (!empty($data['logo']) && !filter_var($data['logo'], FILTER_VALIDATE_URL)) {
			$errors['logo'] = __('Logo URL must be a valid URL.', 'affiliate-product-showcase');
		}

		return $errors;
	}
}
