<?php
/**
 * Product Service
 *
 * Core service for managing affiliate products including:
 * - Product creation, retrieval, and deletion
 * - Custom post type and taxonomy registration
 * - Product data validation and formatting
 * - Caching for improved performance
 *
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 */

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

/**
 * Product Service
 *
 * Core service for managing affiliate products including:
 * - Product creation, retrieval, and deletion
 * - Custom post type and taxonomy registration
 * - Product data validation and formatting
 * - Caching for improved performance
 *
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 * @author Development Team
 */
final class ProductService extends AbstractService {
	/**
	 * Product repository instance
	 *
	 * Handles database operations for products.
	 *
	 * @var ProductRepository
	 * @since 1.0.0
	 */
	private ProductRepository $repository;

	/**
	 * Product validator instance
	 *
	 * Validates product data before saving.
	 *
	 * @var ProductValidator
	 * @since 1.0.0
	 */
	private ProductValidator $validator;

	/**
	 * Product factory instance
	 *
	 * Creates Product objects from raw data.
	 *
	 * @var ProductFactory
	 * @since 1.0.0
	 */
	private ProductFactory $factory;

	/**
	 * Price formatter instance
	 *
	 * Formats prices with currency symbols.
	 *
	 * @var PriceFormatter
	 * @since 1.0.0
	 */
	private PriceFormatter $formatter;

	/**
	 * Cache instance
	 *
	 * Handles caching for product queries.
	 *
	 * @var Cache
	 * @since 1.0.0
	 */
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
	 * Boot service
	 *
	 * Initializes service. Currently empty as all initialization
	 * is handled in constructor.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {}

	/**
	 * Register product post type
	 *
	 * Registers 'aps_product' custom post type with WordPress.
	 * Configures labels, capabilities, and REST API support.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action init
	 */
	public function register_post_type(): void {
		// Debug: Log CPT registration
		error_log( '[APS] Registering CPT: ' . Constants::CPT_PRODUCT );
		
		$result = register_post_type(
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
		
		// Debug: Check if CPT registered successfully
		$registered = post_type_exists( Constants::CPT_PRODUCT );
		error_log( '[APS] CPT registered: ' . ( $registered ? 'YES' : 'NO' ) );
		
		if ( ! $registered ) {
			error_log( '[APS] ERROR: CPT registration failed!' );
		}
	}

	/**
	 * Register product taxonomies
	 *
	 * Registers three taxonomies for product organization:
	 * - Category (hierarchical)
	 * - Tag (non-hierarchical)
	 * - Ribbon (non-hierarchical, for badges/labels)
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action init
	 */
	public function register_taxonomies(): void {
		// Register Category taxonomy (hierarchical)
		register_taxonomy(
			Constants::TAX_CATEGORY,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                  => __( 'Categories', Constants::TEXTDOMAIN ),
					'singular_name'         => __( 'Category', Constants::TEXTDOMAIN ),
					'search_items'          => __( 'Search Categories', Constants::TEXTDOMAIN ),
					'all_items'             => __( 'All Categories', Constants::TEXTDOMAIN ),
					'parent_item'           => __( 'Parent Category', Constants::TEXTDOMAIN ),
					'parent_item_colon'     => __( 'Parent Category:', Constants::TEXTDOMAIN ),
					'edit_item'             => __( 'Edit Category', Constants::TEXTDOMAIN ),
					'update_item'           => __( 'Update Category', Constants::TEXTDOMAIN ),
					'add_new_item'          => __( 'Add New Category', Constants::TEXTDOMAIN ),
					'new_item_name'         => __( 'New Category Name', Constants::TEXTDOMAIN ),
					'menu_name'             => __( 'Categories', Constants::TEXTDOMAIN ),
				],
				'hierarchical'          => true,
				'public'               => true,
				'show_in_rest'         => true,
				'show_ui'              => true,
				'show_admin_column'      => true,
				'show_in_nav_menus'     => true,
				'show_tagcloud'         => true,
				'rewrite'              => [ 'slug' => 'product-category' ],
			]
		);

		// Register Tag taxonomy (non-hierarchical)
		register_taxonomy(
			Constants::TAX_TAG,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                  => __( 'Tags', Constants::TEXTDOMAIN ),
					'singular_name'         => __( 'Tag', Constants::TEXTDOMAIN ),
					'search_items'          => __( 'Search Tags', Constants::TEXTDOMAIN ),
					'all_items'             => __( 'All Tags', Constants::TEXTDOMAIN ),
					'edit_item'             => __( 'Edit Tag', Constants::TEXTDOMAIN ),
					'update_item'           => __( 'Update Tag', Constants::TEXTDOMAIN ),
					'add_new_item'          => __( 'Add New Tag', Constants::TEXTDOMAIN ),
					'new_item_name'         => __( 'New Tag Name', Constants::TEXTDOMAIN ),
					'menu_name'             => __( 'Tags', Constants::TEXTDOMAIN ),
				],
				'hierarchical'          => false,
				'public'               => true,
				'show_in_rest'         => true,
				'show_ui'              => true,
				'show_admin_column'      => true,
				'show_in_nav_menus'     => true,
				'show_tagcloud'         => true,
				'rewrite'              => [ 'slug' => 'product-tag' ],
			]
		);

		// Register Ribbon taxonomy (non-hierarchical, for badges/labels)
		register_taxonomy(
			Constants::TAX_RIBBON,
			Constants::CPT_PRODUCT,
			[
				'labels' => [
					'name'                  => __( 'Ribbons', Constants::TEXTDOMAIN ),
					'singular_name'         => __( 'Ribbon', Constants::TEXTDOMAIN ),
					'search_items'          => __( 'Search Ribbons', Constants::TEXTDOMAIN ),
					'all_items'             => __( 'All Ribbons', Constants::TEXTDOMAIN ),
					'edit_item'             => __( 'Edit Ribbon', Constants::TEXTDOMAIN ),
					'update_item'           => __( 'Update Ribbon', Constants::TEXTDOMAIN ),
					'add_new_item'          => __( 'Add New Ribbon', Constants::TEXTDOMAIN ),
					'new_item_name'         => __( 'New Ribbon Name', Constants::TEXTDOMAIN ),
					'menu_name'             => __( 'Ribbons', Constants::TEXTDOMAIN ),
				],
				'hierarchical'          => false,
				'public'               => true,
				'show_in_rest'         => true,
				'show_ui'              => true,
				'show_admin_column'      => true,
				'show_in_nav_menus'     => false,
				'show_tagcloud'         => false,
				'rewrite'              => [ 'slug' => 'product-ribbon' ],
			]
		);
	}

	/**
	 * Get a product by ID
	 *
	 * Retrieves a single product by its unique identifier.
	 * Returns null if product is not found.
	 *
	 * @param int $id Unique product identifier
	 * @return Product|null Product object or null if not found
	 * @since 1.0.0
	 */
	public function get_product( int $id ): ?Product {
		return $this->repository->find( $id );
	}

	/**
	 * Delete a product
	 *
	 * Permanently deletes a product from database.
	 * Returns false if product doesn't exist or deletion fails.
	 *
	 * @param int $id Unique product identifier
	 * @return bool True if deleted successfully, false otherwise
	 * @since 1.0.0
	 */
	public function delete( int $id ): bool {
		return $this->repository->delete( $id );
	}

	/**
	 * Restore a product from trash
	 *
	 * Restores a product that was previously trashed.
	 * Returns false if product doesn't exist or restoration fails.
	 *
	 * @param int $id Unique product identifier
	 * @return bool True if restored successfully, false otherwise
	 * @since 1.0.0
	 */
	public function restore( int $id ): bool {
		return $this->repository->restore( $id );
	}

	/**
	 * Format price with currency
	 *
	 * Formats a price value with appropriate currency symbol
	 * and number formatting.
	 *
	 * @param float $price Price value to format
	 * @param string $currency Currency code (default: USD)
	 * @return string Formatted price with currency symbol
	 * @since 1.0.0
	 */
	public function format_price( float $price, string $currency = 'USD' ): string {
		return $this->formatter->format( $price, $currency );
	}

	/**
	 * Create or update a product
	 *
	 * Validates and saves product data. Creates new product if ID not provided,
	 * otherwise updates existing product.
	 *
	 * @param array<string, mixed> $data Product data including title, price, affiliate_url, etc.
	 * @return Product Created or updated product object
	 * @throws PluginException If validation fails or unable to save product
	 * @since 1.0.0
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
}
