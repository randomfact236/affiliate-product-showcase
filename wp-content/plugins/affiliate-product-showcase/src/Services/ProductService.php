<?php

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Abstracts\AbstractService;
use AffiliateProductShowcase\Exceptions\PluginException;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Formatters\PriceFormatter;
use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Validators\ProductValidator;

final class ProductService extends AbstractService {
	private ProductRepository $repository;
	private ProductValidator $validator;
	private ProductFactory $factory;
	private PriceFormatter $formatter;

	public function __construct() {
		$this->repository = new ProductRepository();
		$this->validator  = new ProductValidator();
		$this->factory    = new ProductFactory();
		$this->formatter  = new PriceFormatter();
	}

	public function boot(): void {}

	public function register_post_type(): void {
		register_post_type(
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'          => __( 'Affiliate Products', Constants::TEXTDOMAIN ),
					'singular_name' => __( 'Affiliate Product', Constants::TEXTDOMAIN ),
				],
				'public'              => true,
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-cart',
				'supports'            => [ 'title', 'editor', 'thumbnail' ],
				'rewrite'             => [ 'slug' => 'affiliate-product' ],
				'has_archive'         => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => true,
				'capability_type'     => 'post',
			]
		);
	}

	public function get_product( int $id ): ?Product {
		return $this->repository->find( $id );
	}

	public function get_products( array $args = [] ): array {
		return $this->repository->list( $args );
	}

	public function create_or_update( array $data ): Product {
		$clean = $this->validator->validate( $data );
		$product = $this->factory->from_array( $clean );
		$id = $this->repository->save( $product );
		if ( ! $id ) {
			throw new PluginException( 'Unable to save product.' );
		}

		return $this->get_product( $id ) ?? $product;
	}

	public function delete( int $id ): bool {
		return $this->repository->delete( $id );
	}

	public function format_price( float $price, string $currency = 'USD' ): string {
		return $this->formatter->format( $price, $currency );
	}
}
