<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Admin\TermMeta;
use AffiliateProductShowcase\Admin\TermUI;
use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use AffiliateProductShowcase\Blocks\Blocks;
use AffiliateProductShowcase\Cache\Cache;
use AffiliateProductShowcase\Cli\ProductsCommand;
use AffiliateProductShowcase\Public\Templates;
use AffiliateProductShowcase\Public\Shortcodes;
use AffiliateProductShowcase\Public\Widgets;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Privacy\GDPR;
use AffiliateProductShowcase\Rest\AnalyticsController;
use AffiliateProductShowcase\Rest\AffiliatesController;
use AffiliateProductShowcase\Rest\ProductsController;
use AffiliateProductShowcase\Rest\HealthController;
use AffiliateProductShowcase\Rest\TermsController;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AnalyticsService;
use AffiliateProductShowcase\Traits\SingletonTrait;
use AffiliateProductShowcase\Plugin\Container;

/**
 * Plugin Main Class
 *
 * Bootstrap class for the Affiliate Product Showcase plugin.
 * Initializes all services and registers WordPress hooks.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 * @author Development Team
 */
final class Plugin {
	use SingletonTrait;

	/**
	 * Loader instance
	 *
	 * @var Loader
	 * @since 1.0.0
	 */
	private Loader $loader;

	/**
	 * Assets instance
	 *
	 * @var Assets
	 * @since 1.0.0
	 */
	private Assets $assets;

	/**
	 * Manifest instance
	 *
	 * @var Manifest
	 * @since 1.0.0
	 */
	private Manifest $manifest;

	/**
	 * SRI instance
	 *
	 * @var SRI
	 * @since 1.0.0
	 */
	private SRI $sri;

	/**
	 * Cache instance
	 *
	 * @var Cache
	 * @since 1.0.0
	 */
	private Cache $cache;

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
	 * Analytics service instance
	 *
	 * @var AnalyticsService
	 * @since 1.0.0
	 */
	private AnalyticsService $analytics_service;

	/**
	 * Admin instance
	 *
	 * @var Admin
	 * @since 1.0.0
	 */
	private Admin $admin;

	/**
	 * TermMeta instance
	 *
	 * @var TermMeta
	 * @since 1.0.0
	 */
	private TermMeta $term_meta;

	/**
	 * TermUI instance
	 *
	 * @var TermUI
	 * @since 1.0.0
	 */
	private TermUI $term_ui;

	/**
	 * Templates instance
	 *
	 * @var Templates
	 * @since 1.0.0
	 */
	private Templates $templates;

	/**
	 * Shortcodes instance
	 *
	 * @var Shortcodes
	 * @since 1.0.0
	 */
	private Shortcodes $shortcodes;

	/**
	 * Widgets instance
	 *
	 * @var Widgets
	 * @since 1.0.0
	 */
	private Widgets $widgets;

	/**
	 * Public instance
	 *
	 * @var Public_
	 * @since 1.0.0
	 */
	private Public_ $public;

	/**
	 * Blocks instance
	 *
	 * @var Blocks
	 * @since 1.0.0
	 */
	private Blocks $blocks;

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
	 * Analytics controller instance
	 *
	 * @var AnalyticsController
	 * @since 1.0.0
	 */
	private AnalyticsController $analytics_controller;

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
	 * GDPR instance
	 *
	 * @var GDPR
	 * @since 1.0.0
	 */
	private GDPR $gdpr;

	/**
	 * Products command instance
	 *
	 * @var ProductsCommand
	 * @since 1.0.0
	 */
	private ProductsCommand $products_command;

	/**
	 * Headers instance
	 *
	 * @var Headers
	 * @since 1.0.0
	 */
	private Headers $headers;

	/**
	 * Initialize plugin
	 *
	 * Hooks textdomain loading to init action (WordPress best practice),
	 * then bootstraps services and registers loader.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init(): void {
		// Hook textdomain loading to init action (WordPress best practice)
		add_action( 'init', [ $this, 'aps_load_plugin_textdomain' ] );
		
		// Bootstrap all services
		$this->bootstrap();
		
		// Register loader and hooks
		$this->loader->register();
	}

	/**
	 * Bootstrap plugin
	 *
	 * Resolves all services from DI container and initializes loader.
	 * Note: Textdomain loading moved to init() method for WordPress best practices.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function bootstrap(): void {

		/** Get DI container instance */
		$container = Container::get_instance();

		/** Resolve all services from container (automatic dependency injection) */
		$this->cache               = $container->get( Cache::class );
		$this->manifest            = $container->get( Manifest::class );
		$this->sri                 = $container->get( SRI::class );
		$this->assets              = $container->get( Assets::class );
		$this->product_service     = $container->get( ProductService::class );
		$this->affiliate_service   = $container->get( AffiliateService::class );
		$this->analytics_service   = $container->get( AnalyticsService::class );
		$this->headers             = $container->get( Headers::class );
		$this->admin               = $container->get( Admin::class );
		$this->term_meta          = $container->get( TermMeta::class );
		$this->term_ui             = $container->get( TermUI::class );
		$this->templates           = $container->get( Templates::class );
		$this->shortcodes          = $container->get( Shortcodes::class );
		$this->widgets             = $container->get( Widgets::class );
		$this->public              = $container->get( Public_::class );
		$this->blocks              = $container->get( Blocks::class );
		$this->products_controller  = $container->get( ProductsController::class );
		$this->affiliates_controller = $container->get( AffiliatesController::class );
		$this->analytics_controller = $container->get( AnalyticsController::class );
		$this->health_controller   = $container->get( HealthController::class );
		$this->terms_controller    = $container->get( TermsController::class );
		$this->gdpr               = $container->get( GDPR::class );
		$this->products_command    = $container->get( ProductsCommand::class );
		
		/** Initialize GDPR compliance */
		$this->gdpr->register();
		
		/** Initialize loader with all dependencies in correct order */
		$this->loader = new Loader(
			$this->admin,                // 1. Admin
			$this->public,               // 2. Public_
			$this->product_service,       // 3. ProductService
			$this->affiliate_service,     // 4. AffiliateService
			$this->products_controller,    // 5. ProductsController
			$this->affiliates_controller, // 6. AffiliatesController
			$this->health_controller,     // 7. HealthController
			$this->terms_controller,      // 8. TermsController
			$this->products_command       // 9. ProductsCommand
		);
	}

	/**
	 * Set product service (for dependency injection)
	 *
	 * @param ProductService $service Product service instance
	 * @return void
	 * @since 1.0.0
	 */
	public function set_product_service( ProductService $service ): void {
		$this->product_service = $service;
	}

	/**
	 * Set affiliate service (for dependency injection)
	 *
	 * @param AffiliateService $service Affiliate service instance
	 * @return void
	 * @since 1.0.0
	 */
	public function set_affiliate_service( AffiliateService $service ): void {
		$this->affiliate_service = $service;
	}

	/**
	 * Set analytics service (for dependency injection)
	 *
	 * @param AnalyticsService $service Analytics service instance
	 * @return void
	 * @since 1.0.0
	 */
	public function set_analytics_service( AnalyticsService $service ): void {
		$this->analytics_service = $service;
	}

	/**
	 * Load plugin text domain
	 *
	 * Loads translations for plugin on init action hook.
	 * Hooked to init action in init() method to follow WordPress best practices.
	 * Renamed from load_textdomain to avoid WordPress core function conflict.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action init 0
	 * @link https://developer.wordpress.org/plugins/hooks/plugin_locale/
	 */
	public function aps_load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'affiliate-product-showcase',
			false,
			dirname( AFFILIATE_PRODUCT_SHOWCASE_DIR ) . '/languages'
		);
	}

	/**
	 * Get assets instance
	 *
	 * @return Assets
	 * @since 1.0.0
	 */
	public function assets(): Assets {
		return $this->assets;
	}

	/**
	 * Get cache instance
	 *
	 * @return Cache
	 * @since 1.0.0
	 */
	public function cache(): Cache {
		return $this->cache;
	}
}
