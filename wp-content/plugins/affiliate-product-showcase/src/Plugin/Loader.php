<?php
/**
 * Plugin Loader
 *
 * Registers all plugin hooks and services with WordPress.
 * Boots admin, public, and REST API functionality.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Admin\TermMeta;
use AffiliateProductShowcase\Admin\TermUI;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Rest\ProductsController;
use AffiliateProductShowcase\Rest\AffiliatesController;
use AffiliateProductShowcase\Rest\HealthController;
use AffiliateProductShowcase\Rest\TermsController;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Cli\ProductsCommand;

/**
 * Plugin Loader
 *
 * Main loader class that registers all plugin services.
 * Coordinates admin, public, REST API, and CLI components.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 * @author Development Team
 */
final class Loader {
	/**
	 * Admin service instance
	 *
	 * @var Admin
	 * @since 1.0.0
	 */
	private Admin $admin;

	/**
	 * Public service instance
	 *
	 * @var Public_
	 * @since 1.0.0
	 */
	private Public_ $public_;

	/**
	 * Product service instance
	 *
	 * @var ProductService
	 * @since 1.0.0
	 */
	private ProductService $product_service;

	/**
	 * Affiliate service instance
	 *
	 * @var AffiliateService
	 * @since 1.0.0
	 */
	private AffiliateService $affiliate_service;

	/**
	 * Products controller instance
	 *
	 * @var ProductsController
	 * @since 1.0.0
	 */
	private ProductsController $products_controller;

	/**
	 * Affiliates controller instance
	 *
	 * @var AffiliatesController
	 * @since 1.0.0
	 */
	private AffiliatesController $affiliates_controller;

	/**
	 * Health controller instance
	 *
	 * @var HealthController
	 * @since 1.0.0
	 */
	private HealthController $health_controller;

	/**
	 * Terms controller instance
	 *
	 * @var TermsController
	 * @since 1.0.0
	 */
	private TermsController $terms_controller;

	/**
	 * Products command instance
	 *
	 * @var ProductsCommand
	 * @since 1.0.0
	 */
	private ProductsCommand $products_command;

	/**
	 * Constructor
	 *
	 * Initializes all plugin services and registers them with WordPress.
	 *
	 * @param Admin $admin Admin service instance
	 * @param Public_ $public Public service instance
	 * @param ProductService $product_service Product service instance
	 * @param AffiliateService $affiliate_service Affiliate service instance
	 * @param ProductsController $products_controller Products controller instance
	 * @param AffiliatesController $affiliates_controller Affiliates controller instance
	 * @param HealthController $health_controller Health controller instance
	 * @param TermsController $terms_controller Terms controller instance
	 * @param ProductsCommand $products_command Products command instance
	 * @since 1.0.0
	 */
	public function __construct(
		Admin $admin,
		Public_ $public_,
		ProductService $product_service,
		AffiliateService $affiliate_service,
		ProductsController $products_controller,
		AffiliatesController $affiliates_controller,
		HealthController $health_controller,
		TermsController $terms_controller,
		ProductsCommand $products_command
	) {
		$this->admin                  = $admin;
		$this->public_                 = $public_;
		$this->product_service         = $product_service;
		$this->affiliate_service       = $affiliate_service;
		$this->products_controller      = $products_controller;
		$this->affiliates_controller   = $affiliates_controller;
		$this->health_controller       = $health_controller;
		$this->terms_controller        = $terms_controller;
		$this->products_command        = $products_command;
	}

	/**
	 * Register plugin
	 *
	 * Initializes all plugin services and registers their hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register(): void {
		// Initialize bootstrap services (they will hook into WordPress)
		$this->admin->init();
		$this->public_->init();
		$this->products_command->register();

		// Core registration
		add_action( 'init', [ $this->product_service, 'register_post_type' ], 0 );
		add_action( 'init', [ $this->product_service, 'register_taxonomies' ], 0 );

		// REST routes must be registered on rest_api_init
		add_action( 'rest_api_init', [ $this->products_controller, 'register_routes' ] );
		add_action( 'rest_api_init', [ $this->affiliates_controller, 'register_routes' ] );
		add_action( 'rest_api_init', [ $this->health_controller, 'register_routes' ] );
		add_action( 'rest_api_init', [ $this->terms_controller, 'register_routes' ] );

		// Hook into WordPress
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 10, 0 );
		add_action( 'init', [ $this, 'init' ], 10, 0 );
	}

	/**
	 * Plugins loaded hook
	 *
	 * Callback for plugins_loaded action.
	 * Initializes admin and public functionality.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action plugins_loaded
	 */
	public function plugins_loaded(): void {
		// Initialize admin functionality
		$this->admin->init();

		// Initialize public functionality
		$this->public_->init();
	}

	/**
	 * Init hook
	 *
	 * Callback for init action.
	 * Registers REST API routes and CLI commands.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action init
	 */
	public function init(): void {
		// Register CLI commands
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'aps_products', $this->products_command );
		}
	}
}
