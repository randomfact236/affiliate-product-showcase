<?php

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
		public string $affiliate_url,
		public ?string $image_url = null,
		public ?float $rating = null,
		public ?string $badge = null,
		public array $categories = []
	) {}

	public function to_array(): array {
		return [
			'id'            => $this->id,
			'title'         => $this->title,
			'slug'          => $this->slug,
			'description'   => $this->description,
			'currency'      => $this->currency,
			'price'         => $this->price,
			'affiliate_url' => $this->affiliate_url,
			'image_url'     => $this->image_url,
			'rating'        => $this->rating,
			'badge'         => $this->badge,
			'categories'    => $this->categories,
		];
	}
}
