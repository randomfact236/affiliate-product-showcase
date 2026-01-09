<?php

namespace AffiliateProductShowcase\Public;

use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class Public_ {
	private Shortcodes $shortcodes;
	private Widgets $widgets;
	private SettingsRepository $settings_repository;

	public function __construct( private Assets $assets, private ProductService $product_service ) {
		$this->settings_repository = new SettingsRepository();
		$this->shortcodes = new Shortcodes( $this->product_service, $this->settings_repository );
		$this->widgets    = new Widgets( $this->product_service, $this->settings_repository );
	}

	public function init(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
	}

	public function enqueue_frontend_assets(): void {
		$this->assets->enqueue_frontend();
	}

	public function register_shortcodes(): void {
		$this->shortcodes->register();
	}

	public function register_widgets(): void {
		$this->widgets->register();
	}
}
