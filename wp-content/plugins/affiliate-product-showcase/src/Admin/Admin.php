<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Services\ProductService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin - Handles admin functionality and security headers.
 * 
 * Manages admin pages, menu, and security headers for admin pages.
 * 
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class Admin {
	private Settings $settings;
	private MetaBoxes $metaboxes;

	public function __construct( private Assets $assets, private ProductService $product_service ) {
		$this->settings  = new Settings();
		$this->metaboxes = new MetaBoxes( $this->product_service );
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_init', [ $this, 'add_security_headers' ] );
		add_action( 'add_meta_boxes', [ $this->metaboxes, 'register' ] );
		add_action( 'save_post', [ $this->metaboxes, 'save_meta' ], 10, 2 );
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

	/**
	 * Add security headers to admin pages.
	 * 
	 * Adds Content-Security-Policy, X-Content-Type-Options,
	 * X-Frame-Options, and X-XSS-Protection headers.
	 * Only applies to plugin admin pages.
	 *
	 * @return void
	 */
	public function add_security_headers(): void {
		if ( false !== strpos( $_SERVER['PHP_SELF'] ?? '', 'affiliate-product-showcase' ) ) {
			header( "Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;" );
			header( 'X-Content-Type-Options: nosniff' );
			header( 'X-Frame-Options: DENY' );
			header( 'X-XSS-Protection: 1; mode=block' );
		}
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( false !== strpos( $hook, Constants::SLUG ) ) {
			$this->assets->enqueue_admin();
		}
	}
}
