<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class Public_ {
    private Shortcodes $shortcodes;
    private Widgets $widgets;
    private AjaxHandler $ajax_handler;

    public function __construct(
        private Assets $assets,
        private ProductService $product_service,
        private SettingsRepository $settings_repository,
        private AffiliateService $affiliate_service
    ) {
        $this->shortcodes = new Shortcodes( $this->product_service, $this->settings_repository, $this->affiliate_service );
        $this->widgets    = new Widgets( $this->product_service, $this->settings_repository, $this->affiliate_service );
        $this->ajax_handler = new AjaxHandler( $this->product_service );
    }

    public function init(): void {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
        
        // Register AJAX handlers
        $this->ajax_handler->register();
    }

    public function enqueue_frontend_assets(): void {
        $this->assets->enqueue_frontend();
    }

	public function enqueue_block_assets(): void {
		$this->assets->enqueue_editor();
	}

	public function enqueue_editor_assets(): void {
		$this->assets->enqueue_editor();
	}

    public function register_shortcodes(): void {
        $this->shortcodes->register();
    }

    public function register_widgets(): void {
        $this->widgets->register();
    }
}
