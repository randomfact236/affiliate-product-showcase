<?php

namespace AffiliateProductShowcase\Plugin;

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Blocks\Blocks;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Rest\AnalyticsController;
use AffiliateProductShowcase\Rest\ProductsController;
use AffiliateProductShowcase\Cli\ProductsCommand;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Traits\HooksTrait;

final class Loader {
	use HooksTrait;

	public function __construct(
		private ProductService $product_service,
		private Admin $admin,
		private Public_ $public,
		private Blocks $blocks,
		private ProductsController $products_controller,
		private AnalyticsController $analytics_controller,
		private ProductsCommand $products_command
	) {}

	public function register(): void {
		$this->register_hooks();
		$this->admin->init();
		$this->public->init();
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$this->products_command->register();
		}
	}

	protected function actions(): array {
		return [
			[ 'init', 'register_product_cpt' ],
			[ 'init', 'register_blocks' ],
			[ 'init', 'register_shortcodes' ],
			[ 'widgets_init', 'register_widgets' ],
			// IMPORTANT: run before WP core priority-10 block enqueue.
			[ 'enqueue_block_editor_assets', 'enqueue_block_editor_assets', 9 ],
			// IMPORTANT: ensure block front-end handles exist before core enqueues them.
			[ 'enqueue_block_assets', 'enqueue_block_assets', 9 ],
			[ 'rest_api_init', 'register_rest_controllers' ],
			[ 'cli_init', 'register_cli' ],
		];
	}

	public function register_product_cpt(): void {
		$this->product_service->register_post_type();
	}

	public function register_blocks(): void {
		$this->blocks->register();
	}

	public function register_shortcodes(): void {
		$this->public->register_shortcodes();
	}

	public function register_widgets(): void {
		$this->public->register_widgets();
	}

	public function enqueue_block_assets(): void {
		$this->public->enqueue_block_assets();
	}

	public function enqueue_block_editor_assets(): void {
		$this->public->enqueue_editor_assets();
	}

	public function register_rest_controllers(): void {
		$this->products_controller->register_routes();
		$this->analytics_controller->register_routes();
	}

	public function register_cli(): void {
		$this->products_command->register();
	}
}
