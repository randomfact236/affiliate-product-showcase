<?php
/**
 * Product Model
 *
 * Represents an affiliate product with all its properties and methods.
 * Handles product data structure and conversion to array format.
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Model
 *
 * Represents an affiliate product with all its properties and methods.
 * Handles product data structure and conversion to array format.
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 */
final class Product {
	/**
	 * Constructor
	 *
	 * Initializes a new Product instance with all required and optional properties.
	 * Uses promoted constructor properties for type safety and immutability.
	 *
	 * @param int $id Unique product identifier
	 * @param string $title Product title
	 * @param string $slug URL-friendly slug for product
	 * @param string $description Product description content
	 * @param string $short_description Brief product excerpt displayed in cards (max 200 chars)
	 * @param string $currency Currency code (e.g., USD, EUR)
	 * @param float $price Current product price
	 * @param float|null $original_price Original price before discount (optional)
	 * @param string $affiliate_url Affiliate link URL
	 * @param string|null $image_url Product image/logo URL (optional)
	 * @param float|null $rating Product rating 0-5 (optional)
	 * @param string|null $badge Badge/ribbon text (optional)
	 * @param bool $featured Whether product is featured (default: false)
	 * @param string $status Product status (publish, draft, trash)
	 * @param array<int, int> $category_ids Array of category IDs (optional)
	 * @param array<int, int> $tag_ids Array of tag IDs (optional)
	 * @param array<int, int> $ribbon_ids Array of ribbon IDs (optional)
	 * @since 1.0.0
	 */
	public function __construct(
		public int $id,
		public string $title,
		public string $slug,
		public string $description,
		public string $short_description = '',
		public string $currency,
		public float $price,
		public ?float $original_price = null,
		public ?float $discount_percentage = null,
		public string $affiliate_url,
		public ?string $image_url = null,
		public ?float $rating = null,
		public ?string $badge = null,
		public bool $featured = false,
		public string $status = 'publish',
		public array $category_ids = [],
		public array $tag_ids = [],
		public array $ribbon_ids = [],
		public ?string $platform_requirements = null,
		public ?string $version_number = null
	) {}

	/**
	 * Convert product to array
	 *
	 * Converts the Product object to an associative array format suitable for JSON responses.
	 * Includes both canonical field names and backward-compatible aliases.
	 *
	 * @return array<string, mixed> Product data as associative array
	 * @since 1.0.0
	 */
	public function to_array(): array {
		return [
			'id'                     => $this->id,
			'title'                  => $this->title,
			'slug'                   => $this->slug,
			'description'             => $this->description,
			'short_description'       => $this->short_description,
			'currency'                => $this->currency,
			'price'                   => $this->price,
			'original_price'          => $this->original_price,
			'discount_percentage'     => $this->discount_percentage,
			'affiliate_url'          => $this->affiliate_url,
			'affiliate_link'         => $this->affiliate_url, // Alias for React components
			'image_url'              => $this->image_url,
			'rating'                 => $this->rating,
			'badge'                  => $this->badge,
			'featured'               => $this->featured,
			'status'                 => $this->status,
			'category_ids'           => $this->category_ids,
			'categories'             => $this->category_ids, // Alias for backward compatibility
			'tag_ids'                => $this->tag_ids,
			'tags'                   => $this->tag_ids, // Alias for backward compatibility
			'ribbon_ids'             => $this->ribbon_ids,
			'ribbons'                => $this->ribbon_ids, // Alias for backward compatibility
			'platform_requirements'   => $this->platform_requirements,
			'version_number'         => $this->version_number,
		];
	}
}
