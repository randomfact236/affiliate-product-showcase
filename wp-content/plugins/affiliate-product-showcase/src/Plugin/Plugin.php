<?php
declare(strict_types=1);

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
use AffiliateProductShowcase\Plugin\Container;

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
		// Get DI container instance
		$container = Container::get_instance();

		// Resolve all services from container (automatic dependency injection)
		$this->cache               = $container->get( Cache::class );
		$this->manifest            = $container->get( Manifest::class );
		$this->sri                 = $container->get( SRI::class );
		$this->assets              = $container->get( Assets::class );
		$this->product_service     = $container->get( ProductService::class );
		$this->affiliate_service   = $container->get( AffiliateService::class );
		$this->analytics_service   = $container->get( AnalyticsService::class );
		$this->headers             = $container->get( Headers::class );
		$this->admin               = $container->get( Admin::class );
		$this->public              = $container->get( Public_::class );
		$this->blocks              = $container->get( Blocks::class );
		$this->products_controller  = $container->get( ProductsController::class );
		$this->analytics_controller = $container->get( AnalyticsController::class );
		$this->health_controller   = $container->get( HealthController::class );
		$this->gdpr               = $container->get( GDPR::class );
		$this->products_command    = $container->get( ProductsCommand::class );
		
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

	public function assets(): Assets {
		return $this->assets;
	}

	public function cache(): Cache {
		return $this->cache;
	}
}
