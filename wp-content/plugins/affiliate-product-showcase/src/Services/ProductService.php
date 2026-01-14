<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Abstracts\AbstractService;
use AffiliateProductShowcase\Cache\Cache;
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
	private Cache $cache;

	/**
	 * Constructor
	 *
	 * @param ProductRepository $repository Product repository
	 * @param ProductValidator $validator Product validator
	 * @param ProductFactory $factory Product factory
	 * @param PriceFormatter $formatter Price formatter
	 * @param Cache $cache Cache service
	 */
	public function __construct(
		ProductRepository $repository,
		ProductValidator $validator,
		ProductFactory $factory,
		PriceFormatter $formatter,
		Cache $cache
	) {
		$this->repository = $repository;
		$this->validator  = $validator;
		$this->factory    = $factory;
		$this->formatter  = $formatter;
		$this->cache      = $cache;
	}

	/**
	 * Boot the service
	 *
	 * @return void
	 */
	public function boot(): void {}

	/**
	 * Register the product post type
	 *
	 * @return void
	 */
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

	/**
	 * Get a product by ID
	 *
	 * @param int $id Product ID
	 * @return Product|null Product object or null if not found
	 */
	public function get_product( int $id ): ?Product {
		return $this->repository->find( $id );
	}

	/**
	 * Get list of products with caching
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @return array<int, Product> Array of products
	 */
	public function get_products( array $args = [] ): array {
		// Generate cache key from arguments
		$cache_key = 'products_' . md5( wp_json_encode( $args ) );
		
		// Try to get from cache first
		$cached = $this->cache->get( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}
		
		// Fetch from repository if not cached
		$products = $this->repository->list( $args );
		
		// Cache results for 5 minutes (300 seconds)
		$this->cache->set( $cache_key, $products, 300 );
		
		return $products;
	}

	/**
	 * Create or update a product
	 *
	 * @param array<string, mixed> $data Product data
	 * @return Product Created or updated product
	 * @throws PluginException If unable to save product
	 */
	public function create_or_update( array $data ): Product {
		$clean = $this->validator->validate( $data );
		$product = $this->factory->from_array( $clean );
		$id = $this->repository->save( $product );
		if ( ! $id ) {
			throw new PluginException( 'Unable to save product.' );
		}

		return $this->get_product( $id ) ?? $product;
	}

	/**
	 * Delete a product
	 *
	 * @param int $id Product ID
	 * @return bool True if deleted successfully
	 */
	public function delete( int $id ): bool {
		return $this->repository->delete( $id );
	}

	/**
	 * Format price with currency
	 *
	 * @param float $price Price value
	 * @param string $currency Currency code (default: USD)
	 * @return string Formatted price
	 */
	public function format_price( float $price, string $currency = 'USD' ): string {
		return $this->formatter->format( $price, $currency );
	}
}
