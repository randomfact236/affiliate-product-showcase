<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Cli;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Services\ProductService;

final class ProductsCommand {
	/**
	 * Constructor
	 *
	 * @param ProductService $product_service Product service
	 */
	public function __construct( private ProductService $product_service ) {}

	/**
	 * Register WP-CLI commands
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		\WP_CLI::add_command( 'aps products', [ $this, 'list' ] );
	}

	/**
	 * List all products via WP-CLI
	 *
	 * @return void
	 */
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

		\WP_CLI::success( __( 'Products', 'affiliate-product-showcase' ) );
		\WP_CLI\Utils\format_items( 'table', $data, [ 
			'ID' => __( 'ID', 'affiliate-product-showcase' ),
			'Title' => __( 'Title', 'affiliate-product-showcase' ),
			'Price' => __( 'Price', 'affiliate-product-showcase' )
		] );
	}
}
