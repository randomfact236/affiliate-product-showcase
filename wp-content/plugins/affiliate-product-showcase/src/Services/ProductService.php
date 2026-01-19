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
				'name'               => __( 'Products', Constants::TEXTDOMAIN ),
				'singular_name'      => __( 'Product', Constants::TEXTDOMAIN ),
				'menu_name'          => __( 'Affiliate Products', Constants::TEXTDOMAIN ),
				'all_items'          => __( 'All Products', Constants::TEXTDOMAIN ),
				'add_new_item'       => __( 'Add Product', Constants::TEXTDOMAIN ),
				'edit_item'          => __( 'Edit Product', Constants::TEXTDOMAIN ),
				'new_item'           => __( 'New Product', Constants::TEXTDOMAIN ),
				'view_item'          => __( 'View Product', Constants::TEXTDOMAIN ),
				'search_items'       => __( 'Search Products', Constants::TEXTDOMAIN ),
				'not_found'          => __( 'No products found', Constants::TEXTDOMAIN ),
				'not_found_in_trash' => __( 'No products found in trash', Constants::TEXTDOMAIN ),
			],
				'public'              => true,
				'show_in_rest'        => true,
				'supports'            => [ 'title', 'editor', 'thumbnail' ],
				'rewrite'             => [ 'slug' => 'affiliate-product' ],
				'has_archive'         => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => true,
				'capability_type'     => 'post',
				'show_in_menu'        => true,
				'menu_position'       => 55,
				'taxonomies'          => [ Constants::TAX_CATEGORY, Constants::TAX_TAG, Constants::TAX_RIBBON ],
			]
		);
	}

	/**
	 * Register product taxonomies
	 *
	 * @return void
	 */
	public function register_taxonomies(): void {
		// Register Category taxonomy (hierarchical)
		register_taxonomy(
			Constants::TAX_CATEGORY,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                       => __( 'Categories', Constants::TEXTDOMAIN ),
					'singular_name'              => __( 'Category', Constants::TEXTDOMAIN ),
					'search_items'               => __( 'Search Categories', Constants::TEXTDOMAIN ),
					'all_items'                  => __( 'All Categories', Constants::TEXTDOMAIN ),
					'parent_item'                => __( 'Parent Category', Constants::TEXTDOMAIN ),
					'parent_item_colon'          => __( 'Parent Category:', Constants::TEXTDOMAIN ),
					'edit_item'                  => __( 'Edit Category', Constants::TEXTDOMAIN ),
					'update_item'                => __( 'Update Category', Constants::TEXTDOMAIN ),
					'add_new_item'               => __( 'Add New Category', Constants::TEXTDOMAIN ),
					'new_item_name'              => __( 'New Category Name', Constants::TEXTDOMAIN ),
					'menu_name'                  => __( 'Categories', Constants::TEXTDOMAIN ),
				],
				'hierarchical'               => true,
				'public'                     => true,
				'show_in_rest'               => true,
				'show_ui'                    => true,
				'show_admin_column'           => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'rewrite'                    => [ 'slug' => 'product-category' ],
			]
		);

		// Register Tag taxonomy (non-hierarchical)
		register_taxonomy(
			Constants::TAX_TAG,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                       => __( 'Tags', Constants::TEXTDOMAIN ),
					'singular_name'              => __( 'Tag', Constants::TEXTDOMAIN ),
					'search_items'               => __( 'Search Tags', Constants::TEXTDOMAIN ),
					'all_items'                  => __( 'All Tags', Constants::TEXTDOMAIN ),
					'edit_item'                  => __( 'Edit Tag', Constants::TEXTDOMAIN ),
					'update_item'                => __( 'Update Tag', Constants::TEXTDOMAIN ),
					'add_new_item'               => __( 'Add New Tag', Constants::TEXTDOMAIN ),
					'new_item_name'              => __( 'New Tag Name', Constants::TEXTDOMAIN ),
					'menu_name'                  => __( 'Tags', Constants::TEXTDOMAIN ),
				],
				'hierarchical'               => false,
				'public'                     => true,
				'show_in_rest'               => true,
				'show_ui'                    => true,
				'show_admin_column'           => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'rewrite'                    => [ 'slug' => 'product-tag' ],
			]
		);

		// Register Ribbon taxonomy (non-hierarchical, for badges/labels)
		register_taxonomy(
			Constants::TAX_RIBBON,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                       => __( 'Ribbons', Constants::TEXTDOMAIN ),
					'singular_name'              => __( 'Ribbon', Constants::TEXTDOMAIN ),
					'search_items'               => __( 'Search Ribbons', Constants::TEXTDOMAIN ),
					'all_items'                  => __( 'All Ribbons', Constants::TEXTDOMAIN ),
					'edit_item'                  => __( 'Edit Ribbon', Constants::TEXTDOMAIN ),
					'update_item'                => __( 'Update Ribbon', Constants::TEXTDOMAIN ),
					'add_new_item'               => __( 'Add New Ribbon', Constants::TEXTDOMAIN ),
					'new_item_name'              => __( 'New Ribbon Name', Constants::TEXTDOMAIN ),
					'menu_name'                  => __( 'Ribbons', Constants::TEXTDOMAIN ),
				],
				'hierarchical'               => false,
				'public'                     => true,
				'show_in_rest'               => true,
				'show_ui'                    => true,
				'show_admin_column'           => true,
				'show_in_nav_menus'          => false,
				'show_tagcloud'              => false,
				'rewrite'                    => [ 'slug' => 'product-ribbon' ],
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
