<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Product {
	public function __construct(
		public int $id,
		public string $title,
		public string $slug,
		public string $description,
		public string $currency,
		public float $price,
		public ?float $original_price = null,
		public string $affiliate_url,
		public ?string $image_url = null,
		public ?float $rating = null,
		public ?string $badge = null,
		public array $category_ids = [],
		public array $tag_ids = []
	) {}

	public function to_array(): array {
		return [
			'id'             => $this->id,
			'title'          => $this->title,
			'slug'           => $this->slug,
			'description'    => $this->description,
			'currency'       => $this->currency,
			'price'          => $this->price,
			'original_price'  => $this->original_price,
			'affiliate_url'   => $this->affiliate_url,
			'affiliate_link'  => $this->affiliate_url, // Alias for React components
			'image_url'      => $this->image_url,
			'rating'         => $this->rating,
			'badge'          => $this->badge,
			'category_ids'   => $this->category_ids,
			'categories'     => $this->category_ids, // Alias for backward compatibility
			'tag_ids'        => $this->tag_ids,
			'tags'           => $this->tag_ids, // Alias for backward compatibility
		];
	}
}
