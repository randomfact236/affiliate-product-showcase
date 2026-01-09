<?php

namespace AffiliateProductShowcase\Cli;

use AffiliateProductShowcase\Services\ProductService;

final class ProductsCommand {
	public function __construct( private ProductService $product_service ) {}

	public function register(): void {
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		\WP_CLI::add_command( 'aps products', [ $this, 'list' ] );
	}

	public function list(): void {
		$products = $this->product_service->get_products( [ 'per_page' => 100 ] );
		$data     = array_map(
			static fn( $product ) => [
				'ID'    => $product->id,
				'Title' => $product->title,
				'Price' => $product->price,
			],
			$products
		);

		\WP_CLI::success( 'Products' );
		\WP_CLI\Utils\format_items( 'table', $data, [ 'ID', 'Title', 'Price' ] );
	}
}
