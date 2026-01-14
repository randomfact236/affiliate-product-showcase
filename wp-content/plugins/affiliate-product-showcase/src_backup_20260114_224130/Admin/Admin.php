<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Services\ProductService;

final class Admin {
	private Settings $settings;
	private MetaBoxes $metaboxes;

	public function __construct( private Assets $assets, private ProductService $product_service, private Headers $headers ) {
		$this->settings  = new Settings();
		$this->metaboxes = new MetaBoxes( $this->product_service );
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'add_meta_boxes', [ $this, 'metaboxes', 'register' ] );
		add_action( 'save_post', [ $this, 'metaboxes', 'save_meta' ], 10, 2 );
		// Initialize security headers
		$this->headers->init();
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'Affiliate Showcase', Constants::TEXTDOMAIN ),
			__( 'Affiliate Showcase', Constants::TEXTDOMAIN ),
			Constants::MENU_CAP,
			Constants::SLUG,
			[ $this, 'render_settings_page' ],
			'dashicons-admin-generic'
		);
	}

	public function render_settings_page(): void {
		$settings = $this->settings->get();
		require Constants::viewPath( 'src/Admin/partials/settings-page.php' );
	}

	public function register_settings(): void {
		$this->settings->register();
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( false !== strpos( $hook, Constants::SLUG ) ) {
			$this->assets->enqueue_admin();
		}
	}

}
