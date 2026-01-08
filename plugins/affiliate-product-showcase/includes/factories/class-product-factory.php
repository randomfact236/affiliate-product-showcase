<?php

namespace AffiliateProductShowcase\Factories;

use AffiliateProductShowcase\Models\Product;

class ProductFactory {
	public function from_array( array $data ) {
		$product = new Product();
		if ( isset( $data['id'] ) ) {
			$product->id = (int) $data['id'];
		}
		if ( isset( $data['title'] ) ) {
			$product->title = (string) $data['title'];
		}
		return $product;
	}
}
