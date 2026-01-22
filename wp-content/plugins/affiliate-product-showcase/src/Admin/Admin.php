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
	private ProductFormHandler $form_handler;
	private Menu $menu;
	private Columns $columns;

	public function __construct(
		private Assets $assets,
		private ProductService $product_service,
		private Headers $headers,
		Menu $menu,
		ProductFormHandler $form_handler
	) {
		$this->settings = new Settings();
		$this->metaboxes = new MetaBoxes( $this->product_service );
		$this->form_handler = $form_handler;
		$this->menu = $menu;
		$this->columns = new Columns();
	}

	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'add_meta_boxes', [ $this->metaboxes, 'register' ] );
		add_action( 'save_post', [ $this->metaboxes, 'save_meta' ], 10, 2 );
		$this->headers->init();
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
