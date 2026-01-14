<?php
declare( strict_types=1 );

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use AffiliateProductShowcase\Blocks\Blocks;
use AffiliateProductShowcase\Cache\Cache;
use AffiliateProductShowcase\Cli\ProductsCommand;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Privacy\GDPR;
use AffiliateProductShowcase\Rest\AnalyticsController;
use AffiliateProductShowcase\Rest\ProductsController;
use AffiliateProductShowcase\Rest\HealthController;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Services\AnalyticsService;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Traits\SingletonTrait;

final class Plugin {
	use SingletonTrait;

	private Loader $loader;
	private Assets $assets;
	private Manifest $manifest;
	private SRI $sri;
	private Cache $cache;
	private ProductService $product_service;
	private AffiliateService $affiliate_service;
	private AnalyticsService $analytics_service;
	private Admin $admin;
	private Public_ $public;
	private Blocks $blocks;
	private ProductsController $products_controller;
	private AnalyticsController $analytics_controller;
	private HealthController $health_controller;
	private GDPR $gdpr;
	private ProductsCommand $products_command;
	private Headers $headers;

	public function init(): void {
		$this->bootstrap();
		$this->loader->register();
	}

	private function bootstrap(): void {
		$this->load_textdomain();

		$this->cache              = new Cache();
		$this->manifest           = Manifest::get_instance();
		$this->sri                = new SRI( $this->manifest );
		$this->manifest->set_sri( $this->sri );
		$this->assets             = new Assets( $this->manifest );
		
		// Instantiate services with all required dependencies
		// ProductService: ProductRepository, ProductValidator, ProductFactory, PriceFormatter
		$this->product_service    = $this->product_service ?? new ProductService(
			new \AffiliateProductShowcase\Repositories\ProductRepository(),
			new \AffiliateProductShowcase\Validators\ProductValidator(),
			new \AffiliateProductShowcase\Factories\ProductFactory(),
			new \AffiliateProductShowcase\Formatters\PriceFormatter()
		);
		
		// AffiliateService: SettingsRepository
		$this->affiliate_service  = $this->affiliate_service ?? new AffiliateService(
			new \AffiliateProductShowcase\Repositories\SettingsRepository()
		);
		
		// AnalyticsService: Cache
		$this->analytics_service  = $this->analytics_service ?? new AnalyticsService( $this->cache );
		
		// Headers: Security header management
		$this->headers            = new Headers();
		
		$this->admin              = new Admin( $this->assets, $this->product_service, $this->headers );
		$this->public             = new Public_( $this->assets, $this->product_service );
		$this->blocks             = new Blocks( $this->product_service );
		$this->products_controller = new ProductsController( $this->product_service );
		$this->analytics_controller = new AnalyticsController( $this->analytics_service );
		$this->health_controller  = new HealthController();
		$this->gdpr              = new GDPR();
		$this->products_command    = new ProductsCommand( $this->product_service );
		
		$this->gdpr->register();
		
		$this->loader              = new Loader(
			$this->product_service,
			$this->admin,
			$this->public,
			$this->blocks,
			$this->products_controller,
			$this->analytics_controller,
			$this->health_controller,
			$this->products_command
		);
	}

	/**
	 * Set product service (for dependency injection)
	 *
	 * @param ProductService $service
	 */
	public function set_product_service( ProductService $service ): void {
		$this->product_service = $service;
	}

	/**
	 * Set affiliate service (for dependency injection)
	 *
	 * @param AffiliateService $service
	 */
	public function set_affiliate_service( AffiliateService $service ): void {
		$this->affiliate_service = $service;
	}

	/**
	 * Set analytics service (for dependency injection)
	 *
	 * @param AnalyticsService $service
	 */
	public function set_analytics_service( AnalyticsService $service ): void {
		$this->analytics_service = $service;
	}

	private function load_textdomain(): void {
		load_plugin_textdomain(
			Constants::TEXTDOMAIN,
			false,
			Constants::languagesPath()
		);
	}

	public function assets(): Assets {
		return $this->assets;
	}

	public function cache(): Cache {
		return $this->cache;
	}
}
