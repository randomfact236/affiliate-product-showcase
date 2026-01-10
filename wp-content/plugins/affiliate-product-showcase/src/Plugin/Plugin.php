<?php

namespace AffiliateProductShowcase\Plugin;

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use AffiliateProductShowcase\Blocks\Blocks;
use AffiliateProductShowcase\Cache\Cache;
use AffiliateProductShowcase\Cli\ProductsCommand;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Rest\AnalyticsController;
use AffiliateProductShowcase\Rest\ProductsController;
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
	private ProductsCommand $products_command;

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
		$this->product_service    = new ProductService();
		$this->affiliate_service  = new AffiliateService();
		$this->analytics_service  = new AnalyticsService();
		$this->admin              = new Admin( $this->assets, $this->product_service );
		$this->public             = new Public_( $this->assets, $this->product_service );
		$this->blocks             = new Blocks( $this->product_service );
		$this->products_controller = new ProductsController( $this->product_service );
		$this->analytics_controller = new AnalyticsController( $this->analytics_service );
		$this->products_command    = new ProductsCommand( $this->product_service );
		$this->loader              = new Loader(
			$this->product_service,
			$this->admin,
			$this->public,
			$this->blocks,
			$this->products_controller,
			$this->analytics_controller,
			$this->products_command
		);
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
